<?php

namespace App\Service;

use App\Exception\ValidationException;
use App\Response\SignupResponse;

class SignupService
{
    public function __construct(
        private readonly SubscriberService $subscriberService,
        private readonly ListService $listService,
        private readonly EnquiryService $enquiryService
    ) {}

    public function processSignup(
        string $emailAddress,
        ?string $firstName = null,
        ?string $lastName = null,
        ?string $dateOfBirth = null,
        bool $marketingConsent = false,
        array $selectedLists = [],
        ?string $message = null
    ): SignupResponse {
        try {
            $this->validateSignupData($emailAddress, $firstName, $lastName, $dateOfBirth, $selectedLists, $message);

            $listIds = $this->listService->getListIdsForSubscriber($marketingConsent, $selectedLists);

            $subscriberResponse = $this->subscriberService->createOrUpdateSubscriber(
                emailAddress: $emailAddress,
                firstName: $firstName,
                lastName: $lastName,
                dateOfBirth: $dateOfBirth,
                marketingConsent: $marketingConsent,
                listIds: $listIds
            );

            if ($subscriberResponse->getStatusCode() !== 201 && $subscriberResponse->getStatusCode() !== 200) {
                return SignupResponse::error(
                    message: 'Failed to create subscriber',
                    errors: ['Subscriber creation failed']
                );
            }

            $responseContent = $subscriberResponse->getContent();
            $responseData = json_decode($responseContent, true);

            $subscriber = $responseData['data']['subscriber'] ?? null;

            if (!$subscriber) {
                throw new \RuntimeException('Subscriber data not found in response');
            }
            $enquiryResponse = null;

            if ($message !== null && trim($message) !== '') {
                $enquiryResponse = $this->enquiryService->createEnquiryFromSubscriber(
                    subscriber: $subscriber,
                    message: $message,
                );
            }

            $enquiryData = null;
            if ($enquiryResponse !== null && $enquiryResponse->getStatusCode() === 201) {
                $responseContent = json_decode($enquiryResponse->getContent(), true);
                if (isset($responseContent['data']['enquiry'])) {
                    $enquiryData = $responseContent['data']['enquiry'];
                }
            }

            return SignupResponse::signupSuccess(
                subscriber: $subscriber,
                enquiry: $enquiryData,
                message: 'Signup completed successfully'
            );

        } catch (ValidationException $e) {
            return SignupResponse::signupValidationError($e->getErrors());
        } catch (\Exception $e) {
            return SignupResponse::error(
                message: 'An unexpected error occurred during signup',
                errors: ['An unexpected error occurred. Please try again.']
            );
        }
    }

    private function validateSignupData(
        string $emailAddress,
        ?string $firstName,
        ?string $lastName,
        ?string $dateOfBirth,
        array $selectedLists,
        ?string $message
    ): void {
        $errors = [];

        if (!filter_var($emailAddress, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email address format';
        }

        if ($dateOfBirth !== null && !$this->subscriberService->isValidAge($dateOfBirth)) {
            $errors[] = sprintf('Subscriber must be at least %d years old', $this->subscriberService->getMinimumAge());
        }

        if (!empty($selectedLists)) {
            $listErrors = $this->listService->validateSelectedLists($selectedLists);
            $errors = array_merge($errors, $listErrors);
        }

        if ($message !== null && trim($message) !== '') {
            $messageErrors = $this->enquiryService->validateMessage($message);
            $errors = array_merge($errors, $messageErrors);
        }

        if (!empty($errors)) {
            throw new ValidationException('Signup validation failed', $errors);
        }
    }
}
