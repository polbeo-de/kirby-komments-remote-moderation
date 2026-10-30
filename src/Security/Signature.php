<?php

declare(strict_types=1);

namespace Polbeo\KirbyKommentsRemoteModeration\Security;

final readonly class Signature
{
    public function __construct(
        public string $keyId,
        public int $timestamp,
        public string $nonce,
        public string $bodyHash,
        public string $value,
    ) {
    }
}
