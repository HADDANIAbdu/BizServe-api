<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class ServicePolicy
{
    public function before(User $user, $ability)
    {
        if ($user->hasRole('admin')) {
            return true;
        }
    }

    public function viewAny(User $user): bool
    {
        return $user->hasPermission('viewAny_service');
    }

    public function view(User $user): bool
    {
        return $user->hasPermission('view_service');
    }
    public function viewTrashed(User $user): bool
    {
        return $user->hasPermission('viewTrashed_service');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('create_service');
    }

    public function update(User $user): bool
    {
        return $user->hasPermission('update_service');
    }

    public function delete(User $user): bool
    {
        return $user->hasPermission('delete_service');
    }

    public function restore(User $user): bool
    {
        return $user->hasPermission('restore_service');
    }
    
    public function forceDelete(User $user): bool
    {
        return $user->hasPermission('forceDelete_service');
    }
}
