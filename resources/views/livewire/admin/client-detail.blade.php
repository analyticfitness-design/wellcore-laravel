<div class="space-y-6" x-data="{ showCoachModal: @entangle('showCoachModal') }">

    {{-- Success message --}}
    @if($successMessage)
        <div class="flex items-center justify-between rounded-lg border border-emerald-500/30 bg-emerald-500/10 px-4 py-3">
            <div class="flex items-center gap-2">
                <svg class="h-4 w-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                <span class="text-sm font-medium text-emerald-500">{{ $successMessage }}</span>
            </div>
            <button wire:click="dismissMessage" class="text-emerald-500 hover:text-emerald-400">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    @endif

    {{-- Back button + Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div class="flex items-start gap-4">
            <a href="{{ route('admin.clients') }}"
               class="mt-1 flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-wc-border bg-wc-bg-tertiary text-wc-text-secondary hover:text-wc-text transition-colors">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                </svg>
            </a>
            <div class="flex items-center gap-4">
                {{-- Avatar --}}
                <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-red-500/10">
                    @if($client->avatar_url)
                        <img src="{{ $client->avatar_url }}" alt="{{ $client->name }}" class="h-14 w-14 rounded-full object-cover">
                    @else
                        <span class="font-display text-2xl text-red-500">{{ strtoupper(substr($client->name ?? 'C', 0, 1)) }}</span>
                    @endif
                </div>
                <div>
                    <h1 class="font-display text-3xl tracking-wide text-wc-text sm:text-4xl">{{ $client->name }}</h1>
                    <div class="mt-1 flex flex-wrap items-center gap-2">
                        <span class="font-data text-sm text-wc-text-tertiary">{{ $client->client_code ?? 'Sin codigo' }}</span>
                        <span class="text-wc-text-tertiary">|</span>
                        <span class="text-sm text-wc-text-tertiary">{{ $client->email }}</span>
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
                        @endif
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
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Action buttons --}}
        <div class="flex items-center gap-2">
            <button wire:click="openCoachModal"
                    class="inline-flex items-center gap-2 rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700 transition-colors">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z" />
                </svg>
                Asignar Coach
            </button>
        </div>
    </div>

    {{-- Current Coach card --}}
    @if($currentCoach)
        <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-4">
            <div class="flex items-center gap-3">
                <div class="flex h-9 w-9 items-center justify-center rounded-full bg-violet-500/10">
                    <svg class="h-4 w-4 text-violet-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5" />
                    </svg>
                </div>
                <div>
                    <p class="text-xs text-wc-text-tertiary">Coach asignado</p>
                    <p class="text-sm font-medium text-wc-text">{{ $currentCoach->name }}</p>
                </div>
            </div>
        </div>
    @endif

    {{-- Tab Navigation --}}
    <div class="flex gap-1 overflow-x-auto rounded-card border border-wc-border bg-wc-bg-tertiary p-1">
        @foreach([
            'info' => 'Informacion',
            'plans' => 'Planes',
            'checkins' => 'Check-ins',
            'payments' => 'Pagos',
            'metrics' => 'Metricas',
        ] as $tabKey => $tabLabel)
            <button wire:click="switchTab('{{ $tabKey }}')"
                    class="flex-1 rounded-lg px-4 py-2 text-sm font-medium transition-colors whitespace-nowrap
                           {{ $tab === $tabKey ? 'bg-red-500/10 text-wc-text border-l-2 border-red-500' : 'text-wc-text-secondary hover:text-wc-text hover:bg-wc-bg-secondary' }}">
                {{ $tabLabel }}
            </button>
        @endforeach
    </div>

    {{-- Tab Content --}}

    {{-- INFO TAB --}}
    @if($tab === 'info')
        <div class="grid gap-6 lg:grid-cols-2">
            {{-- Client Info Card --}}
            <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-5 space-y-4">
                <h2 class="font-display text-xl tracking-wide text-wc-text">Datos del Cliente</h2>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Nombre</label>
                        <p class="mt-1 text-sm text-wc-text">{{ $client->name }}</p>
                    </div>
                    <div>
                        <label class="text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Email</label>
                        <p class="mt-1 text-sm text-wc-text">{{ $client->email }}</p>
                    </div>
                    <div>
                        <label class="text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Codigo</label>
                        <p class="mt-1 font-data text-sm text-wc-text">{{ $client->client_code ?? '-' }}</p>
                    </div>
                    <div>
                        <label class="text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Ciudad</label>
                        <p class="mt-1 text-sm text-wc-text">{{ $client->city ?? '-' }}</p>
                    </div>
                    <div>
                        <label class="text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Fecha Nacimiento</label>
                        <p class="mt-1 font-data text-sm text-wc-text">{{ $client->birth_date?->format('d/m/Y') ?? '-' }}</p>
                    </div>
                    <div>
                        <label class="text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Fecha Inicio</label>
                        <p class="mt-1 font-data text-sm text-wc-text">{{ $client->fecha_inicio?->format('d/m/Y') ?? '-' }}</p>
                    </div>
                    <div>
                        <label class="text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Referral Code</label>
                        <p class="mt-1 font-data text-sm text-wc-text">{{ $client->referral_code ?? '-' }}</p>
                    </div>
                    <div>
                        <label class="text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Referido por</label>
                        <p class="mt-1 font-data text-sm text-wc-text">{{ $client->referred_by ?? '-' }}</p>
                    </div>
                </div>

                @if($client->bio)
                    <div>
                        <label class="text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Bio</label>
                        <p class="mt-1 text-sm text-wc-text-secondary">{{ $client->bio }}</p>
                    </div>
                @endif
            </div>

            {{-- Quick Actions Card --}}
            <div class="space-y-6">
                {{-- Change Status --}}
                <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-5 space-y-4">
                    <h2 class="font-display text-xl tracking-wide text-wc-text">Cambiar Estado</h2>
                    <div class="flex items-end gap-3">
                        <div class="flex-1">
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Estado</label>
                            <select wire:model="editStatus"
                                    class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary py-2 pl-3 pr-8 text-sm text-wc-text focus:border-red-500 focus:outline-none focus:ring-1 focus:ring-red-500">
                                @foreach(\App\Enums\ClientStatus::cases() as $status)
                                    <option value="{{ $status->value }}">{{ $status->label() }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button wire:click="updateStatus"
                                class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700 transition-colors">
                            Guardar
                        </button>
                    </div>
                </div>

                {{-- Change Plan --}}
                <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-5 space-y-4">
                    <h2 class="font-display text-xl tracking-wide text-wc-text">Cambiar Plan</h2>
                    <div class="flex items-end gap-3">
                        <div class="flex-1">
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Plan</label>
                            <select wire:model="editPlan"
                                    class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary py-2 pl-3 pr-8 text-sm text-wc-text focus:border-red-500 focus:outline-none focus:ring-1 focus:ring-red-500">
                                @foreach(\App\Enums\PlanType::cases() as $plan)
                                    <option value="{{ $plan->value }}">{{ $plan->label() }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button wire:click="updatePlan"
                                class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700 transition-colors">
                            Guardar
                        </button>
                    </div>
                </div>

                {{-- Summary Stats --}}
                <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-5">
                    <h2 class="font-display text-xl tracking-wide text-wc-text mb-4">Resumen</h2>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="rounded-lg bg-wc-bg-secondary p-3 text-center">
                            <p class="font-data text-2xl font-bold text-wc-text">{{ $client->checkins->count() }}</p>
                            <p class="text-xs text-wc-text-tertiary">Check-ins</p>
                        </div>
                        <div class="rounded-lg bg-wc-bg-secondary p-3 text-center">
                            <p class="font-data text-2xl font-bold text-wc-text">{{ $client->payments->where('status.value', 'approved')->count() }}</p>
                            <p class="text-xs text-wc-text-tertiary">Pagos aprobados</p>
                        </div>
                        <div class="rounded-lg bg-wc-bg-secondary p-3 text-center">
                            <p class="font-data text-2xl font-bold text-wc-text">{{ $client->assignedPlans->where('active', true)->count() }}</p>
                            <p class="text-xs text-wc-text-tertiary">Planes activos</p>
                        </div>
                        <div class="rounded-lg bg-wc-bg-secondary p-3 text-center">
                            <p class="font-data text-2xl font-bold text-wc-text">{{ $client->progressPhotos->count() }}</p>
                            <p class="text-xs text-wc-text-tertiary">Fotos progreso</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- PLANS TAB --}}
    @if($tab === 'plans')
        <div class="rounded-card border border-wc-border bg-wc-bg-tertiary overflow-hidden">
            <div class="border-b border-wc-border bg-wc-bg-secondary px-5 py-3 flex items-center justify-between">
                <h2 class="font-display text-xl tracking-wide text-wc-text">Planes Asignados</h2>
                <span class="font-data text-sm text-wc-text-tertiary">{{ $client->assignedPlans->count() }} total</span>
            </div>

            @if($client->assignedPlans->isEmpty())
                <div class="px-5 py-12 text-center">
                    <svg class="mx-auto h-8 w-8 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15a2.25 2.25 0 0 1 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z" />
                    </svg>
                    <p class="mt-2 text-sm text-wc-text-tertiary">No hay planes asignados</p>
                    <button wire:click="openCoachModal" class="mt-3 text-sm font-medium text-red-500 hover:text-red-400 transition-colors">Asignar coach</button>
                </div>
            @else
                <div class="divide-y divide-wc-border">
                    @foreach($client->assignedPlans as $plan)
                        <div class="px-5 py-4 flex items-start justify-between gap-4">
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-medium text-wc-text capitalize">{{ $plan->plan_type }}</span>
                                    @if($plan->active)
                                        <span class="inline-flex rounded-full bg-emerald-500/10 px-2 py-0.5 text-[10px] font-semibold text-emerald-500">Activo</span>
                                    @else
                                        <span class="inline-flex rounded-full bg-zinc-500/10 px-2 py-0.5 text-[10px] font-semibold text-zinc-400">Inactivo</span>
                                    @endif
                                    <span class="font-data text-xs text-wc-text-tertiary">v{{ $plan->version }}</span>
                                </div>
                                <div class="mt-1 flex items-center gap-3 text-xs text-wc-text-tertiary">
                                    <span>Coach: {{ $plan->assignedBy?->name ?? 'N/A' }}</span>
                                    <span>Desde: {{ $plan->valid_from?->format('d/m/Y') ?? '-' }}</span>
                                    @if($plan->created_at)
                                        <span>Creado: {{ $plan->created_at->format('d/m/Y H:i') }}</span>
                                    @endif
                                </div>
                                @if($plan->content)
                                    @php
                                        $contentPreview = is_array($plan->content) ? json_encode($plan->content) : $plan->content;
                                        $contentPreview = \Illuminate\Support\Str::limit($contentPreview, 120);
                                    @endphp
                                    <p class="mt-1 text-xs text-wc-text-tertiary font-mono truncate">{{ $contentPreview }}</p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    @endif

    {{-- CHECKINS TAB --}}
    @if($tab === 'checkins')
        <div class="rounded-card border border-wc-border bg-wc-bg-tertiary overflow-hidden">
            <div class="border-b border-wc-border bg-wc-bg-secondary px-5 py-3 flex items-center justify-between">
                <h2 class="font-display text-xl tracking-wide text-wc-text">Check-ins</h2>
                <span class="font-data text-sm text-wc-text-tertiary">{{ $client->checkins->count() }} total</span>
            </div>

            @if($client->checkins->isEmpty())
                <div class="px-5 py-12 text-center">
                    <svg class="mx-auto h-8 w-8 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                    <p class="mt-2 text-sm text-wc-text-tertiary">No hay check-ins registrados</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-wc-border bg-wc-bg-secondary">
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Fecha</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Semana</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Bienestar</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Dias Entren.</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Nutricion</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">RPE</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Comentario</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Respuesta</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-wc-border">
                            @foreach($client->checkins as $checkin)
                                <tr class="hover:bg-wc-bg-secondary/50 transition-colors">
                                    <td class="px-4 py-3 font-data text-wc-text-secondary">{{ $checkin->checkin_date?->format('d/m/Y') ?? '-' }}</td>
                                    <td class="px-4 py-3 text-wc-text-secondary">{{ $checkin->week_label ?? '-' }}</td>
                                    <td class="px-4 py-3 text-center font-data text-wc-text">{{ $checkin->bienestar ?? '-' }}</td>
                                    <td class="px-4 py-3 text-center font-data text-wc-text">{{ $checkin->dias_entrenados ?? '-' }}</td>
                                    <td class="px-4 py-3 text-center font-data text-wc-text">{{ $checkin->nutricion ?? '-' }}</td>
                                    <td class="px-4 py-3 text-center font-data text-wc-text">{{ $checkin->rpe ?? '-' }}</td>
                                    <td class="px-4 py-3 text-wc-text-secondary max-w-[200px] truncate">{{ $checkin->comentario ?? '-' }}</td>
                                    <td class="px-4 py-3 text-center">
                                        @if($checkin->coach_reply)
                                            <span class="inline-flex rounded-full bg-emerald-500/10 px-2 py-0.5 text-[10px] font-semibold text-emerald-500">Respondido</span>
                                        @else
                                            <span class="inline-flex rounded-full bg-amber-500/10 px-2 py-0.5 text-[10px] font-semibold text-amber-500">Pendiente</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    @endif

    {{-- PAYMENTS TAB --}}
    @if($tab === 'payments')
        <div class="rounded-card border border-wc-border bg-wc-bg-tertiary overflow-hidden">
            <div class="border-b border-wc-border bg-wc-bg-secondary px-5 py-3 flex items-center justify-between">
                <h2 class="font-display text-xl tracking-wide text-wc-text">Pagos</h2>
                <span class="font-data text-sm text-wc-text-tertiary">{{ $client->payments->count() }} total</span>
            </div>

            @if($client->payments->isEmpty())
                <div class="px-5 py-12 text-center">
                    <svg class="mx-auto h-8 w-8 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" />
                    </svg>
                    <p class="mt-2 text-sm text-wc-text-tertiary">No hay pagos registrados</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-wc-border bg-wc-bg-secondary">
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Fecha</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Plan</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Monto</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Metodo</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Estado</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Referencia</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-wc-border">
                            @foreach($client->payments as $payment)
                                <tr class="hover:bg-wc-bg-secondary/50 transition-colors">
                                    <td class="px-4 py-3 font-data text-wc-text-secondary">{{ $payment->created_at?->format('d/m/Y H:i') ?? '-' }}</td>
                                    <td class="px-4 py-3">
                                        @if($payment->plan)
                                            <span class="text-sm text-wc-text capitalize">{{ $payment->plan->label() }}</span>
                                        @else
                                            <span class="text-sm text-wc-text-tertiary">-</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-right font-data font-semibold text-wc-text">
                                        ${{ number_format((float)$payment->amount, 0, ',', '.') }}
                                        <span class="text-xs text-wc-text-tertiary">{{ $payment->currency ?? 'COP' }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-center text-wc-text-secondary">{{ $payment->payment_method ?? '-' }}</td>
                                    <td class="px-4 py-3 text-center">
                                        @if($payment->status)
                                            @php
                                                $payStatusColor = match($payment->status->value) {
                                                    'approved' => 'bg-emerald-500/10 text-emerald-500',
                                                    'pending' => 'bg-amber-500/10 text-amber-500',
                                                    'declined', 'rejected', 'cancelled' => 'bg-red-500/10 text-red-500',
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
                                    <td class="px-4 py-3 font-data text-xs text-wc-text-tertiary">
                                        {{ $payment->wompi_reference ?? $payment->payu_reference ?? '-' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    @endif

    {{-- METRICS TAB --}}
    @if($tab === 'metrics')
        <div class="grid gap-6 lg:grid-cols-2">
            {{-- Biometric Logs --}}
            <div class="rounded-card border border-wc-border bg-wc-bg-tertiary overflow-hidden">
                <div class="border-b border-wc-border bg-wc-bg-secondary px-5 py-3">
                    <h2 class="font-display text-xl tracking-wide text-wc-text">Biometricos</h2>
                </div>

                @if($client->biometricLogs->isEmpty())
                    <div class="px-5 py-12 text-center">
                        <svg class="mx-auto h-8 w-8 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                        </svg>
                        <p class="mt-2 text-sm text-wc-text-tertiary">No hay registros biometricos</p>
                    </div>
                @else
                    <div class="divide-y divide-wc-border max-h-96 overflow-y-auto">
                        @foreach($client->biometricLogs as $log)
                            <div class="px-5 py-3">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="font-data text-xs text-wc-text-tertiary">{{ $log->log_date?->format('d/m/Y') ?? '-' }}</span>
                                    @if($log->source)
                                        <span class="inline-flex rounded-full bg-wc-bg-secondary px-2 py-0.5 text-[10px] font-medium text-wc-text-tertiary">{{ $log->source }}</span>
                                    @endif
                                </div>
                                <div class="grid grid-cols-3 gap-2 text-xs">
                                    @if($log->weight_kg)
                                        <div>
                                            <span class="text-wc-text-tertiary">Peso</span>
                                            <p class="font-data font-semibold text-wc-text">{{ $log->weight_kg }} kg</p>
                                        </div>
                                    @endif
                                    @if($log->body_fat_pct)
                                        <div>
                                            <span class="text-wc-text-tertiary">Grasa</span>
                                            <p class="font-data font-semibold text-wc-text">{{ $log->body_fat_pct }}%</p>
                                        </div>
                                    @endif
                                    @if($log->steps)
                                        <div>
                                            <span class="text-wc-text-tertiary">Pasos</span>
                                            <p class="font-data font-semibold text-wc-text">{{ number_format($log->steps) }}</p>
                                        </div>
                                    @endif
                                    @if($log->sleep_hours)
                                        <div>
                                            <span class="text-wc-text-tertiary">Sueno</span>
                                            <p class="font-data font-semibold text-wc-text">{{ $log->sleep_hours }}h</p>
                                        </div>
                                    @endif
                                    @if($log->heart_rate)
                                        <div>
                                            <span class="text-wc-text-tertiary">FC</span>
                                            <p class="font-data font-semibold text-wc-text">{{ $log->heart_rate }} bpm</p>
                                        </div>
                                    @endif
                                    @if($log->calories)
                                        <div>
                                            <span class="text-wc-text-tertiary">Calorias</span>
                                            <p class="font-data font-semibold text-wc-text">{{ number_format($log->calories) }}</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Weight Logs --}}
            <div class="rounded-card border border-wc-border bg-wc-bg-tertiary overflow-hidden">
                <div class="border-b border-wc-border bg-wc-bg-secondary px-5 py-3">
                    <h2 class="font-display text-xl tracking-wide text-wc-text">Registro de Pesos</h2>
                </div>

                @if($client->weightLogs->isEmpty())
                    <div class="px-5 py-12 text-center">
                        <svg class="mx-auto h-8 w-8 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v17.25m0 0c-1.472 0-2.882.265-4.185.75M12 20.25c1.472 0 2.882.265 4.185.75M18.75 4.97A48.416 48.416 0 0 0 12 4.5c-2.291 0-4.545.16-6.75.47m13.5 0c1.01.143 2.01.317 3 .52m-3-.52 2.62 10.726c.122.499-.106 1.028-.589 1.202a5.988 5.988 0 0 1-2.031.352 5.988 5.988 0 0 1-2.031-.352c-.483-.174-.711-.703-.59-1.202L18.75 4.971Zm-16.5.52c.99-.203 1.99-.377 3-.52m0 0 2.62 10.726c.122.499-.106 1.028-.589 1.202a5.989 5.989 0 0 1-2.031.352 5.989 5.989 0 0 1-2.031-.352c-.483-.174-.711-.703-.59-1.202L5.25 4.971Z" />
                        </svg>
                        <p class="mt-2 text-sm text-wc-text-tertiary">No hay registros de pesos</p>
                    </div>
                @else
                    <div class="overflow-x-auto max-h-96 overflow-y-auto">
                        <table class="w-full text-sm">
                            <thead class="sticky top-0">
                                <tr class="border-b border-wc-border bg-wc-bg-secondary">
                                    <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Fecha</th>
                                    <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Ejercicio</th>
                                    <th class="px-4 py-2 text-right text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Peso (kg)</th>
                                    <th class="px-4 py-2 text-center text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Sets x Reps</th>
                                    <th class="px-4 py-2 text-center text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">RPE</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-wc-border">
                                @foreach($client->weightLogs as $wl)
                                    <tr class="hover:bg-wc-bg-secondary/50 transition-colors">
                                        <td class="px-4 py-2 font-data text-xs text-wc-text-tertiary">{{ $wl->date?->format('d/m/Y') ?? '-' }}</td>
                                        <td class="px-4 py-2 text-wc-text">{{ $wl->exercise ?? '-' }}</td>
                                        <td class="px-4 py-2 text-right font-data font-semibold text-wc-text">{{ $wl->weight_kg ?? '-' }}</td>
                                        <td class="px-4 py-2 text-center font-data text-wc-text-secondary">{{ $wl->sets ?? '-' }}x{{ $wl->reps ?? '-' }}</td>
                                        <td class="px-4 py-2 text-center font-data text-wc-text-secondary">{{ $wl->rpe ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            {{-- Progress Photos --}}
            <div class="rounded-card border border-wc-border bg-wc-bg-tertiary overflow-hidden lg:col-span-2">
                <div class="border-b border-wc-border bg-wc-bg-secondary px-5 py-3">
                    <h2 class="font-display text-xl tracking-wide text-wc-text">Fotos de Progreso</h2>
                </div>

                @if($client->progressPhotos->isEmpty())
                    <div class="px-5 py-12 text-center">
                        <svg class="mx-auto h-8 w-8 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M18 3.75H6A2.25 2.25 0 0 0 3.75 6v12A2.25 2.25 0 0 0 6 20.25h12A2.25 2.25 0 0 0 20.25 18V6A2.25 2.25 0 0 0 18 3.75Z" />
                        </svg>
                        <p class="mt-2 text-sm text-wc-text-tertiary">No hay fotos de progreso</p>
                    </div>
                @else
                    <div class="grid grid-cols-2 gap-4 p-5 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6">
                        @foreach($client->progressPhotos as $photo)
                            <div class="rounded-lg border border-wc-border bg-wc-bg-secondary overflow-hidden">
                                <div class="aspect-square bg-wc-bg flex items-center justify-center">
                                    @if($photo->filename)
                                        <img src="{{ asset('storage/' . $photo->filename) }}" alt="Progreso" class="h-full w-full object-cover" loading="lazy">
                                    @else
                                        <svg class="h-6 w-6 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M18 3.75H6A2.25 2.25 0 0 0 3.75 6v12A2.25 2.25 0 0 0 6 20.25h12A2.25 2.25 0 0 0 20.25 18V6A2.25 2.25 0 0 0 18 3.75Z" />
                                        </svg>
                                    @endif
                                </div>
                                <div class="px-2 py-1.5 text-center">
                                    <p class="font-data text-[10px] text-wc-text-tertiary">{{ $photo->photo_date?->format('d/m/Y') ?? '-' }}</p>
                                    @if($photo->tipo)
                                        <p class="text-[10px] text-wc-text-secondary capitalize">{{ $photo->tipo }}</p>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    @endif

    {{-- ASSIGN COACH MODAL --}}
    <div x-show="showCoachModal"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4"
         x-cloak>

        {{-- Backdrop --}}
        <div class="absolute inset-0 bg-black/60" x-on:click="showCoachModal = false; $wire.closeCoachModal()"></div>

        {{-- Modal --}}
        <div class="relative w-full max-w-md rounded-card border border-wc-border bg-wc-bg-secondary p-6 shadow-xl"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95">

            <div class="flex items-center justify-between mb-5">
                <h3 class="font-display text-2xl tracking-wide text-wc-text">Asignar Coach</h3>
                <button x-on:click="showCoachModal = false; $wire.closeCoachModal()" class="text-wc-text-tertiary hover:text-wc-text transition-colors">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="space-y-4">
                {{-- Coach select --}}
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Coach</label>
                    <select wire:model="selectedCoachId"
                            class="w-full rounded-lg border border-wc-border bg-wc-bg py-2 pl-3 pr-8 text-sm text-wc-text focus:border-red-500 focus:outline-none focus:ring-1 focus:ring-red-500">
                        <option value="0">Seleccionar coach...</option>
                        @foreach($coaches as $coach)
                            <option value="{{ $coach->id }}">{{ $coach->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Plan type select --}}
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Tipo de Plan</label>
                    <select wire:model="assignPlanType"
                            class="w-full rounded-lg border border-wc-border bg-wc-bg py-2 pl-3 pr-8 text-sm text-wc-text focus:border-red-500 focus:outline-none focus:ring-1 focus:ring-red-500">
                        <option value="entrenamiento">Entrenamiento</option>
                        <option value="nutricion">Nutricion</option>
                        <option value="habitos">Habitos</option>
                    </select>
                </div>

                {{-- Buttons --}}
                <div class="flex justify-end gap-3 pt-2">
                    <button x-on:click="showCoachModal = false; $wire.closeCoachModal()"
                            class="rounded-lg border border-wc-border bg-wc-bg-tertiary px-4 py-2 text-sm font-medium text-wc-text hover:bg-wc-bg-secondary transition-colors">
                        Cancelar
                    </button>
                    <button wire:click="assignCoach"
                            class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700 transition-colors">
                        Asignar Coach
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>
