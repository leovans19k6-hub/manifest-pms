<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class DomainServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Register domain contracts and application services here.
    }

    public function boot(): void
    {
        // Register domain policies, observers, and listeners here.
    }
}
