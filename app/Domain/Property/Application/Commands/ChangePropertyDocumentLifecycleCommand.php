<?php

namespace Domain\Property\Application\Commands;

use Domain\Foundation\Models\OrganizationUser;
use Domain\Property\Enums\PropertyDocumentLifecycle;
use Domain\Property\Models\PropertyDocument;

final readonly class ChangePropertyDocumentLifecycleCommand
{
    public function __construct(public OrganizationUser $membership, public PropertyDocument $document, public PropertyDocumentLifecycle $lifecycle) {}
}
