<?php

namespace Tests\Feature\Foundation;

use Database\Factories\OrganizationFactory;
use Database\Factories\PermissionFactory;
use Database\Factories\RoleFactory;
use Database\Factories\UserFactory;
use Domain\Foundation\Models\OrganizationUser;
use Domain\Foundation\Services\AuthorizationService;
use Domain\Foundation\Support\CurrentOrganization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorizationServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_permission_is_resolved_only_for_current_organization_membership(): void
    {
        [$membership, $organization] = $this->membership();
        $role = RoleFactory::new()->create(['organization_id' => $organization->id]);
        $permission = PermissionFactory::new()->create(['code' => 'foundation.roles.view']);
        $role->permissions()->attach($permission);
        $membership->roles()->attach($role);
        app(CurrentOrganization::class)->set($organization);

        $this->assertTrue(app(AuthorizationService::class)->can($membership, 'foundation.roles.view'));
        $this->assertFalse(app(AuthorizationService::class)->can($membership, 'foundation.roles.manage'));
    }

    public function test_cross_tenant_membership_cannot_reuse_permissions(): void
    {
        [$membership, $organization] = $this->membership();
        [, $foreignOrganization] = $this->membership();
        $role = RoleFactory::new()->create(['organization_id' => $organization->id]);
        $permission = PermissionFactory::new()->create(['code' => 'foundation.roles.view']);
        $role->permissions()->attach($permission);
        $membership->roles()->attach($role);
        app(CurrentOrganization::class)->set($foreignOrganization);

        $this->assertFalse(app(AuthorizationService::class)->can($membership, 'foundation.roles.view'));
    }

    public function test_inactive_membership_is_denied(): void
    {
        [$membership, $organization] = $this->membership('suspended');
        app(CurrentOrganization::class)->set($organization);

        $this->assertFalse(app(AuthorizationService::class)->can($membership, 'foundation.roles.view'));
    }

    public function test_super_admin_bypass_requires_system_role_on_current_active_membership(): void
    {
        [$membership, $organization] = $this->membership();
        $role = RoleFactory::new()->create([
            'organization_id' => null,
            'code' => AuthorizationService::SUPER_ADMIN_CODE,
            'scope' => 'system',
            'is_system' => true,
        ]);
        $membership->roles()->attach($role);
        app(CurrentOrganization::class)->set($organization);

        $this->assertTrue(app(AuthorizationService::class)->can($membership, 'any.permission'));
    }

    public function test_organization_role_named_super_admin_does_not_bypass_authorization(): void
    {
        [$membership, $organization] = $this->membership();
        $role = RoleFactory::new()->create([
            'organization_id' => $organization->id,
            'code' => AuthorizationService::SUPER_ADMIN_CODE,
            'scope' => 'organization',
            'is_system' => false,
        ]);
        $membership->roles()->attach($role);
        app(CurrentOrganization::class)->set($organization);

        $this->assertFalse(app(AuthorizationService::class)->can($membership, 'any.permission'));
    }

    private function membership(string $status = 'active'): array
    {
        $organization = OrganizationFactory::new()->create();
        $user = UserFactory::new()->create();
        $membership = OrganizationUser::create([
            'organization_id' => $organization->id,
            'user_id' => $user->id,
            'status' => $status,
            'is_default' => true,
        ]);

        return [$membership, $organization];
    }
}
