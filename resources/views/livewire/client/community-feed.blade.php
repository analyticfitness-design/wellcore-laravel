<div class="space-y-6">
    {{-- ================================================================ --}}
    {{-- HEADER                                                           --}}
    {{-- ================================================================ --}}
    <div class="relative overflow-hidden rounded-2xl border border-wc-border bg-gradient-to-br from-wc-bg-secondary via-wc-bg-tertiary to-wc-bg-secondary p-6">
        <div class="relative z-10 flex items-end justify-between">
            <div>
                <p class="mb-1 text-xs font-semibold uppercase tracking-widest text-wc-accent">Comunidad WellCore</p>
                <h1 class="font-display text-4xl tracking-wide text-wc-text">FEED</h1>
                <p class="mt-1 text-sm text-wc-text-secondary">Comparte tus logros · Celebra los de otros</p>
            </div>
            <div class="hidden sm:flex items-center gap-4">
                <div class="text-center">
                    <p class="font-display text-2xl text-wc-accent">{{ $communityStats['total_posts'] }}</p>
                    <p class="text-[10px] uppercase tracking-wider text-wc-text-tertiary">Posts</p>
                </div>
                <div class="h-8 w-px bg-wc-border"></div>
                <div class="text-center">
                    <p class="font-display text-2xl text-wc-accent">{{ $communityStats['active_members'] }}</p>
                    <p class="text-[10px] uppercase tracking-wider text-wc-text-tertiary">Miembros</p>
                </div>
            </div>
        </div>
        {{-- Decorative lines --}}
        <div class="absolute bottom-0 right-0 h-32 w-32 opacity-5">
            <svg viewBox="0 0 100 100" fill="none" class="h-full w-full text-wc-accent">
                <circle cx="50" cy="50" r="45" stroke="currentColor" stroke-width="1"/>
                <circle cx="50" cy="50" r="30" stroke="currentColor" stroke-width="1"/>
                <circle cx="50" cy="50" r="15" stroke="currentColor" stroke-width="1"/>
            </svg>
        </div>
    </div>

    {{-- ================================================================ --}}
    {{-- CREATE POST                                                       --}}
    {{-- ================================================================ --}}
    <div class="rounded-2xl border border-wc-border bg-wc-bg-tertiary overflow-hidden">
        {{-- Post type tabs --}}
        <div class="flex border-b border-wc-border">
            <button type="button" wire:click="$set('postType', 'text')"
                class="flex-1 flex items-center justify-center gap-2 py-3 text-xs font-semibold uppercase tracking-wider transition-colors
                {{ $postType === 'text' ? 'border-b-2 border-wc-accent text-wc-text bg-wc-accent/5' : 'text-wc-text-tertiary hover:text-wc-text-secondary' }}">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.087.16 2.185.283 3.293.369V21l4.076-4.076a1.526 1.526 0 0 1 1.037-.443 48.282 48.282 0 0 0 5.68-.494c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z" />
                </svg>
                Post
            </button>
            <button type="button" wire:click="$set('postType', 'achievement')"
                class="flex-1 flex items-center justify-center gap-2 py-3 text-xs font-semibold uppercase tracking-wider transition-colors
                {{ $postType === 'achievement' ? 'border-b-2 border-yellow-500 text-yellow-500 bg-yellow-500/5' : 'text-wc-text-tertiary hover:text-wc-text-secondary' }}">
                🏆 Logro
            </button>
            <button type="button" wire:click="$set('postType', 'pr')"
                class="flex-1 flex items-center justify-center gap-2 py-3 text-xs font-semibold uppercase tracking-wider transition-colors
                {{ $postType === 'pr' ? 'border-b-2 border-green-500 text-green-500 bg-green-500/5' : 'text-wc-text-tertiary hover:text-wc-text-secondary' }}">
                💪 Nuevo PR
            </button>
        </div>

        {{-- Input area --}}
        <form wire:submit="createPost" class="p-4">
            <div class="relative">
                <textarea
                    wire:model="postContent"
                    rows="3"
                    maxlength="1000"
                    placeholder="{{ $postType === 'achievement' ? '¿Qué lograste hoy? Cuéntalo...' : ($postType === 'pr' ? 'Describe tu nuevo récord personal...' : 'Comparte algo con la comunidad...') }}"
                    class="w-full resize-none rounded-xl border border-wc-border bg-wc-bg px-4 py-3 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent/50 focus:outline-none focus:ring-2 focus:ring-wc-accent/20 transition-all"
                ></textarea>
                <span class="absolute bottom-3 right-3 text-[10px] tabular-nums text-wc-text-tertiary">
                    {{ strlen($postContent) }}/1000
                </span>
            </div>

            @error('postContent')
                <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
            @enderror

            <div class="mt-3 flex justify-end">
                <button type="submit"
                    wire:loading.attr="disabled"
                    class="btn-press flex items-center gap-2 rounded-xl bg-wc-accent px-5 py-2.5 text-sm font-semibold text-white transition-all hover:bg-wc-accent/90 disabled:opacity-50 shadow-lg shadow-wc-accent/20">
                    <svg wire:loading.remove wire:target="createPost" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" />
                    </svg>
                    <svg wire:loading wire:target="createPost" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    Publicar
                </button>
            </div>
        </form>
    </div>

    {{-- ================================================================ --}}
    {{-- FEED                                                              --}}
    {{-- ================================================================ --}}
    <div class="space-y-3">
        @forelse ($posts as $post)
            @php
                $postTypeColors = [
                    'achievement' => ['bg' => 'bg-yellow-500/10', 'border' => 'border-yellow-500/30', 'text' => 'text-yellow-500', 'label' => '🏆 Logro'],
                    'pr'          => ['bg' => 'bg-green-500/10',  'border' => 'border-green-500/30',  'text' => 'text-green-500',  'label' => '💪 PR'],
                    'photo'       => ['bg' => 'bg-blue-500/10',   'border' => 'border-blue-500/30',   'text' => 'text-blue-400',   'label' => '📸 Foto'],
                    'text'        => ['bg' => '',                  'border' => '',                     'text' => '',               'label' => ''],
                ];
                $ptc = $postTypeColors[$post->post_type] ?? $postTypeColors['text'];
                $postReactions = $myReactions->get($post->id, []);
                $reactionCounts = $reactionCountsAll->get($post->id, collect());
                $initials = strtoupper(substr($post->client->name ?? 'M', 0, 2));
            @endphp

            <div class="group rounded-2xl border border-wc-border bg-wc-bg-tertiary overflow-hidden transition-all hover:border-wc-border/80 hover:shadow-lg hover:shadow-black/5"
                 wire:key="post-{{ $post->id }}">

                {{-- Accent strip for special types --}}
                @if($post->post_type !== 'text')
                    <div class="h-0.5 {{ $post->post_type === 'achievement' ? 'bg-gradient-to-r from-yellow-500 to-yellow-500/20' : ($post->post_type === 'pr' ? 'bg-gradient-to-r from-green-500 to-green-500/20' : 'bg-gradient-to-r from-blue-500 to-blue-500/20') }}"></div>
                @endif

                <div class="p-4">
                    {{-- Header row --}}
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-3">
                            {{-- Avatar --}}
                            <div class="relative flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-wc-accent/30 to-wc-accent/10 text-sm font-bold text-wc-accent">
                                {{ $initials }}
                                @if($post->post_type !== 'text')
                                    <span class="absolute -bottom-0.5 -right-0.5 flex h-4 w-4 items-center justify-center rounded-full bg-wc-bg-tertiary text-[10px]">
                                        {{ $post->post_type === 'achievement' ? '🏆' : ($post->post_type === 'pr' ? '💪' : '📸') }}
                                    </span>
                                @endif
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-wc-text leading-tight">{{ $post->client->name ?? 'Miembro' }}</p>
                                <p class="text-[11px] text-wc-text-tertiary">{{ $post->created_at->diffForHumans() }}</p>
                            </div>
                        </div>

                        {{-- Delete (own posts) --}}
                        @if($post->client_id === $clientId)
                            <button wire:click="deletePost({{ $post->id }})"
                                wire:confirm="¿Eliminar esta publicación?"
                                class="shrink-0 rounded-lg p-1.5 text-wc-text-tertiary opacity-0 group-hover:opacity-100 hover:bg-red-500/10 hover:text-red-400 transition-all">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                </svg>
                            </button>
                        @endif
                    </div>

                    {{-- Content --}}
                    <div class="mt-3 text-sm leading-relaxed text-wc-text whitespace-pre-line">{{ $post->content }}</div>

                    {{-- Reaction bar --}}
                    <div class="mt-4 flex flex-wrap items-center gap-1.5">
                        @foreach(['like' => ['emoji' => '👍', 'label' => ''], 'fire' => ['emoji' => '🔥', 'label' => ''], 'muscle' => ['emoji' => '💪', 'label' => ''], 'clap' => ['emoji' => '👏', 'label' => '']] as $type => $data)
                            @php
                                $isActive = in_array($type, $postReactions);
                                $count = $reactionCounts->get($type, 0);
                            @endphp
                            <button wire:click="toggleReaction({{ $post->id }}, '{{ $type }}')"
                                class="btn-press flex items-center gap-1 rounded-full px-2.5 py-1 text-xs border transition-all
                                {{ $isActive ? 'bg-wc-accent/10 border-wc-accent/40 text-wc-text scale-105' : 'border-wc-border text-wc-text-secondary hover:border-wc-accent/30 hover:bg-wc-accent/5' }}">
                                <span>{{ $data['emoji'] }}</span>
                                @if($count > 0)
                                    <span class="font-data tabular-nums font-semibold">{{ $count }}</span>
                                @endif
                            </button>
                        @endforeach

                        {{-- Comment toggle --}}
                        <button wire:click="toggleComments({{ $post->id }})"
                            class="ml-auto flex items-center gap-1.5 rounded-full px-3 py-1 text-xs text-wc-text-tertiary border border-transparent hover:border-wc-border hover:text-wc-text-secondary transition-all">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 20.25c4.97 0 9-3.694 9-8.25s-4.03-8.25-9-8.25S3 7.444 3 12c0 2.104.859 4.023 2.273 5.48.432.447.74 1.04.586 1.641a4.483 4.483 0 0 1-.923 1.785A5.969 5.969 0 0 0 6 21c1.282 0 2.47-.402 3.445-1.087.81.22 1.668.337 2.555.337Z" />
                            </svg>
                            <span class="font-data font-semibold">{{ $post->comments_count }}</span>
                        </button>
                    </div>

                    {{-- Comments --}}
                    @if($showComments[$post->id] ?? false)
                        <div class="mt-3 space-y-2 rounded-xl bg-wc-bg/60 p-3 border border-wc-border/50">
                            @foreach($post->comments->sortByDesc('created_at')->take(5) as $comment)
                                <div class="flex gap-2.5" wire:key="comment-{{ $comment->id }}">
                                    <div class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-wc-accent/15 text-[10px] font-bold text-wc-accent">
                                        {{ strtoupper(substr($comment->client->name ?? 'M', 0, 1)) }}
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <span class="text-xs font-semibold text-wc-text">{{ $comment->client->name ?? 'Miembro' }}</span>
                                        <span class="ml-1.5 text-[10px] text-wc-text-tertiary">{{ $comment->created_at?->diffForHumans() }}</span>
                                        <p class="mt-0.5 text-xs leading-relaxed text-wc-text-secondary">{{ $comment->content }}</p>
                                    </div>
                                </div>
                            @endforeach

                            @if($post->comments_count > 5)
                                <p class="text-[10px] text-wc-text-tertiary pl-8">+ {{ $post->comments_count - 5 }} comentarios más</p>
                            @endif

                            {{-- Add comment --}}
                            <div class="mt-2 flex gap-2 pt-2 border-t border-wc-border/40">
                                <input type="text"
                                    wire:model="commentTexts.{{ $post->id }}"
                                    wire:keydown.enter="addComment({{ $post->id }})"
                                    placeholder="Comentar..."
                                    maxlength="500"
                                    class="flex-1 rounded-xl border border-wc-border bg-wc-bg px-3 py-1.5 text-xs text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent/50 focus:outline-none focus:ring-1 focus:ring-wc-accent/20">
                                <button wire:click="addComment({{ $post->id }})"
                                    class="btn-press shrink-0 rounded-xl bg-wc-accent/10 border border-wc-accent/20 px-3 py-1.5 text-wc-accent hover:bg-wc-accent/20 transition-colors">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="rounded-2xl border border-dashed border-wc-border bg-wc-bg-tertiary/50 p-16 text-center">
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-wc-accent/10">
                    <svg class="h-8 w-8 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                    </svg>
                </div>
                <h3 class="mt-4 font-display text-xl text-wc-text">SIN PUBLICACIONES AÚN</h3>
                <p class="mt-2 text-sm text-wc-text-secondary">Sé el primero en compartir algo con la comunidad</p>
            </div>
        @endforelse
    </div>

    {{-- Load more --}}
    @if($posts->hasMorePages())
        <div class="flex justify-center">
            <button wire:click="loadMore"
                wire:loading.attr="disabled"
                class="btn-press flex items-center gap-2 rounded-xl border border-wc-border px-6 py-2.5 text-sm font-medium text-wc-text-secondary hover:border-wc-accent/40 hover:text-wc-accent transition-all disabled:opacity-50">
                <svg wire:loading wire:target="loadMore" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                Cargar más
            </button>
        </div>
    @endif
</div>
