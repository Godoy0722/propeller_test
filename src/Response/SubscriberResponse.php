<?php

namespace App\Response;

class SubscriberResponse extends ApiResponse
{
    public static function created(
        array $subscriber,
        ?string $message = 'Subscriber created successfully'
    ): self {
        return new self(
            success: true,
            data: [
                'subscriber' => $subscriber
            ],
            message: $message,
            status: 201
        );
    }

    public static function updated(
        array $subscriber,
        ?string $message = 'Subscriber updated successfully'
    ): self {
        return new self(
            success: true,
            data: [
                'subscriber' => $subscriber
            ],
            message: $message
        );
    }

    public static function retrieved(
        array $subscriber,
        ?string $message = 'Subscriber retrieved successfully'
    ): self {
        return new self(
            success: true,
            data: [
                'subscriber' => $subscriber
            ],
            message: $message
        );
    }

    public static function list(
        array $subscribers,
        ?string $message = 'Subscribers retrieved successfully'
    ): self {
        return new self(
            success: true,
            data: [
                'subscribers' => $subscribers
            ],
            message: $message
        );
    }

    public static function subscriberValidationError(
        array $errors,
        ?string $message = 'Subscriber validation failed'
    ): self {
        return new self(
            success: false,
            message: $message,
            errors: $errors,
            status: 422
        );
    }

    public static function subscriberNotFound(
        ?string $subscriberId = null,
        ?string $message = 'Subscriber not found'
    ): self {
        return parent::notFound(
            message: $message,
            data: $subscriberId ? ['subscriberId' => $subscriberId] : null
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
}
