{{--
    /metodo · long-form editorial v2 (porting Sprint 2 — 2026-04-29).

    Spec: 02-metodo/prompt-implementacion-blade.md (sección 2 mapping COMPs 1-19)
          02-metodo/redesigned-mobile.html (HTML target)
          02-metodo/redesigned-mobile.css (CSS target → portado a v2-public.css)

    Estructura: Hero (Cap00) + Stats + 7 capítulos editoriales numerados,
    pull-quotes brutales x5, inline CTAs x3, Bloomberg ticker (Cap06),
    SVG curva animada (Cap03), period table (Cap04), compare table (Cap02).

    Layout:
        - Compose <x-layouts.public-editorial> (que a su vez compone <x-layouts.public>)
          → hereda topbar, footer, atmosphere global, dark mode, fonts, JSON-LD chrome.
        - Sidebar editorial 220px sticky en ≥1024px (manejado por window.metodoPage()).
        - Main scroll area con cols editorial (var(--col-max) / var(--col-wide)).

    Variables del controller (MetodoController@index):
        $monthlyEsencialCop  → JSON-LD offers.price (entero pesos COP).
        $monthlyEsencialUsd  → futuro fallback USD si se necesita.

    Voz: latino neutro estricto (tú/puedes/quieres). NO voseo argentino.
    Cap05 NUNCA menciona IA/Claude/GPT (feedback_ia_confidencial).
    "RIR" reemplazado por "intensidad relativa" en period table (Daniel decision).
--}}

<x-layouts.public-editorial>
    <x-slot:title>{{ __('metodo.meta_title') }}</x-slot:title>
    <x-slot:description>{{ __('metodo.meta_description') }}</x-slot:description>

    {{-- Chapter pill mobile (sticky top entre 768-1023px). Bind a Alpine activePill. --}}
    <x-slot:chapterPill>
        <span x-text="activePill || '{{ __('metodo.chapters.cap00.pill') }}'">{{ __('metodo.chapters.cap00.pill') }}</span>
    </x-slot:chapterPill>

    {{-- Sidebar editorial (≥1024px). Render del slot del layout. --}}
    <x-slot:sidebar>
        <x-public.editorial-sidebar
            :brand-sub="__('metodo.sidebar.subtitle')"
            :progress-label="__('metodo.sidebar.progress_label')"
            :cta-href="route('planes')"
            :cta-text="__('metodo.sidebar.cta')"
            :chapters="[
                ['id' => 'cap-hero', 'num' => '00', 'title' => __('metodo.chapters.cap00.nav_title')],
                ['id' => 'cap-01',   'num' => '01', 'title' => __('metodo.chapters.cap01.nav_title')],
                ['id' => 'cap-02',   'num' => '02', 'title' => __('metodo.chapters.cap02.nav_title')],
                ['id' => 'cap-03',   'num' => '03', 'title' => __('metodo.chapters.cap03.nav_title')],
                ['id' => 'cap-04',   'num' => '04', 'title' => __('metodo.chapters.cap04.nav_title')],
                ['id' => 'cap-05',   'num' => '05', 'title' => __('metodo.chapters.cap05.nav_title')],
                ['id' => 'cap-06',   'num' => '06', 'title' => __('metodo.chapters.cap06.nav_title')],
                ['id' => 'cap-07',   'num' => '07', 'title' => __('metodo.chapters.cap07.nav_title')],
            ]"
            :nav-links="[
                ['href' => route('proceso'), 'text' => 'Proceso'],
                ['href' => route('planes'),  'text' => 'Planes'],
                ['href' => route('faq'),     'text' => 'FAQ'],
            ]"
        />
    </x-slot:sidebar>

    {{-- JSON-LD EducationalOrganization (preservado del blade legacy). --}}
    <x-json-ld :data="[
        '@context' => 'https://schema.org',
        '@type' => 'EducationalOrganization',
        'name' => 'WellCore Fitness — El Método',
        'url' => url('/metodo'),
        'description' => 'Protocolo cientifico de entrenamiento personalizado al 100%. 5 pilares basados en evidencia, seguimiento 1:1 con coach humano real.',
        'teaches' => [
            'Entrenamiento de fuerza basado en evidencia',
            'Nutricion personalizada y periodizacion calorica',
            'Habitos de recuperacion y manejo del estres',
            'Composicion corporal y seguimiento de progreso',
            'Psicologia del rendimiento y adherencia',
        ],
        'educationalCredentialAwarded' => 'Transformacion fisica medible con metodologia cientifica',
        'provider' => [
            '@type' => 'Organization',
            'name' => 'WellCore Fitness',
            'url' => url('/'),
        ],
        'offers' => [
            '@type' => 'Offer',
            'name' => 'Coaching 1:1 con El Método WellCore',
            'description' => 'Protocolo de 5 pilares: entrenamiento, nutricion, habitos, recuperacion y mentalidad.',
            'url' => url('/planes'),
            'priceCurrency' => 'COP',
            'price' => (string) ($monthlyEsencialCop ?? app(\App\Services\PricingService::class)->priceCop('esencial')),
        ],
    ]" />

    {{-- ──────────────────────────────────────────────────────────────
         Alpine root: window.metodoPage() factory (resources/js/metodo.js)
         Maneja activeChapter, scrollProgress, stickyVisible, activePill.
         ────────────────────────────────────────────────────────────── --}}
    <div class="metodo-root metodo-main"
         x-data="metodoPage()"
         x-init="init()"
         @beforeunload.window="destroy()">

        {{-- ════════════════════════════════════════════════════════════
             CAP 00 — HERO (Portada)
             ════════════════════════════════════════════════════════════ --}}
        <section class="metodo-hero"
                 id="cap-hero"
                 data-chapter="00"
                 data-chapter-label="{{ __('metodo.chapters.cap00.pill') }}">
            <p class="metodo-hero-kicker">{{ __('metodo.hero.kicker') }}</p>
            <h1 class="metodo-hero-pullquote">{!! __('metodo.hero.pullquote_html') !!}</h1>
            <p class="metodo-hero-sub">{{ __('metodo.hero.sub') }}</p>
            <div class="metodo-hero-scroll-hint" aria-hidden="true">
                <div class="metodo-scroll-arrow"></div>
                <span>{{ __('metodo.hero.scroll_hint') }}</span>
            </div>
        </section>

        {{-- ════════════════════════════════════════════════════════════
             STATS BAR (3 KPIs)
             ════════════════════════════════════════════════════════════ --}}
        <div class="metodo-stats-bar" data-animate="fadeInUp">
            <div class="metodo-stat-item">
                <div class="metodo-stat-value">{{ __('metodo.stats.adherence_value') }}</div>
                <div class="metodo-stat-label">{{ __('metodo.stats.adherence') }}</div>
            </div>
            <div class="metodo-stat-item">
                <div class="metodo-stat-value">{{ __('metodo.stats.visible_results_value') }}</div>
                <div class="metodo-stat-label">{{ __('metodo.stats.visible_results') }}</div>
            </div>
            <div class="metodo-stat-item">
                <div class="metodo-stat-value">{{ __('metodo.stats.attention_value') }}</div>
                <div class="metodo-stat-label">{{ __('metodo.stats.attention') }}</div>
            </div>
        </div>

        {{-- ════════════════════════════════════════════════════════════
             CAP 01 — EL PROBLEMA
             ════════════════════════════════════════════════════════════ --}}
        <x-public.s-divider :label="__('metodo.chapters.cap01.divider')" class="metodo-sec-divider" />

        <section class="metodo-chapter"
                 id="cap-01"
                 data-chapter="01"
                 data-chapter-label="{{ __('metodo.chapters.cap01.pill') }}">
            <div class="metodo-col">
                <x-public.chapter-header
                    :num-text="__('metodo.chapters.cap01.num_text')"
                    :title-html="__('metodo.chapters.cap01.title_html')"
                />

                <x-public.dropcap-paragraph data-animate="fadeInUp">
                    {!! __('metodo.problem.intro_p1_html') !!}
                </x-public.dropcap-paragraph>

                <p class="body-text" data-animate="fadeInUp" data-delay="1">
                    {!! __('metodo.problem.intro_p2_html') !!}
                </p>

                <p class="body-text" data-animate="fadeInUp" data-delay="2">
                    {!! __('metodo.problem.intro_p3_html') !!}
                </p>

                <div class="metodo-data-grid" data-animate="fadeInUp">
                    @foreach (__('metodo.problem.data_cells') as $cell)
                        <div class="metodo-data-cell {{ ($cell['accent'] ?? false) ? 'is-accent' : '' }}">
                            <div class="metodo-data-cell-value">{{ $cell['value'] }}</div>
                            <div class="metodo-data-cell-label">{{ $cell['label'] }}</div>
                        </div>
                    @endforeach
                </div>
                <p class="source-note-v2" data-animate="fadeInUp">{{ __('metodo.problem.source') }}</p>
            </div>
        </section>

        {{-- Pull-quote 1 — entre Cap01 y Cap02 --}}
        <x-public.pullquote :cite="__('metodo.pullquotes.q1.cite')">
            {!! __('metodo.pullquotes.q1.text_html') !!}
        </x-public.pullquote>

        {{-- ════════════════════════════════════════════════════════════
             CAP 02 — EL MÉTODO (5 pilares + comparativa Bloomberg)
             ════════════════════════════════════════════════════════════ --}}
        <x-public.s-divider :label="__('metodo.chapters.cap02.divider')" class="metodo-sec-divider" />

        <section class="metodo-chapter"
                 id="cap-02"
                 data-chapter="02"
                 data-chapter-label="{{ __('metodo.chapters.cap02.pill') }}">
            <div class="metodo-with-margin-note">
                <div class="metodo-col">
                    <x-public.chapter-header
                        :num-text="__('metodo.chapters.cap02.num_text')"
                        :title-html="__('metodo.chapters.cap02.title_html')"
                    />

                    <x-public.dropcap-paragraph data-animate="fadeInUp">
                        {!! __('metodo.pillars.intro_p1_html') !!}
                    </x-public.dropcap-paragraph>

                    {{-- Pillar list (5 items) --}}
                    <div class="metodo-pillar-list">
                        @foreach (['p1', 'p2', 'p3', 'p4', 'p5'] as $idx => $key)
                            <div class="metodo-pillar-item"
                                 data-animate="fadeInUp"
                                 @if ($idx) data-delay="{{ ($idx % 3) ?: 1 }}" @endif>
                                <div class="metodo-pillar-num">P0{{ $idx + 1 }}</div>
                                <div>
                                    <div class="metodo-pillar-title">{{ __("metodo.pillars.$key.name") }}</div>
                                    <p class="metodo-pillar-body">{{ __("metodo.pillars.$key.description") }}</p>
                                    <p class="metodo-pillar-cite">{{ __("metodo.pillars.$key.cite") }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                <aside class="margin-note" data-animate="fadeInUp">
                    {{ __('metodo.pillars.margin_note') }}
                </aside>
            </div>

            {{-- Tabla comparativa Bloomberg (full-width dentro del chapter) --}}
            <div class="metodo-col-wide" style="margin-top: 50px;">
                <p class="source-note-v2" style="margin-bottom: 6px;">{{ __('metodo.comparison.title') }} — {{ __('metodo.comparison.subtitle') }}</p>
                @php
                    $cmpRows = [];
                    foreach (['r1', 'r2', 'r3', 'r4', 'r5', 'r6'] as $rk) {
                        $row = __("metodo.comparison.rows.$rk");
                        $appVal = $row['app'] ?? '';
                        $gymVal = $row['gym'] ?? '';
                        $appIsNo = in_array(mb_strtolower(trim($appVal)), ['no', 'none'], true);
                        $gymIsNo = in_array(mb_strtolower(trim($gymVal)), ['no', 'none'], true);
                        $cmpRows[] = [
                            ['text' => $row['feature'] ?? ''],
                            ['text' => $row['wellcore'] ?? '', 'good' => true],
                            ['text' => $appVal, 'good' => $appIsNo ? false : null],
                            ['text' => $gymVal, 'good' => $gymIsNo ? false : null],
                        ];
                    }
                @endphp
                <x-public.compare-table
                    :cols="[
                        __('metodo.comparison.col_feature'),
                        __('metodo.comparison.col_wellcore'),
                        __('metodo.comparison.col_app'),
                        __('metodo.comparison.col_gym'),
                    ]"
                    :rows="$cmpRows"
                    :wc-col-idx="1"
                    :source-note="__('metodo.comparison.footnote')"
                />
            </div>
        </section>

        {{-- Inline CTA 1 (después de Cap02) --}}
        <x-public.inline-cta
            :label="__('metodo.inline_ctas.c1.label')"
            :title="__('metodo.inline_ctas.c1.title')"
            :href="route('planes')"
            :cta-text="__('metodo.inline_ctas.c1.btn')"
        />

        {{-- Pull-quote 2 --}}
        <x-public.pullquote :cite="__('metodo.pullquotes.q2.cite')">
            {!! __('metodo.pullquotes.q2.text_html') !!}
        </x-public.pullquote>

        {{-- ════════════════════════════════════════════════════════════
             CAP 03 — LA CIENCIA (3 párrafos + curva SVG animada)
             ════════════════════════════════════════════════════════════ --}}
        <x-public.s-divider :label="__('metodo.chapters.cap03.divider')" class="metodo-sec-divider" />

        <section class="metodo-chapter"
                 id="cap-03"
                 data-chapter="03"
                 data-chapter-label="{{ __('metodo.chapters.cap03.pill') }}">
            <div class="metodo-col">
                <x-public.chapter-header
                    :num-text="__('metodo.chapters.cap03.num_text')"
                    :title-html="__('metodo.chapters.cap03.title_html')"
                />

                <x-public.dropcap-paragraph data-animate="fadeInUp">
                    {!! __('metodo.ciencia.body_p1_html') !!}
                </x-public.dropcap-paragraph>

                <p class="body-text" data-animate="fadeInUp" data-delay="1">
                    {!! __('metodo.ciencia.body_p2_html') !!}
                </p>

                <p class="body-text" data-animate="fadeInUp" data-delay="2">
                    {!! __('metodo.ciencia.body_p3_html') !!}
                </p>

                {{-- SVG curva progresión 12 semanas (anim. via .curve-reveal-active class) --}}
                <figure class="metodo-svg-figure" data-animate="fadeInUp">
                    <figcaption class="metodo-svg-figure-label">{{ __('metodo.ciencia.svg_label') }}</figcaption>
                    <svg class="metodo-progress-svg"
                         viewBox="0 0 640 280"
                         fill="none"
                         xmlns="http://www.w3.org/2000/svg"
                         role="img"
                         aria-label="{{ __('metodo.ciencia.svg_label') }}">
                        {{-- Grid lines --}}
                        <line x1="60" y1="20" x2="60" y2="240" stroke="rgba(255,255,255,0.06)" stroke-width="1"/>
                        <line x1="60" y1="240" x2="620" y2="240" stroke="rgba(255,255,255,0.06)" stroke-width="1"/>
                        <line x1="60" y1="180" x2="620" y2="180" stroke="rgba(255,255,255,0.04)" stroke-width="1" stroke-dasharray="4 6"/>
                        <line x1="60" y1="130" x2="620" y2="130" stroke="rgba(255,255,255,0.04)" stroke-width="1" stroke-dasharray="4 6"/>
                        <line x1="60" y1="80"  x2="620" y2="80"  stroke="rgba(255,255,255,0.04)" stroke-width="1" stroke-dasharray="4 6"/>
                        {{-- Week markers --}}
                        <text x="60"  y="258" fill="#404040" font-family="JetBrains Mono,monospace" font-size="9" text-anchor="middle">S1</text>
                        <text x="152" y="258" fill="#404040" font-family="JetBrains Mono,monospace" font-size="9" text-anchor="middle">S3</text>
                        <text x="244" y="258" fill="#404040" font-family="JetBrains Mono,monospace" font-size="9" text-anchor="middle">S5</text>
                        <text x="336" y="258" fill="#404040" font-family="JetBrains Mono,monospace" font-size="9" text-anchor="middle">S7</text>
                        <text x="428" y="258" fill="#404040" font-family="JetBrains Mono,monospace" font-size="9" text-anchor="middle">S9</text>
                        <text x="520" y="258" fill="#404040" font-family="JetBrains Mono,monospace" font-size="9" text-anchor="middle">S11</text>
                        <text x="612" y="258" fill="#404040" font-family="JetBrains Mono,monospace" font-size="9" text-anchor="middle">S12</text>
                        {{-- Y labels --}}
                        <text x="52" y="184" fill="#404040" font-family="JetBrains Mono,monospace" font-size="9" text-anchor="end">+10%</text>
                        <text x="52" y="134" fill="#404040" font-family="JetBrains Mono,monospace" font-size="9" text-anchor="end">+20%</text>
                        <text x="52" y="84"  fill="#404040" font-family="JetBrains Mono,monospace" font-size="9" text-anchor="end">+30%</text>
                        {{-- Area fill under curve --}}
                        <path id="metodoCurveArea"
                              d="M60,235 C100,232 120,230 152,224 C184,218 200,210 244,198 C288,186 310,172 336,155 C362,138 384,118 428,98 C472,78 490,72 520,65 C550,58 580,54 612,50 L612,240 L60,240 Z"
                              fill="url(#metodoAreaGrad)" opacity="0"/>
                        {{-- WellCore curve (red, animated) --}}
                        <path id="metodoCurve"
                              d="M60,235 C100,232 120,230 152,224 C184,218 200,210 244,198 C288,186 310,172 336,155 C362,138 384,118 428,98 C472,78 490,72 520,65 C550,58 580,54 612,50"
                              stroke="#DC2626" stroke-width="2.5" stroke-linecap="round"
                              stroke-dasharray="900" stroke-dashoffset="900"/>
                        {{-- Average curve (gray dashed) --}}
                        <path d="M60,235 C110,234 160,233 244,230 C328,227 380,222 428,218 C476,214 530,212 612,210"
                              stroke="rgba(255,255,255,0.15)" stroke-width="1.5" stroke-linecap="round" stroke-dasharray="5 5"/>
                        {{-- Dots at key milestones --}}
                        <circle id="metodoDot1" cx="152" cy="224" r="4" fill="#DC2626" opacity="0"/>
                        <circle id="metodoDot2" cx="336" cy="155" r="4" fill="#DC2626" opacity="0"/>
                        <circle id="metodoDot3" cx="612" cy="50"  r="5" fill="#DC2626" opacity="0"/>
                        {{-- Labels --}}
                        <text id="metodoLbl1" x="158" y="218" fill="#F87171" font-family="JetBrains Mono,monospace" font-size="8" opacity="0">{{ __('metodo.ciencia.svg_dot1') }}</text>
                        <text id="metodoLbl2" x="342" y="149" fill="#F87171" font-family="JetBrains Mono,monospace" font-size="8" opacity="0">{{ __('metodo.ciencia.svg_dot2') }}</text>
                        <text id="metodoLbl3" x="540" y="46"  fill="#F87171" font-family="JetBrains Mono,monospace" font-size="8" opacity="0">{{ __('metodo.ciencia.svg_dot3') }}</text>
                        {{-- Legend --}}
                        <line x1="68" y1="28" x2="96" y2="28" stroke="#DC2626" stroke-width="2.5"/>
                        <text x="102" y="32" fill="#A3A3A3" font-family="JetBrains Mono,monospace" font-size="8">{{ __('metodo.ciencia.svg_legend_wc') }}</text>
                        <line x1="170" y1="28" x2="198" y2="28" stroke="rgba(255,255,255,0.2)" stroke-width="1.5" stroke-dasharray="5 4"/>
                        <text x="204" y="32" fill="#A3A3A3" font-family="JetBrains Mono,monospace" font-size="8">{{ __('metodo.ciencia.svg_legend_avg') }}</text>
                        <defs>
                            <linearGradient id="metodoAreaGrad" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="#DC2626" stop-opacity="0.18"/>
                                <stop offset="100%" stop-color="#DC2626" stop-opacity="0"/>
                            </linearGradient>
                        </defs>
                    </svg>
                </figure>
                <p class="source-note-v2" data-animate="fadeInUp">{{ __('metodo.ciencia.source') }}</p>
            </div>
        </section>

        {{-- Pull-quote 3 --}}
        <x-public.pullquote :cite="__('metodo.pullquotes.q3.cite')">
            {!! __('metodo.pullquotes.q3.text_html') !!}
        </x-public.pullquote>

        {{-- ════════════════════════════════════════════════════════════
             CAP 04 — EL PLAN (period table)
             ════════════════════════════════════════════════════════════ --}}
        <x-public.s-divider :label="__('metodo.chapters.cap04.divider')" class="metodo-sec-divider" />

        <section class="metodo-chapter"
                 id="cap-04"
                 data-chapter="04"
                 data-chapter-label="{{ __('metodo.chapters.cap04.pill') }}">
            <div class="metodo-with-margin-note">
                <div class="metodo-col">
                    <x-public.chapter-header
                        :num-text="__('metodo.chapters.cap04.num_text')"
                        :title-html="__('metodo.chapters.cap04.title_html')"
                    />

                    <x-public.dropcap-paragraph data-animate="fadeInUp">
                        {!! __('metodo.plan.body_p1_html') !!}
                    </x-public.dropcap-paragraph>

                    <p class="body-text" data-animate="fadeInUp" data-delay="1">
                        {!! __('metodo.plan.body_p2_html') !!}
                    </p>

                    @php
                        $periodPhases = [
                            array_merge(['type' => 'adapt'],  __('metodo.plan.period.adapt')),
                            array_merge(['type' => 'hyper'],  __('metodo.plan.period.hyper')),
                            array_merge(['type' => 'fuerza'], __('metodo.plan.period.fuerza')),
                            array_merge(['type' => 'desc'],   __('metodo.plan.period.desc')),
                        ];
                    @endphp
                    <x-public.period-table
                        :headers="__('metodo.plan.period_headers')"
                        :phases="$periodPhases"
                        :source-note="__('metodo.plan.source')"
                    />
                </div>
                <aside class="margin-note" data-animate="fadeInUp">
                    {{ __('metodo.plan.margin_note') }}
                </aside>
            </div>
        </section>

        {{-- Inline CTA 2 (después de Cap04) --}}
        <x-public.inline-cta
            :label="__('metodo.inline_ctas.c2.label')"
            :title="__('metodo.inline_ctas.c2.title')"
            :href="route('coaches')"
            :cta-text="__('metodo.inline_ctas.c2.btn')"
        />

        {{-- ════════════════════════════════════════════════════════════
             CAP 05 — EL COACH (coach humano 1:1, sin mención IA)
             ════════════════════════════════════════════════════════════ --}}
        <x-public.s-divider :label="__('metodo.chapters.cap05.divider')" class="metodo-sec-divider" />

        <section class="metodo-chapter"
                 id="cap-05"
                 data-chapter="05"
                 data-chapter-label="{{ __('metodo.chapters.cap05.pill') }}">
            <div class="metodo-with-margin-note">
                <div class="metodo-col">
                    <x-public.chapter-header
                        :num-text="__('metodo.chapters.cap05.num_text')"
                        :title-html="__('metodo.chapters.cap05.title_html')"
                    />

                    <x-public.dropcap-paragraph data-animate="fadeInUp">
                        {!! __('metodo.coach.body_p1_html') !!}
                    </x-public.dropcap-paragraph>

                    <p class="body-text" data-animate="fadeInUp" data-delay="1">
                        {!! __('metodo.coach.body_p2_html') !!}
                    </p>

                    <p class="body-text" data-animate="fadeInUp" data-delay="2">
                        {!! __('metodo.coach.body_p3_html') !!}
                    </p>
                </div>
                <aside class="margin-note" data-animate="fadeInUp">
                    {{ __('metodo.coach.margin_note') }}
                </aside>
            </div>
        </section>

        {{-- Pull-quote 4 --}}
        <x-public.pullquote :cite="__('metodo.pullquotes.q4.cite')">
            {!! __('metodo.pullquotes.q4.text_html') !!}
        </x-public.pullquote>

        {{-- ════════════════════════════════════════════════════════════
             CAP 06 — LOS CHECK-INS (Bloomberg ticker)
             ════════════════════════════════════════════════════════════ --}}
        <x-public.s-divider :label="__('metodo.chapters.cap06.divider')" class="metodo-sec-divider" />

        <section class="metodo-chapter"
                 id="cap-06"
                 data-chapter="06"
                 data-chapter-label="{{ __('metodo.chapters.cap06.pill') }}">
            <div class="metodo-col">
                <x-public.chapter-header
                    :num-text="__('metodo.chapters.cap06.num_text')"
                    :title-html="__('metodo.chapters.cap06.title_html')"
                />

                <x-public.dropcap-paragraph data-animate="fadeInUp">
                    {!! __('metodo.checkins.body_p1_html') !!}
                </x-public.dropcap-paragraph>

                <p class="body-text" data-animate="fadeInUp" data-delay="1">
                    {!! __('metodo.checkins.body_p2_html') !!}
                </p>
            </div>

            {{-- Ticker full-bleed (sale del col estrecho) --}}
            <x-public.bloomberg-ticker
                :items="__('metodo.checkins.ticker')"
                :duration="35"
                :aria-label="__('metodo.checkins.ticker_label')"
            />
            <p class="metodo-ticker-source" data-animate="fadeInUp">{{ __('metodo.checkins.source') }}</p>
        </section>

        {{-- Inline CTA 3 (después de Cap06) — con secundario --}}
        <x-public.inline-cta
            :label="__('metodo.inline_ctas.c3.label')"
            :title="__('metodo.inline_ctas.c3.title')"
            :href="route('planes')"
            :cta-text="__('metodo.inline_ctas.c3.btn')"
            :secondary-href="route('proceso')"
            :secondary-text="__('metodo.inline_ctas.c3.btn_secondary')"
        />

        {{-- ════════════════════════════════════════════════════════════
             CAP 07 — LAS OBJECIONES (acordeón editorial — usa <details>)
             ════════════════════════════════════════════════════════════ --}}
        <x-public.s-divider :label="__('metodo.chapters.cap07.divider')" class="metodo-sec-divider" />

        <section class="metodo-chapter metodo-objections"
                 id="cap-07"
                 data-chapter="07"
                 data-chapter-label="{{ __('metodo.chapters.cap07.pill') }}">
            <div class="metodo-col">
                <x-public.chapter-header
                    :num-text="__('metodo.chapters.cap07.num_text')"
                    :title-html="__('metodo.chapters.cap07.title_html')"
                />

                <x-public.dropcap-paragraph data-animate="fadeInUp">
                    {!! __('metodo.objections.body_intro_html') !!}
                </x-public.dropcap-paragraph>

                {{-- Acordeón editorial: usa <x-public.faq-accordion> con override visual.
                     Cada Q llega con prefix de mark numérico (renderea como número grande rojo). --}}
                @php
                    $objList = __('metodo.objections.list');
                    $objItems = [];
                    foreach ($objList as $obj) {
                        $mark = $obj['mark'] ?? '';
                        $q = $obj['q'] ?? '';
                        $a = $obj['a'] ?? '';
                        $objItems[] = [
                            // Concatenamos el HTML del mark dentro del summary via <span>
                            // pero el componente faq-accordion solo soporta string plain en q;
                            // por eso nos saltamos faq-accordion y lo pintamos inline más abajo.
                            'mark' => $mark,
                            'q' => $q,
                            'a' => $a,
                        ];
                    }
                @endphp

                <div class="faq-accordion-wrap" data-animate="fadeInUp">
                    <div class="faq-accordion">
                        @foreach ($objItems as $i => $obj)
                            <details class="faq-accordion-item" id="objection-{{ $obj['mark'] }}">
                                <summary class="faq-accordion-summary">
                                    <span class="metodo-objection-mark" aria-hidden="true">{{ $obj['mark'] }}</span>
                                    <span class="metodo-objection-q">{{ $obj['q'] }}</span>
                                    <svg class="faq-accordion-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" aria-hidden="true">
                                        <path d="M12 5v14M5 12h14"/>
                                    </svg>
                                </summary>
                                <div class="faq-accordion-body">{!! $obj['a'] !!}</div>
                            </details>
                        @endforeach
                    </div>

                    {{-- JSON-LD FAQPage schema (SEO bonus) --}}
                    <script type="application/ld+json">
                        {!! json_encode([
                            '@context' => 'https://schema.org',
                            '@type' => 'FAQPage',
                            'mainEntity' => array_map(function ($obj) {
                                return [
                                    '@type' => 'Question',
                                    'name' => $obj['q'],
                                    'acceptedAnswer' => [
                                        '@type' => 'Answer',
                                        'text' => strip_tags($obj['a']),
                                    ],
                                ];
                            }, $objItems),
                        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
                    </script>
                </div>
            </div>
        </section>

        {{-- Pull-quote 5 --}}
        <x-public.pullquote :cite="__('metodo.pullquotes.q5.cite')">
            {!! __('metodo.pullquotes.q5.text_html') !!}
        </x-public.pullquote>

        {{-- ════════════════════════════════════════════════════════════
             CTA FINAL masivo
             ════════════════════════════════════════════════════════════ --}}
        <section class="metodo-cta-final" id="cta-final">
            <div class="metodo-cta-final-inner" data-animate="fadeInUp">
                <p class="metodo-cta-final-kicker">{{ __('metodo.cta_final.kicker') }}</p>
                <h2 class="metodo-cta-final-title">{!! __('metodo.cta_final.title_html') !!}</h2>
                <p class="metodo-cta-final-sub">{{ __('metodo.cta_final.sub') }}</p>
                <div class="metodo-cta-final-btns">
                    <a href="{{ route('planes') }}" class="btn-primary-v2">
                        <span>{{ __('metodo.cta_final.btn_primary') }}</span>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M5 12h14M13 5l7 7-7 7"/>
                        </svg>
                    </a>
                    <a href="{{ route('proceso') }}" class="btn-ghost-v2">{{ __('metodo.cta_final.btn_secondary') }}</a>
                </div>
                <div class="metodo-cta-final-trust">
                    @foreach (__('metodo.cta_final.trust_items') as $idx => $item)
                        @if ($idx > 0)<span class="sep" aria-hidden="true">·</span>@endif
                        <span>{{ $item }}</span>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- ════════════════════════════════════════════════════════════
             Sticky mobile CTA — visible tras scroll 60% (Alpine binding).
             Se oculta cuando #cta-final entra al viewport (evita doble CTA).
             ════════════════════════════════════════════════════════════ --}}
        <div class="metodo-sticky-cta"
             :class="stickyVisible ? 'is-visible' : ''"
             role="region"
             aria-label="{{ __('metodo.sticky.text_strong') }}">
            <div class="metodo-sticky-cta-text">
                <strong>{{ __('metodo.sticky.text_strong') }}</strong>
                {{ __('metodo.sticky.text') }}
            </div>
            <a href="{{ route('planes') }}" class="btn-primary-v2" style="font-size: 12px; padding: 10px 18px; min-height: 40px;">
                <span>{{ __('metodo.sticky.cta') }}</span>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M5 12h14M13 5l7 7-7 7"/>
                </svg>
            </a>
        </div>
    </div>{{-- /metodo-root --}}

    {{-- window.metodoPage() viene del bundle alpine-public (importa ./metodo.js).
         Esa importación lo registra antes de Alpine.start(); no se necesita @vite() extra. --}}
</x-layouts.public-editorial>
