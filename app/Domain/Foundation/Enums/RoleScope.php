<?php

namespace Domain\Foundation\Enums;

enum RoleScope: string
{
    case System = 'system';
    case Organization = 'organization';
}
