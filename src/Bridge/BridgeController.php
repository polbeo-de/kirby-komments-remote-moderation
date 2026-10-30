<?php

declare(strict_types=1);

namespace Polbeo\KirbyKommentsRemoteModeration\Bridge;

use Kirby\Cms\App;
use Kirby\Http\Response;
use Polbeo\KirbyKommentsRemoteModeration\Domain\ModerationAction;
use Polbeo\KirbyKommentsRemoteModeration\Security\HmacSigner;
use Polbeo\KirbyKommentsRemoteModeration\Security\KirbyNonceStore;
use Polbeo\KirbyKommentsRemoteModeration\Security\RequestVerifier;
use Polbeo\KirbyKommentsRemoteModeration\Security\Signature;
use Polbeo\KirbyKommentsRemoteModeration\Security\SystemClock;

final class BridgeController
{
    /** @param array<string, mixed> $configuration */
    public function __construct(private readonly App $kirby, private readonly array $configuration)
    {
    }

    public function pending(): Response
    {
        return $this->handle('/komments-remote-moderation/v1/pending', function (ModerationGateway $gateway): array {
            return ['data' => array_map(static fn ($entry): array => $entry->toArray(), $gateway->pending())];
        });
    }

    public function moderate(): Response
    {
        return $this->handle('/komments-remote-moderation/v1/moderate', function (ModerationGateway $gateway): array {
            $payload = $this->jsonBody();
            $action = ModerationAction::tryFrom((string) ($payload['action'] ?? ''));
            if ($action === null || !is_string($payload['id'] ?? null) || !is_string($payload['updatedAt'] ?? null)) {
                throw new \InvalidArgumentException('invalid_action_payload');
            }
            $result = $gateway->action($payload['id'], $action, $payload['updatedAt']);
            $status = match ($result->state) {
                'not_found' => 404,
                'conflict' => 409,
                default => 200,
            };
            return ['data' => ['state' => $result->state, 'updatedAt' => $result->updatedAt], '_status' => $status];
        });
    }

    /** @param callable(ModerationGateway): array<string, mixed> $operation */
    private function handle(string $path, callable $operation): Response
    {
        if (($this->configuration['enabled'] ?? false) !== true) {
            return $this->response(['error' => ['code' => 'bridge_disabled']], 404);
        }

        $request = $this->kirby->request();
        $body = (string) file_get_contents('php://input');
        $signature = new Signature(
            (string) $request->header('X-Komments-Key-Id'),
            (int) $request->header('X-Komments-Timestamp'),
            (string) $request->header('X-Komments-Nonce'),
            (string) $request->header('X-Komments-Body-Hash'),
            (string) $request->header('X-Komments-Signature'),
        );
        $secrets = [];
        foreach (($this->configuration['keys'] ?? []) as $keyId => $key) {
            if (is_array($key) && is_string($key['secret'] ?? null)) {
                $secrets[(string) $keyId] = $key['secret'];
            }
        }
        $verification = new RequestVerifier($secrets, new KirbyNonceStore($this->kirby), new SystemClock(), new HmacSigner());
        $result = $verification->verify($request->method(), $path, $body, $signature);
        if (!$result->valid) {
            return $this->response(['error' => ['code' => 'unauthorized']], 401);
        }

        try {
            $response = $operation(new KommentsModerationGateway());
            return $this->response($response, (int) ($response['_status'] ?? 200));
        } catch (\InvalidArgumentException) {
            return $this->response(['error' => ['code' => 'invalid_request']], 422);
        } catch (\JsonException) {
            return $this->response(['error' => ['code' => 'invalid_json']], 400);
        }
    }

    /** @return array<string, mixed> */
    private function jsonBody(): array
    {
        $decoded = $this->kirby->request()->data();
        if (!is_array($decoded)) {
            throw new \InvalidArgumentException('JSON object expected');
        }
        return $decoded;
    }

    /** @param array<string, mixed> $body */
    private function response(array $body, int $status): Response
    {
        unset($body['_status']);
        return new Response(json_encode($body, JSON_THROW_ON_ERROR), 'application/json', $status);
    }
}
