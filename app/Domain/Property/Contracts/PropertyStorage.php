<?php

namespace Domain\Property\Contracts;

use DateTimeInterface;

interface PropertyStorage
{
    public function put(string $key, string $contents, string $mimeType): void;

    public function get(string $key): string;

    public function exists(string $key): bool;

    public function delete(string $key): void;

    public function temporaryUrl(string $key, DateTimeInterface $expiresAt): string;

    public function disk(): string;
}
