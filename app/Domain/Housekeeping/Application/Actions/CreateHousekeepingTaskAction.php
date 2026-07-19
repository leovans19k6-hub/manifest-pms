<?php

declare(strict_types=1);

namespace Domain\Housekeeping\Application\Actions;

use Domain\Housekeeping\Application\DTO\HousekeepingTaskData;
use Domain\Housekeeping\Application\Validation\HousekeepingTaskValidator;
use Domain\Housekeeping\Enums\HousekeepingTaskStatus;
use Domain\Housekeeping\Models\HousekeepingTask;

final readonly class CreateHousekeepingTaskAction
{
    public function __construct(
        private HousekeepingTaskValidator $validator,
    ) {
    }

    public function execute(HousekeepingTaskData $data): HousekeepingTask
    {
        $this->validator->validate($data);

        /** @var HousekeepingTask $task */
        $task = HousekeepingTask::query()->create([
            'organization_id' => $data->organizationId,
            'property_id' => $data->propertyId,
            'unit_id' => $data->unitId,
            'reservation_id' => $data->reservationId,
            'assigned_to' => null,
            'status' => HousekeepingTaskStatus::Pending,
            'type' => $data->type,
            'priority' => $data->priority,
            'scheduled_at' => $data->scheduledAt,
            'started_at' => null,
            'completed_at' => null,
            'notes' => $data->notes,
        ]);

        return $task;
    }
}