<?php

declare(strict_types=1);

namespace NuonicSearchHubIntegration\Client;

use NuonicSearchHubIntegration\Config\PluginConfigService;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

readonly class ClientFactory
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private PluginConfigService $config,
        private LoggerInterface $logger,
    ) {
    }

    public function make(): SearchHubClient
    {
        return new SearchHubClient(
            $this->httpClient,
            $this->logger,
            new ClientConfiguration(
                $this->config->getString('baseUrl'),
                $this->config->get('tenantName') ?? 'test',
                $this->config->get('tenantChannel') ?? 'working'
            )
        );
    }
}
