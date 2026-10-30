<?php

declare(strict_types=1);

namespace Polbeo\KirbyKommentsRemoteModeration;

final class Configuration
{
    /** @return array<string, mixed> */
    public static function role(string $pluginRoot, string $role): array
    {
        $configured = option('polbeo.komments-remote-moderation.' . $role, []);
        if (is_array($configured) && $configured !== []) {
            return $configured;
        }

        $local = $pluginRoot . '/config/local.php';
        if (!is_file($local)) {
            return [];
        }
        $configuration = require $local;
        return is_array($configuration[$role] ?? null) ? $configuration[$role] : [];
    }
}
