<?php

namespace Tests\Feature\Foundation;

use Database\Factories\OrganizationFactory;
use Database\Factories\PermissionFactory;
use Database\Factories\RoleFactory;
use Database\Factories\UserFactory;
use Domain\Foundation\Models\OrganizationUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class PermissionMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Route::middleware(['web', 'auth', 'organization', 'permission:foundation.roles.view'])
            ->get('/_test/permission', fn () => 'ok');
    }

    public function test_permission_middleware_is_tenant_safe(): void
    {
        $organization = OrganizationFactory::new()->create();
        $user = UserFactory::new()->create();
        $membership = OrganizationUser::create(['organization_id' => $organization->id, 'user_id' => $user->id, 'status' => 'active', 'is_default' => true]);
        $role = RoleFactory::new()->create(['organization_id' => $organization->id]);
        $permission = PermissionFactory::new()->create(['code' => 'foundation.roles.view']);
        $role->permissions()->attach($permission);
        $membership->roles()->attach($role);

        $this->actingAs($user)->get('/_test/permission')->assertOk();
    }

    public function test_permission_middleware_denies_missing_permission(): void
    {
        $organization = OrganizationFactory::new()->create();
        $user = UserFactory::new()->create();
        OrganizationUser::create(['organization_id' => $organization->id, 'user_id' => $user->id, 'status' => 'active', 'is_default' => true]);
        $this->actingAs($user)->get('/_test/permission')->assertForbidden();
    }
}
