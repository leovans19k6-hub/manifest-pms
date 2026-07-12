<?php

namespace Domain\Foundation\Services;

use Domain\Foundation\Models\User;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Contracts\Session\Session;
use Illuminate\Validation\ValidationException;
use LogicException;

class AuthenticationService
{
    private StatefulGuard $auth;

    public function __construct(
        AuthFactory $auth,
        private Session $session,
        private OrganizationContextService $organizations,
        private ActivityLogger $activity,
    ) {
        $guard = $auth->guard();

        if (! $guard instanceof StatefulGuard) {
            throw new LogicException(
                'The configured authentication guard must be stateful.',
            );
        }

        $this->auth = $guard;
    }

    public function attempt(array $credentials, bool $remember = false): User
    {
        if (! $this->auth->attempt($credentials, $remember)) {
            $this->activity->record(
                'auth.login.failed',
                'Failed login attempt',
                [
                    'email' => $credentials['email'] ?? null,
                ],
            );

            throw ValidationException::withMessages([
                'email' => 'The provided credentials do not match our records.',
            ]);
        }

        $this->session->regenerate();

        /** @var User $user */
        $user = $this->auth->user();

        $organization = $this->organizations->resolveFor($user);

        $this->activity->record(
            'auth.login.succeeded',
            'User logged in',
            [
                'organization_id' => $organization?->id,
            ],
            $user,
        );

        return $user;
    }

    public function logout(): void
    {
        /** @var User|null $user */
        $user = $this->auth->user();

        $this->activity->record(
            'auth.logout',
            'User logged out',
            [],
            $user,
        );

        $this->organizations->clear();

        $this->auth->logout();

        $this->session->invalidate();
        $this->session->regenerateToken();
    }
}
