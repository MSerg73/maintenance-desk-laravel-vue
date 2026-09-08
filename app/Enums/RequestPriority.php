<?php

namespace App\Enums;

enum RequestPriority: string
{
    case Low = 'low';
    case Normal = 'normal';
    case High = 'high';
    case Critical = 'critical';

    public function label(): string
    {
        return ucwords(str_replace('_', ' ', $this->value));
    }
}
