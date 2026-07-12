<?php

namespace Domain\Foundation\Support;

use Domain\Foundation\Exceptions\AuthorizationException;

readonly class AuthorizationResponse
{
    private function __construct(
        public bool $allowed,
        public int $status = 403,
        public string $message = 'This action is unauthorized.',
    ) {}

    public static function allow(): self
    {
        return new self(true, 200, 'Authorized.');
    }

    public static function deny(string $message = 'This action is unauthorized.', int $status = 403): self
    {
        return new self(false, $status, $message);
    }

    public static function unauthenticated(): self
    {
        return self::deny('Authentication is required.', 401);
    }

    public static function missingOrganization(): self
    {
        return self::deny('No active organization membership.', 403);
    }

    public static function missingPermission(string $permission): self
    {
        return self::deny("Missing required permission [{$permission}].");
    }

    public function authorize(): void
    {
        if (! $this->allowed) {
            throw new AuthorizationException($this);
        }
    }
}
