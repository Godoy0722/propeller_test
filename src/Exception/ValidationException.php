<?php

namespace App\Exception;

use Exception;

class ValidationException extends Exception
{
    private array $errors;

    public function __construct(string $message, array $errors = [], int $code = 0, ?Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->errors = $errors;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    public function getFirstError(): ?string
    {
        return $this->errors[0] ?? null;
    }

    public function addError(string $error): void
    {
        $this->errors[] = $error;
    }

    public function getErrorsAsString(): string
    {
        return implode('; ', $this->errors);
    }
}
