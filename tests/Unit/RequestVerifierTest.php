<?php

declare(strict_types=1);

namespace Polbeo\KirbyKommentsRemoteModeration\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Polbeo\KirbyKommentsRemoteModeration\Security\ArrayNonceStore;
use Polbeo\KirbyKommentsRemoteModeration\Security\Clock;
use Polbeo\KirbyKommentsRemoteModeration\Security\HmacSigner;
use Polbeo\KirbyKommentsRemoteModeration\Security\RequestVerifier;
use Polbeo\KirbyKommentsRemoteModeration\Security\Signature;

final class RequestVerifierTest extends TestCase
{
    public function testAcceptsValidRequestOnlyOnce(): void
    {
        $clock = new class implements Clock { public function now(): int { return 1_700_000_000; } };
        $signer = new HmacSigner();
        $body = '{"id":"example"}';
        $signature = new Signature('source-a', $clock->now(), 'nonce012345678901', $signer->bodyHash($body), $signer->sign('secret', 'POST', '/api/v1/moderate', $clock->now(), 'nonce012345678901', $body));
        $verifier = new RequestVerifier(['source-a' => 'secret'], new ArrayNonceStore($clock), $clock, $signer);

        self::assertTrue($verifier->verify('POST', '/api/v1/moderate', $body, $signature)->valid);
        self::assertSame('replayed_nonce', $verifier->verify('POST', '/api/v1/moderate', $body, $signature)->reason);
    }

    public function testRejectsStaleTimestampAndInvalidSignature(): void
    {
        $clock = new class implements Clock { public function now(): int { return 1_700_000_000; } };
        $signer = new HmacSigner();
        $verifier = new RequestVerifier(['source-a' => 'secret'], new ArrayNonceStore($clock), $clock, $signer);
        $stale = new Signature('source-a', 1_699_999_000, 'nonce012345678901', $signer->bodyHash(''), 'x');

        self::assertSame('stale_timestamp', $verifier->verify('GET', '/api/v1/pending', '', $stale)->reason);
    }
}
