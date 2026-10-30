<?php

declare(strict_types=1);

namespace Polbeo\KirbyKommentsRemoteModeration\Bridge;

use Polbeo\KirbyKommentsRemoteModeration\Domain\ModerationAction;
use Polbeo\KirbyKommentsRemoteModeration\Domain\ModerationEntry;

interface ModerationGateway
{
    /** @return list<ModerationEntry> */
    public function pending(): array;

    public function action(string $id, ModerationAction $action, string $expectedUpdatedAt): GatewayActionResult;
}
