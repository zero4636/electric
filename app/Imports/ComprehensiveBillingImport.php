<?php

namespace App\Imports;

use App\Models\OrganizationUnit;
use App\Models\ElectricMeter;
use App\Models\MeterReading;
use App\Models\Substation;
use App\Models\TariffType;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Validators\Failure;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ComprehensiveBillingImport implements ToCollection, WithHeadingRow, WithValidation, SkipsOnError, SkipsOnFailure
{
    protected array $errors = [];
    protected int $successCount = 0;
    protected int $organizationsCreated = 0;
    protected int $metersCreated = 0;
    protected int $readingsCreated = 0;

    public function collection(Collection $rows)
    {
        DB::transaction(function () use ($rows) {
            foreach ($rows as $row) {
                try {
                    // 1. Tạo/Cập nhật Đơn vị chủ quản (nếu có)
                    $parentOrg = null;
                    if (!empty($row['don_vi_chu_quan'])) {
                        $parentOrg = OrganizationUnit::firstOrCreate(
                            ['name' => trim($row['don_vi_chu_quan'])],
                            [
                                'code' => 'ORG-' . strtoupper(substr(md5($row['don_vi_chu_quan']), 0, 8)),
                                'type' => 'UNIT',
                                'status' => 'ACTIVE',
                            ]
                        );
                        if ($parentOrg->wasRecentlyCreated) {
                            $this->organizationsCreated++;
                        }
                    }

                    // 2. Tạo/Cập nhật Hộ tiêu thụ
                    $consumerCode = !empty($row['ma_ho_tieu_thu']) 
                        ? trim($row['ma_ho_tieu_thu'])
                        : 'CONS-' . strtoupper(substr(md5($row['ho_tieu_thu_dien']), 0, 8));

                    $consumer = OrganizationUnit::firstOrCreate(
                        ['code' => $consumerCode],
                        [
                            'name' => trim($row['ho_tieu_thu_dien']),
                            'type' => 'CONSUMER',
                            'parent_id' => $parentOrg?->id,
                            'building' => $row['nha_toa'] ?? null,
                            'address' => $row['dia_chi'] ?? null,
                            'contact_name' => $row['dai_dien_ho'] ?? null,
                            'contact_phone' => $row['dien_thoai_nguoi_dai_dien'] ?? null,
                            'email' => $row['email'] ?? null,
                            'status' => 'ACTIVE',
                        ]
                    );
                    
                    if ($consumer->wasRecentlyCreated) {
                        $this->organizationsCreated++;
                    }

                    // 3. Tìm/Tạo Trạm biến áp
                    $substation = null;
                    if (!empty($row['tram_bien_ap'])) {
                        $substation = Substation::firstOrCreate(
                            ['code' => strtoupper(trim($row['tram_bien_ap']))],
                            [
                                'name' => 'Trạm ' . trim($row['tram_bien_ap']),
                                'status' => 'ACTIVE',
                            ]
                        );
                    }

                    // 4. Tìm Loại hình (Tariff Type)
                    $tariffType = null;
                    if (!empty($row['loai_cong_to'])) {
                        $phaseType = str_contains($row['loai_cong_to'], '3') ? '3_PHASE' : '1_PHASE';
                        // Tìm tariff type phù hợp, mặc định là sinh hoạt
                        $tariffType = TariffType::where('code', 'SINH_HOAT')->first();
                        if (!$tariffType) {
                            $tariffType = TariffType::first(); // Fallback
                        }
                    }

                    // 5. Tạo/Cập nhật Công tơ
                    $meterNumber = trim($row['so_cong_to']);
                    $phaseType = str_contains($row['loai_cong_to'] ?? '', '3') ? '3_PHASE' : '1_PHASE';
                    
                    $meter = ElectricMeter::firstOrCreate(
                        ['meter_number' => $meterNumber],
                        [
                            'organization_unit_id' => $consumer->id,
                            'substation_id' => $substation?->id,
                            'tariff_type_id' => $tariffType?->id,
                            'installation_location' => $row['vi_tri_dat'] ?? null,
                            'phase_type' => $phaseType,
                            'hsn' => floatval($row['he_so_nhan'] ?? 1),
                            'subsidized_kwh' => floatval($row['bao_cap'] ?? 0),
                            'status' => 'ACTIVE',
                        ]
                    );
                    
                    if ($meter->wasRecentlyCreated) {
                        $this->metersCreated++;
                    }

                    // 6. Tạo chỉ số công tơ (nếu có)
                    if (!empty($row['chi_so_moi']) && !empty($row['thang_ghi'])) {
                        $readingDate = $this->parseReadingDate($row['thang_ghi']);
                        
                        // Kiểm tra xem đã có chỉ số này chưa
                        $existingReading = MeterReading::where('electric_meter_id', $meter->id)
                            ->whereDate('reading_date', $readingDate)
                            ->first();

                        if (!$existingReading) {
                            MeterReading::create([
                                'electric_meter_id' => $meter->id,
                                'reading_date' => $readingDate,
                                'reading_value' => floatval(str_replace(',', '', $row['chi_so_moi'])),
                                'reader_name' => $row['nguoi_thuc_hien'] ?? 'Hệ thống',
                                'notes' => 'Import từ file CSV tổng hợp',
                            ]);
                            $this->readingsCreated++;
                        }
                    }

                    $this->successCount++;
                } catch (\Exception $e) {
                    $this->errors[] = "Dòng {$row['stt']}: {$e->getMessage()}";
                }
            }
        });
    }

    protected function parseReadingDate($monthString): Carbon
    {
        // Xử lý format "tháng 10 năm 2025" hoặc "10/2025" hoặc "2025-10"
        if (preg_match('/tháng\s*(\d+)\s*năm\s*(\d+)/i', $monthString, $matches)) {
            $month = $matches[1];
            $year = $matches[2];
            return Carbon::createFromDate($year, $month, 1)->endOfMonth();
        }
        
        if (preg_match('/(\d+)\/(\d+)/', $monthString, $matches)) {
            return Carbon::createFromDate($matches[2], $matches[1], 1)->endOfMonth();
        }

        // Mặc định là cuối tháng hiện tại
        return Carbon::now()->endOfMonth();
    }

    public function rules(): array
    {
        return [
            'ho_tieu_thu_dien' => ['required', 'string', 'max:255'],
            'so_cong_to' => ['required', 'string', 'max:50'],
            'loai_cong_to' => ['nullable', 'string'],
            'he_so_nhan' => ['nullable', 'numeric', 'min:0'],
            'chi_so_moi' => ['nullable', 'string'],
            'bao_cap' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    public function customValidationMessages(): array
    {
        return [
            'ho_tieu_thu_dien.required' => 'Tên hộ tiêu thụ là bắt buộc',
            'so_cong_to.required' => 'Số công tơ là bắt buộc',
        ];
    }

    public function onError(\Throwable $e)
    {
        $this->errors[] = $e->getMessage();
    }

    public function onFailure(Failure ...$failures)
    {
        foreach ($failures as $failure) {
            $this->errors[] = "Dòng {$failure->row()}: " . implode(', ', $failure->errors());
        }
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getSuccessCount(): int
    {
        return $this->successCount;
    }

    public function getStats(): array
    {
        return [
            'success_count' => $this->successCount,
            'organizations_created' => $this->organizationsCreated,
            'meters_created' => $this->metersCreated,
            'readings_created' => $this->readingsCreated,
        ];
    }
}
