<?php

namespace Domain\Foundation\Services;

use Domain\Foundation\Enums\OrganizationMemberStatus;
use Domain\Foundation\Models\OrganizationUser;
use Domain\Foundation\Models\User;
use Domain\Foundation\Support\CurrentMembership;
use Domain\Foundation\Support\CurrentOrganization;

class MembershipResolver
{
    public function __construct(
        private CurrentOrganization $currentOrganization,
        private CurrentMembership $currentMembership,
    ) {}

    public function current(): ?OrganizationUser
    {
        return $this->currentMembership->get();
    }

    public function resolve(User $user): ?OrganizationUser
    {
        $organizationId = $this->currentOrganization->id();

        if ($organizationId === null) {
            $this->currentMembership->clear();

            return null;
        }

        $membership = OrganizationUser::query()
            ->where('user_id', $user->getAuthIdentifier())
            ->where('organization_id', $organizationId)
            ->where('status', OrganizationMemberStatus::Active->value)
            ->first();

        if ($membership === null) {
            $this->currentMembership->clear();

            return null;
        }

        $this->currentMembership->set($membership);

        return $membership;
    }

    public function clear(): void
    {
        $this->currentMembership->clear();
    }
}
