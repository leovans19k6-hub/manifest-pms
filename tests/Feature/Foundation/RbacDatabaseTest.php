<?php

namespace Tests\Feature\Foundation;

use Database\Factories\OrganizationFactory;
use Database\Factories\PermissionFactory;
use Database\Factories\RoleFactory;
use Database\Factories\UserFactory;
use Domain\Foundation\Models\OrganizationUser;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RbacDatabaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_role_permission_and_membership_role_relationships_are_persisted(): void
    {
        $organization = OrganizationFactory::new()->create();
        $user = UserFactory::new()->create();
        $membership = OrganizationUser::create([
            'organization_id' => $organization->id,
            'user_id' => $user->id,
            'status' => 'active',
            'is_default' => true,
        ]);
        $role = RoleFactory::new()->create(['organization_id' => $organization->id]);
        $permission = PermissionFactory::new()->create();

        $role->permissions()->attach($permission);
        $membership->roles()->attach($role);

        $this->assertTrue($role->fresh()->permissions->contains($permission));
        $this->assertTrue($membership->fresh()->roles->contains($role));
    }

    public function test_duplicate_pivot_assignments_are_rejected(): void
    {
        $role = RoleFactory::new()->create();
        $permission = PermissionFactory::new()->create();
        $role->permissions()->attach($permission);

        $this->expectException(QueryException::class);

        $role->permissions()->attach($permission);
    }
}
