<?php

namespace App\Controller\Api;

use App\Service\SubscriberService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/subscribers', name: 'api_subscribers_')]
class SubscriberController extends AbstractController
{
    public function __construct(
        private readonly SubscriberService $subscriberService,
    ) {}

    #[Route('', name: 'create', methods: ['POST'])]
    public function createSubscriber(Request $request): JsonResponse
    {
        try {
            $data = $this->getRequestData($request);

            $this->validateSubscriberData($data);


            $result = $this->subscriberService->createSubscriber(
                emailAddress: $data['emailAddress'],
                firstName: $data['firstName'] ?? null,
                lastName: $data['lastName'] ?? null,
                dateOfBirth: $data['dateOfBirth'] ?? null,
                marketingConsent: $data['marketingConsent'] ?? false,
                listIds: $data['listIds'] ?? []
            );

            return $result;

        } catch (\InvalidArgumentException $e) {
            return $this->json([
                'success' => false,
                'message' => 'Invalid request data',
                'errors' => [$e->getMessage()],
                'timestamp' => date('c')
            ], 400);

        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'An unexpected error occurred',
                'errors' => ['Internal server error'],
                'timestamp' => date('c')
            ], 500);
        }
    }

    private function getRequestData(Request $request): array
    {
        $contentType = $request->headers->get('Content-Type');

        if (!str_contains($contentType, 'application/json')) {
            throw new \InvalidArgumentException('Request must be JSON');
        }

        $data = json_decode($request->getContent(), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \InvalidArgumentException('Invalid JSON: ' . json_last_error_msg());
        }

        if (!is_array($data)) {
            throw new \InvalidArgumentException('Request data must be an object');
        }

        return $data;
    }

    private function validateSubscriberData(array $data): void
    {
        if (empty($data['emailAddress'])) {
            throw new \InvalidArgumentException('Email address is required');
        }

        if (empty($data['dateOfBirth'])) {
            throw new \InvalidArgumentException('Date of birth is required');
        }

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['dateOfBirth'])) {
            throw new \InvalidArgumentException('Date of birth must be in Y-m-d format');
        }
    }
}
