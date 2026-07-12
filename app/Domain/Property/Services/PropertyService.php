<?php

namespace Domain\Property\Services;

use Domain\Foundation\Models\OrganizationUser;
use Domain\Foundation\Services\AuditLogger;
use Domain\Foundation\Services\AuthorizationService;
use Domain\Foundation\Support\CurrentOrganization;
use Domain\Property\Models\Property;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PropertyService
{
    public function __construct(
        private CurrentOrganization $organization,
        private AuthorizationService $authorization,
        private AuditLogger $audit,
    ) {}

    public function list(): Collection
    {
        $organizationId = $this->requireOrganizationId();

        return Property::query()
            ->where('organization_id', $organizationId)
            ->orderBy('name')
            ->get();
    }

    public function find(string $id): Property
    {
        return Property::query()
            ->where('organization_id', $this->requireOrganizationId())
            ->findOrFail($id);
    }

    public function create(OrganizationUser $membership, array $attributes): Property
    {
        $this->authorize($membership, 'property.properties.create');

        return DB::transaction(function () use ($attributes): Property {
            $property = Property::query()->create([
                ...$attributes,
                'organization_id' => $this->requireOrganizationId(),
            ]);

            $this->audit->record('property.created', $property, [], $property->getAttributes());

            return $property;
        });
    }

    public function update(OrganizationUser $membership, Property $property, array $attributes): Property
    {
        $this->authorize($membership, 'property.properties.update');
        $this->assertCurrentTenant($property);

        return DB::transaction(function () use ($property, $attributes): Property {
            $old = $property->getAttributes();
            $property->fill($attributes)->save();
            $this->audit->record('property.updated', $property, $old, $property->getAttributes());

            return $property->refresh();
        });
    }

    public function archive(OrganizationUser $membership, Property $property): void
    {
        $this->authorize($membership, 'property.properties.archive');
        $this->assertCurrentTenant($property);

        DB::transaction(function () use ($property): void {
            $old = $property->getAttributes();
            $property->delete();
            $this->audit->record('property.archived', $property, $old, $property->getAttributes());
        });
    }

    private function authorize(OrganizationUser $membership, string $permission): void
    {
        abort_unless($this->authorization->can($membership, $permission), 403);
    }

    private function assertCurrentTenant(Property $property): void
    {
        if ($property->organization_id !== $this->requireOrganizationId()) {
            throw ValidationException::withMessages(['property' => 'Property does not belong to the current organization.']);
        }
    }

    private function requireOrganizationId(): string
    {
        return $this->organization->id() ?? throw ValidationException::withMessages([
            'organization' => 'Current organization context is required.',
        ]);
    }
}
