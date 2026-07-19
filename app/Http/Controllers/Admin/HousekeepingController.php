<?php

declare(strict_types=1);

namespace App\Http\Controllers\Housekeeping;

use App\Http\Controllers\Controller;
use App\Http\Requests\Housekeeping\AssignHousekeepingTaskRequest;
use Domain\Foundation\Support\CurrentMembership;
use Domain\Housekeeping\Application\Actions\AssignHousekeepingTaskAction;
use Domain\Housekeeping\Application\Actions\CompleteHousekeepingTaskAction;
use Domain\Housekeeping\Application\Actions\StartHousekeepingTaskAction;
use Domain\Housekeeping\Application\Commands\AssignHousekeepingTaskCommand;
use Domain\Housekeeping\Application\Commands\CompleteHousekeepingTaskCommand;
use Domain\Housekeeping\Application\Commands\StartHousekeepingTaskCommand;
use Domain\Housekeeping\Models\HousekeepingTask;
use Domain\Housekeeping\Services\HousekeepingQueryService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class HousekeepingController extends Controller
{
    public function __construct(
        private HousekeepingQueryService $queries,
        private CurrentMembership $membership,
        private AssignHousekeepingTaskAction $assignAction,
        private StartHousekeepingTaskAction $startAction,
        private CompleteHousekeepingTaskAction $completeAction,
    ) {}

    public function index(
        Request $request,
    ): View {
        $tasks = $this->queries->paginate(
            $this->membership->get(),
            $request->all(),
        );

        return view(
            'housekeeping.index',
            [
                'tasks' => $tasks,
                'filters' => $request->all(),
            ],
        );
    }

    public function assign(
        AssignHousekeepingTaskRequest $request,
        HousekeepingTask $task,
    ): RedirectResponse {
        $this->assignAction->execute(
            new AssignHousekeepingTaskCommand(
                membership: $this->membership->get(),
                task: $task,
                assigneeId: $request->string('assignee_id')->toString(),
            ),
        );

        return back()->with(
            'success',
            'Housekeeping task assigned successfully.',
        );
    }
	
	    public function start(
        HousekeepingTask $task,
    ): RedirectResponse {
        $this->startAction->execute(
            new StartHousekeepingTaskCommand(
                membership: $this->membership->get(),
                task: $task,
            ),
        );

        return back()->with(
            'success',
            'Housekeeping task started successfully.',
        );
    }

    public function complete(
        HousekeepingTask $task,
    ): RedirectResponse {
        $this->completeAction->execute(
            new CompleteHousekeepingTaskCommand(
                membership: $this->membership->get(),
                task: $task,
            ),
        );

        return back()->with(
            'success',
            'Housekeeping task completed successfully.',
        );
    }
}