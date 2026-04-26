<div
    class="space-y-6"
    x-data
    x-on:file-url-ready.window="window.open($event.detail.url, '_blank', 'noopener,noreferrer')">

    {{-- Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="font-display text-3xl tracking-wide text-wc-text sm:text-4xl">Comprobantes de Pago</h1>
            <p class="mt-1 text-sm text-wc-text-tertiary">Revisa los comprobantes enviados por los coaches</p>
        </div>
        <a href="{{ route('admin.dashboard') }}"
           class="inline-flex items-center gap-2 rounded-lg border border-wc-border bg-wc-bg-tertiary px-4 py-2 text-sm font-medium text-wc-text hover:bg-wc-bg-secondary transition-colors">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
            </svg>
            Volver al panel
        </a>
    </div>

    {{-- Filters --}}
    <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-4">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:gap-4">

            <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:gap-2">
                <label for="status-filter" class="shrink-0 text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">
                    Estado
                </label>
                <select id="status-filter"
                        wire:model.live="status"
                        class="rounded-lg border border-wc-border bg-wc-bg px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent/50 transition-colors sm:w-40">
                    <option value="">Todos</option>
                    @foreach($statusOptions as $option)
                        <option value="{{ $option->value }}">{{ $option->label() }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:gap-2">
                <label for="coach-filter" class="shrink-0 text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">
                    Coach
                </label>
                <select id="coach-filter"
                        wire:model.live="coachId"
                        class="rounded-lg border border-wc-border bg-wc-bg px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent/50 transition-colors sm:w-48">
                    <option value="">Todos los coaches</option>
                    @foreach($coaches as $coach)
                        <option value="{{ $coach->id }}">{{ $coach->name }}</option>
                    @endforeach
                </select>
            </div>

            @if($status || $coachId)
                <button wire:click="$set('status', ''); $set('coachId', '')"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-wc-accent/10 px-3 py-2 text-xs font-medium text-wc-accent hover:bg-wc-accent/20 transition-colors sm:ml-auto">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                    Limpiar filtros
                </button>
            @endif

        </div>
    </div>

    {{-- Table card --}}
    <div class="rounded-card border border-wc-border bg-wc-bg-tertiary">

        @if($proofs->isEmpty())
            <div class="flex flex-col items-center justify-center px-6 py-16 text-center">
                <div class="flex h-16 w-16 items-center justify-center rounded-full bg-wc-bg-secondary">
                    <svg class="h-8 w-8 text-wc-text-tertiary/50" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                    </svg>
                </div>
                <p class="mt-4 text-sm font-medium text-wc-text">No hay comprobantes</p>
                <p class="mt-1 text-xs text-wc-text-tertiary">
                    @if($status || $coachId)
                        No hay comprobantes que coincidan con los filtros aplicados.
                    @else
                        Los coaches aun no han subido comprobantes de pago.
                    @endif
                </p>
            </div>

        @else
            {{-- Desktop table --}}
            <div class="hidden overflow-x-auto md:block">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-wc-border">
                            <th class="px-5 py-3.5 text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Coach</th>
                            <th class="px-5 py-3.5 text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Cliente</th>
                            <th class="px-5 py-3.5 text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Plan</th>
                            <th class="px-5 py-3.5 text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Monto</th>
                            <th class="px-5 py-3.5 text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Metodo</th>
                            <th class="px-5 py-3.5 text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Estado</th>
                            <th class="px-5 py-3.5 text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Enviado</th>
                            <th class="px-5 py-3.5 text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Archivo</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-wc-border">
                        @foreach($proofs as $proof)
                            <tr class="group transition-colors hover:bg-wc-bg-secondary/40" wire:key="proof-row-{{ $proof->id }}">

                                <td class="px-5 py-4">
                                    <span class="font-medium text-wc-text">{{ $proof->coach?->name ?? '—' }}</span>
                                </td>

                                <td class="px-5 py-4">
                                    <p class="font-medium text-wc-text">{{ $proof->client_name ?: '—' }}</p>
                                    <p class="text-xs text-wc-text-tertiary">{{ $proof->client_email ?: '' }}</p>
                                </td>

                                <td class="px-5 py-4">
                                    <span class="text-wc-text-secondary">{{ $proof->plan?->label() ?? '—' }}</span>
                                </td>

                                <td class="px-5 py-4">
                                    <span class="font-data font-semibold text-wc-text tabular-nums">
                                        @if($proof->amount)
                                            ${{ number_format((float) $proof->amount, 0, ',', '.') }}
                                            <span class="text-xs font-normal text-wc-text-tertiary">{{ $proof->currency }}</span>
                                        @else
                                            —
                                        @endif
                                    </span>
                                </td>

                                <td class="px-5 py-4">
                                    <span class="text-wc-text-secondary">{{ $proof->payment_method?->label() ?? '—' }}</span>
                                </td>

                                <td class="px-5 py-4">
                                    @if($proof->status)
                                        <span class="inline-flex items-center rounded-full px-2.5 py-1 text-[11px] font-semibold {{ $proof->status->badgeClass() }}">
                                            {{ $proof->status->label() }}
                                        </span>
                                    @else
                                        <span class="text-xs text-wc-text-tertiary">—</span>
                                    @endif
                                </td>

                                <td class="px-5 py-4 text-xs text-wc-text-secondary tabular-nums whitespace-nowrap">
                                    {{ $proof->submitted_at?->format('d/m/Y') ?? '—' }}
                                    <p class="text-wc-text-tertiary">{{ $proof->submitted_at?->format('H:i') ?? '' }}</p>
                                </td>

                                <td class="px-5 py-4">
                                    @if($proof->file_path)
                                        <button wire:click="getFileUrl({{ $proof->id }})"
                                                wire:loading.attr="disabled"
                                                class="btn-press inline-flex items-center gap-1.5 rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-1.5 text-xs font-medium text-wc-text hover:bg-wc-bg hover:border-wc-accent/40 transition-colors focus:outline-none focus:ring-2 focus:ring-wc-accent/40 disabled:opacity-50">
                                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                            </svg>
                                            Ver
                                        </button>
                                    @else
                                        <span class="text-xs text-wc-text-tertiary/50">Sin archivo</span>
                                    @endif
                                </td>

                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Mobile cards --}}
            <div class="divide-y divide-wc-border md:hidden">
                @foreach($proofs as $proof)
                    <div class="space-y-3 p-4" wire:key="proof-card-{{ $proof->id }}">

                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0 flex-1">
                                <p class="truncate font-semibold text-wc-text">{{ $proof->client_name ?: '—' }}</p>
                                <p class="truncate text-xs text-wc-text-tertiary">{{ $proof->client_email ?: '' }}</p>
                            </div>
                            @if($proof->status)
                                <span class="inline-flex shrink-0 items-center rounded-full px-2.5 py-1 text-[11px] font-semibold {{ $proof->status->badgeClass() }}">
                                    {{ $proof->status->label() }}
                                </span>
                            @endif
                        </div>

                        <div class="grid grid-cols-2 gap-2 text-xs">
                            <div>
                                <span class="text-wc-text-tertiary">Coach</span>
                                <p class="mt-0.5 font-medium text-wc-text">{{ $proof->coach?->name ?? '—' }}</p>
                            </div>
                            <div>
                                <span class="text-wc-text-tertiary">Plan</span>
                                <p class="mt-0.5 font-medium text-wc-text">{{ $proof->plan?->label() ?? '—' }}</p>
                            </div>
                            <div>
                                <span class="text-wc-text-tertiary">Monto</span>
                                <p class="mt-0.5 font-data font-semibold text-wc-text">
                                    @if($proof->amount)
                                        ${{ number_format((float) $proof->amount, 0, ',', '.') }} {{ $proof->currency }}
                                    @else
                                        —
                                    @endif
                                </p>
                            </div>
                            <div>
                                <span class="text-wc-text-tertiary">Metodo</span>
                                <p class="mt-0.5 font-medium text-wc-text">{{ $proof->payment_method?->label() ?? '—' }}</p>
                            </div>
                            <div class="col-span-2">
                                <span class="text-wc-text-tertiary">Enviado</span>
                                <p class="mt-0.5 font-medium text-wc-text tabular-nums">{{ $proof->submitted_at?->format('d/m/Y H:i') ?? '—' }}</p>
                            </div>
                        </div>

                        @if($proof->file_path)
                            <button wire:click="getFileUrl({{ $proof->id }})"
                                    wire:loading.attr="disabled"
                                    class="btn-press inline-flex min-h-[44px] w-full items-center justify-center gap-1.5 rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2.5 text-xs font-medium text-wc-text hover:bg-wc-bg transition-colors focus:outline-none focus:ring-2 focus:ring-wc-accent/40 disabled:opacity-50">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                </svg>
                                Ver archivo
                            </button>
                        @endif

                    </div>
                @endforeach
            </div>

            {{-- Pagination --}}
            @if($proofs->hasPages())
                <div class="border-t border-wc-border px-5 py-4">
                    {{ $proofs->links() }}
                </div>
            @endif

        @endif
    </div>

</div>
