<?php

declare(strict_types=1);

namespace NuonicSearchHubIntegration\Controller;

use NuonicSearchHubIntegration\Client\ClientFactory;
use NuonicSearchHubIntegration\Config\ConfigValue;
use NuonicSearchHubIntegration\Config\PluginConfigService;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\SearchController;
use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(defaults: ['_routeScope' => ['storefront']])]
class RedirectingSearchController extends StorefrontController
{
    public const SMART_QUERY_RESULT_REQUEST_ATTR = 'nuonicSearchHubSmartQueryResult';
    public const QUERY_KEY = 'search';

    public function __construct(
        private readonly PluginConfigService $config,
        private readonly SearchController $searchController,
        private readonly ClientFactory $clientFactory,
    ) {
    }

    #[Route(path: '/search', name: 'frontend.search.page', methods: ['GET'], priority: 5000)]
    public function search(SalesChannelContext $context, Request $request): Response
    {
        if (!$request->query->has(self::QUERY_KEY)) {
            return $this->forwardToRoute('frontend.home.page');
        }

        // if the smartQuery feature is not enabled, skip
        if (!$this->config->getBool(ConfigValue::SMART_QUERY_STATE)) {
            return $this->searchController->search($context, $request);
        }

        // sessionId will evaluate to null when the cookie is not set or the $request is null
        $sessionId = $request->cookies->has('SearchCollectorSession') ?
            $request->cookies->get('SearchCollectorSession') : null;

        $smartQueryResult = $this->clientFactory->make(
            $context->getSalesChannelId()
        )->smartQuery($request->get(self::QUERY_KEY), $sessionId);

        if (isset($smartQueryResult['redirect'])) {
            return new RedirectResponse($smartQueryResult['redirect']);
        }

        $request->attributes->add([self::SMART_QUERY_RESULT_REQUEST_ATTR => $smartQueryResult]);

        return $this->searchController->search($context, $request);
    }
}
