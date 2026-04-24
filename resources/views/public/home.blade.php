<x-layouts.public>
    <x-slot:title>{{ __('home.meta_title') }}</x-slot:title>
    <x-slot:description>{{ __('home.meta_description') }}</x-slot:description>

    {{-- 1. Organization --}}
    <x-json-ld :data="[
        '@context' => 'https://schema.org',
        '@type' => 'Organization',
        'name' => 'WellCore Fitness',
        'url' => url('/'),
        'logo' => asset('images/logo-light-320.avif'),
        'description' => 'Coaching fitness 1:1 basado en ciencia para Latinoamérica.',
        'sameAs' => [
            'https://www.instagram.com/wellcore.fitness/',
            'https://www.youtube.com/@Wellcorefitness',
        ],
        'address' => [
            '@type' => 'PostalAddress',
            'addressCountry' => 'CO',
        ],
    ]" />

    {{-- 2. WebSite --}}
    <x-json-ld :data="[
        '@context' => 'https://schema.org',
        '@type' => 'WebSite',
        'name' => 'WellCore Fitness',
        'url' => url('/'),
        'potentialAction' => [
            '@type' => 'SearchAction',
            'target' => url('/blog?q={search_term_string}'),
            'query-input' => 'required name=search_term_string',
        ],
    ]" />

    {{-- 3. FAQPage --}}
    <x-json-ld :data="[
        '@context' => 'https://schema.org',
        '@type' => 'FAQPage',
        'mainEntity' => collect(range(1,8))->map(fn($i) => [
            '@type' => 'Question',
            'name' => __('home.faq_q'.$i),
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text' => __('home.faq_a'.$i),
            ],
        ])->toArray(),
    ]" />

    {{-- 4. Product (3 planes) --}}
    @foreach([
        ['esencial', 'Esencial', 299000, 'home.plan_esencial_name'],
        ['metodo', 'Método', 399000, 'home.plan_metodo_name'],
        ['elite', 'Elite', 549000, 'home.plan_elite_name'],
    ] as [$slug, $name, $price, $key])
    <x-json-ld :data="[
        '@context' => 'https://schema.org',
        '@type' => 'Product',
        'name' => 'WellCore '.$name,
        'description' => __($key),
        'brand' => ['@type' => 'Brand', 'name' => 'WellCore Fitness'],
        'offers' => [
            '@type' => 'Offer',
            'url' => url('/inscripcion?plan='.$slug),
            'priceCurrency' => 'COP',
            'price' => $price,
            'availability' => 'https://schema.org/InStock',
        ],
    ]" />
    @endforeach

    {{-- Reading progress bar --}}
    <div class="scroll-progress"></div>

    {{-- RISE banner removido — inscripciones cerradas (2026-04) --}}

    {{-- ================================================================== --}}
    {{-- HERO                                                               --}}
    {{-- ================================================================== --}}
    {{-- ╔══════════════════════════════════════════════════════════╗ --}}
    {{-- ║  HERO v3 — Centered, full-viewport, hp-* design system  ║ --}}
    {{-- ╚══════════════════════════════════════════════════════════╝ --}}
    <section class="hp-hero">
        <div class="hp-hero-orb hp-hero-orb-1" aria-hidden="true"></div>
        <div class="hp-hero-orb hp-hero-orb-2" aria-hidden="true"></div>

        <div class="hp-wrap hp-hero-inner">

            {{-- ── LEFT: texto ──────────────────────────────────────────── --}}
            <div class="hp-hero-left">

                {{-- Badge --}}
                <div class="hp-hero-badge hp-anim-up">
                    <span class="hp-hero-badge-dot"></span>
                    {{ __('home.hero_badge') }}
                </div>

                {{-- Headline --}}
                <h1 class="hp-hero-h1 hp-anim-up hp-anim-d1">
                    {{ __('home.hero_title_1') }}<br>
                    <em>{{ __('home.hero_title_3') }}</em>
                </h1>

                {{-- Subtitle --}}
                <p class="hp-hero-sub hp-anim-up hp-anim-d2">{{ __('home.hero_subtitle') }}</p>

                {{-- CTAs --}}
                <div class="hp-hero-ctas hp-anim-up hp-anim-d3">
                    <a href="{{ route('inscripcion') }}" class="hp-btn-primary btn-press pulse-glow">
                        {{ __('home.cta_comenzar') }}
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                        </svg>
                    </a>
                    <a href="{{ route('planes') }}" class="hp-btn-ghost btn-press">
                        {{ __('home.cta_ver_planes') }}
                    </a>
                </div>

                {{-- Phone mockup (solo mobile — en desktop se muestra en la columna derecha) --}}
                <div class="hp-hero-mockup hp-hero-phone-mobile hp-anim-up hp-anim-d4" aria-hidden="true">
                    <div class="hp-hero-phone">
                        <picture>
                            <source srcset="{{ asset('images/hero/dashboard-mobile.avif') }}" type="image/avif">
                            <img src="{{ asset('images/hero/dashboard-mobile.webp') }}"
                                 alt="" width="280" height="575"
                                 loading="eager" fetchpriority="high">
                        </picture>
                    </div>
                </div>

                {{-- Proof strip --}}
                <div class="hp-hero-proof hp-anim-up hp-anim-d5">
                    <div class="hp-hero-proof-item">
                        <span class="hp-hero-proof-icon font-bold text-wc-accent font-data">94%</span>
                        {{ __('home.hero_stat_adherencia') }}
                    </div>
                    <div class="hp-hero-proof-item">
                        <span class="hp-hero-proof-icon font-bold text-wc-accent">1:1</span>
                        {{ __('home.hero_stat_coaching') }}
                    </div>
                    <div class="hp-hero-proof-item">
                        <span class="hp-hero-proof-icon font-bold text-wc-accent font-data">100%</span>
                        {{ __('home.hero_stat_personalizado') }}
                    </div>
                </div>

                {{-- Plan chips --}}
                <div class="flex flex-wrap gap-2 justify-center lg:justify-start hp-anim-up hp-anim-d6">
                    <span class="rounded-full border border-wc-border px-3 py-1 text-xs text-wc-text-secondary">Esencial <span class="font-data font-semibold text-wc-text">$299k</span></span>
                    <span class="rounded-full border border-wc-border px-3 py-1 text-xs text-wc-text-secondary">Método <span class="font-data font-semibold text-wc-text">$399k</span></span>
                    <span class="rounded-full border border-wc-border px-3 py-1 text-xs text-wc-text-secondary">Elite <span class="font-data font-semibold text-wc-text">$549k</span></span>
                </div>

            </div>

            {{-- ── RIGHT: mockups dashboard ──────────────────────────────── --}}
            <div class="hp-hero-right hp-anim-up hp-anim-d2" aria-hidden="true">

                {{-- Chip flotante: PR --}}
                <div class="hp-float-chip hp-fc-pr">
                    <span class="hp-fc-dot" style="background:#F59E0B"></span>
                    <div>
                        <div class="hp-fc-title">🏆 Nuevo PR</div>
                        <div class="hp-fc-sub">Sentadilla 120 kg × 5</div>
                    </div>
                </div>

                {{-- Laptop / browser frame --}}
                <div class="hp-laptop">
                    <div class="hp-laptop-bar">
                        <span class="hp-lb-dot" style="background:#FF5F57"></span>
                        <span class="hp-lb-dot" style="background:#FEBC2E"></span>
                        <span class="hp-lb-dot" style="background:#28C840"></span>
                        <div class="hp-lb-url">wellcorefitness.com/client</div>
                    </div>
                    <div class="hp-laptop-screen">
                        {{-- Topbar --}}
                        <div class="hp-db-topbar">
                            <span class="hp-db-week-pill">Semana 3 — Fuerza</span>
                            <div class="hp-db-avatar">D</div>
                        </div>
                        {{-- Body: sidebar + main --}}
                        <div class="hp-db-body">
                            {{-- Sidebar --}}
                            <div class="hp-db-sidebar">
                                <div class="hp-db-logo">W/CORE</div>
                                <div class="hp-db-sect">ENTR.</div>
                                <div class="hp-db-item hp-db-active">Dashboard</div>
                                <div class="hp-db-item">Mi Plan</div>
                                <div class="hp-db-item">Entren.</div>
                                <div class="hp-db-sect">PROG.</div>
                                <div class="hp-db-item">Métricas</div>
                                <div class="hp-db-item">Logros</div>
                                <div class="hp-db-sect">SOCIAL</div>
                                <div class="hp-db-item">Chat</div>
                            </div>
                            {{-- Main content --}}
                            <div class="hp-db-main">
                                {{-- Hero welcome card --}}
                                <div class="hp-db-hero">
                                    <div class="hp-db-orb" aria-hidden="true"></div>
                                    <div class="hp-db-greeting">Buenas noches, Daniela</div>
                                    <div class="hp-db-meta">
                                        <span class="hp-db-plan-tag">Plan Método · Activo</span>
                                        <span class="hp-db-streak">🔥 14 días</span>
                                    </div>
                                    <div class="hp-db-quote">"La excelencia no es un evento, es un hábito que defiendes cada día."</div>
                                </div>
                                {{-- Plan activo card --}}
                                <div class="hp-db-card hp-db-card-plan">
                                    <span class="hp-db-ci">✓</span>
                                    <span class="hp-db-ct">Plan activo — Día <strong>37</strong></span>
                                    <span class="hp-db-dot-g"></span>
                                </div>
                                {{-- Stats row con colores de borde superior --}}
                                <div class="hp-db-stats">
                                    <div class="hp-db-stat" style="--sc:#F59E0B">
                                        <div class="hp-dbs-top">RACHA 🔥</div>
                                        <div class="hp-dbs-val">14</div>
                                        <div class="hp-dbs-sub">días consec.</div>
                                    </div>
                                    <div class="hp-db-stat" style="--sc:#10B981">
                                        <div class="hp-dbs-top">CHECK-INS ✓</div>
                                        <div class="hp-dbs-val">6</div>
                                        <div class="hp-dbs-sub">este mes</div>
                                    </div>
                                    <div class="hp-db-stat" style="--sc:#A78BFA">
                                        <div class="hp-dbs-top">NIVEL 3 ★</div>
                                        <div class="hp-dbs-val">520</div>
                                        <div class="hp-dbs-sub">XP total</div>
                                    </div>
                                    <div class="hp-db-stat" style="--sc:#F59E0B">
                                        <div class="hp-dbs-top">ESTA SEM.</div>
                                        <div class="hp-dbs-val">4<span class="hp-dbs-slash">/7</span></div>
                                        <div class="hp-dbs-sub">días entren.</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Phone overlay (solo en pantallas ≥ 1280px) --}}
                <div class="hp-phone-overlay">
                    <div class="hp-hero-phone hp-hero-phone-sm">
                        <picture>
                            <source srcset="{{ asset('images/hero/dashboard-mobile.avif') }}" type="image/avif">
                            <img src="{{ asset('images/hero/dashboard-mobile.webp') }}"
                                 alt="" width="180" height="370"
                                 loading="eager" fetchpriority="high">
                        </picture>
                    </div>
                </div>

                {{-- Chip flotante: Check-in --}}
                <div class="hp-float-chip hp-fc-checkin">
                    <span class="hp-fc-dot" style="background:#10B981;box-shadow:0 0 6px rgba(16,185,129,.6)"></span>
                    <div>
                        <div class="hp-fc-title">Check-in enviado ✓</div>
                        <div class="hp-fc-sub">Coach respondió en 20 min</div>
                    </div>
                </div>

            </div>

        </div>
    </section>

    <x-social-proof-bar />

    <div class="section-divider"></div>

    {{-- ╔══════════════════════════════════════════════════════════╗ --}}
    {{-- ║  SOCIAL PROOF BAR v3 — Stats + Certifications           ║ --}}
    {{-- ╚══════════════════════════════════════════════════════════╝ --}}
    <section class="hp-sp-bar">
        <div class="hp-wrap">
            <div class="hp-sp-inner">
                <div class="hp-sp-item">
                    <p class="hp-sp-num"><em>500</em>+</p>
                    <p class="hp-sp-label">{{ __('home.community_stat_logros') }}</p>
                </div>
                <div class="hp-sp-div" aria-hidden="true"></div>
                <div class="hp-sp-item">
                    <p class="hp-sp-num"><em>94</em>%</p>
                    <p class="hp-sp-label">{{ __('home.why_stat1_label') }}</p>
                </div>
                <div class="hp-sp-div" aria-hidden="true"></div>
                <div class="hp-sp-item">
                    <p class="hp-sp-num"><em>20</em>+</p>
                    <p class="hp-sp-label">{{ __('home.community_stat_miembros') }}</p>
                </div>
                <div class="hp-sp-div" aria-hidden="true"></div>
                <div class="hp-sp-item">
                    <p class="hp-sp-num text-base font-display font-bold leading-tight tracking-wide">
                        ENTRENAMIENTO<br>
                        <span class="text-sm opacity-70">· NUTRICIÓN · SEGUIMIENTO</span>
                    </p>
                    <p class="hp-sp-label">MÉTODO INTEGRAL</p>
                </div>
            </div>
        </div>
    </section>

    <div class="section-divider"></div>

    {{-- ================================================================== --}}
    {{-- 4. WHY WELLCORE                                                    --}}
    {{-- ================================================================== --}}
    <section class="hp-sec hp-why">
        <div class="hp-wrap">
            <p class="hp-eyebrow">{{ __('home.why_eyebrow') }}</p>
            <h2 class="hp-h2 mt-4">{{ __('home.why_title') }}</h2>
            <p class="hp-lead mt-4">{{ __('home.why_subtitle') }}</p>

            {{-- Stats grid --}}
            <div class="mt-12 grid grid-cols-2 gap-4 sm:grid-cols-4">
                <div class="rounded-xl border border-wc-accent/30 bg-wc-accent/5 p-5 text-center">
                    <p class="counter-highlight font-data text-3xl font-bold text-wc-accent"><span data-counter="94" data-counter-suffix="%">0%</span></p>
                    <p class="mt-1 text-sm font-semibold text-wc-text">{{ __('home.why_stat1_label') }}</p>
                    <p class="mt-1 text-xs text-wc-text-tertiary">{{ __('home.why_stat1_desc') }}</p>
                </div>
                <div class="rounded-xl border border-wc-border bg-wc-bg-secondary p-5 text-center">
                    <p class="counter-highlight font-data text-3xl font-bold text-wc-text"><span data-counter="20" data-counter-suffix="+">0</span></p>
                    <p class="mt-1 text-sm font-semibold text-wc-text">{{ __('home.why_stat2_label') }}</p>
                    <p class="mt-1 text-xs text-wc-text-tertiary">{{ __('home.why_stat2_desc') }}</p>
                </div>
                <div class="rounded-xl border border-wc-border bg-wc-bg-secondary p-5 text-center">
                    <p class="counter-highlight font-data text-3xl font-bold text-wc-text"><span data-counter="8" data-counter-suffix="{{ __('home.why_stat3_suffix') }}">0</span></p>
                    <p class="mt-1 text-sm font-semibold text-wc-text">{{ __('home.why_stat3_label') }}</p>
                    <p class="mt-1 text-xs text-wc-text-tertiary">{{ __('home.why_stat3_desc') }}</p>
                </div>
                <div class="rounded-xl border border-wc-border bg-wc-bg-secondary p-5 text-center">
                    <p class="counter-highlight font-data text-3xl font-bold text-wc-text"><span data-counter="100" data-counter-suffix="%">0%</span></p>
                    <p class="mt-1 text-sm font-semibold text-wc-text">{{ __('home.why_stat4_label') }}</p>
                    <p class="mt-1 text-xs text-wc-text-tertiary">{{ __('home.why_stat4_desc') }}</p>
                </div>
            </div>

            {{-- 3 Pillars --}}
            <div class="hp-why-grid">
                @foreach([
                    ['01', __('home.pillar1_title'), __('home.pillar1_desc'), 'M9.75 3.104v5.714a2.25 2.25 0 0 1-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 0 1 4.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0 1 12 15a9.065 9.065 0 0 0-6.23.693L5 14.5m14.8.8 1.402 1.402c1.232 1.232.65 3.318-1.067 3.611A48.309 48.309 0 0 1 12 21c-2.773 0-5.491-.235-8.135-.687-1.718-.293-2.3-2.379-1.067-3.61L5 14.5'],
                    ['02', __('home.pillar2_title'), __('home.pillar2_desc'), 'M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z'],
                    ['03', __('home.pillar3_title'), __('home.pillar3_desc'), 'M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342'],
                ] as [$num, $title, $desc, $iconPath])
                <div class="hp-why-card">
                    <div class="hp-why-icon">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $iconPath }}" /></svg>
                    </div>
                    <h3 class="hp-why-title">{{ $title }}</h3>
                    <p class="hp-why-desc">{{ $desc }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    <div class="section-divider"></div>

    {{-- ================================================================== --}}
    {{-- 5. COMMUNITY                                                       --}}
    {{-- ================================================================== --}}
    <section class="hp-sec hp-com">
        <div class="hp-wrap">
            <p class="hp-eyebrow">{{ __('home.community_eyebrow') }}</p>
            <h2 class="hp-h2 mt-4">{{ __('home.community_title') }}</h2>
            <p class="hp-lead mt-4">{{ __('home.community_subtitle') }}</p>

            {{-- Community stats --}}
            <div class="hp-com-stats mt-10">
                <div class="hp-com-stat">
                    <p class="hp-com-stat-num"><em>20</em>+</p>
                    <p class="hp-com-stat-label">{{ __('home.community_stat_miembros') }}</p>
                </div>
                <div class="hp-com-stat">
                    <p class="hp-com-stat-num"><em>94</em>%</p>
                    <p class="hp-com-stat-label">{{ __('home.community_stat_adherencia') }}</p>
                </div>
                <div class="hp-com-stat">
                    <p class="hp-com-stat-num"><em>3</em></p>
                    <p class="hp-com-stat-label">{{ __('home.community_stat_retos') }}</p>
                </div>
                <div class="hp-com-stat">
                    <p class="hp-com-stat-num"><em>500</em>+</p>
                    <p class="hp-com-stat-label">{{ __('home.community_stat_logros') }}</p>
                </div>
            </div>

            {{-- 3 feature cards --}}
            <div class="mt-12 grid grid-cols-1 gap-6 md:grid-cols-3">
                {{-- Activity Feed --}}
                <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-6">
                    <div class="flex items-center gap-2">
                        <svg class="h-5 w-5 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" /></svg>
                        <h3 class="text-base font-semibold text-wc-text">{{ __('home.community_feed_title') }}</h3>
                    </div>
                    <div class="mt-4 space-y-3">
                        <div class="flex items-center gap-3 rounded-lg bg-wc-bg p-3">
                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-emerald-400/10">
                                <svg class="h-4 w-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-wc-text">{{ __('home.community_feed_maria') }}</p>
                                <p class="text-[10px] text-wc-text-tertiary">{{ __('home.community_feed_maria_time') }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 rounded-lg bg-wc-bg p-3">
                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-amber-400/10">
                                <svg class="h-4 w-4 text-amber-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 0 1 3 3h-15a3 3 0 0 1 3-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.007 0H9.497m5.007 0a7.454 7.454 0 0 1-.982-3.172M9.497 14.25a7.454 7.454 0 0 0 .981-3.172M5.25 4.236c-.982.143-1.954.317-2.916.52A6.003 6.003 0 0 0 7.73 9.728M5.25 4.236V4.5c0 2.108.966 3.99 2.48 5.228M5.25 4.236V2.721C7.456 2.41 9.71 2.25 12 2.25c2.291 0 4.545.16 6.75.47v1.516M18.75 4.236c.982.143 1.954.317 2.916.52A6.003 6.003 0 0 1 16.27 9.728M18.75 4.236V4.5c0 2.108-.966 3.99-2.48 5.228m0 0a6.023 6.023 0 0 1-2.77.672 6.023 6.023 0 0 1-2.77-.672" /></svg>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-wc-text">{{ __('home.community_feed_juan') }}</p>
                                <p class="text-[10px] text-wc-text-tertiary">{{ __('home.community_feed_juan_time') }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 rounded-lg bg-wc-bg p-3">
                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-wc-accent/10">
                                <svg class="h-4 w-4 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18 9 11.25l4.306 4.306a11.95 11.95 0 0 1 5.814-5.518l2.74-1.22m0 0-5.94-2.281m5.94 2.28-2.28 5.941" /></svg>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-wc-text">{{ __('home.community_feed_carlos') }}</p>
                                <p class="text-[10px] text-wc-text-tertiary">{{ __('home.community_feed_carlos_time') }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Challenges --}}
                <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-6">
                    <div class="flex items-center gap-2">
                        <svg class="h-5 w-5 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.362 5.214A8.252 8.252 0 0 1 12 21 8.25 8.25 0 0 1 6.038 7.047 8.287 8.287 0 0 0 9 9.601a8.983 8.983 0 0 1 3.361-6.867 8.21 8.21 0 0 0 3 2.48Z" /></svg>
                        <h3 class="text-base font-semibold text-wc-text">{{ __('home.community_challenges_title') }}</h3>
                    </div>
                    <div class="mt-4 space-y-3">
                        <div class="rounded-lg border border-wc-border bg-wc-bg p-3">
                            <div class="flex items-center justify-between">
                                <p class="text-xs font-medium text-wc-text">{{ __('home.community_reto1_name') }}</p>
                                <span class="rounded-full bg-emerald-400/10 px-2 py-0.5 text-[10px] font-semibold text-emerald-400">{{ __('home.community_reto1_active') }}</span>
                            </div>
                            <p class="mt-1 text-[10px] text-wc-text-tertiary">{{ __('home.community_reto1_meta') }}</p>
                        </div>
                        <div class="rounded-lg border border-wc-border bg-wc-bg p-3">
                            <div class="flex items-center justify-between">
                                <p class="text-xs font-medium text-wc-text">{{ __('home.community_reto2_name') }}</p>
                                <span class="rounded-full bg-emerald-400/10 px-2 py-0.5 text-[10px] font-semibold text-emerald-400">{{ __('home.community_reto2_active') }}</span>
                            </div>
                            <p class="mt-1 text-[10px] text-wc-text-tertiary">{{ __('home.community_reto2_meta') }}</p>
                        </div>
                        <div class="rounded-lg border border-wc-border bg-wc-bg p-3">
                            <div class="flex items-center justify-between">
                                <p class="text-xs font-medium text-wc-text">{{ __('home.community_reto3_name') }}</p>
                                <span class="rounded-full bg-amber-400/10 px-2 py-0.5 text-[10px] font-semibold text-amber-400">{{ __('home.community_reto3_badge') }}</span>
                            </div>
                            <p class="mt-1 text-[10px] text-wc-text-tertiary">{{ __('home.community_reto3_meta') }}</p>
                        </div>
                    </div>
                </div>

                {{-- Gamification --}}
                <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-6">
                    <div class="flex items-center gap-2">
                        <svg class="h-5 w-5 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z" /></svg>
                        <h3 class="text-base font-semibold text-wc-text">{{ __('home.community_gamification_title') }}</h3>
                    </div>
                    <div class="mt-4 space-y-3">
                        <div class="rounded-lg border border-wc-border bg-wc-bg p-3">
                            <p class="text-xs font-medium text-wc-text">{{ __('home.community_xp_title') }}</p>
                            <p class="mt-1 text-[10px] text-wc-text-tertiary">{{ __('home.community_xp_desc') }}</p>
                            <div class="mt-2 flex gap-1">
                                <span class="rounded bg-wc-accent/10 px-1.5 py-0.5 text-[10px] font-semibold text-wc-accent">{{ __('home.community_xp_checkin') }}</span>
                                <span class="rounded bg-wc-accent/10 px-1.5 py-0.5 text-[10px] font-semibold text-wc-accent">{{ __('home.community_xp_racha') }}</span>
                            </div>
                        </div>
                        <div class="rounded-lg border border-wc-border bg-wc-bg p-3">
                            <p class="text-xs font-medium text-wc-text">{{ __('home.community_badges_title') }}</p>
                            <div class="mt-2 flex gap-2">
                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-amber-400/10 text-xs">
                                    <svg class="h-4 w-4 text-amber-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 0 1 3 3h-15a3 3 0 0 1 3-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.007 0H9.497m5.007 0a7.454 7.454 0 0 1-.982-3.172M9.497 14.25a7.454 7.454 0 0 0 .981-3.172M5.25 4.236c-.982.143-1.954.317-2.916.52A6.003 6.003 0 0 0 7.73 9.728M5.25 4.236V4.5c0 2.108.966 3.99 2.48 5.228M5.25 4.236V2.721C7.456 2.41 9.71 2.25 12 2.25c2.291 0 4.545.16 6.75.47v1.516M18.75 4.236c.982.143 1.954.317 2.916.52A6.003 6.003 0 0 1 16.27 9.728M18.75 4.236V4.5c0 2.108-.966 3.99-2.48 5.228m0 0a6.023 6.023 0 0 1-2.77.672 6.023 6.023 0 0 1-2.77-.672" /></svg>
                                </div>
                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-emerald-400/10 text-xs">
                                    <svg class="h-4 w-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.362 5.214A8.252 8.252 0 0 1 12 21 8.25 8.25 0 0 1 6.038 7.047 8.287 8.287 0 0 0 9 9.601a8.983 8.983 0 0 1 3.361-6.867 8.21 8.21 0 0 0 3 2.48Z" /></svg>
                                </div>
                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-wc-accent/10 text-xs">
                                    <svg class="h-4 w-4 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z" /></svg>
                                </div>
                            </div>
                        </div>
                        <div class="rounded-lg border border-wc-border bg-wc-bg p-3">
                            <p class="text-xs font-medium text-wc-text">{{ __('home.community_leaderboard_title') }}</p>
                            <p class="mt-1 text-[10px] text-wc-text-tertiary">{{ __('home.community_leaderboard_desc') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="section-divider"></div>

    {{-- ================================================================== --}}
    {{-- 6. COMO FUNCIONA                                                   --}}
    {{-- ================================================================== --}}
    <section class="hp-sec hp-phases">
        <div class="hp-wrap">
            <p class="hp-eyebrow">{{ __('home.process_eyebrow') }}</p>
            <h2 class="hp-h2 mt-4">{{ __('home.process_title') }}</h2>
            <p class="hp-lead mt-4">{{ __('home.process_subtitle') }}</p>

            <div class="hp-phases-grid">
                @foreach([
                    [__('home.process_step1_num'), __('home.process_step1_title'), __('home.process_step1_time'), __('home.process_step1_desc'), 'M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z'],
                    [__('home.process_step2_num'), __('home.process_step2_title'), __('home.process_step2_time'), __('home.process_step2_desc'), 'M3.75 3v11.25A2.25 2.25 0 0 0 6 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0 1 18 16.5h-2.25m-7.5 0h7.5m-7.5 0-1 3m8.5-3 1 3m0 0 .5 1.5m-.5-1.5h-9.5m0 0-.5 1.5m.75-9 3-3 2.148 2.148A12.061 12.061 0 0 1 16.5 7.605'],
                    [__('home.process_step3_num'), __('home.process_step3_title'), __('home.process_step3_time'), __('home.process_step3_desc'), 'M15.362 5.214A8.252 8.252 0 0 1 12 21 8.25 8.25 0 0 1 6.038 7.047 8.287 8.287 0 0 0 9 9.601a8.983 8.983 0 0 1 3.361-6.867 8.21 8.21 0 0 0 3 2.48Z'],
                    [__('home.process_step4_num'), __('home.process_step4_title'), __('home.process_step4_time'), __('home.process_step4_desc'), 'M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z'],
                ] as [$num, $title, $time, $desc, $iconPath])
                <div class="hp-phase-card">
                    <span class="hp-phase-num">{{ $num }}</span>
                    <div class="hp-phase-icon">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $iconPath }}" /></svg>
                    </div>
                    <div>
                        <h3 class="hp-phase-title">{{ $title }}</h3>
                        <p class="hp-phase-desc mt-1">{{ $time }}</p>
                    </div>
                    <p class="hp-phase-desc">{{ $desc }}</p>
                </div>
                @endforeach
            </div>

            <div class="mt-10 text-center">
                <a href="{{ route('proceso') }}" aria-label="Ver proceso completo WellCore" class="inline-flex items-center gap-2 text-sm font-semibold text-wc-accent hover:text-wc-accent-hover">
                    {{ __('home.process_ver_completo') }}
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" /></svg>
                </a>
            </div>
        </div>
    </section>

    <div class="section-divider"></div>

    {{-- ================================================================== --}}
    {{-- 7. PLANS                                                           --}}
    {{-- ================================================================== --}}
    <section class="hp-sec hp-plan">
        <div class="hp-wrap">
            <p class="hp-eyebrow">{{ __('home.plans_eyebrow') }}</p>
            <h2 class="hp-h2 mt-4">{{ __('home.plans_title') }}</h2>
            <p class="hp-lead mt-4">{{ __('home.plans_subtitle') }}</p>

            <div class="hp-plan-grid">
                {{-- ESENCIAL --}}
                <div class="hp-plan-card">
                    <div>
                        <p class="hp-plan-name">{{ __('home.plan_esencial_name') }}</p>
                        <p class="hp-plan-price mt-3">$299,000 <span class="hp-plan-price-period">{{ __('home.plan_cop_mes') }}</span></p>
                        <p class="mt-1 font-data text-xs text-wc-text-tertiary">≈ <span class="font-semibold">USD $73</span> / mes <span class="opacity-60">(aprox.)</span></p>
                    </div>
                    <ul class="hp-plan-features">
                        @foreach([
                            [true, __('home.feat_entrenamiento_personalizado')],
                            [true, __('home.feat_portal_cliente')],
                            [true, __('home.feat_evaluacion_inicial')],
                            [true, __('home.feat_biblioteca_ejercicios')],
                            [true, __('home.feat_seguimiento_metricas')],
                            [true, __('home.feat_mediciones_corporales')],
                            [true, __('home.feat_comunidad_chat')],
                            [true, __('home.feat_ajuste_mensual')],
                            [true, __('home.feat_soporte_48h')],
                            [false, __('home.feat_nutricion_personalizada')],
                            [false, __('home.feat_checkin_semanal')],
                        ] as [$included, $feature])
                        <li class="hp-plan-feature {{ $included ? '' : 'opacity-40' }}">
                            @if($included)
                                <svg class="hp-plan-feature-icon h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                                {{ $feature }}
                            @else
                                <svg class="h-4 w-4 shrink-0 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                                <span class="line-through">{{ $feature }}</span>
                            @endif
                        </li>
                        @endforeach
                    </ul>
                    <a href="{{ route('inscripcion') }}?plan=esencial" class="hp-plan-cta btn-press">
                        {{ __('home.plan_cta_esencial') }}
                    </a>
                </div>

                {{-- MÉTODO --}}
                <div class="hp-plan-card hp-plan-feat">
                    <span class="hp-plan-badge">{{ __('home.plan_mejor_valor') }}</span>
                    <div>
                        <p class="hp-plan-name">{{ __('home.plan_metodo_name') }}</p>
                        <p class="hp-plan-price mt-3">$399,000 <span class="hp-plan-price-period">{{ __('home.plan_cop_mes') }}</span></p>
                        <p class="mt-1 font-data text-xs text-wc-text-tertiary">≈ <span class="font-semibold">USD $97</span> / mes <span class="opacity-60">(aprox.)</span></p>
                    </div>
                    <ul class="hp-plan-features">
                        @foreach([
                            [true, __('home.feat_todo_esencial')],
                            [true, __('home.feat_nutricion_100')],
                            [true, __('home.feat_macros_calorias')],
                            [true, __('home.feat_recetas_adaptadas')],
                            [true, __('home.feat_guia_habitos')],
                            [true, __('home.feat_seguimiento_sueno')],
                            [true, __('home.feat_reporte_mensual')],
                            [true, __('home.feat_ajuste_quincenal')],
                            [true, __('home.feat_soporte_24h')],
                            [false, __('home.feat_checkin_semanal')],
                            [false, __('home.feat_videollamada_mensual')],
                        ] as [$included, $feature])
                        <li class="hp-plan-feature {{ $included ? '' : 'opacity-40' }}">
                            @if($included)
                                <svg class="hp-plan-feature-icon h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                                {{ $feature }}
                            @else
                                <svg class="h-4 w-4 shrink-0 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                                <span class="line-through">{{ $feature }}</span>
                            @endif
                        </li>
                        @endforeach
                    </ul>
                    <a href="{{ route('inscripcion') }}?plan=metodo" class="hp-plan-cta btn-press">
                        {{ __('home.plan_cta_metodo') }}
                    </a>
                </div>

                {{-- ELITE --}}
                <div class="hp-plan-card">
                    <div>
                        <p class="hp-plan-name">{{ __('home.plan_elite_name') }}</p>
                        <p class="hp-plan-price mt-3">$549,000 <span class="hp-plan-price-period">{{ __('home.plan_cop_mes') }}</span></p>
                        <p class="mt-1 font-data text-xs text-wc-text-tertiary">≈ <span class="font-semibold">USD $134</span> / mes <span class="opacity-60">(aprox.)</span></p>
                    </div>
                    <ul class="hp-plan-features">
                        @foreach([
                            [true, __('home.feat_todo_metodo')],
                            [true, __('home.feat_checkin_semanal')],
                            [true, __('home.feat_videollamada_mensual')],
                            [true, __('home.feat_ajuste_semanal')],
                            [true, __('home.feat_linea_whatsapp')],
                            [true, __('home.feat_analisis_composicion')],
                            [true, __('home.feat_estrategia_suplementacion')],
                            [true, __('home.feat_ciclo_hormonal')],
                            [true, __('home.feat_bloodwork')],
                            [true, __('home.feat_plan_viaje')],
                            [true, __('home.feat_soporte_8h')],
                        ] as [$included, $feature])
                        <li class="hp-plan-feature">
                            <svg class="hp-plan-feature-icon h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                            {{ $feature }}
                        </li>
                        @endforeach
                    </ul>
                    <a href="{{ route('inscripcion') }}?plan=elite" class="hp-plan-cta btn-press">
                        {{ __('home.plan_cta_elite') }}
                    </a>
                </div>
            </div>

            <p class="mt-8 text-center text-xs text-wc-text-tertiary">{{ __('home.plan_cancelacion_nota') }}</p>
        </div>
    </section>

    <div class="section-divider"></div>

    {{-- ================================================================== --}}
    {{-- 8. RESULTS / TESTIMONIALS                                          --}}
    {{-- ================================================================== --}}
    <section class="bg-wc-bg-tertiary cv-auto">
        <div class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8" data-animate="slideInLeft">
            <p class="text-xs font-semibold uppercase tracking-widest text-wc-accent">{{ __('home.testimonials_eyebrow') }}</p>
            <h2 class="mt-3 font-display text-3xl tracking-wide text-wc-text sm:text-4xl">{{ __('home.testimonials_title') }}</h2>
            <p class="mt-4 max-w-2xl text-sm text-wc-text-tertiary">{{ __('home.testimonials_subtitle') }}</p>

            <div class="stagger-grid mt-12 grid grid-cols-1 gap-8 md:grid-cols-3">
                @foreach([
                    [__('home.testimonial1_initials'), __('home.testimonial1_name'), __('home.testimonial1_stat'), __('home.testimonial1_duration'), __('home.testimonial1_plan'), __('home.testimonial1_quote'), 100],
                    [__('home.testimonial2_initials'), __('home.testimonial2_name'), __('home.testimonial2_stat'), __('home.testimonial2_duration'), __('home.testimonial2_plan'), __('home.testimonial2_quote'), 200],
                    [__('home.testimonial3_initials'), __('home.testimonial3_name'), __('home.testimonial3_stat'), __('home.testimonial3_duration'), __('home.testimonial3_plan'), __('home.testimonial3_quote'), 300],
                ] as [$initials, $name, $stat, $duration, $plan, $quote, $delay])
                <div class="scroll-reveal card-hover-lift rounded-xl border border-wc-border bg-wc-bg p-8" data-animate="fadeInUp" data-animate-delay="{{ $delay }}">
                    {{-- Before/After interactive slider --}}
                    <x-before-after-slider height="h-44" />
                    {{-- Stat badges --}}
                    <div class="mt-4 flex flex-wrap gap-2">
                        <span class="rounded-full bg-wc-accent/10 px-3 py-1 text-xs font-semibold text-wc-accent">{{ $stat }}</span>
                        <span class="rounded-full bg-wc-bg-tertiary px-3 py-1 text-xs text-wc-text-secondary">{{ $duration }}</span>
                        <span class="rounded-full bg-wc-bg-tertiary px-3 py-1 text-xs text-wc-text-secondary">{{ $plan }}</span>
                    </div>
                    {{-- Stars --}}
                    <div class="mt-4 flex gap-1">
                        @for ($i = 0; $i < 5; $i++)
                            <svg class="h-4 w-4 text-wc-accent" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 0 0 .95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 0 0-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 0 0-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 0 0-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 0 0 .951-.69l1.07-3.292z"/></svg>
                        @endfor
                    </div>
                    {{-- Quote --}}
                    <p class="mt-4 text-sm text-wc-text-secondary">"{{ $quote }}"</p>
                    {{-- Author --}}
                    <div class="mt-6 flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-wc-accent/10">
                            <span class="text-sm font-semibold text-wc-accent">{{ $initials }}</span>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-wc-text">{{ $name }}</p>
                            <p class="text-xs text-wc-text-tertiary">{{ $stat }} &middot; {{ $plan }}</p>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    <div class="section-divider"></div>

    {{-- ================================================================== --}}
    {{-- 9. COACHES                                                         --}}
    {{-- ================================================================== --}}
    <section class="bg-wc-bg cv-auto">
        <div class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8" data-animate="slideInLeft">
            <div class="grid grid-cols-1 items-center gap-12 lg:grid-cols-2">
                {{-- Left --}}
                <div>
                    <p class="text-xs font-semibold uppercase tracking-widest text-wc-accent">{{ __('home.coaches_eyebrow') }}</p>
                    <h2 class="mt-3 font-display text-3xl tracking-wide text-wc-text sm:text-4xl">{{ __('home.coaches_title') }}</h2>
                    <p class="mt-4 max-w-xl text-sm text-wc-text-tertiary">{{ __('home.coaches_subtitle') }}</p>

                    <div class="mt-6 flex flex-wrap gap-4 text-sm text-wc-text-secondary">
                        <span class="flex items-center gap-1.5">
                            <svg class="h-4 w-4 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                            {{ __('home.coaches_req1') }}
                        </span>
                        <span class="flex items-center gap-1.5">
                            <svg class="h-4 w-4 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                            {{ __('home.coaches_req2') }}
                        </span>
                        <span class="flex items-center gap-1.5">
                            <svg class="h-4 w-4 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                            {{ __('home.coaches_req3') }}
                        </span>
                    </div>

                    <div class="mt-8">
                        <a href="{{ route('coaches') }}" class="btn-press inline-flex items-center gap-2 rounded-lg bg-wc-accent px-8 py-3 text-base font-medium text-white hover:bg-wc-accent-hover">
                            {{ __('home.coaches_cta') }}
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" /></svg>
                        </a>
                    </div>
                </div>

                {{-- Right — Coach Portal Mockup --}}
                <div class="hidden lg:block" data-animate="slideInRight">
                    <div class="rounded-xl border border-wc-border bg-wc-bg shadow-2xl shadow-black/10">
                        {{-- Browser chrome --}}
                        <div class="flex items-center gap-2 border-b border-wc-border px-4 py-3">
                            <span class="h-3 w-3 rounded-full bg-red-500"></span>
                            <span class="h-3 w-3 rounded-full bg-yellow-500"></span>
                            <span class="h-3 w-3 rounded-full bg-green-500"></span>
                            <div class="ml-3 flex-1 rounded-md bg-wc-bg-secondary px-3 py-1">
                                <span class="text-xs text-wc-text-tertiary">coach.wellcorefitness.com</span>
                            </div>
                        </div>
                        {{-- Coach dashboard --}}
                        <div class="p-4">
                            {{-- Stats row --}}
                            <div class="grid grid-cols-3 gap-2">
                                <div class="rounded-lg border border-wc-border bg-wc-bg-tertiary p-3 text-center">
                                    <p class="font-data text-xl font-bold text-wc-accent">18</p>
                                    <p class="text-[10px] text-wc-text-tertiary">{{ __('home.coach_mockup_clientes') }}</p>
                                </div>
                                <div class="rounded-lg border border-wc-border bg-wc-bg-tertiary p-3 text-center">
                                    <p class="font-data text-xl font-bold text-emerald-400">$5.7M</p>
                                    <p class="text-[10px] text-wc-text-tertiary">{{ __('home.coach_mockup_mes') }}</p>
                                </div>
                                <div class="rounded-lg border border-wc-border bg-wc-bg-tertiary p-3 text-center">
                                    <p class="font-data text-xl font-bold text-wc-text">91%</p>
                                    <p class="text-[10px] text-wc-text-tertiary">{{ __('home.coach_mockup_adherencia') }}</p>
                                </div>
                            </div>
                            {{-- Client list --}}
                            <div class="mt-3 rounded-lg border border-wc-border bg-wc-bg-tertiary p-3">
                                <p class="text-xs font-semibold text-wc-text">{{ __('home.coach_mockup_activos') }}</p>
                                <div class="mt-2 space-y-2">
                                    @foreach([
                                        ['Maria G.', '88%', 'text-amber-400'],
                                        ['Juan R.', '94%', 'text-emerald-400'],
                                        ['Andrea M.', '100%', 'text-emerald-400'],
                                    ] as [$clientName, $clientAdherence, $color])
                                    <div class="flex items-center justify-between rounded bg-wc-bg px-2 py-1.5">
                                        <div class="flex items-center gap-2">
                                            <div class="h-5 w-5 rounded-full bg-wc-accent/10"></div>
                                            <span class="text-[11px] text-wc-text-secondary">{{ $clientName }}</span>
                                        </div>
                                        <span class="font-data text-[11px] font-semibold {{ $color }}">{{ $clientAdherence }}</span>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="section-divider"></div>

    {{-- ================================================================== --}}
    {{-- 10. BLOG PREVIEW                                                   --}}
    {{-- ================================================================== --}}
    @php
        $articles = array_slice(\App\Http\Controllers\BlogController::getArticles(), 0, 3);
    @endphp
    <section class="bg-wc-bg-tertiary cv-auto">
        <div class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8" data-animate="slideInLeft">
            <div class="flex items-end justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-widest text-wc-accent">{{ __('home.blog_eyebrow') }}</p>
                    <h2 class="mt-3 font-display text-3xl tracking-wide text-wc-text sm:text-4xl">{{ __('home.blog_title') }}</h2>
                </div>
                <a href="{{ route('blog.index') }}" aria-label="Ver todos los articulos del blog WellCore" class="hidden items-center gap-2 text-sm font-medium text-wc-accent hover:text-wc-accent-hover sm:inline-flex">
                    {{ __('home.blog_ver_todos') }}
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" /></svg>
                </a>
            </div>

            <div class="stagger-grid mt-12 grid grid-cols-1 gap-8 md:grid-cols-3">
                @foreach($articles as $index => $article)
                <a href="{{ route('blog.show', $article['slug']) }}" class="scroll-reveal card-hover-lift group rounded-xl border border-wc-border bg-wc-bg transition-colors hover:border-wc-accent/30" data-animate="fadeInUp" data-animate-delay="{{ ($index + 1) * 100 }}">
                    {{-- Image placeholder --}}
                    <div class="relative h-48 overflow-hidden rounded-t-xl bg-gradient-to-br from-wc-accent/10 via-wc-bg-tertiary to-wc-bg-secondary">
                        <div class="absolute left-4 top-4">
                            <span class="rounded-full bg-wc-accent/10 px-3 py-1 text-xs font-semibold text-wc-accent">{{ $article['category'] }}</span>
                        </div>
                    </div>
                    {{-- Content --}}
                    <div class="p-6">
                        <h3 class="text-base font-semibold text-wc-text group-hover:text-wc-accent">{{ $article['title'] }}</h3>
                        <p class="mt-2 line-clamp-2 text-sm text-wc-text-secondary">{{ $article['excerpt'] }}</p>
                        <div class="mt-4 flex items-center gap-3 text-xs text-wc-text-tertiary">
                            <span>{{ \Carbon\Carbon::parse($article['date'])->format('d M Y') }}</span>
                            <span>&middot;</span>
                            <span>{{ $article['reading_time'] }}</span>
                        </div>
                    </div>
                </a>
                @endforeach
            </div>

            <div class="mt-8 text-center sm:hidden">
                <a href="{{ route('blog.index') }}" aria-label="Ver todos los articulos del blog WellCore" class="inline-flex items-center gap-2 text-sm font-medium text-wc-accent hover:text-wc-accent-hover">
                    {{ __('home.blog_ver_todos') }}
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" /></svg>
                </a>
            </div>
        </div>
    </section>

    <div class="section-divider"></div>

    {{-- ================================================================== --}}
    {{-- 11. FAQ                                                            --}}
    {{-- ================================================================== --}}
    <section class="hp-sec hp-faq" x-data="{ active: null }">
        <div class="hp-wrap">
            <div class="text-center">
                <p class="text-xs font-semibold uppercase tracking-widest text-wc-accent">{{ __('home.faq_eyebrow') }}</p>
                <h2 class="hp-h2 mt-3">{{ __('home.faq_title') }}</h2>
            </div>

            <div class="hp-faq-list">
                @foreach([
                    [__('home.faq_q1'), __('home.faq_a1')],
                    [__('home.faq_q2'), __('home.faq_a2')],
                    [__('home.faq_q3'), __('home.faq_a3')],
                    [__('home.faq_q4'), __('home.faq_a4')],
                    [__('home.faq_q5'), __('home.faq_a5')],
                    [__('home.faq_q6'), __('home.faq_a6')],
                    [__('home.faq_q7'), __('home.faq_a7')],
                    [__('home.faq_q8'), __('home.faq_a8')],
                ] as $index => [$question, $answer])
                <div class="hp-faq-item">
                    <button
                        x-on:click="active = active === {{ $index }} ? null : {{ $index }}"
                        :aria-expanded="(active === {{ $index }}).toString()"
                        class="hp-faq-trigger">
                        {{ $question }}
                        <span class="hp-faq-icon" aria-hidden="true">+</span>
                    </button>
                    <div x-show="active === {{ $index }}" x-collapse x-cloak>
                        <div class="hp-faq-body">{{ $answer }}</div>
                    </div>
                </div>
                @endforeach
            </div>

        </div>
    </section>

    <div class="section-divider"></div>

    {{-- ================================================================== --}}
    {{-- 12. FINAL CTA                                                      --}}
    {{-- ================================================================== --}}
    <section class="hp-cta">
        <div class="hp-wrap">
            <div class="hp-cta-inner">
                <h2 class="hp-cta-h2">{{ __('home.cta_title') }}</h2>
                <p class="hp-cta-sub">{{ __('home.cta_subtitle') }}</p>
                <div class="flex flex-col items-center gap-4 sm:flex-row">
                    <a href="{{ route('planes') }}" class="hp-btn-white btn-press">
                        {{ __('home.cta_btn_planes') }}
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" /></svg>
                    </a>
                    <a href="{{ route('inscripcion') }}" class="btn-press inline-flex items-center justify-center rounded-full border-2 px-8 py-3.5 text-base font-semibold text-white transition-all" style="border-color:rgba(255,255,255,.35);hover:border-color:rgba(255,255,255,.7)">
                        {{ __('home.cta_btn_consulta') }}
                    </a>
                </div>
                <div class="flex flex-wrap items-center justify-center gap-x-6 gap-y-2 text-sm" style="color:rgba(255,255,255,.65)">
                    <span>{{ __('home.cta_trust1') }}</span>
                    <span class="hidden sm:inline" style="opacity:.4">&middot;</span>
                    <span>{{ __('home.cta_trust2') }}</span>
                    <span class="hidden sm:inline" style="opacity:.4">&middot;</span>
                    <span>{{ __('home.cta_trust3') }}</span>
                </div>
            </div>
        </div>
    </section>

</x-layouts.public>
