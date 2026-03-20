<?php

namespace App\Livewire\Client;

use App\Models\CommunityPost;
use App\Models\PostComment;
use App\Models\PostReaction;
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

        $posts = CommunityPost::where('visible', true)
            ->with(['client:id,name', 'reactions', 'comments.client:id,name'])
            ->withCount(['reactions', 'comments'])
            ->orderByDesc('created_at')
            ->paginate($this->perPage);

        // Build a lookup of current user's reactions per post
        $myReactions = PostReaction::where('client_id', $clientId)
            ->whereIn('post_id', $posts->pluck('id'))
            ->get()
            ->groupBy('post_id')
            ->map(fn ($group) => $group->pluck('reaction_type')->toArray());

        return view('livewire.client.community-feed', [
            'posts' => $posts,
            'myReactions' => $myReactions,
            'clientId' => $clientId,
        ]);
    }
}
