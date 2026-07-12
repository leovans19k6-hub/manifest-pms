<?php

namespace Domain\Property\Application\Actions;

use Domain\Property\Application\Commands\ArchivePropertyCommand;
use Domain\Property\Services\PropertyService;

class ArchivePropertyAction
{
    public function __construct(private PropertyService $properties) {}

    public function execute(ArchivePropertyCommand $command): void
    {
        $this->properties->archive($command->membership, $command->property);
    }
}
