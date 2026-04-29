<x-layouts.public>
    <x-slot:title>{{ __('planes.meta_title') }}</x-slot>
    <x-slot:description>{{ __('planes.meta_description') }}</x-slot>

    {{-- ─── JSON-LD: Service + OfferCatalog (mantenemos el del v1, usa PricingService) ─── --}}
    <x-json-ld :data="[
        '@context' => 'https://schema.org',
        '@type' => 'Service',
        'name' => 'WellCore Fitness Coaching',
        'provider' => ['@type' => 'Organization', 'name' => 'WellCore Fitness'],
        'description' => 'Planes de coaching fitness personalizado desde $'.number_format($monthlyCop['esencial'], 0, ',', '.').' COP/mes.',
        'areaServed' => ['@type' => 'Place', 'name' => 'Latinoamerica'],
        'hasOfferCatalog' => [
            '@type' => 'OfferCatalog',
            'name' => 'Planes WellCore',
            'itemListElement' => [
                ['@type' => 'Offer', 'name' => 'Esencial', 'price' => (string) $monthlyCop['esencial'], 'priceCurrency' => 'COP'],
                ['@type' => 'Offer', 'name' => 'Metodo',   'price' => (string) $monthlyCop['metodo'],   'priceCurrency' => 'COP'],
                ['@type' => 'Offer', 'name' => 'Elite',    'price' => (string) $monthlyCop['elite'],    'priceCurrency' => 'COP'],
            ],
        ],
    ]" />

    {{-- ─── ROOT Alpine: estado global de la página /planes ───
         period (mensual|trimestral|anual) y selectedPlan (esencial|metodo|elite) son shared
         entre BillingToggle, TierCards, ComparadorTable, CTAFinal y StickyCTABottom.
         pricesCop/Usd/totals/savings vienen del controller (PricingService como SoT). --}}
    <div
        class="planes-root"
        x-data="{
            period: 'mensual',
            selectedPlan: 'metodo',
            ctaInView: false,
            locale: @js(app()->getLocale()),
            pricesCop: @js($pricesCop),
            totalsCop: @js($totalsCop),
            savingsCop: @js($savingsCop),
            pricesUsd: @js($pricesUsd),
            totalsUsd: @js($totalsUsd),
            savingsUsd: @js($savingsUsd),
            get prices()  { return this.locale === 'en' ? this.pricesUsd  : this.pricesCop  },
            get totals()  { return this.locale === 'en' ? this.totalsUsd  : this.totalsCop  },
            get savings() { return this.locale === 'en' ? this.savingsUsd : this.savingsCop },
            fmt(n) { return Number(n).toLocaleString(this.locale === 'en' ? 'en-US' : 'es-CO'); },
            priceOf(plan)   { return this.fmt(this.prices[plan][this.period]); },
            totalOf(plan)   { return this.fmt(this.totals[plan][this.period]); },
            savingsOf(plan) { return this.fmt(this.savings[plan][this.period]); },
            noteOf(plan) {
                if (this.period === 'mensual') return ' ';
                const total = this.totalOf(plan);
                const saved = this.savingsOf(plan);
                return this.locale === 'en'
                    ? `You pay \$${total} · save \$${saved}`
                    : `Pagas \$${total} · ahorras \$${saved}`;
            },
            selectPlan(p) { this.selectedPlan = p; },
            onTierScroll(ev) {
                const track = ev.target;
                if (!track || track.scrollWidth < 1) return;
                const idx = Math.round(track.scrollLeft / (track.scrollWidth / 3));
                const plans = ['esencial', 'metodo', 'elite'];
                if (plans[idx]) this.selectedPlan = plans[idx];
            },
            planName(p) {
                const names = { esencial: @js(__('planes.esencial_name')), metodo: @js(__('planes.metodo_name')), elite: @js(__('planes.elite_name')) };
                return names[p] || '';
            },
        }"
        x-cloak
    >

        {{-- ═══ COMP 1: HeroPricing v2 (5-line H1 brutal + Fraunces italic gold sub) ═══ --}}
        <section class="hero-planes" data-animate>
            <p class="hero-eyebrow">
                W<span class="r">·</span>CORE<span class="r">·</span>FIT &nbsp;·&nbsp;
                {{ __('planes.hero_eyebrow_word') }} &nbsp;·&nbsp;
                {{ now()->year }}
            </p>
            <h1 class="hero-headline" data-animate data-stagger="1">
                @foreach(__('planes.hero_h1_lines') as $i => $line)
                    @if($i === 0)<span class="acc">{{ $line }}</span>@else{{ $line }}@endif
                    @if(!$loop->last)<br>@endif
                @endforeach
            </h1>
            <p class="hero-sub" data-animate data-stagger="2">{{ __('planes.hero_sub') }}</p>
            <div class="hero-actions" data-animate data-stagger="3">
                <a href="#tier-cards" class="btn-primary">{{ __('planes.hero_cta') }}</a>
                <div class="hero-aside" aria-hidden="true">
                    <div class="hero-aside-arrow"></div>
                    <div class="hero-aside-label">{{ __('planes.hero_aside_label') }}</div>
                </div>
            </div>
        </section>

        {{-- ═══ COMP 2: SocialProofBar (componente existente — mantenemos) ═══ --}}
        <x-social-proof-bar />

        {{-- ═══ COMP 3: BillingToggle sticky (3 pills) ═══ --}}
        <div class="billing-wrap" id="billing-wrap">
            <div class="billing-toggle" role="tablist" aria-label="{{ __('planes.section_subtitle') }}">
                @foreach(['mensual', 'trimestral', 'anual'] as $p)
                    <button
                        type="button"
                        class="b-pill"
                        :class="{ active: period === '{{ $p }}' }"
                        :aria-selected="period === '{{ $p }}'"
                        role="tab"
                        @click="period = '{{ $p }}'"
                    >
                        <span class="b-pill-name">{{ __("planes.billing_{$p}") }}</span>
                        <span class="b-pill-save">@if($p === 'trimestral'){{ __('planes.discount_trimestral_pct') }}@elseif($p === 'anual'){{ __('planes.discount_anual_pct') }}@else&nbsp;@endif</span>
                    </button>
                @endforeach
            </div>
        </div>

        {{-- ═══ COMP 4: TierCards (3 — scroll-snap mobile, grid desktop) ═══ --}}
        <section class="tiers-section" id="tier-cards" data-animate>
            <div class="tiers-header">
                <span class="tiers-hint">{{ __('planes.tiers_hint') }}</span>
                <div class="tiers-dots" role="presentation">
                    @foreach(['esencial', 'metodo', 'elite'] as $plan)
                        <div class="t-dot" :class="{ active: selectedPlan === '{{ $plan }}' }"></div>
                    @endforeach
                </div>
            </div>

            <div class="tier-track" id="tier-track" @scroll.passive="onTierScroll($event)">
                @foreach(['esencial', 'metodo', 'elite'] as $i => $plan)
                    <article
                        class="t-card t-card-{{ $plan }}"
                        data-plan="{{ $plan }}"
                        data-animate
                        @if($i > 0) data-stagger="{{ $i }}" @endif
                        @click="selectPlan('{{ $plan }}')"
                        :class="{ 'is-selected': selectedPlan === '{{ $plan }}' }"
                    >
                        @if($plan === 'metodo')
                            <div class="t-badge badge-gold">{{ __('planes.metodo_badge') }}</div>
                        @endif

                        <div class="t-name">{{ __("planes.{$plan}_name") }}</div>

                        @if($plan === 'elite')
                            <div class="elite-metric" aria-label="{{ __('planes.elite_metric_aria') }}">
                                <div class="mini-ring" aria-hidden="true">
                                    <svg width="44" height="44" viewBox="0 0 44 44" focusable="false">
                                        <circle cx="22" cy="22" r="18" fill="none" stroke="rgba(212,160,76,0.15)" stroke-width="2"/>
                                        <circle cx="22" cy="22" r="18" fill="none" stroke="#D4A04C" stroke-width="2"
                                                stroke-dasharray="113" stroke-dashoffset="28"
                                                stroke-linecap="round" transform="rotate(-90 22 22)"/>
                                    </svg>
                                    <div class="mini-ring-val">{{ __('planes.elite_metric_value') }}</div>
                                </div>
                                <div class="elite-metric-label">{!! __('planes.elite_metric_label_html') !!}</div>
                            </div>
                        @endif

                        <div class="t-price-block">
                            <span class="t-price-sym">$</span>
                            <span class="t-price-num" x-text="priceOf('{{ $plan }}')">{{ number_format($pricesCop[$plan]['mensual'], 0, ',', '.') }}</span>
                            <span class="t-price-cop">{{ __('planes.cop_mes') }}</span>
                            <span class="t-price-note" x-text="noteOf('{{ $plan }}')">&nbsp;</span>
                        </div>

                        <p class="t-quote">{{ __("planes.{$plan}_quote") }}</p>

                        <ul class="t-pillars">
                            @foreach(__("planes.{$plan}_pillars") as $pillar)
                                <li class="t-pillar">
                                    <span class="t-pillar-dot" aria-hidden="true"></span>
                                    <span class="t-pillar-text">{{ $pillar }}</span>
                                </li>
                            @endforeach
                        </ul>

                        <a
                            :href="`{{ route('pagar') }}?plan={{ $plan }}&period=${period}`"
                            class="t-cta {{ $plan === 'metodo' ? 'cta-red' : ($plan === 'elite' ? 'cta-outline-gold' : 'cta-ghost') }}"
                            @click.stop
                        >
                            {{ __("planes.{$plan}_cta") }}
                        </a>
                    </article>
                @endforeach
            </div>
        </section>

        {{-- ═══ Section divider · Comparador ═══ --}}
        <x-public.s-divider :label="__('planes.divider_comparador')" />

        {{-- ═══ COMP 5: ComparadorTable (sección NUEVA) ═══ --}}
        <section class="comparador" data-animate>
            <h2 class="comp-hd">{{ __('planes.comp_h2') }}</h2>
            <p class="comp-sub">{{ __('planes.comp_sub') }}</p>
            <div class="comp-scroll">
                <table class="comp-table">
                    <thead class="comp-thead">
                        <tr>
                            <th class="comp-th comp-th-feat" scope="col"></th>
                            <th class="comp-th" scope="col">{{ __('planes.esencial_name') }}</th>
                            <th class="comp-th hl" scope="col">{{ __('planes.metodo_name') }}</th>
                            <th class="comp-th" scope="col">{{ __('planes.elite_name') }}</th>
                        </tr>
                    </thead>
                    <tbody class="comp-tbody">
                        @foreach(__('planes.comp_rows') as $row)
                            <tr>
                                <td class="comp-feat">{{ $row['feat'] }}</td>
                                @foreach(['esencial', 'metodo', 'elite'] as $col)
                                    <td class="comp-td">
                                        @if(($row[$col]['type'] ?? null) === 'mark')
                                            <span class="comp-mark m-{{ $row[$col]['mod'] ?? 'no' }}">{{ $row[$col]['value'] }}</span>
                                        @else
                                            <span class="comp-val{{ ($row[$col]['mod'] ?? null) ? ' '.$row[$col]['mod'] : '' }}">{{ $row[$col]['value'] }}</span>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        {{-- ═══ Section divider · En todos los planes ═══ --}}
        <x-public.s-divider :label="__('planes.divider_differentiators')" />

        {{-- ═══ COMP 6: Differentiators (lo que TODOS los planes incluyen) ═══ --}}
        <section class="differentiators" data-animate>
            <p class="differentiators-eyebrow">{{ __('planes.differentiators_eyebrow') }}</p>
            <h2 class="differentiators-h2">{{ __('planes.differentiators_h2') }}</h2>
            <p class="differentiators-sub">{{ __('planes.differentiators_sub') }}</p>

            <div class="differentiators-grid">
                @foreach(__('planes.differentiators_list') as $i => $d)
                    <x-public.differentiator-card
                        :icon="$d['icon']"
                        :title="$d['title']"
                        :body="$d['body']"
                        :badge="$d['badge'] ?? null"
                        :featured="$d['featured'] ?? false" />
                @endforeach
            </div>
        </section>

        {{-- ═══ Section divider · Testimonios ═══ --}}
        <x-public.s-divider :label="__('planes.divider_testimonios')" />

        {{-- ═══ COMP 6: TestimoniosTicker (stack vertical con dot+line connector) ═══ --}}
        <section class="testimonios" data-animate>
            <h2 class="test-hd">{{ __('planes.testimonios_h2') }}</h2>
            <p class="test-meta">{{ __('planes.testimonios_meta') }}</p>

            <div class="testimonios-list">
                @foreach(__('planes.testimonios_list') as $i => $t)
                    <div class="testi-item" data-animate @if($i > 0) data-stagger="{{ min($i, 3) }}" @endif>
                        <div class="t-col-left" aria-hidden="true">
                            <div class="t-dot-outer {{ $t['plan'] === 'elite' ? 'elt' : 'vrf' }}"></div>
                            @unless($loop->last)<div class="t-line"></div>@endunless
                        </div>
                        <div class="testi-body">
                            <div class="t-row">
                                <span class="t-who">{{ $t['name'] }}</span>
                                <span class="t-plan tp-{{ $t['plan'] }}">{{ __("planes.{$t['plan']}_name") }}</span>
                                <span class="t-country">{{ $t['country'] }}</span>
                            </div>
                            <p class="t-quote">{{ $t['quote'] }}</p>
                            <p class="t-result">{{ $t['result'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- ═══ Section divider · Preguntas ═══ --}}
        <x-public.s-divider :label="__('planes.divider_preguntas')" />

        {{-- ═══ COMP 7: FAQ accordion (8 preguntas, JSON-LD FAQPage incluido) ═══ --}}
        <section class="faq-section" data-animate>
            <h2 class="faq-hd">{{ __('planes.faq_h2') }}</h2>
            <p class="faq-sub">{{ __('planes.faq_sub') }}</p>
            <x-public.faq-accordion :items="__('planes.faq_list')" :search="false" :jsonld="true" />
        </section>

        {{-- ═══ COMP 8: CTAFinal (precio dinámico + nota sin compromiso) ═══ --}}
        <section
            class="cta-final"
            id="cta-final"
            data-animate
            x-intersect:enter.threshold.15="ctaInView = true"
            x-intersect:leave="ctaInView = false"
        >
            <p class="cta-final-eye">{{ __('planes.cta_eye') }}</p>
            <h2 class="cta-final-hd">
                {{ __('planes.cta_h2_l1') }}<br>
                <span class="cta-final-hd-acc">{{ __('planes.cta_h2_l2') }}</span>
            </h2>
            <p class="cta-final-body">{{ __('planes.cta_body') }}</p>
            <a :href="`{{ route('pagar') }}?plan=metodo&period=${period}`" class="btn-cta-final">
                {{ __('planes.cta_btn') }} — <span class="btn-cta-price">$<span x-text="priceOf('metodo')">{{ number_format($pricesCop['metodo']['mensual'], 0, ',', '.') }}</span>/{{ __('planes.cop_mes_short') }}</span>
            </a>
            <p class="cta-final-note">{{ __('planes.cta_note') }}</p>
        </section>

        {{-- ═══ COMP 9: StickyCTABottom (mobile, plan dinámico) ═══ --}}
        <div
            class="sticky-planes"
            :class="{ 'is-hidden': ctaInView }"
            role="region"
            aria-label="{{ __('planes.sticky_continue') }}"
        >
            <div class="sticky-pill">
                <div class="sp-left">
                    <span class="sp-plan" x-text="`{{ __('planes.plan_label_prefix') }} ${planName(selectedPlan).toUpperCase()}`"></span>
                    <span class="sp-price">$<span x-text="priceOf(selectedPlan)">{{ number_format($pricesCop['metodo']['mensual'], 0, ',', '.') }}</span></span>
                </div>
                <a :href="`{{ route('pagar') }}?plan=${selectedPlan}&period=${period}`" class="sp-action">
                    {{ __('planes.sticky_continue') }}
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                        <path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </a>
            </div>
        </div>
    </div>
</x-layouts.public>
