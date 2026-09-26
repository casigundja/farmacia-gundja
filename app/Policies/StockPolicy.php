<?php

namespace App\Policies;

use App\Models\User;

class StockPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isStockist() || $user->isAttendant();
    }

    public function view(User $user): bool
    {
        return $user->isAdmin() || $user->isStockist() || $user->isAttendant();
    }

    public function manage(User $user): bool
    {
        return $user->isAdmin() || $user->isStockist();
    }
}
