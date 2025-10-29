<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property int $id
 */
class Bill extends Model
{
    use HasFactory;

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

    /**
     * Validation rules
     */
    public static function rules($id = null): array
    {
        return [
            'organization_unit_id' => ['required', 'exists:organization_units,id'],
            'billing_date' => ['required', 'date'],
            'total_amount' => ['required', 'numeric', 'min:0', 'max:999999999999.99'],
            'status' => ['required', 'in:PENDING,PAID,CANCELLED'],
        ];
    }

    /**
     * Calculate total from bill details
     */
    public function calculateTotal()
    {
        return $this->billDetails()->sum('amount');
    }

    /**
     * Update total amount from bill details
     */
    public function updateTotal()
    {
        $this->update(['total_amount' => $this->calculateTotal()]);
    }

    public function organizationUnit()
    {
        return $this->belongsTo(OrganizationUnit::class);
    }

    public function billDetails()
    {
        return $this->hasMany(BillDetail::class);
    }
}
