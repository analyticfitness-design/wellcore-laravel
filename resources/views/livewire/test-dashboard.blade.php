<div>
    {{-- Header --}}
    <div class="mb-8">
        <h1 class="font-display text-4xl tracking-wide text-wc-text">TEST DASHBOARD</h1>
        <p class="mt-2 text-wc-text-secondary">Fase 0 — Conexion a MySQL, modelos Eloquent, Livewire 3, Design System WellCore</p>
    </div>

    {{-- Status Checks --}}
    <div class="mb-8 rounded-xl border border-emerald-500/20 bg-emerald-500/5 p-4">
        <h2 class="text-sm font-semibold text-emerald-500 uppercase tracking-wide mb-3">Estado del Sistema</h2>
        <div class="grid grid-cols-2 gap-2 sm:grid-cols-3 md:grid-cols-6">
            <div class="flex items-center gap-2 text-sm">
                <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                <span class="text-wc-text-secondary">MySQL {{ $dbVersion }}</span>
            </div>
            <div class="flex items-center gap-2 text-sm">
                <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                <span class="text-wc-text-secondary">{{ $tableCount }} tablas WC</span>
            </div>
            <div class="flex items-center gap-2 text-sm">
                <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                <span class="text-wc-text-secondary">Laravel {{ app()->version() }}</span>
            </div>
            <div class="flex items-center gap-2 text-sm">
                <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                <span class="text-wc-text-secondary">PHP {{ PHP_VERSION }}</span>
            </div>
            <div class="flex items-center gap-2 text-sm">
                <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                <span class="text-wc-text-secondary">Livewire 3</span>
            </div>
            <div class="flex items-center gap-2 text-sm">
                <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                <span class="text-wc-text-secondary">Tailwind 4</span>
            </div>
        </div>
    </div>

    {{-- Stats Grid --}}
    <div class="mb-8 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-5">
        <x-stat-card label="Clientes Total" :value="$totalClients" />
        <x-stat-card label="Clientes Activos" :value="$activeClients" />
        <x-stat-card label="Admins/Coaches" :value="$totalAdmins" />
        <x-stat-card label="Pagos" :value="$totalPayments" />
        <x-stat-card label="Check-ins" :value="$totalCheckins" />
    </div>

    {{-- Clients Table --}}
    <x-card title="Clientes" subtitle="Datos reales desde MySQL wellcore_fitness">
        {{-- Filters --}}
        <div class="mb-4 flex flex-col gap-3 sm:flex-row">
            <div class="flex-1">
                <input
                    wire:model.live.debounce.300ms="search"
                    type="text"
                    placeholder="Buscar por nombre, email o codigo..."
                    class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-4 py-2.5 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent"
                >
            </div>
            <select
                wire:model.live="planFilter"
                class="rounded-lg border border-wc-border bg-wc-bg-secondary px-4 py-2.5 text-sm text-wc-text focus:border-wc-accent focus:outline-none"
            >
                <option value="">Todos los planes</option>
                @foreach($plans as $plan)
                    <option value="{{ $plan }}">{{ ucfirst($plan) }}</option>
                @endforeach
            </select>
            <select
                wire:model.live="statusFilter"
                class="rounded-lg border border-wc-border bg-wc-bg-secondary px-4 py-2.5 text-sm text-wc-text focus:border-wc-accent focus:outline-none"
            >
                <option value="">Todos los estados</option>
                @foreach($statuses as $status)
                    <option value="{{ $status }}">{{ ucfirst($status) }}</option>
                @endforeach
            </select>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-wc-border text-xs uppercase tracking-wider text-wc-text-tertiary">
                        <th class="pb-3 pr-4 font-medium">Cliente</th>
                        <th class="pb-3 pr-4 font-medium">Codigo</th>
                        <th class="pb-3 pr-4 font-medium">Plan</th>
                        <th class="pb-3 pr-4 font-medium">Estado</th>
                        <th class="pb-3 pr-4 font-medium">Inicio</th>
                        <th class="pb-3 font-medium">Creado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-wc-border">
                    @forelse($clients as $client)
                        <tr class="hover:bg-wc-bg-secondary/50" wire:key="client-{{ $client->id }}">
                            <td class="py-3 pr-4">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-wc-accent/10 text-xs font-bold text-wc-accent">
                                        {{ strtoupper(substr($client->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <p class="font-medium text-wc-text">{{ $client->name }}</p>
                                        <p class="text-xs text-wc-text-tertiary">{{ $client->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3 pr-4">
                                <code class="font-mono text-xs text-wc-text-secondary">{{ $client->client_code }}</code>
                            </td>
                            <td class="py-3 pr-4">
                                @php
                                    $planVal = $client->plan?->value ?? $client->getRawOriginal('plan');
                                    $planColors = [
                                        'esencial' => 'info',
                                        'metodo' => 'success',
                                        'elite' => 'accent',
                                        'rise' => 'warning',
                                        'presencial' => 'default',
                                    ];
                                @endphp
                                <x-badge :color="$planColors[$planVal] ?? 'default'">
                                    {{ ucfirst($planVal ?? 'N/A') }}
                                </x-badge>
                            </td>
                            <td class="py-3 pr-4">
                                @php
                                    $statusVal = $client->status?->value ?? $client->getRawOriginal('status');
                                    $statusColors = [
                                        'activo' => 'success',
                                        'inactivo' => 'default',
                                        'suspendido' => 'danger',
                                        'pendiente' => 'warning',
                                        'congelado' => 'info',
                                    ];
                                @endphp
                                <x-badge :color="$statusColors[$statusVal] ?? 'default'">
                                    {{ ucfirst($statusVal ?? 'N/A') }}
                                </x-badge>
                            </td>
                            <td class="py-3 pr-4 text-wc-text-secondary">
                                {{ $client->fecha_inicio ? \Carbon\Carbon::parse($client->fecha_inicio)->format('d M Y') : '—' }}
                            </td>
                            <td class="py-3 text-wc-text-secondary">
                                {{ $client->created_at ? $client->created_at->diffForHumans() : '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-wc-text-tertiary">
                                No se encontraron clientes
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($clients->hasPages())
            <div class="mt-4 border-t border-wc-border pt-4">
                {{ $clients->links() }}
            </div>
        @endif
    </x-card>

    {{-- Tech Info --}}
    <div class="mt-8 grid gap-4 sm:grid-cols-2">
        <x-card title="Modelos Eloquent">
            <div class="grid grid-cols-2 gap-1 text-sm">
                @php
                    $modelFiles = glob(app_path('Models/*.php'));
                    $modelCount = count($modelFiles);
                @endphp
                @foreach($modelFiles as $file)
                    <div class="flex items-center gap-2 text-wc-text-secondary">
                        <span class="h-1.5 w-1.5 rounded-full bg-wc-accent"></span>
                        {{ basename($file, '.php') }}
                    </div>
                @endforeach
            </div>
            <p class="mt-3 text-xs text-wc-text-tertiary">{{ $modelCount }} modelos creados</p>
        </x-card>

        <x-card title="Enums">
            <div class="grid grid-cols-2 gap-1 text-sm">
                @php
                    $enumFiles = glob(app_path('Enums/*.php'));
                @endphp
                @foreach($enumFiles as $file)
                    <div class="flex items-center gap-2 text-wc-text-secondary">
                        <span class="h-1.5 w-1.5 rounded-full bg-blue-500"></span>
                        {{ basename($file, '.php') }}
                    </div>
                @endforeach
            </div>
        </x-card>
    </div>
</div>
