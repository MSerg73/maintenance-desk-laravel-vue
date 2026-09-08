<?php

namespace Database\Factories;

use App\Enums\MaintenanceRequestStatus;
use App\Enums\RequestPriority;
use App\Models\Equipment;
use App\Models\MaintenanceRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MaintenanceRequest>
 */
class MaintenanceRequestFactory extends Factory
{
    public function definition(): array
    {
        return [
            'equipment_id' => Equipment::factory(),
            'created_by' => User::factory(),
            'assigned_to' => null,
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'priority' => RequestPriority::Normal,
            'status' => MaintenanceRequestStatus::New,
            'closed_at' => null,
        ];
    }
}
