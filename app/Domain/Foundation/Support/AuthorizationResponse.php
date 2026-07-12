<?php

namespace Domain\Foundation\Support;

use Domain\Foundation\Exceptions\AuthorizationException;

readonly class AuthorizationResponse
{
    private function __construct(
        public bool $allowed,
        public int $status,
        public string $code,
        public string $message,
    ) {}

    public static function allow(): self
    {
        return new self(true, 200, 'authorized', 'Authorized.');
    }

    public static function unauthenticated(): self
    {
        return self::deny(
            'unauthenticated',
            'Authentication is required.',
            401,
        );
    }

    public static function missingOrganization(): self
    {
        return self::deny(
            'organization_context_missing',
            'No active organization is available.',
            403,
        );
    }

    public static function missingMembership(): self
    {
        return self::deny(
            'active_membership_required',
            'No active organization membership.',
            403,
        );
    }

    public static function missingPermission(string $permission): self
    {
        return self::deny(
            'permission_denied',
            "Missing required permission [{$permission}].",
            403,
        );
    }

    public static function deny(
        string $code,
        string $message = 'This action is unauthorized.',
        int $status = 403,
    ): self {
        return new self(false, $status, $code, $message);
    }

    public function authorize(): void
    {
        if (! $this->allowed) {
            throw new AuthorizationException($this);
        }
    }
}
