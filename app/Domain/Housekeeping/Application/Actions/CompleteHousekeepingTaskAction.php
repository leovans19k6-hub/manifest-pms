<?php

declare(strict_types=1);

namespace Domain\Housekeeping\Application\Actions;

use Domain\Housekeeping\Enums\HousekeepingTaskStatus;
use Domain\Housekeeping\Models\HousekeepingTask;
use Illuminate\Database\Eloquent\ModelNotFoundException;

final class CompleteHousekeepingTaskAction
{
    public function execute(string $taskId): HousekeepingTask
    {
        /** @var HousekeepingTask|null $task */
        $task = HousekeepingTask::query()->find($taskId);

        if ($task === null) {
            throw new ModelNotFoundException('Housekeeping task not found.');
        }

        if ($task->status !== HousekeepingTaskStatus::InProgress) {
            throw new \DomainException(
                'Only in-progress housekeeping tasks can be completed.'
            );
        }

        $task->update([
            'status' => HousekeepingTaskStatus::Completed,
            'completed_at' => now(),
        ]);

        return $task->refresh();
    }
}