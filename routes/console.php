<?php

use App\Services\PriceCompareService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('affiliate:generate {--id=} {--limit=200}', function () {
    $service = app(PriceCompareService::class);

    $result = $service->generateAffiliateLinks(
        $this->option('id') ? (int) $this->option('id') : null,
        (int) $this->option('limit')
    );

    $this->info($result['message'] ?? 'Hoàn tất.');
    $this->line('Updated: '.($result['updated'] ?? 0));
    $this->line('Skipped: '.($result['skipped'] ?? 0));

    if (! empty($result['samples'])) {
        $this->table(['id', 'product_url', 'affiliate_url'], $result['samples']);
    }
})->purpose('Tạo link affiliate_url cho các link sản phẩm đã có trong store_prices.');

Artisan::command('products:crawl {--id=} {--limit=5}', function () {
    $service = app(PriceCompareService::class);

    $results = $service->crawlStorePrices(
        $this->option('id') ? (int) $this->option('id') : null,
        (int) $this->option('limit')
    );

    $rows = collect($results)->map(function (array $row): array {
        return [
            'id' => $row['id'] ?? '',
            'ok' => ! empty($row['ok']) ? 'OK' : 'FAIL',
            'store' => $row['store'] ?? '',
            'variant' => $row['variant'] ?? '',
            'price' => isset($row['current_price']) ? number_format((float) $row['current_price'], 0, ',', '.').'đ' : '',
            'error' => $row['error'] ?? '',
        ];
    })->all();

    $this->table(['id', 'status', 'store', 'variant', 'price', 'error'], $rows);
    $this->info('Crawl hoàn tất: '.count($results).' link.');
})->purpose('Crawl giá từ product_url, cập nhật store_prices, affiliate_url và price_history.');

Artisan::command('alerts:check-prices', function () {
    $service = app(PriceCompareService::class);
    $sent = $service->checkPendingAlerts();

    $this->info('Đã gửi '.$sent.' email cảnh báo giá đạt mức mong muốn.');
})->purpose('Kiểm tra price_alerts đang pending và gửi email khi current_price <= target_price.');

Artisan::command('prices:run-cycle {--limit=5}', function () {
    $service = app(PriceCompareService::class);
    $result = $service->runCycle((int) $this->option('limit'));

    $this->info('Một vòng tự động đã chạy xong.');
    $this->line('Crawled: '.$result['crawled']);
    $this->line('Mail sent: '.$result['mail_sent']);
})->purpose('Chạy đủ 1 vòng: crawl giá -> tạo affiliate_url -> kiểm tra gửi email.');

// Bật schedule tự động bằng cách thêm vào .env:
// CRAWLER_AUTO_ENABLED=true
// CRAWLER_CYCLE_LIMIT=5
if ((bool) env('CRAWLER_AUTO_ENABLED', false)) {
    Schedule::command('prices:run-cycle --limit='.(int) env('CRAWLER_CYCLE_LIMIT', 5))
        ->everyMinute()
        ->withoutOverlapping();
}
