<?php

namespace Database\Factories;

use Domain\Foundation\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;

class RoleFactory extends Factory
{
    protected $model = Role::class;

    public function definition(): array
    {
        $suffix = fake()->unique()->numerify('######');

        return [
            'organization_id' => null,
            'code' => 'ROLE_'.$suffix,
            'name' => 'Role '.$suffix,
            'scope' => 'organization',
            'status' => 'active',
            'is_system' => false,
            'description' => null,
        ];
    }
}
