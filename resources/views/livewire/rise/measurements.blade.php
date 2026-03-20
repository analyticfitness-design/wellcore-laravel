<div class="space-y-6">

    {{-- Page header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="font-display text-3xl tracking-wide text-wc-text sm:text-4xl">Mediciones</h1>
            <p class="mt-1 text-sm text-wc-text-tertiary">Registra y monitorea tus mediciones corporales.</p>
        </div>
        <button wire:click="toggleForm"
                class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-amber-500 to-amber-600 px-4 py-2 text-sm font-medium text-white hover:from-amber-600 hover:to-amber-700 transition-all">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Nueva medicion
        </button>
    </div>

    {{-- Success message --}}
    @if($saved)
        <div class="rounded-card border border-emerald-500/20 bg-emerald-500/5 p-4">
            <div class="flex items-center gap-3">
                <svg class="h-5 w-5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                <p class="text-sm font-medium text-emerald-500">Medicion guardada exitosamente.</p>
            </div>
        </div>
    @endif

    {{-- Measurement form (collapsible) --}}
    @if($showForm)
        <div class="rounded-card border border-amber-500/20 bg-wc-bg-tertiary p-5 sm:p-6">
            <h2 class="font-display text-lg tracking-wide text-wc-text">Nueva medicion — {{ now()->format('d M Y') }}</h2>

            <form wire:submit="save" class="mt-5 space-y-5">
                {{-- Weight (required) --}}
                <div>
                    <label for="weight_kg" class="block text-sm font-medium text-wc-text-secondary">
                        Peso (kg) <span class="text-wc-accent">*</span>
                    </label>
                    <input type="number" step="0.1" id="weight_kg"
                           wire:model="weight_kg"
                           placeholder="Ej: 75.5"
                           class="mt-1.5 block w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2.5 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500 sm:max-w-xs">
                    @error('weight_kg')
                        <p class="mt-1 text-xs text-wc-accent">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Body measurements grid --}}
                <div>
                    <p class="text-sm font-medium text-wc-text-secondary">Medidas corporales (cm)</p>
                    <div class="mt-2 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
                        <div>
                            <label for="chest_cm" class="block text-xs text-wc-text-tertiary">Pecho</label>
                            <input type="number" step="0.1" id="chest_cm" wire:model="chest_cm" placeholder="--"
                                   class="mt-1 block w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500">
                        </div>
                        <div>
                            <label for="waist_cm" class="block text-xs text-wc-text-tertiary">Cintura</label>
                            <input type="number" step="0.1" id="waist_cm" wire:model="waist_cm" placeholder="--"
                                   class="mt-1 block w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500">
                        </div>
                        <div>
                            <label for="hips_cm" class="block text-xs text-wc-text-tertiary">Cadera</label>
                            <input type="number" step="0.1" id="hips_cm" wire:model="hips_cm" placeholder="--"
                                   class="mt-1 block w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500">
                        </div>
                        <div>
                            <label for="thigh_cm" class="block text-xs text-wc-text-tertiary">Muslo</label>
                            <input type="number" step="0.1" id="thigh_cm" wire:model="thigh_cm" placeholder="--"
                                   class="mt-1 block w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500">
                        </div>
                        <div>
                            <label for="arm_cm" class="block text-xs text-wc-text-tertiary">Brazo</label>
                            <input type="number" step="0.1" id="arm_cm" wire:model="arm_cm" placeholder="--"
                                   class="mt-1 block w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500">
                        </div>
                    </div>
                </div>

                {{-- Composition --}}
                <div>
                    <p class="text-sm font-medium text-wc-text-secondary">Composicion corporal (%)</p>
                    <div class="mt-2 grid grid-cols-2 gap-3 sm:max-w-sm">
                        <div>
                            <label for="muscle_pct" class="block text-xs text-wc-text-tertiary">Musculo</label>
                            <input type="number" step="0.1" id="muscle_pct" wire:model="muscle_pct" placeholder="--"
                                   class="mt-1 block w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500">
                        </div>
                        <div>
                            <label for="fat_pct" class="block text-xs text-wc-text-tertiary">Grasa</label>
                            <input type="number" step="0.1" id="fat_pct" wire:model="fat_pct" placeholder="--"
                                   class="mt-1 block w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500">
                        </div>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="flex items-center gap-3 border-t border-wc-border pt-4">
                    <button type="submit"
                            class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-amber-500 to-amber-600 px-5 py-2.5 text-sm font-medium text-white hover:from-amber-600 hover:to-amber-700 transition-all">
                        Guardar medicion
                    </button>
                    <button type="button" wire:click="toggleForm"
                            class="rounded-lg border border-wc-border bg-wc-bg-secondary px-4 py-2.5 text-sm font-medium text-wc-text-secondary hover:text-wc-text transition-colors">
                        Cancelar
                    </button>
                    <div wire:loading wire:target="save" class="text-xs text-wc-text-tertiary">Guardando...</div>
                </div>
            </form>
        </div>
    @endif

    {{-- First vs Latest comparison --}}
    @if($firstMeasurement && $latestMeasurement)
        <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-5">
            <h2 class="font-display text-lg tracking-wide text-wc-text">Progreso: Inicio vs Actual</h2>
            <p class="mt-1 text-xs text-wc-text-tertiary">{{ $firstMeasurement['date'] }} vs {{ $latestMeasurement['date'] }}</p>

            <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-4">
                @php
                    $comparisonFields = [
                        ['key' => 'weight_kg', 'label' => 'Peso', 'unit' => 'kg', 'lowerBetter' => true],
                        ['key' => 'waist_cm', 'label' => 'Cintura', 'unit' => 'cm', 'lowerBetter' => true],
                        ['key' => 'muscle_pct', 'label' => 'Musculo', 'unit' => '%', 'lowerBetter' => false],
                        ['key' => 'fat_pct', 'label' => 'Grasa', 'unit' => '%', 'lowerBetter' => true],
                        ['key' => 'chest_cm', 'label' => 'Pecho', 'unit' => 'cm', 'lowerBetter' => false],
                        ['key' => 'hips_cm', 'label' => 'Cadera', 'unit' => 'cm', 'lowerBetter' => true],
                        ['key' => 'thigh_cm', 'label' => 'Muslo', 'unit' => 'cm', 'lowerBetter' => false],
                        ['key' => 'arm_cm', 'label' => 'Brazo', 'unit' => 'cm', 'lowerBetter' => false],
                    ];
                @endphp

                @foreach($comparisonFields as $field)
                    @if($firstMeasurement[$field['key']] !== null && $latestMeasurement[$field['key']] !== null)
                        @php
                            $diff = round($latestMeasurement[$field['key']] - $firstMeasurement[$field['key']], 1);
                            $improved = $field['lowerBetter'] ? $diff < 0 : $diff > 0;
                        @endphp
                        <div class="rounded-lg border border-wc-border bg-wc-bg-secondary p-3">
                            <p class="text-xs font-medium text-wc-text-tertiary">{{ $field['label'] }}</p>
                            <p class="mt-1 font-data text-lg font-bold text-wc-text">
                                {{ $latestMeasurement[$field['key']] }}<span class="text-xs font-normal text-wc-text-tertiary">{{ $field['unit'] }}</span>
                            </p>
                            <p class="mt-0.5 text-xs font-medium {{ $improved ? 'text-emerald-500' : ($diff == 0 ? 'text-wc-text-tertiary' : 'text-wc-accent') }}">
                                @if($diff != 0)
                                    <span>{{ $diff > 0 ? '+' : '' }}{{ $diff }}{{ $field['unit'] }}</span>
                                    @if($improved)
                                        <svg class="inline h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 10.5 12 3m0 0 7.5 7.5M12 3v18" />
                                        </svg>
                                    @else
                                        <svg class="inline h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 13.5 12 21m0 0-7.5-7.5M12 21V3" />
                                        </svg>
                                    @endif
                                @else
                                    Sin cambio
                                @endif
                            </p>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    @endif

    {{-- History table --}}
    <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-5">
        <h2 class="font-display text-lg tracking-wide text-wc-text">Historial de mediciones</h2>

        @if(count($history) > 0)
            <div class="mt-4 overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-wc-border">
                            <th class="whitespace-nowrap pb-3 pr-4 text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Fecha</th>
                            <th class="whitespace-nowrap pb-3 pr-4 text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Peso</th>
                            <th class="whitespace-nowrap pb-3 pr-4 text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Pecho</th>
                            <th class="whitespace-nowrap pb-3 pr-4 text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Cintura</th>
                            <th class="whitespace-nowrap pb-3 pr-4 text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Cadera</th>
                            <th class="whitespace-nowrap pb-3 pr-4 text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Muslo</th>
                            <th class="whitespace-nowrap pb-3 pr-4 text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Brazo</th>
                            <th class="whitespace-nowrap pb-3 pr-4 text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Musculo%</th>
                            <th class="whitespace-nowrap pb-3 text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Grasa%</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-wc-border">
                        @foreach($history as $index => $row)
                            <tr class="{{ $index === 0 ? 'bg-amber-500/5' : '' }}">
                                <td class="whitespace-nowrap py-3 pr-4 font-medium text-wc-text">
                                    {{ $row['date'] }}
                                    @if($index === 0)
                                        <span class="ml-1 text-[10px] font-semibold text-amber-500">ULTIMO</span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap py-3 pr-4 font-data text-wc-text">{{ $row['weight_kg'] ? $row['weight_kg'] . ' kg' : '--' }}</td>
                                <td class="whitespace-nowrap py-3 pr-4 font-data text-wc-text-secondary">{{ $row['chest_cm'] ?? '--' }}</td>
                                <td class="whitespace-nowrap py-3 pr-4 font-data text-wc-text-secondary">{{ $row['waist_cm'] ?? '--' }}</td>
                                <td class="whitespace-nowrap py-3 pr-4 font-data text-wc-text-secondary">{{ $row['hips_cm'] ?? '--' }}</td>
                                <td class="whitespace-nowrap py-3 pr-4 font-data text-wc-text-secondary">{{ $row['thigh_cm'] ?? '--' }}</td>
                                <td class="whitespace-nowrap py-3 pr-4 font-data text-wc-text-secondary">{{ $row['arm_cm'] ?? '--' }}</td>
                                <td class="whitespace-nowrap py-3 pr-4 font-data text-wc-text-secondary">{{ $row['muscle_pct'] ? $row['muscle_pct'] . '%' : '--' }}</td>
                                <td class="whitespace-nowrap py-3 font-data text-wc-text-secondary">{{ $row['fat_pct'] ? $row['fat_pct'] . '%' : '--' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="mt-6 flex flex-col items-center py-8 text-center">
                <svg class="h-10 w-10 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 14.25v2.25m3-4.5v4.5m3-6.75v6.75m3-9v9M6 20.25h12A2.25 2.25 0 0 0 20.25 18V6A2.25 2.25 0 0 0 18 3.75H6A2.25 2.25 0 0 0 3.75 6v12A2.25 2.25 0 0 0 6 20.25Z" />
                </svg>
                <p class="mt-3 text-sm font-medium text-wc-text">Sin mediciones registradas</p>
                <p class="mt-1 text-xs text-wc-text-tertiary">Registra tu primera medicion para comenzar a monitorear tu progreso.</p>
                <button wire:click="toggleForm"
                        class="mt-4 inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-amber-500 to-amber-600 px-4 py-2 text-sm font-medium text-white hover:from-amber-600 hover:to-amber-700 transition-all">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Primera medicion
                </button>
            </div>
        @endif
    </div>

</div>
