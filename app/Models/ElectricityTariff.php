<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property int $id
 */
class ElectricityTariff extends Model
{

    protected $fillable = [
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
}
