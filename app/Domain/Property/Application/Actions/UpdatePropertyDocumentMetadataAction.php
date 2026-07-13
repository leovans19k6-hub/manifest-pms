<?php

namespace Domain\Property\Application\Actions;

use Domain\Property\Application\Commands\UpdatePropertyDocumentMetadataCommand;
use Domain\Property\Application\Validation\PropertyMediaAdministrationValidator;
use Domain\Property\Models\PropertyDocument;
use Domain\Property\Services\PropertyMediaAdministrationService;

final class UpdatePropertyDocumentMetadataAction
{
    public function __construct(private PropertyMediaAdministrationValidator $validator, private PropertyMediaAdministrationService $service) {}

    public function execute(UpdatePropertyDocumentMetadataCommand $c): PropertyDocument
    {
        $this->validator->metadata($c->data);

        return $this->service->updateMetadata($c->membership, $c->document, 'property.documents.update', $c->data->metadata);
    }
}
