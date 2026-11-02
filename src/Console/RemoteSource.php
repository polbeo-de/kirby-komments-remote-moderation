<?php

declare(strict_types=1);

namespace Polbeo\KirbyKommentsRemoteModeration\Console;

final readonly class RemoteSource
{
    public function __construct(
        public string $id,
        public string $label,
        public string $baseUrl,
        public string $keyId,
        public string $secret,
        public bool $live,
        public ?string $basicAuthUser,
        public ?string $basicAuthPassword,
    ) {
    }

    /** @param array<string, mixed> $configuration */
    public static function fromConfiguration(string $id, array $configuration): self
    {
        $label = $configuration['label'] ?? $id;
        $baseUrl = $configuration['baseUrl'] ?? null;
        $keyId = $configuration['keyId'] ?? null;
        $secret = $configuration['secret'] ?? null;
        $basicAuthUser = $configuration['basicAuthUser'] ?? null;
        $basicAuthPassword = $configuration['basicAuthPassword'] ?? null;
        if (
            !is_string($label) || !is_string($baseUrl) || !is_string($keyId) || !is_string($secret)
            || $label === '' || $keyId === '' || $secret === ''
        ) {
            throw new \InvalidArgumentException('Invalid remote source configuration');
        }
        $url = parse_url($baseUrl);
        if (
            ($url['scheme'] ?? null) !== 'https'
            || !isset($url['host'])
            || isset($url['user'])
            || isset($url['pass'])
            || isset($url['query'])
            || isset($url['fragment'])
        ) {
            throw new \InvalidArgumentException('Remote source base URL must be a credential-free HTTPS URL');
        }
        if (
            ($basicAuthUser === null) !== ($basicAuthPassword === null)
            || ($basicAuthUser !== null && (
                !is_string($basicAuthUser)
                || !is_string($basicAuthPassword)
                || $basicAuthUser === ''
                || $basicAuthPassword === ''
            ))
        ) {
            throw new \InvalidArgumentException('Basic authentication requires a non-empty user and password pair');
        }

        return new self(
            $id,
            $label,
            rtrim($baseUrl, '/'),
            $keyId,
            $secret,
            ($configuration['live'] ?? false) === true,
            $basicAuthUser,
            $basicAuthPassword,
        );
    }

    /** @return array{id: string, label: string, live: bool} */
    public function publicData(): array
    {
        return ['id' => $this->id, 'label' => $this->label, 'live' => $this->live];
    }
}
