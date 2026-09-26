<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isAttendant() || $user->isEmployee();
    }

    public function view(User $user, Order $order): bool
    {
        if ($user->isAdmin() || $user->isAttendant() || $user->isEmployee()) {
            return true;
        }

        return $order->customer && $order->customer->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Order $order): bool
    {
        return $user->isAdmin() || $user->isAttendant();
    }

    public function updateStatus(User $user, Order $order): bool
    {
        return $user->isAdmin() || $user->isAttendant() || $user->isStockist();
    }

    public function delete(User $user, Order $order): bool
    {
        return $user->isAdmin();
    }
}
