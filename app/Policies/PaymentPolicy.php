<?php

namespace App\Policies;

use App\Models\Payment;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PaymentPolicy
{
    public function before(User $user, $ability)
    {
        if ($user->hasRole('admin')) {
            return true;
        }
    }

    public function viewAny(User $user): bool
    {
        return $user->hasPermission('viewAny payment');
    }

    public function view(User $user): bool
    {
        return $user->hasPermission('view payment');
    }

    public function viewTrashed(User $user): bool
    {
        return $user->hasPermission('viewTrashed payment');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('create payment');
    }

    public function update(User $user): bool
    {
        return $user->hasPermission('update payment');
    }

    public function delete(User $user): bool
    {
        return $user->hasPermission('delete payment');
    }

    public function restore(User $user): bool
    {
        return $user->hasPermission('restore payment');
    }


    public function forceDelete(User $user): bool
    {
        return $user->hasPermission('forceDelete payment');
    }

    public function reminder(User $user): bool
    {
        return $user->hasPermission('create paymentReminder');
    }

    public function history(User $user): bool
    {
        return $user->hasPermission('view paymentReminder');
    }
}
