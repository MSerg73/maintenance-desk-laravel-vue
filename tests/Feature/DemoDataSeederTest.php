<?php

use Database\Seeders\DemoDataSeeder;

it('builds the deterministic portfolio demo dataset', function () {
    $this->seed(DemoDataSeeder::class);

    $this->assertDatabaseCount('users', 3);
    $this->assertDatabaseCount('equipment', 6);
    $this->assertDatabaseCount('maintenance_requests', 8);

    $this->assertDatabaseHas('users', [
        'email' => 'admin@example.com',
        'role' => 'admin',
    ]);

    $this->assertDatabaseHas('users', [
        'email' => 'engineer@example.com',
        'role' => 'engineer',
    ]);

    $this->assertDatabaseHas('users', [
        'email' => 'operator@example.com',
        'role' => 'operator',
    ]);

    $this->assertDatabaseHas('equipment', [
        'code' => 'CV-420',
        'status' => 'out_of_service',
    ]);

    $this->assertDatabaseHas('maintenance_requests', [
        'title' => 'Cooling pressure below setpoint',
        'priority' => 'critical',
        'status' => 'new',
    ]);
});
