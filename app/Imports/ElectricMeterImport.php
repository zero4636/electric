<?php

namespace App\Imports;

use App\Models\ElectricMeter;
use App\Models\OrganizationUnit;
use App\Models\Substation;
use App\Models\TariffType;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Validators\Failure;
use Illuminate\Validation\Rule;

class ElectricMeterImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnError, SkipsOnFailure
{
    protected array $errors = [];
    protected int $successCount = 0;

    public function model(array $row)
    {
        // Tìm organization unit
        $organization = OrganizationUnit::where('code', $row['ma_ho_tieu_thu'])->first();
        if (!$organization) {
            throw new \Exception("Không tìm thấy hộ tiêu thụ với mã: {$row['ma_ho_tieu_thu']}");
        }

        // Tìm substation nếu có
        $substationId = null;
        if (!empty($row['ma_tram_bien_ap'])) {
            $substation = Substation::where('code', $row['ma_tram_bien_ap'])->first();
            $substationId = $substation?->id;
        }

        // Tìm tariff type
        $tariffType = TariffType::where('code', $row['loai_hinh'])->first();
        if (!$tariffType) {
            throw new \Exception("Không tìm thấy loại hình với mã: {$row['loai_hinh']}");
        }

        $this->successCount++;

        return new ElectricMeter([
            'meter_number' => $row['ma_cong_to'],
            'organization_unit_id' => $organization->id,
            'substation_id' => $substationId,
            'tariff_type_id' => $tariffType->id,
            'phase_type' => $row['loai_cong_to'],
            'installation_location' => $row['vi_tri_dat'] ?? null,
            'hsn' => $row['hsn'] ?? 1.0,
            'subsidized_kwh' => $row['bao_cap_kwh'] ?? 0,
            'status' => $row['trang_thai'] ?? 'ACTIVE',
            'notes' => $row['ghi_chu'] ?? null,
        ]);
    }

    public function rules(): array
    {
        return [
            'ma_cong_to' => ['required', 'string', 'max:50', 'unique:electric_meters,meter_number'],
            'ma_ho_tieu_thu' => ['required', 'exists:organization_units,code'],
            'loai_cong_to' => ['required', Rule::in(['1_PHASE', '3_PHASE'])],
            'loai_hinh' => ['required', 'exists:tariff_types,code'],
            'ma_tram_bien_ap' => ['nullable', 'exists:substations,code'],
            'vi_tri_dat' => ['nullable', 'string', 'max:255'],
            'hsn' => ['nullable', 'numeric', 'min:0'],
            'bao_cap_kwh' => ['nullable', 'numeric', 'min:0'],
            'trang_thai' => ['nullable', Rule::in(['ACTIVE', 'INACTIVE'])],
            'ghi_chu' => ['nullable', 'string'],
        ];
    }

    public function customValidationMessages(): array
    {
        return [
            'ma_cong_to.required' => 'Mã công tơ là bắt buộc',
            'ma_cong_to.unique' => 'Mã công tơ đã tồn tại',
            'ma_ho_tieu_thu.required' => 'Mã hộ tiêu thụ là bắt buộc',
            'ma_ho_tieu_thu.exists' => 'Mã hộ tiêu thụ không tồn tại',
            'loai_cong_to.required' => 'Loại công tơ là bắt buộc',
            'loai_cong_to.in' => 'Loại công tơ phải là 1_PHASE hoặc 3_PHASE',
            'loai_hinh.required' => 'Loại hình là bắt buộc',
            'loai_hinh.exists' => 'Loại hình không tồn tại',
            'ma_tram_bien_ap.exists' => 'Mã trạm biến áp không tồn tại',
            'hsn.numeric' => 'HSN phải là số',
            'bao_cap_kwh.numeric' => 'Bao cấp kWh phải là số',
            'trang_thai.in' => 'Trạng thái phải là ACTIVE hoặc INACTIVE',
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
}
