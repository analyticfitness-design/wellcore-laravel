<div class="mx-auto max-w-2xl space-y-6">
    {{-- Page header --}}
    <div>
        <h1 class="font-display text-2xl tracking-wide text-wc-text sm:text-3xl">COMUNIDAD</h1>
        <p class="mt-1 text-sm text-wc-text-secondary">Comparte tus logros y conecta con otros miembros</p>
    </div>

    {{-- Create post form --}}
    <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
        <form wire:submit="createPost">
            {{-- Post type selector --}}
            <div class="mb-3 flex flex-wrap gap-2">
                <button type="button" wire:click="$set('postType', 'text')"
                    class="flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-medium border transition-colors
                    {{ $postType === 'text' ? 'bg-wc-text/10 border-wc-text/30 text-wc-text' : 'border-wc-border text-wc-text-secondary hover:border-wc-text/20' }}">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.087.16 2.185.283 3.293.369V21l4.076-4.076a1.526 1.526 0 0 1 1.037-.443 48.282 48.282 0 0 0 5.68-.494c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z" />
                    </svg>
                    Texto
                </button>
                <button type="button" wire:click="$set('postType', 'achievement')"
                    class="flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-medium border transition-colors
                    {{ $postType === 'achievement' ? 'bg-yellow-500/10 border-yellow-500/30 text-yellow-500' : 'border-wc-border text-wc-text-secondary hover:border-yellow-500/20' }}">
                    <span class="text-sm">&#127942;</span>
                    Logro
                </button>
                <button type="button" wire:click="$set('postType', 'pr')"
                    class="flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-medium border transition-colors
                    {{ $postType === 'pr' ? 'bg-green-500/10 border-green-500/30 text-green-500' : 'border-wc-border text-wc-text-secondary hover:border-green-500/20' }}">
                    <span class="text-sm">&#128170;</span>
                    Nuevo PR
                </button>
            </div>

            {{-- Textarea --}}
            <div class="relative">
                <textarea
                    wire:model="postContent"
                    rows="3"
                    maxlength="1000"
                    placeholder="{{ $postType === 'achievement' ? 'Cuenta tu logro...' : ($postType === 'pr' ? 'Describe tu nuevo record personal...' : 'Comparte algo con la comunidad...') }}"
                    class="w-full resize-none rounded-lg border border-wc-border bg-wc-bg px-4 py-3 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent/50 focus:outline-none focus:ring-1 focus:ring-wc-accent/30"
                ></textarea>
                <span class="absolute bottom-2 right-3 text-[10px] tabular-nums text-wc-text-tertiary">
                    {{ strlen($postContent) }}/1000
                </span>
            </div>

            @error('postContent')
                <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
            @enderror

            {{-- Submit --}}
            <div class="mt-3 flex justify-end">
                <button type="submit"
                    wire:loading.attr="disabled"
                    class="flex items-center gap-2 rounded-lg bg-wc-accent px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-wc-accent/90 disabled:opacity-50">
                    <svg wire:loading.remove wire:target="createPost" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
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

    {{-- Feed --}}
    <div class="space-y-4">
        @forelse ($posts as $post)
            <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5" wire:key="post-{{ $post->id }}">
                {{-- Post header --}}
                <div class="flex items-start justify-between">
                    <div class="flex items-center gap-3">
                        {{-- Avatar --}}
                        <div class="flex h-9 w-9 items-center justify-center rounded-full bg-wc-accent/20 text-sm font-semibold text-wc-accent">
                            {{ strtoupper(substr($post->client->name ?? 'M', 0, 1)) }}
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-wc-text">{{ $post->client->name ?? 'Miembro' }}</p>
                            <div class="flex items-center gap-2">
                                <span class="text-[11px] text-wc-text-tertiary">{{ $post->created_at->diffForHumans() }}</span>
                                {{-- Post type badge --}}
                                @if($post->post_type === 'achievement')
                                    <span class="inline-flex items-center gap-1 rounded-full bg-yellow-500/10 px-2 py-0.5 text-[10px] font-medium text-yellow-500">
                                        &#127942; Logro
                                    </span>
                                @elseif($post->post_type === 'pr')
                                    <span class="inline-flex items-center gap-1 rounded-full bg-green-500/10 px-2 py-0.5 text-[10px] font-medium text-green-500">
                                        &#128170; PR
                                    </span>
                                @elseif($post->post_type === 'photo')
                                    <span class="inline-flex items-center gap-1 rounded-full bg-blue-500/10 px-2 py-0.5 text-[10px] font-medium text-blue-500">
                                        &#128247; Foto
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Delete button (own posts only) --}}
                    @if($post->client_id === $clientId)
                        <button wire:click="deletePost({{ $post->id }})"
                            wire:confirm="¿Seguro que quieres eliminar esta publicación?"
                            class="rounded-lg p-1.5 text-wc-text-tertiary hover:bg-wc-bg hover:text-red-400 transition-colors"
                            title="Eliminar">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                            </svg>
                        </button>
                    @endif
                </div>

                {{-- Content --}}
                <div class="mt-3 text-sm leading-relaxed text-wc-text whitespace-pre-line">{{ $post->content }}</div>

                {{-- Reaction bar --}}
                <div class="mt-4 flex flex-wrap items-center gap-2">
                    @php
                        $postReactions = $myReactions->get($post->id, []);
                        $reactionCounts = $post->reactions->groupBy('reaction_type')->map->count();
                    @endphp

                    @foreach(['like' => "\u{1F44D}", 'fire' => "\u{1F525}", 'muscle' => "\u{1F4AA}", 'clap' => "\u{1F44F}"] as $type => $emoji)
                        @php
                            $isActive = in_array($type, $postReactions);
                            $count = $reactionCounts->get($type, 0);
                        @endphp
                        <button wire:click="toggleReaction({{ $post->id }}, '{{ $type }}')"
                            class="flex items-center gap-1 rounded-full px-3 py-1 text-xs border transition-colors
                            {{ $isActive ? 'bg-wc-accent/10 border-wc-accent/40 text-wc-text' : 'border-wc-border text-wc-text-secondary hover:border-wc-accent/40' }}">
                            <span>{{ $emoji }}</span>
                            @if($count > 0)
                                <span class="tabular-nums">{{ $count }}</span>
                            @endif
                        </button>
                    @endforeach
                </div>

                {{-- Comments section --}}
                <div class="mt-4 border-t border-wc-border pt-3">
                    {{-- Toggle comments --}}
                    <button wire:click="toggleComments({{ $post->id }})"
                        class="flex items-center gap-1.5 text-xs text-wc-text-secondary hover:text-wc-text transition-colors">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 20.25c4.97 0 9-3.694 9-8.25s-4.03-8.25-9-8.25S3 7.444 3 12c0 2.104.859 4.023 2.273 5.48.432.447.74 1.04.586 1.641a4.483 4.483 0 0 1-.923 1.785A5.969 5.969 0 0 0 6 21c1.282 0 2.47-.402 3.445-1.087.81.22 1.668.337 2.555.337Z" />
                        </svg>
                        {{ $post->comments_count }} {{ $post->comments_count === 1 ? 'comentario' : 'comentarios' }}
                        @if($post->comments_count > 0)
                            <svg class="h-3 w-3 transition-transform {{ ($showComments[$post->id] ?? false) ? 'rotate-180' : '' }}" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                            </svg>
                        @endif
                    </button>

                    {{-- Comments list --}}
                    @if($showComments[$post->id] ?? false)
                        <div class="mt-3 space-y-3">
                            @foreach($post->comments->sortByDesc('created_at')->take(5) as $comment)
                                <div class="flex gap-2.5" wire:key="comment-{{ $comment->id }}">
                                    <div class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-wc-accent/10 text-[10px] font-semibold text-wc-accent">
                                        {{ strtoupper(substr($comment->client->name ?? 'M', 0, 1)) }}
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-baseline gap-2">
                                            <span class="text-xs font-semibold text-wc-text">{{ $comment->client->name ?? 'Miembro' }}</span>
                                            <span class="text-[10px] text-wc-text-tertiary">{{ $comment->created_at?->diffForHumans() }}</span>
                                        </div>
                                        <p class="mt-0.5 text-xs leading-relaxed text-wc-text-secondary">{{ $comment->content }}</p>
                                    </div>
                                </div>
                            @endforeach

                            @if($post->comments_count > 5)
                                <p class="text-[11px] text-wc-text-tertiary">
                                    y {{ $post->comments_count - 5 }} {{ ($post->comments_count - 5) === 1 ? 'comentario más' : 'comentarios más' }}...
                                </p>
                            @endif
                        </div>
                    @endif

                    {{-- Add comment input --}}
                    <div class="mt-3 flex gap-2">
                        <input type="text"
                            wire:model.defer="commentTexts.{{ $post->id }}"
                            wire:keydown.enter="addComment({{ $post->id }})"
                            placeholder="Escribe un comentario..."
                            maxlength="500"
                            class="flex-1 rounded-lg border border-wc-border bg-wc-bg px-3 py-1.5 text-xs text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent/50 focus:outline-none focus:ring-1 focus:ring-wc-accent/30">
                        <button wire:click="addComment({{ $post->id }})"
                            class="shrink-0 rounded-lg border border-wc-border px-3 py-1.5 text-xs text-wc-text-secondary hover:border-wc-accent/40 hover:text-wc-accent transition-colors">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        @empty
            {{-- Empty state --}}
            <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-10 text-center">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-wc-accent/10">
                    <svg class="h-7 w-7 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                    </svg>
                </div>
                <p class="mt-4 text-sm font-medium text-wc-text">Sin publicaciones todavia</p>
                <p class="mt-1 text-xs text-wc-text-tertiary">Se el primero en compartir algo con la comunidad</p>
            </div>
        @endforelse
    </div>

    {{-- Load more --}}
    @if($posts->hasMorePages())
        <div class="flex justify-center">
            <button wire:click="loadMore"
                wire:loading.attr="disabled"
                class="flex items-center gap-2 rounded-lg border border-wc-border px-5 py-2 text-sm font-medium text-wc-text-secondary hover:border-wc-accent/40 hover:text-wc-text transition-colors disabled:opacity-50">
                <svg wire:loading wire:target="loadMore" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                Cargar mas
            </button>
        </div>
    @endif
</div>
