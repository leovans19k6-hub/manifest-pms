<?php

namespace Tests\Feature\Property;

use Database\Factories\OrganizationFactory;
use Database\Seeders\PropertySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PropertySeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_property_seeder_is_idempotent_per_organization(): void
    {
        OrganizationFactory::new()->count(2)->create();
        $this->seed(PropertySeeder::class);
        $this->seed(PropertySeeder::class);

        $this->assertDatabaseCount('properties', 2);
    }
}
