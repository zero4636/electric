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

        // 4. Create substations (khu vực)
        $this->command->info('📍 Tạo trạm biến áp / Khu vực...');
        $substations = $this->createSubstations();

        // 5. Create organization units
        $this->command->info('🏛️ Tạo đơn vị tổ chức...');
        $organizations = $this->createOrganizations();

        // 6. Create electric meters
        $this->command->info('⚡ Tạo công tơ điện...');
        $meters = $this->createElectricMeters($organizations, $substations);

        // 8. Create meter readings
        $this->command->info('📊 Tạo chỉ số công tơ...');
        $this->createMeterReadings($meters);

        // 9. Create bills with details
        $this->command->info('📄 Tạo hóa đơn...');
        $this->createBills($organizations, $meters);

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

        /**
         * Helper: Đọc file CSV và trả về array of records
         */
        private function readCSV(string $filename): array
        {
            $filePath = database_path("csv/{$filename}");
        
            if (!file_exists($filePath)) {
                $this->command->warn("⚠️  File không tồn tại: {$filename}");
                return [];
            }

            $records = [];
            $handle = fopen($filePath, 'r');
        
            // Đọc header
            $headers = fgetcsv($handle);
        
            // Đọc dữ liệu
            while (($data = fgetcsv($handle)) !== false) {
                $record = [];
                foreach ($headers as $index => $header) {
                    $record[$header] = $data[$index] ?? null;
                }
                $records[] = $record;
            }
        
            fclose($handle);
        
            return $records;
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
                'color' => 'success',
                'icon' => 'heroicon-o-home',
                'status' => 'ACTIVE',
                'sort_order' => 1,
            ],
            [
                'code' => 'COMMERCIAL',
                'name' => 'Thương mại',
                'description' => 'Biểu giá điện dành cho văn phòng, cửa hàng, dịch vụ',
                'color' => 'primary',
                'icon' => 'heroicon-o-building-office',
                'status' => 'ACTIVE',
                'sort_order' => 2,
            ],
            [
                'code' => 'INDUSTRIAL',
                'name' => 'Công nghiệp',
                'description' => 'Biểu giá điện dành cho nhà máy, xưởng sản xuất',
                'color' => 'warning',
                'icon' => 'heroicon-o-cog',
                'status' => 'ACTIVE',
                'sort_order' => 3,
            ],
        ];

        foreach ($types as $typeData) {
            TariffType::firstOrCreate(
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

    private function createSubstations(): array
    {
        $data = [
            ['code' => 'B1', 'name' => 'Trạm B1', 'location' => 'Khu vực B1'],
            ['code' => 'ĐLK', 'name' => 'Trạm Điện Lực Khu', 'location' => 'Khu ĐLK'],
            ['code' => 'KTX', 'name' => 'Trạm KTX', 'location' => 'Ký túc xá'],
            ['code' => 'TVĐT', 'name' => 'Trạm TVĐT', 'location' => 'Trung tâm Viễn thông'],
            ['code' => 'BK1', 'name' => 'Trạm BK1', 'location' => 'Khu BK1'],
            ['code' => 'BK2', 'name' => 'Trạm BK2', 'location' => 'Khu BK2'],
            ['code' => 'BK3B', 'name' => 'Trạm BK3B', 'location' => 'Khu BK3B'],
            ['code' => 'SVĐ', 'name' => 'Trạm Sân vận động', 'location' => 'Sân vận động'],
            ['code' => 'THCK', 'name' => 'Trạm THCS-THPT', 'location' => 'Trường THCS-THPT'],
            ['code' => 'VVL', 'name' => 'Trạm VVL', 'location' => 'Vũ Văn Lâm'],
            ['code' => 'ĐCĐT', 'name' => 'Trạm ĐCĐT', 'location' => 'Đào tạo Liên tục'],
        ];

        $substations = [];
        foreach ($data as $item) {
            $substations[$item['code']] = Substation::firstOrCreate(
                ['code' => $item['code']],
                array_merge($item, ['status' => 'ACTIVE'])
            );
        }

        $this->command->info('   ✓ Đã tạo ' . count($substations) . ' trạm biến áp');
        return $substations;
    }

    private function createOrganizations(): array
    {
        $organizations = [
            [
                'name' => 'Công ty TNHH Chuyển giao Công nghệ Bách Khoa',
                'code' => 'CGCN_BK',
                'type' => 'ORGANIZATION',
                'email' => 'contact@cgcnbk.com',
                'address' => 'Đại học Bách Khoa Hà Nội',
                'contact_name' => 'Nguyễn Ngọc Tuấn',
                'contact_phone' => '0973253788',
                'status' => 'ACTIVE',
            ],
            [
                'name' => 'BK Holding',
                'code' => 'BK_HOLDING',
                'type' => 'ORGANIZATION',
                'email' => 'info@bkholding.vn',
                'address' => 'Đại học Bách Khoa Hà Nội',
                'contact_name' => 'Nguyễn Trung Dũng',
                'contact_phone' => '0906123357',
                'status' => 'ACTIVE',
            ],
            [
                'name' => 'Công ty CP XD Hồng Hà',
                'code' => 'HONG_HA',
                'type' => 'ORGANIZATION',
                'email' => 'hongha@construction.vn',
                'address' => 'Nhà A15 TQB',
                'contact_name' => 'Chị Hoa',
                'contact_phone' => '0903251444',
                'status' => 'ACTIVE',
            ],
            [
                'name' => 'Công ty CP Công nghệ cao GENE Việt',
                'code' => 'GENE_VN',
                'type' => 'ORGANIZATION',
                'email' => 'info@geneviet.com',
                'address' => 'Tầng 11 nhà B1',
                'contact_name' => 'Lương Thị Minh Ngọc',
                'contact_phone' => '0977295439',
                'status' => 'ACTIVE',
            ],
            [
                'name' => 'Ngân hàng TMCP Đầu tư và Phát triển chi nhánh Hà Thành',
                'code' => 'BIDV_HT',
                'type' => 'ORGANIZATION',
                'email' => 'hathanh@bidv.com.vn',
                'address' => 'Tầng 1 - Nhà A17 TQB',
                'contact_name' => 'Nguyễn Thị Đông',
                'contact_phone' => '0915344727',
                'status' => 'ACTIVE',
            ],
            [
                'name' => 'Hợp đồng kinh tế (HĐKT)',
                'code' => 'HDKT',
                'type' => 'ORGANIZATION',
                'email' => 'hdkt@bk.edu.vn',
                'address' => 'Đại học Bách Khoa Hà Nội',
                'status' => 'ACTIVE',
            ],
        ];

        $result = [];
        foreach ($organizations as $org) {
            $result[$org['code']] = OrganizationUnit::firstOrCreate(
                ['code' => $org['code']],
                $org
            );
        }

        // Create consumers
        $hdkt = $result['HDKT'];
        $consumers = [
            ['name' => 'Kiot Trịnh Thị Thu Trang', 'code' => 'KIOT_TRANG', 'phone' => '0359933033'],
            ['name' => 'Quán ăn uống giải khát c.Ly', 'code' => 'QUAN_LY', 'phone' => '0945656446'],
            ['name' => 'CLB Bi-a Phú Kỳ', 'code' => 'BIDA_PHU_KY', 'phone' => '0912894948'],
            ['name' => 'Siêu thị Nam Phong', 'code' => 'NAM_PHONG', 'phone' => '0944289288'],
        ];

        foreach ($consumers as $consumer) {
            $result[$consumer['code']] = OrganizationUnit::firstOrCreate(
                ['code' => $consumer['code']],
                [
                    'name' => $consumer['name'],
                    'type' => 'CONSUMER',
                    'parent_id' => $hdkt->id,
                    'contact_phone' => $consumer['phone'],
                    'status' => 'ACTIVE',
                ]
            );
        }

        $this->command->info('   ✓ Đã tạo ' . count($result) . ' đơn vị tổ chức');
        return $result;
    }

    private function createElectricMeters(array $organizations, array $substations): array
    {
        // Get tariff types for FK references
        $commercial = TariffType::where('code', 'COMMERCIAL')->first();

        $metersData = [
            ['number' => '3564', 'org' => 'CGCN_BK', 'sub' => 'ĐLK', 'tariff_type_id' => $commercial->id, 'loc' => 'Nhà D5 - Tầng 5'],
            ['number' => '8306', 'org' => 'CGCN_BK', 'sub' => 'ĐLK', 'tariff_type_id' => $commercial->id, 'loc' => 'Nhà D5 - Tầng 5'],
            ['number' => '9497', 'org' => 'BK_HOLDING', 'sub' => 'B1', 'tariff_type_id' => $commercial->id, 'loc' => 'Nhà A17 - Tủ ĐN 2'],
            ['number' => '1478', 'org' => 'GENE_VN', 'sub' => 'B1', 'tariff_type_id' => $commercial->id, 'loc' => 'Tòa B1 - KTĐ T11'],
            ['number' => '9278', 'org' => 'BIDV_HT', 'sub' => 'B1', 'tariff_type_id' => $commercial->id, 'loc' => 'Nhà A17 - Tủ ĐN1'],
            ['number' => '3206', 'org' => 'KIOT_TRANG', 'sub' => 'SVĐ', 'tariff_type_id' => $commercial->id, 'loc' => 'Sân vận động - KĐ B'],
            ['number' => '5089', 'org' => 'QUAN_LY', 'sub' => 'B1', 'tariff_type_id' => $commercial->id, 'loc' => 'Số 10 TQB - Tủ tổng T1'],
            ['number' => '1738', 'org' => 'BIDA_PHU_KY', 'sub' => 'B1', 'tariff_type_id' => $commercial->id, 'loc' => 'Số 10 TQB - KTĐ T1'],
            ['number' => '3448', 'org' => 'NAM_PHONG', 'sub' => 'B1', 'tariff_type_id' => $commercial->id, 'loc' => 'Số 10 TQB - Tầng 1 TTPV'],
        ];

        $meters = [];
        foreach ($metersData as $data) {
            $meters[$data['number']] = ElectricMeter::firstOrCreate(
                ['meter_number' => $data['number']],
                [
                    'organization_unit_id' => $organizations[$data['org']]->id,
                    'substation_id' => $substations[$data['sub']]->id,
                    'tariff_type_id' => $data['tariff_type_id'],
                    'meter_type' => 'COMMERCIAL', // Legacy
                    'installation_location' => $data['loc'],
                    'hsn' => 1,
                    'subsidized_kwh' => 0, // No subsidy for commercial meters by default
                    'status' => 'ACTIVE',
                ]
            );
        }

        $this->command->info('   ✓ Đã tạo ' . count($meters) . ' công tơ điện');
        return $meters;
    }

    private function createMeterReadings(array $meters): void
    {
        $baseDate = Carbon::create(2025, 9, 1);

        $readingsData = [
            '3206' => [48551, 48551],
            '5089' => [436148, 439092],
            '1738' => [3511, 7342],
            '3448' => [480771, 485846],
            '1478' => [301434, 304740],
            '9278' => [113401, 115119],
            '3564' => [48000, 48551],
            '8306' => [47500, 48198],
            '9497' => [451798, 453883],
        ];

        $count = 0;
        foreach ($meters as $meter) {
            if (isset($readingsData[$meter->meter_number])) {
                [$oldValue, $newValue] = $readingsData[$meter->meter_number];

                MeterReading::firstOrCreate(
                    ['electric_meter_id' => $meter->id, 'reading_date' => $baseDate],
                    ['reading_value' => $oldValue]
                );

                MeterReading::firstOrCreate(
                    ['electric_meter_id' => $meter->id, 'reading_date' => $baseDate->copy()->addMonth()],
                    ['reading_value' => $newValue]
                );

                $count++;
            }
        }

        $this->command->info('   ✓ Đã tạo ' . ($count * 2) . ' chỉ số công tơ');
    }

    private function createBills(array $organizations, array $meters): void
    {
        $count = 0;
        // Use new tariff lookup by tariff_type_id
        $tariffs = ElectricityTariff::with('tariffType')->get()->keyBy('tariff_type_id');

        foreach ($organizations as $org) {
            if ($org->electricMeters()->count() === 0) {
                continue;
            }

            // Create 2 bills per organization
            for ($i = 0; $i < 2; $i++) {
                $billDate = Carbon::now()->subMonths($i)->startOfMonth();
                $status = $i === 0 ? 'PENDING' : 'PAID';

                $bill = Bill::create([
                    'organization_unit_id' => $org->id,
                    'billing_date' => $billDate->toDateString(),
                    'total_amount' => 0,
                    'status' => $status,
                ]);

                $total = 0;
                foreach ($org->electricMeters as $meter) {
                    $readings = $meter->meterReadings()->orderBy('reading_date', 'desc')->take(2)->get();
                    
                    if ($readings->count() < 2) {
                        continue;
                    }

                    $latest = $readings->first();
                    $prev = $readings->last();
                    $rawConsumption = max(0, ($latest->reading_value - $prev->reading_value) * $meter->hsn);

                    // Apply subsidized allowance
                    $subsidizedApplied = min($rawConsumption, $meter->subsidized_kwh ?? 0);
                    $chargeableKwh = max(0, $rawConsumption - $subsidizedApplied);

                    $tariff = $tariffs->get($meter->tariff_type_id);
                    $price = $tariff ? $tariff->price_per_kwh : 2500;

                    $amount = $chargeableKwh * $price;

                    BillDetail::create([
                        'bill_id' => $bill->id,
                        'electric_meter_id' => $meter->id,
                        'consumption' => $rawConsumption,
                        'subsidized_applied' => $subsidizedApplied,
                        'chargeable_kwh' => $chargeableKwh,
                        'price_per_kwh' => $price,
                        'hsn' => $meter->hsn,
                        'amount' => $amount,
                    ]);

                    $total += $amount;
                }

                $bill->update(['total_amount' => $total]);
                $count++;
            }
        }

        $this->command->info('   ✓ Đã tạo ' . $count . ' hóa đơn');
    }
}
