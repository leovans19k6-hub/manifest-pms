<?php

declare(strict_types=1);

namespace Domain\Housekeeping\Application\Commands;

use Domain\Foundation\Models\OrganizationUser;
use Domain\Housekeeping\Models\HousekeepingTask;

final readonly class AssignHousekeepingTaskCommand
{
    public function __construct(
        public OrganizationUser $membership,
        public HousekeepingTask $task,
        public string $assigneeId,
    ) {}
}