<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\ReservationCheckedOut;
use Domain\Housekeeping\Services\HousekeepingPlanner;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

final class CreateCheckoutCleaningTask implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Chỉ xử lý sau khi transaction commit.
     */
    public bool $afterCommit = true;

    /**
     * Retry tối đa.
     */
    public int $tries = 3;

    /**
     * Timeout (giây).
     */
    public int $timeout = 30;

    /**
     * Khoảng thời gian retry (giây).
     *
     * @return array<int>
     */
    public function backoff(): array
    {
        return [5, 15, 30];
    }

    public function __construct(
        private readonly HousekeepingPlanner $planner,
    ) {
    }

    public function handle(
        ReservationCheckedOut $event,
    ): void {
        $this->planner->createCheckoutCleaningTask(
            $event->reservation,
        );
    }
}