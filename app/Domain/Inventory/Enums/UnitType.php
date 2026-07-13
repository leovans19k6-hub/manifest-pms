<?php

namespace Domain\Inventory\Enums;

enum UnitType: string
{
    case Room = 'room';
    case Villa = 'villa';
    case House = 'house';
    case Apartment = 'apartment';
    case Bed = 'bed';
    case Other = 'other';
}
