<div class="space-y-6" x-data="{ animateIn: false }" x-init="$nextTick(() => animateIn = true)">

    {{-- ═══ Header ═══ --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="font-display text-3xl tracking-wider text-wc-text">GENERADOR AI DE PLANES</h1>
            <p class="mt-1 text-sm text-wc-text-secondary">Crea planes personalizados con inteligencia artificial en 4 pasos</p>
        </div>
        @if($saved)
            <button wire:click="startNew"
                    class="inline-flex items-center gap-2 rounded-lg bg-wc-bg-tertiary px-4 py-2 text-sm font-medium text-wc-text hover:bg-wc-border transition-colors">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Nuevo Plan
            </button>
        @endif
    </div>

    {{-- ═══ Step Indicator ═══ --}}
    <div class="rounded-xl border border-wc-border bg-wc-bg-secondary p-4 sm:p-6">
        <div class="flex items-center justify-between">
            @php
                $steps = [
                    1 => ['label' => 'Seleccionar', 'sublabel' => 'Cliente'],
                    2 => ['label' => 'Configurar', 'sublabel' => 'Plan'],
                    3 => ['label' => 'Generar', 'sublabel' => 'IA'],
                    4 => ['label' => 'Guardar', 'sublabel' => 'Asignar'],
                ];
            @endphp
            @foreach($steps as $num => $step)
                <div class="flex items-center {{ $num < 4 ? 'flex-1' : '' }}">
                    {{-- Step circle --}}
                    <button wire:click="goToStep({{ $num }})"
                            class="flex flex-col items-center gap-1 {{ $num < $currentStep ? 'cursor-pointer' : 'cursor-default' }}">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full text-sm font-bold transition-all duration-300
                            {{ $num === $currentStep
                                ? 'bg-red-600 text-white ring-4 ring-red-600/20'
                                : ($num < $currentStep
                                    ? 'bg-emerald-500 text-white'
                                    : 'bg-wc-bg-tertiary text-wc-text-tertiary') }}">
                            @if($num < $currentStep)
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                </svg>
                            @else
                                {{ $num }}
                            @endif
                        </div>
                        <span class="hidden text-xs font-semibold sm:block {{ $num === $currentStep ? 'text-red-500' : ($num < $currentStep ? 'text-emerald-500' : 'text-wc-text-tertiary') }}">
                            {{ $step['label'] }}
                        </span>
                        <span class="hidden text-[10px] text-wc-text-tertiary sm:block">{{ $step['sublabel'] }}</span>
                    </button>
                    {{-- Connector line --}}
                    @if($num < 4)
                        <div class="mx-2 h-0.5 flex-1 rounded {{ $num < $currentStep ? 'bg-emerald-500' : 'bg-wc-border' }} transition-colors duration-300"></div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    {{-- ═══ Step 1: Client Selection ═══ --}}
    @if($currentStep === 1)
    <div class="space-y-6">
        {{-- Search --}}
        <div class="rounded-xl border border-wc-border bg-wc-bg-secondary p-6">
            <h2 class="mb-4 text-lg font-semibold text-wc-text">Buscar Cliente</h2>
            <div class="relative">
                <svg class="absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                </svg>
                <input wire:model.live.debounce.300ms="clientSearch"
                       type="text"
                       placeholder="Buscar por nombre o email (min. 2 caracteres)..."
                       class="w-full rounded-lg border border-wc-border bg-wc-bg py-3 pl-10 pr-4 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-red-500 focus:outline-none focus:ring-1 focus:ring-red-500">
            </div>

            {{-- Results --}}
            @if(strlen($clientSearch) >= 2 && count($clients) > 0)
                <div class="mt-4 grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($clients as $client)
                        <button wire:click="selectClient({{ $client->id }})"
                                class="flex items-center gap-3 rounded-lg border p-3 text-left transition-all
                                    {{ $selectedClientId === $client->id
                                        ? 'border-red-500 bg-red-500/10'
                                        : 'border-wc-border bg-wc-bg hover:border-red-500/50 hover:bg-wc-bg-tertiary' }}">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-red-600/20 text-sm font-bold text-red-400">
                                {{ strtoupper(substr($client->name, 0, 1)) }}
                            </div>
                            <div class="min-w-0">
                                <p class="truncate text-sm font-medium text-wc-text">{{ $client->name }}</p>
                                <p class="truncate text-xs text-wc-text-tertiary">{{ $client->email }}</p>
                                <div class="mt-1 flex items-center gap-2">
                                    <span class="inline-flex rounded-full px-1.5 py-0.5 text-[10px] font-semibold
                                        {{ $client->status?->value === 'activo' ? 'bg-emerald-500/10 text-emerald-400' : 'bg-yellow-500/10 text-yellow-400' }}">
                                        {{ ucfirst($client->status?->value ?? '-') }}
                                    </span>
                                    <span class="text-[10px] text-wc-text-tertiary">{{ ucfirst($client->plan?->value ?? '-') }}</span>
                                </div>
                            </div>
                        </button>
                    @endforeach
                </div>
            @elseif(strlen($clientSearch) >= 2)
                <p class="mt-4 text-center text-sm text-wc-text-tertiary">No se encontraron clientes activos</p>
            @endif
        </div>

        {{-- Selected client profile card --}}
        @if($selectedClientData)
        <div class="rounded-xl border border-red-500/30 bg-wc-bg-secondary p-6">
            <div class="flex items-start justify-between">
                <h2 class="text-lg font-semibold text-wc-text">Cliente Seleccionado</h2>
                <button wire:click="clearClient" class="text-xs text-red-400 hover:text-red-300">Cambiar</button>
            </div>
            <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Nombre</p>
                    <p class="mt-1 text-sm font-medium text-wc-text">{{ $selectedClientData['name'] }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Plan</p>
                    <p class="mt-1 text-sm font-medium text-wc-text">{{ ucfirst($selectedClientData['plan']) }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Edad</p>
                    <p class="mt-1 text-sm font-medium text-wc-text">{{ $selectedClientData['age'] ?? '-' }} anos</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Status</p>
                    <span class="mt-1 inline-flex rounded-full px-2 py-0.5 text-xs font-semibold
                        {{ $selectedClientData['status'] === 'activo' ? 'bg-emerald-500/10 text-emerald-400' : 'bg-yellow-500/10 text-yellow-400' }}">
                        {{ ucfirst($selectedClientData['status']) }}
                    </span>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Peso</p>
                    <p class="mt-1 text-sm font-medium text-wc-text">{{ $selectedClientData['peso'] ? $selectedClientData['peso'] . ' kg' : '-' }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Altura</p>
                    <p class="mt-1 text-sm font-medium text-wc-text">{{ $selectedClientData['altura'] ? $selectedClientData['altura'] . ' cm' : '-' }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Objetivo</p>
                    <p class="mt-1 text-sm font-medium text-wc-text">{{ $selectedClientData['objetivo'] }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Nivel</p>
                    <p class="mt-1 text-sm font-medium text-wc-text">{{ ucfirst($selectedClientData['nivel']) }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Lugar Entreno</p>
                    <p class="mt-1 text-sm font-medium text-wc-text">{{ ucfirst($selectedClientData['lugar_entreno']) }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Dias Disponibles</p>
                    <p class="mt-1 text-sm font-medium text-wc-text">
                        {{ is_array($selectedClientData['dias_disponibles']) && count($selectedClientData['dias_disponibles']) > 0
                            ? count($selectedClientData['dias_disponibles']) . ' dias'
                            : '-' }}
                    </p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Ciudad</p>
                    <p class="mt-1 text-sm font-medium text-wc-text">{{ $selectedClientData['city'] }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Inicio</p>
                    <p class="mt-1 text-sm font-medium text-wc-text">{{ $selectedClientData['fecha_inicio'] }}</p>
                </div>
            </div>
            @if($selectedClientData['restricciones'])
                <div class="mt-4">
                    <p class="text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Restricciones</p>
                    <p class="mt-1 text-sm text-yellow-400">{{ $selectedClientData['restricciones'] }}</p>
                </div>
            @endif
        </div>
        @endif
    </div>
    @endif

    {{-- ═══ Step 2: Plan Configuration ═══ --}}
    @if($currentStep === 2)
    <div class="space-y-6">
        {{-- Plan Type Selection --}}
        <div class="rounded-xl border border-wc-border bg-wc-bg-secondary p-6">
            <h2 class="mb-4 text-lg font-semibold text-wc-text">Tipo de Plan</h2>
            <div class="grid gap-4 sm:grid-cols-3">
                @php
                    $planTypes = [
                        'entrenamiento' => ['label' => 'Entrenamiento', 'desc' => 'Plan de ejercicio con periodizacion y progresion', 'color' => 'red', 'icon' => 'M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z'],
                        'nutricion' => ['label' => 'Nutricion', 'desc' => 'Plan alimenticio personalizado con macros y comidas', 'color' => 'emerald', 'icon' => 'M12 8.25v-1.5m0 1.5c-1.355 0-2.697.056-4.024.166C6.845 8.51 6 9.473 6 10.608v2.513m6-4.871c1.355 0 2.697.056 4.024.166C19.155 8.51 20 9.473 20 10.608v2.513M15 8.25v-1.5m-6 1.5v-1.5m12 9.75l-1.5.75a3.354 3.354 0 0 1-3 0 3.354 3.354 0 0 0-3 0 3.354 3.354 0 0 1-3 0 3.354 3.354 0 0 0-3 0 3.354 3.354 0 0 1-3 0L3 16.5m15-3.379a48.474 48.474 0 0 0-6-.371c-2.032 0-4.034.126-6 .371m12 0c.39.049.777.102 1.163.16 1.07.16 1.837 1.094 1.837 2.175v5.169c0 .621-.504 1.125-1.125 1.125H4.125A1.125 1.125 0 0 1 3 20.625v-5.17c0-1.08.768-2.014 1.837-2.174A47.78 47.78 0 0 1 6 13.12M12.265 3.11a.375.375 0 1 1-.53 0L12 2.845l.265.265Z'],
                        'habitos' => ['label' => 'Habitos', 'desc' => 'Plan de habitos saludables progresivos', 'color' => 'violet', 'icon' => 'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'],
                    ];
                @endphp
                @foreach($planTypes as $key => $pt)
                    <button wire:click="selectPlanType('{{ $key }}')"
                            class="group flex flex-col items-center gap-3 rounded-xl border-2 p-6 text-center transition-all
                                {{ $planType === $key
                                    ? "border-{$pt['color']}-500 bg-{$pt['color']}-500/10"
                                    : 'border-wc-border bg-wc-bg hover:border-wc-text-tertiary' }}">
                        <div class="flex h-14 w-14 items-center justify-center rounded-xl transition-colors
                            {{ $planType === $key ? "bg-{$pt['color']}-500/20 text-{$pt['color']}-400" : 'bg-wc-bg-tertiary text-wc-text-tertiary group-hover:text-wc-text' }}">
                            <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $pt['icon'] }}" />
                            </svg>
                        </div>
                        <div>
                            <p class="font-semibold text-wc-text">{{ $pt['label'] }}</p>
                            <p class="mt-1 text-xs text-wc-text-tertiary">{{ $pt['desc'] }}</p>
                        </div>
                    </button>
                @endforeach
            </div>
        </div>

        {{-- Methodology Selection (Training) --}}
        @if($planType === 'entrenamiento')
        <div class="rounded-xl border border-wc-border bg-wc-bg-secondary p-6">
            <h2 class="mb-4 text-lg font-semibold text-wc-text">Metodologia de Entrenamiento</h2>
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                @foreach($this->methodologies['training'] as $key => $method)
                    <button wire:click="selectMethodology('{{ $key }}')"
                            class="flex items-start gap-3 rounded-lg border p-3 text-left transition-all
                                {{ $methodology === $key
                                    ? 'border-red-500 bg-red-500/10'
                                    : 'border-wc-border bg-wc-bg hover:border-red-500/50' }}">
                        <div class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg
                            {{ $methodology === $key ? 'bg-red-500/20 text-red-400' : 'bg-wc-bg-tertiary text-wc-text-tertiary' }}">
                            @switch($method['icon'])
                                @case('arrow-trending-up')
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18 9 11.25l4.306 4.306a11.95 11.95 0 0 1 5.814-5.518l2.74-1.22m0 0-5.94-2.281m5.94 2.28-2.28 5.941" /></svg>
                                    @break
                                @case('chart-bar')
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" /></svg>
                                    @break
                                @case('squares-2x2')
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" /></svg>
                                    @break
                                @case('bolt')
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m3.75 13.5 10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75Z" /></svg>
                                    @break
                                @case('fire')
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.362 5.214A8.252 8.252 0 0 1 12 21 8.25 8.25 0 0 1 6.038 7.047 8.287 8.287 0 0 0 9 9.601a8.983 8.983 0 0 1 3.361-6.867 8.21 8.21 0 0 0 3 2.48Z" /></svg>
                                    @break
                                @case('trophy')
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 0 1 3 3h-15a3 3 0 0 1 3-3m9 0v-4.5A3.375 3.375 0 0 0 13.125 12h-.75m0 0v6.75m0-6.75H9.375A3.375 3.375 0 0 0 6 15.375v3.375m7.5-9V3.375c0-.621-.504-1.125-1.125-1.125h-.75a1.125 1.125 0 0 0-1.125 1.125V6" /></svg>
                                    @break
                                @case('arrows-right-left')
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" /></svg>
                                    @break
                                @case('arrows-pointing-out')
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3.75v4.5m0-4.5h4.5m-4.5 0L9 9m10.5-6v4.5m0-4.5h-4.5m4.5 0L15 9m-10.5 6v4.5m0-4.5H9m-5.25 0L9 15m10.5 0v4.5m0-4.5H15m4.5 0L15 15" /></svg>
                                    @break
                                @case('arrows-up-down')
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7.5 7.5 3m0 0L12 7.5M7.5 3v13.5m13.5 0L16.5 21m0 0L12 16.5m4.5 4.5V7.5" /></svg>
                                    @break
                                @case('user')
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" /></svg>
                                    @break
                                @case('clock')
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                                    @break
                                @case('sparkles')
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.455 2.456L21.75 6l-1.036.259a3.375 3.375 0 0 0-2.455 2.456ZM16.894 20.567 16.5 21.75l-.394-1.183a2.25 2.25 0 0 0-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 0 0 1.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 0 0 1.423 1.423l1.183.394-1.183.394a2.25 2.25 0 0 0-1.423 1.423Z" /></svg>
                                    @break
                                @case('hand-raised')
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.05 4.575a1.575 1.575 0 1 0-3.15 0v3.15m3.15-3.15v-1.108c0-.87.705-1.575 1.575-1.575s1.575.705 1.575 1.575v1.108m-3.15 0H8.475m3.15 0h1.575m-1.575 0v3.15m0-3.15h1.575m-1.575 3.15v3.75a3.375 3.375 0 0 0 3.375 3.375h.405M10.05 7.725V4.575m0 3.15H6.9m3.15 0h1.575m-1.575 0v3.15m0 3.75V10.875m0 3.75h.405A3.375 3.375 0 0 0 14.4 11.25V7.725m0 0h1.575m-1.575 0v-3.15m0 3.15h1.575m-1.575 3.15V7.725m0 3.15h1.575" /></svg>
                                    @break
                                @case('scale')
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v17.25m0 0c-1.472 0-2.882.265-4.185.75M12 20.25c1.472 0 2.882.265 4.185.75M18.75 4.97A48.416 48.416 0 0 0 12 4.5c-2.291 0-4.545.16-6.75.47m13.5 0c1.01.143 2.01.317 3 .52m-3-.52 2.62 10.726c.122.499-.106 1.028-.589 1.202a5.988 5.988 0 0 1-2.031.352 5.988 5.988 0 0 1-2.031-.352c-.483-.174-.711-.703-.59-1.202L18.75 4.971Zm-16.5.52c.99-.203 1.99-.377 3-.52m0 0 2.62 10.726c.122.499-.106 1.028-.589 1.202a5.989 5.989 0 0 1-2.031.352 5.989 5.989 0 0 1-2.031-.352c-.483-.174-.711-.703-.59-1.202L5.25 4.971Z" /></svg>
                                    @break
                                @case('beaker')
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v5.714a2.25 2.25 0 0 1-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 0 1 4.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0 1 12 15a9.065 9.065 0 0 0-6.23.693L5 14.5m14.8.8 1.402 1.402c1.232 1.232.65 3.318-1.067 3.611l-.417.068a18.749 18.749 0 0 1-6.435.074l-.417-.068c-1.717-.293-2.3-2.379-1.067-3.61L13 15.5" /></svg>
                                    @break
                                @case('heart')
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" /></svg>
                                    @break
                                @default
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09Z" /></svg>
                            @endswitch
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-wc-text">{{ $method['name'] }}</p>
                            <p class="mt-0.5 text-xs leading-relaxed text-wc-text-tertiary">{{ $method['desc'] }}</p>
                        </div>
                    </button>
                @endforeach
            </div>
        </div>

        {{-- Training Parameters --}}
        <div class="rounded-xl border border-wc-border bg-wc-bg-secondary p-6">
            <h2 class="mb-4 text-lg font-semibold text-wc-text">Parametros de Entrenamiento</h2>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <div>
                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Meta Principal</label>
                    <select wire:model="trainingGoal"
                            class="w-full rounded-lg border border-wc-border bg-wc-bg px-3 py-2.5 text-sm text-wc-text focus:border-red-500 focus:outline-none focus:ring-1 focus:ring-red-500">
                        <option value="hipertrofia">Hipertrofia (masa muscular)</option>
                        <option value="fuerza">Fuerza maxima</option>
                        <option value="resistencia">Resistencia muscular</option>
                        <option value="perdida_grasa">Perdida de grasa</option>
                        <option value="rendimiento">Rendimiento deportivo</option>
                    </select>
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Nivel de Experiencia</label>
                    <select wire:model="experienceLevel"
                            class="w-full rounded-lg border border-wc-border bg-wc-bg px-3 py-2.5 text-sm text-wc-text focus:border-red-500 focus:outline-none focus:ring-1 focus:ring-red-500">
                        <option value="principiante">Principiante (< 1 ano)</option>
                        <option value="intermedio">Intermedio (1-3 anos)</option>
                        <option value="avanzado">Avanzado (3+ anos)</option>
                    </select>
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Lesiones / Limitaciones</label>
                    <input wire:model="injuries" type="text" placeholder="Ej: dolor lumbar, tendinitis hombro..."
                           class="w-full rounded-lg border border-wc-border bg-wc-bg px-3 py-2.5 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-red-500 focus:outline-none focus:ring-1 focus:ring-red-500">
                </div>
            </div>

            {{-- Equipment --}}
            <div class="mt-4">
                <label class="mb-2 block text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Equipamiento Disponible</label>
                <div class="flex flex-wrap gap-2">
                    @foreach(['Gym completo', 'Mancuernas', 'Barra', 'Kettlebells', 'Bandas elasticas', 'Maquinas cable', 'TRX', 'Solo peso corporal'] as $eq)
                        <button wire:click="toggleEquipment('{{ $eq }}')"
                                class="rounded-full border px-3 py-1.5 text-xs font-medium transition-colors
                                    {{ in_array($eq, $equipmentAvailable)
                                        ? 'border-red-500 bg-red-500/10 text-red-400'
                                        : 'border-wc-border bg-wc-bg text-wc-text-secondary hover:border-red-500/50' }}">
                            {{ $eq }}
                        </button>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        {{-- Methodology Selection (Nutrition) --}}
        @if($planType === 'nutricion')
        <div class="rounded-xl border border-wc-border bg-wc-bg-secondary p-6">
            <h2 class="mb-4 text-lg font-semibold text-wc-text">Enfoque Nutricional</h2>
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                @foreach($this->methodologies['nutrition'] as $key => $method)
                    <button wire:click="selectMethodology('{{ $key }}')"
                            class="flex flex-col items-center gap-3 rounded-lg border p-4 text-center transition-all
                                {{ $methodology === $key
                                    ? 'border-emerald-500 bg-emerald-500/10'
                                    : 'border-wc-border bg-wc-bg hover:border-emerald-500/50' }}">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl
                            {{ $methodology === $key ? 'bg-emerald-500/20 text-emerald-400' : 'bg-wc-bg-tertiary text-wc-text-tertiary' }}">
                            @if($method['icon'] === 'calculator')
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 15.75V18m-7.5-6.75h.008v.008H8.25v-.008Zm0 2.25h.008v.008H8.25V13.5Zm0 2.25h.008v.008H8.25v-.008Zm0 2.25h.008v.008H8.25V18Zm2.498-6.75h.007v.008h-.007v-.008Zm0 2.25h.007v.008h-.007V13.5Zm0 2.25h.007v.008h-.007v-.008Zm0 2.25h.007v.008h-.007V18Zm2.504-6.75h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V13.5Zm0 2.25h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V18Zm2.498-6.75h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V13.5ZM8.25 6h7.5v2.25h-7.5V6ZM12 2.25c-1.892 0-3.758.11-5.593.322C5.307 2.7 4.5 3.65 4.5 4.757V19.5a2.25 2.25 0 0 0 2.25 2.25h10.5a2.25 2.25 0 0 0 2.25-2.25V4.757c0-1.108-.806-2.057-1.907-2.185A48.507 48.507 0 0 0 12 2.25Z" /></svg>
                            @elseif($method['icon'] === 'fire')
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.362 5.214A8.252 8.252 0 0 1 12 21 8.25 8.25 0 0 1 6.038 7.047 8.287 8.287 0 0 0 9 9.601a8.983 8.983 0 0 1 3.361-6.867 8.21 8.21 0 0 0 3 2.48Z" /></svg>
                            @elseif($method['icon'] === 'arrow-trending-up')
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18 9 11.25l4.306 4.306a11.95 11.95 0 0 1 5.814-5.518l2.74-1.22m0 0-5.94-2.281m5.94 2.28-2.28 5.941" /></svg>
                            @else
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 0 0 8.716-6.747M12 21a9.004 9.004 0 0 1-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 0 1 7.843 4.582M12 3a8.997 8.997 0 0 0-7.843 4.582m15.686 0A11.953 11.953 0 0 1 12 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0 1 21 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0 1 12 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 0 1 3 12c0-1.605.42-3.113 1.157-4.418" /></svg>
                            @endif
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-wc-text">{{ $method['name'] }}</p>
                            <p class="mt-1 text-xs text-wc-text-tertiary">{{ $method['desc'] }}</p>
                        </div>
                    </button>
                @endforeach
            </div>
        </div>

        {{-- Nutrition Parameters --}}
        <div class="rounded-xl border border-wc-border bg-wc-bg-secondary p-6">
            <h2 class="mb-4 text-lg font-semibold text-wc-text">Parametros Nutricionales</h2>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <div>
                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Calorias Objetivo</label>
                    <input wire:model="calorieTarget" type="number" min="1200" max="5000" step="100"
                           class="w-full rounded-lg border border-wc-border bg-wc-bg px-3 py-2.5 text-sm text-wc-text focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Comidas al Dia</label>
                    <select wire:model="mealsPerDay"
                            class="w-full rounded-lg border border-wc-border bg-wc-bg px-3 py-2.5 text-sm text-wc-text focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                        @for($i = 3; $i <= 6; $i++)
                            <option value="{{ $i }}">{{ $i }} comidas</option>
                        @endfor
                    </select>
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Restricciones Dieteticas</label>
                    <input wire:model="dietaryRestrictions" type="text" placeholder="Ej: sin lacteos, sin gluten..."
                           class="w-full rounded-lg border border-wc-border bg-wc-bg px-3 py-2.5 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                </div>
            </div>

            {{-- Macro Split --}}
            <div class="mt-4">
                <label class="mb-2 block text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Distribucion de Macros</label>
                <div class="grid gap-4 sm:grid-cols-3">
                    <div>
                        <label class="mb-1 block text-xs text-wc-text-secondary">Proteina: {{ $proteinPct }}%</label>
                        <input wire:model.live="proteinPct" type="range" min="15" max="50" step="5"
                               class="w-full accent-red-500">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs text-wc-text-secondary">Carbohidratos: {{ $carbsPct }}%</label>
                        <input wire:model.live="carbsPct" type="range" min="10" max="60" step="5"
                               class="w-full accent-amber-500">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs text-wc-text-secondary">Grasas: {{ $fatPct }}%</label>
                        <input wire:model.live="fatPct" type="range" min="15" max="60" step="5"
                               class="w-full accent-blue-500">
                    </div>
                </div>
                @if(($proteinPct + $carbsPct + $fatPct) !== 100)
                    <p class="mt-2 text-xs text-yellow-400">Total: {{ $proteinPct + $carbsPct + $fatPct }}% — debe sumar 100%</p>
                @else
                    <p class="mt-2 text-xs text-emerald-400">Total: 100%</p>
                @endif
            </div>
        </div>
        @endif

        {{-- Habits Focus Areas --}}
        @if($planType === 'habitos')
        <div class="rounded-xl border border-wc-border bg-wc-bg-secondary p-6">
            <h2 class="mb-4 text-lg font-semibold text-wc-text">Areas de Enfoque</h2>
            <p class="mb-4 text-xs text-wc-text-tertiary">Selecciona una o mas areas para el plan de habitos</p>
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($this->habitAreas as $key => $area)
                    <button wire:click="toggleHabitArea('{{ $key }}')"
                            class="flex items-start gap-3 rounded-lg border p-4 text-left transition-all
                                {{ in_array($key, $habitFocusAreas)
                                    ? 'border-violet-500 bg-violet-500/10'
                                    : 'border-wc-border bg-wc-bg hover:border-violet-500/50' }}">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg
                            {{ in_array($key, $habitFocusAreas) ? 'bg-violet-500/20 text-violet-400' : 'bg-wc-bg-tertiary text-wc-text-tertiary' }}">
                            @if($area['icon'] === 'moon')
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z" /></svg>
                            @elseif($area['icon'] === 'beaker')
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v5.714a2.25 2.25 0 0 1-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 0 1 4.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0 1 12 15a9.065 9.065 0 0 0-6.23.693L5 14.5m14.8.8 1.402 1.402c1.232 1.232.65 3.318-1.067 3.611A18.749 18.749 0 0 1 12 21a18.75 18.75 0 0 1-8.435-1.387c-1.717-.293-2.3-2.379-1.067-3.61L5 14.5" /></svg>
                            @elseif($area['icon'] === 'heart')
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" /></svg>
                            @elseif($area['icon'] === 'arrows-pointing-out')
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3.75v4.5m0-4.5h4.5m-4.5 0L9 9m10.5-6v4.5m0-4.5h-4.5m4.5 0L15 9m-10.5 6v4.5m0-4.5H9m-5.25 0L9 15m10.5 0v4.5m0-4.5H15m4.5 0L15 15" /></svg>
                            @elseif($area['icon'] === 'clock')
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                            @else
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09Z" /></svg>
                            @endif
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-wc-text">{{ $area['name'] }}</p>
                            <p class="mt-0.5 text-xs text-wc-text-tertiary">{{ $area['desc'] }}</p>
                        </div>
                    </button>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Duration & Frequency (shown for all plan types) --}}
        @if($planType)
        <div class="rounded-xl border border-wc-border bg-wc-bg-secondary p-6">
            <h2 class="mb-4 text-lg font-semibold text-wc-text">Duracion y Frecuencia</h2>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-2 block text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Duracion (semanas)</label>
                    <div class="flex gap-2">
                        @foreach([4, 8, 12, 16] as $w)
                            <button wire:click="$set('durationWeeks', {{ $w }})"
                                    class="flex-1 rounded-lg border py-2.5 text-center text-sm font-medium transition-colors
                                        {{ $durationWeeks === $w
                                            ? 'border-red-500 bg-red-500/10 text-red-400'
                                            : 'border-wc-border bg-wc-bg text-wc-text-secondary hover:border-red-500/50' }}">
                                {{ $w }}s
                            </button>
                        @endforeach
                    </div>
                </div>
                <div>
                    <label class="mb-2 block text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Frecuencia (dias/semana)</label>
                    <div class="flex gap-2">
                        @foreach([2, 3, 4, 5, 6] as $d)
                            <button wire:click="$set('frequency', {{ $d }})"
                                    class="flex-1 rounded-lg border py-2.5 text-center text-sm font-medium transition-colors
                                        {{ $frequency === $d
                                            ? 'border-red-500 bg-red-500/10 text-red-400'
                                            : 'border-wc-border bg-wc-bg text-wc-text-secondary hover:border-red-500/50' }}">
                                {{ $d }}d
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
    @endif

    {{-- ═══ Step 3: AI Generation ═══ --}}
    @if($currentStep === 3)
    <div class="space-y-6">
        {{-- Summary --}}
        <div class="rounded-xl border border-wc-border bg-wc-bg-secondary p-6">
            <h2 class="mb-4 text-lg font-semibold text-wc-text">Resumen de Configuracion</h2>
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-lg bg-wc-bg p-3">
                    <p class="text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary">Cliente</p>
                    <p class="mt-1 text-sm font-medium text-wc-text">{{ $selectedClientData['name'] ?? '-' }}</p>
                </div>
                <div class="rounded-lg bg-wc-bg p-3">
                    <p class="text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary">Tipo de Plan</p>
                    <p class="mt-1 text-sm font-medium text-wc-text capitalize">{{ $planType }}</p>
                </div>
                <div class="rounded-lg bg-wc-bg p-3">
                    <p class="text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary">
                        {{ $planType === 'habitos' ? 'Areas de Enfoque' : 'Metodologia' }}
                    </p>
                    <p class="mt-1 text-sm font-medium text-wc-text">
                        @if($planType === 'entrenamiento')
                            {{ $this->methodologies['training'][$methodology]['name'] ?? $methodology }}
                        @elseif($planType === 'nutricion')
                            {{ $this->methodologies['nutrition'][$methodology]['name'] ?? $methodology }}
                        @else
                            {{ implode(', ', array_map(fn($a) => $this->habitAreas[$a]['name'] ?? $a, $habitFocusAreas)) }}
                        @endif
                    </p>
                </div>
                <div class="rounded-lg bg-wc-bg p-3">
                    <p class="text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary">Duracion</p>
                    <p class="mt-1 text-sm font-medium text-wc-text">{{ $durationWeeks }} semanas / {{ $frequency }} dias/sem</p>
                </div>
            </div>

            @if($planType === 'entrenamiento')
                <div class="mt-3 grid gap-3 sm:grid-cols-3">
                    <div class="rounded-lg bg-wc-bg p-3">
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary">Meta</p>
                        <p class="mt-1 text-sm font-medium text-wc-text capitalize">{{ str_replace('_', ' ', $trainingGoal) }}</p>
                    </div>
                    <div class="rounded-lg bg-wc-bg p-3">
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary">Nivel</p>
                        <p class="mt-1 text-sm font-medium text-wc-text capitalize">{{ $experienceLevel }}</p>
                    </div>
                    <div class="rounded-lg bg-wc-bg p-3">
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary">Equipamiento</p>
                        <p class="mt-1 text-sm font-medium text-wc-text">{{ !empty($equipmentAvailable) ? implode(', ', $equipmentAvailable) : 'Gym completo' }}</p>
                    </div>
                </div>
            @endif

            @if($planType === 'nutricion')
                <div class="mt-3 grid gap-3 sm:grid-cols-3">
                    <div class="rounded-lg bg-wc-bg p-3">
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary">Calorias</p>
                        <p class="mt-1 text-sm font-medium text-wc-text">{{ number_format($calorieTarget) }} kcal</p>
                    </div>
                    <div class="rounded-lg bg-wc-bg p-3">
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary">Macros</p>
                        <p class="mt-1 text-sm font-medium text-wc-text">P{{ $proteinPct }}% / C{{ $carbsPct }}% / G{{ $fatPct }}%</p>
                    </div>
                    <div class="rounded-lg bg-wc-bg p-3">
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary">Comidas</p>
                        <p class="mt-1 text-sm font-medium text-wc-text">{{ $mealsPerDay }} al dia</p>
                    </div>
                </div>
            @endif
        </div>

        {{-- Generate Button --}}
        @if(!$planGenerated)
        <div class="flex justify-center">
            <button wire:click="generatePlan"
                    wire:loading.attr="disabled"
                    wire:target="generatePlan"
                    class="group relative inline-flex items-center gap-3 rounded-xl bg-gradient-to-r from-red-600 to-red-700 px-8 py-4 text-base font-bold text-white shadow-lg shadow-red-600/25 transition-all hover:from-red-500 hover:to-red-600 hover:shadow-xl hover:shadow-red-600/30 disabled:opacity-50 disabled:cursor-not-allowed">
                <div wire:loading.remove wire:target="generatePlan">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.455 2.456L21.75 6l-1.036.259a3.375 3.375 0 0 0-2.455 2.456Z" />
                    </svg>
                </div>
                <div wire:loading wire:target="generatePlan" class="flex items-center gap-2">
                    <svg class="h-5 w-5 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>
                <span wire:loading.remove wire:target="generatePlan">Generar Plan con IA</span>
                <span wire:loading wire:target="generatePlan">Generando plan...</span>
            </button>
        </div>
        @endif

        {{-- Generation Error --}}
        @if($generationError)
            <div class="rounded-lg border border-red-500/30 bg-red-500/10 p-4">
                <p class="text-sm text-red-400">{{ $generationError }}</p>
            </div>
        @endif

        {{-- Generated Plan Preview --}}
        @if($planGenerated && $generatedPlan)
        <div class="rounded-xl border border-emerald-500/30 bg-wc-bg-secondary p-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-500/20">
                        <svg class="h-5 w-5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-wc-text">Plan Generado</h2>
                        <p class="text-xs text-wc-text-tertiary">
                            @if(($generatedPlan['generated_by'] ?? '') === 'template')
                                Generado con plantilla estructurada (API key no configurada)
                            @else
                                Generado con Claude AI
                            @endif
                        </p>
                    </div>
                </div>
                <button wire:click="toggleRawJson"
                        class="rounded-lg border border-wc-border px-3 py-1.5 text-xs font-medium text-wc-text-secondary hover:bg-wc-bg-tertiary transition-colors">
                    {{ $showRawJson ? 'Vista Formato' : 'Ver JSON' }}
                </button>
            </div>

            @if($showRawJson)
                {{-- Raw JSON editor --}}
                <div class="mt-4">
                    <textarea wire:model="generatedPlanJson"
                              wire:change="updateGeneratedJson"
                              rows="20"
                              class="w-full rounded-lg border border-wc-border bg-wc-bg p-4 font-mono text-xs text-wc-text focus:border-red-500 focus:outline-none focus:ring-1 focus:ring-red-500"
                              spellcheck="false"></textarea>
                    @error('generatedPlanJson')
                        <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            @else
                {{-- Formatted Preview --}}
                <div class="mt-4 space-y-4">
                    @if($planType === 'entrenamiento' && isset($generatedPlan['weeks']))
                        @foreach($generatedPlan['weeks'] as $week)
                            <div class="rounded-lg border border-wc-border bg-wc-bg p-4">
                                <h3 class="text-sm font-bold text-red-400">
                                    Semana {{ $week['week'] ?? '?' }}
                                    @if(isset($week['focus']))
                                        <span class="ml-2 font-normal text-wc-text-tertiary">— {{ $week['focus'] }}</span>
                                    @endif
                                </h3>
                                @if(isset($week['sessions']))
                                    <div class="mt-3 space-y-3">
                                        @foreach($week['sessions'] as $session)
                                            <div class="rounded-lg bg-wc-bg-secondary p-3">
                                                <div class="flex items-center gap-2">
                                                    <span class="flex h-6 w-6 items-center justify-center rounded-full bg-red-600/20 text-[10px] font-bold text-red-400">
                                                        D{{ $session['day'] ?? '?' }}
                                                    </span>
                                                    <span class="text-sm font-semibold text-wc-text">{{ $session['name'] ?? 'Sesion' }}</span>
                                                    @if(isset($session['muscle_groups']))
                                                        <span class="text-xs text-wc-text-tertiary">{{ implode(', ', (array)$session['muscle_groups']) }}</span>
                                                    @endif
                                                </div>
                                                @if(isset($session['exercises']))
                                                    <div class="mt-2 overflow-x-auto">
                                                        <table class="w-full text-xs">
                                                            <thead>
                                                                <tr class="text-wc-text-tertiary">
                                                                    <th class="pb-1 text-left font-semibold">Ejercicio</th>
                                                                    <th class="pb-1 text-center font-semibold">Series</th>
                                                                    <th class="pb-1 text-center font-semibold">Reps</th>
                                                                    <th class="pb-1 text-center font-semibold">Descanso</th>
                                                                    <th class="pb-1 text-left font-semibold">Notas</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody class="text-wc-text">
                                                                @foreach($session['exercises'] as $ex)
                                                                    @if(is_array($ex) && !empty($ex))
                                                                    <tr class="border-t border-wc-border/50">
                                                                        <td class="py-1.5 font-medium">{{ $ex['name'] ?? '-' }}</td>
                                                                        <td class="py-1.5 text-center font-data">{{ $ex['sets'] ?? '-' }}</td>
                                                                        <td class="py-1.5 text-center font-data">{{ $ex['reps'] ?? '-' }}</td>
                                                                        <td class="py-1.5 text-center font-data">{{ $ex['rest'] ?? '-' }}</td>
                                                                        <td class="py-1.5 text-wc-text-tertiary">{{ $ex['notes'] ?? '' }}</td>
                                                                    </tr>
                                                                    @endif
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endforeach

                        @if(isset($generatedPlan['progression_notes']))
                            <div class="rounded-lg bg-wc-bg p-3">
                                <p class="text-xs font-semibold text-wc-text-tertiary">Progresion</p>
                                <p class="mt-1 text-sm text-wc-text">{{ $generatedPlan['progression_notes'] }}</p>
                            </div>
                        @endif

                    @elseif($planType === 'nutricion')
                        {{-- Macros summary --}}
                        @if(isset($generatedPlan['macros']))
                            <div class="grid gap-3 sm:grid-cols-4">
                                <div class="rounded-lg bg-wc-bg p-3 text-center">
                                    <p class="text-[10px] font-semibold uppercase text-wc-text-tertiary">Calorias</p>
                                    <p class="mt-1 font-data text-xl font-bold text-wc-text">{{ number_format($generatedPlan['calories'] ?? 0) }}</p>
                                    <p class="text-[10px] text-wc-text-tertiary">kcal/dia</p>
                                </div>
                                <div class="rounded-lg bg-wc-bg p-3 text-center">
                                    <p class="text-[10px] font-semibold uppercase text-red-400">Proteina</p>
                                    <p class="mt-1 font-data text-xl font-bold text-wc-text">{{ $generatedPlan['macros']['protein_g'] ?? 0 }}g</p>
                                    <p class="text-[10px] text-wc-text-tertiary">{{ $generatedPlan['macros']['protein_pct'] ?? 0 }}%</p>
                                </div>
                                <div class="rounded-lg bg-wc-bg p-3 text-center">
                                    <p class="text-[10px] font-semibold uppercase text-amber-400">Carbos</p>
                                    <p class="mt-1 font-data text-xl font-bold text-wc-text">{{ $generatedPlan['macros']['carbs_g'] ?? 0 }}g</p>
                                    <p class="text-[10px] text-wc-text-tertiary">{{ $generatedPlan['macros']['carbs_pct'] ?? 0 }}%</p>
                                </div>
                                <div class="rounded-lg bg-wc-bg p-3 text-center">
                                    <p class="text-[10px] font-semibold uppercase text-blue-400">Grasas</p>
                                    <p class="mt-1 font-data text-xl font-bold text-wc-text">{{ $generatedPlan['macros']['fat_g'] ?? 0 }}g</p>
                                    <p class="text-[10px] text-wc-text-tertiary">{{ $generatedPlan['macros']['fat_pct'] ?? 0 }}%</p>
                                </div>
                            </div>
                        @endif

                        {{-- Meals --}}
                        @if(isset($generatedPlan['meal_plan']))
                            @foreach($generatedPlan['meal_plan'] as $meal)
                                <div class="rounded-lg border border-wc-border bg-wc-bg p-4">
                                    <div class="flex items-center justify-between">
                                        <h3 class="text-sm font-bold text-emerald-400">{{ $meal['name'] ?? 'Comida' }}</h3>
                                        <span class="text-xs text-wc-text-tertiary">{{ $meal['time'] ?? '' }} &middot; ~{{ $meal['calories'] ?? 0 }} kcal</span>
                                    </div>
                                    @if(isset($meal['foods']))
                                        <div class="mt-2 space-y-1">
                                            @foreach($meal['foods'] as $food)
                                                <div class="flex items-center justify-between text-xs">
                                                    <span class="text-wc-text">{{ $food['name'] ?? '' }} <span class="text-wc-text-tertiary">({{ $food['quantity'] ?? '' }})</span></span>
                                                    <span class="font-data text-wc-text-tertiary">P{{ $food['protein'] ?? 0 }} C{{ $food['carbs'] ?? 0 }} G{{ $food['fat'] ?? 0 }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        @endif

                    @elseif($planType === 'habitos')
                        {{-- Habits list --}}
                        @if(isset($generatedPlan['habits']))
                            @foreach($generatedPlan['habits'] as $habit)
                                <div class="rounded-lg border border-wc-border bg-wc-bg p-4">
                                    <div class="flex items-center gap-2">
                                        <span class="rounded-full bg-violet-500/10 px-2 py-0.5 text-[10px] font-semibold text-violet-400">{{ $habit['area'] ?? '' }}</span>
                                        <span class="text-xs text-wc-text-tertiary">{{ $habit['frequency'] ?? '' }}</span>
                                    </div>
                                    <p class="mt-2 text-sm font-medium text-wc-text">{{ $habit['habit'] ?? '' }}</p>
                                    <div class="mt-2 flex items-center gap-4 text-xs text-wc-text-tertiary">
                                        <span>Metrica: {{ $habit['metric'] ?? '' }}</span>
                                        <span>Meta: {{ $habit['target'] ?? '' }}</span>
                                    </div>
                                </div>
                            @endforeach
                        @endif

                        @if(isset($generatedPlan['daily_routine']))
                            <div class="rounded-lg border border-wc-border bg-wc-bg p-4">
                                <h3 class="text-sm font-bold text-violet-400">Rutina Diaria</h3>
                                <div class="mt-3 grid gap-3 sm:grid-cols-3">
                                    @foreach(['morning' => 'Manana', 'afternoon' => 'Tarde', 'evening' => 'Noche'] as $key => $label)
                                        @if(isset($generatedPlan['daily_routine'][$key]))
                                            <div>
                                                <p class="text-xs font-semibold text-wc-text-secondary">{{ $label }}</p>
                                                <ul class="mt-1 space-y-1">
                                                    @foreach($generatedPlan['daily_routine'][$key] as $item)
                                                        <li class="flex items-start gap-1.5 text-xs text-wc-text">
                                                            <span class="mt-1 h-1.5 w-1.5 shrink-0 rounded-full bg-violet-500"></span>
                                                            {{ $item }}
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endif
                </div>
            @endif
        </div>

        {{-- Regenerate button --}}
        <div class="flex justify-center">
            <button wire:click="generatePlan"
                    wire:loading.attr="disabled"
                    wire:target="generatePlan"
                    class="inline-flex items-center gap-2 rounded-lg border border-wc-border px-4 py-2 text-sm font-medium text-wc-text-secondary hover:bg-wc-bg-tertiary transition-colors">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182" />
                </svg>
                Regenerar Plan
            </button>
        </div>
        @endif
    </div>
    @endif

    {{-- ═══ Step 4: Save & Assign ═══ --}}
    @if($currentStep === 4)
    <div class="space-y-6">
        @if(!$saved)
        {{-- Template Name --}}
        <div class="rounded-xl border border-wc-border bg-wc-bg-secondary p-6">
            <h2 class="mb-4 text-lg font-semibold text-wc-text">Guardar Plan</h2>

            <div class="space-y-4">
                <div>
                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Nombre de la Plantilla</label>
                    <input wire:model="templateName" type="text" maxlength="160"
                           placeholder="Nombre descriptivo para esta plantilla..."
                           class="w-full rounded-lg border border-wc-border bg-wc-bg px-3 py-2.5 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-red-500 focus:outline-none focus:ring-1 focus:ring-red-500">
                    @error('templateName')
                        <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center gap-3">
                    <button wire:click="$toggle('isPublic')"
                            class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200
                                {{ $isPublic ? 'bg-red-600' : 'bg-wc-bg-tertiary' }}"
                            role="switch" aria-checked="{{ $isPublic ? 'true' : 'false' }}">
                        <span class="pointer-events-none inline-block h-5 w-5 rounded-full bg-white shadow transition-transform duration-200
                            {{ $isPublic ? 'translate-x-5' : 'translate-x-0' }}"></span>
                    </button>
                    <span class="text-sm text-wc-text">Plantilla publica (visible para otros coaches)</span>
                </div>
            </div>
        </div>

        {{-- Save Mode --}}
        <div class="rounded-xl border border-wc-border bg-wc-bg-secondary p-6">
            <h2 class="mb-4 text-lg font-semibold text-wc-text">Modo de Guardado</h2>
            <div class="grid gap-4 sm:grid-cols-2">
                <button wire:click="$set('saveMode', 'template_only')"
                        class="flex flex-col items-center gap-3 rounded-xl border-2 p-6 text-center transition-all
                            {{ $saveMode === 'template_only'
                                ? 'border-red-500 bg-red-500/10'
                                : 'border-wc-border bg-wc-bg hover:border-wc-text-tertiary' }}">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl {{ $saveMode === 'template_only' ? 'bg-red-500/20 text-red-400' : 'bg-wc-bg-tertiary text-wc-text-tertiary' }}">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                        </svg>
                    </div>
                    <div>
                        <p class="font-semibold text-wc-text">Solo Plantilla</p>
                        <p class="mt-1 text-xs text-wc-text-tertiary">Guardar como plantilla reutilizable sin asignar al cliente</p>
                    </div>
                </button>

                <button wire:click="$set('saveMode', 'template_and_assign')"
                        class="flex flex-col items-center gap-3 rounded-xl border-2 p-6 text-center transition-all
                            {{ $saveMode === 'template_and_assign'
                                ? 'border-emerald-500 bg-emerald-500/10'
                                : 'border-wc-border bg-wc-bg hover:border-wc-text-tertiary' }}">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl {{ $saveMode === 'template_and_assign' ? 'bg-emerald-500/20 text-emerald-400' : 'bg-wc-bg-tertiary text-wc-text-tertiary' }}">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m3.75 9v6m3-3H9m1.5-12H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                        </svg>
                    </div>
                    <div>
                        <p class="font-semibold text-wc-text">Plantilla + Asignar</p>
                        <p class="mt-1 text-xs text-wc-text-tertiary">Guardar y asignar directamente a {{ $selectedClientData['name'] ?? 'el cliente' }}</p>
                    </div>
                </button>
            </div>
        </div>

        {{-- Save Action --}}
        <div class="flex justify-center">
            <button wire:click="savePlan"
                    wire:loading.attr="disabled"
                    wire:target="savePlan"
                    class="inline-flex items-center gap-3 rounded-xl bg-gradient-to-r from-emerald-600 to-emerald-700 px-8 py-4 text-base font-bold text-white shadow-lg shadow-emerald-600/25 transition-all hover:from-emerald-500 hover:to-emerald-600 hover:shadow-xl">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 3.75H6.912a2.25 2.25 0 0 0-2.15 1.588L2.35 13.177a2.25 2.25 0 0 0-.1.661V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18v-4.162c0-.224-.034-.447-.1-.661L19.24 5.338a2.25 2.25 0 0 0-2.15-1.588H15M2.25 13.5h3.86a2.25 2.25 0 0 1 2.012 1.244l.256.512a2.25 2.25 0 0 0 2.013 1.244h3.218a2.25 2.25 0 0 0 2.013-1.244l.256-.512a2.25 2.25 0 0 1 2.013-1.244h3.859M12 3v8.25m0 0-3-3m3 3 3-3" />
                </svg>
                <span wire:loading.remove wire:target="savePlan">
                    {{ $saveMode === 'template_and_assign' ? 'Guardar y Asignar Plan' : 'Guardar Plantilla' }}
                </span>
                <span wire:loading wire:target="savePlan">Guardando...</span>
            </button>
        </div>
        @else
        {{-- Success State --}}
        <div class="rounded-xl border border-emerald-500/30 bg-wc-bg-secondary p-8 text-center">
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-emerald-500/20">
                <svg class="h-8 w-8 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                </svg>
            </div>
            <h2 class="mt-4 text-xl font-bold text-wc-text">Plan Guardado Exitosamente</h2>
            <p class="mt-2 text-sm text-wc-text-secondary">
                @if($saveMode === 'template_and_assign')
                    La plantilla ha sido creada y el plan fue asignado a {{ $selectedClientData['name'] ?? 'el cliente' }}.
                @else
                    La plantilla ha sido creada y esta disponible para asignar.
                @endif
            </p>

            <div class="mt-6 flex flex-wrap justify-center gap-3">
                @if($savedTemplateId)
                    <a href="{{ route('admin.plans') }}"
                       class="inline-flex items-center gap-2 rounded-lg bg-wc-bg-tertiary px-4 py-2 text-sm font-medium text-wc-text hover:bg-wc-border transition-colors">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15a2.25 2.25 0 0 1 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z" />
                        </svg>
                        Ver Plantillas
                    </a>
                @endif
                @if($savedAssignedId && $selectedClientId)
                    <a href="{{ route('admin.client-detail', $selectedClientId) }}"
                       class="inline-flex items-center gap-2 rounded-lg bg-wc-bg-tertiary px-4 py-2 text-sm font-medium text-wc-text hover:bg-wc-border transition-colors">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                        </svg>
                        Ver Cliente
                    </a>
                @endif
                <button wire:click="startNew"
                        class="inline-flex items-center gap-2 rounded-lg bg-red-600 px-4 py-2 text-sm font-bold text-white hover:bg-red-500 transition-colors">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Crear Otro Plan
                </button>
            </div>
        </div>
        @endif
    </div>
    @endif

    {{-- ═══ Navigation Footer ═══ --}}
    @if(!$saved)
    <div class="flex items-center justify-between rounded-xl border border-wc-border bg-wc-bg-secondary p-4">
        <button wire:click="prevStep"
                class="inline-flex items-center gap-2 rounded-lg px-4 py-2.5 text-sm font-medium transition-colors
                    {{ $currentStep > 1
                        ? 'bg-wc-bg-tertiary text-wc-text hover:bg-wc-border'
                        : 'cursor-not-allowed text-wc-text-tertiary' }}"
                {{ $currentStep <= 1 ? 'disabled' : '' }}>
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
            </svg>
            Atras
        </button>

        <span class="text-xs font-medium text-wc-text-tertiary">Paso {{ $currentStep }} de 4</span>

        @if($currentStep < 4)
            <button wire:click="nextStep"
                    class="inline-flex items-center gap-2 rounded-lg bg-red-600 px-4 py-2.5 text-sm font-bold text-white transition-colors hover:bg-red-500
                        {{ ($currentStep === 1 && !$selectedClientId) || ($currentStep === 2 && empty($planType)) || ($currentStep === 3 && !$planGenerated) ? 'opacity-50 cursor-not-allowed' : '' }}">
                Siguiente
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                </svg>
            </button>
        @else
            <div></div>
        @endif
    </div>
    @endif

</div>
