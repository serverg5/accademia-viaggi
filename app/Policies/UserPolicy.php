<?php

namespace App\Policies;

use App\Models\User;
use App\Support\Permissions;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(Permissions::MANAGE_USERS);
    }

    public function view(User $user, User $model): bool
    {
        return $user->can(Permissions::MANAGE_USERS);
    }

    public function create(User $user): bool
    {
        return $user->can(Permissions::MANAGE_USERS);
    }

    public function update(User $user, User $model): bool
    {
        return $user->can(Permissions::MANAGE_USERS);
    }

    public function delete(User $user, User $model): bool
    {
        return $user->can(Permissions::MANAGE_USERS)
            && $user->getKey() !== $model->getKey();
    }

    public function deleteAny(User $user): bool
    {
        return $user->can(Permissions::MANAGE_USERS);
    }
}
