@php
    use Illuminate\Support\Str;

    $isEs = app()->getLocale() === 'es';

    // Inject IDs into h2/h3 headings of $article['content'] for TOC anchors,
    // then build a flat TOC list (level + label + id) preserving order.
    $tocItems = [];
    $bodyHtml = preg_replace_callback(
        '/<h([23])(\b[^>]*)>(.+?)<\/h\1>/is',
        function ($m) use (&$tocItems) {
            $level = (int) $m[1];
            $attrs = $m[2];
            $inner = $m[3];
            $label = trim(strip_tags($inner));
            $slug  = \Illuminate\Support\Str::slug($label);
            // Avoid duplicate ids: append index if already taken.
            $existing = array_column($tocItems, 'id');
            if ($slug === '' || in_array($slug, $existing, true)) {
                $slug = $slug !== '' ? $slug . '-' . (count($existing) + 1) : 'sec-' . (count($existing) + 1);
            }
            $tocItems[] = ['level' => $level, 'label' => $label, 'id' => $slug];
            // If id attribute already present, leave content alone.
            if (preg_match('/\bid\s*=/i', $attrs)) {
                return $m[0];
            }
            return '<h' . $level . ' id="' . $slug . '"' . $attrs . '>' . $inner . '</h' . $level . '>';
        },
        $article['content']
    );

    // Add drop-cap class to first <p>.
    $bodyHtml = preg_replace('/<p\b/', '<p class="show-dropcap-p"', $bodyHtml, 1);

    $relatedArticles = collect($articles)
        ->filter(fn ($a) => $a['slug'] !== $article['slug'])
        ->shuffle()
        ->take(3)
        ->values()
        ->all();

    // Tone gradient by category.
    $tone = match (\Illuminate\Support\Str::lower($article['category'])) {
        'entrenamiento', 'training' => 'radial-gradient(ellipse 70% 50% at 30% 30%, rgba(220,38,38,0.22), transparent 60%)',
        'nutricion', 'nutrition'    => 'radial-gradient(ellipse 70% 50% at 30% 30%, rgba(16,185,129,0.20), transparent 60%)',
        'recuperacion', 'recovery'  => 'radial-gradient(ellipse 70% 50% at 30% 30%, rgba(56,135,255,0.22), transparent 60%)',
        'mindset'                   => 'radial-gradient(ellipse 70% 50% at 30% 30%, rgba(244,114,182,0.20), transparent 60%)',
        'ciencia', 'science'        => 'radial-gradient(ellipse 70% 50% at 30% 30%, rgba(212,160,76,0.22), transparent 60%)',
        default                     => 'radial-gradient(ellipse 70% 50% at 30% 30%, rgba(220,38,38,0.20), transparent 60%)',
    };

    // JSON-LD BlogPosting.
    $articleSchema = [
        '@context'      => 'https://schema.org',
        '@type'         => 'BlogPosting',
        'headline'      => $article['title'],
        'description'   => $article['excerpt'],
        'datePublished' => $article['date'],
        'author'        => ['@type' => 'Organization', 'name' => $article['author']],
        'publisher'     => [
            '@type' => 'Organization',
            'name'  => 'WellCore Fitness',
            'logo'  => [
                '@type' => 'ImageObject',
                'url'   => url('/images/wellcore-logo.svg'),
            ],
        ],
        'mainEntityOfPage' => [
            '@type' => 'WebPage',
            '@id'   => route('blog.show', $article['slug']),
        ],
        'articleSection' => $article['category'],
    ];
@endphp

<x-layouts.public bodyClass="blog-show-page">
    <x-slot:title>{{ $article['title'] }} — WellCore Fitness</x-slot:title>
    <x-slot:description>{{ $article['excerpt'] }}</x-slot:description>

<div class="blog-show-root" x-data="{ progress: 0 }">

<style>
.blog-show-root {
    --bs-bg:        #0a0a0a;
    --bs-bg-2:      #111111;
    --bs-bg-3:      #1a1a1a;
    --bs-bg-4:      #222222;
    --bs-text:      #FAFAFA;
    --bs-text-2:    #C7C7C7;
    --bs-text-3:    #8A8A8A;
    --bs-text-4:    #525252;
    --bs-border:    rgba(255,255,255,0.07);
    --bs-red:       #DC2626;
    --bs-red-text:  #F87171;
    --bs-gold:      #D4A04C;
    --bs-blue:      #60a5fa;
    --bs-wa:        #25D366;
    --bs-ease:      cubic-bezier(.22,1,.36,1);

    position: relative;
    background: var(--bs-bg);
    color: var(--bs-text);
    min-height: 100vh;
    isolation: isolate;
}

/* Reading progress */
.bs-progress {
    position: fixed; top: 0; left: 0;
    height: 2px;
    background: var(--bs-red);
    z-index: 999;
    transition: width 0.1s linear;
    pointer-events: none;
}

/* ── HERO ─────────────────────────────────────────────── */
.bs-hero {
    position: relative;
    min-height: 460px;
    background: var(--bs-bg-3);
    overflow: hidden;
    display: flex;
}
.bs-hero-bg {
    position: absolute; inset: 0;
    background:
        repeating-linear-gradient(45deg, transparent, transparent 22px, rgba(255,255,255,0.012) 22px, rgba(255,255,255,0.012) 23px),
        var(--hero-tone, radial-gradient(ellipse 60% 70% at 70% 30%, rgba(220,38,38,0.16), transparent 60%)),
        linear-gradient(160deg, #1c1010, #0a0a0a 70%);
}
.bs-hero-overlay {
    position: absolute; inset: 0; z-index: 2;
    background:
        linear-gradient(to top, rgba(0,0,0,0.97) 0%, rgba(0,0,0,0.6) 38%, rgba(0,0,0,0.15) 70%, transparent 100%),
        linear-gradient(to right, rgba(0,0,0,0.5), transparent 55%);
}
.bs-hero-content {
    position: relative; z-index: 3;
    max-width: 1200px; margin: 0 auto; width: 100%;
    display: flex; flex-direction: column; justify-content: flex-end;
    padding: 80px 24px 36px;
    flex: 1;
}
.bs-hero-issue {
    position: absolute; top: 22px; left: 24px; z-index: 4;
    font-family: 'JetBrains Mono', 'SF Mono', monospace;
    font-size: 9.5px; letter-spacing: 0.22em;
    color: rgba(255,255,255,0.4); text-transform: uppercase;
    display: flex; align-items: center; gap: 12px;
}
.bs-hero-issue-num {
    font-family: 'Oswald', Impact, sans-serif;
    font-weight: 600;
    font-size: 13px; color: var(--bs-red); letter-spacing: 0.08em;
}
.bs-hero-back {
    position: absolute; top: 22px; right: 24px; z-index: 4;
    font-family: 'JetBrains Mono', 'SF Mono', monospace;
    font-size: 10px; letter-spacing: 0.22em;
    color: rgba(255,255,255,0.6); text-transform: uppercase;
    display: inline-flex; align-items: center; gap: 8px;
    padding: 8px 12px;
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 8px;
    text-decoration: none;
    transition: color 0.2s, border-color 0.2s;
}
.bs-hero-back:hover { color: #fff; border-color: rgba(255,255,255,0.3); }
.bs-hero-category {
    font-family: 'Fraunces', Georgia, serif;
    font-style: italic;
    font-size: 14px;
    color: var(--bs-gold);
    margin-bottom: 14px;
    display: flex; align-items: center; gap: 12px;
}
.bs-hero-category::after {
    content: ''; flex: 1; max-width: 60px; height: 1px;
    background: linear-gradient(to right, rgba(212,160,76,0.4), transparent);
}
.bs-hero-title {
    font-family: 'Oswald', Impact, sans-serif;
    font-weight: 600;
    font-size: clamp(48px, 12vw, 96px);
    line-height: 0.92; letter-spacing: 0.005em;
    text-transform: uppercase;
    color: var(--bs-text);
    max-width: 900px;
    margin-bottom: 18px;
    text-wrap: balance;
}
.bs-hero-meta {
    font-family: 'JetBrains Mono', 'SF Mono', monospace;
    font-size: 10px; color: rgba(255,255,255,0.5);
    letter-spacing: 0.16em; text-transform: uppercase;
    display: flex; flex-wrap: wrap; gap: 10px; align-items: center;
}
.bs-hero-meta-dot {
    width: 3px; height: 3px;
    background: var(--bs-red); border-radius: 50%;
}
@media (min-width: 1024px) {
    .bs-hero { min-height: 600px; }
    .bs-hero-content { padding: 120px 32px 60px; }
    .bs-hero-issue { top: 32px; left: 32px; }
    .bs-hero-back { top: 32px; right: 32px; }
}

/* ── SHARE BAR (mobile) ───────────────────────────────── */
.bs-share {
    background: var(--bs-bg-2);
    border-bottom: 1px solid var(--bs-border);
    padding: 12px 24px;
}
.bs-share-inner {
    max-width: 1200px; margin: 0 auto;
    display: flex; align-items: center; gap: 10px;
    overflow-x: auto;
    scrollbar-width: none;
}
.bs-share-inner::-webkit-scrollbar { display: none; }
.bs-share-label {
    font-family: 'JetBrains Mono', 'SF Mono', monospace;
    font-size: 9.5px; color: var(--bs-text-3);
    letter-spacing: 0.22em; text-transform: uppercase;
    white-space: nowrap; flex-shrink: 0;
}
.bs-share-btn {
    flex-shrink: 0;
    font-family: 'JetBrains Mono', 'SF Mono', monospace;
    font-size: 10px; letter-spacing: 0.1em;
    text-transform: uppercase;
    padding: 7px 14px; border-radius: 6px;
    cursor: pointer; border: 1px solid;
    background: none;
    transition: all 0.2s var(--bs-ease);
    white-space: nowrap;
    -webkit-tap-highlight-color: transparent;
}
.bs-share-wa {
    background: rgba(37,211,102,0.1);
    border-color: rgba(37,211,102,0.3);
    color: var(--bs-wa);
}
.bs-share-wa:hover { background: rgba(37,211,102,0.2); }
.bs-share-tw {
    background: rgba(29,161,242,0.08);
    border-color: rgba(29,161,242,0.22);
    color: var(--bs-blue);
}
.bs-share-tw:hover { background: rgba(29,161,242,0.18); }
.bs-share-cp {
    background: rgba(255,255,255,0.04);
    border-color: var(--bs-border);
    color: var(--bs-text-3);
}
.bs-share-cp:hover { color: var(--bs-text-2); }

/* ── ARTICLE LAYOUT ───────────────────────────────────── */
.bs-layout {
    position: relative;
    max-width: 1200px;
    margin: 0 auto;
    padding: 56px 24px 0;
}
@media (min-width: 1024px) {
    .bs-layout {
        padding: 72px 32px 0;
        display: grid;
        grid-template-columns: 1fr 280px;
        gap: 72px;
        align-items: start;
    }
}

/* ── BODY (article) ───────────────────────────────────── */
.bs-body {
    max-width: 720px;
}
.bs-body p {
    font-family: 'Raleway', system-ui, sans-serif;
    font-size: 16.5px;
    line-height: 1.78;
    color: var(--bs-text-2);
    margin-bottom: 26px;
    text-wrap: pretty;
}
.bs-body h2 {
    font-family: 'Oswald', Impact, sans-serif;
    font-weight: 600;
    font-size: clamp(32px, 5vw, 44px);
    line-height: 1; letter-spacing: 0.02em;
    text-transform: uppercase;
    color: var(--bs-text);
    margin: 56px 0 22px;
    scroll-margin-top: 80px;
}
.bs-body h3 {
    font-family: 'Oswald', Impact, sans-serif;
    font-weight: 500;
    font-size: 24px; letter-spacing: 0.04em;
    text-transform: uppercase;
    color: var(--bs-text);
    margin: 40px 0 16px;
    line-height: 1.1;
    scroll-margin-top: 80px;
}
.bs-body strong { color: var(--bs-text); font-weight: 600; }
.bs-body em {
    font-family: 'Fraunces', Georgia, serif;
    font-style: italic;
    color: var(--bs-gold);
    font-weight: 500;
}
.bs-body a {
    color: var(--bs-text);
    text-decoration: underline;
    text-decoration-color: var(--bs-red);
    text-underline-offset: 3px;
    transition: color 0.2s;
}
.bs-body a:hover { color: var(--bs-red-text); }
.bs-body ul {
    list-style: none;
    padding: 0;
    margin: 0 0 26px;
    display: flex;
    flex-direction: column;
    gap: 10px;
}
.bs-body ul li {
    background: rgba(26,26,26,0.5);
    border: 1px solid var(--bs-border);
    border-left: 2px solid rgba(220,38,38,0.4);
    border-radius: 6px;
    padding: 14px 18px;
    font-family: 'Raleway', system-ui, sans-serif;
    font-size: 15px;
    line-height: 1.6;
    color: var(--bs-text-2);
}
.bs-body ul li strong {
    color: var(--bs-text);
}

/* Drop cap (first paragraph only) */
.bs-body .show-dropcap-p::first-letter {
    font-family: 'Oswald', Impact, sans-serif;
    font-weight: 600;
    font-size: 5.6rem;
    line-height: 0.82;
    float: left;
    margin: 0.18em 0.16em 0 0;
    color: var(--bs-red);
    text-shadow: 0 0 48px rgba(220,38,38,0.3);
}

/* Editorial quote candidates: any blockquote in content */
.bs-body blockquote {
    font-family: 'Fraunces', Georgia, serif;
    font-style: italic;
    font-size: 19px;
    line-height: 1.55;
    color: var(--bs-gold);
    border-left: 1px solid rgba(212,160,76,0.32);
    padding: 14px 0 14px 22px;
    margin: 32px 0;
    max-width: 56ch;
}

/* ── DIVIDER inline ───────────────────────────────────── */
.bs-divider {
    display: flex; align-items: center; gap: 14px;
    margin: 48px 0 32px;
    font-family: 'Oswald', Impact, sans-serif;
    font-size: 11px; letter-spacing: 0.32em;
    color: var(--bs-text-3); text-transform: uppercase;
    font-weight: 500;
}
.bs-divider-line { flex: 1; height: 1px; background: var(--bs-border); }

/* ── AUTHOR BIO ───────────────────────────────────────── */
.bs-author {
    margin: 48px 0 0;
    padding: 24px;
    background: rgba(26,26,26,0.5);
    border: 1px solid var(--bs-border);
    border-radius: 12px;
    display: flex; gap: 18px; align-items: flex-start;
}
.bs-author-avatar {
    width: 60px; height: 60px;
    border-radius: 50%;
    flex-shrink: 0;
    background:
        radial-gradient(circle at 35% 35%, rgba(220,38,38,0.2), transparent 55%),
        var(--bs-bg-4);
    border: 1px solid var(--bs-border);
    display: flex; align-items: center; justify-content: center;
    font-family: 'Oswald', Impact, sans-serif;
    font-weight: 600;
    font-size: 22px; color: var(--bs-text-3);
    letter-spacing: 0.02em;
}
.bs-author-name {
    font-family: 'Oswald', Impact, sans-serif;
    font-weight: 600;
    font-size: 22px; letter-spacing: 0.04em;
    text-transform: uppercase;
    color: var(--bs-text);
    line-height: 1; margin-bottom: 4px;
}
.bs-author-role {
    font-family: 'JetBrains Mono', 'SF Mono', monospace;
    font-size: 9.5px; color: var(--bs-red);
    letter-spacing: 0.16em; text-transform: uppercase;
    margin-bottom: 10px;
}
.bs-author-bio {
    font-family: 'Raleway', system-ui, sans-serif;
    font-size: 13.5px; line-height: 1.6;
    color: var(--bs-text-2); margin-bottom: 8px;
}
.bs-author-phrase {
    font-family: 'Fraunces', Georgia, serif;
    font-style: italic;
    font-size: 13.5px;
    color: var(--bs-gold);
}

/* ── CTA CONTEXTUAL ───────────────────────────────────── */
.bs-cta {
    margin: 40px 0 56px;
    padding: 28px 24px;
    background:
        radial-gradient(ellipse at 100% 0%, rgba(220,38,38,0.14), transparent 60%),
        rgba(26,26,26,0.5);
    border: 1px solid rgba(220,38,38,0.22);
    border-radius: 12px;
    position: relative; overflow: hidden;
}
.bs-cta-eyebrow {
    font-family: 'JetBrains Mono', 'SF Mono', monospace;
    font-size: 10px; letter-spacing: 0.24em;
    color: var(--bs-red); text-transform: uppercase;
    margin-bottom: 12px;
    display: flex; align-items: center; gap: 10px;
}
.bs-cta-eyebrow::before {
    content: ''; width: 18px; height: 1px; background: var(--bs-red);
}
.bs-cta-title {
    font-family: 'Oswald', Impact, sans-serif;
    font-weight: 600;
    font-size: clamp(28px, 5vw, 38px);
    line-height: 1.05;
    letter-spacing: 0.01em;
    text-transform: uppercase;
    color: var(--bs-text);
    margin-bottom: 10px;
}
.bs-cta-sub {
    font-family: 'Fraunces', Georgia, serif;
    font-style: italic;
    font-size: 15px;
    line-height: 1.5;
    color: var(--bs-gold);
    margin-bottom: 22px;
    max-width: 56ch;
}
.bs-cta-btn {
    display: inline-flex; align-items: center; gap: 10px;
    background: var(--bs-red);
    color: #fff;
    border-radius: 999px;
    padding: 14px 24px;
    font-family: 'Oswald', Impact, sans-serif;
    font-weight: 600;
    font-size: 14px; letter-spacing: 0.18em;
    text-transform: uppercase;
    text-decoration: none;
    transition: background 0.2s, letter-spacing 0.2s var(--bs-ease), transform 0.1s var(--bs-ease);
    box-shadow: 0 12px 28px -10px rgba(220,38,38,0.5);
    -webkit-tap-highlight-color: transparent;
}
.bs-cta-btn:hover { background: #B91C1C; letter-spacing: 0.22em; }
.bs-cta-btn:active { transform: scale(0.98); }

/* ── TOC SIDEBAR (desktop only) ───────────────────────── */
.bs-toc { display: none; }
@media (min-width: 1024px) {
    .bs-toc {
        display: block;
        position: sticky; top: 96px;
    }
}
.bs-toc-inner {
    background: rgba(20,20,20,0.5);
    border: 1px solid var(--bs-border);
    border-radius: 12px;
    padding: 22px;
}
.bs-toc-title {
    font-family: 'Oswald', Impact, sans-serif;
    font-weight: 600;
    font-size: 13px; letter-spacing: 0.24em;
    text-transform: uppercase;
    color: var(--bs-text); margin-bottom: 18px;
    display: flex; align-items: center; gap: 10px;
}
.bs-toc-title::before {
    content: ''; width: 4px; height: 14px;
    background: var(--bs-red);
}
.bs-toc-list {
    display: flex; flex-direction: column; gap: 2px;
    list-style: none; padding: 0; margin: 0;
}
.bs-toc-link {
    display: flex; align-items: flex-start; gap: 10px;
    padding: 10px 0;
    font-family: 'Raleway', system-ui, sans-serif;
    font-size: 13px;
    color: var(--bs-text-3);
    border-bottom: 1px solid var(--bs-border);
    transition: color 0.2s var(--bs-ease);
    line-height: 1.4;
    text-decoration: none;
}
.bs-toc-link:hover { color: var(--bs-text-2); }
.bs-toc-link.is-active { color: var(--bs-text); }
.bs-toc-link.is-active .bs-toc-num { color: var(--bs-red); }
.bs-toc-num {
    font-family: 'JetBrains Mono', 'SF Mono', monospace;
    font-size: 9.5px; color: var(--bs-text-3);
    letter-spacing: 0.1em; flex-shrink: 0;
    margin-top: 1px;
}
.bs-toc-list li:last-child .bs-toc-link { border-bottom: none; }

.bs-toc-h3 { padding-left: 18px; }

.bs-toc-meta {
    margin-top: 20px;
    padding-top: 16px;
    border-top: 1px solid var(--bs-border);
}
.bs-toc-meta-row {
    display: flex; justify-content: space-between;
    font-family: 'JetBrains Mono', 'SF Mono', monospace;
    font-size: 9.5px; color: var(--bs-text-3);
    letter-spacing: 0.1em; padding: 5px 0;
    text-transform: uppercase;
}
.bs-toc-meta-val { color: var(--bs-text-2); }

.bs-toc-progress {
    margin-top: 18px;
    display: flex; align-items: center; gap: 14px;
}
.bs-toc-ring { width: 40px; height: 40px; flex-shrink: 0; }
.bs-toc-ring-bg { fill: none; stroke: rgba(255,255,255,0.06); stroke-width: 3; }
.bs-toc-ring-fill {
    fill: none; stroke: var(--bs-red); stroke-width: 3;
    stroke-linecap: round;
    stroke-dasharray: 100.5;
    stroke-dashoffset: 100.5;
    transform: rotate(-90deg); transform-origin: center;
    transition: stroke-dashoffset 0.3s var(--bs-ease);
}
.bs-toc-ring-label {
    font-family: 'JetBrains Mono', 'SF Mono', monospace;
    font-size: 9px; letter-spacing: 0.1em;
    text-transform: uppercase;
    color: var(--bs-text-3);
}

/* ── RELATED ──────────────────────────────────────────── */
.bs-related {
    padding: 0 24px 56px;
    max-width: 1200px; margin: 0 auto;
}
.bs-related-divider {
    display: flex; align-items: center; gap: 14px;
    padding: 0 0 20px;
    font-family: 'Oswald', Impact, sans-serif;
    font-size: 10px; letter-spacing: 0.32em;
    color: var(--bs-text-3); opacity: 0.6;
    text-transform: uppercase;
    font-weight: 500;
}
.bs-related-divider-line { flex: 1; height: 1px; background: var(--bs-border); }
.bs-related-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 16px;
}
@media (min-width: 720px) {
    .bs-related-grid {
        grid-template-columns: repeat(3, 1fr);
        gap: 20px;
    }
}
.bs-related-card {
    position: relative;
    display: block;
    text-decoration: none;
    overflow: hidden;
    border-radius: 12px;
    border: 1px solid var(--bs-border);
    background: var(--bs-bg-3);
    transition: border-color 0.3s var(--bs-ease), transform 0.3s var(--bs-ease);
}
.bs-related-card:hover {
    border-color: rgba(220,38,38,0.3);
    transform: translateY(-3px);
}
.bs-related-img {
    position: relative;
    aspect-ratio: 4/3;
    background: linear-gradient(160deg, #1a1a1a, #0a0a0a);
}
.bs-related-img::before {
    content: '';
    position: absolute; inset: 0;
    background: var(--card-tone, radial-gradient(ellipse 70% 50% at 35% 35%, rgba(220,38,38,0.14), transparent 60%));
}
.bs-related-img::after {
    content: '';
    position: absolute; inset: 0;
    background:
        repeating-linear-gradient(45deg, transparent, transparent 14px, rgba(255,255,255,0.012) 14px, rgba(255,255,255,0.012) 15px),
        linear-gradient(to top, rgba(0,0,0,0.85), transparent 60%);
}
.bs-related-num {
    position: absolute; top: 12px; left: 12px; z-index: 2;
    font-family: 'Oswald', Impact, sans-serif;
    font-weight: 600;
    font-size: 24px; color: rgba(255,255,255,0.4);
    line-height: 1;
}
.bs-related-cat {
    position: absolute; bottom: 12px; left: 12px; z-index: 2;
    font-family: 'JetBrains Mono', 'SF Mono', monospace;
    font-size: 9px; letter-spacing: 0.18em;
    text-transform: uppercase;
    color: var(--bs-red-text);
    background: rgba(0,0,0,0.55);
    -webkit-backdrop-filter: blur(6px);
    backdrop-filter: blur(6px);
    padding: 4px 9px;
    border-radius: 999px;
    border: 1px solid rgba(220,38,38,0.18);
}
.bs-related-body { padding: 16px 16px 18px; }
.bs-related-title {
    font-family: 'Oswald', Impact, sans-serif;
    font-weight: 500;
    font-size: 16px;
    line-height: 1.18;
    letter-spacing: 0.01em;
    text-transform: uppercase;
    color: var(--bs-text);
    margin-bottom: 6px;
    transition: color 0.2s;
}
.bs-related-card:hover .bs-related-title { color: var(--bs-red-text); }
.bs-related-excerpt {
    font-family: 'Raleway', system-ui, sans-serif;
    font-size: 13.5px; line-height: 1.5;
    color: var(--bs-text-2);
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.bs-related-meta {
    display: flex; align-items: center; gap: 10px;
    font-family: 'JetBrains Mono', 'SF Mono', monospace;
    font-size: 9px; letter-spacing: 0.14em;
    text-transform: uppercase;
    color: var(--bs-text-3);
    border-top: 1px solid var(--bs-border);
    padding-top: 10px;
    margin-top: 10px;
}
.bs-related-meta-dot {
    width: 3px; height: 3px;
    background: var(--bs-red); border-radius: 50%;
}

@media (min-width: 1024px) {
    .bs-related { padding: 0 32px 72px; }
}

@media (prefers-reduced-motion: reduce) {
    .blog-show-root *, .blog-show-root *::before, .blog-show-root *::after {
        animation-duration: 0.01ms !important;
        transition-duration: 0.01ms !important;
    }
}

/* ════════════════════════════════════════════════════════
   LIGHT THEME
   ════════════════════════════════════════════════════════ */
html:not(.dark) .blog-show-root {
    --bs-bg:        #FAFAFA;
    --bs-bg-2:      #F4F4F5;
    --bs-bg-3:      #ECECEC;
    --bs-bg-4:      #DDDDDD;
    --bs-text:      #0A0A0A;
    --bs-text-2:    #2D2D2D;
    --bs-text-3:    #6B6B6B;
    --bs-text-4:    #A3A3A3;
    --bs-border:    rgba(0,0,0,0.08);
    --bs-gold:      #B5852A;
}
/* Hero — keep cinematic dark even in light mode
   (it's a full-bleed editorial banner, not a content area).
   Title white text on dark hero stays brutal. */
html:not(.dark) .bs-hero {
    background: var(--bs-bg-3);
    /* Dark hero bg overridden via inline tone; preserve dark feel */
}
html:not(.dark) .bs-hero-bg {
    /* Keep the dark editorial overlay even in light mode */
    background:
        repeating-linear-gradient(45deg, transparent, transparent 22px, rgba(255,255,255,0.012) 22px, rgba(255,255,255,0.012) 23px),
        var(--hero-tone, radial-gradient(ellipse 60% 70% at 70% 30%, rgba(220,38,38,0.16), transparent 60%)),
        linear-gradient(160deg, #1c1010, #0a0a0a 70%);
}
/* Body text adapts to light */
html:not(.dark) .bs-body p {
    color: #2D2D2D;
}
html:not(.dark) .bs-body ul li {
    background: rgba(0,0,0,0.025);
    border-color: rgba(0,0,0,0.08);
    color: #2D2D2D;
}
html:not(.dark) .bs-body ul li strong {
    color: #0A0A0A;
}
/* Drop cap — slightly less dramatic shadow on white */
html:not(.dark) .bs-body .show-dropcap-p::first-letter {
    text-shadow: 0 0 32px rgba(220,38,38,0.18);
}
html:not(.dark) .bs-share {
    background: rgba(255,255,255,0.7);
    border-bottom-color: rgba(0,0,0,0.08);
}
html:not(.dark) .bs-author {
    background: rgba(255,255,255,0.6);
    border-color: rgba(0,0,0,0.08);
}
html:not(.dark) .bs-author-avatar {
    background:
        radial-gradient(circle at 35% 35%, rgba(220,38,38,0.16), transparent 55%),
        rgba(0,0,0,0.05);
    border-color: rgba(0,0,0,0.10);
}
html:not(.dark) .bs-cta {
    background:
        radial-gradient(ellipse at 100% 0%, rgba(220,38,38,0.10), transparent 60%),
        rgba(255,255,255,0.5);
    border-color: rgba(220,38,38,0.20);
}
html:not(.dark) .bs-toc-inner,
html:not(.dark) .bs-side-share {
    background: rgba(255,255,255,0.5);
    border-color: rgba(0,0,0,0.08);
}
html:not(.dark) .bs-toc-ring-bg {
    stroke: rgba(0,0,0,0.08);
}
html:not(.dark) .bs-related-card {
    background: rgba(255,255,255,0.5);
    border-color: rgba(0,0,0,0.08);
}
/* Card image banners stay dark for editorial impact */
html:not(.dark) .bs-related-img {
    background: linear-gradient(160deg, #2A1D1D, #0a0a0a);
}
html:not(.dark) .bs-divider-line,
html:not(.dark) .bs-related-divider-line {
    background: rgba(0,0,0,0.10);
}
</style>

{{-- Reading progress bar --}}
<div class="bs-progress" :style="`width: ${progress}%`" aria-hidden="true"></div>

{{-- ── HERO ── --}}
<section class="bs-hero" id="bs-top">
    <div class="bs-hero-bg" style="--hero-tone: {{ $tone }};"></div>
    <div class="bs-hero-overlay"></div>

    <div class="bs-hero-issue" aria-hidden="true">
        <span class="bs-hero-issue-num">N° {{ str_pad((string) (collect($articles)->search(fn ($a) => $a['slug'] === $article['slug']) + 1), 2, '0', STR_PAD_LEFT) }}</span>
        <span>{{ \Illuminate\Support\Str::upper($article['category']) }} · {{ \Illuminate\Support\Str::upper(\Carbon\Carbon::parse($article['date'])->translatedFormat('M Y')) }}</span>
    </div>

    <a href="{{ route('blog.index') }}" class="bs-hero-back">
        ← {{ __('blog.back_to_blog') }}
    </a>

    <div class="bs-hero-content">
        <div class="bs-hero-category">{{ $article['category'] }}</div>
        <h1 class="bs-hero-title">{{ $article['title'] }}</h1>
        <div class="bs-hero-meta">
            <span>{{ \Illuminate\Support\Str::upper(\Carbon\Carbon::parse($article['date'])->translatedFormat('d M Y')) }}</span>
            <span class="bs-hero-meta-dot"></span>
            <span>{{ \Illuminate\Support\Str::upper($article['author']) }}</span>
            <span class="bs-hero-meta-dot"></span>
            <span>{{ \Illuminate\Support\Str::upper($article['reading_time']) }} {{ $isEs ? 'LECTURA' : 'READ' }}</span>
        </div>
    </div>
</section>

{{-- ── SHARE BAR (mobile + tablet) ── --}}
<div class="bs-share" role="complementary" aria-label="{{ __('blog.share_label') }}">
    <div class="bs-share-inner">
        <span class="bs-share-label">{{ __('blog.share_label') }}</span>
        <button type="button" class="bs-share-btn bs-share-wa">{{ __('blog.share_whatsapp') }}</button>
        <button type="button" class="bs-share-btn bs-share-tw">{{ __('blog.share_twitter') }}</button>
        <button type="button" class="bs-share-btn bs-share-cp">{{ __('blog.share_copy') }}</button>
    </div>
</div>

{{-- ── ARTICLE LAYOUT ── --}}
<div class="bs-layout">

    {{-- Article body --}}
    <article class="bs-body">
        {!! $bodyHtml !!}

        {{-- Inline divider --}}
        <div class="bs-divider" aria-hidden="true">
            <div class="bs-divider-line"></div>
            <span>CIENCIA · MÉTODO · 2026</span>
            <div class="bs-divider-line"></div>
        </div>

        {{-- Author bio --}}
        <div class="bs-author">
            <div class="bs-author-avatar" aria-hidden="true">WC</div>
            <div>
                <div class="bs-author-name">{{ \Illuminate\Support\Str::upper($article['author']) }}</div>
                <div class="bs-author-role">{{ __('blog.author_role') }}</div>
                <p class="bs-author-bio">{{ __('blog.author_bio') }}</p>
                <div class="bs-author-phrase">"{{ __('blog.author_phrase') }}"</div>
            </div>
        </div>

        {{-- CTA contextual --}}
        <div class="bs-cta" role="complementary">
            <div class="bs-cta-eyebrow">{{ __('blog.show_cta_eyebrow') }}</div>
            <h2 class="bs-cta-title">{{ __('blog.show_cta_heading') }}</h2>
            <p class="bs-cta-sub">{{ __('blog.show_cta_body') }}</p>
            <a href="{{ route('metodo') }}" class="bs-cta-btn">{{ __('blog.show_cta_button') }} →</a>
        </div>
    </article>

    {{-- TOC sidebar (desktop only) --}}
    <aside class="bs-toc" aria-label="{{ __('blog.toc_title') }}">
        <div class="bs-toc-inner">
            <div class="bs-toc-title">{{ __('blog.toc_title') }}</div>
            <ul class="bs-toc-list">
                @foreach ($tocItems as $idx => $item)
                    <li>
                        <a href="#{{ $item['id'] }}"
                           class="bs-toc-link {{ $item['level'] === 3 ? 'bs-toc-h3' : '' }} {{ $idx === 0 ? 'is-active' : '' }}"
                           data-toc-target="{{ $item['id'] }}">
                            <span class="bs-toc-num">{{ str_pad((string) ($idx + 1), 2, '0', STR_PAD_LEFT) }}</span>
                            <span>{{ $item['label'] }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>

            <div class="bs-toc-meta">
                <div class="bs-toc-meta-row">
                    <span>{{ __('blog.show_meta_reading') }}</span>
                    <span class="bs-toc-meta-val">{{ \Illuminate\Support\Str::upper($article['reading_time']) }}</span>
                </div>
                <div class="bs-toc-meta-row">
                    <span>{{ __('blog.show_meta_category') }}</span>
                    <span class="bs-toc-meta-val">{{ \Illuminate\Support\Str::upper($article['category']) }}</span>
                </div>
                <div class="bs-toc-meta-row">
                    <span>{{ __('blog.show_meta_published') }}</span>
                    <span class="bs-toc-meta-val">{{ \Illuminate\Support\Str::upper(\Carbon\Carbon::parse($article['date'])->translatedFormat('d M Y')) }}</span>
                </div>
            </div>

            <div class="bs-toc-progress">
                <svg class="bs-toc-ring" viewBox="0 0 36 36" aria-hidden="true">
                    <circle class="bs-toc-ring-bg" cx="18" cy="18" r="16"></circle>
                    <circle class="bs-toc-ring-fill" id="bs-toc-ring" cx="18" cy="18" r="16" :style="`stroke-dashoffset: ${100.5 - (100.5 * progress / 100)}`"></circle>
                </svg>
                <div class="bs-toc-ring-label">{{ __('blog.toc_progress') }} <span x-text="Math.round(progress)"></span>%</div>
            </div>
        </div>
    </aside>

</div>

{{-- ── RELATED ARTICLES ── --}}
@if (count($relatedArticles) > 0)
    <div class="bs-related">
        <div class="bs-related-divider">
            <div class="bs-related-divider-line"></div>
            <span>{{ __('blog.divider_more') ?? 'MÁS ARTÍCULOS' }}</span>
            <div class="bs-related-divider-line"></div>
        </div>
        <div class="bs-related-grid">
            @foreach ($relatedArticles as $rIdx => $r)
                @php
                    $relTone = match (\Illuminate\Support\Str::lower($r['category'])) {
                        'entrenamiento', 'training' => 'radial-gradient(ellipse 70% 50% at 30% 30%, rgba(220,38,38,0.18), transparent 60%)',
                        'nutricion', 'nutrition'    => 'radial-gradient(ellipse 70% 50% at 30% 30%, rgba(16,185,129,0.16), transparent 60%)',
                        'recuperacion', 'recovery'  => 'radial-gradient(ellipse 70% 50% at 30% 30%, rgba(56,135,255,0.18), transparent 60%)',
                        'mindset'                   => 'radial-gradient(ellipse 70% 50% at 30% 30%, rgba(244,114,182,0.16), transparent 60%)',
                        'ciencia', 'science'        => 'radial-gradient(ellipse 70% 50% at 30% 30%, rgba(212,160,76,0.18), transparent 60%)',
                        default                     => 'radial-gradient(ellipse 70% 50% at 30% 30%, rgba(220,38,38,0.15), transparent 60%)',
                    };
                @endphp
                <a href="{{ route('blog.show', $r['slug']) }}" class="bs-related-card">
                    <div class="bs-related-img" style="--card-tone: {{ $relTone }};">
                        <span class="bs-related-num">{{ str_pad((string) ($rIdx + 1), 2, '0', STR_PAD_LEFT) }}</span>
                        <span class="bs-related-cat">{{ $r['category'] }}</span>
                    </div>
                    <div class="bs-related-body">
                        <h3 class="bs-related-title">{{ $r['title'] }}</h3>
                        <p class="bs-related-excerpt">{{ $r['excerpt'] }}</p>
                        <div class="bs-related-meta">
                            <span>{{ \Illuminate\Support\Str::upper(\Carbon\Carbon::parse($r['date'])->translatedFormat('M Y')) }}</span>
                            <span class="bs-related-meta-dot"></span>
                            <span>{{ \Illuminate\Support\Str::upper($r['reading_time']) }}</span>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
@endif

{{-- ── Reading progress + TOC active link + share buttons (CSP-friendly) ── --}}
<script nonce="@cspNonce">
(function () {
    function init() {
        var root = document.querySelector('.blog-show-root');
        if (!root) return;
        var alpine = root.__x;

        // Reading progress + TOC ring
        function update() {
            var docEl = document.documentElement;
            var max = docEl.scrollHeight - docEl.clientHeight;
            var pct = max > 0 ? Math.min(100, (window.scrollY / max) * 100) : 0;
            // Update Alpine state if available, fallback to direct DOM.
            try {
                if (root._x_dataStack && root._x_dataStack[0]) {
                    root._x_dataStack[0].progress = pct;
                }
            } catch (e) {}
            // Also write directly to the bar (in case Alpine is delayed):
            var bar = root.querySelector('.bs-progress');
            if (bar) bar.style.width = pct + '%';
        }

        // Active TOC link by scroll position
        var headings = Array.from(root.querySelectorAll('.bs-body h2[id], .bs-body h3[id]'));
        var links    = Array.from(root.querySelectorAll('.bs-toc-link[data-toc-target]'));
        function syncToc() {
            if (!headings.length || !links.length) return;
            var current = headings[0].id;
            for (var i = 0; i < headings.length; i++) {
                if (headings[i].getBoundingClientRect().top < 140) current = headings[i].id;
            }
            links.forEach(function (l) {
                l.classList.toggle('is-active', l.getAttribute('data-toc-target') === current);
            });
        }

        function onScroll() { update(); syncToc(); }
        document.addEventListener('scroll', onScroll, { passive: true });
        window.addEventListener('resize', onScroll, { passive: true });
        onScroll();

        // Share buttons (CSP-safe: no inline onclick).
        var shareWa = root.querySelector('.bs-share-wa');
        var shareTw = root.querySelector('.bs-share-tw');
        var shareCp = root.querySelector('.bs-share-cp');
        var sideShareWa = root.querySelector('.bs-side-share-wa');
        var sideShareTw = root.querySelector('.bs-side-share-tw');
        var sideShareCp = root.querySelector('.bs-side-share-cp');
        function shareWhatsApp() {
            window.open('https://wa.me/?text=' + encodeURIComponent(document.title + ' ' + location.href), '_blank', 'noopener');
        }
        function shareTwitter() {
            window.open('https://twitter.com/intent/tweet?text=' + encodeURIComponent(document.title) + '&url=' + encodeURIComponent(location.href), '_blank', 'noopener');
        }
        function copyLink(btn) {
            try {
                navigator.clipboard.writeText(location.href).then(function () {
                    var original = btn.textContent;
                    btn.textContent = '✓ ' + (document.documentElement.lang === 'en' ? 'COPIED' : 'COPIADO');
                    setTimeout(function () { btn.textContent = original; }, 2000);
                });
            } catch (e) {}
        }
        if (shareWa) shareWa.addEventListener('click', shareWhatsApp);
        if (shareTw) shareTw.addEventListener('click', shareTwitter);
        if (shareCp) shareCp.addEventListener('click', function () { copyLink(shareCp); });
        if (sideShareWa) sideShareWa.addEventListener('click', shareWhatsApp);
        if (sideShareTw) sideShareTw.addEventListener('click', shareTwitter);
        if (sideShareCp) sideShareCp.addEventListener('click', function () { copyLink(sideShareCp); });
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
</script>

{{-- ── JSON-LD BlogPosting (SEO) ── --}}
<script type="application/ld+json">
{!! json_encode($articleSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>

</div>{{-- .blog-show-root --}}
</x-layouts.public>
