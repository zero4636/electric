<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ElectricityTariff;
use App\Models\TariffType;
use Carbon\Carbon;

class ElectricityTariffSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('⚡ Tạo biểu giá điện mẫu...');
        
        $tariffs = $this->getTariffData();
        
        foreach ($tariffs as $tariff) {
            ElectricityTariff::firstOrCreate(
                [
                    'tariff_type_id' => $tariff['tariff_type_id'],
                    'effective_from' => $tariff['effective_from'],
                ],
                $tariff
            );
        }
        
        $this->command->info('   ✅ Đã tạo ' . count($tariffs) . ' biểu giá điện mẫu');
    }
    
    /**
     * Get tariff data to seed
     */
    private function getTariffData(): array
    {
        $now = Carbon::now();
        $firstOfMonth = $now->copy()->firstOfMonth();
        
        // Get tariff type IDs
        $sinhHoat = TariffType::where('code', 'SINH_HOAT')->first();
        $sanXuat = TariffType::where('code', 'SAN_XUAT')->first();
        $kinhDoanh = TariffType::where('code', 'KINH_DOANH')->first();
        $hanhChinh = TariffType::where('code', 'HANH_CHINH_SU_NGHIEP')->first();
        $chieuSang = TariffType::where('code', 'CHIEU_SANG_CONG_CONG')->first();
        
        return [
            // Sinh hoạt - Bậc thang như quy định EVN
            [
                'tariff_type_id' => $sinhHoat?->id,
                'tariff_type' => 'RESIDENTIAL', // Legacy field
                'price_per_kwh' => 1728, // VND/kWh cho bậc 1 (0-50 kWh)
                'effective_from' => $firstOfMonth->copy()->subMonths(6),
                'effective_to' => null, // Hiện tại đang có hiệu lực
            ],
            
            // Sản xuất
            [
                'tariff_type_id' => $sanXuat?->id,
                'tariff_type' => 'INDUSTRIAL', // Legacy field
                'price_per_kwh' => 1720, // VND/kWh
                'effective_from' => $firstOfMonth->copy()->subMonths(6),
                'effective_to' => null,
            ],
            
            // Kinh doanh
            [
                'tariff_type_id' => $kinhDoanh?->id,
                'tariff_type' => 'COMMERCIAL', // Legacy field
                'price_per_kwh' => 2729, // VND/kWh
                'effective_from' => $firstOfMonth->copy()->subMonths(6),
                'effective_to' => null,
            ],
            
            // Hành chính sự nghiệp
            [
                'tariff_type_id' => $hanhChinh?->id,
                'tariff_type' => 'COMMERCIAL', // Legacy field
                'price_per_kwh' => 2729, // VND/kWh
                'effective_from' => $firstOfMonth->copy()->subMonths(6),
                'effective_to' => null,
            ],
            
            // Chiếu sáng công cộng
            [
                'tariff_type_id' => $chieuSang?->id,
                'tariff_type' => 'COMMERCIAL', // Legacy field
                'price_per_kwh' => 2243, // VND/kWh
                'effective_from' => $firstOfMonth->copy()->subMonths(6),
                'effective_to' => null,
            ],
            
            // Historical tariffs (đã hết hiệu lực) - để demo
            [
                'tariff_type_id' => $sinhHoat?->id,
                'tariff_type' => 'RESIDENTIAL', // Legacy field
                'price_per_kwh' => 1678, // Giá cũ
                'effective_from' => $firstOfMonth->copy()->subYear(),
                'effective_to' => $firstOfMonth->copy()->subMonths(6)->subDay(),
            ],
            
            [
                'tariff_type_id' => $kinhDoanh?->id,
                'tariff_type' => 'COMMERCIAL', // Legacy field
                'price_per_kwh' => 2650, // Giá cũ
                'effective_from' => $firstOfMonth->copy()->subYear(),
                'effective_to' => $firstOfMonth->copy()->subMonths(6)->subDay(),
            ],
        ];
    }
}