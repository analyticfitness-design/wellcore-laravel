<x-layouts.public>
    <x-slot:title>WellCore Fitness - Coaching 1:1 Basado en Ciencia</x-slot:title>
    <x-slot:description>Coaching fitness 1:1 basado en ciencia. Entrenamiento y nutricion personalizados. 94% adherencia. Sin milagros, solo resultados reales.</x-slot:description>

    {{-- RISE Banner --}}
    <div class="border-b border-wc-border bg-wc-accent/5">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-2 sm:px-6 lg:px-8">
            <p class="text-xs text-wc-text-secondary">
                <span class="font-semibold text-wc-accent">Programa RISE</span> — 30 dias de transformacion guiada.
            </p>
            <a href="{{ route('reto-rise') }}" class="rounded bg-wc-accent px-3 py-1 text-xs font-semibold text-white hover:bg-wc-accent-hover">UNIRME</a>
        </div>
    </div>

    {{-- Hero --}}
    <section class="relative overflow-hidden bg-wc-bg-tertiary">
        <div class="absolute inset-0 bg-gradient-to-br from-wc-accent/5 via-transparent to-transparent"></div>
        <div class="relative mx-auto max-w-7xl px-4 py-24 sm:px-6 sm:py-32 lg:px-8 lg:py-40">
            <div class="max-w-3xl">
                <h1 class="font-display text-5xl leading-none tracking-wide text-wc-text sm:text-6xl lg:text-8xl">
                    SIN MILAGROS,<br>
                    <span class="text-wc-text-secondary">solo</span>
                    <span class="text-wc-accent">CIENCIA</span>
                </h1>
                <p class="mt-6 max-w-xl text-lg text-wc-text-secondary">
                    Coaching 1:1 completamente personalizado. Entrenamiento y nutricion basados en evidencia real, no en modas.
                </p>
                <div class="mt-6 flex flex-wrap gap-4">
                    <div class="flex items-center gap-2 text-sm text-wc-text-secondary">
                        <span class="font-data font-bold text-wc-accent">94%</span> Adherencia
                    </div>
                    <div class="flex items-center gap-2 text-sm text-wc-text-secondary">
                        <span class="font-data font-bold text-wc-accent">1:1</span> Coaching
                    </div>
                    <div class="flex items-center gap-2 text-sm text-wc-text-secondary">
                        <span class="font-data font-bold text-wc-accent">100%</span> Personalizado
                    </div>
                </div>
                <div class="mt-8 flex flex-col gap-4 sm:flex-row">
                    <a href="{{ route('inscripcion') }}" class="inline-flex items-center justify-center rounded-lg bg-wc-accent px-8 py-3 text-base font-medium text-white hover:bg-wc-accent-hover">
                        Comenzar Ahora
                        <svg class="ml-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" /></svg>
                    </a>
                    <a href="{{ route('planes') }}" class="inline-flex items-center justify-center rounded-lg border border-wc-border bg-wc-bg-secondary px-8 py-3 text-base font-medium text-wc-text hover:bg-wc-bg-tertiary">
                        Ver Planes
                    </a>
                </div>
                <p class="mt-3 text-xs text-wc-text-tertiary">Sin tarjeta &middot; 100% sin compromiso</p>
            </div>
        </div>
    </section>

    {{-- Why WellCore --}}
    <section class="bg-wc-bg">
        <div class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
            <h2 class="font-display text-3xl tracking-wide text-wc-text sm:text-4xl">POR QUE WELLCORE</h2>
            <p class="mt-2 text-lg text-wc-text-secondary">Resultados que se sostienen.</p>
            <p class="mt-4 max-w-2xl text-sm text-wc-text-tertiary">No contamos con trucos rapidos. Contamos con metodo, seguimiento y ciencia aplicada a tu cuerpo.</p>

            {{-- Stats --}}
            <div class="mt-12 grid grid-cols-2 gap-4 sm:grid-cols-4">
                <div class="rounded-xl border border-wc-accent/30 bg-wc-accent/5 p-5 text-center">
                    <p class="font-data text-3xl font-bold text-wc-accent">94%</p>
                    <p class="mt-1 text-xs text-wc-text-secondary">Adherencia semanal</p>
                </div>
                <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5 text-center">
                    <p class="font-data text-3xl font-bold text-wc-text">20+</p>
                    <p class="mt-1 text-xs text-wc-text-secondary">Clientes activos</p>
                </div>
                <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5 text-center">
                    <p class="font-data text-3xl font-bold text-wc-text">8sem</p>
                    <p class="mt-1 text-xs text-wc-text-secondary">Resultados medibles</p>
                </div>
                <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5 text-center">
                    <p class="font-data text-3xl font-bold text-wc-text">100%</p>
                    <p class="mt-1 text-xs text-wc-text-secondary">Personalizado</p>
                </div>
            </div>

            {{-- 3 Pillars --}}
            <div class="mt-14 grid grid-cols-1 gap-8 md:grid-cols-3">
                <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-8">
                    <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-wc-accent/10">
                        <svg class="h-6 w-6 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v5.714a2.25 2.25 0 0 1-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 0 1 4.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0 1 12 15a9.065 9.065 0 0 0-6.23.693L5 14.5m14.8.8 1.402 1.402c1.232 1.232.65 3.318-1.067 3.611A48.309 48.309 0 0 1 12 21c-2.773 0-5.491-.235-8.135-.687-1.718-.293-2.3-2.379-1.067-3.61L5 14.5" /></svg>
                    </div>
                    <h3 class="mt-5 text-lg font-semibold text-wc-text">Ciencia, no intuicion</h3>
                    <p class="mt-2 text-sm text-wc-text-secondary">Cada decision de entrenamiento y nutricion esta respaldada por evidencia publicada. Sin mitos, sin dietas milagro.</p>
                </div>
                <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-8">
                    <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-wc-accent/10">
                        <svg class="h-6 w-6 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" /></svg>
                    </div>
                    <h3 class="mt-5 text-lg font-semibold text-wc-text">Atencion 1:1 real</h3>
                    <p class="mt-2 text-sm text-wc-text-secondary">Tu coach te conoce por nombre, sabe tu historial y responde tus dudas. No eres un numero en una base de datos.</p>
                </div>
                <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-8">
                    <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-wc-accent/10">
                        <svg class="h-6 w-6 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342" /></svg>
                    </div>
                    <h3 class="mt-5 text-lg font-semibold text-wc-text">Entiendes el por que</h3>
                    <p class="mt-2 text-sm text-wc-text-secondary">Te explicamos la logica detras de cada indicacion. Aprendes a entrenar y comer bien de por vida, no solo mientras tienes coach.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- How It Works --}}
    <section class="bg-wc-bg-tertiary">
        <div class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
            <h2 class="font-display text-3xl tracking-wide text-wc-text sm:text-4xl">COMO FUNCIONA</h2>
            <p class="mt-2 text-lg text-wc-text-secondary">Tu transformacion en 4 fases.</p>

            <div class="mt-12 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                @foreach([
                    ['1', 'Diagnostico', 'Semana 1', 'Evaluacion completa: historial, habitos, metas, metabolismo y estilo de vida. Nada se asume.'],
                    ['2', 'Plan personalizado', 'Semana 1-2', 'Diseno del protocolo de entrenamiento y nutricion hecho a tu medida, con la logica explicada.'],
                    ['3', 'Ejecucion', 'Semanas 2-8', 'Acompanamiento semanal con check-ins, ajustes en tiempo real y soporte directo de tu coach.'],
                    ['4', 'Evolucion', 'Continuo', 'Ajustes basados en datos reales de tu progreso. El plan evoluciona contigo permanentemente.'],
                ] as [$num, $title, $time, $desc])
                <div class="rounded-xl border border-wc-border bg-wc-bg p-6">
                    <div class="flex items-center gap-3">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-wc-accent/10">
                            <span class="font-data text-sm font-bold text-wc-accent">{{ $num }}</span>
                        </div>
                        <span class="text-xs font-medium text-wc-text-tertiary">{{ $time }}</span>
                    </div>
                    <h3 class="mt-4 text-base font-semibold text-wc-text">{{ $title }}</h3>
                    <p class="mt-2 text-sm text-wc-text-secondary">{{ $desc }}</p>
                </div>
                @endforeach
            </div>

            <div class="mt-8 text-center">
                <a href="{{ route('proceso') }}" class="inline-flex items-center gap-2 text-sm font-medium text-wc-accent hover:text-wc-accent-hover">
                    Ver proceso completo
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" /></svg>
                </a>
            </div>
        </div>
    </section>

    {{-- Testimonials --}}
    <section class="bg-wc-bg">
        <div class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
            <h2 class="font-display text-3xl tracking-wide text-wc-text sm:text-4xl">RESULTADOS REALES</h2>
            <p class="mt-2 text-lg text-wc-text-secondary">Sin filtros, sin edicion. Clientes reales con planes WellCore.</p>

            <div class="mt-12 grid grid-cols-1 gap-8 md:grid-cols-3">
                @foreach([
                    ['ML', 'Maria L.', '-12 kg / 4 meses', 'Plan Metodo', 'Por primera vez entendi por que hacia cada cosa. Eso lo cambio todo.'],
                    ['JC', 'Juan C.', '+8 kg musculo / 6 meses', 'Plan Elite', 'Sin suplementos raros, solo entrenamiento inteligente y buena nutricion.'],
                    ['AP', 'Andrea P.', '-9 kg grasa +5 kg musculo', '12 semanas', 'La guia de habitos fue lo que cambio todo.'],
                ] as [$initials, $name, $result, $plan, $quote])
                <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-8">
                    <div class="flex gap-1">
                        @for ($i = 0; $i < 5; $i++)
                            <svg class="h-4 w-4 text-wc-accent" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        @endfor
                    </div>
                    <p class="mt-4 text-sm text-wc-text-secondary">"{{ $quote }}"</p>
                    <div class="mt-6 flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-wc-accent/10">
                            <span class="text-sm font-semibold text-wc-accent">{{ $initials }}</span>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-wc-text">{{ $name }}</p>
                            <p class="text-xs text-wc-text-tertiary">{{ $result }} &middot; {{ $plan }}</p>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- FAQ --}}
    <section class="bg-wc-bg-tertiary" x-data="{ active: null }">
        <div class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
            <h2 class="font-display text-3xl tracking-wide text-wc-text sm:text-4xl">PREGUNTAS FRECUENTES</h2>

            <div class="mt-12 max-w-3xl space-y-3">
                @foreach([
                    ['Necesito experiencia previa?', 'No. Trabajamos con todos los niveles, desde personas que nunca han hecho ejercicio hasta atletas avanzados. El plan se construye desde cero segun tu historial, tu condicion actual y tus metas especificas.'],
                    ['Como funciona el seguimiento?', 'Cada semana nos envias tu check-in: peso, fotos opcionales, nivel de energia, como se sintio el entrenamiento y cualquier novedad. Con esa informacion ajustamos el plan para la semana siguiente.'],
                    ['Necesito comprar suplementos?', 'No. WellCore no vende suplementos ni gana comision por recomendarlos. Los resultados se logran con entrenamiento, nutricion y descanso, no con polvos magicos.'],
                    ['Puedo cancelar cuando quiera?', 'Si. No hay contratos de permanencia ni cargos ocultos. Cancelas con un mensaje antes de tu siguiente ciclo de facturacion.'],
                    ['En cuanto tiempo vere resultados?', 'Depende de tu punto de partida y tu consistencia. En general: 4-6 semanas para notar cambios en energia y fuerza; 8-12 semanas para cambios visuales claros.'],
                    ['Puedo entrenar en casa?', 'Si. El programa se adapta completamente al equipamiento disponible: gimnasio completo, home gym con mancuernas o solo peso corporal.'],
                ] as $index => [$question, $answer])
                <div class="rounded-xl border border-wc-border bg-wc-bg">
                    <button x-on:click="active = active === {{ $index }} ? null : {{ $index }}" class="flex w-full items-center justify-between px-6 py-4 text-left">
                        <span class="text-sm font-semibold text-wc-text">{{ $question }}</span>
                        <svg class="h-5 w-5 shrink-0 text-wc-text-tertiary transition-transform duration-200" :class="{ 'rotate-45': active === {{ $index }} }" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    </button>
                    <div x-show="active === {{ $index }}" x-collapse x-cloak>
                        <div class="border-t border-wc-border px-6 pb-4 pt-3">
                            <p class="text-sm leading-relaxed text-wc-text-secondary">{{ $answer }}</p>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <div class="mt-8">
                <a href="{{ route('faq') }}" class="inline-flex items-center gap-2 text-sm font-medium text-wc-accent hover:text-wc-accent-hover">
                    Ver todas las preguntas
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" /></svg>
                </a>
            </div>
        </div>
    </section>

    {{-- Final CTA --}}
    <section class="bg-wc-bg">
        <div class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
            <div class="relative overflow-hidden rounded-2xl border border-wc-border bg-wc-bg-tertiary p-10 sm:p-16">
                <div class="absolute inset-0 bg-gradient-to-br from-wc-accent/5 via-transparent to-transparent"></div>
                <div class="relative text-center">
                    <p class="text-xs font-semibold uppercase tracking-widest text-wc-accent">Lanzamiento 2026 — Acceso Fundador</p>
                    <h2 class="mt-4 font-display text-3xl tracking-wide text-wc-text sm:text-5xl">LISTO PARA TRANSFORMARTE?</h2>
                    <p class="mx-auto mt-4 max-w-md text-wc-text-secondary">Sin compromiso. Sin contratos. Solo resultados.</p>
                    <div class="mt-8 flex flex-col items-center justify-center gap-4 sm:flex-row">
                        <a href="{{ route('planes') }}" class="inline-flex items-center justify-center rounded-lg bg-wc-accent px-8 py-3 text-base font-medium text-white hover:bg-wc-accent-hover">
                            Ver Planes
                            <svg class="ml-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" /></svg>
                        </a>
                        <a href="{{ route('inscripcion') }}" class="inline-flex items-center justify-center rounded-lg border border-wc-border px-8 py-3 text-base font-medium text-wc-text-secondary hover:text-wc-text">
                            Consulta Gratuita
                        </a>
                    </div>
                    <div class="mt-8 flex flex-wrap items-center justify-center gap-x-6 gap-y-2 text-xs text-wc-text-tertiary">
                        <span>Seguimiento 1:1 real</span>
                        <span class="hidden sm:inline">&middot;</span>
                        <span>Sin contratos forzados</span>
                        <span class="hidden sm:inline">&middot;</span>
                        <span>Acceso fundador</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

</x-layouts.public>
