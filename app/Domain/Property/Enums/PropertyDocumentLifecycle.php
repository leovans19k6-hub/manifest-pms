<?php

namespace Domain\Property\Enums;

enum PropertyDocumentLifecycle: string
{
    case Active = 'active';
    case Archived = 'archived';
}
