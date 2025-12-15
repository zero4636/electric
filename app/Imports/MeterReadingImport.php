<?php

namespace App\Imports;

use App\Models\MeterReading;
use App\Models\ElectricMeter;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Validators\Failure;
use Carbon\Carbon;

class MeterReadingImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnError, SkipsOnFailure
{
    protected array $errors = [];
    protected int $successCount = 0;

    public function model(array $row)
    {
        // Tìm electric meter
        $meter = ElectricMeter::where('meter_number', $row['ma_cong_to'])->first();
        if (!$meter) {
            throw new \Exception("Không tìm thấy công tơ với mã: {$row['ma_cong_to']}");
        }

        // Parse ngày ghi
        $readingDate = null;
        if (!empty($row['ngay_ghi'])) {
            try {
                $readingDate = Carbon::parse($row['ngay_ghi']);
            } catch (\Exception $e) {
                throw new \Exception("Ngày ghi không đúng định dạng: {$row['ngay_ghi']}");
            }
        }

        $this->successCount++;

        return new MeterReading([
            'electric_meter_id' => $meter->id,
            'reading_date' => $readingDate,
            'reading_value' => $row['chi_so'],
            'reader_name' => $row['nguoi_ghi'] ?? null,
            'notes' => $row['ghi_chu'] ?? null,
        ]);
    }

    public function rules(): array
    {
        return [
            'ma_cong_to' => ['required', 'exists:electric_meters,meter_number'],
            'ngay_ghi' => ['required', 'date'],
            'chi_so' => ['required', 'numeric', 'min:0'],
            'nguoi_ghi' => ['nullable', 'string', 'max:100'],
            'ghi_chu' => ['nullable', 'string'],
        ];
    }

    public function customValidationMessages(): array
    {
        return [
            'ma_cong_to.required' => 'Mã công tơ là bắt buộc',
            'ma_cong_to.exists' => 'Mã công tơ không tồn tại',
            'ngay_ghi.required' => 'Ngày ghi là bắt buộc',
            'ngay_ghi.date' => 'Ngày ghi không đúng định dạng',
            'chi_so.required' => 'Chỉ số là bắt buộc',
            'chi_so.numeric' => 'Chỉ số phải là số',
            'chi_so.min' => 'Chỉ số phải >= 0',
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
