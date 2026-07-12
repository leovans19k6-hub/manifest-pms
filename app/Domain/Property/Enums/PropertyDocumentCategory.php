<?php

namespace Domain\Property\Enums;

enum PropertyDocumentCategory: string
{
    case Legal = 'legal';
    case Policy = 'policy';
    case Brochure = 'brochure';
    case Other = 'other';
}
