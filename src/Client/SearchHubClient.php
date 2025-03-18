<?php

declare(strict_types=1);

namespace NuonicSearchHub\Client;

use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * @phpstan-type SmartQueryResult array{
 *     userQuery: string,
 *     masterQuery: ?string,
 *     searchQuery: string,
 *     successful: bool,
 *     redirect: ?string,
 *     potentialCorrections: ?string[],
 *     relatedQueries: ?string[],
 *     resultModifications: ?string[]
 * }
 */
readonly class SearchHubClient
{
    private HttpClientInterface $httpClient;

    public function __construct(
        HttpClientInterface $httpClient,
        private LoggerInterface $logger,
        private ClientConfiguration $configuration,
    ) {
        $this->httpClient = $httpClient->withOptions([
            'base_uri' => $configuration->baseUrl,
            'timeout' => 1,
            'headers' => [
                'User-Agent' => 'NuonicSearchHub/Shopware6 1.0',
            ],
        ]);
    }

    /**
     * @return SmartQueryResult|null
     */
    public function smartQuery(string $userQuery, ?string $sessionId = null): ?array
    {
        $query = [
            'userQuery' => $userQuery,
        ];

        if (!is_null($sessionId)) {
            $query['sessionId'] = $sessionId;
        }

        try {
            $response = $this->httpClient->request('GET', sprintf(
                '/smartquery/v2/%s/%s',
                $this->configuration->tenantName,
                $this->configuration->tenantChannel
            ), [
                'query' => $query,
            ]);

            return $response->toArray();
        } catch (TransportExceptionInterface|DecodingExceptionInterface|ClientExceptionInterface|RedirectionExceptionInterface|ServerExceptionInterface $e) {
            $this->logger->error($e);

            return null;
        }
    }

    /**
     * @return string[]
     */
    public function smartSuggest(string $userQuery, ?string $sessionId = null): array
    {
        $query = [
            'userQuery' => $userQuery,
        ];

        if (!is_null($sessionId)) {
            $query['sessionId'] = $sessionId;
        }

        if (!is_null($this->configuration->smartSuggestLimit)) {
            $query['limit'] = $this->configuration->smartSuggestLimit;
        }

        try {
            $response = $this->httpClient->request('GET', sprintf(
                '/smartsuggest/v2/%s/%s',
                $this->configuration->tenantName,
                $this->configuration->tenantChannel
            ), ['query' => $query]);

            return $response->toArray();
        } catch (TransportExceptionInterface|DecodingExceptionInterface|ClientExceptionInterface|RedirectionExceptionInterface|ServerExceptionInterface $e) {
            $this->logger->error($e);

            return [];
        }
    }
}
