<?php

namespace Domain\Inventory\Enums;

enum UnitStatus: string
{
    case Draft = 'draft';
    case Active = 'active';
    case Inactive = 'inactive';
    case Maintenance = 'maintenance';
    case Archived = 'archived';
}
