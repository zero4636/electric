<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class OptimizeDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'electric:optimize-db {--fresh : Drop all tables and re-migrate}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Optimize database with indexes and check performance';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔧 Bắt đầu tối ưu database...');
        $this->newLine();

        if ($this->option('fresh')) {
            if (!$this->confirm('⚠️  Bạn có chắc chắn muốn xóa toàn bộ database và migrate lại?', false)) {
                $this->info('Đã hủy.');
                return 0;
            }

            $this->info('📦 Đang migrate fresh...');
            Artisan::call('migrate:fresh', ['--force' => true]);
            $this->info('✅ Migrate fresh hoàn tất!');
            $this->newLine();
        } else {
            $this->info('📦 Đang chạy migrations...');
            Artisan::call('migrate', ['--force' => true]);
            $this->info('✅ Migrations hoàn tất!');
            $this->newLine();
        }

        // Check indexes
        $this->info('🔍 Kiểm tra indexes...');
        $this->checkIndexes();
        $this->newLine();

        // Display table statistics
        $this->info('📊 Thống kê bảng dữ liệu:');
        $this->displayTableStats();
        $this->newLine();

        // Optimize tables
        $this->info('⚡ Tối ưu các bảng...');
        $this->optimizeTables();
        $this->newLine();

        $this->info('✨ Hoàn tất tối ưu database!');

        return 0;
    }

    /**
     * Check indexes on tables
     */
    protected function checkIndexes()
    {
        $tables = [
            'organization_units',
            'substations',
            'electric_meters',
            'meter_readings',
            'electricity_tariffs',
            'bills',
            'bill_details',
        ];

        foreach ($tables as $table) {
            $indexes = DB::select("SHOW INDEX FROM {$table}");
            $indexCount = count(array_unique(array_column($indexes, 'Key_name')));
            
            $this->line("  • {$table}: {$indexCount} indexes");
        }
    }

    /**
     * Display table statistics
     */
    protected function displayTableStats()
    {
        $tables = [
            'organization_units' => 'Đơn vị tổ chức',
            'substations' => 'Trạm biến áp',
            'electric_meters' => 'Công tơ điện',
            'meter_readings' => 'Chỉ số công tơ',
            'electricity_tariffs' => 'Biểu giá điện',
            'bills' => 'Hóa đơn',
            'bill_details' => 'Chi tiết hóa đơn',
        ];

        foreach ($tables as $table => $label) {
            try {
                $count = DB::table($table)->count();
                $this->line("  • {$label} ({$table}): {$count} bản ghi");
            } catch (\Exception $e) {
                $this->error("  • {$label} ({$table}): Lỗi - {$e->getMessage()}");
            }
        }
    }

    /**
     * Optimize database tables
     */
    protected function optimizeTables()
    {
        $tables = [
            'organization_units',
            'substations',
            'electric_meters',
            'meter_readings',
            'electricity_tariffs',
            'bills',
            'bill_details',
        ];

        foreach ($tables as $table) {
            try {
                DB::statement("OPTIMIZE TABLE {$table}");
                $this->line("  • Đã tối ưu bảng: {$table}");
            } catch (\Exception $e) {
                $this->warn("  • Không thể tối ưu bảng {$table}: {$e->getMessage()}");
            }
        }
    }
}
