<?php

declare(strict_types=1);

use Kirby\Cms\App as Kirby;
use Polbeo\KirbyKommentsRemoteModeration\Bridge\BridgeController;

@include_once __DIR__ . '/vendor/autoload.php';

spl_autoload_register(static function (string $class): void {
    $prefix = 'Polbeo\\KirbyKommentsRemoteModeration\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $relativePath = str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
    $file = __DIR__ . '/src/' . $relativePath;
    if (is_file($file)) {
        require $file;
    }
});

Kirby::plugin('polbeo/kirby-komments-remote-moderation', [
    'translations' => require __DIR__ . '/plugin/translations.php',
    'panel' => ['js' => 'index.js', 'css' => 'index.css'],
    'areas' => require __DIR__ . '/plugin/areas.php',
    'fields' => [
        'remote-komments' => [
            'computed' => [
                'pagePath' => function (): string {
                    $uri = $this->model()->uri();
                    return $uri === '' ? '/' : '/' . ltrim($uri, '/');
                },
                'sources' => function (): array {
                    $configuration = \Polbeo\KirbyKommentsRemoteModeration\Configuration::role(__DIR__, 'console');
                    return (new \Polbeo\KirbyKommentsRemoteModeration\Console\ConsoleController(kirby(), $configuration))->sources();
                },
            ],
        ],
    ],
    'api' => [
        'routes' => [
            [
                'pattern' => 'komments-remote-moderation/sources',
                'method' => 'GET',
                'action' => function (): array {
                    $configuration = \Polbeo\KirbyKommentsRemoteModeration\Configuration::role(__DIR__, 'console');
                    return (new \Polbeo\KirbyKommentsRemoteModeration\Console\ConsoleController(kirby(), $configuration))->sources();
                },
            ],
            [
                'pattern' => 'komments-remote-moderation/(:any)/pending',
                'method' => 'GET',
                'action' => function (string $sourceId): array {
                    $configuration = \Polbeo\KirbyKommentsRemoteModeration\Configuration::role(__DIR__, 'console');
                    return (new \Polbeo\KirbyKommentsRemoteModeration\Console\ConsoleController(kirby(), $configuration))->pending($sourceId);
                },
            ],
            [
                'pattern' => 'komments-remote-moderation/(:any)/pending-for-page',
                'method' => 'GET',
                'action' => function (string $sourceId): array {
                    $configuration = \Polbeo\KirbyKommentsRemoteModeration\Configuration::role(__DIR__, 'console');
                    $path = kirby()->request()->get('path');
                    return (new \Polbeo\KirbyKommentsRemoteModeration\Console\ConsoleController(kirby(), $configuration))
                        ->pendingForPage($sourceId, is_string($path) ? $path : '');
                },
            ],
            [
                'pattern' => 'komments-remote-moderation/(:any)/moderate',
                'method' => 'POST',
                'action' => function (string $sourceId): array {
                    $configuration = \Polbeo\KirbyKommentsRemoteModeration\Configuration::role(__DIR__, 'console');
                    $payload = kirby()->request()->data();
                    return (new \Polbeo\KirbyKommentsRemoteModeration\Console\ConsoleController(kirby(), $configuration))->moderate($sourceId, is_array($payload) ? $payload : []);
                },
            ],
        ],
    ],
    'routes' => [
        [
            'pattern' => 'komments-remote-moderation/v1/pending',
            'method' => 'GET',
            'action' => function () {
                $configuration = \Polbeo\KirbyKommentsRemoteModeration\Configuration::role(__DIR__, 'bridge');
                return (new BridgeController(kirby(), $configuration))->pending();
            },
        ],
        [
            'pattern' => 'komments-remote-moderation/v1/moderate',
            'method' => 'POST',
            'action' => function () {
                $configuration = \Polbeo\KirbyKommentsRemoteModeration\Configuration::role(__DIR__, 'bridge');
                return (new BridgeController(kirby(), $configuration))->moderate();
            },
        ],
    ],
]);
