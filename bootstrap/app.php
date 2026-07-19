<?php

use Domain\Foundation\Http\Middleware\RequireOrganization;
use Domain\Foundation\Http\Middleware\RequirePermission;
use Domain\Foundation\Http\Middleware\SetRequestContext;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\SetLocale;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
    $middleware->web(append: [
        SetRequestContext::class,
        SetLocale::class,
    ]);

    $middleware->alias([
        'organization' => RequireOrganization::class,
        'permission' => RequirePermission::class,
    ]);
})
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(fn ($request) => $request->is('api/*') || $request->expectsJson());
    })->create();
