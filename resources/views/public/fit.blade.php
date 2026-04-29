<x-layouts.public bodyClass="fit-page">
    <x-slot:title>{{ __('fit.meta_title') }}</x-slot:title>
    <x-slot:description>{{ __('fit.meta_description') }}</x-slot:description>

    {{-- ================================================================== --}}
    {{-- COMP 2: HERO (split 2-col desktop)                                 --}}
    {{-- ================================================================== --}}
    <section class="fit-hero" aria-labelledby="fit-hero-title">
        <div class="fit-hero-content">
            <span class="fit-hero-label">{{ __('fit.hero_label_mono') }}</span>
            <h1 id="fit-hero-title" class="fit-hero-name" data-animate="fadeInUp">
                {!! __('fit.hero_name_html') !!}
            </h1>
            <p class="fit-hero-bio" data-animate="fadeInUp" data-stagger="1">
                {{ __('fit.hero_bio') }}
            </p>

            <div class="fit-hero-stats" data-animate="fadeInUp" data-stagger="2">
                @foreach(range(1,3) as $i)
                    <div class="fit-hero-stat">
                        <span class="fit-hero-stat-val">{{ __('fit.hero_stat'.$i.'_val') }}</span>
                        <span class="fit-hero-stat-lbl">{{ __('fit.hero_stat'.$i.'_lbl') }}</span>
                    </div>
                @endforeach
            </div>

            <div class="fit-hero-ctas" data-animate="fadeInUp" data-stagger="3">
                <a href="#entrenar" class="h2-btn-primary btn-press">
                    {{ __('fit.hero_cta_primary') }}
                </a>
                <a href="https://wa.me/{{ $whatsappSilvia }}?text=Hola%20Silvia%2C%20quiero%20informaci%C3%B3n"
                   target="_blank" rel="noopener"
                   class="h2-btn-ghost btn-press">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347Z"/>
                    </svg>
                    {{ __('fit.hero_cta_whatsapp') }}
                </a>
            </div>
        </div>

        <div class="fit-hero-photo fit-hero-photo-real" data-animate="fadeInUp" data-stagger="2">
            <picture>
                {{-- Mobile (≤768px) sirve la versión liviana 21KB --}}
                <source media="(max-width: 768px)"
                        srcset="{{ asset('images/coaches/silvia-hero-mobile.webp') }}?v=3"
                        type="image/webp">
                {{-- Desktop WebP 36KB --}}
                <source srcset="{{ asset('images/coaches/silvia-hero.webp') }}?v=3" type="image/webp">
                {{-- PNG fallback navegadores antiguos --}}
                <img src="{{ asset('images/coaches/silvia-hero.png') }}?v=3"
                     alt="Coach Silvia Martínez"
                     width="719" height="900"
                     loading="eager" fetchpriority="high" decoding="async">
            </picture>
        </div>
    </section>

    <x-public.s-divider label="MÉTODO · CIENCIA · COACHING FEMENINO" />

    {{-- ================================================================== --}}
    {{-- COMP 2.5: BIO EXTENDIDA + CERTIFICACIONES (recuperado del v1)      --}}
    {{-- ================================================================== --}}
    <section class="fit-bio" aria-labelledby="bio-title">
        <div class="h2-section-head" style="padding: 0 0 28px;">
            <p class="h2-eyebrow">{{ __('fit.bio_label') }}</p>
            <h2 id="bio-title" class="h2-section-title">{!! __('fit.bio_title') !!}</h2>
        </div>

        <p class="fit-manifiesto-body">{{ __('fit.bio_p1') }}</p>
        <p class="fit-manifiesto-body">{{ __('fit.bio_p2') }}</p>
        <p class="fit-manifiesto-body">{{ __('fit.bio_p3') }}</p>
    </section>

    {{-- ================================================================== --}}
    {{-- COMP 3: MANIFIESTO                                                 --}}
    {{-- ================================================================== --}}
    <section class="fit-manifiesto" aria-labelledby="manifiesto-title">
        <div class="h2-section-head" style="padding: 0 0 28px;">
            <p class="h2-eyebrow">{{ __('fit.manifiesto_label') }}</p>
            <h2 id="manifiesto-title" class="h2-section-title">{!! __('fit.manifiesto_title') !!}</h2>
        </div>

        <p class="fit-manifiesto-body">{{ __('fit.manifiesto_p1') }}</p>
        <p class="fit-manifiesto-body">{{ __('fit.manifiesto_p2') }}</p>
        <p class="fit-manifiesto-body">{{ __('fit.manifiesto_p3') }}</p>

        <blockquote class="fit-manifiesto-cite">
            {{ __('fit.manifiesto_cite') }}
        </blockquote>
        <p class="fit-manifiesto-author">{{ __('fit.manifiesto_author') }}</p>
    </section>

    <x-public.s-divider label="ESPECIALIDADES · 05 PILARES" />

    {{-- ================================================================== --}}
    {{-- COMP 4: ESPECIALIDADES (5 numeradas + 1 editorial)                  --}}
    {{-- ================================================================== --}}
    <section class="fit-spec" aria-labelledby="spec-title">
        <div class="h2-section-head" style="padding: 0 0 28px;">
            <p class="h2-eyebrow">{{ __('fit.spec_label') }}</p>
            <h2 id="spec-title" class="h2-section-title">{!! __('fit.spec_title') !!}</h2>
            <p class="h2-section-sub">{{ __('fit.spec_sub') }}</p>
        </div>

        <div class="fit-spec-list">
            @foreach(range(1,5) as $i)
                <div class="fit-spec-item" data-animate="fadeInUp" data-stagger="{{ min($i, 4) }}">
                    <span class="fit-spec-num">{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}</span>
                    <div>
                        <div class="fit-spec-title">{{ __('fit.spec_'.$i.'_title') }}</div>
                        <p class="fit-spec-desc">{{ __('fit.spec_'.$i.'_desc') }}</p>
                    </div>
                </div>
            @endforeach
        </div>

        <p class="fit-spec-editorial">"{{ __('fit.spec_editorial') }}"</p>
    </section>

    {{-- ================================================================== --}}
    {{-- COMP 4.5: DASHBOARD MOCKUP (recuperado del v1, paleta rosa Silvia) --}}
    {{-- ================================================================== --}}
    <section class="fit-mockup" aria-labelledby="fit-mockup-title">
        <div class="fit-mockup-content">
            <p class="h2-eyebrow h2-eyebrow-red">{{ __('fit.mockup_label') }}</p>
            <h2 id="fit-mockup-title" class="h2-section-title">{!! __('fit.mockup_title') !!}</h2>
            <p class="h2-section-body">{{ __('fit.mockup_body') }}</p>
        </div>

        <div class="fit-phone" aria-hidden="true">
            <div class="fit-phone-screen">
                <div class="fit-phone-coach-row">
                    <div class="fit-phone-coach-avatar">SM</div>
                    <div>
                        <div class="fit-phone-coach-name">{{ __('fit.mockup_coach') }}</div>
                        <div class="fit-phone-coach-status">● {{ __('fit.mockup_status') }}</div>
                    </div>
                </div>

                <div class="fit-phone-section-title">{{ __('fit.mockup_section_workout') }}</div>
                <div class="fit-phone-card">
                    <div class="fit-phone-card-name">{{ __('fit.mockup_workout_name') }}</div>
                    <div class="fit-phone-card-meta">{{ __('fit.mockup_workout_meta') }}</div>
                </div>

                <div class="fit-phone-section-title">{{ __('fit.mockup_section_habits') }}</div>
                @foreach(['water', 'protein', 'sleep', 'walk'] as $habit)
                    <div class="fit-phone-habit">
                        <span class="fit-phone-habit-dot"></span>
                        <span>{{ __('fit.mockup_habit_'.$habit) }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <x-public.s-divider label="EL PROCESO · CUATRO PASOS" />

    {{-- ================================================================== --}}
    {{-- COMP 5: PROCESO 4 PASOS                                            --}}
    {{-- ================================================================== --}}
    <section class="fit-process" aria-labelledby="fit-process-title">
        <div class="h2-section-head" style="padding: 0 0 28px;">
            <p class="h2-eyebrow">{{ __('fit.process_label') }}</p>
            <h2 id="fit-process-title" class="h2-section-title">{!! __('fit.process_title') !!}</h2>
            <p class="h2-section-sub">{{ __('fit.process_sub') }}</p>
        </div>

        <div class="fit-process-grid">
            @foreach(range(1,4) as $i)
                <div class="h2-step" data-animate="fadeInUp" data-stagger="{{ $i }}">
                    <span class="h2-step-num">{{ __('fit.process_'.$i.'_num') }}</span>
                    <span class="h2-step-label">{{ __('fit.process_'.$i.'_label') }}</span>
                    <h3 class="h2-step-title">{{ __('fit.process_'.$i.'_title') }}</h3>
                    <p class="h2-step-desc">{{ __('fit.process_'.$i.'_desc') }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- ================================================================== --}}
    {{-- COMP 6: BLOOMBERG TICKER (testimonios 100% anonimizados)            --}}
    {{-- ================================================================== --}}
    <x-public.bloomberg-ticker
        :duration="35"
        :aria_label="__('fit.ticker_aria')"
        :items="$tickerItems"
    />

    {{-- ================================================================== --}}
    {{-- COMP 7: PULL QUOTE BRUTAL                                          --}}
    {{-- ================================================================== --}}
    <x-public.pullquote :cite="__('fit.pullquote_cite')">
        {!! __('fit.pullquote_text') !!}
    </x-public.pullquote>

    <x-public.s-divider label="PLANES · ELIGE TU NIVEL" />

    {{-- ================================================================== --}}
    {{-- COMP 8: PRICING — 3 planes con toggle billing (estilo /planes)     --}}
    {{-- ================================================================== --}}
    <section class="fit-pricing-section"
             id="entrenar"
             aria-labelledby="fit-pricing-title"
             x-data="{ period: 'mensual' }">
        <div class="h2-section-head" style="padding: 60px 22px 28px;">
            <p class="h2-eyebrow h2-eyebrow-red">{{ __('fit.pricing_label') }}</p>
            <h2 id="fit-pricing-title" class="h2-section-title">{!! __('fit.pricing_title') !!}</h2>
            <p class="h2-section-sub">{{ __('fit.pricing_sub') }}</p>
        </div>

        {{-- Proof bar --}}
        <div class="fit-proof-bar" data-animate="fadeInUp">
            <div class="fit-proof-item">
                <span class="fit-proof-dot fit-proof-dot-pink"></span>
                <span><strong>+45</strong> mujeres en proceso</span>
            </div>
            <div class="fit-proof-item">
                <span class="fit-proof-pills" aria-hidden="true">
                    <span></span><span></span><span></span>
                </span>
                <span><strong>96%</strong> adherencia</span>
            </div>
            <div class="fit-proof-item">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" aria-hidden="true">
                    <path d="m4.5 12.75 6 6 9-13.5"/>
                </svg>
                <span>Verificado por <strong>WellCore</strong></span>
            </div>
        </div>

        {{-- Billing toggle --}}
        <div class="fit-billing-toggle" role="tablist" aria-label="Período de facturación">
            @foreach(['mensual', 'trimestral', 'anual'] as $p)
                <button type="button"
                        class="fit-b-pill"
                        :class="{ 'is-active': period === '{{ $p }}' }"
                        :aria-selected="period === '{{ $p }}'"
                        role="tab"
                        @click="period = '{{ $p }}'">
                    <span class="fit-b-pill-name">{{ __('fit.pricing_billing_'.$p) }}</span>
                    @if($p === 'trimestral')
                        <span class="fit-b-pill-save">{{ __('fit.pricing_discount_trim') }}</span>
                    @elseif($p === 'anual')
                        <span class="fit-b-pill-save">{{ __('fit.pricing_discount_anual') }}</span>
                    @endif
                </button>
            @endforeach
        </div>

        {{-- 3 Tier cards --}}
        <div class="fit-tier-track">
            @php
                $silviaPlans = [
                    ['key' => 'esencial', 'badge' => null,                              'is_featured' => false],
                    ['key' => 'metodo',   'badge' => __('fit.pricing_metodo_badge'),    'is_featured' => true ],
                    ['key' => 'intimo',   'badge' => __('fit.pricing_intimo_badge'),    'is_featured' => false],
                ];
            @endphp
            @foreach($silviaPlans as $i => $p)
                <article class="fit-t-card fit-t-card-{{ $p['key'] }} {{ $p['is_featured'] ? 'is-featured' : '' }}"
                         data-animate="fadeInUp"
                         @if($i > 0) data-stagger="{{ $i }}" @endif>

                    @if($p['badge'])
                        <span class="fit-t-badge {{ $p['key'] === 'metodo' ? 'badge-gold' : 'badge-pink' }}">{{ $p['badge'] }}</span>
                    @endif

                    <h3 class="fit-t-name">{{ __('fit.pricing_'.$p['key'].'_name') }}</h3>

                    <div class="fit-t-price-block">
                        <span class="fit-t-price-sym">$</span>
                        <span class="fit-t-price-num"
                              x-text="({{ json_encode($pricesUsd[$p['key']]) }})[period]">{{ $pricesUsd[$p['key']]['mensual'] }}</span>
                        <span class="fit-t-price-cop">{{ __('fit.pricing_usd_mes') }}</span>
                        <span class="fit-t-price-note"
                              x-text="period === 'trimestral' ? 'Pagas $' + ({{ json_encode($totalsUsd[$p['key']]) }})['trimestral'] + ' · ahorras $' + ({{ json_encode($savingsUsd[$p['key']]) }})['trimestral'] : (period === 'anual' ? 'Pagas $' + ({{ json_encode($totalsUsd[$p['key']]) }})['anual'] + ' · ahorras $' + ({{ json_encode($savingsUsd[$p['key']]) }})['anual'] : '')">&nbsp;</span>
                    </div>

                    <p class="fit-t-quote">{{ __('fit.pricing_'.$p['key'].'_quote') }}</p>

                    <ul class="fit-t-pillars">
                        @foreach(['p1', 'p2', 'p3'] as $pillar)
                            <li class="fit-t-pillar">
                                <span class="fit-t-pillar-dot" aria-hidden="true"></span>
                                <span class="fit-t-pillar-text">{{ __('fit.pricing_'.$p['key'].'_'.$pillar) }}</span>
                            </li>
                        @endforeach
                    </ul>

                    @if($p['key'] === 'intimo')
                        {{-- Íntimo va por WhatsApp con Silvia (aplicación, no checkout directo) --}}
                        <a href="https://wa.me/{{ $whatsappSilvia }}?text=Hola%20Silvia%2C%20quiero%20aplicar%20al%20Plan%20%C3%8Dntimo"
                           target="_blank" rel="noopener"
                           class="fit-t-cta fit-t-cta-{{ $p['key'] }}">
                            {{ __('fit.pricing_'.$p['key'].'_cta') }}
                        </a>
                    @else
                        <a :href="`{{ route('inscripcion') }}?plan={{ $p['key'] }}&coach=silvia&period=${period}`"
                           class="fit-t-cta fit-t-cta-{{ $p['key'] }}">
                            {{ __('fit.pricing_'.$p['key'].'_cta') }}
                        </a>
                    @endif
                </article>
            @endforeach
        </div>

        <p class="fit-pricing-note">{{ __('fit.pricing_note') }}</p>
    </section>

    {{-- ================================================================== --}}
    {{-- COMP 8.5: EN TODOS LOS PLANES (8 features grid)                    --}}
    {{-- ================================================================== --}}
    <section class="fit-incl" aria-labelledby="fit-incl-title">
        <div class="fit-incl-head">
            <p class="h2-eyebrow h2-eyebrow-red">{{ __('fit.incl_label') }}</p>
            <h2 id="fit-incl-title" class="h2-section-title" style="text-align:left; max-width: 720px;">{!! __('fit.incl_title') !!}</h2>
            <p class="fit-incl-sub">{{ __('fit.incl_sub') }}</p>
        </div>

        <div class="fit-incl-grid">
            @php
                $incl = [
                    1 => ['icon' => '<path d="M12 2a3 3 0 0 0-3 3v6a3 3 0 0 0 6 0V5a3 3 0 0 0-3-3Z"/><path d="M19 10v1a7 7 0 0 1-14 0v-1"/><path d="M12 18v3M8 21h8"/>',                                                                                                                                  'has_badge' => true],
                    2 => ['icon' => '<circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>',                                                                                                                                                                                                  'has_badge' => true],
                    3 => ['icon' => '<path d="M7 7h10M7 12h10M7 17h6"/><circle cx="19" cy="17" r="2"/>',                                                                                                                                                                                       'has_badge' => false],
                    4 => ['icon' => '<rect x="3" y="6" width="14" height="12" rx="2"/><path d="m17 10 4-2v8l-4-2z"/>',                                                                                                                                                                       'has_badge' => false],
                    5 => ['icon' => '<path d="M12 21s-7-4.5-7-11a7 7 0 0 1 14 0c0 6.5-7 11-7 11Z"/><circle cx="12" cy="10" r="2.5"/>',                                                                                                                                                       'has_badge' => false],
                    6 => ['icon' => '<rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>',                                                                                                                                                                  'has_badge' => false],
                    7 => ['icon' => '<path d="M3 3v18h18"/><path d="m7 14 4-4 3 3 5-5"/>',                                                                                                                                                                                                    'has_badge' => false],
                    8 => ['icon' => '<circle cx="12" cy="12" r="9"/><path d="m9 12 2 2 4-4"/>',                                                                                                                                                                                              'has_badge' => false],
                ];
            @endphp
            @foreach($incl as $n => $item)
                <article class="fit-incl-card {{ $item['has_badge'] ? 'is-highlight' : '' }}" data-animate="fadeInUp" data-stagger="{{ ($n - 1) % 4 + 1 }}">
                    <div class="fit-incl-icon" aria-hidden="true">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                            {!! $item['icon'] !!}
                        </svg>
                    </div>
                    @if($item['has_badge'])
                        <span class="fit-incl-badge">{{ __('fit.incl_'.$n.'_badge') }}</span>
                    @endif
                    <h3 class="fit-incl-title">{{ __('fit.incl_'.$n.'_title') }}</h3>
                    <p class="fit-incl-desc">{{ __('fit.incl_'.$n.'_desc') }}</p>
                </article>
            @endforeach
        </div>
    </section>

    <x-public.s-divider label="FAQ · LO QUE PREGUNTAN" />

    {{-- ================================================================== --}}
    {{-- COMP 9: FAQ ACORDEÓN                                               --}}
    {{-- ================================================================== --}}
    <section class="h2-faq" aria-labelledby="fit-faq-title">
        <div class="h2-section-head" style="padding: 0 0 28px;">
            <p class="h2-eyebrow">{{ __('fit.faq_label') }}</p>
            <h2 id="fit-faq-title" class="h2-section-title">{!! __('fit.faq_title') !!}</h2>
        </div>

        <div class="h2-faq-list">
            @foreach(range(1,6) as $i)
                <details class="faq-accordion-item">
                    <summary class="faq-accordion-summary">
                        <span>{{ __('fit.faq_q'.$i) }}</span>
                        <svg class="faq-accordion-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M6 9l6 6 6-6"/>
                        </svg>
                    </summary>
                    <div class="faq-accordion-body">
                        {{ __('fit.faq_a'.$i) }}
                    </div>
                </details>
            @endforeach
        </div>
    </section>

    {{-- ================================================================== --}}
    {{-- COMP 10: CTA FINAL                                                  --}}
    {{-- ================================================================== --}}
    <section class="h2-cta-final" id="cta-final" aria-labelledby="fit-cta-final-title">
        <div class="h2-cta-final-inner">
            <p class="h2-eyebrow">{{ __('fit.cta_label') }}</p>
            <h2 id="fit-cta-final-title" class="h2-cta-final-title" data-animate="fadeInUp">
                {!! __('fit.cta_title') !!}
            </h2>
            <p class="h2-cta-final-sub" data-animate="fadeInUp" data-stagger="1">
                {{ __('fit.cta_sub') }}
            </p>

            <div class="h2-cta-final-ctas" data-animate="fadeInUp" data-stagger="2">
                <a href="#entrenar" class="h2-btn-primary btn-press">
                    {{ __('fit.cta_primary') }}
                </a>
                <a href="https://wa.me/{{ $whatsappSilvia }}?text=Hola%20Silvia"
                   target="_blank" rel="noopener"
                   class="h2-btn-ghost btn-press">
                    {{ __('fit.cta_whatsapp') }}
                </a>
            </div>

            <div class="h2-cta-final-trust" data-animate="fadeInUp" data-stagger="3">
                <span>{{ __('fit.cta_trust1') }}</span>
                <span class="h2-trust-sep">·</span>
                <span>{{ __('fit.cta_trust2') }}</span>
                <span class="h2-trust-sep">·</span>
                <span>{{ __('fit.cta_trust3') }}</span>
            </div>
        </div>
    </section>

    {{-- ================================================================== --}}
    {{-- COMP 12: STICKY CTA MOBILE (variant fit, rosa)                     --}}
    {{-- ================================================================== --}}
    <x-public.sticky-mobile-cta
        href="#entrenar"
        :label="__('fit.sticky_cta_text')"
        hide-at="cta-final"
        :threshold="600"
        variant="fit"
    />
</x-layouts.public>
