<?php

namespace Domain\Property\Application\Actions;

use Domain\Property\Application\Commands\ChangePropertyDocumentLifecycleCommand;
use Domain\Property\Models\PropertyDocument;
use Domain\Property\Services\PropertyMediaAdministrationService;

final class ChangePropertyDocumentLifecycleAction
{
    public function __construct(private PropertyMediaAdministrationService $service) {}

    public function execute(ChangePropertyDocumentLifecycleCommand $c): PropertyDocument
    {
        return $this->service->changeLifecycle($c->membership, $c->document, $c->lifecycle);
    }
}
