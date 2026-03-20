<x-layouts.public>
    <x-slot:title>Planes - WellCore Fitness</x-slot:title>

    {{-- Header --}}
    <section class="bg-wc-bg-tertiary">
        <div class="mx-auto max-w-7xl px-4 py-16 text-center sm:px-6 sm:py-20 lg:px-8">
            <h1 class="font-display text-4xl tracking-wide text-wc-text sm:text-5xl lg:text-6xl">NUESTROS PLANES</h1>
            <p class="mx-auto mt-4 max-w-2xl text-lg text-wc-text-secondary">
                Elige el nivel de acompanamiento que necesitas. Todos los planes incluyen programacion
                personalizada basada en tus objetivos.
            </p>
        </div>
    </section>

    {{-- Main Plans --}}
    <section class="bg-wc-bg">
        <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">

                {{-- Esencial --}}
                <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-8">
                    <div>
                        <h3 class="font-display text-2xl tracking-wide text-wc-text">ESENCIAL</h3>
                        <p class="mt-1 text-sm text-wc-text-tertiary">Plan basico</p>
                    </div>
                    <div class="mt-6">
                        <span class="font-data text-4xl font-bold text-wc-text">$95</span>
                        <span class="text-sm text-wc-text-tertiary"> USD/mes</span>
                    </div>
                    <ul class="mt-8 space-y-3">
                        <li class="flex items-start gap-3">
                            <svg class="mt-0.5 h-5 w-5 shrink-0 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                            <span class="text-sm text-wc-text-secondary">Entrenamiento personalizado</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <svg class="mt-0.5 h-5 w-5 shrink-0 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                            <span class="text-sm text-wc-text-secondary">Plan nutricional personalizado</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <svg class="mt-0.5 h-5 w-5 shrink-0 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                            <span class="text-sm text-wc-text-secondary">Ajustes mensuales</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <svg class="mt-0.5 h-5 w-5 shrink-0 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                            <span class="text-sm text-wc-text-secondary">Soporte por chat</span>
                        </li>
                    </ul>
                    <div class="mt-8">
                        <a href="#" class="block rounded-lg border border-wc-border bg-wc-bg-secondary px-6 py-3 text-center text-sm font-medium text-wc-text hover:bg-wc-bg">
                            Seleccionar Plan
                        </a>
                    </div>
                </div>

                {{-- Metodo (Popular) --}}
                <div class="relative rounded-xl border-2 border-wc-accent bg-wc-bg-tertiary p-8">
                    <div class="absolute -top-3 left-1/2 -translate-x-1/2">
                        <span class="rounded-full bg-wc-accent px-4 py-1 text-xs font-semibold uppercase tracking-wider text-white">Mas popular</span>
                    </div>
                    <div>
                        <h3 class="font-display text-2xl tracking-wide text-wc-text">METODO</h3>
                        <p class="mt-1 text-sm text-wc-text-tertiary">Plan intermedio</p>
                    </div>
                    <div class="mt-6">
                        <span class="font-data text-4xl font-bold text-wc-text">$120</span>
                        <span class="text-sm text-wc-text-tertiary"> USD/mes</span>
                    </div>
                    <ul class="mt-8 space-y-3">
                        <li class="flex items-start gap-3">
                            <svg class="mt-0.5 h-5 w-5 shrink-0 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                            <span class="text-sm text-wc-text-secondary">Todo lo del plan Esencial</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <svg class="mt-0.5 h-5 w-5 shrink-0 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                            <span class="text-sm text-wc-text-secondary">Seguimiento semanal</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <svg class="mt-0.5 h-5 w-5 shrink-0 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                            <span class="text-sm text-wc-text-secondary">Acceso a comunidad WellCore</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <svg class="mt-0.5 h-5 w-5 shrink-0 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                            <span class="text-sm text-wc-text-secondary">Ajustes semanales del plan</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <svg class="mt-0.5 h-5 w-5 shrink-0 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                            <span class="text-sm text-wc-text-secondary">Contenido educativo exclusivo</span>
                        </li>
                    </ul>
                    <div class="mt-8">
                        <a href="#" class="block rounded-lg bg-wc-accent px-6 py-3 text-center text-sm font-medium text-white hover:bg-wc-accent-hover">
                            Seleccionar Plan
                        </a>
                    </div>
                </div>

                {{-- Elite --}}
                <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-8">
                    <div>
                        <h3 class="font-display text-2xl tracking-wide text-wc-text">ELITE</h3>
                        <p class="mt-1 text-sm text-wc-text-tertiary">Plan premium</p>
                    </div>
                    <div class="mt-6">
                        <span class="font-data text-4xl font-bold text-wc-text">$150</span>
                        <span class="text-sm text-wc-text-tertiary"> USD/mes</span>
                    </div>
                    <ul class="mt-8 space-y-3">
                        <li class="flex items-start gap-3">
                            <svg class="mt-0.5 h-5 w-5 shrink-0 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                            <span class="text-sm text-wc-text-secondary">Todo lo del plan Metodo</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <svg class="mt-0.5 h-5 w-5 shrink-0 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                            <span class="text-sm text-wc-text-secondary">Coaching 1:1 personalizado</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <svg class="mt-0.5 h-5 w-5 shrink-0 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                            <span class="text-sm text-wc-text-secondary">Video check-ins semanales</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <svg class="mt-0.5 h-5 w-5 shrink-0 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                            <span class="text-sm text-wc-text-secondary">Soporte prioritario</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <svg class="mt-0.5 h-5 w-5 shrink-0 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                            <span class="text-sm text-wc-text-secondary">Analisis avanzado de metricas</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <svg class="mt-0.5 h-5 w-5 shrink-0 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                            <span class="text-sm text-wc-text-secondary">Revision de tecnica en video</span>
                        </li>
                    </ul>
                    <div class="mt-8">
                        <a href="#" class="block rounded-lg border border-wc-border bg-wc-bg-secondary px-6 py-3 text-center text-sm font-medium text-wc-text hover:bg-wc-bg">
                            Seleccionar Plan
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Special Plans --}}
    <section class="bg-wc-bg-tertiary">
        <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
            <div class="text-center">
                <h2 class="font-display text-3xl tracking-wide text-wc-text sm:text-4xl">PROGRAMAS ESPECIALES</h2>
                <p class="mt-4 text-wc-text-secondary">Para quienes buscan algo mas.</p>
            </div>

            <div class="mt-12 grid grid-cols-1 gap-8 lg:grid-cols-2">

                {{-- RISE --}}
                <div class="relative overflow-hidden rounded-xl border border-wc-border bg-wc-bg p-8">
                    <div class="absolute right-0 top-0 h-24 w-24">
                        <div class="absolute right-[-34px] top-[18px] w-[140px] rotate-45 bg-wc-accent py-1 text-center text-xs font-semibold text-white">INTENSIVO</div>
                    </div>
                    <div>
                        <h3 class="font-display text-3xl tracking-wide text-wc-accent">RISE</h3>
                        <p class="mt-1 text-sm text-wc-text-tertiary">Programa intensivo de 12 semanas</p>
                    </div>
                    <p class="mt-4 text-sm text-wc-text-secondary">
                        Programa de transformacion integral disenado para quienes quieren resultados acelerados con
                        acompanamiento intensivo. Incluye entrenamiento, nutricion, suplementacion y coaching
                        semanal personalizado.
                    </p>
                    <ul class="mt-6 space-y-3">
                        <li class="flex items-start gap-3">
                            <svg class="mt-0.5 h-5 w-5 shrink-0 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                            <span class="text-sm text-wc-text-secondary">12 semanas de programacion periodizada</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <svg class="mt-0.5 h-5 w-5 shrink-0 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                            <span class="text-sm text-wc-text-secondary">Check-ins semanales obligatorios</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <svg class="mt-0.5 h-5 w-5 shrink-0 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                            <span class="text-sm text-wc-text-secondary">Guia de suplementacion</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <svg class="mt-0.5 h-5 w-5 shrink-0 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                            <span class="text-sm text-wc-text-secondary">Comunidad exclusiva RISE</span>
                        </li>
                    </ul>
                    <div class="mt-8">
                        <a href="#" class="inline-flex items-center justify-center rounded-lg bg-wc-accent px-6 py-3 text-sm font-medium text-white hover:bg-wc-accent-hover">
                            Consultar disponibilidad
                        </a>
                    </div>
                </div>

                {{-- Presencial --}}
                <div class="rounded-xl border border-wc-border bg-wc-bg p-8">
                    <div>
                        <h3 class="font-display text-3xl tracking-wide text-wc-text">PRESENCIAL</h3>
                        <p class="mt-1 text-sm text-wc-text-tertiary">Entrenamiento en persona - Bogota</p>
                    </div>
                    <p class="mt-4 text-sm text-wc-text-secondary">
                        Sesiones de entrenamiento presencial en Bogota, Colombia. Ideal para quienes prefieren
                        el acompanamiento directo y la correccion de tecnica en tiempo real.
                    </p>
                    <ul class="mt-6 space-y-3">
                        <li class="flex items-start gap-3">
                            <svg class="mt-0.5 h-5 w-5 shrink-0 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                            <span class="text-sm text-wc-text-secondary">Entrenamiento 1:1 presencial</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <svg class="mt-0.5 h-5 w-5 shrink-0 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                            <span class="text-sm text-wc-text-secondary">Correccion de tecnica en tiempo real</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <svg class="mt-0.5 h-5 w-5 shrink-0 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                            <span class="text-sm text-wc-text-secondary">Plan nutricional incluido</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <svg class="mt-0.5 h-5 w-5 shrink-0 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                            <span class="text-sm text-wc-text-secondary">Ubicacion: Bogota, Colombia</span>
                        </li>
                    </ul>
                    <div class="mt-8">
                        <a href="#" class="inline-flex items-center justify-center rounded-lg border border-wc-border bg-wc-bg-secondary px-6 py-3 text-sm font-medium text-wc-text hover:bg-wc-bg-tertiary">
                            Consultar disponibilidad
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- FAQ Link --}}
    <section class="bg-wc-bg">
        <div class="mx-auto max-w-7xl px-4 py-16 text-center sm:px-6 lg:px-8">
            <p class="text-wc-text-secondary">
                Tienes preguntas sobre nuestros planes?
                <a href="{{ route('faq') }}" class="font-medium text-wc-accent hover:text-wc-accent-hover">Consulta nuestras preguntas frecuentes</a>.
            </p>
        </div>
    </section>

</x-layouts.public>
