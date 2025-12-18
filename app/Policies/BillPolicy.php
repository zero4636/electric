<?php

namespace App\Policies;

use App\Models\Bill;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class BillPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isSuperAdmin();
    }

    public function view(User $user, Bill $bill): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->canManageOrganization($bill->organizationUnit);
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isSuperAdmin();
    }

    public function update(User $user, Bill $bill): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->canManageOrganization($bill->organizationUnit);
    }

    public function delete(User $user, Bill $bill): bool
    {
        return $this->update($user, $bill);
    }
}