<?php

namespace Domain\Property\Services;

use Domain\Foundation\Models\OrganizationUser;
use Domain\Foundation\Services\AuthorizationService;
use Domain\Foundation\Support\CurrentOrganization;
use Domain\Property\Enums\PropertyStatus;
use Domain\Property\Enums\PropertyType;
use Domain\Property\Models\Property;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PropertyQueryService
{
    private const SORTS = ['code', 'name', 'type', 'status', 'created_at', 'updated_at'];

    public function __construct(private CurrentOrganization $organization, private AuthorizationService $authorization) {}

    public function paginate(OrganizationUser $membership, array $filters = []): LengthAwarePaginator
    {
        abort_unless($this->authorization->can($membership, 'property.properties.view'), 403);
        $organizationId = $this->organization->id() ?? throw ValidationException::withMessages(['organization' => 'Current organization context is required.']);
        $filters = Validator::make($filters, [
            'status' => ['nullable', Rule::enum(PropertyStatus::class)],
            'type' => ['nullable', Rule::enum(PropertyType::class)],
            'search' => ['nullable', 'string', 'max:150'],
            'sort' => ['nullable', Rule::in(self::SORTS)],
            'direction' => ['nullable', Rule::in(['asc', 'desc'])],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
        ])->validate();

        return Property::query()
            ->where('organization_id', $organizationId)
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['type'] ?? null, fn ($query, $type) => $query->where('type', $type))
            ->when($filters['search'] ?? null, function ($query, $search): void {
                $query->where(fn ($nested) => $nested->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%"));
            })
            ->orderBy($filters['sort'] ?? 'name', $filters['direction'] ?? 'asc')
            ->paginate($filters['per_page'] ?? 15, ['*'], 'page', $filters['page'] ?? null);
    }
}
