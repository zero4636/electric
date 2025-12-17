<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

/**
 * @property int $id
 */
class ElectricityTariff extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'tariff_type_id',
        'tariff_type', // Legacy - will be removed
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
            'tariff_type_id' => ['required', 'exists:tariff_types,id'],
            'tariff_type' => ['nullable', 'in:RESIDENTIAL,COMMERCIAL,INDUSTRIAL'], // Legacy - optional
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
     * Get active tariff for a specific tariff type ID
     */
    public static function getActiveTariff(?int $tariffTypeId, $date = null)
    {
        if (!$tariffTypeId) {
            return null;
        }
        
        $date = $date ?? now();
        
        return static::where('tariff_type_id', $tariffTypeId)
            ->where('effective_from', '<=', $date)
            ->where(function ($query) use ($date) {
                $query->whereNull('effective_to')
                    ->orWhere('effective_to', '>=', $date);
            })
            ->orderBy('effective_from', 'desc')
            ->first();
    }
    
    /**
     * Legacy method - Get active tariff by enum type
     * @deprecated Use getActiveTariff($tariffTypeId) instead
     */
    public static function getActiveTariffByType(string $type, $date = null)
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

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'tariff_type_id',
                'price_per_kwh',
                'effective_from',
                'effective_to',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => match ($eventName) {
                'created' => 'Tạo mới biểu giá điện',
                'updated' => 'Cập nhật biểu giá điện',
                'deleted' => 'Xóa biểu giá điện',
                default => "Thao tác {$eventName} trên biểu giá điện",
            });
    }
}
