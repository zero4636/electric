<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Substation extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'location',
        'status',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Validation rules
     */
    public static function rules($id = null): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:255', 'unique:substations,code,' . $id],
            'location' => ['nullable', 'string', 'max:500'],
            'status' => ['required', 'in:ACTIVE,INACTIVE'],
        ];
    }

    public function buildings()
    {
        return $this->hasMany(Building::class);
    }

    public function electricMeters()
    {
        return $this->hasMany(ElectricMeter::class);
    }
}
