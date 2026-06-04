<?php

namespace App\Policies;

use App\Models\TravelSelectOption;
use App\Models\User;
use App\Support\Permissions;

class TravelSelectOptionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(Permissions::MANAGE_SELECT_OPTIONS);
    }

    public function view(User $user, TravelSelectOption $travelSelectOption): bool
    {
        return $user->can(Permissions::MANAGE_SELECT_OPTIONS);
    }

    public function create(User $user): bool
    {
        return $user->can(Permissions::MANAGE_SELECT_OPTIONS);
    }

    public function update(User $user, TravelSelectOption $travelSelectOption): bool
    {
        return $user->can(Permissions::MANAGE_SELECT_OPTIONS);
    }

    public function delete(User $user, TravelSelectOption $travelSelectOption): bool
    {
        return $user->isAdmin();
    }

    public function deleteAny(User $user): bool
    {
        return $user->isAdmin();
    }
}
