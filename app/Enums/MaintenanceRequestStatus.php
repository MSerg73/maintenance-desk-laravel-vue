<?php

namespace App\Enums;

enum MaintenanceRequestStatus: string
{
    case New = 'new';
    case InProgress = 'in_progress';
    case Done = 'done';
    case Closed = 'closed';

    public function label(): string
    {
        return ucwords(str_replace('_', ' ', $this->value));
    }

    public function next(): ?self
    {
        return match ($this->value) {
            'new' => self::from('in_progress'),
            'in_progress' => self::from('done'),
            'done' => self::from('closed'),
            'closed' => null,
        };
    }

    public function canTransitionTo(self $target): bool
    {
        return $this->next() === $target;
    }
}
