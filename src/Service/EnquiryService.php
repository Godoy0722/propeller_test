<?php

namespace App\Service;

use App\Client\CrmApiClient;
use App\Client\Exception\CrmApiException;
use App\Exception\ValidationException;
use App\Response\EnquiryResponse;

class EnquiryService
{
    private const MESSAGE_MAX_LENGTH = 10000;
    private const MESSAGE_MIN_LENGTH = 1;

    public function __construct(
        private readonly CrmApiClient $crmClient,
    ) {}

    public function createEnquiry(string $subscriberId, string $message): EnquiryResponse {
        try {
            $this->validateEnquiryData($message);

            $enquiryData = [
                'message' => $message
            ];

            $apiResponse = $this->crmClient->createEnquiry($subscriberId, $enquiryData);

            return EnquiryResponse::created($apiResponse);

        } catch (CrmApiException $e) {
            return EnquiryResponse::error(
                message: 'Failed to create enquiry: ' . $e->getMessage(),
                errors: [$e->getMessage()]
            );

        } catch (ValidationException $e) {
            return EnquiryResponse::enquiryValidationError($e->getErrors());
        } catch (\Exception $e) {
            return EnquiryResponse::error(
                message: 'Unexpected error: ' . $e->getMessage(),
                errors: [$e->getMessage()]
            );
        }
    }

    public function createEnquiryFromSubscriber(array $subscriber, string $message ): EnquiryResponse {
        return $this->createEnquiry($subscriber['id'], $message);
    }

    private function validateEnquiryData(string $message): void
    {
        $errors = [];
        $messageLength = strlen($message);

        if ($messageLength < self::MESSAGE_MIN_LENGTH) {
            $errors[] = 'Message cannot be empty';
        }

        if ($messageLength > self::MESSAGE_MAX_LENGTH) {
            $errors[] = sprintf('Message cannot exceed %d characters', self::MESSAGE_MAX_LENGTH);
        }

        if (trim($message) === '') {
            $errors[] = 'Message cannot be only whitespace';
        }

        if (!empty($errors)) {
            throw new ValidationException('Enquiry validation failed', $errors);
        }
    }

    public function validateMessage(string $message): array
    {
        $errors = [];

        try {
            $this->validateEnquiryData($message);
        } catch (ValidationException $e) {
            $errors = $e->getErrors();
        }

        return $errors;
    }
}
