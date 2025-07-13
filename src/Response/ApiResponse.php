<?php

namespace App\Response;

use Symfony\Component\HttpFoundation\JsonResponse;

class ApiResponse extends JsonResponse
{
    public function __construct(
        bool $success = true,
        mixed $data = null,
        ?string $message = null,
        array $errors = [],
        int $status = 200,
        array $headers = []
    ) {
        $response = [
            'success' => $success,
            'timestamp' => date('c'),
            'status' => $status
        ];

        if ($message !== null) {
            $response['message'] = $message;
        }

        if ($data !== null) {
            $response['data'] = $data;
        }

        if (!empty($errors)) {
            $response['errors'] = $errors;
        }

        parent::__construct($response, $status, $headers);
    }

    public static function success(
        mixed $data = null,
        ?string $message = null,
        int $status = 200
    ): self {
        return new self(
            success: true,
            data: $data,
            message: $message,
            status: $status
        );
    }

    public static function error(
        ?string $message = null,
        array $errors = [],
        mixed $data = null,
        int $status = 400
    ): self {
        return new self(
            success: false,
            data: $data,
            message: $message,
            errors: $errors,
            status: $status
        );
    }

    public static function validationError(
        array $errors,
        ?string $message = 'Validation failed',
        int $status = 422
    ): self {
        return new self(
            success: false,
            message: $message,
            errors: $errors,
            status: $status
        );
    }

    public static function notFound(
        ?string $message = 'Resource not found',
        mixed $data = null
    ): self {
        return new self(
            success: false,
            data: $data,
            message: $message,
            status: 404
        );
    }

    public static function serverError(
        ?string $message = 'Internal server error',
        mixed $data = null
    ): self {
        return new self(
            success: false,
            data: $data,
            message: $message,
            status: 500
        );
    }
}
