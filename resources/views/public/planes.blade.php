<x-layouts.public>
    <x-slot:title>{{ __('planes.meta_title') }}</x-slot:title>
    <x-slot:description>{{ __('planes.meta_description') }}</x-slot:description>

    <x-json-ld :data="[
        '@context' => 'https://schema.org',
        '@type' => 'Service',
        'name' => 'WellCore Fitness Coaching',
        'provider' => ['@type' => 'Organization', 'name' => 'WellCore Fitness'],
        'description' => 'Planes de coaching fitness personalizado desde $299.000 COP/mes.',
        'areaServed' => ['@type' => 'Place', 'name' => 'Latinoamerica'],
        'hasOfferCatalog' => [
            '@type' => 'OfferCatalog',
            'name' => 'Planes WellCore',
            'itemListElement' => [
                ['@type' => 'Offer', 'name' => 'Esencial', 'price' => '299000', 'priceCurrency' => 'COP'],
                ['@type' => 'Offer', 'name' => 'Metodo', 'price' => '399000', 'priceCurrency' => 'COP'],
                ['@type' => 'Offer', 'name' => 'Elite', 'price' => '549000', 'priceCurrency' => 'COP'],
            ],
        ],
    ]" />

    {{-- Hero --}}
    <section class="hero-gradient relative overflow-hidden bg-wc-bg-tertiary">
        {{-- Parallax decorative orbs --}}
        <div class="parallax-hero" aria-hidden="true">
            <div class="parallax-orb parallax-orb-1" data-parallax-speed="0.2"></div>
            <div class="parallax-orb parallax-orb-2" data-parallax-speed="0.35"></div>
            <div class="parallax-orb parallax-orb-3" data-parallax-speed="0.15"></div>
            <div class="parallax-orb parallax-orb-4" data-parallax-speed="0.25"></div>
            <div class="parallax-orb parallax-orb-5" data-parallax-speed="0.10"></div>
        </div>
        <div class="relative mx-auto max-w-7xl px-4 py-16 text-center sm:px-6 sm:py-24 lg:px-8" data-animate="fadeInUp">
            <h1 class="font-display text-4xl tracking-wide text-wc-text sm:text-5xl lg:text-6xl">
                {{ __('planes.hero_title_1') }}<span class="text-gradient-accent">{{ __('planes.hero_title_acc') }}</span>
            </h1>
            <p class="mx-auto mt-4 max-w-xl text-lg text-wc-text-secondary">{{ __('planes.hero_subtitle') }}</p>
        </div>
    </section>

    <x-social-proof-bar />

    <div class="section-divider"></div>

    {{-- Plans Grid --}}
    <section class="bg-wc-bg"
        x-data="{
            billing: 'mensual',
            prices: {
                mensual:     { esencial: '$299,000', metodo: '$399,000', elite: '$549,000', savingsEsencial: null, savingsMetodo: null, savingsElite: null },
                trimestral:  { esencial: '$269,100', metodo: '$359,100', elite: '$494,100', savingsEsencial: '$89,700', savingsMetodo: '$119,700', savingsElite: '$164,700' },
                anual:       { esencial: '$239,200', metodo: '$319,200', elite: '$439,200', savingsEsencial: '$716,400', savingsMetodo: '$956,400', savingsElite: '$1,318,400' }
            },
            get esencial() { return this.prices[this.billing].esencial },
            get metodo()   { return this.prices[this.billing].metodo },
            get elite()    { return this.prices[this.billing].elite },
            get savingsEsencial() { return this.prices[this.billing].savingsEsencial },
            get savingsMetodo()   { return this.prices[this.billing].savingsMetodo },
            get savingsElite()    { return this.prices[this.billing].savingsElite }
        }">
        <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8" data-animate="fadeInUp">

            {{-- Section heading --}}
            <div class="mb-10 text-center">
                <h2 class="font-display text-3xl tracking-wide text-wc-text sm:text-4xl">{{ __('planes.section_title') }}</h2>
                <p class="mx-auto mt-3 max-w-lg text-wc-text-secondary">{{ __('planes.section_subtitle') }}</p>
            </div>

            {{-- Billing Toggle --}}
            <div class="mb-12 flex items-center justify-center">
                <div class="inline-flex rounded-full border border-wc-border bg-wc-bg-secondary p-1 gap-1">
                    <button @click="billing = 'mensual'"
                            :class="billing === 'mensual' ? 'bg-wc-accent text-white shadow-md' : 'text-wc-text-secondary hover:text-wc-text'"
                            class="rounded-full px-5 py-2 text-sm font-medium transition-all duration-200">
                        {{ __('planes.billing_mensual') }}
                    </button>
                    <button @click="billing = 'trimestral'"
                            :class="billing === 'trimestral' ? 'bg-wc-accent text-white shadow-md' : 'text-wc-text-secondary hover:text-wc-text'"
                            class="rounded-full px-5 py-2 text-sm font-medium transition-all duration-200">
                        {{ __('planes.billing_trimestral') }}
                        <span class="ml-1 rounded-full bg-wc-accent/20 px-1.5 py-0.5 text-xs font-bold text-wc-accent"
                              :class="billing === 'trimestral' ? 'bg-white/20 text-white' : ''">-10%</span>
                    </button>
                    <button @click="billing = 'anual'"
                            :class="billing === 'anual' ? 'bg-wc-accent text-white shadow-md' : 'text-wc-text-secondary hover:text-wc-text'"
                            class="rounded-full px-5 py-2 text-sm font-medium transition-all duration-200">
                        {{ __('planes.billing_anual') }}
                        <span class="ml-1 rounded-full bg-wc-accent/20 px-1.5 py-0.5 text-xs font-bold text-wc-accent"
                              :class="billing === 'anual' ? 'bg-white/20 text-white' : ''">-20%</span>
                    </button>
                </div>
            </div>

            @php
                $allFeatures = [
                    __('planes.feat_01'),
                    __('planes.feat_02'),
                    __('planes.feat_03'),
                    __('planes.feat_04'),
                    __('planes.feat_05'),
                    __('planes.feat_06'),
                    __('planes.feat_07'),
                    __('planes.feat_08'),
                    __('planes.feat_09'),
                    __('planes.feat_10'),
                    __('planes.feat_11'),
                    __('planes.feat_12'),
                    __('planes.feat_13'),
                    __('planes.feat_14'),
                    __('planes.feat_15'),
                    __('planes.feat_16'),
                    __('planes.feat_17'),
                    __('planes.feat_18'),
                    __('planes.feat_19'),
                    __('planes.feat_20'),
                    __('planes.feat_21'),
                    __('planes.feat_22'),
                    __('planes.feat_23'),
                    __('planes.feat_24'),
                    __('planes.feat_25'),
                    __('planes.feat_26'),
                    __('planes.feat_27'),
                    __('planes.feat_28'),
                    __('planes.feat_29'),
                ];

                $esencialFeatures = array_slice($allFeatures, 0, 9);
                $metodoFeatures   = array_slice($allFeatures, 0, 19);
                $eliteFeatures    = $allFeatures;
            @endphp

            <div class="stagger-grid grid grid-cols-1 items-start gap-8 lg:grid-cols-3">

                {{-- Esencial --}}
                <div class="card-hover-lift flex h-full flex-col rounded-2xl border border-wc-border bg-wc-bg-tertiary p-8"
                     data-animate="fadeInUp" data-delay="100">
                    <div class="mb-6">
                        <h3 class="text-center font-display text-2xl tracking-wide text-wc-text">{{ __('planes.esencial_name') }}</h3>
                        <p class="mt-2 text-center text-sm text-wc-text-secondary">{{ __('planes.esencial_desc') }}</p>
                    </div>

                    <div class="relative text-center">
                        <span class="font-data text-5xl font-bold text-wc-text" x-text="esencial"></span>
                        <span class="ml-1 text-sm text-wc-text-tertiary">{{ __('planes.cop_mes') }}</span>
                        <div x-show="savingsEsencial !== null"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 -translate-y-1"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-150"
                             x-transition:leave-start="opacity-100 translate-y-0"
                             x-transition:leave-end="opacity-0 -translate-y-1"
                             x-cloak
                             class="mt-2">
                            <span class="inline-flex items-center rounded-full bg-green-500/10 px-3 py-1 text-xs font-bold text-green-500">
                                {{ __('planes.savings_label') }} <span class="ml-1" x-text="savingsEsencial"></span>
                            </span>
                        </div>
                    </div>

                    <a href="{{ route('pagar') }}?plan=esencial"
                       class="btn-press mt-6 flex w-full items-center justify-center rounded-full border border-wc-border bg-wc-bg px-6 py-3 text-sm font-semibold text-wc-text transition hover:border-wc-accent hover:text-wc-accent">
                        {{ __('planes.esencial_cta') }}
                    </a>

                    <ul class="mt-8 flex-1 space-y-3">
                        @foreach($allFeatures as $feature)
                            @if(in_array($feature, $esencialFeatures))
                                <li class="flex items-start gap-3 text-sm text-wc-text-secondary">
                                    <svg class="mt-0.5 h-4 w-4 shrink-0 text-green-500" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                                    {{ $feature }}
                                </li>
                            @else
                                <li class="flex items-start gap-3 text-sm text-wc-text-tertiary/40 line-through">
                                    <svg class="mt-0.5 h-4 w-4 shrink-0 text-wc-text-tertiary/30" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                                    {{ $feature }}
                                </li>
                            @endif
                        @endforeach
                    </ul>
                </div>

                {{-- Metodo (elevated / accent) --}}
                <div class="card-hover-lift card-glow pulse-glow relative flex h-full flex-col rounded-2xl border-2 border-wc-accent bg-wc-bg-tertiary p-8 shadow-lg shadow-wc-accent/10 lg:-mt-4 lg:mb-0 lg:pb-10 lg:pt-10"
                     data-animate="fadeInUp" data-delay="200">
                    {{-- Badge --}}
                    <div class="absolute -top-3.5 left-1/2 -translate-x-1/2">
                        <span class="badge-shine rounded-full bg-wc-accent px-5 py-1 text-xs font-bold tracking-wide text-white shadow-md">{{ __('planes.metodo_badge') }}</span>
                    </div>

                    <div class="mb-6">
                        <h3 class="text-center font-display text-2xl tracking-wide text-wc-text">{{ __('planes.metodo_name') }}</h3>
                        <p class="mt-2 text-center text-sm text-wc-text-secondary">{{ __('planes.metodo_desc') }}</p>
                        <p class="mt-1 text-center text-xs font-medium text-wc-accent">{{ __('planes.metodo_popular') }}</p>
                    </div>

                    <div class="relative text-center">
                        <span class="font-data text-5xl font-bold text-wc-accent" x-text="metodo"></span>
                        <span class="ml-1 text-sm text-wc-text-tertiary">{{ __('planes.cop_mes') }}</span>
                        <div x-show="savingsMetodo !== null"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 -translate-y-1"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-150"
                             x-transition:leave-start="opacity-100 translate-y-0"
                             x-transition:leave-end="opacity-0 -translate-y-1"
                             x-cloak
                             class="mt-2">
                            <span class="inline-flex items-center rounded-full bg-green-500/10 px-3 py-1 text-xs font-bold text-green-500">
                                {{ __('planes.savings_label') }} <span class="ml-1" x-text="savingsMetodo"></span>
                            </span>
                        </div>
                    </div>

                    <a href="{{ route('pagar') }}?plan=metodo"
                       class="btn-press pulse-glow mt-6 flex w-full items-center justify-center rounded-full bg-wc-accent px-6 py-3 text-sm font-bold text-white shadow-md transition hover:bg-wc-accent-hover hover:shadow-lg">
                        {{ __('planes.metodo_cta') }}
                    </a>

                    <ul class="mt-8 flex-1 space-y-3">
                        @foreach($allFeatures as $feature)
                            @if(in_array($feature, $metodoFeatures))
                                <li class="flex items-start gap-3 text-sm text-wc-text-secondary">
                                    <svg class="mt-0.5 h-4 w-4 shrink-0 text-green-500" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                                    {{ $feature }}
                                </li>
                            @else
                                <li class="flex items-start gap-3 text-sm text-wc-text-tertiary/40 line-through">
                                    <svg class="mt-0.5 h-4 w-4 shrink-0 text-wc-text-tertiary/30" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                                    {{ $feature }}
                                </li>
                            @endif
                        @endforeach
                    </ul>
                </div>

                {{-- Elite --}}
                <div class="card-hover-lift flex h-full flex-col rounded-2xl border border-wc-border bg-wc-bg-tertiary p-8"
                     data-animate="fadeInUp" data-delay="300">
                    {{-- Badge --}}
                    <div class="mb-4 text-center">
                        <span class="inline-block rounded-full border border-wc-accent/30 bg-wc-accent/10 px-4 py-1 text-xs font-bold tracking-wide text-wc-accent">{{ __('planes.elite_badge') }}</span>
                    </div>

                    <div class="mb-6">
                        <h3 class="text-center font-display text-2xl tracking-wide text-wc-text">{{ __('planes.elite_name') }}</h3>
                        <p class="mt-2 text-center text-sm text-wc-text-secondary">{{ __('planes.elite_desc') }}</p>
                    </div>

                    <div class="relative text-center">
                        <span class="font-data text-5xl font-bold text-wc-text" x-text="elite"></span>
                        <span class="ml-1 text-sm text-wc-text-tertiary">{{ __('planes.cop_mes') }}</span>
                        <div x-show="savingsElite !== null"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 -translate-y-1"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-150"
                             x-transition:leave-start="opacity-100 translate-y-0"
                             x-transition:leave-end="opacity-0 -translate-y-1"
                             x-cloak
                             class="mt-2">
                            <span class="inline-flex items-center rounded-full bg-green-500/10 px-3 py-1 text-xs font-bold text-green-500">
                                {{ __('planes.savings_label') }} <span class="ml-1" x-text="savingsElite"></span>
                            </span>
                        </div>
                    </div>

                    <a href="{{ route('pagar') }}?plan=elite"
                       class="btn-press mt-6 flex w-full items-center justify-center rounded-full border border-wc-border bg-wc-bg px-6 py-3 text-sm font-semibold text-wc-text transition hover:border-wc-accent hover:text-wc-accent">
                        {{ __('planes.elite_cta') }}
                    </a>

                    <ul class="mt-8 flex-1 space-y-3">
                        @foreach($allFeatures as $feature)
                            @if(in_array($feature, $eliteFeatures))
                                <li class="flex items-start gap-3 text-sm text-wc-text-secondary">
                                    <svg class="mt-0.5 h-4 w-4 shrink-0 text-green-500" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                                    {{ $feature }}
                                </li>
                            @else
                                <li class="flex items-start gap-3 text-sm text-wc-text-tertiary/40 line-through">
                                    <svg class="mt-0.5 h-4 w-4 shrink-0 text-wc-text-tertiary/30" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                                    {{ $feature }}
                                </li>
                            @endif
                        @endforeach
                    </ul>
                </div>
            </div>

            {{-- Discounts --}}
            <div class="mt-12 flex flex-wrap items-center justify-center gap-6 text-sm text-wc-text-secondary">
                <div class="flex items-center gap-2">
                    <span class="rounded-full bg-wc-accent/10 px-3 py-1 text-xs font-bold text-wc-accent">-10%</span>
                    {{ __('planes.discount_trimestral') }}
                </div>
                <div class="flex items-center gap-2">
                    <span class="rounded-full bg-wc-accent/10 px-3 py-1 text-xs font-bold text-wc-accent">-20%</span>
                    {{ __('planes.discount_anual') }}
                </div>
                <div class="flex items-center gap-2">
                    <svg class="h-4 w-4 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" /></svg>
                    {{ __('planes.discount_garantia') }}
                </div>
            </div>
        </div>
    </section>

    <div class="section-divider"></div>

    {{-- Trust --}}
    <section class="bg-wc-bg-tertiary">
        <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8" data-animate="fadeInUp">
            <div class="mb-10 text-center">
                <h2 class="font-display text-3xl tracking-wide text-wc-text sm:text-4xl">{{ __('planes.trust_title') }}</h2>
                <p class="mx-auto mt-3 max-w-md text-wc-text-secondary">{{ __('planes.trust_subtitle') }}</p>
            </div>
            <div class="grid grid-cols-2 gap-6 sm:grid-cols-4">
                <div class="scroll-reveal text-center">
                    <p class="counter-highlight font-data text-3xl font-bold text-wc-accent" data-counter="0">{{ __('planes.trust_stat1_value') }}</p>
                    <p class="mt-1 text-sm text-wc-text-secondary">{{ __('planes.trust_stat1_label') }}</p>
                </div>
                <div class="scroll-reveal text-center">
                    <p class="counter-highlight font-data text-3xl font-bold text-wc-accent" data-counter="0">{{ __('planes.trust_stat2_value') }}</p>
                    <p class="mt-1 text-sm text-wc-text-secondary">{{ __('planes.trust_stat2_label') }}</p>
                </div>
                <div class="scroll-reveal text-center">
                    <p class="counter-highlight font-data text-3xl font-bold text-wc-accent">{{ __('planes.trust_stat3_value') }}</p>
                    <p class="mt-1 text-sm text-wc-text-secondary">{{ __('planes.trust_stat3_label') }}</p>
                </div>
                <div class="scroll-reveal text-center">
                    <p class="counter-highlight font-data text-3xl font-bold text-wc-accent">{{ __('planes.trust_stat4_value') }}</p>
                    <p class="mt-1 text-sm text-wc-text-secondary">{{ __('planes.trust_stat4_label') }}</p>
                </div>
            </div>
            <p class="mt-8 text-center text-xs text-wc-text-tertiary">{{ __('planes.trust_footnote') }}</p>
        </div>
    </section>

    <div class="section-divider"></div>

    {{-- FAQ --}}
    <section class="bg-wc-bg">
        <div class="mx-auto max-w-3xl px-4 py-16 sm:px-6 lg:px-8" data-animate="fadeInUp">
            <div class="mb-10 text-center">
                <h2 class="font-display text-3xl tracking-wide text-wc-text sm:text-4xl">{{ __('planes.faq_title') }}</h2>
                <p class="mx-auto mt-3 max-w-md text-wc-text-secondary">{{ __('planes.faq_subtitle') }}</p>
            </div>

            <div class="divide-y divide-wc-border" x-data="{ open: null }">
                @foreach([
                    ['question' => __('planes.faq_q1'), 'answer' => __('planes.faq_a1')],
                    ['question' => __('planes.faq_q2'), 'answer' => __('planes.faq_a2')],
                    ['question' => __('planes.faq_q3'), 'answer' => __('planes.faq_a3')],
                    ['question' => __('planes.faq_q4'), 'answer' => __('planes.faq_a4')],
                    ['question' => __('planes.faq_q5'), 'answer' => __('planes.faq_a5')],
                    ['question' => __('planes.faq_q6'), 'answer' => __('planes.faq_a6')],
                ] as $index => $faq)
                    <div class="scroll-reveal py-5">
                        <button @click="open === {{ $index }} ? open = null : open = {{ $index }}"
                                class="flex w-full items-center justify-between text-left">
                            <span class="text-sm font-medium text-wc-text">{{ $faq['question'] }}</span>
                            <svg class="ml-4 h-4 w-4 shrink-0 text-wc-text-tertiary transition-transform duration-300"
                                 :class="{ 'rotate-180': open === {{ $index }} }"
                                 fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                            </svg>
                        </button>
                        <div x-show="open === {{ $index }}"
                             x-transition:enter="transition ease-out duration-300"
                             x-transition:enter-start="opacity-0 -translate-y-2"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-200"
                             x-transition:leave-start="opacity-100 translate-y-0"
                             x-transition:leave-end="opacity-0 -translate-y-2"
                             x-collapse x-cloak class="mt-3">
                            <p class="text-sm leading-relaxed text-wc-text-secondary">{{ $faq['answer'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <div class="section-divider"></div>

    {{-- CTA --}}
    <section class="bg-wc-bg-tertiary">
        <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8" data-animate="fadeInUp">
            <div class="relative overflow-hidden rounded-2xl border border-wc-border bg-gradient-to-br from-wc-bg via-wc-bg to-wc-bg-secondary p-10 sm:p-16">
                <div class="absolute inset-0 bg-gradient-to-br from-wc-accent/10 via-wc-accent/5 to-transparent"></div>
                <div class="absolute -bottom-12 -right-12 h-48 w-48 rounded-full bg-wc-accent/5 blur-3xl" aria-hidden="true"></div>
                <div class="absolute -left-12 -top-12 h-48 w-48 rounded-full bg-wc-accent/5 blur-3xl" aria-hidden="true"></div>
                <div class="relative text-center">
                    <h2 class="font-display text-3xl tracking-wide text-wc-text sm:text-4xl">{{ __('planes.cta_title') }}</h2>
                    <p class="mx-auto mt-4 max-w-md text-wc-text-secondary">{{ __('planes.cta_subtitle') }}</p>
                    <div class="mt-8 flex flex-col items-center justify-center gap-4 sm:flex-row">
                        <a href="{{ route('inscripcion') }}" class="btn-press pulse-glow inline-flex items-center justify-center rounded-full bg-wc-accent px-8 py-3 text-base font-semibold text-white shadow-md transition hover:bg-wc-accent-hover hover:shadow-lg">{{ __('planes.cta_btn_comenzar') }}</a>
                        <a href="{{ route('faq') }}" class="btn-press inline-flex items-center justify-center rounded-full border border-wc-border px-8 py-3 text-base font-medium text-wc-text-secondary transition hover:border-wc-accent hover:text-wc-accent">{{ __('planes.cta_btn_faq') }}</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

</x-layouts.public>
