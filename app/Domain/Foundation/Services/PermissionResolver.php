<?php

namespace Domain\Foundation\Services;

use Domain\Foundation\Enums\OrganizationMemberStatus;
use Domain\Foundation\Enums\RoleStatus;
use Domain\Foundation\Models\OrganizationUser;
use Domain\Foundation\Support\CurrentOrganization;

class PermissionResolver
{
    private array $cache = [];

    public function __construct(private CurrentOrganization $currentOrganization) {}

    public function resolve(OrganizationUser $membership): array
    {
        $organizationId = $this->currentOrganization->id();

        if ($organizationId === null
            || $membership->organization_id !== $organizationId
            || $membership->status !== OrganizationMemberStatus::Active) {
            return [];
        }

        $cacheKey = $organizationId.':'.$membership->id;

        return $this->cache[$cacheKey] ??= $membership->roles()
            ->where('roles.status', RoleStatus::Active->value)
            ->where(function ($query) use ($organizationId): void {
                $query->where('roles.organization_id', $organizationId)
                    ->orWhere(function ($query): void {
                        $query->whereNull('roles.organization_id')
                            ->where('roles.is_system', true);
                    });
            })
            ->with('permissions:id,code')
            ->get()
            ->flatMap(fn ($role) => $role->permissions->pluck('code'))
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    public function forget(): void
    {
        $this->cache = [];
    }
}
