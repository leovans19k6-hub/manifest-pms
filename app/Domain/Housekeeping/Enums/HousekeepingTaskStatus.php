<?php

namespace Domain\Housekeeping\Enums;

enum HousekeepingTaskStatus: string
{
    case Pending = 'pending';
    case Assigned = 'assigned';
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
}