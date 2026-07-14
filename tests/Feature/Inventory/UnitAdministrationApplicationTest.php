<?php

namespace Tests\Feature\Inventory;

use App\Models\User;
use Database\Factories\OrganizationFactory;
use Database\Factories\PropertyFactory;
use Database\Factories\UnitFactory;
use Domain\Foundation\Enums\OrganizationMemberStatus;
use Domain\Foundation\Models\OrganizationUser;
use Domain\Foundation\Models\Permission;
use Domain\Foundation\Models\Role;
use Domain\Foundation\Support\CurrentOrganization;
use Domain\Inventory\Application\Actions\ArchiveUnitAction;
use Domain\Inventory\Application\Actions\CreateUnitAction;
use Domain\Inventory\Application\Actions\UpdateUnitAction;
use Domain\Inventory\Application\Commands\ArchiveUnitCommand;
use Domain\Inventory\Application\Commands\CreateUnitCommand;
use Domain\Inventory\Application\Commands\UpdateUnitCommand;
use Domain\Inventory\Application\DTO\UnitData;
use Domain\Inventory\Enums\UnitStatus;
use Domain\Inventory\Enums\UnitType;
use Domain\Inventory\Models\Unit;
use Domain\Inventory\Services\UnitQueryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class UnitAdministrationApplicationTest extends TestCase
{
    use RefreshDatabase;

    public function test_authorized_member_can_create_unit_and_audit_event_is_recorded(): void
    {
        [$membership, $property] = $this->contextWithPermission(
            'inventory.units.create',
        );

        $unit = app(CreateUnitAction::class)->execute(
            new CreateUnitCommand(
                $membership,
                $property,
                $this->data(
                    code: 'ROOM-101',
                    name: 'Room 101',
                    slug: 'room-101',
                ),
            ),
        );

        $this->assertDatabaseHas('units', [
            'id' => $unit->id,
            'organization_id' => $property->organization_id,
            'property_id' => $property->id,
            'code' => 'ROOM-101',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'organization_id' => $property->organization_id,
            'event' => 'inventory.unit.created',
            'auditable_type' => Unit::class,
            'auditable_id' => $unit->id,
        ]);
    }

    public function test_create_requires_permission(): void
    {
        [$membership, $property] = $this->context();

        $this->expectException(HttpException::class);

        app(CreateUnitAction::class)->execute(
            new CreateUnitCommand(
                $membership,
                $property,
                $this->data(),
            ),
        );
    }

    public function test_create_rejects_foreign_property(): void
    {
        [$membership] = $this->contextWithPermission(
            'inventory.units.create',
        );

        $foreignProperty = PropertyFactory::new()->create();

        try {
            app(CreateUnitAction::class)->execute(
                new CreateUnitCommand(
                    $membership,
                    $foreignProperty,
                    $this->data(),
                ),
            );

            $this->fail('Expected tenant validation failure.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey(
                'property',
                $exception->errors(),
            );
        }
    }

    public function test_create_maps_duplicate_code_to_validation_error(): void
    {
        [$membership, $property] = $this->contextWithPermission(
            'inventory.units.create',
        );

        UnitFactory::new()->create([
            'organization_id' => $property->organization_id,
            'property_id' => $property->id,
            'code' => 'ROOM-101',
        ]);

        try {
            app(CreateUnitAction::class)->execute(
                new CreateUnitCommand(
                    $membership,
                    $property,
                    $this->data(code: 'ROOM-101'),
                ),
            );

            $this->fail('Expected duplicate code validation failure.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('code', $exception->errors());
        }
    }

    public function test_create_maps_duplicate_slug_to_validation_error(): void
    {
        [$membership, $property] = $this->contextWithPermission(
            'inventory.units.create',
        );

        UnitFactory::new()->create([
            'organization_id' => $property->organization_id,
            'property_id' => $property->id,
            'slug' => 'room-101',
        ]);

        try {
            app(CreateUnitAction::class)->execute(
                new CreateUnitCommand(
                    $membership,
                    $property,
                    $this->data(slug: 'room-101'),
                ),
            );

            $this->fail('Expected duplicate slug validation failure.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('slug', $exception->errors());
        }
    }

    public function test_create_validates_numeric_and_occupancy_contract(): void
    {
        [$membership, $property] = $this->contextWithPermission(
            'inventory.units.create',
        );

        try {
            app(CreateUnitAction::class)->execute(
                new CreateUnitCommand(
                    $membership,
                    $property,
                    $this->data(
                        capacityAdults: -1,
                        baseOccupancy: 3,
                        maxOccupancy: 2,
                    ),
                ),
            );

            $this->fail('Expected Unit validation failure.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey(
                'capacity_adults',
                $exception->errors(),
            );

            $this->assertArrayHasKey(
                'base_occupancy',
                $exception->errors(),
            );
        }
    }

    public function test_authorized_member_can_update_unit_and_audit_event_is_recorded(): void
    {
        [$membership, $property] = $this->contextWithPermission(
            'inventory.units.update',
        );

        $unit = UnitFactory::new()->create([
            'organization_id' => $property->organization_id,
            'property_id' => $property->id,
        ]);

        $updated = app(UpdateUnitAction::class)->execute(
            new UpdateUnitCommand(
                $membership,
                $unit,
                $this->data(
                    code: $unit->code,
                    name: 'Updated Unit',
                    slug: $unit->slug,
                    status: UnitStatus::Active,
                ),
            ),
        );

        $this->assertSame('Updated Unit', $updated->name);
        $this->assertSame(UnitStatus::Active, $updated->status);

        $this->assertDatabaseHas('audit_logs', [
            'organization_id' => $property->organization_id,
            'event' => 'inventory.unit.updated',
            'auditable_type' => Unit::class,
            'auditable_id' => $unit->id,
        ]);
    }

    public function test_update_rejects_foreign_unit(): void
    {
        [$membership] = $this->contextWithPermission(
            'inventory.units.update',
        );

        $foreignUnit = UnitFactory::new()->create();

        try {
            app(UpdateUnitAction::class)->execute(
                new UpdateUnitCommand(
                    $membership,
                    $foreignUnit,
                    $this->data(
                        code: $foreignUnit->code,
                        slug: $foreignUnit->slug,
                    ),
                ),
            );

            $this->fail('Expected tenant validation failure.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('unit', $exception->errors());
        }
    }

    public function test_authorized_member_can_archive_unit_and_audit_event_is_recorded(): void
    {
        [$membership, $property] = $this->contextWithPermission(
            'inventory.units.archive',
        );

        $unit = UnitFactory::new()->create([
            'organization_id' => $property->organization_id,
            'property_id' => $property->id,
        ]);

        app(ArchiveUnitAction::class)->execute(
            new ArchiveUnitCommand($membership, $unit),
        );

        $this->assertSoftDeleted('units', [
            'id' => $unit->id,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'organization_id' => $property->organization_id,
            'event' => 'inventory.unit.archived',
            'auditable_type' => Unit::class,
            'auditable_id' => $unit->id,
        ]);
    }

    public function test_query_service_lists_only_current_tenant_units(): void
    {
        [$membership, $property] = $this->contextWithPermission(
            'inventory.units.view',
        );

        $first = UnitFactory::new()->create([
            'organization_id' => $property->organization_id,
            'property_id' => $property->id,
            'name' => 'Alpha Unit',
        ]);

        $second = UnitFactory::new()->create([
            'organization_id' => $property->organization_id,
            'property_id' => $property->id,
            'name' => 'Beta Unit',
        ]);

        UnitFactory::new()->create();

        $units = app(UnitQueryService::class)->list(
            $membership,
            $property,
        );

        $this->assertSame(
            [$first->id, $second->id],
            $units->pluck('id')->all(),
        );
    }

    public function test_query_service_requires_view_permission(): void
    {
        [$membership, $property] = $this->context();

        $this->expectException(HttpException::class);

        app(UnitQueryService::class)->list(
            $membership,
            $property,
        );
    }

    public function test_query_service_find_rejects_foreign_unit(): void
    {
        [$membership] = $this->contextWithPermission(
            'inventory.units.view',
        );

        $foreignUnit = UnitFactory::new()->create();

        $this->expectException(ValidationException::class);

        app(UnitQueryService::class)->find(
            $membership,
            $foreignUnit->id,
        );
    }

    private function contextWithPermission(string $permission): array
    {
        [$membership, $property] = $this->context();

        $permissionModel = Permission::query()
            ->where('code', $permission)
            ->firstOrFail();

        $role = Role::query()->create([
            'organization_id' => $membership->organization_id,
            'code' => 'UNIT-'.str()->ulid(),
            'name' => 'Unit Test Role',
            'scope' => 'organization',
            'status' => 'active',
            'is_system' => false,
        ]);

        $role->permissions()->attach($permissionModel->id);
        $membership->roles()->attach($role->id);

        return [
            $membership->fresh(),
            $property,
        ];
    }

    private function context(): array
    {
        $organization = OrganizationFactory::new()->create();

        $user = User::factory()->create();

        $membership = OrganizationUser::query()->create([
            'organization_id' => $organization->id,
            'user_id' => $user->id,
            'status' => OrganizationMemberStatus::Active->value,
            'is_default' => true,
            'joined_at' => now(),
        ]);

        $property = PropertyFactory::new()->create([
            'organization_id' => $organization->id,
        ]);

        app(CurrentOrganization::class)->set($organization);

        return [
            $membership,
            $property,
        ];
    }

    private function data(
        string $code = 'UNIT-001',
        string $name = 'Unit 001',
        string $slug = 'unit-001',
        UnitType $type = UnitType::Room,
        UnitStatus $status = UnitStatus::Draft,
        int $capacityAdults = 2,
        int $capacityChildren = 0,
        int $bedrooms = 1,
        int $bathrooms = 1,
        int $baseOccupancy = 1,
        int $maxOccupancy = 2,
        int $sortOrder = 0,
        ?string $description = null,
        ?array $metadata = null,
    ): UnitData {
        return new UnitData(
            code: $code,
            name: $name,
            slug: $slug,
            type: $type,
            status: $status,
            capacityAdults: $capacityAdults,
            capacityChildren: $capacityChildren,
            bedrooms: $bedrooms,
            bathrooms: $bathrooms,
            baseOccupancy: $baseOccupancy,
            maxOccupancy: $maxOccupancy,
            sortOrder: $sortOrder,
            description: $description,
            metadata: $metadata,
        );
    }
}
