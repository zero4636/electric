<?php

namespace App\Imports;

use App\Models\OrganizationUnit;
use App\Models\ElectricMeter;
use App\Models\MeterReading;
use App\Models\Substation;
use App\Models\TariffType;
use App\Models\User;
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
    protected int $organizationsUpdated = 0;
    protected int $metersCreated = 0;
    protected int $metersUpdated = 0;
    protected int $readingsCreated = 0;

    public function collection(Collection $rows)
    {
        DB::transaction(function () use ($rows) {
            foreach ($rows as $rowIndex => $row) {
                try {
                    // Debug: Log row data
                    // \Log::info('Processing row ' . ($rowIndex + 1), ['row_data' => $row->toArray()]);
                    
                    // Chuẩn hóa truy xuất cột cho schema mới (import-thang-12-2025-merged.csv)
                    $g = function (array $keys, $default = null) use ($row) {
                        foreach ($keys as $k) {
                            if (isset($row[$k]) && $row[$k] !== null) {
                                $val = is_string($row[$k]) ? trim($row[$k]) : $row[$k];
                                if ($val !== '') return $val;
                            }
                        }
                        return $default;
                    };

                    $stt = $g(['stt']);

                    // 1) Resolve Creator (for permission assignment)
                    $creatorEmail = strtolower((string) $g(['email_nguoi_tao']));
                    $creatorName = $g(['nguoi_thuc_hien']);
                    $assignManager = null; // User to assign management
                    if ($creatorEmail && !preg_match('/admin|hệ thống/u', $creatorName ?? '')) {
                        $assignManager = User::firstOrCreate(
                            ['email' => $creatorEmail],
                            [
                                'name' => $creatorName ?: explode('@', $creatorEmail)[0],
                                'password' => bcrypt(str()->random(16)),
                                'role' => 'admin',
                            ]
                        );
                    }

                    // 2) Create/Update Unit (Đơn vị) if provided
                    $unitCode = $g(['ma_don_vi']);
                    $unitName = $g(['ten_don_vi']);
                    $unitType = $g(['loai_don_vi']) ?: 'Đơn vị tiêu thụ';
                    $unit = null;

                    if ($unitName || $unitCode) {
                        // Tránh auto-assign từ Observer; tự xử lý sau
                        $unit = OrganizationUnit::withoutEvents(function () use ($unitCode, $unitName, $unitType, $g) {
                            $attrs = [
                                'type' => 'UNIT',
                                'name' => $unitName ?: ($unitCode ?: 'Đơn vị không tên'),
                                'email' => $g(['email_don_vi']),
                                'address' => $g(['dia_chi_don_vi']),
                                'building' => null,
                                'contact_name' => $g(['dai_dien_don_vi']),
                                'contact_phone' => $g(['sdt_don_vi']),
                                'notes' => $g(['ghi_chu']),
                                'status' => 'ACTIVE',
                            ];
                            if ($unitCode) {
                                $model = OrganizationUnit::firstOrNew(['code' => $unitCode]);
                            } else {
                                $model = OrganizationUnit::firstOrNew(['name' => $attrs['name'], 'type' => 'UNIT']);
                            }
                            // Ghi đè thông tin (ưu tiên giá trị có trong file)
                            foreach ($attrs as $k => $v) {
                                if ($v !== null && $v !== '') {
                                    $model->{$k} = $v;
                                }
                            }
                            if (!$model->code && $unitCode) $model->code = $unitCode;
                            if (!$model->exists) {
                                $this->organizationsCreated++;
                            } else {
                                $this->organizationsUpdated++;
                            }
                            $model->save();
                            return $model;
                        });
                    }

                    // 3) Create/Update Consumer (Hộ tiêu thụ) if provided
                    $consumerCode = $g(['ma_ho_tieu_thu']);
                    $consumerName = $g(['ten_ho_tieu_thu']);
                    $consumer = null;
                    if ($consumerName || $consumerCode) {
                        $consumer = OrganizationUnit::withoutEvents(function () use ($consumerCode, $consumerName, $unit, $g) {
                            $attrs = [
                                'type' => 'CONSUMER',
                                'name' => $consumerName ?: ($consumerCode ?: 'Hộ tiêu thụ không tên'),
                                'parent_id' => $unit?->id,
                                'email' => $g(['email_ho']),
                                'address' => $g(['dia_chi_ho']),
                                'building' => null,
                                'contact_name' => $g(['dai_dien_ho']),
                                'contact_phone' => $g(['sdt_ho']),
                                'notes' => $g(['ghi_chu']),
                                'status' => 'ACTIVE',
                            ];
                            if ($consumerCode) {
                                $model = OrganizationUnit::firstOrNew(['code' => $consumerCode]);
                            } else {
                                $model = OrganizationUnit::firstOrNew(['name' => $attrs['name'], 'type' => 'CONSUMER', 'parent_id' => $attrs['parent_id']]);
                            }
                            foreach ($attrs as $k => $v) {
                                if ($v !== null && $v !== '') {
                                    $model->{$k} = $v;
                                }
                            }
                            if (!$model->code && $consumerCode) $model->code = $consumerCode;
                            if (!$model->exists) {
                                $this->organizationsCreated++;
                            } else {
                                $this->organizationsUpdated++;
                            }
                            $model->save();
                            return $model;
                        });
                    }

                    // 4) Assign management permission based on creator email
                    if ($assignManager) {
                        foreach ([$unit, $consumer] as $org) {
                            if ($org) {
                                if (!$assignManager->organizationUnits()->where('organization_unit_id', $org->id)->exists()) {
                                    $assignManager->organizationUnits()->attach($org->id);
                                }
                            }
                        }
                    }

                    // 5) Substation and Tariff Type
                    $substation = null;
                    $subCodeName = $g(['tram_bien_ap']);
                    if ($subCodeName) {
                        $substation = Substation::firstOrCreate(
                            ['code' => strtoupper($subCodeName)],
                            [
                                'name' => 'Trạm ' . $subCodeName,
                                'status' => 'ACTIVE',
                            ]
                        );
                    }
                    $phaseTypeStr = (string) $g(['loai_cong_to']);
                    $phaseType = str_contains($phaseTypeStr, '3') ? '3_PHASE' : '1_PHASE';
                    $tariffType = TariffType::where('code', 'SINH_HOAT')->first() ?: TariffType::first();

                    // 6) Meters (handle multiple meter numbers if provided)
                    $meters = array_filter(array_map('trim', preg_split('/[,;]/', (string) $g(['so_cong_to*', 'so_cong_to'], ''))));
                    $ownerOrg = $consumer ?: $unit; // If consumer empty, assign meter to unit
                    if ($ownerOrg && !empty($meters)) {
                        foreach ($meters as $meterNumber) {
                            if ($meterNumber === '') continue;
                            $meter = ElectricMeter::firstOrNew(['meter_number' => $meterNumber]);
                            $isNew = !$meter->exists;
                            $meter->organization_unit_id = $ownerOrg->id;
                            $meter->substation_id = $substation?->id;
                            $meter->tariff_type_id = $tariffType?->id;
                            $meter->installation_location = $g(['vi_tri_dat']);
                            $meter->phase_type = $phaseType;
                            $meter->hsn = (float) str_replace(',', '.', (string) $g(['he_so_nhan'], 1));
                            $meter->subsidized_kwh = (float) str_replace(',', '.', (string) $g(['bao_cap'], 0));
                            // meter_type: chọn COMMERCIAL nếu chỉ có Đơn vị, ngược lại RESIDENTIAL
                            $meter->meter_type = $consumer ? 'RESIDENTIAL' : 'COMMERCIAL';
                            $meter->status = 'ACTIVE';
                            $meter->save();
                            if ($isNew) {
                                $this->metersCreated++;
                            } else {
                                $this->metersUpdated++;
                            }

                            // 7) Reading for each meter
                            $readingVal = $g(['chi_so_moi']);
                            $monthStr = $g(['thang_ghi']);
                            if ($readingVal !== null && $readingVal !== '' && $monthStr) {
                                $readingDate = $this->parseReadingDate($monthStr);
                                $existingReading = MeterReading::where('electric_meter_id', $meter->id)
                                    ->whereDate('reading_date', $readingDate)
                                    ->first();
                                if (!$existingReading) {
                                    MeterReading::create([
                                        'electric_meter_id' => $meter->id,
                                        'reading_date' => $readingDate,
                                        'reading_value' => (float) str_replace(',', '', (string) $readingVal),
                                        'reader_name' => $creatorName ?: ($g(['nguoi_thuc_hien']) ?: 'Hệ thống'),
                                        'notes' => 'Import tổng hợp CSV',
                                    ]);
                                    $this->readingsCreated++;
                                }
                            }
                        }
                    }

                    $this->successCount++;
                    // \Log::info('Row processed successfully', ['row' => $rowIndex + 1, 'stt' => $stt]);
                } catch (\Exception $e) {
                    // \Log::error('Row processing failed', ['row' => $rowIndex + 1, 'error' => $e->getMessage()]);
                    $this->errors[] = "Dòng " . ($rowIndex + 1) . ": {$e->getMessage()}";
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
        // Tắt validate cứng để linh hoạt với file CSV đa nguồn; logic import đã tự xử lý thiếu dữ liệu
        return [];
    }

    public function customValidationMessages(): array
    {
        return [];
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
            'organizations_updated' => $this->organizationsUpdated,
            'meters_created' => $this->metersCreated,
            'meters_updated' => $this->metersUpdated,
            'readings_created' => $this->readingsCreated,
        ];
    }
}
