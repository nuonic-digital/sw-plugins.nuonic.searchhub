<?php

declare(strict_types=1);

namespace NuonicSearchHubIntegration\Client;

use NuonicSearchHubIntegration\Config\ConfigValue;
use NuonicSearchHubIntegration\Config\PluginConfigService;
use NuonicSearchHubIntegration\Exception\InvalidConfigurationException;
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

    public function make(string $salesChannelId): SearchHubClient
    {
        $serviceUrl = trim($this->config->getString(ConfigValue::SERVICE_URL, $salesChannelId));

        if ('' === $serviceUrl) {
            throw new InvalidConfigurationException('Required config serviceUrl is missing');
        }

        if ($this->config->getBool(ConfigValue::SMART_SUGGEST_STATE, $salesChannelId)) {
            $smartSuggestLimit = $this->config->getInt(ConfigValue::SMART_SUGGEST_LIMIT, $salesChannelId);

            if (empty($smartSuggestLimit)) {
                $smartSuggestLimit = null;
            }

            if ($smartSuggestLimit < 0) {
                $smartSuggestLimit = null;
            }
        }

        return new SearchHubClient(
            $this->httpClient,
            $this->logger,
            new ClientConfiguration(
                $serviceUrl,
                $this->config->get(ConfigValue::TENANT_NAME, $salesChannelId) ?? 'test',
                $this->config->get(ConfigValue::TENANT_CHANNEL, $salesChannelId) ?? 'working',
                $smartSuggestLimit ?? null,
            )
        );
    }
}
