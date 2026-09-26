<?php

namespace App\Policies;

use App\Models\User;

class SupplierPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isStockist();
    }

    public function view(User $user): bool
    {
        return $user->isAdmin() || $user->isStockist();
    }

    public function manage(User $user): bool
    {
        return $user->isAdmin();
    }
}
