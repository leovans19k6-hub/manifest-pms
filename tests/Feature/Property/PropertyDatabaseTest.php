<?php

namespace Tests\Feature\Property;

use Database\Factories\OrganizationFactory;
use Database\Factories\PropertyFactory;
use Domain\Property\Enums\PropertyStatus;
use Domain\Property\Enums\PropertyType;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PropertyDatabaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_property_uses_ulid_casts_enums_and_belongs_to_organization(): void
    {
        $property = PropertyFactory::new()->create();

        $this->assertSame(26, strlen($property->id));
        $this->assertInstanceOf(PropertyType::class, $property->type);
        $this->assertInstanceOf(PropertyStatus::class, $property->status);
        $this->assertTrue($property->organization->is($property->organization));
    }

    public function test_property_code_and_slug_are_unique_per_organization(): void
    {
        $organization = OrganizationFactory::new()->create();
        PropertyFactory::new()->create(['organization_id' => $organization->id, 'code' => 'HP-001', 'slug' => 'harbor']);

        $this->expectException(QueryException::class);
        PropertyFactory::new()->create(['organization_id' => $organization->id, 'code' => 'HP-001', 'slug' => 'different']);
    }

    public function test_same_property_code_can_exist_in_different_organizations(): void
    {
        PropertyFactory::new()->create(['code' => 'SHARED']);
        PropertyFactory::new()->create(['code' => 'SHARED']);
        $this->assertDatabaseCount('properties', 2);
    }
}
