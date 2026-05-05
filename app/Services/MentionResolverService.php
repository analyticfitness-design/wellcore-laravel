<?php

namespace App\Services;

use App\Events\MentionCreated;
use App\Models\Client;
use App\Models\CommunityPost;
use App\Models\PostComment;
use App\Models\PostMention;
use Illuminate\Support\Carbon;

class MentionResolverService
{
    private const TOKEN_REGEX = '/@(cliente_(\d+)|coach|admin|wellcore)\b/iu';

    /**
     * Extract typed mention tokens from a body.
     *
     * @return array<int, array{type: string, id: int|null}>
     */
    public function extract(string $body): array
    {
        if (! preg_match_all(self::TOKEN_REGEX, $body, $matches, PREG_SET_ORDER)) {
            return [];
        }

        $tokens = [];

        foreach ($matches as $m) {
            $token = strtolower($m[1]);

            if (str_starts_with($token, 'cliente_')) {
                $id = isset($m[2]) ? (int) $m[2] : 0;
                if ($id > 0) {
                    $tokens[] = ['type' => 'client', 'id' => $id];
                }

                continue;
            }

            if ($token === 'coach') {
                $tokens[] = ['type' => 'coach', 'id' => null];

                continue;
            }

            if (in_array($token, ['admin', 'wellcore'], true)) {
                $tokens[] = ['type' => 'admin', 'id' => null];
            }
        }

        return $tokens;
    }

    public function persistForPost(CommunityPost $post, string $mentionerType, int $mentionerId): int
    {
        return $this->persist(
            tokens: $this->extract($post->content ?? ''),
            postId: $post->id,
            commentId: null,
            mentionerType: $mentionerType,
            mentionerId: $mentionerId,
            coachAdminId: $post->coach_admin_id,
        );
    }

    public function persistForComment(PostComment $comment, string $mentionerType, int $mentionerId, ?int $coachAdminId): int
    {
        return $this->persist(
            tokens: $this->extract($comment->content ?? ''),
            postId: $comment->post_id,
            commentId: $comment->id,
            mentionerType: $mentionerType,
            mentionerId: $mentionerId,
            coachAdminId: $coachAdminId,
        );
    }

    /**
     * Autocomplete search restricted to a coach's clients (when scoped).
     *
     * @return array<int, array{id:int, type:string, label:string}>
     */
    public function searchMentionTargets(string $query, ?int $scopeCoachId = null, int $limit = 10): array
    {
        $q = Client::query()
            ->select(['id', 'name'])
            ->where('name', 'like', $query.'%');

        if ($scopeCoachId) {
            $q->where('coach_id', $scopeCoachId);
        }

        return $q->limit($limit)
            ->get()
            ->map(fn ($c) => ['id' => $c->id, 'type' => 'client', 'label' => $c->name])
            ->all();
    }

    private function persist(array $tokens, ?int $postId, ?int $commentId, string $mentionerType, int $mentionerId, ?int $coachAdminId): int
    {
        $created = 0;

        foreach ($tokens as $token) {
            $mentionedId = $token['id'];

            if ($mentionedId === null && $token['type'] === 'coach' && $coachAdminId) {
                $mentionedId = $coachAdminId;
            }

            if ($mentionedId === null) {
                continue;
            }

            PostMention::create([
                'post_id' => $postId,
                'comment_id' => $commentId,
                'mentioner_type' => $mentionerType,
                'mentioner_id' => $mentionerId,
                'mentioned_type' => $token['type'],
                'mentioned_id' => $mentionedId,
                'created_at' => Carbon::now(),
            ]);

            event(new MentionCreated(
                postId: $postId,
                commentId: $commentId,
                mentionerType: $mentionerType,
                mentionerId: $mentionerId,
                mentionedType: $token['type'],
                mentionedId: $mentionedId,
            ));

            $created++;
        }

        return $created;
    }
}
