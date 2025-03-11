<?php

declare(strict_types=1);

namespace NuonicSearchHubIntegration\Subscriber;

use NuonicSearchHubIntegration\Config\PluginConfigService;
use NuonicSearchHubIntegration\Extension\SearchHubPageExtension;
use Shopware\Storefront\Page\GenericPageLoadedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

readonly class FrontendSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private PluginConfigService $config,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            GenericPageLoadedEvent::class => 'onRender',
        ];
    }

    public function onRender(GenericPageLoadedEvent $event): void
    {
        $salesChannelId = null;
        $page = $event->getPage();

        $activeState = $this->config->getBool('activeState', $salesChannelId);
        if (!$activeState) {
            return;
        }

        $smartQueryState = $this->config->getBool('smartQueryState', $salesChannelId);
        $smartSuggestState = $this->config->getBool('smartSuggestState', $salesChannelId);
        $scriptId = $this->config->getString('scriptId', $salesChannelId);

        $page->addExtension(SearchHubPageExtension::EXTENSION_NAME, new SearchHubPageExtension(
            $smartQueryState,
            $smartSuggestState,
            $scriptId,
        ));
    }
}
