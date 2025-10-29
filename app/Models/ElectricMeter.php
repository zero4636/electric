<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ElectricMeter extends Model
{
    use HasFactory;

    /**
     * @property int $id
     */
    protected $fillable = [
        'meter_number',
        'meter_book_code',
        'meter_book_page',
        'organization_unit_id',
        'substation_id',
        'building_id',
        'floor_number',
        'installation_location',
        'meter_type',
        'hsn',
        'status',
    ];

    protected $casts = [
        'hsn' => 'decimal:2',
    ];

    /**
     * Validation rules
     */
    public static function rules($id = null): array
    {
        return [
            'meter_number' => ['required', 'string', 'max:255', 'unique:electric_meters,meter_number,' . $id],
            'organization_unit_id' => ['required', 'exists:organization_units,id'],
            'substation_id' => ['nullable', 'exists:substations,id'],
            'meter_type' => ['required', 'in:RESIDENTIAL,COMMERCIAL,INDUSTRIAL'],
            'hsn' => ['required', 'numeric', 'min:0.01', 'max:9999.99'],
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

    public function building()
    {
        return $this->belongsTo(Building::class);
    }

    public function meterReadings()
    {
        return $this->hasMany(MeterReading::class);
    }

    public function billDetails()
    {
        return $this->hasMany(BillDetail::class);
    }
}
