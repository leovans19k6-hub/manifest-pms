<?php

namespace Domain\Reservation\Application\DTO;

use Domain\Reservation\Enums\ReservationSource;
use Domain\Reservation\Enums\ReservationStatus;

final readonly class ReservationData
{
    public function __construct(
        public string $code,
        public ReservationStatus $status,
        public ReservationSource $source,

        public string $guestName,
        public ?string $guestPhone,
        public ?string $guestEmail,

        public int $adults,
        public int $children,

        public \DateTimeInterface $checkIn,
        public \DateTimeInterface $checkOut,

        public ?string $notes = null,
        public ?array $metadata = null,
    ) {}

    public function toArray(): array
    {
        return [
            'code' => $this->code,

            'status' => $this->status->value,
            'source' => $this->source->value,

            'guest_name' => $this->guestName,
            'guest_phone' => $this->guestPhone,
            'guest_email' => $this->guestEmail,

            'adults' => $this->adults,
            'children' => $this->children,

            'check_in' => $this->checkIn,
            'check_out' => $this->checkOut,

            'notes' => $this->notes,
            'metadata' => $this->metadata,
        ];
    }
}
