<?php

namespace Database\Factories;

use Domain\Foundation\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class OrganizationFactory extends Factory
{
    protected $model = Organization::class;

    public function definition(): array
    {
        $name = fake()->unique()->company();

        return [
            'code' => 'ORG'.fake()->unique()->numerify('######'),
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numerify('####'),
            'status' => 'active',
            'timezone' => 'Asia/Ho_Chi_Minh',
            'currency' => 'VND',
            'locale' => 'vi',
        ];
    }
}
