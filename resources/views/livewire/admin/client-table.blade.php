<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="font-display text-3xl tracking-wide text-wc-text sm:text-4xl">Clientes</h1>
            <p class="mt-1 text-sm text-wc-text-tertiary">Gestion de clientes de WellCore</p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button"
                    class="inline-flex items-center gap-2 rounded-lg border border-wc-border bg-wc-bg-tertiary px-4 py-2 text-sm font-medium text-wc-text hover:bg-wc-bg-secondary transition-colors opacity-50 cursor-not-allowed"
                    disabled>
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                </svg>
                Exportar
            </button>
        </div>
    </div>

    {{-- Filters --}}
    <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-4">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            {{-- Search --}}
            <div class="relative flex-1">
                <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                </svg>
                <input type="text"
                       wire:model.live.debounce.300ms="search"
                       placeholder="Buscar por nombre, email o codigo..."
                       class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary py-2 pl-10 pr-4 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-red-500 focus:outline-none focus:ring-1 focus:ring-red-500">
            </div>

            {{-- Plan filter --}}
            <select wire:model.live="planFilter"
                    class="rounded-lg border border-wc-border bg-wc-bg-secondary py-2 pl-3 pr-8 text-sm text-wc-text focus:border-red-500 focus:outline-none focus:ring-1 focus:ring-red-500">
                <option value="">Todos los planes</option>
                <option value="esencial">Esencial</option>
                <option value="metodo">Metodo</option>
                <option value="elite">Elite</option>
                <option value="rise">Rise</option>
                <option value="presencial">Presencial</option>
            </select>

            {{-- Status filter --}}
            <select wire:model.live="statusFilter"
                    class="rounded-lg border border-wc-border bg-wc-bg-secondary py-2 pl-3 pr-8 text-sm text-wc-text focus:border-red-500 focus:outline-none focus:ring-1 focus:ring-red-500">
                <option value="">Todos los estados</option>
                <option value="activo">Activo</option>
                <option value="inactivo">Inactivo</option>
                <option value="suspendido">Suspendido</option>
                <option value="pendiente">Pendiente</option>
            </select>

            {{-- Clear filters --}}
            @if($search !== '' || $planFilter !== '' || $statusFilter !== '')
                <button wire:click="clearFilters"
                        class="inline-flex items-center gap-1 rounded-lg px-3 py-2 text-xs font-medium text-red-500 hover:bg-red-500/10 transition-colors">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                    Limpiar
                </button>
            @endif
        </div>
    </div>

    {{-- Table --}}
    <div class="rounded-card border border-wc-border bg-wc-bg-tertiary overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-wc-border bg-wc-bg-secondary">
                        <th class="px-4 py-3 text-left">
                            <button wire:click="sortByColumn('name')" class="flex items-center gap-1 text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary hover:text-wc-text">
                                Cliente
                                @if($sortBy === 'name')
                                    <svg class="h-3 w-3 {{ $sortDir === 'desc' ? 'rotate-180' : '' }}" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 15.75 7.5-7.5 7.5 7.5" />
                                    </svg>
                                @endif
                            </button>
                        </th>
                        <th class="px-4 py-3 text-left">
                            <button wire:click="sortByColumn('client_code')" class="flex items-center gap-1 text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary hover:text-wc-text">
                                Codigo
                                @if($sortBy === 'client_code')
                                    <svg class="h-3 w-3 {{ $sortDir === 'desc' ? 'rotate-180' : '' }}" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 15.75 7.5-7.5 7.5 7.5" />
                                    </svg>
                                @endif
                            </button>
                        </th>
                        <th class="px-4 py-3 text-left">
                            <span class="text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Plan</span>
                        </th>
                        <th class="px-4 py-3 text-left">
                            <span class="text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Estado</span>
                        </th>
                        <th class="px-4 py-3 text-left">
                            <button wire:click="sortByColumn('fecha_inicio')" class="flex items-center gap-1 text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary hover:text-wc-text">
                                Fecha inicio
                                @if($sortBy === 'fecha_inicio')
                                    <svg class="h-3 w-3 {{ $sortDir === 'desc' ? 'rotate-180' : '' }}" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 15.75 7.5-7.5 7.5 7.5" />
                                    </svg>
                                @endif
                            </button>
                        </th>
                        <th class="px-4 py-3 text-right">
                            <span class="text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Acciones</span>
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-wc-border">
                    @forelse($clients as $client)
                        <tr class="hover:bg-wc-bg-secondary/50 transition-colors">
                            {{-- Name + email --}}
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-red-500/10">
                                        <span class="text-xs font-semibold text-red-500">{{ substr($client->name ?? 'C', 0, 1) }}</span>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="truncate font-medium text-wc-text">{{ $client->name }}</p>
                                        <p class="truncate text-xs text-wc-text-tertiary">{{ $client->email }}</p>
                                    </div>
                                </div>
                            </td>

                            {{-- Code --}}
                            <td class="px-4 py-3">
                                <span class="font-data text-xs text-wc-text-secondary">{{ $client->client_code ?? '-' }}</span>
                            </td>

                            {{-- Plan badge --}}
                            <td class="px-4 py-3">
                                @if($client->plan)
                                    @php
                                        $planColor = match($client->plan->value) {
                                            'esencial' => 'bg-sky-500/10 text-sky-500',
                                            'metodo' => 'bg-violet-500/10 text-violet-500',
                                            'elite' => 'bg-amber-500/10 text-amber-500',
                                            'rise' => 'bg-emerald-500/10 text-emerald-500',
                                            'presencial' => 'bg-orange-500/10 text-orange-500',
                                            default => 'bg-wc-bg-secondary text-wc-text-tertiary',
                                        };
                                    @endphp
                                    <span class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-semibold {{ $planColor }}">
                                        {{ $client->plan->label() }}
                                    </span>
                                @else
                                    <span class="text-xs text-wc-text-tertiary">-</span>
                                @endif
                            </td>

                            {{-- Status badge --}}
                            <td class="px-4 py-3">
                                @if($client->status)
                                    @php
                                        $statusColor = match($client->status->value) {
                                            'activo' => 'bg-emerald-500/10 text-emerald-500',
                                            'inactivo' => 'bg-zinc-500/10 text-zinc-400',
                                            'suspendido' => 'bg-red-500/10 text-red-500',
                                            'pendiente' => 'bg-amber-500/10 text-amber-500',
                                            'congelado' => 'bg-sky-500/10 text-sky-500',
                                            default => 'bg-wc-bg-secondary text-wc-text-tertiary',
                                        };
                                    @endphp
                                    <span class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-semibold {{ $statusColor }}">
                                        {{ $client->status->label() }}
                                    </span>
                                @else
                                    <span class="text-xs text-wc-text-tertiary">-</span>
                                @endif
                            </td>

                            {{-- Fecha inicio --}}
                            <td class="px-4 py-3">
                                <span class="font-data text-xs text-wc-text-secondary">{{ $client->fecha_inicio?->format('d/m/Y') ?? '-' }}</span>
                            </td>

                            {{-- Actions --}}
                            <td class="px-4 py-3 text-right">
                                <button class="inline-flex h-7 w-7 items-center justify-center rounded-lg text-wc-text-tertiary hover:bg-wc-bg-secondary hover:text-wc-text transition-colors"
                                        title="Ver detalle">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                    </svg>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center">
                                <svg class="mx-auto h-8 w-8 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                                </svg>
                                <p class="mt-2 text-sm text-wc-text-tertiary">No se encontraron clientes</p>
                                @if($search !== '' || $planFilter !== '' || $statusFilter !== '')
                                    <button wire:click="clearFilters" class="mt-2 text-xs font-medium text-red-500 hover:text-red-400 transition-colors">Limpiar filtros</button>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($clients->hasPages())
            <div class="border-t border-wc-border px-4 py-3">
                {{ $clients->links() }}
            </div>
        @endif
    </div>

</div>
