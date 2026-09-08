<?php

namespace App\Http\Controllers;

use App\Enums\EquipmentStatus;
use App\Enums\MaintenanceRequestStatus;
use App\Enums\RequestPriority;
use App\Models\Equipment;
use App\Models\MaintenanceRequest;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        $new = MaintenanceRequestStatus::from('new')->value;
        $inProgress = MaintenanceRequestStatus::from('in_progress')->value;
        $done = MaintenanceRequestStatus::from('done')->value;
        $closed = MaintenanceRequestStatus::from('closed')->value;

        $recentRequests = MaintenanceRequest::query()
            ->with([
                'equipment:id,name,code',
                'assignee:id,name',
            ])
            ->latest('created_at')
            ->latest('id')
            ->limit(5)
            ->get()
            ->map(fn (MaintenanceRequest $request): array => [
                'id' => $request->id,
                'title' => $request->title,
                'status' => $request->status->value,
                'status_label' => $request->status->label(),
                'priority' => $request->priority->value,
                'priority_label' => $request->priority->label(),
                'created_at' => $request->created_at->toIso8601String(),
                'equipment' => [
                    'name' => $request->equipment->name,
                    'code' => $request->equipment->code,
                ],
                'assignee' => $request->assignee?->name,
            ])
            ->values()
            ->all();

        return Inertia::render('Dashboard', [
            'stats' => [
                'equipment' => [
                    'total' => Equipment::query()->count(),
                    'active' => Equipment::query()
                        ->where('status', EquipmentStatus::Active->value)
                        ->count(),
                    'maintenance' => Equipment::query()
                        ->where('status', EquipmentStatus::Maintenance->value)
                        ->count(),
                    'out_of_service' => Equipment::query()
                        ->where('status', EquipmentStatus::OutOfService->value)
                        ->count(),
                ],
                'requests' => [
                    'new' => MaintenanceRequest::query()->where('status', $new)->count(),
                    'in_progress' => MaintenanceRequest::query()->where('status', $inProgress)->count(),
                    'done' => MaintenanceRequest::query()->where('status', $done)->count(),
                    'closed' => MaintenanceRequest::query()->where('status', $closed)->count(),
                    'high_or_critical_open' => MaintenanceRequest::query()
                        ->whereIn('status', [$new, $inProgress])
                        ->whereIn('priority', [
                            RequestPriority::from('high')->value,
                            RequestPriority::from('critical')->value,
                        ])
                        ->count(),
                ],
            ],
            'recentRequests' => $recentRequests,
        ]);
    }
}
