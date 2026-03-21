<x-layouts.public>
    <x-slot:title>RISE - Reto 30 Dias de Transformacion | WellCore Fitness</x-slot:title>
    <x-slot:description>RISE: el reto de transformacion real de WellCore. 30 dias, personalizado, con ciencia. Entrenamiento 1:1, nutricion, habitos y seguimiento. $99.900 COP pago unico.</x-slot:description>

    {{-- Hero Section --}}
    <section class="relative overflow-hidden bg-wc-bg-tertiary">
        <div class="absolute inset-0 bg-gradient-to-br from-wc-accent/8 via-transparent to-transparent"></div>
        <div class="relative mx-auto max-w-7xl px-4 py-20 sm:px-6 sm:py-28 lg:px-8 lg:py-36">
            <div class="max-w-3xl">
                <p class="text-sm font-semibold uppercase tracking-widest text-wc-accent">Reto Activo &middot; Marzo 2026</p>
                <h1 class="mt-4 font-display text-6xl leading-none tracking-wide text-wc-text sm:text-7xl lg:text-9xl">
                    RISE.<br>
                    <span class="text-wc-accent">30 DIAS.</span>
                </h1>
                <p class="mt-6 max-w-xl text-lg text-wc-text-secondary">
                    El reto de transformacion real de WellCore. Personalizado, con ciencia, sin atajos. Todos los niveles. Gym o casa.
                </p>

                {{-- Countdown Timer --}}
                <div class="mt-8" x-data="countdown('2026-03-31T23:59:59')" x-init="start()">
                    <p class="mb-3 text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Cierra inscripcion en:</p>
                    <div class="flex gap-3">
                        <div class="rounded-lg border border-wc-accent/20 bg-wc-accent/5 px-4 py-3 text-center">
                            <p class="font-data text-2xl font-bold text-wc-accent" x-text="days">00</p>
                            <p class="text-[10px] uppercase text-wc-text-tertiary">Dias</p>
                        </div>
                        <div class="rounded-lg border border-wc-accent/20 bg-wc-accent/5 px-4 py-3 text-center">
                            <p class="font-data text-2xl font-bold text-wc-accent" x-text="hours">00</p>
                            <p class="text-[10px] uppercase text-wc-text-tertiary">Horas</p>
                        </div>
                        <div class="rounded-lg border border-wc-accent/20 bg-wc-accent/5 px-4 py-3 text-center">
                            <p class="font-data text-2xl font-bold text-wc-accent" x-text="minutes">00</p>
                            <p class="text-[10px] uppercase text-wc-text-tertiary">Min</p>
                        </div>
                        <div class="rounded-lg border border-wc-accent/20 bg-wc-accent/5 px-4 py-3 text-center">
                            <p class="font-data text-2xl font-bold text-wc-accent" x-text="seconds">00</p>
                            <p class="text-[10px] uppercase text-wc-text-tertiary">Seg</p>
                        </div>
                    </div>
                </div>

                <div class="mt-8">
                    <a href="{{ route('planes') }}" class="inline-flex items-center justify-center rounded-full bg-wc-accent px-8 py-3.5 font-semibold text-white shadow-lg shadow-wc-accent/20">
                        Unirme al Reto RISE
                        <svg class="ml-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" /></svg>
                    </a>
                    <p class="mt-3 text-xs text-wc-text-tertiary">Sin compromiso &middot; Pago unico $99.900</p>
                </div>
            </div>
        </div>
    </section>

    {{-- What's Included --}}
    <section class="bg-wc-bg">
        <div class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
            <div class="text-center">
                <h2 class="font-display text-3xl tracking-wide text-wc-text sm:text-4xl">QUE INCLUYE</h2>
                <p class="mt-2 text-lg text-wc-text-secondary">4 pilares. 1 metodo.</p>
                <p class="mt-1 text-sm text-wc-text-tertiary">Todo lo que necesitas para transformar tu cuerpo en 30 dias.</p>
            </div>

            <div class="mt-14 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-8 transition-colors hover:border-wc-accent/40">
                    <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-wc-accent/10">
                        <svg class="h-6 w-6 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" />
                        </svg>
                    </div>
                    <h3 class="mt-5 text-lg font-semibold text-wc-text">Entrenamiento</h3>
                    <p class="mt-2 text-sm text-wc-text-secondary">Programa 1:1 disenado para ti. Gym o casa. Sin importar tu nivel.</p>
                </div>

                <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-8 transition-colors hover:border-wc-accent/40">
                    <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-wc-accent/10">
                        <svg class="h-6 w-6 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v17.25m0 0c-1.472 0-2.882.265-4.185.75M12 20.25c1.472 0 2.882.265 4.185.75M18.75 4.97A48.416 48.416 0 0 0 12 4.5c-2.291 0-4.545.16-6.75.47m13.5 0c1.01.143 2.01.317 3 .52m-3-.52 2.62 10.726c.122.499-.106 1.028-.589 1.202a5.988 5.988 0 0 1-2.031.352 5.988 5.988 0 0 1-2.031-.352c-.483-.174-.711-.703-.59-1.202L18.75 4.971Zm-16.5.52c.99-.203 1.99-.377 3-.52m0 0 2.62 10.726c.122.499-.106 1.028-.589 1.202a5.989 5.989 0 0 1-2.031.352 5.989 5.989 0 0 1-2.031-.352c-.483-.174-.711-.703-.59-1.202L5.25 4.971Z" />
                        </svg>
                    </div>
                    <h3 class="mt-5 text-lg font-semibold text-wc-text">Nutricion</h3>
                    <p class="mt-2 text-sm text-wc-text-secondary">Guia de alimentacion real para los 30 dias. Sin dietas extremas.</p>
                </div>

                <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-8 transition-colors hover:border-wc-accent/40">
                    <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-wc-accent/10">
                        <svg class="h-6 w-6 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.745 3.745 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z" />
                        </svg>
                    </div>
                    <h3 class="mt-5 text-lg font-semibold text-wc-text">Habitos</h3>
                    <p class="mt-2 text-sm text-wc-text-secondary">Sistema diario de habitos para garantizar tu constancia.</p>
                </div>

                <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-8 transition-colors hover:border-wc-accent/40">
                    <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-wc-accent/10">
                        <svg class="h-6 w-6 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                        </svg>
                    </div>
                    <h3 class="mt-5 text-lg font-semibold text-wc-text">Seguimiento</h3>
                    <p class="mt-2 text-sm text-wc-text-secondary">Revision de tu progreso durante los 30 dias del reto.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- For Whom --}}
    <section class="bg-wc-bg-tertiary">
        <div class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
            <h2 class="text-center font-display text-3xl tracking-wide text-wc-text sm:text-4xl">PARA QUIEN ES</h2>

            <div class="mt-12 grid grid-cols-1 gap-8 md:grid-cols-3">
                <div class="flex gap-4">
                    <span class="font-data text-4xl font-bold text-wc-accent/20">01</span>
                    <div>
                        <h3 class="text-lg font-semibold text-wc-text">Todos los niveles</h3>
                        <p class="mt-2 text-sm text-wc-text-secondary">Principiante, intermedio o avanzado. El reto se adapta a ti, no al reves.</p>
                    </div>
                </div>
                <div class="flex gap-4">
                    <span class="font-data text-4xl font-bold text-wc-accent/20">02</span>
                    <div>
                        <h3 class="text-lg font-semibold text-wc-text">Gym o casa</h3>
                        <p class="mt-2 text-sm text-wc-text-secondary">Sin equipamiento especial requerido. Tu espacio, tu ritmo.</p>
                    </div>
                </div>
                <div class="flex gap-4">
                    <span class="font-data text-4xl font-bold text-wc-accent/20">03</span>
                    <div>
                        <h3 class="text-lg font-semibold text-wc-text">30 dias reales</h3>
                        <p class="mt-2 text-sm text-wc-text-secondary">Un compromiso de un mes. Resultados medibles. Sin promesas vacias.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Pricing --}}
    <section class="bg-wc-bg">
        <div class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
            <div class="text-center">
                <h2 class="font-display text-3xl tracking-wide text-wc-text sm:text-4xl">INVERSION</h2>
                <p class="mt-2 text-lg text-wc-text-secondary">Una sola inversion.</p>
                <p class="mt-1 text-sm text-wc-text-tertiary">Sin cuotas, sin sorpresas, sin contratos.</p>
            </div>

            <div class="mx-auto mt-12 max-w-md">
                <div class="rounded-2xl border-2 border-wc-accent bg-wc-bg-tertiary p-8 text-center">
                    <span class="inline-block rounded-full bg-wc-accent/10 px-4 py-1 text-xs font-semibold text-wc-accent">Precio especial Marzo 2026</span>

                    <div class="mt-6">
                        <span class="font-data text-5xl font-bold text-wc-text">$99.900</span>
                        <p class="mt-1 text-sm text-wc-text-tertiary">COP &middot; Pago unico &middot; 30 dias completos</p>
                    </div>

                    <ul class="mt-8 space-y-3 text-left">
                        <li class="flex items-center gap-3 text-sm text-wc-text-secondary">
                            <svg class="h-4 w-4 shrink-0 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                            Entrenamiento Personalizado 1:1
                        </li>
                        <li class="flex items-center gap-3 text-sm text-wc-text-secondary">
                            <svg class="h-4 w-4 shrink-0 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                            Guia de Nutricion para los 30 dias
                        </li>
                        <li class="flex items-center gap-3 text-sm text-wc-text-secondary">
                            <svg class="h-4 w-4 shrink-0 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                            Guia de Habitos diarios
                        </li>
                        <li class="flex items-center gap-3 text-sm text-wc-text-secondary">
                            <svg class="h-4 w-4 shrink-0 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                            Seguimiento durante todo el reto
                        </li>
                    </ul>

                    <a href="{{ route('planes') }}" class="mt-8 flex w-full items-center justify-center rounded-full bg-wc-accent px-8 py-3.5 font-semibold text-white shadow-lg shadow-wc-accent/20">
                        Quiero unirme al RISE
                        <svg class="ml-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" /></svg>
                    </a>

                    <p class="mt-4 text-xs text-wc-text-tertiary">Pago seguro Wompi &middot; SSL 256-bit &middot; Soporte incluido</p>
                </div>
            </div>
        </div>
    </section>

    {{-- Testimonials --}}
    <section class="bg-wc-bg-tertiary">
        <div class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
            <h2 class="text-center font-display text-3xl tracking-wide text-wc-text sm:text-4xl">QUIENES YA LO VIVIERON</h2>

            <div class="mt-12 grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-3">
                <div class="rounded-xl border border-wc-border bg-wc-bg p-8">
                    <div class="flex gap-1">
                        @for ($i = 0; $i < 5; $i++)
                            <svg class="h-4 w-4 text-wc-accent" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        @endfor
                    </div>
                    <p class="mt-4 text-sm text-wc-text-secondary">
                        "Los 30 dias me demostraron que si era posible. El plan estaba hecho para mi, no un copy-paste."
                    </p>
                    <div class="mt-6 flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-wc-accent/10">
                            <span class="text-sm font-semibold text-wc-accent">LM</span>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-wc-text">Laura M.</p>
                            <p class="text-xs text-wc-text-tertiary">-8 kg &middot; Participante RISE</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-wc-border bg-wc-bg p-8">
                    <div class="flex gap-1">
                        @for ($i = 0; $i < 5; $i++)
                            <svg class="h-4 w-4 text-wc-accent" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        @endfor
                    </div>
                    <p class="mt-4 text-sm text-wc-text-secondary">
                        "La guia de habitos fue el diferencial. Por primera vez la constancia no fue un problema."
                    </p>
                    <div class="mt-6 flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-wc-accent/10">
                            <span class="text-sm font-semibold text-wc-accent">CR</span>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-wc-text">Carlos R.</p>
                            <p class="text-xs text-wc-text-tertiary">+5 kg musculo &middot; Participante RISE</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-wc-border bg-wc-bg p-8">
                    <div class="flex gap-1">
                        @for ($i = 0; $i < 5; $i++)
                            <svg class="h-4 w-4 text-wc-accent" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        @endfor
                    </div>
                    <p class="mt-4 text-sm text-wc-text-secondary">
                        "Funciona sin importar el lugar. Lo hice desde casa y los resultados fueron reales."
                    </p>
                    <div class="mt-6 flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-wc-accent/10">
                            <span class="text-sm font-semibold text-wc-accent">AP</span>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-wc-text">Ana P.</p>
                            <p class="text-xs text-wc-text-tertiary">Entrenamiento en casa &middot; Participante RISE</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Urgency CTA --}}
    <section class="bg-wc-bg">
        <div class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
            <div class="relative overflow-hidden rounded-2xl border border-wc-accent/30 bg-wc-bg-tertiary p-10 sm:p-16">
                <div class="absolute inset-0 bg-gradient-to-br from-wc-accent/8 via-transparent to-transparent"></div>
                <div class="relative text-center">
                    <h2 class="font-display text-3xl tracking-wide text-wc-text sm:text-5xl">CUPOS LIMITADOS</h2>
                    <p class="mt-4 text-lg text-wc-text-secondary">
                        Cierra el <span class="font-semibold text-wc-accent">31 de Marzo 2026</span>
                    </p>
                    <p class="mx-auto mt-2 max-w-md text-sm text-wc-text-tertiary">
                        No esperes al ultimo dia. Los cupos del RISE son limitados.
                    </p>
                    <div class="mt-8">
                        <a href="{{ route('planes') }}" class="inline-flex items-center justify-center rounded-full bg-wc-accent px-8 py-3.5 font-semibold text-white shadow-lg shadow-wc-accent/20">
                            Inscribirme ahora
                            <svg class="ml-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" /></svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Countdown Alpine.js Component --}}
    <script>
        function countdown(deadline) {
            return {
                days: '00', hours: '00', minutes: '00', seconds: '00',
                start() {
                    const update = () => {
                        const now = new Date().getTime();
                        const end = new Date(deadline).getTime();
                        const diff = end - now;
                        if (diff <= 0) { this.days = '00'; this.hours = '00'; this.minutes = '00'; this.seconds = '00'; return; }
                        this.days = String(Math.floor(diff / 86400000)).padStart(2, '0');
                        this.hours = String(Math.floor((diff % 86400000) / 3600000)).padStart(2, '0');
                        this.minutes = String(Math.floor((diff % 3600000) / 60000)).padStart(2, '0');
                        this.seconds = String(Math.floor((diff % 60000) / 1000)).padStart(2, '0');
                    };
                    update();
                    setInterval(update, 1000);
                }
            };
        }
    </script>

</x-layouts.public>
