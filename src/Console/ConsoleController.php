<?php

declare(strict_types=1);

namespace Polbeo\KirbyKommentsRemoteModeration\Console;

use Kirby\Cms\App;

final class ConsoleController
{
    /** @param array<string, mixed> $configuration */
    public function __construct(private readonly App $kirby, private readonly array $configuration, private readonly RemoteClient $client = new RemoteClient())
    {
    }

    /** @return list<array{id: string, label: string, live: bool}> */
    public function sources(): array
    {
        $this->requirePanelUser();
        return array_map(static fn (RemoteSource $source): array => $source->publicData(), $this->configuredSources());
    }

    /** @return array<string, mixed> */
    public function pending(string $sourceId): array
    {
        $this->requirePanelUser();
        return $this->client->pending($this->source($sourceId));
    }

    /** @return array<string, mixed> */
    public function pendingForPage(string $sourceId, string $path): array
    {
        $path = '/' . ltrim($path, '/');
        $response = $this->pending($sourceId);
        $entries = $response['data'] ?? [];
        if (!is_array($entries)) {
            return ['data' => []];
        }

        $response['data'] = array_values(array_filter($entries, static function (mixed $entry) use ($path): bool {
            if (!is_array($entry) || !is_string($entry['pageUrl'] ?? null)) {
                return false;
            }

            $entryPath = parse_url($entry['pageUrl'], PHP_URL_PATH);
            return is_string($entryPath) && rtrim($entryPath, '/') === rtrim($path, '/');
        }));

        return $response;
    }

    /** @return array<string, mixed> */
    public function moderate(string $sourceId, array $payload): array
    {
        $this->requirePanelUser();
        foreach (['id', 'action', 'updatedAt'] as $key) {
            if (!is_string($payload[$key] ?? null) || $payload[$key] === '') {
                throw new \InvalidArgumentException('Invalid moderation request');
            }
        }
        if (!in_array($payload['action'], ['publish', 'hold', 'spam', 'delete'], true)) {
            throw new \InvalidArgumentException('Unsupported moderation action');
        }
        return $this->client->moderate($this->source($sourceId), $payload['id'], $payload['action'], $payload['updatedAt']);
    }

    /** @return list<RemoteSource> */
    private function configuredSources(): array
    {
        if (($this->configuration['enabled'] ?? false) !== true) {
            return [];
        }
        $sources = [];
        foreach (($this->configuration['sources'] ?? []) as $id => $configuration) {
            if (is_string($id) && preg_match('/^[a-z0-9][a-z0-9-]{0,63}$/', $id) && is_array($configuration)) {
                $sources[] = RemoteSource::fromConfiguration($id, $configuration);
            }
        }
        return $sources;
    }

    private function source(string $id): RemoteSource
    {
        foreach ($this->configuredSources() as $source) {
            if (hash_equals($source->id, $id)) {
                return $source;
            }
        }
        throw new \InvalidArgumentException('Unknown source');
    }

    private function requirePanelUser(): void
    {
        if ($this->kirby->user() === null) {
            throw new \RuntimeException('Panel authentication required');
        }
    }
}
