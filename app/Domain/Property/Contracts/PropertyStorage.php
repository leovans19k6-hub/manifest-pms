<?php

namespace Domain\Property\Contracts;

interface PropertyStorage
{
    public function put(string $key, string $contents, string $mimeType): void;

    public function delete(string $key): void;

    public function disk(): string;
}
