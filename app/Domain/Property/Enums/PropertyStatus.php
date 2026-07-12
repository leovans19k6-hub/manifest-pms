<?php

namespace Domain\Property\Enums;

enum PropertyStatus: string
{
    case Draft = 'draft';
    case Active = 'active';
    case Inactive = 'inactive';
    case Archived = 'archived';
}
