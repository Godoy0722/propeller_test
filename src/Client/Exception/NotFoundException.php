<?php

namespace App\Client\Exception;

class NotFoundException extends CrmApiException
{
    private ?string $resourceType;
    private ?string $resourceId;

    public function __construct(
        string $message = 'Resource not found',
        int $code = 404,
        ?\Exception $previous = null,
        ?array $context = null,
        ?string $httpMethod = null,
        ?string $endpoint = null,
        ?int $httpStatusCode = null,
        ?string $resourceType = null,
        ?string $resourceId = null
    ) {
        parent::__construct($message, $code, $previous, $context, $httpMethod, $endpoint, $httpStatusCode);
        $this->resourceType = $resourceType;
        $this->resourceId = $resourceId;
    }

    public function getResourceType(): ?string
    {
        return $this->resourceType;
    }

    public function getResourceId(): ?string
    {
        return $this->resourceId;
    }

    public function getFormattedMessage(): string
    {
        $message = parent::getFormattedMessage();

        if ($this->resourceType && $this->resourceId) {
            $message .= sprintf(' - %s with ID "%s" not found', $this->resourceType, $this->resourceId);
        } elseif ($this->resourceType) {
            $message .= sprintf(' - %s not found', $this->resourceType);
        }

        return $message;
    }

    public static function forResource(
        string $resourceType,
        string $resourceId,
        ?string $httpMethod = null,
        ?string $endpoint = null
    ): self {
        $message = sprintf('%s with ID "%s" not found', ucfirst($resourceType), $resourceId);

        return new self(
            message: $message,
            httpMethod: $httpMethod,
            endpoint: $endpoint,
            httpStatusCode: 404,
            resourceType: $resourceType,
            resourceId: $resourceId
        );
    }
}
