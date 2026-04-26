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

    {{-- 4. Product (3 planes) — precios desde config/plans.php (SSOT) --}}
    @foreach([
        ['esencial', 'Esencial', (int) config('plans.esencial.price_cop'), 'home.plan_esencial_name'],
        ['metodo', 'Método', (int) config('plans.metodo.price_cop'), 'home.plan_metodo_name'],
        ['elite', 'Elite', (int) config('plans.elite.price_cop'), 'home.plan_elite_name'],
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
                    <span class="rounded-full border border-wc-border px-3 py-1 text-xs text-wc-text-secondary">Esencial <span class="font-data font-semibold text-wc-text">$254k</span></span>
                    <span class="rounded-full border border-wc-border px-3 py-1 text-xs text-wc-text-secondary">Método <span class="font-data font-semibold text-wc-text">$339k</span></span>
                    <span class="rounded-full border border-wc-border px-3 py-1 text-xs text-wc-text-secondary">Elite <span class="font-data font-semibold text-wc-text">$466k</span></span>
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
    <section class="hp-sec hp-why hp-cv-section">
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

            {{-- 4 Pillars v3 --}}
            <div class="hp-why-grid">
                <div class="hp-why-card">
                    <div class="hp-why-icon-wrap"><span class="hp-why-ic-emoji">🏋️</span></div>
                    <h3 class="hp-why-title">Entrenamiento científico</h3>
                    <p class="hp-why-desc">Periodización basada en ciencia del deporte. Progresión de carga calculada semana a semana.</p>
                    <span class="hp-why-tag">NSCA Certified</span>
                </div>
                <div class="hp-why-card">
                    <div class="hp-why-icon-wrap"><span class="hp-why-ic-emoji">🥗</span></div>
                    <h3 class="hp-why-title">Nutrición personalizada</h3>
                    <p class="hp-why-desc">Macros calculados para tu cuerpo, objetivo y estilo de vida. No plantillas genéricas.</p>
                    <span class="hp-why-tag">ISSN Backed</span>
                </div>
                <div class="hp-why-card">
                    <div class="hp-why-icon-wrap"><span class="hp-why-ic-emoji">🤝</span></div>
                    <h3 class="hp-why-title">Coach humano real</h3>
                    <p class="hp-why-desc">No es una app, es una relación. Tu coach te conoce, ajusta tu plan y responde en menos de 24h.</p>
                    <span class="hp-why-tag">1 a 1 Dedicado</span>
                </div>
                <div class="hp-why-card">
                    <div class="hp-why-icon-wrap"><span class="hp-why-ic-emoji">📊</span></div>
                    <h3 class="hp-why-title">Métricas y evolución</h3>
                    <p class="hp-why-desc">Dashboard con racha, peso, fotos comparativas, XP y check-ins. Ves tu evolución con datos.</p>
                    <span class="hp-why-tag">Data Driven</span>
                </div>
            </div>
        </div>
    </section>

    <div class="section-divider"></div>

    {{-- ================================================================== --}}
    {{-- 5. COMMUNITY v3                                                   --}}
    {{-- ================================================================== --}}
    <section class="hp-sec hp-com-v3 hp-cv-section">
        <div class="hp-wrap">
            <div class="hp-com-strip">
                <div class="hp-com-orb" aria-hidden="true"></div>
                {{-- Left --}}
                <div class="hp-com-left">
                    <div class="hp-com-avs">
                        <div class="hp-com-av" style="background:#DC2626">S</div>
                        <div class="hp-com-av" style="background:#B91C1C">M</div>
                        <div class="hp-com-av" style="background:#7F1D1D">R</div>
                        <div class="hp-com-av" style="background:#991B1B">A</div>
                        <div class="hp-com-av hp-com-av-more">+836</div>
                    </div>
                    <h2 class="hp-com-title">Únete a la<br>comunidad</h2>
                    <p class="hp-com-sub">840+ profesionales de LATAM transformando su físico con ciencia. Retos, comunidad privada, logros y leaderboards.</p>
                </div>
                {{-- Right --}}
                <div class="hp-com-items">
                    <div class="hp-com-item">
                        <div class="hp-com-item-ic">🎯</div>
                        <div>
                            <div class="hp-com-item-t">Retos mensuales</div>
                            <div class="hp-com-item-s">Compite, gana puntos y sube al leaderboard.</div>
                        </div>
                    </div>
                    <div class="hp-com-item">
                        <div class="hp-com-item-ic">💬</div>
                        <div>
                            <div class="hp-com-item-t">Chat directo con tu coach</div>
                            <div class="hp-com-item-s">Respuesta garantizada en menos de 24h.</div>
                        </div>
                    </div>
                    <div class="hp-com-item">
                        <div class="hp-com-item-ic">🏆</div>
                        <div>
                            <div class="hp-com-item-t">Logros y XP</div>
                            <div class="hp-com-item-s">Sube de nivel, desbloquea badges y celebra PRs.</div>
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
    <section class="hp-sec hp-phases hp-cv-section">
        <div class="hp-wrap">
            <p class="hp-eyebrow">{{ __('home.process_eyebrow') }}</p>
            <h2 class="hp-h2 mt-4">{{ __('home.process_title') }}</h2>
            <p class="hp-lead mt-4">{{ __('home.process_subtitle') }}</p>

            <div class="hp-track">
                <div class="hp-phase">
                    <div class="hp-phase-num-wrap"><div class="hp-phase-num">01</div></div>
                    <span class="hp-phase-week">Semanas 1–3</span>
                    <div class="hp-phase-name">Adaptación</div>
                    <div class="hp-phase-desc-v3">Activación neuromuscular, ajuste de carga, establecer línea base.</div>
                </div>
                <div class="hp-phase">
                    <div class="hp-phase-num-wrap"><div class="hp-phase-num">02</div></div>
                    <span class="hp-phase-week">Semanas 4–6</span>
                    <div class="hp-phase-name">Volumen</div>
                    <div class="hp-phase-desc-v3">Acumulación muscular. Aumento progresivo de series y carga.</div>
                </div>
                <div class="hp-phase">
                    <div class="hp-phase-num-wrap"><div class="hp-phase-num">03</div></div>
                    <span class="hp-phase-week">Semanas 7–9</span>
                    <div class="hp-phase-name">Fuerza Máx.</div>
                    <div class="hp-phase-desc-v3">Intensificación: menos volumen, más peso. Picos de fuerza y nuevos PRs.</div>
                </div>
                <div class="hp-phase">
                    <div class="hp-phase-num-wrap"><div class="hp-phase-num">04</div></div>
                    <span class="hp-phase-week">Semanas 10–12</span>
                    <div class="hp-phase-name">Peak &amp; Recomp.</div>
                    <div class="hp-phase-desc-v3">Recomposición final, deload estratégico y evaluación de resultados.</div>
                </div>
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
    <section class="hp-sec hp-plan hp-cv-section">
        <div class="hp-wrap">
            <p class="hp-eyebrow">{{ __('home.plans_eyebrow') }}</p>
            <h2 class="hp-h2 mt-4">{{ __('home.plans_title') }}</h2>
            <p class="hp-lead mt-4">{{ __('home.plans_subtitle') }}</p>

            {{-- Banner Promo Abril --}}
            <div class="hp-plan-promo">
                <span class="hp-plan-promo-flame">🔥</span>
                <div class="hp-plan-promo-text">
                    <strong>Promoción Abril</strong>
                    <span>15% OFF en todos los planes · hasta el 30 de abril</span>
                </div>
            </div>

            <div class="hp-plan-grid">
                {{-- ESENCIAL --}}
                <div class="hp-plan-card">
                    <span class="hp-plan-discount-badge">-15%</span>
                    <div class="flex flex-col">
                        <p class="hp-plan-name">{{ __('home.plan_esencial_name') }}</p>
                        <p class="hp-plan-price-old">$299,000</p>
                        <p class="hp-plan-price mt-1">$254,150 <span class="hp-plan-price-period">{{ __('home.plan_cop_mes') }}</span></p>
                        <p class="mt-1 font-data text-xs text-wc-text-tertiary">≈ <span class="font-semibold">USD $62</span> / mes <span class="line-through opacity-90">USD $73</span></p>
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
                        <li class="hp-plan-feature {{ $included ? '' : 'opacity-80' }}">
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
                    <span class="hp-plan-discount-badge hp-plan-discount-badge-feat">-15%</span>
                    <div class="flex flex-col">
                        <p class="hp-plan-name">{{ __('home.plan_metodo_name') }}</p>
                        <p class="hp-plan-price-old">$399,000</p>
                        <p class="hp-plan-price mt-1">$339,150 <span class="hp-plan-price-period">{{ __('home.plan_cop_mes') }}</span></p>
                        <p class="mt-1 font-data text-xs text-wc-text-tertiary">≈ <span class="font-semibold">USD $82</span> / mes <span class="line-through opacity-90">USD $97</span></p>
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
                        <li class="hp-plan-feature {{ $included ? '' : 'opacity-80' }}">
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
                    <span class="hp-plan-discount-badge">-15%</span>
                    <div class="flex flex-col">
                        <p class="hp-plan-name">{{ __('home.plan_elite_name') }}</p>
                        <p class="hp-plan-price-old">$549,000</p>
                        <p class="hp-plan-price mt-1">$466,650 <span class="hp-plan-price-period">{{ __('home.plan_cop_mes') }}</span></p>
                        <p class="mt-1 font-data text-xs text-wc-text-tertiary">≈ <span class="font-semibold">USD $114</span> / mes <span class="line-through opacity-90">USD $134</span></p>
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
            <p class="text-xs font-semibold uppercase tracking-widest text-red-700 dark:text-red-400">{{ __('home.testimonials_eyebrow') }}</p>
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
                    <p class="text-xs font-semibold uppercase tracking-widest text-red-700 dark:text-red-400">{{ __('home.coaches_eyebrow') }}</p>
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
                                    <div class="flex items-center gap-2 rounded bg-wc-bg px-2 py-1.5">
                                        <div class="h-5 w-5 shrink-0 rounded-full bg-wc-accent/10"></div>
                                        <span class="flex-1 text-[11px] text-wc-text-secondary">{{ $clientName }}</span>
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
                    <p class="text-xs font-semibold uppercase tracking-widest text-red-700 dark:text-red-400">{{ __('home.blog_eyebrow') }}</p>
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
                        <span class="absolute left-4 top-4 rounded-full bg-wc-accent/10 px-3 py-1 text-xs font-semibold text-wc-accent">{{ $article['category'] }}</span>
                    </div>
                    {{-- Content --}}
                    <div class="p-6">
                        <h3 class="text-base font-semibold text-wc-text group-hover:text-wc-accent">{{ $article['title'] }}</h3>
                        <p class="mt-2 line-clamp-2 text-sm text-wc-text-secondary">{{ $article['excerpt'] }}</p>
                        <p class="mt-4 text-xs text-wc-text-tertiary">{{ \Carbon\Carbon::parse($article['date'])->format('d M Y') }} &middot; {{ $article['reading_time'] }}</p>
                    </div>
                </a>
                @endforeach
            </div>

            <a href="{{ route('blog.index') }}" aria-label="Ver todos los articulos del blog WellCore" class="mt-8 sm:hidden mx-auto flex w-fit items-center gap-2 text-sm font-medium text-wc-accent hover:text-wc-accent-hover">
                {{ __('home.blog_ver_todos') }}
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" /></svg>
            </a>
        </div>
    </section>

    <div class="section-divider"></div>

    {{-- ================================================================== --}}
    {{-- TRUST CARDS — entre pricing y FAQ                                   --}}
    {{-- ================================================================== --}}
    <section class="hp-sec hp-trust hp-cv-section">
        <div class="hp-wrap">
            <div class="hp-trust-grid">
                <div class="hp-trust-card">
                    <div class="hp-trust-ic">🛡️</div>
                    <div class="hp-trust-t">Garantía 30 días</div>
                    <div class="hp-trust-s">Si en los primeros 30 días no ves resultados, te devolvemos el 100%.</div>
                </div>
                <div class="hp-trust-card">
                    <div class="hp-trust-ic">🚫</div>
                    <div class="hp-trust-t">Sin contratos</div>
                    <div class="hp-trust-s">Cancela cuando quieras. Sin penalizaciones ni letra pequeña.</div>
                </div>
                <div class="hp-trust-card">
                    <div class="hp-trust-ic">👤</div>
                    <div class="hp-trust-t">Coach real, 1 a 1</div>
                    <div class="hp-trust-s">No una IA. Un coach certificado que conoce tu nombre e historial.</div>
                </div>
                <div class="hp-trust-card">
                    <div class="hp-trust-ic">🔬</div>
                    <div class="hp-trust-t">Ciencia publicada</div>
                    <div class="hp-trust-s">NSCA y ISSN. Cada protocolo con respaldo científico.</div>
                </div>
            </div>
        </div>
    </section>
    <div class="section-divider"></div>

    {{-- ================================================================== --}}
    {{-- 11. FAQ                                                            --}}
    {{-- ================================================================== --}}
    <section class="hp-sec hp-faq hp-cv-section" x-data="{ active: null }">
        <div class="hp-wrap">
            <div class="text-center">
                <p class="text-xs font-semibold uppercase tracking-widest text-red-700 dark:text-red-400">{{ __('home.faq_eyebrow') }}</p>
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
    <section class="hp-cta hp-cv-section">
        <div class="hp-wrap">
            <div class="hp-cta-inner hp-cta-v3">
                <div class="hp-cta-orb1" aria-hidden="true"></div>
                <div class="hp-cta-orb2" aria-hidden="true"></div>
                <h2 class="hp-cta-h2-v3">¿Listo para tu<br>transformación?</h2>
                <p class="hp-cta-sub-v3">840 personas ya comenzaron su cambio. Tu coach está esperando. Sin excusas, sin milagros — solo ciencia y trabajo.</p>
                <div class="flex flex-col items-center gap-4 sm:flex-row sm:justify-center">
                    <a href="{{ route('planes') }}" class="hp-btn-cta-primary btn-press">
                        {{ __('home.cta_btn_planes') }}
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" /></svg>
                    </a>
                    <a href="{{ route('inscripcion') }}" class="hp-btn-cta-ghost btn-press">
                        {{ __('home.cta_btn_consulta') }}
                    </a>
                </div>
                <div class="hp-cta-note-v3">
                    <span>{{ __('home.cta_trust1') }}</span>
                    <span class="hp-cta-dot">·</span>
                    <span>{{ __('home.cta_trust2') }}</span>
                    <span class="hp-cta-dot">·</span>
                    <span>{{ __('home.cta_trust3') }}</span>
                </div>
            </div>
        </div>
    </section>

</x-layouts.public>
