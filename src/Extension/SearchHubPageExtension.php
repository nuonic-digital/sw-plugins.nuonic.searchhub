<?php

declare(strict_types=1);

namespace NuonicSearchHubIntegration\Extension;

use Shopware\Core\Framework\Struct\Struct;

class SearchHubPageExtension extends Struct
{
    public const EXTENSION_NAME = 'searchHubGenericPageExtension';

    public function __construct(
        public bool $smartQueryState,
        public bool $smartSuggestState,
        public string $scriptId,
    ) {
    }
}
