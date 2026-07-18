<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Reservation\StoreReservationRequest;
use App\Http\Requests\Reservation\UpdateReservationRequest;
use Domain\Foundation\Models\OrganizationUser;
use Domain\Foundation\Services\AuthorizationService;
use Domain\Foundation\Support\CurrentOrganization;
use Domain\Inventory\Services\UnitQueryService;
use Domain\Reservation\Application\Actions\CancelReservationAction;
use Domain\Reservation\Application\Actions\CreateReservationAction;
use Domain\Reservation\Application\Actions\UpdateReservationAction;
use Domain\Reservation\Application\Commands\CancelReservationCommand;
use Domain\Reservation\Application\Commands\CreateReservationCommand;
use Domain\Reservation\Application\Commands\UpdateReservationCommand;
use Domain\Reservation\Application\Mappers\ReservationDataMapper;
use Domain\Reservation\Models\Reservation;
use Domain\Reservation\Services\ReservationQueryService;
use Domain\Reservation\Enums\ReservationStatus;
use Domain\Reservation\Enums\ReservationSource;
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
        )->loadMissing('property');;

        return view('admin.properties.units.reservations.index', [
            'unit' => $unitModel,
            'reservations' => $queries->list(
                $membership,
                $unitModel,
            ),
            'abilities' => $this->abilities(
                $membership,
            ),
        ]);
    }
	public function show(
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

		return view('admin.properties.units.reservations.show', [
			'reservation' => $queries->find(
				$membership,
				$reservation,
			),
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

        return view('admin.properties.units.reservations.create', [
			...$this->formData(),
			'unit' => $units->find(
				$membership,
				$unit,
			),
			'checkIn' => $request->filled('check_in')
				? \Illuminate\Support\Carbon::parse($request->string('check_in'))
				: null,
			'checkOut' => $request->filled('check_in')
				? \Illuminate\Support\Carbon::parse($request->string('check_in'))->addDay()
				: null,
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

        return view('admin.properties.units.reservations.edit', [
			...$this->formData(),
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
                $updated->unit_id,
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
                $unitId,
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
	private function formData(): array
	{
		return [
			'statuses' => ReservationStatus::cases(),
			'sources' => ReservationSource::cases(),
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
}
