<?php

declare(strict_types=1);

namespace Polbeo\KirbyKommentsRemoteModeration\Security;

/** In-memory adapter used by tests only. */
final class ArrayNonceStore implements NonceStore
{
    /** @var array<string, int> */
    private array $claims = [];

    public function __construct(private readonly Clock $clock)
    {
    }

    public function claim(string $key, int $ttlSeconds): bool
    {
        $now = $this->clock->now();
        foreach ($this->claims as $claimedKey => $expiresAt) {
            if ($expiresAt <= $now) {
                unset($this->claims[$claimedKey]);
            }
        }

        if (isset($this->claims[$key])) {
            return false;
        }

        $this->claims[$key] = $now + $ttlSeconds;
        return true;
    }
}
