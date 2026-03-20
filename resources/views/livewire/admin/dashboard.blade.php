<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="font-display text-3xl tracking-wide text-wc-text sm:text-4xl">Panel de Administracion</h1>
            <p class="mt-1 text-sm text-wc-text-tertiary">Resumen general de WellCore Fitness</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.clients') }}"
               class="inline-flex items-center gap-2 rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700 transition-colors">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                </svg>
                Ver clientes
            </a>
            <a href="{{ route('admin.payments') }}"
               class="inline-flex items-center gap-2 rounded-lg border border-wc-border bg-wc-bg-tertiary px-4 py-2 text-sm font-medium text-wc-text hover:bg-wc-bg-secondary transition-colors">
                Ver pagos
            </a>
        </div>
    </div>

    {{-- Summary stats cards --}}
    <div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
        {{-- Active clients --}}
        <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-4 sm:p-5">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Clientes activos</span>
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-500/10">
                    <svg class="h-4 w-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                    </svg>
                </div>
            </div>
            <p class="mt-3 font-data text-3xl font-bold text-wc-text">{{ $activeClients }}</p>
            <p class="mt-0.5 text-xs text-wc-text-tertiary">en total</p>
        </div>

        {{-- Monthly revenue --}}
        <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-4 sm:p-5">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Ingresos del mes</span>
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-violet-500/10">
                    <svg class="h-4 w-4 text-violet-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </div>
            </div>
            <p class="mt-3 font-data text-3xl font-bold text-wc-text">${{ $monthlyRevenue }}</p>
            <p class="mt-0.5 text-xs text-wc-text-tertiary">COP este mes</p>
        </div>

        {{-- Pending check-ins --}}
        <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-4 sm:p-5">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Check-ins pendientes</span>
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-orange-500/10">
                    <svg class="h-4 w-4 text-orange-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </div>
            </div>
            <p class="mt-3 font-data text-3xl font-bold text-wc-text">{{ $pendingCheckins }}</p>
            <p class="mt-0.5 text-xs text-wc-text-tertiary">sin responder</p>
        </div>

        {{-- New inscriptions --}}
        <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-4 sm:p-5">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Inscripciones</span>
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-sky-500/10">
                    <svg class="h-4 w-4 text-sky-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                    </svg>
                </div>
            </div>
            <p class="mt-3 font-data text-3xl font-bold text-wc-text">{{ $newInscriptions }}</p>
            <p class="mt-0.5 text-xs text-wc-text-tertiary">nuevas este mes</p>
        </div>
    </div>

    {{-- Client breakdown + Quick links --}}
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">

        {{-- Client status breakdown --}}
        <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-5 lg:col-span-2">
            <h2 class="font-display text-lg tracking-wide text-wc-text">Estado de Clientes</h2>
            <p class="mt-1 text-xs text-wc-text-tertiary">Distribucion por estado — {{ $totalClients }} clientes en total</p>

            <div class="mt-5 space-y-3">
                {{-- Activos --}}
                <div class="flex items-center gap-3">
                    <span class="w-24 text-sm text-wc-text-secondary">Activos</span>
                    <div class="flex-1">
                        <div class="h-6 w-full overflow-hidden rounded-full bg-wc-bg-secondary">
                            <div class="h-full rounded-full bg-emerald-500 transition-all duration-500"
                                 style="width: {{ $totalClients > 0 ? round(($clientsActivo / $totalClients) * 100) : 0 }}%"></div>
                        </div>
                    </div>
                    <span class="font-data text-sm font-semibold text-wc-text tabular-nums w-10 text-right">{{ $clientsActivo }}</span>
                </div>

                {{-- Inactivos --}}
                <div class="flex items-center gap-3">
                    <span class="w-24 text-sm text-wc-text-secondary">Inactivos</span>
                    <div class="flex-1">
                        <div class="h-6 w-full overflow-hidden rounded-full bg-wc-bg-secondary">
                            <div class="h-full rounded-full bg-zinc-500 transition-all duration-500"
                                 style="width: {{ $totalClients > 0 ? round(($clientsInactivo / $totalClients) * 100) : 0 }}%"></div>
                        </div>
                    </div>
                    <span class="font-data text-sm font-semibold text-wc-text tabular-nums w-10 text-right">{{ $clientsInactivo }}</span>
                </div>

                {{-- Pendientes --}}
                <div class="flex items-center gap-3">
                    <span class="w-24 text-sm text-wc-text-secondary">Pendientes</span>
                    <div class="flex-1">
                        <div class="h-6 w-full overflow-hidden rounded-full bg-wc-bg-secondary">
                            <div class="h-full rounded-full bg-amber-500 transition-all duration-500"
                                 style="width: {{ $totalClients > 0 ? round(($clientsPendiente / $totalClients) * 100) : 0 }}%"></div>
                        </div>
                    </div>
                    <span class="font-data text-sm font-semibold text-wc-text tabular-nums w-10 text-right">{{ $clientsPendiente }}</span>
                </div>

                {{-- Suspendidos --}}
                <div class="flex items-center gap-3">
                    <span class="w-24 text-sm text-wc-text-secondary">Suspendidos</span>
                    <div class="flex-1">
                        <div class="h-6 w-full overflow-hidden rounded-full bg-wc-bg-secondary">
                            <div class="h-full rounded-full bg-red-500 transition-all duration-500"
                                 style="width: {{ $totalClients > 0 ? round(($clientsSuspendido / $totalClients) * 100) : 0 }}%"></div>
                        </div>
                    </div>
                    <span class="font-data text-sm font-semibold text-wc-text tabular-nums w-10 text-right">{{ $clientsSuspendido }}</span>
                </div>
            </div>

            {{-- Legend --}}
            <div class="mt-5 flex flex-wrap items-center gap-4 text-xs text-wc-text-tertiary">
                <div class="flex items-center gap-1.5">
                    <div class="h-2.5 w-2.5 rounded-full bg-emerald-500"></div>
                    Activos
                </div>
                <div class="flex items-center gap-1.5">
                    <div class="h-2.5 w-2.5 rounded-full bg-zinc-500"></div>
                    Inactivos
                </div>
                <div class="flex items-center gap-1.5">
                    <div class="h-2.5 w-2.5 rounded-full bg-amber-500"></div>
                    Pendientes
                </div>
                <div class="flex items-center gap-1.5">
                    <div class="h-2.5 w-2.5 rounded-full bg-red-500"></div>
                    Suspendidos
                </div>
            </div>
        </div>

        {{-- Quick links --}}
        <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-5">
            <h2 class="font-display text-lg tracking-wide text-wc-text">Accesos rapidos</h2>

            <div class="mt-4 space-y-2">
                <a href="{{ route('admin.clients') }}"
                   class="flex items-center gap-3 rounded-lg border border-wc-border bg-wc-bg-secondary px-4 py-3 text-sm font-medium text-wc-text hover:bg-wc-bg-tertiary transition-colors">
                    <svg class="h-5 w-5 text-emerald-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                    </svg>
                    Gestionar Clientes
                    <svg class="ml-auto h-4 w-4 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                    </svg>
                </a>

                <a href="{{ route('admin.payments') }}"
                   class="flex items-center gap-3 rounded-lg border border-wc-border bg-wc-bg-secondary px-4 py-3 text-sm font-medium text-wc-text hover:bg-wc-bg-tertiary transition-colors">
                    <svg class="h-5 w-5 text-violet-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" />
                    </svg>
                    Ver Pagos
                    <svg class="ml-auto h-4 w-4 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                    </svg>
                </a>

                <a href="{{ route('admin.inscriptions') }}"
                   class="flex items-center gap-3 rounded-lg border border-wc-border bg-wc-bg-secondary px-4 py-3 text-sm font-medium text-wc-text hover:bg-wc-bg-tertiary transition-colors">
                    <svg class="h-5 w-5 text-sky-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                    </svg>
                    Ver Inscripciones
                    <svg class="ml-auto h-4 w-4 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                    </svg>
                </a>

                <a href="{{ route('admin.coaches') }}"
                   class="flex items-center gap-3 rounded-lg border border-wc-border bg-wc-bg-secondary px-4 py-3 text-sm font-medium text-wc-text hover:bg-wc-bg-tertiary transition-colors">
                    <svg class="h-5 w-5 text-orange-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5" />
                    </svg>
                    Gestionar Coaches
                    <svg class="ml-auto h-4 w-4 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                    </svg>
                </a>

                <a href="{{ route('admin.tickets') }}"
                   class="flex items-center gap-3 rounded-lg border border-wc-border bg-wc-bg-secondary px-4 py-3 text-sm font-medium text-wc-text hover:bg-wc-bg-tertiary transition-colors">
                    <svg class="h-5 w-5 text-red-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 0 1 0 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 0 1 0-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375Z" />
                    </svg>
                    Tickets de Soporte
                    <svg class="ml-auto h-4 w-4 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                    </svg>
                </a>
            </div>
        </div>
    </div>

    {{-- Recent inscriptions + Recent payments --}}
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">

        {{-- Recent inscriptions --}}
        <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-5">
            <div class="flex items-center justify-between">
                <h2 class="font-display text-lg tracking-wide text-wc-text">Inscripciones Recientes</h2>
                <a href="{{ route('admin.inscriptions') }}" class="text-xs font-medium text-red-500 hover:text-red-400 transition-colors">Ver todas</a>
            </div>

            @if(count($recentInscriptions) > 0)
                <div class="mt-4 space-y-3">
                    @foreach($recentInscriptions as $inscription)
                        <div class="flex items-center gap-3 rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2.5">
                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-sky-500/10">
                                <span class="text-xs font-semibold text-sky-500">{{ substr($inscription['nombre'], 0, 1) }}</span>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-medium text-wc-text">{{ $inscription['nombre'] }}</p>
                                <p class="truncate text-xs text-wc-text-tertiary">{{ $inscription['email'] }}</p>
                            </div>
                            <div class="text-right shrink-0">
                                <span class="inline-flex rounded-full bg-sky-500/10 px-2 py-0.5 text-[10px] font-semibold text-sky-500">{{ $inscription['plan'] }}</span>
                                <p class="mt-0.5 text-[10px] text-wc-text-tertiary">{{ $inscription['timeAgo'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="mt-6 flex flex-col items-center py-4 text-center">
                    <svg class="h-8 w-8 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                    </svg>
                    <p class="mt-2 text-sm text-wc-text-tertiary">Sin inscripciones recientes</p>
                </div>
            @endif
        </div>

        {{-- Recent payments --}}
        <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-5">
            <div class="flex items-center justify-between">
                <h2 class="font-display text-lg tracking-wide text-wc-text">Pagos Recientes</h2>
                <a href="{{ route('admin.payments') }}" class="text-xs font-medium text-red-500 hover:text-red-400 transition-colors">Ver todos</a>
            </div>

            @if(count($recentPayments) > 0)
                <div class="mt-4 space-y-3">
                    @foreach($recentPayments as $payment)
                        <div class="flex items-center gap-3 rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2.5">
                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-emerald-500/10">
                                <svg class="h-4 w-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                </svg>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-medium text-wc-text">{{ $payment['buyerName'] }}</p>
                                <p class="text-xs text-wc-text-tertiary">{{ $payment['plan'] }} &middot; {{ $payment['method'] }}</p>
                            </div>
                            <div class="text-right shrink-0">
                                <span class="font-data text-sm font-semibold text-emerald-500">${{ $payment['amount'] }}</span>
                                <p class="mt-0.5 text-[10px] text-wc-text-tertiary">{{ $payment['timeAgo'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="mt-6 flex flex-col items-center py-4 text-center">
                    <svg class="h-8 w-8 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" />
                    </svg>
                    <p class="mt-2 text-sm text-wc-text-tertiary">Sin pagos recientes</p>
                </div>
            @endif
        </div>
    </div>

</div>
