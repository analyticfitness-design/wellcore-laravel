<x-layouts.public>
    <x-slot:title>{{ __('presencial.meta_title') }}</x-slot:title>
    <x-slot:description>{{ __('presencial.meta_description') }}</x-slot:description>

    @php
        $waNumber  = config('wellcore.whatsapp_presencial', '573000000000');
        $waMessage = urlencode(__('presencial.whatsapp_message'));
    @endphp

    {{-- Hero v2 — eyebrow mono + headline brutal + 2 CTAs --}}
    <section class="hero-gradient relative overflow-hidden bg-wc-bg-tertiary">
        <div class="absolute inset-0 bg-gradient-to-br from-wc-accent/5 via-transparent to-transparent"></div>
        <div class="parallax-hero" aria-hidden="true">
            <div class="parallax-orb parallax-orb-1" data-parallax-speed="0.2"></div>
            <div class="parallax-orb parallax-orb-2" data-parallax-speed="0.35"></div>
            <div class="parallax-orb parallax-orb-3" data-parallax-speed="0.15"></div>
            <div class="parallax-orb parallax-orb-4" data-parallax-speed="0.25"></div>
            <div class="parallax-orb parallax-orb-5" data-parallax-speed="0.4"></div>
        </div>
        <div class="relative mx-auto max-w-5xl px-4 py-20 text-center sm:px-6 sm:py-28 lg:px-8" data-animate="fadeInUp">
            <p class="font-mono text-[10px] uppercase tracking-[0.32em] text-wc-text-tertiary">{{ __('presencial.hero_eyebrow') }}</p>
            <span class="mt-5 inline-flex rounded-full bg-wc-accent/10 px-3 py-1 text-xs font-semibold text-red-700 dark:text-red-400">{{ __('presencial.hero_badge') }}</span>
            <h1 class="mt-4 font-display text-5xl tracking-wide text-wc-text sm:text-6xl lg:text-7xl">
                {{ __('presencial.hero_line1') }}<br><span class="text-gradient-accent">{{ __('presencial.hero_line2') }}</span>
            </h1>
            <p class="mx-auto mt-6 max-w-2xl text-lg text-wc-text-secondary">{{ __('presencial.hero_body') }}</p>
            <div class="mt-8 flex flex-col items-center justify-center gap-3 sm:flex-row sm:gap-4">
                <a href="{{ route('presencial.form') }}" class="btn-press pulse-glow inline-flex items-center justify-center rounded-full bg-wc-accent px-8 py-3.5 text-base font-semibold text-white shadow-lg shadow-wc-accent/20 hover:bg-wc-accent-hover">
                    {{ __('presencial.hero_cta') }}
                    <svg class="ml-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" /></svg>
                </a>
                <a href="#planes-presencial" class="inline-flex items-center justify-center rounded-full border border-wc-border bg-wc-bg-secondary/50 px-6 py-3 text-sm font-medium text-wc-text-secondary hover:bg-wc-bg-secondary">
                    {{ __('presencial.hero_cta_secondary') }}
                </a>
            </div>
        </div>
    </section>

    <div class="section-divider" aria-hidden="true"></div>

    {{-- Comparativa Online vs Presencial --}}
    <section class="bg-wc-bg hp-cv-section">
        <div class="mx-auto max-w-5xl px-4 py-16 sm:px-6 lg:px-8">
            <div class="text-center" data-animate="fadeInUp">
                <p class="font-mono text-[10px] uppercase tracking-[0.32em] text-wc-text-tertiary">{{ __('presencial.compare_eyebrow') }}</p>
                <h2 class="mt-3 font-display text-3xl tracking-wide text-wc-text sm:text-4xl">{{ __('presencial.compare_heading') }}</h2>
                <p class="mx-auto mt-4 max-w-2xl text-wc-text-secondary">{{ __('presencial.compare_subtitle') }}</p>
            </div>

            <div class="mt-10 overflow-hidden rounded-2xl border border-wc-border bg-wc-bg-tertiary" data-animate="fadeInUp">
                {{-- Header de columnas --}}
                <div class="grid grid-cols-3 border-b border-wc-border bg-wc-bg-secondary/40">
                    <div class="px-4 py-4 text-xs font-mono uppercase tracking-[0.18em] text-wc-text-tertiary">·</div>
                    <div class="border-l border-wc-border px-3 py-4 text-center font-mono text-[10px] font-semibold uppercase tracking-[0.18em] text-wc-text-secondary sm:text-[11px]">{{ __('presencial.compare_col_online') }}</div>
                    <div class="border-l border-wc-border bg-wc-accent/5 px-3 py-4 text-center font-mono text-[10px] font-semibold uppercase tracking-[0.18em] text-wc-accent sm:text-[11px]">{{ __('presencial.compare_col_presencial') }}</div>
                </div>

                {{-- Filas --}}
                @foreach(__('presencial.compare_rows') as $row)
                    <div class="grid grid-cols-3 border-b border-wc-border last:border-b-0 hover:bg-wc-bg-secondary/30">
                        <div class="px-4 py-4 text-[13px] font-medium text-wc-text sm:text-sm">{{ $row['feat'] }}</div>
                        <div class="border-l border-wc-border px-3 py-4 text-center text-[12px] text-wc-text-secondary sm:text-sm">{{ $row['online'] }}</div>
                        <div class="border-l border-wc-border bg-wc-accent/[0.03] px-3 py-4 text-center text-[12px] text-wc-text sm:text-sm">{{ $row['presencial'] }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <div class="section-divider" aria-hidden="true"></div>

    {{-- Mapa simplificado SVG + info ubicación --}}
    <section class="bg-wc-bg-secondary hp-cv-section">
        <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8" data-animate="fadeIn">
            <div class="grid grid-cols-1 gap-8 lg:grid-cols-2">

                {{-- Info textual --}}
                <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-8">
                    <p class="font-mono text-[10px] uppercase tracking-[0.32em] text-wc-text-tertiary">{{ __('presencial.map_eyebrow') }}</p>
                    <h2 class="mt-3 font-display text-2xl tracking-wide text-wc-text sm:text-3xl">{{ __('presencial.map_heading') }}</h2>
                    <p class="mt-4 text-sm text-wc-text-secondary">{{ __('presencial.map_address') }}</p>
                    <div class="mt-6 flex items-center gap-2">
                        <span class="relative flex h-2 w-2">
                            <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-500 opacity-75"></span>
                            <span class="relative inline-flex h-2 w-2 rounded-full bg-emerald-500"></span>
                        </span>
                        <span class="text-xs font-medium text-emerald-500">{{ __('presencial.map_status') }}</span>
                    </div>
                    <a href="https://wa.me/{{ $waNumber }}?text={{ $waMessage }}" target="_blank" rel="noopener" class="btn-press mt-6 inline-flex items-center justify-center gap-2 rounded-full border border-wc-border bg-wc-bg-secondary/50 px-5 py-2.5 text-sm font-medium text-wc-text hover:bg-wc-bg-secondary">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                        {{ __('presencial.final_cta_whatsapp') }}
                    </a>
                </div>

                {{-- Mapa real OpenStreetMap embed — Bucaramanga centrado.
                     Por qué OSM y NO Google Maps:
                     · Sin cookies third-party (OSM no trackea)
                     · Sin API key requerida
                     · Sin LCP penalty pesado (un iframe único, lazy-loaded)
                     · Marker rojo built-in vía param `marker=lat,lng`
                     bbox = west,south,east,north (recorte alrededor de Bucaramanga). --}}
                <div class="overflow-hidden rounded-xl border border-wc-border bg-wc-bg-tertiary">
                    <iframe
                        src="https://www.openstreetmap.org/export/embed.html?bbox=-73.2200%2C7.0700%2C-73.0300%2C7.1900&amp;layer=mapnik&amp;marker={{ $location['lat'] }}%2C{{ $location['lng'] }}"
                        title="{{ __('presencial.map_aria') }}"
                        class="block h-[360px] w-full sm:h-[420px]"
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade"
                        style="border:0;"
                    ></iframe>
                    <a
                        href="https://www.openstreetmap.org/?mlat={{ $location['lat'] }}&amp;mlon={{ $location['lng'] }}#map=14/{{ $location['lat'] }}/{{ $location['lng'] }}"
                        target="_blank"
                        rel="noopener"
                        class="block px-4 py-3 text-center font-mono text-[10px] uppercase tracking-[0.18em] text-wc-text-tertiary hover:bg-wc-bg-secondary hover:text-wc-text"
                    >
                        Ver en mapa más grande →
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="section-divider" aria-hidden="true"></div>

    {{-- Cómo funciona --}}
    <section class="bg-wc-bg hp-cv-section">
        <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 gap-8 lg:grid-cols-2">
                <div data-animate="slideInLeft">
                    <h2 class="font-display text-3xl tracking-wide text-wc-text">{{ __('presencial.how_heading') }}</h2>
                    <div class="mt-6 space-y-4 text-wc-text-secondary">
                        <p>{{ __('presencial.how_p1') }}</p>
                        <p>{{ __('presencial.how_p2') }}</p>
                    </div>
                </div>
                <div class="stagger-grid grid grid-cols-2 gap-4" data-animate="slideInRight">
                    @php
                        $features = [
                            ['num' => '3-5', 'label_key' => 'presencial.stat_sessions', 'counter' => null],
                            ['num' => '60', 'label_key' => 'presencial.stat_duration', 'counter' => '60', 'suffix' => 'min'],
                            ['num' => '1:1', 'label_key' => 'presencial.stat_coach', 'counter' => null],
                            ['num' => '24/7', 'label_key' => 'presencial.stat_support', 'counter' => null],
                        ];
                    @endphp
                    @foreach($features as $f)
                        <div class="card-hover-lift rounded-xl border border-wc-border bg-wc-bg-tertiary p-5 text-center">
                            <span class="counter-highlight font-data text-2xl font-bold text-wc-accent" @if(!empty($f['counter'])) data-counter="{{ $f['counter'] }}" data-suffix="{{ $f['suffix'] ?? '' }}" @endif>{{ $f['num'] }}</span>
                            <p class="mt-1 text-xs text-wc-text-secondary">{{ __($f['label_key']) }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <div class="section-divider" aria-hidden="true"></div>

    {{-- Schedule --}}
    <section class="bg-wc-bg-tertiary hp-cv-section">
        <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
            <h2 class="text-center font-display text-3xl tracking-wide text-wc-text" data-animate="fadeInUp">{{ __('presencial.schedule_heading') }}</h2>
            <div class="scroll-reveal mx-auto mt-10 max-w-2xl overflow-hidden rounded-xl border border-wc-border">
                <table class="w-full text-sm">
                    <thead class="bg-wc-bg-secondary">
                        <tr>
                            <th class="px-6 py-3 text-left font-semibold text-wc-text">{{ __('presencial.sched_col_time') }}</th>
                            <th class="px-6 py-3 text-left font-semibold text-wc-text">{{ __('presencial.sched_col_days') }}</th>
                            <th class="px-6 py-3 text-left font-semibold text-wc-text">{{ __('presencial.sched_col_avail') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-wc-border">
                        <tr class="transition-colors hover:bg-wc-bg-secondary/50">
                            <td class="px-6 py-3 text-wc-text">{{ __('presencial.sched_row1_time') }}</td>
                            <td class="px-6 py-3 text-wc-text-secondary">{{ __('presencial.sched_row1_days') }}</td>
                            <td class="px-6 py-3"><span class="pulse-glow inline-flex rounded-full bg-emerald-500/10 px-2 py-0.5 text-[10px] font-semibold text-emerald-500">{{ __('presencial.avail_open') }}</span></td>
                        </tr>
                        <tr class="transition-colors hover:bg-wc-bg-secondary/50">
                            <td class="px-6 py-3 text-wc-text">{{ __('presencial.sched_row2_time') }}</td>
                            <td class="px-6 py-3 text-wc-text-secondary">{{ __('presencial.sched_row2_days') }}</td>
                            <td class="px-6 py-3"><span class="pulse-glow inline-flex rounded-full bg-emerald-500/10 px-2 py-0.5 text-[10px] font-semibold text-emerald-500">{{ __('presencial.avail_open') }}</span></td>
                        </tr>
                        <tr class="transition-colors hover:bg-wc-bg-secondary/50">
                            <td class="px-6 py-3 text-wc-text">{{ __('presencial.sched_row3_time') }}</td>
                            <td class="px-6 py-3 text-wc-text-secondary">{{ __('presencial.sched_row3_days') }}</td>
                            <td class="px-6 py-3"><span class="inline-flex rounded-full bg-amber-500/10 px-2 py-0.5 text-[10px] font-semibold text-amber-500">{{ __('presencial.avail_limited') }}</span></td>
                        </tr>
                        <tr class="transition-colors hover:bg-wc-bg-secondary/50">
                            <td class="px-6 py-3 text-wc-text">{{ __('presencial.sched_row4_time') }}</td>
                            <td class="px-6 py-3 text-wc-text-secondary">{{ __('presencial.sched_row4_days') }}</td>
                            <td class="px-6 py-3"><span class="inline-flex rounded-full bg-red-500/10 px-2 py-0.5 text-[10px] font-semibold text-red-500">{{ __('presencial.avail_closed') }}</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <div class="section-divider" aria-hidden="true"></div>

    {{-- Pricing --}}
    <section id="planes-presencial" class="bg-wc-bg hp-cv-section">
        <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
            <div class="text-center" data-animate="fadeInUp">
                <h2 class="font-display text-3xl tracking-wide text-wc-text">{{ __('presencial.pricing_heading') }}</h2>
                <p class="mx-auto mt-4 max-w-2xl text-wc-text-secondary">{{ __('presencial.pricing_subtitle') }}</p>
            </div>

            <div class="stagger-grid mt-10 grid grid-cols-1 gap-6 sm:grid-cols-3">
                @php
                    $plans = [
                        ['name_key' => 'presencial.plan_3_name', 'price' => '450.000', 'features_keys' => ['presencial.feat_3_sessions', 'presencial.feat_supervision', 'presencial.feat_progressions', 'presencial.feat_platform', 'presencial.feat_3_nutrition', 'presencial.feat_biweekly', 'presencial.feat_portal'], 'delay' => 100],
                        ['name_key' => 'presencial.plan_4_name', 'price' => '550.000', 'popular' => true, 'features_keys' => ['presencial.feat_4_sessions', 'presencial.feat_supervision', 'presencial.feat_progressions', 'presencial.feat_intensity', 'presencial.feat_platform', 'presencial.feat_4_nutrition', 'presencial.feat_biweekly', 'presencial.feat_measurements', 'presencial.feat_portal'], 'delay' => 200],
                        ['name_key' => 'presencial.plan_5_name', 'price' => '650.000', 'features_keys' => ['presencial.feat_5_sessions', 'presencial.feat_supervision', 'presencial.feat_progressions', 'presencial.feat_intensity', 'presencial.feat_platform', 'presencial.feat_5_nutrition', 'presencial.feat_biweekly', 'presencial.feat_measurements', 'presencial.feat_portal', 'presencial.feat_whatsapp'], 'delay' => 300],
                    ];
                @endphp
                @foreach($plans as $plan)
                    <div
                        class="card-hover-lift {{ isset($plan['popular']) ? 'card-glow pulse-glow' : '' }} relative rounded-xl border {{ isset($plan['popular']) ? 'border-wc-accent' : 'border-wc-border' }} bg-wc-bg-tertiary p-8"
                        data-animate="scaleIn"
                        data-animate-delay="{{ $plan['delay'] }}"
                    >
                        @if(isset($plan['popular']))
                            <span class="badge-shine absolute -top-3 left-1/2 -translate-x-1/2 rounded-full bg-wc-accent px-3 py-0.5 text-[10px] font-semibold text-white">{{ __('presencial.popular_badge') }}</span>
                        @endif
                        <h3 class="text-sm font-semibold text-wc-text">{{ __($plan['name_key']) }}</h3>
                        <div class="mt-3">
                            <span class="font-data text-3xl font-bold text-wc-text">${{ $plan['price'] }}</span>
                            <span class="text-sm text-wc-text-tertiary">{{ __('presencial.price_currency') }}</span>
                        </div>
                        <ul class="mt-6 space-y-3">
                            @foreach($plan['features_keys'] as $featureKey)
                                <li class="flex items-start gap-2 text-sm text-wc-text-secondary">
                                    <svg class="mt-0.5 h-4 w-4 shrink-0 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                                    {{ __($featureKey) }}
                                </li>
                            @endforeach
                        </ul>
                        <a href="{{ route('presencial.form') }}" class="btn-press {{ isset($plan['popular']) ? 'pulse-glow' : '' }} mt-8 block rounded-lg {{ isset($plan['popular']) ? 'bg-wc-accent text-white hover:bg-wc-accent-hover' : 'border border-wc-border bg-wc-bg-secondary text-wc-text hover:bg-wc-bg' }} px-6 py-3 text-center text-sm font-medium">
                            {{ __('presencial.pricing_cta') }}
                        </a>
                    </div>
                @endforeach
            </div>
            <p class="mt-6 text-center text-xs text-wc-text-tertiary">{{ __('presencial.pricing_note') }}</p>
        </div>
    </section>

    <div class="section-divider" aria-hidden="true"></div>

    {{-- FAQ --}}
    <section class="bg-wc-bg-secondary hp-cv-section">
        <div class="mx-auto max-w-3xl px-4 py-16 sm:px-6 lg:px-8">
            <div class="text-center" data-animate="fadeInUp">
                <h2 class="font-display text-3xl tracking-wide text-wc-text sm:text-4xl">{{ __('presencial.faq_heading') }}</h2>
                <p class="mt-4 text-wc-text-secondary">{{ __('presencial.faq_subtitle') }}</p>
            </div>
            <div class="mt-10" data-animate="fadeInUp">
                <x-public.faq-accordion :items="$faqs" :search="false" :jsonld="true" />
            </div>
        </div>
    </section>

    <div class="section-divider" aria-hidden="true"></div>

    {{-- Final CTA --}}
    <section id="cta-final" class="bg-wc-bg-tertiary hp-cv-section">
        <div class="mx-auto max-w-7xl px-4 py-20 text-center sm:px-6 lg:px-8" data-animate="scaleIn">
            <div class="relative mx-auto max-w-2xl overflow-hidden rounded-2xl border border-wc-border bg-wc-bg p-10 sm:p-16">
                <div class="absolute -top-10 -right-10 h-40 w-40 rounded-full bg-wc-accent/5 blur-3xl" aria-hidden="true"></div>
                <h2 class="font-display text-3xl tracking-wide text-wc-text sm:text-4xl">{{ __('presencial.final_cta_heading') }}</h2>
                <p class="mt-4 text-wc-text-secondary">{{ __('presencial.final_cta_subtitle') }}</p>
                <div class="mt-8 flex flex-col items-center justify-center gap-3 sm:flex-row sm:gap-4">
                    <a href="{{ route('presencial.form') }}" class="btn-press pulse-glow inline-flex items-center justify-center rounded-full bg-wc-accent px-8 py-3.5 text-base font-semibold text-white shadow-lg shadow-wc-accent/20 hover:bg-wc-accent-hover">
                        {{ __('presencial.final_cta_button') }}
                        <svg class="ml-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" /></svg>
                    </a>
                    <a href="https://wa.me/{{ $waNumber }}?text={{ $waMessage }}" target="_blank" rel="noopener" class="btn-press inline-flex items-center justify-center gap-2 rounded-full border border-wc-border bg-wc-bg-secondary/50 px-6 py-3 text-sm font-medium text-wc-text hover:bg-wc-bg-secondary">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                        {{ __('presencial.final_cta_whatsapp') }}
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- Sticky mobile CTA --}}
    <x-public.sticky-mobile-cta
        :href="route('presencial.form')"
        :label="__('presencial.sticky_label')"
        hide-at="cta-final"
        :threshold="600"
    />

</x-layouts.public>
