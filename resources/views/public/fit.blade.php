<x-layouts.public>
    <x-slot:title>{{ __('fit.meta_title') }}</x-slot:title>

    {{-- 1. HERO --}}
    <section class="hero-gradient relative overflow-hidden bg-wc-bg-tertiary">
        <div class="absolute inset-0 bg-gradient-to-br from-[#DC3C64]/5 via-transparent to-transparent"></div>
        {{-- Parallax decorative orbs --}}
        <div class="parallax-hero" aria-hidden="true">
            <div class="parallax-orb parallax-orb-1" data-parallax-speed="0.2"></div>
            <div class="parallax-orb parallax-orb-2" data-parallax-speed="0.35"></div>
            <div class="parallax-orb parallax-orb-3" data-parallax-speed="0.15"></div>
            {{-- Extra pink-tinted orbs --}}
            <div class="absolute -right-40 top-20 h-[28rem] w-[28rem] rounded-full bg-[#DC3C64]/6 blur-3xl" data-parallax-speed="0.1"></div>
            <div class="absolute -left-24 bottom-10 h-[20rem] w-[20rem] rounded-full bg-[#DC3C64]/8 blur-2xl" data-parallax-speed="0.25"></div>
            <div class="absolute left-1/2 top-1/3 h-[14rem] w-[14rem] -translate-x-1/2 rounded-full bg-[#DC3C64]/4 blur-3xl" data-parallax-speed="0.18"></div>
        </div>
        <div class="relative mx-auto max-w-7xl px-4 py-24 sm:px-6 sm:py-32 lg:px-8" data-animate="fadeInUp">
            <div class="grid grid-cols-1 items-center gap-12 lg:grid-cols-2">
                {{-- Left column --}}
                <div>
                    <span class="inline-flex items-center gap-2 rounded-full bg-[#DC3C64]/10 px-4 py-1.5 text-xs font-semibold text-[#DC3C64]">
                        <svg class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd" /></svg>
                        {{ __('fit.hero_badge') }}
                    </span>
                    <h1 class="mt-4 font-display text-5xl leading-none tracking-wide text-wc-text sm:text-6xl lg:text-7xl">
                        {{ __('fit.hero_name_line1') }}<br>
                        <span class="text-[#DC3C64]">{{ __('fit.hero_name_line2') }}</span>
                    </h1>
                    <p class="mt-6 max-w-lg text-lg text-wc-text-secondary">
                        {{ __('fit.hero_bio') }}
                    </p>
                    <div class="mt-8 flex flex-wrap gap-8 text-sm text-wc-text-secondary">
                        <div class="flex items-center gap-2">
                            <span class="counter-highlight font-data text-2xl font-bold text-[#DC3C64]" data-counter="6" data-suffix="+">{{ __('fit.stat_years_value') }}</span>
                            <span>{{ __('fit.stat_years_label') }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="counter-highlight font-data text-2xl font-bold text-[#DC3C64]" data-counter="120" data-suffix="+">{{ __('fit.stat_clients_value') }}</span>
                            <span>{{ __('fit.stat_clients_label') }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="counter-highlight font-data text-2xl font-bold text-[#DC3C64]" data-counter="96" data-suffix="%">{{ __('fit.stat_adherence_value') }}</span>
                            <span>{{ __('fit.stat_adherence_label') }}</span>
                        </div>
                    </div>
                    <div class="mt-8 flex flex-wrap gap-4">
                        <a href="{{ route('inscripcion') }}" class="btn-press rounded-full bg-[#DC3C64] px-7 py-3.5 text-sm font-semibold text-white shadow-lg shadow-[#DC3C64]/20 hover:bg-[#DC3C64]/80 transition-colors">
                            {{ __('fit.hero_cta_train') }}
                        </a>
                        <a href="https://wa.me/573001234567?text=Hola%20Silvia%2C%20quiero%20informaci%C3%B3n" target="_blank" class="btn-press rounded-full border border-[#DC3C64]/30 px-7 py-3.5 text-sm font-semibold text-wc-text hover:bg-[#DC3C64]/10 transition-colors">
                            <span class="flex items-center gap-2">
                                <svg class="h-4 w-4 text-[#DC3C64]" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
                                {{ __('fit.hero_cta_whatsapp') }}
                            </span>
                        </a>
                    </div>
                </div>

                {{-- Right column: Phone mockup --}}
                <div class="hidden justify-center lg:flex">
                    <div class="animate-float relative mx-auto w-[300px]">
                        {{-- Phone frame --}}
                        <div class="rounded-[2.5rem] border-[6px] border-wc-border bg-wc-bg-secondary shadow-2xl shadow-[#DC3C64]/10 overflow-hidden">
                            {{-- Notch --}}
                            <div class="flex justify-center bg-wc-bg-secondary pt-2 pb-1">
                                <div class="h-5 w-28 rounded-full bg-wc-bg-tertiary"></div>
                            </div>

                            {{-- Screen content --}}
                            <div class="bg-wc-bg px-4 pb-6 pt-4">
                                {{-- Header --}}
                                <div class="flex items-center gap-3 rounded-xl bg-wc-bg-tertiary p-3">
                                    <div class="flex h-9 w-9 items-center justify-center rounded-full bg-[#DC3C64]/20">
                                        <span class="text-sm font-bold text-[#DC3C64]">SM</span>
                                    </div>
                                    <div>
                                        <p class="text-xs font-semibold text-wc-text">{{ __('fit.mockup_coach_label') }}</p>
                                        <p class="text-[10px] text-[#DC3C64]">{{ __('fit.mockup_online') }}</p>
                                    </div>
                                </div>

                                {{-- Stats row --}}
                                <div class="mt-3 grid grid-cols-3 gap-2">
                                    <div class="rounded-lg bg-wc-bg-tertiary p-2 text-center">
                                        <p class="font-data text-sm font-bold text-[#DC3C64]">Sem 8</p>
                                        <p class="text-[9px] text-wc-text-secondary">{{ __('fit.mockup_week_label') }}</p>
                                    </div>
                                    <div class="rounded-lg bg-wc-bg-tertiary p-2 text-center">
                                        <p class="font-data text-sm font-bold text-[#DC3C64]">-6.2kg</p>
                                        <p class="text-[9px] text-wc-text-secondary">{{ __('fit.mockup_progress') }}</p>
                                    </div>
                                    <div class="rounded-lg bg-wc-bg-tertiary p-2 text-center">
                                        <p class="font-data text-sm font-bold text-[#DC3C64]">94%</p>
                                        <p class="text-[9px] text-wc-text-secondary">{{ __('fit.mockup_adherence') }}</p>
                                    </div>
                                </div>

                                {{-- Next session --}}
                                <div class="mt-3 rounded-xl border border-[#DC3C64]/30 bg-[#DC3C64]/5 p-3">
                                    <p class="text-[10px] font-semibold uppercase tracking-wider text-[#DC3C64]">{{ __('fit.mockup_next_session') }}</p>
                                    <p class="mt-1 text-xs font-medium text-wc-text">{{ __('fit.mockup_tomorrow') }}</p>
                                    <p class="text-[10px] text-wc-text-secondary">{{ __('fit.mockup_session_name') }}</p>
                                </div>

                                {{-- Habit checklist --}}
                                <div class="mt-3 space-y-2">
                                    <p class="text-[10px] font-semibold uppercase tracking-wider text-wc-text-secondary">{{ __('fit.mockup_habits_today') }}</p>
                                    <div class="flex items-center gap-2">
                                        <div class="flex h-4 w-4 items-center justify-center rounded bg-[#DC3C64]">
                                            <svg class="h-2.5 w-2.5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                                        </div>
                                        <span class="text-[11px] text-wc-text line-through opacity-60">{{ __('fit.mockup_habit_water') }}</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <div class="flex h-4 w-4 items-center justify-center rounded bg-[#DC3C64]">
                                            <svg class="h-2.5 w-2.5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                                        </div>
                                        <span class="text-[11px] text-wc-text line-through opacity-60">{{ __('fit.mockup_habit_protein') }}</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <div class="flex h-4 w-4 items-center justify-center rounded bg-[#DC3C64]">
                                            <svg class="h-2.5 w-2.5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                                        </div>
                                        <span class="text-[11px] text-wc-text line-through opacity-60">{{ __('fit.mockup_habit_sleep') }}</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <div class="flex h-4 w-4 items-center justify-center rounded border border-wc-border bg-wc-bg-tertiary"></div>
                                        <span class="text-[11px] text-wc-text">{{ __('fit.mockup_habit_walk') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Section divider --}}
    <div class="section-divider" aria-hidden="true"></div>

    {{-- 2. BIO --}}
    <section class="bg-wc-bg">
        <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8" data-animate="fadeInUp">
            <div class="mx-auto max-w-3xl">
                <h2 class="text-center font-display text-3xl tracking-wide text-wc-text">{{ __('fit.bio_heading') }}</h2>
                <div class="mx-auto mt-2 h-1 w-12 rounded-full bg-[#DC3C64]"></div>
                <div class="mt-8 space-y-4 text-wc-text-secondary">
                    <p>{{ __('fit.bio_p1') }}</p>
                    <p>{!! __('fit.bio_p2') !!}</p>
                    <p>{{ __('fit.bio_p3') }}</p>
                </div>
            </div>
        </div>
    </section>

    {{-- Section divider --}}
    <div class="section-divider" aria-hidden="true"></div>

    {{-- 2.5 TRANSFORMACIONES --}}
    <section class="bg-wc-bg-tertiary">
        <div class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8" data-animate="fadeInUp">
            <div class="mx-auto max-w-3xl text-center">
                <h2 class="font-display text-3xl tracking-wide text-wc-text sm:text-4xl">{{ __('fit.transformations_heading') }}</h2>
                <div class="mx-auto mt-2 h-1 w-12 rounded-full bg-[#DC3C64]"></div>
                <p class="mt-4 text-wc-text-secondary">{{ __('fit.transformations_subtitle') }}</p>
            </div>
            <div class="mt-12 grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-3 stagger-grid">
                @php
                    $transformations = [
                        ['name' => 'Camila R.', 'duration' => '16 semanas', 'result' => '-8 kg, +tono muscular', 'quote' => 'Silvia cambio mi relacion con el ejercicio.'],
                        ['name' => 'Laura M.', 'duration' => '12 semanas', 'result' => 'Recomposicion corporal', 'quote' => 'Por primera vez, disfruto entrenar.'],
                        ['name' => 'Valentina G.', 'duration' => '20 semanas', 'result' => '-12 kg, fuerza x3', 'quote' => 'No sabia que era capaz de tanto.'],
                    ];
                @endphp
                @foreach($transformations as $i => $t)
                <div class="card-hover-lift scroll-reveal rounded-xl border border-wc-border bg-wc-bg p-8" data-animate="fadeInUp" data-animate-delay="{{ ($i + 1) * 100 }}">
                    <div class="mb-4 aspect-[4/3] rounded-lg bg-gradient-to-br from-[#DC3C64]/10 to-[#DC3C64]/5 flex items-center justify-center">
                        <div class="text-center">
                            <p class="font-display text-lg text-[#DC3C64]">{{ __('fit.transformation_placeholder_title') }}</p>
                            <p class="mt-1 text-xs text-wc-text-tertiary">{{ __('fit.transformation_placeholder_sub') }}</p>
                        </div>
                    </div>
                    <h3 class="font-semibold text-wc-text">{{ $t['name'] }}</h3>
                    <p class="mt-1 text-sm text-[#DC3C64]">{{ $t['duration'] }} · {{ $t['result'] }}</p>
                    <p class="mt-3 text-sm italic text-wc-text-secondary">"{{ $t['quote'] }}"</p>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Section divider --}}
    <div class="section-divider" aria-hidden="true"></div>

    {{-- 3. SPECIALTIES --}}
    <section class="bg-wc-bg">
        <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8" data-animate="fadeInUp">
            <h2 class="text-center font-display text-3xl tracking-wide text-wc-text">{{ __('fit.specialties_heading') }}</h2>
            <div class="mx-auto mt-2 h-1 w-12 rounded-full bg-[#DC3C64]"></div>
            <div class="mt-12 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 stagger-grid">
                @php
                    $specialties = [
                        ['icon' => 'M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z', 'title_key' => 'fit.spec_1_title', 'desc_key' => 'fit.spec_1_desc'],
                        ['icon' => 'M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z', 'title_key' => 'fit.spec_2_title', 'desc_key' => 'fit.spec_2_desc'],
                        ['icon' => 'M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z', 'title_key' => 'fit.spec_3_title', 'desc_key' => 'fit.spec_3_desc'],
                        ['icon' => 'M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z', 'title_key' => 'fit.spec_4_title', 'desc_key' => 'fit.spec_4_desc'],
                        ['icon' => 'M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.455 2.456L21.75 6l-1.036.259a3.375 3.375 0 0 0-2.455 2.456ZM16.894 20.567 16.5 21.75l-.394-1.183a2.25 2.25 0 0 0-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 0 0 1.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 0 0 1.423 1.423l1.183.394-1.183.394a2.25 2.25 0 0 0-1.423 1.423Z', 'title_key' => 'fit.spec_5_title', 'desc_key' => 'fit.spec_5_desc'],
                        ['icon' => 'M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z', 'title_key' => 'fit.spec_6_title', 'desc_key' => 'fit.spec_6_desc'],
                    ];
                @endphp
                @foreach($specialties as $i => $spec)
                    <div class="card-hover-lift scroll-reveal-scale rounded-xl border border-wc-border bg-wc-bg-secondary p-6 transition-colors hover:border-[#DC3C64]/30" data-animate="scaleIn" data-animate-delay="{{ ($i + 1) * 100 }}">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-[#DC3C64]/10">
                            <svg class="h-5 w-5 text-[#DC3C64]" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $spec['icon'] }}" />
                            </svg>
                        </div>
                        <h3 class="mt-4 text-sm font-semibold text-wc-text">{{ __($spec['title_key']) }}</h3>
                        <p class="mt-2 text-sm text-wc-text-secondary">{{ __($spec['desc_key']) }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Section divider --}}
    <div class="section-divider" aria-hidden="true"></div>

    {{-- 4. DASHBOARD PREVIEW --}}
    <section class="bg-wc-bg-tertiary">
        <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8" data-animate="fadeInUp">
            <div class="text-center">
                <span class="inline-flex rounded-full bg-[#DC3C64]/10 px-4 py-1.5 text-xs font-semibold text-[#DC3C64]">{{ __('fit.dashboard_label') }}</span>
                <h2 class="mt-4 font-display text-3xl tracking-wide text-wc-text sm:text-4xl">{{ __('fit.dashboard_heading') }}</h2>
                <p class="mx-auto mt-4 max-w-2xl text-wc-text-secondary">
                    {{ __('fit.dashboard_body') }}
                </p>
            </div>

            <div class="mt-12 grid grid-cols-1 items-start gap-8 lg:grid-cols-2">
                {{-- LEFT: Browser window mockup --}}
                <div class="animate-float-slow rounded-xl border border-wc-border bg-wc-bg shadow-xl shadow-black/10 overflow-hidden">
                    {{-- Chrome bar --}}
                    <div class="flex items-center gap-3 border-b border-wc-border bg-wc-bg-secondary px-4 py-3">
                        <div class="flex gap-1.5">
                            <div class="h-3 w-3 rounded-full bg-red-500/60"></div>
                            <div class="h-3 w-3 rounded-full bg-yellow-500/60"></div>
                            <div class="h-3 w-3 rounded-full bg-green-500/60"></div>
                        </div>
                        <div class="flex-1 rounded-md bg-wc-bg-tertiary px-3 py-1 text-center">
                            <span class="text-[10px] text-wc-text-tertiary">app.wellcorefitness.com/coach/silvia</span>
                        </div>
                    </div>

                    {{-- Dashboard content --}}
                    <div class="p-5">
                        {{-- Dashboard header --}}
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-sm font-semibold text-wc-text">{{ __('fit.mockup_plan_heading') }}</h3>
                                <p class="text-xs text-wc-text-secondary">{{ __('fit.mockup_week_phase') }}</p>
                            </div>
                            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-[#DC3C64]/20">
                                <span class="text-xs font-bold text-[#DC3C64]">SM</span>
                            </div>
                        </div>

                        {{-- Weekly plan grid --}}
                        <div class="mt-4 grid grid-cols-6 gap-1.5">
                            @php
                                $days = [
                                    ['day' => 'Lun', 'workout' => 'Upper A', 'active' => false],
                                    ['day' => 'Mar', 'workout' => 'Cardio HIIT', 'active' => false],
                                    ['day' => 'Mi&eacute;', 'workout' => 'Lower A', 'active' => true],
                                    ['day' => 'Jue', 'workout' => 'Descanso', 'active' => false],
                                    ['day' => 'Vie', 'workout' => 'Upper B', 'active' => false],
                                    ['day' => 'S&aacute;b', 'workout' => 'Lower B', 'active' => false],
                                ];
                            @endphp
                            @foreach($days as $d)
                                <div class="rounded-lg p-2 text-center {{ $d['active'] ? 'bg-[#DC3C64] text-white' : 'bg-wc-bg-secondary' }}">
                                    <p class="text-[10px] font-medium {{ $d['active'] ? 'text-white/80' : 'text-wc-text-secondary' }}">{!! $d['day'] !!}</p>
                                    <p class="mt-0.5 text-[9px] font-semibold {{ $d['active'] ? 'text-white' : 'text-wc-text' }}">{!! $d['workout'] !!}</p>
                                </div>
                            @endforeach
                        </div>

                        {{-- Exercise preview card --}}
                        <div class="mt-4 rounded-xl border border-[#DC3C64]/30 bg-[#DC3C64]/5 p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-[10px] font-semibold uppercase tracking-wider text-[#DC3C64]">{{ __('fit.mockup_current_ex') }}</p>
                                    <p class="mt-1 text-sm font-medium text-wc-text">{{ __('fit.mockup_exercise_name') }}</p>
                                    <p class="text-xs text-wc-text-secondary">{{ __('fit.mockup_exercise_desc') }}</p>
                                </div>
                                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-[#DC3C64]">
                                    <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.347a1.125 1.125 0 0 1 0 1.972l-11.54 6.347a1.125 1.125 0 0 1-1.667-.986V5.653Z" /></svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- RIGHT: Phone mockup --}}
                <div class="flex justify-center">
                    <div class="animate-float w-[280px]">
                        <div class="rounded-[2.5rem] border-[6px] border-wc-border bg-wc-bg-secondary shadow-2xl shadow-[#DC3C64]/10 overflow-hidden">
                            {{-- Notch --}}
                            <div class="flex justify-center bg-wc-bg-secondary pt-2 pb-1">
                                <div class="h-5 w-24 rounded-full bg-wc-bg-tertiary"></div>
                            </div>

                            {{-- Screen --}}
                            <div class="bg-wc-bg px-4 pb-6 pt-3">
                                {{-- Title --}}
                                <div class="flex items-center justify-between">
                                    <h4 class="text-sm font-semibold text-wc-text">{{ __('fit.mockup_nutrition_heading') }}</h4>
                                    <div class="flex h-6 w-6 items-center justify-center rounded-full bg-[#DC3C64]/20">
                                        <svg class="h-3.5 w-3.5 text-[#DC3C64]" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.75a.75.75 0 1 1 0-1.5.75.75 0 0 1 0 1.5ZM12 12.75a.75.75 0 1 1 0-1.5.75.75 0 0 1 0 1.5ZM12 18.75a.75.75 0 1 1 0-1.5.75.75 0 0 1 0 1.5Z" /></svg>
                                    </div>
                                </div>

                                {{-- Macros card --}}
                                <div class="mt-3 rounded-xl bg-wc-bg-tertiary p-3">
                                    <div class="text-center">
                                        <p class="font-data text-2xl font-bold text-[#DC3C64]">1,800</p>
                                        <p class="text-[10px] text-wc-text-secondary">{{ __('fit.mockup_kcal_goal') }}</p>
                                    </div>
                                    <div class="mt-3 grid grid-cols-3 gap-2">
                                        <div class="text-center">
                                            <p class="font-data text-xs font-bold text-wc-text">130g</p>
                                            <p class="text-[9px] text-wc-text-tertiary">{{ __('fit.mockup_protein') }}</p>
                                        </div>
                                        <div class="text-center">
                                            <p class="font-data text-xs font-bold text-wc-text">180g</p>
                                            <p class="text-[9px] text-wc-text-tertiary">{{ __('fit.mockup_carbs') }}</p>
                                        </div>
                                        <div class="text-center">
                                            <p class="font-data text-xs font-bold text-wc-text">65g</p>
                                            <p class="text-[9px] text-wc-text-tertiary">{{ __('fit.mockup_fats') }}</p>
                                        </div>
                                    </div>
                                </div>

                                {{-- Progress bars with CSS animation --}}
                                <div class="mt-3 space-y-2"
                                     x-data="{ visible: false }"
                                     x-intersect="visible = true">
                                    <div>
                                        <div class="flex items-center justify-between text-[10px]">
                                            <span class="text-wc-text-secondary">{{ __('fit.mockup_protein') }}</span>
                                            <span class="font-data font-semibold text-wc-text">98/130g</span>
                                        </div>
                                        <div class="mt-1 h-1.5 w-full rounded-full bg-wc-bg-tertiary">
                                            <div class="h-1.5 rounded-full bg-[#DC3C64] transition-all duration-1000 ease-out"
                                                 :style="visible ? 'width: 75%' : 'width: 0%'"></div>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="flex items-center justify-between text-[10px]">
                                            <span class="text-wc-text-secondary">{{ __('fit.mockup_carbs') }}</span>
                                            <span class="font-data font-semibold text-wc-text">120/180g</span>
                                        </div>
                                        <div class="mt-1 h-1.5 w-full rounded-full bg-wc-bg-tertiary">
                                            <div class="h-1.5 rounded-full bg-[#DC3C64]/60 transition-all duration-1000 ease-out delay-150"
                                                 :style="visible ? 'width: 67%' : 'width: 0%'"></div>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="flex items-center justify-between text-[10px]">
                                            <span class="text-wc-text-secondary">{{ __('fit.mockup_fats') }}</span>
                                            <span class="font-data font-semibold text-wc-text">45/65g</span>
                                        </div>
                                        <div class="mt-1 h-1.5 w-full rounded-full bg-wc-bg-tertiary">
                                            <div class="h-1.5 rounded-full bg-[#DC3C64]/40 transition-all duration-1000 ease-out delay-300"
                                                 :style="visible ? 'width: 69%' : 'width: 0%'"></div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Meal card --}}
                                <div class="mt-3 rounded-xl border border-wc-border bg-wc-bg-tertiary p-3">
                                    <div class="flex items-center gap-2">
                                        <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-[#DC3C64]/10">
                                            <svg class="h-3.5 w-3.5 text-[#DC3C64]" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8.25v-1.5m0 1.5c-1.355 0-2.697.056-4.024.166C6.845 8.51 6 9.473 6 10.608v2.513m6-4.871c1.355 0 2.697.056 4.024.166C17.155 8.51 18 9.473 18 10.608v2.513M15 8.25v-1.5m-6 1.5v-1.5m12 9.75-1.5.75a3.354 3.354 0 0 1-3 0 3.354 3.354 0 0 0-3 0 3.354 3.354 0 0 1-3 0 3.354 3.354 0 0 0-3 0 3.354 3.354 0 0 1-3 0L3 16.5m15-3.379a48.474 48.474 0 0 0-6-.371c-2.032 0-4.034.126-6 .371m12 0c.39.049.777.102 1.163.16 1.07.16 1.837 1.094 1.837 2.175v5.169c0 .621-.504 1.125-1.125 1.125H4.125A1.125 1.125 0 0 1 3 20.625v-5.17c0-1.08.768-2.014 1.837-2.174A47.78 47.78 0 0 1 6 13.12M12.265 3.11a.375.375 0 1 1-.53 0L12 2.845l.265.265Z" /></svg>
                                        </div>
                                        <div>
                                            <p class="text-[10px] font-semibold text-wc-text">{{ __('fit.mockup_lunch') }}</p>
                                            <p class="text-[9px] text-wc-text-secondary">{{ __('fit.mockup_lunch_desc') }}</p>
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

    {{-- Section divider --}}
    <div class="section-divider" aria-hidden="true"></div>

    {{-- 5. TESTIMONIALS with Alpine slider --}}
    <section class="bg-wc-bg">
        <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8" data-animate="fadeInUp">
            <h2 class="text-center font-display text-3xl tracking-wide text-wc-text">{{ __('fit.testimonials_heading') }}</h2>
            <div class="mx-auto mt-2 h-1 w-12 rounded-full bg-[#DC3C64]"></div>

            @php
                $testimonials = [
                    ['name' => 'Camila R.', 'months' => '8 meses', 'text' => 'Silvia cambi&oacute; mi relaci&oacute;n con el entrenamiento. Antes odiaba el gym, ahora es mi momento favorito del d&iacute;a. Perd&iacute; 12kg de grasa y gan&eacute; confianza en m&iacute; misma.'],
                    ['name' => 'Laura M.', 'months' => '5 meses', 'text' => 'Lo que m&aacute;s valoro es que Silvia entiende la vida real. No me juzga si un d&iacute;a no puedo entrenar, me ayuda a adaptar. Mi fuerza se duplic&oacute; en 5 meses.'],
                    ['name' => 'Valentina G.', 'months' => '1 a&ntilde;o', 'text' => 'Empec&eacute; sin saber nada de nutrici&oacute;n. Hoy manejo mis macros con confianza y mi rendimiento en el gym es otro nivel. La mejor inversi&oacute;n que he hecho.'],
                ];
            @endphp

            {{-- Alpine.js auto-advancing slider --}}
            <div class="mt-12"
                 x-data="{ current: 0, total: 3 }"
                 x-init="setInterval(() => current = (current + 1) % total, 5000)">

                {{-- Mobile: one at a time --}}
                <div class="block md:hidden">
                    @foreach($testimonials as $idx => $t)
                        <div x-show="current === {{ $idx }}"
                             x-transition:enter="transition ease-out duration-500"
                             x-transition:enter-start="opacity-0 translate-x-4"
                             x-transition:enter-end="opacity-100 translate-x-0"
                             x-transition:leave="transition ease-in duration-300"
                             x-transition:leave-start="opacity-100 translate-x-0"
                             x-transition:leave-end="opacity-0 -translate-x-4"
                             class="card-hover-lift rounded-xl border border-wc-border bg-wc-bg-secondary p-6">
                            <div class="flex items-center gap-1 text-[#DC3C64]">
                                @for($i = 0; $i < 5; $i++)
                                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                @endfor
                            </div>
                            <p class="mt-4 text-sm text-wc-text-secondary italic">&ldquo;{!! $t['text'] !!}&rdquo;</p>
                            <div class="mt-4 flex items-center gap-3">
                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-[#DC3C64]/20 text-xs font-semibold text-[#DC3C64]">
                                    {{ substr($t['name'], 0, 1) }}
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-wc-text">{{ $t['name'] }}</p>
                                    <p class="text-xs text-wc-text-tertiary">{{ $t['months'] }} {{ __('fit.months_with_silvia') }}</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Desktop: all 3 visible with stagger --}}
                <div class="hidden md:grid md:grid-cols-3 md:gap-6 stagger-grid">
                    @foreach($testimonials as $idx => $t)
                        <div class="card-hover-lift scroll-reveal rounded-xl border border-wc-border bg-wc-bg-secondary p-6"
                             data-animate="fadeInUp"
                             data-animate-delay="{{ ($idx + 1) * 150 }}"
                             :class="current === {{ $idx }} ? 'ring-1 ring-[#DC3C64]/30' : ''"
                             style="transition: box-shadow 0.3s, ring 0.3s;">
                            <div class="flex items-center gap-1 text-[#DC3C64]">
                                @for($i = 0; $i < 5; $i++)
                                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                @endfor
                            </div>
                            <p class="mt-4 text-sm text-wc-text-secondary italic">&ldquo;{!! $t['text'] !!}&rdquo;</p>
                            <div class="mt-4 flex items-center gap-3">
                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-[#DC3C64]/20 text-xs font-semibold text-[#DC3C64]">
                                    {{ substr($t['name'], 0, 1) }}
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-wc-text">{{ $t['name'] }}</p>
                                    <p class="text-xs text-wc-text-tertiary">{{ $t['months'] }} {{ __('fit.months_with_silvia') }}</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Navigation dots --}}
                <div class="mt-8 flex justify-center gap-2">
                    @foreach($testimonials as $idx => $t)
                        <button
                            @click="current = {{ $idx }}"
                            :class="current === {{ $idx }} ? 'bg-[#DC3C64] w-6' : 'bg-wc-border w-2'"
                            class="h-2 rounded-full transition-all duration-300"
                            aria-label="Testimonio {{ $idx + 1 }}">
                        </button>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    {{-- Section divider --}}
    <div class="section-divider" aria-hidden="true"></div>

    {{-- 6. CTA --}}
    <section class="relative overflow-hidden bg-wc-bg-secondary">
        {{-- Decorative orbs --}}
        <div class="absolute inset-0 pointer-events-none" aria-hidden="true">
            <div class="absolute -left-32 -top-32 h-80 w-80 rounded-full bg-[#DC3C64]/8 blur-3xl"></div>
            <div class="absolute -right-32 -bottom-32 h-96 w-96 rounded-full bg-[#DC3C64]/6 blur-3xl"></div>
            <div class="absolute left-1/2 top-1/2 h-64 w-64 -translate-x-1/2 -translate-y-1/2 rounded-full bg-[#DC3C64]/4 blur-2xl"></div>
        </div>
        <div class="relative mx-auto max-w-7xl px-4 py-20 text-center sm:px-6 lg:px-8" data-animate="fadeInUp">
            <h2 class="font-display text-3xl tracking-wide text-wc-text sm:text-4xl">{{ __('fit.cta_heading') }}</h2>
            <div class="mx-auto mt-2 h-1 w-12 rounded-full bg-[#DC3C64]"></div>
            <p class="mx-auto mt-6 max-w-lg text-wc-text-secondary">
                {{ __('fit.cta_body') }}
            </p>
            <div class="mt-8 flex flex-wrap justify-center gap-4">
                <a href="{{ route('inscripcion') }}" class="btn-press rounded-full bg-[#DC3C64] px-8 py-3.5 text-base font-semibold text-white shadow-lg shadow-[#DC3C64]/20 hover:bg-[#DC3C64]/80 transition-colors">
                    {{ __('fit.cta_enroll') }}
                </a>
                <a href="{{ route('planes') }}" class="btn-press rounded-full border border-[#DC3C64]/30 px-8 py-3.5 text-base font-semibold text-wc-text hover:bg-[#DC3C64]/10 transition-colors">
                    {{ __('fit.cta_plans') }}
                </a>
            </div>
        </div>
    </section>
</x-layouts.public>
