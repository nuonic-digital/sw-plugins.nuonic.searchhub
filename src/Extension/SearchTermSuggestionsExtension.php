<?php

declare(strict_types=1);

namespace NuonicSearchHubIntegration\Extension;

use Shopware\Core\Framework\Struct\Struct;

class SearchTermSuggestionsExtension extends Struct
{
    public const EXTENSION_NAME = 'nuonicSearchHubIntegrationSearchTermSuggestions';

    /** @param string[] $suggestions */
    public function __construct(
        public ?array $suggestions = [],
    ) {
    }
}
