<x-layouts.public>
    <x-slot:title>WellCore Fitness - Tu Mejor Version Empieza Aqui</x-slot:title>

    {{-- Hero Section --}}
    <section class="relative overflow-hidden bg-wc-bg-tertiary">
        <div class="absolute inset-0 bg-gradient-to-b from-wc-accent/5 to-transparent"></div>
        <div class="relative mx-auto max-w-7xl px-4 py-24 sm:px-6 sm:py-32 lg:px-8 lg:py-40">
            <div class="max-w-3xl">
                <h1 class="font-display text-5xl leading-none tracking-wide text-wc-text sm:text-6xl lg:text-8xl">
                    TU MEJOR VERSION<br>
                    <span class="text-wc-accent">EMPIEZA AQUI</span>
                </h1>
                <p class="mt-6 max-w-xl text-lg text-wc-text-secondary">
                    Coaching fitness basado en ciencia, no en tendencias. Entrenamiento personalizado, nutricion
                    estructurada y seguimiento constante para resultados reales y sostenibles.
                </p>
                <div class="mt-8 flex flex-col gap-4 sm:flex-row">
                    <a href="{{ route('planes') }}" class="inline-flex items-center justify-center rounded-lg bg-wc-accent px-6 py-3 text-base font-medium text-white hover:bg-wc-accent-hover">
                        Ver Planes
                        <svg class="ml-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                        </svg>
                    </a>
                    <a href="{{ route('nosotros') }}" class="inline-flex items-center justify-center rounded-lg border border-wc-border bg-wc-bg-secondary px-6 py-3 text-base font-medium text-wc-text hover:bg-wc-bg-tertiary">
                        Conocer mas
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- Stats Bar --}}
    <section class="border-y border-wc-border bg-wc-bg">
        <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 gap-8 sm:grid-cols-3">
                <div class="text-center">
                    <p class="font-data text-4xl font-bold text-wc-accent">200+</p>
                    <p class="mt-1 text-sm font-medium text-wc-text-secondary">Clientes activos</p>
                </div>
                <div class="text-center">
                    <p class="font-data text-4xl font-bold text-wc-accent">5</p>
                    <p class="mt-1 text-sm font-medium text-wc-text-secondary">Planes disponibles</p>
                </div>
                <div class="text-center">
                    <p class="font-data text-4xl font-bold text-wc-accent">100%</p>
                    <p class="mt-1 text-sm font-medium text-wc-text-secondary">Coaching personalizado</p>
                </div>
            </div>
        </div>
    </section>

    {{-- Features Grid --}}
    <section class="bg-wc-bg-tertiary">
        <div class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
            <div class="text-center">
                <h2 class="font-display text-3xl tracking-wide text-wc-text sm:text-4xl">NUESTRO METODO</h2>
                <p class="mt-4 text-wc-text-secondary">Un enfoque integral basado en tres pilares fundamentales.</p>
            </div>

            <div class="mt-14 grid grid-cols-1 gap-8 md:grid-cols-3">
                {{-- Entrenamiento --}}
                <div class="rounded-xl border border-wc-border bg-wc-bg p-8">
                    <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-wc-accent/10">
                        <svg class="h-6 w-6 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" />
                        </svg>
                    </div>
                    <h3 class="mt-5 text-lg font-semibold text-wc-text">Entrenamiento</h3>
                    <p class="mt-2 text-sm text-wc-text-secondary">
                        Programacion periodizada y adaptada a tus objetivos. Cada rutina esta disenada con base en
                        principios de hipertrofia, fuerza y rendimiento funcional.
                    </p>
                </div>

                {{-- Nutricion --}}
                <div class="rounded-xl border border-wc-border bg-wc-bg p-8">
                    <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-wc-accent/10">
                        <svg class="h-6 w-6 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v17.25m0 0c-1.472 0-2.882.265-4.185.75M12 20.25c1.472 0 2.882.265 4.185.75M18.75 4.97A48.416 48.416 0 0012 4.5c-2.291 0-4.545.16-6.75.47m13.5 0c1.01.143 2.01.317 3 .52m-3-.52l2.62 10.726c.122.499-.106 1.028-.589 1.202a5.988 5.988 0 01-2.031.352 5.988 5.988 0 01-2.031-.352c-.483-.174-.711-.703-.59-1.202L18.75 4.971zm-16.5.52c.99-.203 1.99-.377 3-.52m0 0l2.62 10.726c.122.499-.106 1.028-.589 1.202a5.989 5.989 0 01-2.031.352 5.989 5.989 0 01-2.031-.352c-.483-.174-.711-.703-.59-1.202L5.25 4.971z" />
                        </svg>
                    </div>
                    <h3 class="mt-5 text-lg font-semibold text-wc-text">Nutricion</h3>
                    <p class="mt-2 text-sm text-wc-text-secondary">
                        Plan nutricional calculado segun tu metabolismo, composicion corporal y estilo de vida.
                        Sin dietas restrictivas, con estrategias sostenibles.
                    </p>
                </div>

                {{-- Seguimiento --}}
                <div class="rounded-xl border border-wc-border bg-wc-bg p-8">
                    <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-wc-accent/10">
                        <svg class="h-6 w-6 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                        </svg>
                    </div>
                    <h3 class="mt-5 text-lg font-semibold text-wc-text">Seguimiento</h3>
                    <p class="mt-2 text-sm text-wc-text-secondary">
                        Monitoreo continuo de tu progreso con metricas reales. Ajustes semanales basados en datos,
                        no en suposiciones.
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- Testimonials --}}
    <section class="bg-wc-bg">
        <div class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
            <div class="text-center">
                <h2 class="font-display text-3xl tracking-wide text-wc-text sm:text-4xl">RESULTADOS REALES</h2>
                <p class="mt-4 text-wc-text-secondary">Lo que dicen nuestros clientes.</p>
            </div>

            <div class="mt-14 grid grid-cols-1 gap-8 md:grid-cols-3">
                <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-8">
                    <div class="flex gap-1">
                        @for ($i = 0; $i < 5; $i++)
                            <svg class="h-4 w-4 text-wc-accent" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        @endfor
                    </div>
                    <p class="mt-4 text-sm text-wc-text-secondary">
                        "El enfoque basado en datos me ayudo a entender mi cuerpo de una manera completamente nueva.
                        Resultados consistentes en 3 meses."
                    </p>
                    <div class="mt-6 flex items-center gap-3">
                        <div class="h-10 w-10 rounded-full bg-wc-accent/10 flex items-center justify-center">
                            <span class="text-sm font-semibold text-wc-accent">CA</span>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-wc-text">Carlos A.</p>
                            <p class="text-xs text-wc-text-tertiary">Plan Elite - 6 meses</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-8">
                    <div class="flex gap-1">
                        @for ($i = 0; $i < 5; $i++)
                            <svg class="h-4 w-4 text-wc-accent" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        @endfor
                    </div>
                    <p class="mt-4 text-sm text-wc-text-secondary">
                        "La combinacion de entrenamiento y nutricion personalizada hizo toda la diferencia. El seguimiento
                        semanal mantiene la motivacion."
                    </p>
                    <div class="mt-6 flex items-center gap-3">
                        <div class="h-10 w-10 rounded-full bg-wc-accent/10 flex items-center justify-center">
                            <span class="text-sm font-semibold text-wc-accent">MR</span>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-wc-text">Maria R.</p>
                            <p class="text-xs text-wc-text-tertiary">Plan Metodo - 4 meses</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-8">
                    <div class="flex gap-1">
                        @for ($i = 0; $i < 5; $i++)
                            <svg class="h-4 w-4 text-wc-accent" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        @endfor
                    </div>
                    <p class="mt-4 text-sm text-wc-text-secondary">
                        "Llevo un ano con WellCore y no volveria atras. El programa RISE fue un antes y despues en mi
                        composicion corporal."
                    </p>
                    <div class="mt-6 flex items-center gap-3">
                        <div class="h-10 w-10 rounded-full bg-wc-accent/10 flex items-center justify-center">
                            <span class="text-sm font-semibold text-wc-accent">DL</span>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-wc-text">Daniel L.</p>
                            <p class="text-xs text-wc-text-tertiary">Programa RISE - 12 semanas</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- CTA Section --}}
    <section class="bg-wc-bg-tertiary">
        <div class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
            <div class="relative overflow-hidden rounded-2xl border border-wc-border bg-wc-bg p-10 sm:p-16">
                <div class="absolute inset-0 bg-gradient-to-br from-wc-accent/5 via-transparent to-transparent"></div>
                <div class="relative text-center">
                    <h2 class="font-display text-3xl tracking-wide text-wc-text sm:text-5xl">COMIENZA HOY</h2>
                    <p class="mx-auto mt-4 max-w-lg text-wc-text-secondary">
                        El mejor momento para empezar fue ayer. El segundo mejor momento es ahora. Elige tu plan
                        y comienza tu transformacion.
                    </p>
                    <div class="mt-8 flex flex-col items-center justify-center gap-4 sm:flex-row">
                        <a href="{{ route('planes') }}" class="inline-flex items-center justify-center rounded-lg bg-wc-accent px-8 py-3 text-base font-medium text-white hover:bg-wc-accent-hover">
                            Ver Planes
                        </a>
                        <a href="{{ route('faq') }}" class="inline-flex items-center justify-center rounded-lg border border-wc-border px-8 py-3 text-base font-medium text-wc-text-secondary hover:text-wc-text">
                            Preguntas frecuentes
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

</x-layouts.public>
