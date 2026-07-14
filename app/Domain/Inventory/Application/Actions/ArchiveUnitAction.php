<?php

namespace Domain\Inventory\Application\Actions;

use Domain\Inventory\Application\Commands\ArchiveUnitCommand;
use Domain\Inventory\Services\UnitService;

final class ArchiveUnitAction
{
    public function __construct(private UnitService $units) {}

    public function execute(ArchiveUnitCommand $command): void
    {
        $this->units->archive(
            $command->membership,
            $command->unit,
        );
    }
}
