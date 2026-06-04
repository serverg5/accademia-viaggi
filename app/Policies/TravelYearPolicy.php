<?php

namespace App\Policies;

use App\Models\TravelYear;
use App\Models\User;
use App\Support\Permissions;

class TravelYearPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(Permissions::MANAGE_YEARS);
    }

    public function view(User $user, TravelYear $travelYear): bool
    {
        return $user->can(Permissions::MANAGE_YEARS);
    }

    public function create(User $user): bool
    {
        return $user->can(Permissions::MANAGE_YEARS);
    }

    public function update(User $user, TravelYear $travelYear): bool
    {
        return $user->can(Permissions::MANAGE_YEARS);
    }

    public function delete(User $user, TravelYear $travelYear): bool
    {
        return $user->can(Permissions::MANAGE_YEARS);
    }

    public function deleteAny(User $user): bool
    {
        return $user->can(Permissions::MANAGE_YEARS);
    }

    public function unlock(User $user, TravelYear $travelYear): bool
    {
        return $user->can(Permissions::UNLOCK_YEARS);
    }
}
