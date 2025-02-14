<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class RolePolicy
{
    public function before(User $user, $ability)
    {
        if ($user->hasRole('admin')) {
            return true;
        }
    }

    public function viewAny(User $user): bool
    {
        return $user->hasPermission('viewAny role');
    }
    public function viewTrashed(User $user): bool
    {
        return $user->hasPermission('viewTrashed role');
    }
    public function view(User $user): bool
    {
        return $user->hasPermission('view role');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('create role');
    }

    public function update(User $user): bool
    {
        return $user->hasPermission('update role');
    }

    public function delete(User $user): bool
    {
        return $user->hasPermission('delete role');
    }

    public function restore(User $user): bool
    {
        return $user->hasPermission('restore role');
    }

    public function forceDelete(User $user): bool
    {
        return $user->hasPermission('forceDelete role');
    }
}
