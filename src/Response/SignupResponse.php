<?php

namespace App\Response;

class SignupResponse extends ApiResponse
{
    public static function signupSuccess(
        array $subscriber,
        ?array $enquiry = null,
        ?string $message = 'Signup completed successfully'
    ): self {
        $data = [
            'subscriber' => $subscriber
        ];

        if ($enquiry !== null) {
            $data['enquiry'] = $enquiry;
        }

        return new self(
            success: true,
            data: $data,
            message: $message,
            status: 201
        );
    }

    public static function updated(
        array $subscriber,
        ?array $enquiry = null,
        ?string $message = 'Signup completed - subscriber updated'
    ): self {
        $data = [
            'subscriber' => $subscriber
        ];

        if ($enquiry !== null) {
            $data['enquiry'] = $enquiry;
        }

        return new self(
            success: true,
            data: $data,
            message: $message
        );
    }

    public static function signupValidationError(
        array $errors,
        ?string $message = 'Signup validation failed'
    ): self {
        return new self(
            success: false,
            message: $message,
            errors: $errors,
            status: 422
        );
    }

    public static function ageValidationError(
        ?string $message = 'Subscriber must be at least 18 years old'
    ): self {
        return new self(
            success: false,
            errors: [
                'dateOfBirth' => [$message]
            ],
            message: $message,
            status: 422
        );
    }

    public static function marketingConsentError(
        ?string $message = 'Marketing consent is required for list subscriptions'
    ): self {
        return new self(
            success: false,
            errors: [
                'marketingConsent' => [$message]
            ],
            message: $message,
            status: 422
        );
    }

    public static function externalApiError(
        ?string $message = 'External API error occurred during signup'
    ): self {
        return new self(
            success: false,
            message: $message,
            data: [
                'type' => 'external_api_error',
                'suggestion' => 'Please try again later or contact support'
            ],
            status: 500
        );
    }

    public static function summary(
        array $data,
        ?string $message = 'Signup summary'
    ): self {
        return new self(
            success: true,
            data: $data,
            message: $message
        );
    }
}
