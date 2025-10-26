<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Substation extends Model
{
    //

    protected $fillable = [
        'name',
        'code',
        'location',
        'status',
    ];

    public function electricMeters()
    {
        return $this->hasMany(ElectricMeter::class);
    }
}
