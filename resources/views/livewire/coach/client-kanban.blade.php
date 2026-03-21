<div
    x-data="kanbanBoard()"
    x-on:keydown.escape.window="$wire.closeDetail()"
    class="space-y-6"
>

    {{-- Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="font-display text-3xl tracking-wide text-wc-text sm:text-4xl">Kanban Clientes</h1>
            <p class="mt-1 text-sm text-wc-text-tertiary">{{ $totalClients }} cliente{{ $totalClients !== 1 ? 's' : '' }} &middot; Vista por actividad</p>
        </div>

        <div class="flex items-center gap-3">
            {{-- Search --}}
            <div class="relative w-full sm:w-64">
                <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                </svg>
                <input
                    type="text"
                    wire:model.live.debounce.400ms="search"
                    placeholder="Buscar cliente..."
                    class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary py-2 pl-10 pr-4 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent"
                >
            </div>

            {{-- Link back to list view --}}
            <a wire:navigate href="{{ route('coach.clients') }}"
               class="flex h-9 w-9 items-center justify-center rounded-lg border border-wc-border bg-wc-bg-secondary text-wc-text-secondary hover:text-wc-text transition-colors"
               title="Vista lista">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0ZM3.75 12h.007v.008H3.75V12Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm-.375 5.25h.007v.008H3.75v-.008Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                </svg>
            </a>

            {{-- Refresh --}}
            <button
                wire:click="loadBoard"
                class="btn-press flex h-9 w-9 items-center justify-center rounded-lg border border-wc-border bg-wc-bg-secondary text-wc-text-secondary hover:text-wc-text transition-colors"
                title="Actualizar"
            >
                <svg class="h-4 w-4" wire:loading.class="animate-spin" wire:target="loadBoard" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.992 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182" />
                </svg>
            </button>
        </div>
    </div>

    {{-- Summary stats --}}
    <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
        @foreach ($columns as $colKey => $col)
            @php
                $count = count($col['clients']);
                $colorMap = [
                    'blue' => 'bg-blue-500/10 text-blue-500 border-blue-500/20',
                    'emerald' => 'bg-emerald-500/10 text-emerald-500 border-emerald-500/20',
                    'amber' => 'bg-amber-500/10 text-amber-500 border-amber-500/20',
                    'red' => 'bg-red-500/10 text-red-500 border-red-500/20',
                ];
            @endphp
            <div class="flex items-center gap-3 rounded-lg border {{ $colorMap[$col['color']] }} p-3">
                <span class="text-2xl font-bold font-data">{{ $count }}</span>
                <span class="text-xs font-medium">{{ $col['title'] }}</span>
            </div>
        @endforeach
    </div>

    {{-- Kanban Board --}}
    <div class="kanban-scroll -mx-4 flex gap-4 overflow-x-auto px-4 pb-4 sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8"
         style="scroll-snap-type: x mandatory;">

        @foreach ($columns as $colKey => $col)
            @php
                $borderColorMap = [
                    'blue' => 'border-t-blue-500',
                    'emerald' => 'border-t-emerald-500',
                    'amber' => 'border-t-amber-500',
                    'red' => 'border-t-red-500',
                ];
                $headerBgMap = [
                    'blue' => 'bg-blue-500/10 text-blue-600 dark:text-blue-400',
                    'emerald' => 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400',
                    'amber' => 'bg-amber-500/10 text-amber-600 dark:text-amber-400',
                    'red' => 'bg-red-500/10 text-red-600 dark:text-red-400',
                ];
                $badgeBgMap = [
                    'blue' => 'bg-blue-500 text-white',
                    'emerald' => 'bg-emerald-500 text-white',
                    'amber' => 'bg-amber-500 text-white',
                    'red' => 'bg-red-500 text-white',
                ];
                $dotColorMap = [
                    'blue' => 'bg-blue-500',
                    'emerald' => 'bg-emerald-500',
                    'amber' => 'bg-amber-500',
                    'red' => 'bg-red-500',
                ];
            @endphp

            {{-- Column --}}
            <div
                class="kanban-column flex w-72 shrink-0 flex-col rounded-xl border border-t-[3px] border-wc-border {{ $borderColorMap[$col['color']] }} bg-wc-bg-secondary sm:w-[280px]"
                style="scroll-snap-align: start; min-height: 420px;"
                data-column="{{ $colKey }}"
                x-on:dragover.prevent="onDragOver($event, '{{ $colKey }}')"
                x-on:dragleave="onDragLeave($event, '{{ $colKey }}')"
                x-on:drop.prevent="onDrop($event, '{{ $colKey }}')"
                :class="{ 'ring-2 ring-wc-accent/40 bg-wc-accent/5': dragOverColumn === '{{ $colKey }}' }"
            >
                {{-- Column header --}}
                <div class="flex items-center justify-between border-b border-wc-border px-4 py-3">
                    <div class="flex items-center gap-2">
                        <span class="h-2 w-2 rounded-full {{ $dotColorMap[$col['color']] }}"></span>
                        <h3 class="text-sm font-semibold text-wc-text">{{ $col['title'] }}</h3>
                    </div>
                    <span class="inline-flex h-5 min-w-5 items-center justify-center rounded-full {{ $badgeBgMap[$col['color']] }} px-1.5 text-[10px] font-bold">
                        {{ count($col['clients']) }}
                    </span>
                </div>

                {{-- Column body --}}
                <div class="flex-1 space-y-2.5 overflow-y-auto p-3" style="max-height: 65vh;">
                    @forelse ($col['clients'] as $client)
                        {{-- Client Card --}}
                        <div
                            class="kanban-card card-hover-lift group relative cursor-grab rounded-lg border border-wc-border bg-wc-bg-tertiary p-3 shadow-sm transition-all hover:shadow-md hover:border-wc-accent/30 active:cursor-grabbing active:shadow-lg"
                            draggable="true"
                            data-client-id="{{ $client['id'] }}"
                            x-on:dragstart="onDragStart($event, {{ $client['id'] }})"
                            x-on:dragend="onDragEnd($event)"
                        >
                            {{-- Card top: avatar + name --}}
                            <div class="flex items-start gap-2.5">
                                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-wc-accent/15">
                                    <span class="text-sm font-semibold text-wc-accent">{{ $client['avatar_initial'] }}</span>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-medium text-wc-text truncate leading-tight">{{ $client['name'] }}</p>
                                    <span class="mt-0.5 inline-block rounded-full bg-wc-accent/10 px-1.5 py-0.5 text-[10px] font-semibold text-wc-accent leading-none">
                                        {{ $client['plan_label'] }}
                                    </span>
                                </div>
                            </div>

                            {{-- Activity info --}}
                            <div class="mt-2.5 flex items-center gap-3 text-[11px] text-wc-text-tertiary">
                                {{-- Days since activity --}}
                                <div class="flex items-center gap-1" title="Ultima actividad">
                                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                    </svg>
                                    @if ($client['days_since_activity'] !== null)
                                        <span class="{{ $client['days_since_activity'] > 14 ? 'text-red-500 font-semibold' : ($client['days_since_activity'] > 7 ? 'text-amber-500 font-semibold' : '') }}">
                                            {{ $client['days_since_activity'] === 0 ? 'Hoy' : $client['days_since_activity'] . 'd' }}
                                        </span>
                                    @else
                                        <span class="text-red-500">--</span>
                                    @endif
                                </div>

                                {{-- Last checkin --}}
                                @if ($client['last_checkin_date'])
                                    <div class="flex items-center gap-1" title="Ultimo check-in">
                                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                        </svg>
                                        {{ $client['last_checkin_date'] }}
                                    </div>
                                @endif

                                {{-- Last training --}}
                                @if ($client['last_training_date'])
                                    <div class="flex items-center gap-1" title="Ultimo entrenamiento">
                                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75Z" />
                                        </svg>
                                        {{ $client['last_training_date'] }}
                                    </div>
                                @endif
                            </div>

                            {{-- Badges row --}}
                            @if ($client['pending_checkins'] > 0 || $client['unread_messages'] > 0)
                                <div class="mt-2 flex items-center gap-1.5">
                                    @if ($client['pending_checkins'] > 0)
                                        <span class="inline-flex items-center gap-0.5 rounded-full bg-orange-500/10 px-1.5 py-0.5 text-[10px] font-semibold text-orange-500">
                                            <svg class="h-2.5 w-2.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                                            </svg>
                                            {{ $client['pending_checkins'] }} check-in{{ $client['pending_checkins'] > 1 ? 's' : '' }}
                                        </span>
                                    @endif
                                    @if ($client['unread_messages'] > 0)
                                        <span class="inline-flex items-center gap-0.5 rounded-full bg-violet-500/10 px-1.5 py-0.5 text-[10px] font-semibold text-violet-500">
                                            <svg class="h-2.5 w-2.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                                            </svg>
                                            {{ $client['unread_messages'] }}
                                        </span>
                                    @endif
                                </div>
                            @endif

                            {{-- Detail button --}}
                            <button
                                wire:click="openDetail({{ $client['id'] }})"
                                class="btn-press mt-2 flex w-full items-center justify-center gap-1 rounded-md border border-wc-border bg-wc-bg-secondary/50 py-1 text-[11px] font-medium text-wc-text-secondary opacity-0 transition-all group-hover:opacity-100 hover:bg-wc-bg-secondary hover:text-wc-text"
                            >
                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                </svg>
                                Ver detalle
                            </button>
                        </div>
                    @empty
                        {{-- Empty column --}}
                        <div class="flex flex-col items-center justify-center rounded-lg border border-dashed border-wc-border/50 py-8 text-center">
                            <svg class="h-8 w-8 text-wc-text-tertiary/50" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                            </svg>
                            <p class="mt-2 text-xs text-wc-text-tertiary/70">Sin clientes</p>
                        </div>
                    @endforelse
                </div>
            </div>
        @endforeach
    </div>

    {{-- Drag hint --}}
    <p class="hidden text-center text-[11px] text-wc-text-tertiary sm:block">
        <svg class="mr-1 inline h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" />
        </svg>
        Arrastra las tarjetas entre columnas para reclasificar clientes
    </p>

    {{-- Client Detail Modal --}}
    @if ($showDetail && !empty($detailClient))
        <div
            class="fixed inset-0 z-50 flex items-center justify-center p-4"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
        >
            {{-- Backdrop --}}
            <div class="absolute inset-0 bg-black/60" wire:click="closeDetail"></div>

            {{-- Modal content --}}
            <div class="relative w-full max-w-md rounded-xl border border-wc-border bg-wc-bg-tertiary shadow-2xl"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
            >
                {{-- Close button --}}
                <button wire:click="closeDetail" class="absolute right-3 top-3 flex h-7 w-7 items-center justify-center rounded-lg text-wc-text-tertiary hover:bg-wc-bg-secondary hover:text-wc-text transition-colors">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>

                <div class="p-6">
                    {{-- Client header --}}
                    <div class="flex items-center gap-4">
                        <div class="flex h-14 w-14 items-center justify-center rounded-full bg-wc-accent/15">
                            <span class="text-xl font-bold text-wc-accent">{{ $detailClient['avatar_initial'] }}</span>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-wc-text">{{ $detailClient['name'] }}</h3>
                            <p class="text-xs text-wc-text-tertiary">{{ $detailClient['email'] }}</p>
                            <div class="mt-1 flex items-center gap-2">
                                <span class="rounded-full bg-wc-accent/10 px-2 py-0.5 text-[10px] font-semibold text-wc-accent">{{ $detailClient['plan_label'] }}</span>
                                <span class="rounded-full bg-wc-bg-secondary px-2 py-0.5 text-[10px] font-medium text-wc-text-secondary">{{ $detailClient['status_label'] }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Stats grid --}}
                    <div class="mt-5 grid grid-cols-3 gap-3">
                        <div class="rounded-lg bg-wc-bg-secondary p-3 text-center">
                            <p class="text-lg font-bold font-data text-wc-text">Nv. {{ $detailClient['xp_level'] }}</p>
                            <p class="text-[10px] text-wc-text-tertiary">Nivel XP</p>
                        </div>
                        <div class="rounded-lg bg-wc-bg-secondary p-3 text-center">
                            <p class="text-lg font-bold font-data text-wc-text">{{ number_format($detailClient['xp_total']) }}</p>
                            <p class="text-[10px] text-wc-text-tertiary">XP Total</p>
                        </div>
                        <div class="rounded-lg bg-wc-bg-secondary p-3 text-center">
                            <p class="text-lg font-bold font-data text-wc-text">{{ $detailClient['streak_days'] }}</p>
                            <p class="text-[10px] text-wc-text-tertiary">Racha (dias)</p>
                        </div>
                    </div>

                    {{-- Info rows --}}
                    <div class="mt-4 space-y-2">
                        <div class="flex items-center justify-between rounded-lg bg-wc-bg-secondary/50 px-3 py-2">
                            <span class="text-xs text-wc-text-tertiary">Fecha inicio</span>
                            <span class="text-xs font-medium text-wc-text">{{ $detailClient['fecha_inicio'] }}</span>
                        </div>
                        <div class="flex items-center justify-between rounded-lg bg-wc-bg-secondary/50 px-3 py-2">
                            <span class="text-xs text-wc-text-tertiary">Ultimo check-in</span>
                            <span class="text-xs font-medium text-wc-text">{{ $detailClient['last_checkin'] }}</span>
                        </div>
                        @if ($detailClient['last_checkin_bienestar'])
                            <div class="flex items-center justify-between rounded-lg bg-wc-bg-secondary/50 px-3 py-2">
                                <span class="text-xs text-wc-text-tertiary">Bienestar</span>
                                <span class="text-xs font-medium text-wc-text">{{ $detailClient['last_checkin_bienestar'] }}/10</span>
                            </div>
                        @endif
                        <div class="flex items-center justify-between rounded-lg bg-wc-bg-secondary/50 px-3 py-2">
                            <span class="text-xs text-wc-text-tertiary">Plan activo</span>
                            <span class="text-xs font-medium text-wc-text capitalize">{{ $detailClient['active_plan_type'] }}</span>
                        </div>
                    </div>

                    {{-- Recent notes --}}
                    @if (!empty($detailClient['recent_notes']))
                        <div class="mt-4">
                            <p class="text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary mb-2">Notas recientes</p>
                            <div class="space-y-1.5">
                                @foreach ($detailClient['recent_notes'] as $note)
                                    <div class="rounded-lg border border-wc-border/50 bg-wc-bg-secondary/30 px-3 py-2">
                                        <p class="text-xs text-wc-text">{{ $note['note'] }}</p>
                                        <p class="mt-0.5 text-[10px] text-wc-text-tertiary">{{ $note['date'] }} &middot; {{ $note['type'] }}</p>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Actions --}}
                    <div class="mt-5 flex items-center gap-2">
                        <a href="{{ route('coach.checkins') }}"
                           class="btn-press inline-flex flex-1 items-center justify-center gap-1.5 rounded-lg bg-wc-accent px-3 py-2 text-xs font-medium text-white hover:bg-wc-accent-hover transition-colors">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                            Check-ins
                        </a>
                        <a href="{{ route('coach.messages') }}"
                           class="btn-press inline-flex flex-1 items-center justify-center gap-1.5 rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-xs font-medium text-wc-text hover:bg-wc-bg-tertiary transition-colors">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a5.969 5.969 0 0 1-.474-.065 4.48 4.48 0 0 0 .978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z" />
                            </svg>
                            Mensajes
                        </a>
                        <a href="{{ route('coach.notes') }}"
                           class="btn-press inline-flex flex-1 items-center justify-center gap-1.5 rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-xs font-medium text-wc-text hover:bg-wc-bg-tertiary transition-colors">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125" />
                            </svg>
                            Notas
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

@script
<script>
    Alpine.data('kanbanBoard', () => ({
        dragOverColumn: null,
        draggedClientId: null,

        onDragStart(event, clientId) {
            this.draggedClientId = clientId;
            event.dataTransfer.effectAllowed = 'move';
            event.dataTransfer.setData('text/plain', clientId.toString());

            // Add visual feedback to dragged card
            requestAnimationFrame(() => {
                event.target.style.opacity = '0.5';
                event.target.style.transform = 'rotate(2deg)';
            });
        },

        onDragEnd(event) {
            event.target.style.opacity = '1';
            event.target.style.transform = 'rotate(0deg)';
            this.dragOverColumn = null;
            this.draggedClientId = null;
        },

        onDragOver(event, columnKey) {
            event.dataTransfer.dropEffect = 'move';
            this.dragOverColumn = columnKey;
        },

        onDragLeave(event, columnKey) {
            // Only clear if we actually left the column (not entering a child)
            const rect = event.currentTarget.getBoundingClientRect();
            const x = event.clientX;
            const y = event.clientY;
            if (x < rect.left || x > rect.right || y < rect.top || y > rect.bottom) {
                this.dragOverColumn = null;
            }
        },

        onDrop(event, columnKey) {
            const clientId = parseInt(event.dataTransfer.getData('text/plain'));
            this.dragOverColumn = null;

            if (clientId && !isNaN(clientId)) {
                $wire.moveClient(clientId, columnKey);
            }
        },
    }));
</script>
@endscript

<style>
    .kanban-scroll {
        scrollbar-width: thin;
        scrollbar-color: var(--color-wc-border) transparent;
    }

    .kanban-scroll::-webkit-scrollbar {
        height: 6px;
    }

    .kanban-scroll::-webkit-scrollbar-track {
        background: transparent;
    }

    .kanban-scroll::-webkit-scrollbar-thumb {
        background-color: var(--color-wc-border);
        border-radius: 3px;
    }

    .kanban-card {
        transition: transform 0.15s ease, box-shadow 0.15s ease, opacity 0.15s ease;
    }

    .kanban-card:hover {
        transform: translateY(-1px);
    }

    .kanban-card[draggable="true"]:active {
        transform: rotate(2deg) scale(1.02);
    }

    @media (max-width: 640px) {
        .kanban-scroll {
            scroll-snap-type: x mandatory;
            -webkit-overflow-scrolling: touch;
        }

        .kanban-column {
            scroll-snap-align: center;
        }
    }
</style>
