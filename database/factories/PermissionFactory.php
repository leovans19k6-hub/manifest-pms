<?php

namespace Database\Factories;

use Domain\Foundation\Models\Permission;
use Illuminate\Database\Eloquent\Factories\Factory;

class PermissionFactory extends Factory
{
    protected $model = Permission::class;

    public function definition(): array
    {
        $suffix = fake()->unique()->numerify('######');

        return [
            'code' => 'foundation.test.'.$suffix,
            'name' => 'Test Permission '.$suffix,
            'group' => 'foundation',
            'description' => null,
        ];
    }
}
