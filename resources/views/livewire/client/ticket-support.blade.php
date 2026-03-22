<div class="space-y-6">

    {{-- Page header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="font-display text-3xl tracking-wide text-wc-text">SOPORTE</h1>
            <p class="mt-1 text-sm text-wc-text-secondary">Envia solicitudes a tu coach. Respuesta garantizada en 48 horas.</p>
        </div>
        <button wire:click="openForm"
                class="rounded-lg bg-wc-accent px-4 py-2.5 text-sm font-semibold text-white hover:bg-wc-accent-hover transition-colors">
            + Nueva Solicitud
        </button>
    </div>

    {{-- Success banner --}}
    @if($showSuccess)
        <div class="flex items-center justify-between rounded-xl border border-green-500/30 bg-green-500/10 px-4 py-3"
             wire:key="success-banner">
            <div class="flex items-center gap-3">
                <svg class="h-5 w-5 text-green-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                <p class="text-sm font-medium text-green-400">Solicitud enviada correctamente. Tu coach respondera en 48 horas.</p>
            </div>
            <button wire:click="dismissSuccess" class="text-green-400 hover:text-green-300">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    @endif

    {{-- Stats --}}
    <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-4 text-center">
            <p class="font-data text-2xl font-bold text-wc-text">{{ $stats['total'] }}</p>
            <p class="mt-1 text-[10px] font-medium uppercase tracking-wider text-wc-text-tertiary">Total</p>
        </div>
        <div class="rounded-xl border border-yellow-500/30 bg-yellow-500/5 p-4 text-center">
            <p class="font-data text-2xl font-bold text-yellow-400">{{ $stats['open'] }}</p>
            <p class="mt-1 text-[10px] font-medium uppercase tracking-wider text-wc-text-tertiary">Abiertos</p>
        </div>
        <div class="rounded-xl border border-blue-500/30 bg-blue-500/5 p-4 text-center">
            <p class="font-data text-2xl font-bold text-blue-400">{{ $stats['in_progress'] }}</p>
            <p class="mt-1 text-[10px] font-medium uppercase tracking-wider text-wc-text-tertiary">En Progreso</p>
        </div>
        <div class="rounded-xl border border-green-500/30 bg-green-500/5 p-4 text-center">
            <p class="font-data text-2xl font-bold text-green-400">{{ $stats['closed'] }}</p>
            <p class="mt-1 text-[10px] font-medium uppercase tracking-wider text-wc-text-tertiary">Resueltos</p>
        </div>
    </div>

    {{-- Status filter tabs (ENUM: open, in_progress, closed) --}}
    <div class="flex flex-wrap gap-2">
        @foreach(['all' => 'Todos', 'open' => 'Abiertos', 'in_progress' => 'En Progreso', 'closed' => 'Cerrados'] as $key => $label)
            <button wire:click="$set('statusFilter', '{{ $key }}')"
                    class="rounded-lg border px-3 py-1.5 text-sm font-medium transition-colors
                           {{ $statusFilter === $key
                               ? 'border-wc-accent bg-wc-accent/10 text-wc-accent'
                               : 'border-wc-border bg-wc-bg-tertiary text-wc-text-secondary hover:text-wc-text' }}">
                {{ $label }}
            </button>
        @endforeach
    </div>

    {{-- Ticket list --}}
    @if($tickets->count() > 0)
        <div class="space-y-3">
            @foreach($tickets as $ticket)
                @php
                    $statusVal = $ticket->status instanceof \App\Enums\TicketStatus ? $ticket->status->value : $ticket->status;
                    $priorityVal = $ticket->priority instanceof \App\Enums\TicketPriority ? $ticket->priority->value : $ticket->priority;
                    $statusColors = [
                        'open'        => 'bg-yellow-500/10 text-yellow-400 border-yellow-500/30',
                        'in_progress' => 'bg-blue-500/10 text-blue-400 border-blue-500/30',
                        'closed'      => 'bg-green-500/10 text-green-400 border-green-500/30',
                    ];
                    $statusLabels = [
                        'open'        => 'Abierto',
                        'in_progress' => 'En progreso',
                        'closed'      => 'Cerrado',
                    ];
                    $typeLabels = [
                        'rutina_nueva'       => 'Rutina nueva',
                        'cambio_rutina'      => 'Cambio de rutina',
                        'nutricion'          => 'Nutricion',
                        'habitos'            => 'Habitos',
                        'invitacion_cliente' => 'Invitacion cliente',
                        'otro'               => 'Otro',
                    ];
                    $priorityLabels = [
                        'normal'  => 'Normal',
                        'alta'    => 'Alta',
                        'low'     => 'Baja',
                        'high'    => 'Alta',
                        'urgent'  => 'Urgente',
                    ];
                    $priorityColors = [
                        'normal'  => 'text-wc-text-secondary bg-wc-bg-secondary',
                        'alta'    => 'text-wc-accent bg-wc-accent/10',
                        'low'     => 'text-wc-text-secondary bg-wc-bg-secondary',
                        'high'    => 'text-wc-accent bg-wc-accent/10',
                        'urgent'  => 'text-red-400 bg-red-500/10',
                    ];
                    $colorClass = $statusColors[$statusVal] ?? 'bg-wc-bg-secondary text-wc-text-secondary border-wc-border';
                    $isExpanded = $expandedId === $ticket->id;
                @endphp

                <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary transition-all hover:border-wc-accent/30"
                     wire:key="ticket-{{ $ticket->id }}">

                    {{-- Card header (always visible) --}}
                    <button wire:click="toggleExpand('{{ $ticket->id }}')"
                            class="flex w-full items-start gap-4 p-4 text-left">

                        {{-- Type icon --}}
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-wc-bg-secondary">
                            <svg class="h-5 w-5 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 0 1 0 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a3 3 0 0 1 0-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375Z" />
                            </svg>
                        </div>

                        {{-- Info --}}
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="text-sm font-semibold text-wc-text">
                                    {{ $typeLabels[$ticket->ticket_type] ?? $ticket->ticket_type }}
                                </span>
                                <span class="rounded-full border px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wider {{ $colorClass }}">
                                    {{ $statusLabels[$statusVal] ?? $statusVal }}
                                </span>
                                @if($priorityVal && in_array($priorityVal, ['alta', 'high', 'urgent']))
                                    <span class="rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wider {{ $priorityColors[$priorityVal] ?? '' }}">
                                        {{ $priorityLabels[$priorityVal] ?? $priorityVal }}
                                    </span>
                                @endif
                                @if($ticket->response)
                                    <span class="rounded-full bg-wc-accent/10 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wider text-wc-accent">
                                        Respondido
                                    </span>
                                @endif
                            </div>
                            <p class="mt-1 text-sm text-wc-text-secondary line-clamp-2">{{ $ticket->description }}</p>
                            <p class="mt-1 text-xs text-wc-text-tertiary">
                                {{ $ticket->created_at?->diffForHumans() }}
                                &middot; Limite: {{ $ticket->deadline?->format('d M Y, H:i') }}
                            </p>
                        </div>

                        {{-- Expand chevron --}}
                        <svg class="h-5 w-5 shrink-0 text-wc-text-tertiary transition-transform {{ $isExpanded ? 'rotate-180' : '' }}"
                             fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>

                    {{-- Expanded detail --}}
                    @if($isExpanded)
                        <div class="border-t border-wc-border px-4 pb-4 pt-4 space-y-4">
                            <div>
                                <h4 class="mb-1.5 text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Descripcion completa</h4>
                                <p class="text-sm text-wc-text leading-relaxed">{{ $ticket->description }}</p>
                            </div>

                            @if($ticket->response)
                                <div class="rounded-lg border border-wc-accent/20 bg-wc-accent/5 p-4">
                                    <h4 class="mb-1.5 flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-wc-accent">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.76c0 1.6 1.123 2.994 2.707 3.227 1.087.16 2.185.283 3.293.369V21l4.076-4.076a1.526 1.526 0 0 1 1.037-.443 48.282 48.282 0 0 0 5.68-.494c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z" />
                                        </svg>
                                        Respuesta del coach
                                    </h4>
                                    <p class="text-sm text-wc-text leading-relaxed">{{ $ticket->response }}</p>
                                    @if($ticket->resolved_at)
                                        <p class="mt-2 text-xs text-wc-text-tertiary">Respondido {{ $ticket->resolved_at->diffForHumans() }}</p>
                                    @endif
                                </div>
                            @else
                                <div class="rounded-lg border border-wc-border bg-wc-bg-secondary p-3 text-center">
                                    <p class="text-sm text-wc-text-tertiary">Pendiente de respuesta. Tu coach respondera pronto.</p>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @else
        {{-- Empty state --}}
        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-12 text-center">
            <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-wc-bg-secondary">
                <svg class="h-7 w-7 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 0 1 0 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a3 3 0 0 1 0-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375Z" />
                </svg>
            </div>
            <h3 class="mb-1 text-base font-semibold text-wc-text">
                @if($statusFilter !== 'all')
                    Sin tickets con ese estado
                @else
                    Sin solicitudes aun
                @endif
            </h3>
            <p class="text-sm text-wc-text-secondary">
                @if($statusFilter !== 'all')
                    Prueba con otro filtro.
                @else
                    Crea tu primera solicitud de soporte con el boton de arriba.
                @endif
            </p>
        </div>
    @endif

    {{-- New Ticket Form Modal --}}
    @if($showForm)
        <div class="fixed inset-0 z-50 flex items-end justify-center p-4 sm:items-center"
             wire:key="ticket-form-modal">
            {{-- Backdrop --}}
            <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" wire:click="closeForm"></div>

            {{-- Modal --}}
            <div class="relative z-10 w-full max-w-lg rounded-2xl border border-wc-border bg-wc-bg-secondary p-6 shadow-2xl">
                <div class="mb-5 flex items-center justify-between">
                    <h2 class="font-display text-2xl tracking-wide text-wc-text">NUEVA SOLICITUD</h2>
                    <button wire:click="closeForm" class="flex h-8 w-8 items-center justify-center rounded-lg border border-wc-border text-wc-text-secondary hover:text-wc-text">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form wire:submit="createTicket" class="space-y-4">

                    {{-- Ticket type --}}
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-wc-text-secondary">
                            Tipo de solicitud <span class="text-wc-accent">*</span>
                        </label>
                        <select wire:model="ticketType"
                                class="w-full rounded-lg border border-wc-border bg-wc-bg-tertiary py-2.5 px-3 text-sm text-wc-text focus:border-wc-accent focus:outline-none">
                            <option value="">-- Selecciona --</option>
                            <option value="rutina_nueva">Rutina nueva</option>
                            <option value="cambio_rutina">Cambio de rutina</option>
                            <option value="nutricion">Nutricion</option>
                            <option value="habitos">Habitos</option>
                            <option value="invitacion_cliente">Invitacion de cliente</option>
                            <option value="otro">Otro</option>
                        </select>
                        @error('ticketType')
                            <p class="mt-1 text-xs text-wc-accent">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Priority --}}
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-wc-text-secondary">
                            Prioridad
                        </label>
                        <div class="flex gap-3">
                            <label class="flex flex-1 cursor-pointer items-center gap-2 rounded-lg border p-3 transition-colors
                                          {{ $priority === 'normal' ? 'border-wc-accent bg-wc-accent/5' : 'border-wc-border bg-wc-bg-tertiary hover:border-wc-accent/40' }}">
                                <input type="radio" wire:model="priority" value="normal" class="accent-wc-accent" />
                                <span class="text-sm text-wc-text">Normal</span>
                            </label>
                            <label class="flex flex-1 cursor-pointer items-center gap-2 rounded-lg border p-3 transition-colors
                                          {{ $priority === 'alta' ? 'border-wc-accent bg-wc-accent/5' : 'border-wc-border bg-wc-bg-tertiary hover:border-wc-accent/40' }}">
                                <input type="radio" wire:model="priority" value="alta" class="accent-wc-accent" />
                                <span class="text-sm text-wc-text">Alta</span>
                            </label>
                        </div>
                        @error('priority')
                            <p class="mt-1 text-xs text-wc-accent">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Description --}}
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-wc-text-secondary">
                            Descripcion <span class="text-wc-accent">*</span>
                        </label>
                        <textarea wire:model="description"
                                  rows="5"
                                  placeholder="Describe tu solicitud con el mayor detalle posible..."
                                  class="w-full resize-none rounded-lg border border-wc-border bg-wc-bg-tertiary py-2.5 px-3 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none"></textarea>
                        @error('description')
                            <p class="mt-1 text-xs text-wc-accent">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Actions --}}
                    <div class="flex gap-3 pt-1">
                        <button type="button" wire:click="closeForm"
                                class="flex-1 rounded-lg border border-wc-border bg-wc-bg-tertiary py-2.5 text-sm font-medium text-wc-text-secondary hover:text-wc-text transition-colors">
                            Cancelar
                        </button>
                        <button type="submit"
                                class="btn-press flex-1 rounded-lg bg-wc-accent py-2.5 text-sm font-semibold text-white hover:bg-wc-accent-hover transition-colors"
                                wire:loading.attr="disabled"
                                wire:loading.class="opacity-70 cursor-not-allowed">
                            <span wire:loading.remove wire:target="createTicket">Enviar Solicitud</span>
                            <span wire:loading wire:target="createTicket" class="inline-flex items-center justify-center gap-2">
                                <svg class="h-4 w-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                                Enviando...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

</div>
