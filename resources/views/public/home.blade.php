<x-layouts.public>
    <x-slot:title>{{ __('home.meta_title') }}</x-slot:title>
    <x-slot:description>{{ __('home.meta_description') }}</x-slot:description>

    {{-- ================================================================== --}}
    {{-- SEO: 4 JSON-LD schemas (Organization, WebSite, FAQPage, Product×3)  --}}
    {{-- Mantenidos sin cambios estructurales del v1                         --}}
    {{-- ================================================================== --}}

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

    {{-- 4. Product (3 planes) — precios desde PricingService --}}
    @foreach([
        ['esencial', 'Esencial', $monthlyCop['esencial'], 'home.plan_esencial_name'],
        ['metodo',   'Método',   $monthlyCop['metodo'],   'home.plan_metodo_name'],
        ['elite',    'Elite',    $monthlyCop['elite'],    'home.plan_elite_name'],
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

    {{-- ================================================================== --}}
    {{-- COMP 3: HERO v2.1                                                  --}}
    {{-- ================================================================== --}}
    <section class="h2-hero" id="inicio" aria-labelledby="hero-title">
        <div class="h2-hero-orb h2-hero-orb-1" aria-hidden="true"></div>
        <div class="h2-hero-orb h2-hero-orb-2" aria-hidden="true"></div>

        <div class="h2-hero-inner">
            <p class="h2-hero-eyebrow" data-animate="fadeInUp">
                {{ __('home.hero_eyebrow_prefix') }} · {{ now()->year }} · <b>{{ __('home.hero_eyebrow_suffix') }}</b>
            </p>

            <h1 id="hero-title" class="h2-hero-title" data-animate="fadeInUp" data-stagger="1">
                {!! __('home.hero_title_html') !!}
            </h1>

            <p class="h2-hero-sub" data-animate="fadeInUp" data-stagger="2">
                {{ __('home.hero_subtitle') }}
            </p>

            <p class="h2-hero-italic" data-animate="fadeInUp" data-stagger="3">
                "{{ __('home.hero_italic_line') }}"
            </p>

            <div class="h2-hero-ctas" data-animate="fadeInUp" data-stagger="4">
                <a href="{{ route('inscripcion') }}" class="h2-btn-primary btn-press">
                    {{ __('home.hero_cta_primary') }}
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M5 12h14M13 5l7 7-7 7"/>
                    </svg>
                </a>
                <a href="{{ route('metodo') }}" class="h2-btn-ghost btn-press">
                    {{ __('home.hero_cta_secondary') }}
                </a>
            </div>

            {{-- Brand strip (sin claims demo — sustituye al live counter) --}}
            <p class="h2-hero-brand-strip" data-animate="fadeInUp" data-stagger="4">
                {{ __('home.hero_live_suffix') }}
            </p>
        </div>
    </section>

    {{-- ================================================================== --}}
    {{-- COMP 4: SOCIAL PROOF BAR                                           --}}
    {{-- ================================================================== --}}
    <section class="h2-proof" aria-label="Estadísticas WellCore">
        <p class="h2-proof-eyebrow">{{ __('home.proof_eyebrow') }}</p>
        <div class="h2-proof-grid">
            @foreach(range(1,4) as $i)
                <div class="h2-proof-item" data-animate="fadeInUp" data-stagger="{{ $i }}">
                    <span class="h2-proof-val">{{ __('home.proof_stat'.$i.'_val') }}</span>
                    <span class="h2-proof-lbl">{{ __('home.proof_stat'.$i.'_lbl') }}</span>
                </div>
            @endforeach
        </div>
    </section>

    <x-public.s-divider label="CIENCIA · MÉTODO · RESULTADOS" />

    {{-- ================================================================== --}}
    {{-- COMP 5: POR QUÉ WELLCORE (4 razones)                               --}}
    {{-- ================================================================== --}}
    <section class="h2-why" aria-labelledby="why-title">
        <div class="h2-section-head">
            <p class="h2-eyebrow">{{ __('home.why_eyebrow') }}</p>
            <h2 id="why-title" class="h2-section-title">{!! __('home.why_title') !!}</h2>
            <p class="h2-section-sub">{{ __('home.why_subtitle') }}</p>
        </div>

        <div class="h2-why-grid">
            @php
                $whyIcons = [
                    1 => '<path d="M12 2 4 6v6c0 5 3.4 9.4 8 10 4.6-.6 8-5 8-10V6l-8-4Z"/><path d="m9 12 2 2 4-4"/>',
                    2 => '<path d="M12 8v4l3 2"/><circle cx="12" cy="12" r="9"/>',
                    3 => '<path d="M2 3h6l4 8 4-8h6"/><path d="M2 21h20"/><path d="M12 11v10"/>',
                    4 => '<path d="M16 21v-2a4 4 0 0 0-8 0v2"/><circle cx="12" cy="7" r="4"/>',
                ];
            @endphp
            @foreach(range(1,4) as $i)
                <article class="h2-why-card" data-animate="fadeInUp" data-stagger="{{ $i }}">
                    <div class="h2-why-icon" aria-hidden="true">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="1.5"
                             stroke-linecap="round" stroke-linejoin="round">
                            {!! $whyIcons[$i] !!}
                        </svg>
                    </div>
                    <h3 class="h2-why-title">{{ __('home.why'.$i.'_title') }}</h3>
                    <p class="h2-why-desc">{{ __('home.why'.$i.'_desc') }}</p>
                </article>
            @endforeach
        </div>
    </section>

    <x-public.s-divider label="CINCO PASOS · UN SISTEMA" />

    {{-- ================================================================== --}}
    {{-- COMP 6: CÓMO FUNCIONA (5 steps)                                    --}}
    {{-- ================================================================== --}}
    <section class="h2-process" aria-labelledby="process-title">
        <div class="h2-section-head">
            <p class="h2-eyebrow">{{ __('home.process_eyebrow') }}</p>
            <h2 id="process-title" class="h2-section-title">{!! __('home.process_title') !!}</h2>
            <p class="h2-section-sub">{{ __('home.process_subtitle') }}</p>
        </div>

        <div class="h2-process-grid">
            @foreach(range(1,5) as $i)
                <div class="h2-step" data-animate="fadeInUp" data-stagger="{{ $i }}">
                    <span class="h2-step-num">{{ __('home.process_s'.$i.'_num') }}</span>
                    <span class="h2-step-label">{{ __('home.process_s'.$i.'_label') }}</span>
                    <h3 class="h2-step-title">{{ __('home.process_s'.$i.'_title') }}</h3>
                    <p class="h2-step-desc">{{ __('home.process_s'.$i.'_desc') }}</p>
                </div>
            @endforeach
        </div>

        <a href="{{ route('proceso') }}" class="h2-process-cta btn-press">
            {{ __('home.process_ver_completo') }} →
        </a>
    </section>

    <x-public.s-divider label="PLANES · ELIGE TU PLAN" />

    {{-- ================================================================== --}}
    {{-- COMP 7: PLANES MINI (3 cards)                                      --}}
    {{-- ================================================================== --}}
    <section class="h2-plans" aria-labelledby="plans-title">
        <div class="h2-section-head">
            <p class="h2-eyebrow">{{ __('home.plans_eyebrow') }}</p>
            <h2 id="plans-title" class="h2-section-title">{{ __('home.plans_title') }}</h2>
            <p class="h2-section-sub">{{ __('home.plans_subtitle') }}</p>
        </div>

        <div class="h2-plans-grid">
            @php
                $plans = [
                    ['key' => 'esencial', 'short' => __('home.plan_esencial_short'), 'price' => $monthlyCop['esencial'], 'badge' => null],
                    ['key' => 'metodo',   'short' => __('home.plan_metodo_short'),   'price' => $monthlyCop['metodo'],   'badge' => __('home.plans_badge_popular')],
                    ['key' => 'elite',    'short' => __('home.plan_elite_short'),    'price' => $monthlyCop['elite'],    'badge' => null],
                ];
            @endphp
            @foreach($plans as $i => $plan)
                <article class="h2-plan-card {{ $plan['badge'] ? 'is-popular' : '' }}" data-animate="fadeInUp" data-stagger="{{ $i + 1 }}">
                    @if($plan['badge'])
                        <span class="h2-plan-badge">{{ $plan['badge'] }}</span>
                    @endif
                    <h3 class="h2-plan-name">{{ $plan['short'] }}</h3>
                    <div class="h2-plan-price">
                        <span class="h2-plan-price-amount">${{ number_format($plan['price'], 0, ',', '.') }}</span>
                        <span class="h2-plan-price-period">{{ __('home.plans_cop_mes') }}</span>
                    </div>
                    <ul class="h2-plan-pillars">
                        <li>{{ __('home.'.$plan['key'].'_p1') }}</li>
                        <li>{{ __('home.'.$plan['key'].'_p2') }}</li>
                        <li>{{ __('home.'.$plan['key'].'_p3') }}</li>
                    </ul>
                    <a href="{{ route('planes') }}#{{ $plan['key'] }}" class="h2-plan-cta">
                        {{ __('home.plans_ver_detalle') }}
                    </a>
                </article>
            @endforeach
        </div>

        <p class="h2-plans-note">{{ __('home.plans_cancel_note') }}</p>
    </section>

    <x-public.s-divider label="EQUIPO · COACHES · COMUNIDAD" />

    {{-- ================================================================== --}}
    {{-- COMP 8: COMUNIDAD (split 2-col desktop)                            --}}
    {{-- ================================================================== --}}
    <section class="h2-community" aria-labelledby="community-title">
        <div class="h2-community-grid">
            <div class="h2-community-photo" data-animate="fadeInUp">
                <x-public.team-photo-fallback />
            </div>
            <div class="h2-community-content" data-animate="fadeInUp" data-stagger="1">
                <p class="h2-eyebrow">{{ __('home.community_eyebrow') }}</p>
                <h2 id="community-title" class="h2-section-title">{!! __('home.community_title') !!}</h2>
                <p class="h2-community-body">{{ __('home.community_subtitle') }}</p>
                <blockquote class="h2-community-quote">
                    "{{ __('home.community_quote') }}"
                </blockquote>
                <a href="{{ route('coaches') }}" class="h2-link-arrow">
                    {{ __('home.community_cta') }}
                </a>
            </div>
        </div>
    </section>

    {{-- ================================================================== --}}
    {{-- COMP 9: TESTIMONIOS TICKER                                         --}}
    {{-- ================================================================== --}}
    <x-public.bloomberg-ticker
        :duration="30"
        :aria_label="__('home.ticker_aria')"
        :items="[
            ['name' => 'Camila R. · Bogotá CO',     'metric' => '−12 KG',     'detail' => '24 SEM · MÉTODO'],
            ['name' => 'Sebastián M. · Buenos Aires AR', 'metric' => '+18 KG BANCA', 'detail' => '16 SEM · ELITE'],
            ['name' => 'Valeria T. · CDMX MX',      'metric' => '94% ADHER.', 'detail' => '6 MES · MÉTODO'],
            ['name' => 'Andrés P. · Medellín CO',   'metric' => '−8% GRASA',  'detail' => '12 SEM · ESENCIAL'],
        ]"
    />

    {{-- ================================================================== --}}
    {{-- COMP 10: SECTION COACH RECRUIT (NUEVO v2.1)                        --}}
    {{-- ================================================================== --}}
    <section class="h2-cr" aria-labelledby="cr-title">
        <div class="h2-cr-grid">
            <div class="h2-cr-content" data-animate="fadeInUp">
                <p class="h2-eyebrow h2-eyebrow-red">{{ __('home.coach_recruit_eyebrow') }}</p>
                <h2 id="cr-title" class="h2-section-title">{!! __('home.coach_recruit_title') !!}</h2>
                <p class="h2-section-body">{{ __('home.coach_recruit_sub') }}</p>

                <div class="h2-cr-stats">
                    @foreach(range(1,3) as $i)
                        <div class="h2-cr-stat">
                            <span class="h2-cr-stat-val">{{ __('home.coach_recruit_stat'.$i.'_val') }}</span>
                            <span class="h2-cr-stat-lbl">{{ __('home.coach_recruit_stat'.$i.'_lbl') }}</span>
                        </div>
                    @endforeach
                </div>

                <div class="h2-cr-ctas">
                    <a href="{{ route('coaches.apply') }}" class="h2-btn-primary btn-press">
                        {{ __('home.coach_recruit_cta_primary') }}
                    </a>
                    <a href="{{ route('coaches') }}" class="h2-btn-ghost btn-press">
                        {{ __('home.coach_recruit_cta_secondary') }}
                    </a>
                </div>
            </div>

            <div class="h2-cr-mockup-wrap" data-animate="fadeInUp" data-stagger="1">
                <x-public.coach-recruit-mockup
                    :label="__('home.coach_recruit_mockup_label')"
                    :status="__('home.coach_recruit_mockup_status')"
                    :clients_count="'12'"
                    :monthly_total="'$3.420 USD'"
                    :disclaimer="__('home.coach_recruit_disclaimer')"
                    :rows="[
                        ['initials' => 'CR', 'name' => 'C.R. · Colombia',  'status' => 'En racha', 'day' => 'LUN', 'variant' => 'red'],
                        ['initials' => 'SM', 'name' => 'S.M. · Argentina', 'status' => 'Check-in', 'day' => 'MAR', 'variant' => 'gold'],
                        ['initials' => 'VT', 'name' => 'V.T. · México',    'status' => 'Activo',   'day' => 'MIÉ', 'variant' => 'green'],
                        ['initials' => 'AP', 'name' => 'A.P. · Chile',     'status' => 'Activo',   'day' => 'JUE', 'variant' => 'blue'],
                    ]"
                />
            </div>
        </div>
    </section>

    {{-- ================================================================== --}}
    {{-- COMP 11: PULL QUOTE BRUTAL                                         --}}
    {{-- ================================================================== --}}
    <x-public.pullquote :cite="__('home.pullquote_cite')">
        {!! __('home.pullquote_text') !!}
    </x-public.pullquote>

    <x-public.s-divider label="RECURSOS · APRENDIZAJE" />

    {{-- ================================================================== --}}
    {{-- COMP 12: BLOG TEASER (NUEVO v2.1)                                  --}}
    {{-- ================================================================== --}}
    @if($latestPosts->isNotEmpty() || true)
    <section class="h2-blog" aria-labelledby="blog-title">
        <div class="h2-section-head">
            <p class="h2-eyebrow">{{ __('home.blog_eyebrow') }}</p>
            <h2 id="blog-title" class="h2-section-title">{!! __('home.blog_title') !!}</h2>
            <p class="h2-section-sub h2-section-sub-italic">"{{ __('home.blog_sub') }}"</p>
        </div>

        <div class="h2-blog-grid">
            @if($latestPosts->isNotEmpty())
                @foreach($latestPosts as $post)
                    <x-public.article-card :post="$post" />
                @endforeach
            @else
                {{-- Fallback: 3 placeholders editoriales hasta que haya posts publicados --}}
                @php
                    $fallbackPosts = [
                        ['title' => 'Sobrecarga progresiva: la clave del crecimiento muscular', 'category' => 'Entrenamiento', 'excerpt' => 'Entrenar sin progresión es solo repetición. La biología del estímulo mecánico explicada.', 'reading_minutes' => 8, 'slug' => 'sobrecarga-progresiva'],
                        ['title' => 'Periodización del entrenamiento: cómo planificar tu progreso', 'category' => 'Entrenamiento', 'excerpt' => 'No todos los meses entrenas igual. Cómo distribuir volumen, intensidad y recuperación.', 'reading_minutes' => 12, 'slug' => 'periodizacion-entrenamiento'],
                        ['title' => 'TDEE: cómo calcular tus calorías correctamente', 'category' => 'Nutrición', 'excerpt' => 'Sin un punto de partida real, cualquier dieta es una apuesta. La ecuación que usa todo coach serio.', 'reading_minutes' => 6, 'slug' => 'tdee-calorias'],
                    ];
                @endphp
                @foreach($fallbackPosts as $i => $fp)
                    <article class="bt-card" data-animate="fadeInUp" data-stagger="{{ $i + 1 }}">
                        <a href="#" style="display:contents;text-decoration:none;color:inherit;" aria-disabled="true" tabindex="-1">
                            <div class="bt-cover-placeholder" aria-hidden="true"></div>
                            <span class="bt-cat">{{ strtoupper($fp['category']) }}</span>
                            <h3 class="bt-title">{{ $fp['title'] }}</h3>
                            <p class="bt-excerpt">{{ $fp['excerpt'] }}</p>
                            <div class="bt-meta">
                                <span>WellCore</span>
                                <span>·</span>
                                <span>{{ $fp['reading_minutes'] }} {{ __('home.blog_min_read') }}</span>
                            </div>
                        </a>
                    </article>
                @endforeach
            @endif
        </div>

        <a href="{{ route('blog.index') }}" class="h2-link-arrow h2-blog-see-all">
            {{ __('home.blog_see_all') }}
        </a>
    </section>
    @endif

    <x-public.s-divider label="PREGUNTAS · FRECUENTES" />

    {{-- ================================================================== --}}
    {{-- COMP 13: FAQ                                                       --}}
    {{-- ================================================================== --}}
    <section class="h2-faq" aria-labelledby="faq-title">
        <div class="h2-section-head">
            <p class="h2-eyebrow">{{ __('home.faq_eyebrow') }}</p>
            <h2 id="faq-title" class="h2-section-title">{!! __('home.faq_title') !!}</h2>
        </div>

        <div class="h2-faq-list">
            @foreach(range(1,8) as $i)
                <details class="faq-accordion-item">
                    <summary class="faq-accordion-summary">
                        <span>{{ __('home.faq_q'.$i) }}</span>
                        <svg class="faq-accordion-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M6 9l6 6 6-6"/>
                        </svg>
                    </summary>
                    <div class="faq-accordion-body">
                        {{ __('home.faq_a'.$i) }}
                    </div>
                </details>
            @endforeach
        </div>
    </section>

    {{-- ================================================================== --}}
    {{-- COMP 14: CTA FINAL MASIVO                                          --}}
    {{-- ================================================================== --}}
    <section class="h2-cta-final" id="empezar" aria-labelledby="cta-final-title">
        <div class="h2-cta-final-inner">
            <p class="h2-eyebrow">{{ __('home.cta_eyebrow') }}</p>
            <h2 id="cta-final-title" class="h2-cta-final-title" data-animate="fadeInUp">
                {!! __('home.cta_title') !!}
            </h2>
            <p class="h2-cta-final-sub" data-animate="fadeInUp" data-stagger="1">
                {{ __('home.cta_subtitle') }}
            </p>

            <div class="h2-cta-final-ctas" data-animate="fadeInUp" data-stagger="2">
                <a href="{{ route('inscripcion') }}" class="h2-btn-primary btn-press">
                    {{ __('home.cta_primary') }}
                </a>
                <a href="{{ route('proceso') }}" class="h2-btn-ghost btn-press">
                    {{ __('home.cta_secondary') }}
                </a>
            </div>

            <div class="h2-cta-final-trust" data-animate="fadeInUp" data-stagger="3">
                <span>{{ __('home.cta_trust1') }}</span>
                <span class="h2-trust-sep">·</span>
                <span>{{ __('home.cta_trust2') }}</span>
                <span class="h2-trust-sep">·</span>
                <span>{{ __('home.cta_trust3') }}</span>
                <span class="h2-trust-sep">·</span>
                <span>{{ __('home.cta_trust4') }}</span>
            </div>
        </div>
    </section>

    {{-- ================================================================== --}}
    {{-- COMP 1: STICKY CTA MOBILE                                          --}}
    {{-- ================================================================== --}}
    <x-public.sticky-mobile-cta
        :href="route('inscripcion')"
        :label="__('home.sticky_cta_label')"
        hide-at="empezar"
        :threshold="600"
    />
</x-layouts.public>
