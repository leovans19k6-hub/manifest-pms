<?php

namespace Domain\Foundation\Services;

use Domain\Foundation\Enums\OrganizationMemberStatus;
use Domain\Foundation\Events\OrganizationSwitched;
use Domain\Foundation\Exceptions\OrganizationContextException;
use Domain\Foundation\Models\Organization;
use Domain\Foundation\Models\User;
use Domain\Foundation\Support\CurrentOrganization;
use Illuminate\Contracts\Session\Session;

class OrganizationContextService
{
    public const SESSION_KEY = 'current_organization_id';

    public function __construct(
        private CurrentOrganization $currentOrganization,
        private Session $session,
    ) {}

    public function current(): ?Organization
    {
        return $this->currentOrganization->get();
    }

    public function resolveFor(User $user): ?Organization
    {
        $requestedId = $this->session->get(self::SESSION_KEY);

        if (is_string($requestedId)) {
            $organization = $this->activeOrganizationFor($user, $requestedId);

            if ($organization !== null) {
                return $this->activate($organization);
            }

            $this->session->forget(self::SESSION_KEY);
        }

        $default = $user->organizations()
            ->wherePivot('status', OrganizationMemberStatus::Active->value)
            ->wherePivot('is_default', true)
            ->first();

        if ($default !== null) {
            return $this->activate($default);
        }

        $first = $user->organizations()
            ->wherePivot('status', OrganizationMemberStatus::Active->value)
            ->orderBy('organizations.id')
            ->first();

        return $first === null ? null : $this->activate($first);
    }

    public function switch(User $user, string $organizationId): Organization
    {
        $organization = $this->activeOrganizationFor($user, $organizationId);

        if ($organization === null) {
            throw new OrganizationContextException('The organization is not available to the current user.');
        }

        $previous = $this->current();
        $this->activate($organization);

        OrganizationSwitched::dispatch($user, $previous, $organization);

        return $organization;
    }

    public function clear(): void
    {
        $this->session->forget(self::SESSION_KEY);
        $this->currentOrganization->clear();
    }

    private function activeOrganizationFor(User $user, string $organizationId): ?Organization
    {
        return $user->organizations()
            ->whereKey($organizationId)
            ->wherePivot('status', OrganizationMemberStatus::Active->value)
            ->first();
    }

    private function activate(Organization $organization): Organization
    {
        $this->session->put(self::SESSION_KEY, $organization->id);
        $this->currentOrganization->set($organization);

        return $organization;
    }
}
