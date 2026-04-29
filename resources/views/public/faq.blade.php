@php
    // Substitute :url placeholders before rendering (refund policy link in pa3_a).
    $refundUrl = route('reembolsos');
    $faqItems = collect(__('faq.items'))->map(function ($item) use ($refundUrl) {
        $item['a'] = str_replace(':url', $refundUrl, $item['a']);
        return $item;
    })->values()->all();

    $tabs       = __('faq.tabs');
    $tabCounts  = collect($faqItems)->countBy('cat');
    $totalCount = count($faqItems);
    $whatsapp   = config('wellcore.whatsapp_silvia', '573000000000');
    $whatsappMsg= urlencode(__('faq.cta_whatsapp_msg'));
    $isEs       = app()->getLocale() === 'es';

    // JSON-LD payload (FAQPage schema).
    $faqSchema = [
        '@context'   => 'https://schema.org',
        '@type'      => 'FAQPage',
        'mainEntity' => collect($faqItems)->map(fn ($f) => [
            '@type'          => 'Question',
            'name'           => strip_tags($f['q']),
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text'  => strip_tags($f['a']),
            ],
        ])->values()->all(),
    ];
@endphp

<x-layouts.public bodyClass="faq-page">
    <x-slot:title>{{ __('faq.title') }}</x-slot:title>
    <x-slot:description>{{ __('faq.meta_description') }}</x-slot:description>

<div
    class="faq-page-root"
    x-data="{
        search: '',
        activeTab: 'general',
        searchFocused: false,
        get isSearching() { return this.search.trim().length > 0; },
        matchesTab(cat) {
            return this.isSearching || this.activeTab === cat;
        },
        matchesSearch(haystack) {
            if (!this.isSearching) return true;
            return haystack.indexOf(this.search.trim().toLowerCase()) !== -1;
        },
        clearSearch() { this.search = ''; },
        setTab(id) { this.activeTab = id; this.search = ''; },
        init() {
            const saved = localStorage.getItem('wc-faq-tab');
            if (saved && ['general','planes','pagos','entrenamiento','soporte'].includes(saved)) {
                this.activeTab = saved;
            }
            this.$watch('activeTab', v => localStorage.setItem('wc-faq-tab', v));
        },
        get visibleCount() {
            return Array.from(document.querySelectorAll('.faq-list .faq-item'))
                .filter(el => el.offsetParent !== null).length;
        }
    }"
    x-init="init()"
>

{{-- ════════════════════════════════════════════════════════════════
     iOS-feel FAQ styles — inline (Sprint 4 noche, sin npm build).
     Tokens scoped a .faq-page-root. Adaptado del v2 brutal con
     fuentes Oswald + Raleway + Fraunces italic + JetBrains Mono.
     ════════════════════════════════════════════════════════════════ --}}
<style>
.faq-page-root {
    --faq-bg:        #0a0a0a;
    --faq-bg-2:      #111111;
    --faq-bg-3:      #1a1a1a;
    --faq-text:      #FAFAFA;
    --faq-text-2:    #A3A3A3;
    --faq-text-3:    #737373;
    --faq-text-4:    #525252;
    --faq-border:    rgba(255,255,255,0.07);
    --faq-border-2:  rgba(255,255,255,0.12);
    --faq-red:       #DC2626;
    --faq-red-soft:  rgba(220,38,38,0.04);
    --faq-red-text:  #F87171;
    --faq-gold:      #D4A04C;
    --faq-wa:        #25D366;
    --faq-ease-out:  cubic-bezier(.22,1,.36,1);

    position: relative;
    min-height: calc(100vh - 64px);
    min-height: calc(100dvh - 64px);
    background: var(--faq-bg);
    color: var(--faq-text);
    overflow-x: hidden;
    isolation: isolate;
}

/* Atmosphere + grain */
.faq-page-root::before {
    content: '';
    position: absolute; inset: 0;
    pointer-events: none; z-index: 0;
    background:
        radial-gradient(ellipse 70% 40% at 0% -10%, rgba(220,38,38,0.10), transparent 55%),
        radial-gradient(ellipse 50% 30% at 110% 10%, rgba(220,38,38,0.05), transparent 50%);
}
.faq-page-root::after {
    content: '';
    position: absolute; inset: 0;
    pointer-events: none; z-index: 0;
    opacity: 0.025;
    mix-blend-mode: overlay;
    background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.85' numOctaves='3' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E");
    background-size: 220px;
}

.faq-shell {
    position: relative; z-index: 1;
    max-width: 720px;
    margin: 0 auto;
    padding-left: env(safe-area-inset-left);
    padding-right: env(safe-area-inset-right);
}

/* ── HERO ─────────────────────────────────────────────── */
.faq-hero {
    padding: 56px 24px 36px;
}
.faq-hero-eyebrow {
    display: inline-flex; align-items: center; gap: 10px;
    font-family: 'JetBrains Mono', 'SF Mono', monospace;
    font-size: 11px; letter-spacing: 0.22em;
    text-transform: uppercase;
    color: var(--faq-red);
    margin-bottom: 16px;
}
.faq-hero-eyebrow::before {
    content: ''; width: 18px; height: 1px;
    background: var(--faq-red);
}
.faq-hero-title {
    font-family: 'Oswald', Impact, sans-serif;
    font-weight: 600;
    font-size: clamp(56px, 16vw, 92px);
    line-height: 0.9;
    letter-spacing: 0.005em;
    color: var(--faq-text);
    text-transform: uppercase;
    margin-bottom: 20px;
}
.faq-hero-title em {
    font-style: normal;
    color: var(--faq-red);
}
.faq-hero-sub {
    font-family: 'Fraunces', Georgia, serif;
    font-style: italic;
    font-weight: 400;
    font-size: 17px;
    line-height: 1.45;
    color: var(--faq-gold);
    max-width: 38ch;
}

/* ── SEARCH ───────────────────────────────────────────── */
.faq-search-wrap {
    position: relative;
    margin-top: 32px;
}
.faq-search-input {
    width: 100%;
    background: rgba(26,26,26,0.8);
    -webkit-backdrop-filter: blur(8px);
    backdrop-filter: blur(8px);
    border: 1px solid rgba(255,255,255,0.10);
    border-radius: 12px;
    padding: 14px 48px 14px 18px;
    color: var(--faq-text);
    font-family: 'Raleway', system-ui, sans-serif;
    font-size: 14px;
    letter-spacing: -0.005em;
    outline: none;
    transition: border-color 0.25s var(--faq-ease-out), box-shadow 0.25s var(--faq-ease-out);
    -webkit-appearance: none;
}
.faq-search-input::placeholder { color: var(--faq-text-3); }
.faq-search-input::-webkit-search-cancel-button { display: none; }
.faq-search-input:focus {
    border-color: var(--faq-red);
    box-shadow: 0 0 0 3px rgba(220,38,38,0.12);
}
.faq-search-clear {
    position: absolute; right: 10px; top: 50%; transform: translateY(-50%);
    background: none; border: none; cursor: pointer;
    color: var(--faq-text-3);
    width: 30px; height: 30px;
    display: flex; align-items: center; justify-content: center;
    border-radius: 8px;
    -webkit-tap-highlight-color: transparent;
    transition: color 0.2s, background 0.2s;
}
.faq-search-clear:hover { color: var(--faq-text); background: rgba(255,255,255,0.05); }
.faq-search-meta {
    margin-top: 12px;
    font-family: 'JetBrains Mono', 'SF Mono', monospace;
    font-size: 10.5px; color: var(--faq-text-3);
    letter-spacing: 0.10em;
    text-transform: uppercase;
}
.faq-search-meta strong { color: var(--faq-text-2); font-weight: 600; }
.faq-search-meta .accent { color: var(--faq-red); }

/* ── TABS NAV (sticky) ────────────────────────────────── */
.faq-tabs-sticky {
    position: sticky;
    top: 64px; z-index: 40;
    background: rgba(10,10,10,0.92);
    -webkit-backdrop-filter: blur(16px) saturate(140%);
    backdrop-filter: blur(16px) saturate(140%);
    border-top: 1px solid var(--faq-border);
    border-bottom: 1px solid var(--faq-border);
    margin: 32px 0 0;
    will-change: transform;
    transform: translateZ(0);
}
.faq-tabs-inner {
    display: flex;
    overflow-x: auto;
    scrollbar-width: none; -ms-overflow-style: none;
    padding: 0 24px;
    max-width: 720px;
    margin: 0 auto;
}
.faq-tabs-inner::-webkit-scrollbar { display: none; }
.faq-tab {
    flex-shrink: 0;
    display: flex; flex-direction: column; align-items: center; gap: 3px;
    padding: 14px 16px 12px;
    border: none; background: none; cursor: pointer;
    border-bottom: 2px solid transparent;
    transition: border-color 0.25s var(--faq-ease-out);
    -webkit-tap-highlight-color: transparent;
}
.faq-tab.is-active { border-bottom-color: var(--faq-red); }
.faq-tab-name {
    font-family: 'JetBrains Mono', 'SF Mono', monospace;
    font-size: 10.5px; letter-spacing: 0.15em;
    text-transform: uppercase;
    color: var(--faq-text-3);
    white-space: nowrap;
    transition: color 0.2s;
}
.faq-tab.is-active .faq-tab-name { color: var(--faq-text); }
.faq-tab-count {
    font-family: 'JetBrains Mono', 'SF Mono', monospace;
    font-size: 9.5px; letter-spacing: 0.05em;
    color: var(--faq-text-4);
    transition: color 0.2s;
}
.faq-tab.is-active .faq-tab-count { color: var(--faq-red); }

/* ── CATEGORY HEADER ──────────────────────────────────── */
.faq-cat-header {
    padding: 32px 24px 0;
}
.faq-cat-eyebrow {
    font-family: 'JetBrains Mono', 'SF Mono', monospace;
    font-size: 10px; letter-spacing: 0.25em; text-transform: uppercase;
    color: var(--faq-text-3); margin-bottom: 6px;
}
.faq-cat-title-row {
    display: flex; align-items: baseline; gap: 12px;
}
.faq-cat-title {
    font-family: 'Oswald', Impact, sans-serif;
    font-weight: 600;
    font-size: 30px; letter-spacing: 0.04em;
    color: var(--faq-text);
    text-transform: uppercase;
    line-height: 1;
}
.faq-cat-n {
    font-family: 'JetBrains Mono', 'SF Mono', monospace;
    font-size: 12px; color: var(--faq-red);
    letter-spacing: 0.05em;
}

/* ── DIVIDER ──────────────────────────────────────────── */
.faq-divider {
    display: flex; align-items: center; gap: 14px;
    padding: 18px 24px 0;
    font-family: 'Oswald', Impact, sans-serif;
    font-size: 9.5px; letter-spacing: 0.32em;
    color: var(--faq-text-3); opacity: 0.5;
    text-transform: uppercase;
    font-weight: 500;
}
.faq-divider-line {
    flex: 1; height: 1px;
    background: linear-gradient(to right, transparent, var(--faq-border), transparent);
}

/* ── ACCORDION LIST ───────────────────────────────────── */
.faq-list {
    padding: 8px 24px 0;
}
.faq-item {
    border-top: 1px solid var(--faq-border);
    border-left: 2px solid transparent;
    margin-left: -2px;
    transition: border-left-color 0.25s var(--faq-ease-out), background 0.25s var(--faq-ease-out);
}
.faq-item[open] {
    border-left-color: var(--faq-red);
    background: var(--faq-red-soft);
    border-radius: 0 6px 6px 0;
}
.faq-item summary {
    display: flex; align-items: flex-start; gap: 12px;
    padding: 18px 14px;
    cursor: pointer; list-style: none;
    user-select: none;
    -webkit-tap-highlight-color: transparent;
}
.faq-item summary::-webkit-details-marker { display: none; }
.faq-num {
    font-family: 'JetBrains Mono', 'SF Mono', monospace;
    font-size: 10px; color: var(--faq-text-3);
    padding-top: 2px;
    flex-shrink: 0; min-width: 32px;
    letter-spacing: 0.04em;
}
.faq-item[open] .faq-num { color: rgba(220,38,38,0.7); }
.faq-q {
    font-family: 'Oswald', Impact, sans-serif;
    font-weight: 500;
    font-size: 16px;
    letter-spacing: 0.02em;
    text-transform: uppercase;
    color: var(--faq-text);
    line-height: 1.3;
    flex: 1;
    word-break: break-word;
}
.faq-icon {
    font-size: 22px;
    font-weight: 300;
    color: var(--faq-text-3);
    flex-shrink: 0;
    line-height: 1;
    transition: transform 0.3s var(--faq-ease-out), color 0.2s;
    font-family: 'Raleway', system-ui, sans-serif;
    margin-top: -2px;
}
.faq-item[open] .faq-icon {
    transform: rotate(45deg);
    color: var(--faq-red);
}
.faq-body {
    padding: 0 14px 22px 58px;
    font-family: 'Raleway', system-ui, sans-serif;
    font-size: 15px;
    line-height: 1.65;
    color: var(--faq-text-2);
    text-wrap: pretty;
}
.faq-body p + p { margin-top: 10px; }
.faq-body strong { color: var(--faq-text); font-weight: 600; }
.faq-body a {
    color: var(--faq-text);
    text-decoration: underline;
    text-decoration-color: var(--faq-red);
    text-underline-offset: 3px;
    transition: color 0.2s;
}
.faq-body a:hover { color: var(--faq-red-text); }

/* ── EMPTY STATE ──────────────────────────────────────── */
.faq-empty {
    padding: 56px 24px;
    text-align: center;
}
.faq-empty-mono {
    font-family: 'JetBrains Mono', 'SF Mono', monospace;
    font-size: 10px; letter-spacing: 0.25em;
    text-transform: uppercase;
    color: var(--faq-text-3); margin-bottom: 18px;
}
.faq-empty-title {
    font-family: 'Oswald', Impact, sans-serif;
    font-weight: 600;
    font-size: 32px; letter-spacing: 0.04em;
    text-transform: uppercase;
    color: var(--faq-text); margin-bottom: 10px;
}
.faq-empty-sub {
    font-family: 'Fraunces', Georgia, serif;
    font-style: italic;
    font-size: 15px;
    line-height: 1.5;
    color: var(--faq-gold);
    margin: 0 auto 28px;
    max-width: 38ch;
}

/* ── CTA ──────────────────────────────────────────────── */
.faq-cta {
    margin: 56px 24px 56px;
}
.faq-cta-card {
    background: rgba(26,26,26,0.62);
    -webkit-backdrop-filter: blur(20px) saturate(180%);
    backdrop-filter: blur(20px) saturate(180%);
    border: 0.5px solid rgba(255,255,255,0.10);
    border-radius: 18px;
    padding: 32px 24px;
    box-shadow:
        0 1px 0 rgba(255,255,255,0.04) inset,
        0 12px 40px -12px rgba(0,0,0,0.55);
}
.faq-cta-eyebrow {
    font-family: 'JetBrains Mono', 'SF Mono', monospace;
    font-size: 10px; letter-spacing: 0.25em;
    text-transform: uppercase;
    color: var(--faq-text-3); margin-bottom: 12px;
}
.faq-cta-title {
    font-family: 'Oswald', Impact, sans-serif;
    font-weight: 600;
    font-size: 36px; letter-spacing: 0.03em;
    line-height: 0.95;
    text-transform: uppercase;
    color: var(--faq-text);
    margin-bottom: 12px;
}
.faq-cta-title em {
    font-style: normal;
    color: var(--faq-red);
}
.faq-cta-sub {
    font-family: 'Fraunces', Georgia, serif;
    font-style: italic;
    font-size: 15px; line-height: 1.5;
    color: var(--faq-gold);
    margin-bottom: 24px;
    max-width: 38ch;
}
.faq-cta-actions {
    display: flex; flex-direction: column; gap: 10px;
}
.faq-cta-btn-primary {
    display: inline-flex; align-items: center; justify-content: center; gap: 10px;
    background: var(--faq-wa);
    color: #fff;
    font-family: 'Oswald', Impact, sans-serif;
    font-weight: 600;
    font-size: 14px; letter-spacing: 0.16em;
    text-transform: uppercase;
    padding: 16px 22px;
    border-radius: 999px;
    text-decoration: none;
    transition: opacity 0.2s, transform 0.12s var(--faq-ease-out);
    -webkit-tap-highlight-color: transparent;
    white-space: nowrap;
    min-height: 52px;
    box-shadow: 0 12px 32px -10px rgba(37,211,102,0.45);
}
.faq-cta-btn-primary:active { transform: scale(0.98); }
.faq-cta-btn-primary:hover { opacity: 0.92; }
.faq-cta-btn-primary .pulse-dot {
    width: 7px; height: 7px; border-radius: 50%;
    background: rgba(255,255,255,0.85);
    animation: faq-pulse-dot 2s ease-in-out infinite;
    flex-shrink: 0;
}
@keyframes faq-pulse-dot {
    0%, 100% { opacity: 0.6; transform: scale(1); }
    50%      { opacity: 1;   transform: scale(1.35); }
}
.faq-cta-btn-secondary {
    display: inline-flex; align-items: center; justify-content: center; gap: 8px;
    background: transparent;
    color: var(--faq-text-2);
    font-family: 'Raleway', system-ui, sans-serif;
    font-weight: 500;
    font-size: 13px; letter-spacing: 0.05em;
    padding: 15px 22px;
    border-radius: 999px;
    border: 1px solid var(--faq-border);
    text-decoration: none;
    transition: border-color 0.2s, color 0.2s;
    -webkit-tap-highlight-color: transparent;
    min-height: 52px;
}
.faq-cta-btn-secondary:hover {
    border-color: rgba(255,255,255,0.20);
    color: var(--faq-text);
}
.faq-cta-btn-secondary .path { color: var(--faq-red); }

/* ── DESKTOP ──────────────────────────────────────────── */
@media (min-width: 1024px) {
    .faq-shell { max-width: 760px; }
    .faq-hero { padding: 80px 24px 48px; }
    .faq-hero-title { font-size: 96px; }
    .faq-cta-actions { flex-direction: row; }
    .faq-cta-btn-primary, .faq-cta-btn-secondary { flex: 1; }
    .faq-tabs-sticky { top: 72px; }
}

/* ── REDUCED MOTION ───────────────────────────────────── */
@media (prefers-reduced-motion: reduce) {
    .faq-page-root *, .faq-page-root *::before, .faq-page-root *::after {
        animation-duration: 0.01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: 0.01ms !important;
    }
}

/* ════════════════════════════════════════════════════════
   LIGHT THEME — toggled via .dark class on <html>
   Layout uses Alpine + localStorage; light = NO .dark class.
   ════════════════════════════════════════════════════════ */
html:not(.dark) .faq-page-root {
    --faq-bg:        #FAFAFA;
    --faq-bg-2:      #F4F4F5;
    --faq-bg-3:      #E8E8E8;
    --faq-text:      #0A0A0A;
    --faq-text-2:    #404040;
    --faq-text-3:    #737373;
    --faq-text-4:    #A3A3A3;
    --faq-border:    rgba(0,0,0,0.08);
    --faq-border-2:  rgba(0,0,0,0.14);
    --faq-red-soft:  rgba(220,38,38,0.06);
    --faq-gold:      #B5852A;
}
html:not(.dark) .faq-page-root::before {
    background:
        radial-gradient(ellipse 70% 40% at 0% -10%, rgba(220,38,38,0.07), transparent 55%),
        radial-gradient(ellipse 50% 30% at 110% 10%, rgba(212,160,76,0.05), transparent 50%);
}
html:not(.dark) .faq-page-root::after {
    opacity: 0.05;
    mix-blend-mode: multiply;
}
html:not(.dark) .faq-search-input {
    background: rgba(255,255,255,0.8);
    border-color: rgba(0,0,0,0.10);
}
html:not(.dark) .faq-tabs-sticky {
    background: rgba(250,250,250,0.92);
}
html:not(.dark) .faq-divider-line {
    background: linear-gradient(to right, transparent, rgba(0,0,0,0.10), transparent);
}
html:not(.dark) .faq-cta-card {
    background: rgba(255,255,255,0.7);
    border-color: rgba(0,0,0,0.08);
    box-shadow:
        0 1px 0 rgba(0,0,0,0.02) inset,
        0 12px 40px -12px rgba(0,0,0,0.18);
}
html:not(.dark) .faq-cta-btn-primary {
    box-shadow: 0 12px 32px -10px rgba(37,211,102,0.35);
}
</style>

<div class="faq-shell">

    {{-- ── HERO ── --}}
    <section class="faq-hero">
        <div class="faq-hero-eyebrow">
            <span>{{ __('faq.hero_eyebrow') }}</span>
        </div>
        <h1 class="faq-hero-title">
            {{ __('faq.hero_h1_line1') }}<br>
            <em>{{ __('faq.hero_h1_accent') }}</em>
        </h1>
        <p class="faq-hero-sub">{{ __('faq.hero_sub') }}</p>

        {{-- Search bar --}}
        <div class="faq-search-wrap">
            <input
                type="search"
                class="faq-search-input"
                x-model="search"
                @focus="searchFocused = true"
                @blur="searchFocused = false"
                placeholder="{{ __('faq.buscar') }}"
                aria-label="{{ __('faq.search_label') }}"
                autocomplete="off"
                spellcheck="false"
            >
            <button
                type="button"
                class="faq-search-clear"
                x-show="search.length > 0"
                @click="clearSearch()"
                aria-label="{{ __('faq.search_clear_aria') }}"
                style="display:none"
            >
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>

        <p class="faq-search-meta" x-show="!isSearching">
            <strong>{{ $totalCount }}</strong>&nbsp;{{ $isEs ? 'respuestas' : 'answers' }}
            &nbsp;·&nbsp;
            <strong>{{ count($tabs) }}</strong>&nbsp;{{ $isEs ? 'categorías' : 'categories' }}
        </p>
        <p class="faq-search-meta" x-show="isSearching" style="display:none">
            <span x-text="visibleCount"></span>&nbsp;{{ $isEs ? 'resultados' : 'results' }}
            &nbsp;·&nbsp;
            <span class="accent">"<span x-text="search"></span>"</span>
        </p>
    </section>

</div>

{{-- ── TABS STICKY ── --}}
<nav class="faq-tabs-sticky" x-show="!isSearching" role="tablist" aria-label="{{ __('faq.title') }}">
    <div class="faq-tabs-inner">
        @foreach ($tabs as $key => $label)
            <button
                type="button"
                class="faq-tab"
                :class="{ 'is-active': activeTab === '{{ $key }}' }"
                @click="setTab('{{ $key }}')"
                role="tab"
                :aria-selected="activeTab === '{{ $key }}' ? 'true' : 'false'"
            >
                <span class="faq-tab-name">{{ $label }}</span>
                <span class="faq-tab-count">{{ str_pad((string) ($tabCounts[$key] ?? 0), 2, '0', STR_PAD_LEFT) }}</span>
            </button>
        @endforeach
    </div>
</nav>

<div class="faq-shell">

    {{-- Category header (hidden when searching) --}}
    <div class="faq-cat-header" x-show="!isSearching">
        <div class="faq-cat-eyebrow">/ {{ $isEs ? 'Categoría' : 'Category' }}</div>
        <div class="faq-cat-title-row">
            @foreach ($tabs as $key => $label)
                <span class="faq-cat-title" x-show="activeTab === '{{ $key }}'" style="display:none">{{ \Illuminate\Support\Str::upper($label) }}</span>
            @endforeach
            <span class="faq-cat-n">·&nbsp;
                @foreach ($tabs as $key => $label)
                    <span x-show="activeTab === '{{ $key }}'" style="display:none">{{ str_pad((string) ($tabCounts[$key] ?? 0), 2, '0', STR_PAD_LEFT) }}</span>
                @endforeach
            </span>
        </div>
    </div>

    {{-- Divider --}}
    <div class="faq-divider" x-show="!isSearching" aria-hidden="true">
        <div class="faq-divider-line"></div>
        <span>CIENCIA · MÉTODO · 2026</span>
        <div class="faq-divider-line"></div>
    </div>

    {{-- ── ACCORDION LIST (SSR for SEO) ── --}}
    <div class="faq-list" role="region" aria-label="{{ __('faq.title') }}">
        @foreach ($faqItems as $idx => $item)
            @php
                $haystack = strtolower(strip_tags($item['q'] . ' ' . $item['a']));
                $haystackJs = json_encode($haystack, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_APOS | JSON_HEX_QUOT);
            @endphp
            <details
                class="faq-item"
                data-cat="{{ $item['cat'] }}"
                x-show="matchesTab('{{ $item['cat'] }}') && matchesSearch({{ $haystackJs }})"
                @if ($idx === 0) open @endif
            >
                <summary>
                    <span class="faq-num">{{ str_pad((string) ($idx + 1), 3, '0', STR_PAD_LEFT) }}</span>
                    <span class="faq-q">{{ $item['q'] }}</span>
                    <span class="faq-icon" aria-hidden="true">+</span>
                </summary>
                <div class="faq-body">
                    <p>{!! $item['a'] !!}</p>
                </div>
            </details>
        @endforeach
    </div>

    {{-- ── EMPTY STATE ── --}}
    <div class="faq-empty"
         x-show="isSearching && visibleCount === 0"
         style="display:none">
        <div class="faq-empty-mono">/ 0 {{ $isEs ? 'RESULTADOS' : 'RESULTS' }}</div>
        <div class="faq-empty-title">{{ \Illuminate\Support\Str::upper(__('faq.empty_title')) }}.</div>
        <p class="faq-empty-sub">
            <span x-text="`{{ $isEs ? 'No encontramos respuestas para' : 'No matches for' }} “${search}”. {{ $isEs ? 'Escríbenos directo, te responde una persona.' : 'Reach out — a real person responds.' }}`"></span>
        </p>
        <a href="https://wa.me/{{ $whatsapp }}?text={{ $whatsappMsg }}"
           target="_blank" rel="noopener"
           class="faq-cta-btn-primary"
           style="display:inline-flex">
            <span class="pulse-dot" aria-hidden="true"></span>
            {{ __('faq.cta_whatsapp') }}
        </a>
    </div>

    {{-- ── CTA FINAL ── --}}
    <section class="faq-cta">
        <div class="faq-cta-card">
            <div class="faq-cta-eyebrow">/ {{ __('faq.cta_eyebrow') }}</div>
            <h2 class="faq-cta-title">
                {{ __('faq.cta_h2') }}<br>
                <em>{{ __('faq.cta_h2_accent') }}</em>
            </h2>
            <p class="faq-cta-sub">{{ __('faq.cta_sub') }}</p>
            <div class="faq-cta-actions">
                <a href="https://wa.me/{{ $whatsapp }}?text={{ $whatsappMsg }}"
                   target="_blank" rel="noopener"
                   class="faq-cta-btn-primary">
                    <span class="pulse-dot" aria-hidden="true"></span>
                    {{ __('faq.cta_whatsapp') }}
                </a>
                <a href="{{ route('metodo') }}" class="faq-cta-btn-secondary">
                    {{ $isEs ? 'Ver' : 'See' }} <span class="path">/{{ $isEs ? 'método' : 'method' }}</span> →
                </a>
            </div>
        </div>
    </section>

</div>

{{-- ── JSON-LD FAQPage (SEO) ── --}}
<script type="application/ld+json">
{!! json_encode($faqSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>

</div>{{-- .faq-page-root --}}
</x-layouts.public>
