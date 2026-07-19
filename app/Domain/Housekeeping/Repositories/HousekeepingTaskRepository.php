<?php

namespace Domain\Housekeeping\Repositories;

use Domain\Housekeeping\Aggregates\HousekeepingTask;

interface HousekeepingTaskRepository
{
    public function save(HousekeepingTask $task): void;

    public function find(string $id): ?HousekeepingTask;

    public function nextPending(string $unitId): ?HousekeepingTask;
}