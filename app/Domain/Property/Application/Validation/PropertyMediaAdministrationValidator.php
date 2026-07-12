<?php

namespace Domain\Property\Application\Validation;

use Domain\Property\Application\DTO\AssetOrderData;
use Domain\Property\Application\DTO\MediaMetadataData;
use Illuminate\Support\Facades\Validator;

final class PropertyMediaAdministrationValidator
{
    public function metadata(MediaMetadataData $data): void
    {
        Validator::make(['metadata' => $data->metadata], ['metadata' => ['nullable', 'array']])->validate();
    }

    public function order(AssetOrderData $data): void
    {
        Validator::make(['asset_ids' => $data->assetIds], [
            'asset_ids' => ['required', 'array', 'min:1'],
            'asset_ids.*' => ['required', 'string', 'distinct'],
        ])->validate();
    }
}
