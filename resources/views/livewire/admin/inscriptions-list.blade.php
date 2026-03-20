<div class="space-y-6">

    {{-- Header --}}
    <div>
        <h1 class="font-display text-3xl tracking-wide text-wc-text sm:text-4xl">Inscripciones</h1>
        <p class="mt-1 text-sm text-wc-text-tertiary">Gestiona las solicitudes de inscripcion</p>
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
                       placeholder="Buscar por nombre o email..."
                       class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary py-2 pl-10 pr-4 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-red-500 focus:outline-none focus:ring-1 focus:ring-red-500">
            </div>

            {{-- Status filter --}}
            <select wire:model.live="statusFilter"
                    class="rounded-lg border border-wc-border bg-wc-bg-secondary py-2 pl-3 pr-8 text-sm text-wc-text focus:border-red-500 focus:outline-none focus:ring-1 focus:ring-red-500">
                <option value="">Todos los estados</option>
                <option value="pendiente">Pendiente</option>
                <option value="contactado">Contactado</option>
                <option value="convertido">Convertido</option>
                <option value="rechazado">Rechazado</option>
            </select>

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

            {{-- Clear filters --}}
            @if($search !== '' || $statusFilter !== '' || $planFilter !== '')
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
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Nombre</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Email</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Plan</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Estado</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Ciudad</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Fecha</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-wc-border">
                    @forelse($inscriptions as $inscription)
                        <tr class="hover:bg-wc-bg-secondary/50 transition-colors">
                            {{-- Name --}}
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-sky-500/10">
                                        <span class="text-xs font-semibold text-sky-500">{{ substr($inscription->nombre ?? 'I', 0, 1) }}</span>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="truncate font-medium text-wc-text">{{ trim(($inscription->nombre ?? '') . ' ' . ($inscription->apellido ?? '')) ?: '-' }}</p>
                                        @if($inscription->whatsapp)
                                            <p class="text-xs text-wc-text-tertiary">{{ $inscription->whatsapp }}</p>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            {{-- Email --}}
                            <td class="px-4 py-3">
                                <span class="truncate text-xs text-wc-text-secondary">{{ $inscription->email ?? '-' }}</span>
                            </td>

                            {{-- Plan badge --}}
                            <td class="px-4 py-3">
                                @if($inscription->plan)
                                    @php
                                        $planColor = match($inscription->plan->value) {
                                            'esencial' => 'bg-sky-500/10 text-sky-500',
                                            'metodo' => 'bg-violet-500/10 text-violet-500',
                                            'elite' => 'bg-amber-500/10 text-amber-500',
                                            'rise' => 'bg-emerald-500/10 text-emerald-500',
                                            'presencial' => 'bg-orange-500/10 text-orange-500',
                                            default => 'bg-wc-bg-secondary text-wc-text-tertiary',
                                        };
                                    @endphp
                                    <span class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-semibold {{ $planColor }}">
                                        {{ $inscription->plan->label() }}
                                    </span>
                                @else
                                    <span class="text-xs text-wc-text-tertiary">-</span>
                                @endif
                            </td>

                            {{-- Status badge --}}
                            <td class="px-4 py-3">
                                @if($inscription->status)
                                    @php
                                        $inscStatusColor = match($inscription->status) {
                                            'pendiente' => 'bg-amber-500/10 text-amber-500',
                                            'contactado' => 'bg-sky-500/10 text-sky-500',
                                            'convertido' => 'bg-emerald-500/10 text-emerald-500',
                                            'rechazado' => 'bg-red-500/10 text-red-500',
                                            default => 'bg-wc-bg-secondary text-wc-text-tertiary',
                                        };
                                    @endphp
                                    <span class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-semibold {{ $inscStatusColor }}">
                                        {{ ucfirst($inscription->status) }}
                                    </span>
                                @else
                                    <span class="text-xs text-wc-text-tertiary">-</span>
                                @endif
                            </td>

                            {{-- City --}}
                            <td class="px-4 py-3">
                                <span class="text-xs text-wc-text-secondary">{{ $inscription->ciudad ?? '-' }}</span>
                            </td>

                            {{-- Date --}}
                            <td class="px-4 py-3">
                                <span class="font-data text-xs text-wc-text-secondary">{{ $inscription->created_at?->format('d/m/Y H:i') ?? '-' }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center">
                                <svg class="mx-auto h-8 w-8 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                </svg>
                                <p class="mt-2 text-sm text-wc-text-tertiary">No se encontraron inscripciones</p>
                                @if($search !== '' || $statusFilter !== '' || $planFilter !== '')
                                    <button wire:click="clearFilters" class="mt-2 text-xs font-medium text-red-500 hover:text-red-400 transition-colors">Limpiar filtros</button>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($inscriptions->hasPages())
            <div class="border-t border-wc-border px-4 py-3">
                {{ $inscriptions->links() }}
            </div>
        @endif
    </div>

</div>
