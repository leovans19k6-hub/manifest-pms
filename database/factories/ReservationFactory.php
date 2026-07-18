<?php

namespace Database\Factories;

use Domain\Reservation\Enums\ReservationSource;
use Domain\Reservation\Enums\ReservationStatus;
use Domain\Reservation\Models\Reservation;
use Domain\Inventory\Models\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<Reservation>
 */
class ReservationFactory extends Factory
{
    protected $model = Reservation::class;

    public function definition(): array
    {
        $unit = UnitFactory::new()->create();

        $checkIn = Carbon::now()->addDays(fake()->numberBetween(1, 30));
        $checkOut = (clone $checkIn)->addDays(fake()->numberBetween(1, 7));

        return [
            'organization_id' => $unit->organization_id,
            'property_id' => $unit->property_id,
            'unit_id' => $unit->id,

            'code' => strtoupper(fake()->unique()->bothify('RSV-######')),

            'status' => ReservationStatus::Reserved,
            'source' => ReservationSource::Website,

            'guest_name' => fake()->name(),
            'guest_phone' => fake()->phoneNumber(),
            'guest_email' => fake()->safeEmail(),

            'adults' => 2,
            'children' => 0,

            'check_in' => $checkIn,
            'check_out' => $checkOut,

            'notes' => fake()->optional()->sentence(),

            'metadata' => null,
        ];
    }
	public function forUnit(Unit $unit): static
	{
		return $this->state(fn () => [
			'organization_id' => $unit->organization_id,
			'property_id' => $unit->property_id,
			'unit_id' => $unit->id,
		]);
	}
	public function status(ReservationStatus $status): static
	{
		return $this->state(fn () => [
			'status' => $status,
		]);
	}
}
