<div class="space-y-6">

    {{-- ITEM 1: Plan-Specific Onboarding (Livewire component) --}}
    <div x-data="{ showOnboarding: !localStorage.getItem('wc_onboarding_plan_done') }"
         x-init="if (showOnboarding) { localStorage.setItem('wc_onboarding_plan_done', '1'); }"
         x-cloak>
        <livewire:client.plan-onboarding />
    </div>

    {{-- Legacy Onboarding (replaced by PlanOnboarding — kept as fallback) --}}
    <div x-data="{
            showOnboarding: false,
            currentSlide: 0,
            totalSlides: 3,
            next() { if (this.currentSlide < this.totalSlides - 1) this.currentSlide++ },
            prev() { if (this.currentSlide > 0) this.currentSlide-- },
            finish() {
                localStorage.setItem('wc_onboarding_done', '1');
                this.showOnboarding = false;
            }
         }"
         x-cloak>

        {{-- Overlay --}}
        <template x-if="showOnboarding">
            <div class="fixed inset-0 z-[60] flex items-center justify-center bg-black/60 backdrop-blur-sm p-4"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0">

                {{-- Modal --}}
                <div class="relative w-full max-w-lg rounded-2xl border border-wc-border bg-wc-bg-secondary p-6 sm:p-8 shadow-2xl"
                     @click.away="finish()">

                    {{-- Close button --}}
                    <button @click="finish()" class="absolute top-4 right-4 text-wc-text-tertiary hover:text-wc-text transition-colors">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>

                    {{-- Slide 1: Bienvenido --}}
                    <div x-show="currentSlide === 0" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0">
                        <div class="flex flex-col items-center text-center">
                            <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-wc-accent/10 mb-5">
                                <svg class="h-8 w-8 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 0 0-2.456 2.456Z" />
                                </svg>
                            </div>
                            <h2 class="font-display text-2xl tracking-wide text-wc-text">{{ __('client_dashboard.onboarding_welcome_title') }}</h2>
                            <p class="mt-3 text-sm text-wc-text-tertiary leading-relaxed max-w-sm">
                                {{ __('client_dashboard.onboarding_welcome_desc') }}
                            </p>
                        </div>
                    </div>

                    {{-- Slide 2: Tu Dashboard --}}
                    <div x-show="currentSlide === 1" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0">
                        <div class="flex flex-col items-center text-center">
                            <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-violet-500/10 mb-5">
                                <svg class="h-8 w-8 text-violet-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6Z" />
                                </svg>
                            </div>
                            <h2 class="font-display text-2xl tracking-wide text-wc-text">{{ __('client_dashboard.onboarding_dashboard_title') }}</h2>
                            <p class="mt-3 text-sm text-wc-text-tertiary leading-relaxed max-w-sm">
                                {!! __('client_dashboard.onboarding_dashboard_text', ['missions' => __('client_dashboard.onboarding_dashboard_desc_1'), 'training' => __('client_dashboard.onboarding_dashboard_desc_2')]) !!}
                            </p>
                        </div>
                    </div>

                    {{-- Slide 3: Primeros Pasos --}}
                    <div x-show="currentSlide === 2" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0">
                        <div class="flex flex-col items-center text-center">
                            <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-emerald-500/10 mb-5">
                                <svg class="h-8 w-8 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.59 14.37a6 6 0 0 1-5.84 7.38v-4.8m5.84-2.58a14.98 14.98 0 0 0 6.16-12.12A14.98 14.98 0 0 0 9.631 8.41m5.96 5.96a14.926 14.926 0 0 1-5.841 2.58m-.119-8.54a6 6 0 0 0-7.381 5.84h4.8m2.581-5.84a14.927 14.927 0 0 0-2.58 5.84m2.699 2.7c-.103.021-.207.041-.311.06a15.09 15.09 0 0 1-2.448-2.448 14.9 14.9 0 0 1 .06-.312m-2.24 2.39a4.493 4.493 0 0 0-1.757 4.306 4.493 4.493 0 0 0 4.306-1.758M16.5 9a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Z" />
                                </svg>
                            </div>
                            <h2 class="font-display text-2xl tracking-wide text-wc-text">{{ __('client_dashboard.onboarding_steps_title') }}</h2>
                            <div class="mt-4 space-y-3 text-left w-full max-w-xs">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-wc-accent/10">
                                        <span class="text-xs font-bold text-wc-accent">1</span>
                                    </div>
                                    <span class="text-sm text-wc-text">{{ __('client_dashboard.onboarding_step_1') }}</span>
                                </div>
                                <div class="flex items-center gap-3">
                                    <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-wc-accent/10">
                                        <span class="text-xs font-bold text-wc-accent">2</span>
                                    </div>
                                    <span class="text-sm text-wc-text">{{ __('client_dashboard.onboarding_step_2') }}</span>
                                </div>
                                <div class="flex items-center gap-3">
                                    <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-wc-accent/10">
                                        <span class="text-xs font-bold text-wc-accent">3</span>
                                    </div>
                                    <span class="text-sm text-wc-text">{{ __('client_dashboard.onboarding_step_3') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Navigation dots + buttons --}}
                    <div class="mt-8 flex items-center justify-between">
                        <button @click="prev()" x-show="currentSlide > 0"
                                class="inline-flex items-center gap-1.5 rounded-lg border border-wc-border bg-wc-bg-tertiary px-4 py-2 text-sm font-medium text-wc-text hover:bg-wc-bg-secondary transition-colors">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                            </svg>
                            {{ __('client_dashboard.onboarding_prev') }}
                        </button>
                        <div x-show="currentSlide === 0" class="w-20"></div>

                        {{-- Dots --}}
                        <div class="flex items-center gap-2">
                            <template x-for="i in totalSlides" :key="i">
                                <button @click="currentSlide = i - 1"
                                        :class="currentSlide === i - 1 ? 'bg-wc-accent w-6' : 'bg-wc-border w-2'"
                                        class="h-2 rounded-full transition-all duration-300"></button>
                            </template>
                        </div>

                        <template x-if="currentSlide < totalSlides - 1">
                            <button @click="next()"
                                    class="inline-flex items-center gap-1.5 rounded-lg bg-wc-accent px-4 py-2 text-sm font-medium text-white hover:bg-wc-accent-hover transition-colors shadow-lg shadow-wc-accent/20 btn-press">
                                {{ __('client_dashboard.onboarding_next') }}
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                                </svg>
                            </button>
                        </template>
                        <template x-if="currentSlide === totalSlides - 1">
                            <button @click="finish()"
                                    class="inline-flex items-center gap-1.5 rounded-full bg-wc-accent px-6 py-2 text-sm font-medium text-white hover:bg-wc-accent-hover transition-colors shadow-lg shadow-wc-accent/20 btn-press">
                                {{ __('client_dashboard.onboarding_start') }}
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.59 14.37a6 6 0 0 1-5.84 7.38v-4.8m5.84-2.58a14.98 14.98 0 0 0 6.16-12.12A14.98 14.98 0 0 0 9.631 8.41m5.96 5.96a14.926 14.926 0 0 1-5.841 2.58m-.119-8.54a6 6 0 0 0-7.381 5.84h4.8m2.581-5.84a14.927 14.927 0 0 0-2.58 5.84m2.699 2.7c-.103.021-.207.041-.311.06a15.09 15.09 0 0 1-2.448-2.448 14.9 14.9 0 0 1 .06-.312m-2.24 2.39a4.493 4.493 0 0 0-1.757 4.306 4.493 4.493 0 0 0 4.306-1.758M16.5 9a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Z" />
                                </svg>
                            </button>
                        </template>
                    </div>
                </div>
            </div>
        </template>
    </div>

    {{-- Greeting section --}}
    <div class="relative flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between overflow-hidden">
        <div class="wc-orb-tr" aria-hidden="true"></div>
        <div>
            <h1
                x-data="{
                    g: '',
                    init() {
                        const h = new Date().getHours();
                        this.g = h < 12 ? 'Buenos días' : h < 18 ? 'Buenas tardes' : 'Buenas noches';
                    }
                }"
                x-text="g + ', {{ $clientName }}'"
                class="font-display text-3xl tracking-wide text-wc-text sm:text-4xl"
            >{{ $greeting }}, {{ $clientName }}</h1>
            @if($planLabel)
                <div class="mt-2 flex items-center gap-2">
                    <span class="inline-flex rounded-full bg-wc-accent/10 px-3 py-1 text-xs font-semibold text-wc-accent">
                        {{ __('client_dashboard.plan_label', ['plan' => $planLabel]) }}
                    </span>
                </div>
            @endif
        </div>

        {{-- Quick actions (desktop) --}}
        <div class="hidden sm:flex items-center gap-2">
            <a href="{{ route('client.plan') }}"
               class="inline-flex items-center gap-2 rounded-lg bg-wc-accent px-4 py-2 text-sm font-medium text-white hover:bg-wc-accent-hover transition-colors btn-ripple btn-press">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                {{ __('client_dashboard.log_workout') }}
            </a>
            <a href="{{ route('client.checkin') }}"
               class="inline-flex items-center gap-2 rounded-lg border border-wc-border bg-wc-bg-tertiary px-4 py-2 text-sm font-medium text-wc-text hover:bg-wc-bg-secondary transition-colors">
                {{ __('client_dashboard.do_checkin') }}
            </a>
        </div>
    </div>

    {{-- ITEM 4: Daily Motivational Quote --}}
    <div class="wc-topline flex items-start gap-3 rounded-xl border border-wc-border/50 bg-wc-bg-tertiary/50 px-4 py-3">
        <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-amber-500/10 mt-0.5">
            <svg class="h-4 w-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m3.75 13.5 10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75Z" />
            </svg>
        </div>
        <div class="min-w-0 flex-1">
            <p class="text-sm italic text-wc-text-tertiary leading-relaxed">
                &ldquo;{{ $dailyQuote }}&rdquo;
            </p>
            <p class="mt-1 text-[10px] font-medium uppercase tracking-wider text-wc-text-tertiary/60">— Tu coach</p>
        </div>
    </div>

    {{-- Plan alert --}}
    @if(!$hasActivePlan)
        <div class="flex items-start gap-4 rounded-xl border border-wc-accent/30 bg-wc-accent/5 p-4">
            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-wc-accent/10">
                <svg class="h-5 w-5 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-wc-text">{{ __('client_dashboard.no_active_plan') }}</p>
                <p class="mt-0.5 text-xs text-wc-text-tertiary">{{ __('client_dashboard.no_active_plan_desc') }}</p>
            </div>
            <a href="{{ route('client.chat') }}"
               class="shrink-0 inline-flex items-center gap-1.5 rounded-lg bg-wc-accent px-3 py-1.5 text-xs font-medium text-white hover:bg-wc-accent-hover transition-colors">
                {{ __('client_dashboard.contact_coach') }}
            </a>
        </div>
    @else
        <div class="wc-glass flex items-center gap-3 rounded-xl border border-wc-border bg-wc-bg-tertiary px-4 py-3">
            <div class="relative z-10 flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-emerald-500/10">
                <svg class="h-4 w-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
            </div>
            <span class="relative z-10 text-xs text-wc-text-tertiary">
                {{ __('client_dashboard.plan_label', ['plan' => '']) }}
                @if($planPhase) <span class="font-semibold capitalize text-wc-text">{{ $planPhase }}</span> @endif
                {{ __('client_dashboard.plan_active_label') }} &mdash; {{ __('client_dashboard.plan_active_day') }} <span class="font-semibold text-wc-text">{{ $planDaysActive }}</span>
            </span>
        </div>
    @endif

    {{-- Stats cards --}}
    {{-- Skeleton: Stats cards (shown during Livewire loading) --}}
    <div wire:loading.delay class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
        <x-skeleton :card="true" />
        <x-skeleton :card="true" />
        <x-skeleton :card="true" />
        <x-skeleton :card="true" />
    </div>
    <div wire:loading.remove class="relative wc-grain grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
        {{-- Streak with Flame Animation --}}
        <div class="relative z-10 rounded-card border border-wc-border bg-wc-bg-tertiary p-4 sm:p-5 card-hover-lift wc-lift stat-glow-red">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">{{ __('client_dashboard.streak') }}</span>
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-orange-500/10 {{ $streakDays >= 3 ? 'flame-active' : '' }}">
                    <svg class="h-4 w-4 text-orange-500" viewBox="0 0 24 24" fill="currentColor">
                        <path fill-rule="evenodd" d="M12.963 2.286a.75.75 0 0 0-1.071-.136 9.742 9.742 0 0 0-3.539 6.176A7.547 7.547 0 0 1 6.648 6.61a.75.75 0 0 0-1.152.082A9 9 0 1 0 15.68 4.534a7.46 7.46 0 0 1-2.717-2.248ZM15.75 14.25a3.75 3.75 0 1 1-7.313-1.172c.628.465 1.35.81 2.133 1a5.99 5.99 0 0 1 1.925-3.546 3.75 3.75 0 0 1 3.255 3.718Z" clip-rule="evenodd" />
                    </svg>
                </div>
            </div>
            <p class="mt-3 font-data text-3xl font-bold text-wc-text"><span data-counter="{{ $streakDays }}" class="counter-highlight">0</span></p>
            <p class="mt-0.5 text-xs text-wc-text-tertiary">{{ __('client_dashboard.consecutive_days') }}</p>
        </div>

        {{-- Check-ins this month --}}
        <div class="relative z-10 rounded-card border border-wc-border bg-wc-bg-tertiary p-4 sm:p-5 card-hover-lift wc-lift stat-glow-emerald">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">{{ __('client_dashboard.checkins') }}</span>
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-500/10">
                    <svg class="h-4 w-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </div>
            </div>
            <p class="mt-3 font-data text-3xl font-bold text-wc-text"><span data-counter="{{ $checkinsThisMonth }}" class="counter-highlight">0</span></p>
            <p class="mt-0.5 text-xs text-wc-text-tertiary">{{ __('client_dashboard.this_month') }}</p>
        </div>

        {{-- XP + Level --}}
        <div class="relative z-10 rounded-card border border-wc-border bg-wc-bg-tertiary p-4 sm:p-5 card-hover-lift wc-lift stat-glow-violet">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">{{ __('client_dashboard.level', ['level' => $level]) }}</span>
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-violet-500/10">
                    <svg class="h-4 w-4 text-violet-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z" />
                    </svg>
                </div>
            </div>
            <p class="mt-3 font-data text-3xl font-bold text-wc-text"><span data-counter="{{ $xpTotal }}" class="counter-highlight">0</span></p>
            <p class="mt-0.5 text-xs text-wc-text-tertiary">{{ __('client_dashboard.xp_total') }}</p>
            {{-- XP Progress bar --}}
            <div class="mt-3">
                <div class="h-1.5 w-full overflow-hidden rounded-full bg-wc-bg-secondary">
                    <div class="h-full rounded-full bg-violet-500 transition-all duration-500"
                         style="width: {{ $xpProgress }}%"></div>
                </div>
                <p class="mt-1 text-[10px] text-wc-text-tertiary">
                    {{ number_format($xpTotal % $xpForNextLevel) }} / {{ number_format($xpForNextLevel) }} XP
                </p>
            </div>
        </div>

        {{-- Days trained this week — Progress Ring --}}
        <div class="relative z-10 rounded-card border border-wc-border bg-wc-bg-tertiary p-4 sm:p-5 card-hover-lift wc-lift stat-glow-amber">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">{{ __('client_dashboard.this_week') }}</span>
            </div>
            <div class="mt-3 flex items-center gap-3">
                {{-- SVG Progress Ring ~60px --}}
                @php
                    $circumference = 251;
                    $progressOffset = $circumference - ($circumference * min($trainedThisWeek, 7) / 7);
                @endphp
                <svg width="60" height="60" viewBox="0 0 86 86" class="shrink-0">
                    {{-- Background track --}}
                    <circle cx="43" cy="43" r="40"
                            fill="none"
                            stroke="var(--color-wc-border)"
                            stroke-width="6" />
                    {{-- Animated progress arc --}}
                    <circle cx="43" cy="43" r="40"
                            fill="none"
                            stroke="#DC2626"
                            stroke-width="6"
                            stroke-linecap="round"
                            stroke-dasharray="{{ $circumference }}"
                            stroke-dashoffset="{{ $progressOffset }}"
                            class="progress-ring-circle" />
                    {{-- Center text --}}
                    <text x="43" y="43"
                          text-anchor="middle"
                          dominant-baseline="central"
                          fill="var(--color-wc-text)"
                          font-family="var(--font-data)"
                          font-size="18"
                          font-weight="700">{{ $trainedThisWeek }}/7</text>
                </svg>
                <div>
                    <p class="text-xs text-wc-text-tertiary">{{ __('client_dashboard.days_trained') }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- ITEM 5: Plan Progress Timeline --}}
    <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-5 card-hover-lift">
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-display text-lg tracking-wide text-wc-text">{{ __('client_dashboard.your_progress') }}</h2>
            <span class="text-xs text-wc-text-tertiary">{{ __('client_dashboard.week_x_of_y', ['current' => min($weeksActive, $totalWeeks), 'total' => $totalWeeks]) }}</span>
        </div>

        {{-- Progress bar with week markers --}}
        <div class="relative">
            {{-- Background bar --}}
            <div class="h-2.5 w-full overflow-hidden rounded-full bg-wc-bg-secondary">
                <div class="h-full rounded-full bg-gradient-to-r from-wc-accent to-red-400 transition-all duration-700 ease-out"
                     style="width: {{ $progressPercent }}%"></div>
            </div>

            {{-- Current position dot --}}
            <div class="absolute top-1/2 -translate-y-1/2 -translate-x-1/2 transition-all duration-700"
                 style="left: {{ $progressPercent }}%">
                <div class="h-5 w-5 rounded-full border-[3px] border-wc-accent bg-wc-bg-tertiary shadow-lg shadow-wc-accent/30"></div>
            </div>
        </div>

        {{-- Week markers --}}
        <div class="mt-3 flex items-center justify-between">
            <div class="text-left">
                <p class="text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary">{{ __('client_dashboard.start') }}</p>
                <p class="text-xs font-data text-wc-text">{{ $startDate }}</p>
            </div>
            <div class="hidden sm:flex items-center gap-0 flex-1 mx-4">
                @for($i = 1; $i <= $totalWeeks; $i++)
                    <div class="flex-1 flex flex-col items-center">
                        @if($i <= $weeksActive)
                            <div class="h-1.5 w-1.5 rounded-full bg-wc-accent/60"></div>
                        @else
                            <div class="h-1 w-1 rounded-full bg-wc-border"></div>
                        @endif
                        @if($i % 3 === 0)
                            <span class="mt-1 text-[9px] text-wc-text-tertiary">{{ $i }}</span>
                        @endif
                    </div>
                @endfor
            </div>
            <div class="text-right">
                <p class="text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary">{{ $weeksActive >= $totalWeeks ? __('client_dashboard.continuous') : __('client_dashboard.week_12') }}</p>
                <p class="text-xs font-data text-wc-text">{{ $progressPercent }}%</p>
            </div>
        </div>
    </div>

    {{-- Streak Calendar — 90-day GitHub-style heatmap --}}
    <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-4 sm:p-5 card-hover-lift">
        <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-2">
                <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-orange-500/10">
                    <svg class="h-4 w-4 text-orange-500" viewBox="0 0 24 24" fill="currentColor">
                        <path fill-rule="evenodd" d="M12.963 2.286a.75.75 0 0 0-1.071-.136 9.742 9.742 0 0 0-3.539 6.176A7.547 7.547 0 0 1 6.648 6.61a.75.75 0 0 0-1.152.082A9 9 0 1 0 15.68 4.534a7.46 7.46 0 0 1-2.717-2.248ZM15.75 14.25a3.75 3.75 0 1 1-7.313-1.172c.628.465 1.35.81 2.133 1a5.99 5.99 0 0 1 1.925-3.546 3.75 3.75 0 0 1 3.255 3.718Z" clip-rule="evenodd" />
                    </svg>
                </div>
                <h3 class="text-sm font-semibold text-wc-text">{{ __('client_dashboard.training_streak') }}</h3>
                @if($calendarStreak > 0)
                    <span class="inline-flex items-center gap-1 rounded-full bg-orange-500/10 px-2 py-0.5 text-xs font-bold text-orange-500">
                        {{ trans_choice('client_dashboard.days_in_row', $calendarStreak, ['count' => $calendarStreak]) }}
                    </span>
                @endif
            </div>
            <span class="text-xs text-wc-text-tertiary hidden sm:inline">{{ __('client_dashboard.last_90_days') }}</span>
        </div>

        {{-- Calendar grid --}}
        <div class="flex gap-0.5 overflow-x-auto pb-1">
            {{-- Day labels --}}
            <div class="flex flex-col gap-0.5 pr-1 shrink-0">
                <span class="h-2.5 w-4 text-[9px] leading-[10px] text-wc-text-tertiary sm:h-3 sm:text-[10px] sm:leading-3">L</span>
                <span class="h-2.5 w-4 sm:h-3">&nbsp;</span>
                <span class="h-2.5 w-4 text-[9px] leading-[10px] text-wc-text-tertiary sm:h-3 sm:text-[10px] sm:leading-3">M</span>
                <span class="h-2.5 w-4 sm:h-3">&nbsp;</span>
                <span class="h-2.5 w-4 text-[9px] leading-[10px] text-wc-text-tertiary sm:h-3 sm:text-[10px] sm:leading-3">V</span>
                <span class="h-2.5 w-4 sm:h-3">&nbsp;</span>
                <span class="h-2.5 w-4 text-[9px] leading-[10px] text-wc-text-tertiary sm:h-3 sm:text-[10px] sm:leading-3">D</span>
            </div>

            {{-- Grid: columns = weeks, rows = 7 days --}}
            <div class="grid grid-flow-col grid-rows-7 gap-0.5 flex-1">
                @php
                    $today = now();
                    // Start from 90 days ago, aligned to start of that week (Monday)
                    $startDate = $today->copy()->subDays(90)->startOfWeek(\Carbon\Carbon::MONDAY);
                    $endDate = $today->copy()->endOfWeek(\Carbon\Carbon::MONDAY);
                @endphp

                @for($date = $startDate->copy(); $date->lte($endDate); $date->addDay())
                    @php
                        $dateStr = $date->format('Y-m-d');
                        $count = $streakCalendar[$dateStr] ?? 0;
                        $isFuture = $date->gt($today);
                        $isBeforeRange = $date->lt($today->copy()->subDays(90));

                        if ($isFuture || $isBeforeRange) {
                            $colorClass = 'bg-wc-bg-secondary/30';
                        } elseif ($count >= 5) {
                            $colorClass = 'bg-wc-accent';
                        } elseif ($count >= 4) {
                            $colorClass = 'bg-wc-accent/80';
                        } elseif ($count >= 3) {
                            $colorClass = 'bg-wc-accent/60';
                        } elseif ($count === 2) {
                            $colorClass = 'bg-wc-accent/40';
                        } elseif ($count === 1) {
                            $colorClass = 'bg-wc-accent/20';
                        } else {
                            $colorClass = 'bg-wc-bg-secondary';
                        }

                        $isToday = $date->isSameDay($today);
                    @endphp
                    <div
                        class="h-2.5 w-2.5 rounded-[2px] {{ $colorClass }} sm:h-3 sm:w-3 sm:rounded-sm transition-all duration-150 hover:scale-125 hover:z-10 relative {{ $isToday ? 'ring-1 ring-wc-text/30' : '' }}"
                        title="{{ $date->translatedFormat('D j M Y') }}{{ $count ? ' — ' . $count . ' sesion(es)' : '' }}"
                        x-data="{ tooltip: false }"
                        @mouseenter="tooltip = true"
                        @mouseleave="tooltip = false"
                        @if($isFuture) style="opacity: 0.2" @endif
                    >
                        <template x-if="tooltip">
                            <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-1.5 z-20 pointer-events-none whitespace-nowrap rounded-md bg-wc-bg-secondary border border-wc-border px-2 py-1 text-[10px] text-wc-text shadow-lg">
                                {{ $date->format('d/m') }}{{ $count ? ' · ' . $count . 'x' : '' }}
                            </div>
                        </template>
                    </div>
                @endfor
            </div>
        </div>

        {{-- Legend + mobile label --}}
        <div class="mt-2 flex items-center justify-between">
            <span class="text-[10px] text-wc-text-tertiary sm:hidden">{{ __('client_dashboard.last_90_days') }}</span>
            <div class="flex items-center gap-1 text-[10px] text-wc-text-tertiary ml-auto">
                <span>{{ __('client_dashboard.less') }}</span>
                <div class="h-2 w-2 rounded-[2px] bg-wc-bg-secondary sm:h-2.5 sm:w-2.5 sm:rounded-sm"></div>
                <div class="h-2 w-2 rounded-[2px] bg-wc-accent/40 sm:h-2.5 sm:w-2.5 sm:rounded-sm"></div>
                <div class="h-2 w-2 rounded-[2px] bg-wc-accent/70 sm:h-2.5 sm:w-2.5 sm:rounded-sm"></div>
                <div class="h-2 w-2 rounded-[2px] bg-wc-accent sm:h-2.5 sm:w-2.5 sm:rounded-sm"></div>
                <span>{{ __('client_dashboard.more') }}</span>
            </div>
        </div>
    </div>

    {{-- Weight/Metrics Trend Chart --}}
    <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-5 card-hover-lift"
         x-data="{
             chart: null,
             init() {
                 const existing = this.chart || Chart.getChart(this.$refs.weightCanvas); if (existing) { existing.stop(); existing.destroy(); } this.chart = null;
                 const data = @js($weightChartData);
                 if (!data.length) return;
                 const datasets = [{
                     label: '{{ __('client_dashboard.weight_kg') }}',
                     data: data.map(d => d.weight),
                     borderColor: '#DC2626',
                     backgroundColor: 'rgba(220, 38, 38, 0.08)',
                     fill: true,
                     tension: 0.4,
                     pointRadius: 3,
                     pointHoverRadius: 6,
                     pointBackgroundColor: '#DC2626',
                     pointBorderColor: '#DC2626',
                     borderWidth: 2.5,
                     yAxisID: 'y',
                 }];
                 // Add body fat % if data exists
                 const hasBodyFat = data.some(d => d.bodyFat !== null);
                 if (hasBodyFat) {
                     datasets.push({
                         label: '{{ __('client_dashboard.body_fat_pct') }}',
                         data: data.map(d => d.bodyFat),
                         borderColor: '#8B5CF6',
                         backgroundColor: 'transparent',
                         borderDash: [5, 5],
                         tension: 0.4,
                         pointRadius: 2,
                         pointHoverRadius: 5,
                         pointBackgroundColor: '#8B5CF6',
                         pointBorderColor: '#8B5CF6',
                         borderWidth: 2,
                         yAxisID: 'y1',
                         spanGaps: true,
                     });
                 }
                 const ctx = this.$refs.weightCanvas.getContext('2d');
                 this.chart = new Chart(ctx, {
                     type: 'line',
                     data: {
                         labels: data.map(d => d.date),
                         datasets: datasets
                     },
                     options: {
                         responsive: true,
                         maintainAspectRatio: false,
                         interaction: { mode: 'index', intersect: false },
                         plugins: {
                             legend: {
                                 display: hasBodyFat,
                                 position: 'top',
                                 labels: { padding: 12, usePointStyle: true, pointStyleWidth: 8, font: { size: 11 } }
                             },
                             tooltip: {
                                 backgroundColor: 'rgba(24,24,27,0.92)',
                                 borderColor: 'rgba(63,63,70,0.6)',
                                 borderWidth: 1,
                                 titleColor: '#f4f4f5',
                                 bodyColor: '#a1a1aa',
                                 padding: 10,
                                 callbacks: {
                                     label: ctx => {
                                         if (ctx.raw === null) return null;
                                         const suffix = ctx.datasetIndex === 0 ? ' kg' : '%';
                                         const val = typeof ctx.raw === 'number' ? ctx.raw.toFixed(1) : ctx.raw;
                                         return ' ' + ctx.dataset.label.split(' ')[0] + ': ' + val + suffix;
                                     },
                                     title: items => items[0]?.label ?? ''
                                 }
                             }
                         },
                         scales: (() => { const s = { y: { type: 'linear', display: true, position: 'left', grid: { color: 'rgba(63, 63, 70, 0.15)' }, title: { display: true, text: '{{ __('client_dashboard.weight_kg') }}', font: { size: 11 } } }, x: { grid: { display: false } } }; if (hasBodyFat) { s.y1 = { type: 'linear', display: true, position: 'right', grid: { drawOnChartArea: false }, title: { display: true, text: '{{ __('client_dashboard.fat_pct_axis') }}', font: { size: 11 } } }; } return s; })()
                     }
                 });
             },
             destroy() { const c = this.chart || Chart.getChart(this.$refs.weightCanvas); if (c) { c.stop(); c.destroy(); } this.chart = null; }
         }"
         x-init="init()">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-semibold text-wc-text">{{ __('client_dashboard.weight_trend') }}</h3>
            <span class="text-xs text-wc-text-tertiary">{{ __('client_dashboard.last_90_days') }}</span>
        </div>
        @if(count($weightChartData) > 0)
            <div class="relative h-56 chart-container">
                <canvas x-ref="weightCanvas"></canvas>
            </div>
        @else
            <div class="flex flex-col items-center justify-center h-48 text-center">
                <svg class="h-8 w-8 text-wc-text-tertiary/40" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v17.25m0 0c-1.472 0-2.882.265-4.185.75M12 20.25c1.472 0 2.882.265 4.185.75M18.75 4.97A48.416 48.416 0 0 0 12 4.5c-2.291 0-4.545.16-6.75.47m13.5 0c1.01.143 2.01.317 3 .52m-3-.52 2.62 10.726c.122.499-.106 1.028-.589 1.202a5.988 5.988 0 0 1-2.031.352 5.988 5.988 0 0 1-2.031-.352c-.483-.174-.711-.703-.59-1.202L18.75 4.971Zm-16.5.52c.99-.203 1.99-.377 3-.52m0 0 2.62 10.726c.122.499-.106 1.028-.589 1.202a5.989 5.989 0 0 1-2.031.352 5.989 5.989 0 0 1-2.031-.352c-.483-.174-.711-.703-.59-1.202L5.25 4.971Z" />
                </svg>
                <p class="mt-2 text-sm text-wc-text-tertiary">{{ __('client_dashboard.no_weight_data') }}</p>
                <a href="{{ route('client.metrics') }}" class="mt-2 text-xs font-medium text-wc-accent hover:underline">{{ __('client_dashboard.log_weight') }}</a>
            </div>
        @endif
    </div>

    {{-- ITEM 3: Coach Avatar Card --}}
    <div class="flex items-center gap-4 rounded-card border border-wc-border bg-wc-bg-tertiary p-4 card-hover-lift">
        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-wc-accent/10">
            <span class="font-display text-sm tracking-wide text-wc-accent">{{ $coachInitials }}</span>
        </div>
        <div class="min-w-0 flex-1">
            <p class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">{{ __('client_dashboard.your_coach') }}</p>
            <p class="text-sm font-semibold text-wc-text truncate">{{ $coachName }}</p>
        </div>
        <a href="{{ route('client.chat') }}"
           class="inline-flex items-center gap-1.5 rounded-full bg-wc-accent px-4 py-2 text-xs font-medium text-white hover:bg-wc-accent-hover transition-colors shadow-lg shadow-wc-accent/20 btn-press">
            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a5.969 5.969 0 0 1-.474-.065 4.48 4.48 0 0 0 .978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z" />
            </svg>
            {{ __('client_dashboard.send_message') }}
        </a>
    </div>

    {{-- Check-in countdown --}}
    <a href="{{ route('client.checkin') }}"
       class="group block rounded-card border p-4 sm:p-5 transition-colors
              @if($daysUntilCheckin <= 0)
                  border-wc-accent/40 bg-wc-accent/10 hover:bg-wc-accent/15
              @elseif($daysUntilCheckin <= 2)
                  border-amber-500/40 bg-amber-500/10 hover:bg-amber-500/15
              @else
                  border-emerald-500/30 bg-emerald-500/5 hover:bg-emerald-500/10
              @endif"
       x-data="{
           targetDate: '{{ $daysUntilCheckin <= 0 ? now()->toIso8601String() : now()->addDays($daysUntilCheckin)->startOfDay()->toIso8601String() }}',
           hours: '00',
           minutes: '00',
           seconds: '00',
           isUrgent: {{ $daysUntilCheckin <= 0 ? 'true' : 'false' }},
           showTimer: {{ $daysUntilCheckin >= 0 && $daysUntilCheckin <= 1 ? 'true' : 'false' }},
           tick() {
               if (!this.showTimer || this.isUrgent) return;
               const now = new Date();
               const target = new Date(this.targetDate);
               let diff = Math.max(0, Math.floor((target - now) / 1000));
               this.hours = String(Math.floor(diff / 3600)).padStart(2, '0');
               this.minutes = String(Math.floor((diff % 3600) / 60)).padStart(2, '0');
               this.seconds = String(diff % 60).padStart(2, '0');
           },
           init() {
               this.tick();
               if (this.showTimer && !this.isUrgent) {
                   this._intervalId = setInterval(() => this.tick(), 1000);
               }
           },
           destroy() {
               if (this._intervalId) {
                   clearInterval(this._intervalId);
                   this._intervalId = null;
               }
           }
       }">
        <div class="flex items-center gap-4">
            {{-- Icon --}}
            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl
                        @if($daysUntilCheckin <= 0) bg-wc-accent/20 @elseif($daysUntilCheckin <= 2) bg-amber-500/20 @else bg-emerald-500/15 @endif">
                @if($daysUntilCheckin <= 0)
                    <svg class="h-5 w-5 text-wc-accent animate-pulse" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                    </svg>
                @elseif($daysUntilCheckin <= 2)
                    <svg class="h-5 w-5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                @else
                    <svg class="h-5 w-5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                    </svg>
                @endif
            </div>

            {{-- Text --}}
            <div class="min-w-0 flex-1">
                @if($daysUntilCheckin <= 0)
                    <p class="text-sm font-semibold text-wc-accent uppercase tracking-wide">{{ __('client_dashboard.checkin_pending') }}</p>
                    <p class="mt-0.5 text-xs text-wc-text-tertiary">{{ __('client_dashboard.checkin_pending_desc') }}</p>
                @elseif($daysUntilCheckin <= 2)
                    <p class="text-sm font-semibold text-amber-600 dark:text-amber-400">{{ trans_choice('client_dashboard.checkin_in_days', $daysUntilCheckin, ['count' => $daysUntilCheckin]) }}</p>
                    <p class="mt-0.5 text-xs text-wc-text-tertiary capitalize">{{ $nextCheckinDate }}</p>
                @else
                    <p class="text-sm font-semibold text-emerald-600 dark:text-emerald-400">{{ __('client_dashboard.next_checkin_in', ['count' => $daysUntilCheckin]) }}</p>
                    <p class="mt-0.5 text-xs text-wc-text-tertiary capitalize">{{ $nextCheckinDate }}</p>
                @endif
            </div>

            {{-- Live countdown timer (if < 24h) --}}
            <template x-if="showTimer && !isUrgent">
                <div class="hidden sm:flex items-center gap-1 font-data text-lg font-bold tabular-nums
                            @if($daysUntilCheckin <= 2) text-amber-600 dark:text-amber-400 @else text-emerald-600 dark:text-emerald-400 @endif">
                    <span x-text="hours"></span><span class="text-wc-text-tertiary">:</span>
                    <span x-text="minutes"></span><span class="text-wc-text-tertiary">:</span>
                    <span x-text="seconds"></span>
                </div>
            </template>

            {{-- Arrow --}}
            <svg class="h-4 w-4 shrink-0 text-wc-text-tertiary group-hover:text-wc-text transition-colors" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
            </svg>
        </div>
    </a>

    {{-- ITEM 2: Weekly Summary Card --}}
    {{-- Skeleton: Weekly Summary (shown during Livewire loading) --}}
    <div wire:loading.delay>
        <x-skeleton :card="true" />
    </div>
    <div wire:loading.remove>
    @if($hasLastWeekData)
        <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-5 card-hover-lift">
            <div class="flex items-center gap-2 mb-4">
                <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-sky-500/10">
                    <svg class="h-4 w-4 text-sky-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                    </svg>
                </div>
                <h2 class="font-display text-lg tracking-wide text-wc-text">{{ __('client_dashboard.last_week_summary') }}</h2>
            </div>

            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                {{-- Workouts --}}
                <div class="rounded-xl bg-wc-bg-secondary px-4 py-3 text-center">
                    <p class="font-data text-2xl font-bold text-wc-text"><span data-counter="{{ $lastWeekWorkouts }}" class="counter-highlight">0</span></p>
                    <p class="mt-0.5 text-[11px] text-wc-text-tertiary">{{ __('client_dashboard.workouts_label') }}</p>
                </div>
                {{-- Check-ins --}}
                <div class="rounded-xl bg-wc-bg-secondary px-4 py-3 text-center">
                    <p class="font-data text-2xl font-bold text-wc-text"><span data-counter="{{ $lastWeekCheckins }}" class="counter-highlight">0</span></p>
                    <p class="mt-0.5 text-[11px] text-wc-text-tertiary">{{ __('client_dashboard.checkins_label') }}</p>
                </div>
                {{-- Weight --}}
                <div class="rounded-xl bg-wc-bg-secondary px-4 py-3 text-center col-span-2 sm:col-span-1">
                    <p class="font-data text-2xl font-bold text-wc-text">{{ $lastWeekWeight ?? '--' }}</p>
                    <p class="mt-0.5 text-[11px] text-wc-text-tertiary">{{ $lastWeekWeight ? __('client_dashboard.current_kg') : __('client_dashboard.no_record') }}</p>
                </div>
            </div>

            {{-- Motivational text based on performance --}}
            <div class="mt-4 rounded-xl bg-wc-accent/5 border border-wc-accent/10 px-4 py-2.5">
                <p class="text-xs text-wc-text-tertiary">
                    @if($lastWeekWorkouts >= 5)
                        <span class="font-semibold text-emerald-600 dark:text-emerald-400">{{ __('client_dashboard.exceptional_week') }}</span> {{ __('client_dashboard.exceptional_week_desc', ['count' => $lastWeekWorkouts]) }}
                    @elseif($lastWeekWorkouts >= 3)
                        <span class="font-semibold text-sky-600 dark:text-sky-400">{{ __('client_dashboard.good_pace') }}</span> {{ __('client_dashboard.good_pace_desc', ['count' => $lastWeekWorkouts]) }}
                    @elseif($lastWeekWorkouts >= 1)
                        <span class="font-semibold text-amber-600 dark:text-amber-400">{{ __('client_dashboard.on_track') }}</span> {{ __('client_dashboard.on_track_desc') }}
                    @else
                        <span class="font-semibold text-wc-accent">{{ __('client_dashboard.new_week') }}</span> {{ __('client_dashboard.new_week_desc') }}
                    @endif
                </p>
            </div>
        </div>
    @else
        <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-5 card-hover-lift">
            <div class="flex items-center gap-2 mb-3">
                <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-sky-500/10">
                    <svg class="h-4 w-4 text-sky-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                    </svg>
                </div>
                <h2 class="font-display text-lg tracking-wide text-wc-text">{{ __('client_dashboard.last_week_summary') }}</h2>
            </div>
            <div class="flex items-center gap-3 rounded-xl bg-wc-accent/5 border border-wc-accent/10 px-4 py-3">
                <svg class="h-5 w-5 shrink-0 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.59 14.37a6 6 0 0 1-5.84 7.38v-4.8m5.84-2.58a14.98 14.98 0 0 0 6.16-12.12A14.98 14.98 0 0 0 9.631 8.41m5.96 5.96a14.926 14.926 0 0 1-5.841 2.58m-.119-8.54a6 6 0 0 0-7.381 5.84h4.8m2.581-5.84a14.927 14.927 0 0 0-2.58 5.84m2.699 2.7c-.103.021-.207.041-.311.06a15.09 15.09 0 0 1-2.448-2.448 14.9 14.9 0 0 1 .06-.312m-2.24 2.39a4.493 4.493 0 0 0-1.757 4.306 4.493 4.493 0 0 0 4.306-1.758M16.5 9a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Z" />
                </svg>
                <p class="text-sm text-wc-text-tertiary">
                    <span class="font-semibold text-wc-text">{{ __('client_dashboard.first_week') }}</span> &mdash; {{ __('client_dashboard.first_week_desc') }}
                </p>
            </div>
        </div>
    @endif
    </div>

    {{-- Daily missions --}}
    <div>
        <div class="mb-3 flex items-center justify-between">
            <h2 class="font-display text-lg tracking-wide text-wc-text">{{ __('client_dashboard.daily_missions') }}</h2>
            <span class="text-xs text-wc-text-tertiary">
                {{ __('client_dashboard.missions_completed', ['done' => collect($dailyMissions)->where('completed', true)->count(), 'total' => count($dailyMissions)]) }}
            </span>
        </div>
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
            @foreach($dailyMissions as $mission)
                <a href="{{ $mission['route'] }}"
                   class="group flex items-center gap-3 rounded-xl border p-4 transition-colors
                          {{ $mission['completed']
                              ? 'border-emerald-500/30 bg-emerald-500/5 hover:bg-emerald-500/10'
                              : 'border-wc-border bg-wc-bg-tertiary hover:bg-wc-bg-secondary' }}">

                    {{-- Status icon --}}
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full
                                {{ $mission['completed'] ? 'bg-emerald-500/15' : 'border-2 border-wc-border' }}">
                        @if($mission['completed'])
                            <svg class="h-5 w-5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                            </svg>
                        @else
                            @if($mission['icon'] === 'dumbbell')
                                <svg class="h-4 w-4 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                                </svg>
                            @elseif($mission['icon'] === 'checkin')
                                <svg class="h-4 w-4 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                </svg>
                            @elseif($mission['icon'] === 'scale')
                                <svg class="h-4 w-4 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v17.25m0 0c-1.472 0-2.882.265-4.185.75M12 20.25c1.472 0 2.882.265 4.185.75M18.75 4.97A48.416 48.416 0 0 0 12 4.5c-2.291 0-4.545.16-6.75.47m13.5 0c1.01.143 2.01.317 3 .52m-3-.52 2.62 10.726c.122.499-.106 1.028-.589 1.202a5.988 5.988 0 0 1-2.031.352 5.988 5.988 0 0 1-2.031-.352c-.483-.174-.711-.703-.589-1.202L18.75 4.97Zm-16.5.52c.99-.203 1.99-.377 3-.52m0 0 2.62 10.726c.122.499-.106 1.028-.589 1.202a5.989 5.989 0 0 1-2.031.352 5.989 5.989 0 0 1-2.031-.352c-.483-.174-.711-.703-.589-1.202L5.25 4.97Z" />
                                </svg>
                            @else
                                <svg class="h-4 w-4 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                                </svg>
                            @endif
                        @endif
                    </div>

                    {{-- Text --}}
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-medium leading-tight
                                  {{ $mission['completed'] ? 'text-emerald-600 dark:text-emerald-400' : 'text-wc-text' }}">
                            {{ $mission['title'] }}
                        </p>
                        <p class="mt-0.5 text-[11px] text-wc-text-tertiary">
                            {{ $mission['completed'] ? __('client_dashboard.completed') : __('client_dashboard.pending') }}
                        </p>
                    </div>

                    {{-- Arrow --}}
                    @if(!$mission['completed'])
                        <svg class="h-4 w-4 shrink-0 text-wc-text-tertiary group-hover:text-wc-text transition-colors" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                        </svg>
                    @endif
                </a>
            @endforeach
        </div>
    </div>

    {{-- Weekly overview + Recent activity --}}
    {{-- Skeleton: Weekly overview + Recent activity (shown during Livewire loading) --}}
    <div wire:loading.delay class="grid grid-cols-1 gap-4 lg:grid-cols-3">
        <div class="lg:col-span-2">
            <x-skeleton :chart="true" />
        </div>
        <x-skeleton :avatar="true" :lines="4" />
    </div>
    <div wire:loading.remove class="grid grid-cols-1 gap-4 lg:grid-cols-3">

        {{-- Weekly training overview --}}
        <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-5 lg:col-span-2 card-hover-lift">
            <h2 class="font-display text-lg tracking-wide text-wc-text">{{ __('client_dashboard.training_week') }}</h2>
            <p class="mt-1 text-xs text-wc-text-tertiary">{{ __('client_dashboard.week_of_year', ['week' => now()->isoWeek(), 'year' => now()->year]) }}</p>

            <div class="mt-5 flex items-center justify-between gap-2 sm:justify-start sm:gap-4">
                @foreach($weekDays as $day)
                    <div class="flex flex-col items-center gap-2">
                        <span class="text-[11px] font-medium text-wc-text-tertiary {{ $day['isToday'] ? '!text-wc-accent font-semibold' : '' }}">
                            {{ $day['label'] }}
                        </span>
                        @if($day['completed'])
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-500/15 sm:h-12 sm:w-12">
                                <svg class="h-5 w-5 text-emerald-500 sm:h-6 sm:w-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                </svg>
                            </div>
                        @else
                            <div class="flex h-10 w-10 items-center justify-center rounded-full border-2 border-wc-border sm:h-12 sm:w-12 {{ $day['isToday'] ? '!border-wc-accent/40' : '' }}">
                                @if($day['isToday'])
                                    <div class="h-2 w-2 rounded-full bg-wc-accent"></div>
                                @endif
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

            <div class="mt-5 flex items-center gap-4 text-xs text-wc-text-tertiary">
                <div class="flex items-center gap-1.5">
                    <div class="h-2.5 w-2.5 rounded-full bg-emerald-500/40"></div>
                    {{ __('client_dashboard.completed_legend') }}
                </div>
                <div class="flex items-center gap-1.5">
                    <div class="h-2.5 w-2.5 rounded-full border border-wc-border"></div>
                    {{ __('client_dashboard.pending_legend') }}
                </div>
                <div class="flex items-center gap-1.5">
                    <div class="h-2.5 w-2.5 rounded-full bg-wc-accent"></div>
                    {{ __('client_dashboard.today') }}
                </div>
            </div>
        </div>

        {{-- Recent activity --}}
        <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-5 card-hover-lift">
            <h2 class="font-display text-lg tracking-wide text-wc-text">{{ __('client_dashboard.recent_activity') }}</h2>

            @if(count($recentActivity) > 0)
                <ul class="mt-4 space-y-3">
                    @foreach($recentActivity as $activity)
                        <li class="flex items-start gap-3">
                            {{-- Icon --}}
                            <div class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-full
                                @if($activity['type'] === 'training') bg-emerald-500/10
                                @elseif($activity['type'] === 'checkin') bg-sky-500/10
                                @elseif($activity['type'] === 'payment') bg-violet-500/10
                                @else bg-wc-bg-secondary
                                @endif
                            ">
                                @if($activity['type'] === 'training')
                                    <svg class="h-3.5 w-3.5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                    </svg>
                                @elseif($activity['type'] === 'checkin')
                                    <svg class="h-3.5 w-3.5 text-sky-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                    </svg>
                                @elseif($activity['type'] === 'payment')
                                    <svg class="h-3.5 w-3.5 text-violet-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" />
                                    </svg>
                                @endif
                            </div>

                            {{-- Text --}}
                            <div class="min-w-0 flex-1">
                                <p class="text-sm text-wc-text">{{ $activity['description'] }}</p>
                                <p class="text-xs text-wc-text-tertiary">{{ $activity['timeAgo'] }}</p>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @else
                <div class="mt-6 flex flex-col items-center py-4 text-center">
                    <svg class="h-8 w-8 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                    <p class="mt-2 text-sm text-wc-text-tertiary">{{ __('client_dashboard.no_recent_activity') }}</p>
                </div>
            @endif
        </div>
    </div>

    {{-- Quick actions (mobile) --}}
    <div class="grid grid-cols-1 gap-3 sm:hidden">
        <a href="{{ route('client.plan') }}"
           class="flex items-center justify-center gap-2 rounded-lg bg-wc-accent px-4 py-3 text-sm font-medium text-white hover:bg-wc-accent-hover transition-colors btn-press">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            {{ __('client_dashboard.log_workout') }}
        </a>
        <a href="{{ route('client.checkin') }}"
           class="flex items-center justify-center gap-2 rounded-lg border border-wc-border bg-wc-bg-tertiary px-4 py-3 text-sm font-medium text-wc-text hover:bg-wc-bg-secondary transition-colors">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
            {{ __('client_dashboard.do_checkin') }}
        </a>
        <a href="{{ route('client.plan') }}"
           class="flex items-center justify-center gap-2 rounded-lg border border-wc-border bg-wc-bg-tertiary px-4 py-3 text-sm font-medium text-wc-text hover:bg-wc-bg-secondary transition-colors">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
            </svg>
            {{ __('client_dashboard.view_plan') }}
        </a>
    </div>

</div>
