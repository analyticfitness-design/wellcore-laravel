<div>
    <div class="mb-8">
        <h1 class="font-display text-3xl tracking-wide text-wc-text">MI PLAN</h1>
        <p class="mt-1 text-sm text-wc-text-secondary">Tu programacion personalizada asignada por tu coach</p>
    </div>

    {{-- Tabs --}}
    <div class="mb-6 flex gap-1 rounded-xl border border-wc-border bg-wc-bg-secondary p-1">
        @foreach(['entrenamiento' => 'Entrenamiento', 'nutricion' => 'Nutricion', 'suplementacion' => 'Suplementos'] as $key => $label)
            <button
                wire:click="setTab('{{ $key }}')"
                class="flex-1 rounded-lg px-4 py-2.5 text-sm font-medium transition-colors {{ $activeTab === $key ? 'bg-wc-bg-tertiary text-wc-text shadow-sm' : 'text-wc-text-tertiary hover:text-wc-text-secondary' }}"
            >
                {{ $label }}
            </button>
        @endforeach
    </div>

    {{-- Tab Content --}}
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
                                            @if(is_array($ej) && isset($ej['series']))
                                                <span class="font-data text-sm text-wc-text-secondary">
                                                    {{ $ej['series'] }}x{{ $ej['repeticiones'] ?? $ej['reps'] ?? '' }}
                                                </span>
                                            @endif
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
    @elseif($activeTab === 'nutricion')
        @if($nutritionPlan)
            <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-6">
                <pre class="text-sm text-wc-text-secondary whitespace-pre-wrap">{{ json_encode($nutritionPlan, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
            </div>
        @else
            <x-empty-state title="SIN PLAN DE NUTRICION" message="Tu coach aun no ha asignado un plan de nutricion." />
        @endif
    @elseif($activeTab === 'suplementacion')
        @if($supplementPlan)
            <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-6">
                <pre class="text-sm text-wc-text-secondary whitespace-pre-wrap">{{ json_encode($supplementPlan, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
            </div>
        @else
            <x-empty-state title="SIN PLAN DE SUPLEMENTACION" message="Tu coach aun no ha asignado un plan de suplementacion." />
        @endif
    @endif
</div>
