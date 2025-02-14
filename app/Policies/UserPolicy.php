<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    public function before(User $user, $ability)
    {
        if ($user->hasRole('admin')) {
            return true;
        }
    }

    public function viewAny(User $user): bool
    {
        return $user->hasPermission('viewAny user');
    }

    public function viewTrashed(User $user): bool
    {
        return $user->hasPermission('viewTrashed user');
    }

    public function view(User $user): bool
    {
        return $user->hasPermission('view user');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('create user');
    }

    public function update(User $user, $userToUpdate): bool
    {
        return $user->hasPermission('update user') || ($user->id === $userToUpdate->id);
    }

    public function delete(User $user): bool
    {
        return $user->hasPermission('delete user');
    }

    public function restore(User $user): bool
    {
        return $user->hasPermission('restore user');
    }

    public function forceDelete(User $user): bool
    {
        return $user->hasPermission('forceDelete user');
    }
}
