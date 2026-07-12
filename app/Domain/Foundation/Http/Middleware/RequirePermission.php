<?php

namespace Domain\Foundation\Http\Middleware;

use Closure;
use Domain\Foundation\Models\User;
use Domain\Foundation\Services\AuthorizationService;
use Domain\Foundation\Services\MembershipResolver;
use Domain\Foundation\Support\AuthorizationResponse;
use Domain\Foundation\Support\CurrentMembership;
use Domain\Foundation\Support\CurrentOrganization;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequirePermission
{
    public function __construct(
        private CurrentOrganization $organization,
        private CurrentMembership $membership,
        private MembershipResolver $memberships,
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

        if ($this->organization->id() === null) {
            $this->memberships->clear();

            return AuthorizationResponse::missingOrganization();
        }

        $membership = $this->membership->get() ?? $this->memberships->resolve($user);

        if ($membership === null || ! $this->authorization->canCurrent($permission)) {
            return AuthorizationResponse::missingPermission($permission);
        }

        return AuthorizationResponse::allow();
    }
}
