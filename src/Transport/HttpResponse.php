<?php

declare(strict_types=1);

namespace Polbeo\KirbyKommentsRemoteModeration\Transport;

final readonly class HttpResponse
{
    public function __construct(public int $status, public string $body)
    {
    }
}
