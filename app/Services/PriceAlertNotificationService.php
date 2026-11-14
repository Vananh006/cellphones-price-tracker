<?php

namespace App\Services;

use App\Mail\HistoricalLowestPriceMail;
use App\Mail\PriceTargetReachedMail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Throwable;

class PriceAlertNotificationService
{
    public function checkAndSend(bool $dryRun = false): array
    {
        $alerts = DB::table('price_alerts')
            ->join('users', 'price_alerts.user_id', '=', 'users.id')
            ->join('product_variants', 'price_alerts.variant_id', '=', 'product_variants.id')
            ->join('products', 'product_variants.product_id', '=', 'products.id')
            ->where('price_alerts.alert_status', 'pending')
            ->select(
                'price_alerts.id as alert_id',
                'price_alerts.user_id',
                'price_alerts.variant_id',
                'price_alerts.submitted_url',
                'price_alerts.target_price',
                'users.email',
                'users.user_name',
                'products.id as product_id',
                'products.product_name',
                'product_variants.storage',
                'product_variants.color'
            )
            ->orderBy('price_alerts.id')
            ->get();

        $result = [
            'checked' => $alerts->count(),
            'sent_target' => 0,
            'sent_lowest' => 0,
            'skipped' => 0,
            'failed' => 0,
            'samples' => [],
            'errors' => [],
        ];

        foreach ($alerts as $alert) {
            try {
                $prices = $this->pricesForVariant((int) $alert->variant_id);
                $bestPrice = $prices->first();

                if (!$bestPrice) {
                    $result['skipped']++;
                    continue;
                }

                $currentPrice = (float) $bestPrice->current_price;
                $targetPrice = (float) $alert->target_price;
                $previousLowest = $this->previousHistoricalLowest((int) $alert->variant_id, $bestPrice->updated_at ?? null);

                $targetReached = $currentPrice <= $targetPrice;
                $isHistoricalLowest = !$targetReached
                    && $previousLowest !== null
                    && $currentPrice < (float) $previousLowest;

                if (!$targetReached && !$isHistoricalLowest) {
                    $result['skipped']++;
                    continue;
                }

                $mailData = $this->buildMailData($alert, $prices, $bestPrice, $previousLowest);

                if (!$dryRun) {
                    if ($targetReached) {
                        Mail::to($alert->email)->send(new PriceTargetReachedMail($mailData));
                    } else {
                        Mail::to($alert->email)->send(new HistoricalLowestPriceMail($mailData));
                    }

                    DB::table('price_alerts')
                        ->where('id', $alert->alert_id)
                        ->update([
                            'alert_status' => 'triggered',
                            'updated_at' => now(),
                        ]);
                }

                if ($targetReached) {
                    $result['sent_target']++;
                    $type = 'target_reached';
                } else {
                    $result['sent_lowest']++;
                    $type = 'historical_lowest';
                }

                if (count($result['samples']) < 8) {
                    $result['samples'][] = [
                        'alert_id' => $alert->alert_id,
                        'email' => $alert->email,
                        'type' => $type,
                        'product' => $mailData['product_name'],
                        'current_price' => $currentPrice,
                        'target_price' => $targetPrice,
                        'best_shop' => $mailData['best_shop'],
                        'buy_url' => $mailData['buy_url'],
                    ];
                }
            } catch (Throwable $e) {
                $result['failed']++;
                $result['errors'][] = [
                    'alert_id' => $alert->alert_id ?? null,
                    'message' => $e->getMessage(),
                ];
            }
        }

        return $result;
    }

    private function pricesForVariant(int $variantId)
    {
        return DB::table('store_prices')
            ->join('stores', 'store_prices.store_id', '=', 'stores.id')
            ->where('store_prices.variant_id', $variantId)
            ->where('store_prices.current_price', '>', 0)
            ->where('store_prices.in_stock', 1)
            ->select(
                'store_prices.id',
                'store_prices.variant_id',
                'store_prices.store_id',
                'store_prices.current_price',
                'store_prices.original_price',
                'store_prices.product_url',
                'store_prices.affiliate_url',
                'store_prices.updated_at',
                'stores.store_name'
            )
            ->orderBy('store_prices.current_price', 'asc')
            ->get();
    }

    private function previousHistoricalLowest(int $variantId, ?string $beforeTime): ?float
    {
        $query = DB::table('price_history')
            ->where('variant_id', $variantId)
            ->where('price', '>', 0);

        if (!empty($beforeTime)) {
            $query->where('recorded_at', '<', $beforeTime);
        }

        $lowest = $query->min('price');

        return $lowest !== null ? (float) $lowest : null;
    }

    private function buildMailData($alert, $prices, $bestPrice, ?float $previousLowest): array
    {
        $cellphones = $prices->first(function ($price) {
            return str_contains(mb_strtolower($price->store_name, 'UTF-8'), 'cellphone');
        });

        $fpt = $prices->first(function ($price) {
            return str_contains(mb_strtolower($price->store_name, 'UTF-8'), 'fpt');
        });

        $variantName = trim(($alert->storage ?: '') . ' ' . ($alert->color ?: ''));
        $productName = trim($alert->product_name . ($variantName ? ' - ' . $variantName : ''));

        // Link nút mua ưu tiên affiliate_url. Nếu rỗng thì dùng product_url. Nếu vẫn rỗng thì dùng submitted_url.
        $buyUrl = $bestPrice->affiliate_url ?: $bestPrice->product_url ?: $alert->submitted_url;

        return [
            'alert_id' => $alert->alert_id,
            'user_name' => $alert->user_name ?: $alert->email,
            'email' => $alert->email,
            'product_name' => $productName,
            'base_product_name' => $alert->product_name,
            'storage' => $alert->storage,
            'color' => $alert->color,
            'target_price' => (float) $alert->target_price,
            'current_price' => (float) $bestPrice->current_price,
            'old_lowest_price' => $previousLowest,
            'best_shop' => $bestPrice->store_name,
            'cellphones_price' => $cellphones ? (float) $cellphones->current_price : null,
            'fpt_price' => $fpt ? (float) $fpt->current_price : null,
            'buy_url' => $buyUrl,
            'product_url' => $buyUrl,
            'manage_url' => url('/theo-doi-san-pham'),
            'shop_prices' => $prices->map(function ($price) {
                return [
                    'store_name' => $price->store_name,
                    'current_price' => (float) $price->current_price,
                    'product_url' => $price->affiliate_url ?: $price->product_url,
                ];
            })->values()->all(),
        ];
    }
}
