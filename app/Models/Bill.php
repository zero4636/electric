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
        'billing_month',
        'due_date',
        'total_amount',
        'payment_status',
    ];

    protected $casts = [
        'billing_month' => 'date',
        'due_date' => 'date',
        'total_amount' => 'decimal:2',
    ];

    /**
     * Validation rules
     */
    public static function rules($id = null): array
    {
        return [
            'organization_unit_id' => ['required', 'exists:organization_units,id'],
            'billing_month' => ['required', 'date'],
            'due_date' => ['required', 'date'],
            'total_amount' => ['required', 'numeric', 'min:0', 'max:999999999999.99'],
            'payment_status' => ['required', 'in:UNPAID,PARTIAL,PAID,OVERDUE'],
        ];
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
