<?php

declare(strict_types=1);

namespace Domain\Housekeeping\Services;

use Domain\Foundation\Models\OrganizationUser;
use Domain\Foundation\Services\AuthorizationService;
use Domain\Foundation\Support\CurrentOrganization;
use Domain\Housekeeping\Models\HousekeepingTask;
use Domain\Housekeeping\Enums\HousekeepingTaskStatus;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

final class HousekeepingQueryService
{
    private const SORTS = [
        'scheduled_at',
        'priority',
        'status',
        'created_at',
    ];

    public function __construct(
        private CurrentOrganization $organization,
        private AuthorizationService $authorization,
    ) {
    }

    public function paginate(
        OrganizationUser $membership,
        array $filters = [],
    ): LengthAwarePaginator {
        abort_unless(
            $this->authorization->can(
                $membership,
                'housekeeping.tasks.view',
            ),
            403,
        );

        $organizationId = $this->organization->id()
            ?? throw ValidationException::withMessages([
                'organization' => 'Current organization context is required.',
            ]);

        $filters = Validator::make($filters, [
            'status' => ['nullable', Rule::enum(HousekeepingTaskStatus::class)],
            'priority' => ['nullable', 'integer'],
            'assigned_to' => ['nullable', 'uuid'],
            'unit_id' => ['nullable', 'uuid'],
            'sort' => ['nullable', Rule::in(self::SORTS)],
            'direction' => ['nullable', Rule::in(['asc', 'desc'])],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
        ])->validate();

        return HousekeepingTask::query()
            ->where('organization_id', $organizationId)
            ->when(
                $filters['status'] ?? null,
                fn ($query, $status) => $query->where('status', $status),
            )
            ->when(
                $filters['priority'] ?? null,
                fn ($query, $priority) => $query->where('priority', $priority),
            )
            ->when(
                $filters['assigned_to'] ?? null,
                fn ($query, $assignedTo) => $query->where('assigned_to', $assignedTo),
            )
            ->when(
                $filters['unit_id'] ?? null,
                fn ($query, $unitId) => $query->where('unit_id', $unitId),
            )
            ->orderBy(
                $filters['sort'] ?? 'scheduled_at',
                $filters['direction'] ?? 'desc',
            )
            ->paginate(
                $filters['per_page'] ?? 15,
                ['*'],
                'page',
                $filters['page'] ?? null,
            );
    }

    public function find(
        OrganizationUser $membership,
        HousekeepingTask $task,
    ): HousekeepingTask {
        abort_unless(
            $this->authorization->can(
                $membership,
                'housekeeping.tasks.view',
            ),
            403,
        );

        $organizationId = $this->organization->id()
            ?? throw ValidationException::withMessages([
                'organization' => 'Current organization context is required.',
            ]);

        abort_unless(
            $task->organization_id === $organizationId,
            404,
        );

        return $task;
    }
}