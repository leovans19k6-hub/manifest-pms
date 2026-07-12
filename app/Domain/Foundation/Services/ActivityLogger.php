<?php

namespace Domain\Foundation\Services;

use Domain\Foundation\Models\ActivityLog;
use Domain\Foundation\Models\User;
use Domain\Foundation\Support\CurrentOrganization;
use Domain\Foundation\Support\RequestContext;

class ActivityLogger
{
    public function __construct(private CurrentOrganization $organization, private RequestContext $requestContext) {}

    public function record(string $event, string $description, array $metadata = [], ?User $actor = null): ActivityLog
    {
        return ActivityLog::query()->create([
            'organization_id' => $this->organization->id(), 'actor_id' => $actor?->id, 'event' => $event,
            'description' => $description, 'metadata' => $metadata ?: null, 'request_id' => $this->requestContext->id(),
        ]);
    }
}
