<?php

namespace App\Livewire\Client;

use App\Models\Client;
use App\Models\CommunityPost;
use App\Models\PostComment;
use App\Models\PostReaction;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.client')]
class CommunityFeed extends Component
{
    public string $postContent = '';

    public string $postType = 'text';

    public int $perPage = 10;

    public array $commentTexts = [];

    public array $showComments = [];

    protected function rules(): array
    {
        return [
            'postContent' => 'required|string|max:1000',
            'postType' => 'required|in:text,achievement,pr,photo',
        ];
    }

    public function createPost(): void
    {
        $this->validate();

        $content = $this->postContent;

        // Add prefix based on post type
        if ($this->postType === 'achievement' && ! str_starts_with($content, 'Logro: ')) {
            $content = 'Logro: ' . $content;
        } elseif ($this->postType === 'pr' && ! str_starts_with($content, 'Nuevo PR: ')) {
            $content = 'Nuevo PR: ' . $content;
        }

        CommunityPost::create([
            'client_id' => auth('wellcore')->id(),
            'content' => $content,
            'post_type' => $this->postType,
        ]);

        $this->postContent = '';
        $this->postType = 'text';
    }

    public function toggleReaction(int $postId, string $reactionType): void
    {
        $clientId = auth('wellcore')->id();

        $existing = PostReaction::where('post_id', $postId)
            ->where('client_id', $clientId)
            ->where('reaction_type', $reactionType)
            ->first();

        if ($existing) {
            $existing->delete();
        } else {
            PostReaction::create([
                'post_id' => $postId,
                'client_id' => $clientId,
                'reaction_type' => $reactionType,
            ]);
        }
    }

    public function addComment(int $postId): void
    {
        $text = trim($this->commentTexts[$postId] ?? '');

        if ($text === '' || strlen($text) > 500) {
            return;
        }

        PostComment::create([
            'post_id' => $postId,
            'client_id' => auth('wellcore')->id(),
            'content' => $text,
        ]);

        $this->commentTexts[$postId] = '';
        $this->showComments[$postId] = true;
    }

    public function toggleComments(int $postId): void
    {
        $this->showComments[$postId] = ! ($this->showComments[$postId] ?? false);
    }

    public function loadMore(): void
    {
        $this->perPage += 10;
    }

    public function deletePost(int $postId): void
    {
        CommunityPost::where('id', $postId)
            ->where('client_id', auth('wellcore')->id())
            ->update(['visible' => false]);
    }

    public function render()
    {
        $clientId = auth('wellcore')->id();

        // L2 cache: community stats — recomputed at most every 5 minutes.
        // Avoids two bare Eloquent queries firing on every Livewire re-render
        // (including every keystroke in the textarea).
        $communityStats = Cache::remember('community:stats', 300, function () {
            return [
                'total_posts'    => CommunityPost::where('visible', true)->count(),
                'active_members' => Client::where('active', true)->count(),
            ];
        });

        $posts = CommunityPost::where('visible', true)
            ->withCount(['reactions', 'comments'])
            ->with([
                'client:id,name',
                'comments.client:id,name',
                // Only load the authenticated user's own reactions per post
                // instead of every reaction from every user (N+1 reduction).
                'reactions' => fn ($q) => $q->where('client_id', $clientId),
            ])
            ->orderByDesc('created_at')
            ->paginate($this->perPage);

        $postIds = $posts->pluck('id');

        // Per-type reaction counts for all users — one query, not N queries.
        // Grouped in PHP to avoid issuing one COUNT per post per reaction type.
        $reactionCountsAll = PostReaction::whereIn('post_id', $postIds)
            ->selectRaw('post_id, reaction_type, COUNT(*) as total')
            ->groupBy('post_id', 'reaction_type')
            ->get()
            ->groupBy('post_id')
            ->map(fn ($rows) => $rows->pluck('total', 'reaction_type'));

        // Build a per-post lookup of the current user's reaction types.
        // The eager load above is already constrained to $clientId, so no
        // additional query is needed here.
        $myReactions = $posts->getCollection()
            ->mapWithKeys(fn ($post) => [
                $post->id => $post->reactions->pluck('reaction_type')->toArray(),
            ]);

        return view('livewire.client.community-feed', [
            'posts'            => $posts,
            'myReactions'      => $myReactions,
            'reactionCountsAll' => $reactionCountsAll,
            'clientId'         => $clientId,
            'communityStats'   => $communityStats,
        ]);
    }
}
