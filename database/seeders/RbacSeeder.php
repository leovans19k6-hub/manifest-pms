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
            ['code' => 'property.properties.view', 'name' => 'View Properties', 'group' => PermissionGroup::Property],
            ['code' => 'property.properties.create', 'name' => 'Create Properties', 'group' => PermissionGroup::Property],
            ['code' => 'property.properties.update', 'name' => 'Update Properties', 'group' => PermissionGroup::Property],
            ['code' => 'property.properties.archive', 'name' => 'Archive Properties', 'group' => PermissionGroup::Property],
            ['code' => 'property.media.view', 'name' => 'View Property Media', 'group' => PermissionGroup::Property],
            ['code' => 'property.media.create', 'name' => 'Create Property Media', 'group' => PermissionGroup::Property],
            ['code' => 'property.media.update', 'name' => 'Update Property Media', 'group' => PermissionGroup::Property],
            ['code' => 'property.media.delete', 'name' => 'Delete Property Media', 'group' => PermissionGroup::Property],
            ['code' => 'property.documents.view', 'name' => 'View Property Documents', 'group' => PermissionGroup::Property],
            ['code' => 'property.documents.create', 'name' => 'Create Property Documents', 'group' => PermissionGroup::Property],
            ['code' => 'property.documents.update', 'name' => 'Update Property Documents', 'group' => PermissionGroup::Property],
            ['code' => 'property.documents.delete', 'name' => 'Delete Property Documents', 'group' => PermissionGroup::Property],
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
