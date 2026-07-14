<?php

namespace Domain\Inventory\Services;

use Domain\Foundation\Models\OrganizationUser;
use Domain\Foundation\Services\AuthorizationService;
use Domain\Foundation\Support\CurrentOrganization;
use Domain\Inventory\Models\Unit;
use Domain\Property\Models\Property;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;

final class UnitQueryService
{
    public function __construct(
        private CurrentOrganization $organization,
        private AuthorizationService $authorization,
    ) {}

    public function list(
        OrganizationUser $membership,
        Property $property,
    ): Collection {
        $this->authorize($membership);
        $organizationId = $this->requireOrganizationId();

        if (
            $membership->organization_id !== $organizationId
            || $property->organization_id !== $organizationId
        ) {
            throw ValidationException::withMessages([
                'property' => 'Property does not belong to the current organization.',
            ]);
        }

        return Unit::query()
            ->where('organization_id', $organizationId)
            ->where('property_id', $property->id)
            ->orderBy('name')
            ->orderBy('id')
            ->get();
    }

    public function find(
        OrganizationUser $membership,
        string $id,
    ): Unit {
        $this->authorize($membership);

        $unit = Unit::query()->find($id);

        if (
            $unit === null
            || $membership->organization_id !== $this->requireOrganizationId()
            || $unit->organization_id !== $this->requireOrganizationId()
        ) {
            throw ValidationException::withMessages([
                'unit' => 'Unit does not belong to the current organization.',
            ]);
        }

        return $unit;
    }

    private function authorize(OrganizationUser $membership): void
    {
        abort_unless(
            $this->authorization->can(
                $membership,
                'inventory.units.view',
            ),
            403,
        );
    }

    private function requireOrganizationId(): string
    {
        return $this->organization->id()
            ?? throw ValidationException::withMessages([
                'organization' => 'Current organization context is required.',
            ]);
    }
}
