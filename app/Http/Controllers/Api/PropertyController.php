<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Property\IndexPropertyRequest;
use App\Http\Requests\Property\StorePropertyRequest;
use App\Http\Requests\Property\UpdatePropertyRequest;
use App\Http\Resources\PropertyResource;
use Domain\Foundation\Models\OrganizationUser;
use Domain\Foundation\Support\CurrentOrganization;
use Domain\Property\Application\Actions\ArchivePropertyAction;
use Domain\Property\Application\Actions\CreatePropertyAction;
use Domain\Property\Application\Actions\UpdatePropertyAction;
use Domain\Property\Application\Commands\ArchivePropertyCommand;
use Domain\Property\Application\Commands\CreatePropertyCommand;
use Domain\Property\Application\Commands\UpdatePropertyCommand;
use Domain\Property\Services\PropertyQueryService;
use Domain\Property\Services\PropertyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PropertyController extends Controller
{
    public function __construct(private CurrentOrganization $organization) {}

    public function index(IndexPropertyRequest $request, PropertyQueryService $queries): AnonymousResourceCollection
    {
        return PropertyResource::collection($queries->paginate($this->membership($request), $request->validated()));
    }

    public function show(Request $request, string $property, PropertyService $properties): PropertyResource
    {
        return new PropertyResource($properties->find($property));
    }

    public function store(StorePropertyRequest $request, CreatePropertyAction $action): JsonResponse
    {
        $property = $action->execute(new CreatePropertyCommand($this->membership($request), $request->validated()));

        return (new PropertyResource($property))->response()->setStatusCode(201);
    }

    public function update(UpdatePropertyRequest $request, string $property, PropertyService $properties, UpdatePropertyAction $action): PropertyResource
    {
        return new PropertyResource($action->execute(new UpdatePropertyCommand($this->membership($request), $properties->find($property), $request->validated())));
    }

    public function destroy(Request $request, string $property, PropertyService $properties, ArchivePropertyAction $action): JsonResponse
    {
        $action->execute(new ArchivePropertyCommand($this->membership($request), $properties->find($property)));

        return response()->json(null, 204);
    }

    private function membership(Request $request): OrganizationUser
    {
        return OrganizationUser::query()->where('user_id', $request->user()->getAuthIdentifier())->where('organization_id', $this->organization->id())->firstOrFail();
    }
}
