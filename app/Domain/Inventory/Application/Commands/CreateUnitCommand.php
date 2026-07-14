<?php

namespace Domain\Inventory\Application\Commands;

use Domain\Foundation\Models\OrganizationUser;
use Domain\Inventory\Application\DTO\UnitData;
use Domain\Property\Models\Property;

final readonly class CreateUnitCommand
{
    public function __construct(
        public OrganizationUser $membership,
        public Property $property,
        public UnitData $input,
    ) {}
}
