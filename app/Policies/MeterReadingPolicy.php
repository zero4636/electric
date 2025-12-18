<?php

namespace App\Policies;

use App\Models\MeterReading;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class MeterReadingPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isSuperAdmin();
    }

    public function view(User $user, MeterReading $meterReading): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->canManageOrganization($meterReading->electricMeter->organizationUnit);
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isSuperAdmin();
    }

    public function update(User $user, MeterReading $meterReading): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->canManageOrganization($meterReading->electricMeter->organizationUnit);
    }

    public function delete(User $user, MeterReading $meterReading): bool
    {
        return $this->update($user, $meterReading);
    }
}