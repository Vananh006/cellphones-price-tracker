<?php

namespace App\Console\Commands;

use App\Services\PriceAlertNotificationService;
use Illuminate\Console\Command;

class CheckPriceAlertsCommand extends Command
{
    protected $signature = 'alerts:check-prices {--dry-run : Chỉ kiểm tra, không gửi email và không đổi trạng thái}';

    protected $description = 'Kiểm tra price_alerts và gửi email khi giá đạt mục tiêu hoặc thấp nhất lịch sử.';

    public function handle(PriceAlertNotificationService $service): int
    {
        $result = $service->checkAndSend((bool) $this->option('dry-run'));

        $this->info('Kết quả kiểm tra cảnh báo giá:');
        $this->line('Đã kiểm tra: ' . $result['checked']);
        $this->line('Gửi đạt giá mong muốn: ' . $result['sent_target']);
        $this->line('Gửi giá thấp nhất lịch sử: ' . $result['sent_lowest']);
        $this->line('Bỏ qua: ' . $result['skipped']);
        $this->line('Lỗi: ' . $result['failed']);

        foreach ($result['samples'] as $sample) {
            $this->line('- #' . $sample['alert_id'] . ' | ' . $sample['type'] . ' | ' . $sample['email'] . ' | ' . $sample['product']);
        }

        foreach ($result['errors'] as $error) {
            $this->error('- Alert #' . ($error['alert_id'] ?? '?') . ': ' . $error['message']);
        }

        return $result['failed'] > 0 ? self::FAILURE : self::SUCCESS;
    }
}
