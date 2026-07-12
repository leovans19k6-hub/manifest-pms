<?php

namespace App\Providers;

use Domain\Foundation\Support\CurrentOrganization;
use Domain\Foundation\Support\RequestContext;
use Illuminate\Support\ServiceProvider;

class DomainServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->scoped(CurrentOrganization::class, fn () => new CurrentOrganization);
        $this->app->scoped(RequestContext::class, fn () => new RequestContext);
        // Register domain contracts and application services here.
    }

    public function boot(): void
    {
        // Register domain policies, observers, and listeners here.
    }
}
