<?php

namespace App\Imports;

use App\Models\Substation;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Validators\Failure;
use Illuminate\Validation\Rule;

class SubstationImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnError, SkipsOnFailure
{
    protected array $errors = [];
    protected int $successCount = 0;

    public function model(array $row)
    {
        $this->successCount++;

        return new Substation([
            'code' => $row['ma'],
            'name' => $row['ten'],
            'location' => $row['vi_tri'] ?? null,
            'capacity' => $row['cong_suat'] ?? null,
            'voltage_level' => $row['cap_dien_ap'] ?? null,
            'installation_date' => !empty($row['ngay_lap_dat']) ? \Carbon\Carbon::parse($row['ngay_lap_dat']) : null,
            'status' => $row['trang_thai'] ?? 'ACTIVE',
            'notes' => $row['ghi_chu'] ?? null,
        ]);
    }

    public function rules(): array
    {
        return [
            'ma' => ['required', 'string', 'max:50', 'unique:substations,code'],
            'ten' => ['required', 'string', 'max:255'],
            'vi_tri' => ['nullable', 'string', 'max:255'],
            'cong_suat' => ['nullable', 'numeric', 'min:0'],
            'cap_dien_ap' => ['nullable', 'string', 'max:50'],
            'ngay_lap_dat' => ['nullable', 'date'],
            'trang_thai' => ['nullable', Rule::in(['ACTIVE', 'INACTIVE', 'MAINTENANCE'])],
            'ghi_chu' => ['nullable', 'string'],
        ];
    }

    public function customValidationMessages(): array
    {
        return [
            'ma.required' => 'Mã trạm biến áp là bắt buộc',
            'ma.unique' => 'Mã trạm biến áp đã tồn tại',
            'ten.required' => 'Tên trạm biến áp là bắt buộc',
            'cong_suat.numeric' => 'Công suất phải là số',
            'cong_suat.min' => 'Công suất phải >= 0',
            'ngay_lap_dat.date' => 'Ngày lắp đặt không đúng định dạng',
            'trang_thai.in' => 'Trạng thái phải là ACTIVE, INACTIVE hoặc MAINTENANCE',
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
