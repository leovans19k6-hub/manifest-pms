<?php

namespace Domain\Property\Application\Commands;

use Domain\Foundation\Models\OrganizationUser;
use Domain\Property\Application\DTO\UploadFileData;
use Domain\Property\Enums\PropertyDocumentCategory;
use Domain\Property\Models\Property;

final readonly class UploadPropertyDocumentCommand
{
    public function __construct(public OrganizationUser $membership, public Property $property, public PropertyDocumentCategory $category, public UploadFileData $file) {}
}
