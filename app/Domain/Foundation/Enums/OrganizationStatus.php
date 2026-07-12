<?php

namespace Domain\Foundation\Enums;

enum OrganizationStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
    case Archived = 'archived';
}
