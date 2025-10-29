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
        'start_reading_id',
        'end_reading_id',
        'consumption',
        'price_per_kwh',
        'hsn',
        'subsidized_amount',
        'amount',
    ];

    protected $casts = [
        'consumption' => 'decimal:2',
        'price_per_kwh' => 'decimal:2',
        'hsn' => 'decimal:2',
        'subsidized_amount' => 'decimal:2',
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
            'start_reading_id' => ['nullable', 'exists:meter_readings,id'],
            'end_reading_id' => ['nullable', 'exists:meter_readings,id'],
            'consumption' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'price_per_kwh' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'hsn' => ['required', 'numeric', 'min:0.01', 'max:9999.99'],
            'subsidized_amount' => ['nullable', 'numeric', 'min:0', 'max:999999999999.99'],
            'amount' => ['required', 'numeric', 'min:0', 'max:999999999999.99'],
        ];
    }

    /**
     * Calculate amount automatically
     */
    public function calculateAmount()
    {
        return $this->consumption * $this->price_per_kwh * $this->hsn;
    }

    /**
     * Boot method to auto-calculate amount
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($billDetail) {
            if ($billDetail->consumption && $billDetail->price_per_kwh && $billDetail->hsn) {
                $billDetail->amount = $billDetail->consumption * $billDetail->price_per_kwh * $billDetail->hsn;
            }
        });

        // Update bill total after saving or deleting bill detail
        static::saved(function ($billDetail) {
            $billDetail->bill->updateTotal();
        });

        static::deleted(function ($billDetail) {
            $billDetail->bill->updateTotal();
        });
    }

    public function bill()
    {
        return $this->belongsTo(Bill::class);
    }

    public function electricMeter()
    {
        return $this->belongsTo(ElectricMeter::class);
    }

    public function startReading()
    {
        return $this->belongsTo(MeterReading::class, 'start_reading_id');
    }

    public function endReading()
    {
        return $this->belongsTo(MeterReading::class, 'end_reading_id');
    }
}
