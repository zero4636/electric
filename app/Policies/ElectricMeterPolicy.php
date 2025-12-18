<?php

namespace App\Policies;

use App\Models\ElectricMeter;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ElectricMeterPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ElectricMeter $electricMeter): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->canManageOrganization($electricMeter->organizationUnit);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ElectricMeter $electricMeter): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->canManageOrganization($electricMeter->organizationUnit);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ElectricMeter $electricMeter): bool
    {
        return $this->update($user, $electricMeter);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ElectricMeter $electricMeter): bool
    {
        return $this->delete($user, $electricMeter);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ElectricMeter $electricMeter): bool
    {
        return $this->delete($user, $electricMeter);
    }
}