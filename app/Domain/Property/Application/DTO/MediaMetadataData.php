<?php

namespace Domain\Property\Application\DTO;

final readonly class MediaMetadataData
{
    public function __construct(public ?array $metadata) {}
}
