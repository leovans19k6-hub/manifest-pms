<?php

namespace Database\Seeders;

use Domain\Foundation\Enums\PermissionGroup;
use Domain\Foundation\Enums\RoleScope;
use Domain\Foundation\Enums\RoleStatus;
use Domain\Foundation\Models\Permission;
use Domain\Foundation\Models\Role;
use Illuminate\Database\Seeder;

class RbacSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = collect([
            ['code' => 'foundation.organizations.view', 'name' => 'View Organizations', 'group' => PermissionGroup::Foundation],
            ['code' => 'foundation.memberships.view', 'name' => 'View Memberships', 'group' => PermissionGroup::Foundation],
            ['code' => 'foundation.roles.view', 'name' => 'View Roles', 'group' => PermissionGroup::Foundation],
            ['code' => 'foundation.roles.manage', 'name' => 'Manage Roles', 'group' => PermissionGroup::Foundation],
        ])->map(fn (array $data): Permission => Permission::query()->updateOrCreate(
            ['code' => $data['code']],
            $data,
        ));

        $superAdmin = Role::query()->updateOrCreate(
            ['organization_id' => null, 'code' => 'SUPER_ADMIN'],
            [
                'name' => 'Super Admin',
                'scope' => RoleScope::System,
                'status' => RoleStatus::Active,
                'is_system' => true,
                'description' => 'System-level administrative role.',
            ],
        );

        $superAdmin->permissions()->syncWithoutDetaching(
            $permissions->pluck('id')->all(),
        );
    }
}
