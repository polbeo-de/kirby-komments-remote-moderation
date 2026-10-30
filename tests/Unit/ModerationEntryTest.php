<?php

declare(strict_types=1);

namespace Polbeo\KirbyKommentsRemoteModeration\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Polbeo\KirbyKommentsRemoteModeration\Domain\ModerationEntry;

final class ModerationEntryTest extends TestCase
{
    public function testFiltersPrivateKommentsFields(): void
    {
        $entry = ModerationEntry::fromKomments([
            'id' => 'comment-id', 'type' => 'comment', 'content' => 'Hello',
            'createdAt' => '2026-01-01T00:00:00Z', 'updatedAt' => '2026-01-01T00:00:00Z',
            'authorName' => 'Author', 'authorEmail' => 'private@example.invalid', 'ip' => '127.0.0.1',
        ], 'Page', 'https://example.invalid/page');

        self::assertArrayNotHasKey('authorEmail', $entry->toArray());
        self::assertArrayNotHasKey('ip', $entry->toArray());
    }
}
