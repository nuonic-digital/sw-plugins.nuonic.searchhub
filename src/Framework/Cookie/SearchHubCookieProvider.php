<?php

declare(strict_types=1);

namespace NuonicSearchHub\Framework\Cookie;

use NuonicSearchHub\Config\ConfigValue;
use NuonicSearchHub\Config\PluginConfigService;
use Shopware\Storefront\Framework\Cookie\CookieProviderInterface;
use Symfony\Component\HttpFoundation\RequestStack;

readonly class SearchHubCookieProvider implements CookieProviderInterface
{
    private const COOKIE = [
        'snippet_name' => 'NuonicSearchHub.cookie.name',
        'snippet_description' => 'NuonicSearchHub.cookie.description ',
        'cookie' => 'NuonicSearchHub-js',
        'value' => '1',
        'expiration' => '30',
    ];

    private const COOKIE_GROUP = [
        'snippet_name' => 'NuonicSearchHub.cookie.groupStatistical',
        'snippet_description' => 'NuonicSearchHub.cookie.groupStatisticalDescription',
        'entries' => [
            self::COOKIE,
        ],
    ];

    public function __construct(
        private CookieProviderInterface $originalService,
        private RequestStack $requestStack,
        private PluginConfigService $config,
    ) {
    }

    public function getCookieGroups(): array
    {
        $cookies = $this->originalService->getCookieGroups();

        $request = $this->requestStack->getCurrentRequest();
        $salesChannelId = $request?->attributes->get('sw-sales-channel-id');

        if (!$this->config->getBool(ConfigValue::ACTIVE_STATE, $salesChannelId)) {
            return $cookies;
        }

        foreach ($cookies as &$cookie) {
            if (!\is_array($cookie)) {
                continue;
            }

            if (!$this->isStatisticalCookieGroup($cookie)) {
                continue;
            }

            if (!array_key_exists('entries', $cookie)) {
                continue;
            }

            $cookie['entries'][] = [
                'snippet_name' => 'NuonicSearchHub.cookie.name',
                'snippet_description' => 'NuonicSearchHub.cookie.description',
                'cookie' => 'NuonicSearchHub-js',
                'value' => '1',
                'expiration' => '30',
            ];

            return $cookies;
        }

        return array_merge($cookies, [self::COOKIE_GROUP]);
    }

    private function isStatisticalCookieGroup(array $cookie): bool
    {
        return array_key_exists('snippet_name', $cookie)
            && 'cookie.groupStatistical' === $cookie['snippet_name'];
    }
}
