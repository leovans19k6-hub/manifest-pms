<?php

namespace Tests\Feature\Reservation;

use Domain\Reservation\Enums\ReservationSource;
use Domain\Reservation\Enums\ReservationStatus;
use Domain\Reservation\Models\Reservation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReservationDomainTest extends TestCase
{
    use RefreshDatabase;

    public function test_reservation_factory_creates_valid_model(): void
    {
        $reservation = Reservation::factory()->create();

        $this->assertNotNull($reservation->id);
        $this->assertNotNull($reservation->organization);
        $this->assertNotNull($reservation->property);
        $this->assertNotNull($reservation->unit);

        $this->assertInstanceOf(
            ReservationStatus::class,
            $reservation->status,
        );

        $this->assertInstanceOf(
            ReservationSource::class,
            $reservation->source,
        );
    }
}
