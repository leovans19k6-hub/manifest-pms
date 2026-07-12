<?php

namespace Domain\Property\Application\Commands;

use Domain\Foundation\Models\OrganizationUser;
use Domain\Property\Application\DTO\MediaMetadataData;
use Domain\Property\Models\PropertyAsset;

final readonly class UpdatePropertyAssetMetadataCommand
{
    public function __construct(public OrganizationUser $membership, public PropertyAsset $asset, public MediaMetadataData $data) {}
}
