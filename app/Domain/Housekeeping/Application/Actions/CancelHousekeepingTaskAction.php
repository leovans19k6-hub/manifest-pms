<?php

declare(strict_types=1);

namespace Domain\Housekeeping\Application\Actions;

use Domain\Housekeeping\Enums\HousekeepingTaskStatus;
use Domain\Housekeeping\Models\HousekeepingTask;
use Illuminate\Database\Eloquent\ModelNotFoundException;

final class CancelHousekeepingTaskAction
{
    public function execute(string $taskId): HousekeepingTask
    {
        /** @var HousekeepingTask|null $task */
        $task = HousekeepingTask::query()->find($taskId);

        if ($task === null) {
            throw new ModelNotFoundException('Housekeeping task not found.');
        }

        if (
            $task->status === HousekeepingTaskStatus::Completed ||
            $task->status === HousekeepingTaskStatus::Cancelled
        ) {
            throw new \DomainException(
                'Completed or cancelled housekeeping tasks cannot be cancelled.'
            );
        }

        $task->update([
            'status' => HousekeepingTaskStatus::Cancelled,
        ]);

        return $task->refresh();
    }
}