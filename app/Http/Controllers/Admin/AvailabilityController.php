<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Carbon\CarbonImmutable;
use Domain\Availability\Services\AvailabilityQueryService;
use Domain\Foundation\Models\OrganizationUser;
use Domain\Foundation\Services\AuthorizationService;
use Domain\Foundation\Support\CurrentOrganization;
use Domain\Inventory\Services\UnitQueryService;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class AvailabilityController extends Controller
{
    public function __construct(
        private CurrentOrganization $organization,
        private AuthorizationService $authorization,
    ) {}

    public function index(
        Request $request,
        string $unit,
        UnitQueryService $units,
        AvailabilityQueryService $availability,
    ): View {
        $membership = $this->membership($request);

        $unitModel = $units
            ->find($membership, $unit)
            ->loadMissing('property');

        $month = $request->filled('month')
			? CarbonImmutable::parse(
				$request->string('month')
			)->startOfMonth()
			: CarbonImmutable::today()->startOfMonth();

		$calendar = $availability->calendar(
			$membership,
			$unitModel,
			$month,
		);

		return view(
			'admin.properties.units.availability.index',
			[
				'unit' => $unitModel,
				'calendar' => $calendar,
				'month' => $month,
				'previousMonth' => $month->subMonth(),
				'nextMonth' => $month->addMonth(),
				'highlightReservation' => $request->string('highlight')->toString(),
			],
		);
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
}