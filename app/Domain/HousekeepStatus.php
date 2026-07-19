<?php

namespace Domain\Housekeeping\Enums;

enum HousekeepingStatus: string
{
    case Pending = 'pending';

    case Assigned = 'assigned';

    case InProgress = 'in_progress';

    case Completed = 'completed';

    case Verified = 'verified';

    case Cancelled = 'cancelled';
}