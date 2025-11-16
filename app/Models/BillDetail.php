<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property int $id
 */
class BillDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'bill_id',
        'electric_meter_id',
        'consumption',
        'subsidized_applied',
        'chargeable_kwh',
        'price_per_kwh',
        'hsn',
        'amount',
    ];

    protected $casts = [
        'consumption' => 'decimal:2',
        'subsidized_applied' => 'decimal:2',
        'chargeable_kwh' => 'decimal:2',
        'price_per_kwh' => 'decimal:2',
        'hsn' => 'decimal:2',
        'amount' => 'decimal:2',
    ];

    /**
     * Validation rules
     */
    public static function rules($id = null): array
    {
        return [
            'bill_id' => ['required', 'exists:bills,id'],
            'electric_meter_id' => ['required', 'exists:electric_meters,id'],
            'consumption' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'subsidized_applied' => ['nullable', 'numeric', 'min:0'],
            'chargeable_kwh' => ['nullable', 'numeric', 'min:0'],
            'price_per_kwh' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'hsn' => ['required', 'numeric', 'min:0.01', 'max:9999.99'],
            'amount' => ['required', 'numeric', 'min:0', 'max:999999999999.99'],
        ];
    }

    public function bill()
    {
        return $this->belongsTo(Bill::class);
    }

    public function electricMeter()
    {
        return $this->belongsTo(ElectricMeter::class);
    }
}
