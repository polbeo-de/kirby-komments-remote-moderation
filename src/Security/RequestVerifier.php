<?php

declare(strict_types=1);

namespace Polbeo\KirbyKommentsRemoteModeration\Security;

final class RequestVerifier
{
    /** @param array<string, string> $secrets */
    public function __construct(
        private readonly array $secrets,
        private readonly NonceStore $nonces,
        private readonly Clock $clock,
        private readonly HmacSigner $signer = new HmacSigner(),
        private readonly int $maximumSkewSeconds = 300,
    ) {
    }

    public function verify(string $method, string $path, string $body, Signature $signature): VerificationResult
    {
        $secret = $this->secrets[$signature->keyId] ?? null;
        if (!is_string($secret) || $secret === '') {
            return VerificationResult::invalid('unknown_key');
        }

        if (abs($this->clock->now() - $signature->timestamp) > $this->maximumSkewSeconds) {
            return VerificationResult::invalid('stale_timestamp');
        }

        if (!preg_match('/^[A-Za-z0-9_-]{16,128}$/', $signature->nonce)) {
            return VerificationResult::invalid('invalid_nonce');
        }

        $bodyHash = $this->signer->bodyHash($body);
        if (!hash_equals($bodyHash, $signature->bodyHash)) {
            return VerificationResult::invalid('body_hash_mismatch');
        }

        $expected = $this->signer->sign($secret, $method, $path, $signature->timestamp, $signature->nonce, $body);
        if (!hash_equals($expected, $signature->value)) {
            return VerificationResult::invalid('invalid_signature');
        }

        if (!$this->nonces->claim($signature->keyId . ':' . $signature->nonce, $this->maximumSkewSeconds * 2)) {
            return VerificationResult::invalid('replayed_nonce');
        }

        return VerificationResult::valid($signature->keyId);
    }
}
