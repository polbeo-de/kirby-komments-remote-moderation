<?php

declare(strict_types=1);

namespace Polbeo\KirbyKommentsRemoteModeration\Bridge;

final readonly class GatewayActionResult
{
    private function __construct(public string $state, public ?string $updatedAt = null)
    {
    }

    public static function applied(?string $updatedAt = null): self
    {
        return new self('applied', $updatedAt);
    }

    public static function alreadyApplied(?string $updatedAt = null): self
    {
        return new self('already_applied', $updatedAt);
    }

    public static function conflict(string $updatedAt): self
    {
        return new self('conflict', $updatedAt);
    }

    public static function notFound(): self
    {
        return new self('not_found');
    }
}
