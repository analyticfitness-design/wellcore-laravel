<x-layouts.public>
    <x-slot:title>Lanzamiento Oficial — WellCore Fitness Abril 2026</x-slot:title>
    <x-slot:description>WellCore Fitness llega con todo en Abril 2026. Plataforma nueva, coaching potenciado por IA, y 3 dias gratis para celebrar contigo. Cupos de fundador limitados.</x-slot:description>

    <x-json-ld :data="[
        '@context' => 'https://schema.org',
        '@type' => 'Event',
        'name' => 'Lanzamiento Oficial WellCore Fitness',
        'description' => 'Lanzamiento de la nueva plataforma WellCore Fitness con IA coaching, trial gratuito 3 dias y cupos de fundador.',
        'startDate' => '2026-04-01',
        'organizer' => ['@type' => 'Organization', 'name' => 'WellCore Fitness', 'url' => 'https://wellcorefitness.com'],
        'url' => url('/lanzamiento'),
    ]" />

    {{-- Reading progress bar --}}
    <div class="scroll-progress"></div>

    {{-- ================================================================== --}}
    {{-- LAUNCH BANNER                                                       --}}
    {{-- ================================================================== --}}
    <div class="border-b border-wc-accent/30 bg-wc-accent/10"
         x-data="{ show: true }" x-show="show">
        <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-2.5 sm:px-6 lg:px-8">
            <div class="flex items-center gap-2 text-xs font-medium text-wc-text">
                <span class="inline-flex h-2 w-2 animate-ping rounded-full bg-wc-accent"></span>
                <span class="text-wc-accent font-bold tracking-wide uppercase">Lanzamiento Oficial</span>
                <span class="text-wc-text-secondary hidden sm:inline">— 3 dias gratis para todos los nuevos miembros de Abril</span>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('inscripcion') }}" class="btn-press rounded-full bg-wc-accent px-4 py-1 text-xs font-bold text-white hover:bg-wc-accent-hover">
                    Registrarme gratis
                </a>
                <button @click="show = false" class="text-wc-text-tertiary hover:text-wc-text" aria-label="Cerrar">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                </button>
            </div>
        </div>
    </div>

    {{-- ================================================================== --}}
    {{-- 1. HERO — LANZAMIENTO OFICIAL                                       --}}
    {{-- ================================================================== --}}
    <section class="hero-gradient relative min-h-screen overflow-hidden"
             x-data="{
                 countdown: { days: 0, hours: 0, minutes: 0, seconds: 0 },
                 init() {
                     const target = new Date('2026-04-01T00:00:00');
                     const tick = () => {
                         const now = new Date();
                         const diff = Math.max(0, target - now);
                         this.countdown.days    = Math.floor(diff / 86400000);
                         this.countdown.hours   = Math.floor((diff % 86400000) / 3600000);
                         this.countdown.minutes = Math.floor((diff % 3600000) / 60000);
                         this.countdown.seconds = Math.floor((diff % 60000) / 1000);
                     };
                     tick();
                     setInterval(tick, 1000);
                 }
             }">

        {{-- Ambient background gradient --}}
        <div class="absolute inset-0 bg-gradient-to-br from-wc-accent/8 via-transparent to-wc-accent/3 pointer-events-none" aria-hidden="true"></div>

        {{-- Parallax orbs --}}
        <div class="parallax-hero" aria-hidden="true">
            <div class="parallax-orb parallax-orb-1" data-parallax-speed="0.2" style="opacity:0.07;width:36rem;height:36rem;top:-8rem;right:-8rem;"></div>
            <div class="parallax-orb parallax-orb-2" data-parallax-speed="0.35" style="opacity:0.05;width:24rem;height:24rem;bottom:-6rem;left:-6rem;"></div>
            <div class="parallax-orb parallax-orb-3" data-parallax-speed="0.15" style="opacity:0.04;width:18rem;height:18rem;top:30%;left:55%;"></div>
            <div class="parallax-orb parallax-orb-4" data-parallax-speed="0.25" style="opacity:0.03;width:14rem;height:14rem;top:60%;right:20%;background:var(--color-wc-accent);"></div>
            <div class="parallax-orb parallax-orb-5" data-parallax-speed="0.10" style="opacity:0.06;width:20rem;height:20rem;top:10%;left:20%;background:var(--color-wc-accent);"></div>
        </div>

        <div class="relative mx-auto max-w-7xl px-4 pb-24 pt-20 sm:px-6 sm:pt-28 lg:px-8 lg:pt-36">

            {{-- Pre-headline badge --}}
            <div class="mb-8 text-center" data-animate="fadeInDown">
                <span class="badge-shine inline-flex items-center gap-2 rounded-full border border-wc-accent/40 bg-wc-accent/10 px-5 py-2 text-xs font-bold uppercase tracking-widest text-wc-accent">
                    <span class="relative flex h-2 w-2">
                        <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-wc-accent opacity-75"></span>
                        <span class="relative inline-flex h-2 w-2 rounded-full bg-wc-accent"></span>
                    </span>
                    Abril 2026 &bull; Cupos de Fundador Limitados
                </span>
            </div>

            {{-- Main headline --}}
            <div class="text-center" data-animate="fadeInUp">
                <h1 class="font-display leading-none tracking-wide text-wc-text">
                    <span class="block text-5xl sm:text-7xl lg:text-9xl">WELLCORE</span>
                    <span class="block text-gradient-accent text-5xl sm:text-7xl lg:text-9xl">FITNESS</span>
                    <span class="block text-3xl sm:text-4xl lg:text-5xl text-wc-text-secondary mt-2">LANZAMIENTO OFICIAL</span>
                </h1>
                <p class="mx-auto mt-6 max-w-2xl text-lg leading-relaxed text-wc-text-secondary sm:text-xl">
                    La plataforma de coaching fitness mas avanzada de LATAM.
                    IA, coaching 1:1, nutricion personalizada y comunidad.<br>
                    <strong class="text-wc-text">Todo en un solo lugar. Gratis por 3 dias.</strong>
                </p>
            </div>

            {{-- Countdown Timer --}}
            <div class="mt-12 flex justify-center" data-animate="scaleIn" data-animate-delay="200">
                <div class="inline-grid grid-cols-4 gap-3 sm:gap-6">
                    <template x-for="(unit, key) in [
                        { label: 'Dias',     value: countdown.days },
                        { label: 'Horas',    value: countdown.hours },
                        { label: 'Minutos',  value: countdown.minutes },
                        { label: 'Segundos', value: countdown.seconds }
                    ]" :key="key">
                        <div class="card-hover-lift flex flex-col items-center justify-center rounded-2xl border border-wc-border bg-wc-bg-tertiary px-4 py-4 shadow-lg sm:px-8 sm:py-6">
                            <span class="font-data text-3xl font-bold tabular-nums text-wc-accent sm:text-5xl"
                                  x-text="String(unit.value).padStart(2, '0')"></span>
                            <span class="mt-1 text-xs font-medium uppercase tracking-wider text-wc-text-tertiary" x-text="unit.label"></span>
                        </div>
                    </template>
                </div>
            </div>

            {{-- Hero CTAs --}}
            <div class="mt-10 flex flex-col items-center gap-4 sm:flex-row sm:justify-center" data-animate="fadeInUp" data-animate-delay="300">
                <a href="{{ route('inscripcion') }}"
                   class="pulse-glow btn-press btn-ripple inline-flex w-full items-center justify-center gap-2 rounded-full bg-wc-accent px-10 py-4 text-base font-bold text-white shadow-xl shadow-wc-accent/30 hover:bg-wc-accent-hover sm:w-auto">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.59 14.37a6 6 0 0 1-5.84 7.38v-4.8m5.84-2.58a14.98 14.98 0 0 0 6.16-12.12A14.98 14.98 0 0 0 9.631 8.41m5.96 5.96a14.926 14.926 0 0 1-5.841 2.58m-.119-8.54a6 6 0 0 0-7.381 5.84h4.8m2.581-5.84a14.927 14.927 0 0 0-2.58 5.84m2.699 2.7c-.103.021-.207.041-.311.06a15.09 15.09 0 0 1-2.448-2.448 14.9 14.9 0 0 1 .06-.312m-2.24 2.39a4.493 4.493 0 0 0-1.757 4.306 4.493 4.493 0 0 0 4.306-1.758M16.5 9a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Z" />
                    </svg>
                    Comenzar Trial Gratis
                </a>
                <a href="{{ route('planes') }}"
                   class="btn-press inline-flex w-full items-center justify-center gap-2 rounded-full border border-wc-border px-10 py-4 text-base font-medium text-wc-text hover:border-wc-accent hover:text-wc-accent sm:w-auto">
                    Ver Planes
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" /></svg>
                </a>
            </div>

            <p class="mt-4 text-center text-xs text-wc-text-tertiary">
                Sin tarjeta de credito &bull; Cancela cuando quieras &bull; Acceso inmediato
            </p>

            {{-- Social proof ticker --}}
            <div class="mt-12 flex flex-wrap items-center justify-center gap-6 text-sm text-wc-text-secondary" data-animate="fadeIn" data-animate-delay="400">
                <div class="flex items-center gap-2">
                    <span class="font-data text-2xl font-bold text-wc-accent">+500</span>
                    <span>clientes activos</span>
                </div>
                <span class="hidden text-wc-border sm:inline">|</span>
                <div class="flex items-center gap-2">
                    <span class="font-data text-2xl font-bold text-wc-accent">94%</span>
                    <span>adherencia promedio</span>
                </div>
                <span class="hidden text-wc-border sm:inline">|</span>
                <div class="flex items-center gap-2">
                    <span class="font-data text-2xl font-bold text-wc-accent">8</span>
                    <span>coaches certificados</span>
                </div>
                <span class="hidden text-wc-border sm:inline">|</span>
                <div class="flex items-center gap-2">
                    <span class="font-data text-2xl font-bold text-wc-accent">3</span>
                    <span>anos transformando vidas</span>
                </div>
            </div>

        </div>

        {{-- Scroll indicator --}}
        <div class="absolute bottom-6 left-1/2 -translate-x-1/2 flex flex-col items-center gap-1 opacity-40" aria-hidden="true">
            <span class="text-xs text-wc-text-tertiary uppercase tracking-widest">Descubre</span>
            <svg class="h-5 w-5 animate-bounce text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
            </svg>
        </div>
    </section>

    <div class="section-divider"></div>

    {{-- ================================================================== --}}
    {{-- 2. TRIAL SECTION — 3 DIAS GRATIS                                   --}}
    {{-- ================================================================== --}}
    <section class="bg-wc-bg" id="trial">
        <div class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">

            <div class="grid grid-cols-1 items-center gap-16 lg:grid-cols-2">

                {{-- Left: Explanation --}}
                <div data-animate="slideInLeft">
                    <span class="inline-block rounded-full bg-wc-accent/10 px-4 py-1.5 text-xs font-bold uppercase tracking-wider text-wc-accent">
                        Trial Gratuito
                    </span>
                    <h2 class="mt-4 font-display text-4xl leading-none tracking-wide text-wc-text sm:text-5xl lg:text-6xl">
                        PRUEBA<br>
                        <span class="text-gradient-accent">3 DIAS</span><br>
                        GRATIS
                    </h2>
                    <p class="mt-6 text-lg leading-relaxed text-wc-text-secondary">
                        Para celebrar nuestro lanzamiento oficial, te regalamos <strong class="text-wc-text">3 dias completos</strong>
                        con acceso al plan Metodo — nuestra experiencia mas popular — completamente gratis.
                    </p>
                    <p class="mt-3 text-wc-text-secondary">
                        Sin restricciones. Sin tarjeta requerida. Solo entra, explora y transforma.
                        Si al dia 3 no estas enamorado/a de la plataforma, simplemente no suscribes.
                    </p>

                    <ul class="mt-8 space-y-3">
                        @foreach([
                            'Acceso completo al plan Metodo por 72 horas',
                            'Plan de entrenamiento personalizado desde el dia 1',
                            'Chat directo con tu coach asignado',
                            'Nutricion y macros calculados por IA',
                            'Dashboard de progreso con metricas en tiempo real',
                            'WellCoins de bienvenida para tu primera semana',
                        ] as $benefit)
                        <li class="flex items-start gap-3 text-sm text-wc-text-secondary">
                            <svg class="mt-0.5 h-5 w-5 shrink-0 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                            </svg>
                            {{ $benefit }}
                        </li>
                        @endforeach
                    </ul>

                    <div class="mt-10 flex flex-col gap-3 sm:flex-row sm:items-center">
                        <a href="{{ route('inscripcion') }}"
                           class="pulse-glow btn-press btn-ripple inline-flex items-center justify-center gap-2 rounded-full bg-wc-accent px-8 py-3.5 text-base font-bold text-white shadow-lg shadow-wc-accent/25 hover:bg-wc-accent-hover">
                            Activar mi trial gratis
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" /></svg>
                        </a>
                        <span class="text-xs text-wc-text-tertiary">Disponible solo durante Abril 2026</span>
                    </div>
                </div>

                {{-- Right: Trial timeline visual --}}
                <div data-animate="slideInRight">
                    <div class="relative">
                        {{-- Glow ring --}}
                        <div class="absolute inset-0 rounded-3xl bg-gradient-to-br from-wc-accent/15 via-wc-accent/5 to-transparent blur-2xl" aria-hidden="true"></div>

                        <div class="relative rounded-3xl border border-wc-border bg-wc-bg-tertiary p-8 shadow-2xl">
                            <div class="mb-6 flex items-center justify-between">
                                <h3 class="font-display text-xl tracking-wide text-wc-text">Tu Trial — Paso a Paso</h3>
                                <span class="rounded-full bg-wc-accent px-3 py-1 text-xs font-bold text-white">72h</span>
                            </div>

                            {{-- Timeline --}}
                            <div class="space-y-6">
                                @foreach([
                                    ['day' => 'Dia 1', 'title' => 'Acceso instantaneo', 'desc' => 'Registro, onboarding y primer check-in. Tu coach ya tiene tu perfil.', 'icon' => '🚀', 'accent' => true],
                                    ['day' => 'Dia 2', 'title' => 'Inmersion total', 'desc' => 'Entrena con tu plan, registra nutricion, chatea con tu coach y acumula WellCoins.', 'icon' => '💪', 'accent' => false],
                                    ['day' => 'Dia 3', 'title' => 'Decide con certeza', 'desc' => 'Ve tus primeras metricas y decide si continuas. Sin presion, sin trampa.', 'icon' => '⚡', 'accent' => false],
                                    ['day' => 'Dia 4+', 'title' => 'Si te quedas...', 'desc' => 'Precio de fundador especial. Descuento exclusivo para quienes entran en Abril.', 'icon' => '🎯', 'accent' => false],
                                ] as $step)
                                <div class="flex gap-4">
                                    <div class="flex flex-col items-center">
                                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $step['accent'] ? 'bg-wc-accent text-white shadow-md shadow-wc-accent/30' : 'bg-wc-bg-secondary' }} text-lg">
                                            {{ $step['icon'] }}
                                        </div>
                                        @if(!$loop->last)
                                        <div class="mt-2 h-full min-h-[2rem] w-px bg-wc-border"></div>
                                        @endif
                                    </div>
                                    <div class="pb-2 pt-1">
                                        <span class="text-xs font-bold uppercase tracking-wider {{ $step['accent'] ? 'text-wc-accent' : 'text-wc-text-tertiary' }}">{{ $step['day'] }}</span>
                                        <p class="text-sm font-semibold text-wc-text">{{ $step['title'] }}</p>
                                        <p class="mt-0.5 text-xs leading-relaxed text-wc-text-secondary">{{ $step['desc'] }}</p>
                                    </div>
                                </div>
                                @endforeach
                            </div>

                            <div class="mt-6 rounded-xl border border-wc-accent/20 bg-wc-accent/5 p-4">
                                <p class="text-xs leading-relaxed text-wc-text-secondary">
                                    <strong class="text-wc-accent">Garantia total:</strong>
                                    Si decides no continuar al finalizar los 3 dias, tu cuenta simplemente se congela.
                                    No cobros sorpresa. No datos perdidos.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <div class="section-divider"></div>

    {{-- ================================================================== --}}
    {{-- 3. NOVEDADES — LO QUE HAY DE NUEVO                                --}}
    {{-- ================================================================== --}}
    <section class="bg-wc-bg-tertiary" id="novedades">
        <div class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">

            <div class="mb-16 text-center" data-animate="fadeInUp">
                <span class="inline-block rounded-full bg-wc-accent/10 px-4 py-1.5 text-xs font-bold uppercase tracking-wider text-wc-accent">
                    Nueva Plataforma
                </span>
                <h2 class="mt-4 font-display text-4xl tracking-wide text-wc-text sm:text-5xl">
                    TODO LO QUE<br><span class="text-gradient-accent">CONSTRUIMOS</span> PARA TI
                </h2>
                <p class="mx-auto mt-4 max-w-xl text-wc-text-secondary">
                    Tres anos escuchando a nuestra comunidad. Un lanzamiento con todo adentro.
                </p>
            </div>

            <div class="stagger-grid grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">

                @foreach([
                    [
                        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 0 0-2.456 2.456Z" />',
                        'title' => 'Coaching con IA',
                        'desc' => 'Asistente inteligente que analiza tu progreso, adapta tu plan y responde preguntas nutricionales 24/7 — entrenado en ciencia del ejercicio.',
                        'badge' => 'Nuevo',
                    ],
                    [
                        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 0 1 .865-.501 48.172 48.172 0 0 0 3.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z" />',
                        'title' => 'Chat en Tiempo Real',
                        'desc' => 'Mensajeria instantanea con tu coach. Envia fotos, videos de ejercicios y archivos. Respuestas garantizadas en menos de 24 horas.',
                        'badge' => 'Mejorado',
                    ],
                    [
                        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />',
                        'title' => 'Dashboards Interactivos',
                        'desc' => 'Graficas Chart.js en tiempo real de peso, porcentaje graso, fuerza y adherencia. Exporta tu progreso en PDF o CSV.',
                        'badge' => 'Nuevo',
                    ],
                    [
                        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />',
                        'title' => 'WellCoins',
                        'desc' => 'Sistema de recompensas que convierte tu consistencia en beneficios reales. Gana monedas por cada entreno, check-in y logro.',
                        'badge' => 'Exclusivo',
                    ],
                    [
                        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />',
                        'title' => 'Comunidad WellCore',
                        'desc' => 'Feed social privado, retos grupales, rankings semanales y celebraciones de logros. Nunca entrenas solo/a.',
                        'badge' => 'Nuevo',
                    ],
                    [
                        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />',
                        'title' => 'Habitos + Rachas',
                        'desc' => 'Tracker de habitos diarios con calendario de rachas, notificaciones push inteligentes y recordatorios adaptados a tu zona horaria.',
                        'badge' => 'Mejorado',
                    ],
                    [
                        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 0 0 6 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0 1 18 16.5h-2.25m-7.5 0h7.5m-7.5 0-1 3m8.5-3 1 3m0 0 .5 1.5m-.5-1.5h-9.5m0 0-.5 1.5m.75-9 3-3 2.148 2.148A12.061 12.061 0 0 1 16.5 7.605" />',
                        'title' => 'Planes de Entrenamiento Drag & Drop',
                        'desc' => 'Coaches crean y reorganizan planes con interfaz visual. Ejercicios con video, instrucciones y progresion automatica de carga.',
                        'badge' => 'Pro',
                    ],
                    [
                        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />',
                        'title' => 'App Optimizada para Movil',
                        'desc' => 'PWA instalable. Funciona offline en el gym. Timer de descanso, videos de ejercicios, cronometro integrado y modo no molestar.',
                        'badge' => 'Mejorado',
                    ],
                    [
                        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />',
                        'title' => 'Pagos 100% Colombia',
                        'desc' => 'Integracion completa con Wompi. PSE, tarjetas debito/credito, Nequi y Bancolombia. Facturacion electronica incluida.',
                        'badge' => 'Local',
                    ],
                ] as $index => $feature)
                <div class="card-hover-lift scroll-reveal group flex flex-col rounded-2xl border border-wc-border bg-wc-bg p-6 transition-all duration-300 hover:border-wc-accent/40 hover:shadow-lg hover:shadow-wc-accent/5"
                     data-animate="fadeInUp" data-delay="{{ ($index % 3 + 1) * 100 }}">
                    <div class="mb-4 flex items-start justify-between">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-wc-accent/10 text-wc-accent transition-colors group-hover:bg-wc-accent group-hover:text-white">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                {!! $feature['icon'] !!}
                            </svg>
                        </div>
                        <span class="rounded-full border border-wc-accent/20 bg-wc-accent/8 px-2.5 py-0.5 text-xs font-bold text-wc-accent">
                            {{ $feature['badge'] }}
                        </span>
                    </div>
                    <h3 class="mb-2 font-semibold text-wc-text">{{ $feature['title'] }}</h3>
                    <p class="text-sm leading-relaxed text-wc-text-secondary">{{ $feature['desc'] }}</p>
                </div>
                @endforeach

            </div>
        </div>
    </section>

    <div class="section-divider"></div>

    {{-- ================================================================== --}}
    {{-- 4. CELEBRACION — CELEBRAMOS CONTIGO                               --}}
    {{-- ================================================================== --}}
    <section class="relative overflow-hidden bg-wc-bg" id="celebracion">

        {{-- Background --}}
        <div class="absolute inset-0 bg-gradient-to-br from-wc-accent/6 via-transparent to-wc-accent/3 pointer-events-none" aria-hidden="true"></div>
        <div class="absolute -right-32 top-0 h-96 w-96 rounded-full bg-wc-accent/5 blur-3xl" aria-hidden="true"></div>
        <div class="absolute -left-32 bottom-0 h-96 w-96 rounded-full bg-wc-accent/5 blur-3xl" aria-hidden="true"></div>

        <div class="relative mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">

            <div class="mb-16 text-center" data-animate="fadeInUp">
                <span class="inline-block rounded-full bg-wc-accent/10 px-4 py-1.5 text-xs font-bold uppercase tracking-wider text-wc-accent">
                    Hito de la Comunidad
                </span>
                <h2 class="mt-4 font-display text-4xl tracking-wide text-wc-text sm:text-5xl lg:text-6xl">
                    CELEBRAMOS<br>
                    <span class="text-gradient-accent">CONTIGO</span>
                </h2>
                <p class="mx-auto mt-4 max-w-2xl text-lg leading-relaxed text-wc-text-secondary">
                    Este no es solo el lanzamiento de una app. Es la materializacion de tres anos de trabajo,
                    de cientos de transformaciones reales, y de una comunidad que crece cada dia.
                    <strong class="text-wc-text">Gracias por ser parte.</strong>
                </p>
            </div>

            {{-- Milestone timeline --}}
            <div class="relative mx-auto max-w-3xl" data-animate="fadeInUp" data-animate-delay="200">
                {{-- Vertical line --}}
                <div class="absolute left-6 top-0 h-full w-px bg-gradient-to-b from-wc-accent/50 via-wc-accent/20 to-transparent lg:left-1/2" aria-hidden="true"></div>

                <div class="space-y-10">
                    @foreach([
                        ['year' => '2023', 'title' => 'El Inicio', 'desc' => 'WellCore nace en Bucaramanga con 12 clientes fundadores y la vision de hacer coaching fitness accesible para toda LATAM.', 'side' => 'left'],
                        ['year' => '2024', 'title' => 'Crecimiento', 'desc' => 'Superamos 200 clientes activos. Lanzamos el programa RISE. Incorporamos 8 coaches certificados en la plataforma.', 'side' => 'right'],
                        ['year' => '2025', 'title' => 'La Mision', 'desc' => 'Comenzamos la migracion tecnologica. 500 clientes. Primer sistema de inteligencia artificial propio entrenado en datos WellCore.', 'side' => 'left'],
                        ['year' => 'Abril 2026', 'title' => 'Lanzamiento Oficial', 'desc' => 'La nueva plataforma llega con todo. IA nativa, experiencia premium y una comunidad que ya cambio vidas. Tu momento es ahora.', 'side' => 'right', 'accent' => true],
                    ] as $milestone)
                    <div class="relative flex items-start gap-6 pl-16 lg:pl-0 {{ $milestone['side'] === 'right' ? 'lg:flex-row-reverse' : 'lg:flex-row' }}">
                        {{-- Timeline dot --}}
                        <div class="absolute left-3 flex h-7 w-7 items-center justify-center rounded-full border-2 {{ isset($milestone['accent']) ? 'border-wc-accent bg-wc-accent shadow-lg shadow-wc-accent/40' : 'border-wc-border bg-wc-bg-secondary' }} lg:static lg:shrink-0">
                            @if(isset($milestone['accent']))
                            <svg class="h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                            </svg>
                            @endif
                        </div>
                        <div class="card-hover-lift w-full rounded-2xl border {{ isset($milestone['accent']) ? 'border-wc-accent/40 bg-gradient-to-br from-wc-accent/8 to-wc-bg-tertiary' : 'border-wc-border bg-wc-bg-tertiary' }} p-6 lg:w-5/12">
                            <span class="text-xs font-bold uppercase tracking-wider {{ isset($milestone['accent']) ? 'text-wc-accent' : 'text-wc-text-tertiary' }}">
                                {{ $milestone['year'] }}
                            </span>
                            <h3 class="mt-1 font-display text-xl tracking-wide text-wc-text">{{ $milestone['title'] }}</h3>
                            <p class="mt-2 text-sm leading-relaxed text-wc-text-secondary">{{ $milestone['desc'] }}</p>
                        </div>
                        <div class="hidden lg:block lg:w-5/12"></div>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Community stats --}}
            <div class="mt-20 grid grid-cols-2 gap-6 sm:grid-cols-4" data-animate="fadeInUp" data-animate-delay="300">
                @foreach([
                    ['value' => '+500', 'label' => 'Clientes transformados'],
                    ['value' => '94%', 'label' => 'Adherencia promedio'],
                    ['value' => '8', 'label' => 'Coaches certificados'],
                    ['value' => '3', 'label' => 'Anos de trayectoria'],
                ] as $stat)
                <div class="scroll-reveal rounded-2xl border border-wc-border bg-wc-bg-tertiary p-6 text-center">
                    <p class="font-data text-4xl font-bold text-wc-accent">{{ $stat['value'] }}</p>
                    <p class="mt-2 text-sm text-wc-text-secondary">{{ $stat['label'] }}</p>
                </div>
                @endforeach
            </div>

        </div>
    </section>

    <div class="section-divider"></div>

    {{-- ================================================================== --}}
    {{-- 5. PRECIOS CON BADGE DE TRIAL                                      --}}
    {{-- ================================================================== --}}
    <section class="bg-wc-bg-tertiary" id="precios">
        <div class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">

            <div class="mb-12 text-center" data-animate="fadeInUp">
                <span class="inline-block rounded-full bg-wc-accent/10 px-4 py-1.5 text-xs font-bold uppercase tracking-wider text-wc-accent">
                    Precios de Fundador
                </span>
                <h2 class="mt-4 font-display text-4xl tracking-wide text-wc-text sm:text-5xl">
                    ELIGE TU PLAN<br>
                    <span class="text-gradient-accent">3 DIAS GRATIS</span>
                </h2>
                <p class="mx-auto mt-4 max-w-lg text-wc-text-secondary">
                    Comienza con el trial gratuito. Sin tarjeta. Luego decide con cual plan seguir.
                    Los precios de fundador solo estan disponibles en Abril 2026.
                </p>
            </div>

            <div class="stagger-grid grid grid-cols-1 items-start gap-8 lg:grid-cols-3">

                {{-- Plan Esencial --}}
                <div class="card-hover-lift relative flex h-full flex-col rounded-2xl border border-wc-border bg-wc-bg p-8"
                     data-animate="fadeInUp" data-delay="100">
                    {{-- Trial badge --}}
                    <div class="absolute -top-3.5 right-4">
                        <span class="rounded-full bg-emerald-500 px-4 py-1 text-xs font-bold text-white shadow-md">3 dias gratis</span>
                    </div>
                    <div class="mb-4">
                        <h3 class="font-display text-2xl tracking-wide text-wc-text">Esencial</h3>
                        <p class="mt-1 text-sm text-wc-text-secondary">Todo lo que necesitas para comenzar</p>
                    </div>
                    <div class="mb-6">
                        <span class="font-data text-5xl font-bold text-wc-text">$299k</span>
                        <span class="ml-1 text-sm text-wc-text-tertiary">COP/mes</span>
                    </div>
                    <a href="{{ route('inscripcion') }}?plan=esencial"
                       class="btn-press mb-6 flex w-full items-center justify-center rounded-full border border-wc-border bg-wc-bg-secondary px-6 py-3 text-sm font-semibold text-wc-text transition hover:border-wc-accent hover:text-wc-accent">
                        Probar gratis
                    </a>
                    <ul class="space-y-2.5">
                        @foreach(['Plan de entrenamiento personalizado','Nutricion basica con macros','Check-ins semanales','Chat con coach (respuesta 48h)','Dashboard de progreso','Seguimiento de habitos','Biblioteca de ejercicios'] as $feat)
                        <li class="flex items-start gap-2.5 text-sm text-wc-text-secondary">
                            <svg class="mt-0.5 h-4 w-4 shrink-0 text-green-500" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                            {{ $feat }}
                        </li>
                        @endforeach
                    </ul>
                </div>

                {{-- Plan Metodo (elevated) --}}
                <div class="card-hover-lift card-glow pulse-glow relative flex h-full flex-col rounded-2xl border-2 border-wc-accent bg-wc-bg p-8 shadow-xl shadow-wc-accent/15 lg:-mt-4 lg:pb-10 lg:pt-10"
                     data-animate="fadeInUp" data-delay="200">
                    {{-- Trial badge --}}
                    <div class="absolute -top-3.5 left-1/2 -translate-x-1/2">
                        <span class="badge-shine rounded-full bg-wc-accent px-5 py-1 text-xs font-bold tracking-wide text-white shadow-md">MAS POPULAR</span>
                    </div>
                    <div class="absolute -top-3.5 right-4">
                        <span class="rounded-full bg-emerald-500 px-4 py-1 text-xs font-bold text-white shadow-md">3 dias gratis</span>
                    </div>
                    <div class="mb-4">
                        <h3 class="font-display text-2xl tracking-wide text-wc-text">Metodo</h3>
                        <p class="mt-1 text-sm text-wc-text-secondary">La experiencia WellCore completa</p>
                        <p class="mt-0.5 text-xs font-medium text-wc-accent">Incluido en el trial gratuito</p>
                    </div>
                    <div class="mb-6">
                        <span class="font-data text-5xl font-bold text-wc-accent">$399k</span>
                        <span class="ml-1 text-sm text-wc-text-tertiary">COP/mes</span>
                    </div>
                    <a href="{{ route('inscripcion') }}?plan=metodo"
                       class="btn-press pulse-glow mb-6 flex w-full items-center justify-center rounded-full bg-wc-accent px-6 py-3 text-sm font-bold text-white shadow-md transition hover:bg-wc-accent-hover hover:shadow-lg">
                        Comenzar trial gratis
                    </a>
                    <ul class="space-y-2.5">
                        @foreach(['Todo en Esencial, mas:','IA Coaching 24/7','Chat con coach (respuesta 24h)','Planes de nutricion detallados','Analisis de fotos de progreso','WellCoins dobles','Retos grupales y comunidad','Check-ins ilimitados','Exportacion de datos y reportes'] as $feat)
                        <li class="flex items-start gap-2.5 text-sm text-wc-text-secondary">
                            <svg class="mt-0.5 h-4 w-4 shrink-0 text-green-500" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                            {{ $feat }}
                        </li>
                        @endforeach
                    </ul>
                </div>

                {{-- Plan Elite --}}
                <div class="card-hover-lift relative flex h-full flex-col rounded-2xl border border-wc-border bg-wc-bg p-8"
                     data-animate="fadeInUp" data-delay="300">
                    {{-- Trial badge --}}
                    <div class="absolute -top-3.5 right-4">
                        <span class="rounded-full bg-emerald-500 px-4 py-1 text-xs font-bold text-white shadow-md">3 dias gratis</span>
                    </div>
                    <div class="mb-2">
                        <span class="inline-block rounded-full border border-wc-accent/30 bg-wc-accent/10 px-3 py-0.5 text-xs font-bold text-wc-accent">ELITE</span>
                    </div>
                    <div class="mb-4">
                        <h3 class="font-display text-2xl tracking-wide text-wc-text">Elite</h3>
                        <p class="mt-1 text-sm text-wc-text-secondary">Para quienes no se conforman con menos</p>
                    </div>
                    <div class="mb-6">
                        <span class="font-data text-5xl font-bold text-wc-text">$549k</span>
                        <span class="ml-1 text-sm text-wc-text-tertiary">COP/mes</span>
                    </div>
                    <a href="{{ route('inscripcion') }}?plan=elite"
                       class="btn-press mb-6 flex w-full items-center justify-center rounded-full border border-wc-border bg-wc-bg-secondary px-6 py-3 text-sm font-semibold text-wc-text transition hover:border-wc-accent hover:text-wc-accent">
                        Probar gratis
                    </a>
                    <ul class="space-y-2.5">
                        @foreach(['Todo en Metodo, mas:','Coach dedicado exclusivo','Sesiones de video 1:1 mensuales','Analisis avanzado de biometricas','Plan de suplementacion personalizado','Prioridad de respuesta inmediata','Acceso anticipado a nuevas funciones','WellCoins premium x3','Comunidad privada Elite'] as $feat)
                        <li class="flex items-start gap-2.5 text-sm text-wc-text-secondary">
                            <svg class="mt-0.5 h-4 w-4 shrink-0 text-green-500" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                            {{ $feat }}
                        </li>
                        @endforeach
                    </ul>
                </div>

            </div>

            {{-- Founder urgency --}}
            <div class="mt-10 rounded-2xl border border-wc-accent/25 bg-wc-accent/5 p-6 text-center" data-animate="scaleIn" data-animate-delay="400">
                <p class="text-sm font-medium text-wc-text">
                    <span class="font-bold text-wc-accent">Precio de Fundador — Solo Abril 2026.</span>
                    Los precios actuales estan congelados para todos los miembros que se registren este mes.
                    A partir de Mayo 2026, los planes subiran de precio. Entra ahora y bloquea tu precio.
                </p>
            </div>

        </div>
    </section>

    <div class="section-divider"></div>

    {{-- ================================================================== --}}
    {{-- 6. TESTIMONIOS                                                     --}}
    {{-- ================================================================== --}}
    <section class="bg-wc-bg" id="testimonios">
        <div class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">

            <div class="mb-12 text-center" data-animate="fadeInUp">
                <h2 class="font-display text-4xl tracking-wide text-wc-text sm:text-5xl">
                    LO QUE DICE<br><span class="text-gradient-accent">NUESTRA COMUNIDAD</span>
                </h2>
            </div>

            <div class="stagger-grid grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach([
                    ['quote' => 'En 3 meses perdi 8 kg y gane mas musculo que en 2 anos en el gym por mi cuenta. El coaching 1:1 marca una diferencia enorme.', 'name' => 'Maria C.', 'city' => 'Bogota', 'plan' => 'Metodo'],
                    ['quote' => 'La IA de nutricion me explica todo de forma sencilla. Por fin entiendo que comer y por que. Mi energia cambio completamente.', 'name' => 'Juan P.', 'city' => 'Medellin', 'plan' => 'Elite'],
                    ['quote' => 'Probe el trial de 3 dias y al segundo dia ya sabia que me quedaba. La plataforma es increible y el coach siempre esta disponible.', 'name' => 'Laura M.', 'city' => 'Cali', 'plan' => 'Metodo'],
                    ['quote' => 'Los WellCoins me mantienen motivado cada semana. Es como un juego pero con resultados reales. Llevo 6 meses sin fallar un entreno.', 'name' => 'Carlos R.', 'city' => 'Bucaramanga', 'plan' => 'Esencial'],
                    ['quote' => 'Trabajo hasta las 10pm y siempre encuentro tiempo para revisar mi plan. La app es rapida, intuitiva y funciona sin internet en el gym.', 'name' => 'Andrea S.', 'city' => 'Barranquilla', 'plan' => 'Metodo'],
                    ['quote' => 'El programa RISE me cambio la vida. Ahora entreno con el plan Elite y siento que tengo un entrenador personal al precio de un cafe.', 'name' => 'Diego V.', 'city' => 'Pereira', 'plan' => 'Elite'],
                ] as $index => $testimonial)
                <div class="card-hover-lift scroll-reveal flex flex-col rounded-2xl border border-wc-border bg-wc-bg-tertiary p-6"
                     data-animate="fadeInUp" data-delay="{{ ($index % 3 + 1) * 100 }}">
                    {{-- Stars --}}
                    <div class="mb-3 flex gap-1">
                        @for($s = 0; $s < 5; $s++)
                        <svg class="h-4 w-4 text-amber-400" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 0 0 .95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 0 0-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 0 0-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 0 0-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 0 0 .951-.69l1.07-3.292Z" />
                        </svg>
                        @endfor
                    </div>
                    <p class="flex-1 text-sm italic leading-relaxed text-wc-text-secondary">"{{ $testimonial['quote'] }}"</p>
                    <div class="mt-4 flex items-center justify-between">
                        <div>
                            <p class="text-sm font-semibold text-wc-text">{{ $testimonial['name'] }}</p>
                            <p class="text-xs text-wc-text-tertiary">{{ $testimonial['city'] }}</p>
                        </div>
                        <span class="rounded-full bg-wc-bg-secondary px-2.5 py-1 text-xs font-medium text-wc-text-secondary">{{ $testimonial['plan'] }}</span>
                    </div>
                </div>
                @endforeach
            </div>

        </div>
    </section>

    <div class="section-divider"></div>

    {{-- ================================================================== --}}
    {{-- 7. FINAL CTA — UNETE AL LANZAMIENTO                               --}}
    {{-- ================================================================== --}}
    <section class="bg-wc-bg-tertiary" id="unete">
        <div class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">

            <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-wc-bg via-wc-bg to-wc-bg-secondary border border-wc-accent/20 p-12 sm:p-20"
                 data-animate="scaleIn">

                {{-- Background decoration --}}
                <div class="absolute inset-0 bg-gradient-to-br from-wc-accent/10 via-wc-accent/5 to-transparent" aria-hidden="true"></div>
                <div class="absolute -bottom-16 -right-16 h-64 w-64 rounded-full bg-wc-accent/8 blur-3xl" aria-hidden="true"></div>
                <div class="absolute -left-16 -top-16 h-64 w-64 rounded-full bg-wc-accent/8 blur-3xl" aria-hidden="true"></div>

                {{-- Parallax orbs internal --}}
                <div class="absolute right-8 top-8 h-24 w-24 rounded-full bg-wc-accent/10 blur-2xl" aria-hidden="true"></div>
                <div class="absolute bottom-8 left-8 h-16 w-16 rounded-full bg-wc-accent/10 blur-2xl" aria-hidden="true"></div>

                <div class="relative text-center">
                    <div class="mb-6 inline-flex items-center gap-2 rounded-full border border-wc-accent/30 bg-wc-accent/10 px-5 py-2">
                        <span class="relative flex h-2 w-2">
                            <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-wc-accent opacity-75"></span>
                            <span class="relative inline-flex h-2 w-2 rounded-full bg-wc-accent"></span>
                        </span>
                        <span class="text-xs font-bold uppercase tracking-widest text-wc-accent">Lanzamiento Abril 2026</span>
                    </div>

                    <h2 class="font-display text-4xl leading-none tracking-wide text-wc-text sm:text-5xl lg:text-7xl">
                        UNETE AL<br>
                        <span class="text-gradient-accent">LANZAMIENTO</span>
                    </h2>

                    <p class="mx-auto mt-6 max-w-xl text-lg leading-relaxed text-wc-text-secondary">
                        Empieza con <strong class="text-wc-text">3 dias completamente gratis</strong>.
                        Sin tarjeta. Sin compromiso. Solo resultados.
                        Cupos de fundador limitados — el precio actual no se repite.
                    </p>

                    <div class="mt-10 flex flex-col items-center gap-4 sm:flex-row sm:justify-center">
                        <a href="{{ route('inscripcion') }}"
                           class="pulse-glow btn-press btn-ripple inline-flex w-full items-center justify-center gap-3 rounded-full bg-wc-accent px-12 py-4 text-lg font-bold text-white shadow-2xl shadow-wc-accent/30 hover:bg-wc-accent-hover sm:w-auto">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.59 14.37a6 6 0 0 1-5.84 7.38v-4.8m5.84-2.58a14.98 14.98 0 0 0 6.16-12.12A14.98 14.98 0 0 0 9.631 8.41m5.96 5.96a14.926 14.926 0 0 1-5.841 2.58m-.119-8.54a6 6 0 0 0-7.381 5.84h4.8m2.581-5.84a14.927 14.927 0 0 0-2.58 5.84m2.699 2.7c-.103.021-.207.041-.311.06a15.09 15.09 0 0 1-2.448-2.448 14.9 14.9 0 0 1 .06-.312m-2.24 2.39a4.493 4.493 0 0 0-1.757 4.306 4.493 4.493 0 0 0 4.306-1.758M16.5 9a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Z" />
                            </svg>
                            COMENZAR TRIAL GRATIS
                        </a>
                        <a href="{{ route('planes') }}"
                           class="btn-press inline-flex w-full items-center justify-center gap-2 rounded-full border border-wc-border px-10 py-4 text-base font-medium text-wc-text hover:border-wc-accent hover:text-wc-accent sm:w-auto">
                            Ver todos los planes
                        </a>
                    </div>

                    {{-- Trust indicators --}}
                    <div class="mt-8 flex flex-wrap items-center justify-center gap-6 text-xs text-wc-text-tertiary">
                        <span class="flex items-center gap-1.5">
                            <svg class="h-4 w-4 text-green-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" /></svg>
                            Sin tarjeta de credito
                        </span>
                        <span class="flex items-center gap-1.5">
                            <svg class="h-4 w-4 text-green-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" /></svg>
                            Cancela en cualquier momento
                        </span>
                        <span class="flex items-center gap-1.5">
                            <svg class="h-4 w-4 text-green-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" /></svg>
                            Acceso inmediato
                        </span>
                        <span class="flex items-center gap-1.5">
                            <svg class="h-4 w-4 text-green-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" /></svg>
                            Pagos seguros con Wompi
                        </span>
                    </div>

                </div>
            </div>

        </div>
    </section>

</x-layouts.public>
