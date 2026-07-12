<?php

namespace Domain\Property\Application\Commands;

use Domain\Foundation\Models\OrganizationUser;
use Domain\Property\Application\DTO\AssetOrderData;
use Domain\Property\Models\Property;

final readonly class ReorderPropertyAssetsCommand
{
    public function __construct(public OrganizationUser $membership, public Property $property, public AssetOrderData $order) {}
}
