<?php

declare(strict_types=1);

namespace App\Events;

use Domain\Reservation\Models\Reservation;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class ReservationCheckedOut
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly Reservation $reservation,
    ) {
    }
}