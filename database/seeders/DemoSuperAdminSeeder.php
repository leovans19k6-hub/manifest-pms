<?php

namespace Database\Seeders;

use Domain\Foundation\Enums\OrganizationMemberStatus;
use Domain\Foundation\Enums\OrganizationStatus;
use Domain\Foundation\Enums\RoleScope;
use Domain\Foundation\Enums\RoleStatus;
use Domain\Foundation\Models\Organization;
use Domain\Foundation\Models\OrganizationUser;
use Domain\Foundation\Models\Permission;
use Domain\Foundation\Models\Role;
use Domain\Foundation\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoSuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $organization = Organization::query()->firstOrCreate(
            [
                'slug' => 'demo',
            ],
            [
                'code' => 'DEMO',
                'name' => 'Hieu',
                'status' => OrganizationStatus::Active,
                'timezone' => 'Asia/Ho_Chi_Minh',
                'currency' => 'VND',
                'locale' => 'vi',
            ],
        );

        $user = User::query()->firstOrCreate(
            [
                'email' => 'hieufhhp@gmail.com',
            ],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('Bomthui@123'),
            ],
        );

        $membership = OrganizationUser::query()->firstOrCreate(
            [
                'organization_id' => $organization->id,
                'user_id' => $user->id,
            ],
            [
                'status' => OrganizationMemberStatus::Active,
                'is_default' => true,
                'joined_at' => now(),
            ],
        );

        $role = Role::query()->firstOrCreate(
            [
                'organization_id' => $organization->id,
                'code' => 'super_admin',
            ],
            [
                'name' => 'Super Admin',
                'scope' => RoleScope::Organization,
                'status' => RoleStatus::Active,
                'is_system' => false,
                'description' => 'Demo Super Administrator',
            ],
        );

        $role->permissions()->sync(
            Permission::query()->pluck('id')->all(),
        );

        $membership->roles()->syncWithoutDetaching([
            $role->id,
        ]);

        $this->command?->info('');
        $this->command?->info('Demo Super Admin created');
        $this->command?->info('Email: admin@manifest.test');
        $this->command?->info('Password: 12345678');
    }
}
