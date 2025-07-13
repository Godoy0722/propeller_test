<?php

namespace App\Service;

use App\Client\CrmApiClient;
use App\Client\Exception\CrmApiException;

use App\Exception\ValidationException;
use App\Response\SubscriberResponse;
use DateTime;
use DateTimeInterface;

class SubscriberService
{
    private const MINIMUM_AGE_YEARS = 18;
    private const EMAIL_MAX_LENGTH = 255;
    private const NAME_MAX_LENGTH = 255;

    public function __construct(
        private readonly CrmApiClient $crmClient,
    ) {}

    public function createSubscriber(
        string $emailAddress,
        ?string $firstName = null,
        ?string $lastName = null,
        ?string $dateOfBirth = null,
        bool $marketingConsent = false,
        array $listIds = []
    ): SubscriberResponse {
        try {
            $this->validateSubscriberData($emailAddress, $firstName, $lastName, $dateOfBirth);

            $subscriberData = [
                'emailAddress' => $emailAddress,
                'firstName' => $firstName,
                'lastName' => $lastName,
                'dateOfBirth' => $dateOfBirth,
                'marketingConsent' => $marketingConsent,
                'listIds' => $listIds
            ];

            $apiResponse = $this->crmClient->createSubscriber($subscriberData);

            return SubscriberResponse::created($apiResponse);

        } catch (CrmApiException $e) {
            return SubscriberResponse::error(
                message: 'Failed to create subscriber: ' . $e->getMessage(),
                errors: [$e->getMessage()]
            );

        } catch (ValidationException $e) {
            return SubscriberResponse::subscriberValidationError($e->getErrors());
        }
    }

    public function createOrUpdateSubscriber(
        string $emailAddress,
        ?string $firstName = null,
        ?string $lastName = null,
        ?string $dateOfBirth = null,
        bool $marketingConsent = false,
        array $listIds = []
    ): SubscriberResponse {
        try {
            $this->validateSubscriberData($emailAddress, $firstName, $lastName, $dateOfBirth);

            $subscriberData = [
                'emailAddress' => $emailAddress,
                'firstName' => $firstName,
                'lastName' => $lastName,
                'dateOfBirth' => $dateOfBirth,
                'marketingConsent' => $marketingConsent,
                'listIds' => $listIds
            ];

            $apiResponse = $this->crmClient->createOrUpdateSubscriber($subscriberData);

            return SubscriberResponse::updated($apiResponse);

        } catch (CrmApiException $e) {
            return SubscriberResponse::error(
                message: 'Failed to create or update subscriber: ' . $e->getMessage(),
                errors: [$e->getMessage()]
            );

        } catch (ValidationException $e) {
            return SubscriberResponse::subscriberValidationError($e->getErrors());
        }
    }

    private function validateSubscriberData(
        string $emailAddress,
        ?string $firstName,
        ?string $lastName,
        ?string $dateOfBirth
    ): void {
        $errors = [];

        if (!filter_var($emailAddress, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email address format';
        }

        if (strlen($emailAddress) > self::EMAIL_MAX_LENGTH) {
            $errors[] = sprintf('Email address cannot exceed %d characters', self::EMAIL_MAX_LENGTH);
        }

        if ($firstName !== null && strlen($firstName) > self::NAME_MAX_LENGTH) {
            $errors[] = sprintf('First name cannot exceed %d characters', self::NAME_MAX_LENGTH);
        }

        if ($lastName !== null && strlen($lastName) > self::NAME_MAX_LENGTH) {
            $errors[] = sprintf('Last name cannot exceed %d characters', self::NAME_MAX_LENGTH);
        }

        if ($dateOfBirth !== null) {
            $birthDate = DateTime::createFromFormat('Y-m-d', $dateOfBirth);
            if (!$birthDate || $birthDate->format('Y-m-d') !== $dateOfBirth) {
                $errors[] = 'Date of birth must be in Y-m-d format';
            } else {
                $age = $this->calculateAge($birthDate);
                if ($age < self::MINIMUM_AGE_YEARS) {
                    $errors[] = sprintf('Subscriber must be at least %d years old', self::MINIMUM_AGE_YEARS);
                }
            }
        }

        if (!empty($errors)) {
            throw new ValidationException('Subscriber validation failed', $errors);
        }
    }

    private function calculateAge(DateTimeInterface $birthDate): int
    {
        $today = new DateTime();
        $age = $today->diff($birthDate);
        return $age->y;
    }

    public function isValidAge(?string $dateOfBirth): bool
    {
        if ($dateOfBirth === null) {
            return false;
        }

        $birthDate = DateTime::createFromFormat('Y-m-d', $dateOfBirth);
        if (!$birthDate) {
            return false;
        }

        return $this->calculateAge($birthDate) >= self::MINIMUM_AGE_YEARS;
    }

    public function getMinimumAge(): int
    {
        return self::MINIMUM_AGE_YEARS;
    }
}
