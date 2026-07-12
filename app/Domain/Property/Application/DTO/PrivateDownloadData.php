<?php

namespace Domain\Property\Application\DTO;

use DateTimeInterface;

final readonly class PrivateDownloadData
{
    public function __construct(public string $url, public DateTimeInterface $expiresAt) {}
}
