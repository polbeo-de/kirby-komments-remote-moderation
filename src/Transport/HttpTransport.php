<?php

declare(strict_types=1);

namespace Polbeo\KirbyKommentsRemoteModeration\Transport;

interface HttpTransport
{
    /** @param array<string, string> $headers */
    public function send(string $method, string $url, string $body, array $headers): HttpResponse;
}
