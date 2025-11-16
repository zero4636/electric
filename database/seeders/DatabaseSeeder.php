<?php

namespace Database\Seeders;

use App\Models\Bill;
use App\Models\BillDetail;
use App\Models\ElectricityTariff;
use App\Models\ElectricMeter;
use App\Models\MeterReading;
use App\Models\OrganizationUnit;
use App\Models\Substation;
use App\Models\TariffType;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

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

        // 1. Create admin user
        $this->command->info('👤 Tạo tài khoản Admin...');
        $this->createAdminUser();

        // 2. Create tariff types
        $this->command->info('📂 Tạo loại biểu giá...');
        $this->createTariffTypes();

        // 3. Create electricity tariffs
        $this->command->info('💰 Tạo biểu giá điện...');
        $this->createTariffs();

        // 4. Import data from CSV
        $this->command->info('');
        $this->command->info('📁 Import dữ liệu từ data.csv...');
        $this->call(CsvDataImporter::class);

        $this->command->info('');
        $this->command->info('═══════════════════════════════════════════════');
        $this->command->info('  ✅ HOÀN TẤT SEEDING DỮ LIỆU THÀNH CÔNG!');
        $this->command->info('═══════════════════════════════════════════════');
        $this->command->info('');
        $this->command->info('📋 Thông tin đăng nhập:');
        $this->command->info('   Email: admin@example.com');
        $this->command->info('   Password: password');
        $this->command->info('');
    }

    private function createAdminUser(): void
    {
        $email = 'admin@example.com';

        if (User::where('email', $email)->exists()) {
            $this->command->info('   ✓ Tài khoản admin đã tồn tại');
            return;
        }

        User::create([
            'name' => 'Admin',
            'email' => $email,
            'password' => Hash::make('password'),
        ]);

        $this->command->info('   ✓ Đã tạo: admin@example.com (password: password)');
    }

    private function createTariffTypes(): void
    {
        $types = [
            [
                'code' => 'RESIDENTIAL',
                'name' => 'Dân cư',
                'description' => 'Biểu giá điện dành cho hộ gia đình, khu nhà ở',
                'color' => '#22c55e', // green-500 (was 'success')
                'icon' => 'heroicon-o-home',
                'status' => 'ACTIVE',
                'sort_order' => 1,
            ],
            [
                'code' => 'COMMERCIAL',
                'name' => 'Thương mại',
                'description' => 'Biểu giá điện dành cho văn phòng, cửa hàng, dịch vụ',
                'color' => '#3b82f6', // blue-500 (was 'primary')
                'icon' => 'heroicon-o-building-office',
                'status' => 'ACTIVE',
                'sort_order' => 2,
            ],
            [
                'code' => 'INDUSTRIAL',
                'name' => 'Công nghiệp',
                'description' => 'Biểu giá điện dành cho nhà máy, xưởng sản xuất',
                'color' => '#f59e0b', // amber-500 (was 'warning')
                'icon' => 'heroicon-o-cog',
                'status' => 'ACTIVE',
                'sort_order' => 3,
            ],
        ];

        foreach ($types as $typeData) {
            TariffType::updateOrCreate(
                ['code' => $typeData['code']],
                $typeData
            );
        }

        $this->command->info('   ✓ Đã tạo ' . count($types) . ' loại biểu giá');
    }

    private function createTariffs(): void
    {
        // Get tariff type IDs for proper FK references
        $residential = TariffType::where('code', 'RESIDENTIAL')->first();
        $commercial = TariffType::where('code', 'COMMERCIAL')->first();
        $industrial = TariffType::where('code', 'INDUSTRIAL')->first();

        $tariffs = [
            [
                'tariff_type_id' => $residential->id,
                'tariff_type' => 'RESIDENTIAL', // Legacy
                'price_per_kwh' => 2500,
                'effective_from' => '2024-01-01',
                'effective_to' => null,
            ],
            [
                'tariff_type_id' => $commercial->id,
                'tariff_type' => 'COMMERCIAL', // Legacy
                'price_per_kwh' => 4169,
                'effective_from' => '2024-01-01',
                'effective_to' => null,
            ],
            [
                'tariff_type_id' => $industrial->id,
                'tariff_type' => 'INDUSTRIAL', // Legacy
                'price_per_kwh' => 3500,
                'effective_from' => '2024-01-01',
                'effective_to' => null,
            ],
        ];

        foreach ($tariffs as $tariff) {
            ElectricityTariff::firstOrCreate(
                [
                    'tariff_type_id' => $tariff['tariff_type_id'],
                    'effective_from' => $tariff['effective_from']
                ],
                $tariff
            );
        }

        $this->command->info('   ✓ Đã tạo ' . count($tariffs) . ' biểu giá điện');
    }
}
