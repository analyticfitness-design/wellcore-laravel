<div class="space-y-6">

    {{-- Page header --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="font-display text-3xl tracking-wide text-wc-text">INVITACIONES</h1>
            <p class="mt-1 text-sm text-wc-text-secondary">Genera y gestiona codigos de invitacion para nuevos clientes.</p>
        </div>
        <button wire:click="openCreateModal"
                class="inline-flex items-center gap-2 rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-red-700 transition-colors">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Nueva Invitacion
        </button>
    </div>

    {{-- Stats row --}}
    <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-4 text-center">
            <p class="font-data text-2xl font-bold text-wc-text">{{ $stats['total'] }}</p>
            <p class="mt-1 text-[10px] font-medium uppercase tracking-wider text-wc-text-tertiary">Total</p>
        </div>
        <div class="rounded-xl border border-amber-500/30 bg-amber-500/5 p-4 text-center">
            <p class="font-data text-2xl font-bold text-amber-400">{{ $stats['pending'] }}</p>
            <p class="mt-1 text-[10px] font-medium uppercase tracking-wider text-wc-text-tertiary">Pendientes</p>
        </div>
        <div class="rounded-xl border border-emerald-500/30 bg-emerald-500/5 p-4 text-center">
            <p class="font-data text-2xl font-bold text-emerald-400">{{ $stats['used'] }}</p>
            <p class="mt-1 text-[10px] font-medium uppercase tracking-wider text-wc-text-tertiary">Usadas</p>
        </div>
        <div class="rounded-xl border border-zinc-500/30 bg-zinc-500/5 p-4 text-center">
            <p class="font-data text-2xl font-bold text-zinc-400">{{ $stats['expired'] }}</p>
            <p class="mt-1 text-[10px] font-medium uppercase tracking-wider text-wc-text-tertiary">Expiradas</p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="flex flex-wrap items-center gap-3">
        {{-- Search --}}
        <div class="relative min-w-48 flex-1">
            <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
            </svg>
            <input type="text" wire:model.live.debounce.300ms="search"
                   placeholder="Buscar codigo, email..."
                   class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary py-2 pl-10 pr-4 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none" />
        </div>

        {{-- Status filter --}}
        <select wire:model.live="statusFilter"
                class="rounded-lg border border-wc-border bg-wc-bg-tertiary px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none">
            <option value="all">Todos los estados</option>
            <option value="pending">Pendientes</option>
            <option value="used">Usadas</option>
            <option value="expired">Expiradas</option>
        </select>

        {{-- Clear filters --}}
        @if($search !== '' || $statusFilter !== 'all')
            <button wire:click="clearFilters"
                    class="rounded-lg border border-wc-border bg-wc-bg-tertiary px-3 py-2 text-sm text-wc-text-secondary hover:text-wc-text transition-colors">
                Limpiar
            </button>
        @endif
    </div>

    {{-- Table --}}
    <div class="overflow-hidden rounded-xl border border-wc-border bg-wc-bg-tertiary">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-wc-border">
                        <th class="px-4 py-3 text-left">
                            <button wire:click="sortByColumn('code')" class="flex items-center gap-1 text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary hover:text-wc-text">
                                Codigo
                                @if($sortBy === 'code')
                                    <svg class="h-3 w-3 {{ $sortDir === 'asc' ? '' : 'rotate-180' }}" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75 12 8.25l7.5 7.5"/></svg>
                                @endif
                            </button>
                        </th>
                        <th class="px-4 py-3 text-left">
                            <button wire:click="sortByColumn('plan')" class="flex items-center gap-1 text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary hover:text-wc-text">
                                Plan
                                @if($sortBy === 'plan')
                                    <svg class="h-3 w-3 {{ $sortDir === 'asc' ? '' : 'rotate-180' }}" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75 12 8.25l7.5 7.5"/></svg>
                                @endif
                            </button>
                        </th>
                        <th class="hidden px-4 py-3 text-left md:table-cell">
                            <span class="text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary">Email Hint</span>
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
                            <span class="text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary">Creado por</span>
                        </th>
                        <th class="hidden px-4 py-3 text-left lg:table-cell">
                            <button wire:click="sortByColumn('created_at')" class="flex items-center gap-1 text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary hover:text-wc-text">
                                Creado
                                @if($sortBy === 'created_at')
                                    <svg class="h-3 w-3 {{ $sortDir === 'asc' ? '' : 'rotate-180' }}" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75 12 8.25l7.5 7.5"/></svg>
                                @endif
                            </button>
                        </th>
                        <th class="hidden px-4 py-3 text-left xl:table-cell">
                            <button wire:click="sortByColumn('expires_at')" class="flex items-center gap-1 text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary hover:text-wc-text">
                                Expira
                                @if($sortBy === 'expires_at')
                                    <svg class="h-3 w-3 {{ $sortDir === 'asc' ? '' : 'rotate-180' }}" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75 12 8.25l7.5 7.5"/></svg>
                                @endif
                            </button>
                        </th>
                        <th class="hidden px-4 py-3 text-left xl:table-cell">
                            <span class="text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary">Usado por</span>
                        </th>
                        <th class="px-4 py-3 text-right">
                            <span class="text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary">Acciones</span>
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-wc-border">
                    @forelse($invitations as $inv)
                        @php
                            $statusVal = $inv->getRawOriginal('status') ?? 'pending';
                            $planVal = $inv->plan instanceof \App\Enums\PlanType ? $inv->plan->value : $inv->plan;

                            $statusColors = [
                                'pending' => 'bg-amber-500/10 text-amber-400',
                                'used'    => 'bg-emerald-500/10 text-emerald-400',
                                'expired' => 'bg-zinc-500/10 text-zinc-400',
                            ];
                            $statusLabels = [
                                'pending' => 'Pendiente',
                                'used'    => 'Usada',
                                'expired' => 'Expirada',
                            ];
                            $planColors = [
                                'esencial'   => 'bg-sky-500/10 text-sky-400',
                                'metodo'     => 'bg-violet-500/10 text-violet-400',
                                'elite'      => 'bg-amber-500/10 text-amber-400',
                                'presencial' => 'bg-orange-500/10 text-orange-400',
                                'rise'       => 'bg-red-500/10 text-red-400',
                            ];
                        @endphp
                        <tr class="transition-colors hover:bg-wc-bg-secondary/50" wire:key="inv-{{ $inv->id }}">
                            {{-- Code --}}
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2"
                                     x-data="{ copied: false }">
                                    <span class="font-data text-sm font-semibold tracking-wider text-wc-text">{{ $inv->code }}</span>
                                    <button
                                        x-on:click="navigator.clipboard.writeText('{{ $inv->code }}'); copied = true; setTimeout(() => copied = false, 2000)"
                                        class="flex h-6 w-6 shrink-0 items-center justify-center rounded border border-wc-border text-wc-text-tertiary hover:text-wc-text transition-colors"
                                        title="Copiar codigo">
                                        <template x-if="!copied">
                                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.666 3.888A2.25 2.25 0 0 0 13.5 2.25h-3c-1.03 0-1.9.693-2.166 1.638m7.332 0c.055.194.084.4.084.612v0a.75.75 0 0 1-.75.75H9.75a.75.75 0 0 1-.75-.75v0c0-.212.03-.418.084-.612m7.332 0c.646.049 1.288.11 1.927.184 1.1.128 1.907 1.077 1.907 2.185V19.5a2.25 2.25 0 0 1-2.25 2.25H6.75A2.25 2.25 0 0 1 4.5 19.5V6.257c0-1.108.806-2.057 1.907-2.185a48.208 48.208 0 0 1 1.927-.184" />
                                            </svg>
                                        </template>
                                        <template x-if="copied">
                                            <svg class="h-3.5 w-3.5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                            </svg>
                                        </template>
                                    </button>
                                </div>
                            </td>

                            {{-- Plan --}}
                            <td class="px-4 py-3">
                                <span class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wider {{ $planColors[$planVal] ?? 'bg-wc-bg-secondary text-wc-text-secondary' }}">
                                    {{ $inv->plan instanceof \App\Enums\PlanType ? $inv->plan->label() : ucfirst($planVal) }}
                                </span>
                            </td>

                            {{-- Email hint --}}
                            <td class="hidden px-4 py-3 md:table-cell">
                                <span class="text-xs text-wc-text-secondary">{{ $inv->email_hint ?? '—' }}</span>
                            </td>

                            {{-- Status --}}
                            <td class="px-4 py-3">
                                <span class="inline-flex rounded-full px-2.5 py-1 text-[10px] font-semibold uppercase tracking-wider {{ $statusColors[$statusVal] ?? 'bg-wc-bg-secondary text-wc-text-secondary' }}">
                                    {{ $statusLabels[$statusVal] ?? ucfirst($statusVal) }}
                                </span>
                            </td>

                            {{-- Created by --}}
                            <td class="hidden px-4 py-3 lg:table-cell">
                                <span class="text-xs text-wc-text-secondary">{{ $inv->createdBy?->name ?? '—' }}</span>
                            </td>

                            {{-- Created at --}}
                            <td class="hidden px-4 py-3 lg:table-cell">
                                <span class="text-xs text-wc-text-tertiary">{{ $inv->created_at?->diffForHumans() ?? '—' }}</span>
                            </td>

                            {{-- Expires at --}}
                            <td class="hidden px-4 py-3 xl:table-cell">
                                @if($inv->expires_at)
                                    <span class="text-xs {{ $inv->expires_at->isPast() ? 'text-red-400' : 'text-wc-text-tertiary' }}">
                                        {{ $inv->expires_at->format('d M Y') }}
                                    </span>
                                @else
                                    <span class="text-xs text-wc-text-tertiary">Sin limite</span>
                                @endif
                            </td>

                            {{-- Used by --}}
                            <td class="hidden px-4 py-3 xl:table-cell">
                                @if($inv->usedBy)
                                    <span class="text-xs text-emerald-400">{{ $inv->usedBy->name }}</span>
                                @else
                                    <span class="text-xs text-wc-text-tertiary">—</span>
                                @endif
                            </td>

                            {{-- Actions --}}
                            <td class="px-4 py-3 text-right">
                                @if($statusVal === 'pending')
                                    <button
                                        x-data
                                        x-on:click="if(confirm('Eliminar esta invitacion pendiente?')) $wire.deleteInvitation({{ $inv->id }})"
                                        class="rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-1.5 text-xs font-medium text-red-400 hover:border-red-500 hover:bg-red-500/10 transition-colors">
                                        Eliminar
                                    </button>
                                @elseif($statusVal === 'used')
                                    <span class="text-xs text-wc-text-tertiary">{{ $inv->used_at?->format('d M Y') }}</span>
                                @else
                                    <span class="text-xs text-wc-text-tertiary">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 py-16 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <svg class="h-10 w-10 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                                    </svg>
                                    <p class="text-sm text-wc-text-tertiary">No hay invitaciones con los filtros seleccionados.</p>
                                    <button wire:click="openCreateModal"
                                            class="mt-1 inline-flex items-center gap-1.5 rounded-lg bg-red-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-red-700 transition-colors">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                        </svg>
                                        Crear primera invitacion
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Pagination --}}
    @if($invitations->hasPages())
        <div class="flex justify-center">
            {{ $invitations->links() }}
        </div>
    @endif

    {{-- Create modal --}}
    @if($showCreateModal)
        <div class="fixed inset-0 z-50 flex items-end justify-center p-4 sm:items-center"
             x-data
             x-trap.noscroll="true"
             wire:key="create-modal">
            {{-- Backdrop --}}
            <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" wire:click="closeCreateModal"></div>

            {{-- Modal panel --}}
            <div class="relative z-10 w-full max-w-lg rounded-2xl border border-wc-border bg-wc-bg-secondary p-6 shadow-2xl">
                <div class="mb-5 flex items-start justify-between gap-4">
                    <div>
                        <h2 class="font-display text-2xl tracking-wide text-wc-text">NUEVA INVITACION</h2>
                        <p class="mt-1 text-sm text-wc-text-secondary">Se generara un codigo unico de 12 caracteres.</p>
                    </div>
                    <button wire:click="closeCreateModal"
                            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border border-wc-border text-wc-text-secondary hover:text-wc-text">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form wire:submit="createInvitation" class="space-y-4">
                    {{-- Plan --}}
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-wc-text-secondary">
                            Plan <span class="text-wc-accent">*</span>
                        </label>
                        <select wire:model="newPlan"
                                class="w-full rounded-lg border border-wc-border bg-wc-bg-tertiary px-3 py-2.5 text-sm text-wc-text focus:border-wc-accent focus:outline-none">
                            @foreach($planCases as $plan)
                                <option value="{{ $plan->value }}">{{ $plan->label() }}</option>
                            @endforeach
                        </select>
                        @error('newPlan')
                            <p class="mt-1 text-xs text-wc-accent">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Email hint --}}
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-wc-text-secondary">
                            Email / Referencia
                        </label>
                        <input type="text" wire:model="newEmailHint"
                               placeholder="email@ejemplo.com o nombre del referido"
                               class="w-full rounded-lg border border-wc-border bg-wc-bg-tertiary px-3 py-2.5 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none" />
                        @error('newEmailHint')
                            <p class="mt-1 text-xs text-wc-accent">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Note --}}
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-wc-text-secondary">
                            Nota interna
                        </label>
                        <textarea wire:model="newNote"
                                  rows="3"
                                  placeholder="Nota opcional sobre esta invitacion..."
                                  class="w-full resize-none rounded-lg border border-wc-border bg-wc-bg-tertiary px-3 py-2.5 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none"></textarea>
                        @error('newNote')
                            <p class="mt-1 text-xs text-wc-accent">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Expires at --}}
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-wc-text-secondary">
                            Fecha de expiracion
                        </label>
                        <input type="date" wire:model="newExpiresAt"
                               class="w-full rounded-lg border border-wc-border bg-wc-bg-tertiary px-3 py-2.5 text-sm text-wc-text focus:border-wc-accent focus:outline-none" />
                        <p class="mt-1 text-[10px] text-wc-text-tertiary">Dejar vacio para que no expire.</p>
                        @error('newExpiresAt')
                            <p class="mt-1 text-xs text-wc-accent">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Actions --}}
                    <div class="flex gap-3 pt-1">
                        <button type="button" wire:click="closeCreateModal"
                                class="flex-1 rounded-lg border border-wc-border bg-wc-bg-tertiary py-2.5 text-sm font-medium text-wc-text-secondary hover:text-wc-text transition-colors">
                            Cancelar
                        </button>
                        <button type="submit"
                                class="flex-1 rounded-lg bg-red-600 py-2.5 text-sm font-semibold text-white hover:bg-red-700 transition-colors"
                                wire:loading.attr="disabled"
                                wire:loading.class="opacity-70 cursor-not-allowed">
                            <span wire:loading.remove wire:target="createInvitation">Crear Invitacion</span>
                            <span wire:loading wire:target="createInvitation">Creando...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

</div>
