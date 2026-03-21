<x-layouts.public>
    <x-slot:title>Entrenamiento Presencial Bucaramanga - WellCore Fitness</x-slot:title>

    {{-- Hero --}}
    <section class="hero-gradient relative overflow-hidden bg-wc-bg-tertiary">
        <div class="absolute inset-0 bg-gradient-to-br from-wc-accent/5 via-transparent to-transparent"></div>
        {{-- Parallax decorative orbs --}}
        <div class="parallax-hero" aria-hidden="true">
            <div class="parallax-orb parallax-orb-1" data-parallax-speed="0.2"></div>
            <div class="parallax-orb parallax-orb-2" data-parallax-speed="0.35"></div>
            <div class="parallax-orb parallax-orb-3" data-parallax-speed="0.15"></div>
            <div class="parallax-orb parallax-orb-4" data-parallax-speed="0.25"></div>
            <div class="parallax-orb parallax-orb-5" data-parallax-speed="0.4"></div>
        </div>
        <div class="relative mx-auto max-w-7xl px-4 py-24 text-center sm:px-6 sm:py-32 lg:px-8" data-animate="fadeInUp">
            <span class="inline-flex rounded-full bg-wc-accent/10 px-3 py-1 text-xs font-semibold text-wc-accent">Bucaramanga, Colombia</span>
            <h1 class="mt-4 font-display text-5xl tracking-wide text-wc-text sm:text-6xl lg:text-7xl">
                ENTRENAMIENTO<br><span class="text-gradient-accent">PRESENCIAL</span>
            </h1>
            <p class="mx-auto mt-6 max-w-2xl text-lg text-wc-text-secondary">
                Coaching 1:1 en persona. El mismo metodo basado en ciencia de WellCore, con la ventaja del acompañamiento presencial.
            </p>
            <div class="mt-8">
                <a href="{{ route('presencial.form') }}" class="btn-press pulse-glow inline-flex items-center justify-center rounded-full bg-wc-accent px-8 py-3.5 text-base font-semibold text-white shadow-lg shadow-wc-accent/20 hover:bg-wc-accent-hover">
                    Inscribirme
                    <svg class="ml-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" /></svg>
                </a>
            </div>
        </div>
    </section>

    <div class="section-divider" aria-hidden="true"></div>

    {{-- Map Placeholder --}}
    <section class="bg-wc-bg-secondary">
        <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8" data-animate="fadeIn">
            <div class="grid grid-cols-1 gap-8 lg:grid-cols-2">
                <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-8">
                    <div class="flex items-start gap-4">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-wc-accent/10">
                            <svg class="h-6 w-6 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 0 1 15 0Z" /></svg>
                        </div>
                        <div>
                            <h3 class="font-display text-xl tracking-wide text-wc-text">UBICACION</h3>
                            <p class="mt-2 text-sm text-wc-text-secondary">Bucaramanga, Santander, Colombia</p>
                            <p class="mt-1 text-sm text-wc-text-tertiary">Direccion exacta se comparte al inscribirte</p>
                            <div class="mt-4 flex items-center gap-2">
                                <span class="relative flex h-2 w-2">
                                    <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                                    <span class="relative inline-flex h-2 w-2 rounded-full bg-emerald-400"></span>
                                </span>
                                <span class="text-xs text-emerald-400">Aceptando inscripciones</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="flex items-center justify-center rounded-xl border border-wc-border bg-wc-bg-tertiary p-8">
                    <div class="text-center">
                        <svg class="mx-auto h-16 w-16 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="0.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 6.75V15m6-6v8.25m.503 3.498 4.875-2.437c.381-.19.622-.58.622-1.006V4.82c0-.836-.88-1.38-1.628-1.006l-3.869 1.934c-.317.159-.69.159-1.006 0L9.503 3.252a1.125 1.125 0 0 0-1.006 0L3.622 5.689C3.24 5.88 3 6.27 3 6.695V19.18c0 .836.88 1.38 1.628 1.006l3.869-1.934c.317-.159.69-.159 1.006 0l4.994 2.497c.317.158.69.158 1.006 0Z" /></svg>
                        <p class="mt-3 text-sm text-wc-text-tertiary">Mapa interactivo proximamente</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="section-divider" aria-hidden="true"></div>

    {{-- Info --}}
    <section class="bg-wc-bg">
        <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 gap-8 lg:grid-cols-2">
                <div data-animate="slideInLeft">
                    <h2 class="font-display text-3xl tracking-wide text-wc-text">COMO FUNCIONA</h2>
                    <div class="mt-6 space-y-4 text-wc-text-secondary">
                        <p>El plan presencial te da acceso a sesiones de entrenamiento 1:1 con tu coach en Bucaramanga. Cada sesion es personalizada y supervisada para garantizar tecnica perfecta y progresion optima.</p>
                        <p>Ademas de las sesiones presenciales, recibes tu programa completo en la plataforma WellCore para los dias que entrenas solo, mas plan nutricional y seguimiento semanal.</p>
                    </div>
                </div>
                <div class="stagger-grid grid grid-cols-2 gap-4" data-animate="slideInRight">
                    @php
                        $features = [
                            ['num' => '3-5', 'label' => 'Sesiones/semana', 'counter' => null],
                            ['num' => '60', 'label' => 'Minutos/sesion', 'counter' => '60', 'suffix' => 'min'],
                            ['num' => '1:1', 'label' => 'Coach dedicado', 'counter' => null],
                            ['num' => '24/7', 'label' => 'App + soporte', 'counter' => null],
                        ];
                    @endphp
                    @foreach($features as $f)
                        <div class="card-hover-lift rounded-xl border border-wc-border bg-wc-bg-tertiary p-5 text-center">
                            <span
                                class="counter-highlight font-data text-2xl font-bold text-wc-accent"
                                @if(!empty($f['counter'])) data-counter="{{ $f['counter'] }}" data-suffix="{{ $f['suffix'] ?? '' }}" @endif
                            >{{ $f['num'] }}</span>
                            <p class="mt-1 text-xs text-wc-text-secondary">{{ $f['label'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <div class="section-divider" aria-hidden="true"></div>

    {{-- Schedule --}}
    <section class="bg-wc-bg-tertiary">
        <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
            <h2 class="text-center font-display text-3xl tracking-wide text-wc-text" data-animate="fadeInUp">HORARIOS DISPONIBLES</h2>
            <div class="scroll-reveal mx-auto mt-10 max-w-2xl overflow-hidden rounded-xl border border-wc-border">
                <table class="w-full text-sm">
                    <thead class="bg-wc-bg-secondary">
                        <tr>
                            <th class="px-6 py-3 text-left font-semibold text-wc-text">Horario</th>
                            <th class="px-6 py-3 text-left font-semibold text-wc-text">Dias</th>
                            <th class="px-6 py-3 text-left font-semibold text-wc-text">Disponibilidad</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-wc-border">
                        <tr class="transition-colors hover:bg-wc-bg-secondary/50">
                            <td class="px-6 py-3 text-wc-text">8:00 - 10:00 AM</td>
                            <td class="px-6 py-3 text-wc-text-secondary">Lun - Vie</td>
                            <td class="px-6 py-3"><span class="pulse-glow inline-flex rounded-full bg-emerald-500/10 px-2 py-0.5 text-[10px] font-semibold text-emerald-400">Disponible</span></td>
                        </tr>
                        <tr class="transition-colors hover:bg-wc-bg-secondary/50">
                            <td class="px-6 py-3 text-wc-text">2:00 - 4:00 PM</td>
                            <td class="px-6 py-3 text-wc-text-secondary">Lun - Vie</td>
                            <td class="px-6 py-3"><span class="pulse-glow inline-flex rounded-full bg-emerald-500/10 px-2 py-0.5 text-[10px] font-semibold text-emerald-400">Disponible</span></td>
                        </tr>
                        <tr class="transition-colors hover:bg-wc-bg-secondary/50">
                            <td class="px-6 py-3 text-wc-text">Otros horarios</td>
                            <td class="px-6 py-3 text-wc-text-secondary">Lun - Vie</td>
                            <td class="px-6 py-3"><span class="inline-flex rounded-full bg-amber-500/10 px-2 py-0.5 text-[10px] font-semibold text-amber-400">Limitado</span></td>
                        </tr>
                        <tr class="transition-colors hover:bg-wc-bg-secondary/50">
                            <td class="px-6 py-3 text-wc-text">Sabados</td>
                            <td class="px-6 py-3 text-wc-text-secondary">—</td>
                            <td class="px-6 py-3"><span class="inline-flex rounded-full bg-red-500/10 px-2 py-0.5 text-[10px] font-semibold text-red-400">No disponible</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <div class="section-divider" aria-hidden="true"></div>

    {{-- Pricing --}}
    <section class="bg-wc-bg">
        <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
            <h2 class="text-center font-display text-3xl tracking-wide text-wc-text" data-animate="fadeInUp">PRECIOS</h2>
            <div class="stagger-grid mt-10 grid grid-cols-1 gap-6 sm:grid-cols-3">
                @php
                    $plans = [
                        ['name' => '3 Sesiones/Semana', 'price' => '450.000', 'features' => ['12 sesiones presenciales/mes', 'Supervision de tecnica en vivo', 'Progresiones y variaciones semanales', 'Programa personalizado en plataforma', 'Plan nutricional basico', 'Revision quincenal de progreso', 'Acceso al portal de cliente 24/7'], 'delay' => 100],
                        ['name' => '4 Sesiones/Semana', 'price' => '550.000', 'popular' => true, 'features' => ['16 sesiones presenciales/mes', 'Supervision de tecnica en vivo', 'Progresiones y variaciones semanales', 'Intensidad progresiva supervisada', 'Programa personalizado en plataforma', 'Plan nutricional completo + macros', 'Revision quincenal de progreso', 'Mediciones corporales quincenales', 'Acceso al portal de cliente 24/7'], 'delay' => 200],
                        ['name' => '5 Sesiones/Semana', 'price' => '650.000', 'features' => ['20 sesiones presenciales/mes', 'Supervision de tecnica en vivo', 'Progresiones y variaciones semanales', 'Intensidad progresiva supervisada', 'Programa personalizado en plataforma', 'Plan nutricional completo + suplementacion', 'Revision quincenal de progreso', 'Mediciones corporales quincenales', 'Acceso al portal de cliente 24/7', 'Soporte prioritario WhatsApp'], 'delay' => 300],
                    ];
                @endphp
                @foreach($plans as $plan)
                    <div
                        class="card-hover-lift {{ isset($plan['popular']) ? 'card-glow pulse-glow' : '' }} relative rounded-xl border {{ isset($plan['popular']) ? 'border-wc-accent' : 'border-wc-border' }} bg-wc-bg-tertiary p-8"
                        data-animate="scaleIn"
                        data-animate-delay="{{ $plan['delay'] }}"
                    >
                        @if(isset($plan['popular']))
                            <span class="badge-shine absolute -top-3 left-1/2 -translate-x-1/2 rounded-full bg-wc-accent px-3 py-0.5 text-[10px] font-semibold text-white">MAS POPULAR</span>
                        @endif
                        <h3 class="text-sm font-semibold text-wc-text">{{ $plan['name'] }}</h3>
                        <div class="mt-3">
                            <span class="font-data text-3xl font-bold text-wc-text">${{ $plan['price'] }}</span>
                            <span class="text-sm text-wc-text-tertiary">COP/mes</span>
                        </div>
                        <ul class="mt-6 space-y-3">
                            @foreach($plan['features'] as $feature)
                                <li class="flex items-start gap-2 text-sm text-wc-text-secondary">
                                    <svg class="mt-0.5 h-4 w-4 shrink-0 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                                    {{ $feature }}
                                </li>
                            @endforeach
                        </ul>
                        <a href="{{ route('presencial.form') }}" class="btn-press {{ isset($plan['popular']) ? 'pulse-glow' : '' }} mt-8 block rounded-lg {{ isset($plan['popular']) ? 'bg-wc-accent text-white hover:bg-wc-accent-hover' : 'border border-wc-border bg-wc-bg-secondary text-wc-text hover:bg-wc-bg' }} px-6 py-3 text-center text-sm font-medium">
                            Inscribirme
                        </a>
                    </div>
                @endforeach
            </div>
            <p class="mt-6 text-center text-xs text-wc-text-tertiary">Ubicacion: Zona norte de Bucaramanga. Direccion exacta se comparte al confirmar inscripcion.</p>
        </div>
    </section>

    <div class="section-divider" aria-hidden="true"></div>

    {{-- Final CTA --}}
    <section class="bg-wc-bg-tertiary">
        <div class="mx-auto max-w-7xl px-4 py-20 text-center sm:px-6 lg:px-8" data-animate="scaleIn">
            <div class="relative mx-auto max-w-2xl overflow-hidden rounded-2xl border border-wc-border bg-wc-bg p-10 sm:p-16">
                <div class="absolute -top-10 -right-10 h-40 w-40 rounded-full bg-wc-accent/5 blur-3xl" aria-hidden="true"></div>
                <h2 class="font-display text-3xl tracking-wide text-wc-text sm:text-4xl">ENTRENA EN PERSONA</h2>
                <p class="mt-4 text-wc-text-secondary">Cupos limitados. Solo en Bucaramanga.</p>
                <a href="{{ route('presencial.form') }}" class="btn-press pulse-glow mt-8 inline-flex items-center justify-center rounded-full bg-wc-accent px-8 py-3.5 text-base font-semibold text-white shadow-lg shadow-wc-accent/20 hover:bg-wc-accent-hover">
                    Inscribirme Ahora
                    <svg class="ml-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" /></svg>
                </a>
            </div>
        </div>
    </section>

</x-layouts.public>
