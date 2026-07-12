<?php

namespace Domain\Property\Application\Commands;

use Domain\Foundation\Models\OrganizationUser;
use Domain\Property\Models\Property;

final readonly class UpdatePropertyCommand
{
    public function __construct(public OrganizationUser $membership, public Property $property, public array $input) {}
}
