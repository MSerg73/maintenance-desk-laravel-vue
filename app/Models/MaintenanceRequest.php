<?php

namespace App\Models;

use App\Enums\MaintenanceRequestStatus;
use App\Enums\RequestPriority;
use Database\Factories\MaintenanceRequestFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaintenanceRequest extends Model
{
    /** @use HasFactory<MaintenanceRequestFactory> */
    use HasFactory;

    protected $fillable = [
        'equipment_id',
        'created_by',
        'assigned_to',
        'title',
        'description',
        'priority',
        'status',
        'closed_at',
    ];

    protected function casts(): array
    {
        return [
            'priority' => RequestPriority::class,
            'status' => MaintenanceRequestStatus::class,
            'closed_at' => 'datetime',
        ];
    }

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
