<?php

namespace Domain\Foundation\Support;

use Domain\Foundation\Models\OrganizationUser;

class CurrentMembership
{
    private ?OrganizationUser $membership = null;

    public function set(OrganizationUser $membership): void
    {
        $this->membership = $membership;
    }

    public function get(): ?OrganizationUser
    {
        return $this->membership;
    }

    public function id(): ?string
    {
        return $this->membership?->id;
    }

    public function clear(): void
    {
        $this->membership = null;
    }
}
