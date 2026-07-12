<?php

namespace Domain\Property\Application\Actions;

use Domain\Property\Application\Commands\DeletePropertyAssetCommand;
use Domain\Property\Services\PropertyMediaAdministrationService;

final class DeletePropertyAssetAction
{
    public function __construct(private PropertyMediaAdministrationService $service) {}

    public function execute(DeletePropertyAssetCommand $c): void
    {
        $this->service->delete($c->membership, $c->asset, 'property.media.delete');
    }
}
