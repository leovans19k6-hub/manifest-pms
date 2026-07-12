<?php

namespace Domain\Property\Enums;

enum PropertyType: string
{
    case Villa = 'villa';
    case Homestay = 'homestay';
    case Resort = 'resort';
    case Hotel = 'hotel';
    case Apartment = 'apartment';
    case Other = 'other';
}
