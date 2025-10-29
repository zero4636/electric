<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property int $id
 */
class MeterReading extends Model
{
    use HasFactory;

    protected $fillable = [
        'electric_meter_id',
        'reading_date',
        'reading_value',
        'reader_name',
        'notes',
    ];

    protected $casts = [
        'reading_date' => 'date',
        'reading_value' => 'decimal:2',
    ];

    /**
     * Validation rules
     */
    public static function rules($id = null): array
    {
        return [
            'electric_meter_id' => ['required', 'exists:electric_meters,id'],
            'reading_date' => ['required', 'date', 'before_or_equal:today'],
            'reading_value' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'reader_name' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Calculate consumption from previous reading (with HSN from meter)
     */
    public function getConsumption()
    {
        $previousReading = static::where('electric_meter_id', $this->electric_meter_id)
            ->where('reading_date', '<', $this->reading_date)
            ->orderBy('reading_date', 'desc')
            ->first();

        if (!$previousReading) {
            return 0;
        }

        $rawConsumption = $this->reading_value - $previousReading->reading_value;
        $hsn = $this->electricMeter->hsn ?? 1;
        
        return $rawConsumption * $hsn;
    }

    public function electricMeter()
    {
        return $this->belongsTo(ElectricMeter::class);
    }
}
