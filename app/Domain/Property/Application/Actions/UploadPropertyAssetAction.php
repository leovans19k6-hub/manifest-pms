<?php

namespace Domain\Property\Application\Actions;

use Domain\Property\Application\Commands\UploadPropertyAssetCommand;
use Domain\Property\Application\Validation\PropertyMediaValidator;
use Domain\Property\Models\PropertyAsset;
use Domain\Property\Services\PropertyMediaService;

final class UploadPropertyAssetAction
{
    public function __construct(private PropertyMediaValidator $validator, private PropertyMediaService $service) {}

    public function execute(UploadPropertyAssetCommand $c): PropertyAsset
    {
        $this->validator->asset($c->file);

        return $this->service->store($c->membership, $c->property, 'property.media.create', PropertyAsset::class, 'kind', $c->kind->value, $c->file);
    }
}
