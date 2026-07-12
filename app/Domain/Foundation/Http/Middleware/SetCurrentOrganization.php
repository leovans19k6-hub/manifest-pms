<?php

namespace Domain\Foundation\Http\Middleware;

use Closure;
use Domain\Foundation\Services\OrganizationContextService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetCurrentOrganization
{
    public function __construct(private OrganizationContextService $context) {}

    public function handle(Request $request, Closure $next): Response
    {
        $this->context->clear();

        if ($request->user() !== null) {
            $this->context->resolveFor($request->user());
        }

        try {
            return $next($request);
        } finally {
            $this->context->clear();
        }
    }
}
