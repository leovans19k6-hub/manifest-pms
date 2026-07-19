<?php

declare(strict_types=1);

namespace Tests\Feature\Housekeeping;

use App\Listeners\CreateCheckoutCleaningTask;
use Illuminate\Contracts\Queue\ShouldQueue;
use Tests\TestCase;

final class QueuedListenerTest extends TestCase
{
    public function test_listener_is_queued(): void
    {
        $listener = app(CreateCheckoutCleaningTask::class);

        $this->assertInstanceOf(
            ShouldQueue::class,
            $listener,
        );
    }

    public function test_listener_runs_after_commit(): void
    {
        $listener = app(CreateCheckoutCleaningTask::class);

        $this->assertTrue(
            $listener->afterCommit,
        );
    }

    public function test_listener_has_retry_configuration(): void
    {
        $listener = app(CreateCheckoutCleaningTask::class);

        $this->assertSame(
            3,
            $listener->tries,
        );

        $this->assertSame(
            30,
            $listener->timeout,
        );

        $this->assertSame(
            [5, 15, 30],
            $listener->backoff(),
        );
    }
}