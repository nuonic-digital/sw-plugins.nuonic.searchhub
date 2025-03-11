<?php

declare(strict_types=1);

namespace NuonicSearchHubIntegration\Content\Product\SalesChannel\Search;

use NuonicSearchHubIntegration\Client\SearchHubClient;
use Shopware\Core\Content\Product\SalesChannel\Search\AbstractProductSearchRoute;
use Shopware\Core\Content\Product\SalesChannel\Search\ProductSearchRouteResponse;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Routing\RoutingException;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class SearchHubSmartQueryRoute extends AbstractProductSearchRoute
{
    public function __construct(
        private readonly AbstractProductSearchRoute $decorated,
        private readonly SearchHubClient $client,
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
        if (!$request->query->has('search')) {
            throw RoutingException::missingRequestParameter('search');
        }

        $request->query->set(
            'search',
            $this->client->smartQuery($request->get('search')),
        );

        return $this->decorated->load($request, $context, $criteria);
    }
}
