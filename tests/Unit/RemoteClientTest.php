<?php

declare(strict_types=1);

namespace Polbeo\KirbyKommentsRemoteModeration\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Polbeo\KirbyKommentsRemoteModeration\Console\RemoteClient;
use Polbeo\KirbyKommentsRemoteModeration\Console\RemoteSource;
use Polbeo\KirbyKommentsRemoteModeration\Security\Clock;
use Polbeo\KirbyKommentsRemoteModeration\Transport\HttpResponse;
use Polbeo\KirbyKommentsRemoteModeration\Transport\HttpTransport;

final class RemoteClientTest extends TestCase
{
    public function testSendsSignedPendingRequestWithoutLeakingSecret(): void
    {
        $transport = new class implements HttpTransport {
            /** @var array<string, string> */
            public array $headers = [];
            public string $method = '';
            public string $url = '';
            public string $body = '';

            public function send(string $method, string $url, string $body, array $headers): HttpResponse
            {
                $this->method = $method;
                $this->url = $url;
                $this->body = $body;
                $this->headers = $headers;
                return new HttpResponse(200, '{"data":[]}');
            }
        };
        $clock = new class implements Clock {
            public function now(): int
            {
                return 1_700_000_000;
            }
        };
        $source = RemoteSource::fromConfiguration('staging', [
            'label' => 'Staging', 'baseUrl' => 'https://remote.invalid', 'keyId' => 'key-a', 'secret' => 'test-secret',
        ]);

        self::assertSame(['data' => []], (new RemoteClient($transport, $clock))->pending($source));
        self::assertSame('GET', $transport->method);
        self::assertSame('https://remote.invalid/komments-remote-moderation/v1/pending', $transport->url);
        self::assertSame('', $transport->body);
        self::assertSame('key-a', $transport->headers['X-Komments-Key-Id']);
        self::assertArrayNotHasKey('test-secret', $transport->headers);
        self::assertStringNotContainsString('test-secret', implode("\n", $transport->headers));
    }
}
