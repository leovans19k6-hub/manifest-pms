<?php

namespace Domain\Reservation\Enums;

enum ReservationSource: string
{
    case WalkIn = 'walk_in';
    case Website = 'website';
    case Phone = 'phone';
    case Facebook = 'facebook';
    case Booking = 'booking';
    case Airbnb = 'airbnb';
    case Agoda = 'agoda';
    case Traveloka = 'traveloka';
    case Other = 'other';
}
