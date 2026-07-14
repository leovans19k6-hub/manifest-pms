<?php

namespace Domain\Inventory\Services;

use Domain\Foundation\Models\OrganizationUser;
use Domain\Foundation\Services\AuditLogger;
use Domain\Foundation\Services\AuthorizationService;
use Domain\Foundation\Support\CurrentOrganization;
use Domain\Inventory\Models\Unit;
use Domain\Property\Models\Property;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class UnitService
{
    public function __construct(
        private CurrentOrganization $organization,
        private AuthorizationService $authorization,
        private AuditLogger $audit,
    ) {}

    public function create(
        OrganizationUser $membership,
        Property $property,
        array $attributes,
    ): Unit {
        $this->authorize($membership, 'inventory.units.create');
        $this->assertCurrentProperty($membership, $property);

        return DB::transaction(function () use ($property, $attributes): Unit {
            $unit = Unit::query()->create([
                ...$attributes,
                'organization_id' => $this->requireOrganizationId(),
                'property_id' => $property->id,
            ]);

            $this->audit->record(
                'inventory.unit.created',
                $unit,
                [],
                $unit->getAttributes(),
            );

            return $unit;
        });
    }

    public function update(
        OrganizationUser $membership,
        Unit $unit,
        array $attributes,
    ): Unit {
        $this->authorize($membership, 'inventory.units.update');
        $this->assertCurrentUnit($membership, $unit);

        return DB::transaction(function () use ($unit, $attributes): Unit {
            $old = $unit->getAttributes();

            $unit->fill($attributes)->save();

            $this->audit->record(
                'inventory.unit.updated',
                $unit,
                $old,
                $unit->getAttributes(),
            );

            return $unit->refresh();
        });
    }

    public function archive(
        OrganizationUser $membership,
        Unit $unit,
    ): void {
        $this->authorize($membership, 'inventory.units.archive');
        $this->assertCurrentUnit($membership, $unit);

        DB::transaction(function () use ($unit): void {
            $old = $unit->getAttributes();

            $unit->delete();

            $this->audit->record(
                'inventory.unit.archived',
                $unit,
                $old,
                $unit->getAttributes(),
            );
        });
    }

    private function authorize(
        OrganizationUser $membership,
        string $permission,
    ): void {
        abort_unless(
            $this->authorization->can($membership, $permission),
            403,
        );
    }

    private function assertCurrentProperty(
        OrganizationUser $membership,
        Property $property,
    ): void {
        $organizationId = $this->requireOrganizationId();

        if (
            $membership->organization_id !== $organizationId
            || $property->organization_id !== $organizationId
        ) {
            throw ValidationException::withMessages([
                'property' => 'Property does not belong to the current organization.',
            ]);
        }
    }

    private function assertCurrentUnit(
        OrganizationUser $membership,
        Unit $unit,
    ): void {
        $organizationId = $this->requireOrganizationId();

        if (
            $membership->organization_id !== $organizationId
            || $unit->organization_id !== $organizationId
        ) {
            throw ValidationException::withMessages([
                'unit' => 'Unit does not belong to the current organization.',
            ]);
        }
    }

    private function requireOrganizationId(): string
    {
        return $this->organization->id()
            ?? throw ValidationException::withMessages([
                'organization' => 'Current organization context is required.',
            ]);
    }
}
