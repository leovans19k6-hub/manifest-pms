<?php

namespace Domain\Foundation\Exceptions;

use Domain\Foundation\Support\AuthorizationResponse;
use Domain\Shared\Exceptions\DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthorizationException extends DomainException
{
    public function __construct(
        private readonly AuthorizationResponse $authorizationResponse,
    ) {
        parent::__construct(
            $authorizationResponse->message,
            $authorizationResponse->status,
        );
    }

    public function response(): AuthorizationResponse
    {
        return $this->authorizationResponse;
    }

    public function render(Request $request): Response
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            return new JsonResponse([
                'error' => [
                    'code' => $this->authorizationResponse->code,
                    'message' => $this->authorizationResponse->message,
                ],
            ], $this->authorizationResponse->status);
        }

        return response(
            $this->authorizationResponse->message,
            $this->authorizationResponse->status,
        );
    }

    public function status(): int
    {
        return $this->authorizationResponse->status;
    }
}
