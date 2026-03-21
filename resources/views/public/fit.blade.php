<x-layouts.public>
    <x-slot:title>Coach Silvia - WellCore Fitness</x-slot:title>

    {{-- Hero --}}
    <section class="relative overflow-hidden bg-wc-bg-tertiary">
        <div class="absolute inset-0 bg-gradient-to-br from-emerald-500/5 via-transparent to-transparent"></div>
        <div class="relative mx-auto max-w-7xl px-4 py-24 sm:px-6 sm:py-32 lg:px-8">
            <div class="grid grid-cols-1 items-center gap-12 lg:grid-cols-2">
                <div>
                    <span class="inline-flex rounded-full bg-emerald-500/10 px-3 py-1 text-xs font-semibold text-emerald-400">Coach WellCore</span>
                    <h1 class="mt-4 font-display text-5xl leading-none tracking-wide text-wc-text sm:text-6xl lg:text-7xl">
                        SILVIA<br>
                        <span class="text-emerald-400">MARTINEZ</span>
                    </h1>
                    <p class="mt-6 max-w-lg text-lg text-wc-text-secondary">
                        Especialista en fitness femenino y recomposicion corporal. Te ayudo a construir fuerza, confianza y habitos que duran.
                    </p>
                    <div class="mt-6 flex flex-wrap gap-6 text-sm text-wc-text-secondary">
                        <div class="flex items-center gap-2">
                            <span class="font-data text-2xl font-bold text-emerald-400">6+</span>
                            <span>anos de<br>experiencia</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="font-data text-2xl font-bold text-emerald-400">120+</span>
                            <span>clientas<br>transformadas</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="font-data text-2xl font-bold text-emerald-400">96%</span>
                            <span>tasa de<br>adherencia</span>
                        </div>
                    </div>
                    <div class="mt-8 flex flex-wrap gap-4">
                        <a href="{{ route('inscripcion') }}" class="rounded-full bg-emerald-500 px-6 py-3.5 text-sm font-semibold text-white shadow-lg shadow-emerald-500/20 hover:bg-emerald-600">
                            Entrena Conmigo
                        </a>
                        <a href="https://wa.me/573001234567?text=Hola%20Silvia%2C%20quiero%20informacion" target="_blank" class="rounded-full px-6 py-3.5 text-sm font-semibold text-wc-text hover:bg-wc-bg-secondary">
                            WhatsApp
                        </a>
                    </div>
                </div>
                <div class="flex justify-center">
                    <div class="relative h-80 w-80 overflow-hidden rounded-2xl border border-wc-border bg-gradient-to-br from-emerald-500/20 to-emerald-500/5 lg:h-96 lg:w-96">
                        <div class="absolute inset-0 flex items-center justify-center">
                            <span class="font-display text-[120px] text-emerald-400/20">SM</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Bio --}}
    <section class="bg-wc-bg">
        <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-3xl">
                <h2 class="font-display text-3xl tracking-wide text-wc-text">MI HISTORIA</h2>
                <div class="mt-6 space-y-4 text-wc-text-secondary">
                    <p>
                        Comence mi camino en el fitness hace mas de 6 anos, cuando descubri que el entrenamiento de fuerza
                        era mucho mas que estetica — era una herramienta de empoderamiento. Desde entonces, me he
                        especializado en ayudar a mujeres a construir una relacion saludable con el ejercicio y la nutricion.
                    </p>
                    <p>
                        Soy licenciada en Ciencias del Deporte con especializacion en Fisiologia del Ejercicio.
                        Certificada NSCA-CPT y Precision Nutrition Level 1. Mi enfoque combina periodizacion
                        inteligente, nutricion basada en evidencia y un acompanamiento constante que se adapta a tu vida real.
                    </p>
                    <p>
                        No creo en dietas restrictivas ni en entrenar hasta el agotamiento. Creo en procesos sostenibles
                        que te permitan disfrutar del camino mientras construyes la mejor version de ti misma.
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- Specialties --}}
    <section class="bg-wc-bg-tertiary">
        <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
            <h2 class="text-center font-display text-3xl tracking-wide text-wc-text">ESPECIALIDADES</h2>
            <div class="mt-12 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @php
                    $specialties = [
                        ['icon' => 'M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z', 'title' => 'Fitness Femenino', 'desc' => 'Programas disenados especificamente para la fisiologia femenina, respetando el ciclo menstrual y las necesidades hormonales.'],
                        ['icon' => 'M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z', 'title' => 'Recomposicion Corporal', 'desc' => 'Perdida de grasa y ganancia muscular simultanea. Estrategias de nutricion y entrenamiento para cambiar tu composicion.'],
                        ['icon' => 'M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z', 'title' => 'Habitos Sostenibles', 'desc' => 'No dietas extremas. Construimos habitos nutricionales que puedas mantener a largo plazo sin sentir restriccion.'],
                        ['icon' => 'M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z', 'title' => 'Entrenamiento de Fuerza', 'desc' => 'Periodizacion progresiva para que ganes fuerza real. Tecnica, sobrecarga y recuperacion inteligente.'],
                        ['icon' => 'M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.455 2.456L21.75 6l-1.036.259a3.375 3.375 0 0 0-2.455 2.456ZM16.894 20.567 16.5 21.75l-.394-1.183a2.25 2.25 0 0 0-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 0 0 1.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 0 0 1.423 1.423l1.183.394-1.183.394a2.25 2.25 0 0 0-1.423 1.423Z', 'title' => 'Mindset y Motivacion', 'desc' => 'Trabajo en mentalidad para superar barreras, construir disciplina y mantener la consistencia.'],
                        ['icon' => 'M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z', 'title' => 'Flexibilidad Horaria', 'desc' => 'Programas adaptados a tu rutina real. No importa si eres mama, profesional o estudiante — se adapta.'],
                    ];
                @endphp
                @foreach($specialties as $spec)
                    <div class="rounded-xl border border-wc-border bg-wc-bg p-6">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-500/10">
                            <svg class="h-5 w-5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $spec['icon'] }}" />
                            </svg>
                        </div>
                        <h3 class="mt-4 text-sm font-semibold text-wc-text">{{ $spec['title'] }}</h3>
                        <p class="mt-2 text-sm text-wc-text-secondary">{{ $spec['desc'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Testimonials --}}
    <section class="bg-wc-bg">
        <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
            <h2 class="text-center font-display text-3xl tracking-wide text-wc-text">TESTIMONIOS</h2>
            <div class="mt-12 grid grid-cols-1 gap-6 md:grid-cols-3">
                @php
                    $testimonials = [
                        ['name' => 'Camila R.', 'months' => '8 meses', 'text' => 'Silvia cambio mi relacion con el entrenamiento. Antes odiaba el gym, ahora es mi momento favorito del dia. Perdi 12kg de grasa y gane confianza en mi misma.'],
                        ['name' => 'Laura M.', 'months' => '5 meses', 'text' => 'Lo que mas valoro es que Silvia entiende la vida real. No me juzga si un dia no puedo entrenar, me ayuda a adaptar. Mi fuerza se duplico en 5 meses.'],
                        ['name' => 'Valentina G.', 'months' => '1 ano', 'text' => 'Empece sin saber nada de nutricion. Hoy manejo mis macros con confianza y mi rendimiento en el gym es otro nivel. La mejor inversion que he hecho.'],
                    ];
                @endphp
                @foreach($testimonials as $t)
                    <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-6">
                        <div class="flex items-center gap-1 text-emerald-400">
                            @for($i = 0; $i < 5; $i++)
                                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                            @endfor
                        </div>
                        <p class="mt-4 text-sm text-wc-text-secondary italic">"{{ $t['text'] }}"</p>
                        <div class="mt-4 flex items-center gap-3">
                            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-emerald-500/20 text-xs font-semibold text-emerald-400">
                                {{ substr($t['name'], 0, 1) }}
                            </div>
                            <div>
                                <p class="text-sm font-medium text-wc-text">{{ $t['name'] }}</p>
                                <p class="text-xs text-wc-text-tertiary">{{ $t['months'] }} con Silvia</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- CTA --}}
    <section class="bg-wc-bg-tertiary">
        <div class="mx-auto max-w-7xl px-4 py-16 text-center sm:px-6 lg:px-8">
            <h2 class="font-display text-3xl tracking-wide text-wc-text sm:text-4xl">LISTA PARA EMPEZAR?</h2>
            <p class="mx-auto mt-4 max-w-lg text-wc-text-secondary">
                Tu proceso empieza con una decision. Elige el plan que se adapte a ti y comienza tu transformacion.
            </p>
            <div class="mt-8 flex flex-wrap justify-center gap-4">
                <a href="{{ route('inscripcion') }}" class="rounded-lg bg-emerald-500 px-8 py-3 text-base font-medium text-white hover:bg-emerald-600">
                    Inscribirme
                </a>
                <a href="{{ route('planes') }}" class="rounded-full px-8 py-3.5 text-base font-semibold text-wc-text hover:bg-wc-bg-secondary">
                    Ver Planes
                </a>
            </div>
        </div>
    </section>
</x-layouts.public>
