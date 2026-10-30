<?php

declare(strict_types=1);

namespace Polbeo\KirbyKommentsRemoteModeration\Security;

interface Clock
{
    public function now(): int;
}
