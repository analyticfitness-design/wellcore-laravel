<x-layouts.public>
    <x-slot:title>Planes y Precios - WellCore Fitness</x-slot:title>
    <x-slot:description>Planes de coaching fitness personalizado desde $299.000 COP/mes. Esencial, Metodo y Elite. Sin contratos, cancela cuando quieras.</x-slot:description>

    {{-- Hero --}}
    <section class="bg-wc-bg-tertiary">
        <div class="mx-auto max-w-7xl px-4 py-16 text-center sm:px-6 sm:py-20 lg:px-8">
            <h1 class="font-display text-4xl tracking-wide text-wc-text sm:text-5xl lg:text-6xl">ELIGE TU PLAN</h1>
            <p class="mx-auto mt-4 max-w-xl text-lg text-wc-text-secondary">Sin contratos de permanencia. Cancela cuando quieras. Invierte en lo que funciona.</p>
        </div>
    </section>

    {{-- Plans Grid --}}
    <section class="bg-wc-bg">
        <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

                {{-- Esencial --}}
                <div class="rounded-2xl border border-wc-border bg-wc-bg-tertiary p-8">
                    <h2 class="font-display text-2xl tracking-wide text-wc-text">ESENCIAL</h2>
                    <p class="mt-2 text-sm text-wc-text-secondary">Entrena con proposito. Primer paso real hacia resultados medibles.</p>
                    <div class="mt-6">
                        <span class="font-data text-4xl font-bold text-wc-text">$299.000</span>
                        <span class="text-sm text-wc-text-tertiary">COP/mes</span>
                    </div>
                    <a href="{{ route('pagar') }}?plan=esencial" class="mt-6 flex w-full items-center justify-center rounded-lg border border-wc-border bg-wc-bg px-6 py-3 text-sm font-medium text-wc-text hover:bg-wc-bg-tertiary">
                        Comenzar Esencial
                    </a>
                    <ul class="mt-8 space-y-3">
                        @foreach([
                            'Entrenamiento personalizado desde cero',
                            'Portal de cliente 24/7',
                            'Evaluacion inicial + diagnostico',
                            'Biblioteca de ejercicios con video',
                            'Seguimiento de metricas y progreso',
                            'Mediciones corporales + fotos',
                            'Comunidad y chat grupal',
                            'Ajuste mensual del programa',
                            'Soporte por mensaje — respuesta 48h',
                        ] as $feature)
                        <li class="flex items-start gap-3 text-sm text-wc-text-secondary">
                            <svg class="mt-0.5 h-4 w-4 shrink-0 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                            {{ $feature }}
                        </li>
                        @endforeach
                    </ul>
                </div>

                {{-- Metodo --}}
                <div class="relative rounded-2xl border-2 border-wc-accent bg-wc-bg-tertiary p-8">
                    <div class="absolute -top-3 left-1/2 -translate-x-1/2">
                        <span class="rounded-full bg-wc-accent px-4 py-1 text-xs font-semibold text-white">MEJOR VALOR</span>
                    </div>
                    <h2 class="font-display text-2xl tracking-wide text-wc-text">METODO</h2>
                    <p class="mt-2 text-sm text-wc-text-secondary">Entrenamiento + nutricion + habitos. El sistema completo.</p>
                    <p class="mt-1 text-xs text-wc-accent">Elegido por el +60% de nuestros clientes</p>
                    <div class="mt-6">
                        <span class="font-data text-4xl font-bold text-wc-accent">$399.000</span>
                        <span class="text-sm text-wc-text-tertiary">COP/mes</span>
                    </div>
                    <a href="{{ route('pagar') }}?plan=metodo" class="mt-6 flex w-full items-center justify-center rounded-lg bg-wc-accent px-6 py-3 text-sm font-semibold text-white hover:bg-wc-accent-hover">
                        Comenzar Metodo
                    </a>
                    <p class="mt-3 text-xs text-wc-text-tertiary">Todo lo del plan Esencial +</p>
                    <ul class="mt-4 space-y-3">
                        @foreach([
                            'Nutricion 100% personalizada',
                            'Macros y calorias ajustadas',
                            'Recetas adaptadas a preferencias',
                            'Guia de habitos y estilo de vida',
                            'Seguimiento de sueno y estres',
                            'Reporte mensual de progreso',
                            'Ajuste quincenal del programa',
                            'Soporte — respuesta 24h',
                            'Check-in semanal en vivo',
                            'Videollamada mensual',
                        ] as $feature)
                        <li class="flex items-start gap-3 text-sm text-wc-text-secondary">
                            <svg class="mt-0.5 h-4 w-4 shrink-0 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                            {{ $feature }}
                        </li>
                        @endforeach
                    </ul>
                </div>

                {{-- Elite --}}
                <div class="rounded-2xl border border-wc-border bg-wc-bg-tertiary p-8">
                    <div class="mb-2">
                        <span class="rounded-full bg-wc-text/10 px-3 py-1 text-xs font-semibold text-wc-text-secondary">SOLO 5 CUPOS</span>
                    </div>
                    <h2 class="font-display text-2xl tracking-wide text-wc-text">ELITE</h2>
                    <p class="mt-2 text-sm text-wc-text-secondary">Atencion total. Resultados sin excusas. Para quienes exigen lo mejor.</p>
                    <div class="mt-6">
                        <span class="font-data text-4xl font-bold text-wc-text">$549.000</span>
                        <span class="text-sm text-wc-text-tertiary">COP/mes</span>
                    </div>
                    <a href="{{ route('pagar') }}?plan=elite" class="mt-6 flex w-full items-center justify-center rounded-lg border border-wc-border bg-wc-bg px-6 py-3 text-sm font-medium text-wc-text hover:bg-wc-bg-tertiary">
                        Comenzar Elite
                    </a>
                    <p class="mt-3 text-xs text-wc-text-tertiary">Todo lo del plan Metodo +</p>
                    <ul class="mt-4 space-y-3">
                        @foreach([
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
                        ] as $feature)
                        <li class="flex items-start gap-3 text-sm text-wc-text-secondary">
                            <svg class="mt-0.5 h-4 w-4 shrink-0 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                            {{ $feature }}
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>

            {{-- Discounts --}}
            <div class="mt-12 flex flex-wrap items-center justify-center gap-6 text-sm text-wc-text-secondary">
                <div class="flex items-center gap-2">
                    <span class="rounded bg-wc-accent/10 px-2 py-1 text-xs font-bold text-wc-accent">-10%</span>
                    Pago trimestral
                </div>
                <div class="flex items-center gap-2">
                    <span class="rounded bg-wc-accent/10 px-2 py-1 text-xs font-bold text-wc-accent">-20%</span>
                    Pago anual
                </div>
                <div class="flex items-center gap-2">
                    <svg class="h-4 w-4 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" /></svg>
                    Garantia 7 dias
                </div>
            </div>
        </div>
    </section>

    {{-- Trust --}}
    <section class="bg-wc-bg-tertiary">
        <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 gap-6 sm:grid-cols-4">
                <div class="text-center">
                    <p class="font-data text-2xl font-bold text-wc-accent">0</p>
                    <p class="mt-1 text-xs text-wc-text-secondary">Contratos largo plazo</p>
                </div>
                <div class="text-center">
                    <p class="font-data text-2xl font-bold text-wc-accent">0</p>
                    <p class="mt-1 text-xs text-wc-text-secondary">Suplementos obligatorios</p>
                </div>
                <div class="text-center">
                    <p class="font-data text-2xl font-bold text-wc-accent">SSL</p>
                    <p class="mt-1 text-xs text-wc-text-secondary">Pago seguro Wompi</p>
                </div>
                <div class="text-center">
                    <p class="font-data text-2xl font-bold text-wc-accent">24h</p>
                    <p class="mt-1 text-xs text-wc-text-secondary">Cancela sin penalidad</p>
                </div>
            </div>
            <p class="mt-8 text-center text-xs text-wc-text-tertiary">Cancelacion sin penalizacion. Reembolsos evaluados antes de entregar el plan.</p>
        </div>
    </section>

    {{-- CTA --}}
    <section class="bg-wc-bg">
        <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
            <div class="relative overflow-hidden rounded-2xl border border-wc-border bg-wc-bg-tertiary p-10 sm:p-16">
                <div class="absolute inset-0 bg-gradient-to-br from-wc-accent/5 via-transparent to-transparent"></div>
                <div class="relative text-center">
                    <h2 class="font-display text-3xl tracking-wide text-wc-text sm:text-4xl">TIENES DUDAS?</h2>
                    <p class="mx-auto mt-4 max-w-md text-wc-text-secondary">Un sistema completo de entrenamiento, nutricion y habitos por menos de $13.000 al dia.</p>
                    <div class="mt-8 flex flex-col items-center justify-center gap-4 sm:flex-row">
                        <a href="{{ route('inscripcion') }}" class="inline-flex items-center justify-center rounded-lg bg-wc-accent px-8 py-3 text-base font-medium text-white hover:bg-wc-accent-hover">Comenzar Ahora</a>
                        <a href="{{ route('faq') }}" class="inline-flex items-center justify-center rounded-lg border border-wc-border px-8 py-3 text-base font-medium text-wc-text-secondary hover:text-wc-text">Ver FAQ completo</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

</x-layouts.public>
