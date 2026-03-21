{{-- Shared plan content preview partial --}}
{{-- Supports drag-and-drop reordering when $reorderable is true --}}
@php
    $plan = $plan ?? [];
    $reorderable = $reorderable ?? false;
    // Wire method prefixes differ by context:
    //   '' => reorderExercise, moveExercise, etc. (generated plan / admin view)
    //   'preview' => reorderPreviewExercise, movePreviewExercise, etc. (coach preview modal)
    $wirePrefix = $wirePrefix ?? '';
    // Build actual method names
    $fnReorderEx = $wirePrefix ? 'reorder' . ucfirst($wirePrefix) . 'Exercise' : 'reorderExercise';
    $fnMoveEx = $wirePrefix ? 'move' . ucfirst($wirePrefix) . 'Exercise' : 'moveExercise';
    $fnReorderFood = $wirePrefix ? 'reorder' . ucfirst($wirePrefix) . 'Food' : 'reorderFood';
    $fnMoveFood = $wirePrefix ? 'move' . ucfirst($wirePrefix) . 'Food' : 'moveFood';
    $fnReorderHabit = $wirePrefix ? 'reorder' . ucfirst($wirePrefix) . 'Habit' : 'reorderHabit';
    $fnMoveHabit = $wirePrefix ? 'move' . ucfirst($wirePrefix) . 'Habit' : 'moveHabit';
@endphp

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
            @if ($reorderable)
                <span class="ml-auto inline-flex items-center gap-1 rounded-full bg-wc-accent/10 px-2 py-0.5 text-[10px] font-medium text-wc-accent">
                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                    </svg>
                    Arrastra para reordenar
                </span>
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

        @foreach ($plan['weeks'] as $weekIdx => $week)
            <div class="rounded-lg border border-wc-border p-3">
                <h4 class="font-medium text-wc-text">Semana {{ $week['week'] ?? '?' }}</h4>
                @if (isset($week['focus']))
                    <p class="text-xs text-wc-text-tertiary">{{ $week['focus'] }}</p>
                @endif

                @if (!empty($week['sessions']))
                    <div class="mt-2 space-y-2">
                        @foreach ($week['sessions'] as $sessionIdx => $session)
                            <div class="rounded bg-wc-bg-secondary/50 p-2">
                                <p class="font-medium text-wc-text text-xs">
                                    Dia {{ $session['day'] ?? '?' }} — {{ $session['name'] ?? 'Sesion' }}
                                </p>
                                @if (!empty($session['muscle_groups']))
                                    <p class="text-xs text-wc-text-tertiary">{{ implode(', ', $session['muscle_groups']) }}</p>
                                @endif
                                @if (!empty($session['exercises']))
                                    @if ($reorderable)
                                        {{-- Drag-and-drop exercise list --}}
                                        <div class="mt-1.5 space-y-0.5"
                                             x-data="{
                                                 dragging: null,
                                                 dragOver: null,
                                                 handleDragStart(e, index) {
                                                     this.dragging = index;
                                                     e.dataTransfer.effectAllowed = 'move';
                                                     e.dataTransfer.setData('text/plain', index);
                                                     e.target.closest('[data-exercise-item]').classList.add('opacity-50');
                                                 },
                                                 handleDragOver(e, index) {
                                                     e.preventDefault();
                                                     e.dataTransfer.dropEffect = 'move';
                                                     this.dragOver = index;
                                                 },
                                                 handleDrop(e, index) {
                                                     e.preventDefault();
                                                     if (this.dragging !== null && this.dragging !== index) {
                                                         $wire.{{ $fnReorderEx }}({{ $weekIdx }}, {{ $sessionIdx }}, this.dragging, index);
                                                     }
                                                     this.dragging = null;
                                                     this.dragOver = null;
                                                 },
                                                 handleDragEnd(e) {
                                                     e.target.closest('[data-exercise-item]').classList.remove('opacity-50');
                                                     this.dragging = null;
                                                     this.dragOver = null;
                                                 }
                                             }">
                                            @foreach ($session['exercises'] as $exIdx => $ex)
                                                <div data-exercise-item
                                                     class="group flex items-center gap-1.5 rounded-md px-1.5 py-1 transition-all duration-150"
                                                     :class="{
                                                         'border-t-2 border-wc-accent': dragOver === {{ $exIdx }} && dragging !== null && dragging !== {{ $exIdx }} && dragging > {{ $exIdx }},
                                                         'border-b-2 border-wc-accent': dragOver === {{ $exIdx }} && dragging !== null && dragging !== {{ $exIdx }} && dragging < {{ $exIdx }},
                                                         'bg-wc-bg-tertiary/50': dragOver === {{ $exIdx }} && dragging !== null && dragging !== {{ $exIdx }},
                                                     }"
                                                     @dragover="handleDragOver($event, {{ $exIdx }})"
                                                     @drop="handleDrop($event, {{ $exIdx }})"
                                                     @dragleave="if (dragOver === {{ $exIdx }}) dragOver = null">

                                                    {{-- Drag handle (desktop) --}}
                                                    <div class="hidden md:flex shrink-0 cursor-grab active:cursor-grabbing text-wc-text-tertiary hover:text-wc-text-secondary opacity-0 group-hover:opacity-100 transition-opacity"
                                                         draggable="true"
                                                         @dragstart="handleDragStart($event, {{ $exIdx }})"
                                                         @dragend="handleDragEnd($event)"
                                                         role="img"
                                                         aria-label="Arrastrar para reordenar {{ $ex['name'] ?? 'ejercicio' }}">
                                                        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                                            <path d="M7 2a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm6 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4zM7 8a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm6 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4zM7 14a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm6 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4z"/>
                                                        </svg>
                                                    </div>

                                                    {{-- Mobile up/down arrows --}}
                                                    <div class="flex flex-col gap-0.5 md:hidden shrink-0">
                                                        <button wire:click="{{ $fnMoveEx }}({{ $weekIdx }}, {{ $sessionIdx }}, {{ $exIdx }}, 'up')"
                                                                class="text-wc-text-tertiary hover:text-wc-accent disabled:opacity-30 disabled:cursor-not-allowed transition-colors"
                                                                @if($exIdx === 0) disabled @endif
                                                                aria-label="Mover arriba {{ $ex['name'] ?? 'ejercicio' }}"
                                                                title="Mover arriba">
                                                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75l7.5-7.5 7.5 7.5" />
                                                            </svg>
                                                        </button>
                                                        <button wire:click="{{ $fnMoveEx }}({{ $weekIdx }}, {{ $sessionIdx }}, {{ $exIdx }}, 'down')"
                                                                class="text-wc-text-tertiary hover:text-wc-accent disabled:opacity-30 disabled:cursor-not-allowed transition-colors"
                                                                @if($exIdx === count($session['exercises']) - 1) disabled @endif
                                                                aria-label="Mover abajo {{ $ex['name'] ?? 'ejercicio' }}"
                                                                title="Mover abajo">
                                                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                                            </svg>
                                                        </button>
                                                    </div>

                                                    {{-- Exercise info --}}
                                                    <div class="flex-1 min-w-0">
                                                        <p class="text-xs text-wc-text-secondary">
                                                            <span class="font-medium text-wc-text">{{ $ex['name'] ?? '?' }}</span>
                                                            — {{ $ex['sets'] ?? '?' }}x{{ $ex['reps'] ?? '?' }}
                                                            @if (isset($ex['rest'])) ({{ $ex['rest'] }}) @endif
                                                            @if (isset($ex['rpe'])) RPE {{ $ex['rpe'] }} @endif
                                                        </p>
                                                    </div>

                                                    {{-- Position badge --}}
                                                    <span class="shrink-0 rounded bg-wc-bg-tertiary px-1.5 py-0.5 text-[10px] font-data text-wc-text-tertiary">
                                                        {{ $exIdx + 1 }}
                                                    </span>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        {{-- Read-only exercise list --}}
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
            @foreach ($plan['meal_plan'] as $mealIdx => $meal)
                <div class="rounded-lg border border-wc-border p-3">
                    <div class="flex items-center justify-between">
                        <h4 class="font-medium text-wc-text text-xs">{{ $meal['name'] ?? 'Comida' }}</h4>
                        <div class="flex items-center gap-2 text-xs text-wc-text-tertiary">
                            @if (isset($meal['time'])) <span>{{ $meal['time'] }}</span> @endif
                            @if (isset($meal['calories'])) <span>{{ $meal['calories'] }} kcal</span> @endif
                        </div>
                    </div>
                    @if (!empty($meal['foods']))
                        @if ($reorderable)
                            {{-- Drag-and-drop food list --}}
                            <div class="mt-1.5 space-y-0.5"
                                 x-data="{
                                     dragging: null,
                                     dragOver: null,
                                     handleDragStart(e, index) {
                                         this.dragging = index;
                                         e.dataTransfer.effectAllowed = 'move';
                                         e.dataTransfer.setData('text/plain', index);
                                         e.target.closest('[data-food-item]').classList.add('opacity-50');
                                     },
                                     handleDragOver(e, index) {
                                         e.preventDefault();
                                         e.dataTransfer.dropEffect = 'move';
                                         this.dragOver = index;
                                     },
                                     handleDrop(e, index) {
                                         e.preventDefault();
                                         if (this.dragging !== null && this.dragging !== index) {
                                             $wire.{{ $fnReorderFood }}({{ $mealIdx }}, this.dragging, index);
                                         }
                                         this.dragging = null;
                                         this.dragOver = null;
                                     },
                                     handleDragEnd(e) {
                                         e.target.closest('[data-food-item]').classList.remove('opacity-50');
                                         this.dragging = null;
                                         this.dragOver = null;
                                     }
                                 }">
                                @foreach ($meal['foods'] as $foodIdx => $food)
                                    <div data-food-item
                                         class="group flex items-center gap-1.5 rounded-md px-1.5 py-1 transition-all duration-150"
                                         :class="{
                                             'border-t-2 border-wc-accent': dragOver === {{ $foodIdx }} && dragging !== null && dragging !== {{ $foodIdx }} && dragging > {{ $foodIdx }},
                                             'border-b-2 border-wc-accent': dragOver === {{ $foodIdx }} && dragging !== null && dragging !== {{ $foodIdx }} && dragging < {{ $foodIdx }},
                                             'bg-wc-bg-tertiary/50': dragOver === {{ $foodIdx }} && dragging !== null && dragging !== {{ $foodIdx }},
                                         }"
                                         @dragover="handleDragOver($event, {{ $foodIdx }})"
                                         @drop="handleDrop($event, {{ $foodIdx }})"
                                         @dragleave="if (dragOver === {{ $foodIdx }}) dragOver = null">

                                        {{-- Drag handle (desktop) --}}
                                        <div class="hidden md:flex shrink-0 cursor-grab active:cursor-grabbing text-wc-text-tertiary hover:text-wc-text-secondary opacity-0 group-hover:opacity-100 transition-opacity"
                                             draggable="true"
                                             @dragstart="handleDragStart($event, {{ $foodIdx }})"
                                             @dragend="handleDragEnd($event)"
                                             role="img"
                                             aria-label="Arrastrar para reordenar {{ $food['name'] ?? 'alimento' }}">
                                            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M7 2a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm6 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4zM7 8a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm6 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4zM7 14a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm6 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4z"/>
                                            </svg>
                                        </div>

                                        {{-- Mobile up/down arrows --}}
                                        <div class="flex flex-col gap-0.5 md:hidden shrink-0">
                                            <button wire:click="{{ $fnMoveFood }}({{ $mealIdx }}, {{ $foodIdx }}, 'up')"
                                                    class="text-wc-text-tertiary hover:text-wc-accent disabled:opacity-30 disabled:cursor-not-allowed transition-colors"
                                                    @if($foodIdx === 0) disabled @endif
                                                    aria-label="Mover arriba {{ $food['name'] ?? 'alimento' }}"
                                                    title="Mover arriba">
                                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75l7.5-7.5 7.5 7.5" />
                                                </svg>
                                            </button>
                                            <button wire:click="{{ $fnMoveFood }}({{ $mealIdx }}, {{ $foodIdx }}, 'down')"
                                                    class="text-wc-text-tertiary hover:text-wc-accent disabled:opacity-30 disabled:cursor-not-allowed transition-colors"
                                                    @if($foodIdx === count($meal['foods']) - 1) disabled @endif
                                                    aria-label="Mover abajo {{ $food['name'] ?? 'alimento' }}"
                                                    title="Mover abajo">
                                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                                </svg>
                                            </button>
                                        </div>

                                        {{-- Food info --}}
                                        <div class="flex-1 min-w-0">
                                            <p class="text-xs text-wc-text-secondary">
                                                {{ $food['name'] ?? '?' }} — {{ $food['quantity'] ?? '?' }}
                                                <span class="text-wc-text-tertiary">(P:{{ $food['protein'] ?? 0 }} C:{{ $food['carbs'] ?? 0 }} G:{{ $food['fat'] ?? 0 }})</span>
                                            </p>
                                        </div>

                                        {{-- Position badge --}}
                                        <span class="shrink-0 rounded bg-wc-bg-tertiary px-1.5 py-0.5 text-[10px] font-data text-wc-text-tertiary">
                                            {{ $foodIdx + 1 }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="mt-1 space-y-0.5">
                                @foreach ($meal['foods'] as $food)
                                    <p class="text-xs text-wc-text-secondary">
                                        {{ $food['name'] ?? '?' }} — {{ $food['quantity'] ?? '?' }}
                                        <span class="text-wc-text-tertiary">(P:{{ $food['protein'] ?? 0 }} C:{{ $food['carbs'] ?? 0 }} G:{{ $food['fat'] ?? 0 }})</span>
                                    </p>
                                @endforeach
                            </div>
                        @endif
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
            @if ($reorderable)
                {{-- Drag-and-drop habits list --}}
                <div class="space-y-2"
                     x-data="{
                         dragging: null,
                         dragOver: null,
                         handleDragStart(e, index) {
                             this.dragging = index;
                             e.dataTransfer.effectAllowed = 'move';
                             e.dataTransfer.setData('text/plain', index);
                             e.target.closest('[data-habit-item]').classList.add('opacity-50');
                         },
                         handleDragOver(e, index) {
                             e.preventDefault();
                             e.dataTransfer.dropEffect = 'move';
                             this.dragOver = index;
                         },
                         handleDrop(e, index) {
                             e.preventDefault();
                             if (this.dragging !== null && this.dragging !== index) {
                                 $wire.{{ $fnReorderHabit }}(this.dragging, index);
                             }
                             this.dragging = null;
                             this.dragOver = null;
                         },
                         handleDragEnd(e) {
                             e.target.closest('[data-habit-item]').classList.remove('opacity-50');
                             this.dragging = null;
                             this.dragOver = null;
                         }
                     }">
                    @foreach ($plan['habits'] as $habitIdx => $habit)
                        <div data-habit-item
                             class="group rounded-lg border border-wc-border p-3 transition-all duration-150"
                             :class="{
                                 'border-t-2 border-wc-accent': dragOver === {{ $habitIdx }} && dragging !== null && dragging !== {{ $habitIdx }} && dragging > {{ $habitIdx }},
                                 'border-b-2 border-wc-accent': dragOver === {{ $habitIdx }} && dragging !== null && dragging !== {{ $habitIdx }} && dragging < {{ $habitIdx }},
                                 'bg-wc-bg-tertiary/50': dragOver === {{ $habitIdx }} && dragging !== null && dragging !== {{ $habitIdx }},
                             }"
                             @dragover="handleDragOver($event, {{ $habitIdx }})"
                             @drop="handleDrop($event, {{ $habitIdx }})"
                             @dragleave="if (dragOver === {{ $habitIdx }}) dragOver = null">

                            <div class="flex items-start gap-2">
                                {{-- Drag handle (desktop) --}}
                                <div class="hidden md:flex mt-0.5 shrink-0 cursor-grab active:cursor-grabbing text-wc-text-tertiary hover:text-wc-text-secondary opacity-0 group-hover:opacity-100 transition-opacity"
                                     draggable="true"
                                     @dragstart="handleDragStart($event, {{ $habitIdx }})"
                                     @dragend="handleDragEnd($event)"
                                     role="img"
                                     aria-label="Arrastrar para reordenar {{ $habit['habit'] ?? 'habito' }}">
                                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M7 2a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm6 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4zM7 8a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm6 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4zM7 14a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm6 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4z"/>
                                    </svg>
                                </div>

                                {{-- Mobile up/down arrows --}}
                                <div class="flex flex-col gap-0.5 md:hidden shrink-0 mt-0.5">
                                    <button wire:click="{{ $fnMoveHabit }}({{ $habitIdx }}, 'up')"
                                            class="text-wc-text-tertiary hover:text-wc-accent disabled:opacity-30 disabled:cursor-not-allowed transition-colors"
                                            @if($habitIdx === 0) disabled @endif
                                            aria-label="Mover arriba {{ $habit['habit'] ?? 'habito' }}"
                                            title="Mover arriba">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75l7.5-7.5 7.5 7.5" />
                                        </svg>
                                    </button>
                                    <button wire:click="{{ $fnMoveHabit }}({{ $habitIdx }}, 'down')"
                                            class="text-wc-text-tertiary hover:text-wc-accent disabled:opacity-30 disabled:cursor-not-allowed transition-colors"
                                            @if($habitIdx === count($plan['habits']) - 1) disabled @endif
                                            aria-label="Mover abajo {{ $habit['habit'] ?? 'habito' }}"
                                            title="Mover abajo">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                        </svg>
                                    </button>
                                </div>

                                {{-- Habit content --}}
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-start justify-between">
                                        <div>
                                            <h4 class="font-medium text-wc-text text-xs">{{ $habit['habit'] ?? '?' }}</h4>
                                            <p class="text-xs text-wc-text-tertiary">{{ $habit['area'] ?? '' }} — {{ $habit['frequency'] ?? '' }}</p>
                                        </div>
                                        <div class="flex items-center gap-1.5 shrink-0">
                                            @if (isset($habit['target']))
                                                <span class="rounded bg-wc-bg-secondary px-2 py-0.5 text-xs text-wc-text-secondary">{{ $habit['target'] }}</span>
                                            @endif
                                            <span class="rounded bg-wc-bg-tertiary px-1.5 py-0.5 text-[10px] font-data text-wc-text-tertiary">
                                                {{ $habitIdx + 1 }}
                                            </span>
                                        </div>
                                    </div>
                                    @if (!empty($habit['weeks_progression']))
                                        <div class="mt-2 flex flex-wrap gap-1">
                                            @foreach ($habit['weeks_progression'] as $wp)
                                                <span class="rounded bg-wc-bg-secondary px-1.5 py-0.5 text-xs text-wc-text-tertiary">S{{ $wp['week'] ?? '?' }}: {{ $wp['goal'] ?? '' }}</span>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
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
