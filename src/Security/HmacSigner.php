<?php

declare(strict_types=1);

namespace Polbeo\KirbyKommentsRemoteModeration\Security;

final class HmacSigner
{
    public function bodyHash(string $body): string
    {
        return hash('sha256', $body);
    }

    public function canonical(string $method, string $path, int $timestamp, string $nonce, string $bodyHash): string
    {
        return strtoupper($method) . "\n" . $path . "\n" . $timestamp . "\n" . $nonce . "\n" . $bodyHash;
    }

    public function sign(string $secret, string $method, string $path, int $timestamp, string $nonce, string $body): string
    {
        return hash_hmac('sha256', $this->canonical($method, $path, $timestamp, $nonce, $this->bodyHash($body)), $secret);
    }
}
