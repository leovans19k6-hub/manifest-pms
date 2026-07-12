<?php

namespace Tests\Feature\Foundation;

use Database\Factories\OrganizationFactory;
use Database\Factories\PermissionFactory;
use Database\Factories\RoleFactory;
use Database\Factories\UserFactory;
use Domain\Foundation\Exceptions\AuthorizationException;
use Domain\Foundation\Http\Middleware\RequirePermission;
use Domain\Foundation\Models\OrganizationUser;
use Domain\Foundation\Services\OrganizationContextService;
use Domain\Foundation\Support\CurrentMembership;
use Domain\Foundation\Support\CurrentOrganization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class AuthorizationHttpFoundationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Route::middleware(['web', 'auth', 'organization'])
            ->get('/_test/authorization/organization', fn () => response()->json(['ok' => true]));

        Route::middleware(['web', 'auth', 'organization', 'permission:test.foundation.access'])
            ->get('/_test/authorization/permission', fn () => response()->json(['ok' => true]));
    }

    public function test_guest_api_request_returns_json_unauthorized(): void
    {
        $this->getJson('/api/v1/properties')
            ->assertUnauthorized();
    }

    public function test_authenticated_user_without_active_organization_returns_standard_json_forbidden(): void
    {
        $user = UserFactory::new()->create();

        $this->actingAs($user)
            ->getJson('/_test/authorization/organization')
            ->assertForbidden()
            ->assertJson([
                'error' => [
                    'code' => 'organization_context_missing',
                    'message' => 'No active organization is available.',
                ],
            ]);
    }

    public function test_suspended_membership_returns_standard_json_forbidden(): void
    {
        [$user] = $this->principal(status: 'suspended');

        $this->actingAs($user)
            ->getJson('/_test/authorization/organization')
            ->assertForbidden()
            ->assertJson([
                'error' => [
                    'code' => 'organization_context_missing',
                    'message' => 'No active organization is available.',
                ],
            ]);
    }

    public function test_missing_permission_returns_standard_json_forbidden(): void
    {
        [$user] = $this->principal();

        $this->actingAs($user)
            ->getJson('/_test/authorization/permission')
            ->assertForbidden()
            ->assertJson([
                'error' => [
                    'code' => 'permission_denied',
                    'message' => 'Missing required permission [test.foundation.access].',
                ],
            ]);
    }

    public function test_authorized_membership_succeeds(): void
    {
        [$user] = $this->principal(['test.foundation.access']);

        $this->actingAs($user)
            ->getJson('/_test/authorization/permission')
            ->assertOk()
            ->assertJson(['ok' => true]);
    }

    public function test_permission_middleware_does_not_resolve_missing_membership_state(): void
    {
        [$user, $organization] = $this->principal(['test.foundation.access']);

        $this->app->make(CurrentOrganization::class)->set($organization);
        $this->app->make(CurrentMembership::class)->clear();

        $request = Request::create(
            '/_test/authorization/direct-permission',
            'GET',
        );

        $request->setUserResolver(fn () => $user);

        $middleware = $this->app->make(
            RequirePermission::class
        );

        try {
            $middleware->handle(
                $request,
                fn () => response()->json(['ok' => true]),
                'test.foundation.access',
            );

            $this->fail('Permission middleware should deny a missing current membership.');
        } catch (AuthorizationException $exception) {
            $this->assertSame(403, $exception->status());
            $this->assertSame(
                'active_membership_required',
                $exception->response()->code,
            );
        }

        $this->assertNull(
            $this->app->make(CurrentMembership::class)->get()
        );
    }

    public function test_organization_middleware_clears_request_state_after_response(): void
    {
        [$user] = $this->principal();

        $this->actingAs($user)
            ->getJson('/_test/authorization/organization')
            ->assertOk();

        $this->assertNull(
            $this->app->make(CurrentOrganization::class)->get()
        );

        $this->assertNull(
            $this->app->make(CurrentMembership::class)->get()
        );
    }

    public function test_failed_resolution_clears_stale_request_state(): void
    {
        [$validUser, $organization, $membership] = $this->principal();
        $userWithoutMembership = UserFactory::new()->create();

        $this->app->make(CurrentOrganization::class)->set($organization);
        $this->app->make(CurrentMembership::class)->set($membership);

        $this->actingAs($userWithoutMembership)
            ->getJson('/_test/authorization/organization')
            ->assertForbidden();

        $this->assertNull(
            $this->app->make(CurrentOrganization::class)->get()
        );

        $this->assertNull(
            $this->app->make(CurrentMembership::class)->get()
        );
    }

    public function test_logout_works_without_active_membership(): void
    {
        $user = UserFactory::new()->create();

        $this->actingAs($user)
            ->post('/logout')
            ->assertRedirect('/login');

        $this->assertGuest();
    }

    public function test_cross_tenant_session_context_cannot_select_foreign_organization(): void
    {
        [$user, $organization] = $this->principal();

        $foreignOrganization = OrganizationFactory::new()->create();

        $observedOrganizationId = null;

        Route::middleware(['web', 'auth', 'organization'])
            ->get(
                '/_test/authorization/cross-tenant-context',
                function () use (&$observedOrganizationId) {
                    $observedOrganizationId = app(CurrentOrganization::class)->id();

                    return response()->json(['ok' => true]);
                },
            );

        $this->actingAs($user)
            ->withSession([
                OrganizationContextService::SESSION_KEY => $foreignOrganization->id,
            ])
            ->getJson('/_test/authorization/cross-tenant-context')
            ->assertOk()
            ->assertJson(['ok' => true]);

        $this->assertSame(
            $organization->id,
            $observedOrganizationId,
        );

        $this->assertNotSame(
            $foreignOrganization->id,
            $observedOrganizationId,
        );

        $this->assertNull(
            $this->app->make(CurrentOrganization::class)->get(),
        );

        $this->assertNull(
            $this->app->make(CurrentMembership::class)->get(),
        );
    }

    public function test_html_and_json_authorization_failures_use_distinct_response_contracts(): void
    {
        $user = UserFactory::new()->create();

        $this->actingAs($user)
            ->get('/_test/authorization/organization')
            ->assertForbidden()
            ->assertSeeText('No active organization is available.')
            ->assertHeader('content-type', 'text/html; charset=UTF-8');

        $this->actingAs($user)
            ->getJson('/_test/authorization/organization')
            ->assertForbidden()
            ->assertJson([
                'error' => [
                    'code' => 'organization_context_missing',
                    'message' => 'No active organization is available.',
                ],
            ]);
    }

    public function test_sequential_requests_do_not_reuse_organization_or_membership_state(): void
    {
        [$authorizedUser] = $this->principal(['test.foundation.access']);
        $userWithoutMembership = UserFactory::new()->create();

        $this->actingAs($authorizedUser)
            ->getJson('/_test/authorization/permission')
            ->assertOk();

        $this->assertNull(
            $this->app->make(CurrentOrganization::class)->get(),
        );

        $this->assertNull(
            $this->app->make(CurrentMembership::class)->get(),
        );

        $this->actingAs($userWithoutMembership)
            ->getJson('/_test/authorization/permission')
            ->assertForbidden()
            ->assertJson([
                'error' => [
                    'code' => 'organization_context_missing',
                    'message' => 'No active organization is available.',
                ],
            ]);

        $this->assertNull(
            $this->app->make(CurrentOrganization::class)->get(),
        );

        $this->assertNull(
            $this->app->make(CurrentMembership::class)->get(),
        );
    }

    private function principal(
        array $permissions = [],
        string $status = 'active',
    ): array {
        $organization = OrganizationFactory::new()->create();
        $user = UserFactory::new()->create();

        $membership = OrganizationUser::create([
            'organization_id' => $organization->id,
            'user_id' => $user->id,
            'status' => $status,
            'is_default' => true,
        ]);

        if ($permissions !== []) {
            $role = RoleFactory::new()->create([
                'organization_id' => $organization->id,
            ]);

            foreach ($permissions as $code) {
                $permission = PermissionFactory::new()->create([
                    'code' => $code,
                ]);

                $role->permissions()->attach($permission);
            }

            $membership->roles()->attach($role);
        }

        return [$user, $organization, $membership];
    }
}
