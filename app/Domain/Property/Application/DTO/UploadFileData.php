<?php

namespace Domain\Property\Application\DTO;

final readonly class UploadFileData
{
    public function __construct(public string $originalName, public string $mimeType, public string $contents, public ?array $metadata = null) {}

    public function size(): int
    {
        return strlen($this->contents);
    }
}
