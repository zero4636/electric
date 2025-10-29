<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TariffType extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'description',
        'color',
        'icon',
        'status',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Validation rules
     */
    public static function rules($id = null): array
    {
        return [
            'code' => ['required', 'string', 'max:50', 'unique:tariff_types,code,' . $id, 'regex:/^[A-Z_]+$/'],
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'color' => ['required', 'string', 'max:20', 'in:primary,success,warning,danger,info'],
            'icon' => ['nullable', 'string', 'max:50'],
            'status' => ['required', 'in:ACTIVE,INACTIVE'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ];
    }

    /**
     * Relationship: Tariff Type has many Electricity Tariffs
     */
    public function electricityTariffs()
    {
        return $this->hasMany(ElectricityTariff::class);
    }

    /**
     * Scope: Only active tariff types
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'ACTIVE');
    }

    /**
     * Scope: Ordered by sort_order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }
}
