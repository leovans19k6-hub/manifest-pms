<?php

namespace Domain\Inventory\Application\Commands;

use Domain\Foundation\Models\OrganizationUser;
use Domain\Inventory\Application\DTO\UnitData;
use Domain\Inventory\Models\Unit;

final readonly class UpdateUnitCommand
{
    public function __construct(
        public OrganizationUser $membership,
        public Unit $unit,
        public UnitData $input,
    ) {}
}
