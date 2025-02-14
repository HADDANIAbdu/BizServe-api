<?php

namespace App\Policies;

use App\Models\Notitfication;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class NotificationPolicy
{
    public function before(User $user, $ability)
    {
        if ($user->hasRole('admin')) {
            return true;
        }
    }

    public function viewAny(User $user): bool
    {
        return $user->hasPermission('viewAny notification');
    }

    public function viewTrashed(User $user): bool
    {
        return $user->hasPermission('viewTrashed notification');
    }

    public function view(User $user): bool
    {
        return $user->hasPermission('view notification');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('create notification');
    }

    public function update(User $user): bool
    {
        return $user->hasPermission('update notification');
    }

    public function delete(User $user): bool
    {
        return $user->hasPermission('delete notification');
    }

    public function restore(User $user): bool
    {
        return $user->hasPermission('restore notification');
    }

    public function forceDelete(User $user): bool
    {
        return $user->hasPermission('forceDelete notification');
    }
}
