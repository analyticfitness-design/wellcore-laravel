<div class="space-y-6"
     x-data
     x-on:habits-saved.window="$dispatch('notify', { message: 'Habitos guardados correctamente', type: 'success' })">

    {{-- Page header --}}
    <div>
        <h1 class="font-display text-3xl tracking-wide text-wc-text sm:text-4xl">HABITOS DIARIOS</h1>
        <p class="mt-1 text-sm text-wc-text-tertiary">{{ now()->translatedFormat('l d \d\e F, Y') }}</p>
    </div>

    {{-- Stats summary --}}
    <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
        {{-- Racha actual --}}
        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-4">
            <div class="flex items-center gap-2">
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-amber-500/15">
                    <svg class="h-4 w-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.362 5.214A8.252 8.252 0 0 1 12 21 8.25 8.25 0 0 1 6.038 7.047 8.287 8.287 0 0 0 9 9.601a8.983 8.983 0 0 1 3.361-6.867 8.21 8.21 0 0 0 3 2.48Z" />
                    </svg>
                </div>
                <div>
                    <p class="font-data text-2xl font-bold text-wc-text">{{ $currentStreak }}</p>
                    <p class="text-[10px] uppercase tracking-wider text-wc-text-tertiary">Racha</p>
                </div>
            </div>
        </div>

        {{-- Dias completados --}}
        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-4">
            <div class="flex items-center gap-2">
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-500/15">
                    <svg class="h-4 w-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </div>
                <div>
                    <p class="font-data text-2xl font-bold text-wc-text">{{ $completedDays }}</p>
                    <p class="text-[10px] uppercase tracking-wider text-wc-text-tertiary">Dias</p>
                </div>
            </div>
        </div>

        {{-- Promedio agua --}}
        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-4">
            <div class="flex items-center gap-2">
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-sky-500/15">
                    <svg class="h-4 w-4 text-sky-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" />
                    </svg>
                </div>
                <div>
                    <p class="font-data text-2xl font-bold text-wc-text">{{ $avgWater ?? '--' }}<span class="text-xs font-normal text-wc-text-tertiary">L</span></p>
                    <p class="text-[10px] uppercase tracking-wider text-wc-text-tertiary">Agua prom.</p>
                </div>
            </div>
        </div>

        {{-- Promedio sueno --}}
        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-4">
            <div class="flex items-center gap-2">
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-violet-500/15">
                    <svg class="h-4 w-4 text-violet-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z" />
                    </svg>
                </div>
                <div>
                    <p class="font-data text-2xl font-bold text-wc-text">{{ $avgSleep ?? '--' }}<span class="text-xs font-normal text-wc-text-tertiary">h</span></p>
                    <p class="text-[10px] uppercase tracking-wider text-wc-text-tertiary">Sueno prom.</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Weekly grid --}}
    <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
        <h2 class="font-display text-lg tracking-wide text-wc-text">Esta semana</h2>
        <p class="mt-1 text-xs text-wc-text-tertiary">Semana {{ now()->isoWeek() }} del {{ now()->year }}</p>

        <div class="mt-5 grid grid-cols-7 gap-2 sm:gap-3">
            @foreach($weekDays as $day)
                <div class="flex flex-col items-center gap-1.5 rounded-lg p-2 {{ $day['isToday'] ? 'bg-amber-500/5 ring-1 ring-amber-500/20' : '' }}">
                    <span class="text-[11px] font-medium {{ $day['isToday'] ? 'text-amber-500 font-semibold' : 'text-wc-text-tertiary' }}">
                        {{ $day['label'] }}
                    </span>

                    @if($day['hasEntry'])
                        @php
                            $pct = $day['total'] > 0 ? round(($day['habitCount'] / $day['total']) * 100) : 0;
                            $color = $pct >= 80 ? 'bg-emerald-500/15 text-emerald-500' : ($pct >= 40 ? 'bg-amber-500/15 text-amber-500' : 'bg-wc-accent/10 text-wc-accent');
                        @endphp
                        <div class="flex h-9 w-9 items-center justify-center rounded-full {{ $color }}">
                            <span class="text-xs font-bold">{{ $day['habitCount'] }}</span>
                        </div>
                    @else
                        <div class="flex h-9 w-9 items-center justify-center rounded-full border border-wc-border {{ $day['isToday'] ? '!border-amber-500/30' : '' }}">
                            @if($day['isToday'])
                                <div class="h-1.5 w-1.5 rounded-full bg-amber-500"></div>
                            @endif
                        </div>
                    @endif

                    {{-- Micro indicators --}}
                    <div class="flex items-center gap-0.5">
                        <div class="h-1 w-1 rounded-full {{ $day['training'] ? 'bg-emerald-500' : 'bg-wc-border' }}"></div>
                        <div class="h-1 w-1 rounded-full {{ $day['nutrition'] ? 'bg-amber-400' : 'bg-wc-border' }}"></div>
                        <div class="h-1 w-1 rounded-full {{ $day['meditation'] ? 'bg-violet-400' : 'bg-wc-border' }}"></div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-4 flex flex-wrap items-center gap-4 text-[11px] text-wc-text-tertiary">
            <div class="flex items-center gap-1.5">
                <div class="h-2 w-2 rounded-full bg-emerald-500/50"></div>
                Entreno
            </div>
            <div class="flex items-center gap-1.5">
                <div class="h-2 w-2 rounded-full bg-amber-400"></div>
                Nutricion
            </div>
            <div class="flex items-center gap-1.5">
                <div class="h-2 w-2 rounded-full bg-violet-400"></div>
                Meditacion
            </div>
        </div>
    </div>

    {{-- Today's form --}}
    <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5 sm:p-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-display text-lg tracking-wide text-wc-text">Hoy — {{ now()->translatedFormat('l d M') }}</h2>
                @if($todaySaved && $savedAt)
                    <p class="mt-0.5 text-xs text-emerald-500">Guardado a las {{ $savedAt }}</p>
                @endif
            </div>
            @if($todaySaved)
                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-500/10 px-2.5 py-0.5 text-xs font-medium text-emerald-500">
                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                    </svg>
                    Registrado
                </span>
            @endif
        </div>

        <form wire:submit="save" class="mt-6 space-y-5">
            {{-- Checkboxes: Training + Nutrition + Meditation --}}
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                {{-- Training --}}
                <label class="flex cursor-pointer items-center gap-3 rounded-lg border p-4 transition-colors
                              {{ $training ? 'border-emerald-500/30 bg-emerald-500/5' : 'border-wc-border hover:border-wc-text-tertiary' }}">
                    <input type="checkbox" wire:model.live="training" class="sr-only">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg {{ $training ? 'bg-emerald-500/15' : 'bg-wc-bg-secondary' }}">
                        @if($training)
                            <svg class="h-4.5 w-4.5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                            </svg>
                        @else
                            <svg class="h-4.5 w-4.5 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                            </svg>
                        @endif
                    </div>
                    <div>
                        <p class="text-sm font-medium text-wc-text">Entrenamiento</p>
                        <p class="text-xs text-wc-text-tertiary">{{ $training ? 'Completado' : 'Pendiente' }}</p>
                    </div>
                </label>

                {{-- Nutrition --}}
                <label class="flex cursor-pointer items-center gap-3 rounded-lg border p-4 transition-colors
                              {{ $nutrition ? 'border-amber-500/30 bg-amber-500/5' : 'border-wc-border hover:border-wc-text-tertiary' }}">
                    <input type="checkbox" wire:model.live="nutrition" class="sr-only">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg {{ $nutrition ? 'bg-amber-500/15' : 'bg-wc-bg-secondary' }}">
                        @if($nutrition)
                            <svg class="h-4.5 w-4.5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                            </svg>
                        @else
                            <svg class="h-4.5 w-4.5 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8.25v-1.5m0 1.5c-1.355 0-2.697.056-4.024.166C6.845 8.51 6 9.473 6 10.608v2.513m6-4.871c1.355 0 2.697.056 4.024.166C17.155 8.51 18 9.473 18 10.608v2.513M15 8.25v-1.5m-6 1.5v-1.5m12 9.75-1.5.75a3.354 3.354 0 0 1-3 0 3.354 3.354 0 0 0-3 0 3.354 3.354 0 0 1-3 0 3.354 3.354 0 0 0-3 0 3.354 3.354 0 0 1-3 0L3 16.5m15-3.379a48.474 48.474 0 0 0-6-.371c-2.032 0-4.034.126-6 .371m12 0c.39.049.777.102 1.163.16 1.07.16 1.837 1.094 1.837 2.175v5.169c0 .621-.504 1.125-1.125 1.125H4.125A1.125 1.125 0 0 1 3 20.625v-5.17c0-1.08.768-2.014 1.837-2.174A47.78 47.78 0 0 1 6 13.12M12.265 3.11a.375.375 0 1 1-.53 0L12 2.845l.265.265Z" />
                            </svg>
                        @endif
                    </div>
                    <div>
                        <p class="text-sm font-medium text-wc-text">Nutricion</p>
                        <p class="text-xs text-wc-text-tertiary">{{ $nutrition ? 'Plan seguido' : 'Pendiente' }}</p>
                    </div>
                </label>

                {{-- Meditation --}}
                <label class="flex cursor-pointer items-center gap-3 rounded-lg border p-4 transition-colors
                              {{ $meditation ? 'border-violet-500/30 bg-violet-500/5' : 'border-wc-border hover:border-wc-text-tertiary' }}">
                    <input type="checkbox" wire:model.live="meditation" class="sr-only">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg {{ $meditation ? 'bg-violet-500/15' : 'bg-wc-bg-secondary' }}">
                        @if($meditation)
                            <svg class="h-4.5 w-4.5 text-violet-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                            </svg>
                        @else
                            <svg class="h-4.5 w-4.5 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" />
                            </svg>
                        @endif
                    </div>
                    <div>
                        <p class="text-sm font-medium text-wc-text">Meditacion</p>
                        <p class="text-xs text-wc-text-tertiary">{{ $meditation ? 'Completada' : 'Pendiente' }}</p>
                    </div>
                </label>
            </div>

            {{-- Water + Sleep + Steps --}}
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div>
                    <label for="water" class="block text-sm font-medium text-wc-text-secondary">Agua (litros)</label>
                    <input type="number" step="0.1" min="0" max="10" id="water"
                           wire:model="water"
                           placeholder="Ej: 2.5"
                           class="mt-1.5 block w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-4 py-2 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500">
                    @error('water')
                        <p class="mt-1 text-xs text-wc-accent">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="sleep" class="block text-sm font-medium text-wc-text-secondary">Sueno (horas)</label>
                    <input type="number" step="0.5" min="0" max="24" id="sleep"
                           wire:model="sleep"
                           placeholder="Ej: 7.5"
                           class="mt-1.5 block w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-4 py-2 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500">
                    @error('sleep')
                        <p class="mt-1 text-xs text-wc-accent">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="steps" class="block text-sm font-medium text-wc-text-secondary">Pasos</label>
                    <input type="number" min="0" max="100000" id="steps"
                           wire:model="steps"
                           placeholder="Ej: 8000"
                           class="mt-1.5 block w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-4 py-2 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500">
                    @error('steps')
                        <p class="mt-1 text-xs text-wc-accent">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Notes --}}
            <div>
                <label for="notes" class="block text-sm font-medium text-wc-text-secondary">Notas del dia (opcional)</label>
                <textarea id="notes"
                          wire:model="notes"
                          rows="3"
                          placeholder="Como te sentiste hoy? Algo que destacar?"
                          class="mt-1.5 block w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-4 py-2 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500 resize-none"></textarea>
                @error('notes')
                    <p class="mt-1 text-xs text-wc-accent">{{ $message }}</p>
                @enderror
            </div>

            {{-- Submit --}}
            <div class="flex items-center gap-3 pt-2">
                <button type="submit"
                        class="rounded-full bg-wc-accent px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-wc-accent/20 hover:bg-wc-accent-hover transition-all">
                    <span wire:loading.remove wire:target="save" class="flex items-center gap-2">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                        </svg>
                        {{ $todaySaved ? 'Actualizar habitos' : 'Guardar habitos' }}
                    </span>
                    <span wire:loading wire:target="save">Guardando...</span>
                </button>
            </div>
        </form>
    </div>
</div>
