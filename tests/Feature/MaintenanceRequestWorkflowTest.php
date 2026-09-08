<?php

use App\Enums\MaintenanceRequestStatus;
use App\Enums\RequestPriority;
use App\Models\Equipment;
use App\Models\MaintenanceRequest;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->withoutVite();
});

it('redirects guests away from the maintenance request register', function () {
    $this->get(route('maintenance-requests.index'))
        ->assertRedirect(route('login'));
});

it('shows maintenance requests with equipment creator and assignee', function () {
    $viewer = User::factory()->create();
    $creator = User::factory()->create(['name' => 'Request Creator']);
    $assignee = User::factory()->create(['name' => 'Assigned Engineer']);
    $equipment = Equipment::factory()->create([
        'name' => 'Packaging machine',
        'code' => 'PK-100',
    ]);

    MaintenanceRequest::factory()->create([
        'equipment_id' => $equipment->id,
        'title' => 'Knife drive vibration',
        'description' => 'Knife drive vibration detected during operation',
        'status' => MaintenanceRequestStatus::from('new'),
        'created_by' => $creator->id,
        'assigned_to' => $assignee->id,
    ]);

    $this->actingAs($viewer)
        ->get(route('maintenance-requests.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('maintenance-requests/Index')
            ->has('requests.data', 1)
            ->where('requests.data.0.title', 'Knife drive vibration')
            ->where('requests.data.0.description', 'Knife drive vibration detected during operation')
            ->where('requests.data.0.equipment.code', 'PK-100')
            ->where('requests.data.0.creator.name', 'Request Creator')
            ->where('requests.data.0.assignee.name', 'Assigned Engineer')
            ->where('requests.data.0.status', 'new')
            ->where('requests.data.0.next_status.value', 'in_progress')
        );
});

it('creates a request in new status and records the creator', function () {
    $creator = User::factory()->create();
    $assignee = User::factory()->create();
    $equipment = Equipment::factory()->create();
    $priority = RequestPriority::cases()[0];

    $this->actingAs($creator)
        ->post(route('maintenance-requests.store'), [
            'equipment_id' => $equipment->id,
            'title' => 'Unstable temperature sensor signal',
            'description' => 'Temperature sensor signal is unstable',
            'priority' => $priority->value,
            'assigned_to' => $assignee->id,
        ])
        ->assertRedirect(route('maintenance-requests.index'))
        ->assertSessionHas('success');

    $this->assertDatabaseHas('maintenance_requests', [
        'equipment_id' => $equipment->id,
        'title' => 'Unstable temperature sensor signal',
        'description' => 'Temperature sensor signal is unstable',
        'priority' => $priority->value,
        'status' => 'new',
        'created_by' => $creator->id,
        'assigned_to' => $assignee->id,
    ]);
});

it('updates request details without changing workflow status or creator', function () {
    $actor = User::factory()->create();
    $creator = User::factory()->create();
    $newAssignee = User::factory()->create();
    $equipment = Equipment::factory()->create();
    $request = MaintenanceRequest::factory()->create([
        'created_by' => $creator->id,
        'status' => MaintenanceRequestStatus::from('new'),
    ]);
    $priority = RequestPriority::cases()[0];

    $this->actingAs($actor)
        ->put(route('maintenance-requests.update', $request), [
            'equipment_id' => $equipment->id,
            'title' => 'Updated request title',
            'description' => 'Updated diagnostic description',
            'priority' => $priority->value,
            'assigned_to' => $newAssignee->id,
            'status' => 'closed',
            'created_by' => $actor->id,
        ])
        ->assertRedirect(route('maintenance-requests.index'));

    $this->assertDatabaseHas('maintenance_requests', [
        'id' => $request->id,
        'equipment_id' => $equipment->id,
        'title' => 'Updated request title',
        'description' => 'Updated diagnostic description',
        'priority' => $priority->value,
        'assigned_to' => $newAssignee->id,
        'status' => 'new',
        'created_by' => $creator->id,
    ]);
});

it('allows the next valid workflow transition', function () {
    $actor = User::factory()->create();
    $request = MaintenanceRequest::factory()->create([
        'status' => MaintenanceRequestStatus::from('new'),
    ]);

    $this->actingAs($actor)
        ->patch(route('maintenance-requests.status.update', $request), [
            'status' => 'in_progress',
        ])
        ->assertSessionHas('success');

    $this->assertDatabaseHas('maintenance_requests', [
        'id' => $request->id,
        'status' => 'in_progress',
    ]);
});

it('rejects skipping a workflow state', function () {
    $actor = User::factory()->create();
    $request = MaintenanceRequest::factory()->create([
        'status' => MaintenanceRequestStatus::from('new'),
    ]);

    $this->actingAs($actor)
        ->patch(route('maintenance-requests.status.update', $request), [
            'status' => 'done',
        ])
        ->assertSessionHasErrors('status');

    $this->assertDatabaseHas('maintenance_requests', [
        'id' => $request->id,
        'status' => 'new',
    ]);
});

it('supports the complete forward workflow to closed', function () {
    $actor = User::factory()->create();
    $request = MaintenanceRequest::factory()->create([
        'status' => MaintenanceRequestStatus::from('new'),
    ]);

    foreach (['in_progress', 'done', 'closed'] as $status) {
        $this->actingAs($actor)
            ->patch(route('maintenance-requests.status.update', $request), [
                'status' => $status,
            ])
            ->assertSessionHas('success');

        $request->refresh();
    }

    expect($request->status)->toBe(MaintenanceRequestStatus::from('closed'));
});

it('rejects any transition after a request is closed', function () {
    $actor = User::factory()->create();
    $request = MaintenanceRequest::factory()->create([
        'status' => MaintenanceRequestStatus::from('closed'),
    ]);

    $this->actingAs($actor)
        ->patch(route('maintenance-requests.status.update', $request), [
            'status' => 'new',
        ])
        ->assertSessionHasErrors('status');

    $this->assertDatabaseHas('maintenance_requests', [
        'id' => $request->id,
        'status' => 'closed',
    ]);
});
