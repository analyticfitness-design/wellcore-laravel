<x-layouts.public>
    <x-slot:title>Planes y Precios - WellCore Fitness</x-slot:title>
    <x-slot:description>Planes de coaching fitness personalizado desde $299.000 COP/mes. Esencial, Metodo y Elite. Sin contratos, cancela cuando quieras.</x-slot:description>

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
                ELIGE TU <span class="text-gradient-accent">PLAN</span>
            </h1>
            <p class="mx-auto mt-4 max-w-xl text-lg text-wc-text-secondary">Sin contratos de permanencia. Cancela cuando quieras. Invierte en lo que funciona.</p>
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
                <h2 class="font-display text-3xl tracking-wide text-wc-text sm:text-4xl">NUESTROS PLANES</h2>
                <p class="mx-auto mt-3 max-w-lg text-wc-text-secondary">Cada plan incluye acceso completo a la plataforma. Elige el nivel de acompañamiento que necesitas.</p>
            </div>

            {{-- Billing Toggle --}}
            <div class="mb-12 flex items-center justify-center">
                <div class="inline-flex rounded-full border border-wc-border bg-wc-bg-secondary p-1 gap-1">
                    <button @click="billing = 'mensual'"
                            :class="billing === 'mensual' ? 'bg-wc-accent text-white shadow-md' : 'text-wc-text-secondary hover:text-wc-text'"
                            class="rounded-full px-5 py-2 text-sm font-medium transition-all duration-200">
                        Mensual
                    </button>
                    <button @click="billing = 'trimestral'"
                            :class="billing === 'trimestral' ? 'bg-wc-accent text-white shadow-md' : 'text-wc-text-secondary hover:text-wc-text'"
                            class="rounded-full px-5 py-2 text-sm font-medium transition-all duration-200">
                        Trimestral
                        <span class="ml-1 rounded-full bg-wc-accent/20 px-1.5 py-0.5 text-xs font-bold text-wc-accent"
                              :class="billing === 'trimestral' ? 'bg-white/20 text-white' : ''">-10%</span>
                    </button>
                    <button @click="billing = 'anual'"
                            :class="billing === 'anual' ? 'bg-wc-accent text-white shadow-md' : 'text-wc-text-secondary hover:text-wc-text'"
                            class="rounded-full px-5 py-2 text-sm font-medium transition-all duration-200">
                        Anual
                        <span class="ml-1 rounded-full bg-wc-accent/20 px-1.5 py-0.5 text-xs font-bold text-wc-accent"
                              :class="billing === 'anual' ? 'bg-white/20 text-white' : ''">-20%</span>
                    </button>
                </div>
            </div>

            @php
                $allFeatures = [
                    'Entrenamiento personalizado desde cero',
                    'Portal de cliente 24/7',
                    'Evaluacion inicial + diagnostico',
                    'Biblioteca de ejercicios con video',
                    'Seguimiento de metricas y progreso',
                    'Mediciones corporales + fotos',
                    'Comunidad y chat grupal',
                    'Ajuste mensual del programa',
                    'Soporte por mensaje — respuesta 48h',
                    'Nutricion 100% personalizada',
                    'Macros y calorias ajustadas',
                    'Recetas adaptadas a preferencias',
                    'Guia de habitos y estilo de vida',
                    'Seguimiento de sueño y estres',
                    'Reporte mensual de progreso',
                    'Ajuste quincenal del programa',
                    'Soporte — respuesta 24h',
                    'Check-in semanal en vivo',
                    'Videollamada mensual',
                    'Check-in semanal dedicado',
                    'Videollamada mensual de revision',
                    'Soporte prioritario — respuesta 8h',
                    'Ajuste semanal del programa',
                    'Analisis composicion corporal',
                    'Estrategia de suplementacion',
                    'Ciclo hormonal personalizado',
                    'Bloodwork — analisis laboratorio',
                    'Plan de viaje y contingencia',
                    'Linea directa WhatsApp con coach',
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
                        <h3 class="text-center font-display text-2xl tracking-wide text-wc-text">ESENCIAL</h3>
                        <p class="mt-2 text-center text-sm text-wc-text-secondary">Entrena con proposito. Primer paso real hacia resultados medibles.</p>
                    </div>

                    <div class="relative text-center">
                        <span class="font-data text-5xl font-bold text-wc-text" x-text="esencial"></span>
                        <span class="ml-1 text-sm text-wc-text-tertiary">COP/mes</span>
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
                                Ahorro: <span class="ml-1" x-text="savingsEsencial"></span>
                            </span>
                        </div>
                    </div>

                    <a href="{{ route('pagar') }}?plan=esencial"
                       class="btn-press mt-6 flex w-full items-center justify-center rounded-full border border-wc-border bg-wc-bg px-6 py-3 text-sm font-semibold text-wc-text transition hover:border-wc-accent hover:text-wc-accent">
                        Comenzar Esencial
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
                        <span class="badge-shine rounded-full bg-wc-accent px-5 py-1 text-xs font-bold tracking-wide text-white shadow-md">MEJOR VALOR</span>
                    </div>

                    <div class="mb-6">
                        <h3 class="text-center font-display text-2xl tracking-wide text-wc-text">METODO</h3>
                        <p class="mt-2 text-center text-sm text-wc-text-secondary">Entrenamiento + nutricion + habitos. El sistema completo.</p>
                        <p class="mt-1 text-center text-xs font-medium text-wc-accent">Elegido por el +60% de nuestros clientes</p>
                    </div>

                    <div class="relative text-center">
                        <span class="font-data text-5xl font-bold text-wc-accent" x-text="metodo"></span>
                        <span class="ml-1 text-sm text-wc-text-tertiary">COP/mes</span>
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
                                Ahorro: <span class="ml-1" x-text="savingsMetodo"></span>
                            </span>
                        </div>
                    </div>

                    <a href="{{ route('pagar') }}?plan=metodo"
                       class="btn-press pulse-glow mt-6 flex w-full items-center justify-center rounded-full bg-wc-accent px-6 py-3 text-sm font-bold text-white shadow-md transition hover:bg-wc-accent-hover hover:shadow-lg">
                        Comenzar Metodo
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
                        <span class="inline-block rounded-full border border-wc-accent/30 bg-wc-accent/10 px-4 py-1 text-xs font-bold tracking-wide text-wc-accent">SOLO 5 CUPOS</span>
                    </div>

                    <div class="mb-6">
                        <h3 class="text-center font-display text-2xl tracking-wide text-wc-text">ELITE</h3>
                        <p class="mt-2 text-center text-sm text-wc-text-secondary">Atencion total. Resultados sin excusas. Para quienes exigen lo mejor.</p>
                    </div>

                    <div class="relative text-center">
                        <span class="font-data text-5xl font-bold text-wc-text" x-text="elite"></span>
                        <span class="ml-1 text-sm text-wc-text-tertiary">COP/mes</span>
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
                                Ahorro: <span class="ml-1" x-text="savingsElite"></span>
                            </span>
                        </div>
                    </div>

                    <a href="{{ route('pagar') }}?plan=elite"
                       class="btn-press mt-6 flex w-full items-center justify-center rounded-full border border-wc-border bg-wc-bg px-6 py-3 text-sm font-semibold text-wc-text transition hover:border-wc-accent hover:text-wc-accent">
                        Comenzar Elite
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
                    Pago trimestral
                </div>
                <div class="flex items-center gap-2">
                    <span class="rounded-full bg-wc-accent/10 px-3 py-1 text-xs font-bold text-wc-accent">-20%</span>
                    Pago anual
                </div>
                <div class="flex items-center gap-2">
                    <svg class="h-4 w-4 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" /></svg>
                    Garantia 7 dias
                </div>
            </div>
        </div>
    </section>

    <div class="section-divider"></div>

    {{-- Trust --}}
    <section class="bg-wc-bg-tertiary">
        <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8" data-animate="fadeInUp">
            <div class="mb-10 text-center">
                <h2 class="font-display text-3xl tracking-wide text-wc-text sm:text-4xl">SIN LETRA PEQUENA</h2>
                <p class="mx-auto mt-3 max-w-md text-wc-text-secondary">Transparencia total. Asi trabajamos.</p>
            </div>
            <div class="grid grid-cols-2 gap-6 sm:grid-cols-4">
                <div class="scroll-reveal text-center">
                    <p class="counter-highlight font-data text-3xl font-bold text-wc-accent" data-counter="0">0</p>
                    <p class="mt-1 text-sm text-wc-text-secondary">Contratos largo plazo</p>
                </div>
                <div class="scroll-reveal text-center">
                    <p class="counter-highlight font-data text-3xl font-bold text-wc-accent" data-counter="0">0</p>
                    <p class="mt-1 text-sm text-wc-text-secondary">Suplementos obligatorios</p>
                </div>
                <div class="scroll-reveal text-center">
                    <p class="counter-highlight font-data text-3xl font-bold text-wc-accent">SSL</p>
                    <p class="mt-1 text-sm text-wc-text-secondary">Pago seguro Wompi</p>
                </div>
                <div class="scroll-reveal text-center">
                    <p class="counter-highlight font-data text-3xl font-bold text-wc-accent">24h</p>
                    <p class="mt-1 text-sm text-wc-text-secondary">Cancela sin penalidad</p>
                </div>
            </div>
            <p class="mt-8 text-center text-xs text-wc-text-tertiary">Cancelacion sin penalizacion. Reembolsos evaluados antes de entregar el plan.</p>
        </div>
    </section>

    <div class="section-divider"></div>

    {{-- FAQ --}}
    <section class="bg-wc-bg">
        <div class="mx-auto max-w-3xl px-4 py-16 sm:px-6 lg:px-8" data-animate="fadeInUp">
            <div class="mb-10 text-center">
                <h2 class="font-display text-3xl tracking-wide text-wc-text sm:text-4xl">PREGUNTAS FRECUENTES</h2>
                <p class="mx-auto mt-3 max-w-md text-wc-text-secondary">Resolvemos tus dudas antes de empezar.</p>
            </div>

            <div class="divide-y divide-wc-border" x-data="{ open: null }">
                @foreach([
                    ['question' => 'Puedo cambiar de plan despues?', 'answer' => 'Si. Puedes subir o bajar de plan en cualquier momento. El cambio se aplica en tu siguiente ciclo de facturacion.'],
                    ['question' => 'Hay algun contrato de permanencia?', 'answer' => 'No. Todos los planes son mes a mes. Cancela cuando quieras sin penalizaciones ni cargos extra.'],
                    ['question' => 'Que metodos de pago aceptan?', 'answer' => 'Aceptamos tarjeta de credito, debito, PSE y Nequi a traves de Wompi, nuestra pasarela de pago segura.'],
                    ['question' => 'Que pasa si no veo resultados?', 'answer' => 'Ofrecemos garantia de 7 dias. Si no estas satisfecho, te devolvemos el dinero sin preguntas.'],
                    ['question' => 'Necesito equipo de gimnasio?', 'answer' => 'Depende. Adaptamos tu plan a lo que tengas disponible: gimnasio completo, equipo en casa, o sin equipo.'],
                    ['question' => 'Cuanto dura cada sesion de entrenamiento?', 'answer' => 'Entre 45 y 75 minutos dependiendo de tu plan y nivel. Todo esta disenado para ser eficiente y efectivo.'],
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
                    <h2 class="font-display text-3xl tracking-wide text-wc-text sm:text-4xl">TIENES DUDAS?</h2>
                    <p class="mx-auto mt-4 max-w-md text-wc-text-secondary">Un sistema completo de entrenamiento, nutricion y habitos por menos de $13.000 al dia.</p>
                    <div class="mt-8 flex flex-col items-center justify-center gap-4 sm:flex-row">
                        <a href="{{ route('inscripcion') }}" class="btn-press pulse-glow inline-flex items-center justify-center rounded-full bg-wc-accent px-8 py-3 text-base font-semibold text-white shadow-md transition hover:bg-wc-accent-hover hover:shadow-lg">Comenzar Ahora</a>
                        <a href="{{ route('faq') }}" class="btn-press inline-flex items-center justify-center rounded-full border border-wc-border px-8 py-3 text-base font-medium text-wc-text-secondary transition hover:border-wc-accent hover:text-wc-accent">Ver FAQ completo</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

</x-layouts.public>
