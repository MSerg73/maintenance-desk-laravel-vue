<?php

use App\Enums\EquipmentStatus;
use App\Enums\MaintenanceRequestStatus;
use App\Enums\RequestPriority;
use App\Enums\UserRole;
use App\Models\Equipment;
use App\Models\MaintenanceRequest;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->withoutVite();
});

it('shows maintenance summary metrics and recent requests', function () {
    $user = User::factory()->create(['role' => UserRole::Operator]);
    $equipment = Equipment::factory()->create([
        'name' => 'Mixer MX-01',
        'code' => 'MX-01',
        'status' => EquipmentStatus::Maintenance,
    ]);

    MaintenanceRequest::factory()->create([
        'equipment_id' => $equipment->id,
        'title' => 'Critical vibration alarm',
        'priority' => RequestPriority::from('critical'),
        'status' => MaintenanceRequestStatus::from('new'),
        'created_by' => $user->id,
        'assigned_to' => null,
    ]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard')
            ->where('stats.equipment.total', 1)
            ->where('stats.equipment.active', 0)
            ->where('stats.equipment.maintenance', 1)
            ->where('stats.equipment.out_of_service', 0)
            ->where('stats.requests.new', 1)
            ->where('stats.requests.in_progress', 0)
            ->where('stats.requests.done', 0)
            ->where('stats.requests.closed', 0)
            ->where('stats.requests.high_or_critical_open', 1)
            ->has('recentRequests', 1)
            ->where('recentRequests.0.title', 'Critical vibration alarm')
            ->where('recentRequests.0.equipment.code', 'MX-01')
        );
});
