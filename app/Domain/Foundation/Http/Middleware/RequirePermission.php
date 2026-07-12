<?php

namespace Domain\Foundation\Http\Middleware;

use Closure;
use Domain\Foundation\Models\OrganizationUser;
use Domain\Foundation\Models\User;
use Domain\Foundation\Services\AuthorizationService;
use Domain\Foundation\Support\AuthorizationResponse;
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
        $this->authorize($request, $permission)->authorize();

        return $next($request);
    }

    private function authorize(Request $request, string $permission): AuthorizationResponse
    {
        $user = $request->user();

        if (! $user instanceof User) {
            return AuthorizationResponse::unauthenticated();
        }

        $organizationId = $this->organization->id();

        if ($organizationId === null) {
            return AuthorizationResponse::missingOrganization();
        }

        $membership = OrganizationUser::query()
            ->where('user_id', $user->getAuthIdentifier())
            ->where('organization_id', $organizationId)
            ->first();

        if ($membership === null || ! $this->authorization->can($membership, $permission)) {
            return AuthorizationResponse::missingPermission($permission);
        }

        return AuthorizationResponse::allow();
    }
}
