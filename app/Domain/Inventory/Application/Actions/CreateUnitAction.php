<?php

namespace Domain\Inventory\Application\Actions;

use Domain\Inventory\Application\Commands\CreateUnitCommand;
use Domain\Inventory\Application\Validation\UnitValidator;
use Domain\Inventory\Models\Unit;
use Domain\Inventory\Services\UnitService;

final class CreateUnitAction
{
    public function __construct(
        private UnitValidator $validator,
        private UnitService $units,
    ) {}

    public function execute(CreateUnitCommand $command): Unit
    {
        $data = $this->validator->validate(
            $command->input,
            $command->property->organization_id,
            $command->property->id,
        );

        return $this->units->create(
            $command->membership,
            $command->property,
            $data->toArray(),
        );
    }
}
