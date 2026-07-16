<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Domain\Foundation\Models\OrganizationUser;
use Domain\Foundation\Services\AuthorizationService;
use Domain\Foundation\Support\CurrentOrganization;
use Domain\Inventory\Services\UnitQueryService;
use Domain\Reservation\Services\ReservationQueryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReservationController extends Controller
{
    public function __construct(
        private CurrentOrganization $organization,
        private AuthorizationService $authorization,
    ) {}

    public function index(
        Request $request,
        string $unit,
        UnitQueryService $units,
        ReservationQueryService $queries,
    ): View {
        $membership = $this->membership($request);
        $unitModel = $units->find(
            $membership,
            $unit,
        );

        return view('admin.properties.units.index', [
            'property' => $propertyModel,
            'reservations' => $queries->list($membership, $propertyModel),
            'abilities' => $this->abilities($membership),
        ]);
    }

    public function create(
        Request $request,
        string $unit,
        UnitQueryService $units,
    ): View {
        $membership = $this->membership($request);

        abort_unless(
            $this->authorization->can(
                $membership,
                'reservation.reservations.create',
            ),
            403,
        );

        return view('admin.reservations.create', [
            'unit' => $units->find(
                $membership,
                $unit,
            ),
        ]);
    }

    public function store(
        StoreReservationRequest $request,
        string $unit,
        UnitQueryService $units,
        ReservationDataMapper $mapper,
        CreateReservationAction $action,
    ): RedirectResponse {
        $membership = $this->membership($request);

        $unitModel = $units->find(
            $membership,
            $unit,
        );

        $reservation = $action->execute(
            new CreateReservationCommand(
                $membership,
                $unitModel,
                $mapper->fromArray(
                    $request->validated(),
                ),
            ),
        );

        return redirect()
            ->route(
                'admin.reservations.edit',
                $reservation,
            )
            ->with(
                'status',
                'Reservation created successfully.',
            );
    }

    public function edit(
        Request $request,
        string $reservation,
        ReservationQueryService $queries,
    ): View {
        $membership = $this->membership($request);

        abort_unless(
            $this->authorization->can(
                $membership,
                'reservation.reservations.update',
            ),
            403,
        );

        return view('admin.reservations.edit', [
            'reservation' => $queries->find(
                $membership,
                $reservation,
            ),
        ]);
    }

    public function update(
        UpdateReservationRequest $request,
        string $reservation,
        ReservationQueryService $queries,
        ReservationDataMapper $mapper,
        UpdateReservationAction $action,
    ): RedirectResponse {
        $membership = $this->membership($request);

        $reservationModel = $queries->find(
            $membership,
            $reservation,
        );

        $attributes = array_replace(
            $this->reservationAttributes(
                $reservationModel,
            ),
            $request->validated(),
        );

        $updated = $action->execute(
            new UpdateReservationCommand(
                $membership,
                $reservationModel,
                $mapper->fromArray($attributes),
            ),
        );

        return redirect()
            ->route(
                'admin.units.reservations.index',
                $reservation->unit_id,
            )
            ->with(
                'status',
                'Reservation updated successfully.',
            );
    }

    public function destroy(
        Request $request,
        string $reservation,
        ReservationQueryService $queries,
        CancelReservationAction $action,
    ): RedirectResponse {
        $membership = $this->membership($request);

        $reservationModel = $queries->find(
            $membership,
            $reservation,
        );

        $unitId = $reservationModel->unit_id;

        $action->execute(
            new CancelReservationCommand(
                $membership,
                $reservationModel,
            ),
        );

        return redirect()
            ->route(
                'admin.units.reservations.index',
                $reservation->unit_id,
            )
            ->with(
                'status',
                'Reservation cancelled successfully.',
            );
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

    private function abilities(
        OrganizationUser $membership,
    ): array {
        return [
            'create' => $this->authorization->can(
                $membership,
                'reservation.reservations.create',
            ),

            'update' => $this->authorization->can(
                $membership,
                'reservation.reservations.update',
            ),

            'cancel' => $this->authorization->can(
                $membership,
                'reservation.reservations.cancel',
            ),
        ];
    }

    private function reservationAttributes(
        Reservation $reservation,
    ): array {
        return [
            'code' => $reservation->code,

            'status' => $reservation->status->value,
            'source' => $reservation->source->value,

            'guest_name' => $reservation->guest_name,
            'guest_phone' => $reservation->guest_phone,
            'guest_email' => $reservation->guest_email,

            'adults' => $reservation->adults,
            'children' => $reservation->children,

            'check_in' => $reservation->check_in,
            'check_out' => $reservation->check_out,

            'notes' => $reservation->notes,
            'metadata' => $reservation->metadata,
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
