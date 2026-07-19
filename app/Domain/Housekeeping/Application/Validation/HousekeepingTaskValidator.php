<?php

declare(strict_types=1);

namespace Domain\Housekeeping\Application\Validation;

use Domain\Housekeeping\Application\DTO\HousekeepingTaskData;
use InvalidArgumentException;

final class HousekeepingTaskValidator
{
    public function validate(HousekeepingTaskData $data): void
    {
        if ($data->organizationId === '') {
            throw new InvalidArgumentException('Organization ID is required.');
        }

        if ($data->propertyId === '') {
            throw new InvalidArgumentException('Property ID is required.');
        }

        if ($data->unitId === '') {
            throw new InvalidArgumentException('Unit ID is required.');
        }

        if ($data->priority < 1 || $data->priority > 5) {
            throw new InvalidArgumentException(
                'Priority must be between 1 and 5.'
            );
        }
    }
}