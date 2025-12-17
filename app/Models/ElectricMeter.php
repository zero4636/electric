<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class ElectricMeter extends Model
{
    use HasFactory, LogsActivity;

    /**
     * @property int $id
     */
    protected $fillable = [
        'meter_number',
        'organization_unit_id',
        'substation_id',
        'tariff_type_id',
        'installation_location',
        'meter_type', // Legacy - will be removed
        'phase_type',
        'hsn',
        'subsidized_kwh',
        'status',
    ];

    protected $casts = [
        'hsn' => 'decimal:2',
        'subsidized_kwh' => 'decimal:2',
    ];

    /**
     * Validation rules
     */
    public static function rules($id = null): array
    {
        return [
            'meter_number' => ['required', 'string', 'max:255', 'unique:electric_meters,meter_number,' . $id],
            'organization_unit_id' => ['required', 'exists:organization_units,id'],
            'substation_id' => ['required', 'exists:substations,id'],
            'tariff_type_id' => ['required', 'exists:tariff_types,id'],
            'meter_type' => ['nullable', 'in:RESIDENTIAL,COMMERCIAL,INDUSTRIAL'], // Legacy - optional
            'hsn' => ['required', 'numeric', 'min:0.01', 'max:9999.99'],
            'subsidized_kwh' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'status' => ['required', 'in:ACTIVE,INACTIVE'],
        ];
    }

    public function organizationUnit()
    {
        return $this->belongsTo(OrganizationUnit::class);
    }

    public function substation()
    {
        return $this->belongsTo(Substation::class);
    }

    public function tariffType()
    {
        return $this->belongsTo(TariffType::class);
    }

    public function meterReadings()
    {
        return $this->hasMany(MeterReading::class);
    }

    public function billDetails()
    {
        return $this->hasMany(BillDetail::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'meter_number',
                'organization_unit_id',
                'substation_id',
                'tariff_type_id',
                'installation_location',
                'phase_type',
                'hsn',
                'subsidized_kwh',
                'status',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => match ($eventName) {
                'created' => 'Tạo mới công tơ điện',
                'updated' => 'Cập nhật thông tin công tơ điện',
                'deleted' => 'Xóa công tơ điện',
                default => "Thao tác {$eventName} trên công tơ điện",
            });
    }
}
