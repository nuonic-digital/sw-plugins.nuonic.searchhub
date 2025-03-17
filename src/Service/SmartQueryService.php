<?php

declare(strict_types=1);

namespace NuonicSearchHubIntegration\Service;

use NuonicSearchHubIntegration\Client\ClientFactory;
use NuonicSearchHubIntegration\Client\SearchHubClient;

/**
 * @phpstan-import-type SmartQueryResult from SearchHubClient
 */
class SmartQueryService
{
    /** @var array<string, SmartQueryResult>  */
    private array $queryCache = [];

    public function __construct(
        private readonly ClientFactory $clientFactory
    ) {
    }

    /**
     * @param string $userQuery
     * @param string|null $salesChannelId
     * @return SmartQueryResult|null
     */
    public function query(string $userQuery, ?string $salesChannelId): ?array
    {
        $key = $salesChannelId . ':' . $userQuery;
        if (!isset($this->queryCache[$key])) {
            $this->queryCache[$key] = $this->clientFactory->make(
                $salesChannelId
            )->smartQuery($userQuery);
        }

        return $this->queryCache[$key];
    }
}
