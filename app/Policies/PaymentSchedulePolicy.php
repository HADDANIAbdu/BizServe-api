<?php

namespace App\Policies;

use App\Models\PaymentSchedule;
use App\Models\User;

class PaymentSchedulePolicy
{

    public function before(User $user, $ability)
    {
        if ($user->hasRole('admin')) {
            return true;
        }
    }

    public function viewAny(User $user): bool
    {
        return $user->hasPermission('viewAny paymentSchedule');
    }

    public function view(User $user): bool
    {
        return $user->hasPermission('view paymentSchedule');
    }
    public function viewTrashed(User $user): bool
    {
        return $user->hasPermission('viewTrashed paymentSchedule');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('create paymentSchedule');
    }

    public function update(User $user): bool
    {
        return $user->hasPermission('update paymentSchedule');
    }

    public function delete(User $user): bool
    {
        return $user->hasPermission('delete paymentSchedule');
    }

    public function forceDelete(User $user): bool
    {
        return $user->hasPermission('restore paymentSchedule');
    }
    public function restore(User $user): bool
    {
        return $user->hasPermission('restore paymentSchedule');
    }
}
