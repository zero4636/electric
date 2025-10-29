<?php

namespace App\Console\Commands;

use App\Services\BillingService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateMonthlyBills extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'electric:generate-bills 
                            {--year= : Năm (mặc định: năm hiện tại)}
                            {--month= : Tháng (mặc định: tháng trước)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tạo hóa đơn điện hàng tháng cho tất cả đơn vị';

    /**
     * Execute the console command.
     */
    public function handle(BillingService $billingService)
    {
        $year = $this->option('year') ?? now()->year;
        $month = $this->option('month') ?? now()->subMonth()->month;

        // Adjust year if month is December of previous year
        if ($month == 12 && !$this->option('month')) {
            $year = now()->subMonth()->year;
        }

        $this->info("📅 Tạo hóa đơn cho tháng {$month}/{$year}");
        $this->newLine();

        if (!$this->confirm('Bạn có chắc chắn muốn tạo hóa đơn?', true)) {
            $this->info('Đã hủy.');
            return 0;
        }

        $this->info('🔄 Đang xử lý...');
        $this->newLine();

        $results = $billingService->generateMonthlyBills((int) $year, (int) $month);

        $successCount = 0;
        $failCount = 0;
        $totalAmount = 0;

        foreach ($results as $result) {
            if ($result['success']) {
                $successCount++;
                $totalAmount += $result['amount'];
                $this->info("  ✅ Đơn vị #{$result['organization_unit_id']}: " . number_format($result['amount'], 0, ',', '.') . " VNĐ");
            } else {
                $failCount++;
                $this->error("  ❌ Đơn vị #{$result['organization_unit_id']}: {$result['error']}");
            }
        }

        $this->newLine();
        $this->info("📊 Tổng kết:");
        $this->line("  • Thành công: {$successCount} hóa đơn");
        $this->line("  • Thất bại: {$failCount} hóa đơn");
        $this->line("  • Tổng tiền: " . number_format($totalAmount, 0, ',', '.') . " VNĐ");
        $this->newLine();

        $this->info('✨ Hoàn tất!');

        return 0;
    }
}
