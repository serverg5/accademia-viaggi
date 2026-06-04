<?php

namespace App\Policies;

use App\Models\CompanySetting;
use App\Models\User;
use App\Support\Permissions;

class CompanySettingPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(Permissions::MANAGE_COMPANY_SETTINGS);
    }

    public function view(User $user, CompanySetting $companySetting): bool
    {
        return $user->can(Permissions::MANAGE_COMPANY_SETTINGS);
    }

    public function create(User $user): bool
    {
        return $user->can(Permissions::MANAGE_COMPANY_SETTINGS);
    }

    public function update(User $user, CompanySetting $companySetting): bool
    {
        return $user->can(Permissions::MANAGE_COMPANY_SETTINGS);
    }

    public function delete(User $user, CompanySetting $companySetting): bool
    {
        return $user->can(Permissions::MANAGE_COMPANY_SETTINGS);
    }

    public function deleteAny(User $user): bool
    {
        return $user->can(Permissions::MANAGE_COMPANY_SETTINGS);
    }
}
