<?php

namespace App\Controller\Api;

use App\Client\CrmApiClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api', name: 'api_')]
class ListController extends AbstractController
{
    public function __construct(
        private readonly CrmApiClient $crmClient,
    ) {}

    #[Route('/lists', name: 'lists', methods: ['GET'])]
    public function getAllLists(): Response
    {
        try {
            $data = $this->crmClient->getRawSubscriberLists();
            return $this->json($data);
        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Failed to retrieve lists',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
