<?php

declare(strict_types=1);

namespace Domain\Housekeeping\Application\Actions;

use Domain\Housekeeping\Application\Commands\AssignHousekeepingTaskCommand;
use Domain\Housekeeping\Models\HousekeepingTask;
use Domain\Housekeeping\Services\HousekeepingService;

final class AssignHousekeepingTaskAction
{
    public function __construct(
        private HousekeepingService $service,
    ) {}

    public function execute(
        AssignHousekeepingTaskCommand $command,
    ): HousekeepingTask {
        return $this->service->assign(
            $command->membership,
            $command->task,
            $command->assigneeId,
        );
    }
}