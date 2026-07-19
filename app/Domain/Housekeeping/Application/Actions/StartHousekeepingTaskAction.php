<?php

declare(strict_types=1);

namespace Domain\Housekeeping\Application\Actions;

use Domain\Housekeeping\Enums\HousekeepingTaskStatus;
use Domain\Housekeeping\Models\HousekeepingTask;
use Illuminate\Database\Eloquent\ModelNotFoundException;

final class StartHousekeepingTaskAction
{
    public function execute(string $taskId): HousekeepingTask
    {
        /** @var HousekeepingTask|null $task */
        $task = HousekeepingTask::query()->find($taskId);

        if ($task === null) {
            throw new ModelNotFoundException('Housekeeping task not found.');
        }

        if ($task->status !== HousekeepingTaskStatus::Assigned) {
            throw new \DomainException(
                'Only assigned housekeeping tasks can be started.'
            );
        }

        $task->update([
            'status' => HousekeepingTaskStatus::InProgress,
            'started_at' => now(),
        ]);

        return $task->refresh();
    }
}