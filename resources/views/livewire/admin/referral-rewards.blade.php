<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="font-display text-3xl tracking-wide text-wc-text sm:text-4xl">Recompensas de Referidos</h1>
            <p class="mt-1 text-sm text-wc-text-secondary">Aprueba o deniega recompensas para referidores activos</p>
        </div>
    </div>

    {{-- Stat cards --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        {{-- Pendientes --}}
        <div class="rounded-xl border border-amber-500/30 bg-amber-500/5 p-4">
            <p class="text-xs font-semibold uppercase tracking-wider text-amber-500">Pendientes</p>
            <p class="mt-2 font-data text-3xl font-bold text-wc-text">{{ $stats['pending'] }}</p>
            <p class="mt-0.5 text-xs text-wc-text-secondary">sin aprobar</p>
        </div>

        {{-- Aprobadas --}}
        <div class="rounded-xl border border-emerald-500/30 bg-emerald-500/5 p-4">
            <p class="text-xs font-semibold uppercase tracking-wider text-emerald-500">Aprobadas</p>
            <p class="mt-2 font-data text-3xl font-bold text-wc-text">{{ $stats['approved'] }}</p>
            <p class="mt-0.5 text-xs text-wc-text-secondary">recompensas otorgadas</p>
        </div>

        {{-- Total --}}
        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-4">
            <p class="text-xs font-semibold uppercase tracking-wider text-wc-text-secondary">Total Referidos</p>
            <p class="mt-2 font-data text-3xl font-bold text-wc-text">{{ $stats['total'] }}</p>
            <p class="mt-0.5 text-xs text-wc-text-secondary">registros en total</p>
        </div>
    </div>

    {{-- Filter tabs --}}
    <div class="flex items-center gap-1.5 border-b border-wc-border pb-0">
        @foreach(['pending' => 'Pendientes', 'approved' => 'Aprobadas', 'all' => 'Todas'] as $key => $label)
            <button
                wire:click="$set('statusFilter', '{{ $key }}')"
                class="rounded-t-lg px-4 py-2 text-sm font-medium transition-colors
                    {{ $statusFilter === $key
                        ? 'border-b-2 border-wc-accent text-wc-accent bg-wc-accent/5'
                        : 'text-wc-text-secondary hover:text-wc-text hover:bg-wc-bg-secondary' }}">
                {{ $label }}
                @if($key === 'pending' && $stats['pending'] > 0)
                    <span class="ml-1.5 inline-flex items-center rounded-full bg-amber-500 px-1.5 py-0.5 text-[10px] font-bold text-white">
                        {{ $stats['pending'] }}
                    </span>
                @endif
            </button>
        @endforeach
    </div>

    {{-- Table --}}
    <div class="overflow-hidden rounded-xl border border-wc-border bg-wc-bg-tertiary">
        @if($referrals->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-wc-border bg-wc-bg-secondary">
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-wc-text-secondary">Referidor</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-wc-text-secondary">Referido</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-wc-text-secondary">Estado</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-wc-text-secondary">Recompensa</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-wc-text-secondary">Fecha</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-wc-text-secondary">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-wc-border">
                        @foreach($referrals as $referral)
                            <tr class="transition-colors hover:bg-wc-bg-secondary/40" wire:key="referral-{{ $referral->id }}">

                                {{-- Referidor --}}
                                <td class="px-4 py-3">
                                    @if($referral->referrer)
                                        <p class="font-medium text-wc-text">{{ $referral->referrer->name }}</p>
                                        <p class="text-xs text-wc-text-secondary">{{ $referral->referrer->email }}</p>
                                    @else
                                        <span class="text-xs text-wc-text-secondary italic">Desconocido</span>
                                    @endif
                                </td>

                                {{-- Referido --}}
                                <td class="px-4 py-3">
                                    @if($referral->referred)
                                        <p class="font-medium text-wc-text">{{ $referral->referred->name }}</p>
                                        <p class="text-xs text-wc-text-secondary">{{ $referral->referred_email }}</p>
                                    @else
                                        <p class="text-sm text-wc-text-secondary">{{ $referral->referred_email }}</p>
                                        <p class="text-xs text-wc-text-secondary italic">Sin cuenta</p>
                                    @endif
                                </td>

                                {{-- Estado / status badge --}}
                                <td class="px-4 py-3">
                                    @php
                                        $badgeClass = match($referral->status) {
                                            'pending'    => 'bg-amber-500/10 text-amber-500',
                                            'registered' => 'bg-sky-500/10 text-sky-500',
                                            'converted'  => 'bg-emerald-500/10 text-emerald-500',
                                            'denied'     => 'bg-red-500/10 text-red-500',
                                            default      => 'bg-wc-bg-secondary text-wc-text-secondary',
                                        };
                                        $badgeLabel = match($referral->status) {
                                            'pending'    => 'Pendiente',
                                            'registered' => 'Registrado',
                                            'converted'  => 'Convertido',
                                            'denied'     => 'Denegado',
                                            default      => ucfirst($referral->status),
                                        };
                                    @endphp
                                    <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $badgeClass }}">
                                        {{ $badgeLabel }}
                                    </span>
                                </td>

                                {{-- Recompensa badge --}}
                                <td class="px-4 py-3">
                                    @if($referral->reward_granted)
                                        <span class="inline-flex items-center gap-1 rounded-full bg-emerald-500/10 px-2.5 py-0.5 text-xs font-semibold text-emerald-500">
                                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                            </svg>
                                            Aprobada (+7 dias)
                                        </span>
                                    @elseif($referral->status === 'denied')
                                        <span class="inline-flex rounded-full bg-red-500/10 px-2.5 py-0.5 text-xs font-semibold text-red-500">
                                            Denegada
                                        </span>
                                    @else
                                        <span class="text-xs text-wc-text-secondary italic">Pendiente</span>
                                    @endif
                                </td>

                                {{-- Fecha --}}
                                <td class="px-4 py-3">
                                    <p class="text-sm text-wc-text-secondary">
                                        {{ $referral->created_at?->format('d/m/Y') ?? '-' }}
                                    </p>
                                    @if($referral->converted_at)
                                        <p class="text-xs text-wc-text-secondary">Aprobado: {{ $referral->converted_at->format('d/m/Y') }}</p>
                                    @endif
                                </td>

                                {{-- Acciones --}}
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-end gap-2">
                                        @if(!$referral->reward_granted && $referral->status !== 'denied')
                                            <button
                                                wire:click="approveReward({{ $referral->id }})"
                                                wire:confirm="Aprobar recompensa de +7 dias para {{ $referral->referrer?->name ?? 'este usuario' }}?"
                                                class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-500 px-3 py-1.5 text-xs font-semibold text-white transition-colors hover:bg-emerald-600 active:scale-95">
                                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                                </svg>
                                                Aprobar
                                            </button>
                                            <button
                                                wire:click="denyReward({{ $referral->id }})"
                                                wire:confirm="Denegar esta recompensa de referido?"
                                                class="inline-flex items-center gap-1.5 rounded-lg border border-red-500/50 px-3 py-1.5 text-xs font-semibold text-red-500 transition-colors hover:bg-red-500/10 active:scale-95">
                                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                                </svg>
                                                Denegar
                                            </button>
                                        @else
                                            <span class="text-xs text-wc-text-secondary">—</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($referrals->hasPages())
                <div class="border-t border-wc-border px-4 py-3">
                    {{ $referrals->links() }}
                </div>
            @endif

        @else
            {{-- Empty state --}}
            <div class="flex flex-col items-center py-16 text-center">
                <div class="flex h-14 w-14 items-center justify-center rounded-full bg-wc-bg-secondary">
                    <svg class="h-7 w-7 text-wc-text-secondary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                    </svg>
                </div>
                <p class="mt-4 text-sm font-medium text-wc-text">No hay referidos en esta vista</p>
                <p class="mt-1 text-xs text-wc-text-secondary">Cambia el filtro o espera nuevos referidos</p>
            </div>
        @endif
    </div>

</div>
