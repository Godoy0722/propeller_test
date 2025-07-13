<?php

namespace App\Client\Exception;

use Exception;

class CrmApiException extends Exception
{
    private ?array $context;
    private ?string $httpMethod;
    private ?string $endpoint;
    private ?int $httpStatusCode;

    public function __construct(
        string $message = '',
        int $code = 0,
        ?Exception $previous = null,
        ?array $context = null,
        ?string $httpMethod = null,
        ?string $endpoint = null,
        ?int $httpStatusCode = null
    ) {
        parent::__construct($message, $code, $previous);

        $this->context = $context;
        $this->httpMethod = $httpMethod;
        $this->endpoint = $endpoint;
        $this->httpStatusCode = $httpStatusCode;
    }

    public function getContext(): ?array
    {
        return $this->context;
    }

    public function getHttpMethod(): ?string
    {
        return $this->httpMethod;
    }

    public function getEndpoint(): ?string
    {
        return $this->endpoint;
    }

    public function getHttpStatusCode(): ?int
    {
        return $this->httpStatusCode;
    }

    public function hasContext(): bool
    {
        return !empty($this->context);
    }

    public function getFormattedMessage(): string
    {
        $message = $this->getMessage();

        if ($this->httpMethod && $this->endpoint) {
            $message .= sprintf(' [%s %s]', $this->httpMethod, $this->endpoint);
        }

        if ($this->httpStatusCode) {
            $message .= sprintf(' (HTTP %d)', $this->httpStatusCode);
        }

        return $message;
    }
}
