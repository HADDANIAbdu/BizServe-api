<?php

namespace App\Policies;

use App\Models\Client;
use App\Models\User;

class ClientPolicy
{
    public function before(User $user, $ability)
    {
        if ($user->hasRole('admin')) {
            return true;
        }
    }

    public function viewAny(User $user): bool
    {
        return $user->hasPermission('viewAny client');
    }

    public function view(User $user): bool
    {
        return $user->hasPermission('view client');
    }
    public function viewTrashed(User $user): bool
    {
        return $user->hasPermission('viewTrashed client');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('create client');
    }

    public function update(User $user): bool
    {
        return $user->hasPermission('update client');
    }

    public function delete(User $user): bool
    {
        return $user->hasPermission('delete client');
    }

    public function restore(User $user): bool
    {
        return $user->hasPermission('restore client');
    }

    public function forceDelete(User $user): bool
    {
        return $user->hasPermission('forceDelete client');
    }



    public function enrollService(User $user): bool
    {
        return $user->hasPermission('enroll service');
    }

    public function getEnrolledServices(User $user): bool
    {
        return $user->hasPermission('get enrolled service');
    }
    public function removeEnrolledService(User $user): bool
    {
        return $user->hasPermission('remove enrolled service');
    }
    public function forceRemoveEnrolledService(User $user): bool
    {
        return $user->hasPermission('force remove enrolled service');
    }
}
