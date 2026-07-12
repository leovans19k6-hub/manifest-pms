<?php

namespace Domain\Foundation\Http\Middleware;

use Closure;
use Domain\Foundation\Models\User;
use Domain\Foundation\Services\OrganizationContextService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireOrganization
{
    public function __construct(private OrganizationContextService $organizations) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        abort_unless($user instanceof User, 401);
        abort_if($this->organizations->resolveFor($user) === null, 403, 'No active organization membership.');

        return $next($request);
    }
}
