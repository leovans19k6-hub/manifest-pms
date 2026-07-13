<?php

namespace Database\Factories;

use Domain\Inventory\Enums\UnitStatus;
use Domain\Inventory\Enums\UnitType;
use Domain\Inventory\Models\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Unit>
 */
class UnitFactory extends Factory
{
    protected $model = Unit::class;

    public function definition(): array
    {
        $property = PropertyFactory::new()->create();
        $name = fake()->unique()->words(3, true);

        return [
            'organization_id' => $property->organization_id,
            'property_id' => $property->id,
            'code' => strtoupper(fake()->unique()->bothify('UNIT-###??')),
            'name' => Str::title($name),
            'slug' => Str::slug($name).'-'.strtolower(fake()->unique()->bothify('##??')),
            'type' => fake()->randomElement(UnitType::cases()),
            'status' => UnitStatus::Draft,
            'capacity_adults' => 2,
            'capacity_children' => 0,
            'bedrooms' => 1,
            'bathrooms' => 1,
            'base_occupancy' => 1,
            'max_occupancy' => 2,
            'sort_order' => 0,
            'description' => fake()->optional()->sentence(),
            'metadata' => null,
        ];
    }
}
