<?php

declare(strict_types=1);

namespace NuonicSearchHub\Extension;

use Shopware\Core\Framework\Struct\Struct;

class SearchHubSmartQueryProductSearchRouteExtension extends Struct
{
    public const EXTENSION_NAME = 'searchHubSmartQuery';

    public string $userQuery;
    public ?string $masterQuery;
    public string $searchQuery;
    public bool $successful;
    public ?string $redirect;
    /** @var string[]|null */
    public ?array $potentialCorrections;
    /** @var string[]|null */
    public ?array $relatedQueries;
    /** @var string[]|null */
    public ?array $resultModifications;
}
