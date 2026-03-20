<div class="space-y-6">

    {{-- Page header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="font-display text-3xl tracking-wide text-wc-text">TICKETS SOPORTE</h1>
            <p class="mt-1 text-sm text-wc-text-secondary">Gestiona las solicitudes de clientes y da respuesta oportuna.</p>
        </div>
    </div>

    {{-- Stats row --}}
    <div class="grid grid-cols-2 gap-3 sm:grid-cols-5">
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
            <p class="font-data text-2xl font-bold text-green-400">{{ $stats['resolved'] }}</p>
            <p class="mt-1 text-[10px] font-medium uppercase tracking-wider text-wc-text-tertiary">Resueltos</p>
        </div>
        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-4 text-center">
            <p class="font-data text-2xl font-bold text-wc-text-secondary">{{ $stats['closed'] }}</p>
            <p class="mt-1 text-[10px] font-medium uppercase tracking-wider text-wc-text-tertiary">Cerrados</p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="flex flex-wrap gap-3">
        {{-- Search --}}
        <div class="relative flex-1 min-w-48">
            <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
            </svg>
            <input type="text" wire:model.live.debounce.300ms="search"
                   placeholder="Buscar cliente, tipo..."
                   class="w-full rounded-lg border border-wc-border bg-wc-bg-tertiary py-2.5 pl-10 pr-4 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none" />
        </div>

        {{-- Status filter --}}
        <select wire:model.live="statusFilter"
                class="rounded-lg border border-wc-border bg-wc-bg-tertiary py-2.5 px-3 text-sm text-wc-text focus:border-wc-accent focus:outline-none">
            <option value="all">Todos los estados</option>
            <option value="open">Abiertos</option>
            <option value="in_progress">En progreso</option>
            <option value="resolved">Resueltos</option>
            <option value="closed">Cerrados</option>
        </select>

        {{-- Priority filter --}}
        <select wire:model.live="priorityFilter"
                class="rounded-lg border border-wc-border bg-wc-bg-tertiary py-2.5 px-3 text-sm text-wc-text focus:border-wc-accent focus:outline-none">
            <option value="all">Toda prioridad</option>
            <option value="normal">Normal</option>
            <option value="alta">Alta</option>
            <option value="high">Alta (alt)</option>
            <option value="urgent">Urgente</option>
        </select>
    </div>

    {{-- Tickets table --}}
    <div class="overflow-hidden rounded-xl border border-wc-border bg-wc-bg-tertiary">
        <table class="w-full">
            <thead>
                <tr class="border-b border-wc-border">
                    <th class="px-4 py-3 text-left">
                        <button wire:click="sortByColumn('client_name')" class="flex items-center gap-1 text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary hover:text-wc-text">
                            Cliente
                            @if($sortBy === 'client_name')
                                <svg class="h-3 w-3 {{ $sortDir === 'asc' ? '' : 'rotate-180' }}" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75 12 8.25l7.5 7.5"/></svg>
                            @endif
                        </button>
                    </th>
                    <th class="px-4 py-3 text-left">
                        <span class="text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary">Tipo</span>
                    </th>
                    <th class="hidden px-4 py-3 text-left sm:table-cell">
                        <button wire:click="sortByColumn('priority')" class="flex items-center gap-1 text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary hover:text-wc-text">
                            Prioridad
                            @if($sortBy === 'priority')
                                <svg class="h-3 w-3 {{ $sortDir === 'asc' ? '' : 'rotate-180' }}" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75 12 8.25l7.5 7.5"/></svg>
                            @endif
                        </button>
                    </th>
                    <th class="px-4 py-3 text-left">
                        <button wire:click="sortByColumn('status')" class="flex items-center gap-1 text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary hover:text-wc-text">
                            Estado
                            @if($sortBy === 'status')
                                <svg class="h-3 w-3 {{ $sortDir === 'asc' ? '' : 'rotate-180' }}" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75 12 8.25l7.5 7.5"/></svg>
                            @endif
                        </button>
                    </th>
                    <th class="hidden px-4 py-3 text-left lg:table-cell">
                        <button wire:click="sortByColumn('created_at')" class="flex items-center gap-1 text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary hover:text-wc-text">
                            Fecha
                            @if($sortBy === 'created_at')
                                <svg class="h-3 w-3 {{ $sortDir === 'asc' ? '' : 'rotate-180' }}" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75 12 8.25l7.5 7.5"/></svg>
                            @endif
                        </button>
                    </th>
                    <th class="px-4 py-3 text-right">
                        <span class="text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary">Accion</span>
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-wc-border">
                @forelse($tickets as $ticket)
                    @php
                        $statusVal = $ticket->status instanceof \App\Enums\TicketStatus ? $ticket->status->value : $ticket->status;
                        $priorityVal = $ticket->priority instanceof \App\Enums\TicketPriority ? $ticket->priority->value : $ticket->priority;
                        $statusColors = [
                            'open'        => 'bg-yellow-500/10 text-yellow-400',
                            'in_progress' => 'bg-blue-500/10 text-blue-400',
                            'resolved'    => 'bg-green-500/10 text-green-400',
                            'closed'      => 'bg-wc-bg-secondary text-wc-text-secondary',
                        ];
                        $statusLabels = [
                            'open'        => 'Abierto',
                            'in_progress' => 'En progreso',
                            'resolved'    => 'Resuelto',
                            'closed'      => 'Cerrado',
                        ];
                        $typeLabels = [
                            'rutina_nueva'       => 'Rutina nueva',
                            'cambio_rutina'      => 'Cambio rutina',
                            'nutricion'          => 'Nutricion',
                            'habitos'            => 'Habitos',
                            'invitacion_cliente' => 'Inv. cliente',
                            'otro'               => 'Otro',
                        ];
                        $priorityColors = [
                            'normal' => 'text-wc-text-secondary',
                            'alta'   => 'text-wc-accent font-semibold',
                            'high'   => 'text-wc-accent font-semibold',
                            'urgent' => 'text-red-400 font-semibold',
                            'low'    => 'text-wc-text-tertiary',
                        ];
                        $priorityLabels = [
                            'normal' => 'Normal',
                            'alta'   => 'Alta',
                            'high'   => 'Alta',
                            'urgent' => 'Urgente',
                            'low'    => 'Baja',
                        ];
                        $rowColorClass = $statusColors[$statusVal] ?? 'bg-wc-bg-secondary text-wc-text-secondary';
                    @endphp
                    <tr class="transition-colors hover:bg-wc-bg-secondary/50" wire:key="row-{{ $ticket->id }}">
                        <td class="px-4 py-3">
                            <div class="text-sm font-medium text-wc-text">{{ $ticket->client_name }}</div>
                            @if($ticket->response)
                                <div class="text-xs text-green-400">Respondido</div>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-xs text-wc-text-secondary">{{ $typeLabels[$ticket->ticket_type] ?? $ticket->ticket_type }}</span>
                        </td>
                        <td class="hidden px-4 py-3 sm:table-cell">
                            <span class="text-xs {{ $priorityColors[$priorityVal] ?? 'text-wc-text-secondary' }}">
                                {{ $priorityLabels[$priorityVal] ?? $priorityVal }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="rounded-full px-2.5 py-1 text-[10px] font-semibold uppercase tracking-wider {{ $rowColorClass }}">
                                {{ $statusLabels[$statusVal] ?? $statusVal }}
                            </span>
                        </td>
                        <td class="hidden px-4 py-3 lg:table-cell">
                            <span class="text-xs text-wc-text-tertiary">{{ $ticket->created_at?->format('d M Y') }}</span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <button wire:click="openRespond('{{ $ticket->id }}')"
                                    class="rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-1.5 text-xs font-medium text-wc-text-secondary hover:border-wc-accent hover:text-wc-accent transition-colors">
                                Responder
                            </button>
                        </td>
                    </tr>

                    {{-- Inline expansion / respond handled by modal below --}}
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-12 text-center text-sm text-wc-text-tertiary">
                            No hay tickets con los filtros seleccionados.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($tickets->hasPages())
        <div class="flex justify-center">
            {{ $tickets->links() }}
        </div>
    @endif

    {{-- Respond modal --}}
    @if($respondingId)
        @php
            $activeTicket = $tickets->firstWhere('id', $respondingId)
                ?? \App\Models\Ticket::find($respondingId);
        @endphp
        <div class="fixed inset-0 z-50 flex items-end justify-center p-4 sm:items-center"
             wire:key="respond-modal-{{ $respondingId }}">
            {{-- Backdrop --}}
            <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" wire:click="closeRespond"></div>

            {{-- Modal --}}
            <div class="relative z-10 w-full max-w-2xl rounded-2xl border border-wc-border bg-wc-bg-secondary p-6 shadow-2xl">
                <div class="mb-5 flex items-start justify-between gap-4">
                    <div>
                        <h2 class="font-display text-2xl tracking-wide text-wc-text">RESPONDER TICKET</h2>
                        @if($activeTicket)
                            <p class="mt-1 text-sm text-wc-text-secondary">
                                {{ $activeTicket->client_name }}
                                &middot;
                                @php
                                    $tTypeLabels = [
                                        'rutina_nueva'       => 'Rutina nueva',
                                        'cambio_rutina'      => 'Cambio de rutina',
                                        'nutricion'          => 'Nutricion',
                                        'habitos'            => 'Habitos',
                                        'invitacion_cliente' => 'Invitacion de cliente',
                                        'otro'               => 'Otro',
                                    ];
                                @endphp
                                {{ $tTypeLabels[$activeTicket->ticket_type] ?? $activeTicket->ticket_type }}
                            </p>
                        @endif
                    </div>
                    <button wire:click="closeRespond" class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border border-wc-border text-wc-text-secondary hover:text-wc-text">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                {{-- Original description --}}
                @if($activeTicket)
                    <div class="mb-4 rounded-lg border border-wc-border bg-wc-bg-tertiary p-4">
                        <h4 class="mb-1.5 text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary">Solicitud del cliente</h4>
                        <p class="text-sm text-wc-text leading-relaxed">{{ $activeTicket->description }}</p>
                    </div>
                @endif

                <form wire:submit="respond" class="space-y-4">

                    {{-- New status --}}
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-wc-text-secondary">
                            Cambiar estado
                        </label>
                        <select wire:model="newStatus"
                                class="w-full rounded-lg border border-wc-border bg-wc-bg-tertiary py-2.5 px-3 text-sm text-wc-text focus:border-wc-accent focus:outline-none">
                            <option value="open">Abierto</option>
                            <option value="in_progress">En progreso</option>
                            <option value="resolved">Resuelto</option>
                            <option value="closed">Cerrado</option>
                        </select>
                        @error('newStatus')
                            <p class="mt-1 text-xs text-wc-accent">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Response text --}}
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-wc-text-secondary">
                            Respuesta <span class="text-wc-accent">*</span>
                        </label>
                        <textarea wire:model="responseText"
                                  rows="6"
                                  placeholder="Escribe tu respuesta al cliente..."
                                  class="w-full resize-none rounded-lg border border-wc-border bg-wc-bg-tertiary py-2.5 px-3 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none"></textarea>
                        @error('responseText')
                            <p class="mt-1 text-xs text-wc-accent">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Actions --}}
                    <div class="flex gap-3 pt-1">
                        <button type="button" wire:click="closeRespond"
                                class="flex-1 rounded-lg border border-wc-border bg-wc-bg-tertiary py-2.5 text-sm font-medium text-wc-text-secondary hover:text-wc-text transition-colors">
                            Cancelar
                        </button>
                        <button type="submit"
                                class="flex-1 rounded-lg bg-wc-accent py-2.5 text-sm font-semibold text-white hover:bg-wc-accent-hover transition-colors"
                                wire:loading.attr="disabled"
                                wire:loading.class="opacity-70 cursor-not-allowed">
                            <span wire:loading.remove wire:target="respond">Guardar Respuesta</span>
                            <span wire:loading wire:target="respond">Guardando...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

</div>
