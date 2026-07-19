<?php

declare(strict_types=1);

namespace Domain\Housekeeping\Application\DTO;

use Carbon\CarbonInterface;
use Domain\Housekeeping\Enums\HousekeepingTaskType;

final readonly class HousekeepingTaskData
{
    public function __construct(
        public string $organizationId,
        public string $propertyId,
        public string $unitId,
        public ?string $reservationId,
        public HousekeepingTaskType $type,
        public int $priority = 3,
        public ?CarbonInterface $scheduledAt = null,
        public ?string $notes = null,
    ) {
    }
}