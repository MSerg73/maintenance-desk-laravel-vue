<?php

namespace App\Http\Controllers;

use App\Enums\EquipmentStatus;
use App\Http\Requests\StoreEquipmentRequest;
use App\Http\Requests\UpdateEquipmentRequest;
use App\Models\Equipment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class EquipmentController extends Controller
{
    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', Equipment::class);

        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'string'],
        ]);

        $search = trim((string) ($filters['q'] ?? ''));
        $status = (string) ($filters['status'] ?? '');

        if ($status !== '' && EquipmentStatus::tryFrom($status) === null) {
            $status = '';
        }

        $equipment = Equipment::query()
            ->withCount('maintenanceRequests')
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('location', 'like', "%{$search}%");
                });
            })
            ->when($status !== '', fn (Builder $query) => $query->where('status', $status))
            ->orderBy('name')
            ->orderBy('code')
            ->paginate(10)
            ->withQueryString();

        $equipmentData = $equipment->getCollection()
            ->map(
                fn (Equipment $item): array => [
                    'id' => $item->id,
                    'name' => $item->name,
                    'code' => $item->code,
                    'location' => $item->location,
                    'status' => $item->status->value,
                    'status_label' => $item->status->label(),
                    'maintenance_requests_count' => $item->maintenance_requests_count,
                ],
            )
            ->values()
            ->all();

        $equipmentPayload = [
            'data' => $equipmentData,
            'current_page' => $equipment->currentPage(),
            'from' => $equipment->firstItem(),
            'last_page' => $equipment->lastPage(),
            'links' => $equipment->linkCollection()->all(),
            'per_page' => $equipment->perPage(),
            'to' => $equipment->lastItem(),
            'total' => $equipment->total(),
        ];

        return Inertia::render('equipment/Index', [
            'equipment' => $equipmentPayload,
            'filters' => [
                'q' => $search,
                'status' => $status,
            ],
            'statuses' => $this->statuses(),
            'permissions' => [
                'can_manage' => Gate::allows('create', Equipment::class),
            ],
        ]);
    }

    public function create(): Response
    {
        Gate::authorize('create', Equipment::class);

        return Inertia::render('equipment/Create', [
            'statuses' => $this->statuses(),
        ]);
    }

    public function store(StoreEquipmentRequest $request): RedirectResponse
    {
        Gate::authorize('create', Equipment::class);

        Equipment::query()->create($request->validated());

        return to_route('equipment.index')
            ->with('success', 'Equipment created successfully.');
    }

    public function edit(Equipment $equipment): Response
    {
        Gate::authorize('update', $equipment);

        return Inertia::render('equipment/Edit', [
            'equipment' => [
                'id' => $equipment->id,
                'name' => $equipment->name,
                'code' => $equipment->code,
                'location' => $equipment->location,
                'status' => $equipment->status->value,
            ],
            'statuses' => $this->statuses(),
        ]);
    }

    public function update(UpdateEquipmentRequest $request, Equipment $equipment): RedirectResponse
    {
        Gate::authorize('update', $equipment);

        $equipment->update($request->validated());

        return to_route('equipment.index')
            ->with('success', 'Equipment updated successfully.');
    }

    public function destroy(Equipment $equipment): RedirectResponse
    {
        Gate::authorize('delete', $equipment);

        if ($equipment->maintenanceRequests()->exists()) {
            return back()->with('error', 'Equipment with maintenance requests cannot be deleted.');
        }

        $equipment->delete();

        return to_route('equipment.index')
            ->with('success', 'Equipment deleted successfully.');
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    private function statuses(): array
    {
        return array_map(
            fn (EquipmentStatus $status): array => [
                'value' => $status->value,
                'label' => $status->label(),
            ],
            EquipmentStatus::cases(),
        );
    }
}
