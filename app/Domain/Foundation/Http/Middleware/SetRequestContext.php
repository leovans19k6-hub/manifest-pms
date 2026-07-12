<?php

namespace Domain\Foundation\Http\Middleware;

use Closure;
use Domain\Foundation\Support\RequestContext;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetRequestContext
{
    public function __construct(private RequestContext $context) {}

    public function handle(Request $request, Closure $next): Response
    {
        $this->context->set($request->headers->get('X-Request-ID') ?: $this->context->id());

        try {
            $response = $next($request);
            $response->headers->set('X-Request-ID', $this->context->id());

            return $response;
        } finally {
            $this->context->clear();
        }
    }
}
