<?php

namespace Tests\Feature\Foundation;

use Database\Factories\OrganizationFactory;
use Database\Factories\UserFactory;
use Domain\Foundation\Models\OrganizationUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationSessionFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_regenerates_session_resolves_organization_and_records_activity(): void
    {
        $organization = OrganizationFactory::new()->create();
        $user = UserFactory::new()->create(['password' => 'password']);
        OrganizationUser::create(['organization_id' => $organization->id, 'user_id' => $user->id, 'status' => 'active', 'is_default' => true]);

        $old = session()->getId();
        $response = $this->post('/login', ['email' => $user->email, 'password' => 'password']);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
        $this->assertNotSame($old, session()->getId());
        $this->assertSame($organization->id, session('current_organization_id'));
        $this->assertDatabaseHas('activity_logs', ['event' => 'auth.login.succeeded', 'actor_id' => $user->id]);
    }

    public function test_failed_login_records_activity_and_does_not_authenticate(): void
    {
        $user = UserFactory::new()->create();
        $this->from('/login')->post('/login', ['email' => $user->email, 'password' => 'wrong'])->assertSessionHasErrors('email');
        $this->assertGuest();
        $this->assertDatabaseHas('activity_logs', ['event' => 'auth.login.failed']);
    }

    public function test_logout_invalidates_authentication_and_records_activity(): void
    {
        $organization = OrganizationFactory::new()->create();
        $user = UserFactory::new()->create();
        OrganizationUser::create(['organization_id' => $organization->id, 'user_id' => $user->id, 'status' => 'active', 'is_default' => true]);
        $this->actingAs($user);
        session(['current_organization_id' => $organization->id]);

        $this->post('/logout')->assertRedirect('/login');
        $this->assertGuest();
        $this->assertNull(session('current_organization_id'));
        $this->assertDatabaseHas('activity_logs', ['event' => 'auth.logout', 'actor_id' => $user->id]);
    }

    public function test_dashboard_requires_authentication_and_active_organization(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
        $user = UserFactory::new()->create();
        $this->actingAs($user)->get('/dashboard')->assertForbidden();
    }

    public function test_login_endpoint_is_rate_limited(): void
    {
        config(['auth_security.login_attempts' => 2]);
        $user = UserFactory::new()->create();
        $this->post('/login', ['email' => $user->email, 'password' => 'bad']);
        $this->post('/login', ['email' => $user->email, 'password' => 'bad']);
        $this->post('/login', ['email' => $user->email, 'password' => 'bad'])->assertStatus(429);
    }
}
