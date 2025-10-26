<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property int $id
 */
class BillDetail extends Model
{

    protected $fillable = [
        'bill_id',
        'electric_meter_id',
        'consumption',
        'price_per_kwh',
        'hsn',
        'amount',
    ];

    protected $casts = [
        'consumption' => 'decimal:2',
        'price_per_kwh' => 'decimal:2',
        'hsn' => 'decimal:2',
        'amount' => 'decimal:2',
    ];

    public function bill()
    {
        return $this->belongsTo(Bill::class);
    }

    public function electricMeter()
    {
        return $this->belongsTo(ElectricMeter::class);
    }
}
