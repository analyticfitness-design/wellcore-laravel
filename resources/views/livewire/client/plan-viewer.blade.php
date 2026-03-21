<div>
    <div class="mb-8">
        <h1 class="font-display text-3xl tracking-wide text-wc-text">MI PLAN</h1>
        <p class="mt-1 text-sm text-wc-text-secondary">Tu programacion personalizada asignada por tu coach</p>
    </div>

    {{-- Tabs --}}
    @php
        $canAccessNutricion = in_array($clientPlanType, ['metodo', 'elite', 'presencial', 'rise']);
        $canAccessElite     = in_array($clientPlanType, ['elite']);
        $tabs = [
            'entrenamiento' => 'Entrenamiento',
            'habitos'       => 'Habitos',
            'nutricion'     => 'Nutricion',
            'suplementacion'=> 'Suplementos',
            'ciclo'         => 'Ciclo',
            'bloodwork'     => 'Bloodwork',
        ];
    @endphp
    <div class="mb-6 flex gap-1 overflow-x-auto rounded-xl border border-wc-border bg-wc-bg-secondary p-1">
        @foreach($tabs as $key => $label)
            @php
                $locked = (in_array($key, ['nutricion','suplementacion']) && !$canAccessNutricion)
                       || (in_array($key, ['ciclo','bloodwork']) && !$canAccessElite);
            @endphp
            <button
                @if(!$locked) wire:click="setTab('{{ $key }}')" @endif
                @class([
                    'shrink-0 flex-1 rounded-lg px-4 py-2.5 text-sm font-medium transition-colors whitespace-nowrap',
                    'bg-wc-bg-tertiary text-wc-text shadow-sm' => $activeTab === $key,
                    'text-wc-text-tertiary hover:text-wc-text-secondary' => $activeTab !== $key && !$locked,
                    'cursor-not-allowed opacity-40' => $locked,
                ])
            >
                {{ $label }}
                @if($locked)
                    <span class="ml-1 text-xs">🔒</span>
                @endif
            </button>
        @endforeach
    </div>

    {{-- Tab Content --}}

    {{-- ==================== TAB: ENTRENAMIENTO ==================== --}}
    @if($activeTab === 'entrenamiento')
        @if($trainingPlan)
            <div class="space-y-4">
                @if(isset($trainingPlan['dias']))
                    @foreach($trainingPlan['dias'] as $dia)
                        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
                            <h3 class="font-display text-lg tracking-wide text-wc-accent">
                                {{ strtoupper($dia['nombre'] ?? $dia['dia'] ?? 'DIA') }}
                            </h3>
                            @if(isset($dia['grupo_muscular']))
                                <p class="text-sm text-wc-text-secondary">{{ $dia['grupo_muscular'] }}</p>
                            @endif
                            @if(isset($dia['ejercicios']))
                                <div class="mt-3 space-y-2">
                                    @foreach($dia['ejercicios'] as $ej)
                                        <div class="flex items-center justify-between rounded-lg bg-wc-bg-secondary px-4 py-2.5">
                                            <span class="text-sm font-medium text-wc-text">
                                                {{ is_array($ej) ? ($ej['nombre'] ?? $ej['ejercicio'] ?? '') : $ej }}
                                            </span>
                                            <div class="flex items-center gap-2">
                                                @if(is_array($ej) && isset($ej['series']))
                                                    <span class="font-data text-sm text-wc-text-secondary">
                                                        {{ $ej['series'] }}x{{ $ej['repeticiones'] ?? $ej['reps'] ?? '' }}
                                                    </span>
                                                @endif
                                                <button wire:click="$dispatch('open-rest-timer', {seconds: 90})"
                                                    class="btn-press inline-flex items-center gap-1 rounded-lg bg-wc-bg-tertiary px-2 py-1 text-xs text-wc-text-secondary hover:bg-wc-accent/10 hover:text-wc-accent transition-colors"
                                                    title="Timer de descanso">
                                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                    <span class="hidden sm:inline">Descanso</span>
                                                </button>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach
                @else
                    <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-6">
                        <pre class="text-sm text-wc-text-secondary whitespace-pre-wrap">{{ json_encode($trainingPlan, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                    </div>
                @endif
            </div>
        @else
            <x-empty-state title="SIN PLAN DE ENTRENAMIENTO" message="Tu coach aun no ha asignado un plan de entrenamiento." />
        @endif

    {{-- ==================== TAB: NUTRICION ==================== --}}
    @elseif($activeTab === 'nutricion')
        @if($canAccessNutricion)
            @if($nutritionPlan)
                <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-6">
                    <pre class="text-sm text-wc-text-secondary whitespace-pre-wrap">{{ json_encode($nutritionPlan, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                </div>
            @else
                <x-empty-state title="SIN PLAN DE NUTRICION" message="Tu coach aun no ha asignado un plan de nutricion." />
            @endif
        @else
            <div class="rounded-xl border border-wc-accent/20 bg-wc-accent/5 p-8 text-center">
                <p class="font-display text-xl text-wc-text">Nutricion Premium</p>
                <p class="mt-2 text-sm text-wc-text-secondary">Disponible en planes Metodo y Elite.</p>
                <a href="/planes" class="mt-4 inline-block rounded-full bg-wc-accent px-6 py-2.5 text-sm font-semibold text-white">Upgrade</a>
            </div>
        @endif

    {{-- ==================== TAB: SUPLEMENTACION ==================== --}}
    @elseif($activeTab === 'suplementacion')
        @if($canAccessNutricion)
            @if($supplementPlan)
                <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-6">
                    <pre class="text-sm text-wc-text-secondary whitespace-pre-wrap">{{ json_encode($supplementPlan, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                </div>
            @else
                <x-empty-state title="SIN PLAN DE SUPLEMENTACION" message="Tu coach aun no ha asignado un plan de suplementacion." />
            @endif
        @else
            <div class="rounded-xl border border-wc-accent/20 bg-wc-accent/5 p-8 text-center">
                <p class="font-display text-xl text-wc-text">Suplementacion Premium</p>
                <p class="mt-2 text-sm text-wc-text-secondary">Disponible en planes Metodo y Elite.</p>
                <a href="/planes" class="mt-4 inline-block rounded-full bg-wc-accent px-6 py-2.5 text-sm font-semibold text-white">Upgrade</a>
            </div>
        @endif

    {{-- ==================== TAB: HABITOS ==================== --}}
    @elseif($activeTab === 'habitos')
        <div class="space-y-6">
            {{-- Compliance bar --}}
            <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="font-display text-lg tracking-wide text-wc-text">CUMPLIMIENTO MENSUAL</h3>
                        <p class="mt-0.5 text-sm text-wc-text-secondary">Dias con al menos 1 habito registrado este mes</p>
                    </div>
                    <span class="font-data text-3xl font-bold text-wc-accent">{{ $habitCompliance }}%</span>
                </div>
                <div class="mt-3 h-2 w-full overflow-hidden rounded-full bg-wc-bg-secondary">
                    <div class="h-full rounded-full bg-wc-accent transition-all" style="width: {{ $habitCompliance }}%"></div>
                </div>
            </div>

            {{-- Habit Cards --}}
            <div class="grid gap-4 sm:grid-cols-2">
                @foreach($habitData as $habit)
                    <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
                        <div class="flex items-start justify-between">
                            <div>
                                <h4 class="font-display text-base tracking-wide text-wc-text">{{ strtoupper($habit['label']) }}</h4>
                                <p class="mt-1 text-xs text-wc-text-tertiary">
                                    Racha: <span class="font-data font-semibold text-wc-text">{{ $habit['streak'] }} dias</span>
                                </p>
                            </div>
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-wc-bg-secondary">
                                @if($habit['icon'] === 'droplet')
                                    <svg class="h-5 w-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 21c-4.97 0-9-4.03-9-9 0-3.87 4.5-9.5 7.68-12.38a1.74 1.74 0 012.64 0C16.5 2.5 21 8.13 21 12c0 4.97-4.03 9-9 9z"/></svg>
                                @elseif($habit['icon'] === 'moon')
                                    <svg class="h-5 w-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12.79A9 9 0 1111.21 3a7 7 0 009.79 9.79z"/></svg>
                                @elseif($habit['icon'] === 'utensils')
                                    <svg class="h-5 w-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2v6m0 0c1.66 0 3-1.34 3-3S13.66 2 12 2s-3 1.34-3 3 1.34 3 3 3zm0 0v14m6-20v8a2 2 0 01-2 2h-1v10"/></svg>
                                @elseif($habit['icon'] === 'brain')
                                    <svg class="h-5 w-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                                @endif
                            </div>
                        </div>

                        <div class="mt-3">
                            <p class="text-xs text-wc-text-tertiary">
                                Promedio: <span class="font-data font-semibold text-wc-text">{{ $habit['average'] }}/10</span>
                            </p>
                        </div>

                        {{-- Last 7 days dots --}}
                        <div class="mt-3 flex items-end gap-1.5">
                            @foreach($habit['last7'] as $day)
                                <div class="flex flex-1 flex-col items-center gap-1">
                                    <div
                                        class="h-6 w-full rounded-sm transition-all {{ $day['value'] > 0 ? '' : 'bg-wc-bg-secondary' }}"
                                        style="{{ $day['value'] > 0 ? 'background-color: rgba(220, 38, 38, ' . min($day['value'] / 10, 1) . ');' : '' }}"
                                        title="{{ $day['date'] }}: {{ $day['value'] }}/10"
                                    ></div>
                                    <span class="text-[10px] text-wc-text-tertiary">{{ $day['date'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            @if(empty($habitData) || collect($habitData)->every(fn($h) => $h['streak'] === 0 && $h['average'] == 0))
                <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-6 text-center">
                    <p class="text-sm text-wc-text-secondary">Aun no tienes habitos registrados en los ultimos 30 dias.</p>
                    <p class="mt-1 text-xs text-wc-text-tertiary">Registra tus habitos diarios desde la pantalla principal.</p>
                </div>
            @endif
        </div>

    {{-- ==================== TAB: CICLO HORMONAL ==================== --}}
    @elseif($activeTab === 'ciclo')
        @if(!$canAccessElite)
            <div class="rounded-xl border border-wc-accent/20 bg-wc-accent/5 p-8 text-center">
                <p class="font-display text-xl text-wc-text">Ciclo Hormonal Personalizado</p>
                <p class="mt-2 text-sm text-wc-text-secondary">Disponible exclusivamente en el plan Elite.</p>
                <a href="/planes" class="mt-4 inline-block rounded-full bg-wc-accent px-6 py-2.5 text-sm font-semibold text-white">Upgrade a Elite</a>
            </div>
        @else
        <div
            x-data="{
                startDate: localStorage.getItem('wc_cycle_start') || '',
                cycleLength: parseInt(localStorage.getItem('wc_cycle_length')) || 28,
                get currentDay() {
                    if (!this.startDate) return null;
                    const start = new Date(this.startDate + 'T00:00:00');
                    const today = new Date();
                    today.setHours(0, 0, 0, 0);
                    const diff = Math.floor((today - start) / (1000 * 60 * 60 * 24));
                    const dayInCycle = (diff % this.cycleLength) + 1;
                    return dayInCycle > 0 ? dayInCycle : null;
                },
                get phaseName() {
                    const day = this.currentDay;
                    if (!day) return '';
                    if (day <= 5) return 'Menstrual';
                    if (day <= 13) return 'Folicular';
                    if (day <= 16) return 'Ovulatoria';
                    return 'Lutea';
                },
                get phaseKey() {
                    const day = this.currentDay;
                    if (!day) return '';
                    if (day <= 5) return 'menstrual';
                    if (day <= 13) return 'folicular';
                    if (day <= 16) return 'ovulatoria';
                    return 'lutea';
                },
                get phaseColor() {
                    const colors = { menstrual: 'text-red-400', folicular: 'text-green-400', ovulatoria: 'text-amber-400', lutea: 'text-purple-400' };
                    return colors[this.phaseKey] || 'text-wc-text';
                },
                get phaseBg() {
                    const colors = { menstrual: 'bg-red-400/10 border-red-400/30', folicular: 'bg-green-400/10 border-green-400/30', ovulatoria: 'bg-amber-400/10 border-amber-400/30', lutea: 'bg-purple-400/10 border-purple-400/30' };
                    return colors[this.phaseKey] || 'bg-wc-bg-tertiary border-wc-border';
                },
                get recommendation() {
                    const recs = {
                        menstrual: 'Prioriza el descanso y recuperacion. Ejercicios de baja intensidad como yoga, caminata o estiramientos suaves. Escucha a tu cuerpo.',
                        folicular: 'Tu energia sube. Buen momento para aumentar intensidad y probar nuevos ejercicios. Entrenamiento de fuerza e intervalos son ideales.',
                        ovulatoria: 'Pico de energia y rendimiento. Aprovecha para entrenamientos de alta intensidad, PRs y sesiones exigentes.',
                        lutea: 'La energia comienza a bajar. Mantiene entrenamientos moderados. Enfocate en tecnica y trabajo de estabilidad.'
                    };
                    return recs[this.phaseKey] || '';
                },
                get daysUntilNext() {
                    if (!this.currentDay) return null;
                    return this.cycleLength - this.currentDay + 1;
                },
                save() {
                    localStorage.setItem('wc_cycle_start', this.startDate);
                    localStorage.setItem('wc_cycle_length', this.cycleLength);
                },
                phases: [
                    { name: 'Menstrual', days: '1-5', color: 'bg-red-400' },
                    { name: 'Folicular', days: '6-13', color: 'bg-green-400' },
                    { name: 'Ovulatoria', days: '14-16', color: 'bg-amber-400' },
                    { name: 'Lutea', days: '17-28', color: 'bg-purple-400' },
                ]
            }"
            class="space-y-6"
        >
            {{-- Info note --}}
            <div class="rounded-xl border border-wc-border bg-wc-bg-secondary p-4">
                <p class="text-xs text-wc-text-tertiary">
                    Esta herramienta es opcional y esta disponible para quienes la necesiten. Los datos se guardan localmente en tu navegador.
                </p>
            </div>

            {{-- Configuration form --}}
            <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
                <h3 class="font-display text-lg tracking-wide text-wc-text">CONFIGURACION</h3>
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-xs font-medium text-wc-text-tertiary mb-1">Fecha inicio del ultimo ciclo</label>
                        <input
                            type="date"
                            x-model="startDate"
                            @change="save()"
                            class="w-full rounded-lg border border-wc-border bg-wc-bg-tertiary py-2.5 px-3 text-sm text-wc-text focus:border-wc-accent focus:outline-none"
                        />
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-wc-text-tertiary mb-1">Duracion del ciclo (dias)</label>
                        <input
                            type="number"
                            x-model.number="cycleLength"
                            @change="save()"
                            min="21"
                            max="40"
                            class="w-full rounded-lg border border-wc-border bg-wc-bg-tertiary py-2.5 px-3 text-sm text-wc-text focus:border-wc-accent focus:outline-none"
                        />
                    </div>
                </div>
            </div>

            {{-- Current phase display --}}
            <template x-if="currentDay">
                <div>
                    <div :class="phaseBg" class="rounded-xl border p-6 text-center">
                        <p class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Fase actual</p>
                        <h2 :class="phaseColor" class="font-display text-3xl tracking-wide mt-1" x-text="phaseName"></h2>
                        <p class="mt-2 font-data text-lg text-wc-text">
                            Dia <span x-text="currentDay" class="font-bold text-wc-accent"></span>
                            de <span x-text="cycleLength"></span>
                        </p>
                        <p class="mt-1 text-xs text-wc-text-tertiary">
                            Proximo ciclo en <span x-text="daysUntilNext" class="font-semibold"></span> dias
                        </p>
                    </div>

                    {{-- Training recommendation --}}
                    <div class="mt-4 rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
                        <h4 class="font-display text-sm tracking-wide text-wc-accent">RECOMENDACION DE ENTRENAMIENTO</h4>
                        <p class="mt-2 text-sm text-wc-text-secondary" x-text="recommendation"></p>
                    </div>
                </div>
            </template>

            <template x-if="!currentDay">
                <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-6 text-center">
                    <p class="text-sm text-wc-text-secondary">Ingresa la fecha de inicio de tu ultimo ciclo para ver tu fase actual.</p>
                </div>
            </template>

            {{-- Phase reference --}}
            <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
                <h3 class="font-display text-sm tracking-wide text-wc-text mb-3">FASES DEL CICLO</h3>
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                    <template x-for="phase in phases" :key="phase.name">
                        <div class="rounded-lg bg-wc-bg-secondary p-3 text-center">
                            <div :class="phase.color" class="mx-auto mb-2 h-2 w-8 rounded-full"></div>
                            <p class="text-xs font-medium text-wc-text" x-text="phase.name"></p>
                            <p class="text-[10px] text-wc-text-tertiary">Dias <span x-text="phase.days"></span></p>
                        </div>
                    </template>
                </div>
            </div>
        </div>
        @endif

    {{-- ==================== TAB: BLOODWORK ==================== --}}
    @elseif($activeTab === 'bloodwork')
        @if(!$canAccessElite)
            <div class="rounded-xl border border-wc-accent/20 bg-wc-accent/5 p-8 text-center">
                <p class="font-display text-xl text-wc-text">Bloodwork &amp; Analisis Laboratorio</p>
                <p class="mt-2 text-sm text-wc-text-secondary">Disponible exclusivamente en el plan Elite.</p>
                <a href="/planes" class="mt-4 inline-block rounded-full bg-wc-accent px-6 py-2.5 text-sm font-semibold text-white">Upgrade a Elite</a>
            </div>
        @else
        <div class="space-y-6">
            {{-- Add result form --}}
            <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
                <h3 class="font-display text-lg tracking-wide text-wc-text">AGREGAR RESULTADO</h3>

                @if($bwShowSuccess)
                    <div class="mt-3 rounded-lg border border-green-500/30 bg-green-500/10 px-4 py-2.5 text-sm text-green-400">
                        Resultado guardado correctamente.
                    </div>
                @endif

                <form wire:submit="saveBloodwork" class="mt-4 space-y-4">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="block text-xs font-medium text-wc-text-tertiary mb-1">Prueba</label>
                            <select
                                wire:model="bwTestName"
                                class="w-full rounded-lg border border-wc-border bg-wc-bg-tertiary py-2.5 px-3 text-sm text-wc-text focus:border-wc-accent focus:outline-none"
                            >
                                <option value="">Seleccionar prueba...</option>
                                <option value="Glucosa">Glucosa</option>
                                <option value="Hemoglobina">Hemoglobina</option>
                                <option value="Colesterol Total">Colesterol Total</option>
                                <option value="HDL">HDL</option>
                                <option value="LDL">LDL</option>
                                <option value="Trigliceridos">Trigliceridos</option>
                                <option value="TSH">TSH</option>
                                <option value="Testosterona">Testosterona</option>
                                <option value="Vitamina D">Vitamina D</option>
                                <option value="Hierro">Hierro</option>
                            </select>
                            @error('bwTestName') <span class="text-xs text-red-400">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-wc-text-tertiary mb-1">Fecha</label>
                            <input
                                type="date"
                                wire:model="bwTestDate"
                                class="w-full rounded-lg border border-wc-border bg-wc-bg-tertiary py-2.5 px-3 text-sm text-wc-text focus:border-wc-accent focus:outline-none"
                            />
                            @error('bwTestDate') <span class="text-xs text-red-400">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-wc-text-tertiary mb-1">Valor</label>
                            <input
                                type="number"
                                step="0.01"
                                wire:model="bwValue"
                                placeholder="ej: 95.5"
                                class="w-full rounded-lg border border-wc-border bg-wc-bg-tertiary py-2.5 px-3 text-sm text-wc-text focus:border-wc-accent focus:outline-none"
                            />
                            @error('bwValue') <span class="text-xs text-red-400">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-wc-text-tertiary mb-1">Unidad</label>
                            <input
                                type="text"
                                wire:model="bwUnit"
                                placeholder="ej: mg/dL"
                                class="w-full rounded-lg border border-wc-border bg-wc-bg-tertiary py-2.5 px-3 text-sm text-wc-text focus:border-wc-accent focus:outline-none"
                            />
                            @error('bwUnit') <span class="text-xs text-red-400">{{ $message }}</span> @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-xs font-medium text-wc-text-tertiary mb-1">Rango de referencia (opcional)</label>
                            <input
                                type="text"
                                wire:model="bwReferenceRange"
                                placeholder="ej: 70-100 mg/dL"
                                class="w-full rounded-lg border border-wc-border bg-wc-bg-tertiary py-2.5 px-3 text-sm text-wc-text focus:border-wc-accent focus:outline-none"
                            />
                        </div>
                    </div>

                    <button
                        type="submit"
                        class="btn-press rounded-lg bg-wc-accent px-4 py-2 text-sm font-medium text-white hover:bg-wc-accent/90 transition-colors"
                        wire:loading.attr="disabled"
                    >
                        <span wire:loading.remove>Guardar Resultado</span>
                        <span wire:loading class="inline-flex items-center gap-2">
                            <svg class="h-4 w-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                            Guardando...
                        </span>
                    </button>
                </form>
            </div>

            {{-- Results table --}}
            <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary">
                <div class="p-5">
                    <h3 class="font-display text-lg tracking-wide text-wc-text">HISTORIAL DE RESULTADOS</h3>
                </div>

                @if(count($bloodworkResults) > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead>
                                <tr class="border-t border-wc-border">
                                    <th class="px-5 py-3 text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Prueba</th>
                                    <th class="px-5 py-3 text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Valor</th>
                                    <th class="px-5 py-3 text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Unidad</th>
                                    <th class="hidden px-5 py-3 text-xs font-medium uppercase tracking-wider text-wc-text-tertiary sm:table-cell">Referencia</th>
                                    <th class="px-5 py-3 text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Fecha</th>
                                    <th class="px-5 py-3 text-xs font-medium uppercase tracking-wider text-wc-text-tertiary"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($bloodworkResults as $result)
                                    <tr class="border-t border-wc-border">
                                        <td class="px-5 py-3 font-medium text-wc-text">{{ $result['test_name'] }}</td>
                                        <td class="px-5 py-3 font-data text-wc-text">{{ $result['value'] }}</td>
                                        <td class="px-5 py-3 text-wc-text-secondary">{{ $result['unit'] }}</td>
                                        <td class="hidden px-5 py-3 text-wc-text-tertiary sm:table-cell">{{ $result['reference_range'] ?? '-' }}</td>
                                        <td class="px-5 py-3 text-wc-text-secondary">{{ \Carbon\Carbon::parse($result['test_date'])->format('d/m/Y') }}</td>
                                        <td class="px-5 py-3">
                                            <button
                                                wire:click="deleteBloodwork({{ $result['id'] }})"
                                                wire:confirm="Eliminar este resultado?"
                                                class="text-xs text-red-400 hover:text-red-300 transition-colors"
                                            >
                                                Eliminar
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="border-t border-wc-border p-6 text-center">
                        <p class="text-sm text-wc-text-secondary">Aun no tienes resultados de laboratorio registrados.</p>
                        <p class="mt-1 text-xs text-wc-text-tertiary">Agrega tus resultados para llevar un seguimiento de tu salud.</p>
                    </div>
                @endif
            </div>
        </div>
        @endif
    @endif
</div>
