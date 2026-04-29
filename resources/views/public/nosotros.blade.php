{{--
    /nosotros · brand storytelling editorial v2 (porting Sprint — 2026-04-29).

    Spec: 05-nosotros/prompt-implementacion-blade.md
          05-nosotros/redesigned-mobile.html

    Estructura (5 capítulos + CTA):
        Cap 00 — Hero founder (Daniel Esparza)
        Cap 01 — Manifiesto editorial (drop-cap + 4 párrafos + firma)
        Cap 02 — Historia (timeline 5 hitos: 2018 → 2026)
        Cap 03 — Equipo (6 personas: Daniel con bio + 5 placeholders)
        Cap 04 — Valores (3 pull-quote brutales)
        CTA Suave — invitación, no urgencia (sin countdowns)

    Decisiones tomadas (en prompt LA-03):
        - Daniel Esparza con bio completa (founder público).
        - 5 placeholders con iniciales CR/MV/LM/JR/SB + roles genéricos.
          NO bios largas inventadas (sin autorización).
        - Timeline 5 hitos: 2018 (fundación) → 2020 → 2022 → 2024 → 2026 (en curso).
        - 3 valores literales del prompt:
            "No prometemos milagros." / "Tu progreso es tuyo." / "La ciencia no es opcional."
        - CTA final SUAVE — invita a /metodo, no urgencia.
        - NO mención IA / Claude / GPT / algoritmo / machine learning.

    Layout: <x-layouts.public-editorial> (sidebar 220px sticky + main scroll area).
    Voz: latino neutro estricto (tú/puedes/quieres/sabes).
--}}

<x-layouts.public-editorial>
    <x-slot:title>{{ __('nosotros.meta_title') }}</x-slot:title>
    <x-slot:description>{{ __('nosotros.meta_description') }}</x-slot:description>

    {{-- Chapter pill mobile (sticky top entre 768-1023px). Bind a Alpine activePill. --}}
    <x-slot:chapterPill>
        <span x-text="activePill || '{{ __('nosotros.chapters.cap00.pill') }}'">{{ __('nosotros.chapters.cap00.pill') }}</span>
    </x-slot:chapterPill>

    {{-- Sidebar editorial (≥1024px). --}}
    <x-slot:sidebar>
        <x-public.editorial-sidebar
            :brand-sub="__('nosotros.sidebar.subtitle')"
            :progress-label="__('nosotros.sidebar.progress_label')"
            :cta-href="route('metodo')"
            :cta-text="__('nosotros.sidebar.cta')"
            :chapters="[
                ['id' => 'cap-hero', 'num' => '00', 'title' => __('nosotros.chapters.cap00.nav_title')],
                ['id' => 'cap-manifiesto', 'num' => '01', 'title' => __('nosotros.chapters.s1.nav_title')],
                ['id' => 'cap-historia',   'num' => '02', 'title' => __('nosotros.chapters.s2.nav_title')],
                ['id' => 'cap-equipo',     'num' => '03', 'title' => __('nosotros.chapters.s3.nav_title')],
                ['id' => 'cap-valores',    'num' => '04', 'title' => __('nosotros.chapters.s4.nav_title')],
                ['id' => 'cta-final',      'num' => '→',  'title' => __('nosotros.chapters.cta.nav_title')],
            ]"
            :nav-links="[
                ['href' => route('metodo'),  'text' => 'Método'],
                ['href' => route('proceso'), 'text' => 'Proceso'],
                ['href' => route('planes'),  'text' => 'Planes'],
            ]"
        />
    </x-slot:sidebar>

    {{-- JSON-LD Organization (preserva intent del schema legacy). --}}
    <x-json-ld :data="[
        '@context' => 'https://schema.org',
        '@type' => 'Organization',
        'name' => 'WellCore Fitness',
        'url' => url('/'),
        'logo' => url('/images/logo-dark.png'),
        'description' => 'WellCore Fitness — coaching basado en ciencia, sin milagros. Fundada en Bucaramanga, Colombia. Atendemos clientes en LATAM con método estructurado, no atajos.',
        'foundingDate' => '2018',
        'foundingLocation' => [
            '@type' => 'Place',
            'name' => 'Bucaramanga, Santander, Colombia',
        ],
        'address' => [
            '@type' => 'PostalAddress',
            'addressLocality' => 'Bucaramanga',
            'addressRegion' => 'Santander',
            'addressCountry' => 'CO',
        ],
        'areaServed' => [
            '@type' => 'Place',
            'name' => 'Latinoamerica',
        ],
        'sameAs' => [
            'https://www.instagram.com/wellcore.fitness/',
            'https://www.youtube.com/@Wellcorefitness',
        ],
        'knowsAbout' => [
            'Entrenamiento de fuerza basado en evidencia',
            'Nutrición deportiva',
            'Coaching fitness 1:1',
            'Periodización de entrenamiento',
        ],
        'founder' => [
            '@type' => 'Person',
            'name' => 'Daniel Esparza',
            'jobTitle' => 'CEO · Fundador',
        ],
    ]" />

    {{-- ──────────────────────────────────────────────────────────────
         Alpine root: window.nosotrosPage() factory (resources/js/nosotros.js)
         Maneja activeChapter, scrollProgress, activePill, reveal observers.
         Reusa .metodo-main (espejo de /proceso) para el flow scroll area.
         ────────────────────────────────────────────────────────────── --}}
    <div class="nosotros-root metodo-main"
         x-data="nosotrosPage()"
         x-init="init()"
         @beforeunload.window="destroy()">

        {{-- ════════════════════════════════════════════════════════════
             CAP 00 — HERO FOUNDER
             ════════════════════════════════════════════════════════════ --}}
        <section class="nosotros-hero"
                 id="cap-hero"
                 data-chapter="00"
                 data-chapter-label="{{ __('nosotros.chapters.cap00.pill') }}">
            <div class="nosotros-hero-bg" aria-hidden="true">
                <div class="nosotros-hero-bg-stripes"></div>
                <div class="nosotros-hero-bg-grain"></div>
                <div class="nosotros-hero-bg-overlay"></div>
            </div>

            <div class="nosotros-hero-content">
                <p class="nosotros-hero-eyebrow">
                    <span class="dot" aria-hidden="true"></span>
                    {{ __('nosotros.hero.eyebrow') }}
                </p>
                <h1 class="nosotros-hero-title">{!! __('nosotros.hero.title_html') !!}</h1>
                <p class="nosotros-hero-sub">{{ __('nosotros.hero.sub') }}</p>

                <div class="nosotros-hero-meta">
                    @foreach (__('nosotros.hero.meta') as $item)
                        <div class="nosotros-hero-meta-item">
                            <span class="k">{{ $item['k'] }}</span>
                            <span class="v">{{ $item['v'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- ════════════════════════════════════════════════════════════
             CAP 01 — MANIFIESTO EDITORIAL
             ════════════════════════════════════════════════════════════ --}}
        <x-public.s-divider :label="'§ 01 · ' . __('nosotros.chapters.s1.nav_title')" />

        <section class="nosotros-manifiesto"
                 id="cap-manifiesto"
                 data-chapter="01"
                 data-chapter-label="{{ __('nosotros.chapters.s1.pill') }}">
            <p class="nosotros-section-tag">{{ __('nosotros.manifiesto.tag') }}</p>

            <div class="nosotros-manifiesto-body">
                <x-public.dropcap-paragraph>
                    {!! __('nosotros.manifiesto.p1_html') !!}
                </x-public.dropcap-paragraph>

                <p class="nosotros-manifiesto-p" data-animate="fadeInUp">
                    {!! __('nosotros.manifiesto.p2_html') !!}
                </p>
                <p class="nosotros-manifiesto-p" data-animate="fadeInUp">
                    {!! __('nosotros.manifiesto.p3_html') !!}
                </p>
                <p class="nosotros-manifiesto-p" data-animate="fadeInUp">
                    {!! __('nosotros.manifiesto.p4_html') !!}
                </p>
            </div>

            <div class="nosotros-signature" data-animate="fadeInUp">
                <div class="nosotros-sig-avatar" aria-hidden="true">
                    <span>DE</span>
                </div>
                <div class="nosotros-sig-meta">
                    <div class="nosotros-sig-name">{{ __('nosotros.manifiesto.sig_name') }}</div>
                    <div class="nosotros-sig-role">{{ __('nosotros.manifiesto.sig_role') }}</div>
                </div>
                <div class="nosotros-sig-mark" aria-hidden="true">§</div>
            </div>
        </section>

        {{-- ════════════════════════════════════════════════════════════
             CAP 02 — HISTORIA (timeline 5 hitos)
             ════════════════════════════════════════════════════════════ --}}
        <x-public.s-divider :label="'§ 02 · ' . __('nosotros.chapters.s2.nav_title')" />

        <section class="nosotros-historia"
                 id="cap-historia"
                 data-chapter="02"
                 data-chapter-label="{{ __('nosotros.chapters.s2.pill') }}">
            <p class="nosotros-tl-intro" data-animate="fadeInUp">{{ __('nosotros.timeline.intro') }}</p>
            <p class="nosotros-tl-intro-sub" data-animate="fadeInUp">{{ __('nosotros.timeline.intro_sub') }}</p>

            <ol class="nosotros-tl-track" aria-label="Línea de tiempo de WellCore">
                @foreach (__('nosotros.timeline.items') as $item)
                    <li class="nosotros-tl-item is-{{ $item['state'] ?? 'done' }}"
                        data-nosotros-reveal>
                        <div class="nosotros-tl-year">
                            <span>{{ $item['year'] }}</span>
                            @if (!empty($item['tag']))
                                <span class="nosotros-tl-tag">{{ $item['tag'] }}</span>
                            @endif
                        </div>
                        <h3 class="nosotros-tl-title">{!! $item['title_html'] !!}</h3>
                        <p class="nosotros-tl-desc">{{ $item['desc'] }}</p>
                        @if (!empty($item['meta']))
                            <div class="nosotros-tl-meta">
                                @foreach ($item['meta'] as $idx => $m)
                                    @if ($idx > 0)<span class="bullet" aria-hidden="true"></span>@endif
                                    <span>{{ $m }}</span>
                                @endforeach
                            </div>
                        @endif
                    </li>
                @endforeach
            </ol>
        </section>

        {{-- ════════════════════════════════════════════════════════════
             CAP 03 — EQUIPO (6 personas)
             ════════════════════════════════════════════════════════════ --}}
        <x-public.s-divider :label="'§ 03 · ' . __('nosotros.chapters.s3.nav_title')" />

        <section class="nosotros-equipo"
                 id="cap-equipo"
                 data-chapter="03"
                 data-chapter-label="{{ __('nosotros.chapters.s3.pill') }}">
            <p class="nosotros-eq-intro" data-animate="fadeInUp">{{ __('nosotros.equipo.intro') }}</p>
            <p class="nosotros-eq-intro-sub" data-animate="fadeInUp">{{ __('nosotros.equipo.intro_sub') }}</p>

            <div class="nosotros-eq-grid">
                @foreach (__('nosotros.equipo.members') as $member)
                    @php $isFeatured = !empty($member['featured']); @endphp
                    <article class="nosotros-eq-card{{ $isFeatured ? ' is-featured' : '' }}"
                             data-nosotros-reveal>
                        <div class="nosotros-eq-photo" aria-hidden="true">
                            <span class="nosotros-eq-photo-num">{{ $member['num'] }}</span>
                            <span class="nosotros-eq-photo-initials">{{ $member['initials'] }}</span>
                        </div>
                        <div class="nosotros-eq-body">
                            <h3 class="nosotros-eq-name">{!! $member['name_html'] !!}</h3>
                            <div class="nosotros-eq-role">{{ $member['role'] }}</div>
                            @if ($isFeatured && !empty($member['bio']))
                                <p class="nosotros-eq-bio">{{ $member['bio'] }}</p>
                            @endif
                            <p class="nosotros-eq-quote">{{ $member['quote'] }}</p>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>

        {{-- ════════════════════════════════════════════════════════════
             CAP 04 — VALORES (3 pull-quote brutales)
             ════════════════════════════════════════════════════════════ --}}
        <x-public.s-divider :label="'§ 04 · ' . __('nosotros.chapters.s4.nav_title')" />

        <section class="nosotros-valores"
                 id="cap-valores"
                 data-chapter="04"
                 data-chapter-label="{{ __('nosotros.chapters.s4.pill') }}">
            <p class="nosotros-val-intro" data-animate="fadeInUp">{{ __('nosotros.valores.intro') }}</p>
            <p class="nosotros-val-headline" data-animate="fadeInUp">{{ __('nosotros.valores.headline') }}</p>

            <div class="nosotros-val-list">
                @foreach (__('nosotros.valores.items') as $val)
                    <div class="nosotros-val-pq" data-nosotros-reveal>
                        <span class="nosotros-val-pq-num" aria-hidden="true">{{ $val['num'] }}</span>
                        <h3 class="nosotros-val-statement">{!! $val['statement_html'] !!}</h3>
                        <p class="nosotros-val-context">{!! $val['context_html'] !!}</p>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- ════════════════════════════════════════════════════════════
             CTA SUAVE (invitación, no urgencia)
             ════════════════════════════════════════════════════════════ --}}
        <section class="nosotros-cta"
                 id="cta-final"
                 data-chapter="cta"
                 data-chapter-label="{{ __('nosotros.chapters.cta.pill') }}">
            <div class="nosotros-cta-inner">
                <p class="nosotros-cta-kicker">{{ __('nosotros.cta_suave.kicker') }}</p>
                <h2 class="nosotros-cta-title">{!! __('nosotros.cta_suave.title_html') !!}</h2>
                <p class="nosotros-cta-sub">{{ __('nosotros.cta_suave.sub') }}</p>

                <div class="nosotros-cta-actions">
                    <a href="{{ route('metodo') }}" class="nosotros-cta-link">
                        <span class="arrow" aria-hidden="true">→</span>
                        {{ __('nosotros.cta_suave.btn_primary') }}
                    </a>
                    <a href="{{ route('planes') }}" class="nosotros-cta-link-soft">
                        {{ __('nosotros.cta_suave.btn_secondary') }}
                    </a>
                </div>

                <div class="nosotros-cta-foot">
                    <span class="nosotros-cta-foot-line">{{ __('nosotros.cta_suave.foot_line') }}</span>
                </div>
            </div>
        </section>
    </div>
</x-layouts.public-editorial>
