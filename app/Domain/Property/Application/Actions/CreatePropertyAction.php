<?php

namespace Domain\Property\Application\Actions;

use Domain\Property\Application\Commands\CreatePropertyCommand;
use Domain\Property\Application\Validation\PropertyValidator;
use Domain\Property\Models\Property;
use Domain\Property\Services\PropertyService;

class CreatePropertyAction
{
    public function __construct(private PropertyValidator $validator, private PropertyService $properties) {}

    public function execute(CreatePropertyCommand $command): Property
    {
        $data = $this->validator->validate($command->input);

        return $this->properties->create($command->membership, $data->toArray());
    }
}
