{{-- Shared plan content preview partial --}}
@php $plan = $plan ?? []; @endphp

<div class="space-y-4 text-sm">
    {{-- Plan type header --}}
    @if (isset($plan['plan_type']))
        <div class="flex items-center gap-2 flex-wrap">
            @php
                $tc = match($plan['plan_type'] ?? '') {
                    'entrenamiento' => 'bg-sky-500/10 text-sky-500',
                    'nutricion' => 'bg-emerald-500/10 text-emerald-500',
                    'habitos' => 'bg-amber-500/10 text-amber-500',
                    default => 'bg-gray-500/10 text-gray-500',
                };
            @endphp
            <span class="rounded-full px-2.5 py-0.5 text-xs font-medium {{ $tc }}">{{ ucfirst($plan['plan_type']) }}</span>
            @if (isset($plan['methodology']))
                <span class="text-xs text-wc-text-secondary">{{ $plan['methodology'] }}</span>
            @elseif (isset($plan['approach']))
                <span class="text-xs text-wc-text-secondary">{{ $plan['approach'] }}</span>
            @endif
            @if (isset($plan['duration_weeks']))
                <span class="text-xs text-wc-text-tertiary">{{ $plan['duration_weeks'] }} sem</span>
            @endif
            @if (isset($plan['frequency']))
                <span class="text-xs text-wc-text-tertiary">{{ $plan['frequency'] }} dias/sem</span>
            @endif
        </div>
    @endif

    {{-- TRAINING PLAN --}}
    @if (($plan['plan_type'] ?? '') === 'entrenamiento' && !empty($plan['weeks']))
        @if (isset($plan['goal']) || isset($plan['level']))
            <div class="flex gap-4 text-xs text-wc-text-secondary">
                @if (isset($plan['goal'])) <span>Meta: <strong class="text-wc-text">{{ ucfirst($plan['goal']) }}</strong></span> @endif
                @if (isset($plan['level'])) <span>Nivel: <strong class="text-wc-text">{{ ucfirst($plan['level']) }}</strong></span> @endif
            </div>
        @endif

        @foreach ($plan['weeks'] as $week)
            <div class="rounded-lg border border-wc-border p-3">
                <h4 class="font-medium text-wc-text">Semana {{ $week['week'] ?? '?' }}</h4>
                @if (isset($week['focus']))
                    <p class="text-xs text-wc-text-tertiary">{{ $week['focus'] }}</p>
                @endif

                @if (!empty($week['sessions']))
                    <div class="mt-2 space-y-2">
                        @foreach ($week['sessions'] as $session)
                            <div class="rounded bg-wc-bg-secondary/50 p-2">
                                <p class="font-medium text-wc-text text-xs">
                                    Dia {{ $session['day'] ?? '?' }} — {{ $session['name'] ?? 'Sesion' }}
                                </p>
                                @if (!empty($session['muscle_groups']))
                                    <p class="text-xs text-wc-text-tertiary">{{ implode(', ', $session['muscle_groups']) }}</p>
                                @endif
                                @if (!empty($session['exercises']))
                                    <div class="mt-1 space-y-0.5">
                                        @foreach ($session['exercises'] as $ex)
                                            <p class="text-xs text-wc-text-secondary">
                                                <span class="text-wc-text">{{ $ex['name'] ?? '?' }}</span>
                                                — {{ $ex['sets'] ?? '?' }}x{{ $ex['reps'] ?? '?' }}
                                                @if (isset($ex['rest'])) ({{ $ex['rest'] }}) @endif
                                                @if (isset($ex['rpe'])) RPE {{ $ex['rpe'] }} @endif
                                            </p>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @endforeach

        @if (isset($plan['progression_notes']))
            <div class="text-xs text-wc-text-secondary">
                <strong class="text-wc-text">Progresion:</strong> {{ $plan['progression_notes'] }}
            </div>
        @endif
    @endif

    {{-- NUTRITION PLAN --}}
    @if (($plan['plan_type'] ?? '') === 'nutricion')
        @if (isset($plan['macros']))
            <div class="grid grid-cols-4 gap-2">
                <div class="rounded-lg bg-wc-bg-secondary p-2 text-center">
                    <p class="font-data text-lg font-bold text-wc-text">{{ $plan['calories'] ?? '-' }}</p>
                    <p class="text-xs text-wc-text-tertiary">kcal</p>
                </div>
                <div class="rounded-lg bg-wc-bg-secondary p-2 text-center">
                    <p class="font-data text-lg font-bold text-sky-500">{{ $plan['macros']['protein_g'] ?? '-' }}g</p>
                    <p class="text-xs text-wc-text-tertiary">Proteina</p>
                </div>
                <div class="rounded-lg bg-wc-bg-secondary p-2 text-center">
                    <p class="font-data text-lg font-bold text-amber-500">{{ $plan['macros']['carbs_g'] ?? '-' }}g</p>
                    <p class="text-xs text-wc-text-tertiary">Carbos</p>
                </div>
                <div class="rounded-lg bg-wc-bg-secondary p-2 text-center">
                    <p class="font-data text-lg font-bold text-emerald-500">{{ $plan['macros']['fat_g'] ?? '-' }}g</p>
                    <p class="text-xs text-wc-text-tertiary">Grasas</p>
                </div>
            </div>
        @endif

        @if (!empty($plan['meal_plan']))
            @foreach ($plan['meal_plan'] as $meal)
                <div class="rounded-lg border border-wc-border p-3">
                    <div class="flex items-center justify-between">
                        <h4 class="font-medium text-wc-text text-xs">{{ $meal['name'] ?? 'Comida' }}</h4>
                        <div class="flex items-center gap-2 text-xs text-wc-text-tertiary">
                            @if (isset($meal['time'])) <span>{{ $meal['time'] }}</span> @endif
                            @if (isset($meal['calories'])) <span>{{ $meal['calories'] }} kcal</span> @endif
                        </div>
                    </div>
                    @if (!empty($meal['foods']))
                        <div class="mt-1 space-y-0.5">
                            @foreach ($meal['foods'] as $food)
                                <p class="text-xs text-wc-text-secondary">
                                    {{ $food['name'] ?? '?' }} — {{ $food['quantity'] ?? '?' }}
                                    <span class="text-wc-text-tertiary">(P:{{ $food['protein'] ?? 0 }} C:{{ $food['carbs'] ?? 0 }} G:{{ $food['fat'] ?? 0 }})</span>
                                </p>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach
        @endif

        @if (!empty($plan['supplements']))
            <div class="text-xs text-wc-text-secondary">
                <strong class="text-wc-text">Suplementos:</strong> {{ implode(', ', $plan['supplements']) }}
            </div>
        @endif
    @endif

    {{-- HABITS PLAN --}}
    @if (($plan['plan_type'] ?? '') === 'habitos')
        @if (!empty($plan['focus_areas']))
            <div class="flex flex-wrap gap-1">
                @foreach ($plan['focus_areas'] as $area)
                    <span class="rounded-full bg-amber-500/10 px-2 py-0.5 text-xs text-amber-500">{{ $area }}</span>
                @endforeach
            </div>
        @endif

        @if (!empty($plan['habits']))
            @foreach ($plan['habits'] as $habit)
                <div class="rounded-lg border border-wc-border p-3">
                    <div class="flex items-start justify-between">
                        <div>
                            <h4 class="font-medium text-wc-text text-xs">{{ $habit['habit'] ?? '?' }}</h4>
                            <p class="text-xs text-wc-text-tertiary">{{ $habit['area'] ?? '' }} — {{ $habit['frequency'] ?? '' }}</p>
                        </div>
                        @if (isset($habit['target']))
                            <span class="shrink-0 rounded bg-wc-bg-secondary px-2 py-0.5 text-xs text-wc-text-secondary">{{ $habit['target'] }}</span>
                        @endif
                    </div>
                    @if (!empty($habit['weeks_progression']))
                        <div class="mt-2 flex flex-wrap gap-1">
                            @foreach ($habit['weeks_progression'] as $wp)
                                <span class="rounded bg-wc-bg-secondary px-1.5 py-0.5 text-xs text-wc-text-tertiary">S{{ $wp['week'] ?? '?' }}: {{ $wp['goal'] ?? '' }}</span>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach
        @endif

        @if (!empty($plan['daily_routine']))
            <div class="rounded-lg border border-wc-border p-3">
                <h4 class="font-medium text-wc-text text-xs mb-2">Rutina diaria</h4>
                <div class="grid grid-cols-3 gap-2 text-xs">
                    @foreach (['morning' => 'Manana', 'afternoon' => 'Tarde', 'evening' => 'Noche'] as $period => $periodLabel)
                        @if (!empty($plan['daily_routine'][$period]))
                            <div>
                                <p class="font-medium text-wc-text-secondary mb-1">{{ $periodLabel }}</p>
                                @foreach ($plan['daily_routine'][$period] as $activity)
                                    <p class="text-wc-text-tertiary">{{ $activity }}</p>
                                @endforeach
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        @endif
    @endif

    {{-- Fallback: show raw JSON for unknown plan types --}}
    @if (!in_array($plan['plan_type'] ?? '', ['entrenamiento', 'nutricion', 'habitos']))
        <pre class="text-xs text-wc-text-secondary whitespace-pre-wrap break-words">{{ json_encode($plan, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
    @endif
</div>
