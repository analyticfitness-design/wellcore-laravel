<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-3">
            <h1 class="font-display text-3xl tracking-wide text-wc-text">PROGRAMA RISE</h1>
            <span class="inline-flex items-center gap-1 rounded-full bg-emerald-500/10 px-2.5 py-0.5 text-[10px] font-semibold uppercase tracking-wider text-emerald-500">
                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.59 14.37a6 6 0 0 1-5.84 7.38v-4.8m5.84-2.58a14.98 14.98 0 0 0 6.16-12.12A14.98 14.98 0 0 0 9.631 8.41m5.96 5.96a14.926 14.926 0 0 1-5.841 2.58m-.119-8.54a6 6 0 0 0-7.381 5.84h4.8m2.581-5.84a14.927 14.927 0 0 0-2.58 5.84m2.699 2.7c-.103.021-.207.041-.311.06a15.09 15.09 0 0 1-2.448-2.448 14.9 14.9 0 0 1 .06-.312m-2.24 2.39a4.493 4.493 0 0 0-1.757 4.306 4.493 4.493 0 0 0 4.306-1.758M16.5 9a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Z" />
                </svg>
                Reto 12 Semanas
            </span>
        </div>
    </div>

    {{-- Tab Bar --}}
    <div class="flex gap-1 rounded-xl border border-wc-border bg-wc-bg-secondary p-1">
        @foreach ([
            'overview' => 'Overview',
            'participants' => 'Participantes',
            'progress' => 'Progreso',
            'payments' => 'Pagos',
        ] as $key => $label)
            <button
                wire:click="switchTab('{{ $key }}')"
                class="flex-1 rounded-lg px-4 py-2 text-sm font-medium transition-colors
                    {{ $activeTab === $key
                        ? 'bg-emerald-500/10 text-emerald-500 shadow-sm'
                        : 'text-wc-text-secondary hover:text-wc-text hover:bg-wc-bg-tertiary' }}"
            >
                {{ $label }}
            </button>
        @endforeach
    </div>

    {{-- ===== OVERVIEW TAB ===== --}}
    @if ($activeTab === 'overview')
        <div class="space-y-6">

            {{-- Stats Cards --}}
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                {{-- Total Participants --}}
                <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-5">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Participantes</p>
                            <p class="mt-1 font-display text-3xl tracking-wide text-wc-text">{{ $stats['totalParticipants'] }}</p>
                        </div>
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-500/10">
                            <svg class="h-5 w-5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                            </svg>
                        </div>
                    </div>
                </div>

                {{-- Active Programs --}}
                <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-5">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Activos</p>
                            <p class="mt-1 font-display text-3xl tracking-wide text-emerald-500">{{ $stats['activePrograms'] }}</p>
                        </div>
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-500/10">
                            <svg class="h-5 w-5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.59 14.37a6 6 0 0 1-5.84 7.38v-4.8m5.84-2.58a14.98 14.98 0 0 0 6.16-12.12A14.98 14.98 0 0 0 9.631 8.41m5.96 5.96a14.926 14.926 0 0 1-5.841 2.58m-.119-8.54a6 6 0 0 0-7.381 5.84h4.8m2.581-5.84a14.927 14.927 0 0 0-2.58 5.84m2.699 2.7c-.103.021-.207.041-.311.06a15.09 15.09 0 0 1-2.448-2.448 14.9 14.9 0 0 1 .06-.312m-2.24 2.39a4.493 4.493 0 0 0-1.757 4.306 4.493 4.493 0 0 0 4.306-1.758M16.5 9a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Z" />
                            </svg>
                        </div>
                    </div>
                </div>

                {{-- Completion Rate --}}
                <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-5">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Tasa Completado</p>
                            <p class="mt-1 font-display text-3xl tracking-wide text-wc-text">{{ $stats['completionRate'] }}%</p>
                        </div>
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-500/10">
                            <svg class="h-5 w-5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                        </div>
                    </div>
                </div>

                {{-- Revenue --}}
                <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-5">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Revenue RISE</p>
                            <p class="mt-1 font-display text-3xl tracking-wide text-wc-text">${{ number_format($stats['riseRevenue'], 0, ',', '.') }}</p>
                            <p class="mt-0.5 text-[11px] text-wc-text-tertiary">{{ $stats['totalRisePayments'] }} pagos</p>
                        </div>
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-amber-500/10">
                            <svg class="h-5 w-5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Status Breakdown + Activity Summary --}}
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">

                {{-- Status Breakdown --}}
                <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-5">
                    <h3 class="mb-4 text-sm font-semibold text-wc-text">Estado de Programas</h3>
                    @php
                        $statusColors = [
                            'active' => ['bg' => 'bg-emerald-500', 'text' => 'text-emerald-500', 'label' => 'Activo'],
                            'completed' => ['bg' => 'bg-blue-500', 'text' => 'text-blue-500', 'label' => 'Completado'],
                            'paused' => ['bg' => 'bg-amber-500', 'text' => 'text-amber-500', 'label' => 'Pausado'],
                            'cancelled' => ['bg' => 'bg-red-500', 'text' => 'text-red-500', 'label' => 'Cancelado'],
                        ];
                        $totalForBar = max(array_sum($stats['statusBreakdown']), 1);
                    @endphp
                    <div class="space-y-3">
                        @forelse ($statusColors as $statusKey => $colors)
                            @php $count = $stats['statusBreakdown'][$statusKey] ?? 0; @endphp
                            <div>
                                <div class="mb-1 flex items-center justify-between text-xs">
                                    <span class="{{ $colors['text'] }} font-medium">{{ $colors['label'] }}</span>
                                    <span class="text-wc-text-tertiary">{{ $count }}</span>
                                </div>
                                <div class="h-2 w-full overflow-hidden rounded-full bg-wc-bg">
                                    <div class="{{ $colors['bg'] }} h-full rounded-full transition-all duration-500"
                                         style="width: {{ ($count / $totalForBar) * 100 }}%"></div>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-wc-text-tertiary">Sin datos</p>
                        @endforelse
                    </div>
                </div>

                {{-- Activity Summary --}}
                <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-5">
                    <h3 class="mb-4 text-sm font-semibold text-wc-text">Actividad del Programa</h3>
                    <div class="grid grid-cols-3 gap-4">
                        <div class="rounded-lg bg-wc-bg p-3 text-center">
                            <p class="font-display text-2xl text-wc-text">{{ $stats['dailyLogCount'] }}</p>
                            <p class="mt-0.5 text-[10px] font-medium uppercase tracking-wider text-wc-text-tertiary">Daily Logs</p>
                        </div>
                        <div class="rounded-lg bg-wc-bg p-3 text-center">
                            <p class="font-display text-2xl text-wc-text">{{ $stats['measurementCount'] }}</p>
                            <p class="mt-0.5 text-[10px] font-medium uppercase tracking-wider text-wc-text-tertiary">Mediciones</p>
                        </div>
                        <div class="rounded-lg bg-wc-bg p-3 text-center">
                            <p class="font-display text-2xl text-wc-text">{{ $stats['trackingCount'] }}</p>
                            <p class="mt-0.5 text-[10px] font-medium uppercase tracking-wider text-wc-text-tertiary">Tracking</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Recent Enrollments --}}
            <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-5">
                <h3 class="mb-4 text-sm font-semibold text-wc-text">Inscripciones Recientes</h3>
                @if ($stats['recentEnrollments']->isEmpty())
                    <div class="flex flex-col items-center justify-center py-8 text-center">
                        <svg class="mb-3 h-10 w-10 text-wc-text-tertiary/50" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                        </svg>
                        <p class="text-sm text-wc-text-tertiary">No hay inscripciones recientes</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead>
                                <tr class="border-b border-wc-border">
                                    <th class="pb-2 text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Cliente</th>
                                    <th class="pb-2 text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Fecha</th>
                                    <th class="pb-2 text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Nivel</th>
                                    <th class="pb-2 text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Estado</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-wc-border/50">
                                @foreach ($stats['recentEnrollments'] as $enrollment)
                                    <tr class="group">
                                        <td class="py-2.5">
                                            <div class="flex items-center gap-2">
                                                <div class="flex h-7 w-7 items-center justify-center rounded-full bg-emerald-500/20">
                                                    <span class="text-xs font-semibold text-emerald-500">{{ substr($enrollment->client?->name ?? '?', 0, 1) }}</span>
                                                </div>
                                                <span class="font-medium text-wc-text">{{ $enrollment->client?->name ?? 'N/A' }}</span>
                                            </div>
                                        </td>
                                        <td class="py-2.5 text-wc-text-secondary">{{ $enrollment->enrollment_date?->format('d M Y') ?? '-' }}</td>
                                        <td class="py-2.5">
                                            <span class="inline-flex rounded-full bg-wc-bg px-2 py-0.5 text-[10px] font-semibold capitalize text-wc-text-secondary">
                                                {{ $enrollment->experience_level }}
                                            </span>
                                        </td>
                                        <td class="py-2.5">
                                            @php
                                                $sc = match($enrollment->status) {
                                                    'active' => 'bg-emerald-500/10 text-emerald-500',
                                                    'completed' => 'bg-blue-500/10 text-blue-500',
                                                    'paused' => 'bg-amber-500/10 text-amber-500',
                                                    'cancelled' => 'bg-red-500/10 text-red-500',
                                                    default => 'bg-wc-bg text-wc-text-tertiary',
                                                };
                                            @endphp
                                            <span class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-semibold capitalize {{ $sc }}">
                                                {{ $enrollment->status }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    @endif

    {{-- ===== PARTICIPANTS TAB ===== --}}
    @if ($activeTab === 'participants')
        <div class="space-y-4">

            {{-- Search + Filters --}}
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                <div class="relative flex-1">
                    <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                    </svg>
                    <input wire:model.live.debounce.300ms="search" type="text" placeholder="Buscar participante..."
                           class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary py-2 pl-9 pr-4 text-sm text-wc-text placeholder-wc-text-tertiary outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500">
                </div>
                <select wire:model.live="statusFilter"
                        class="rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text outline-none focus:border-emerald-500">
                    <option value="all">Todos los estados</option>
                    <option value="active">Activo</option>
                    <option value="completed">Completado</option>
                    <option value="paused">Pausado</option>
                    <option value="cancelled">Cancelado</option>
                </select>
            </div>

            {{-- Participants Table --}}
            <div class="rounded-card border border-wc-border bg-wc-bg-tertiary overflow-hidden">
                @if ($participants->isEmpty())
                    <div class="flex flex-col items-center justify-center py-12 text-center">
                        <svg class="mb-3 h-12 w-12 text-wc-text-tertiary/50" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.59 14.37a6 6 0 0 1-5.84 7.38v-4.8m5.84-2.58a14.98 14.98 0 0 0 6.16-12.12A14.98 14.98 0 0 0 9.631 8.41m5.96 5.96a14.926 14.926 0 0 1-5.841 2.58m-.119-8.54a6 6 0 0 0-7.381 5.84h4.8m2.581-5.84a14.927 14.927 0 0 0-2.58 5.84m2.699 2.7c-.103.021-.207.041-.311.06a15.09 15.09 0 0 1-2.448-2.448 14.9 14.9 0 0 1 .06-.312m-2.24 2.39a4.493 4.493 0 0 0-1.757 4.306 4.493 4.493 0 0 0 4.306-1.758M16.5 9a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Z" />
                        </svg>
                        <p class="text-sm font-medium text-wc-text-secondary">No hay participantes RISE</p>
                        <p class="mt-1 text-xs text-wc-text-tertiary">Los participantes apareceran cuando se inscriban al programa</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead>
                                <tr class="border-b border-wc-border bg-wc-bg-secondary/50">
                                    <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">
                                        <button wire:click="sortByColumn('client_id')" class="flex items-center gap-1 hover:text-wc-text">
                                            Cliente
                                            @if ($sortBy === 'client_id')
                                                <svg class="h-3 w-3 {{ $sortDir === 'asc' ? 'rotate-180' : '' }}" fill="currentColor" viewBox="0 0 20 20"><path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/></svg>
                                            @endif
                                        </button>
                                    </th>
                                    <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">
                                        <button wire:click="sortByColumn('enrollment_date')" class="flex items-center gap-1 hover:text-wc-text">
                                            Inscripcion
                                            @if ($sortBy === 'enrollment_date')
                                                <svg class="h-3 w-3 {{ $sortDir === 'asc' ? 'rotate-180' : '' }}" fill="currentColor" viewBox="0 0 20 20"><path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/></svg>
                                            @endif
                                        </button>
                                    </th>
                                    <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Periodo</th>
                                    <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Nivel</th>
                                    <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Ubicacion</th>
                                    <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">
                                        <button wire:click="sortByColumn('status')" class="flex items-center gap-1 hover:text-wc-text">
                                            Estado
                                            @if ($sortBy === 'status')
                                                <svg class="h-3 w-3 {{ $sortDir === 'asc' ? 'rotate-180' : '' }}" fill="currentColor" viewBox="0 0 20 20"><path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/></svg>
                                            @endif
                                        </button>
                                    </th>
                                    <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Daily Logs</th>
                                    <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-wc-border/50">
                                @foreach ($participants as $program)
                                    <tr class="group hover:bg-wc-bg-secondary/30 transition-colors">
                                        <td class="px-4 py-3">
                                            <div class="flex items-center gap-2.5">
                                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-emerald-500/20">
                                                    <span class="text-xs font-semibold text-emerald-500">{{ substr($program->client?->name ?? '?', 0, 1) }}</span>
                                                </div>
                                                <div>
                                                    <p class="font-medium text-wc-text">{{ $program->client?->name ?? 'N/A' }}</p>
                                                    <p class="text-[11px] text-wc-text-tertiary">{{ $program->client?->email ?? '' }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-wc-text-secondary">{{ $program->enrollment_date?->format('d M Y') ?? '-' }}</td>
                                        <td class="px-4 py-3 text-xs text-wc-text-secondary">
                                            {{ $program->start_date?->format('d/m') }} - {{ $program->end_date?->format('d/m/Y') }}
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="inline-flex rounded-full bg-wc-bg px-2 py-0.5 text-[10px] font-semibold capitalize text-wc-text-secondary">
                                                {{ $program->experience_level }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3">
                                            @php
                                                $locIcon = match($program->training_location) {
                                                    'gym' => 'Gym',
                                                    'home' => 'Casa',
                                                    'hybrid' => 'Hibrido',
                                                    default => $program->training_location,
                                                };
                                            @endphp
                                            <span class="text-xs text-wc-text-secondary">{{ $locIcon }}</span>
                                        </td>
                                        <td class="px-4 py-3">
                                            @php
                                                $sc = match($program->status) {
                                                    'active' => 'bg-emerald-500/10 text-emerald-500',
                                                    'completed' => 'bg-blue-500/10 text-blue-500',
                                                    'paused' => 'bg-amber-500/10 text-amber-500',
                                                    'cancelled' => 'bg-red-500/10 text-red-500',
                                                    default => 'bg-wc-bg text-wc-text-tertiary',
                                                };
                                            @endphp
                                            <span class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-semibold capitalize {{ $sc }}">
                                                {{ $program->status }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="font-data text-sm text-wc-text-secondary">{{ $program->dailyLogs?->count() ?? 0 }}</span>
                                        </td>
                                        <td class="px-4 py-3">
                                            <button wire:click="viewParticipant({{ $program->id }})"
                                                    class="rounded-lg bg-emerald-500/10 px-3 py-1.5 text-xs font-medium text-emerald-500 opacity-0 transition-all group-hover:opacity-100 hover:bg-emerald-500/20">
                                                Ver detalle
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination --}}
                    <div class="border-t border-wc-border px-4 py-3">
                        {{ $participants->links() }}
                    </div>
                @endif
            </div>
        </div>
    @endif

    {{-- ===== PROGRESS TAB ===== --}}
    @if ($activeTab === 'progress')
        <div class="space-y-6">

            @if ($progress['totalLogs'] === 0 && $progress['avgMeasurements']->total_entries == 0 && $progress['trackingAvg']->total_tracking == 0)
                {{-- Empty State --}}
                <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-12">
                    <div class="flex flex-col items-center justify-center text-center">
                        <svg class="mb-4 h-16 w-16 text-wc-text-tertiary/30" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                        </svg>
                        <h3 class="text-lg font-semibold text-wc-text">Sin datos de progreso</h3>
                        <p class="mt-2 max-w-sm text-sm text-wc-text-tertiary">Los datos de progreso se mostraran cuando los participantes comiencen a registrar sus actividades diarias, mediciones y seguimiento.</p>
                    </div>
                </div>
            @else

                {{-- Workout & Nutrition Stats --}}
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-5">
                        <p class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Workout Rate</p>
                        <p class="mt-1 font-display text-3xl tracking-wide text-emerald-500">{{ $progress['workoutRate'] }}%</p>
                        <p class="mt-0.5 text-[11px] text-wc-text-tertiary">{{ $progress['workoutCompleted'] }}/{{ $progress['totalLogs'] }} completados</p>
                    </div>
                    <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-5">
                        <p class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Avg Mood</p>
                        <p class="mt-1 font-display text-3xl tracking-wide text-wc-text">{{ number_format($progress['moodEnergyAvg']->avg_mood ?? 0, 1) }}/10</p>
                        <p class="mt-0.5 text-[11px] text-wc-text-tertiary">Nivel de animo promedio</p>
                    </div>
                    <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-5">
                        <p class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Avg Energy</p>
                        <p class="mt-1 font-display text-3xl tracking-wide text-wc-text">{{ number_format($progress['moodEnergyAvg']->avg_energy ?? 0, 1) }}/10</p>
                        <p class="mt-0.5 text-[11px] text-wc-text-tertiary">Nivel de energia promedio</p>
                    </div>
                    <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-5">
                        <p class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Clientes Medidos</p>
                        <p class="mt-1 font-display text-3xl tracking-wide text-wc-text">{{ $progress['avgMeasurements']->clients_measured ?? 0 }}</p>
                        <p class="mt-0.5 text-[11px] text-wc-text-tertiary">{{ $progress['avgMeasurements']->total_entries ?? 0 }} registros totales</p>
                    </div>
                </div>

                {{-- Nutrition Adherence + Tracking Averages --}}
                <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">

                    {{-- Nutrition Adherence Breakdown --}}
                    <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-5">
                        <h3 class="mb-4 text-sm font-semibold text-wc-text">Adherencia Nutricional</h3>
                        @if (empty($progress['nutritionBreakdown']))
                            <p class="py-4 text-center text-sm text-wc-text-tertiary">Sin datos de nutricion</p>
                        @else
                            @php
                                $nutritionColors = [
                                    'excellent' => ['bg' => 'bg-emerald-500', 'text' => 'text-emerald-500', 'label' => 'Excelente'],
                                    'good' => ['bg' => 'bg-blue-500', 'text' => 'text-blue-500', 'label' => 'Buena'],
                                    'fair' => ['bg' => 'bg-amber-500', 'text' => 'text-amber-500', 'label' => 'Regular'],
                                    'poor' => ['bg' => 'bg-red-500', 'text' => 'text-red-500', 'label' => 'Pobre'],
                                ];
                                $totalNut = max(array_sum($progress['nutritionBreakdown']), 1);
                            @endphp
                            <div class="space-y-3">
                                @foreach ($nutritionColors as $key => $colors)
                                    @php $cnt = $progress['nutritionBreakdown'][$key] ?? 0; @endphp
                                    <div>
                                        <div class="mb-1 flex items-center justify-between text-xs">
                                            <span class="{{ $colors['text'] }} font-medium">{{ $colors['label'] }}</span>
                                            <span class="text-wc-text-tertiary">{{ $cnt }} ({{ round(($cnt / $totalNut) * 100) }}%)</span>
                                        </div>
                                        <div class="h-2 w-full overflow-hidden rounded-full bg-wc-bg">
                                            <div class="{{ $colors['bg'] }} h-full rounded-full transition-all duration-500"
                                                 style="width: {{ ($cnt / $totalNut) * 100 }}%"></div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    {{-- Tracking Averages --}}
                    <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-5">
                        <h3 class="mb-4 text-sm font-semibold text-wc-text">Promedios de Tracking</h3>
                        @if (($progress['trackingAvg']->total_tracking ?? 0) == 0)
                            <p class="py-4 text-center text-sm text-wc-text-tertiary">Sin datos de tracking</p>
                        @else
                            <div class="grid grid-cols-2 gap-4">
                                <div class="rounded-lg bg-wc-bg p-3 text-center">
                                    <p class="font-display text-2xl text-blue-400">{{ number_format($progress['trackingAvg']->avg_water ?? 0, 1) }}L</p>
                                    <p class="mt-0.5 text-[10px] font-medium uppercase tracking-wider text-wc-text-tertiary">Agua/Dia</p>
                                </div>
                                <div class="rounded-lg bg-wc-bg p-3 text-center">
                                    <p class="font-display text-2xl text-indigo-400">{{ number_format($progress['trackingAvg']->avg_sleep ?? 0, 1) }}h</p>
                                    <p class="mt-0.5 text-[10px] font-medium uppercase tracking-wider text-wc-text-tertiary">Sueno/Dia</p>
                                </div>
                                <div class="rounded-lg bg-wc-bg p-3 text-center">
                                    <p class="font-display text-2xl text-emerald-400">{{ $progress['trackingAvg']->training_done_count ?? 0 }}</p>
                                    <p class="mt-0.5 text-[10px] font-medium uppercase tracking-wider text-wc-text-tertiary">Entrenamientos</p>
                                </div>
                                <div class="rounded-lg bg-wc-bg p-3 text-center">
                                    <p class="font-display text-2xl text-amber-400">{{ $progress['trackingAvg']->nutrition_done_count ?? 0 }}</p>
                                    <p class="mt-0.5 text-[10px] font-medium uppercase tracking-wider text-wc-text-tertiary">Nutricion OK</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Average Measurements --}}
                @if (($progress['avgMeasurements']->total_entries ?? 0) > 0)
                    <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-5">
                        <h3 class="mb-4 text-sm font-semibold text-wc-text">Promedios de Mediciones</h3>
                        <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                            <div class="rounded-lg bg-wc-bg p-3 text-center">
                                <p class="font-display text-2xl text-wc-text">{{ number_format($progress['avgMeasurements']->avg_weight ?? 0, 1) }}</p>
                                <p class="mt-0.5 text-[10px] font-medium uppercase tracking-wider text-wc-text-tertiary">Peso (kg)</p>
                            </div>
                            <div class="rounded-lg bg-wc-bg p-3 text-center">
                                <p class="font-display text-2xl text-wc-text">{{ number_format($progress['avgMeasurements']->avg_waist ?? 0, 1) }}</p>
                                <p class="mt-0.5 text-[10px] font-medium uppercase tracking-wider text-wc-text-tertiary">Cintura (cm)</p>
                            </div>
                            <div class="rounded-lg bg-wc-bg p-3 text-center">
                                <p class="font-display text-2xl text-emerald-400">{{ number_format($progress['avgMeasurements']->avg_muscle ?? 0, 1) }}%</p>
                                <p class="mt-0.5 text-[10px] font-medium uppercase tracking-wider text-wc-text-tertiary">Musculo</p>
                            </div>
                            <div class="rounded-lg bg-wc-bg p-3 text-center">
                                <p class="font-display text-2xl text-amber-400">{{ number_format($progress['avgMeasurements']->avg_fat ?? 0, 1) }}%</p>
                                <p class="mt-0.5 text-[10px] font-medium uppercase tracking-wider text-wc-text-tertiary">Grasa</p>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Measurement Deltas --}}
                @if (!empty($progress['measurementDeltas']))
                    <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-5">
                        <h3 class="mb-4 text-sm font-semibold text-wc-text">Cambios por Participante (primera vs ultima medicion)</h3>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm">
                                <thead>
                                    <tr class="border-b border-wc-border">
                                        <th class="pb-2 text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Cliente</th>
                                        <th class="pb-2 text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Peso (kg)</th>
                                        <th class="pb-2 text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Cintura (cm)</th>
                                        <th class="pb-2 text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Grasa (%)</th>
                                        <th class="pb-2 text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Musculo (%)</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-wc-border/50">
                                    @foreach ($progress['measurementDeltas'] as $delta)
                                        <tr>
                                            <td class="py-2.5 font-medium text-wc-text">{{ $delta['client_name'] }}</td>
                                            <td class="py-2.5">
                                                <span class="font-data {{ $delta['weight_delta'] < 0 ? 'text-emerald-500' : ($delta['weight_delta'] > 0 ? 'text-red-500' : 'text-wc-text-secondary') }}">
                                                    {{ $delta['weight_delta'] > 0 ? '+' : '' }}{{ number_format($delta['weight_delta'], 1) }}
                                                </span>
                                            </td>
                                            <td class="py-2.5">
                                                <span class="font-data {{ $delta['waist_delta'] < 0 ? 'text-emerald-500' : ($delta['waist_delta'] > 0 ? 'text-red-500' : 'text-wc-text-secondary') }}">
                                                    {{ $delta['waist_delta'] > 0 ? '+' : '' }}{{ number_format($delta['waist_delta'], 1) }}
                                                </span>
                                            </td>
                                            <td class="py-2.5">
                                                <span class="font-data {{ $delta['fat_delta'] < 0 ? 'text-emerald-500' : ($delta['fat_delta'] > 0 ? 'text-red-500' : 'text-wc-text-secondary') }}">
                                                    {{ $delta['fat_delta'] > 0 ? '+' : '' }}{{ number_format($delta['fat_delta'], 1) }}
                                                </span>
                                            </td>
                                            <td class="py-2.5">
                                                <span class="font-data {{ $delta['muscle_delta'] > 0 ? 'text-emerald-500' : ($delta['muscle_delta'] < 0 ? 'text-red-500' : 'text-wc-text-secondary') }}">
                                                    {{ $delta['muscle_delta'] > 0 ? '+' : '' }}{{ number_format($delta['muscle_delta'], 1) }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

            @endif
        </div>
    @endif

    {{-- ===== PAYMENTS TAB ===== --}}
    @if ($activeTab === 'payments')
        <div class="space-y-4">

            {{-- Payment Stats --}}
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-5">
                    <p class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Revenue Total</p>
                    <p class="mt-1 font-display text-3xl tracking-wide text-emerald-500">${{ number_format($paymentStats['totalRevenue'], 0, ',', '.') }}</p>
                    <p class="mt-0.5 text-[11px] text-wc-text-tertiary">{{ $paymentStats['approved'] }} pagos aprobados</p>
                </div>
                <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-5">
                    <p class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Total Pagos</p>
                    <p class="mt-1 font-display text-3xl tracking-wide text-wc-text">{{ $paymentStats['total'] }}</p>
                </div>
                <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-5">
                    <p class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Pendientes</p>
                    <p class="mt-1 font-display text-3xl tracking-wide text-amber-500">{{ $paymentStats['pending'] }}</p>
                </div>
            </div>

            {{-- Search + Filters --}}
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                <div class="relative flex-1">
                    <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                    </svg>
                    <input wire:model.live.debounce.300ms="paymentSearch" type="text" placeholder="Buscar por nombre, email o referencia..."
                           class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary py-2 pl-9 pr-4 text-sm text-wc-text placeholder-wc-text-tertiary outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500">
                </div>
                <select wire:model.live="paymentStatusFilter"
                        class="rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text outline-none focus:border-emerald-500">
                    <option value="all">Todos los estados</option>
                    <option value="approved">Aprobado</option>
                    <option value="pending">Pendiente</option>
                    <option value="declined">Rechazado</option>
                    <option value="voided">Anulado</option>
                    <option value="error">Error</option>
                </select>
            </div>

            {{-- Payments Table --}}
            <div class="rounded-card border border-wc-border bg-wc-bg-tertiary overflow-hidden">
                @if ($payments->isEmpty())
                    <div class="flex flex-col items-center justify-center py-12 text-center">
                        <svg class="mb-3 h-12 w-12 text-wc-text-tertiary/50" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" />
                        </svg>
                        <p class="text-sm font-medium text-wc-text-secondary">No hay pagos RISE</p>
                        <p class="mt-1 text-xs text-wc-text-tertiary">Los pagos apareceran cuando se procesen transacciones del plan RISE</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead>
                                <tr class="border-b border-wc-border bg-wc-bg-secondary/50">
                                    <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Cliente</th>
                                    <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Monto</th>
                                    <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Estado</th>
                                    <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Metodo</th>
                                    <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Referencia</th>
                                    <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Fecha</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-wc-border/50">
                                @foreach ($payments as $payment)
                                    <tr class="hover:bg-wc-bg-secondary/30 transition-colors">
                                        <td class="px-4 py-3">
                                            <div>
                                                <p class="font-medium text-wc-text">{{ $payment->buyer_name ?? $payment->client?->name ?? 'N/A' }}</p>
                                                <p class="text-[11px] text-wc-text-tertiary">{{ $payment->email }}</p>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="font-data font-semibold text-wc-text">${{ number_format($payment->amount, 0, ',', '.') }}</span>
                                            <span class="text-[10px] text-wc-text-tertiary">{{ $payment->currency }}</span>
                                        </td>
                                        <td class="px-4 py-3">
                                            @php
                                                $psc = match($payment->status?->value ?? $payment->status) {
                                                    'approved' => 'bg-emerald-500/10 text-emerald-500',
                                                    'pending' => 'bg-amber-500/10 text-amber-500',
                                                    'declined' => 'bg-red-500/10 text-red-500',
                                                    'voided' => 'bg-gray-500/10 text-gray-500',
                                                    'error' => 'bg-red-500/10 text-red-500',
                                                    default => 'bg-wc-bg text-wc-text-tertiary',
                                                };
                                            @endphp
                                            <span class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-semibold {{ $psc }}">
                                                {{ $payment->status instanceof \App\Enums\PaymentStatus ? $payment->status->label() : ucfirst($payment->status) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-xs text-wc-text-secondary">{{ $payment->payment_method ?? '-' }}</td>
                                        <td class="px-4 py-3">
                                            <span class="font-mono text-[11px] text-wc-text-tertiary">{{ $payment->wompi_reference ?? $payment->payu_reference ?? '-' }}</span>
                                        </td>
                                        <td class="px-4 py-3 text-xs text-wc-text-secondary">{{ $payment->created_at?->format('d M Y H:i') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination --}}
                    <div class="border-t border-wc-border px-4 py-3">
                        {{ $payments->links() }}
                    </div>
                @endif
            </div>
        </div>
    @endif

    {{-- ===== PARTICIPANT DETAIL MODAL ===== --}}
    @if ($showDetailModal && isset($detailProgram))
        <div class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto bg-black/60 p-4 pt-12"
             x-data x-on:keydown.escape.window="$wire.closeDetail()">
            <div class="w-full max-w-3xl rounded-2xl border border-wc-border bg-wc-bg-secondary shadow-2xl"
                 x-on:click.outside="$wire.closeDetail()">

                {{-- Modal Header --}}
                <div class="flex items-center justify-between border-b border-wc-border px-6 py-4">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-500/20">
                            <span class="text-sm font-semibold text-emerald-500">{{ substr($detailProgram->client?->name ?? '?', 0, 1) }}</span>
                        </div>
                        <div>
                            <h3 class="font-display text-xl tracking-wide text-wc-text">{{ $detailProgram->client?->name ?? 'N/A' }}</h3>
                            <p class="text-xs text-wc-text-tertiary">{{ $detailProgram->client?->email ?? '' }}</p>
                        </div>
                    </div>
                    <button wire:click="closeDetail" class="rounded-lg p-2 text-wc-text-tertiary hover:bg-wc-bg-tertiary hover:text-wc-text">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                {{-- Modal Body --}}
                <div class="space-y-5 p-6">

                    {{-- Program Info --}}
                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                        <div class="rounded-lg bg-wc-bg-tertiary p-3">
                            <p class="text-[10px] font-medium uppercase tracking-wider text-wc-text-tertiary">Estado</p>
                            @php
                                $sc = match($detailProgram->status) {
                                    'active' => 'text-emerald-500',
                                    'completed' => 'text-blue-500',
                                    'paused' => 'text-amber-500',
                                    'cancelled' => 'text-red-500',
                                    default => 'text-wc-text-secondary',
                                };
                            @endphp
                            <p class="mt-0.5 text-sm font-semibold capitalize {{ $sc }}">{{ $detailProgram->status }}</p>
                        </div>
                        <div class="rounded-lg bg-wc-bg-tertiary p-3">
                            <p class="text-[10px] font-medium uppercase tracking-wider text-wc-text-tertiary">Nivel</p>
                            <p class="mt-0.5 text-sm font-semibold capitalize text-wc-text">{{ $detailProgram->experience_level }}</p>
                        </div>
                        <div class="rounded-lg bg-wc-bg-tertiary p-3">
                            <p class="text-[10px] font-medium uppercase tracking-wider text-wc-text-tertiary">Ubicacion</p>
                            <p class="mt-0.5 text-sm font-semibold capitalize text-wc-text">{{ $detailProgram->training_location }}</p>
                        </div>
                        <div class="rounded-lg bg-wc-bg-tertiary p-3">
                            <p class="text-[10px] font-medium uppercase tracking-wider text-wc-text-tertiary">Adherencia</p>
                            <p class="mt-0.5 text-sm font-semibold text-emerald-500">{{ $detailAdherence }}%</p>
                        </div>
                    </div>

                    {{-- Dates --}}
                    <div class="flex flex-wrap gap-4 text-xs text-wc-text-secondary">
                        <span>Inscripcion: <strong class="text-wc-text">{{ $detailProgram->enrollment_date?->format('d M Y') ?? '-' }}</strong></span>
                        <span>Inicio: <strong class="text-wc-text">{{ $detailProgram->start_date?->format('d M Y') ?? '-' }}</strong></span>
                        <span>Fin: <strong class="text-wc-text">{{ $detailProgram->end_date?->format('d M Y') ?? '-' }}</strong></span>
                    </div>

                    {{-- Measurements --}}
                    @if (isset($detailMeasurements) && $detailMeasurements->isNotEmpty())
                        <div>
                            <h4 class="mb-2 text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Mediciones Recientes</h4>
                            <div class="overflow-x-auto rounded-lg border border-wc-border">
                                <table class="w-full text-left text-xs">
                                    <thead>
                                        <tr class="border-b border-wc-border bg-wc-bg-tertiary/50">
                                            <th class="px-3 py-2 text-wc-text-tertiary">Fecha</th>
                                            <th class="px-3 py-2 text-wc-text-tertiary">Peso</th>
                                            <th class="px-3 py-2 text-wc-text-tertiary">Cintura</th>
                                            <th class="px-3 py-2 text-wc-text-tertiary">Grasa%</th>
                                            <th class="px-3 py-2 text-wc-text-tertiary">Musculo%</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-wc-border/50">
                                        @foreach ($detailMeasurements as $m)
                                            <tr>
                                                <td class="px-3 py-2 text-wc-text-secondary">{{ $m->log_date?->format('d/m/Y') }}</td>
                                                <td class="px-3 py-2 font-data text-wc-text">{{ $m->weight_kg ?? '-' }} kg</td>
                                                <td class="px-3 py-2 font-data text-wc-text">{{ $m->waist_cm ?? '-' }} cm</td>
                                                <td class="px-3 py-2 font-data text-wc-text">{{ $m->fat_pct ?? '-' }}%</td>
                                                <td class="px-3 py-2 font-data text-wc-text">{{ $m->muscle_pct ?? '-' }}%</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif

                    {{-- Recent Tracking --}}
                    @if (isset($detailTracking) && $detailTracking->isNotEmpty())
                        <div>
                            <h4 class="mb-2 text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Tracking Reciente</h4>
                            <div class="overflow-x-auto rounded-lg border border-wc-border">
                                <table class="w-full text-left text-xs">
                                    <thead>
                                        <tr class="border-b border-wc-border bg-wc-bg-tertiary/50">
                                            <th class="px-3 py-2 text-wc-text-tertiary">Fecha</th>
                                            <th class="px-3 py-2 text-wc-text-tertiary">Entrenamiento</th>
                                            <th class="px-3 py-2 text-wc-text-tertiary">Nutricion</th>
                                            <th class="px-3 py-2 text-wc-text-tertiary">Agua</th>
                                            <th class="px-3 py-2 text-wc-text-tertiary">Sueno</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-wc-border/50">
                                        @foreach ($detailTracking as $t)
                                            <tr>
                                                <td class="px-3 py-2 text-wc-text-secondary">{{ $t->log_date?->format('d/m/Y') }}</td>
                                                <td class="px-3 py-2">
                                                    @if ($t->training_done)
                                                        <span class="text-emerald-500">Si</span>
                                                    @else
                                                        <span class="text-red-400">No</span>
                                                    @endif
                                                </td>
                                                <td class="px-3 py-2">
                                                    @if ($t->nutrition_done)
                                                        <span class="text-emerald-500">Si</span>
                                                    @else
                                                        <span class="text-red-400">No</span>
                                                    @endif
                                                </td>
                                                <td class="px-3 py-2 font-data text-wc-text">{{ $t->water_liters }}L</td>
                                                <td class="px-3 py-2 font-data text-wc-text">{{ $t->sleep_hours }}h</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif

                    {{-- Daily Logs --}}
                    @if ($detailProgram->dailyLogs->isNotEmpty())
                        <div>
                            <h4 class="mb-2 text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Daily Logs (ultimos 10)</h4>
                            <div class="overflow-x-auto rounded-lg border border-wc-border">
                                <table class="w-full text-left text-xs">
                                    <thead>
                                        <tr class="border-b border-wc-border bg-wc-bg-tertiary/50">
                                            <th class="px-3 py-2 text-wc-text-tertiary">Fecha</th>
                                            <th class="px-3 py-2 text-wc-text-tertiary">Workout</th>
                                            <th class="px-3 py-2 text-wc-text-tertiary">Nutricion</th>
                                            <th class="px-3 py-2 text-wc-text-tertiary">Mood</th>
                                            <th class="px-3 py-2 text-wc-text-tertiary">Energia</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-wc-border/50">
                                        @foreach ($detailProgram->dailyLogs->sortByDesc('log_date')->take(10) as $log)
                                            <tr>
                                                <td class="px-3 py-2 text-wc-text-secondary">{{ $log->log_date?->format('d/m/Y') }}</td>
                                                <td class="px-3 py-2">
                                                    @if ($log->workout_completed)
                                                        <span class="text-emerald-500">Completado</span>
                                                    @else
                                                        <span class="text-red-400">No</span>
                                                    @endif
                                                </td>
                                                <td class="px-3 py-2">
                                                    <span class="capitalize text-wc-text-secondary">{{ $log->nutrition_adherence ?? '-' }}</span>
                                                </td>
                                                <td class="px-3 py-2 font-data text-wc-text">{{ $log->mood_level ?? '-' }}/10</td>
                                                <td class="px-3 py-2 font-data text-wc-text">{{ $log->energy_level ?? '-' }}/10</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Modal Footer --}}
                <div class="flex justify-end border-t border-wc-border px-6 py-4">
                    <button wire:click="closeDetail"
                            class="rounded-lg bg-wc-bg-tertiary px-4 py-2 text-sm font-medium text-wc-text-secondary hover:text-wc-text transition-colors">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>
