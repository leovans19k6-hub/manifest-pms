<?php

namespace Domain\Property\Application\Commands;

use Domain\Foundation\Models\OrganizationUser;
use Domain\Property\Application\DTO\UploadFileData;
use Domain\Property\Enums\PropertyAssetKind;
use Domain\Property\Models\Property;

final readonly class UploadPropertyAssetCommand
{
    public function __construct(public OrganizationUser $membership, public Property $property, public PropertyAssetKind $kind, public UploadFileData $file) {}
}
