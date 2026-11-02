<?php

namespace App\Services;

use App\Mail\ContinueTrackingMail;
use App\Mail\PriceDropAlertMail;
use App\Models\PriceAlert;
use App\Models\PriceHistory;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Store;
use App\Models\StorePrice;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class PriceCompareService
{
    /** Ngưỡng gửi mail: giá hiện tại được phép cao hơn giá thấp nhất 15 ngày tối đa 0.8%. */
    private const OPTIMAL_ALERT_TOLERANCE_PERCENT = 0.8;
    private const OPTIMAL_ALERT_MULTIPLIER = 1.008;

    public function __construct(
        private readonly ProductPageCrawler $crawler,
        private readonly AffiliateLinkService $affiliateLinkService,
    ) {}


    /**
     * Crawl tự động các link trong store_prices.
     * Lệnh này được routes/console.php gọi bởi products:crawl và prices:run-cycle.
     * Mỗi link crawl thành công sẽ cập nhật store_prices và lưu thêm price_history.
     */
    public function crawlStorePrices(?int $onlyId = null, int $limit = 5): array
    {
        $limit = max(1, $limit);

        $query = StorePrice::query()
            ->with(['store', 'variant.product'])
            ->whereNotNull('product_url')
            ->where('product_url', '!=', '')
            ->where('product_url', '!=', '#');

        if ($onlyId !== null) {
            $query->where('id', $onlyId);
        } else {
            // Crawl dòng cũ nhất trước để sau vài vòng sẽ tới cả CellphoneS và FPT Shop.
            if (Schema::hasColumn('store_prices', 'last_crawled_at')) {
                $query->orderByRaw('last_crawled_at IS NULL DESC')->orderBy('last_crawled_at');
            } elseif (Schema::hasColumn('store_prices', 'updated_at')) {
                $query->orderBy('updated_at');
            } else {
                $query->orderBy('id');
            }

            $query->limit($limit);
        }

        $results = [];

        foreach ($query->get() as $storePrice) {
            try {
                $updated = $this->refreshStorePrice($storePrice);

                $results[] = [
                    'id' => $updated->id,
                    'ok' => true,
                    'store' => $updated->store?->store_name ?? '',
                    'variant' => trim(($updated->variant?->product?->product_name ?? '').' '.($updated->variant?->storage ?? '').' '.($updated->variant?->color ?? '')),
                    'current_price' => (float) $updated->current_price,
                    'original_price' => $updated->original_price !== null ? (float) $updated->original_price : null,
                    'product_url' => $updated->product_url,
                    'affiliate_url' => $updated->affiliate_url ?? null,
                ];
            } catch (Throwable $exception) {
                report($exception);

                $payload = [];
                if (Schema::hasColumn('store_prices', 'last_crawled_at')) {
                    $payload['last_crawled_at'] = now();
                }
                if (Schema::hasColumn('store_prices', 'last_crawl_status')) {
                    $payload['last_crawl_status'] = 'failed';
                }
                if (Schema::hasColumn('store_prices', 'crawl_error')) {
                    $payload['crawl_error'] = mb_substr($exception->getMessage(), 0, 500);
                }

                if (! empty($payload)) {
                    $storePrice->fill($payload)->save();
                }

                $results[] = [
                    'id' => $storePrice->id,
                    'ok' => false,
                    'store' => $storePrice->store?->store_name ?? '',
                    'variant' => trim(($storePrice->variant?->product?->product_name ?? '').' '.($storePrice->variant?->storage ?? '').' '.($storePrice->variant?->color ?? '')),
                    'product_url' => $storePrice->product_url,
                    'error' => $exception->getMessage(),
                ];
            }
        }

        return $results;
    }

    /**
     * Tạo/cập nhật affiliate_url cho các dòng store_prices đang có product_url.
     */
    public function generateAffiliateLinks(?int $onlyId = null, int $limit = 200): array
    {
        if (! Schema::hasColumn('store_prices', 'affiliate_url')) {
            return [
                'message' => 'Bảng store_prices chưa có cột affiliate_url.',
                'updated' => 0,
                'skipped' => 0,
                'samples' => [],
            ];
        }

        $limit = max(1, $limit);

        $query = StorePrice::query()
            ->with('store')
            ->whereNotNull('product_url')
            ->where('product_url', '!=', '')
            ->where('product_url', '!=', '#');

        if ($onlyId !== null) {
            $query->where('id', $onlyId);
        } else {
            $query->where(function ($query): void {
                $query->whereNull('affiliate_url')->orWhere('affiliate_url', '');
            })->limit($limit);
        }

        $updated = 0;
        $skipped = 0;
        $samples = [];

        foreach ($query->get() as $storePrice) {
            $url = trim((string) $storePrice->product_url);

            if ($url === '' || $url === '#') {
                $skipped++;
                continue;
            }

            try {
                $affiliateUrl = $this->affiliateLinkService->make($url, $storePrice->store?->store_name ?? $url);

                if ($affiliateUrl === '') {
                    $skipped++;
                    continue;
                }

                $storePrice->affiliate_url = $affiliateUrl;
                $storePrice->save();
                $updated++;

                if (count($samples) < 5) {
                    $samples[] = [
                        'id' => $storePrice->id,
                        'product_url' => $url,
                        'affiliate_url' => $affiliateUrl,
                    ];
                }
            } catch (Throwable $exception) {
                report($exception);
                $skipped++;
            }
        }

        return [
            'message' => 'Tạo link affiliate hoàn tất.',
            'updated' => $updated,
            'skipped' => $skipped,
            'samples' => $samples,
        ];
    }

    /**
     * Một vòng tự động hoàn chỉnh:
     * 1) Crawl giá và lưu price_history
     * 2) Bổ sung affiliate_url nếu còn thiếu
     * 3) Kiểm tra price_alerts pending để gửi email khi giá đạt yêu cầu
     */
    public function runCycle(int $limit = 5): array
    {
        $crawlResults = $this->crawlStorePrices(null, $limit);
        $affiliateResults = $this->generateAffiliateLinks(null, max($limit * 2, 20));
        $mailSent = $this->checkPendingAlerts();

        $crawled = collect($crawlResults)->where('ok', true)->count();
        $failed = collect($crawlResults)->where('ok', false)->count();

        return [
            'crawled' => $crawled,
            'failed' => $failed,
            'mail_sent' => $mailSent,
            'affiliate_updated' => $affiliateResults['updated'] ?? 0,
            'affiliate_skipped' => $affiliateResults['skipped'] ?? 0,
            'crawl_results' => $crawlResults,
            'affiliate_results' => $affiliateResults,
        ];
    }

    /**
     * Người dùng nhập 1 URL + email.
     * Hệ thống:
     * 1) tìm URL trong DB, nếu chưa có thì crawl live và lưu;
     * 2) cố gắng crawl lại các link cùng biến thể ở CellphoneS/FPT Shop;
     * 3) trả bảng so sánh giá hiện tại 2 sàn + giá thấp nhất trong 15 ngày;
     * 4) gửi mail khi giá hiện tại không cao hơn quá 0.8% so với giá thấp nhất 15 ngày.
     */
    public function checkAndSave(string $url, string $email, int|float|null $targetPrice = null): array
    {
        $targetPrice = $targetPrice !== null ? (float) $targetPrice : null;
        $url = $this->normalizeUrl($url);
        $user = $this->findOrCreateUser($email);

        $storePrice = $this->findStorePriceByUrl($url);
        $crawlStatus = 'from_database';
        $crawlErrors = [];

        if (! $storePrice) {
            $storePrice = $this->crawlAndPersistUrl($url);
            $crawlStatus = 'crawled_live';
        } else {
            try {
                // Ưu tiên giá mới nhất cho đúng yêu cầu “giá hiện tại”.
                $storePrice = $this->refreshStorePrice($storePrice);
                $crawlStatus = 'refreshed_from_live';
            } catch (Throwable $exception) {
                // Nếu website chặn crawl/live lỗi, hệ thống vẫn dùng DB để demo không bị chết.
                report($exception);
                $crawlErrors[] = $exception->getMessage();
                $storePrice->refresh();
            }
        }

        // Nếu database có link cùng biến thể ở cả 2 sàn, hệ thống crawl cả 2 trong cùng một lượt.
        $crawlErrors = array_merge($crawlErrors, $this->refreshStorePricesForVariant((int) $storePrice->variant_id, (int) $storePrice->id));

        $storePrice->refresh()->loadMissing('store', 'variant.product');
        $comparison = $this->getComparisonForVariant((int) $storePrice->variant_id, $targetPrice);
        $bestCurrent = $comparison['best_current'];
        $optimalAlert = $comparison['optimal_alert'] ?? null;

        // GIỮ NGUYÊN GIÁ MONG MUỐN DO NGƯỜI DÙNG NHẬP.
        // Không được dùng ngưỡng tối ưu 0.8% để ghi vào price_alerts.target_price.
        // Ví dụ lỗi cũ: 39.890.000 x 1.008 = 40.209.120 và bị ghi đè lên giá mong muốn.
        $savedTargetPrice = $targetPrice !== null && $targetPrice > 0
            ? (float) $targetPrice
            : (float) ($bestCurrent['current_price'] ?? 0);

        $currentBestPrice = $bestCurrent !== null ? (float) $bestCurrent['current_price'] : null;
        $isOk = $currentBestPrice !== null
            && $savedTargetPrice > 0
            && $currentBestPrice <= $savedTargetPrice;

        $alertPayload = [
            'user_id' => $user->id,
            'variant_id' => $storePrice->variant_id,
            'target_price' => $savedTargetPrice,
        ];
        if (Schema::hasColumn('price_alerts', 'submitted_url')) {
            $alertPayload['submitted_url'] = $url;
        }
        if (Schema::hasColumn('price_alerts', 'alert_status')) {
            $alertPayload['alert_status'] = $isOk ? 'triggered' : 'pending';
        } elseif (Schema::hasColumn('price_alerts', 'status')) {
            $alertPayload['status'] = $isOk ? 'triggered' : 'pending';
        }

        $alert = PriceAlert::create($alertPayload);

        if ($isOk && $bestCurrent !== null) {
            $bestStorePrice = StorePrice::query()->with('store', 'variant.product')->find($bestCurrent['store_price_id']);
            if ($bestStorePrice) {
                $this->sendPriceOkMail($user, $alert, $bestStorePrice);
            }
        }

        return [
            'user' => $user,
            'alert' => $alert->load('variant.product'),
            'store_price' => $storePrice,
            'target_price' => $savedTargetPrice,
            'alert_threshold' => $savedTargetPrice,
            'optimal_threshold' => $optimalAlert['threshold_price'] ?? null,
            'price_gap_percent' => $currentBestPrice !== null && $savedTargetPrice > 0
                ? round((($currentBestPrice - $savedTargetPrice) / $savedTargetPrice) * 100, 2)
                : null,
            'is_ok' => $isOk,
            'crawl_status' => $crawlStatus,
            'crawl_errors' => $crawlErrors,
            'comparison' => $comparison,
            'message' => $isOk
                ? 'Giá hiện tại đã thấp hơn hoặc bằng giá mong muốn của bạn. Hệ thống đã gửi email thông báo.'
                : 'Hệ thống đã lưu giá mong muốn của bạn và sẽ gửi email khi giá sản phẩm thấp hơn hoặc bằng mức này.',
        ];
    }

    public function refreshStorePrice(StorePrice $storePrice): StorePrice
    {
        return $this->crawlAndPersistUrl($storePrice->product_url, $storePrice);
    }

    /**
     * Crawl tất cả link đang có của cùng một biến thể. Nếu biến thể có cả CellphoneS và FPT Shop,
     * lệnh này sẽ cập nhật cả 2 sàn trong cùng một lượt chạy.
     *
     * @return array<int,string> danh sách lỗi crawl không chặn flow chính
     */
    public function refreshStorePricesForVariant(int $variantId, ?int $exceptStorePriceId = null): array
    {
        $errors = [];

        StorePrice::query()
            ->where('variant_id', $variantId)
            ->when($exceptStorePriceId, fn ($query) => $query->where('id', '!=', $exceptStorePriceId))
            ->with(['store', 'variant.product'])
            ->get()
            ->each(function (StorePrice $storePrice) use (&$errors): void {
                try {
                    $this->refreshStorePrice($storePrice);
                } catch (Throwable $exception) {
                    report($exception);
                    $errors[] = '#'.$storePrice->id.' '.$storePrice->product_url.' - '.$exception->getMessage();
                }
            });

        return $errors;
    }

    public function crawlAndPersistUrl(string $url, ?StorePrice $existingStorePrice = null): StorePrice
    {
        $url = $this->normalizeUrl($url);
        $data = $this->crawler->crawl($url);

        $store = $this->resolveStore((string) ($data['source'] ?? $url));
        $productName = trim((string) ($data['name'] ?? 'Sản phẩm chưa rõ tên'));
        $price = (float) ($data['price'] ?? 0);

        if ($price <= 0) {
            throw new RuntimeException('Không lấy được giá hợp lệ từ link sản phẩm.');
        }

        $affiliateUrl = $this->affiliateLinkService->make($url, $store->store_name);

        // Nếu link đã được map sẵn trong database, giữ nguyên variant_id hiện có.
        // Điều này rất quan trọng với FPT Shop vì nhiều màu có thể dùng chung một URL trang sản phẩm.
        // Nếu ép lấy tên/màu từ HTML FPT, hệ thống có thể làm mất mapping CellphoneS - FPT đã chuẩn bị.
        if ($existingStorePrice) {
            $existingStorePrice->loadMissing('variant.product');
            $variant = $existingStorePrice->variant;

            if (! $variant) {
                throw new RuntimeException('Bản ghi store_prices hiện tại chưa liên kết với biến thể sản phẩm.');
            }

            if (($data['image_url'] ?? null) && ! $variant->product?->thumbnail_url) {
                $variant->product?->update(['thumbnail_url' => $data['image_url']]);
            }
        } else {
            [$storage, $color] = $this->extractVariantParts($productName);
            $productBaseName = $this->extractBaseProductName($productName);
            $product = $this->findOrCreateMatchedProduct($productBaseName, $productName, $data['image_url'] ?? null);
            $variant = $this->findOrCreateMatchedVariant($product, $storage, $color, $store->store_name, $productName);
        }

        $storePrice = $existingStorePrice ?: StorePrice::where('store_id', $store->id)
            ->where('variant_id', $variant->id)
            ->first();

        if (! $storePrice) {
            $storePrice = new StorePrice([
                'store_id' => $store->id,
                'variant_id' => $variant->id,
            ]);
        }

        $payload = [
            'variant_id' => $variant->id,
            'store_id' => $store->id,
            'current_price' => $price,
            'original_price' => isset($data['original_price']) ? (float) $data['original_price'] : null,
            'product_url' => $url,
            'updated_at' => now(),
        ];

        // Tương thích cả database cũ và database đã patch.
        if (Schema::hasColumn('store_prices', 'affiliate_url')) {
            $payload['affiliate_url'] = $affiliateUrl;
        }
        if (Schema::hasColumn('store_prices', 'in_stock')) {
            $payload['in_stock'] = true;
        }
        if (Schema::hasColumn('store_prices', 'availability_status')) {
            $payload['availability_status'] = 'in_stock';
        }
        if (Schema::hasColumn('store_prices', 'last_crawled_at')) {
            $payload['last_crawled_at'] = now();
        }
        if (Schema::hasColumn('store_prices', 'last_crawl_status')) {
            $payload['last_crawl_status'] = 'success';
        }
        if (Schema::hasColumn('store_prices', 'crawl_error')) {
            $payload['crawl_error'] = null;
        }

        $storePrice->fill($payload);
        $storePrice->save();

        $this->insertPriceHistoryIfNeeded($variant, $store, $price);

        return $storePrice->load('store', 'variant.product');
    }

    /**
     * Kết quả so sánh cho 1 biến thể sản phẩm:
     * - current_prices: giá hiện tại từng sàn
     * - best_current: giá thấp nhất hiện tại
     * - best_fifteen_days: giá thấp nhất trong 15 ngày gần nhất từ price_history
     */
    public function getComparisonForVariant(int $variantId, ?float $targetPrice = null): array
    {
        $storePrices = StorePrice::query()
            ->with(['store', 'variant.product'])
            ->where('variant_id', $variantId)
            ->where('current_price', '>', 0)
            ->when(Schema::hasColumn('store_prices', 'in_stock'), fn ($query) => $query->where('in_stock', true))
            ->when(Schema::hasColumn('store_prices', 'availability_status'), fn ($query) => $query->where(function ($subQuery): void {
                $subQuery->whereNull('availability_status')->orWhere('availability_status', '!=', 'out_of_stock');
            }))
            ->orderBy('current_price')
            ->get();

        $currentPrices = $storePrices->map(function (StorePrice $storePrice) use ($targetPrice): array {
            $price = (float) $storePrice->current_price;

            return [
                'store_price_id' => $storePrice->id,
                'store_name' => $storePrice->store?->store_name ?? 'Không rõ',
                'product_name' => $storePrice->variant?->product?->product_name ?? 'Sản phẩm',
                'current_price' => $price,
                'display_price' => number_format($price, 0, ',', '.').'đ',
                'target_ok' => $targetPrice !== null && $price <= $targetPrice,
                'product_url' => $storePrice->product_url,
                'affiliate_url' => $storePrice->affiliate_url ?: $storePrice->product_url,
                'updated_at' => optional($storePrice->updated_at)->format('d/m/Y H:i'),
            ];
        })->values()->all();

        $bestCurrent = $currentPrices[0] ?? null;

        $history = PriceHistory::query()
            ->with('store')
            ->where('variant_id', $variantId)
            ->where('price', '>', 1000000)
            ->where('recorded_at', '>=', now()->subDays(15))
            ->orderBy('price')
            ->orderByDesc('recorded_at')
            ->first();

        $bestFifteenDays = null;
        if ($history) {
            $matchedStorePrice = StorePrice::query()
                ->where('variant_id', $variantId)
                ->where('store_id', $history->store_id)
                ->first();

            $bestFifteenDays = [
                'store_price_id' => $matchedStorePrice?->id,
                'store_name' => $history->store?->store_name ?? 'Không rõ',
                'price' => (float) $history->price,
                'display_price' => number_format((float) $history->price, 0, ',', '.').'đ',
                'recorded_at' => optional($history->recorded_at)->format('d/m/Y H:i'),
                'affiliate_url' => $matchedStorePrice?->affiliate_url ?: $matchedStorePrice?->product_url,
            ];
        } elseif ($bestCurrent) {
            // Fallback để web vẫn có kết quả khi DB lịch sử trống.
            $bestFifteenDays = [
                'store_price_id' => $bestCurrent['store_price_id'],
                'store_name' => $bestCurrent['store_name'],
                'price' => $bestCurrent['current_price'],
                'display_price' => $bestCurrent['display_price'],
                'recorded_at' => $bestCurrent['updated_at'],
                'affiliate_url' => $bestCurrent['affiliate_url'],
            ];
        }

        // Gắn nhãn cho từng sàn theo ngưỡng 0.8% so với giá thấp nhất 15 ngày.
        if ($bestFifteenDays && (float) $bestFifteenDays['price'] > 0) {
            $lowest15 = (float) $bestFifteenDays['price'];
            $threshold = $lowest15 * self::OPTIMAL_ALERT_MULTIPLIER;

            $currentPrices = array_map(function (array $row) use ($lowest15, $threshold): array {
                $gapPercent = (($row['current_price'] - $lowest15) / $lowest15) * 100;
                $row['gap_percent'] = round($gapPercent, 2);
                $row['gap_display'] = number_format(round($gapPercent, 2), 2, ',', '.').'%';
                $row['near_low_ok'] = $row['current_price'] <= $threshold;
                $row['optimal_status_label'] = match (true) {
                    $row['current_price'] <= $lowest15 => 'Giá thấp nhất mới',
                    $row['current_price'] <= $threshold => 'Rất gần đáy 15 ngày',
                    $gapPercent <= 1.5 => 'Gần vùng giá tốt',
                    default => 'Nên theo dõi thêm',
                };

                return $row;
            }, $currentPrices);

            $bestCurrent = $currentPrices[0] ?? null;
        }

        $historicalBest = $this->getHistoricalBest($variantId);
        $prediction = $this->predictOptimalPrice($variantId, $bestCurrent, $historicalBest, $bestFifteenDays);
        $optimalAlert = $this->buildOptimalAlert($bestCurrent, $bestFifteenDays);

        return [
            'current_prices' => $currentPrices,
            'best_current' => $bestCurrent,
            'best_fifteen_days' => $bestFifteenDays,
            // Alias để không làm hỏng view/code cũ nếu còn gọi key cũ.
            'best_three_days' => $bestFifteenDays,
            'historical_best' => $historicalBest,
            'predicted_optimal' => $prediction,
            'optimal_alert' => $optimalAlert,
            'has_two_stores' => count($currentPrices) >= 2,
            'target_price' => $targetPrice,
        ];
    }

    private function buildOptimalAlert(?array $bestCurrent, ?array $bestFifteenDays): array
    {
        if (! $bestCurrent || ! $bestFifteenDays || (float) ($bestFifteenDays['price'] ?? 0) <= 0) {
            return [
                'should_send_mail' => false,
                'status' => 'insufficient_data',
                'label' => 'Chưa đủ dữ liệu 15 ngày',
                'tolerance_percent' => self::OPTIMAL_ALERT_TOLERANCE_PERCENT,
                'threshold_price' => null,
                'display_threshold_price' => null,
                'gap_percent' => null,
                'message' => 'Hệ thống cần có giá hiện tại và giá thấp nhất 15 ngày để quyết định gửi mail.',
            ];
        }

        $currentPrice = (float) $bestCurrent['current_price'];
        $lowest15 = (float) $bestFifteenDays['price'];
        $thresholdPrice = $lowest15 * self::OPTIMAL_ALERT_MULTIPLIER;
        $gapPercent = (($currentPrice - $lowest15) / $lowest15) * 100;

        if ($currentPrice <= $lowest15) {
            $status = 'new_lowest';
            $label = 'Giá thấp nhất mới';
            $shouldSendMail = true;
            $message = 'Giá hiện tại bằng hoặc thấp hơn đáy 15 ngày, nên gửi email ngay.';
        } elseif ($currentPrice <= $thresholdPrice) {
            $status = 'optimal_zone';
            $label = 'Rất gần giá thấp nhất 15 ngày';
            $shouldSendMail = true;
            $message = 'Giá hiện tại chỉ lệch tối đa 0.8% so với đáy 15 ngày, đủ điều kiện gửi email.';
        } elseif ($gapPercent <= 1.5) {
            $status = 'near_good_zone';
            $label = 'Gần vùng giá tốt';
            $shouldSendMail = false;
            $message = 'Giá khá tốt nhưng chưa đủ sát ngưỡng 0.8%, chỉ hiển thị trên web và tiếp tục theo dõi.';
        } else {
            $status = 'wait_more';
            $label = 'Nên theo dõi thêm';
            $shouldSendMail = false;
            $message = 'Giá hiện tại còn cách khá xa đáy 15 ngày, chưa nên gửi email.';
        }

        $roundedThreshold = round($thresholdPrice / 10000) * 10000;

        return [
            'should_send_mail' => $shouldSendMail,
            'status' => $status,
            'label' => $label,
            'tolerance_percent' => self::OPTIMAL_ALERT_TOLERANCE_PERCENT,
            'lowest_15_days_price' => $lowest15,
            'threshold_price' => $thresholdPrice,
            'display_threshold_price' => number_format($roundedThreshold, 0, ',', '.').'đ',
            'gap_percent' => round($gapPercent, 2),
            'gap_display' => number_format(round($gapPercent, 2), 2, ',', '.').'%',
            'message' => $message,
        ];
    }

    private function getHistoricalBest(int $variantId): ?array
    {
        $history = PriceHistory::query()
            ->with('store')
            ->where('variant_id', $variantId)
            ->where('price', '>', 1000000)
            ->orderBy('price')
            ->orderByDesc('recorded_at')
            ->first();

        if (!$history) {
            return null;
        }

        $matchedStorePrice = StorePrice::query()
            ->where('variant_id', $variantId)
            ->where('store_id', $history->store_id)
            ->first();

        return [
            'store_price_id' => $matchedStorePrice?->id,
            'store_name' => $history->store?->store_name ?? 'Không rõ',
            'price' => (float) $history->price,
            'display_price' => number_format((float) $history->price, 0, ',', '.').'đ',
            'recorded_at' => optional($history->recorded_at)->format('d/m/Y H:i'),
            'affiliate_url' => $matchedStorePrice?->affiliate_url ?: $matchedStorePrice?->product_url,
        ];
    }

    /**
     * Dự đoán đơn giản cho đồ án: lấy dữ liệu lịch sử gần nhất để ước lượng mức giá nên chờ.
     * Không phải mô hình AI phức tạp; đây là thuật toán heuristic dựa trên min lịch sử + xu hướng gần đây.
     */
    private function predictOptimalPrice(int $variantId, ?array $bestCurrent, ?array $historicalBest, ?array $bestThreeDays): ?array
    {
        if (!$bestCurrent && !$historicalBest && !$bestThreeDays) {
            return null;
        }

        $recentPrices = PriceHistory::query()
            ->where('variant_id', $variantId)
            ->where('price', '>', 1000000)
            ->orderByDesc('recorded_at')
            ->limit(10)
            ->pluck('price')
            ->map(fn ($price) => (float) $price)
            ->values();

        $candidate = min(array_filter([
            $bestCurrent['current_price'] ?? null,
            $bestThreeDays['price'] ?? null,
            $historicalBest['price'] ?? null,
        ], fn ($price) => $price !== null && $price > 0));

        $note = 'Dùng mức thấp nhất đã ghi nhận để làm mốc giá tối ưu.';

        if ($recentPrices->count() >= 3) {
            $latest = $recentPrices[0];
            $older = $recentPrices[min(4, $recentPrices->count() - 1)];
            $trend = $latest - $older;

            if ($trend < 0) {
                $candidate = min($candidate, max(1000000, $latest + ($trend * 0.5)));
                $note = 'Giá đang có xu hướng giảm nhẹ, hệ thống dự đoán có thể chờ thấp hơn một chút.';
            } elseif ($trend > 0) {
                $note = 'Giá gần đây có xu hướng tăng, nên ưu tiên mức tốt nhất hiện tại hoặc mức thấp nhất lịch sử.';
            }
        }

        return [
            'price' => round($candidate / 10000) * 10000,
            'display_price' => number_format(round($candidate / 10000) * 10000, 0, ',', '.').'đ',
            'note' => $note,
        ];
    }

    public function checkPendingAlerts(): int
    {
        $sent = 0;

        PriceAlert::query()
            ->with(['user', 'variant.product'])
            ->where('alert_status', 'pending')
            ->chunkById(50, function ($alerts) use (&$sent): void {
                foreach ($alerts as $alert) {
                    $targetPrice = (float) $alert->target_price;

                    if ($targetPrice <= 0) {
                        continue;
                    }

                    $comparison = $this->getComparisonForVariant((int) $alert->variant_id, $targetPrice);
                    $bestPrice = $comparison['best_current'] ?? null;

                    if (! $bestPrice) {
                        continue;
                    }

                    $storePrice = StorePrice::query()
                        ->with('store', 'variant.product')
                        ->find($bestPrice['store_price_id']);

                    if (! $storePrice || ! $alert->user) {
                        continue;
                    }

                    $currentPrice = (float) $bestPrice['current_price'];

                    // Trường hợp 1: giá hiện tại đã đạt giá mong muốn người dùng nhập.
                    // Chỉ đổi trạng thái, tuyệt đối KHÔNG sửa price_alerts.target_price.
                    if ($currentPrice <= $targetPrice) {
                        $alert->update(['alert_status' => 'triggered']);
                        $this->sendPriceOkMail($alert->user, $alert->fresh(), $storePrice);
                        $sent++;
                        continue;
                    }

                    // Trường hợp 2: sau 3 ngày vẫn chưa đạt giá mong muốn.
                    // Hệ thống gửi email hỏi người dùng có muốn tiếp tục theo dõi không.
                    // KHÔNG đổi alert_status sang giá trị lạ vì Database_final chỉ có pending/triggered/canceled.
                    // Chỉ cập nhật updated_at để 3 ngày sau mới hỏi lại nếu người dùng chưa hủy.
                    if ($this->shouldAskContinueTracking($alert)) {
                        $alert->forceFill(['updated_at' => now()])->save();
                        $this->sendContinueTrackingMail($alert->user, $alert->fresh(), $storePrice);
                        $sent++;
                    }
                }
            });

        return $sent;
    }

    private function shouldAskContinueTracking(PriceAlert $alert): bool
    {
        $startAt = $alert->updated_at ?: $alert->created_at;

        if (! $startAt) {
            return false;
        }

        return $startAt->lte(now()->subDays(3));
    }

    private function sendContinueTrackingMail(User $user, PriceAlert $alert, StorePrice $storePrice): void
    {
        try {
            Mail::to($user->email)->send(new ContinueTrackingMail($alert, $storePrice));
        } catch (Throwable $exception) {
            report($exception);
        }
    }

    private function insertPriceHistoryIfNeeded(ProductVariant $variant, Store $store, float $price): void
    {
        $latest = PriceHistory::query()
            ->where('variant_id', $variant->id)
            ->where('store_id', $store->id)
            ->latest('recorded_at')
            ->first();

        // Lần đầu luôn lưu. Sau đó chỉ lưu nếu giá thay đổi, hoặc bản ghi cũ hơn 6 giờ.
        if (! $latest || (float) $latest->price !== $price || optional($latest->recorded_at)->lt(now()->subHours(6))) {
            PriceHistory::create([
                'variant_id' => $variant->id,
                'store_id' => $store->id,
                'price' => $price,
                'recorded_at' => now(),
            ]);
        }
    }

    private function findOrCreateUser(string $email): User
    {
        $email = mb_strtolower(trim($email));
        $userDefaults = ['password' => Hash::make(Str::random(32))];

        // Tương thích database cũ/mới: có DB chỉ có cột `name`, không có `user_name` hoặc `role`.
        if (Schema::hasColumn('users', 'name')) {
            $userDefaults['name'] = Str::before($email, '@');
        }
        if (Schema::hasColumn('users', 'user_name')) {
            $userDefaults['user_name'] = Str::before($email, '@');
        }
        if (Schema::hasColumn('users', 'role')) {
            $userDefaults['role'] = 'user';
        }

        return User::firstOrCreate(['email' => $email], $userDefaults);
    }

    private function findStorePriceByUrl(string $url): ?StorePrice
    {
        $normalizedUrl = $this->normalizeUrl($url);
        $withoutTrailingSlash = rtrim($normalizedUrl, '/');
        $withTrailingSlash = $withoutTrailingSlash.'/';

        $storePrice = StorePrice::query()
            ->with('store', 'variant.product')
            ->whereIn('product_url', array_unique([
                $normalizedUrl,
                $withoutTrailingSlash,
                $withTrailingSlash,
            ]))
            ->latest('updated_at')
            ->first();

        if ($storePrice) {
            return $storePrice;
        }

        $path = parse_url($normalizedUrl, PHP_URL_PATH);
        $host = parse_url($normalizedUrl, PHP_URL_HOST);

        if (! $path) {
            return null;
        }

        $path = rtrim($path, '/');

        return StorePrice::query()
            ->with('store', 'variant.product')
            ->when($host, function ($query) use ($host): void {
                $host = preg_replace('/^www\./i', '', (string) $host);
                $query->where('product_url', 'like', '%'.$host.'%');
            })
            ->where(function ($query) use ($path): void {
                $query->where('product_url', 'like', '%'.$path)
                    ->orWhere('product_url', 'like', '%'.$path.'?%')
                    ->orWhere('product_url', 'like', '%'.$path.'/%');
            })
            ->latest('updated_at')
            ->first();
    }

    private function resolveStore(string $source): Store
    {
        $source = mb_strtolower($source);

        if (str_contains($source, 'cellphones')) {
            return Store::firstOrCreate(
                ['store_name' => 'CellphoneS'],
                ['base_url' => 'https://cellphones.com.vn'],
            );
        }

        if (str_contains($source, 'fpt')) {
            return Store::firstOrCreate(
                ['store_name' => 'FPT Shop'],
                ['base_url' => 'https://fptshop.com.vn'],
            );
        }

        $host = parse_url($source, PHP_URL_HOST) ?: 'Khác';

        return Store::firstOrCreate(
            ['store_name' => $host],
            ['base_url' => Str::startsWith($source, 'http') ? $source : null],
        );
    }

    private function sendPriceOkMail(User $user, PriceAlert $alert, StorePrice $storePrice): void
    {
        try {
            Mail::to($user->email)->send(new PriceDropAlertMail($alert, $storePrice));
        } catch (Throwable $exception) {
            report($exception);
        }
    }

    private function normalizeUrl(string $url): string
    {
        return trim($url);
    }

    private function detectBrand(string $name): string
    {
        $lower = mb_strtolower($name);

        return match (true) {
            str_contains($lower, 'samsung') => 'Samsung',
            str_contains($lower, 'xiaomi') => 'Xiaomi',
            str_contains($lower, 'oppo') => 'OPPO',
            str_contains($lower, 'vivo') => 'Vivo',
            str_contains($lower, 'realme') => 'Realme',
            str_contains($lower, 'iphone'), str_contains($lower, 'apple') => 'Apple',
            default => 'Khác',
        };
    }

    /** @return array{0:string,1:string} */
    private function extractVariantParts(string $name): array
    {
        $storage = 'Không rõ';
        if (preg_match('/(\d+\s?(?:GB|TB))/iu', $name, $matches)) {
            $storage = strtoupper(str_replace(' ', '', $matches[1]));
        }

        $color = 'Không rõ';
        if (preg_match('/(?:màu|color)\s+([\p{L}\s]+)$/iu', $name, $matches)) {
            $color = trim($matches[1]);
        }

        return [$storage, $color];
    }

    private function extractBaseProductName(string $name): string
    {
        $name = preg_replace('/\s+/', ' ', $name) ?: $name;
        $name = preg_replace('/\b\d+\s?(?:GB|TB)\b/iu', '', $name) ?: $name;
        $name = preg_replace('/\s+-\s+màu\s+.+$/iu', '', $name) ?: $name;
        $name = preg_replace('/\s+màu\s+.+$/iu', '', $name) ?: $name;
        $name = preg_replace('/\s*\|.*$/u', '', $name) ?: $name;
        $name = preg_replace('/\b(chính hãng|vn\/a|new|mới)\b/iu', '', $name) ?: $name;
        $name = preg_replace('/^điện thoại\s+/iu', '', $name) ?: $name;
        $name = preg_replace('/\s+/', ' ', $name) ?: $name;

        return trim($name) ?: 'Sản phẩm chưa rõ tên';
    }

    private function findOrCreateMatchedProduct(string $baseName, string $fullName, ?string $imageUrl = null): Product
    {
        $targetKey = $this->productMatchKey($baseName);

        $product = Product::query()->get()->first(function (Product $product) use ($targetKey): bool {
            $key = $this->productMatchKey($product->product_name);

            return $key === $targetKey
                || ($key !== '' && str_contains($targetKey, $key))
                || ($targetKey !== '' && str_contains($key, $targetKey));
        });

        if (! $product) {
            $product = Product::create([
                'product_name' => $baseName,
                'brand' => $this->detectBrand($fullName),
                'thumbnail_url' => $imageUrl,
                'view_count' => 0,
            ]);
        } elseif ($imageUrl && ! $product->thumbnail_url) {
            $product->update(['thumbnail_url' => $imageUrl]);
        }

        return $product;
    }

    private function findOrCreateMatchedVariant(Product $product, string $storage, string $color, string $storeName, string $productName): ProductVariant
    {
        $variant = ProductVariant::query()
            ->where('product_id', $product->id)
            ->where('storage', $storage)
            ->where(function ($query) use ($color): void {
                $query->where('color', $color)
                    ->orWhere('color', 'Không rõ');
            })
            ->first();

        if ($variant) {
            return $variant;
        }

        return ProductVariant::create([
            'product_id' => $product->id,
            'storage' => $storage,
            'color' => $color,
            'sku' => $this->makeSku($storeName, $productName, $storage, $color),
        ]);
    }

    private function productMatchKey(string $name): string
    {
        $name = $this->extractBaseProductName($name);
        $ascii = Str::of($name)->ascii()->lower()->replaceMatches('/[^a-z0-9]+/', ' ')->trim()->toString();

        return preg_replace('/\s+/', ' ', $ascii) ?: '';
    }

    private function makeSku(string $storeName, string $productName, string $storage, string $color): string
    {
        return Str::upper(Str::slug($storeName.'-'.$productName.'-'.$storage.'-'.$color));
    }
}
