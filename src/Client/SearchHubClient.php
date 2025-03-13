<?php

declare(strict_types=1);

namespace NuonicSearchHubIntegration\Client;

use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

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
                'User-Agent' => 'NuonicSearchHubIntegration/Shopware6 1.0',
            ],
        ]);
    }

    public function smartQuery(string $userQuery): string
    {
        try {
            $response = $this->httpClient->request('GET', sprintf(
                '/smartquery/v1/%s/%s',
                $this->configuration->tenantName,
                $this->configuration->tenantChannel
            ), [
                'query' => [
                    'userQuery' => $userQuery,
                ],
            ]);

            return $response->getContent();
        } catch (TransportExceptionInterface|ClientExceptionInterface|RedirectionExceptionInterface|ServerExceptionInterface $e) {
            $this->logger->error($e);

            return $userQuery;
        }
    }

    /**
     * @return string[]
     */
    public function smartSuggest(string $userQuery): array
    {
        try {
            $response = $this->httpClient->request('GET', sprintf(
                '/smartsuggest/v2/%s/%s',
                $this->configuration->tenantName,
                $this->configuration->tenantChannel
            ), [
                'query' => [
                    'userQuery' => $userQuery,
                ],
            ]);

            return $response->toArray();
        } catch (TransportExceptionInterface|ClientExceptionInterface|RedirectionExceptionInterface|ServerExceptionInterface $e) {
            $this->logger->error($e);

            return [];
        }
    }
}
