<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TariffType;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('');
        $this->command->info('═══════════════════════════════════════════════');
        $this->command->info('  🌱 SEEDING DATABASE - HỆ THỐNG QUẢN LÝ ĐIỆN');
        $this->command->info('═══════════════════════════════════════════════');
        $this->command->info('');

        // Create tariff types first (required for billing)
        $this->createTariffTypes();
        
        // Create electricity tariffs
        $this->call(ElectricityTariffSeeder::class);
        
        // Create admin user
        $this->call(AdminSeeder::class);

        $this->command->info('');
        $this->command->info('═══════════════════════════════════════════════');
        $this->command->info('  ✅ HOÀN TẤT SEEDING DỮ LIỆU THÀNH CÔNG!');
        $this->command->info('═══════════════════════════════════════════════');
    }

    /**
     * Create tariff types for electricity billing
     */
    private function createTariffTypes(): void
    {
        $this->command->info('📋 Tạo các loại biểu giá điện...');
        
        $tariffTypes = [
            [
                'code' => 'SINH_HOAT',
                'name' => 'Sinh hoạt',
                'description' => 'Biểu giá điện sinh hoạt cho hộ gia đình',
            ],
            [
                'code' => 'SAN_XUAT',
                'name' => 'Sản xuất',
                'description' => 'Biểu giá điện sản xuất cho các cơ sở sản xuất',
            ],
            [
                'code' => 'KINH_DOANH',
                'name' => 'Kinh doanh',
                'description' => 'Biểu giá điện kinh doanh cho các cơ sở kinh doanh',
            ],
            [
                'code' => 'HANH_CHINH_SU_NGHIEP',
                'name' => 'Hành chính sự nghiệp',
                'description' => 'Biểu giá điện cho các cơ quan hành chính sự nghiệp',
            ],
            [
                'code' => 'CHIEU_SANG_CONG_CONG',
                'name' => 'Chiếu sáng công cộng',
                'description' => 'Biểu giá điện chiếu sáng công cộng',
            ],
        ];

        foreach ($tariffTypes as $tariffType) {
            TariffType::firstOrCreate(
                ['code' => $tariffType['code']],
                $tariffType
            );
        }

        $this->command->info('   ✅ Đã tạo ' . count($tariffTypes) . ' loại biểu giá điện');
    }
}
