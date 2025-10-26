<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property int $id
 */
class Bill extends Model
{

    protected $fillable = [
        'organization_unit_id',
        'billing_date',
        'total_amount',
        'status',
    ];

    protected $casts = [
        'billing_date' => 'date',
        'total_amount' => 'decimal:2',
    ];

    public function organizationUnit()
    {
        return $this->belongsTo(OrganizationUnit::class);
    }

    public function billDetails()
    {
        return $this->hasMany(BillDetail::class);
    }
}
