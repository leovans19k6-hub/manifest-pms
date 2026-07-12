<?php

namespace Domain\Property\Application\Actions;

use Domain\Property\Application\Commands\CreatePropertyDocumentDownloadCommand;
use Domain\Property\Application\DTO\PrivateDownloadData;
use Domain\Property\Services\PropertyPrivateDownloadService;

final class CreatePropertyDocumentDownloadAction
{
    public function __construct(private PropertyPrivateDownloadService $service) {}

    public function execute(CreatePropertyDocumentDownloadCommand $c): PrivateDownloadData
    {
        return $this->service->create($c->membership, $c->document, 'property.documents.view');
    }
}
