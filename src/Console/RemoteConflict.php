<?php

declare(strict_types=1);

namespace Polbeo\KirbyKommentsRemoteModeration\Console;

final class RemoteConflict extends \RuntimeException
{
    /** @param array<string, mixed> $response */
    public function __construct(public readonly array $response)
    {
        parent::__construct('Remote moderation conflict');
    }
}
