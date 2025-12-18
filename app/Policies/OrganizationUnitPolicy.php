<?php

namespace App\Policies;

use App\Models\OrganizationUnit;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class OrganizationUnitPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Tất cả admins đều có thể view danh sách (sẽ được filter bởi getEloquentQuery)
        return $user->isAdmin() || $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, OrganizationUnit $organizationUnit): bool
    {
        // Super admin có thể xem tất cả
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Admin chỉ có thể xem organizations được assign
        return $user->canManageOrganization($organizationUnit);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Tất cả admins đều có thể tạo mới
        return $user->isAdmin() || $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, OrganizationUnit $organizationUnit): bool
    {
        // Super admin có thể sửa tất cả
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Admin chỉ có thể sửa organizations được assign
        return $user->canManageOrganization($organizationUnit);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, OrganizationUnit $organizationUnit): bool
    {
        // Super admin có thể xóa tất cả
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Admin chỉ có thể xóa organizations được assign
        return $user->canManageOrganization($organizationUnit);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, OrganizationUnit $organizationUnit): bool
    {
        return $this->delete($user, $organizationUnit);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, OrganizationUnit $organizationUnit): bool
    {
        return $this->delete($user, $organizationUnit);
    }
}