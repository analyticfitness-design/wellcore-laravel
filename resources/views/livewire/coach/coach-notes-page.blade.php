<div class="space-y-6">

    {{-- Success Message --}}
    @if($successMessage)
        <div
            x-data="{ show: true }"
            x-init="setTimeout(() => { show = false; $wire.dismissSuccess() }, 3500)"
            x-show="show"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="flex items-center gap-3 rounded-lg border border-emerald-500/20 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-400"
        >
            <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
            {{ $successMessage }}
        </div>
    @endif

    {{-- Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="font-display text-3xl tracking-wide text-wc-text sm:text-4xl">Notas y Tickets</h1>
            <p class="mt-1 text-sm text-wc-text-tertiary">Gestiona tus notas de clientes y tickets de soporte</p>
        </div>
    </div>

    {{-- Tab Bar --}}
    <div class="flex items-center gap-1 rounded-lg border border-wc-border bg-wc-bg-secondary p-1">
        <button
            wire:click="switchTab('notes')"
            class="flex items-center gap-2 rounded-md px-4 py-2 text-sm font-medium transition-colors
                   {{ $activeTab === 'notes'
                       ? 'bg-wc-accent text-white shadow-sm'
                       : 'text-wc-text-secondary hover:text-wc-text hover:bg-wc-bg-tertiary' }}"
        >
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
            </svg>
            Notas
            <span class="rounded-full bg-white/20 px-2 py-0.5 text-xs {{ $activeTab === 'notes' ? '' : 'bg-wc-bg-tertiary' }}">{{ $noteStats['total'] }}</span>
        </button>
        <button
            wire:click="switchTab('tickets')"
            class="flex items-center gap-2 rounded-md px-4 py-2 text-sm font-medium transition-colors
                   {{ $activeTab === 'tickets'
                       ? 'bg-wc-accent text-white shadow-sm'
                       : 'text-wc-text-secondary hover:text-wc-text hover:bg-wc-bg-tertiary' }}"
        >
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 0 1 0 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 0 1 0-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375Z" />
            </svg>
            Tickets
            @if($ticketStats['open'] > 0)
                <span class="rounded-full bg-amber-500/20 px-2 py-0.5 text-xs text-amber-400">{{ $ticketStats['open'] }}</span>
            @else
                <span class="rounded-full bg-white/20 px-2 py-0.5 text-xs {{ $activeTab === 'tickets' ? '' : 'bg-wc-bg-tertiary' }}">{{ $ticketStats['total'] }}</span>
            @endif
        </button>
    </div>

    {{-- ============================================================ --}}
    {{-- NOTES TAB                                                     --}}
    {{-- ============================================================ --}}
    @if($activeTab === 'notes')

        {{-- Note Type Stats --}}
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-5">
            <button wire:click="$set('noteTypeFilter', 'all')" class="rounded-card border p-3 text-center transition-colors {{ $noteTypeFilter === 'all' ? 'border-wc-accent bg-wc-accent/10' : 'border-wc-border bg-wc-bg-tertiary hover:border-wc-accent/50' }}">
                <p class="font-data text-2xl font-bold text-wc-text">{{ $noteStats['total'] }}</p>
                <p class="text-xs text-wc-text-tertiary">Todas</p>
            </button>
            <button wire:click="$set('noteTypeFilter', 'general')" class="rounded-card border p-3 text-center transition-colors {{ $noteTypeFilter === 'general' ? 'border-sky-500 bg-sky-500/10' : 'border-wc-border bg-wc-bg-tertiary hover:border-sky-500/50' }}">
                <p class="font-data text-2xl font-bold text-sky-400">{{ $noteStats['general'] }}</p>
                <p class="text-xs text-wc-text-tertiary">General</p>
            </button>
            <button wire:click="$set('noteTypeFilter', 'seguimiento')" class="rounded-card border p-3 text-center transition-colors {{ $noteTypeFilter === 'seguimiento' ? 'border-violet-500 bg-violet-500/10' : 'border-wc-border bg-wc-bg-tertiary hover:border-violet-500/50' }}">
                <p class="font-data text-2xl font-bold text-violet-400">{{ $noteStats['seguimiento'] }}</p>
                <p class="text-xs text-wc-text-tertiary">Seguimiento</p>
            </button>
            <button wire:click="$set('noteTypeFilter', 'alerta')" class="rounded-card border p-3 text-center transition-colors {{ $noteTypeFilter === 'alerta' ? 'border-amber-500 bg-amber-500/10' : 'border-wc-border bg-wc-bg-tertiary hover:border-amber-500/50' }}">
                <p class="font-data text-2xl font-bold text-amber-400">{{ $noteStats['alerta'] }}</p>
                <p class="text-xs text-wc-text-tertiary">Alerta</p>
            </button>
            <button wire:click="$set('noteTypeFilter', 'logro')" class="rounded-card border p-3 text-center transition-colors {{ $noteTypeFilter === 'logro' ? 'border-emerald-500 bg-emerald-500/10' : 'border-wc-border bg-wc-bg-tertiary hover:border-emerald-500/50' }}">
                <p class="font-data text-2xl font-bold text-emerald-400">{{ $noteStats['logro'] }}</p>
                <p class="text-xs text-wc-text-tertiary">Logro</p>
            </button>
        </div>

        {{-- Filter Bar + CTA --}}
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex flex-1 flex-col gap-3 sm:flex-row sm:items-center">
                {{-- Search --}}
                <div class="relative flex-1 sm:max-w-xs">
                    <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                    </svg>
                    <input
                        wire:model.live.debounce.300ms="search"
                        type="text"
                        placeholder="Buscar nota o cliente..."
                        class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary py-2 pl-10 pr-4 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent"
                    />
                </div>

                {{-- Client Filter --}}
                <select
                    wire:model.live="clientFilter"
                    class="rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent"
                >
                    <option value="all">Todos los clientes</option>
                    @foreach($clients as $client)
                        <option value="{{ $client->id }}">{{ $client->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Nueva Nota CTA --}}
            <button
                wire:click="openCreateNote"
                class="inline-flex items-center gap-2 rounded-lg bg-wc-accent px-4 py-2 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-red-700"
            >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Nueva Nota
            </button>
        </div>

        {{-- Notes List --}}
        @if($notes->count() > 0)
            <div class="space-y-3">
                @foreach($notes as $note)
                    <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-4 sm:p-5 transition-colors hover:border-wc-border/80
                                {{ $confirmDeleteId === $note['id'] ? 'ring-2 ring-red-500/30' : '' }}">

                        {{-- Delete confirmation overlay --}}
                        @if($confirmDeleteId === $note['id'])
                            <div class="flex items-center justify-between rounded-lg border border-red-500/20 bg-red-500/10 p-3 mb-3">
                                <p class="text-sm text-red-400">Eliminar esta nota?</p>
                                <div class="flex items-center gap-2">
                                    <button
                                        wire:click="cancelDelete"
                                        class="rounded-md px-3 py-1 text-xs font-medium text-wc-text-secondary hover:text-wc-text transition-colors"
                                    >
                                        Cancelar
                                    </button>
                                    <button
                                        wire:click="deleteNote"
                                        class="rounded-md bg-red-600 px-3 py-1 text-xs font-medium text-white hover:bg-red-700 transition-colors"
                                    >
                                        Eliminar
                                    </button>
                                </div>
                            </div>
                        @endif

                        <div class="flex items-start gap-3 sm:gap-4">
                            {{-- Client Avatar --}}
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-wc-accent/15">
                                <span class="text-sm font-semibold text-wc-accent">{{ $note['client_initial'] }}</span>
                            </div>

                            {{-- Content --}}
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <p class="text-sm font-medium text-wc-text">{{ $note['client_name'] }}</p>
                                    {{-- Note type badge --}}
                                    @php
                                        $badgeClass = match($note['note_type']) {
                                            'general' => 'bg-sky-500/10 text-sky-400 border-sky-500/20',
                                            'seguimiento' => 'bg-violet-500/10 text-violet-400 border-violet-500/20',
                                            'alerta' => 'bg-amber-500/10 text-amber-400 border-amber-500/20',
                                            'logro' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
                                            default => 'bg-sky-500/10 text-sky-400 border-sky-500/20',
                                        };
                                        $typeLabel = match($note['note_type']) {
                                            'general' => 'General',
                                            'seguimiento' => 'Seguimiento',
                                            'alerta' => 'Alerta',
                                            'logro' => 'Logro',
                                            default => 'General',
                                        };
                                    @endphp
                                    <span class="inline-flex rounded-full border px-2 py-0.5 text-[10px] font-semibold uppercase {{ $badgeClass }}">
                                        {{ $typeLabel }}
                                    </span>
                                </div>

                                <p class="mt-2 text-sm text-wc-text-secondary leading-relaxed whitespace-pre-line">{{ $note['note'] }}</p>

                                <div class="mt-3 flex flex-wrap items-center gap-3 text-xs text-wc-text-tertiary">
                                    <span>{{ $note['created_at'] }}</span>
                                    <span class="hidden sm:inline">&middot;</span>
                                    <span class="hidden sm:inline">{{ $note['created_at_ago'] }}</span>
                                </div>
                            </div>

                            {{-- Actions --}}
                            <div class="flex shrink-0 items-center gap-1">
                                <button
                                    wire:click="openEditNote({{ $note['id'] }})"
                                    class="rounded-lg p-2 text-wc-text-tertiary transition-colors hover:bg-wc-bg-secondary hover:text-wc-text"
                                    title="Editar"
                                >
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                    </svg>
                                </button>
                                <button
                                    wire:click="confirmDelete({{ $note['id'] }})"
                                    class="rounded-lg p-2 text-wc-text-tertiary transition-colors hover:bg-red-500/10 hover:text-red-400"
                                    title="Eliminar"
                                >
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            {{-- Empty State --}}
            <div class="rounded-card border border-dashed border-wc-border bg-wc-bg-tertiary p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-wc-text-tertiary/50" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                </svg>
                <h3 class="mt-4 text-sm font-medium text-wc-text">No hay notas</h3>
                <p class="mt-1 text-xs text-wc-text-tertiary">
                    @if($search !== '' || $noteTypeFilter !== 'all' || $clientFilter !== 'all')
                        No se encontraron notas con los filtros aplicados.
                    @else
                        Crea tu primera nota para llevar seguimiento de tus clientes.
                    @endif
                </p>
                @if($search === '' && $noteTypeFilter === 'all' && $clientFilter === 'all')
                    <button
                        wire:click="openCreateNote"
                        class="mt-4 inline-flex items-center gap-2 rounded-lg bg-wc-accent px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-700 transition-colors"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        Crear Nota
                    </button>
                @endif
            </div>
        @endif

    {{-- ============================================================ --}}
    {{-- TICKETS TAB                                                   --}}
    {{-- ============================================================ --}}
    @elseif($activeTab === 'tickets')

        {{-- Ticket Stats --}}
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
            <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-4 text-center">
                <p class="font-data text-2xl font-bold text-wc-text">{{ $ticketStats['total'] }}</p>
                <p class="text-xs text-wc-text-tertiary">Total</p>
            </div>
            <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-4 text-center">
                <p class="font-data text-2xl font-bold text-amber-400">{{ $ticketStats['open'] }}</p>
                <p class="text-xs text-wc-text-tertiary">Abiertos</p>
            </div>
            <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-4 text-center">
                <p class="font-data text-2xl font-bold text-sky-400">{{ $ticketStats['in_progress'] }}</p>
                <p class="text-xs text-wc-text-tertiary">En progreso</p>
            </div>
            <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-4 text-center">
                <p class="font-data text-2xl font-bold text-emerald-400">{{ $ticketStats['resolved'] }}</p>
                <p class="text-xs text-wc-text-tertiary">Resueltos</p>
            </div>
        </div>

        {{-- Filter Bar + CTA --}}
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                {{-- Status Filter --}}
                <select
                    wire:model.live="ticketStatusFilter"
                    class="rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent"
                >
                    <option value="all">Todos los estados</option>
                    <option value="open">Abierto</option>
                    <option value="in_progress">En progreso</option>
                    <option value="resolved">Resuelto</option>
                    <option value="closed">Cerrado</option>
                </select>

                {{-- Priority Filter --}}
                <select
                    wire:model.live="ticketPriorityFilter"
                    class="rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent"
                >
                    <option value="all">Todas las prioridades</option>
                    <option value="low">Baja</option>
                    <option value="normal">Normal</option>
                    <option value="high">Alta</option>
                    <option value="urgent">Urgente</option>
                </select>
            </div>

            {{-- Nuevo Ticket CTA --}}
            <button
                wire:click="openCreateTicket"
                class="inline-flex items-center gap-2 rounded-lg bg-wc-accent px-4 py-2 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-red-700"
            >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Nuevo Ticket
            </button>
        </div>

        {{-- Tickets List --}}
        @if($tickets->count() > 0)
            <div class="space-y-3">
                @foreach($tickets as $ticket)
                    <div class="rounded-card border border-wc-border bg-wc-bg-tertiary overflow-hidden">
                        {{-- Ticket Header (clickable) --}}
                        <button
                            wire:click="toggleTicketExpand('{{ $ticket['id'] }}')"
                            class="flex w-full items-center gap-4 p-4 sm:p-5 text-left"
                        >
                            {{-- Type icon --}}
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg
                                @php
                                    $typeIconBg = match($ticket['ticket_type']) {
                                        'soporte' => 'bg-sky-500/10',
                                        'tecnico' => 'bg-violet-500/10',
                                        'facturacion' => 'bg-emerald-500/10',
                                        default => 'bg-wc-text-tertiary/10',
                                    };
                                @endphp
                                {{ $typeIconBg }}
                            ">
                                @if($ticket['ticket_type'] === 'soporte')
                                    <svg class="h-5 w-5 text-sky-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 5.25h.008v.008H12v-.008Z" />
                                    </svg>
                                @elseif($ticket['ticket_type'] === 'tecnico')
                                    <svg class="h-5 w-5 text-violet-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17 17.25 21A2.652 2.652 0 0 0 21 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 1 1-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 0 0 4.486-6.336l-3.276 3.277a3.004 3.004 0 0 1-2.25-2.25l3.276-3.276a4.5 4.5 0 0 0-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437L5.909 7.5H4.5L2.25 3.75l1.5-1.5L7.5 4.5v1.409l4.26 4.26m-1.745 1.437 1.745-1.437m6.615 8.206L15.75 15.75M4.867 19.125h.008v.008h-.008v-.008Z" />
                                    </svg>
                                @elseif($ticket['ticket_type'] === 'facturacion')
                                    <svg class="h-5 w-5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" />
                                    </svg>
                                @else
                                    <svg class="h-5 w-5 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a5.969 5.969 0 0 1-.474-.065 4.48 4.48 0 0 0 .978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z" />
                                    </svg>
                                @endif
                            </div>

                            {{-- Info --}}
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <p class="text-sm font-medium capitalize text-wc-text">{{ $ticket['ticket_type'] }}</p>

                                    {{-- Status badge --}}
                                    @php
                                        $statusBadge = match($ticket['status_value']) {
                                            'open' => 'bg-amber-500/10 text-amber-400 border-amber-500/20',
                                            'in_progress' => 'bg-sky-500/10 text-sky-400 border-sky-500/20',
                                            'resolved' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
                                            'closed' => 'bg-zinc-500/10 text-zinc-400 border-zinc-500/20',
                                            default => 'bg-wc-text-tertiary/10 text-wc-text-tertiary border-wc-border',
                                        };
                                    @endphp
                                    <span class="inline-flex rounded-full border px-2 py-0.5 text-[10px] font-semibold {{ $statusBadge }}">
                                        {{ $ticket['status_label'] }}
                                    </span>

                                    {{-- Priority badge --}}
                                    @php
                                        $priorityBadge = match($ticket['priority_value']) {
                                            'high' => 'bg-red-500/10 text-red-400 border-red-500/20',
                                            'urgent' => 'bg-red-500/10 text-red-400 border-red-500/20',
                                            'normal' => 'bg-amber-500/10 text-amber-400 border-amber-500/20',
                                            'low' => 'bg-sky-500/10 text-sky-400 border-sky-500/20',
                                            default => 'bg-wc-text-tertiary/10 text-wc-text-tertiary border-wc-border',
                                        };
                                    @endphp
                                    <span class="inline-flex rounded-full border px-2 py-0.5 text-[10px] font-semibold {{ $priorityBadge }}">
                                        {{ $ticket['priority_label'] }}
                                    </span>
                                </div>

                                <p class="mt-1 text-xs text-wc-text-tertiary truncate">
                                    {{ Str::limit($ticket['description'], 100) }}
                                    @if($ticket['client_name'])
                                        &middot; {{ $ticket['client_name'] }}
                                    @endif
                                </p>

                                <div class="mt-1 flex items-center gap-3 text-xs text-wc-text-tertiary">
                                    <span>{{ $ticket['created_at_ago'] }}</span>
                                    @if($ticket['deadline'])
                                        <span>&middot; Deadline: {{ $ticket['deadline'] }}</span>
                                    @endif
                                </div>
                            </div>

                            {{-- Expand chevron --}}
                            <svg class="h-4 w-4 shrink-0 text-wc-text-tertiary transition-transform {{ $expandedTicketId === $ticket['id'] ? 'rotate-180' : '' }}"
                                 fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                            </svg>
                        </button>

                        {{-- Expanded content --}}
                        @if($expandedTicketId === $ticket['id'])
                            <div class="border-t border-wc-border bg-wc-bg-secondary/30 p-4 sm:p-5 space-y-4">
                                {{-- Full description --}}
                                <div>
                                    <p class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary mb-1">Descripcion</p>
                                    <p class="text-sm text-wc-text-secondary leading-relaxed whitespace-pre-line">{{ $ticket['description'] }}</p>
                                </div>

                                {{-- Details grid --}}
                                <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                                    <div>
                                        <p class="text-xs text-wc-text-tertiary">Tipo</p>
                                        <p class="text-sm font-medium capitalize text-wc-text">{{ $ticket['ticket_type'] }}</p>
                                    </div>
                                    @if($ticket['client_name'])
                                    <div>
                                        <p class="text-xs text-wc-text-tertiary">Cliente</p>
                                        <p class="text-sm font-medium text-wc-text">{{ $ticket['client_name'] }}</p>
                                    </div>
                                    @endif
                                    <div>
                                        <p class="text-xs text-wc-text-tertiary">Creado</p>
                                        <p class="text-sm font-medium text-wc-text">{{ $ticket['created_at'] }}</p>
                                    </div>
                                    @if($ticket['deadline'])
                                    <div>
                                        <p class="text-xs text-wc-text-tertiary">Deadline</p>
                                        <p class="text-sm font-medium text-wc-text">{{ $ticket['deadline'] }}</p>
                                    </div>
                                    @endif
                                </div>

                                {{-- Response (if admin has replied) --}}
                                @if($ticket['response'])
                                    <div class="rounded-lg border border-emerald-500/20 bg-emerald-500/5 p-3">
                                        <p class="text-xs font-medium uppercase tracking-wider text-emerald-400 mb-1">Respuesta del equipo</p>
                                        <p class="text-sm text-wc-text-secondary leading-relaxed whitespace-pre-line">{{ $ticket['response'] }}</p>
                                    </div>
                                @else
                                    <div class="rounded-lg border border-dashed border-wc-border bg-wc-bg-tertiary p-3 text-center">
                                        <p class="text-xs text-wc-text-tertiary">Sin respuesta aun. El equipo revisara tu ticket pronto.</p>
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @else
            {{-- Empty State --}}
            <div class="rounded-card border border-dashed border-wc-border bg-wc-bg-tertiary p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-wc-text-tertiary/50" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 0 1 0 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 0 1 0-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375Z" />
                </svg>
                <h3 class="mt-4 text-sm font-medium text-wc-text">No hay tickets</h3>
                <p class="mt-1 text-xs text-wc-text-tertiary">
                    @if($ticketStatusFilter !== 'all' || $ticketPriorityFilter !== 'all')
                        No se encontraron tickets con los filtros aplicados.
                    @else
                        Crea un ticket cuando necesites soporte del equipo.
                    @endif
                </p>
                @if($ticketStatusFilter === 'all' && $ticketPriorityFilter === 'all')
                    <button
                        wire:click="openCreateTicket"
                        class="mt-4 inline-flex items-center gap-2 rounded-lg bg-wc-accent px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-700 transition-colors"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        Crear Ticket
                    </button>
                @endif
            </div>
        @endif
    @endif

    {{-- ============================================================ --}}
    {{-- CREATE/EDIT NOTE MODAL                                        --}}
    {{-- ============================================================ --}}
    @if($showNoteModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4" x-data x-trap.noscroll="true">
            {{-- Backdrop --}}
            <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" wire:click="closeNoteModal"></div>

            {{-- Modal --}}
            <div class="relative w-full max-w-lg rounded-xl border border-wc-border bg-wc-bg-secondary shadow-2xl">
                {{-- Header --}}
                <div class="flex items-center justify-between border-b border-wc-border px-6 py-4">
                    <h2 class="font-display text-xl tracking-wide text-wc-text">
                        {{ $editingNoteId ? 'Editar Nota' : 'Nueva Nota' }}
                    </h2>
                    <button wire:click="closeNoteModal" class="rounded-lg p-1 text-wc-text-tertiary hover:text-wc-text transition-colors">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                {{-- Body --}}
                <div class="space-y-4 p-6">
                    {{-- Client select --}}
                    <div>
                        <label class="mb-1 block text-sm font-medium text-wc-text">Cliente</label>
                        <select
                            wire:model="noteClientId"
                            class="w-full rounded-lg border border-wc-border bg-wc-bg-tertiary px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent"
                        >
                            <option value="">Selecciona un cliente</option>
                            @foreach($clients as $client)
                                <option value="{{ $client->id }}">{{ $client->name }}</option>
                            @endforeach
                        </select>
                        @error('noteClientId')
                            <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Note type radio buttons --}}
                    <div>
                        <label class="mb-2 block text-sm font-medium text-wc-text">Tipo de nota</label>
                        <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
                            @foreach([
                                'general' => ['General', 'bg-sky-500/10 text-sky-400 border-sky-500/30', 'border-sky-500 bg-sky-500/20'],
                                'seguimiento' => ['Seguimiento', 'bg-violet-500/10 text-violet-400 border-violet-500/30', 'border-violet-500 bg-violet-500/20'],
                                'alerta' => ['Alerta', 'bg-amber-500/10 text-amber-400 border-amber-500/30', 'border-amber-500 bg-amber-500/20'],
                                'logro' => ['Logro', 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30', 'border-emerald-500 bg-emerald-500/20'],
                            ] as $typeVal => [$typeLabel, $normalClass, $activeClass])
                                <button
                                    type="button"
                                    wire:click="$set('noteType', '{{ $typeVal }}')"
                                    class="rounded-lg border px-3 py-2 text-xs font-medium text-center transition-colors
                                           {{ $noteType === $typeVal ? $activeClass : $normalClass }}"
                                >
                                    {{ $typeLabel }}
                                </button>
                            @endforeach
                        </div>
                        @error('noteType')
                            <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Note text --}}
                    <div>
                        <label class="mb-1 block text-sm font-medium text-wc-text">Nota</label>
                        <textarea
                            wire:model="noteText"
                            rows="4"
                            placeholder="Escribe tu nota aqui..."
                            class="w-full rounded-lg border border-wc-border bg-wc-bg-tertiary px-3 py-2 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent resize-none"
                        ></textarea>
                        @error('noteText')
                            <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Footer --}}
                <div class="flex items-center justify-end gap-3 border-t border-wc-border px-6 py-4">
                    <button
                        wire:click="closeNoteModal"
                        class="rounded-lg border border-wc-border px-4 py-2 text-sm font-medium text-wc-text-secondary hover:text-wc-text transition-colors"
                    >
                        Cancelar
                    </button>
                    <button
                        wire:click="saveNote"
                        wire:loading.attr="disabled"
                        class="btn-press inline-flex items-center gap-2 rounded-lg bg-wc-accent px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-700 transition-colors disabled:opacity-50"
                    >
                        <svg wire:loading wire:target="saveNote" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        <span wire:loading.remove wire:target="saveNote">{{ $editingNoteId ? 'Actualizar' : 'Guardar' }}</span>
                        <span wire:loading wire:target="saveNote">Guardando...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ============================================================ --}}
    {{-- CREATE TICKET MODAL                                           --}}
    {{-- ============================================================ --}}
    @if($showTicketModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4" x-data x-trap.noscroll="true">
            {{-- Backdrop --}}
            <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" wire:click="closeTicketModal"></div>

            {{-- Modal --}}
            <div class="relative w-full max-w-lg rounded-xl border border-wc-border bg-wc-bg-secondary shadow-2xl">
                {{-- Header --}}
                <div class="flex items-center justify-between border-b border-wc-border px-6 py-4">
                    <h2 class="font-display text-xl tracking-wide text-wc-text">Nuevo Ticket</h2>
                    <button wire:click="closeTicketModal" class="rounded-lg p-1 text-wc-text-tertiary hover:text-wc-text transition-colors">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                {{-- Body --}}
                <div class="space-y-4 p-6">
                    {{-- Ticket Type --}}
                    <div>
                        <label class="mb-1 block text-sm font-medium text-wc-text">Tipo de ticket</label>
                        <select
                            wire:model="ticketType"
                            class="w-full rounded-lg border border-wc-border bg-wc-bg-tertiary px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent"
                        >
                            <option value="">Selecciona un tipo</option>
                            <option value="soporte">Soporte</option>
                            <option value="tecnico">Tecnico</option>
                            <option value="facturacion">Facturacion</option>
                            <option value="otro">Otro</option>
                        </select>
                        @error('ticketType')
                            <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Client (optional) --}}
                    <div>
                        <label class="mb-1 block text-sm font-medium text-wc-text">Cliente <span class="text-wc-text-tertiary">(opcional)</span></label>
                        <select
                            wire:model="ticketClientName"
                            class="w-full rounded-lg border border-wc-border bg-wc-bg-tertiary px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent"
                        >
                            <option value="">Sin cliente especifico</option>
                            @foreach($clients as $client)
                                <option value="{{ $client->name }}">{{ $client->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Priority --}}
                    <div>
                        <label class="mb-2 block text-sm font-medium text-wc-text">Prioridad</label>
                        <div class="grid grid-cols-4 gap-2">
                            @foreach([
                                'low' => ['Baja', 'bg-sky-500/10 text-sky-400 border-sky-500/30', 'border-sky-500 bg-sky-500/20'],
                                'normal' => ['Normal', 'bg-amber-500/10 text-amber-400 border-amber-500/30', 'border-amber-500 bg-amber-500/20'],
                                'high' => ['Alta', 'bg-red-500/10 text-red-400 border-red-500/30', 'border-red-500 bg-red-500/20'],
                                'urgent' => ['Urgente', 'bg-red-500/10 text-red-400 border-red-500/30', 'border-red-500 bg-red-600/20'],
                            ] as $prioVal => [$prioLabel, $normalClass, $activeClass])
                                <button
                                    type="button"
                                    wire:click="$set('ticketPriority', '{{ $prioVal }}')"
                                    class="rounded-lg border px-3 py-2 text-xs font-medium text-center transition-colors
                                           {{ $ticketPriority === $prioVal ? $activeClass : $normalClass }}"
                                >
                                    {{ $prioLabel }}
                                </button>
                            @endforeach
                        </div>
                        @error('ticketPriority')
                            <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Description --}}
                    <div>
                        <label class="mb-1 block text-sm font-medium text-wc-text">Descripcion</label>
                        <textarea
                            wire:model="ticketDescription"
                            rows="4"
                            placeholder="Describe el problema o solicitud..."
                            class="w-full rounded-lg border border-wc-border bg-wc-bg-tertiary px-3 py-2 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent resize-none"
                        ></textarea>
                        @error('ticketDescription')
                            <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Footer --}}
                <div class="flex items-center justify-end gap-3 border-t border-wc-border px-6 py-4">
                    <button
                        wire:click="closeTicketModal"
                        class="rounded-lg border border-wc-border px-4 py-2 text-sm font-medium text-wc-text-secondary hover:text-wc-text transition-colors"
                    >
                        Cancelar
                    </button>
                    <button
                        wire:click="createTicket"
                        wire:loading.attr="disabled"
                        class="btn-press inline-flex items-center gap-2 rounded-lg bg-wc-accent px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-700 transition-colors disabled:opacity-50"
                    >
                        <svg wire:loading wire:target="createTicket" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        <span wire:loading.remove wire:target="createTicket">Crear Ticket</span>
                        <span wire:loading wire:target="createTicket">Creando...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>
