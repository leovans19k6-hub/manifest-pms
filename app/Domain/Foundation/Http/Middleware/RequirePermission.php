<?php

namespace Domain\Foundation\Http\Middleware;

use Closure;
use Domain\Foundation\Models\OrganizationUser;
use Domain\Foundation\Services\AuthorizationService;
use Domain\Foundation\Support\CurrentOrganization;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequirePermission
{
    public function __construct(
        private CurrentOrganization $organization,
        private AuthorizationService $authorization,
    ) {}

    public function handle(Request $request, Closure $next, string $permission): Response
    {
        abort_unless($request->user(), 401);
        abort_unless($this->organization->id(), 403);

        $membership = OrganizationUser::query()
            ->where('user_id', $request->user()->getAuthIdentifier())
            ->where('organization_id', $this->organization->id())
            ->first();

        abort_unless($membership && $this->authorization->can($membership, $permission), 403);

        return $next($request);
    }
}
