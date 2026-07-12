<?php

namespace Domain\Foundation\Http\Middleware;

use Closure;
use Domain\Foundation\Models\User;
use Domain\Foundation\Services\MembershipResolver;
use Domain\Foundation\Services\OrganizationContextService;
use Domain\Foundation\Support\AuthorizationResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireOrganization
{
    public function __construct(
        private OrganizationContextService $organizations,
        private MembershipResolver $memberships,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $this->authorize($request)->authorize();

        return $next($request);
    }

    private function authorize(Request $request): AuthorizationResponse
    {
        $user = $request->user();

        if (! $user instanceof User) {
            return AuthorizationResponse::unauthenticated();
        }

        if ($this->organizations->resolveFor($user) === null) {
            $this->memberships->clear();

            return AuthorizationResponse::missingOrganization();
        }

        if ($this->memberships->resolve($user) === null) {
            return AuthorizationResponse::missingOrganization();
        }

        return AuthorizationResponse::allow();
    }
}
