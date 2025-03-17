<?php

declare(strict_types=1);

namespace NuonicSearchHubIntegration\Extension;

use Shopware\Core\Framework\Struct\Struct;

class SearchHubSmartQueryProductSearchRouteExtension extends Struct
{
    public const EXTENSION_NAME = 'searchHubSmartQuery';

    public string $userQuery;
    public ?string $masterQuery;
    public string $searchQuery;
    public bool $successful;
    public ?string $redirect;
    public ?array $potentialCorrections;
    public ?array $relatedQueries;
    public ?array $resultModifications;
}
