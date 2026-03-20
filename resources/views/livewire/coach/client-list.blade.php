<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="font-display text-3xl tracking-wide text-wc-text sm:text-4xl">Mis Clientes</h1>
            <p class="mt-1 text-sm text-wc-text-tertiary">{{ $totalClients }} cliente{{ $totalClients !== 1 ? 's' : '' }} activo{{ $totalClients !== 1 ? 's' : '' }}</p>
        </div>

        {{-- Search --}}
        <div class="relative w-full sm:w-72">
            <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
            </svg>
            <input
                type="text"
                wire:model.live.debounce.300ms="search"
                placeholder="Buscar por nombre..."
                class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary py-2 pl-10 pr-4 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent"
            >
        </div>
    </div>

    {{-- Client cards --}}
    @if($clients->count() > 0)
        <div class="space-y-3">
            @foreach($clients as $client)
                <div class="rounded-card border border-wc-border bg-wc-bg-tertiary overflow-hidden">
                    {{-- Client row --}}
                    <button
                        wire:click="toggleExpand({{ $client['id'] }})"
                        class="flex w-full items-center gap-4 p-4 text-left hover:bg-wc-bg-secondary/50 transition-colors"
                    >
                        {{-- Avatar --}}
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-wc-accent/15">
                            <span class="text-base font-semibold text-wc-accent">{{ $client['avatar_initial'] }}</span>
                        </div>

                        {{-- Name + plan --}}
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2">
                                <p class="text-sm font-medium text-wc-text truncate">{{ $client['name'] }}</p>
                                <span class="inline-flex shrink-0 rounded-full bg-wc-accent/10 px-2 py-0.5 text-[10px] font-semibold text-wc-accent">
                                    {{ $client['plan_label'] }}
                                </span>
                                @if($client['pending_checkins'] > 0)
                                    <span class="inline-flex shrink-0 rounded-full bg-orange-500/10 px-2 py-0.5 text-[10px] font-semibold text-orange-500">
                                        {{ $client['pending_checkins'] }} pendiente{{ $client['pending_checkins'] > 1 ? 's' : '' }}
                                    </span>
                                @endif
                            </div>
                            <div class="mt-0.5 flex items-center gap-4 text-xs text-wc-text-tertiary">
                                <span>Check-in: {{ $client['last_checkin'] }}</span>
                                <span class="hidden sm:inline">Mensaje: {{ $client['last_message'] }}</span>
                            </div>
                        </div>

                        {{-- Level badge --}}
                        <div class="hidden sm:flex items-center gap-2">
                            <div class="flex items-center gap-1 rounded-full bg-violet-500/10 px-2.5 py-1">
                                <svg class="h-3 w-3 text-violet-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z" />
                                </svg>
                                <span class="text-[11px] font-semibold text-violet-500">Nv. {{ $client['xp_level'] }}</span>
                            </div>
                        </div>

                        {{-- Expand chevron --}}
                        <svg class="h-4 w-4 shrink-0 text-wc-text-tertiary transition-transform {{ $expandedClient === $client['id'] ? 'rotate-180' : '' }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>

                    {{-- Expanded details --}}
                    @if($expandedClient === $client['id'])
                        <div class="border-t border-wc-border bg-wc-bg-secondary/30 px-4 py-4">
                            <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                                <div>
                                    <p class="text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary">XP Total</p>
                                    <p class="mt-1 text-sm font-semibold text-wc-text">{{ number_format($client['xp_total']) }}</p>
                                </div>
                                <div>
                                    <p class="text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary">Racha</p>
                                    <p class="mt-1 text-sm font-semibold text-wc-text">{{ $client['streak_days'] }} dias</p>
                                </div>
                                <div>
                                    <p class="text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary">Fecha inicio</p>
                                    <p class="mt-1 text-sm font-semibold text-wc-text">{{ $client['fecha_inicio'] }}</p>
                                </div>
                                <div>
                                    <p class="text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary">Ultimo check-in</p>
                                    <p class="mt-1 text-sm font-semibold text-wc-text">{{ $client['last_checkin_date'] ?? 'N/A' }}</p>
                                </div>
                            </div>

                            <div class="mt-4 flex items-center gap-2">
                                <a href="{{ route('coach.checkins') }}"
                                   class="inline-flex items-center gap-1.5 rounded-lg bg-wc-accent px-3 py-1.5 text-xs font-medium text-white hover:bg-wc-accent-hover transition-colors">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                    </svg>
                                    Ver check-ins
                                </a>
                                <a href="{{ route('coach.messages') }}"
                                   class="inline-flex items-center gap-1.5 rounded-lg border border-wc-border bg-wc-bg-tertiary px-3 py-1.5 text-xs font-medium text-wc-text hover:bg-wc-bg-secondary transition-colors">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a5.969 5.969 0 0 1-.474-.065 4.48 4.48 0 0 0 .978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z" />
                                    </svg>
                                    Enviar mensaje
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @else
        <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-12 text-center">
            <svg class="mx-auto h-12 w-12 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
            </svg>
            <p class="mt-3 text-sm font-medium text-wc-text">No se encontraron clientes</p>
            <p class="mt-1 text-xs text-wc-text-tertiary">
                @if($search !== '')
                    No hay resultados para "{{ $search }}"
                @else
                    No tienes clientes asignados aun
                @endif
            </p>
        </div>
    @endif

</div>
