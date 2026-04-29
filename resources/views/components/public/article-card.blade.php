{{--
    <x-public.article-card> — Card de artículo para BlogTeaser y /blog index.

    Props:
        $post (object|array) — objeto con: title, slug, excerpt, category, author_name, published_at, reading_minutes, cover (opcional)
        $lazy (bool) — lazy loading. Default true.
--}}
@props([
    'post' => null,
    'lazy' => true,
])

@if($post)
@php
    $slug        = is_array($post) ? ($post['slug'] ?? '#')          : ($post->slug ?? '#');
    $title       = is_array($post) ? ($post['title'] ?? '')          : ($post->title ?? '');
    $excerpt     = is_array($post) ? ($post['excerpt'] ?? '')        : ($post->excerpt ?? '');
    $category    = is_array($post) ? ($post['category'] ?? '')       : ($post->category ?? '');
    $authorName  = is_array($post) ? ($post['author_name'] ?? '')    : ($post->author_name ?? ($post->author->name ?? ''));
    $publishedAt = is_array($post) ? ($post['published_at'] ?? null) : ($post->published_at ?? null);
    $readMins    = is_array($post) ? ($post['reading_minutes'] ?? 5) : ($post->reading_minutes ?? 5);
    $cover       = is_array($post) ? ($post['cover'] ?? null)        : ($post->cover ?? null);
    $coverAvif   = is_array($post) ? ($post['cover_avif'] ?? null)   : ($post->cover_avif ?? null);
    $coverWebp   = is_array($post) ? ($post['cover_webp'] ?? null)   : ($post->cover_webp ?? null);
    $url         = $slug !== '#' ? route('blog.show', $slug) : '#';
@endphp

<article class="bt-card" data-animate="fadeInUp">
    <a href="{{ $url }}" style="display:contents;text-decoration:none;color:inherit;">
        @if($cover)
            <picture class="bt-cover-pic">
                @if($coverAvif)<source srcset="{{ $coverAvif }}" type="image/avif">@endif
                @if($coverWebp)<source srcset="{{ $coverWebp }}" type="image/webp">@endif
                <img src="{{ $cover }}" alt="{{ $title }}"
                     loading="{{ $lazy ? 'lazy' : 'eager' }}"
                     width="600" height="375"
                     decoding="async">
            </picture>
        @else
            <div class="bt-cover-placeholder" aria-hidden="true"></div>
        @endif

        @if($category)
            <span class="bt-cat">{{ strtoupper($category) }}</span>
        @endif

        <h3 class="bt-title">{{ $title }}</h3>

        @if($excerpt)
            <p class="bt-excerpt">{{ $excerpt }}</p>
        @endif

        <div class="bt-meta">
            @if($authorName)<span>{{ $authorName }}</span><span>·</span>@endif
            @if($publishedAt)
                <time datetime="{{ is_string($publishedAt) ? $publishedAt : $publishedAt->toIso8601String() }}">
                    {{ strtoupper(is_string($publishedAt) ? \Carbon\Carbon::parse($publishedAt)->isoFormat('D MMM') : $publishedAt->isoFormat('D MMM')) }}
                </time>
                <span>·</span>
            @endif
            <span>{{ $readMins }} {{ __('home.blog_min_read') }}</span>
        </div>
    </a>
</article>
@endif
