<?php

namespace Domain\Foundation\Services;

use Domain\Foundation\Enums\OrganizationMemberStatus;
use Domain\Foundation\Enums\RoleStatus;
use Domain\Foundation\Models\OrganizationUser;
use Domain\Foundation\Support\CurrentMembership;
use Domain\Foundation\Support\CurrentOrganization;

class AuthorizationService
{
    public const SUPER_ADMIN_CODE = 'SUPER_ADMIN';

    public function __construct(
        private CurrentOrganization $currentOrganization,
        private CurrentMembership $currentMembership,
        private PermissionResolver $resolver,
    ) {}

    public function canCurrent(string $permission): bool
    {
        $membership = $this->currentMembership->get();

        return $membership !== null && $this->can($membership, $permission);
    }

    public function can(OrganizationUser $membership, string $permission): bool
    {
        if (! $this->isCurrentActiveMembership($membership)) {
            return false;
        }

        if ($this->isSuperAdmin($membership)) {
            return true;
        }

        return in_array($permission, $this->resolver->resolve($membership), true);
    }

    public function isSuperAdmin(OrganizationUser $membership): bool
    {
        if (! $this->isCurrentActiveMembership($membership)) {
            return false;
        }

        return $membership->roles()
            ->whereNull('roles.organization_id')
            ->where('roles.is_system', true)
            ->where('roles.code', self::SUPER_ADMIN_CODE)
            ->where('roles.status', RoleStatus::Active->value)
            ->exists();
    }

    private function isCurrentActiveMembership(OrganizationUser $membership): bool
    {
        return $this->currentOrganization->id() !== null
            && $membership->organization_id === $this->currentOrganization->id()
            && $membership->status === OrganizationMemberStatus::Active;
    }
}
