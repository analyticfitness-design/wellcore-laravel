@php
    $articles = \App\Http\Controllers\BlogController::getArticles();
    $featured = $articles[0] ?? null;
    $rest     = array_slice($articles, 1);
    $isEs     = app()->getLocale() === 'es';

    // Build category counts (localized labels stored in $article['category']).
    $catCounts = collect($articles)->countBy('category');

    // JSON-LD Blog schema.
    $blogSchema = [
        '@context'    => 'https://schema.org',
        '@type'       => 'Blog',
        'name'        => __('blog.meta_title'),
        'description' => __('blog.meta_description'),
        'url'         => route('blog.index'),
        'blogPost'    => collect($articles)->map(fn ($a) => [
            '@type'         => 'BlogPosting',
            'headline'      => $a['title'],
            'url'           => route('blog.show', $a['slug']),
            'datePublished' => $a['date'],
            'author'        => ['@type' => 'Organization', 'name' => 'WellCore Fitness'],
        ])->values()->all(),
    ];
@endphp

<x-layouts.public bodyClass="blog-page">
    <x-slot:title>{{ __('blog.meta_title') }}</x-slot:title>
    <x-slot:description>{{ __('blog.meta_description') }}</x-slot:description>

<div
    class="blog-page-root"
    x-data="{
        category: 'all',
        search: '',
        get isFiltering() { return this.category !== 'all' || this.search.trim().length > 0; },
        matchesCategory(cat) { return this.category === 'all' || cat === this.category; },
        matchesSearch(title) {
            const s = this.search.trim().toLowerCase();
            return s === '' || title.toLowerCase().includes(s);
        }
    }"
>

{{-- ════════════════════════════════════════════════════════════════
     iOS-feel + editorial blog styles — inline (Sprint 4 noche).
     Scoped a .blog-page-root.
     ════════════════════════════════════════════════════════════════ --}}
<style>
.blog-page-root {
    --blog-bg:        #0a0a0a;
    --blog-bg-2:      #111111;
    --blog-bg-3:      #1a1a1a;
    --blog-text:      #FAFAFA;
    --blog-text-2:    #A3A3A3;
    --blog-text-3:    #737373;
    --blog-text-4:    #525252;
    --blog-border:    rgba(255,255,255,0.07);
    --blog-border-2:  rgba(255,255,255,0.12);
    --blog-red:       #DC2626;
    --blog-red-soft:  rgba(220,38,38,0.04);
    --blog-red-text:  #F87171;
    --blog-gold:      #D4A04C;
    --blog-ease-out:  cubic-bezier(.22,1,.36,1);

    position: relative;
    min-height: calc(100vh - 64px);
    background: var(--blog-bg);
    color: var(--blog-text);
    overflow-x: hidden;
    isolation: isolate;
}
.blog-page-root::before {
    content: '';
    position: absolute; inset: 0;
    pointer-events: none; z-index: 0;
    background:
        radial-gradient(ellipse 70% 40% at 0% -10%, rgba(220,38,38,0.10), transparent 55%),
        radial-gradient(ellipse 50% 30% at 110% 5%, rgba(212,160,76,0.05), transparent 50%);
}

.blog-shell {
    position: relative; z-index: 1;
    max-width: 1200px;
    margin: 0 auto;
    padding-left: env(safe-area-inset-left);
    padding-right: env(safe-area-inset-right);
}

/* ── HERO ─────────────────────────────────────────────── */
.blog-hero {
    padding: 56px 24px 32px;
    border-bottom: 1px solid var(--blog-border);
    max-width: 760px;
    margin: 0 auto;
}
.blog-hero-eyebrow {
    display: inline-flex; align-items: center; gap: 10px;
    font-family: 'JetBrains Mono', 'SF Mono', monospace;
    font-size: 11px; letter-spacing: 0.22em;
    text-transform: uppercase;
    color: var(--blog-red);
    margin-bottom: 16px;
}
.blog-hero-eyebrow::before {
    content: ''; width: 18px; height: 1px;
    background: var(--blog-red);
}
.blog-hero-title {
    font-family: 'Oswald', Impact, sans-serif;
    font-weight: 600;
    font-size: clamp(56px, 16vw, 96px);
    line-height: 0.9;
    letter-spacing: 0.005em;
    color: var(--blog-text);
    text-transform: uppercase;
    margin-bottom: 18px;
}
.blog-hero-title em {
    font-style: normal;
    color: var(--blog-red);
}
.blog-hero-sub {
    font-family: 'Fraunces', Georgia, serif;
    font-style: italic;
    font-size: 17px;
    line-height: 1.45;
    color: var(--blog-gold);
    max-width: 38ch;
}

/* ── FEATURED ARTICLE ──────────────────────────────────── */
.blog-featured {
    margin: 32px 0 0;
    padding: 0 24px;
    max-width: 1080px;
    margin-left: auto; margin-right: auto;
}
.blog-featured-card {
    position: relative;
    display: block;
    text-decoration: none;
    overflow: hidden;
    border-radius: 14px;
    border: 1px solid var(--blog-border);
    background: var(--blog-bg-3);
    transition: border-color 0.3s var(--blog-ease-out), transform 0.3s var(--blog-ease-out);
}
.blog-featured-card:hover {
    border-color: rgba(220,38,38,0.3);
    transform: translateY(-2px);
}
.blog-featured-bg {
    position: relative;
    aspect-ratio: 16/9;
    background: linear-gradient(160deg, #1c1010, #0a0a0a 70%);
    overflow: hidden;
}
.blog-featured-bg::before {
    content: '';
    position: absolute; inset: 0;
    background: var(--blog-featured-tone, radial-gradient(ellipse 70% 50% at 30% 30%, rgba(220,38,38,0.18), transparent 60%));
}
.blog-featured-bg::after {
    content: '';
    position: absolute; inset: 0;
    background:
        repeating-linear-gradient(45deg, transparent, transparent 22px, rgba(255,255,255,0.012) 22px, rgba(255,255,255,0.012) 23px),
        linear-gradient(to top, rgba(0,0,0,0.95) 0%, rgba(0,0,0,0.5) 50%, transparent 100%);
}
.blog-featured-issue {
    position: absolute; top: 18px; left: 18px;
    z-index: 2;
    font-family: 'JetBrains Mono', 'SF Mono', monospace;
    font-size: 10px; color: rgba(255,255,255,0.5);
    letter-spacing: 0.18em; text-transform: uppercase;
    display: flex; align-items: center; gap: 10px;
}
.blog-featured-issue-num {
    font-family: 'Oswald', Impact, sans-serif;
    font-weight: 600;
    font-size: 13px;
    color: var(--blog-red);
    letter-spacing: 0.06em;
}
.blog-featured-content {
    position: absolute; bottom: 0; left: 0; right: 0;
    z-index: 2;
    padding: 22px 22px 22px;
}
.blog-featured-eyebrow {
    display: inline-flex; align-items: center; gap: 10px;
    font-family: 'JetBrains Mono', 'SF Mono', monospace;
    font-size: 9.5px; letter-spacing: 0.2em;
    text-transform: uppercase;
    color: rgba(255,255,255,0.7);
    margin-bottom: 12px;
}
.blog-featured-eyebrow-tag {
    background: rgba(220,38,38,0.18);
    border: 1px solid rgba(220,38,38,0.32);
    color: #F87171;
    padding: 3px 9px;
    border-radius: 999px;
    letter-spacing: 0.14em;
    font-size: 9px;
}
.blog-featured-title {
    font-family: 'Oswald', Impact, sans-serif;
    font-weight: 600;
    font-size: clamp(32px, 6vw, 56px);
    line-height: 0.95;
    letter-spacing: 0.01em;
    text-transform: uppercase;
    color: var(--blog-text);
    margin-bottom: 14px;
}
.blog-featured-excerpt {
    font-family: 'Raleway', system-ui, sans-serif;
    font-size: 14.5px;
    line-height: 1.55;
    color: rgba(255,255,255,0.78);
    margin-bottom: 14px;
    max-width: 60ch;
}
.blog-featured-meta {
    font-family: 'JetBrains Mono', 'SF Mono', monospace;
    font-size: 9.5px;
    letter-spacing: 0.14em;
    text-transform: uppercase;
    color: rgba(255,255,255,0.5);
    display: flex; flex-wrap: wrap; gap: 8px; align-items: center;
    margin-bottom: 14px;
}
.blog-featured-meta-dot {
    width: 3px; height: 3px;
    background: var(--blog-red);
    border-radius: 50%;
}
.blog-featured-cta {
    display: inline-flex; align-items: center; gap: 8px;
    font-family: 'Oswald', Impact, sans-serif;
    font-weight: 600;
    font-size: 12px; letter-spacing: 0.18em;
    text-transform: uppercase;
    color: var(--blog-text);
    transition: color 0.2s, letter-spacing 0.2s var(--blog-ease-out);
}
.blog-featured-card:hover .blog-featured-cta {
    color: var(--blog-red-text);
    letter-spacing: 0.22em;
}

/* ── TABS CATEGORÍAS ───────────────────────────────────── */
.blog-tabs-wrap {
    margin: 32px 0 0;
    border-bottom: 1px solid var(--blog-border);
    background: rgba(10,10,10,0.92);
    -webkit-backdrop-filter: blur(16px) saturate(140%);
    backdrop-filter: blur(16px) saturate(140%);
}
.blog-tabs {
    display: flex;
    overflow-x: auto;
    scrollbar-width: none; -ms-overflow-style: none;
    padding: 0 24px;
    max-width: 1200px;
    margin: 0 auto;
    align-items: center;
}
.blog-tabs::-webkit-scrollbar { display: none; }
.blog-tab {
    flex-shrink: 0;
    display: inline-flex; align-items: center; gap: 8px;
    padding: 14px 16px 12px;
    border: none; background: none; cursor: pointer;
    border-bottom: 2px solid transparent;
    transition: border-color 0.25s var(--blog-ease-out);
    font-family: 'Oswald', Impact, sans-serif;
    font-weight: 500;
    font-size: 13px; letter-spacing: 0.12em;
    text-transform: uppercase;
    color: var(--blog-text-3);
    white-space: nowrap;
    -webkit-tap-highlight-color: transparent;
}
.blog-tab.is-active {
    border-bottom-color: var(--blog-red);
    color: var(--blog-text);
}
.blog-tab:hover { color: var(--blog-text-2); }
.blog-tab-count {
    font-family: 'JetBrains Mono', 'SF Mono', monospace;
    font-size: 9.5px; letter-spacing: 0.05em;
    color: var(--blog-text-4);
}
.blog-tab.is-active .blog-tab-count { color: var(--blog-red); }
.blog-tab-search {
    margin-left: auto;
    padding: 0 8px;
    flex-shrink: 0;
    position: relative;
}
.blog-tab-search input {
    background: rgba(26,26,26,0.7);
    border: 1px solid var(--blog-border);
    border-radius: 8px;
    padding: 8px 12px 8px 30px;
    color: var(--blog-text);
    font-family: 'Raleway', system-ui, sans-serif;
    font-size: 13px;
    width: 200px;
    outline: none;
    transition: border-color 0.2s, width 0.2s var(--blog-ease-out);
}
.blog-tab-search input:focus {
    border-color: var(--blog-red);
    width: 240px;
}
.blog-tab-search::before {
    content: '⌕';
    position: absolute; left: 16px; top: 50%; transform: translateY(-50%);
    color: var(--blog-text-3);
    font-size: 14px;
    pointer-events: none;
}

/* ── DIVIDER ──────────────────────────────────────────── */
.blog-divider {
    display: flex; align-items: center; gap: 14px;
    padding: 28px 24px 0;
    max-width: 1200px;
    margin: 0 auto;
    font-family: 'Oswald', Impact, sans-serif;
    font-size: 10px; letter-spacing: 0.32em;
    color: var(--blog-text-3); opacity: 0.6;
    text-transform: uppercase;
    font-weight: 500;
}
.blog-divider-line {
    flex: 1; height: 1px;
    background: linear-gradient(to right, transparent, var(--blog-border), transparent);
}

/* ── GRID ─────────────────────────────────────────────── */
.blog-grid {
    padding: 28px 24px 0;
    max-width: 1200px;
    margin: 0 auto;
    display: grid;
    grid-template-columns: 1fr;
    gap: 16px;
}
@media (min-width: 720px) {
    .blog-grid { grid-template-columns: repeat(2, 1fr); gap: 20px; }
    .blog-card-wide { grid-column: span 2; }
}
@media (min-width: 1024px) {
    .blog-grid { grid-template-columns: repeat(3, 1fr); gap: 24px; }
    .blog-card-wide { grid-column: span 3; }
}

.blog-card, .blog-card-wide {
    position: relative;
    display: block;
    text-decoration: none;
    overflow: hidden;
    border-radius: 12px;
    border: 1px solid var(--blog-border);
    background: var(--blog-bg-3);
    transition: border-color 0.3s var(--blog-ease-out), transform 0.3s var(--blog-ease-out);
}
.blog-card:hover, .blog-card-wide:hover {
    border-color: rgba(220,38,38,0.3);
    transform: translateY(-3px);
}
.blog-card-img {
    position: relative;
    aspect-ratio: 4/3;
    overflow: hidden;
    background: linear-gradient(160deg, #1a1a1a, #0a0a0a);
}
.blog-card-img::before {
    content: '';
    position: absolute; inset: 0;
    background: var(--card-tone, radial-gradient(ellipse 70% 50% at 35% 35%, rgba(220,38,38,0.15), transparent 60%));
}
.blog-card-img::after {
    content: '';
    position: absolute; inset: 0;
    background:
        repeating-linear-gradient(45deg, transparent, transparent 14px, rgba(255,255,255,0.012) 14px, rgba(255,255,255,0.012) 15px),
        linear-gradient(to top, rgba(0,0,0,0.85) 0%, rgba(0,0,0,0.2) 60%, transparent 100%);
}
.blog-card-num {
    position: absolute; top: 14px; left: 14px;
    z-index: 2;
    font-family: 'Oswald', Impact, sans-serif;
    font-weight: 600;
    font-size: 28px; letter-spacing: 0.04em;
    color: rgba(255,255,255,0.4);
    line-height: 1;
}
.blog-card-cat {
    position: absolute; bottom: 14px; left: 14px;
    z-index: 2;
    font-family: 'JetBrains Mono', 'SF Mono', monospace;
    font-size: 9.5px; letter-spacing: 0.18em;
    text-transform: uppercase;
    color: var(--blog-red-text);
    background: rgba(0,0,0,0.55);
    -webkit-backdrop-filter: blur(8px);
    backdrop-filter: blur(8px);
    padding: 5px 10px;
    border-radius: 999px;
    border: 1px solid rgba(220,38,38,0.18);
}
.blog-card-body {
    padding: 18px 18px 20px;
}
.blog-card-title {
    font-family: 'Oswald', Impact, sans-serif;
    font-weight: 500;
    font-size: 18px; line-height: 1.18;
    letter-spacing: 0.01em;
    text-transform: uppercase;
    color: var(--blog-text);
    margin-bottom: 8px;
    transition: color 0.2s;
}
.blog-card:hover .blog-card-title,
.blog-card-wide:hover .blog-card-wide-title {
    color: var(--blog-red-text);
}
.blog-card-excerpt {
    font-family: 'Raleway', system-ui, sans-serif;
    font-size: 14px;
    line-height: 1.55;
    color: var(--blog-text-2);
    margin-bottom: 14px;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-wrap: pretty;
}
.blog-card-meta {
    display: flex; align-items: center; gap: 10px;
    font-family: 'JetBrains Mono', 'SF Mono', monospace;
    font-size: 9.5px; letter-spacing: 0.14em;
    text-transform: uppercase;
    color: var(--blog-text-3);
    border-top: 1px solid var(--blog-border);
    padding-top: 10px;
}
.blog-card-meta-dot {
    width: 3px; height: 3px;
    background: var(--blog-red);
    border-radius: 50%;
}

/* Wide card variant */
.blog-card-wide-body {
    padding: 22px 22px 24px;
}
.blog-card-wide-eyebrow {
    display: flex; align-items: center; gap: 8px; flex-wrap: wrap;
    font-family: 'JetBrains Mono', 'SF Mono', monospace;
    font-size: 9.5px; letter-spacing: 0.16em;
    text-transform: uppercase;
    color: var(--blog-text-3);
    margin-bottom: 12px;
}
.blog-card-wide-eyebrow-tag {
    background: rgba(220,38,38,0.12);
    border: 1px solid rgba(220,38,38,0.25);
    color: var(--blog-red-text);
    padding: 3px 9px;
    border-radius: 999px;
    letter-spacing: 0.14em;
}
.blog-card-wide-title {
    font-family: 'Oswald', Impact, sans-serif;
    font-weight: 600;
    font-size: clamp(22px, 4vw, 32px);
    line-height: 1.05;
    letter-spacing: 0.01em;
    text-transform: uppercase;
    color: var(--blog-text);
    margin-bottom: 12px;
    transition: color 0.2s;
}
.blog-card-wide-excerpt {
    font-family: 'Raleway', system-ui, sans-serif;
    font-size: 15px;
    line-height: 1.58;
    color: var(--blog-text-2);
    max-width: 65ch;
    text-wrap: pretty;
}

/* ── EMPTY ────────────────────────────────────────────── */
.blog-empty {
    grid-column: 1 / -1;
    text-align: center;
    padding: 72px 24px;
    font-family: 'Fraunces', Georgia, serif;
    font-style: italic;
    font-size: 17px;
    color: var(--blog-gold);
}

/* ── NEWSLETTER ───────────────────────────────────────── */
.blog-newsletter {
    margin: 56px 0 0;
    padding: 32px 24px;
    max-width: 1200px;
    margin-left: auto; margin-right: auto;
    background:
        radial-gradient(ellipse 60% 80% at 100% 0%, rgba(220,38,38,0.12), transparent 60%),
        rgba(26,26,26,0.5);
    border: 1px solid var(--blog-border);
    border-radius: 14px;
}
.blog-newsletter-eyebrow {
    font-family: 'JetBrains Mono', 'SF Mono', monospace;
    font-size: 10px; letter-spacing: 0.25em;
    text-transform: uppercase;
    color: var(--blog-red); margin-bottom: 14px;
}
.blog-newsletter-headline {
    font-family: 'Oswald', Impact, sans-serif;
    font-weight: 600;
    font-size: clamp(36px, 8vw, 56px);
    line-height: 0.95;
    letter-spacing: 0.005em;
    text-transform: uppercase;
    color: var(--blog-text);
    margin-bottom: 14px;
}
.blog-newsletter-headline em {
    font-style: normal;
    color: var(--blog-red);
}
.blog-newsletter-sub {
    font-family: 'Raleway', system-ui, sans-serif;
    font-size: 15px;
    line-height: 1.55;
    color: var(--blog-text-2);
    margin-bottom: 22px;
    max-width: 56ch;
}
.blog-newsletter-form {
    display: flex; flex-direction: column; gap: 10px;
    max-width: 480px;
}
@media (min-width: 720px) {
    .blog-newsletter-form { flex-direction: row; }
}
.blog-newsletter-input {
    flex: 1;
    background: rgba(0,0,0,0.4);
    border: 1px solid var(--blog-border);
    border-radius: 8px;
    padding: 13px 14px;
    color: var(--blog-text);
    font-family: 'Raleway', system-ui, sans-serif;
    font-size: 14px;
    outline: none;
    transition: border-color 0.2s;
}
.blog-newsletter-input:focus { border-color: var(--blog-red); }
.blog-newsletter-input::placeholder { color: var(--blog-text-3); }
.blog-newsletter-btn {
    background: var(--blog-red);
    color: #fff;
    border: none;
    border-radius: 8px;
    padding: 13px 22px;
    font-family: 'Oswald', Impact, sans-serif;
    font-weight: 600;
    font-size: 13px; letter-spacing: 0.18em;
    text-transform: uppercase;
    cursor: pointer;
    transition: background 0.2s, transform 0.1s var(--blog-ease-out);
    -webkit-tap-highlight-color: transparent;
}
.blog-newsletter-btn:hover { background: #B91C1C; }
.blog-newsletter-btn:active { transform: scale(0.97); }
.blog-newsletter-disclaimer {
    margin-top: 14px;
    font-family: 'JetBrains Mono', 'SF Mono', monospace;
    font-size: 9.5px; letter-spacing: 0.18em;
    text-transform: uppercase;
    color: var(--blog-text-4);
}

/* ── CTA SUAVE ────────────────────────────────────────── */
.blog-cta-soft {
    display: flex; flex-direction: column;
    gap: 18px;
    margin: 32px 24px 64px;
    padding: 28px 24px;
    background: rgba(20,20,20,0.5);
    border: 1px solid var(--blog-border);
    border-radius: 12px;
    text-decoration: none;
    transition: border-color 0.3s var(--blog-ease-out), transform 0.3s var(--blog-ease-out);
    max-width: 1152px;
    margin-left: auto; margin-right: auto;
}
@media (min-width: 720px) {
    .blog-cta-soft {
        flex-direction: row;
        align-items: center; justify-content: space-between;
    }
}
.blog-cta-soft:hover {
    border-color: rgba(220,38,38,0.3);
    transform: translateY(-2px);
}
.blog-cta-soft-eyebrow {
    font-family: 'JetBrains Mono', 'SF Mono', monospace;
    font-size: 10px; letter-spacing: 0.25em;
    text-transform: uppercase;
    color: var(--blog-red); margin-bottom: 6px;
}
.blog-cta-soft-title {
    font-family: 'Oswald', Impact, sans-serif;
    font-weight: 600;
    font-size: 22px; letter-spacing: 0.02em;
    text-transform: uppercase;
    color: var(--blog-text);
    margin-bottom: 6px;
    line-height: 1.1;
}
.blog-cta-soft-sub {
    font-family: 'Raleway', system-ui, sans-serif;
    font-size: 14px;
    color: var(--blog-text-2);
    line-height: 1.5;
}
.blog-cta-soft-link {
    font-family: 'Oswald', Impact, sans-serif;
    font-weight: 600;
    font-size: 13px; letter-spacing: 0.18em;
    text-transform: uppercase;
    color: var(--blog-red-text);
    white-space: nowrap;
    transition: letter-spacing 0.2s var(--blog-ease-out);
}
.blog-cta-soft:hover .blog-cta-soft-link { letter-spacing: 0.22em; }

/* ── DESKTOP TUNING ───────────────────────────────────── */
@media (min-width: 1024px) {
    .blog-hero { padding: 80px 24px 40px; max-width: none; padding-left: 32px; padding-right: 32px; }
    .blog-featured { padding: 0 32px; }
    .blog-grid { padding: 28px 32px 0; }
    .blog-newsletter { margin: 64px 32px 0; padding: 48px 40px; }
    .blog-cta-soft { margin: 32px 32px 72px; padding: 32px 40px; }
}

/* Reduced motion */
@media (prefers-reduced-motion: reduce) {
    .blog-page-root *, .blog-page-root *::before, .blog-page-root *::after {
        animation-duration: 0.01ms !important;
        transition-duration: 0.01ms !important;
    }
}
</style>

<div class="blog-shell">

    {{-- ── HERO ── --}}
    <section class="blog-hero">
        <div class="blog-hero-eyebrow">
            <span>{{ __('blog.hero_eyebrow') }}</span>
        </div>
        <h1 class="blog-hero-title">
            {{ __('blog.hero_heading') }}<br>
            <em>{{ __('blog.hero_heading_accent') }}</em>
        </h1>
        <p class="blog-hero-sub">{{ __('blog.hero_description') }}</p>
    </section>

    {{-- ── FEATURED (only when not filtering) ── --}}
    @if ($featured)
        <section class="blog-featured" x-show="!isFiltering">
            <a href="{{ route('blog.show', $featured['slug']) }}" class="blog-featured-card">
                <div class="blog-featured-bg">
                    <div class="blog-featured-issue" aria-hidden="true">
                        <span class="blog-featured-issue-num">N° 01</span>
                        <span>{{ \Illuminate\Support\Str::upper($featured['category']) }} · {{ \Illuminate\Support\Str::upper(\Carbon\Carbon::parse($featured['date'])->translatedFormat('M Y')) }}</span>
                    </div>
                    <div class="blog-featured-content">
                        <div class="blog-featured-eyebrow">
                            <span class="blog-featured-eyebrow-tag">{{ $featured['category'] }}</span>
                            <span>·</span>
                            <span>{{ __('blog.featured_eyebrow') }}</span>
                        </div>
                        <h2 class="blog-featured-title">{{ $featured['title'] }}</h2>
                        <p class="blog-featured-excerpt">{{ $featured['excerpt'] }}</p>
                        <div class="blog-featured-meta">
                            <span>{{ \Illuminate\Support\Str::upper(\Carbon\Carbon::parse($featured['date'])->translatedFormat('d M Y')) }}</span>
                            <span class="blog-featured-meta-dot"></span>
                            <span>{{ \Illuminate\Support\Str::upper($featured['author']) }}</span>
                            <span class="blog-featured-meta-dot"></span>
                            <span>{{ $featured['reading_time'] }} {{ $isEs ? 'LECTURA' : 'READ' }}</span>
                        </div>
                        <span class="blog-featured-cta">{{ __('blog.featured_cta') }} →</span>
                    </div>
                </div>
            </a>
        </section>
    @endif

</div>

{{-- ── TABS CATEGORIES ── --}}
<div class="blog-tabs-wrap">
    <div class="blog-tabs" role="tablist">
        <button type="button" class="blog-tab" :class="{ 'is-active': category === 'all' }" @click="category = 'all'" role="tab">
            {{ __('blog.filter_all') }}<span class="blog-tab-count">{{ str_pad((string) count($articles), 2, '0', STR_PAD_LEFT) }}</span>
        </button>
        @foreach ($catCounts as $catName => $count)
            <button type="button" class="blog-tab"
                :class="{ 'is-active': category === '{{ addslashes($catName) }}' }"
                @click="category = '{{ addslashes($catName) }}'"
                role="tab">
                {{ $catName }}<span class="blog-tab-count">{{ str_pad((string) $count, 2, '0', STR_PAD_LEFT) }}</span>
            </button>
        @endforeach
        <div class="blog-tab-search">
            <input
                type="search"
                x-model="search"
                placeholder="{{ __('blog.search_placeholder') }}"
                aria-label="{{ __('blog.search_placeholder') }}"
                autocomplete="off"
                spellcheck="false"
            >
        </div>
    </div>
</div>

<div class="blog-shell">

    {{-- ── DIVIDER ── --}}
    <div class="blog-divider" aria-hidden="true">
        <div class="blog-divider-line"></div>
        <span>{{ __('blog.divider_latest') }}</span>
        <div class="blog-divider-line"></div>
    </div>

    {{-- ── GRID ── --}}
    <div class="blog-grid">
        @foreach ($rest as $idx => $article)
            @php
                $isWide = (($idx + 1) % 4 === 0); // every 4th card → wide hero
                $tone = match (\Illuminate\Support\Str::lower($article['category'])) {
                    'entrenamiento', 'training' => 'radial-gradient(ellipse 70% 50% at 30% 30%, rgba(220,38,38,0.18), transparent 60%)',
                    'nutricion', 'nutrition'    => 'radial-gradient(ellipse 70% 50% at 30% 30%, rgba(16,185,129,0.16), transparent 60%)',
                    'recuperacion', 'recovery'  => 'radial-gradient(ellipse 70% 50% at 30% 30%, rgba(56,135,255,0.18), transparent 60%)',
                    'mindset'                   => 'radial-gradient(ellipse 70% 50% at 30% 30%, rgba(244,114,182,0.16), transparent 60%)',
                    'ciencia', 'science'        => 'radial-gradient(ellipse 70% 50% at 30% 30%, rgba(212,160,76,0.18), transparent 60%)',
                    default                     => 'radial-gradient(ellipse 70% 50% at 30% 30%, rgba(220,38,38,0.15), transparent 60%)',
                };
                $titleSlug = strtolower(strip_tags($article['title']));
            @endphp
            <a href="{{ route('blog.show', $article['slug']) }}"
               class="{{ $isWide ? 'blog-card-wide' : 'blog-card' }}"
               x-show="matchesCategory({{ json_encode($article['category']) }}) && matchesSearch({{ json_encode($titleSlug) }})">
                <div class="blog-card-img" style="--card-tone: {{ $tone }};">
                    <span class="blog-card-num">{{ str_pad((string) ($idx + 2), 2, '0', STR_PAD_LEFT) }}</span>
                    <span class="blog-card-cat">{{ $article['category'] }}</span>
                </div>
                @if ($isWide)
                    <div class="blog-card-wide-body">
                        <div class="blog-card-wide-eyebrow">
                            <span class="blog-card-wide-eyebrow-tag">{{ $article['category'] }}</span>
                            <span>·</span>
                            <span>{{ \Illuminate\Support\Str::upper(\Carbon\Carbon::parse($article['date'])->translatedFormat('d M Y')) }}</span>
                            <span>·</span>
                            <span>{{ \Illuminate\Support\Str::upper($article['reading_time']) }}</span>
                        </div>
                        <h3 class="blog-card-wide-title">{{ $article['title'] }}</h3>
                        <p class="blog-card-wide-excerpt">{{ $article['excerpt'] }}</p>
                    </div>
                @else
                    <div class="blog-card-body">
                        <h3 class="blog-card-title">{{ $article['title'] }}</h3>
                        <p class="blog-card-excerpt">{{ $article['excerpt'] }}</p>
                        <div class="blog-card-meta">
                            <span>{{ \Illuminate\Support\Str::upper(\Carbon\Carbon::parse($article['date'])->translatedFormat('d M Y')) }}</span>
                            <span class="blog-card-meta-dot"></span>
                            <span>{{ \Illuminate\Support\Str::upper($article['reading_time']) }}</span>
                        </div>
                    </div>
                @endif
            </a>
        @endforeach
    </div>

    {{-- ── NEWSLETTER ── --}}
    <section class="blog-newsletter">
        <div class="blog-newsletter-eyebrow">{{ __('blog.newsletter_eyebrow') }}</div>
        <h2 class="blog-newsletter-headline">
            {{ __('blog.newsletter_headline') }} <em>{{ __('blog.newsletter_headline_accent') }}</em><br>
            {{ __('blog.newsletter_headline_tail') }}
        </h2>
        <p class="blog-newsletter-sub">{{ __('blog.newsletter_sub') }}</p>
        {{-- Form sin POST handler — sólo visual; backend pendiente. --}}
        <form class="blog-newsletter-form" id="blog-newsletter-form"
              data-pending-msg="{{ $isEs ? 'Próximamente — estamos preparando el sistema de suscripción.' : 'Coming soon — newsletter system in setup.' }}">
            <input type="email" class="blog-newsletter-input" placeholder="{{ __('blog.newsletter_placeholder') }}" aria-label="Email" required>
            <button type="submit" class="blog-newsletter-btn">{{ __('blog.newsletter_btn') }}</button>
        </form>
        <script nonce="@cspNonce">
            (function () {
                var form = document.getElementById('blog-newsletter-form');
                if (!form) return;
                form.addEventListener('submit', function (e) {
                    e.preventDefault();
                    alert(form.getAttribute('data-pending-msg') || '');
                });
            })();
        </script>
        <div class="blog-newsletter-disclaimer">{{ __('blog.newsletter_disclaimer') }}</div>
    </section>

    {{-- ── CTA SOFT ── --}}
    <a href="{{ route('metodo') }}" class="blog-cta-soft">
        <div>
            <div class="blog-cta-soft-eyebrow">{{ __('blog.cta_soft_eyebrow') }}</div>
            <div class="blog-cta-soft-title">{{ __('blog.cta_soft_title') }}</div>
            <div class="blog-cta-soft-sub">{{ __('blog.cta_soft_sub') }}</div>
        </div>
        <span class="blog-cta-soft-link">{{ __('blog.cta_soft_link') }} →</span>
    </a>

</div>

{{-- ── JSON-LD Blog ── --}}
<script type="application/ld+json">
{!! json_encode($blogSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>

</div>{{-- .blog-page-root --}}
</x-layouts.public>
