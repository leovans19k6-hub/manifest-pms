<?php

namespace Domain\Property\Application\Actions;

use Domain\Property\Application\Commands\DeletePropertyDocumentCommand;
use Domain\Property\Services\PropertyMediaAdministrationService;

final class DeletePropertyDocumentAction
{
    public function __construct(private PropertyMediaAdministrationService $service) {}

    public function execute(DeletePropertyDocumentCommand $c): void
    {
        $this->service->delete($c->membership, $c->document, 'property.documents.delete');
    }
}
