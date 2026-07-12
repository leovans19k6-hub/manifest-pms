<?php

namespace Domain\Foundation\Support;

use Illuminate\Support\Str;

class RequestContext
{
    private ?string $requestId = null;

    public function id(): string
    {
        return $this->requestId ??= (string) Str::uuid();
    }

    public function set(string $requestId): void
    {
        $this->requestId = $requestId;
    }

    public function clear(): void
    {
        $this->requestId = null;
    }
}
