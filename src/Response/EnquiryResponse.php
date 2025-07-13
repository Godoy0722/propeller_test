<?php


namespace App\Response;

class EnquiryResponse extends ApiResponse
{
    public static function created(
        array $enquiry,
        ?array $subscriber = null,
        ?string $message = 'Enquiry created successfully'
    ): self {
        $data = [
            'enquiry' => $enquiry
        ];

        if ($subscriber !== null) {
            $data['subscriber'] = $subscriber;
        }

        return new self(
            success: true,
            data: $data,
            message: $message,
            status: 201
        );
    }

    public static function retrieved(
        array $enquiry,
        ?string $message = 'Enquiry retrieved successfully'
    ): self {
        return new self(
            success: true,
            data: [
                'enquiry' => $enquiry
            ],
            message: $message
        );
    }

    public static function list(
        array $enquiries,
        ?array $subscriber = null,
        ?string $message = 'Enquiries retrieved successfully'
    ): self {
        $data = [
            'enquiries' => $enquiries
        ];

        if ($subscriber !== null) {
            $data['subscriber'] = $subscriber;
        }

        return new self(
            success: true,
            data: $data,
            message: $message
        );
    }

    public static function enquiryValidationError(
        array $errors,
        ?string $message = 'Enquiry validation failed'
    ): self {
        return parent::validationError($errors, $message);
    }

    public static function enquiryNotFound(
        ?string $enquiryId = null,
        ?string $message = 'Enquiry not found'
    ): self {
        return parent::notFound(
            message: $message,
            data: $enquiryId ? ['enquiryId' => $enquiryId] : null
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
