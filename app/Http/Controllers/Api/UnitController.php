<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Inventory\IndexUnitRequest;
use App\Http\Requests\Inventory\StoreUnitRequest;
use App\Http\Requests\Inventory\UpdateUnitRequest;
use App\Http\Resources\UnitResource;
use Domain\Foundation\Models\OrganizationUser;
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
use Domain\Property\Services\PropertyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class UnitController extends Controller
{
    public function __construct(
        private CurrentOrganization $organization,
    ) {}

    public function index(
        IndexUnitRequest $request,
        string $property,
        PropertyService $properties,
        UnitQueryService $queries,
    ): AnonymousResourceCollection {
        $propertyModel = $properties->find($property);

        return UnitResource::collection(
            $queries->list(
                $this->membership($request),
                $propertyModel,
            ),
        );
    }

    public function show(
        Request $request,
        string $unit,
        UnitQueryService $queries,
    ): UnitResource {
        return new UnitResource(
            $queries->find(
                $this->membership($request),
                $unit,
            ),
        );
    }

    public function store(
        StoreUnitRequest $request,
        string $property,
        PropertyService $properties,
        CreateUnitAction $action,
    ): JsonResponse {
        $propertyModel = $properties->find($property);

        $unit = $action->execute(
            new CreateUnitCommand(
                $this->membership($request),
                $propertyModel,
                $this->data($request->validated()),
            ),
        );

        return (new UnitResource($unit))
            ->response()
            ->setStatusCode(201);
    }

    public function update(
        UpdateUnitRequest $request,
        string $unit,
        UnitQueryService $queries,
        UpdateUnitAction $action,
    ): UnitResource {
        $unitModel = $queries->find(
            $this->membership($request),
            $unit,
        );

        $attributes = array_replace(
            $this->attributes($unitModel),
            $request->validated(),
        );

        return new UnitResource(
            $action->execute(
                new UpdateUnitCommand(
                    $this->membership($request),
                    $unitModel,
                    $this->data($attributes),
                ),
            ),
        );
    }

    public function destroy(
        Request $request,
        string $unit,
        UnitQueryService $queries,
        ArchiveUnitAction $action,
    ): JsonResponse {
        $unitModel = $queries->find(
            $this->membership($request),
            $unit,
        );

        $action->execute(
            new ArchiveUnitCommand(
                $this->membership($request),
                $unitModel,
            ),
        );

        return response()->json(null, 204);
    }

    private function membership(Request $request): OrganizationUser
    {
        return OrganizationUser::query()
            ->where(
                'user_id',
                $request->user()->getAuthIdentifier(),
            )
            ->where(
                'organization_id',
                $this->organization->id(),
            )
            ->firstOrFail();
    }

    private function data(array $attributes): UnitData
    {
        return new UnitData(
            code: $attributes['code'],
            name: $attributes['name'],
            slug: $attributes['slug'],
            type: UnitType::from(
                $attributes['type'] ?? UnitType::Room->value,
            ),
            status: UnitStatus::from(
                $attributes['status'] ?? UnitStatus::Draft->value,
            ),
            capacityAdults: $attributes['capacity_adults'] ?? 2,
            capacityChildren: $attributes['capacity_children'] ?? 0,
            bedrooms: $attributes['bedrooms'] ?? 1,
            bathrooms: $attributes['bathrooms'] ?? 1,
            baseOccupancy: $attributes['base_occupancy'] ?? 1,
            maxOccupancy: $attributes['max_occupancy'] ?? 2,
            sortOrder: $attributes['sort_order'] ?? 0,
            description: $attributes['description'] ?? null,
            metadata: $attributes['metadata'] ?? null,
        );
    }

    private function attributes(
        Unit $unit,
    ): array {
        return [
            'code' => $unit->code,
            'name' => $unit->name,
            'slug' => $unit->slug,
            'type' => $unit->type->value,
            'status' => $unit->status->value,
            'capacity_adults' => $unit->capacity_adults,
            'capacity_children' => $unit->capacity_children,
            'bedrooms' => $unit->bedrooms,
            'bathrooms' => $unit->bathrooms,
            'base_occupancy' => $unit->base_occupancy,
            'max_occupancy' => $unit->max_occupancy,
            'sort_order' => $unit->sort_order,
            'description' => $unit->description,
            'metadata' => $unit->metadata,
        ];
    }
}
