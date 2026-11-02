<?php

declare(strict_types=1);

namespace Polbeo\KirbyKommentsRemoteModeration\Bridge;

use mauricerenck\Komments\KommentModeration;
use Polbeo\KirbyKommentsRemoteModeration\Domain\ModerationAction;
use Polbeo\KirbyKommentsRemoteModeration\Domain\ModerationEntry;

final class KommentsModerationGateway implements ModerationGateway
{
    public function __construct(private readonly KommentModeration $moderation = new KommentModeration())
    {
    }

    public function pending(): array
    {
        $pending = $this->moderation->getPendingComments();
        $published = $this->moderation->getPendingComments(true);
        $comments = [
            ...json_decode($pending['comments'], true, 512, JSON_THROW_ON_ERROR),
            ...json_decode($published['comments'], true, 512, JSON_THROW_ON_ERROR),
        ];
        $pages = [];
        foreach ([...$pending['affectedPages'], ...$published['affectedPages']] as $page) {
            if (is_array($page) && is_string($page['uuid'] ?? null)) {
                $pages[$page['uuid']] = $page;
            }
        }

        $entries = [];
        foreach ($comments as $comment) {
            $comment = [
                ...$comment,
                'createdAt' => $comment['createdAt'] ?? $comment['createdat'] ?? $comment['created_at'] ?? null,
                'updatedAt' => $comment['updatedAt'] ?? $comment['updatedat'] ?? $comment['updated_at'] ?? null,
                'authorName' => $comment['authorName'] ?? $comment['authorname'] ?? $comment['author_name'] ?? null,
                'authorUrl' => $comment['authorUrl'] ?? $comment['authorurl'] ?? $comment['author_url'] ?? null,
                'authorAvatar' => $comment['authorAvatar']
                    ?? $comment['authoravatar']
                    ?? $comment['author_avatar']
                    ?? null,
            ];
            $pageUuid = $comment['pageUuid'] ?? $comment['pageuuid'] ?? null;
            if (!is_string($pageUuid) || $pageUuid === '') {
                continue;
            }
            $page = $pages[$pageUuid] ?? ['title' => 'Gelöschte Seite'];
            $pageObject = function_exists('page') ? page($pageUuid) : null;
            $entries[] = ModerationEntry::fromKomments(
                $comment,
                $page['title'],
                $pageObject?->url() ?? '',
            );
        }

        return $entries;
    }

    public function action(string $id, ModerationAction $action, string $expectedUpdatedAt): GatewayActionResult
    {
        $comment = $this->moderation->getComment($id);
        if (!isset($comment['id']) || $comment['id'] === '') {
            return GatewayActionResult::notFound();
        }
        $updatedAt = $comment['updatedAt'] ?? $comment['updatedat'] ?? $comment['updated_at'] ?? null;
        if (!is_string($updatedAt) || $updatedAt !== $expectedUpdatedAt) {
            return GatewayActionResult::conflict((string) ($updatedAt ?? ''));
        }

        $alreadyApplied = match ($action) {
            ModerationAction::Publish => (bool) ($comment['published'] ?? false),
            ModerationAction::Hold => !(bool) ($comment['published'] ?? false),
            ModerationAction::Spam => (int) ($comment['spamlevel'] ?? 0) > 0,
            ModerationAction::Delete => false,
        };
        if ($alreadyApplied) {
            return GatewayActionResult::alreadyApplied($expectedUpdatedAt);
        }

        match ($action) {
            ModerationAction::Publish, ModerationAction::Hold => $this->moderation->publishComment($id),
            ModerationAction::Spam => $this->moderation->flagComment($id, 'spamlevel'),
            ModerationAction::Delete => $this->moderation->deleteComment($id),
        };

        return GatewayActionResult::applied();
    }
}
