<?php

use App\Enums\EquipmentStatus;
use App\Enums\MaintenanceRequestStatus;
use App\Enums\RequestPriority;
use App\Models\Equipment;
use App\Models\MaintenanceRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('creates equipment with a typed status', function () {
    $equipment = Equipment::factory()->create([
        'status' => EquipmentStatus::Maintenance,
    ]);

    expect($equipment->status)
        ->toBeInstanceOf(EquipmentStatus::class)
        ->toBe(EquipmentStatus::Maintenance);
});

it('creates a maintenance request with typed state and relationships', function () {
    $equipment = Equipment::factory()->create();
    $creator = User::factory()->create();
    $assignee = User::factory()->create();

    $request = MaintenanceRequest::factory()->create([
        'equipment_id' => $equipment->id,
        'created_by' => $creator->id,
        'assigned_to' => $assignee->id,
        'priority' => RequestPriority::Critical,
        'status' => MaintenanceRequestStatus::InProgress,
    ]);

    expect($request->priority)
        ->toBe(RequestPriority::Critical)
        ->and($request->status)
        ->toBe(MaintenanceRequestStatus::InProgress)
        ->and($request->equipment->is($equipment))
        ->toBeTrue()
        ->and($request->creator->is($creator))
        ->toBeTrue()
        ->and($request->assignee->is($assignee))
        ->toBeTrue();
});
