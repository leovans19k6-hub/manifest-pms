<?php

namespace Domain\Inventory\Application\Actions;

use Domain\Inventory\Application\Commands\UpdateUnitCommand;
use Domain\Inventory\Application\Validation\UnitValidator;
use Domain\Inventory\Models\Unit;
use Domain\Inventory\Services\UnitService;

final class UpdateUnitAction
{
    public function __construct(
        private UnitValidator $validator,
        private UnitService $units,
    ) {}

    public function execute(UpdateUnitCommand $command): Unit
    {
        $data = $this->validator->validate(
            $command->input,
            $command->unit->organization_id,
            $command->unit->property_id,
            $command->unit,
        );

        return $this->units->update(
            $command->membership,
            $command->unit,
            $data->toArray(),
        );
    }
}
