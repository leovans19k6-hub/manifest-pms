<?php

namespace Domain\Property\Enums;

enum PropertyAssetKind: string
{
    case Image = 'image';
    case Video = 'video';
    case FloorPlan = 'floor_plan';
}
