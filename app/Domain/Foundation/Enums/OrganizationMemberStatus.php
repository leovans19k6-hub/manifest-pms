<?php

namespace Domain\Foundation\Enums;

enum OrganizationMemberStatus: string
{
    case Active = 'active';
    case Invited = 'invited';
    case Suspended = 'suspended';
    case Left = 'left';
}
