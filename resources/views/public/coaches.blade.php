<x-layouts.public>
    <x-slot:title>{{ __('coaches.meta_title') }}</x-slot:title>
    <x-slot:description>{{ __('coaches.meta_description') }}</x-slot:description>

    {{-- Hero v2 — eyebrow mono + headline brutal + dos CTAs --}}
    <section class="hero-gradient relative overflow-hidden bg-wc-bg-tertiary">
        {{-- Parallax decorative orbs --}}
        <div class="parallax-hero" aria-hidden="true">
            <div class="parallax-orb parallax-orb-1" data-parallax-speed="0.2"></div>
            <div class="parallax-orb parallax-orb-2" data-parallax-speed="0.35"></div>
            <div class="parallax-orb parallax-orb-3" data-parallax-speed="0.15"></div>
            <div class="parallax-orb parallax-orb-4" data-parallax-speed="0.25"></div>
            <div class="parallax-orb parallax-orb-5" data-parallax-speed="0.1"></div>
        </div>
        <div class="relative mx-auto max-w-5xl px-4 py-20 text-center sm:px-6 sm:py-28 lg:px-8" data-animate="fadeInUp">
            <p class="font-mono text-[10px] uppercase tracking-[0.32em] text-wc-text-tertiary">{{ __('coaches.hero_eyebrow') }}</p>
            <h1 class="mt-6 font-display text-5xl tracking-wide text-wc-text sm:text-6xl lg:text-7xl">
                {{ __('coaches.hero_heading') }} <span class="text-gradient-accent">{{ __('coaches.hero_brand') }}</span>
            </h1>
            <p class="mx-auto mt-6 max-w-2xl text-lg text-wc-text-secondary">
                {{ __('coaches.hero_body') }}
            </p>
            <div class="mt-10 flex flex-col items-center justify-center gap-3 sm:flex-row sm:gap-4">
                <a href="{{ route('coaches.apply') }}" class="btn-press pulse-glow inline-flex items-center justify-center rounded-full bg-wc-accent px-8 py-3.5 text-base font-semibold text-white shadow-lg shadow-wc-accent/20 hover:bg-wc-accent-hover">
                    {{ __('coaches.hero_cta') }}
                    <svg class="ml-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                    </svg>
                </a>
                <a href="#requisitos" class="inline-flex items-center justify-center rounded-full border border-wc-border bg-wc-bg-secondary/50 px-6 py-3 text-sm font-medium text-wc-text-secondary hover:bg-wc-bg-secondary">
                    {{ __('coaches.hero_cta_secondary') }}
                </a>
            </div>
        </div>
    </section>

    {{-- Section Divider --}}
    <div class="h-px bg-gradient-to-r from-transparent via-wc-border to-transparent" aria-hidden="true"></div>

    {{-- Stats marketplace — vista de ejemplo --}}
    {{-- TODO confirmar valores reales con Daniel antes de quitar el badge "VISTA DE EJEMPLO" --}}
    <section class="bg-wc-bg hp-cv-section">
        <div class="mx-auto max-w-6xl px-4 py-16 sm:px-6 lg:px-8">
            <div class="text-center" data-animate="fadeInUp">
                <p class="font-mono text-[10px] uppercase tracking-[0.32em] text-wc-text-tertiary">{{ __('coaches.stats_demo_label') }}</p>
                <h2 class="mt-3 font-display text-3xl tracking-wide text-wc-text sm:text-4xl">{{ __('coaches.stats_title') }}</h2>
            </div>

            <div class="stagger-grid mt-12 grid grid-cols-2 gap-4 sm:gap-6 lg:grid-cols-4">
                <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-6 text-center" data-animate="scaleIn" data-delay="100">
                    <p class="font-data text-4xl font-bold text-wc-accent sm:text-5xl">{{ __('coaches.stats_coaches_value') }}</p>
                    <p class="mt-2 font-mono text-[10px] uppercase tracking-[0.18em] text-wc-text-tertiary">{{ __('coaches.stats_coaches_label') }}</p>
                </div>
                <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-6 text-center" data-animate="scaleIn" data-delay="200">
                    <p class="font-data text-4xl font-bold text-wc-accent sm:text-5xl">{{ __('coaches.stats_retention_value') }}</p>
                    <p class="mt-2 font-mono text-[10px] uppercase tracking-[0.18em] text-wc-text-tertiary">{{ __('coaches.stats_retention_label') }}</p>
                </div>
                <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-6 text-center" data-animate="scaleIn" data-delay="300">
                    <p class="font-data text-4xl font-bold text-wc-accent sm:text-5xl">{{ __('coaches.stats_rating_value') }}</p>
                    <p class="mt-2 font-mono text-[10px] uppercase tracking-[0.18em] text-wc-text-tertiary">{{ __('coaches.stats_rating_label') }}</p>
                </div>
                <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-6 text-center" data-animate="scaleIn" data-delay="400">
                    <p class="font-data text-2xl font-bold text-wc-accent sm:text-3xl">{{ __('coaches.stats_paid_value') }}</p>
                    <p class="mt-2 font-mono text-[10px] uppercase tracking-[0.18em] text-wc-text-tertiary">{{ __('coaches.stats_paid_label') }}</p>
                </div>
            </div>
        </div>
    </section>

    {{-- Section Divider --}}
    <div class="h-px bg-gradient-to-r from-transparent via-wc-border to-transparent" aria-hidden="true"></div>

    {{-- Benefits --}}
    <section class="bg-wc-bg hp-cv-section">
        <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
            <div class="text-center" data-animate="fadeInUp">
                <h2 class="font-display text-3xl tracking-wide text-wc-text sm:text-4xl">{{ __('coaches.benefits_heading') }}</h2>
                <p class="mt-4 text-wc-text-secondary">{{ __('coaches.benefits_subtitle') }}</p>
            </div>

            <div class="stagger-grid mt-14 grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-4">
                <div class="scroll-reveal-scale card-hover-lift card-glow rounded-xl border border-wc-border bg-wc-bg-tertiary p-8 text-center" data-animate="scaleIn" data-delay="100">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-lg bg-wc-accent/10">
                        <svg class="h-7 w-7 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 0 0 8.716-6.747M12 21a9.004 9.004 0 0 1-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 0 1 7.843 4.582M12 3a8.997 8.997 0 0 0-7.843 4.582m15.686 0A11.953 11.953 0 0 1 12 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0 1 21 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0 1 12 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 0 1 3 12c0-1.605.42-3.113 1.157-4.418" /></svg>
                    </div>
                    <h3 class="mt-5 text-lg font-semibold text-wc-text">{{ __('coaches.benefit_remote_title') }}</h3>
                    <p class="mt-2 text-sm text-wc-text-secondary">{{ __('coaches.benefit_remote_body') }}</p>
                </div>

                <div class="scroll-reveal-scale card-hover-lift card-glow rounded-xl border border-wc-border bg-wc-bg-tertiary p-8 text-center" data-animate="scaleIn" data-delay="200">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-lg bg-wc-accent/10">
                        <svg class="h-7 w-7 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17 17.25 21A2.652 2.652 0 0 0 21 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 1 1-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 0 0 4.486-6.336l-3.276 3.277a3.004 3.004 0 0 1-2.25-2.25l3.276-3.276a4.5 4.5 0 0 0-6.336 4.486c.049.58.025 1.194-.14 1.743" /></svg>
                    </div>
                    <h3 class="mt-5 text-lg font-semibold text-wc-text">{{ __('coaches.benefit_tools_title') }}</h3>
                    <p class="mt-2 text-sm text-wc-text-secondary">{{ __('coaches.benefit_tools_body') }}</p>
                </div>

                <div class="scroll-reveal-scale card-hover-lift card-glow rounded-xl border border-wc-border bg-wc-bg-tertiary p-8 text-center" data-animate="scaleIn" data-delay="300">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-lg bg-wc-accent/10">
                        <svg class="h-7 w-7 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" /></svg>
                    </div>
                    <h3 class="mt-5 text-lg font-semibold text-wc-text">{{ __('coaches.benefit_community_title') }}</h3>
                    <p class="mt-2 text-sm text-wc-text-secondary">{{ __('coaches.benefit_community_body') }}</p>
                </div>

                <div class="scroll-reveal-scale card-hover-lift card-glow rounded-xl border border-wc-border bg-wc-bg-tertiary p-8 text-center" data-animate="scaleIn" data-delay="400">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-lg bg-wc-accent/10">
                        <svg class="h-7 w-7 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" /></svg>
                    </div>
                    <h3 class="mt-5 text-lg font-semibold text-wc-text">{{ __('coaches.benefit_income_title') }}</h3>
                    <p class="mt-2 text-sm text-wc-text-secondary">{{ __('coaches.benefit_income_body') }}</p>
                </div>
            </div>
        </div>
    </section>

    {{-- Section Divider --}}
    <div class="h-px bg-gradient-to-r from-transparent via-wc-border to-transparent" aria-hidden="true"></div>

    {{-- Bloomberg ticker — coaches activos anonimizados --}}
    <section class="bg-wc-bg-secondary py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <p class="mb-4 text-center font-mono text-[10px] uppercase tracking-[0.32em] text-wc-text-tertiary">{{ __('coaches.ticker_label') }}</p>
            <x-public.bloomberg-ticker :items="$tickerCoaches" :duration="40" />
        </div>
    </section>

    {{-- Section Divider --}}
    <div class="h-px bg-gradient-to-r from-transparent via-wc-border to-transparent" aria-hidden="true"></div>

    {{-- Tu Portal de Coach — Dashboard Mockups (anonimizado) --}}
    <section class="bg-wc-bg-tertiary hp-cv-section">
        <div class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 items-center gap-12 lg:grid-cols-2">

                {{-- Left — Description --}}
                <div data-animate="slideInLeft">
                    <p class="text-xs font-semibold uppercase tracking-widest text-red-700 dark:text-red-400">{{ __('coaches.portal_label') }}</p>
                    <h2 class="mt-3 font-display text-3xl tracking-wide text-wc-text sm:text-4xl">{{ __('coaches.portal_heading') }}</h2>
                    <p class="mt-4 max-w-lg text-sm text-wc-text-tertiary">{{ __('coaches.portal_body') }}</p>

                    <div class="mt-8 space-y-4">
                        @foreach(['portal_feature_1','portal_feature_2','portal_feature_3','portal_feature_4'] as $i => $key)
                            <div class="scroll-reveal flex items-center gap-3" data-delay="{{ ($i + 1) * 100 }}">
                                <div class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-wc-accent/10">
                                    <svg class="h-3.5 w-3.5 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                                </div>
                                <span class="text-sm text-wc-text-secondary">{{ __('coaches.' . $key) }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Right — Mockups --}}
                <div class="space-y-6" data-animate="slideInRight">

                    {{-- Mockup 1: Coach Dashboard (Browser Window) --}}
                    <div class="animate-float-slow rounded-xl border border-wc-border bg-wc-bg shadow-2xl shadow-black/10">
                        <div class="flex items-center gap-2 border-b border-wc-border px-4 py-3">
                            <span class="h-2.5 w-2.5 rounded-full bg-red-500"></span>
                            <span class="h-2.5 w-2.5 rounded-full bg-yellow-500"></span>
                            <span class="h-2.5 w-2.5 rounded-full bg-green-500"></span>
                            <div class="ml-3 flex-1 rounded-md bg-wc-bg-secondary px-3 py-1">
                                <span class="text-xs text-wc-text-tertiary">coach.wellcorefitness.com</span>
                            </div>
                        </div>
                        <div class="space-y-3 p-4">
                            <div class="flex items-center justify-between">
                                <h3 class="font-display text-sm tracking-wide text-wc-text sm:text-base">
                                    {{ __('coaches.mockup_dashboard_heading') }} <span class="text-wc-text-tertiary">&mdash;</span> <span class="text-wc-text-secondary">2026</span>
                                </h3>
                            </div>

                            <div class="grid grid-cols-3 gap-2">
                                <div class="rounded-lg border border-wc-border bg-wc-bg-tertiary p-2.5 text-center">
                                    <p class="font-data text-lg font-bold text-red-700 dark:text-red-400">18</p>
                                    <p class="text-[10px] text-wc-text-tertiary">{{ __('coaches.mockup_clients_label') }}</p>
                                </div>
                                <div class="rounded-lg border border-wc-border bg-wc-bg-tertiary p-2.5 text-center">
                                    <p class="font-data text-lg font-bold text-emerald-400">$5.7M</p>
                                    <p class="text-[10px] text-wc-text-tertiary">{{ __('coaches.mockup_revenue_label') }}</p>
                                </div>
                                <div class="rounded-lg border border-wc-border bg-wc-bg-tertiary p-2.5 text-center">
                                    <p class="font-data text-lg font-bold text-wc-text">91%</p>
                                    <p class="text-[10px] text-wc-text-tertiary">{{ __('coaches.mockup_adherence_label') }}</p>
                                </div>
                            </div>

                            <div class="rounded-lg border border-wc-border bg-wc-bg-tertiary p-3">
                                <p class="text-xs font-semibold text-wc-text">{{ __('coaches.mockup_active_clients') }}</p>
                                <div class="mt-2 space-y-1.5">
                                    @foreach([
                                        ['name' => __('coaches.mockup_client_1_name'), 'plan' => __('coaches.mockup_client_1_plan'), 'adherence' => __('coaches.mockup_client_1_adherence'), 'color' => 'text-amber-400', 'badge' => 'bg-amber-400/10 text-amber-400'],
                                        ['name' => __('coaches.mockup_client_2_name'), 'plan' => __('coaches.mockup_client_2_plan'), 'adherence' => __('coaches.mockup_client_2_adherence'), 'color' => 'text-emerald-400', 'badge' => 'bg-emerald-400/10 text-emerald-400'],
                                        ['name' => __('coaches.mockup_client_3_name'), 'plan' => __('coaches.mockup_client_3_plan'), 'adherence' => __('coaches.mockup_client_3_adherence'), 'color' => 'text-emerald-400', 'badge' => 'bg-emerald-400/10 text-emerald-400'],
                                        ['name' => __('coaches.mockup_client_4_name'), 'plan' => __('coaches.mockup_client_4_plan'), 'adherence' => __('coaches.mockup_client_4_adherence'), 'color' => 'text-amber-400', 'badge' => 'bg-amber-400/10 text-amber-400'],
                                    ] as $client)
                                    <div class="flex items-center justify-between rounded bg-wc-bg px-2.5 py-1.5">
                                        <div class="flex items-center gap-2">
                                            <div class="h-5 w-5 rounded-full bg-wc-accent/10"></div>
                                            <span class="text-[11px] text-wc-text-secondary">{{ $client['name'] }}</span>
                                            <span class="rounded-full px-1.5 py-0.5 text-[9px] font-semibold {{ $client['badge'] }}">{{ $client['plan'] }}</span>
                                        </div>
                                        <span class="font-data text-[11px] font-semibold {{ $client['color'] }}">{{ $client['adherence'] }}</span>
                                    </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="rounded-lg border border-wc-border bg-wc-bg-tertiary p-3">
                                <p class="text-xs font-semibold text-wc-text">{{ __('coaches.mockup_recent_activity') }}</p>
                                <div class="mt-2 space-y-2">
                                    @foreach([
                                        ['key' => 'mockup_activity_1', 'dot' => 'bg-wc-accent', 'time' => '2m'],
                                        ['key' => 'mockup_activity_2', 'dot' => 'bg-emerald-400', 'time' => '1h'],
                                        ['key' => 'mockup_activity_3', 'dot' => 'bg-amber-400', 'time' => '3h'],
                                    ] as $act)
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center gap-2">
                                                <div class="h-1.5 w-1.5 rounded-full {{ $act['dot'] }}"></div>
                                                <span class="text-[11px] text-wc-text-secondary">{{ __('coaches.' . $act['key']) }}</span>
                                            </div>
                                            <span class="text-[10px] text-wc-text-tertiary">{{ $act['time'] }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <p class="pt-1 text-center font-mono text-[9px] uppercase tracking-[0.18em] text-wc-text-tertiary">{{ __('coaches.mockup_disclaimer') }}</p>
                        </div>
                    </div>

                    {{-- Mockup 2: Phone (Coach Mobile — Messages) --}}
                    <div class="flex justify-center">
                        <div class="animate-float mx-auto w-[280px] rounded-[2.5rem] border-[6px] border-wc-border bg-wc-bg-tertiary p-2 shadow-2xl">
                            <div class="mx-auto mb-2 h-5 w-24 rounded-full bg-wc-bg-secondary"></div>
                            <div class="space-y-3 rounded-[2rem] bg-wc-bg p-4">
                                <div class="flex items-center justify-between">
                                    <p class="font-display text-lg tracking-wide text-wc-text">{{ __('coaches.mockup_messages_heading') }}</p>
                                    <span class="flex h-5 w-5 items-center justify-center rounded-full bg-wc-accent text-[10px] font-bold text-white">3</span>
                                </div>

                                <div class="space-y-2">
                                    {{-- Message 1 (unread) --}}
                                    <div class="rounded-lg border border-wc-accent/30 bg-wc-accent/5 p-3">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center gap-2">
                                                <div class="h-6 w-6 rounded-full bg-wc-accent/20"></div>
                                                <span class="text-[11px] font-semibold text-wc-text">{{ __('coaches.mockup_client_1_name') }}</span>
                                            </div>
                                            <div class="flex items-center gap-1.5">
                                                <span class="text-[9px] text-wc-text-tertiary">5m</span>
                                                <div class="h-2 w-2 rounded-full bg-wc-accent"></div>
                                            </div>
                                        </div>
                                        <p class="mt-1.5 text-[10px] text-wc-text-secondary">{{ __('coaches.mockup_msg_1') }}</p>
                                    </div>

                                    {{-- Message 2 --}}
                                    <div class="rounded-lg border border-wc-border bg-wc-bg-tertiary p-3">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center gap-2">
                                                <div class="h-6 w-6 rounded-full bg-wc-accent/10"></div>
                                                <span class="text-[11px] font-semibold text-wc-text">{{ __('coaches.mockup_client_2_name') }}</span>
                                            </div>
                                            <span class="text-[9px] text-wc-text-tertiary">1h</span>
                                        </div>
                                        <p class="mt-1.5 text-[10px] text-wc-text-secondary">{{ __('coaches.mockup_msg_2') }}</p>
                                    </div>

                                    {{-- Message 3 --}}
                                    <div class="rounded-lg border border-wc-border bg-wc-bg-tertiary p-3">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center gap-2">
                                                <div class="h-6 w-6 rounded-full bg-wc-accent/10"></div>
                                                <span class="text-[11px] font-semibold text-wc-text">{{ __('coaches.mockup_client_3_name') }}</span>
                                            </div>
                                            <span class="text-[9px] text-wc-text-tertiary">3h</span>
                                        </div>
                                        <p class="mt-1.5 text-[10px] text-wc-text-secondary">{{ __('coaches.mockup_msg_3') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>

    {{-- Section Divider --}}
    <div class="h-px bg-gradient-to-r from-transparent via-wc-border to-transparent" aria-hidden="true"></div>

    {{-- Calculadora de ingresos --}}
    <section class="bg-wc-bg hp-cv-section">
        <div class="mx-auto max-w-3xl px-4 py-16 sm:px-6 lg:px-8">
            <div class="text-center" data-animate="fadeInUp">
                <p class="font-mono text-[10px] uppercase tracking-[0.32em] text-wc-text-tertiary">{{ __('coaches.calc_eyebrow') }}</p>
                <h2 class="mt-3 font-display text-3xl tracking-wide text-wc-text sm:text-4xl">{{ __('coaches.calc_heading') }}</h2>
                <p class="mx-auto mt-4 max-w-xl text-wc-text-secondary">{{ __('coaches.calc_subtitle') }}</p>
            </div>

            <div
                class="coach-calc mt-10 rounded-2xl border border-wc-border bg-wc-bg-tertiary p-6 sm:p-10"
                data-animate="fadeInUp"
                x-data="{
                    clients: {{ (int) ($calc['default'] ?? 10) }},
                    min: {{ (int) ($calc['min'] ?? 1) }},
                    max: {{ (int) ($calc['max'] ?? 30) }},
                    pricePerClient: {{ (int) ($calc['price_per_client'] ?? 380000) }},
                    split: {{ (float) ($calc['split'] ?? 0.6) }},
                    get income() { return Math.round(this.clients * this.pricePerClient * this.split); },
                    fmt(n) {
                        try { return n.toLocaleString('{{ $calc['locale'] ?? 'es-CO' }}', { maximumFractionDigits: 0 }); }
                        catch (_) { return String(n); }
                    }
                }"
            >
                <div class="flex items-center justify-between">
                    <span class="font-mono text-[10px] uppercase tracking-[0.18em] text-wc-text-tertiary">{{ __('coaches.calc_slider_label') }}</span>
                    <span class="font-data text-2xl font-bold text-wc-accent" x-text="clients"></span>
                </div>
                <input
                    type="range"
                    :min="min"
                    :max="max"
                    x-model.number="clients"
                    class="coach-calc-slider mt-4 w-full"
                    aria-label="{{ __('coaches.calc_slider_label') }}"
                >
                <div class="mt-1 flex justify-between font-mono text-[10px] text-wc-text-tertiary">
                    <span x-text="min"></span>
                    <span x-text="max"></span>
                </div>

                <div class="mt-8 border-t border-wc-border pt-6 text-center">
                    <p class="font-mono text-[10px] uppercase tracking-[0.22em] text-wc-text-tertiary">{{ __('coaches.calc_output_label') }}</p>
                    <p class="mt-3 font-data text-4xl font-bold text-wc-accent sm:text-5xl">
                        $<span x-text="fmt(income)">0</span> <span class="text-2xl text-wc-text-secondary sm:text-3xl">{{ $calc['currency'] ?? 'COP' }}</span>
                    </p>
                </div>

                <p class="mt-6 text-center text-xs italic text-wc-text-tertiary">{{ __('coaches.calc_disclaimer') }}</p>
            </div>
        </div>
    </section>

    {{-- Section Divider --}}
    <div class="h-px bg-gradient-to-r from-transparent via-wc-border to-transparent" aria-hidden="true"></div>

    {{-- Requirements --}}
    <section id="requisitos" class="bg-wc-bg hp-cv-section">
        <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-3xl">
                <div data-animate="fadeInUp">
                    <h2 class="font-display text-3xl tracking-wide text-wc-text sm:text-4xl">{{ __('coaches.requirements_heading') }}</h2>
                    <p class="mt-4 text-wc-text-secondary">{{ __('coaches.requirements_subtitle') }}</p>
                </div>

                <div class="stagger-grid mt-10 space-y-4">
                    @foreach([1, 2, 3, 4, 5] as $i)
                        <div class="scroll-reveal card-hover-lift flex items-start gap-4 rounded-lg border border-wc-border bg-wc-bg-tertiary p-5" data-animate="fadeInUp" data-delay="{{ $i * 100 }}">
                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-wc-accent/10">
                                <svg class="h-4 w-4 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                            </div>
                            <div>
                                <h4 class="text-sm font-semibold text-wc-text">{{ __('coaches.req_' . $i . '_title') }}</h4>
                                <p class="mt-1 text-sm text-wc-text-tertiary">{{ __('coaches.req_' . $i . '_body') }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>

                <p class="mt-6 text-center text-xs text-wc-text-tertiary">{{ __('coaches.cred_note') }}</p>
            </div>
        </div>
    </section>

    {{-- Section Divider --}}
    <div class="h-px bg-gradient-to-r from-transparent via-wc-border to-transparent" aria-hidden="true"></div>

    {{-- Process --}}
    <section class="bg-wc-bg-tertiary hp-cv-section">
        <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
            <div class="text-center" data-animate="fadeInUp">
                <h2 class="font-display text-3xl tracking-wide text-wc-text sm:text-4xl">{{ __('coaches.process_heading') }}</h2>
                <p class="mt-4 text-wc-text-secondary">{{ __('coaches.process_subtitle') }}</p>
            </div>

            <div class="relative mx-auto mt-14 grid max-w-4xl grid-cols-1 gap-8 md:grid-cols-3">
                <div class="hidden lg:block absolute top-6 left-[calc(16.67%+1.5rem)] right-[calc(16.67%+1.5rem)] h-px bg-wc-border" aria-hidden="true"></div>

                @foreach([1, 2, 3] as $i)
                    <div class="stagger-grid card-hover-lift relative rounded-xl border border-wc-border bg-wc-bg p-8 text-center" data-animate="slideInUp" data-delay="{{ $i * 100 }}">
                        <div class="@if($i === 1) pulse-glow @endif mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-wc-accent font-display text-xl text-white">{{ $i }}</div>
                        <h3 class="mt-5 text-lg font-semibold text-wc-text">{{ __('coaches.step_' . $i . '_title') }}</h3>
                        <p class="mt-2 text-sm text-wc-text-secondary">{{ __('coaches.step_' . $i . '_body') }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Section Divider --}}
    <div class="h-px bg-gradient-to-r from-transparent via-wc-border to-transparent" aria-hidden="true"></div>

    {{-- FAQ económico --}}
    <section class="bg-wc-bg hp-cv-section">
        <div class="mx-auto max-w-3xl px-4 py-16 sm:px-6 lg:px-8">
            <div class="text-center" data-animate="fadeInUp">
                <h2 class="font-display text-3xl tracking-wide text-wc-text sm:text-4xl">{{ __('coaches.faq_heading') }}</h2>
                <p class="mt-4 text-wc-text-secondary">{{ __('coaches.faq_subtitle') }}</p>
            </div>

            <div class="mt-10" data-animate="fadeInUp">
                <x-public.faq-accordion :items="$faqs" :search="false" :jsonld="true" />
            </div>
        </div>
    </section>

    {{-- Section Divider --}}
    <div class="h-px bg-gradient-to-r from-transparent via-wc-border to-transparent" aria-hidden="true"></div>

    {{-- CTA Final --}}
    <section id="cta-final" class="relative overflow-hidden bg-wc-bg hp-cv-section">
        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
            <div class="absolute -left-32 -top-32 h-80 w-80 rounded-full bg-wc-accent/5 blur-3xl"></div>
            <div class="absolute -bottom-32 -right-32 h-96 w-96 rounded-full bg-wc-accent/8 blur-3xl"></div>
            <div class="absolute left-1/2 top-1/2 h-64 w-64 -translate-x-1/2 -translate-y-1/2 rounded-full bg-wc-accent/3 blur-2xl"></div>
        </div>
        <div class="relative mx-auto max-w-7xl px-4 py-20 text-center sm:px-6 lg:px-8" data-animate="zoomIn">
            <h2 class="font-display text-3xl tracking-wide text-wc-text sm:text-4xl">{{ __('coaches.cta_heading') }}</h2>
            <p class="mx-auto mt-4 max-w-lg text-wc-text-secondary">{{ __('coaches.cta_body') }}</p>
            <div class="mt-8 flex flex-col items-center justify-center gap-3 sm:flex-row sm:gap-4">
                <a href="{{ route('coaches.apply') }}" class="btn-press pulse-glow inline-flex items-center justify-center rounded-full bg-wc-accent px-8 py-3.5 text-base font-semibold text-white shadow-lg shadow-wc-accent/20 hover:bg-wc-accent-hover">
                    {{ __('coaches.cta_button') }}
                    <svg class="ml-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" /></svg>
                </a>
                <a href="https://wa.me/{{ config('wellcore.whatsapp_silvia', '573000000000') }}?text=Hola%2C%20quiero%20hablar%20con%20el%20equipo%20sobre%20ser%20coach%20WellCore" target="_blank" rel="noopener" class="inline-flex items-center justify-center rounded-full border border-wc-border bg-wc-bg-secondary/50 px-6 py-3 text-sm font-medium text-wc-text-secondary hover:bg-wc-bg-secondary">
                    {{ __('coaches.cta_secondary') }}
                </a>
            </div>
        </div>
    </section>

    {{-- Sticky Mobile CTA — solo mobile, oculto cuando #cta-final entra al viewport --}}
    <x-public.sticky-mobile-cta
        :href="route('coaches.apply')"
        :label="__('coaches.sticky_label')"
        hide-at="cta-final"
        :threshold="600"
    />

    {{-- Estilos scoped: slider de la calculadora.
         Mantenemos inline (sin tocar app.css/v2-public.css) para no requerir rebuild. --}}
    <style>
        .coach-calc-slider {
            -webkit-appearance: none;
            appearance: none;
            height: 4px;
            border-radius: 2px;
            background: var(--color-wc-bg-secondary, rgba(255,255,255,0.08));
            outline: none;
        }
        .coach-calc-slider::-webkit-slider-thumb {
            -webkit-appearance: none;
            appearance: none;
            width: 22px;
            height: 22px;
            border-radius: 50%;
            background: var(--color-wc-accent, #DC2626);
            border: 3px solid var(--color-wc-bg, #0a0a0a);
            box-shadow: 0 0 0 1px var(--color-wc-accent, #DC2626);
            cursor: pointer;
            transition: transform 0.18s cubic-bezier(0.22, 1, 0.36, 1);
        }
        .coach-calc-slider::-webkit-slider-thumb:hover { transform: scale(1.12); }
        .coach-calc-slider::-moz-range-thumb {
            width: 22px;
            height: 22px;
            border-radius: 50%;
            background: var(--color-wc-accent, #DC2626);
            border: 3px solid var(--color-wc-bg, #0a0a0a);
            cursor: pointer;
        }
        .coach-calc-slider:focus-visible::-webkit-slider-thumb {
            box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.3);
        }
    </style>

</x-layouts.public>
