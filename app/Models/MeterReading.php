<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property int $id
 */
class MeterReading extends Model
{

    protected $fillable = [
        'electric_meter_id',
        'reading_date',
        'reading_value',
        'hsn',
    ];

    protected $casts = [
        'reading_date' => 'date',
        'reading_value' => 'decimal:2',
        'hsn' => 'decimal:2',
    ];

    public function electricMeter()
    {
        return $this->belongsTo(ElectricMeter::class);
    }
}
