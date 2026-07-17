<?php

namespace Domain\Availability\Enums;

enum AvailabilityStatus: string
{
    case Available = 'available';

    case Reserved = 'reserved';

    case CheckedIn = 'checked_in';
}