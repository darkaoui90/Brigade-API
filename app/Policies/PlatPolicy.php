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
        return $plat->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Plat $plat): bool
    {
        return $plat->user_id === $user->id;
    }

    public function delete(User $user, Plat $plat): bool
    {
        return $plat->user_id === $user->id;
    }
}

