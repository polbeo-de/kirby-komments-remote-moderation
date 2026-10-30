<?php

declare(strict_types=1);

namespace Polbeo\KirbyKommentsRemoteModeration\Security;

final readonly class VerificationResult
{
    private function __construct(public bool $valid, public ?string $keyId, public ?string $reason)
    {
    }

    public static function valid(string $keyId): self
    {
        return new self(true, $keyId, null);
    }

    public static function invalid(string $reason): self
    {
        return new self(false, null, $reason);
    }
}
