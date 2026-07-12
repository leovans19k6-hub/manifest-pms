<?php

namespace Domain\Property\Application\Commands;

use Domain\Foundation\Models\OrganizationUser;

final readonly class CreatePropertyCommand
{
    public function __construct(public OrganizationUser $membership, public array $input) {}
}
