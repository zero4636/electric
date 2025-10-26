<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OrganizationUnit extends Model
{
    use HasFactory;

    protected $fillable = [
        'parent_id',
        'name',
        'code',
        'type',
        'tax_code',
        'email',
        'address',
        'contact_name',
        'contact_phone',
        'notes',
        'status',
        'password_hash',
    ];

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id', 'id');
    }

    public function ancestors()
    {
        return $this->parent()->with('ancestors');
    }

    public function descendants()
    {
        return $this->children()->with('descendants');
    }

    public function electricMeters()
    {
        return $this->hasMany(ElectricMeter::class);
    }

    public function bills()
    {
        return $this->hasMany(Bill::class);
    }

    // Depth (level) in the hierarchy (0 = root)
    public function getDepthAttribute(): int
    {
        $depth = 0;
        $parent = $this->parent;
        while ($parent) {
            $depth++;
            $parent = $parent->parent;
        }
        return $depth;
    }

    // Breadcrumb string of ancestor names (Parent > ... > Self)
    public function getBreadcrumbAttribute(): string
    {
        $names = [];
        $node = $this;
        // collect ancestors
        $ancestors = [];
        $parent = $this->parent;
        while ($parent) {
            array_unshift($ancestors, $parent->name);
            $parent = $parent->parent;
        }
        $names = array_merge($ancestors, [$this->name]);
        return implode(' > ', $names);
    }
}
