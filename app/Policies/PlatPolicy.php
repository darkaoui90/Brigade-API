<?php

namespace App\Policies;

use App\Models\Plat;
use App\Models\User;

class PlatPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Plat $plat): bool
    {
        return $plat->is_available || $user->is_admin;
    }

    public function create(User $user): bool
    {
        return (bool) $user->is_admin;
    }

    public function update(User $user, Plat $plat): bool
    {
        return (bool) $user->is_admin;
    }

    public function delete(User $user, Plat $plat): bool
    {
        return (bool) $user->is_admin;
    }
}
