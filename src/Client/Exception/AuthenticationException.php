<?php

namespace App\Client\Exception;

class AuthenticationException extends CrmApiException
{
    public function __construct(
        string $message = 'Authentication failed',
        int $code = 401,
        ?\Exception $previous = null,
        ?array $context = null,
        ?string $httpMethod = null,
        ?string $endpoint = null,
        ?int $httpStatusCode = null
    ) {
        parent::__construct($message, $code, $previous, $context, $httpMethod, $endpoint, $httpStatusCode);
    }

    public function isTokenExpired(): bool
    {
        $message = strtolower($this->getMessage());
        return str_contains($message, 'expired') || str_contains($message, 'token');
    }

    public function isInvalidToken(): bool
    {
        $message = strtolower($this->getMessage());
        return str_contains($message, 'invalid') || str_contains($message, 'unauthorized');
    }

    public function getSuggestedAction(): string
    {
        if ($this->isTokenExpired()) {
            return 'Please refresh your authentication token';
        }

        if ($this->isInvalidToken()) {
            return 'Please check your API token configuration';
        }

        return 'Please verify your authentication credentials';
    }
}
