<?php
declare(strict_types=1);

namespace NuonicSearchHubIntegration\Subscriber;

use Shopware\Storefront\Page\GenericPageLoadedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Shopware\Core\System\SystemConfig\SystemConfigService;


class FrontendSubscriber implements EventSubscriberInterface
{

	private SystemConfigService $systemConfigService;

	public function __construct(SystemConfigService $systemConfigService)
	{
		$this->systemConfigService = $systemConfigService;
	}

	/**
	 * @inheritDoc
	 */
	public static function getSubscribedEvents(): array
	{
		return [
			GenericPageLoadedEvent::class => 'onRender',
		];
	}

	public function onRender(GenericPageLoadedEvent $event)
	{
		$salesChannelId = null;
		$page = $event->getPage();

		$activeState = $this->systemConfigService->get('NuonicSearchHubIntegration.config.nuonicSearchHubActiveState', $salesChannelId);
		$smartQueryState = $this->systemConfigService->get('NuonicSearchHubIntegration.config.nuonicSearchHubSmartQueryState', $salesChannelId);
		$smartSuggestState = $this->systemConfigService->get('NuonicSearchHubIntegration.config.nuonicSearchHubSmartSuggestState', $salesChannelId);
		$scriptId = $this->systemConfigService->get('NuonicSearchHubIntegration.config.nuonicSearchHubScriptId', $salesChannelId);

		$page->assign([
			'searchHub' => [
				'activeState' => $activeState,
				'smartQueryState' => $smartQueryState,
				'smartSuggestState' => $smartSuggestState,
				'scriptId' => $scriptId,
			]
		]);
	}
}