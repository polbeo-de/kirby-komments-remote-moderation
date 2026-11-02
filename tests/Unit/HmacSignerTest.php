<?php

declare(strict_types=1);

namespace Polbeo\KirbyKommentsRemoteModeration\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Polbeo\KirbyKommentsRemoteModeration\Security\HmacSigner;

final class HmacSignerTest extends TestCase
{
    public function testSignsCanonicalRequestIncludingBodyHash(): void
    {
        $signer = new HmacSigner();
        $signature = $signer->sign(
            'test-secret',
            'post',
            '/api/v1/example',
            1_700_000_000,
            'nonce012345678901',
            '{"id":"a"}',
        );

        self::assertSame(64, strlen($signature));
        self::assertSame(
            $signature,
            $signer->sign('test-secret', 'POST', '/api/v1/example', 1_700_000_000, 'nonce012345678901', '{"id":"a"}'),
        );
        self::assertNotSame(
            $signature,
            $signer->sign('test-secret', 'POST', '/api/v1/example', 1_700_000_000, 'nonce012345678901', '{"id":"b"}'),
        );
    }
}
