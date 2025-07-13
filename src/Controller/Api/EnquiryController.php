<?php

namespace App\Controller\Api;

use App\Service\EnquiryService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/enquiries', name: 'api_enquiries_')]
class EnquiryController extends AbstractController
{
    public function __construct(
        private readonly EnquiryService $enquiryService,
    ) {}

    #[Route('', name: 'create', methods: ['POST'])]
    public function createEnquiry(Request $request): JsonResponse
    {
        try {
            $data = $this->getRequestData($request);

            $this->validateEnquiryData($data);

            $result = $this->enquiryService->createEnquiry(
                subscriberId: $data['subscriberId'],
                message: $data['message'],
                metadata: $data['metadata'] ?? null
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

    private function validateEnquiryData(array $data): void
    {
        if (empty($data['subscriberId'])) {
            throw new \InvalidArgumentException('Subscriber ID is required');
        }

        if (empty($data['message'])) {
            throw new \InvalidArgumentException('Message is required');
        }

        if (!is_string($data['message'])) {
            throw new \InvalidArgumentException('Message must be a string');
        }
    }
}
