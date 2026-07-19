<?php

declare(strict_types=1);

namespace Domain\Housekeeping\Application\Actions;

use Domain\Housekeeping\Models\HousekeepingTask;
use Domain\Housekeeping\Enums\HousekeepingTaskStatus;
use Illuminate\Database\Eloquent\ModelNotFoundException;

final class AssignHousekeepingTaskAction
{
    public function execute(
        string $taskId,
        string $userId,
    ): HousekeepingTask {
        /** @var HousekeepingTask|null $task */
        $task = HousekeepingTask::query()->find($taskId);

        if ($task === null) {
            throw new ModelNotFoundException('Housekeeping task not found.');
        }

        if ($task->status !== HousekeepingTaskStatus::Pending) {
            throw new \DomainException(
                'Only pending housekeeping tasks can be assigned.'
            );
        }

        $task->update([
            'assigned_to' => $userId,
            'status' => HousekeepingTaskStatus::Assigned,
        ]);

        return $task->refresh();
    }
}