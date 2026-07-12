<?php

namespace Domain\Foundation\Enums;

enum PermissionGroup: string
{
    case Foundation = 'foundation';
    case Property = 'property';
    case Inventory = 'inventory';
    case Reservation = 'reservation';
}
