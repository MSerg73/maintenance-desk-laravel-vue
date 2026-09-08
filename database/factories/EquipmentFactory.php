<?php

namespace Database\Factories;

use App\Enums\EquipmentStatus;
use App\Models\Equipment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Equipment>
 */
class EquipmentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement([
                'Mixer',
                'Conveyor',
                'Packaging machine',
                'Pump station',
                'Control cabinet',
            ]),
            'code' => fake()->unique()->bothify('EQ-####'),
            'location' => fake()->randomElement([
                'Production line 1',
                'Production line 2',
                'Utility area',
                'Workshop',
            ]),
            'status' => EquipmentStatus::Active,
        ];
    }
}
