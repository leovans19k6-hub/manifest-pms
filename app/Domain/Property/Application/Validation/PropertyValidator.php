<?php

namespace Domain\Property\Application\Validation;

use Domain\Foundation\Support\CurrentOrganization;
use Domain\Property\Application\DTO\PropertyData;
use Domain\Property\Enums\PropertyStatus;
use Domain\Property\Enums\PropertyType;
use Domain\Property\Models\Property;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PropertyValidator
{
    public function __construct(private CurrentOrganization $organization) {}

    public function validate(array $input, ?Property $property = null): PropertyData
    {
        $organizationId = $this->organization->id() ?? throw ValidationException::withMessages([
            'organization' => 'Current organization context is required.',
        ]);

        $validated = Validator::make($input, [
            'code' => ['required', 'string', 'max:50', Rule::unique('properties', 'code')->where('organization_id', $organizationId)->ignore($property?->id)],
            'name' => ['required', 'string', 'max:150'],
            'slug' => ['required', 'string', 'max:180', Rule::unique('properties', 'slug')->where('organization_id', $organizationId)->ignore($property?->id)],
            'type' => ['required', Rule::enum(PropertyType::class)],
            'status' => ['required', Rule::enum(PropertyStatus::class)],
            'timezone' => ['required', 'timezone'],
            'currency' => ['required', 'string', 'size:3', 'uppercase'],
            'address' => ['nullable', 'string'],
            'metadata' => ['sometimes', 'array'],
        ])->validate();

        return new PropertyData(
            code: $validated['code'], name: $validated['name'], slug: $validated['slug'],
            type: PropertyType::from($validated['type']), status: PropertyStatus::from($validated['status']),
            timezone: $validated['timezone'], currency: $validated['currency'],
            address: $validated['address'] ?? null, metadata: $validated['metadata'] ?? [],
        );
    }
}
