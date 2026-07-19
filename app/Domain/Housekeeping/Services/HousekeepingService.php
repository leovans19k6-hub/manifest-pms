<?php

declare(strict_types=1);

namespace Domain\Housekeeping\Services;

use Domain\Foundation\Models\OrganizationUser;
use Domain\Foundation\Services\AuditLogger;
use Domain\Foundation\Services\AuthorizationService;
use Domain\Foundation\Support\CurrentOrganization;
use Domain\Housekeeping\Models\HousekeepingTask;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class HousekeepingService
{
    public function __construct(
        private CurrentOrganization $organization,
        private AuthorizationService $authorization,
        private AuditLogger $audit,
    ) {}

    public function assign(
        OrganizationUser $membership,
        HousekeepingTask $task,
        string $assigneeId,
    ): HousekeepingTask {
        $this->authorize(
            $membership,
            'housekeeping.tasks.assign',
        );

        $this->assertCurrentTask(
            $membership,
            $task,
        );

        return DB::transaction(
            function () use (
                $task,
                $assigneeId,
            ): HousekeepingTask {
                $old = $task->getAttributes();

                $task->assign($assigneeId);

                $task->save();

                $this->audit->record(
                    'housekeeping.assigned',
                    $task,
                    $old,
                    $task->getAttributes(),
                );

                return $task->refresh();
            },
        );
    }

    public function start(
        OrganizationUser $membership,
        HousekeepingTask $task,
    ): HousekeepingTask {
        $this->authorize(
            $membership,
            'housekeeping.tasks.update',
        );

        $this->assertCurrentTask(
            $membership,
            $task,
        );

        return DB::transaction(
            function () use (
                $task,
            ): HousekeepingTask {
                $old = $task->getAttributes();

                $task->start();

                $task->save();

                $this->audit->record(
                    'housekeeping.started',
                    $task,
                    $old,
                    $task->getAttributes(),
                );

                return $task->refresh();
            },
        );
    }

    public function complete(
        OrganizationUser $membership,
        HousekeepingTask $task,
    ): HousekeepingTask {
        $this->authorize(
            $membership,
            'housekeeping.tasks.complete',
        );

        $this->assertCurrentTask(
            $membership,
            $task,
        );

        return DB::transaction(
            function () use (
                $task,
            ): HousekeepingTask {
                $old = $task->getAttributes();

                $task->complete();

                $task->save();

                $this->audit->record(
                    'housekeeping.completed',
                    $task,
                    $old,
                    $task->getAttributes(),
                );

                return $task->refresh();
            },
        );
    }
	
	    private function authorize(
        OrganizationUser $membership,
        string $permission,
    ): void {
        abort_unless(
            $this->authorization->can(
                $membership,
                $permission,
            ),
            403,
        );
    }

    private function assertCurrentTask(
        OrganizationUser $membership,
        HousekeepingTask $task,
    ): void {
        $organizationId = $this->requireOrganizationId();

        if (
            $membership->organization_id !== $organizationId
            || $task->organization_id !== $organizationId
        ) {
            throw ValidationException::withMessages([
                'task' => 'Housekeeping task does not belong to the current organization.',
            ]);
        }
    }

    private function requireOrganizationId(): string
    {
        return $this->organization->id()
            ?? throw ValidationException::withMessages([
                'organization' => 'Current organization context is required.',
            ]);
    }
}