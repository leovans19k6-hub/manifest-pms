<?php

namespace Domain\Property\Application\Commands;

use Domain\Foundation\Models\OrganizationUser;
use Domain\Property\Application\DTO\MediaMetadataData;
use Domain\Property\Models\PropertyDocument;

final readonly class UpdatePropertyDocumentMetadataCommand
{
    public function __construct(public OrganizationUser $membership, public PropertyDocument $document, public MediaMetadataData $data) {}
}
