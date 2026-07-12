<?php

namespace Domain\Foundation\Exceptions;

use Domain\Foundation\Support\AuthorizationResponse;
use Domain\Shared\Exceptions\DomainException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthorizationException extends DomainException
{
    public function __construct(private readonly AuthorizationResponse $response)
    {
        parent::__construct($response->message, $response->status);
    }

    public function response(): AuthorizationResponse
    {
        return $this->response;
    }

    public function render(Request $request): Response
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => $this->getMessage()], $this->status());
        }

        return response($this->getMessage(), $this->status());
    }

    public function status(): int
    {
        return $this->response->status;
    }
}
