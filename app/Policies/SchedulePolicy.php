<?php

namespace App\Policies;

use App\Models\Schedule;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class SchedulePolicy
{
    public function before(User $user, $ability)
    {
        if ($user->hasRole('admin')) {
            return true;
        }
    }

    public function viewAny(User $user): bool
    {
        return $user->hasPermission('viewAny_schedule');
    }

    public function viewTrashed(User $user): bool
    {
        return $user->hasPermission('viewTrashed_schedule');
    }

    public function view(User $user): bool
    {
        return $user->hasPermission('view_schedule');
    }
    public function conflictCheck(User $user): bool
    {
        return $user->hasPermission('conflictCheck_schedule');
    }
    public function conflictResolution(User $user): bool
    {
        return $user->hasPermission('conflictResolution_schedule');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('create_schedule');
    }

    public function update(User $user): bool
    {
        return $user->hasPermission('update_schedule');
    }

    public function delete(User $user): bool
    {
        return $user->hasPermission('delete_schedule');
    }

    public function restore(User $user): bool
    {
        return $user->hasPermission('restore_schedule');
    }

    public function forceDelete(User $user): bool
    {
        return $user->hasPermission('forceDelete_schedule');
    }
}
