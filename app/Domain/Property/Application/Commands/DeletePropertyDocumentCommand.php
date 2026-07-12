<?php

namespace Domain\Property\Application\Commands;

use Domain\Foundation\Models\OrganizationUser;
use Domain\Property\Models\PropertyDocument;

final readonly class DeletePropertyDocumentCommand
{
    public function __construct(public OrganizationUser $membership, public PropertyDocument $document) {}
}
