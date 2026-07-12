<?php

namespace Domain\Property\Application\Actions;

use Domain\Property\Application\Commands\UpdatePropertyCommand;
use Domain\Property\Application\Validation\PropertyValidator;
use Domain\Property\Models\Property;
use Domain\Property\Services\PropertyService;

class UpdatePropertyAction
{
    public function __construct(private PropertyValidator $validator, private PropertyService $properties) {}

    public function execute(UpdatePropertyCommand $command): Property
    {
        $data = $this->validator->validate($command->input, $command->property);

        return $this->properties->update($command->membership, $command->property, $data->toArray());
    }
}
