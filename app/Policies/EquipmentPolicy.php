<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Equipment;
use App\Models\User;

class EquipmentPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Equipment $equipment): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->hasRole(UserRole::Admin);
    }

    public function update(User $user, Equipment $equipment): bool
    {
        return $user->hasRole(UserRole::Admin);
    }

    public function delete(User $user, Equipment $equipment): bool
    {
        return $user->hasRole(UserRole::Admin);
    }
}
