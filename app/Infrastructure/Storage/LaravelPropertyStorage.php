<?php

namespace Infrastructure\Storage;

use Domain\Property\Contracts\PropertyStorage;
use Illuminate\Support\Facades\Storage;

final class LaravelPropertyStorage implements PropertyStorage
{
    public function put(string $key, string $contents, string $mimeType): void
    {
        Storage::disk($this->disk())->put($key, $contents, ['ContentType' => $mimeType]);
    }

    public function delete(string $key): void
    {
        Storage::disk($this->disk())->delete($key);
    }

    public function disk(): string
    {
        return (string) config('property_media.disk', 'local');
    }
}
