<?php

namespace App\Livewire\Client;

use App\Models\Client;
use App\Models\CommunityPost;
use App\Models\PostComment;
use App\Models\PostReaction;
use App\Models\WorkoutPr;
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
            $content = 'Logro: '.$content;
        } elseif ($this->postType === 'pr' && ! str_starts_with($content, 'Nuevo PR: ')) {
            $content = 'Nuevo PR: '.$content;
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

        // L2 cache: community stats extendidas con logros y PRs totales.
        // Recomputadas máximo cada 5 minutos para no spamear queries por cada keystroke.
        $communityStats = Cache::remember('community:stats', 300, function () {
            return [
                'total_posts' => CommunityPost::where('visible', true)->count(),
                'active_members' => Client::where('status', 'activo')->count(),
                'total_achievements' => CommunityPost::where('visible', true)
                    ->where('post_type', 'achievement')->count(),
                'total_prs' => WorkoutPr::count(),
            ];
        });

        // Stories row: miembros activos con flag de "tiene contenido nuevo" (últimas 24h).
        // Ordenados por contenido nuevo primero para priorizar quien publicó recientemente.
        $storiesMembers = Cache::remember('community:stories', 180, function () {
            $cutoff = now()->subHours(24);

            return Client::where('status', 'activo')
                ->select('id', 'name')
                ->withCount(['communityPosts as new_posts_count' => function ($q) use ($cutoff) {
                    $q->where('visible', true)->where('created_at', '>=', $cutoff);
                }])
                ->orderByDesc('new_posts_count')
                ->limit(8)
                ->get()
                ->map(fn ($c) => [
                    'id' => $c->id,
                    'name' => $c->name,
                    'initials' => $this->initialsFor($c->name),
                    'has_new' => $c->new_posts_count > 0,
                    'color' => $this->colorForName($c->name),
                    'last_type' => null,
                ]);
        });

        // Miembros activos para el right panel, ordenados por actividad reciente.
        // Usa updated_at como proxy de actividad (last_active_at no existe en la tabla).
        $activeMembersList = Cache::remember('community:active-list', 180, function () {
            return Client::where('status', 'activo')
                ->select('id', 'name', 'updated_at')
                ->orderByDesc('updated_at')
                ->limit(4)
                ->get();
        });

        $myPhase = $this->getCurrentPhaseLabel($clientId);

        $posts = CommunityPost::where('visible', true)
            ->withCount(['reactions', 'comments'])
            ->with([
                'client:id,name',
                'comments.client:id,name',
                // Solo las reacciones del usuario autenticado por post (reduce N+1).
                'reactions' => fn ($q) => $q->where('client_id', $clientId),
            ])
            ->orderByDesc('created_at')
            ->paginate($this->perPage);

        $postIds = $posts->pluck('id');

        // Conteos de reacciones por tipo para todos los usuarios — un solo query agrupado.
        $reactionCountsAll = PostReaction::whereIn('post_id', $postIds)
            ->selectRaw('post_id, reaction_type, COUNT(*) as total')
            ->groupBy('post_id', 'reaction_type')
            ->get()
            ->groupBy('post_id')
            ->map(fn ($rows) => $rows->pluck('total', 'reaction_type'));

        // Lookup de reacciones del usuario actual por post.
        $myReactions = $posts->getCollection()
            ->mapWithKeys(fn ($post) => [
                $post->id => $post->reactions->pluck('reaction_type')->toArray(),
            ]);

        return view('livewire.client.community-feed', [
            'posts' => $posts,
            'myReactions' => $myReactions,
            'reactionCountsAll' => $reactionCountsAll,
            'clientId' => $clientId,
            'communityStats' => $communityStats,
            'storiesMembers' => $storiesMembers,
            'activeMembersList' => $activeMembersList,
            'myPhase' => $myPhase,
            'myInitials' => $this->initialsFor(auth('wellcore')->user()?->name ?? 'TU'),
        ]);
    }

    // Asignación determinística de color por nombre (hash crc32).
    // Garantiza consistencia visual del mismo usuario en stories, posts y comments.
    public function colorForName(string $name): string
    {
        $colors = ['red', 'green', 'blue', 'purple', 'amber'];

        return $colors[abs(crc32($name)) % count($colors)];
    }

    // Iniciales de 1-2 caracteres en mayúsculas para avatares de texto.
    public function initialsFor(string $name): string
    {
        return mb_strtoupper(mb_substr(trim($name) ?: 'M', 0, 2));
    }

    protected function getCurrentPhaseLabel(int $clientId): ?array
    {
        // Implementación temporal: el equipo conectará con el sistema real de fases.
        return [
            'name' => 'S1 · Adaptación',
            'week' => 1,
            'total_weeks' => 4,
        ];
    }
}
