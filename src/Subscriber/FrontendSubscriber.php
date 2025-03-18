<?php

declare(strict_types=1);

namespace NuonicSearchHub\Subscriber;

use NuonicSearchHub\Client\ClientFactory;
use NuonicSearchHub\Config\ConfigValue;
use NuonicSearchHub\Config\PluginConfigService;
use NuonicSearchHub\Extension\SearchHubPageExtension;
use NuonicSearchHub\Extension\SearchTermSuggestionsExtension;
use Shopware\Storefront\Page\GenericPageLoadedEvent;
use Shopware\Storefront\Page\Suggest\SuggestPageLoadedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;

readonly class FrontendSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private PluginConfigService $config,
        private ClientFactory $clientFactory,
        private RequestStack $requestStack,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            GenericPageLoadedEvent::class => 'onRender',
            SuggestPageLoadedEvent::class => 'onSuggestPageLoaded',
        ];
    }

    public function onRender(GenericPageLoadedEvent $event): void
    {
        $salesChannelId = null;
        $page = $event->getPage();

        $activeState = $this->config->getBool(ConfigValue::ACTIVE_STATE, $salesChannelId);
        if (!$activeState) {
            return;
        }

        $smartQueryState = $this->config->getBool(ConfigValue::SMART_QUERY_STATE, $salesChannelId);
        $smartSuggestState = $this->config->getBool(ConfigValue::SMART_SUGGEST_STATE, $salesChannelId);
        $scriptId = $this->config->getString(ConfigValue::SCRIPT_ID, $salesChannelId);

        $page->addExtension(SearchHubPageExtension::EXTENSION_NAME, new SearchHubPageExtension(
            $smartQueryState,
            $smartSuggestState,
            $scriptId,
        ));
    }

    public function onSuggestPageLoaded(SuggestPageLoadedEvent $event): void
    {
        if (!$this->config->getBool(ConfigValue::SMART_SUGGEST_STATE, $event->getSalesChannelContext()->getSalesChannelId())) {
            return;
        }

        $request = $this->requestStack->getMainRequest();

        // sessionId will evaluate to null when the cookie is not set or the $request is null
        $sessionId = $request?->cookies->has('SearchCollectorSession') ?
            $request->cookies->get('SearchCollectorSession') : null;

        $suggestions = $this->clientFactory->make($event->getSalesChannelContext()->getSalesChannelId())
            ->smartSuggest($event->getPage()->getSearchTerm(), $sessionId);

        if (empty($suggestions)) {
            return;
        }

        $event->getPage()->addExtension(
            SearchTermSuggestionsExtension::EXTENSION_NAME,
            new SearchTermSuggestionsExtension($suggestions)
        );
    }
}
