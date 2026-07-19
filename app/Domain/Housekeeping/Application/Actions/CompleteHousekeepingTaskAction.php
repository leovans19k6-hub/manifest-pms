<?php

declare(strict_types=1);

namespace Domain\Housekeeping\Application\Actions;

use Domain\Housekeeping\Application\Commands\CompleteHousekeepingTaskCommand;
use Domain\Housekeeping\Models\HousekeepingTask;
use Domain\Housekeeping\Services\HousekeepingService;

final class CompleteHousekeepingTaskAction
{
    public function __construct(
        private HousekeepingService $service,
    ) {}

    public function execute(
        CompleteHousekeepingTaskCommand $command,
    ): HousekeepingTask {
        return $this->service->complete(
            $command->membership,
            $command->task,
        );
    }
}