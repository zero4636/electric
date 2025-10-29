<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ValidationHelper
{
    /**
     * Validate data against model rules
     *
     * @param string $modelClass
     * @param array $data
     * @param int|null $id
     * @return array
     * @throws ValidationException
     */
    public static function validateModel(string $modelClass, array $data, ?int $id = null): array
    {
        if (!method_exists($modelClass, 'rules')) {
            throw new \Exception("Model {$modelClass} does not have rules method");
        }

        $rules = $modelClass::rules($id);
        
        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }

    /**
     * Common validation rules
     */
    public static function commonRules(): array
    {
        return [
            'phone' => ['nullable', 'regex:/^([0-9\s\-\+\(\)]*)$/', 'min:10', 'max:15'],
            'email' => ['nullable', 'email', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:10', 'regex:/^[0-9]*$/'],
        ];
    }

    /**
     * Get Vietnamese error messages
     */
    public static function messages(): array
    {
        return [
            'required' => ':attribute là bắt buộc.',
            'string' => ':attribute phải là chuỗi ký tự.',
            'numeric' => ':attribute phải là số.',
            'email' => ':attribute phải là địa chỉ email hợp lệ.',
            'min.string' => ':attribute phải có ít nhất :min ký tự.',
            'min.numeric' => ':attribute phải lớn hơn hoặc bằng :min.',
            'max.string' => ':attribute không được vượt quá :max ký tự.',
            'max.numeric' => ':attribute không được vượt quá :max.',
            'unique' => ':attribute đã tồn tại trong hệ thống.',
            'exists' => ':attribute không tồn tại.',
            'in' => ':attribute không hợp lệ.',
            'date' => ':attribute phải là ngày hợp lệ.',
            'before_or_equal' => ':attribute phải trước hoặc bằng :date.',
            'after' => ':attribute phải sau :date.',
            'regex' => 'Định dạng :attribute không hợp lệ.',
        ];
    }

    /**
     * Get Vietnamese attribute names
     */
    public static function attributes(): array
    {
        return [
            'meter_number' => 'Số công tơ',
            'organization_unit_id' => 'Đơn vị tổ chức',
            'substation_id' => 'Trạm biến áp',
            'meter_type' => 'Loại công tơ',
            'hsn' => 'Hệ số nhân',
            'status' => 'Trạng thái',
            'name' => 'Tên',
            'code' => 'Mã',
            'location' => 'Vị trí',
            'tariff_type' => 'Loại biểu giá',
            'price_per_kwh' => 'Giá điện',
            'effective_from' => 'Hiệu lực từ',
            'effective_to' => 'Hiệu lực đến',
            'reading_date' => 'Ngày ghi',
            'reading_value' => 'Chỉ số',
            'billing_date' => 'Ngày lập hóa đơn',
            'total_amount' => 'Tổng tiền',
            'consumption' => 'Sản lượng',
            'amount' => 'Thành tiền',
            'bill_id' => 'Hóa đơn',
            'electric_meter_id' => 'Công tơ điện',
        ];
    }
}
