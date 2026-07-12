<?php

namespace Domain\Property\Application\Actions;

use Domain\Property\Application\Commands\ReorderPropertyAssetsCommand;
use Domain\Property\Application\Validation\PropertyMediaAdministrationValidator;
use Domain\Property\Services\PropertyMediaAdministrationService;

final class ReorderPropertyAssetsAction
{
    public function __construct(private PropertyMediaAdministrationValidator $validator, private PropertyMediaAdministrationService $service) {}

    public function execute(ReorderPropertyAssetsCommand $c): void
    {
        $this->validator->order($c->order);
        $this->service->reorder($c->membership, $c->property, $c->order);
    }
}
