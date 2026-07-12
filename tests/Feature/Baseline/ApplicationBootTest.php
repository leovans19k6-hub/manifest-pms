<?php

namespace Tests\Feature\Baseline;

use Tests\TestCase;

class ApplicationBootTest extends TestCase
{
    public function test_application_home_route_returns_successful_response(): void
    {
        $this->get('/')->assertSuccessful();
    }

    public function test_dashboard_requires_authentication(): void
    {
        $this->get('/dashboard')
            ->assertRedirect('/login');
    }
}
