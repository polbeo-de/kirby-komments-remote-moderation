<?php

declare(strict_types=1);

namespace Polbeo\KirbyKommentsRemoteModeration\Security;

interface NonceStore
{
    /** Returns false if the nonce was already present. */
    public function claim(string $key, int $ttlSeconds): bool;
}
