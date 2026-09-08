<?php

namespace App\Http\Controllers;

use App\Enums\MaintenanceRequestStatus;
use App\Enums\RequestPriority;
use App\Http\Requests\StoreMaintenanceRequestRequest;
use App\Http\Requests\UpdateMaintenanceRequestRequest;
use App\Http\Requests\UpdateMaintenanceRequestStatusRequest;
use App\Models\Equipment;
use App\Models\MaintenanceRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MaintenanceRequestController extends Controller
{
    public function index(Request $request): Response
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'string'],
            'priority' => ['nullable', 'string'],
        ]);

        $search = trim((string) ($filters['q'] ?? ''));
        $status = (string) ($filters['status'] ?? '');
        $priority = (string) ($filters['priority'] ?? '');

        if ($status !== '' && MaintenanceRequestStatus::tryFrom($status) === null) {
            $status = '';
        }

        if ($priority !== '' && RequestPriority::tryFrom($priority) === null) {
            $priority = '';
        }

        $requests = MaintenanceRequest::query()
            ->with([
                'equipment:id,name,code',
                'creator:id,name',
                'assignee:id,name',
            ])
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query
                        ->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhereHas('equipment', function (Builder $equipmentQuery) use ($search): void {
                            $equipmentQuery
                                ->where('name', 'like', "%{$search}%")
                                ->orWhere('code', 'like', "%{$search}%");
                        });
                });
            })
            ->when($status !== '', fn (Builder $query) => $query->where('status', $status))
            ->when($priority !== '', fn (Builder $query) => $query->where('priority', $priority))
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        $requestData = $requests->getCollection()
            ->map(function (MaintenanceRequest $item): array {
                $next = $item->status->next();

                return [
                    'id' => $item->id,
                    'title' => $item->title,
                    'description' => $item->description,
                    'priority' => $item->priority->value,
                    'priority_label' => $item->priority->label(),
                    'status' => $item->status->value,
                    'status_label' => $item->status->label(),
                    'created_at' => $item->created_at->toIso8601String(),
                    'equipment' => [
                        'id' => $item->equipment->id,
                        'name' => $item->equipment->name,
                        'code' => $item->equipment->code,
                    ],
                    'creator' => [
                        'id' => $item->creator->id,
                        'name' => $item->creator->name,
                    ],
                    'assignee' => $item->assignee === null ? null : [
                        'id' => $item->assignee->id,
                        'name' => $item->assignee->name,
                    ],
                    'next_status' => $next === null ? null : [
                        'value' => $next->value,
                        'label' => $next->label(),
                    ],
                ];
            })
            ->values()
            ->all();

        $payload = [
            'data' => $requestData,
            'current_page' => $requests->currentPage(),
            'from' => $requests->firstItem(),
            'last_page' => $requests->lastPage(),
            'links' => $requests->linkCollection()->all(),
            'per_page' => $requests->perPage(),
            'to' => $requests->lastItem(),
            'total' => $requests->total(),
        ];

        return Inertia::render('maintenance-requests/Index', [
            'requests' => $payload,
            'filters' => [
                'q' => $search,
                'status' => $status,
                'priority' => $priority,
            ],
            'statuses' => $this->statusOptions(),
            'priorities' => $this->priorityOptions(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('maintenance-requests/Create', [
            ...$this->formOptions(),
        ]);
    }

    public function store(StoreMaintenanceRequestRequest $request): RedirectResponse
    {
        $user = $request->user();
        abort_if($user === null, 403);

        MaintenanceRequest::query()->create([
            ...$request->validated(),
            'status' => MaintenanceRequestStatus::from('new')->value,
            'created_by' => (int) $user->getKey(),
        ]);

        return to_route('maintenance-requests.index')
            ->with('success', 'Maintenance request created.');
    }

    public function edit(MaintenanceRequest $maintenanceRequest): Response
    {
        return Inertia::render('maintenance-requests/Edit', [
            'maintenanceRequest' => [
                'id' => $maintenanceRequest->id,
                'equipment_id' => $maintenanceRequest->equipment_id,
                'title' => $maintenanceRequest->title,
                'description' => $maintenanceRequest->description,
                'priority' => $maintenanceRequest->priority->value,
                'assigned_to' => $maintenanceRequest->assigned_to,
                'status' => $maintenanceRequest->status->value,
                'status_label' => $maintenanceRequest->status->label(),
            ],
            ...$this->formOptions(),
        ]);
    }

    public function update(
        UpdateMaintenanceRequestRequest $request,
        MaintenanceRequest $maintenanceRequest,
    ): RedirectResponse {
        $maintenanceRequest->update($request->validated());

        return to_route('maintenance-requests.index')
            ->with('success', 'Maintenance request updated.');
    }

    public function updateStatus(
        UpdateMaintenanceRequestStatusRequest $request,
        MaintenanceRequest $maintenanceRequest,
    ): RedirectResponse {
        $target = MaintenanceRequestStatus::from((string) $request->validated('status'));

        if (! $maintenanceRequest->status->canTransitionTo($target)) {
            return back()->withErrors([
                'status' => sprintf(
                    'Invalid status transition: %s -> %s.',
                    $maintenanceRequest->status->label(),
                    $target->label(),
                ),
            ]);
        }

        $maintenanceRequest->update([
            'status' => $target->value,
        ]);

        return back()->with('success', sprintf(
            'Request moved to %s.',
            $target->label(),
        ));
    }

    /**
     * @return array<string, mixed>
     */
    private function formOptions(): array
    {
        return [
            'equipmentOptions' => Equipment::query()
                ->orderBy('name')
                ->orderBy('code')
                ->get(['id', 'name', 'code'])
                ->map(fn (Equipment $equipment): array => [
                    'id' => $equipment->id,
                    'name' => $equipment->name,
                    'code' => $equipment->code,
                ])
                ->values()
                ->all(),
            'userOptions' => User::query()
                ->orderBy('name')
                ->get(['id', 'name', 'email'])
                ->map(fn (User $user): array => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ])
                ->values()
                ->all(),
            'priorities' => $this->priorityOptions(),
        ];
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    private function priorityOptions(): array
    {
        return array_map(
            fn (RequestPriority $priority): array => [
                'value' => $priority->value,
                'label' => $priority->label(),
            ],
            RequestPriority::cases(),
        );
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    private function statusOptions(): array
    {
        return array_map(
            fn (MaintenanceRequestStatus $status): array => [
                'value' => $status->value,
                'label' => $status->label(),
            ],
            MaintenanceRequestStatus::cases(),
        );
    }
}
