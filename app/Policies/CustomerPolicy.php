<?php

namespace App\Policies;

use App\Models\Customer;
use App\Models\User;

class CustomerPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isAttendant();
    }

    public function view(User $user, Customer $customer): bool
    {
        if ($user->isAdmin() || $user->isAttendant()) {
            return true;
        }

        return $customer->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Customer $customer): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $customer->user_id === $user->id;
    }

    public function delete(User $user, Customer $customer): bool
    {
        return $user->isAdmin();
    }
}
