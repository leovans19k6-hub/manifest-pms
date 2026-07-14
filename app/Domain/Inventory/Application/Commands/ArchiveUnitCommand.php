<?php

namespace Domain\Inventory\Application\Commands;

use Domain\Foundation\Models\OrganizationUser;
use Domain\Inventory\Models\Unit;

final readonly class ArchiveUnitCommand
{
    public function __construct(
        public OrganizationUser $membership,
        public Unit $unit,
    ) {}
}
