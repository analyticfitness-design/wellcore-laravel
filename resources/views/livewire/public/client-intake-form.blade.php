<div
    x-data="{
        password: '',
        passwordConfirm: '',
        showPassword: false,
        showConfirm: false,
        get strength() {
            let s = 0;
            if (this.password.length >= 8) s++;
            if (/[A-Z]/.test(this.password)) s++;
            if (/[0-9]/.test(this.password)) s++;
            if (/[^A-Za-z0-9]/.test(this.password)) s++;
            return s;
        },
        get strengthLabel() {
            const l = ['', 'Debil', 'Regular', 'Buena', 'Fuerte'];
            return l[this.strength] ?? '';
        },
        get strengthColor() {
            const c = ['', 'bg-red-500', 'bg-amber-500', 'bg-yellow-400', 'bg-emerald-500'];
            return c[this.strength] ?? '';
        },
        get strengthWidth() {
            return (this.strength / 4 * 100) + '%';
        }
    }"
    class="min-h-screen bg-wc-bg"
>

    {{-- ===================================================================== --}}
    {{-- INVALID / EXPIRED CODE SCREENS                                        --}}
    {{-- ===================================================================== --}}
    @if($alreadyLoggedIn)
        <div class="flex min-h-[80vh] items-center justify-center px-4">
            <div class="w-full max-w-md text-center">
                <div class="mx-auto mb-6 flex h-20 w-20 items-center justify-center rounded-full bg-amber-500/10 ring-1 ring-amber-500/20">
                    <svg class="h-10 w-10 text-amber-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                    </svg>
                </div>
                <h1 class="font-display text-3xl tracking-wide text-wc-text">YA TIENES SESION ACTIVA</h1>
                <p class="mt-3 text-wc-text-secondary">Para usar este link de invitacion, primero cierra tu sesion actual. Este formulario es para nuevos clientes.</p>
                <div class="mt-6 flex flex-col gap-3">
                    <a href="{{ route('client.dashboard') }}" wire:navigate class="inline-block rounded-lg bg-wc-accent px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-red-700">
                        Ir a mi Dashboard
                    </a>
                    <a href="{{ url('/login') }}" class="text-sm text-wc-text-tertiary hover:text-wc-text">
                        Cerrar sesion e intentar de nuevo
                    </a>
                </div>
            </div>
        </div>
    @elseif($invalidCode || $codeExpired)
        <div class="flex min-h-[80vh] items-center justify-center px-4">
            <div class="w-full max-w-md text-center">
                <div class="mx-auto mb-6 flex h-20 w-20 items-center justify-center rounded-full bg-red-500/10 ring-1 ring-red-500/20">
                    <svg class="h-10 w-10 text-red-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636" />
                    </svg>
                </div>
                @if($codeExpired)
                    <h1 class="font-display text-3xl tracking-wide text-wc-text">INVITACION EXPIRADA</h1>
                    <p class="mt-3 text-wc-text-secondary">Este enlace de invitacion ha expirado. Contacta a tu coach para obtener uno nuevo.</p>
                @else
                    <h1 class="font-display text-3xl tracking-wide text-wc-text">ENLACE INVALIDO</h1>
                    <p class="mt-3 text-wc-text-secondary">Este enlace de invitacion no existe o ya fue utilizado. Si crees que es un error, contacta a tu coach.</p>
                @endif
                <a href="{{ route('home') }}" wire:navigate
                   class="mt-8 inline-flex items-center gap-2 rounded-full border border-wc-border bg-wc-bg-secondary px-6 py-3 text-sm font-medium text-wc-text hover:bg-wc-bg-tertiary transition-colors">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                    </svg>
                    Ir al inicio
                </a>
            </div>
        </div>
    @else

    {{-- ===================================================================== --}}
    {{-- MAIN FORM                                                              --}}
    {{-- ===================================================================== --}}

    {{-- Progress bar (full-width, sticky top) --}}
    <div class="sticky top-16 z-40 border-b border-wc-border bg-wc-bg/95 backdrop-blur-md">
        <div class="mx-auto max-w-2xl px-4 py-3">
            <div class="mb-2 flex items-center justify-between">
                <span class="text-xs font-medium text-wc-text-secondary">Paso {{ $step }} de {{ $totalSteps }}</span>
                @php
                    $stepNames = ['Bienvenida', 'Datos Personales', 'Perfil Fitness'];
                    if (in_array($planType, ['metodo', 'elite'])) $stepNames[] = 'Nutricion';
                    if ($planType === 'elite') $stepNames[] = 'Info Avanzada';
                    $stepNames[] = 'Tu Contrasena';
                @endphp
                <span class="text-xs font-semibold text-wc-text">{{ $stepNames[$step - 1] ?? '' }}</span>
            </div>
            <div class="h-1.5 w-full overflow-hidden rounded-full bg-wc-bg-tertiary">
                <div class="h-full rounded-full bg-wc-accent transition-all duration-500 ease-out"
                     style="width: {{ round(($step / $totalSteps) * 100) }}%"></div>
            </div>
        </div>
    </div>

    <div class="mx-auto max-w-2xl px-4 py-10">

        {{-- ----------------------------------------------------------------- --}}
        {{-- STEP 1 — BIENVENIDA                                               --}}
        {{-- ----------------------------------------------------------------- --}}
        @if($step === 1)
        <div
            x-show="true"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4"
            x-transition:enter-end="opacity-100 translate-y-0"
        >
            {{-- Plan badge --}}
            @php
                $planBadgeColors = [
                    'esencial'   => 'bg-sky-500/15 text-sky-300 ring-sky-500/30',
                    'metodo'     => 'bg-violet-500/15 text-violet-300 ring-violet-500/30',
                    'elite'      => 'bg-amber-500/15 text-amber-300 ring-amber-500/30',
                    'rise'       => 'bg-orange-500/15 text-orange-300 ring-orange-500/30',
                    'presencial' => 'bg-teal-500/15 text-teal-300 ring-teal-500/30',
                    'trial'      => 'bg-zinc-500/15 text-zinc-300 ring-zinc-500/30',
                ];
                $planIcons = [
                    'esencial'   => '⚡',
                    'metodo'     => '🧬',
                    'elite'      => '👑',
                    'rise'       => '🔥',
                    'presencial' => '🏋',
                    'trial'      => '🎯',
                ];
                $planIncludes = [
                    'esencial'   => ['Plan de entrenamiento personalizado', 'Seguimiento mensual con tu coach', 'Acceso al portal de clientes', 'Biblioteca de ejercicios y recetas'],
                    'metodo'     => ['Todo lo del plan Esencial', 'Plan nutricional personalizado con macros', 'Check-in semanal con analisis', 'Ajustes mensuales de plan'],
                    'elite'      => ['Todo lo del plan Metodo', 'Analisis de composicion corporal', 'Protocolo hormonal y bloodwork', 'Acceso directo al coach (24/7)'],
                    'rise'       => ['Plan personalizado de 30 dias', 'Entrenamiento diario estructurado', 'Nutricion y habitos saludables', 'Seguimiento de progreso con tu coach'],
                    'presencial' => ['Sesiones presenciales con tu coach', 'Plan de entrenamiento personalizado', 'Seguimiento y ajustes continuos', 'Acceso al portal digital de clientes'],
                    'trial'      => ['Acceso de prueba de 7 dias', 'Plan de entrenamiento introductorio', 'Consulta inicial con coach'],
                ];
                $badgeColor = $planBadgeColors[$planType] ?? 'bg-wc-bg-secondary text-wc-text ring-wc-border';
                $planIcon   = $planIcons[$planType] ?? '✦';
                $includes   = $planIncludes[$planType] ?? [];
            @endphp

            <div class="text-center">
                <span class="inline-flex items-center gap-2 rounded-full px-4 py-1.5 text-xs font-semibold uppercase tracking-widest ring-1 {{ $badgeColor }}">
                    <span>{{ $planIcon }}</span>
                    Plan {{ ucfirst($planType) }}
                </span>
                <h1 class="mt-6 font-display text-5xl tracking-wide text-wc-text sm:text-6xl">
                    BIENVENIDO A<br>WELLCORE
                </h1>
                <p class="mt-4 text-lg text-wc-text-secondary">
                    Tu coach te invito personalmente al <strong class="text-wc-text">Plan {{ ucfirst($planType) }}</strong>.
                    Este es el primer paso hacia tu transformacion.
                </p>
            </div>

            {{-- Coach note --}}
            @if($planNote)
            <div class="mt-8 rounded-2xl border border-wc-border bg-wc-bg-secondary p-5">
                <div class="flex items-start gap-3">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-wc-accent/10 ring-1 ring-wc-accent/20">
                        <svg class="h-4 w-4 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 0 1 .865-.501 48.172 48.172 0 0 0 3.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-wc-text-secondary">Mensaje de tu coach</p>
                        <p class="mt-1 text-sm text-wc-text">{{ $planNote }}</p>
                    </div>
                </div>
            </div>
            @endif

            {{-- What's included --}}
            @if(count($includes))
            <div class="mt-8 rounded-2xl border border-wc-border bg-wc-bg-secondary p-6">
                <h2 class="mb-4 text-xs font-semibold uppercase tracking-wider text-wc-text-secondary">Tu plan incluye</h2>
                <ul class="space-y-3">
                    @foreach($includes as $item)
                    <li class="flex items-center gap-3">
                        <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-emerald-500/15 ring-1 ring-emerald-500/30">
                            <svg class="h-3 w-3 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                            </svg>
                        </span>
                        <span class="text-sm text-wc-text">{{ $item }}</span>
                    </li>
                    @endforeach
                </ul>
            </div>
            @endif

            {{-- Estimated time --}}
            <div class="mt-6 flex items-center justify-center gap-2 text-xs text-wc-text-secondary">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                Tiempo estimado: {{ $totalSteps <= 4 ? '3-5' : ($totalSteps === 5 ? '5-7' : '7-10') }} minutos
            </div>

            <div class="mt-8">
                <button wire:click="nextStep"
                        class="group relative w-full overflow-hidden rounded-full bg-wc-accent py-4 text-base font-bold text-white shadow-lg shadow-wc-accent/20 transition-all duration-200 hover:bg-red-700 hover:shadow-wc-accent/30 focus:outline-none focus:ring-2 focus:ring-wc-accent focus:ring-offset-2 focus:ring-offset-wc-bg">
                    <span class="flex items-center justify-center gap-2">
                        Comenzar mi registro
                        <svg class="h-5 w-5 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                        </svg>
                    </span>
                </button>
            </div>
        </div>
        @endif

        {{-- ----------------------------------------------------------------- --}}
        {{-- STEP 2 — DATOS PERSONALES                                         --}}
        {{-- ----------------------------------------------------------------- --}}
        @if($step === 2)
        <div
            x-show="true"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4"
            x-transition:enter-end="opacity-100 translate-y-0"
        >
            <div class="mb-8">
                <h2 class="font-display text-4xl tracking-wide text-wc-text">DATOS PERSONALES</h2>
                <p class="mt-2 text-sm text-wc-text-secondary">Esta informacion nos permite personalizar tu experiencia desde el primer dia.</p>
            </div>

            <div class="space-y-6">
                {{-- Nombre y Apellido --}}
                <div class="rounded-2xl border border-wc-border bg-wc-bg-secondary p-6">
                    <h3 class="mb-4 flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-wc-text-secondary">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                        </svg>
                        Informacion basica
                    </h3>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-wc-text-secondary">
                                Nombre <span class="text-wc-accent">*</span>
                            </label>
                            <input type="text" wire:model="nombre" autocomplete="given-name"
                                   placeholder="Daniel"
                                   class="w-full rounded-lg border border-wc-border bg-wc-bg-tertiary px-4 py-3 text-sm text-wc-text placeholder-wc-text-tertiary transition-colors focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent" />
                            @error('nombre')<p class="mt-1.5 text-xs text-wc-accent">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-wc-text-secondary">
                                Apellido <span class="text-wc-accent">*</span>
                            </label>
                            <input type="text" wire:model="apellido" autocomplete="family-name"
                                   placeholder="Esparza"
                                   class="w-full rounded-lg border border-wc-border bg-wc-bg-tertiary px-4 py-3 text-sm text-wc-text placeholder-wc-text-tertiary transition-colors focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent" />
                            @error('apellido')<p class="mt-1.5 text-xs text-wc-accent">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="mt-4 grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-wc-text-secondary">
                                Email <span class="text-wc-accent">*</span>
                            </label>
                            <input type="email" wire:model="email" autocomplete="email"
                                   placeholder="tu@email.com"
                                   class="w-full rounded-lg border border-wc-border bg-wc-bg-tertiary px-4 py-3 text-sm text-wc-text placeholder-wc-text-tertiary transition-colors focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent" />
                            <p class="mt-1 text-[10px] text-wc-text-tertiary">Con este email iniciaras sesion en tu cuenta.</p>
                            @error('email')<p class="mt-1.5 text-xs text-wc-accent">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-wc-text-secondary">
                                WhatsApp <span class="text-wc-accent">*</span>
                            </label>
                            <input type="tel" wire:model="whatsapp" autocomplete="tel"
                                   placeholder="+57 300 000 0000"
                                   class="w-full rounded-lg border border-wc-border bg-wc-bg-tertiary px-4 py-3 text-sm text-wc-text placeholder-wc-text-tertiary transition-colors focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent" />
                            <p class="mt-1 text-[10px] text-wc-text-tertiary">Tu coach te contactara por este numero.</p>
                            @error('whatsapp')<p class="mt-1.5 text-xs text-wc-accent">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </div>

                {{-- Datos fisicos --}}
                <div class="rounded-2xl border border-wc-border bg-wc-bg-secondary p-6">
                    <h3 class="mb-4 flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-wc-text-secondary">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                        </svg>
                        Datos fisicos
                    </h3>
                    <p class="mb-4 text-[11px] text-wc-text-tertiary">El peso y la altura nos ayudan a calcular tus macros exactos y disenar un plan a tu medida.</p>
                    <div class="grid gap-4 sm:grid-cols-3">
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-wc-text-secondary">
                                Edad <span class="text-wc-accent">*</span>
                            </label>
                            <div class="relative">
                                <input type="number" wire:model="edad" min="16" max="80"
                                       placeholder="28"
                                       class="w-full rounded-lg border border-wc-border bg-wc-bg-tertiary px-4 py-3 pr-12 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent" />
                                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-wc-text-tertiary">anos</span>
                            </div>
                            @error('edad')<p class="mt-1.5 text-xs text-wc-accent">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-wc-text-secondary">
                                Peso <span class="text-wc-accent">*</span>
                            </label>
                            <div class="relative">
                                <input type="number" wire:model="peso" step="0.1" min="30" max="300"
                                       placeholder="75.5"
                                       class="w-full rounded-lg border border-wc-border bg-wc-bg-tertiary px-4 py-3 pr-8 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent" />
                                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-wc-text-tertiary">kg</span>
                            </div>
                            @error('peso')<p class="mt-1.5 text-xs text-wc-accent">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-wc-text-secondary">
                                Altura <span class="text-wc-accent">*</span>
                            </label>
                            <div class="relative">
                                <input type="number" wire:model="altura" step="0.1" min="100" max="250"
                                       placeholder="175"
                                       class="w-full rounded-lg border border-wc-border bg-wc-bg-tertiary px-4 py-3 pr-8 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent" />
                                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-wc-text-tertiary">cm</span>
                            </div>
                            @error('altura')<p class="mt-1.5 text-xs text-wc-accent">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </div>

                {{-- Genero y Ubicacion --}}
                <div class="rounded-2xl border border-wc-border bg-wc-bg-secondary p-6">
                    <h3 class="mb-4 flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-wc-text-secondary">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                        </svg>
                        Genero y ubicacion
                    </h3>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-3 block text-xs font-semibold uppercase tracking-wider text-wc-text-secondary">
                                Genero <span class="text-wc-accent">*</span>
                            </label>
                            <div class="flex gap-3">
                                @foreach(['hombre' => 'Hombre', 'mujer' => 'Mujer', 'otro' => 'Otro'] as $val => $label)
                                <label class="flex flex-1 cursor-pointer items-center justify-center rounded-lg border py-2.5 text-xs font-medium transition-all
                                    {{ $genero === $val ? 'border-wc-accent bg-wc-accent/10 text-wc-accent' : 'border-wc-border bg-wc-bg-tertiary text-wc-text-secondary hover:border-wc-accent/50' }}">
                                    <input type="radio" wire:model.live="genero" value="{{ $val }}" class="sr-only" />
                                    {{ $label }}
                                </label>
                                @endforeach
                            </div>
                            @error('genero')<p class="mt-1.5 text-xs text-wc-accent">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-wc-text-secondary">
                                Ciudad <span class="text-wc-accent">*</span>
                            </label>
                            <input type="text" wire:model="ciudad" autocomplete="address-level2"
                                   placeholder="Bogota, Medellin, Cali..."
                                   class="w-full rounded-lg border border-wc-border bg-wc-bg-tertiary px-4 py-3 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent" />
                            @error('ciudad')<p class="mt-1.5 text-xs text-wc-accent">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-8 flex gap-3">
                <button wire:click="prevStep"
                        class="flex items-center gap-2 rounded-full border border-wc-border bg-wc-bg-secondary px-6 py-3.5 text-sm font-medium text-wc-text-secondary hover:text-wc-text transition-colors">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                    </svg>
                    Atras
                </button>
                <button wire:click="nextStep"
                        wire:loading.attr="disabled"
                        wire:loading.class="opacity-60 cursor-not-allowed"
                        class="group flex flex-1 items-center justify-center gap-2 rounded-full bg-wc-accent py-3.5 text-sm font-bold text-white shadow-lg shadow-wc-accent/20 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-wc-accent focus:ring-offset-2 focus:ring-offset-wc-bg transition-all">
                    <span wire:loading.remove wire:target="nextStep" class="flex items-center gap-2">
                        Continuar
                        <svg class="h-4 w-4 transition-transform group-hover:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                        </svg>
                    </span>
                    <span wire:loading wire:target="nextStep" class="flex items-center gap-2">
                        <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                        Validando...
                    </span>
                </button>
            </div>
        </div>
        @endif

        {{-- ----------------------------------------------------------------- --}}
        {{-- STEP 3 — PERFIL FITNESS                                           --}}
        {{-- ----------------------------------------------------------------- --}}
        @if($step === 3)
        <div
            x-show="true"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4"
            x-transition:enter-end="opacity-100 translate-y-0"
        >
            <div class="mb-8">
                <h2 class="font-display text-4xl tracking-wide text-wc-text">PERFIL FITNESS</h2>
                <p class="mt-2 text-sm text-wc-text-secondary">Cuéntanos sobre tus objetivos y como entrenas actualmente.</p>
            </div>

            <div class="space-y-6">
                {{-- Objetivo principal --}}
                <div class="rounded-2xl border border-wc-border bg-wc-bg-secondary p-6">
                    <h3 class="mb-4 text-xs font-semibold uppercase tracking-wider text-wc-text-secondary">
                        Objetivo principal <span class="text-wc-accent">*</span>
                    </h3>
                    @php
                        $objetivos = [
                            'perder_grasa'   => ['Perder grasa', 'Reducir % de grasa corporal y definir'],
                            'ganar_musculo'  => ['Ganar musculo', 'Aumentar masa muscular y fuerza'],
                            'recomposicion'  => ['Recomposicion', 'Perder grasa y ganar musculo a la vez'],
                            'rendimiento'    => ['Rendimiento', 'Mejorar performance atletico'],
                            'salud_general'  => ['Salud general', 'Sentirte mejor, mas energia y vitalidad'],
                            'tonificar'      => ['Tonificar', 'Dar forma y firmeza al cuerpo'],
                        ];
                    @endphp
                    <div class="grid gap-3 sm:grid-cols-2">
                        @foreach($objetivos as $val => [$titulo, $desc])
                        <label class="flex cursor-pointer items-start gap-3 rounded-xl border p-4 transition-all
                            {{ $objetivo_principal === $val ? 'border-wc-accent bg-wc-accent/5' : 'border-wc-border bg-wc-bg-tertiary hover:border-wc-accent/40' }}">
                            <input type="radio" wire:model.live="objetivo_principal" value="{{ $val }}" class="sr-only" />
                            <span class="mt-0.5 flex h-4 w-4 shrink-0 items-center justify-center rounded-full border-2 transition-colors
                                {{ $objetivo_principal === $val ? 'border-wc-accent' : 'border-wc-border' }}">
                                @if($objetivo_principal === $val)
                                <span class="h-2 w-2 rounded-full bg-wc-accent"></span>
                                @endif
                            </span>
                            <div>
                                <p class="text-sm font-semibold text-wc-text">{{ $titulo }}</p>
                                <p class="mt-0.5 text-xs text-wc-text-secondary">{{ $desc }}</p>
                            </div>
                        </label>
                        @endforeach
                    </div>
                    @error('objetivo_principal')<p class="mt-2 text-xs text-wc-accent">{{ $message }}</p>@enderror
                </div>

                {{-- Nivel de experiencia --}}
                <div class="rounded-2xl border border-wc-border bg-wc-bg-secondary p-6">
                    <h3 class="mb-4 text-xs font-semibold uppercase tracking-wider text-wc-text-secondary">
                        Nivel de experiencia <span class="text-wc-accent">*</span>
                    </h3>
                    <div class="grid gap-3 sm:grid-cols-3">
                        @php
                            $niveles = [
                                'principiante' => ['Principiante', 'Menos de 1 ano entrenando o volviendo despues de un descanso largo'],
                                'intermedio'   => ['Intermedio', '1-3 anos de entrenamiento consistente con buena tecnica'],
                                'avanzado'     => ['Avanzado', 'Mas de 3 anos entrenando con metodologia estructurada'],
                            ];
                        @endphp
                        @foreach($niveles as $val => [$titulo, $desc])
                        <label class="flex cursor-pointer flex-col gap-2 rounded-xl border p-4 transition-all
                            {{ $nivel_experiencia === $val ? 'border-wc-accent bg-wc-accent/5' : 'border-wc-border bg-wc-bg-tertiary hover:border-wc-accent/40' }}">
                            <input type="radio" wire:model.live="nivel_experiencia" value="{{ $val }}" class="sr-only" />
                            <p class="text-sm font-bold text-wc-text">{{ $titulo }}</p>
                            <p class="text-xs leading-relaxed text-wc-text-secondary">{{ $desc }}</p>
                        </label>
                        @endforeach
                    </div>
                    @error('nivel_experiencia')<p class="mt-2 text-xs text-wc-accent">{{ $message }}</p>@enderror
                </div>

                {{-- Lugar de entrenamiento --}}
                <div class="rounded-2xl border border-wc-border bg-wc-bg-secondary p-6">
                    <h3 class="mb-4 text-xs font-semibold uppercase tracking-wider text-wc-text-secondary">
                        Donde entrenas <span class="text-wc-accent">*</span>
                    </h3>
                    @php
                        $lugares = [
                            'gym'              => ['Gym', 'Acceso completo a maquinas y pesas'],
                            'casa_con_equipo'  => ['Casa con equipo', 'Mancuernas, barras, banda elastica'],
                            'casa_sin_equipo'  => ['Casa sin equipo', 'Solo peso corporal'],
                            'aire_libre'       => ['Aire libre', 'Parque, cancha, calistenia'],
                            'mixto'            => ['Mixto', 'Combinas varios espacios'],
                        ];
                    @endphp
                    <div class="flex flex-wrap gap-2.5">
                        @foreach($lugares as $val => [$titulo, $desc])
                        <label class="cursor-pointer">
                            <input type="radio" wire:model.live="lugar_entreno" value="{{ $val }}" class="sr-only" />
                            <span class="inline-flex flex-col items-center gap-1 rounded-xl border px-4 py-3 text-center transition-all
                                {{ $lugar_entreno === $val ? 'border-wc-accent bg-wc-accent/10 text-wc-accent' : 'border-wc-border bg-wc-bg-tertiary text-wc-text-secondary hover:border-wc-accent/40' }}">
                                <span class="text-sm font-semibold">{{ $titulo }}</span>
                                <span class="text-[10px]">{{ $desc }}</span>
                            </span>
                        </label>
                        @endforeach
                    </div>
                    @error('lugar_entreno')<p class="mt-2 text-xs text-wc-accent">{{ $message }}</p>@enderror
                </div>

                {{-- Dias disponibles --}}
                <div class="rounded-2xl border border-wc-border bg-wc-bg-secondary p-6">
                    <h3 class="mb-1 text-xs font-semibold uppercase tracking-wider text-wc-text-secondary">
                        Dias disponibles <span class="text-wc-accent">*</span>
                    </h3>
                    <p class="mb-4 text-[11px] text-wc-text-tertiary">Selecciona minimo 2 dias. Tu plan se estructurara segun tu disponibilidad.</p>
                    @php
                        $dias = ['lunes' => 'LUN', 'martes' => 'MAR', 'miercoles' => 'MIE', 'jueves' => 'JUE', 'viernes' => 'VIE', 'sabado' => 'SAB', 'domingo' => 'DOM'];
                    @endphp
                    <div class="flex gap-2">
                        @foreach($dias as $val => $short)
                        <label class="flex flex-1 cursor-pointer flex-col items-center">
                            <input type="checkbox" wire:model.live="dias_disponibles" value="{{ $val }}" class="sr-only" />
                            <span class="flex h-12 w-full items-center justify-center rounded-xl border text-xs font-bold transition-all
                                {{ in_array($val, $dias_disponibles) ? 'border-wc-accent bg-wc-accent text-white' : 'border-wc-border bg-wc-bg-tertiary text-wc-text-secondary hover:border-wc-accent/50' }}">
                                {{ $short }}
                            </span>
                        </label>
                        @endforeach
                    </div>
                    @error('dias_disponibles')<p class="mt-2 text-xs text-wc-accent">{{ $message }}</p>@enderror
                </div>

                {{-- Duracion de sesion --}}
                <div class="rounded-2xl border border-wc-border bg-wc-bg-secondary p-6">
                    <h3 class="mb-4 text-xs font-semibold uppercase tracking-wider text-wc-text-secondary">
                        Duracion de tus sesiones <span class="text-wc-accent">*</span>
                    </h3>
                    <div class="flex flex-wrap gap-3">
                        @foreach(['45' => '45 min', '60' => '60 min', '75' => '75 min', '90' => '90 min'] as $val => $label)
                        <label class="cursor-pointer">
                            <input type="radio" wire:model.live="duracion_sesion" value="{{ $val }}" class="sr-only" />
                            <span class="inline-flex items-center justify-center rounded-xl border px-6 py-3 text-sm font-semibold transition-all
                                {{ $duracion_sesion === $val ? 'border-wc-accent bg-wc-accent/10 text-wc-accent' : 'border-wc-border bg-wc-bg-tertiary text-wc-text-secondary hover:border-wc-accent/40' }}">
                                {{ $label }}
                            </span>
                        </label>
                        @endforeach
                    </div>
                    @error('duracion_sesion')<p class="mt-2 text-xs text-wc-accent">{{ $message }}</p>@enderror
                </div>

                {{-- Lesiones --}}
                <div class="rounded-2xl border border-wc-border bg-wc-bg-secondary p-6">
                    <h3 class="mb-1 text-xs font-semibold uppercase tracking-wider text-wc-text-secondary">
                        Lesiones o restricciones fisicas <span class="text-wc-accent">*</span>
                    </h3>
                    <p class="mb-4 text-[11px] text-wc-text-tertiary">Queremos asegurarnos de que tu plan sea 100% seguro para tu cuerpo.</p>
                    <div class="flex gap-3">
                        @foreach(['no' => 'No tengo lesiones', 'si' => 'Si, tengo alguna'] as $val => $label)
                        <label class="flex flex-1 cursor-pointer items-center gap-2 rounded-xl border px-4 py-3 transition-all
                            {{ $tiene_lesiones === $val ? 'border-wc-accent bg-wc-accent/5' : 'border-wc-border bg-wc-bg-tertiary hover:border-wc-accent/40' }}">
                            <input type="radio" wire:model.live="tiene_lesiones" value="{{ $val }}" class="sr-only" />
                            <span class="flex h-4 w-4 shrink-0 items-center justify-center rounded-full border-2 transition-colors
                                {{ $tiene_lesiones === $val ? 'border-wc-accent' : 'border-wc-border' }}">
                                @if($tiene_lesiones === $val)
                                <span class="h-2 w-2 rounded-full bg-wc-accent"></span>
                                @endif
                            </span>
                            <span class="text-sm {{ $tiene_lesiones === $val ? 'font-semibold text-wc-text' : 'text-wc-text-secondary' }}">{{ $label }}</span>
                        </label>
                        @endforeach
                    </div>
                    @if($tiene_lesiones === 'si')
                    <div class="mt-4">
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-wc-text-secondary">
                            Describe tu lesion o restriccion <span class="text-wc-accent">*</span>
                        </label>
                        <textarea wire:model="detalle_lesiones" rows="3"
                                  placeholder="Ej: tengo una hernia lumbar L4-L5, no puedo hacer sentadillas con carga axial..."
                                  class="w-full resize-none rounded-lg border border-wc-border bg-wc-bg-tertiary px-4 py-3 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent"></textarea>
                        @error('detalle_lesiones')<p class="mt-1.5 text-xs text-wc-accent">{{ $message }}</p>@enderror
                    </div>
                    @endif
                    @error('tiene_lesiones')<p class="mt-2 text-xs text-wc-accent">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="mt-8 flex gap-3">
                <button wire:click="prevStep"
                        class="flex items-center gap-2 rounded-full border border-wc-border bg-wc-bg-secondary px-6 py-3.5 text-sm font-medium text-wc-text-secondary hover:text-wc-text transition-colors">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                    </svg>
                    Atras
                </button>
                <button wire:click="nextStep"
                        wire:loading.attr="disabled"
                        wire:loading.class="opacity-60 cursor-not-allowed"
                        class="group flex flex-1 items-center justify-center gap-2 rounded-full bg-wc-accent py-3.5 text-sm font-bold text-white shadow-lg shadow-wc-accent/20 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-wc-accent focus:ring-offset-2 focus:ring-offset-wc-bg transition-all">
                    <span wire:loading.remove wire:target="nextStep" class="flex items-center gap-2">
                        Continuar
                        <svg class="h-4 w-4 transition-transform group-hover:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                        </svg>
                    </span>
                    <span wire:loading wire:target="nextStep" class="flex items-center gap-2">
                        <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                        Validando...
                    </span>
                </button>
            </div>
        </div>
        @endif

        {{-- ----------------------------------------------------------------- --}}
        {{-- STEP 4 — NUTRICION & ESTILO DE VIDA (Método + Elite)              --}}
        {{-- ----------------------------------------------------------------- --}}
        @if($step === 4 && in_array($planType, ['metodo', 'elite']))
        <div
            x-show="true"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4"
            x-transition:enter-end="opacity-100 translate-y-0"
        >
            <div class="mb-8">
                <h2 class="font-display text-4xl tracking-wide text-wc-text">NUTRICION & ESTILO DE VIDA</h2>
                <p class="mt-2 text-sm text-wc-text-secondary">Esta informacion nos permite disenar un plan nutricional que se adapte a tu vida real.</p>
            </div>

            <div class="space-y-6">
                {{-- Actividad laboral --}}
                <div class="rounded-2xl border border-wc-border bg-wc-bg-secondary p-6">
                    <h3 class="mb-4 text-xs font-semibold uppercase tracking-wider text-wc-text-secondary">
                        Nivel de actividad en tu trabajo <span class="text-wc-accent">*</span>
                    </h3>
                    <div class="grid gap-3 sm:grid-cols-3">
                        @foreach(['sedentario' => ['Sedentario', 'Trabajo de escritorio, poco movimiento'], 'moderado' => ['Moderado', 'Estoy de pie o me muevo parte del dia'], 'activo' => ['Activo', 'Trabajo fisico o en movimiento constante']] as $val => [$titulo, $desc])
                        <label class="flex cursor-pointer flex-col gap-2 rounded-xl border p-4 transition-all
                            {{ $trabajo_tipo === $val ? 'border-wc-accent bg-wc-accent/5' : 'border-wc-border bg-wc-bg-tertiary hover:border-wc-accent/40' }}">
                            <input type="radio" wire:model.live="trabajo_tipo" value="{{ $val }}" class="sr-only" />
                            <p class="text-sm font-bold text-wc-text">{{ $titulo }}</p>
                            <p class="text-xs text-wc-text-secondary">{{ $desc }}</p>
                        </label>
                        @endforeach
                    </div>
                    @error('trabajo_tipo')<p class="mt-2 text-xs text-wc-accent">{{ $message }}</p>@enderror
                </div>

                {{-- Sueno y estres --}}
                <div class="rounded-2xl border border-wc-border bg-wc-bg-secondary p-6">
                    <h3 class="mb-4 text-xs font-semibold uppercase tracking-wider text-wc-text-secondary">Descanso y manejo del estres</h3>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-3 block text-xs font-medium text-wc-text-secondary">Horas de sueno promedio <span class="text-wc-accent">*</span></label>
                            <div class="space-y-2">
                                @foreach(['5_menos' => 'Menos de 5 horas', '6_7' => '6-7 horas', '8_mas' => '8 horas o mas'] as $val => $label)
                                <label class="flex cursor-pointer items-center gap-3 rounded-lg border px-4 py-2.5 transition-all
                                    {{ $horas_sueno === $val ? 'border-wc-accent bg-wc-accent/5' : 'border-wc-border hover:border-wc-accent/40' }}">
                                    <input type="radio" wire:model.live="horas_sueno" value="{{ $val }}" class="sr-only" />
                                    <span class="flex h-4 w-4 shrink-0 items-center justify-center rounded-full border-2 {{ $horas_sueno === $val ? 'border-wc-accent' : 'border-wc-border' }}">
                                        @if($horas_sueno === $val)<span class="h-2 w-2 rounded-full bg-wc-accent"></span>@endif
                                    </span>
                                    <span class="text-sm text-wc-text">{{ $label }}</span>
                                </label>
                                @endforeach
                            </div>
                            @error('horas_sueno')<p class="mt-1.5 text-xs text-wc-accent">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="mb-3 block text-xs font-medium text-wc-text-secondary">Nivel de estres <span class="text-wc-accent">*</span></label>
                            <div class="space-y-2">
                                @foreach(['bajo' => 'Bajo — vivo tranquilo', 'moderado' => 'Moderado — algo de estres', 'alto' => 'Alto — estres frecuente', 'muy_alto' => 'Muy alto — estres constante'] as $val => $label)
                                <label class="flex cursor-pointer items-center gap-3 rounded-lg border px-4 py-2.5 transition-all
                                    {{ $nivel_estres === $val ? 'border-wc-accent bg-wc-accent/5' : 'border-wc-border hover:border-wc-accent/40' }}">
                                    <input type="radio" wire:model.live="nivel_estres" value="{{ $val }}" class="sr-only" />
                                    <span class="flex h-4 w-4 shrink-0 items-center justify-center rounded-full border-2 {{ $nivel_estres === $val ? 'border-wc-accent' : 'border-wc-border' }}">
                                        @if($nivel_estres === $val)<span class="h-2 w-2 rounded-full bg-wc-accent"></span>@endif
                                    </span>
                                    <span class="text-sm text-wc-text">{{ $label }}</span>
                                </label>
                                @endforeach
                            </div>
                            @error('nivel_estres')<p class="mt-1.5 text-xs text-wc-accent">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </div>

                {{-- Comidas e intolerancias --}}
                <div class="rounded-2xl border border-wc-border bg-wc-bg-secondary p-6">
                    <h3 class="mb-4 text-xs font-semibold uppercase tracking-wider text-wc-text-secondary">Habitos alimenticios</h3>

                    <div class="mb-5">
                        <label class="mb-3 block text-xs font-medium text-wc-text-secondary">Comidas al dia <span class="text-wc-accent">*</span></label>
                        <div class="flex flex-wrap gap-2.5">
                            @foreach(['2' => '2 comidas', '3' => '3 comidas', '4' => '4 comidas', '5_mas' => '5 o mas'] as $val => $label)
                            <label class="cursor-pointer">
                                <input type="radio" wire:model.live="comidas_por_dia" value="{{ $val }}" class="sr-only" />
                                <span class="inline-flex items-center rounded-xl border px-4 py-2.5 text-sm font-medium transition-all
                                    {{ $comidas_por_dia === $val ? 'border-wc-accent bg-wc-accent/10 text-wc-accent' : 'border-wc-border bg-wc-bg-tertiary text-wc-text-secondary hover:border-wc-accent/40' }}">
                                    {{ $label }}
                                </span>
                            </label>
                            @endforeach
                        </div>
                        @error('comidas_por_dia')<p class="mt-2 text-xs text-wc-accent">{{ $message }}</p>@enderror
                    </div>

                    <div class="mb-5">
                        <label class="mb-3 block text-xs font-medium text-wc-text-secondary">Intolerancias o alergias alimentarias</label>
                        <div class="flex flex-wrap gap-2">
                            @foreach(['lactosa' => 'Lactosa', 'gluten' => 'Gluten', 'mariscos' => 'Mariscos', 'nueces' => 'Nueces', 'huevo' => 'Huevo', 'otras' => 'Otras'] as $val => $label)
                            <label class="cursor-pointer">
                                <input type="checkbox" wire:model.live="intolerancias" value="{{ $val }}" class="sr-only" />
                                <span class="inline-flex items-center rounded-full border px-3.5 py-1.5 text-xs font-medium transition-all
                                    {{ in_array($val, $intolerancias) ? 'border-amber-400/50 bg-amber-500/10 text-amber-300' : 'border-wc-border bg-wc-bg-tertiary text-wc-text-secondary hover:border-amber-400/30' }}">
                                    {{ $label }}
                                </span>
                            </label>
                            @endforeach
                        </div>
                    </div>

                    @if(in_array('otras', $intolerancias))
                    <div class="mb-5">
                        <input type="text" wire:model="otras_intolerancias"
                               placeholder="Especifica tus otras intolerancias o alergias..."
                               class="w-full rounded-lg border border-wc-border bg-wc-bg-tertiary px-4 py-3 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent" />
                    </div>
                    @endif

                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-wc-text-secondary">Alimentos que no comes o prefieres evitar</label>
                        <textarea wire:model="alimentos_evitar" rows="2"
                                  placeholder="Ej: no como cerdo, no me gusta el brocoli..."
                                  class="w-full resize-none rounded-lg border border-wc-border bg-wc-bg-tertiary px-4 py-3 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent"></textarea>
                    </div>
                </div>

                {{-- Suplementos --}}
                <div class="rounded-2xl border border-wc-border bg-wc-bg-secondary p-6">
                    <h3 class="mb-1 text-xs font-semibold uppercase tracking-wider text-wc-text-secondary">Suplementacion actual</h3>
                    <p class="mb-3 text-[11px] text-wc-text-tertiary">Opcional. Lista los suplementos que tomas actualmente.</p>
                    <input type="text" wire:model="suplementos_actuales"
                           placeholder="Ej: proteina de suero, creatina, omega-3..."
                           class="w-full rounded-lg border border-wc-border bg-wc-bg-tertiary px-4 py-3 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent" />
                </div>
            </div>

            <div class="mt-8 flex gap-3">
                <button wire:click="prevStep"
                        class="flex items-center gap-2 rounded-full border border-wc-border bg-wc-bg-secondary px-6 py-3.5 text-sm font-medium text-wc-text-secondary hover:text-wc-text transition-colors">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                    </svg>
                    Atras
                </button>
                <button wire:click="nextStep"
                        wire:loading.attr="disabled"
                        wire:loading.class="opacity-60 cursor-not-allowed"
                        class="group flex flex-1 items-center justify-center gap-2 rounded-full bg-wc-accent py-3.5 text-sm font-bold text-white shadow-lg shadow-wc-accent/20 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-wc-accent focus:ring-offset-2 focus:ring-offset-wc-bg transition-all">
                    <span wire:loading.remove wire:target="nextStep" class="flex items-center gap-2">
                        Continuar
                        <svg class="h-4 w-4 transition-transform group-hover:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                        </svg>
                    </span>
                    <span wire:loading wire:target="nextStep" class="flex items-center gap-2">
                        <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                        Validando...
                    </span>
                </button>
            </div>
        </div>
        @endif

        {{-- ----------------------------------------------------------------- --}}
        {{-- STEP 5 — INFO AVANZADA (Elite only)                               --}}
        {{-- ----------------------------------------------------------------- --}}
        @if($step === 5 && $planType === 'elite')
        <div
            x-show="true"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4"
            x-transition:enter-end="opacity-100 translate-y-0"
        >
            <div class="mb-8">
                <h2 class="font-display text-4xl tracking-wide text-wc-text">INFO AVANZADA</h2>
                <p class="mt-2 text-sm text-wc-text-secondary">Esta informacion es completamente opcional y confidencial. Nos ayuda a personalizar tu protocolo al maximo nivel.</p>
            </div>

            <div class="space-y-6">
                <div class="rounded-2xl border border-wc-border bg-wc-bg-secondary p-6">
                    <div class="mb-5">
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-wc-text-secondary">Objetivo de composicion corporal especifico</label>
                        <p class="mb-3 text-[11px] text-wc-text-tertiary">Ej: llegar a 12% de grasa, ganar 5kg de musculo en 6 meses...</p>
                        <textarea wire:model="objetivo_composicion" rows="3"
                                  placeholder="Describe tu objetivo ideal de composicion corporal..."
                                  class="w-full resize-none rounded-lg border border-wc-border bg-wc-bg-tertiary px-4 py-3 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent"></textarea>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-wc-text-secondary">Historial medico relevante</label>
                        <p class="mb-3 text-[11px] text-wc-text-tertiary">Condiciones medicas, cirugias, tratamientos que debemos tener en cuenta.</p>
                        <textarea wire:model="historial_medico" rows="3"
                                  placeholder="Ej: hipotiroidismo, diabetes tipo 2, cirugia de rodilla en 2022..."
                                  class="w-full resize-none rounded-lg border border-wc-border bg-wc-bg-tertiary px-4 py-3 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent"></textarea>
                    </div>
                </div>

                <div class="rounded-2xl border border-wc-border bg-wc-bg-secondary p-6">
                    <h3 class="mb-4 text-xs font-semibold uppercase tracking-wider text-wc-text-secondary">Informacion hormonal</h3>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-3 block text-xs font-medium text-wc-text-secondary">Ciclo hormonal activo (mujeres)</label>
                            <div class="flex gap-3">
                                @foreach(['no' => 'No aplica', 'si' => 'Si, lo tengo'] as $val => $label)
                                <label class="flex flex-1 cursor-pointer items-center justify-center gap-2 rounded-xl border py-3 text-sm font-medium transition-all
                                    {{ $ciclo_hormonal === $val ? 'border-wc-accent bg-wc-accent/10 text-wc-accent' : 'border-wc-border text-wc-text-secondary hover:border-wc-accent/40' }}">
                                    <input type="radio" wire:model.live="ciclo_hormonal" value="{{ $val }}" class="sr-only" />
                                    {{ $label }}
                                </label>
                                @endforeach
                            </div>
                        </div>
                        <div>
                            <label class="mb-3 block text-xs font-medium text-wc-text-secondary">Tienes resultados de bloodwork recientes?</label>
                            <div class="flex gap-3">
                                @foreach(['no' => 'No', 'si' => 'Si, los tengo'] as $val => $label)
                                <label class="flex flex-1 cursor-pointer items-center justify-center gap-2 rounded-xl border py-3 text-sm font-medium transition-all
                                    {{ $bloodwork_disponible === $val ? 'border-wc-accent bg-wc-accent/10 text-wc-accent' : 'border-wc-border text-wc-text-secondary hover:border-wc-accent/40' }}">
                                    <input type="radio" wire:model.live="bloodwork_disponible" value="{{ $val }}" class="sr-only" />
                                    {{ $label }}
                                </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @if($bloodwork_disponible === 'si')
                    <p class="mt-4 rounded-lg bg-amber-500/10 px-4 py-3 text-xs text-amber-300 ring-1 ring-amber-500/20">
                        Tu coach te pedira los resultados de tu bloodwork en la primera sesion de seguimiento.
                    </p>
                    @endif
                </div>
            </div>

            <div class="mt-8 flex gap-3">
                <button wire:click="prevStep"
                        class="flex items-center gap-2 rounded-full border border-wc-border bg-wc-bg-secondary px-6 py-3.5 text-sm font-medium text-wc-text-secondary hover:text-wc-text transition-colors">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                    </svg>
                    Atras
                </button>
                <button wire:click="nextStep"
                        class="group flex flex-1 items-center justify-center gap-2 rounded-full bg-wc-accent py-3.5 text-sm font-bold text-white shadow-lg shadow-wc-accent/20 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-wc-accent focus:ring-offset-2 focus:ring-offset-wc-bg transition-all">
                    Continuar
                    <svg class="h-4 w-4 transition-transform group-hover:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                    </svg>
                </button>
            </div>
        </div>
        @endif

        {{-- ----------------------------------------------------------------- --}}
        {{-- LAST STEP — CONTRASENA                                            --}}
        {{-- ----------------------------------------------------------------- --}}
        @if($step === $totalSteps)
        <div
            x-show="true"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4"
            x-transition:enter-end="opacity-100 translate-y-0"
        >
            <div class="mb-8">
                <h2 class="font-display text-4xl tracking-wide text-wc-text">CREA TU CUENTA</h2>
                <p class="mt-2 text-sm text-wc-text-secondary">Elige una contrasena segura para proteger tu cuenta de WellCore.</p>
            </div>

            <div class="space-y-6">
                {{-- Summary card --}}
                <div class="rounded-2xl border border-emerald-500/20 bg-emerald-500/5 p-5">
                    <div class="flex items-start gap-3">
                        <svg class="mt-0.5 h-5 w-5 shrink-0 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                        <div>
                            <p class="text-sm font-semibold text-emerald-300">Informacion completada</p>
                            <p class="mt-0.5 text-xs text-wc-text-secondary">Tu perfil esta listo. Solo falta crear tu acceso al portal.</p>
                        </div>
                    </div>
                </div>

                {{-- Password fields --}}
                <div class="rounded-2xl border border-wc-border bg-wc-bg-secondary p-6">
                    <h3 class="mb-4 text-xs font-semibold uppercase tracking-wider text-wc-text-secondary">Contrasena</h3>

                    <div class="mb-4">
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-wc-text-secondary">
                            Nueva contrasena <span class="text-wc-accent">*</span>
                        </label>
                        <div class="relative">
                            <input
                                :type="showPassword ? 'text' : 'password'"
                                wire:model="password"
                                x-model="password"
                                autocomplete="new-password"
                                placeholder="Minimo 8 caracteres"
                                class="w-full rounded-lg border border-wc-border bg-wc-bg-tertiary px-4 py-3 pr-12 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent"
                            />
                            <button type="button"
                                    x-on:click="showPassword = !showPassword"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-wc-text-tertiary hover:text-wc-text"
                                    aria-label="Mostrar u ocultar contrasena">
                                <svg x-show="!showPassword" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                </svg>
                                <svg x-show="showPassword" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                                </svg>
                            </button>
                        </div>

                        {{-- Password strength bar --}}
                        <div class="mt-2" x-show="password.length > 0">
                            <div class="mb-1 flex items-center justify-between">
                                <span class="text-[10px] text-wc-text-tertiary">Seguridad</span>
                                <span class="text-[10px] font-semibold" :class="{
                                    'text-red-400': strength === 1,
                                    'text-amber-400': strength === 2,
                                    'text-yellow-300': strength === 3,
                                    'text-emerald-400': strength === 4
                                }" x-text="strengthLabel"></span>
                            </div>
                            <div class="h-1.5 w-full overflow-hidden rounded-full bg-wc-bg-tertiary">
                                <div class="h-full rounded-full transition-all duration-300"
                                     :class="strengthColor"
                                     :style="'width: ' + strengthWidth"></div>
                            </div>
                            <div class="mt-2 flex flex-wrap gap-x-4 gap-y-1">
                                <span class="text-[10px]" :class="password.length >= 8 ? 'text-emerald-400' : 'text-wc-text-tertiary'">
                                    <span x-text="password.length >= 8 ? '✓' : '○'"></span> 8+ caracteres
                                </span>
                                <span class="text-[10px]" :class="/[A-Z]/.test(password) ? 'text-emerald-400' : 'text-wc-text-tertiary'">
                                    <span x-text="/[A-Z]/.test(password) ? '✓' : '○'"></span> Mayuscula
                                </span>
                                <span class="text-[10px]" :class="/[0-9]/.test(password) ? 'text-emerald-400' : 'text-wc-text-tertiary'">
                                    <span x-text="/[0-9]/.test(password) ? '✓' : '○'"></span> Numero
                                </span>
                                <span class="text-[10px]" :class="/[^A-Za-z0-9]/.test(password) ? 'text-emerald-400' : 'text-wc-text-tertiary'">
                                    <span x-text="/[^A-Za-z0-9]/.test(password) ? '✓' : '○'"></span> Simbolo
                                </span>
                            </div>
                        </div>
                        @error('password')<p class="mt-1.5 text-xs text-wc-accent">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-wc-text-secondary">
                            Confirmar contrasena <span class="text-wc-accent">*</span>
                        </label>
                        <div class="relative">
                            <input
                                :type="showConfirm ? 'text' : 'password'"
                                wire:model="password_confirmation"
                                x-model="passwordConfirm"
                                autocomplete="new-password"
                                placeholder="Repite tu contrasena"
                                class="w-full rounded-lg border border-wc-border bg-wc-bg-tertiary px-4 py-3 pr-12 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent"
                            />
                            <button type="button"
                                    x-on:click="showConfirm = !showConfirm"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-wc-text-tertiary hover:text-wc-text"
                                    aria-label="Mostrar u ocultar confirmacion">
                                <svg x-show="!showConfirm" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                </svg>
                                <svg x-show="showConfirm" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                                </svg>
                            </button>
                        </div>
                        <div class="mt-1.5 h-4">
                            <span x-show="passwordConfirm.length > 0 && password !== passwordConfirm"
                                  class="text-xs text-wc-accent">Las contrasenas no coinciden.</span>
                            <span x-show="passwordConfirm.length > 0 && password === passwordConfirm && password.length > 0"
                                  class="text-xs text-emerald-400">Las contrasenas coinciden.</span>
                        </div>
                        @error('password_confirmation')<p class="mt-1.5 text-xs text-wc-accent">{{ $message }}</p>@enderror
                    </div>
                </div>

                {{-- Plan-specific fields --}}
                @if($planType === 'presencial')
                <div class="rounded-2xl border border-wc-border bg-wc-bg-secondary p-6">
                    <h3 class="mb-4 text-xs font-semibold uppercase tracking-wider text-wc-text-secondary">
                        Horario preferido para entrenar <span class="text-wc-accent">*</span>
                    </h3>
                    <div class="grid gap-3 sm:grid-cols-3">
                        @foreach(['manana' => ['Manana', '6am - 12pm'], 'tarde' => ['Tarde', '12pm - 6pm'], 'noche' => ['Noche', '6pm - 10pm']] as $val => [$titulo, $hora])
                        <label class="flex cursor-pointer flex-col items-center gap-1 rounded-xl border p-4 transition-all
                            {{ $horario_preferido === $val ? 'border-wc-accent bg-wc-accent/5' : 'border-wc-border bg-wc-bg-tertiary hover:border-wc-accent/40' }}">
                            <input type="radio" wire:model.live="horario_preferido" value="{{ $val }}" class="sr-only" />
                            <span class="text-sm font-bold text-wc-text">{{ $titulo }}</span>
                            <span class="text-xs text-wc-text-secondary">{{ $hora }}</span>
                        </label>
                        @endforeach
                    </div>
                    @error('horario_preferido')<p class="mt-2 text-xs text-wc-accent">{{ $message }}</p>@enderror
                </div>
                @endif

                @if($planType === 'rise')
                <div class="rounded-2xl border border-orange-500/20 bg-orange-500/5 p-5">
                    <label class="flex cursor-pointer items-start gap-3">
                        <input type="checkbox" wire:model="compromiso_30dias"
                               class="mt-0.5 h-4 w-4 shrink-0 rounded border-wc-border bg-wc-bg-tertiary text-wc-accent focus:ring-wc-accent focus:ring-offset-wc-bg" />
                        <div>
                            <p class="text-sm font-semibold text-orange-300">Compromiso de 30 dias</p>
                            <p class="mt-1 text-xs text-wc-text-secondary">Me comprometo a completar el programa RISE de 30 dias, siguiendo el plan de entrenamiento, nutricion y habitos que se me asigne. Entiendo que los resultados dependen de mi dedicacion y constancia.</p>
                        </div>
                    </label>
                    @error('compromiso_30dias')<p class="mt-2 text-xs text-wc-accent">{{ $message }}</p>@enderror
                </div>
                @endif

                {{-- Terminos y condiciones --}}
                <div class="rounded-2xl border border-wc-border bg-wc-bg-secondary p-5">
                    <label class="flex cursor-pointer items-start gap-3">
                        <input type="checkbox" wire:model="acepta_terminos"
                               class="mt-0.5 h-4 w-4 shrink-0 rounded border-wc-border bg-wc-bg-tertiary text-wc-accent focus:ring-wc-accent focus:ring-offset-wc-bg" />
                        <p class="text-sm text-wc-text-secondary">
                            He leido y acepto los
                            <a href="{{ route('terminos') }}" target="_blank" class="font-medium text-wc-accent hover:underline">Terminos y Condiciones</a>
                            y la
                            <a href="{{ route('privacidad') }}" target="_blank" class="font-medium text-wc-accent hover:underline">Politica de Privacidad</a>
                            de WellCore Fitness.
                        </p>
                    </label>
                    @error('acepta_terminos')<p class="mt-2 text-xs text-wc-accent">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="mt-8 flex gap-3">
                <button wire:click="prevStep"
                        class="flex items-center gap-2 rounded-full border border-wc-border bg-wc-bg-secondary px-6 py-3.5 text-sm font-medium text-wc-text-secondary hover:text-wc-text transition-colors">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                    </svg>
                    Atras
                </button>
                <button wire:click="submit"
                        wire:loading.attr="disabled"
                        wire:loading.class="opacity-70 cursor-not-allowed"
                        class="group flex flex-1 items-center justify-center gap-2 rounded-full bg-wc-accent py-4 text-sm font-bold text-white shadow-xl shadow-wc-accent/25 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-wc-accent focus:ring-offset-2 focus:ring-offset-wc-bg transition-all disabled:opacity-70"
                        aria-busy="{{ $submitted ? 'true' : 'false' }}">
                    <span wire:loading.remove wire:target="submit" class="flex items-center gap-2">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                        </svg>
                        Crear mi cuenta
                    </span>
                    <span wire:loading wire:target="submit" class="flex items-center gap-2">
                        <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                        Creando tu cuenta...
                    </span>
                </button>
            </div>

            {{-- Submitted fallback --}}
            @if($submitted)
            <div class="mt-8 rounded-2xl border border-emerald-500/30 bg-emerald-500/10 p-6 text-center">
                <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-full bg-emerald-500/20">
                    <svg class="h-7 w-7 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                    </svg>
                </div>
                <p class="text-base font-bold text-emerald-300">Cuenta creada correctamente</p>
                <p class="mt-1 text-sm text-wc-text-secondary">Redirigiendo a tu dashboard...</p>
                <a href="{{ route('client.dashboard') }}" wire:navigate
                   class="mt-4 inline-flex items-center gap-2 text-sm font-medium text-wc-accent hover:underline">
                    Ir al dashboard ahora
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                    </svg>
                </a>
            </div>
            @endif
        </div>
        @endif

    </div>{{-- /max-w-2xl --}}
    @endif{{-- /if not invalid --}}
</div>
