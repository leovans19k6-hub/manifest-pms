<?php

namespace Database\Factories;

use Domain\Foundation\Models\Organization;
use Domain\Property\Enums\PropertyStatus;
use Domain\Property\Enums\PropertyType;
use Domain\Property\Models\Property;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PropertyFactory extends Factory
{
    protected $model = Property::class;

    public function definition(): array
    {
        $name = fake()->unique()->company();

        return [
            'organization_id' => Organization::factory(),
            'code' => strtoupper(fake()->unique()->bothify('PR-####')),
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numerify('###'),
            'type' => PropertyType::Villa,
            'status' => PropertyStatus::Active,
            'timezone' => 'Asia/Ho_Chi_Minh',
            'currency' => 'VND',
            'address' => fake()->address(),
            'metadata' => [],
        ];
    }
}
