<x-layouts.public>
    <x-slot:title>Pago Confirmado - WellCore Fitness</x-slot:title>
    <x-slot:description>Tu pago ha sido procesado exitosamente. Bienvenido a WellCore Fitness.</x-slot:description>

    @php
        $estado = request('estado', 'aprobado');
        $planName = request('plan', 'Metodo');
        $monto = request('monto', '$399.000 COP');
        $ref = request('ref', 'WC-' . strtoupper(substr(md5(now()), 0, 8)));
    @endphp

    <div class="mx-auto max-w-3xl px-4 py-16 sm:px-6 sm:py-24 lg:px-8">

        @if($estado === 'aprobado')
            {{-- Exitoso --}}
            <div class="text-center">
                <div class="relative mx-auto flex h-20 w-20 items-center justify-center">
                    <div class="absolute inset-0 animate-ping rounded-full bg-wc-accent/20"></div>
                    <div class="relative flex h-20 w-20 items-center justify-center rounded-full bg-wc-accent/10">
                        <svg class="h-10 w-10 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                    </div>
                </div>
                <h1 class="mt-6 font-display text-4xl tracking-wide text-wc-text sm:text-5xl">PAGO EXITOSO</h1>
                <p class="mt-4 text-lg text-wc-text-secondary">Tu suscripcion ha sido procesada. En las proximas 24 horas recibiras tus credenciales de acceso al portal.</p>
            </div>

            {{-- Payment Details --}}
            <div class="mt-10 rounded-xl border border-wc-accent/30 bg-wc-accent/5 p-6">
                <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                    <div>
                        <p class="text-xs font-medium text-wc-text-tertiary">Plan</p>
                        <p class="mt-1 text-sm font-semibold text-wc-text">{{ $planName }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-wc-text-tertiary">Monto</p>
                        <p class="mt-1 text-sm font-semibold text-wc-accent">{{ $monto }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-wc-text-tertiary">Referencia</p>
                        <p class="mt-1 font-mono text-sm text-wc-text">{{ $ref }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-wc-text-tertiary">Estado</p>
                        <span class="mt-1 inline-block rounded-full bg-green-500/10 px-2 py-0.5 text-xs font-semibold text-green-500">APROBADO</span>
                    </div>
                </div>
            </div>

            {{-- Next Steps --}}
            <div class="mt-10">
                <h2 class="font-display text-xl tracking-wide text-wc-text">PROXIMOS PASOS</h2>
                <div class="mt-6 space-y-4">
                    <div class="flex items-start gap-4">
                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-wc-accent/10">
                            <span class="font-data text-sm font-bold text-wc-accent">1</span>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-wc-text">Revisa tu email</p>
                            <p class="mt-0.5 text-sm text-wc-text-secondary">Recibiras tus credenciales de acceso al portal en menos de 6 horas.</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-4">
                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-wc-accent/10">
                            <span class="font-data text-sm font-bold text-wc-accent">2</span>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-wc-text">Ingresa al portal</p>
                            <p class="mt-0.5 text-sm text-wc-text-secondary">Usa tu email y contrasena temporal. Puedes cambiarla al ingresar.</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-4">
                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-wc-accent/10">
                            <span class="font-data text-sm font-bold text-wc-accent">3</span>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-wc-text">Tu programa personalizado</p>
                            <p class="mt-0.5 text-sm text-wc-text-secondary">Sera subido al portal en 5 a 48 horas segun la cola de coaches.</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-4">
                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-wc-accent/10">
                            <span class="font-data text-sm font-bold text-wc-accent">4</span>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-wc-text">Dudas?</p>
                            <p class="mt-0.5 text-sm text-wc-text-secondary">Escribenos a <a href="mailto:info@wellcorefitness.com" class="text-wc-accent hover:underline">info@wellcorefitness.com</a> o por WhatsApp.</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Info Cards --}}
            <div class="mt-10 grid grid-cols-2 gap-4 sm:grid-cols-4">
                <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-4 text-center">
                    <p class="font-data text-lg font-bold text-wc-accent">&lt;6h</p>
                    <p class="mt-1 text-xs text-wc-text-secondary">Credenciales por email</p>
                </div>
                <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-4 text-center">
                    <p class="font-data text-lg font-bold text-wc-accent">5-48h</p>
                    <p class="mt-1 text-xs text-wc-text-secondary">Tu programa listo</p>
                </div>
                <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-4 text-center">
                    <p class="font-data text-lg font-bold text-wc-accent">1:1</p>
                    <p class="mt-1 text-xs text-wc-text-secondary">Personalizado</p>
                </div>
                <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-4 text-center">
                    <p class="font-data text-lg font-bold text-wc-accent">SSL</p>
                    <p class="mt-1 text-xs text-wc-text-secondary">Pago 100% seguro</p>
                </div>
            </div>

            {{-- CTAs --}}
            <div class="mt-10 flex flex-col items-center gap-4 sm:flex-row sm:justify-center">
                <a href="{{ route('login') }}" class="inline-flex items-center justify-center rounded-lg bg-wc-accent px-6 py-3 text-sm font-medium text-white hover:bg-wc-accent-hover">
                    Acceso Cliente
                    <svg class="ml-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" /></svg>
                </a>
                <a href="{{ route('home') }}" class="inline-flex items-center justify-center rounded-lg border border-wc-border px-6 py-3 text-sm font-medium text-wc-text-secondary hover:text-wc-text">
                    Ir al Inicio
                </a>
            </div>

        @elseif($estado === 'pendiente')
            {{-- Pendiente --}}
            <div class="text-center">
                <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-yellow-500/10">
                    <svg class="h-10 w-10 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                </div>
                <h1 class="mt-6 font-display text-4xl tracking-wide text-wc-text sm:text-5xl">PAGO PENDIENTE</h1>
                <p class="mt-4 text-lg text-wc-text-secondary">Tu pago esta siendo procesado. Recibiras confirmacion por email cuando se complete.</p>
                <p class="mt-2 text-sm text-wc-text-tertiary">Si pagaste con PSE o Efecty, puede tomar hasta 24 horas.</p>
                <a href="{{ route('home') }}" class="mt-8 inline-flex items-center justify-center rounded-lg border border-wc-border px-6 py-3 text-sm font-medium text-wc-text-secondary hover:text-wc-text">Ir al Inicio</a>
            </div>

        @else
            {{-- Rechazado --}}
            <div class="text-center">
                <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-red-500/10">
                    <svg class="h-10 w-10 text-red-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                </div>
                <h1 class="mt-6 font-display text-4xl tracking-wide text-wc-text sm:text-5xl">PAGO RECHAZADO</h1>
                <p class="mt-4 text-lg text-wc-text-secondary">No pudimos procesar tu pago. Verifica los datos de tu metodo de pago e intenta nuevamente.</p>
                <div class="mt-8 flex flex-col items-center gap-4 sm:flex-row sm:justify-center">
                    <a href="{{ route('pagar') }}" class="inline-flex items-center justify-center rounded-lg bg-wc-accent px-6 py-3 text-sm font-medium text-white hover:bg-wc-accent-hover">Intentar de nuevo</a>
                    <a href="{{ route('home') }}" class="inline-flex items-center justify-center rounded-lg border border-wc-border px-6 py-3 text-sm font-medium text-wc-text-secondary hover:text-wc-text">Ir al Inicio</a>
                </div>
            </div>
        @endif

    </div>

</x-layouts.public>
