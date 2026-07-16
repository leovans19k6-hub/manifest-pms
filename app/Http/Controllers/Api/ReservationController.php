<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Reservation\IndexReservationRequest;
use App\Http\Requests\Reservation\StoreReservationRequest;
use App\Http\Requests\Reservation\UpdateReservationRequest;
use App\Http\Resources\ReservationResource;
use Domain\Foundation\Models\OrganizationUser;
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
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ReservationController extends Controller
{
    public function __construct(
        private CurrentOrganization $organization,
    ) {}

    public function index(
        IndexReservationRequest $request,
        string $unit,
        UnitQueryService $units,
        ReservationQueryService $queries,
    ): AnonymousResourceCollection {
        $unitModel = $units->find(
            $this->membership($request),
            $unit,
        );

        return ReservationResource::collection(
            $queries->list(
                $this->membership($request),
                $unitModel,
            ),
        );
    }

    public function show(
        Request $request,
        string $reservation,
        ReservationQueryService $queries,
    ): ReservationResource {
        return new ReservationResource(
            $queries->find(
                $this->membership($request),
                $reservation,
            ),
        );
    }

    public function store(
        StoreReservationRequest $request,
        string $unit,
        UnitQueryService $units,
        ReservationDataMapper $mapper,
        CreateReservationAction $action,
    ): JsonResponse {
        $unitModel = $units->find(
            $this->membership($request),
            $unit,
        );

        $reservation = $action->execute(
            new CreateReservationCommand(
                $this->membership($request),
                $unitModel,
                $mapper->fromArray(
                    $request->validated(),
                ),
            ),
        );

        return (new ReservationResource($reservation))
            ->response()
            ->setStatusCode(201);
    }

    public function update(
        UpdateReservationRequest $request,
        string $reservation,
        ReservationQueryService $queries,
        ReservationDataMapper $mapper,
        UpdateReservationAction $action,
    ): ReservationResource {
        $reservationModel = $queries->find(
            $this->membership($request),
            $reservation,
        );

        $attributes = array_replace(
            $this->attributes($reservationModel),
            $request->validated(),
        );

        return new ReservationResource(
            $action->execute(
                new UpdateReservationCommand(
                    $this->membership($request),
                    $reservationModel,
                    $mapper->fromArray($attributes),
                ),
            ),
        );
    }

    public function destroy(
        Request $request,
        string $reservation,
        ReservationQueryService $queries,
        CancelReservationAction $action,
    ): JsonResponse {
        $reservationModel = $queries->find(
            $this->membership($request),
            $reservation,
        );

        $action->execute(
            new CancelReservationCommand(
                $this->membership($request),
                $reservationModel,
            ),
        );

        return response()->json(null, 204);
    }

    private function membership(
        Request $request,
    ): OrganizationUser {
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

    private function attributes(
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
