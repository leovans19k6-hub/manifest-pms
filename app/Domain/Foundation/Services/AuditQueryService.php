<?php

namespace Domain\Foundation\Services;

use Domain\Foundation\Models\AuditLog;
use Domain\Foundation\Support\CurrentOrganization;
use Illuminate\Database\Eloquent\Builder;
use LogicException;

class AuditQueryService
{
    public function __construct(private CurrentOrganization $organization) {}

    public function query(): Builder
    {
        $organizationId = $this->organization->id();
        if ($organizationId === null) {
            throw new LogicException('Organization context is required.');
        }

        return AuditLog::query()->where('organization_id', $organizationId);
    }
}
