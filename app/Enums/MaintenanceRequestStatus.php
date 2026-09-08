<?php

namespace App\Enums;

enum MaintenanceRequestStatus: string
{
    case New = 'new';
    case InProgress = 'in_progress';
    case Done = 'done';
    case Closed = 'closed';
}
