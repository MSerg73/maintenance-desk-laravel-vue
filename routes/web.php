<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EquipmentController;
use App\Http\Controllers\MaintenanceRequestController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');
    Route::resource('equipment', EquipmentController::class)->except(['show']);
    Route::patch(
        'maintenance-requests/{maintenanceRequest}/status',
        [MaintenanceRequestController::class, 'updateStatus'],
    )->name('maintenance-requests.status.update');

    Route::resource('maintenance-requests', MaintenanceRequestController::class)
        ->except(['show', 'destroy']);
});

require __DIR__.'/settings.php';
