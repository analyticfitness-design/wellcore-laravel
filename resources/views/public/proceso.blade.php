<x-layouts.public>
    <x-slot:title>Asi Funciona el Proceso - 4 Fases, 12 Semanas | WellCore Fitness</x-slot:title>
    <x-slot:description>De tu diagnostico inicial a tus primeros resultados: 4 fases, 12 semanas, 1 objetivo. Proceso 100% personalizado con seguimiento 1:1 de coach real.</x-slot:description>

    {{-- Hero Section --}}
    <section class="hero-gradient relative overflow-hidden bg-wc-bg-tertiary">
        <div class="absolute inset-0 bg-gradient-to-br from-wc-accent/5 via-transparent to-transparent"></div>
        {{-- Parallax decorative orbs --}}
        <div class="parallax-hero" aria-hidden="true">
            <div class="parallax-orb parallax-orb-1" data-parallax-speed="0.2"></div>
            <div class="parallax-orb parallax-orb-2" data-parallax-speed="0.35"></div>
            <div class="parallax-orb parallax-orb-3" data-parallax-speed="0.15"></div>
            <div class="parallax-orb parallax-orb-4" data-parallax-speed="0.25"></div>
            <div class="parallax-orb parallax-orb-5" data-parallax-speed="0.1"></div>
        </div>
        <div class="relative mx-auto max-w-7xl px-4 py-20 sm:px-6 sm:py-28 lg:px-8 lg:py-36" data-animate="fadeInUp">
            <div class="mx-auto max-w-3xl text-center">
                <p class="text-sm font-semibold uppercase tracking-widest text-wc-accent">{{ __('proceso.hero.label') }}</p>
                <h1 class="mt-4 font-display text-5xl leading-none tracking-wide text-wc-text sm:text-6xl lg:text-8xl">
                    {{ __('proceso.hero.title_line1') }}<br>
                    <span class="text-gradient-accent text-wc-accent">{{ __('proceso.hero.title_line2') }}</span>
                </h1>
                <p class="mx-auto mt-6 max-w-xl text-lg text-wc-text-secondary">
                    {{ __('proceso.hero.description') }}
                </p>
            </div>
        </div>
    </section>

    <div class="section-divider"></div>

    {{-- Stats Bar --}}
    <section class="scroll-reveal border-y border-wc-border bg-wc-bg">
        <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8" data-animate="fadeInUp">
            <div class="grid grid-cols-2 gap-6 sm:grid-cols-4">
                <div class="text-center">
                    <p class="counter-highlight font-data text-3xl font-bold text-wc-accent" data-counter="4" data-counter-suffix="">4</p>
                    <p class="mt-1 text-sm font-medium text-wc-text-secondary">{{ __('proceso.stats.phases') }}</p>
                </div>
                <div class="text-center">
                    <p class="counter-highlight font-data text-3xl font-bold text-wc-accent" data-counter="12" data-counter-suffix="">12</p>
                    <p class="mt-1 text-sm font-medium text-wc-text-secondary">{{ __('proceso.stats.weeks') }}</p>
                </div>
                <div class="text-center">
                    <p class="font-data text-3xl font-bold text-wc-accent">1:1</p>
                    <p class="mt-1 text-sm font-medium text-wc-text-secondary">{{ __('proceso.stats.coach') }}</p>
                </div>
                <div class="text-center">
                    <p class="counter-highlight font-data text-3xl font-bold text-wc-accent" data-counter="0" data-counter-suffix="">0</p>
                    <p class="mt-1 text-sm font-medium text-wc-text-secondary">{{ __('proceso.stats.generic_templates') }}</p>
                </div>
            </div>
        </div>
    </section>

    <div class="section-divider"></div>

    {{-- Phase Navigation Pills --}}
    <section class="scroll-reveal bg-wc-bg-tertiary" x-data="{ activePhase: null }">
        <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8" data-animate="fadeInUp">
            <div class="flex gap-3 overflow-x-auto pb-2 scrollbar-hide">
                <a href="#fase-01"
                   @click="activePhase = 'f01'"
                   :class="activePhase === 'f01' ? 'bg-wc-accent text-white border-wc-accent' : 'bg-wc-bg border-wc-border hover:border-wc-accent/40'"
                   class="flex shrink-0 items-center gap-3 rounded-full border px-5 py-3 transition-all duration-300 scroll-smooth">
                    <span class="font-data text-xs font-bold" :class="activePhase === 'f01' ? 'text-white' : 'text-wc-accent'">F01</span>
                    <div>
                        <p class="text-sm font-semibold" :class="activePhase === 'f01' ? 'text-white' : 'text-wc-text'">{{ __('proceso.nav.f01_name') }}</p>
                        <p class="text-xs" :class="activePhase === 'f01' ? 'text-white/70' : 'text-wc-text-tertiary'">{{ __('proceso.nav.f01_range') }}</p>
                    </div>
                </a>
                <a href="#fase-02"
                   @click="activePhase = 'f02'"
                   :class="activePhase === 'f02' ? 'bg-wc-accent text-white border-wc-accent' : 'bg-wc-bg border-wc-border hover:border-wc-accent/40'"
                   class="flex shrink-0 items-center gap-3 rounded-full border px-5 py-3 transition-all duration-300 scroll-smooth">
                    <span class="font-data text-xs font-bold" :class="activePhase === 'f02' ? 'text-white' : 'text-wc-accent'">F02</span>
                    <div>
                        <p class="text-sm font-semibold" :class="activePhase === 'f02' ? 'text-white' : 'text-wc-text'">{{ __('proceso.nav.f02_name') }}</p>
                        <p class="text-xs" :class="activePhase === 'f02' ? 'text-white/70' : 'text-wc-text-tertiary'">{{ __('proceso.nav.f02_range') }}</p>
                    </div>
                </a>
                <a href="#fase-03"
                   @click="activePhase = 'f03'"
                   :class="activePhase === 'f03' ? 'bg-wc-accent text-white border-wc-accent' : 'bg-wc-bg border-wc-border hover:border-wc-accent/40'"
                   class="flex shrink-0 items-center gap-3 rounded-full border px-5 py-3 transition-all duration-300 scroll-smooth">
                    <span class="font-data text-xs font-bold" :class="activePhase === 'f03' ? 'text-white' : 'text-wc-accent'">F03</span>
                    <div>
                        <p class="text-sm font-semibold" :class="activePhase === 'f03' ? 'text-white' : 'text-wc-text'">{{ __('proceso.nav.f03_name') }}</p>
                        <p class="text-xs" :class="activePhase === 'f03' ? 'text-white/70' : 'text-wc-text-tertiary'">{{ __('proceso.nav.f03_range') }}</p>
                    </div>
                </a>
                <a href="#fase-04"
                   @click="activePhase = 'f04'"
                   :class="activePhase === 'f04' ? 'bg-wc-accent text-white border-wc-accent' : 'bg-wc-bg border-wc-border hover:border-wc-accent/40'"
                   class="flex shrink-0 items-center gap-3 rounded-full border px-5 py-3 transition-all duration-300 scroll-smooth">
                    <span class="font-data text-xs font-bold" :class="activePhase === 'f04' ? 'text-white' : 'text-wc-accent'">F04</span>
                    <div>
                        <p class="text-sm font-semibold" :class="activePhase === 'f04' ? 'text-white' : 'text-wc-text'">{{ __('proceso.nav.f04_name') }}</p>
                        <p class="text-xs" :class="activePhase === 'f04' ? 'text-white/70' : 'text-wc-text-tertiary'">{{ __('proceso.nav.f04_range') }}</p>
                    </div>
                </a>
            </div>
        </div>
    </section>

    <div class="section-divider"></div>

    {{-- Vertical Timeline Wrapper for all 4 phases --}}
    <div class="relative">
        {{-- Timeline vertical line (lg only) --}}
        <div class="absolute left-10 top-0 bottom-0 hidden w-px bg-wc-border lg:block" aria-hidden="true">
            <div class="line-draw-scroll h-full w-full bg-wc-accent/40"></div>
        </div>

        {{-- FASE 01: Diagnostico --}}
        <section id="fase-01" class="scroll-reveal relative bg-wc-bg scroll-mt-20" data-animate="slideInRight">
            {{-- Timeline node --}}
            <div class="absolute left-4 top-20 hidden lg:flex" aria-hidden="true">
                <div class="flex h-12 w-12 items-center justify-center rounded-full border-2 border-wc-accent bg-wc-bg-tertiary shadow-lg shadow-wc-accent/20">
                    <span class="font-data text-sm font-bold text-wc-accent">01</span>
                </div>
            </div>

            <div class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8 lg:pl-24">
                <div class="mb-4 flex items-center justify-center gap-3" data-animate="fadeInUp">
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-wc-accent text-white font-data font-bold text-sm lg:hidden">01</div>
                    <span class="font-data text-sm font-semibold text-wc-accent">{{ __('proceso.fase01.label') }}</span>
                    <span class="text-xs font-medium text-wc-text-tertiary">{{ __('proceso.fase01.range') }}</span>
                </div>
                <h2 class="text-center font-display text-3xl tracking-wide text-wc-text sm:text-4xl">{{ __('proceso.fase01.title') }}</h2>
                <p class="mt-2 text-center text-lg text-wc-text-secondary">{{ __('proceso.fase01.subtitle') }}</p>

                <div class="mt-8 grid grid-cols-1 gap-8 lg:grid-cols-5">
                    {{-- Main Content --}}
                    <div class="lg:col-span-3">
                        <p class="text-sm leading-relaxed text-wc-text-secondary">
                            {{ __('proceso.fase01.description') }}
                        </p>

                        <div class="mt-8 flex flex-wrap gap-3">
                            <div class="card-hover-lift rounded-lg border border-wc-accent/20 bg-wc-accent/5 px-4 py-2 transition-transform hover:scale-105">
                                <span class="font-data text-sm font-bold text-wc-accent">48h</span>
                                <span class="ml-1 text-xs text-wc-text-secondary">{{ __('proceso.fase01.badge_delivery') }}</span>
                            </div>
                            <div class="card-hover-lift rounded-lg border border-wc-accent/20 bg-wc-accent/5 px-4 py-2 transition-transform hover:scale-105">
                                <span class="font-data text-sm font-bold text-wc-accent">1:1</span>
                                <span class="ml-1 text-xs text-wc-text-secondary">{{ __('proceso.fase01.badge_interview') }}</span>
                            </div>
                        </div>

                        <h3 class="mt-10 text-center text-sm font-semibold uppercase tracking-wider text-wc-accent">{{ __('proceso.fase01.checklist_title') }}</h3>
                        <ul class="mt-4 space-y-3">
                            @foreach (__('proceso.fase01.checklist') as $i => $item)
                            <li class="flex items-start gap-3 text-sm text-wc-text-secondary transition-transform hover:scale-[1.01]" style="animation-delay: {{ ($i + 1) * 0.05 }}s">
                                <svg class="mt-0.5 h-4 w-4 shrink-0 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                                {{ $item }}
                            </li>
                            @endforeach
                        </ul>
                    </div>

                    {{-- Sidebar: What you receive --}}
                    <div class="lg:col-span-2">
                        <div class="card-hover-lift rounded-xl border border-wc-border bg-wc-bg-tertiary p-6 transition-all duration-300 hover:border-wc-accent/30">
                            <h3 class="text-center text-sm font-semibold uppercase tracking-wider text-wc-text">{{ __('proceso.fase01.sidebar_title') }}</h3>
                            <p class="mt-1 text-center text-xs text-wc-text-tertiary">{{ __('proceso.fase01.sidebar_subtitle') }}</p>
                            <ul class="mt-5 space-y-3">
                                @foreach (__('proceso.fase01.sidebar_items') as $i => $item)
                                <li class="flex items-start gap-3 text-sm text-wc-text-secondary" style="animation-delay: {{ ($i + 1) * 0.05 }}s">
                                    <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded bg-wc-accent/10 text-xs font-bold text-wc-accent transition-transform hover:scale-110">{{ $i + 1 }}</span>
                                    {{ $item }}
                                </li>
                                @endforeach
                            </ul>
                        </div>
                        <p class="mt-4 text-xs text-wc-text-tertiary">
                            {{ __('proceso.fase01.sidebar_footnote') }}
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <div class="section-divider"></div>

        {{-- FASE 02: Diseno --}}
        <section id="fase-02" class="scroll-reveal relative bg-wc-bg-tertiary scroll-mt-20" data-animate="slideInRight">
            {{-- Timeline node --}}
            <div class="absolute left-4 top-20 hidden lg:flex" aria-hidden="true">
                <div class="flex h-12 w-12 items-center justify-center rounded-full border-2 border-wc-accent bg-wc-bg shadow-lg shadow-wc-accent/20">
                    <span class="font-data text-sm font-bold text-wc-accent">02</span>
                </div>
            </div>

            <div class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8 lg:pl-24">
                <div class="mb-4 flex items-center justify-center gap-3" data-animate="fadeInUp">
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-wc-accent text-white font-data font-bold text-sm lg:hidden">02</div>
                    <span class="font-data text-sm font-semibold text-wc-accent">{{ __('proceso.fase02.label') }}</span>
                    <span class="text-xs font-medium text-wc-text-tertiary">{{ __('proceso.fase02.range') }}</span>
                </div>
                <h2 class="text-center font-display text-3xl tracking-wide text-wc-text sm:text-4xl">{{ __('proceso.fase02.title') }}</h2>
                <p class="mt-2 text-center text-lg text-wc-text-secondary">{{ __('proceso.fase02.subtitle') }}</p>

                <div class="mt-8 grid grid-cols-1 gap-8 lg:grid-cols-5">
                    <div class="lg:col-span-3">
                        <p class="text-sm leading-relaxed text-wc-text-secondary">
                            {{ __('proceso.fase02.description') }}
                        </p>

                        <div class="mt-8 flex flex-wrap gap-3">
                            <div class="card-hover-lift rounded-lg border border-wc-accent/20 bg-wc-accent/5 px-4 py-2 transition-transform hover:scale-105">
                                <span class="font-data text-sm font-bold text-wc-accent">PDF</span>
                                <span class="ml-1 text-xs text-wc-text-secondary">{{ __('proceso.fase02.badge_pdf') }}</span>
                            </div>
                            <div class="card-hover-lift rounded-lg border border-wc-accent/20 bg-wc-accent/5 px-4 py-2 transition-transform hover:scale-105">
                                <span class="font-data text-sm font-bold text-wc-accent">100%</span>
                                <span class="ml-1 text-xs text-wc-text-secondary">{{ __('proceso.fase02.badge_custom') }}</span>
                            </div>
                        </div>

                        <h3 class="mt-10 text-center text-sm font-semibold uppercase tracking-wider text-wc-accent">{{ __('proceso.fase02.checklist_title') }}</h3>
                        <ul class="mt-4 space-y-3">
                            @foreach (__('proceso.fase02.checklist') as $i => $item)
                            <li class="flex items-start gap-3 text-sm text-wc-text-secondary transition-transform hover:scale-[1.01]" style="animation-delay: {{ ($i + 1) * 0.05 }}s">
                                <svg class="mt-0.5 h-4 w-4 shrink-0 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                                {{ $item }}
                            </li>
                            @endforeach
                        </ul>
                    </div>

                    <div class="lg:col-span-2">
                        <div class="card-hover-lift rounded-xl border border-wc-border bg-wc-bg p-6 transition-all duration-300 hover:border-wc-accent/30">
                            <h3 class="text-center text-sm font-semibold uppercase tracking-wider text-wc-text">{{ __('proceso.fase02.sidebar_title') }}</h3>
                            <p class="mt-1 text-center text-xs text-wc-text-tertiary">{{ __('proceso.fase02.sidebar_subtitle') }}</p>
                            <div class="mt-5 space-y-4">
                                <div class="card-hover-lift rounded-xl border border-wc-border bg-wc-bg-tertiary p-4 transition-all duration-300 hover:border-wc-accent/20">
                                    <p class="text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">{{ __('proceso.fase02.plan_inicial_name') }}</p>
                                    <p class="mt-1 text-sm text-wc-text-secondary">{{ __('proceso.fase02.plan_inicial_desc') }}</p>
                                </div>
                                <div class="card-hover-lift rounded-xl border border-wc-border bg-wc-bg-tertiary p-4 transition-all duration-300 hover:border-wc-accent/20">
                                    <p class="text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">{{ __('proceso.fase02.plan_base_name') }}</p>
                                    <p class="mt-1 text-sm text-wc-text-secondary">{{ __('proceso.fase02.plan_base_desc') }}</p>
                                </div>
                                <div class="card-hover-lift rounded-xl border border-wc-accent/30 bg-wc-accent/5 p-4 transition-all duration-300 hover:border-wc-accent/60 hover:bg-wc-accent/10">
                                    <p class="text-xs font-semibold uppercase tracking-wider text-wc-accent">{{ __('proceso.fase02.plan_elite_name') }}</p>
                                    <p class="mt-1 text-sm text-wc-text-secondary">{{ __('proceso.fase02.plan_elite_desc') }}</p>
                                </div>
                            </div>
                        </div>
                        <p class="mt-4 text-xs text-wc-text-tertiary">
                            {{ __('proceso.fase02.sidebar_footnote') }}
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <div class="section-divider"></div>

        {{-- FASE 03: Ejecucion --}}
        <section id="fase-03" class="scroll-reveal relative bg-wc-bg scroll-mt-20" data-animate="slideInRight">
            {{-- Timeline node --}}
            <div class="absolute left-4 top-20 hidden lg:flex" aria-hidden="true">
                <div class="flex h-12 w-12 items-center justify-center rounded-full border-2 border-wc-accent bg-wc-bg-tertiary shadow-lg shadow-wc-accent/20">
                    <span class="font-data text-sm font-bold text-wc-accent">03</span>
                </div>
            </div>

            <div class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8 lg:pl-24">
                <div class="mb-4 flex items-center justify-center gap-3" data-animate="fadeInUp">
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-wc-accent text-white font-data font-bold text-sm lg:hidden">03</div>
                    <span class="font-data text-sm font-semibold text-wc-accent">{{ __('proceso.fase03.label') }}</span>
                    <span class="text-xs font-medium text-wc-text-tertiary">{{ __('proceso.fase03.range') }}</span>
                </div>
                <h2 class="text-center font-display text-3xl tracking-wide text-wc-text sm:text-4xl">{{ __('proceso.fase03.title') }}</h2>
                <p class="mt-2 text-center text-lg text-wc-text-secondary">{{ __('proceso.fase03.subtitle') }}</p>

                <div class="mt-8">
                    <p class="mx-auto max-w-3xl text-center text-sm leading-relaxed text-wc-text-secondary">
                        {{ __('proceso.fase03.description') }}
                    </p>

                    <div class="mt-8 flex flex-wrap justify-center gap-3">
                        <div class="card-hover-lift rounded-lg border border-wc-accent/20 bg-wc-accent/5 px-4 py-2 transition-transform hover:scale-105">
                            <span class="font-data text-sm font-bold text-wc-accent">7 dias/sem</span>
                            <span class="ml-1 text-xs text-wc-text-secondary">{{ __('proceso.fase03.badge_tracking') }}</span>
                        </div>
                        <div class="card-hover-lift rounded-lg border border-wc-accent/20 bg-wc-accent/5 px-4 py-2 transition-transform hover:scale-105">
                            <span class="font-data text-sm font-bold text-wc-accent">WA directo</span>
                            <span class="ml-1 text-xs text-wc-text-secondary">{{ __('proceso.fase03.badge_wa') }}</span>
                        </div>
                    </div>
                </div>

                {{-- Weekly Cycle --}}
                <div class="mt-14">
                    <h3 class="text-center text-sm font-semibold uppercase tracking-wider text-wc-accent">{{ __('proceso.fase03.weekly_cycle_title') }}</h3>
                    <div class="stagger-grid mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        @foreach (['cycle_step1','cycle_step2','cycle_step3','cycle_step4'] as $n => $step)
                        <div class="card-hover-lift rounded-xl border border-wc-border bg-wc-bg-tertiary p-5 text-center transition-all duration-300 hover:border-wc-accent/30">
                            <div class="mx-auto flex h-10 w-10 items-center justify-center rounded-lg bg-wc-accent/10 transition-transform hover:scale-110">
                                <span class="font-data text-sm font-bold text-wc-accent">{{ $n + 1 }}</span>
                            </div>
                            <p class="mt-3 text-sm font-medium text-wc-text">{{ __('proceso.fase03.'.$step) }}</p>
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- What's included + How it works --}}
                <div class="mt-14 grid grid-cols-1 gap-8 lg:grid-cols-2">
                    <div>
                        <h3 class="text-center text-sm font-semibold uppercase tracking-wider text-wc-accent">{{ __('proceso.fase03.includes_title') }}</h3>
                        <ul class="mt-4 space-y-3">
                            @foreach (__('proceso.fase03.includes') as $item)
                            <li class="flex items-start gap-3 text-sm text-wc-text-secondary transition-transform hover:scale-[1.01]">
                                <svg class="mt-0.5 h-4 w-4 shrink-0 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                                {{ $item }}
                            </li>
                            @endforeach
                        </ul>
                    </div>

                    <div class="card-hover-lift rounded-xl border border-wc-border bg-wc-bg-tertiary p-6 transition-all duration-300 hover:border-wc-accent/30">
                        <h3 class="text-center text-sm font-semibold uppercase tracking-wider text-wc-text">{{ __('proceso.fase03.how_it_works_title') }}</h3>
                        <ol class="mt-4 space-y-3">
                            @foreach (__('proceso.fase03.how_it_works') as $i => $step)
                            <li class="flex items-start gap-3 text-sm text-wc-text-secondary">
                                <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded bg-wc-accent/10 text-xs font-bold text-wc-accent transition-transform hover:scale-110">{{ $i + 1 }}</span>
                                {{ $step }}
                            </li>
                            @endforeach
                        </ol>
                    </div>
                </div>

                <div class="mt-8 rounded-xl border border-wc-accent/20 bg-wc-accent/5 p-6">
                    <p class="text-sm font-medium text-wc-text">
                        {{ __('proceso.fase03.highlight') }}
                    </p>
                </div>
            </div>
        </section>

        <div class="section-divider"></div>

        {{-- FASE 04: Resultados --}}
        <section id="fase-04" class="scroll-reveal relative bg-wc-bg-tertiary scroll-mt-20" data-animate="slideInRight">
            {{-- Timeline node --}}
            <div class="absolute left-4 top-20 hidden lg:flex" aria-hidden="true">
                <div class="flex h-12 w-12 items-center justify-center rounded-full border-2 border-wc-accent bg-wc-bg shadow-lg shadow-wc-accent/20">
                    <span class="font-data text-sm font-bold text-wc-accent">04</span>
                </div>
            </div>

            <div class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8 lg:pl-24">
                <div class="mb-4 flex items-center justify-center gap-3" data-animate="fadeInUp">
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-wc-accent text-white font-data font-bold text-sm lg:hidden">04</div>
                    <span class="font-data text-sm font-semibold text-wc-accent">{{ __('proceso.fase04.label') }}</span>
                    <span class="text-xs font-medium text-wc-text-tertiary">{{ __('proceso.fase04.range') }}</span>
                </div>
                <h2 class="text-center font-display text-3xl tracking-wide text-wc-text sm:text-4xl">{{ __('proceso.fase04.title') }}</h2>
                <p class="mt-2 text-center text-lg text-wc-text-secondary">{{ __('proceso.fase04.subtitle') }}</p>

                <p class="mx-auto mt-8 max-w-3xl text-center text-sm leading-relaxed text-wc-text-secondary">
                    {{ __('proceso.fase04.description') }}
                </p>

                {{-- Results Stats --}}
                <div class="stagger-grid mt-10 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div class="card-hover-lift rounded-xl border border-wc-accent/30 bg-wc-accent/5 p-5 text-center transition-all duration-300 hover:border-wc-accent/60 hover:bg-wc-accent/10">
                        <p class="font-data text-2xl font-bold text-wc-accent">4-8kg</p>
                        <p class="mt-1 text-xs text-wc-text-secondary">{{ __('proceso.fase04.stat1_label') }}</p>
                    </div>
                    <div class="card-hover-lift rounded-xl border border-wc-accent/30 bg-wc-accent/5 p-5 text-center transition-all duration-300 hover:border-wc-accent/60 hover:bg-wc-accent/10">
                        <p class="font-data text-2xl font-bold text-wc-accent">-4%</p>
                        <p class="mt-1 text-xs text-wc-text-secondary">{{ __('proceso.fase04.stat2_label') }}</p>
                    </div>
                    <div class="card-hover-lift rounded-xl border border-wc-accent/30 bg-wc-accent/5 p-5 text-center transition-all duration-300 hover:border-wc-accent/60 hover:bg-wc-accent/10">
                        <p class="counter-highlight font-data text-2xl font-bold text-wc-accent" data-counter="87" data-counter-suffix="%">87%</p>
                        <p class="mt-1 text-xs text-wc-text-secondary">{{ __('proceso.fase04.stat3_label') }}</p>
                    </div>
                    <div class="card-hover-lift rounded-xl border border-wc-accent/30 bg-wc-accent/5 p-5 text-center transition-all duration-300 hover:border-wc-accent/60 hover:bg-wc-accent/10">
                        <p class="counter-highlight font-data text-2xl font-bold text-wc-accent" data-counter="74" data-counter-suffix="%">74%</p>
                        <p class="mt-1 text-xs text-wc-text-secondary">{{ __('proceso.fase04.stat4_label') }}</p>
                    </div>
                </div>

                <div class="mt-14 grid grid-cols-1 gap-8 lg:grid-cols-2">
                    <div>
                        <h3 class="text-center text-sm font-semibold uppercase tracking-wider text-wc-accent">{{ __('proceso.fase04.eval_title') }}</h3>
                        <ul class="mt-4 space-y-3">
                            @foreach (__('proceso.fase04.eval') as $item)
                            <li class="flex items-start gap-3 text-sm text-wc-text-secondary transition-transform hover:scale-[1.01]">
                                <svg class="mt-0.5 h-4 w-4 shrink-0 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                                {{ $item }}
                            </li>
                            @endforeach
                        </ul>
                    </div>

                    <div class="card-hover-lift rounded-xl border border-wc-border bg-wc-bg p-6 transition-all duration-300 hover:border-wc-accent/30">
                        <h3 class="text-center text-sm font-semibold uppercase tracking-wider text-wc-text">{{ __('proceso.fase04.next_title') }}</h3>
                        <ul class="mt-4 space-y-3">
                            @foreach (__('proceso.fase04.next') as $item)
                            <li class="flex items-start gap-3 text-sm text-wc-text-secondary transition-transform hover:scale-[1.01]">
                                <svg class="mt-0.5 h-4 w-4 shrink-0 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" /></svg>
                                {{ $item }}
                            </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </section>

    </div>{{-- end vertical timeline wrapper --}}

    <div class="section-divider"></div>

    {{-- FAQ --}}
    <section class="scroll-reveal bg-wc-bg" x-data="{ active: null }">
        <div class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8" data-animate="fadeInUp">
            <h2 class="text-center font-display text-3xl tracking-wide text-wc-text sm:text-4xl">{{ __('proceso.faq.title') }}</h2>
            <p class="mt-2 text-center text-lg text-wc-text-secondary">{{ __('proceso.faq.subtitle') }}</p>

            <div class="mx-auto mt-12 max-w-3xl divide-y divide-wc-border">
                <div>
                    <button x-on:click="active = active === 1 ? null : 1" class="flex w-full items-center justify-between py-5 text-left transition-colors hover:text-wc-accent">
                        <span class="text-sm font-semibold text-wc-text">{{ __('proceso.faq.q1.question') }}</span>
                        <svg class="h-5 w-5 shrink-0 text-wc-text-tertiary transition-transform duration-300" :class="{ 'rotate-180': active === 1 }" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>
                    </button>
                    <div x-show="active === 1" x-collapse x-cloak>
                        <div class="pb-5">
                            <p class="text-sm leading-relaxed text-wc-text-secondary">{{ __('proceso.faq.q1.answer') }}</p>
                        </div>
                    </div>
                </div>

                <div>
                    <button x-on:click="active = active === 2 ? null : 2" class="flex w-full items-center justify-between py-5 text-left transition-colors hover:text-wc-accent">
                        <span class="text-sm font-semibold text-wc-text">{{ __('proceso.faq.q2.question') }}</span>
                        <svg class="h-5 w-5 shrink-0 text-wc-text-tertiary transition-transform duration-300" :class="{ 'rotate-180': active === 2 }" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>
                    </button>
                    <div x-show="active === 2" x-collapse x-cloak>
                        <div class="pb-5">
                            <p class="text-sm leading-relaxed text-wc-text-secondary">{{ __('proceso.faq.q2.answer') }}</p>
                        </div>
                    </div>
                </div>

                <div>
                    <button x-on:click="active = active === 3 ? null : 3" class="flex w-full items-center justify-between py-5 text-left transition-colors hover:text-wc-accent">
                        <span class="text-sm font-semibold text-wc-text">{{ __('proceso.faq.q3.question') }}</span>
                        <svg class="h-5 w-5 shrink-0 text-wc-text-tertiary transition-transform duration-300" :class="{ 'rotate-180': active === 3 }" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>
                    </button>
                    <div x-show="active === 3" x-collapse x-cloak>
                        <div class="pb-5">
                            <p class="text-sm leading-relaxed text-wc-text-secondary">{{ __('proceso.faq.q3.answer') }}</p>
                        </div>
                    </div>
                </div>

                <div>
                    <button x-on:click="active = active === 4 ? null : 4" class="flex w-full items-center justify-between py-5 text-left transition-colors hover:text-wc-accent">
                        <span class="text-sm font-semibold text-wc-text">{{ __('proceso.faq.q4.question') }}</span>
                        <svg class="h-5 w-5 shrink-0 text-wc-text-tertiary transition-transform duration-300" :class="{ 'rotate-180': active === 4 }" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>
                    </button>
                    <div x-show="active === 4" x-collapse x-cloak>
                        <div class="pb-5">
                            <p class="text-sm leading-relaxed text-wc-text-secondary">{{ __('proceso.faq.q4.answer') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="section-divider"></div>

    {{-- Final CTA --}}
    <section class="bg-wc-bg-tertiary">
        <div class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8" data-animate="fadeInUp">
            <div class="relative overflow-hidden rounded-2xl border border-wc-border bg-wc-bg p-10 sm:p-16">
                <div class="absolute inset-0 bg-gradient-to-br from-wc-accent/10 via-wc-accent/5 to-transparent"></div>
                <div class="absolute -top-24 -right-24 h-64 w-64 rounded-full bg-wc-accent/5 blur-3xl" aria-hidden="true"></div>
                <div class="absolute -bottom-16 -left-16 h-48 w-48 rounded-full bg-wc-accent/5 blur-2xl" aria-hidden="true"></div>
                <div class="relative text-center">
                    <p class="text-sm font-semibold uppercase tracking-widest text-wc-accent">{{ __('proceso.cta.label') }}</p>
                    <h2 class="mt-4 font-display text-3xl tracking-wide text-wc-text sm:text-5xl">{{ __('proceso.cta.title') }}</h2>
                    <p class="mx-auto mt-6 max-w-lg text-wc-text-secondary">
                        {{ __('proceso.cta.description') }}
                    </p>
                    <div class="mt-8 flex flex-col items-center justify-center gap-4 sm:flex-row">
                        <a href="{{ route('planes') }}" class="btn-press pulse-glow inline-flex items-center justify-center rounded-full bg-wc-accent px-8 py-3.5 font-semibold text-white shadow-lg shadow-wc-accent/20 transition-all duration-200 hover:bg-wc-accent-hover hover:shadow-xl hover:shadow-wc-accent/30">
                            {{ __('proceso.cta.btn_primary') }}
                        </a>
                        <a href="{{ route('planes') }}" class="btn-press inline-flex items-center justify-center rounded-full border border-wc-border px-8 py-3.5 font-semibold text-wc-text transition-all duration-200 hover:border-wc-accent/40 hover:bg-wc-bg-secondary">
                            {{ __('proceso.cta.btn_secondary') }}
                        </a>
                    </div>
                    <p class="mt-6 text-center text-xs text-wc-text-tertiary">{{ __('proceso.cta.footnote') }}</p>
                </div>
            </div>
        </div>
    </section>

</x-layouts.public>
