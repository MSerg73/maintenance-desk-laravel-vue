<?php

use App\Enums\EquipmentStatus;
use App\Enums\MaintenanceRequestStatus;
use App\Enums\RequestPriority;
use App\Enums\UserRole;
use App\Models\Equipment;
use App\Models\MaintenanceRequest;
use App\Models\User;

beforeEach(function () {
    $this->withoutVite();
});

it('denies equipment management to an operator', function () {
    $operator = User::factory()->create(['role' => UserRole::Operator]);
    $equipment = Equipment::factory()->create();

    $this->actingAs($operator)
        ->get(route('equipment.create'))
        ->assertForbidden();

    $this->actingAs($operator)
        ->post(route('equipment.store'), [
            'name' => 'Unauthorized asset',
            'code' => 'NO-001',
            'location' => 'Line 1',
            'status' => EquipmentStatus::Active->value,
        ])
        ->assertForbidden();

    $this->actingAs($operator)
        ->delete(route('equipment.destroy', $equipment))
        ->assertForbidden();
});

it('denies equipment management to an engineer', function () {
    $engineer = User::factory()->create(['role' => UserRole::Engineer]);
    $equipment = Equipment::factory()->create();

    $this->actingAs($engineer)
        ->put(route('equipment.update', $equipment), [
            'name' => 'Unauthorized update',
            'code' => $equipment->code,
            'location' => $equipment->location,
            'status' => EquipmentStatus::Active->value,
        ])
        ->assertForbidden();
});

it('allows an operator to create a request but denies assignee selection', function () {
    $operator = User::factory()->create(['role' => UserRole::Operator]);
    $engineer = User::factory()->create(['role' => UserRole::Engineer]);
    $equipment = Equipment::factory()->create();
    $priority = RequestPriority::cases()[0];

    $this->actingAs($operator)
        ->post(route('maintenance-requests.store'), [
            'equipment_id' => $equipment->id,
            'title' => 'Operator request',
            'description' => 'Created without privileged assignment.',
            'priority' => $priority->value,
        ])
        ->assertRedirect(route('maintenance-requests.index'));

    $this->actingAs($operator)
        ->post(route('maintenance-requests.store'), [
            'equipment_id' => $equipment->id,
            'title' => 'Unauthorized assignment',
            'description' => 'Operator attempts to choose an assignee.',
            'priority' => $priority->value,
            'assigned_to' => $engineer->id,
        ])
        ->assertForbidden();
});

it('denies request editing and workflow transitions to an operator', function () {
    $operator = User::factory()->create(['role' => UserRole::Operator]);
    $request = MaintenanceRequest::factory()->create([
        'status' => MaintenanceRequestStatus::from('new'),
    ]);
    $priority = RequestPriority::cases()[0];

    $this->actingAs($operator)
        ->get(route('maintenance-requests.edit', $request))
        ->assertForbidden();

    $this->actingAs($operator)
        ->put(route('maintenance-requests.update', $request), [
            'equipment_id' => $request->equipment_id,
            'title' => 'Forbidden update',
            'description' => 'Operator may not edit an existing request.',
            'priority' => $priority->value,
        ])
        ->assertForbidden();

    $this->actingAs($operator)
        ->patch(route('maintenance-requests.status.update', $request), [
            'status' => 'in_progress',
        ])
        ->assertForbidden();
});

it('allows an engineer to edit and transition but denies changing assignee', function () {
    $engineer = User::factory()->create(['role' => UserRole::Engineer]);
    $otherEngineer = User::factory()->create(['role' => UserRole::Engineer]);
    $request = MaintenanceRequest::factory()->create([
        'status' => MaintenanceRequestStatus::from('new'),
        'assigned_to' => null,
    ]);
    $priority = RequestPriority::cases()[0];

    $this->actingAs($engineer)
        ->put(route('maintenance-requests.update', $request), [
            'equipment_id' => $request->equipment_id,
            'title' => 'Engineer update',
            'description' => 'Engineer may update request details.',
            'priority' => $priority->value,
        ])
        ->assertRedirect(route('maintenance-requests.index'));

    $this->actingAs($engineer)
        ->put(route('maintenance-requests.update', $request), [
            'equipment_id' => $request->equipment_id,
            'title' => 'Assignment attempt',
            'description' => 'Engineer attempts privileged assignment.',
            'priority' => $priority->value,
            'assigned_to' => $otherEngineer->id,
        ])
        ->assertForbidden();

    $this->actingAs($engineer)
        ->patch(route('maintenance-requests.status.update', $request), [
            'status' => 'in_progress',
        ])
        ->assertSessionHas('success');
});

it('allows an admin to manage equipment and assign maintenance requests', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $engineer = User::factory()->create(['role' => UserRole::Engineer]);
    $equipment = Equipment::factory()->create();
    $request = MaintenanceRequest::factory()->create([
        'equipment_id' => $equipment->id,
        'assigned_to' => null,
    ]);
    $priority = RequestPriority::cases()[0];

    $this->actingAs($admin)
        ->post(route('equipment.store'), [
            'name' => 'Admin-created equipment',
            'code' => 'ADM-001',
            'location' => 'Workshop',
            'status' => EquipmentStatus::Active->value,
        ])
        ->assertRedirect(route('equipment.index'));

    $this->actingAs($admin)
        ->put(route('maintenance-requests.update', $request), [
            'equipment_id' => $equipment->id,
            'title' => 'Admin assignment',
            'description' => 'Admin assigns the responsible engineer.',
            'priority' => $priority->value,
            'assigned_to' => $engineer->id,
        ])
        ->assertRedirect(route('maintenance-requests.index'));

    $this->assertDatabaseHas('maintenance_requests', [
        'id' => $request->id,
        'assigned_to' => $engineer->id,
    ]);
});
