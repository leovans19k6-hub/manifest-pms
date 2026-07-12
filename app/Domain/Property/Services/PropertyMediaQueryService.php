<?php

namespace Domain\Property\Services;

use Domain\Foundation\Models\OrganizationUser;
use Domain\Foundation\Services\AuthorizationService;
use Domain\Foundation\Support\CurrentOrganization;
use Domain\Property\Models\Property;
use Domain\Property\Models\PropertyAsset;
use Domain\Property\Models\PropertyDocument;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;

final class PropertyMediaQueryService
{
    public function __construct(private CurrentOrganization $org, private AuthorizationService $auth) {}

    public function assets(OrganizationUser $membership, Property $property, array $filters = []): LengthAwarePaginator
    {
        $this->guard($membership, $property, 'property.media.view');
        $query = PropertyAsset::query()->where('organization_id', $this->org->id())->where('property_id', $property->id);
        if (! empty($filters['kind'])) {
            $query->where('kind', $filters['kind']);
        }

        return $this->paginate($query, $filters, ['position', 'created_at', 'original_name'], 'position');
    }

    public function documents(OrganizationUser $membership, Property $property, array $filters = []): LengthAwarePaginator
    {
        $this->guard($membership, $property, 'property.documents.view');
        $query = PropertyDocument::query()->where('organization_id', $this->org->id())->where('property_id', $property->id);
        if (! empty($filters['category'])) {
            $query->where('category', $filters['category']);
        }
        if (! empty($filters['lifecycle_status'])) {
            $query->where('lifecycle_status', $filters['lifecycle_status']);
        }

        return $this->paginate($query, $filters, ['created_at', 'original_name', 'category', 'lifecycle_status'], 'created_at');
    }

    private function paginate(Builder $query, array $filters, array $allowedSorts, string $defaultSort): LengthAwarePaginator
    {
        $sort = $filters['sort'] ?? $defaultSort;
        $direction = $filters['direction'] ?? 'asc';
        $perPage = (int) ($filters['per_page'] ?? 15);
        if (! in_array($sort, $allowedSorts, true) || ! in_array($direction, ['asc', 'desc'], true) || $perPage < 1 || $perPage > 100) {
            throw ValidationException::withMessages(['filters' => 'Invalid media query contract.']);
        }

        return $query->orderBy($sort, $direction)->paginate($perPage);
    }

    private function guard(OrganizationUser $membership, Property $property, string $permission): void
    {
        abort_unless($this->auth->can($membership, $permission), 403);
        abort_unless($membership->organization_id === $this->org->id() && $property->organization_id === $this->org->id(), 404);
    }
}
