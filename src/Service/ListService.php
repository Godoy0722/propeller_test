<?php

namespace App\Service;

use App\Client\CrmApiClient;
use App\Client\Exception\CrmApiException;
use App\Response\ListResponse;

class ListService
{
    private const MARKETING_LISTS = [
        'london',
        'birmingham',
        'edinburgh'
    ];

    private const CACHE_TTL = 300;
    private static array $listCache = [];
    private static int $cacheTime = 0;

    public function __construct(
        private readonly CrmApiClient $crmClient,
    ) {}

    public function getAllLists(): ListResponse
    {
        try {
            $lists = $this->fetchLists();

            return ListResponse::collection($lists);

        } catch (CrmApiException $e) {
            return ListResponse::error(
                message: 'Failed to retrieve lists: ' . $e->getMessage(),
                errors: [$e->getMessage()]
            );
        }
    }

    public function getListsForSubscriber(bool $hasMarketingConsent, array $selectedLists = []): array
    {
        try {
            $allLists = $this->fetchLists();

            if (!$hasMarketingConsent) {
                return [];
            }

            $marketingLists = $this->filterMarketingLists($allLists);

            if (empty($selectedLists)) {
                return $marketingLists;
            }

            $filteredLists = array_filter($marketingLists, function($list) use ($selectedLists) {
                return in_array(strtolower($list['name']), array_map('strtolower', $selectedLists)) ||
                       in_array($list['id'], $selectedLists);
            });

            return array_values($filteredLists);

        } catch (CrmApiException $e) {
            return [];
        }
    }

    public function getListIdsForSubscriber(bool $hasMarketingConsent, array $selectedLists = []): array
    {
        $lists = $this->getListsForSubscriber($hasMarketingConsent, $selectedLists);
        return array_map(fn($list) => $list['id'], $lists);
    }

    public function validateSelectedLists(array $selectedLists): array
    {
        $errors = [];

        if (empty($selectedLists)) {
            return $errors;
        }

        try {
            $marketingLists = $this->filterMarketingLists($this->fetchLists());
            $availableListNames = array_map('strtolower', array_map(fn($list) => $list['name'], $marketingLists));
            $availableListIds = array_map(fn($list) => $list['id'], $marketingLists);

            foreach ($selectedLists as $selectedList) {
                $listName = strtolower($selectedList);

                if (!in_array($listName, $availableListNames) && !in_array($selectedList, $availableListIds)) {
                    $errors[] = "Invalid list selection: '{$selectedList}'. Available lists: " . implode(', ', self::MARKETING_LISTS);
                }
            }

        } catch (CrmApiException $e) {
            $errors[] = 'Unable to validate lists: ' . $e->getMessage();
        }

        return $errors;
    }

    public function isMarketingList(string $listName): bool
    {
        return in_array(strtolower($listName), self::MARKETING_LISTS);
    }

    private function fetchLists(): array
    {
        $currentTime = time();

        if (!empty(self::$listCache) && ($currentTime - self::$cacheTime) < self::CACHE_TTL) {
            return self::$listCache;
        }

        $lists = $this->crmClient->getSubscriberLists();

        self::$listCache = $lists;
        self::$cacheTime = $currentTime;

        return $lists;
    }

    private function filterMarketingLists(array $lists): array
    {
        return array_filter($lists, function($list) {
            if (is_array($list) && isset($list['name'])) {
                return $this->isMarketingList($list['name']);
            }
            return false;
        });
    }
}
