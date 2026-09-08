<?php

namespace App\Enums;

enum EquipmentStatus: string
{
    case Active = 'active';
    case Maintenance = 'maintenance';
    case OutOfService = 'out_of_service';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Maintenance => 'Maintenance',
            self::OutOfService => 'Out of service',
        };
    }
}
