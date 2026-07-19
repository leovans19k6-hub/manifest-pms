<?php

namespace Domain\Housekeeping\Enums;

enum RoomCondition: string
{
    case Clean = 'clean';

    case Dirty = 'dirty';

    case Inspected = 'inspected';

    case OutOfService = 'out_of_service';
}