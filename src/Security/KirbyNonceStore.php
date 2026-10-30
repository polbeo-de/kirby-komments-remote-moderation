<?php

declare(strict_types=1);

namespace Polbeo\KirbyKommentsRemoteModeration\Security;

use Kirby\Cms\App;

final class KirbyNonceStore implements NonceStore
{
    public function __construct(private readonly App $kirby)
    {
    }

    public function claim(string $key, int $ttlSeconds): bool
    {
        $cache = $this->kirby->cache('polbeo.komments-remote-moderation.nonces');
        $cacheKey = hash('sha256', $key);
        if ($cache->get($cacheKey) !== null) {
            return false;
        }

        $cache->set($cacheKey, true, $ttlSeconds);
        return true;
    }
}
