<?php

namespace Infrastructure\Storage;

use DateTimeInterface;
use Domain\Property\Contracts\PropertyStorage;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

final class LaravelPropertyStorage implements PropertyStorage
{
    public function put(string $key, string $contents, string $mimeType): void
    {
        if (! Storage::disk($this->disk())->put($key, $contents, ['ContentType' => $mimeType, 'visibility' => 'private'])) {
            throw new RuntimeException('Unable to persist property file.');
        }
    }

    public function get(string $key): string
    {
        return Storage::disk($this->disk())->get($key);
    }

    public function exists(string $key): bool
    {
        return Storage::disk($this->disk())->exists($key);
    }

    public function delete(string $key): void
    {
        if (! Storage::disk($this->disk())->delete($key)) {
            throw new RuntimeException('Unable to delete property file.');
        }
    }

    public function temporaryUrl(string $key, DateTimeInterface $expiresAt): string
    {
        return Storage::disk($this->disk())->temporaryUrl($key, $expiresAt);
    }

    public function disk(): string
    {
        return (string) config('property_media.disk', 'local');
    }
}
