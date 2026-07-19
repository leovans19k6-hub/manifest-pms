<?php

declare(strict_types=1);

namespace Database\Factories;

use Domain\Foundation\Models\Organization;
use Domain\Foundation\Models\User;
use Domain\Housekeeping\Enums\HousekeepingTaskStatus;
use Domain\Housekeeping\Enums\HousekeepingTaskType;
use Domain\Housekeeping\Models\HousekeepingTask;
use Domain\Inventory\Models\Unit;
use Domain\Property\Models\Property;
use Domain\Reservation\Models\Reservation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HousekeepingTask>
 */
class HousekeepingTaskFactory extends Factory
{
    protected $model = HousekeepingTask::class;

    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'property_id' => Property::factory(),
            'unit_id' => Unit::factory(),
            'reservation_id' => Reservation::factory(),

            'assigned_to' => null,

            'status' => HousekeepingTaskStatus::Pending,

            'type' => HousekeepingTaskType::CheckoutCleaning,

            'priority' => 3,

            'scheduled_at' => now(),

            'started_at' => null,

            'completed_at' => null,

            'notes' => fake()->optional()->sentence(),
        ];
    }

    public function pending(): static
    {
        return $this->state(fn () => [
            'status' => HousekeepingTaskStatus::Pending,
            'assigned_to' => null,
            'started_at' => null,
            'completed_at' => null,
        ]);
    }

    public function assigned(): static
    {
        return $this->state(fn () => [
            'status' => HousekeepingTaskStatus::Assigned,
            'assigned_to' => User::factory(),
        ]);
    }

    public function inProgress(): static
    {
        return $this->state(fn () => [
            'status' => HousekeepingTaskStatus::InProgress,
            'assigned_to' => User::factory(),
            'started_at' => now(),
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn () => [
            'status' => HousekeepingTaskStatus::Completed,
            'assigned_to' => User::factory(),
            'started_at' => now()->subMinutes(45),
            'completed_at' => now(),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn () => [
            'status' => HousekeepingTaskStatus::Cancelled,
        ]);
    }

    public function checkoutCleaning(): static
    {
        return $this->state(fn () => [
            'type' => HousekeepingTaskType::CheckoutCleaning,
        ]);
    }
}