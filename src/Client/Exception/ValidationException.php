<?php

namespace App\Client\Exception;

class ValidationException extends CrmApiException
{
    private array $validationErrors;

    public function __construct(
        string $message = '',
        array $validationErrors = [],
        int $code = 400,
        ?\Exception $previous = null,
        ?array $context = null,
        ?string $httpMethod = null,
        ?string $endpoint = null,
        ?int $httpStatusCode = null
    ) {
        parent::__construct($message, $code, $previous, $context, $httpMethod, $endpoint, $httpStatusCode);
        $this->validationErrors = $validationErrors;
    }

    public function getValidationErrors(): array
    {
        return $this->validationErrors;
    }

    public function hasValidationErrors(): bool
    {
        return !empty($this->validationErrors);
    }

    public function getFieldErrors(string $fieldName): array
    {
        return array_filter($this->validationErrors, function ($error) use ($fieldName) {
            return isset($error['field']) && $error['field'] === $fieldName;
        });
    }

    public function getFormattedValidationMessage(): string
    {
        if (empty($this->validationErrors)) {
            return $this->getMessage();
        }

        $message = $this->getMessage() . "\nValidation errors:\n";

        foreach ($this->validationErrors as $error) {
            $fieldName = $error['field'] ?? 'unknown';
            $errorMessage = $error['message'] ?? 'Invalid value';
            $message .= "- {$fieldName}: {$errorMessage}\n";
        }

        return trim($message);
    }
}
