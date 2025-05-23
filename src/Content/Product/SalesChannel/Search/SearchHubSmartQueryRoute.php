<?php

declare(strict_types=1);

namespace NuonicSearchHub\Content\Product\SalesChannel\Search;

use NuonicSearchHub\Client\ClientFactory;
use NuonicSearchHub\Config\ConfigValue;
use NuonicSearchHub\Config\PluginConfigService;
use NuonicSearchHub\Controller\RedirectingSearchController;
use NuonicSearchHub\Extension\SearchHubSmartQueryProductSearchRouteExtension;
use Shopware\Core\Content\Product\SalesChannel\Search\AbstractProductSearchRoute;
use Shopware\Core\Content\Product\SalesChannel\Search\ProductSearchRouteResponse;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Routing\RoutingException;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class SearchHubSmartQueryRoute extends AbstractProductSearchRoute
{
    private const QUERY_KEY = 'search';

    public function __construct(
        private readonly AbstractProductSearchRoute $decorated,
        private readonly PluginConfigService $config,
        private readonly ClientFactory $clientFactory,
    ) {
    }

    public function getDecorated(): AbstractProductSearchRoute
    {
        return $this->decorated;
    }

    #[Route(
        path: '/store-api/search',
        name: 'store-api.search',
        defaults: ['_entity' => 'product'],
        methods: ['POST'],
    )]
    public function load(Request $request, SalesChannelContext $context, Criteria $criteria): ProductSearchRouteResponse
    {
        // if the smartQuery feature is not enabled, skip
        if (!$this->config->getBool(ConfigValue::SMART_QUERY_STATE)) {
            return $this->decorated->load($request, $context, $criteria);
        }

        if (!$request->query->has(self::QUERY_KEY)) {
            throw RoutingException::missingRequestParameter(self::QUERY_KEY);
        }

        $userSearch = $request->get(self::QUERY_KEY);

        if (!$request->attributes->has(RedirectingSearchController::SMART_QUERY_RESULT_REQUEST_ATTR)) {
            // sessionId will evaluate to null when the cookie is not set or the $request is null
            $sessionId = $request->cookies->has('SearchCollectorSession') ?
                $request->cookies->get('SearchCollectorSession') : null;

            $smartQueryResult = $this->clientFactory->make($context->getSalesChannelId())
                ->smartQuery($userSearch, $sessionId);
        } else {
            $smartQueryResult = $request->attributes->get(RedirectingSearchController::SMART_QUERY_RESULT_REQUEST_ATTR);
        }

        $request->query->set(self::QUERY_KEY, $smartQueryResult['searchQuery'] ?? $userSearch);

        $result = $this->decorated->load($request, $context, $criteria);

        if (str_starts_with($userSearch, '"') && str_ends_with($userSearch, '"')) {
            $request->query->set(self::QUERY_KEY, substr($userSearch, 1, -1));
        }

        if (is_array($smartQueryResult) && $smartQueryResult['masterQuery'] !== $userSearch && !str_starts_with($userSearch, '"')) {
            $result->getListingResult()->addExtension(
                SearchHubSmartQueryProductSearchRouteExtension::EXTENSION_NAME,
                (new SearchHubSmartQueryProductSearchRouteExtension())->assign($smartQueryResult)
            );
        }

        return $result;
    }
}
