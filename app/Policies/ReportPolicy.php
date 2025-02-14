<?php

namespace App\Policies;

use App\Models\User;

class ReportPolicy
{
    public function before(User $user, $ability)
    {
        if ($user->hasRole('admin')) {
            return true;
        }
    }

    public function viewAny(User $user): bool
    {
        return $user->hasPermission('viewAny_report');
    }
    public function viewTrashed(User $user): bool
    {
        return $user->hasPermission('viewTrashed_report');
    }
    public function view(User $user): bool
    {
        return $user->hasPermission('view_report');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('create_report');
    }

    public function update(User $user): bool
    {
        return $user->hasPermission('update_report');
    }

    public function delete(User $user): bool
    {
        return $user->hasPermission('delete_report');
    }

    public function restore(User $user): bool
    {
        return $user->hasPermission('restore_report');
    }

    public function forceDelete(User $user): bool
    {
        return $user->hasPermission('forceDelete_report');
    }
}
