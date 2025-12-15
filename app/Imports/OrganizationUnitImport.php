<?php

namespace App\Imports;

use App\Models\OrganizationUnit;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Validators\Failure;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;

class OrganizationUnitImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnError, SkipsOnFailure
{
    protected array $errors = [];
    protected int $successCount = 0;

    public function model(array $row)
    {
        // Tìm parent nếu có
        $parentId = null;
        if (!empty($row['ma_don_vi_cap_tren'])) {
            $parent = OrganizationUnit::where('code', $row['ma_don_vi_cap_tren'])->first();
            $parentId = $parent?->id;
        }

        $this->successCount++;

        return new OrganizationUnit([
            'code' => $row['ma'],
            'name' => $row['ten'],
            'type' => $row['loai'],
            'parent_id' => $parentId,
            'building' => $row['nha_toa'] ?? null,
            'address' => $row['dia_chi'] ?? null,
            'contact_name' => $row['nguoi_lien_he'] ?? null,
            'contact_phone' => $row['so_dien_thoai'] ?? null,
            'email' => $row['email'] ?? null,
            'status' => $row['trang_thai'] ?? 'ACTIVE',
            'notes' => $row['ghi_chu'] ?? null,
        ]);
    }

    public function rules(): array
    {
        return [
            'ma' => ['required', 'string', 'max:50', 'unique:organization_units,code'],
            'ten' => ['required', 'string', 'max:255'],
            'loai' => ['required', Rule::in(['UNIT', 'CONSUMER'])],
            'ma_don_vi_cap_tren' => ['nullable', 'exists:organization_units,code'],
            'nha_toa' => ['nullable', 'string', 'max:100'],
            'dia_chi' => ['nullable', 'string', 'max:255'],
            'nguoi_lien_he' => ['nullable', 'string', 'max:100'],
            'so_dien_thoai' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:100'],
            'trang_thai' => ['nullable', Rule::in(['ACTIVE', 'INACTIVE'])],
            'ghi_chu' => ['nullable', 'string'],
        ];
    }

    public function customValidationMessages(): array
    {
        return [
            'ma.required' => 'Mã đơn vị là bắt buộc',
            'ma.unique' => 'Mã đơn vị đã tồn tại',
            'ten.required' => 'Tên đơn vị là bắt buộc',
            'loai.required' => 'Loại đơn vị là bắt buộc',
            'loai.in' => 'Loại đơn vị phải là UNIT hoặc CONSUMER',
            'ma_don_vi_cap_tren.exists' => 'Mã đơn vị cấp trên không tồn tại',
            'email.email' => 'Email không đúng định dạng',
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
