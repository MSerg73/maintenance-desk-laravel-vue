<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\MaintenanceRequest;
use App\Models\User;

class MaintenanceRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, MaintenanceRequest $maintenanceRequest): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->hasRole(
            UserRole::Operator,
            UserRole::Engineer,
            UserRole::Admin,
        );
    }

    public function manage(User $user): bool
    {
        return $user->hasRole(UserRole::Engineer, UserRole::Admin);
    }

    public function update(User $user, MaintenanceRequest $maintenanceRequest): bool
    {
        return $this->manage($user);
    }

    public function transition(User $user, MaintenanceRequest $maintenanceRequest): bool
    {
        return $this->manage($user);
    }

    public function assign(User $user): bool
    {
        return $user->hasRole(UserRole::Admin);
    }
}
