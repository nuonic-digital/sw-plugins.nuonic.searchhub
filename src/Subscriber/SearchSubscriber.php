<?php

declare(strict_types=1);

namespace NuonicSearchHubIntegration\Subscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

readonly class SearchSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            'frontend.search.suggest.request' => 'onSearchSuggestRequest',
        ];
    }

    public function onSearchSuggestRequest(): void
    {
        $req = func_get_args();
    }
}
