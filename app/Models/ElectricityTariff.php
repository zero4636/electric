<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property int $id
 */
class ElectricityTariff extends Model
{
    use HasFactory;

    protected $fillable = [
        'tariff_type_id',
        'tariff_type',
        'price_per_kwh',
        'effective_from',
        'effective_to',
    ];

    protected $casts = [
        'effective_from' => 'date',
        'effective_to' => 'date',
        'price_per_kwh' => 'decimal:2',
    ];

    /**
     * Validation rules
     */
    public static function rules($id = null): array
    {
        return [
            'tariff_type_id' => ['nullable', 'exists:tariff_types,id'],
            'tariff_type' => ['nullable', 'in:RESIDENTIAL,COMMERCIAL,INDUSTRIAL'],
            'price_per_kwh' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'effective_from' => ['required', 'date'],
            'effective_to' => ['nullable', 'date', 'after:effective_from'],
        ];
    }

    /**
     * Relationship: Electricity Tariff belongs to Tariff Type
     */
    public function tariffType()
    {
        return $this->belongsTo(TariffType::class);
    }

    /**
     * Get active tariff for a specific type
     */
    public static function getActiveTariff(string $type, $date = null)
    {
        $date = $date ?? now();
        
        return static::where('tariff_type', $type)
            ->where('effective_from', '<=', $date)
            ->where(function ($query) use ($date) {
                $query->whereNull('effective_to')
                    ->orWhere('effective_to', '>=', $date);
            })
            ->orderBy('effective_from', 'desc')
            ->first();
    }
}
