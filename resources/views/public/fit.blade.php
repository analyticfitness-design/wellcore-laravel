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

        <div class="fit-hero-photo" data-animate="fadeInUp" data-stagger="2" aria-hidden="true">
            {{-- TODO: reemplazar por foto autorizada de Silvia --}}
            <div class="fit-hero-photo-placeholder">
                <div class="fit-hero-photo-initials">SM</div>
                <div class="fit-hero-photo-caption">foto · silvia martínez</div>
            </div>
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

        <div class="fit-bio-cert">
            <span class="fit-bio-cert-pill">{{ __('fit.bio_cert_1') }}</span>
            <span class="fit-bio-cert-pill">{{ __('fit.bio_cert_2') }}</span>
            <span class="fit-bio-cert-pill">{{ __('fit.bio_cert_3') }}</span>
        </div>

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

    <x-public.s-divider label="INVERSIÓN · ENTRENAR CON SILVIA" />

    {{-- ================================================================== --}}
    {{-- COMP 8: PRICING                                                    --}}
    {{-- ================================================================== --}}
    <section class="fit-pricing" id="entrenar" aria-labelledby="fit-pricing-title">
        <div class="h2-section-head" style="padding: 0 0 12px;">
            <p class="h2-eyebrow">{{ __('fit.pricing_label') }}</p>
            <h2 id="fit-pricing-title" class="h2-section-title">{!! __('fit.pricing_title') !!}</h2>
            <p class="h2-section-sub">{{ __('fit.pricing_sub') }}</p>
        </div>

        <article class="fit-pricing-card" data-animate="fadeInUp">
            <h3 class="fit-pricing-name">{{ __('fit.pricing_plan_name') }}</h3>
            <p class="fit-pricing-tagline">{{ __('fit.pricing_plan_tagline') }}</p>

            <div class="fit-pricing-price">
                <span class="fit-pricing-amount">
                    {!! __('fit.pricing_price_html') !!}
                </span>
                <span class="fit-pricing-period">{{ __('fit.pricing_period') }}</span>
            </div>

            <ul class="fit-pricing-features">
                @foreach(range(1,5) as $i)
                    <li>{{ __('fit.pricing_feat'.$i) }}</li>
                @endforeach
            </ul>

            {{-- TODO: validar precio real con Silvia ($180 USD es base de referencia) --}}
            <a href="https://wa.me/{{ $whatsappSilvia }}?text=Hola%20Silvia%2C%20quiero%20aplicar%20al%20protocolo"
               target="_blank" rel="noopener"
               class="h2-btn-primary fit-pricing-cta btn-press">
                {{ __('fit.pricing_cta') }} →
            </a>
            <p class="fit-pricing-note">{{ __('fit.pricing_note') }}</p>
        </article>
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
