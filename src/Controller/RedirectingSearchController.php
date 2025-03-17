<?php

declare(strict_types=1);

namespace NuonicSearchHubIntegration\Controller;

use NuonicSearchHubIntegration\Config\ConfigValue;
use NuonicSearchHubIntegration\Config\PluginConfigService;
use NuonicSearchHubIntegration\Service\SmartQueryService;
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
    public function __construct(
        private readonly PluginConfigService $config,
        private readonly SearchController $searchController,
        private readonly SmartQueryService $smartQueryService,
    ) {
    }

    #[Route(path: '/search', name: 'frontend.search.page', methods: ['GET'], priority: 5000)]
    public function search(SalesChannelContext $context, Request $request): Response
    {
        // if the smartQuery feature is not enabled, skip
        if (!$this->config->getBool(ConfigValue::SMART_QUERY_STATE)) {
            return $this->searchController->search($context, $request);
        }

        $smartQueryResult = $this->smartQueryService->query(
            $request->get('search'),
            $context->getSalesChannelId()
        );

        if (isset($smartQueryResult['redirect'])) {
            return new RedirectResponse($smartQueryResult['redirect']);
        }

        return $this->searchController->search($context, $request);
    }
}

