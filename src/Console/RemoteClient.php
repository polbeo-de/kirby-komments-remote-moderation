<?php

declare(strict_types=1);

namespace Polbeo\KirbyKommentsRemoteModeration\Console;

use Polbeo\KirbyKommentsRemoteModeration\Security\Clock;
use Polbeo\KirbyKommentsRemoteModeration\Security\HmacSigner;
use Polbeo\KirbyKommentsRemoteModeration\Security\SystemClock;
use Polbeo\KirbyKommentsRemoteModeration\Transport\HttpTransport;
use Polbeo\KirbyKommentsRemoteModeration\Transport\KirbyHttpTransport;

final class RemoteClient
{
    public function __construct(
        private readonly HttpTransport $transport = new KirbyHttpTransport(),
        private readonly Clock $clock = new SystemClock(),
        private readonly HmacSigner $signer = new HmacSigner(),
    ) {
    }

    /** @return array<string, mixed> */
    public function pending(RemoteSource $source): array
    {
        return $this->request($source, 'GET', '/komments-remote-moderation/v1/pending', '');
    }

    /** @return array<string, mixed> */
    public function moderate(RemoteSource $source, string $id, string $action, string $updatedAt): array
    {
        $body = json_encode([
            'id' => $id,
            'action' => $action,
            'updatedAt' => $updatedAt,
            'idempotencyKey' => bin2hex(random_bytes(16)),
        ], JSON_THROW_ON_ERROR);
        return $this->request($source, 'POST', '/komments-remote-moderation/v1/moderate', $body);
    }

    /** @return array<string, mixed> */
    private function request(RemoteSource $source, string $method, string $path, string $body): array
    {
        $timestamp = $this->clock->now();
        $nonce = rtrim(strtr(base64_encode(random_bytes(24)), '+/', '-_'), '=');
        $headers = [
            'X-Komments-Key-Id' => $source->keyId,
            'X-Komments-Timestamp' => (string) $timestamp,
            'X-Komments-Nonce' => $nonce,
            'X-Komments-Body-Hash' => $this->signer->bodyHash($body),
            'X-Komments-Signature' => $this->signer->sign($source->secret, $method, $path, $timestamp, $nonce, $body),
        ];
        if ($body !== '') {
            $headers['Content-Type'] = 'application/json';
        }
        if ($source->basicAuthUser !== null) {
            $headers['Authorization'] = 'Basic ' . base64_encode($source->basicAuthUser . ':' . $source->basicAuthPassword);
        }
        try {
            $response = $this->transport->send($method, $source->baseUrl . $path, $body, $headers);
            $payload = json_decode($response->body, true, 512, JSON_THROW_ON_ERROR);
        } catch (\Throwable $exception) {
            throw new RemoteUnavailable('Remote source unavailable', previous: $exception);
        }
        if (!is_array($payload)) {
            throw new RemoteUnavailable('Remote source returned an invalid response');
        }
        if ($response->status === 409) {
            throw new RemoteConflict($payload);
        }
        if ($response->status < 200 || $response->status >= 300) {
            throw new RemoteUnavailable('Remote source rejected the request');
        }
        return $payload;
    }
}
