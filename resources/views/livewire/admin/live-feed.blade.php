<div wire:poll.10s="loadFeed">
    {{-- Header --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-3">
            <h1 class="font-display text-3xl tracking-wide text-wc-text">LIVE FEED</h1>
            <span class="flex items-center gap-2 rounded-full bg-emerald-500/10 px-3 py-1 text-xs font-medium text-emerald-400">
                <span class="relative flex h-2 w-2">
                    <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex h-2 w-2 rounded-full bg-emerald-500"></span>
                </span>
                Auto-actualizacion cada 10s
            </span>
        </div>
    </div>

    {{-- Stats Row --}}
    <div class="mb-6 grid grid-cols-2 gap-4 lg:grid-cols-4">
        {{-- Events Today --}}
        <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-5">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-sky-500/10">
                    <svg class="h-5 w-5 text-sky-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" />
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-bold font-data text-wc-text">{{ $eventsToday }}</p>
                    <p class="text-xs text-wc-text-tertiary">Eventos hoy</p>
                </div>
            </div>
        </div>

        {{-- Inscriptions Today --}}
        <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-5">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-sky-500/10">
                    <svg class="h-5 w-5 text-sky-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15a2.25 2.25 0 012.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z" />
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-bold font-data text-wc-text">{{ $inscriptionsToday }}</p>
                    <p class="text-xs text-wc-text-tertiary">Inscripciones hoy</p>
                </div>
            </div>
        </div>

        {{-- Payments Today --}}
        <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-5">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-500/10">
                    <svg class="h-5 w-5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" />
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-bold font-data text-wc-text">{{ $paymentsToday }}</p>
                    <p class="text-xs text-wc-text-tertiary">Pagos hoy</p>
                </div>
            </div>
        </div>

        {{-- Active Conversations --}}
        <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-5">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-violet-500/10">
                    <svg class="h-5 w-5 text-violet-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 01-.825-.242m9.345-8.334a2.126 2.126 0 00-.476-.095 48.64 48.64 0 00-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0011.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155" />
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-bold font-data text-wc-text">{{ $activeConversations }}</p>
                    <p class="text-xs text-wc-text-tertiary">Conversaciones</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center">
        {{-- Type Filter --}}
        <div class="flex items-center gap-2">
            <label for="typeFilter" class="text-sm font-medium text-wc-text-secondary">Tipo:</label>
            <select wire:model.live="typeFilter" id="typeFilter"
                    class="rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent">
                <option value="all">Todos</option>
                <option value="inscriptions">Inscripciones</option>
                <option value="payments">Pagos</option>
                <option value="checkins">Check-ins</option>
                <option value="messages">Mensajes</option>
                <option value="community">Comunidad</option>
            </select>
        </div>

        {{-- Date Filter --}}
        <div class="flex items-center gap-2">
            <label for="dateFilter" class="text-sm font-medium text-wc-text-secondary">Periodo:</label>
            <select wire:model.live="dateFilter" id="dateFilter"
                    class="rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent">
                <option value="today">Hoy</option>
                <option value="week">Ultima semana</option>
                <option value="month">Ultimo mes</option>
                <option value="all">Todo</option>
            </select>
        </div>

        {{-- Item Count --}}
        <div class="sm:ml-auto">
            <span class="text-sm text-wc-text-tertiary">{{ count($feed) }} eventos</span>
        </div>
    </div>

    {{-- Feed Timeline --}}
    @if(count($feed) > 0)
        <div class="relative">
            {{-- Vertical line --}}
            <div class="absolute left-5 top-0 bottom-0 w-px bg-wc-border sm:left-6"></div>

            <div class="space-y-1">
                @foreach($feed as $index => $item)
                    <div class="relative flex items-start gap-4 py-3 pl-12 pr-4 rounded-lg transition-colors hover:bg-wc-bg-tertiary/50 sm:pl-14"
                         wire:key="feed-{{ $index }}-{{ $item['type'] }}-{{ $item['timestamp'] ?? $index }}">

                        {{-- Colored dot --}}
                        <div class="absolute left-3.5 top-4 sm:left-4.5">
                            @switch($item['color'])
                                @case('sky')
                                    <div class="h-3 w-3 rounded-full border-2 border-wc-bg bg-sky-500 ring-2 ring-sky-500/20"></div>
                                    @break
                                @case('emerald')
                                    <div class="h-3 w-3 rounded-full border-2 border-wc-bg bg-emerald-500 ring-2 ring-emerald-500/20"></div>
                                    @break
                                @case('orange')
                                    <div class="h-3 w-3 rounded-full border-2 border-wc-bg bg-orange-500 ring-2 ring-orange-500/20"></div>
                                    @break
                                @case('violet')
                                    <div class="h-3 w-3 rounded-full border-2 border-wc-bg bg-violet-500 ring-2 ring-violet-500/20"></div>
                                    @break
                                @case('red')
                                    <div class="h-3 w-3 rounded-full border-2 border-wc-bg bg-red-500 ring-2 ring-red-500/20"></div>
                                    @break
                                @case('pink')
                                    <div class="h-3 w-3 rounded-full border-2 border-wc-bg bg-pink-500 ring-2 ring-pink-500/20"></div>
                                    @break
                            @endswitch
                        </div>

                        {{-- Icon circle --}}
                        <div class="shrink-0">
                            @switch($item['color'])
                                @case('sky')
                                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-sky-500/10">
                                        @include('livewire.admin.live-feed-icon', ['icon' => $item['icon'], 'colorClass' => 'text-sky-400'])
                                    </div>
                                    @break
                                @case('emerald')
                                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-emerald-500/10">
                                        @include('livewire.admin.live-feed-icon', ['icon' => $item['icon'], 'colorClass' => 'text-emerald-400'])
                                    </div>
                                    @break
                                @case('orange')
                                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-orange-500/10">
                                        @include('livewire.admin.live-feed-icon', ['icon' => $item['icon'], 'colorClass' => 'text-orange-400'])
                                    </div>
                                    @break
                                @case('violet')
                                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-violet-500/10">
                                        @include('livewire.admin.live-feed-icon', ['icon' => $item['icon'], 'colorClass' => 'text-violet-400'])
                                    </div>
                                    @break
                                @case('red')
                                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-red-500/10">
                                        @include('livewire.admin.live-feed-icon', ['icon' => $item['icon'], 'colorClass' => 'text-red-400'])
                                    </div>
                                    @break
                                @case('pink')
                                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-pink-500/10">
                                        @include('livewire.admin.live-feed-icon', ['icon' => $item['icon'], 'colorClass' => 'text-pink-400'])
                                    </div>
                                    @break
                            @endswitch
                        </div>

                        {{-- Content --}}
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2">
                                @switch($item['color'])
                                    @case('sky')
                                        <span class="inline-flex rounded-full bg-sky-500/10 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wider text-sky-400">{{ $item['title'] }}</span>
                                        @break
                                    @case('emerald')
                                        <span class="inline-flex rounded-full bg-emerald-500/10 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wider text-emerald-400">{{ $item['title'] }}</span>
                                        @break
                                    @case('orange')
                                        <span class="inline-flex rounded-full bg-orange-500/10 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wider text-orange-400">{{ $item['title'] }}</span>
                                        @break
                                    @case('violet')
                                        <span class="inline-flex rounded-full bg-violet-500/10 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wider text-violet-400">{{ $item['title'] }}</span>
                                        @break
                                    @case('red')
                                        <span class="inline-flex rounded-full bg-red-500/10 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wider text-red-400">{{ $item['title'] }}</span>
                                        @break
                                    @case('pink')
                                        <span class="inline-flex rounded-full bg-pink-500/10 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wider text-pink-400">{{ $item['title'] }}</span>
                                        @break
                                @endswitch
                                <span class="text-xs text-wc-text-tertiary">{{ $item['time_ago'] }}</span>
                            </div>
                            <p class="mt-1 text-sm text-wc-text-secondary truncate">{{ $item['description'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @else
        {{-- Empty State --}}
        <div class="flex flex-col items-center justify-center rounded-card border border-wc-border bg-wc-bg-tertiary py-16 px-6 text-center">
            <div class="mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-wc-bg-secondary">
                <svg class="h-8 w-8 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-wc-text">Sin eventos</h3>
            <p class="mt-1 text-sm text-wc-text-tertiary">No hay actividad para el periodo y filtro seleccionado.</p>
        </div>
    @endif
</div>
