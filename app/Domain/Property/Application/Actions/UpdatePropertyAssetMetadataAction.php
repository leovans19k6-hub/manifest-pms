<?php

namespace Domain\Property\Application\Actions;

use Domain\Property\Application\Commands\UpdatePropertyAssetMetadataCommand;
use Domain\Property\Application\Validation\PropertyMediaAdministrationValidator;
use Domain\Property\Models\PropertyAsset;
use Domain\Property\Services\PropertyMediaAdministrationService;

final class UpdatePropertyAssetMetadataAction
{
    public function __construct(private PropertyMediaAdministrationValidator $validator, private PropertyMediaAdministrationService $service) {}

    public function execute(UpdatePropertyAssetMetadataCommand $c): PropertyAsset
    {
        $this->validator->metadata($c->data);

        return $this->service->updateMetadata($c->membership, $c->asset, 'property.media.update', $c->data->metadata);
    }
}
