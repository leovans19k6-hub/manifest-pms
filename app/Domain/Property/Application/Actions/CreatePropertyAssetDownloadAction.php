<?php

namespace Domain\Property\Application\Actions;

use Domain\Property\Application\Commands\CreatePropertyAssetDownloadCommand;
use Domain\Property\Application\DTO\PrivateDownloadData;
use Domain\Property\Services\PropertyPrivateDownloadService;

final class CreatePropertyAssetDownloadAction
{
    public function __construct(private PropertyPrivateDownloadService $service) {}

    public function execute(CreatePropertyAssetDownloadCommand $c): PrivateDownloadData
    {
        return $this->service->create($c->membership, $c->asset, 'property.media.view');
    }
}
