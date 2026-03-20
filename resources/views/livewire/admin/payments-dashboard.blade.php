<div class="space-y-6">

    {{-- Header --}}
    <div>
        <h1 class="font-display text-3xl tracking-wide text-wc-text sm:text-4xl">Pagos</h1>
        <p class="mt-1 text-sm text-wc-text-tertiary">Resumen financiero y listado de pagos</p>
    </div>

    {{-- Stats cards --}}
    <div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
        {{-- Total revenue --}}
        <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-4 sm:p-5">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Ingresos totales</span>
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-500/10">
                    <svg class="h-4 w-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </div>
            </div>
            <p class="mt-3 font-data text-3xl font-bold text-wc-text">${{ $totalRevenue }}</p>
            <p class="mt-0.5 text-xs text-wc-text-tertiary">COP historico</p>
        </div>

        {{-- This month --}}
        <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-4 sm:p-5">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Este mes</span>
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-violet-500/10">
                    <svg class="h-4 w-4 text-violet-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                    </svg>
                </div>
            </div>
            <p class="mt-3 font-data text-3xl font-bold text-wc-text">${{ $monthRevenue }}</p>
            <p class="mt-0.5 text-xs text-wc-text-tertiary">COP {{ now()->translatedFormat('F') }}</p>
        </div>

        {{-- Pending --}}
        <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-4 sm:p-5">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Pendientes</span>
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-orange-500/10">
                    <svg class="h-4 w-4 text-orange-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </div>
            </div>
            <p class="mt-3 font-data text-3xl font-bold text-wc-text">{{ $pendingPayments }}</p>
            <p class="mt-0.5 text-xs text-wc-text-tertiary">por confirmar</p>
        </div>

        {{-- Avg per client --}}
        <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-4 sm:p-5">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Promedio/cliente</span>
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-sky-500/10">
                    <svg class="h-4 w-4 text-sky-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                    </svg>
                </div>
            </div>
            <p class="mt-3 font-data text-3xl font-bold text-wc-text">${{ $avgPerClient }}</p>
            <p class="mt-0.5 text-xs text-wc-text-tertiary">COP promedio</p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-4">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            {{-- Status filter --}}
            <select wire:model.live="statusFilter"
                    class="rounded-lg border border-wc-border bg-wc-bg-secondary py-2 pl-3 pr-8 text-sm text-wc-text focus:border-red-500 focus:outline-none focus:ring-1 focus:ring-red-500">
                <option value="">Todos los estados</option>
                <option value="approved">Aprobado</option>
                <option value="pending">Pendiente</option>
                <option value="declined">Rechazado</option>
                <option value="voided">Anulado</option>
                <option value="error">Error</option>
            </select>

            {{-- Date from --}}
            <div class="flex items-center gap-2">
                <label class="text-xs text-wc-text-tertiary shrink-0">Desde</label>
                <input type="date"
                       wire:model.live="dateFrom"
                       class="rounded-lg border border-wc-border bg-wc-bg-secondary py-2 px-3 text-sm text-wc-text focus:border-red-500 focus:outline-none focus:ring-1 focus:ring-red-500">
            </div>

            {{-- Date to --}}
            <div class="flex items-center gap-2">
                <label class="text-xs text-wc-text-tertiary shrink-0">Hasta</label>
                <input type="date"
                       wire:model.live="dateTo"
                       class="rounded-lg border border-wc-border bg-wc-bg-secondary py-2 px-3 text-sm text-wc-text focus:border-red-500 focus:outline-none focus:ring-1 focus:ring-red-500">
            </div>

            {{-- Clear filters --}}
            @if($statusFilter !== '' || $dateFrom !== '' || $dateTo !== '')
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

    {{-- Payments table --}}
    <div class="rounded-card border border-wc-border bg-wc-bg-tertiary overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-wc-border bg-wc-bg-secondary">
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Cliente</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Plan</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Monto</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Estado</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Metodo</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Fecha</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-wc-border">
                    @forelse($payments as $payment)
                        <tr class="hover:bg-wc-bg-secondary/50 transition-colors">
                            {{-- Client name --}}
                            <td class="px-4 py-3">
                                <div class="min-w-0">
                                    <p class="truncate font-medium text-wc-text">{{ $payment->buyer_name ?? $payment->client?->name ?? '-' }}</p>
                                    <p class="truncate text-xs text-wc-text-tertiary">{{ $payment->email ?? $payment->client?->email ?? '' }}</p>
                                </div>
                            </td>

                            {{-- Plan --}}
                            <td class="px-4 py-3">
                                @if($payment->plan)
                                    @php
                                        $planColor = match($payment->plan->value) {
                                            'esencial' => 'bg-sky-500/10 text-sky-500',
                                            'metodo' => 'bg-violet-500/10 text-violet-500',
                                            'elite' => 'bg-amber-500/10 text-amber-500',
                                            'rise' => 'bg-emerald-500/10 text-emerald-500',
                                            'presencial' => 'bg-orange-500/10 text-orange-500',
                                            default => 'bg-wc-bg-secondary text-wc-text-tertiary',
                                        };
                                    @endphp
                                    <span class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-semibold {{ $planColor }}">
                                        {{ $payment->plan->label() }}
                                    </span>
                                @else
                                    <span class="text-xs text-wc-text-tertiary">-</span>
                                @endif
                            </td>

                            {{-- Amount --}}
                            <td class="px-4 py-3 text-right">
                                <span class="font-data text-sm font-semibold text-wc-text">${{ number_format((float) $payment->amount, 0, ',', '.') }}</span>
                                <p class="text-[10px] text-wc-text-tertiary">{{ $payment->currency ?? 'COP' }}</p>
                            </td>

                            {{-- Status badge --}}
                            <td class="px-4 py-3">
                                @if($payment->status)
                                    @php
                                        $payStatusColor = match($payment->status->value) {
                                            'approved' => 'bg-emerald-500/10 text-emerald-500',
                                            'pending' => 'bg-amber-500/10 text-amber-500',
                                            'declined' => 'bg-red-500/10 text-red-500',
                                            'voided' => 'bg-zinc-500/10 text-zinc-400',
                                            'error' => 'bg-red-500/10 text-red-500',
                                            default => 'bg-wc-bg-secondary text-wc-text-tertiary',
                                        };
                                    @endphp
                                    <span class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-semibold {{ $payStatusColor }}">
                                        {{ $payment->status->label() }}
                                    </span>
                                @else
                                    <span class="text-xs text-wc-text-tertiary">-</span>
                                @endif
                            </td>

                            {{-- Payment method --}}
                            <td class="px-4 py-3">
                                <span class="text-xs text-wc-text-secondary">{{ $payment->payment_method ?? '-' }}</span>
                            </td>

                            {{-- Date --}}
                            <td class="px-4 py-3">
                                <span class="font-data text-xs text-wc-text-secondary">{{ $payment->created_at?->format('d/m/Y H:i') ?? '-' }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center">
                                <svg class="mx-auto h-8 w-8 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" />
                                </svg>
                                <p class="mt-2 text-sm text-wc-text-tertiary">No se encontraron pagos</p>
                                @if($statusFilter !== '' || $dateFrom !== '' || $dateTo !== '')
                                    <button wire:click="clearFilters" class="mt-2 text-xs font-medium text-red-500 hover:text-red-400 transition-colors">Limpiar filtros</button>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($payments->hasPages())
            <div class="border-t border-wc-border px-4 py-3">
                {{ $payments->links() }}
            </div>
        @endif
    </div>

</div>
