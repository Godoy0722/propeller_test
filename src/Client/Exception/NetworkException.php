<?php

namespace App\Client\Exception;

class NetworkException extends CrmApiException
{
    public function __construct(
        string $message = 'Network error occurred',
        int $code = 0,
        ?\Exception $previous = null,
        ?array $context = null,
        ?string $httpMethod = null,
        ?string $endpoint = null,
        ?int $httpStatusCode = null
    ) {
        parent::__construct($message, $code, $previous, $context, $httpMethod, $endpoint, $httpStatusCode);
    }

    public function isTimeout(): bool
    {
        $message = strtolower($this->getMessage());
        return str_contains($message, 'timeout') || str_contains($message, 'timed out');
    }

    public function isConnectionError(): bool
    {
        $message = strtolower($this->getMessage());
        return str_contains($message, 'connection') || str_contains($message, 'connect');
    }

    public function isDnsError(): bool
    {
        $message = strtolower($this->getMessage());
        return str_contains($message, 'dns') || str_contains($message, 'resolve');
    }

    public function getSuggestedAction(): string
    {
        if ($this->isTimeout()) {
            return 'The request timed out. Please try again or increase the timeout setting.';
        }

        if ($this->isConnectionError()) {
            return 'Unable to connect to the API. Please check your network connection and API endpoint.';
        }

        if ($this->isDnsError()) {
            return 'DNS resolution failed. Please check the API hostname configuration.';
        }

        return 'A network error occurred. Please check your connection and try again.';
    }
}
