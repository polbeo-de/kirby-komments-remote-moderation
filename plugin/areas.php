<?php

declare(strict_types=1);

use Polbeo\KirbyKommentsRemoteModeration\Console\ConsoleController;

return [
    'komments-remote-moderation' => function (): array {
        $configuration = \Polbeo\KirbyKommentsRemoteModeration\Configuration::role(dirname(__DIR__), 'console');
        if (($configuration['enabled'] ?? false) !== true) {
            return [];
        }
        $controller = new ConsoleController(kirby(), $configuration);

        return [
            'label' => t('polbeo.komments-remote-moderation.area.label'),
            'icon' => 'chat',
            'menu' => true,
            'link' => 'remote-komments',
            'views' => [[
                'pattern' => 'remote-komments',
                'action' => fn (): array => [
                    'component' => 'k-remote-komments-view',
                    'title' => t('polbeo.komments-remote-moderation.area.label'),
                    'props' => ['sources' => $controller->sources()],
                ],
            ]],
        ];
    },
];
