<?php

declare(strict_types=1);

namespace Domain\Housekeeping\Services;

use Domain\Housekeeping\Application\Actions\CreateHousekeepingTaskAction;
use Domain\Housekeeping\Application\DTO\HousekeepingTaskData;
use Domain\Housekeeping\Enums\HousekeepingTaskType;
use Domain\Housekeeping\Models\HousekeepingTask;
use Domain\Reservation\Models\Reservation;

final readonly class HousekeepingPlanner
{
    public function __construct(
        private CreateHousekeepingTaskAction $createTask,
    ) {
    }

    public function createCheckoutCleaningTask(
		Reservation $reservation,
	): HousekeepingTask {
		/** @var HousekeepingTask|null $existing */
		$existing = HousekeepingTask::query()
			->where('reservation_id', $reservation->id)
			->where('type', HousekeepingTaskType::CheckoutCleaning)
			->first();

		if ($existing !== null) {
			return $existing;
		}

		return $this->createTask->execute(
			new HousekeepingTaskData(
				organizationId: $reservation->organization_id,
				propertyId: $reservation->property_id,
				unitId: $reservation->unit_id,
				reservationId: $reservation->id,
				type: HousekeepingTaskType::CheckoutCleaning,
				priority: 3,
				scheduledAt: $reservation->check_out_date,
				notes: 'Automatically generated after guest check-out.',
			)
		);
	}
	
	public function createStayoverCleaningTask(
		Reservation $reservation,
	): HousekeepingTask {
		/** @var HousekeepingTask|null $existing */
		$existing = HousekeepingTask::query()
			->where('reservation_id', $reservation->id)
			->where('type', HousekeepingTaskType::StayoverCleaning)
			->first();

		if ($existing !== null) {
			return $existing;
		}

		return $this->createTask->execute(
			new HousekeepingTaskData(
				organizationId: $reservation->organization_id,
				propertyId: $reservation->property_id,
				unitId: $reservation->unit_id,
				reservationId: $reservation->id,
				type: HousekeepingTaskType::StayoverCleaning,
				priority: 2,
				scheduledAt: $reservation->check_in_date->addDays(...),
				notes: 'Automatically generated for stayover cleaning.',
			)
		);
	}
}