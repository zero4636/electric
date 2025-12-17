<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

/**
 * @property int $id
 */
class MeterReading extends Model
{
    use HasFactory, LogsActivity;

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

        // If no previous reading, assume previous value was 0
        $previousValue = $previousReading ? $previousReading->reading_value : 0;
        
        $rawConsumption = $this->reading_value - $previousValue;
        $hsn = $this->electricMeter->hsn ?? 1;
        
        return $rawConsumption * $hsn;
    }

    public function electricMeter()
    {
        return $this->belongsTo(ElectricMeter::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'electric_meter_id',
                'reading_date',
                'reading_value',
                'reader_name',
                'notes',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => match ($eventName) {
                'created' => 'Ghi nhận chỉ số công tơ',
                'updated' => 'Cập nhật chỉ số công tơ',
                'deleted' => 'Xóa chỉ số công tơ',
                default => "Thao tác {$eventName} trên chỉ số công tơ",
            });
    }
}
