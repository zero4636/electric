<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ElectricMeter extends Model
{
    //

    /**
     * @property int $id
     */
    protected $fillable = [
        'meter_number',
        'organization_unit_id',
        'substation_id',
        'meter_type',
        'hsn',
        'status',
    ];

    public function organizationUnit()
    {
        return $this->belongsTo(OrganizationUnit::class);
    }

    public function substation()
    {
        return $this->belongsTo(Substation::class);
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
