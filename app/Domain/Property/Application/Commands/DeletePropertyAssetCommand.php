<?php

namespace Domain\Property\Application\Commands;

use Domain\Foundation\Models\OrganizationUser;
use Domain\Property\Models\PropertyAsset;

final readonly class DeletePropertyAssetCommand
{
    public function __construct(public OrganizationUser $membership, public PropertyAsset $asset) {}
}
