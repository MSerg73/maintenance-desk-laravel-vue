<?php

namespace App\Enums;

enum UserRole: string
{
    case Operator = 'operator';
    case Engineer = 'engineer';
    case Admin = 'admin';

    public function label(): string
    {
        return match ($this) {
            self::Operator => 'Operator',
            self::Engineer => 'Engineer',
            self::Admin => 'Admin',
        };
    }
}
