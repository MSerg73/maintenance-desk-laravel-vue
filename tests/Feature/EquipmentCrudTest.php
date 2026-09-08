<?php

use App\Enums\EquipmentStatus;
use App\Models\Equipment;
use App\Models\MaintenanceRequest;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->withoutVite();
});

it('redirects guests away from the equipment register', function () {
    $this->get(route('equipment.index'))
        ->assertRedirect(route('login'));
});

it('shows the equipment register to an authenticated user', function () {
    $user = User::factory()->create();
    Equipment::factory()->create([
        'name' => 'Mixer MX-01',
        'code' => 'MX-01',
        'status' => EquipmentStatus::Maintenance,
    ]);

    $this->actingAs($user)
        ->get(route('equipment.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('equipment/Index')
            ->has('equipment.data', 1)
            ->where('equipment.data.0.name', 'Mixer MX-01')
            ->where('equipment.data.0.status', 'maintenance')
            ->where('equipment.data.0.status_label', 'Maintenance')
        );
});

it('filters equipment by search and status', function () {
    $user = User::factory()->create();

    Equipment::factory()->create([
        'name' => 'Packaging machine',
        'code' => 'PK-100',
        'location' => 'Line 1',
        'status' => EquipmentStatus::Active,
    ]);

    Equipment::factory()->create([
        'name' => 'Utility pump',
        'code' => 'PU-200',
        'location' => 'Utility area',
        'status' => EquipmentStatus::Maintenance,
    ]);

    $this->actingAs($user)
        ->get(route('equipment.index', [
            'q' => 'pump',
            'status' => 'maintenance',
        ]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('equipment.data', 1)
            ->where('equipment.data.0.code', 'PU-200')
            ->where('filters.q', 'pump')
            ->where('filters.status', 'maintenance')
        );
});

it('creates equipment with validated data', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('equipment.store'), [
            'name' => 'Control cabinet',
            'code' => 'CAB-001',
            'location' => 'Workshop',
            'status' => EquipmentStatus::Active->value,
        ])
        ->assertRedirect(route('equipment.index'))
        ->assertSessionHas('success');

    $this->assertDatabaseHas('equipment', [
        'name' => 'Control cabinet',
        'code' => 'CAB-001',
        'location' => 'Workshop',
        'status' => 'active',
    ]);
});

it('rejects duplicate equipment codes and invalid status values', function () {
    $user = User::factory()->create();
    Equipment::factory()->create(['code' => 'EQ-001']);

    $this->actingAs($user)
        ->from(route('equipment.create'))
        ->post(route('equipment.store'), [
            'name' => 'Duplicate code machine',
            'code' => 'EQ-001',
            'location' => 'Line 2',
            'status' => 'broken',
        ])
        ->assertRedirect(route('equipment.create'))
        ->assertSessionHasErrors(['code', 'status']);
});

it('updates equipment and keeps its code unique against other records', function () {
    $user = User::factory()->create();
    $equipment = Equipment::factory()->create(['code' => 'EQ-010']);
    Equipment::factory()->create(['code' => 'EQ-020']);

    $this->actingAs($user)
        ->put(route('equipment.update', $equipment), [
            'name' => 'Updated equipment',
            'code' => 'EQ-010',
            'location' => 'Updated location',
            'status' => EquipmentStatus::OutOfService->value,
        ])
        ->assertRedirect(route('equipment.index'))
        ->assertSessionHas('success');

    $this->assertDatabaseHas('equipment', [
        'id' => $equipment->id,
        'name' => 'Updated equipment',
        'code' => 'EQ-010',
        'status' => 'out_of_service',
    ]);

    $this->actingAs($user)
        ->put(route('equipment.update', $equipment), [
            'name' => 'Invalid duplicate',
            'code' => 'EQ-020',
            'location' => null,
            'status' => EquipmentStatus::Active->value,
        ])
        ->assertSessionHasErrors('code');
});

it('deletes equipment that has no maintenance requests', function () {
    $user = User::factory()->create();
    $equipment = Equipment::factory()->create();

    $this->actingAs($user)
        ->delete(route('equipment.destroy', $equipment))
        ->assertRedirect(route('equipment.index'))
        ->assertSessionHas('success');

    $this->assertDatabaseMissing('equipment', ['id' => $equipment->id]);
});

it('protects equipment referenced by maintenance requests from deletion', function () {
    $user = User::factory()->create();
    $equipment = Equipment::factory()->create();

    MaintenanceRequest::factory()->create([
        'equipment_id' => $equipment->id,
    ]);

    $this->actingAs($user)
        ->delete(route('equipment.destroy', $equipment))
        ->assertSessionHas('error');

    $this->assertDatabaseHas('equipment', ['id' => $equipment->id]);
});
