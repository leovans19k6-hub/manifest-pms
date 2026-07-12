<?php

namespace Tests\Feature\Foundation;

use Database\Seeders\RbacSeeder;
use Domain\Foundation\Models\Permission;
use Domain\Foundation\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RbacSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_rbac_seeder_is_idempotent_and_creates_system_super_admin(): void
    {
        $this->seed(RbacSeeder::class);
        $this->seed(RbacSeeder::class);

        $role = Role::query()->where('code', 'SUPER_ADMIN')->firstOrFail();

        $this->assertTrue($role->is_system);
        $this->assertNull($role->organization_id);
        $permissionCount = Permission::query()->count();
        $this->assertSame(8, $permissionCount);
        $this->assertSame(
            $permissionCount,
            $role->permissions()->count(),
        );
    }
}
