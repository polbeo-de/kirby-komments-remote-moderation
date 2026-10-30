<?php

declare(strict_types=1);

namespace Polbeo\KirbyKommentsRemoteModeration\Domain;

enum ModerationAction: string
{
    case Publish = 'publish';
    case Hold = 'hold';
    case Spam = 'spam';
    case Delete = 'delete';
}
