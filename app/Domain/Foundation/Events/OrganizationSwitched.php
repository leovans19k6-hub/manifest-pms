<?php

namespace Domain\Foundation\Events;

use Domain\Foundation\Models\Organization;
use Domain\Foundation\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrganizationSwitched
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public User $user,
        public ?Organization $previousOrganization,
        public Organization $organization,
    ) {}
}
