<?php

namespace Tests\Unit\Housekeeping;

use DateTimeImmutable;
use Domain\Housekeeping\Aggregates\HousekeepingTask;
use Domain\Housekeeping\Enums\HousekeepingTaskStatus;
use Domain\Housekeeping\Enums\HousekeepingTaskType;
use DomainException;
use PHPUnit\Framework\TestCase;

class HousekeepingTaskTest extends TestCase
{
    private function makeTask(): HousekeepingTask
    {
        return new HousekeepingTask(
            id: 'task-001',
            organizationId: 'org-001',
            propertyId: 'property-001',
            unitId: 'unit-001',
            taskType: HousekeepingTaskType::CheckoutCleaning,
            status: HousekeepingTaskStatus::Pending,
            scheduledAt: new DateTimeImmutable(),
        );
    }

    public function test_can_assign_pending_task(): void
    {
        $task = $this->makeTask();

        $task->assign('staff-001');

        $this->assertSame(
            HousekeepingTaskStatus::Assigned,
            $task->status()
        );

        $this->assertSame(
            'staff-001',
            $task->assignedTo()
        );
    }

    public function test_can_start_assigned_task(): void
    {
        $task = $this->makeTask();

        $task->assign('staff-001');
        $task->start();

        $this->assertSame(
            HousekeepingTaskStatus::InProgress,
            $task->status()
        );

        $this->assertNotNull(
            $task->startedAt()
        );
    }

    public function test_can_complete_in_progress_task(): void
    {
        $task = $this->makeTask();

        $task->assign('staff-001');
        $task->start();
        $task->complete();

        $this->assertSame(
            HousekeepingTaskStatus::Completed,
            $task->status()
        );

        $this->assertNotNull(
            $task->completedAt()
        );
    }

    public function test_can_cancel_pending_task(): void
    {
        $task = $this->makeTask();

        $task->cancel();

        $this->assertSame(
            HousekeepingTaskStatus::Cancelled,
            $task->status()
        );
    }

    public function test_cannot_assign_completed_task(): void
    {
        $task = new HousekeepingTask(
            id: 'task-001',
            organizationId: 'org-001',
            propertyId: 'property-001',
            unitId: 'unit-001',
            taskType: HousekeepingTaskType::CheckoutCleaning,
            status: HousekeepingTaskStatus::Completed,
            scheduledAt: new DateTimeImmutable(),
        );

        $this->expectException(DomainException::class);

        $task->assign('staff-001');
    }

    public function test_cannot_assign_cancelled_task(): void
    {
        $task = new HousekeepingTask(
            id: 'task-001',
            organizationId: 'org-001',
            propertyId: 'property-001',
            unitId: 'unit-001',
            taskType: HousekeepingTaskType::CheckoutCleaning,
            status: HousekeepingTaskStatus::Cancelled,
            scheduledAt: new DateTimeImmutable(),
        );

        $this->expectException(DomainException::class);

        $task->assign('staff-001');
    }

    public function test_cannot_start_without_assignment(): void
    {
        $task = $this->makeTask();

        $this->expectException(DomainException::class);

        $task->start();
    }

    public function test_cannot_complete_without_starting(): void
    {
        $task = $this->makeTask();

        $task->assign('staff-001');

        $this->expectException(DomainException::class);

        $task->complete();
    }
}