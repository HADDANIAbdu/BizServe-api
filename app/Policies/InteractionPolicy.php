<?php

namespace App\Policies;

use App\Models\Interaction;
use App\Models\User;

class InteractionPolicy
{
    public function before(User $user, $ability)
    {
        if ($user->hasRole('admin')) {
            return true;
        }
    }

    public function viewAny(User $user): bool
    {
        return $user->hasPermission('viewAny interaction');
    }

    public function view(User $user): bool
    {
        return $user->hasPermission('view interaction');
    }
    public function viewTrashed(User $user): bool
    {
        return $user->hasPermission('viewTrashed interaction');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('create interaction');
    }

    public function update(User $user): bool
    {
        return $user->hasPermission('update interaction');
    }

    public function delete(User $user): bool
    {
        return $user->hasPermission('delete interaction');
    }

    public function restore(User $user): bool
    {
        return $user->hasPermission('restore interaction');
    }

    public function forceDelete(User $user): bool
    {
        return $user->hasPermission('forceDelete interaction');
    }
    public function summary(User $user): bool
    {
        return $user->hasPermission('summary interaction');
    }
    public function upcomming(User $user): bool
    {
        return $user->hasPermission('upcomming interaction');
    }
}
