<?php

namespace App\Models;

use App\Enums\EquipmentStatus;
use Database\Factories\EquipmentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Equipment extends Model
{
    /** @use HasFactory<EquipmentFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'location',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => EquipmentStatus::class,
        ];
    }

    public function maintenanceRequests(): HasMany
    {
        return $this->hasMany(MaintenanceRequest::class);
    }
}
