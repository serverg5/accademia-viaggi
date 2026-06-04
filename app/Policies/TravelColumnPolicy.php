<?php

namespace App\Policies;

use App\Models\TravelColumn;
use App\Models\User;
use App\Support\Permissions;

class TravelColumnPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(Permissions::MANAGE_TRAVEL_COLUMNS);
    }

    public function view(User $user, TravelColumn $travelColumn): bool
    {
        return $user->can(Permissions::MANAGE_TRAVEL_COLUMNS);
    }

    public function create(User $user): bool
    {
        return $user->can(Permissions::MANAGE_TRAVEL_COLUMNS);
    }

    public function update(User $user, TravelColumn $travelColumn): bool
    {
        return $user->can(Permissions::MANAGE_TRAVEL_COLUMNS);
    }

    public function delete(User $user, TravelColumn $travelColumn): bool
    {
        return $user->can(Permissions::MANAGE_TRAVEL_COLUMNS) && $travelColumn->is_deletable;
    }

    public function deleteAny(User $user): bool
    {
        return $user->can(Permissions::MANAGE_TRAVEL_COLUMNS);
    }

    public function reorder(User $user): bool
    {
        return $user->can(Permissions::MANAGE_TRAVEL_COLUMNS);
    }

    public function changeBillingVisibility(User $user, TravelColumn $travelColumn): bool
    {
        return $user->can(Permissions::MANAGE_TRAVEL_COLUMNS);
    }
}
