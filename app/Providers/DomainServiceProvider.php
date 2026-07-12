<?php

namespace App\Providers;

use Domain\Foundation\Support\CurrentOrganization;
use Domain\Foundation\Support\RequestContext;
use Domain\Property\Contracts\PropertyStorage;
use Illuminate\Support\ServiceProvider;
use Infrastructure\Storage\LaravelPropertyStorage;

class DomainServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->scoped(CurrentOrganization::class, fn () => new CurrentOrganization);
        $this->app->scoped(RequestContext::class, fn () => new RequestContext);
        $this->app->bind(PropertyStorage::class, LaravelPropertyStorage::class);
    }

    public function boot(): void
    {
        // Register domain policies, observers, and listeners here.
    }
}
