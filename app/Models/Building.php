<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Building extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'address',
        'substation_id',
        'total_floors',
        'status',
    ];

    protected $casts = [
        'total_floors' => 'integer',
    ];

    /**
     * Validation rules
     */
    public static function rules($id = null): array
    {
        return [
            'code' => ['required', 'string', 'max:50', 'unique:buildings,code,' . $id],
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'substation_id' => ['nullable', 'exists:substations,id'],
            'total_floors' => ['nullable', 'integer', 'min:1', 'max:100'],
            'status' => ['required', 'in:ACTIVE,INACTIVE'],
        ];
    }

    public function substation()
    {
        return $this->belongsTo(Substation::class);
    }

    public function electricMeters()
    {
        return $this->hasMany(ElectricMeter::class);
    }
}
