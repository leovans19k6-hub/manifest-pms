<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Inventory\StoreUnitRequest;
use App\Http\Requests\Inventory\UpdateUnitRequest;
use Domain\Foundation\Models\OrganizationUser;
use Domain\Foundation\Services\AuthorizationService;
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
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UnitController extends Controller
{
    public function __construct(
        private CurrentOrganization $organization,
        private AuthorizationService $authorization,
    ) {}

    public function index(
		Request $request,
		string $property,
		PropertyService $properties,
		UnitQueryService $queries,
	): View {
		$membership = $this->membership($request);

		$propertyModel = $properties->find($property);

		$units = $queries->list(
			$membership,
			$propertyModel,
		);

		$abilities = $this->abilities($membership);

		return view(
			'admin.properties.units.index',
			[
				'property' => $propertyModel,
				'units' => $units,
				'abilities' => $abilities,
				'statuses' => UnitStatus::cases(),
				'types' => UnitType::cases(),
			],
		);
	}

    public function create(
        Request $request,
        string $property,
        PropertyService $properties,
    ): View {
        $membership = $this->membership($request);

        abort_unless(
            $this->authorization->can(
                $membership,
                'inventory.units.create',
            ),
            403,
        );

        return view('admin.properties.units.create', [
            ...$this->formData(),
            'property' => $properties->find($property),
        ]);
    }

    public function store(
        StoreUnitRequest $request,
        string $property,
        PropertyService $properties,
        CreateUnitAction $action,
    ): RedirectResponse {
        $unit = $action->execute(
            new CreateUnitCommand(
                $this->membership($request),
                $properties->find($property),
                $this->unitData($request),
            ),
        );

        return redirect()
            ->route('admin.units.edit', $unit)
            ->with('status', 'Unit created successfully.');
    }

    public function edit(
        Request $request,
        string $unit,
        UnitQueryService $queries,
    ): View {
        $membership = $this->membership($request);

        abort_unless(
            $this->authorization->can(
                $membership,
                'inventory.units.update',
            ),
            403,
        );

        return view('admin.properties.units.edit', [
            ...$this->formData(),
            'unit' => $queries->find($membership, $unit),
        ]);
    }

    public function update(
        UpdateUnitRequest $request,
        string $unit,
        UnitQueryService $queries,
        UpdateUnitAction $action,
    ): RedirectResponse {
        $membership = $this->membership($request);

        $unitModel = $queries->find($membership, $unit);

        $updated = $action->execute(
            new UpdateUnitCommand(
                $membership,
                $unitModel,
                $this->unitData($request, $unitModel),
            ),
        );

        return redirect()
            ->route('admin.units.edit', $updated)
            ->with('status', 'Unit updated successfully.');
    }

    public function destroy(
        Request $request,
        string $unit,
        UnitQueryService $queries,
        ArchiveUnitAction $action,
    ): RedirectResponse {
        $membership = $this->membership($request);
        $unitModel = $queries->find($membership, $unit);

        $action->execute(
            new ArchiveUnitCommand(
                $membership,
                $unitModel,
            ),
        );

        return redirect()
            ->route(
                'admin.properties.units.index',
                $unitModel->property_id,
            )
            ->with('status', 'Unit archived successfully.');
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

    private function abilities(OrganizationUser $membership): array
    {
        return [
            'create' => $this->authorization->can(
                $membership,
                'inventory.units.create',
            ),
            'update' => $this->authorization->can(
                $membership,
                'inventory.units.update',
            ),
            'archive' => $this->authorization->can(
                $membership,
                'inventory.units.archive',
            ),
        ];
    }

    private function formData(): array
    {
        return [
            'types' => UnitType::cases(),
            'statuses' => UnitStatus::cases(),
        ];
    }

    private function unitData(
        Request $request,
        ?Unit $unit = null,
    ): UnitData {
        return new UnitData(
            code: $request->input('code', $unit?->code),
            name: $request->input('name', $unit?->name),
            slug: $request->input('slug', $unit?->slug),

            type: UnitType::from(
                $request->input(
                    'type',
                    $unit?->type->value,
                ),
            ),

            status: UnitStatus::from(
                $request->input(
                    'status',
                    $unit?->status->value,
                ),
            ),

            capacityAdults: (int) $request->input(
                'capacity_adults',
                $unit?->capacity_adults,
            ),

            capacityChildren: (int) $request->input(
                'capacity_children',
                $unit?->capacity_children,
            ),

            bedrooms: (int) $request->input(
                'bedrooms',
                $unit?->bedrooms,
            ),

            bathrooms: (int) $request->input(
                'bathrooms',
                $unit?->bathrooms,
            ),

            baseOccupancy: (int) $request->input(
                'base_occupancy',
                $unit?->base_occupancy,
            ),

            maxOccupancy: (int) $request->input(
                'max_occupancy',
                $unit?->max_occupancy,
            ),

            sortOrder: (int) $request->input(
                'sort_order',
                $unit?->sort_order,
            ),

            description: $request->input(
                'description',
                $unit?->description,
            ),

            metadata: $request->input(
                'metadata',
                $unit?->metadata,
            ),
        );
    }
}
