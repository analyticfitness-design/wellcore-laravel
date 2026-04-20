<x-layouts.public>
    <x-slot:title>{{ __('rise.title') }}</x-slot:title>
    <x-slot:description>{{ __('rise.description') }}</x-slot:description>

    {{-- ================================================================== --}}
    {{-- 1. HERO (remastered — hero-gradient, extra orbs, pulse-glow)        --}}
    {{-- ================================================================== --}}
    <section class="relative overflow-hidden hero-gradient">
        <div class="absolute inset-0 bg-gradient-to-br from-wc-accent/8 via-transparent to-transparent"></div>
        {{-- Parallax decorative orbs --}}
        <div class="parallax-hero" aria-hidden="true">
            <div class="parallax-orb parallax-orb-1" data-parallax-speed="0.2"></div>
            <div class="parallax-orb parallax-orb-2" data-parallax-speed="0.35"></div>
            <div class="parallax-orb parallax-orb-3" data-parallax-speed="0.15"></div>
            <div class="parallax-orb parallax-orb-4" data-parallax-speed="0.25"></div>
            <div class="parallax-orb parallax-orb-5" data-parallax-speed="0.1"></div>
        </div>
        <div class="relative mx-auto max-w-7xl px-4 py-20 sm:px-6 sm:py-28 lg:px-8 lg:py-36" data-animate="fadeInUp">
            <div class="grid grid-cols-1 items-center gap-12 lg:grid-cols-2">
                {{-- Left — Text --}}
                <div class="max-w-3xl">
                    <p class="text-sm font-semibold uppercase tracking-widest text-wc-accent">{{ __('rise.hero_badge') }}</p>
                    <h1 class="mt-4 font-display text-6xl leading-none tracking-wide text-wc-text sm:text-7xl lg:text-9xl">
                        {{ __('rise.hero_h1_line1') }}<br>
                        <span class="text-gradient-accent">{{ __('rise.hero_h1_line2') }}</span>
                    </h1>
                    <p class="mt-6 max-w-xl text-lg text-wc-text-secondary">
                        {{ __('rise.hero_subtitle') }}
                    </p>

                    {{-- Countdown Timer --}}
                    <div class="mt-8 pulse-glow inline-block rounded-xl p-1" x-data="countdown('2026-03-31T23:59:59')" x-init="start()">
                        <p class="mb-3 text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">{{ __('rise.countdown_label') }}</p>
                        <div class="flex gap-3">
                            <div class="rounded-lg border border-wc-accent/20 bg-wc-accent/5 px-4 py-3 text-center">
                                <p class="font-data text-3xl sm:text-4xl font-bold text-wc-accent" x-text="days">00</p>
                                <p class="text-[10px] uppercase text-wc-text-tertiary">{{ __('rise.countdown_days') }}</p>
                            </div>
                            <div class="rounded-lg border border-wc-accent/20 bg-wc-accent/5 px-4 py-3 text-center">
                                <p class="font-data text-3xl sm:text-4xl font-bold text-wc-accent" x-text="hours">00</p>
                                <p class="text-[10px] uppercase text-wc-text-tertiary">{{ __('rise.countdown_hours') }}</p>
                            </div>
                            <div class="rounded-lg border border-wc-accent/20 bg-wc-accent/5 px-4 py-3 text-center">
                                <p class="font-data text-3xl sm:text-4xl font-bold text-wc-accent" x-text="minutes">00</p>
                                <p class="text-[10px] uppercase text-wc-text-tertiary">{{ __('rise.countdown_min') }}</p>
                            </div>
                            <div class="rounded-lg border border-wc-accent/20 bg-wc-accent/5 px-4 py-3 text-center">
                                <p class="font-data text-3xl sm:text-4xl font-bold text-wc-accent" x-text="seconds">00</p>
                                <p class="text-[10px] uppercase text-wc-text-tertiary">{{ __('rise.countdown_sec') }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8">
                        <a href="{{ route('rise.enroll') }}" class="btn-press pulse-glow inline-flex items-center justify-center rounded-full bg-wc-accent px-8 py-3.5 font-semibold text-white shadow-lg shadow-wc-accent/20">
                            {{ __('rise.cta_join') }}
                            <svg class="ml-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" /></svg>
                        </a>
                        <p class="mt-3 text-xs text-wc-text-tertiary">{{ __('rise.hero_fine_print') }}</p>
                    </div>
                </div>

                {{-- Right — RISE Dashboard Mockup --}}
                <div class="hidden lg:block">
                    <div class="animate-float-slow rounded-xl border border-wc-border bg-wc-bg shadow-2xl shadow-black/10">
                        {{-- Browser chrome --}}
                        <div class="flex items-center gap-2 border-b border-wc-border px-4 py-3">
                            <span class="h-2.5 w-2.5 rounded-full bg-red-500"></span>
                            <span class="h-2.5 w-2.5 rounded-full bg-yellow-500"></span>
                            <span class="h-2.5 w-2.5 rounded-full bg-green-500"></span>
                            <div class="ml-3 flex-1 rounded-md bg-wc-bg-secondary px-3 py-1">
                                <span class="text-xs text-wc-text-tertiary">rise.wellcorefitness.com</span>
                            </div>
                        </div>
                        {{-- Dashboard content --}}
                        <div class="p-5 space-y-4">
                            {{-- Header --}}
                            <div class="flex items-center justify-between">
                                <div>
                                    <h2 class="font-display text-lg tracking-wide text-wc-text">RISE <span class="text-wc-text-tertiary">&mdash;</span> {{ __('rise.mockup_week') }}</h2>
                                </div>
                                <span class="rounded-full bg-wc-accent/10 px-3 py-1 text-[10px] font-semibold text-wc-accent">{{ __('rise.mockup_status') }}</span>
                            </div>

                            {{-- Progress bar --}}
                            <div>
                                <div class="flex items-center justify-between text-xs">
                                    <span class="text-wc-text-secondary">{{ __('rise.mockup_progress') }}</span>
                                    <span class="font-data font-semibold text-wc-text">50%</span>
                                </div>
                                <div class="mt-1.5 h-2 w-full rounded-full bg-wc-bg-secondary">
                                    <div class="h-2 w-1/2 rounded-full bg-wc-accent"></div>
                                </div>
                            </div>

                            {{-- Stats grid 2x2 --}}
                            <div class="grid grid-cols-2 gap-2">
                                <div class="rounded-lg border border-wc-border bg-wc-bg-tertiary p-3 text-center">
                                    <p class="font-data text-xl font-bold text-wc-text">12 <span class="text-sm font-normal text-wc-text-tertiary">/ 30</span></p>
                                    <p class="text-[10px] uppercase tracking-wide text-wc-text-tertiary">{{ __('rise.mockup_day') }}</p>
                                </div>
                                <div class="rounded-lg border border-wc-border bg-wc-bg-tertiary p-3 text-center">
                                    <p class="font-data text-xl font-bold text-emerald-400">85%</p>
                                    <p class="text-[10px] uppercase tracking-wide text-wc-text-tertiary">{{ __('rise.mockup_adherence') }}</p>
                                </div>
                                <div class="rounded-lg border border-wc-border bg-wc-bg-tertiary p-3 text-center">
                                    <p class="font-data text-xl font-bold text-wc-accent">4.2 <span class="text-sm font-normal text-wc-text-tertiary">kg</span></p>
                                    <p class="text-[10px] uppercase tracking-wide text-wc-text-tertiary">{{ __('rise.mockup_lost') }}</p>
                                </div>
                                <div class="rounded-lg border border-wc-border bg-wc-bg-tertiary p-3 text-center">
                                    <p class="font-data text-xl font-bold text-amber-400">8/8</p>
                                    <p class="text-[10px] uppercase tracking-wide text-wc-text-tertiary">{{ __('rise.mockup_habits_today') }}</p>
                                </div>
                            </div>

                            {{-- Daily checklist card --}}
                            <div class="rounded-lg border border-wc-border bg-wc-bg-tertiary p-4">
                                <div class="flex items-center gap-2 mb-3">
                                    <svg class="h-4 w-4 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                                    <span class="text-xs font-semibold text-wc-text">{{ __('rise.mockup_missions') }}</span>
                                </div>
                                <div class="space-y-2">
                                    <div class="flex items-center gap-2.5">
                                        <svg class="h-4 w-4 shrink-0 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                                        <span class="text-xs text-wc-text-secondary line-through">{{ __('rise.mockup_workout_done') }}</span>
                                    </div>
                                    <div class="flex items-center gap-2.5">
                                        <svg class="h-4 w-4 shrink-0 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                                        <span class="text-xs text-wc-text-secondary line-through">{{ __('rise.mockup_water') }}</span>
                                    </div>
                                    <div class="flex items-center gap-2.5">
                                        <div class="h-4 w-4 shrink-0 rounded-full border-2 border-wc-border"></div>
                                        <span class="text-xs text-wc-text-secondary">{{ __('rise.mockup_photo') }}</span>
                                    </div>
                                    <div class="flex items-center gap-2.5">
                                        <div class="h-4 w-4 shrink-0 rounded-full border-2 border-wc-border"></div>
                                        <span class="text-xs text-wc-text-secondary">{{ __('rise.mockup_checkin') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ================================================================== --}}
    {{-- 1B. SOCIAL PROOF COUNTER (NEW)                                      --}}
    {{-- ================================================================== --}}
    <section class="border-y border-wc-border bg-wc-bg">
        <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
            <div class="flex flex-wrap items-center justify-center gap-8 text-center">
                <div class="flex items-center gap-2">
                    <span class="h-2 w-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span class="text-sm text-wc-text-secondary"><span class="font-data font-bold text-wc-text counter-highlight" data-counter="47" data-counter-suffix="+">0+</span> {{ __('rise.social_inscribed', ['count' => '']) }}</span>
                </div>
                <div class="hidden h-4 w-px bg-wc-border sm:block"></div>
                <div class="flex items-center gap-2">
                    <span class="text-sm text-wc-text-secondary">{{ __('rise.social_spots', ['count' => '']) }}<span class="font-data font-bold text-wc-accent">12</span></span>
                </div>
                <div class="hidden h-4 w-px bg-wc-border sm:block"></div>
                <div class="flex items-center gap-2">
                    <svg class="h-4 w-4 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                    <span class="text-sm text-wc-text-secondary">{{ __('rise.social_verified') }} <span class="font-semibold text-wc-text">WellCore</span></span>
                </div>
            </div>
        </div>
    </section>

    <div class="section-divider"></div>

    {{-- ================================================================== --}}
    {{-- 2. QUE INCLUYE (remastered — stagger-grid, card-hover-lift)         --}}
    {{-- ================================================================== --}}
    <section class="bg-wc-bg">
        <div class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8" data-animate="fadeInUp">
            <div class="text-center">
                <h2 class="font-display text-3xl tracking-wide text-wc-text sm:text-4xl">{{ __('rise.includes_h2') }}</h2>
                <p class="mt-2 text-lg text-wc-text-secondary">{{ __('rise.includes_sub') }}</p>
                <p class="mt-1 text-sm text-wc-text-tertiary">{{ __('rise.includes_desc') }}</p>
            </div>

            <div class="mt-14 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-5 stagger-grid">
                <div class="scroll-reveal-scale card-hover-lift rounded-xl border border-wc-border bg-wc-bg-tertiary p-8 transition-colors hover:border-wc-accent/40" data-animate="scaleIn" data-delay="100">
                    <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-wc-accent/10">
                        <svg class="h-6 w-6 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" />
                        </svg>
                    </div>
                    <h3 class="mt-5 text-lg font-semibold text-wc-text">{{ __('rise.pillar1_title') }}</h3>
                    <p class="mt-2 text-sm text-wc-text-secondary">{{ __('rise.pillar1_desc') }}</p>
                </div>

                <div class="scroll-reveal-scale card-hover-lift rounded-xl border border-wc-border bg-wc-bg-tertiary p-8 transition-colors hover:border-wc-accent/40" data-animate="scaleIn" data-delay="200">
                    <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-wc-accent/10">
                        <svg class="h-6 w-6 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v17.25m0 0c-1.472 0-2.882.265-4.185.75M12 20.25c1.472 0 2.882.265 4.185.75M18.75 4.97A48.416 48.416 0 0 0 12 4.5c-2.291 0-4.545.16-6.75.47m13.5 0c1.01.143 2.01.317 3 .52m-3-.52 2.62 10.726c.122.499-.106 1.028-.589 1.202a5.988 5.988 0 0 1-2.031.352 5.988 5.988 0 0 1-2.031-.352c-.483-.174-.711-.703-.59-1.202L18.75 4.971Zm-16.5.52c.99-.203 1.99-.377 3-.52m0 0 2.62 10.726c.122.499-.106 1.028-.589 1.202a5.989 5.989 0 0 1-2.031.352 5.989 5.989 0 0 1-2.031-.352c-.483-.174-.711-.703-.59-1.202L5.25 4.971Z" />
                        </svg>
                    </div>
                    <h3 class="mt-5 text-lg font-semibold text-wc-text">{{ __('rise.pillar2_title') }}</h3>
                    <p class="mt-2 text-sm text-wc-text-secondary">{{ __('rise.pillar2_desc') }}</p>
                </div>

                <div class="scroll-reveal-scale card-hover-lift rounded-xl border border-wc-border bg-wc-bg-tertiary p-8 transition-colors hover:border-wc-accent/40" data-animate="scaleIn" data-delay="300">
                    <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-wc-accent/10">
                        <svg class="h-6 w-6 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.745 3.745 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z" />
                        </svg>
                    </div>
                    <h3 class="mt-5 text-lg font-semibold text-wc-text">{{ __('rise.pillar3_title') }}</h3>
                    <p class="mt-2 text-sm text-wc-text-secondary">{{ __('rise.pillar3_desc') }}</p>
                </div>

                <div class="scroll-reveal-scale card-hover-lift rounded-xl border border-wc-border bg-wc-bg-tertiary p-8 transition-colors hover:border-wc-accent/40" data-animate="scaleIn" data-delay="400">
                    <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-wc-accent/10">
                        <svg class="h-6 w-6 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                        </svg>
                    </div>
                    <h3 class="mt-5 text-lg font-semibold text-wc-text">{{ __('rise.pillar4_title') }}</h3>
                    <p class="mt-2 text-sm text-wc-text-secondary">{{ __('rise.pillar4_desc') }}</p>
                </div>

                <div class="scroll-reveal-scale card-hover-lift rounded-xl border border-wc-border bg-wc-bg-tertiary p-8 transition-colors hover:border-wc-accent/40" data-animate="scaleIn" data-delay="500">
                    <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-wc-accent/10">
                        <svg class="h-6 w-6 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v5.714a2.25 2.25 0 0 1-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 0 1 4.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0 1 12 15a9.065 9.065 0 0 1-6.23-.693L4.2 13.9m15.6 1.4-1.57.393M4.2 13.9l-1.57.393m0 0a48.667 48.667 0 0 1-.014-4.337m.014 4.337a48.667 48.667 0 0 0 4.918 2.048M18.43 14.293a48.667 48.667 0 0 0 4.918-2.048m-4.918 2.048 1.57.393" />
                        </svg>
                    </div>
                    <h3 class="mt-5 text-lg font-semibold text-wc-text">{{ __('rise.pillar5_title') }}</h3>
                    <p class="mt-2 text-sm text-wc-text-secondary">{{ __('rise.pillar5_desc') }}</p>
                </div>
            </div>
        </div>
    </section>

    <div class="section-divider"></div>

    {{-- ================================================================== --}}
    {{-- 3. TU DASHBOARD RISE (remastered — animate-float, animate-float-slow) --}}
    {{-- ================================================================== --}}
    <section class="bg-wc-bg-tertiary">
        <div class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8" data-animate="slideInLeft">
            <div class="text-center">
                <p class="text-xs font-semibold uppercase tracking-widest text-wc-accent">{{ __('rise.dashboard_badge') }}</p>
                <h2 class="mt-3 font-display text-3xl tracking-wide text-wc-text sm:text-4xl">{{ __('rise.dashboard_h2') }}</h2>
                <p class="mx-auto mt-4 max-w-2xl text-sm text-wc-text-tertiary">
                    {{ __('rise.dashboard_desc') }}
                </p>
            </div>

            <div class="mt-16 grid grid-cols-1 items-center gap-12 lg:grid-cols-2">
                {{-- LEFT — Phone mockup --}}
                <div class="flex justify-center">
                    <div class="animate-float mx-auto w-[280px] rounded-[2.5rem] border-[6px] border-wc-border bg-wc-bg-tertiary p-2 shadow-2xl shadow-wc-accent/10">
                        {{-- Notch --}}
                        <div class="mx-auto mb-2 h-5 w-24 rounded-full bg-wc-bg-secondary"></div>
                        {{-- Screen content --}}
                        <div class="rounded-[2rem] bg-wc-bg p-4 space-y-3">
                            {{-- Day heading --}}
                            <div class="text-center">
                                <p class="text-[10px] uppercase tracking-wide text-wc-text-tertiary">{{ __('rise.phone_reto') }}</p>
                                <p class="font-display text-3xl tracking-wide text-wc-text">{{ __('rise.phone_day') }}</p>
                            </div>

                            {{-- Progress ring --}}
                            <div class="flex justify-center">
                                <div class="relative flex h-20 w-20 items-center justify-center">
                                    <svg class="h-20 w-20 -rotate-90" viewBox="0 0 36 36">
                                        <path class="text-wc-bg-secondary" stroke="currentColor" stroke-width="3" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"/>
                                        <path class="text-wc-accent" stroke="currentColor" stroke-width="3" fill="none" stroke-dasharray="50, 100" stroke-linecap="round" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"/>
                                    </svg>
                                    <span class="absolute font-data text-lg font-bold text-wc-text">50%</span>
                                </div>
                            </div>

                            {{-- Habit checklist --}}
                            <div class="space-y-2">
                                <p class="text-[10px] font-semibold uppercase tracking-wide text-wc-text-tertiary">{{ __('rise.phone_habits') }}</p>
                                <div class="flex items-center gap-2">
                                    <div class="flex h-4 w-4 shrink-0 items-center justify-center rounded bg-emerald-400/20">
                                        <svg class="h-3 w-3 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                                    </div>
                                    <span class="text-[11px] text-wc-text-secondary">{{ __('rise.phone_workout') }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <div class="flex h-4 w-4 shrink-0 items-center justify-center rounded bg-emerald-400/20">
                                        <svg class="h-3 w-3 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                                    </div>
                                    <span class="text-[11px] text-wc-text-secondary">{{ __('rise.phone_water') }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <div class="flex h-4 w-4 shrink-0 items-center justify-center rounded bg-emerald-400/20">
                                        <svg class="h-3 w-3 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                                    </div>
                                    <span class="text-[11px] text-wc-text-secondary">{{ __('rise.phone_sleep') }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <div class="h-4 w-4 shrink-0 rounded border border-wc-border"></div>
                                    <span class="text-[11px] text-wc-text-secondary">{{ __('rise.phone_photo') }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <div class="h-4 w-4 shrink-0 rounded border border-wc-border"></div>
                                    <span class="text-[11px] text-wc-text-secondary">{{ __('rise.phone_food') }}</span>
                                </div>
                            </div>

                            {{-- Streak badge --}}
                            <div class="flex justify-center">
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-wc-accent/10 px-3 py-1">
                                    <svg class="h-3.5 w-3.5 text-wc-accent" fill="currentColor" viewBox="0 0 24 24"><path d="M12.75 3.03v.568c0 .334.148.65.405.864A6.75 6.75 0 0 1 9.75 15.75a.75.75 0 0 1 0-1.5 5.25 5.25 0 0 0 4.659-7.643.75.75 0 0 0-1.334.21A4.508 4.508 0 0 1 8.25 10.5a.75.75 0 0 1 0-1.5 3 3 0 0 0 2.903-3.75.75.75 0 0 0-.746-.692h-.107a7.502 7.502 0 0 0-2.55 14.578.75.75 0 0 1-.212 1.467A9.001 9.001 0 0 1 12.75 3.03Z"/></svg>
                                    <span class="text-[11px] font-semibold text-wc-accent">{{ __('rise.phone_streak') }}</span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- RIGHT — Desktop/Tablet mockup --}}
                <div>
                    <div class="animate-float-slow rounded-xl border border-wc-border bg-wc-bg shadow-2xl shadow-black/10">
                        {{-- Browser chrome --}}
                        <div class="flex items-center gap-2 border-b border-wc-border px-4 py-3">
                            <span class="h-2.5 w-2.5 rounded-full bg-red-500"></span>
                            <span class="h-2.5 w-2.5 rounded-full bg-yellow-500"></span>
                            <span class="h-2.5 w-2.5 rounded-full bg-green-500"></span>
                            <div class="ml-3 flex-1 rounded-md bg-wc-bg-secondary px-3 py-1">
                                <span class="text-xs text-wc-text-tertiary">rise.wellcorefitness.com/dashboard</span>
                            </div>
                        </div>
                        {{-- Dashboard content --}}
                        <div class="p-5 space-y-4">
                            {{-- Top stats row --}}
                            <div class="grid grid-cols-4 gap-2">
                                <div class="rounded-lg border border-wc-border bg-wc-bg-tertiary p-2.5 text-center">
                                    <p class="font-data text-lg font-bold text-wc-accent">50%</p>
                                    <p class="text-[10px] text-wc-text-tertiary">{{ __('rise.dash_progress') }}</p>
                                </div>
                                <div class="rounded-lg border border-wc-border bg-wc-bg-tertiary p-2.5 text-center">
                                    <p class="font-data text-lg font-bold text-emerald-400">85%</p>
                                    <p class="text-[10px] text-wc-text-tertiary">{{ __('rise.dash_adherence') }}</p>
                                </div>
                                <div class="rounded-lg border border-wc-border bg-wc-bg-tertiary p-2.5 text-center">
                                    <p class="font-data text-lg font-bold text-amber-400">12</p>
                                    <p class="text-[10px] text-wc-text-tertiary">{{ __('rise.dash_streak') }}</p>
                                </div>
                                <div class="rounded-lg border border-wc-border bg-wc-bg-tertiary p-2.5 text-center">
                                    <p class="font-data text-lg font-bold text-wc-text">-4.2<span class="text-xs font-normal text-wc-text-tertiary">kg</span></p>
                                    <p class="text-[10px] text-wc-text-tertiary">{{ __('rise.dash_weight') }}</p>
                                </div>
                            </div>

                            {{-- Weekly calendar grid --}}
                            <div class="rounded-lg border border-wc-border bg-wc-bg-tertiary p-3">
                                <p class="mb-2 text-[10px] font-semibold uppercase tracking-wide text-wc-text-tertiary">{{ __('rise.dash_week2') }}</p>
                                <div class="grid grid-cols-7 gap-1">
                                    @php $daysOfWeek = ['Lun','Mar','Mie','Jue','Vie','Sab','Dom']; @endphp
                                    @foreach ($daysOfWeek as $i => $day)
                                        <div class="text-center">
                                            <p class="text-[9px] text-wc-text-tertiary">{{ $day }}</p>
                                            @if ($i < 3)
                                                <div class="mx-auto mt-1 flex h-7 w-7 items-center justify-center rounded-full bg-emerald-400/20">
                                                    <svg class="h-3 w-3 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                                                </div>
                                            @elseif ($i === 3)
                                                <div class="mx-auto mt-1 flex h-7 w-7 items-center justify-center rounded-full border-2 border-wc-accent bg-wc-accent/10">
                                                    <span class="font-data text-[10px] font-bold text-wc-accent">15</span>
                                                </div>
                                            @else
                                                <div class="mx-auto mt-1 flex h-7 w-7 items-center justify-center rounded-full bg-wc-bg-secondary">
                                                    <span class="font-data text-[10px] text-wc-text-tertiary">{{ 15 + ($i - 3) }}</span>
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Bottom row: Measurements + Training --}}
                            <div class="grid grid-cols-2 gap-3">
                                {{-- Measurements card --}}
                                <div class="rounded-lg border border-wc-border bg-wc-bg-tertiary p-3">
                                    <p class="mb-2 text-[10px] font-semibold uppercase tracking-wide text-wc-text-tertiary">{{ __('rise.dash_measurements') }}</p>
                                    <div class="space-y-1.5">
                                        <div class="flex items-center justify-between">
                                            <span class="text-[11px] text-wc-text-secondary">{{ __('rise.dash_weight') }}</span>
                                            <span class="flex items-center gap-1 text-[11px]">
                                                <span class="font-data font-semibold text-wc-text">78.3 kg</span>
                                                <svg class="h-3 w-3 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>
                                            </span>
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <span class="text-[11px] text-wc-text-secondary">{{ __('rise.dash_waist') }}</span>
                                            <span class="flex items-center gap-1 text-[11px]">
                                                <span class="font-data font-semibold text-wc-text">86 cm</span>
                                                <svg class="h-3 w-3 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>
                                            </span>
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <span class="text-[11px] text-wc-text-secondary">{{ __('rise.dash_hip') }}</span>
                                            <span class="flex items-center gap-1 text-[11px]">
                                                <span class="font-data font-semibold text-wc-text">98 cm</span>
                                                <svg class="h-3 w-3 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                {{-- Training log card --}}
                                <div class="rounded-lg border border-wc-accent/30 bg-wc-accent/5 p-3">
                                    <p class="mb-2 text-[10px] font-semibold uppercase tracking-wide text-wc-accent">{{ __('rise.dash_training') }}</p>
                                    <p class="text-xs font-semibold text-wc-text">{{ __('rise.dash_upper_body') }}</p>
                                    <div class="mt-2 space-y-1">
                                        <div class="flex items-center justify-between text-[10px] text-wc-text-secondary">
                                            <span>{{ __('rise.dash_bench') }}</span>
                                            <span class="font-data font-semibold text-wc-text">4x10</span>
                                        </div>
                                        <div class="flex items-center justify-between text-[10px] text-wc-text-secondary">
                                            <span>{{ __('rise.dash_row') }}</span>
                                            <span class="font-data font-semibold text-wc-text">4x10</span>
                                        </div>
                                        <div class="flex items-center justify-between text-[10px] text-wc-text-secondary">
                                            <span>{{ __('rise.dash_ohp') }}</span>
                                            <span class="font-data font-semibold text-wc-text">3x12</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="section-divider"></div>

    {{-- ================================================================== --}}
    {{-- 4. SEGUIMIENTO EN TIEMPO REAL (remastered — stagger-grid, card-glow) --}}
    {{-- ================================================================== --}}
    <section class="bg-wc-bg">
        <div class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8" data-animate="fadeIn">
            <div class="text-center">
                <p class="text-xs font-semibold uppercase tracking-widest text-wc-accent">{{ __('rise.tracking_badge') }}</p>
                <h2 class="mt-3 font-display text-3xl tracking-wide text-wc-text sm:text-4xl">{{ __('rise.tracking_h2') }}</h2>
                <p class="mx-auto mt-4 max-w-2xl text-sm text-wc-text-tertiary">
                    {{ __('rise.tracking_desc') }}
                </p>
            </div>

            <div class="mt-14 grid grid-cols-1 gap-6 md:grid-cols-3 stagger-grid">
                {{-- Tracking Diario --}}
                <div class="card-hover-lift card-glow rounded-xl border border-wc-border bg-wc-bg-tertiary p-8 transition-colors hover:border-wc-accent/40" data-delay="100">
                    <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-wc-accent/10">
                        <svg class="h-6 w-6 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.125 2.25h-4.5c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125v-9M10.125 2.25h.375a9 9 0 0 1 9 9v.375M10.125 2.25A3.375 3.375 0 0 1 13.5 5.625v1.5c0 .621.504 1.125 1.125 1.125h1.5a3.375 3.375 0 0 1 3.375 3.375M9 15l2.25 2.25L15 12" />
                        </svg>
                    </div>
                    <h3 class="mt-5 text-lg font-semibold text-wc-text">{{ __('rise.track1_title') }}</h3>
                    <p class="mt-2 text-sm text-wc-text-secondary">
                        {{ __('rise.track1_desc') }}
                    </p>
                    <div class="mt-4 space-y-2">
                        <div class="flex items-center gap-2 text-xs text-wc-text-tertiary">
                            <div class="h-1 w-1 rounded-full bg-wc-accent"></div>
                            {{ __('rise.track1_b1') }}
                        </div>
                        <div class="flex items-center gap-2 text-xs text-wc-text-tertiary">
                            <div class="h-1 w-1 rounded-full bg-wc-accent"></div>
                            {{ __('rise.track1_b2') }}
                        </div>
                        <div class="flex items-center gap-2 text-xs text-wc-text-tertiary">
                            <div class="h-1 w-1 rounded-full bg-wc-accent"></div>
                            {{ __('rise.track1_b3') }}
                        </div>
                    </div>
                </div>

                {{-- Mediciones Semanales --}}
                <div class="card-hover-lift card-glow rounded-xl border border-wc-border bg-wc-bg-tertiary p-8 transition-colors hover:border-wc-accent/40" data-delay="200">
                    <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-wc-accent/10">
                        <svg class="h-6 w-6 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                        </svg>
                    </div>
                    <h3 class="mt-5 text-lg font-semibold text-wc-text">{{ __('rise.track2_title') }}</h3>
                    <p class="mt-2 text-sm text-wc-text-secondary">
                        {{ __('rise.track2_desc') }}
                    </p>
                    <div class="mt-4 space-y-2">
                        <div class="flex items-center gap-2 text-xs text-wc-text-tertiary">
                            <div class="h-1 w-1 rounded-full bg-wc-accent"></div>
                            {{ __('rise.track2_b1') }}
                        </div>
                        <div class="flex items-center gap-2 text-xs text-wc-text-tertiary">
                            <div class="h-1 w-1 rounded-full bg-wc-accent"></div>
                            {{ __('rise.track2_b2') }}
                        </div>
                        <div class="flex items-center gap-2 text-xs text-wc-text-tertiary">
                            <div class="h-1 w-1 rounded-full bg-wc-accent"></div>
                            {{ __('rise.track2_b3') }}
                        </div>
                    </div>
                </div>

                {{-- Analisis de Progreso --}}
                <div class="card-hover-lift card-glow rounded-xl border border-wc-border bg-wc-bg-tertiary p-8 transition-colors hover:border-wc-accent/40" data-delay="300">
                    <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-wc-accent/10">
                        <svg class="h-6 w-6 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18 9 11.25l4.306 4.306a11.95 11.95 0 0 1 5.814-5.518l2.74-1.22m0 0-5.94-2.281m5.94 2.28-2.28 5.941" />
                        </svg>
                    </div>
                    <h3 class="mt-5 text-lg font-semibold text-wc-text">{{ __('rise.track3_title') }}</h3>
                    <p class="mt-2 text-sm text-wc-text-secondary">
                        {{ __('rise.track3_desc') }}
                    </p>
                    <div class="mt-4 space-y-2">
                        <div class="flex items-center gap-2 text-xs text-wc-text-tertiary">
                            <div class="h-1 w-1 rounded-full bg-wc-accent"></div>
                            {{ __('rise.track3_b1') }}
                        </div>
                        <div class="flex items-center gap-2 text-xs text-wc-text-tertiary">
                            <div class="h-1 w-1 rounded-full bg-wc-accent"></div>
                            {{ __('rise.track3_b2') }}
                        </div>
                        <div class="flex items-center gap-2 text-xs text-wc-text-tertiary">
                            <div class="h-1 w-1 rounded-full bg-wc-accent"></div>
                            {{ __('rise.track3_b3') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="section-divider"></div>

    {{-- ================================================================== --}}
    {{-- 5. PARA QUIEN ES (remastered — scroll-reveal, counter-highlight)    --}}
    {{-- ================================================================== --}}
    <section class="bg-wc-bg-tertiary">
        <div class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8" data-animate="slideInRight">
            <h2 class="text-center font-display text-3xl tracking-wide text-wc-text sm:text-4xl">{{ __('rise.for_whom_h2') }}</h2>

            <div class="mt-12 grid grid-cols-1 gap-8 md:grid-cols-3">
                <div class="scroll-reveal flex gap-4" data-delay="100">
                    <span class="counter-highlight font-data text-4xl font-bold text-wc-accent/20">01</span>
                    <div>
                        <h3 class="text-lg font-semibold text-wc-text">{{ __('rise.for1_title') }}</h3>
                        <p class="mt-2 text-sm text-wc-text-secondary">{{ __('rise.for1_desc') }}</p>
                    </div>
                </div>
                <div class="scroll-reveal flex gap-4" data-delay="200">
                    <span class="counter-highlight font-data text-4xl font-bold text-wc-accent/20">02</span>
                    <div>
                        <h3 class="text-lg font-semibold text-wc-text">{{ __('rise.for2_title') }}</h3>
                        <p class="mt-2 text-sm text-wc-text-secondary">{{ __('rise.for2_desc') }}</p>
                    </div>
                </div>
                <div class="scroll-reveal flex gap-4" data-delay="300">
                    <span class="counter-highlight font-data text-4xl font-bold text-wc-accent/20">03</span>
                    <div>
                        <h3 class="text-lg font-semibold text-wc-text">{{ __('rise.for3_title') }}</h3>
                        <p class="mt-2 text-sm text-wc-text-secondary">{{ __('rise.for3_desc') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="section-divider"></div>

    {{-- ================================================================== --}}
    {{-- 6. TESTIMONIOS (remastered — card-hover-lift, scroll-reveal, stagger) --}}
    {{-- ================================================================== --}}
    <section class="bg-wc-bg">
        <div class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8" data-animate="fadeInUp">
            <h2 class="text-center font-display text-3xl tracking-wide text-wc-text sm:text-4xl">{{ __('rise.testimonials_h2') }}</h2>

            <div class="mt-12 grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-3">
                <div class="scroll-reveal card-hover-lift rounded-xl border border-wc-border bg-wc-bg-tertiary p-8" data-delay="100">
                    <div class="flex gap-1">
                        @for ($i = 0; $i < 5; $i++)
                            <svg class="h-4 w-4 text-wc-accent" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        @endfor
                    </div>
                    <p class="mt-4 text-sm text-wc-text-secondary">
                        {{ __('rise.test1_quote') }}
                    </p>
                    <div class="mt-6 flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-wc-accent/10">
                            <span class="text-sm font-semibold text-wc-accent">LM</span>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-wc-text">{{ __('rise.test1_name') }}</p>
                            <p class="text-xs text-wc-text-tertiary">{{ __('rise.test1_meta') }}</p>
                        </div>
                    </div>
                </div>

                <div class="scroll-reveal card-hover-lift rounded-xl border border-wc-border bg-wc-bg-tertiary p-8" data-delay="200">
                    <div class="flex gap-1">
                        @for ($i = 0; $i < 5; $i++)
                            <svg class="h-4 w-4 text-wc-accent" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        @endfor
                    </div>
                    <p class="mt-4 text-sm text-wc-text-secondary">
                        {{ __('rise.test2_quote') }}
                    </p>
                    <div class="mt-6 flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-wc-accent/10">
                            <span class="text-sm font-semibold text-wc-accent">CR</span>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-wc-text">{{ __('rise.test2_name') }}</p>
                            <p class="text-xs text-wc-text-tertiary">{{ __('rise.test2_meta') }}</p>
                        </div>
                    </div>
                </div>

                <div class="scroll-reveal card-hover-lift rounded-xl border border-wc-border bg-wc-bg-tertiary p-8" data-delay="300">
                    <div class="flex gap-1">
                        @for ($i = 0; $i < 5; $i++)
                            <svg class="h-4 w-4 text-wc-accent" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        @endfor
                    </div>
                    <p class="mt-4 text-sm text-wc-text-secondary">
                        {{ __('rise.test3_quote') }}
                    </p>
                    <div class="mt-6 flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-wc-accent/10">
                            <span class="text-sm font-semibold text-wc-accent">AP</span>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-wc-text">{{ __('rise.test3_name') }}</p>
                            <p class="text-xs text-wc-text-tertiary">{{ __('rise.test3_meta') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="section-divider"></div>

    {{-- ================================================================== --}}
    {{-- 7. PRICING (remastered — card-glow, pulse-glow, badge-shine)        --}}
    {{-- ================================================================== --}}
    <section class="bg-wc-bg-tertiary">
        <div class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8" data-animate="scaleIn">
            <div class="text-center">
                <h2 class="font-display text-3xl tracking-wide text-wc-text sm:text-4xl">{{ __('rise.pricing_h2') }}</h2>
                <p class="mt-2 text-lg text-wc-text-secondary">{{ __('rise.pricing_sub') }}</p>
                <p class="mt-1 text-sm text-wc-text-tertiary">{{ __('rise.pricing_fine') }}</p>
            </div>

            <div class="mx-auto mt-12 max-w-md">
                <div class="card-glow pulse-glow rounded-2xl border-2 border-wc-accent bg-wc-bg p-8 text-center">
                    <span class="badge-shine inline-block rounded-full bg-wc-accent/10 px-4 py-1 text-xs font-semibold text-wc-accent">{{ __('rise.pricing_badge') }}</span>

                    <div class="mt-6">
                        <span class="font-data text-5xl font-bold text-wc-text">$99.900</span>
                        <p class="mt-1 text-sm text-wc-text-tertiary">{{ __('rise.pricing_period') }}</p>
                    </div>

                    <ul class="mt-8 space-y-3 text-left">
                        <li class="flex items-center gap-3 text-sm text-wc-text-secondary">
                            <svg class="h-4 w-4 shrink-0 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                            {{ __('rise.feat1') }}
                        </li>
                        <li class="flex items-center gap-3 text-sm text-wc-text-secondary">
                            <svg class="h-4 w-4 shrink-0 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                            {{ __('rise.feat2') }}
                        </li>
                        <li class="flex items-center gap-3 text-sm text-wc-text-secondary">
                            <svg class="h-4 w-4 shrink-0 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                            {{ __('rise.feat3') }}
                        </li>
                        <li class="flex items-center gap-3 text-sm text-wc-text-secondary">
                            <svg class="h-4 w-4 shrink-0 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                            {{ __('rise.feat4') }}
                        </li>
                        <li class="flex items-center gap-3 text-sm text-wc-text-secondary">
                            <svg class="h-4 w-4 shrink-0 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                            {{ __('rise.feat5') }}
                        </li>
                    </ul>

                    <a href="{{ route('rise.enroll') }}" class="btn-press pulse-glow mt-8 flex w-full items-center justify-center rounded-full bg-wc-accent px-8 py-3.5 font-semibold text-white shadow-lg shadow-wc-accent/20">
                        {{ __('rise.cta_join2') }}
                        <svg class="ml-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" /></svg>
                    </a>

                    <p class="mt-4 text-xs text-wc-text-tertiary">{{ __('rise.pricing_trust') }}</p>
                </div>
            </div>
        </div>
    </section>

    <div class="section-divider"></div>

    {{-- ================================================================== --}}
    {{-- 8. URGENCY CTA (remastered — gradient bg, orbs, btn-press, badge-shine) --}}
    {{-- ================================================================== --}}
    <section class="bg-wc-bg">
        <div class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8" data-animate="fadeInUp">
            <div class="relative overflow-hidden rounded-2xl border border-wc-accent/30 bg-wc-bg-tertiary p-10 sm:p-16">
                {{-- Gradient background --}}
                <div class="absolute inset-0 bg-gradient-to-br from-wc-accent/10 via-transparent to-wc-accent/5"></div>
                {{-- Decorative orbs --}}
                <div aria-hidden="true" class="pointer-events-none absolute -left-16 -top-16 h-64 w-64 rounded-full bg-wc-accent/5 blur-3xl"></div>
                <div aria-hidden="true" class="pointer-events-none absolute -bottom-16 -right-16 h-64 w-64 rounded-full bg-wc-accent/8 blur-3xl"></div>
                <div class="relative text-center">
                    <h2 class="font-display text-3xl tracking-wide text-wc-text sm:text-5xl">{{ __('rise.urgency_h2') }}</h2>
                    <p class="mt-4 text-lg text-wc-text-secondary">
                        {{ __('rise.urgency_sub') }} <span class="badge-shine font-semibold text-wc-accent">{{ __('rise.urgency_date') }}</span>
                    </p>
                    <p class="mx-auto mt-2 max-w-md text-sm text-wc-text-tertiary">
                        {{ __('rise.urgency_desc') }}
                    </p>
                    <div class="mt-8">
                        <a href="{{ route('rise.enroll') }}" class="btn-press pulse-glow inline-flex items-center justify-center rounded-full bg-wc-accent px-8 py-3.5 font-semibold text-white shadow-lg shadow-wc-accent/20">
                            {{ __('rise.cta_join3') }}
                            <svg class="ml-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" /></svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Countdown Alpine.js Component --}}
    <script>
        function countdown(deadline) {
            return {
                days: '00', hours: '00', minutes: '00', seconds: '00',
                start() {
                    const update = () => {
                        const now = new Date().getTime();
                        const end = new Date(deadline).getTime();
                        const diff = end - now;
                        if (diff <= 0) { this.days = '00'; this.hours = '00'; this.minutes = '00'; this.seconds = '00'; return; }
                        this.days = String(Math.floor(diff / 86400000)).padStart(2, '0');
                        this.hours = String(Math.floor((diff % 86400000) / 3600000)).padStart(2, '0');
                        this.minutes = String(Math.floor((diff % 3600000) / 60000)).padStart(2, '0');
                        this.seconds = String(Math.floor((diff % 60000) / 1000)).padStart(2, '0');
                    };
                    update();
                    setInterval(update, 1000);
                }
            };
        }
    </script>

</x-layouts.public>
