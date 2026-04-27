<link rel="stylesheet" href="{{ asset('css/community.css') }}?v={{ filemtime(public_path('css/community.css')) }}">

<div class="community-grid">

  {{-- ════════ FEED ════════ --}}
  <div class="feed-col">

    {{-- ================================================================ --}}
    {{-- STORIES ROW                                                       --}}
    {{-- ================================================================ --}}
    <div class="stories-card">
      <div class="stories-row">

        {{-- Tu historia (siempre primero) --}}
        <div class="story add">
          <div class="story-circle"></div>
          <span class="story-name">Tu historia</span>
        </div>

        {{-- Miembros activos --}}
        @foreach($storiesMembers as $member)
          <div class="story {{ $member['has_new'] ? 'has-new' : '' }}">
            <div class="story-circle">
              <div class="story-av av-{{ $member['color'] }}">{{ $member['initials'] }}</div>
              @if($member['has_new'])
                <span class="story-badge">
                  {{ $member['last_type'] === 'pr' ? '💪' : '🏆' }}
                </span>
              @endif
            </div>
            <span class="story-name">{{ \Illuminate\Support\Str::limit(explode(' ', $member['name'])[0], 8, '') }}</span>
          </div>
        @endforeach

      </div>
    </div>

    {{-- ================================================================ --}}
    {{-- COMMUNITY HEADER                                                  --}}
    {{-- ================================================================ --}}
    <div class="ch-card">
      <div class="ch-label">RED SOCIAL FITNESS · WELLCORE</div>
      <div class="ch-title">COMUNIDAD</div>
      <div class="ch-sub">
        <strong>{{ $communityStats['total_posts'] }} posts</strong>
        · <strong>{{ $communityStats['active_members'] }} miembros</strong>
        · comparte, celebra, supérate
      </div>
    </div>

    {{-- ================================================================ --}}
    {{-- COMPOSER                                                           --}}
    {{-- ================================================================ --}}
    <div class="composer">

      {{-- Tabs --}}
      <div class="composer-tabs">
        <button type="button"
          wire:click="$set('postType', 'text')"
          @class(['c-tab', 'on-text' => $postType === 'text'])>
          <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.087.16 2.185.283 3.293.369V21l4.076-4.076a1.526 1.526 0 0 1 1.037-.443 48.282 48.282 0 0 0 5.68-.494c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z"/>
          </svg>
          Post
        </button>
        <button type="button"
          wire:click="$set('postType', 'achievement')"
          @class(['c-tab', 'on-gold' => $postType === 'achievement'])>
          🏆 Logro
        </button>
        <button type="button"
          wire:click="$set('postType', 'pr')"
          @class(['c-tab', 'on-green' => $postType === 'pr'])>
          💪 PR
        </button>
      </div>

      {{-- Body: avatar + textarea --}}
      <form wire:submit="createPost">
        <div class="composer-body">
          <div class="composer-av">{{ $myInitials }}</div>
          <textarea
            wire:model.live.debounce.300ms="postContent"
            class="composer-textarea"
            rows="3"
            maxlength="1000"
            placeholder="{{ $postType === 'achievement' ? '¿Qué lograste hoy? Cuéntalo...' : ($postType === 'pr' ? 'Describe tu nuevo récord personal...' : 'Comparte algo con la comunidad...') }}"
          ></textarea>
        </div>

        @error('postContent')
          <p style="margin: 4px 16px 0; font-size:12px; color:#f87171;">{{ $message }}</p>
        @enderror

        <div class="composer-actions">
          <div class="composer-extras">
            <button type="button" class="composer-extra" title="Imagen">
              <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/>
              </svg>
            </button>
            <button type="button" class="composer-extra" title="Emoji">
              <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.182 15.182a4.5 4.5 0 0 1-6.364 0M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0ZM9.75 9.75c0 .414-.168.75-.375.75S9 10.164 9 9.75 9.168 9 9.375 9s.375.336.375.75Zm5.25 0c0 .414-.168.75-.375.75s-.375-.336-.375-.75.168-.75.375-.75.375.336.375.75Z"/>
              </svg>
            </button>
          </div>
          <div class="composer-right">
            <span class="char-count">{{ strlen($postContent) }}/1000</span>
            <button type="submit" class="btn-publicar"
              wire:loading.attr="disabled"
              wire:target="createPost">
              <svg wire:loading.remove wire:target="createPost" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5"/>
              </svg>
              <svg wire:loading wire:target="createPost" width="12" height="12" class="animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
              </svg>
              Publicar
            </button>
          </div>
        </div>
      </form>
    </div>

    {{-- ================================================================ --}}
    {{-- FEED — Post Cards                                                  --}}
    {{-- ================================================================ --}}
    <div style="display:flex;flex-direction:column;gap:12px;">
      @forelse ($posts as $post)
        @php
          $postReactions = $myReactions->get($post->id, []);
          $reactionCounts = $reactionCountsAll->get($post->id, collect());
          $avatarColor = $this->colorForName($post->client->name ?? 'M');
          $initials    = $this->initialsFor($post->client->name ?? 'M');
          $reactionMap = [
              'like'   => ['emoji' => '👍', 'label' => 'Genial'],
              'fire'   => ['emoji' => '🔥', 'label' => 'Fuego'],
              'muscle' => ['emoji' => '💪', 'label' => 'Fuerza'],
              'clap'   => ['emoji' => '🎉', 'label' => 'Bravo'],
          ];
        @endphp

        <div class="post-card" wire:key="post-{{ $post->id }}">

          {{-- Strip de color para achievement/pr --}}
          @if($post->post_type === 'achievement')
            <div class="post-strip strip-gold"></div>
          @elseif($post->post_type === 'pr')
            <div class="post-strip strip-green"></div>
          @endif

          <div class="post-body">
            <div class="post-head">

              {{-- Avatar con badge de tipo --}}
              <div class="post-av av-{{ $avatarColor }}">
                {{ $initials }}
                @if($post->post_type !== 'text')
                  <span class="post-av-badge">
                    {{ $post->post_type === 'achievement' ? '🏆' : ($post->post_type === 'pr' ? '💪' : '📸') }}
                  </span>
                @endif
              </div>

              {{-- Meta: nombre + tiempo + pill de tipo --}}
              <div class="post-meta">
                <div class="post-name">
                  {{ $post->client->name ?? 'Miembro' }}
                  @if($post->client_id === $clientId)
                    <span style="font-size:9px;background:var(--wc-accent-light,rgba(220,38,38,0.15));color:var(--wc-accent);border-radius:4px;padding:1px 6px;margin-left:4px;font-weight:700;letter-spacing:0.06em;">TÚ</span>
                  @endif
                </div>
                <div class="post-time">{{ $post->created_at->diffForHumans() }}</div>
                @if($post->post_type === 'achievement')
                  <span class="type-pill pill-gold">🏆 LOGRO</span>
                @elseif($post->post_type === 'pr')
                  <span class="type-pill pill-green">💪 NUEVO PR</span>
                @endif
              </div>

              {{-- Botón eliminar (solo posts propios, visible en hover) --}}
              @if($post->client_id === $clientId)
                <button class="post-delete-btn"
                  wire:click="deletePost({{ $post->id }})"
                  wire:confirm="¿Eliminar esta publicación?">
                  <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/>
                  </svg>
                </button>
              @endif
            </div>

            {{-- Contenido del post --}}
            <div class="post-text">{{ $post->content }}</div>

            {{-- Reactions + toggle comments --}}
            <div class="reactions">
              @foreach($reactionMap as $type => $data)
                @php $isActive = in_array($type, $postReactions); $count = $reactionCounts->get($type, 0); @endphp
                <button
                  wire:click="toggleReaction({{ $post->id }}, '{{ $type }}')"
                  title="{{ $data['label'] }}"
                  @class(['rxn', 'on' => $isActive])>
                  {{ $data['emoji'] }}
                  @if($count > 0)<span class="n">{{ $count }}</span>@endif
                </button>
              @endforeach

              <button wire:click="toggleComments({{ $post->id }})" class="cmt-toggle">
                💬 <span class="n">{{ $post->comments_count }}</span>
              </button>
            </div>

            {{-- Comments (expandible) --}}
            @if($showComments[$post->id] ?? false)
              <div class="comments-box">

                @foreach($post->comments->sortByDesc('created_at')->take(5) as $comment)
                  @php $cColor = $this->colorForName($comment->client->name ?? 'M'); @endphp
                  <div class="cmt-row" wire:key="comment-{{ $comment->id }}">
                    <div class="cmt-av av-{{ $cColor }}">
                      {{ $this->initialsFor($comment->client->name ?? 'M') }}
                    </div>
                    <div class="cmt-body">
                      <span class="cmt-name">{{ $comment->client->name ?? 'Miembro' }}</span>
                      <span class="cmt-time">{{ $comment->created_at?->diffForHumans() }}</span>
                      <div class="cmt-text">{{ $comment->content }}</div>
                    </div>
                  </div>
                @endforeach

                @if($post->comments_count > 5)
                  <span class="cmt-more">+ {{ $post->comments_count - 5 }} comentarios más</span>
                @endif

                {{-- Agregar comentario --}}
                <div class="cmt-input-row">
                  <input
                    type="text"
                    class="cmt-input"
                    wire:model="commentTexts.{{ $post->id }}"
                    wire:keydown.enter="addComment({{ $post->id }})"
                    placeholder="Comentar..."
                    maxlength="500">
                  <button type="button" class="cmt-send" wire:click="addComment({{ $post->id }})">
                    <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5"/>
                    </svg>
                  </button>
                </div>

              </div>
            @endif

          </div>
        </div>

      @empty
        <div class="feed-empty">
          <div class="feed-empty-icon">
            <svg width="32" height="32" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="var(--wc-accent)">
              <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z"/>
            </svg>
          </div>
          <h3 style="margin-top:16px;font-family:var(--font-display);font-size:20px;letter-spacing:0.06em;text-transform:uppercase;color:var(--wc-text);">SIN PUBLICACIONES AÚN</h3>
          <p style="margin-top:8px;font-size:13px;color:var(--wc-text-secondary);">Sé el primero en compartir algo con la comunidad</p>
        </div>
      @endforelse
    </div>

    {{-- Load more --}}
    @if($posts->hasMorePages())
      <div style="display:flex;justify-content:center;margin-top:8px;">
        <button wire:click="loadMore" wire:loading.attr="disabled" class="load-more-btn">
          <svg wire:loading wire:target="loadMore" width="16" height="16" class="animate-spin" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
          </svg>
          Cargar más
        </button>
      </div>
    @endif

  </div>{{-- /feed-col --}}

  {{-- ════════ RIGHT PANEL ════════ --}}
  <div class="right-col">

    {{-- Card: Mi fase actual --}}
    <div class="panel">
      <div class="panel-title">Mi fase actual</div>
      @if($myPhase)
        <div class="phase-box">
          <div class="phase-dot"></div>
          <div>
            <div class="phase-name">{{ $myPhase['name'] }}</div>
            <div class="phase-week">Semana {{ $myPhase['week'] }} de {{ $myPhase['total_weeks'] }}</div>
          </div>
        </div>
      @else
        <p style="font-size:12px;color:var(--wc-text-tertiary);">Sin fase asignada aún</p>
      @endif
    </div>

    {{-- Card: Stats comunidad 2×2 --}}
    <div class="panel">
      <div class="panel-title">Comunidad</div>
      <div class="stats-grid">
        <div class="stat-box">
          <div class="stat-box-num">{{ $communityStats['total_posts'] }}</div>
          <div class="stat-box-lbl">Posts</div>
        </div>
        <div class="stat-box">
          <div class="stat-box-num">{{ $communityStats['active_members'] }}</div>
          <div class="stat-box-lbl">Miembros</div>
        </div>
        <div class="stat-box">
          <div class="stat-box-num">{{ $communityStats['total_achievements'] }}</div>
          <div class="stat-box-lbl">Logros</div>
        </div>
        <div class="stat-box">
          <div class="stat-box-num">{{ $communityStats['total_prs'] }}</div>
          <div class="stat-box-lbl">PRs</div>
        </div>
      </div>
    </div>

    {{-- Card: Miembros activos --}}
    <div class="panel">
      <div class="panel-title">Miembros activos</div>
      @foreach($activeMembersList as $member)
        @php $mColor = $this->colorForName($member->name); @endphp
        <div class="member">
          <div class="m-av av-{{ $mColor }}">{{ $this->initialsFor($member->name) }}</div>
          <div>
            <div class="m-name">{{ $member->name }}</div>
            <div class="m-phase">S1 · Adaptación</div>
          </div>
          <div class="online-dot"></div>
        </div>
      @endforeach
      @if($activeMembersList->isEmpty())
        <p style="font-size:12px;color:var(--wc-text-tertiary);">Sin miembros activos aún</p>
      @endif
    </div>

  </div>{{-- /right-col --}}

</div>{{-- /community-grid --}}
