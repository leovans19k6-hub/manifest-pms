<?php

namespace Domain\Housekeeping\Aggregates;

use DateTimeImmutable;
use DateTimeInterface;
use Domain\Housekeeping\Enums\HousekeepingTaskStatus;
use Domain\Housekeeping\Enums\HousekeepingTaskType;

final class HousekeepingTask
{
    public function __construct(
        private string $id,
        private string $organizationId,
        private string $propertyId,
        private string $unitId,
        private HousekeepingTaskType $taskType,
        private HousekeepingTaskStatus $status,
        private DateTimeInterface $scheduledAt,
        private ?DateTimeInterface $startedAt = null,
        private ?DateTimeInterface $completedAt = null,
        private ?string $assignedTo = null,
        private ?string $notes = null,
    ) {
    }

    public function assign(string $staffId): void
    {
        if ($this->status === HousekeepingTaskStatus::Completed) {
            throw new \DomainException('Completed task cannot be assigned.');
        }

        if ($this->status === HousekeepingTaskStatus::Cancelled) {
            throw new \DomainException('Cancelled task cannot be assigned.');
        }

        if ($this->status !== HousekeepingTaskStatus::Pending) {
            throw new \DomainException('Task has already been assigned.');
        }

        $this->assignedTo = $staffId;
        $this->status = HousekeepingTaskStatus::Assigned;
    }

    public function start(): void
    {
        if ($this->status !== HousekeepingTaskStatus::Assigned) {
            throw new \DomainException('Only assigned tasks can be started.');
        }

        $this->status = HousekeepingTaskStatus::InProgress;
        $this->startedAt = new DateTimeImmutable();
    }

    public function complete(): void
    {
        if ($this->status !== HousekeepingTaskStatus::InProgress) {
            throw new \DomainException('Only in-progress tasks can be completed.');
        }

        $this->status = HousekeepingTaskStatus::Completed;
        $this->completedAt = new DateTimeImmutable();
    }

    public function cancel(): void
    {
        if ($this->status === HousekeepingTaskStatus::Completed) {
            throw new \DomainException('Completed task cannot be cancelled.');
        }

        if ($this->status === HousekeepingTaskStatus::Cancelled) {
            throw new \DomainException('Task is already cancelled.');
        }

        $this->status = HousekeepingTaskStatus::Cancelled;
    }

    public function id(): string
    {
        return $this->id;
    }

    public function status(): HousekeepingTaskStatus
    {
        return $this->status;
    }

    public function assignedTo(): ?string
    {
        return $this->assignedTo;
    }

    public function startedAt(): ?DateTimeInterface
    {
        return $this->startedAt;
    }

    public function completedAt(): ?DateTimeInterface
    {
        return $this->completedAt;
    }
}