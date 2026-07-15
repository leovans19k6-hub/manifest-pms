<?php

namespace Domain\Reservation\Enums;

enum ReservationStatus: string
{
    case Reserved = 'reserved';
    case Confirmed = 'confirmed';
    case CheckedIn = 'checked_in';
    case CheckedOut = 'checked_out';
    case Cancelled = 'cancelled';
    case NoShow = 'no_show';
}
