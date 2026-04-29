{{--
    /proceso · long-form storytelling v2 (porting Sprint 2 — 2026-04-29).

    Spec: 03-proceso/prompt-implementacion-blade.md (sección 2 mapping COMPs 1-15)
          03-proceso/redesigned-mobile.html (HTML target)

    Estructura: Hero (Cap00) + Manifesto + 5 steps con mockup viz intercaladas:
        Step 01 — Diagnóstico (form mockup)
        Step 02 — El Match (3 coach cards con SVG rings)
        Step 03 — Tu Plan (PDF card + microciclo table)
        Pull-quote brutal
        Step 04 — Check-ins (chat + delta dashboard)
        Step 05 — Resultados (journey progress chart)
        CTA Final

    Decisiones tomadas:
        - Datos viz TODOS demo, con disclaimer obligatorio bajo cada uno.
        - Step2 NO menciona IA/algoritmo — usa "sistema de match" / "compatibilidad por afinidad".
        - Stats bar antigua eliminada (no estaba en redesign).
        - Reusa <x-layouts.public-editorial> para hereder topbar + footer + atmósfera.

    Layout:
        - Compose <x-layouts.public-editorial> (sidebar + chapter pill).
        - Sidebar editorial 220px sticky en ≥1024px (manejado por window.procesoPage()).
        - Main scroll area con steps full-bleed (storytelling).

    Variables del controller (ProcesoController@index):
        $monthlyEsencialCop  → JSON-LD HowTo offer.priceSpecification.
        $monthlyEsencialUsd  → fallback futuro USD.

    Voz: latino neutro estricto (tú/puedes/quieres/empieza/cancelas).
    NO voseo argentino (vos/pagás/empezá/podés/tenés).
    NO menciones IA/Claude/GPT/algoritmo/machine learning (feedback_ia_confidencial).
--}}

<x-layouts.public-editorial pageFactory="procesoPage()">
    <x-slot:title>{{ __('proceso.meta_title') }}</x-slot:title>
    <x-slot:description>{{ __('proceso.meta_description') }}</x-slot:description>

    {{-- Chapter pill mobile (sticky top entre 768-1023px). Bind a Alpine activePill. --}}
    <x-slot:chapterPill>
        <span x-text="activePill || '{{ __('proceso.chapters.cap00.pill') }}'">{{ __('proceso.chapters.cap00.pill') }}</span>
    </x-slot:chapterPill>

    {{-- Sidebar editorial (≥1024px). --}}
    <x-slot:sidebar>
        <x-public.editorial-sidebar
            :brand-sub="__('proceso.sidebar.subtitle')"
            :progress-label="__('proceso.sidebar.progress_label')"
            :cta-href="route('inscripcion')"
            :cta-text="__('proceso.sidebar.cta')"
            :chapters="[
                ['id' => 'cap-hero', 'num' => '00', 'title' => __('proceso.chapters.cap00.nav_title')],
                ['id' => 'step-1',   'num' => '01', 'title' => __('proceso.chapters.s1.nav_title')],
                ['id' => 'step-2',   'num' => '02', 'title' => __('proceso.chapters.s2.nav_title')],
                ['id' => 'step-3',   'num' => '03', 'title' => __('proceso.chapters.s3.nav_title')],
                ['id' => 'step-4',   'num' => '04', 'title' => __('proceso.chapters.s4.nav_title')],
                ['id' => 'step-5',   'num' => '05', 'title' => __('proceso.chapters.s5.nav_title')],
                ['id' => 'cta-final','num' => '→',  'title' => __('proceso.chapters.cta.nav_title')],
            ]"
            :nav-links="[
                ['href' => route('metodo'), 'text' => 'Método'],
                ['href' => route('planes'), 'text' => 'Planes'],
                ['href' => route('faq'),    'text' => 'FAQ'],
            ]"
        />
    </x-slot:sidebar>

    {{-- JSON-LD HowTo (preservando intent del schema legacy si existió). --}}
    <x-json-ld :data="[
        '@context' => 'https://schema.org',
        '@type' => 'HowTo',
        'name' => 'El Proceso WellCore — 5 pasos hacia tu transformación',
        'description' => 'Recorrido de 5 pasos: diagnóstico inicial, match con coach, plan personalizado, check-ins quincenales y resultados verificables.',
        'totalTime' => 'P12W',
        'tool' => [
            '@type' => 'HowToTool',
            'name' => 'Coaching 1:1 WellCore',
            'url' => url('/planes'),
        ],
        'estimatedCost' => [
            '@type' => 'MonetaryAmount',
            'currency' => 'COP',
            'value' => (string) ($monthlyEsencialCop ?? app(\App\Services\PricingService::class)->priceCop('esencial')),
        ],
        'step' => [
            ['@type' => 'HowToStep', 'name' => 'Diagnóstico inicial', 'text' => 'Formulario corto de 5 minutos que define punto de partida y objetivo.'],
            ['@type' => 'HowToStep', 'name' => 'Match con coach',     'text' => 'Sistema de compatibilidad por afinidad asigna el coach con mejor match.'],
            ['@type' => 'HowToStep', 'name' => 'Plan personalizado',  'text' => 'Entrenamiento, nutrición y hábitos diseñados sobre tus datos en 72h.'],
            ['@type' => 'HowToStep', 'name' => 'Check-ins quincenales','text' => 'Revisión cada 14 días con ajuste basado en datos reales.'],
            ['@type' => 'HowToStep', 'name' => 'Resultados verificables', 'text' => 'Métricas auditadas a las 8-12 semanas: peso, composición, adherencia.'],
        ],
    ]" />

    {{-- ──────────────────────────────────────────────────────────────
         Alpine root: window.procesoPage() factory.
         x-data declarado en el wrapper del layout via pageFactory prop
         (Sprint 4 audit fix — englobar chapterPill + sidebar slots).
         ────────────────────────────────────────────────────────────── --}}
    <div class="proceso-root metodo-main">

        {{-- ════════════════════════════════════════════════════════════
             CAP 00 — HERO
             ════════════════════════════════════════════════════════════ --}}
        <section class="proceso-hero"
                 id="cap-hero"
                 data-chapter="00"
                 data-chapter-label="{{ __('proceso.chapters.cap00.pill') }}">
            <div class="proceso-hero-bg-number" aria-hidden="true">5</div>
            <p class="proceso-hero-eyebrow">{{ __('proceso.hero.eyebrow') }}</p>
            <h1 class="proceso-hero-title">{!! __('proceso.hero.title_html') !!}</h1>
            <p class="proceso-hero-sub">{{ __('proceso.hero.sub') }}</p>
            <div class="proceso-hero-scroll-hint" aria-hidden="true">
                <div class="proceso-scroll-arrow">
                    <svg width="8" height="8" viewBox="0 0 8 8" fill="none">
                        <path d="M4 1v6M1 4.5l3 3 3-3" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <span>{{ __('proceso.hero.scroll_hint') }}</span>
            </div>
        </section>

        {{-- ════════════════════════════════════════════════════════════
             MANIFESTO (transición editorial entre Hero y Step 1)
             ════════════════════════════════════════════════════════════ --}}
        <section class="proceso-manifesto" data-animate="fadeInUp">
            <p class="proceso-manifesto-kicker">{{ __('proceso.manifesto.kicker') }}</p>
            <p class="proceso-manifesto-body">{{ __('proceso.manifesto.body') }}</p>
        </section>

        {{-- ════════════════════════════════════════════════════════════
             STEP 01 — DIAGNÓSTICO (form mockup)
             ════════════════════════════════════════════════════════════ --}}
        <section class="proceso-step"
                 id="step-1"
                 data-step="1"
                 data-chapter="01"
                 data-chapter-label="{{ __('proceso.chapters.s1.pill') }}">
            <span class="proceso-step-bg-number" aria-hidden="true">01</span>
            <div class="proceso-step-text-col">
                <div class="proceso-step-meta" data-animate="fadeInUp">
                    <span class="proceso-step-index">{{ __('proceso.step1.meta_index') }}</span>
                    <span class="proceso-step-timing">{{ __('proceso.step1.meta_timing') }}</span>
                </div>
                <h2 class="proceso-step-title" data-animate="fadeInUp">{!! __('proceso.step1.title_html') !!}</h2>
                <p class="proceso-step-desc" data-animate="fadeInUp">{{ __('proceso.step1.desc') }}</p>
                <p class="proceso-step-detail" data-animate="fadeInUp">{{ __('proceso.step1.detail') }}</p>
            </div>
            <div class="proceso-step-viz-col">
                <div class="proceso-step-viz" data-proceso-viz>
                    {{-- VIZ 1: Form mockup --}}
                    <div class="proceso-glass proceso-form-mockup" role="img" aria-label="Vista de ejemplo: formulario de diagnóstico">
                        <div class="proceso-form-header">
                            <span class="proceso-form-title">{{ __('proceso.step1.viz.header_label') }}</span>
                            <span class="proceso-form-duration">{{ __('proceso.step1.viz.duration') }}</span>
                        </div>
                        <p class="proceso-form-q">{{ __('proceso.step1.viz.question') }}</p>
                        <div class="proceso-form-opts">
                            @foreach (__('proceso.step1.viz.opts') as $i => $opt)
                                <div class="proceso-form-opt {{ $i === 0 ? 'is-selected' : '' }}">{{ $opt }}</div>
                            @endforeach
                        </div>
                        <p class="proceso-form-slider-q">{{ __('proceso.step1.viz.slider_q') }}</p>
                        <div class="proceso-form-slider-track">
                            <div class="proceso-form-slider-fill"></div>
                            <div class="proceso-form-slider-thumb" aria-hidden="true"></div>
                        </div>
                        <div class="proceso-form-slider-labels">
                            @foreach (__('proceso.step1.viz.slider_labels') as $lbl)
                                <span>{{ $lbl }}</span>
                            @endforeach
                        </div>
                        <div class="proceso-form-submit">{{ __('proceso.step1.viz.submit') }}</div>
                    </div>
                    <p class="proceso-step-disclaimer">{{ __('proceso.step1.disclaimer') }}</p>
                </div>
            </div>
        </section>

        {{-- ════════════════════════════════════════════════════════════
             STEP 02 — EL MATCH (3 coach cards con SVG rings)
             ════════════════════════════════════════════════════════════ --}}
        <section class="proceso-step"
                 id="step-2"
                 data-step="2"
                 data-chapter="02"
                 data-chapter-label="{{ __('proceso.chapters.s2.pill') }}">
            <span class="proceso-step-bg-number" aria-hidden="true">02</span>
            <div class="proceso-step-text-col">
                <div class="proceso-step-meta" data-animate="fadeInUp">
                    <span class="proceso-step-index">{{ __('proceso.step2.meta_index') }}</span>
                    <span class="proceso-step-timing">{{ __('proceso.step2.meta_timing') }}</span>
                </div>
                <h2 class="proceso-step-title" data-animate="fadeInUp">{!! __('proceso.step2.title_html') !!}</h2>
                <p class="proceso-step-desc" data-animate="fadeInUp">{{ __('proceso.step2.desc') }}</p>
                <p class="proceso-step-detail" data-animate="fadeInUp">{{ __('proceso.step2.detail') }}</p>
            </div>
            <div class="proceso-step-viz-col">
                <div class="proceso-step-viz" data-proceso-viz>
                    @php
                        // SVG ring math: 2 × π × r (r=24) ≈ 150.8
                        $circumference = 150.8;
                    @endphp
                    <div class="proceso-coaches-row" role="img" aria-label="Vista de ejemplo: 3 coaches sugeridos por compatibilidad">
                        @foreach (__('proceso.step2.coaches') as $coach)
                            @php
                                $match = (int) ($coach['match'] ?? 0);
                                $offset = round($circumference * (1 - $match / 100), 1);
                                $isBest = !empty($coach['best']);
                                $stroke = $isBest ? '#DC2626' : '#10B981';
                            @endphp
                            <div class="proceso-glass proceso-coach-card{{ $isBest ? ' is-best' : '' }}">
                                @if ($isBest)
                                    <div class="proceso-coach-best-tag">{{ __('proceso.step2.best_label') }}</div>
                                @endif
                                <div class="proceso-coach-ring-wrap">
                                    <svg class="proceso-coach-ring-svg" viewBox="0 0 56 56" aria-hidden="true">
                                        <circle cx="28" cy="28" r="24" fill="none" stroke="rgba(255,255,255,0.06)" stroke-width="3"/>
                                        <circle class="ring-progress"
                                                cx="28" cy="28" r="24"
                                                fill="none"
                                                stroke="{{ $stroke }}"
                                                stroke-width="3"
                                                stroke-dasharray="{{ $circumference }}"
                                                stroke-dashoffset="{{ $offset }}"
                                                stroke-linecap="round"
                                                transform="rotate(-90 28 28)"/>
                                    </svg>
                                    <div class="proceso-coach-avatar">{{ $coach['initials'] }}</div>
                                </div>
                                <div class="proceso-coach-name">{{ $coach['name'] }}</div>
                                <div class="proceso-coach-spec">{{ $coach['spec'] }}</div>
                                <div class="proceso-coach-match">{{ $match }}%</div>
                            </div>
                        @endforeach
                    </div>
                    <p class="proceso-step-disclaimer">{{ __('proceso.step2.disclaimer') }}</p>
                </div>
            </div>
        </section>

        {{-- ════════════════════════════════════════════════════════════
             STEP 03 — TU PLAN (PDF card + microciclo table)
             ════════════════════════════════════════════════════════════ --}}
        <section class="proceso-step"
                 id="step-3"
                 data-step="3"
                 data-chapter="03"
                 data-chapter-label="{{ __('proceso.chapters.s3.pill') }}">
            <span class="proceso-step-bg-number" aria-hidden="true">03</span>
            <div class="proceso-step-text-col">
                <div class="proceso-step-meta" data-animate="fadeInUp">
                    <span class="proceso-step-index">{{ __('proceso.step3.meta_index') }}</span>
                    <span class="proceso-step-timing">{{ __('proceso.step3.meta_timing') }}</span>
                </div>
                <h2 class="proceso-step-title" data-animate="fadeInUp">{!! __('proceso.step3.title_html') !!}</h2>
                <p class="proceso-step-desc" data-animate="fadeInUp">{{ __('proceso.step3.desc') }}</p>
                <p class="proceso-step-detail" data-animate="fadeInUp">{{ __('proceso.step3.detail') }}</p>
            </div>
            <div class="proceso-step-viz-col">
                <div class="proceso-step-viz" data-proceso-viz>
                    <div class="proceso-plan-wrap" role="img" aria-label="Vista de ejemplo: plan personalizado en PDF y microciclo semanal">
                        <div class="proceso-glass proceso-pdf-card">
                            <div class="proceso-pdf-icon" aria-hidden="true">PDF</div>
                            <div class="proceso-pdf-info">
                                <div class="proceso-pdf-filename">{{ __('proceso.step3.viz.pdf_filename') }}</div>
                                <div class="proceso-pdf-meta">{{ __('proceso.step3.viz.pdf_meta') }}</div>
                            </div>
                            <div class="proceso-pdf-download">{{ __('proceso.step3.viz.pdf_download') }}</div>
                        </div>
                        <div class="proceso-glass proceso-micro-wrap">
                            <table class="proceso-micro-table">
                                <thead>
                                    <tr>
                                        <th>{{ __('proceso.step3.viz.th_day') }}</th>
                                        <th>{{ __('proceso.step3.viz.th_session') }}</th>
                                        <th>{{ __('proceso.step3.viz.th_vol') }}</th>
                                        <th>{{ __('proceso.step3.viz.th_kcal') }}</th>
                                        <th>{{ __('proceso.step3.viz.th_type') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach (__('proceso.step3.viz.rows') as $row)
                                        <tr class="{{ !empty($row['focus']) ? 'is-focus' : '' }}">
                                            <td>{{ $row['day'] }}</td>
                                            <td>{{ $row['session'] }}</td>
                                            <td class="num">{{ $row['vol'] }}</td>
                                            <td class="num">{{ $row['kcal'] }}</td>
                                            <td><span class="proceso-tag-pill {{ $row['type_color'] ?? 'red' }}">{{ $row['type'] }}</span></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <p class="proceso-step-disclaimer">{{ __('proceso.step3.disclaimer') }}</p>
                </div>
            </div>
        </section>

        {{-- ════════════════════════════════════════════════════════════
             PULL-QUOTE BRUTAL (mid-journey break)
             ════════════════════════════════════════════════════════════ --}}
        <section class="proceso-pullquote-section"
                 id="pullquote"
                 data-chapter="pq"
                 data-chapter-label="{{ __('proceso.chapters.s3.pill') }}">
            <p class="proceso-pullquote-label">{{ __('proceso.pullquote.label') }}</p>
            <x-public.pullquote :cite="__('proceso.pullquote.cite')">
                {!! __('proceso.pullquote.text_html') !!}
            </x-public.pullquote>
            <div class="proceso-pullquote-bar" aria-hidden="true"></div>
        </section>

        {{-- ════════════════════════════════════════════════════════════
             STEP 04 — CHECK-INS (chat + delta dashboard)
             ════════════════════════════════════════════════════════════ --}}
        <section class="proceso-step"
                 id="step-4"
                 data-step="4"
                 data-chapter="04"
                 data-chapter-label="{{ __('proceso.chapters.s4.pill') }}">
            <span class="proceso-step-bg-number" aria-hidden="true">04</span>
            <div class="proceso-step-text-col">
                <div class="proceso-step-meta" data-animate="fadeInUp">
                    <span class="proceso-step-index">{{ __('proceso.step4.meta_index') }}</span>
                    <span class="proceso-step-timing">{{ __('proceso.step4.meta_timing') }}</span>
                </div>
                <h2 class="proceso-step-title" data-animate="fadeInUp">{!! __('proceso.step4.title_html') !!}</h2>
                <p class="proceso-step-desc" data-animate="fadeInUp">{{ __('proceso.step4.desc') }}</p>
                <p class="proceso-step-detail" data-animate="fadeInUp">{{ __('proceso.step4.detail') }}</p>
            </div>
            <div class="proceso-step-viz-col">
                <div class="proceso-step-viz" data-proceso-viz>
                    @php
                        $viz4 = __('proceso.step4.viz');
                        $m1 = $viz4['delta_metric_1'];
                        $m2 = $viz4['delta_metric_2'];
                    @endphp
                    <div class="proceso-checkin-wrap" role="img" aria-label="Vista de ejemplo: chat con coach y métricas quincenales">
                        <div class="proceso-glass proceso-chat-card">
                            <div class="proceso-chat-header">
                                <div class="proceso-chat-avatar">{{ $viz4['coach_avatar'] }}</div>
                                <div class="proceso-chat-meta">
                                    <div class="proceso-chat-name">{{ $viz4['coach_name'] }}</div>
                                    <div class="proceso-chat-status">{{ $viz4['coach_status'] }}</div>
                                </div>
                                <div class="proceso-chat-online" aria-hidden="true"></div>
                            </div>
                            <div class="proceso-chat-msgs">
                                @foreach ($viz4['msgs'] as $msg)
                                    <div class="proceso-chat-msg is-{{ $msg['role'] }}">{{ $msg['text'] }}</div>
                                @endforeach
                                <div class="proceso-chat-ts">{{ $viz4['msg_ts'] }}</div>
                            </div>
                        </div>
                        <div class="proceso-glass proceso-delta-card">
                            <div class="proceso-delta-header">{{ $viz4['delta_header'] }}</div>
                            <div class="proceso-delta-row">
                                <div class="proceso-delta-metric">
                                    <div class="proceso-delta-label">{{ $m1['label'] }}</div>
                                    <div class="proceso-delta-value is-{{ $m1['tone'] }}">
                                        {{ $m1['value'] }}<span class="unit"> {{ $m1['unit'] }}</span>
                                    </div>
                                    <div class="proceso-delta-desc">{{ $m1['desc'] }}</div>
                                    <div class="proceso-delta-bar-track">
                                        <div class="proceso-delta-bar-fill" style="--target-width: {{ (int) $m1['pct'] }}%;"></div>
                                    </div>
                                </div>
                                <div class="proceso-delta-metric">
                                    <div class="proceso-delta-label">{{ $m2['label'] }}</div>
                                    <div class="proceso-delta-value is-{{ $m2['tone'] }}">
                                        {{ $m2['value'] }}<span class="unit">{{ $m2['unit'] }}</span>
                                    </div>
                                    <div class="proceso-delta-desc">{{ $m2['desc'] }}</div>
                                    <div class="proceso-delta-bar-track">
                                        <div class="proceso-delta-bar-fill is-red" style="--target-width: {{ (int) $m2['pct'] }}%;"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <p class="proceso-step-disclaimer">{{ __('proceso.step4.disclaimer') }}</p>
                </div>
            </div>
        </section>

        {{-- ════════════════════════════════════════════════════════════
             STEP 05 — RESULTADOS (journey progress chart)
             ════════════════════════════════════════════════════════════ --}}
        <section class="proceso-step"
                 id="step-5"
                 data-step="5"
                 data-chapter="05"
                 data-chapter-label="{{ __('proceso.chapters.s5.pill') }}">
            <span class="proceso-step-bg-number" aria-hidden="true">05</span>
            <div class="proceso-step-text-col">
                <div class="proceso-step-meta" data-animate="fadeInUp">
                    <span class="proceso-step-index">{{ __('proceso.step5.meta_index') }}</span>
                    <span class="proceso-step-timing">{{ __('proceso.step5.meta_timing') }}</span>
                </div>
                <h2 class="proceso-step-title" data-animate="fadeInUp">{!! __('proceso.step5.title_html') !!}</h2>
                <p class="proceso-step-desc" data-animate="fadeInUp">{{ __('proceso.step5.desc') }}</p>
                <p class="proceso-step-detail" data-animate="fadeInUp">{{ __('proceso.step5.detail') }}</p>
            </div>
            <div class="proceso-step-viz-col">
                <div class="proceso-step-viz" data-proceso-viz>
                    @php $viz5 = __('proceso.step5.viz'); @endphp
                    <div class="proceso-glass proceso-chart-card" role="img" aria-label="Vista de ejemplo: gráfica de progreso de peso 8 semanas">
                        <div class="proceso-chart-header">
                            <div>
                                <div class="proceso-chart-label">{{ $viz5['chart_label'] }}</div>
                                <div class="proceso-chart-value">{{ $viz5['chart_value'] }}</div>
                            </div>
                            <div class="proceso-chart-pill">{{ $viz5['chart_pill'] }}</div>
                        </div>
                        <div class="proceso-chart-svg-wrap">
                            <svg class="proceso-chart-svg" viewBox="0 0 320 90" preserveAspectRatio="none" aria-hidden="true">
                                <defs>
                                    <linearGradient id="procesoChartGradient" x1="0" y1="0" x2="0" y2="1">
                                        <stop offset="0%" stop-color="#10B981" stop-opacity="0.25"/>
                                        <stop offset="100%" stop-color="#10B981" stop-opacity="0"/>
                                    </linearGradient>
                                </defs>
                                <path class="proceso-chart-area" d="M0,20 C30,22 55,30 80,35 C105,40 130,48 160,52 C190,56 215,60 240,62 C265,64 290,68 320,70 L320,90 L0,90 Z"/>
                                <path class="proceso-chart-line" d="M0,20 C30,22 55,30 80,35 C105,40 130,48 160,52 C190,56 215,60 240,62 C265,64 290,68 320,70"/>
                                <circle cx="0" cy="20" r="3" fill="#10B981" opacity="0.6"/>
                                <circle cx="160" cy="52" r="3" fill="#10B981" opacity="0.8"/>
                                <circle cx="320" cy="70" r="4" fill="#10B981"/>
                            </svg>
                            <div class="proceso-chart-baseline" aria-hidden="true"></div>
                        </div>
                        <div class="proceso-chart-axis">
                            @foreach ($viz5['axis_labels'] as $lbl)
                                <span>{{ $lbl }}</span>
                            @endforeach
                        </div>
                        <div class="proceso-chart-weeks">
                            @foreach ($viz5['weeks'] as $w)
                                <div class="proceso-chart-week-pill is-pass">{{ $w }}</div>
                            @endforeach
                        </div>
                    </div>
                    <p class="proceso-step-disclaimer">{{ __('proceso.step5.disclaimer') }}</p>
                </div>
            </div>
        </section>

        {{-- ════════════════════════════════════════════════════════════
             DIVIDER (entre Step 5 y CTA Final)
             ════════════════════════════════════════════════════════════ --}}
        <div class="proceso-divider" aria-hidden="true">
            <span>{{ __('proceso.divider') }}</span>
        </div>

        {{-- ════════════════════════════════════════════════════════════
             CTA FINAL
             ════════════════════════════════════════════════════════════ --}}
        <section class="proceso-cta-final"
                 id="cta-final"
                 data-chapter="cta"
                 data-chapter-label="{{ __('proceso.chapters.cta.pill') }}">
            <div class="proceso-cta-bg-text" aria-hidden="true">GO</div>
            <p class="proceso-cta-eyebrow">{{ __('proceso.cta_final.kicker') }}</p>
            <h2 class="proceso-cta-title">{!! __('proceso.cta_final.title_html') !!}</h2>
            <p class="proceso-cta-sub">{{ __('proceso.cta_final.sub') }}</p>
            <div class="proceso-cta-btns">
                <a href="{{ route('inscripcion') }}" class="btn-primary-v2">
                    <span>{{ __('proceso.cta_final.btn_primary') }}</span>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M5 12h14M13 5l7 7-7 7"/>
                    </svg>
                </a>
                <a href="{{ route('planes') }}" class="btn-ghost-v2">{{ __('proceso.cta_final.btn_secondary') }}</a>
            </div>
            <div class="proceso-cta-stats">
                @foreach (__('proceso.cta_final.stats') as $stat)
                    <div>
                        <div class="proceso-cta-stat-val">{{ $stat['val'] }}</div>
                        <div class="proceso-cta-stat-label">{{ $stat['label'] }}</div>
                    </div>
                @endforeach
            </div>
            <div class="proceso-cta-trust">
                @foreach (__('proceso.cta_final.trust_items') as $idx => $item)
                    @if ($idx > 0)<span class="sep" aria-hidden="true">·</span>@endif
                    <span>{{ $item }}</span>
                @endforeach
            </div>
        </section>

        {{-- ════════════════════════════════════════════════════════════
             Sticky mobile CTA (reusa <x-public.sticky-mobile-cta>)
             Visible tras 60% de scroll. Oculta automáticamente cuando
             #cta-final entra al viewport (evita doble CTA).
             ════════════════════════════════════════════════════════════ --}}
        <x-public.sticky-mobile-cta
            :href="route('inscripcion')"
            :label="__('proceso.sticky.text_strong')"
            :price="__('proceso.sticky.text')"
            hide-at="cta-final"
            :threshold="600"
        />
    </div>{{-- /proceso-root --}}

    {{-- window.procesoPage() viene del bundle alpine-public (importa ./proceso.js).
         Esa importación lo registra antes de Alpine.start(); no se necesita @vite() extra. --}}
</x-layouts.public-editorial>
