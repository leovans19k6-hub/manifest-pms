<?php

namespace Domain\Foundation\Exceptions;

use Domain\Foundation\Support\AuthorizationResponse;
use Domain\Shared\Exceptions\DomainException;

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

    public function status(): int
    {
        return $this->response->status;
    }
}
