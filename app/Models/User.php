<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'created_by',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get organization units that this user can manage
     */
    public function organizationUnits(): BelongsToMany
    {
        return $this->belongsToMany(OrganizationUnit::class, 'user_organization_units')
            ->withPivot('is_primary')
            ->withTimestamps();
    }

    /**
     * Get primary organization unit for this user
     */
    public function primaryOrganizationUnit()
    {
        return $this->organizationUnits()->wherePivot('is_primary', true)->first();
    }

    /**
     * Check if user is Super Admin
     */
    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    /**
     * Check if user is Admin (sub-admin)
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Get the admin who created this user
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get users created by this admin
     */
    public function createdUsers()
    {
        return $this->hasMany(User::class, 'created_by');
    }

    /**
     * Check if user can manage organization
     * - Super Admin: can manage all
     * - Admin: only assigned organizations + children
     */
    public function canManageOrganization(OrganizationUnit $organization): bool
    {
        // Super Admin can manage everything
        if ($this->isSuperAdmin()) {
            return true;
        }

        // Admin: check if organization is assigned or is child of assigned org
        $assignedOrgIds = $this->organizationUnits()->pluck('organization_units.id')->toArray();
        
        // Direct assignment
        if (in_array($organization->id, $assignedOrgIds)) {
            return true;
        }

        // Check if org is child of assigned org (recursive parent check)
        $currentOrg = $organization;
        while ($currentOrg->parent_id) {
            if (in_array($currentOrg->parent_id, $assignedOrgIds)) {
                return true;
            }
            $currentOrg = $currentOrg->parent;
            if (!$currentOrg) break;
        }

        return false;
    }

    /**
     * Get all organizations this user can manage (including children)
     */
    public function getManagedOrganizationIds(): array
    {
        if ($this->isSuperAdmin()) {
            return OrganizationUnit::pluck('id')->toArray();
        }

        $assignedIds = $this->organizationUnits()->pluck('organization_units.id')->toArray();
        
        // Get all children of assigned orgs (recursive)
        $allIds = $assignedIds;
        foreach ($assignedIds as $orgId) {
            $children = OrganizationUnit::where('parent_id', $orgId)->pluck('id')->toArray();
            $allIds = array_merge($allIds, $children);
            
            // Get grandchildren (simple 2-level for now, can be made recursive)
            foreach ($children as $childId) {
                $grandchildren = OrganizationUnit::where('parent_id', $childId)->pluck('id')->toArray();
                $allIds = array_merge($allIds, $grandchildren);
            }
        }

        return array_unique($allIds);
    }

    /**
     * Check if user can manage another user
     * - Super Admin: can manage all admins
     * - Admin: cannot manage other admins
     */
    public function canManageUser(User $user): bool
    {
        // Super Admin can manage everyone except themselves
        if ($this->isSuperAdmin()) {
            return $this->id !== $user->id;
        }

        // Regular admin cannot manage other users
        return false;
    }

    /**
     * Check if user can update their own profile
     */
    public function canUpdateOwnProfile(): bool
    {
        return true; // All users can update their own profile
    }
}

