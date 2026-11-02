<?php

declare(strict_types=1);

namespace Polbeo\KirbyKommentsRemoteModeration\Domain;

final readonly class ModerationEntry
{
    public function __construct(
        public string $id,
        public string $type,
        public string $authorName,
        public ?string $authorUrl,
        public ?string $avatarUrl,
        public string $content,
        public string $pageTitle,
        public string $pageUrl,
        public string $createdAt,
        public string $updatedAt,
        public string $status,
    ) {
    }

    /** @param array<string, mixed> $value */
    public static function fromKomments(array $value, string $pageTitle, string $pageUrl): self
    {
        foreach (['id', 'type', 'createdAt', 'updatedAt'] as $required) {
            if (!isset($value[$required]) || !is_string($value[$required]) || $value[$required] === '') {
                throw new \InvalidArgumentException(sprintf('Missing required Komments field: %s', $required));
            }
        }
        if (!isset($value['content']) || !is_string($value['content'])) {
            throw new \InvalidArgumentException('Missing required Komments field: content');
        }

        return new self(
            $value['id'],
            $value['type'],
            isset($value['authorName']) && is_string($value['authorName']) ? $value['authorName'] : '',
            isset($value['authorUrl']) && is_string($value['authorUrl']) ? $value['authorUrl'] : null,
            isset($value['authorAvatar']) && is_string($value['authorAvatar']) ? $value['authorAvatar'] : null,
            $value['content'],
            $pageTitle,
            $pageUrl,
            $value['createdAt'],
            $value['updatedAt'],
            isset($value['verification_status']) && is_string($value['verification_status'])
                ? $value['verification_status']
                : 'PENDING',
        );
    }

    /** @return array<string, string|null> */
    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
