<?php

namespace Tests\Feature\Foundation;

use Domain\Foundation\Http\Middleware\SetRequestContext;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class RequestContextMiddlewareTest extends TestCase
{
    public function test_request_id_is_propagated_to_response(): void
    {
        Route::middleware(SetRequestContext::class)->get('/_test/request-context', fn () => response('ok'));
        $this->get('/_test/request-context', ['X-Request-ID' => '550e8400-e29b-41d4-a716-446655440000'])
            ->assertOk()->assertHeader('X-Request-ID', '550e8400-e29b-41d4-a716-446655440000');
    }
}
