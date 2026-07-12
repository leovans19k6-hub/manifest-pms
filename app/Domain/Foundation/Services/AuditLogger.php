<?php

namespace Domain\Foundation\Services;

use Domain\Foundation\Models\AuditLog;
use Domain\Foundation\Models\User;
use Domain\Foundation\Support\CurrentOrganization;
use Domain\Foundation\Support\RequestContext;
use Illuminate\Database\Eloquent\Model;

class AuditLogger
{
    public function __construct(private CurrentOrganization $organization, private RequestContext $requestContext) {}

    public function record(string $event, ?Model $auditable = null, array $old = [], array $new = [], array $metadata = [], ?User $actor = null): AuditLog
    {
        return AuditLog::query()->create([
            'organization_id' => $this->organization->id(), 'actor_id' => $actor?->id,
            'event' => $event, 'auditable_type' => $auditable?->getMorphClass(), 'auditable_id' => $auditable?->getKey(),
            'old_values' => $old ?: null, 'new_values' => $new ?: null, 'metadata' => $metadata ?: null,
            'request_id' => $this->requestContext->id(), 'ip_address' => request()?->ip(), 'user_agent' => request()?->userAgent(),
        ]);
    }
}
