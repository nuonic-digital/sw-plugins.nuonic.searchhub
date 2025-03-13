<?php

declare(strict_types=1);

namespace NuonicSearchHubIntegration\Client;

class ClientConfiguration
{
    public function __construct(
        public string $baseUrl,
        public string $tenantName,
        public string $tenantChannel,
        public ?int $smartSuggestLimit = null,
    ) {
    }
}
