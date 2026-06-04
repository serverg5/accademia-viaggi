<?php

namespace App\Policies;

use App\Models\TravelRecord;
use App\Models\User;
use App\Support\Permissions;

class TravelRecordPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(Permissions::VIEW_TRAVEL_RECORDS);
    }

    public function view(User $user, TravelRecord $travelRecord): bool
    {
        return $user->can(Permissions::VIEW_TRAVEL_RECORDS);
    }

    public function create(User $user): bool
    {
        return $user->can(Permissions::CREATE_TRAVEL_RECORDS);
    }

    public function update(User $user, TravelRecord $travelRecord): bool
    {
        return $user->can(Permissions::EDIT_TRAVEL_RECORDS)
            && $this->canChangeRecordYear($user, $travelRecord);
    }

    public function delete(User $user, TravelRecord $travelRecord): bool
    {
        return $user->can(Permissions::DELETE_TRAVEL_RECORDS)
            && $this->canChangeRecordYear($user, $travelRecord);
    }

    public function deleteAny(User $user): bool
    {
        return $user->can(Permissions::DELETE_TRAVEL_RECORDS);
    }

    public function restore(User $user, TravelRecord $travelRecord): bool
    {
        return $user->can(Permissions::DELETE_TRAVEL_RECORDS)
            && $this->canChangeRecordYear($user, $travelRecord);
    }

    public function restoreAny(User $user): bool
    {
        return $user->can(Permissions::DELETE_TRAVEL_RECORDS);
    }

    public function forceDelete(User $user, TravelRecord $travelRecord): bool
    {
        return $user->can(Permissions::DELETE_TRAVEL_RECORDS)
            && $this->canChangeRecordYear($user, $travelRecord);
    }

    public function forceDeleteAny(User $user): bool
    {
        return $user->can(Permissions::DELETE_TRAVEL_RECORDS);
    }

    private function canChangeRecordYear(User $user, TravelRecord $travelRecord): bool
    {
        $travelYear = $travelRecord->travelYear;

        if ($travelYear === null) {
            return true;
        }

        return ! $travelYear->is_locked || $user->can(Permissions::UNLOCK_YEARS);
    }
}
