<?php

namespace Tests\Feature\Inventory;

use Database\Factories\OrganizationFactory;
use Database\Factories\PropertyFactory;
use Database\Factories\UnitFactory;
use Domain\Inventory\Enums\UnitStatus;
use Domain\Inventory\Enums\UnitType;
use Domain\Inventory\Models\Unit;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class UnitDomainCoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_unit_persists_public_domain_contract(): void
    {
        $property = PropertyFactory::new()->create();

        $unit = UnitFactory::new()->create([
            'organization_id' => $property->organization_id,
            'property_id' => $property->id,
            'code' => 'VILLA-01',
            'name' => 'Villa Biển 01',
            'slug' => 'villa-bien-01',
            'type' => UnitType::Villa,
            'status' => UnitStatus::Active,
            'capacity_adults' => 4,
            'capacity_children' => 2,
            'bedrooms' => 2,
            'bathrooms' => 2,
            'base_occupancy' => 2,
            'max_occupancy' => 6,
            'sort_order' => 10,
            'description' => 'Villa hướng biển.',
            'metadata' => ['view' => 'sea'],
        ]);

        $unit = $unit->fresh();

        $this->assertInstanceOf(UnitType::class, $unit->type);
        $this->assertSame(UnitType::Villa, $unit->type);
        $this->assertInstanceOf(UnitStatus::class, $unit->status);
        $this->assertSame(UnitStatus::Active, $unit->status);
        $this->assertSame(['view' => 'sea'], $unit->metadata);
        $this->assertSame($property->id, $unit->property->id);
        $this->assertSame(
            $property->organization_id,
            $unit->organization->id,
        );
    }

    public function test_property_exposes_units_relation(): void
    {
        $property = PropertyFactory::new()->create();

        $unit = UnitFactory::new()->create([
            'organization_id' => $property->organization_id,
            'property_id' => $property->id,
        ]);

        $this->assertTrue($property->units->contains($unit));
    }

    public function test_unit_code_is_unique_within_property_tenant_scope(): void
    {
        $property = PropertyFactory::new()->create();

        UnitFactory::new()->create([
            'organization_id' => $property->organization_id,
            'property_id' => $property->id,
            'code' => 'ROOM-101',
        ]);

        $this->expectException(QueryException::class);

        UnitFactory::new()->create([
            'organization_id' => $property->organization_id,
            'property_id' => $property->id,
            'code' => 'ROOM-101',
        ]);
    }

    public function test_same_unit_code_can_exist_in_different_properties(): void
    {
        $organization = OrganizationFactory::new()->create();

        $first = PropertyFactory::new()->create([
            'organization_id' => $organization->id,
        ]);

        $second = PropertyFactory::new()->create([
            'organization_id' => $organization->id,
        ]);

        UnitFactory::new()->create([
            'organization_id' => $organization->id,
            'property_id' => $first->id,
            'code' => 'ROOM-101',
        ]);

        $unit = UnitFactory::new()->create([
            'organization_id' => $organization->id,
            'property_id' => $second->id,
            'code' => 'ROOM-101',
        ]);

        $this->assertDatabaseHas('units', [
            'id' => $unit->id,
        ]);
    }

    public function test_same_unit_slug_can_exist_in_different_properties(): void
    {
        $organization = OrganizationFactory::new()->create();

        $first = PropertyFactory::new()->create([
            'organization_id' => $organization->id,
        ]);

        $second = PropertyFactory::new()->create([
            'organization_id' => $organization->id,
        ]);

        UnitFactory::new()->create([
            'organization_id' => $organization->id,
            'property_id' => $first->id,
            'slug' => 'room-101',
        ]);

        $unit = UnitFactory::new()->create([
            'organization_id' => $organization->id,
            'property_id' => $second->id,
            'slug' => 'room-101',
        ]);

        $this->assertDatabaseHas('units', [
            'id' => $unit->id,
        ]);
    }

    public function test_unit_slug_is_unique_within_property_tenant_scope(): void
    {
        $property = PropertyFactory::new()->create();

        UnitFactory::new()->create([
            'organization_id' => $property->organization_id,
            'property_id' => $property->id,
            'slug' => 'room-101',
        ]);

        $this->expectException(QueryException::class);

        UnitFactory::new()->create([
            'organization_id' => $property->organization_id,
            'property_id' => $property->id,
            'slug' => 'room-101',
        ]);
    }

    public function test_unit_rejects_cross_tenant_property_reference(): void
    {
        $organization = OrganizationFactory::new()->create();
        $foreignProperty = PropertyFactory::new()->create();

        $this->expectException(QueryException::class);

        Unit::query()->create([
            'organization_id' => $organization->id,
            'property_id' => $foreignProperty->id,
            'code' => 'FOREIGN-01',
            'name' => 'Foreign Unit',
            'slug' => 'foreign-unit',
            'type' => UnitType::Room,
            'status' => UnitStatus::Draft,
            'capacity_adults' => 2,
            'capacity_children' => 0,
            'bedrooms' => 1,
            'bathrooms' => 1,
            'base_occupancy' => 1,
            'max_occupancy' => 2,
            'sort_order' => 0,
        ]);
    }

    public function test_unit_rejects_invalid_occupancy_contract(): void
    {
        $property = PropertyFactory::new()->create();

        $this->expectException(QueryException::class);

        Unit::query()->create([
            'organization_id' => $property->organization_id,
            'property_id' => $property->id,
            'code' => 'INVALID-01',
            'name' => 'Invalid Unit',
            'slug' => 'invalid-unit',
            'type' => UnitType::Room,
            'status' => UnitStatus::Draft,
            'capacity_adults' => 2,
            'capacity_children' => 0,
            'bedrooms' => 1,
            'bathrooms' => 1,
            'base_occupancy' => 4,
            'max_occupancy' => 2,
            'sort_order' => 0,
        ]);
    }

    public function test_unit_supports_soft_deletes(): void
    {
        $unit = UnitFactory::new()->create();

        $unit->delete();

        $this->assertSoftDeleted('units', [
            'id' => $unit->id,
        ]);

        $this->assertNotNull(
            Unit::withTrashed()->findOrFail($unit->id)->deleted_at,
        );
    }

    public function test_unit_rejects_negative_numeric_values(): void
    {
        $property = PropertyFactory::new()->create();

        $columns = [
            'capacity_adults',
            'capacity_children',
            'bedrooms',
            'bathrooms',
            'base_occupancy',
            'max_occupancy',
            'sort_order',
        ];

        foreach ($columns as $column) {
            try {
                UnitFactory::new()->create([
                    'organization_id' => $property->organization_id,
                    'property_id' => $property->id,
                    $column => -1,
                ]);

                $this->fail(
                    "Expected negative {$column} to be rejected.",
                );
            } catch (QueryException) {
                $this->assertTrue(true);
            }
        }
    }

    public function test_unit_rejects_unknown_type_and_status_values(): void
    {
        $property = PropertyFactory::new()->create();

        foreach ([
            ['type' => 'unknown'],
            ['status' => 'unknown'],
        ] as $invalid) {
            try {
                DB::table('units')->insert(array_merge([
                    'id' => (string) str()->ulid(),
                    'organization_id' => $property->organization_id,
                    'property_id' => $property->id,
                    'code' => fake()
                        ->unique()
                        ->bothify('INVALID-##??'),
                    'name' => 'Invalid Unit',
                    'slug' => fake()
                        ->unique()
                        ->bothify('invalid-unit-##??'),
                    'type' => UnitType::Room->value,
                    'status' => UnitStatus::Draft->value,
                    'capacity_adults' => 2,
                    'capacity_children' => 0,
                    'bedrooms' => 1,
                    'bathrooms' => 1,
                    'base_occupancy' => 1,
                    'max_occupancy' => 2,
                    'sort_order' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ], $invalid));

                $this->fail(
                    'Expected invalid enum value to be rejected.',
                );
            } catch (QueryException) {
                $this->assertTrue(true);
            }
        }
    }

    public function test_unit_rejects_invalid_occupancy_contract_on_update(): void
    {
        $unit = UnitFactory::new()->create([
            'base_occupancy' => 1,
            'max_occupancy' => 2,
        ]);

        $this->expectException(QueryException::class);

        $unit->update([
            'base_occupancy' => 3,
        ]);
    }

    public function test_force_deleting_property_cascades_units(): void
    {
        $property = PropertyFactory::new()->create();

        $unit = UnitFactory::new()->create([
            'organization_id' => $property->organization_id,
            'property_id' => $property->id,
        ]);

        $property->forceDelete();

        $this->assertDatabaseMissing('units', [
            'id' => $unit->id,
        ]);
    }

    public function test_force_deleting_organization_cascades_units(): void
    {
        $unit = UnitFactory::new()->create();

        $organization = $unit->organization;

        $organization->forceDelete();

        $this->assertDatabaseMissing('units', [
            'id' => $unit->id,
        ]);
    }

    public function test_unit_factory_preserves_property_tenant_consistency(): void
    {
        $units = UnitFactory::new()
            ->count(5)
            ->create();

        foreach ($units as $unit) {
            $this->assertSame(
                $unit->property->organization_id,
                $unit->organization_id,
            );
        }
    }
}
