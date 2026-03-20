<div class="space-y-6">

    {{-- Page header --}}
    <div>
        <h1 class="font-display text-3xl tracking-wide text-wc-text sm:text-4xl">Tracking Diario</h1>
        <p class="mt-1 text-sm text-wc-text-tertiary">Registra tu progreso diario en el programa RISE.</p>
    </div>

    {{-- Weekly overview grid --}}
    <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-5">
        <h2 class="font-display text-lg tracking-wide text-wc-text">Esta semana</h2>
        <p class="mt-1 text-xs text-wc-text-tertiary">Semana {{ now()->isoWeek() }} del {{ now()->year }}</p>

        <div class="mt-5 grid grid-cols-7 gap-2 sm:gap-3">
            @foreach($weekDays as $day)
                <div class="flex flex-col items-center gap-1.5 rounded-lg p-2 {{ $day['isToday'] ? 'bg-amber-500/5 ring-1 ring-amber-500/20' : '' }}">
                    <span class="text-[11px] font-medium {{ $day['isToday'] ? 'text-amber-500 font-semibold' : 'text-wc-text-tertiary' }}">
                        {{ $day['label'] }}
                    </span>

                    {{-- Training status --}}
                    @if($day['trainingDone'])
                        <div class="flex h-9 w-9 items-center justify-center rounded-full bg-emerald-500/15">
                            <svg class="h-4 w-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                            </svg>
                        </div>
                    @elseif($day['hasEntry'])
                        <div class="flex h-9 w-9 items-center justify-center rounded-full bg-wc-accent/10">
                            <svg class="h-4 w-4 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                            </svg>
                        </div>
                    @else
                        <div class="flex h-9 w-9 items-center justify-center rounded-full border border-wc-border {{ $day['isToday'] ? '!border-amber-500/30' : '' }}">
                            @if($day['isToday'])
                                <div class="h-1.5 w-1.5 rounded-full bg-amber-500"></div>
                            @endif
                        </div>
                    @endif

                    {{-- Nutrition dot --}}
                    <div class="h-1 w-1 rounded-full {{ $day['nutritionDone'] ? 'bg-amber-400' : 'bg-wc-border' }}"></div>

                    {{-- Water --}}
                    @if($day['waterLiters'])
                        <span class="text-[10px] font-medium text-sky-400">{{ $day['waterLiters'] }}L</span>
                    @endif
                </div>
            @endforeach
        </div>

        <div class="mt-4 flex items-center gap-4 text-[11px] text-wc-text-tertiary">
            <div class="flex items-center gap-1.5">
                <div class="h-2 w-2 rounded-full bg-emerald-500/40"></div>
                Entreno
            </div>
            <div class="flex items-center gap-1.5">
                <div class="h-2 w-2 rounded-full bg-amber-400"></div>
                Nutricion
            </div>
            <div class="flex items-center gap-1.5">
                <div class="h-2 w-2 rounded-full bg-sky-400"></div>
                Agua
            </div>
        </div>
    </div>

    {{-- Today's form --}}
    <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-5 sm:p-6">
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
            {{-- Training + Nutrition toggles --}}
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                {{-- Training done --}}
                <label class="flex cursor-pointer items-center gap-4 rounded-lg border p-4 transition-colors
                              {{ $trainingDone ? 'border-emerald-500/30 bg-emerald-500/5' : 'border-wc-border hover:border-wc-text-tertiary' }}">
                    <input type="checkbox" wire:model.live="trainingDone" class="sr-only">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg {{ $trainingDone ? 'bg-emerald-500/15' : 'bg-wc-bg-secondary' }}">
                        @if($trainingDone)
                            <svg class="h-5 w-5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                            </svg>
                        @else
                            <svg class="h-5 w-5 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                            </svg>
                        @endif
                    </div>
                    <div>
                        <p class="text-sm font-medium text-wc-text">Entrenamiento</p>
                        <p class="text-xs text-wc-text-tertiary">{{ $trainingDone ? 'Completado' : 'No completado' }}</p>
                    </div>
                </label>

                {{-- Nutrition done --}}
                <label class="flex cursor-pointer items-center gap-4 rounded-lg border p-4 transition-colors
                              {{ $nutritionDone ? 'border-amber-500/30 bg-amber-500/5' : 'border-wc-border hover:border-wc-text-tertiary' }}">
                    <input type="checkbox" wire:model.live="nutritionDone" class="sr-only">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg {{ $nutritionDone ? 'bg-amber-500/15' : 'bg-wc-bg-secondary' }}">
                        @if($nutritionDone)
                            <svg class="h-5 w-5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                            </svg>
                        @else
                            <svg class="h-5 w-5 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8.25v-1.5m0 1.5c-1.355 0-2.697.056-4.024.166C6.845 8.51 6 9.473 6 10.608v2.513m6-4.871c1.355 0 2.697.056 4.024.166C17.155 8.51 18 9.473 18 10.608v2.513M15 8.25v-1.5m-6 1.5v-1.5m12 9.75-1.5.75a3.354 3.354 0 0 1-3 0 3.354 3.354 0 0 0-3 0 3.354 3.354 0 0 1-3 0 3.354 3.354 0 0 0-3 0 3.354 3.354 0 0 1-3 0L3 16.5m15-3.379a48.474 48.474 0 0 0-6-.371c-2.032 0-4.034.126-6 .371m12 0c.39.049.777.102 1.163.16 1.07.16 1.837 1.094 1.837 2.175v5.169c0 .621-.504 1.125-1.125 1.125H4.125A1.125 1.125 0 0 1 3 20.625v-5.17c0-1.08.768-2.014 1.837-2.174A47.78 47.78 0 0 1 6 13.12M12.265 3.11a.375.375 0 1 1-.53 0L12 2.845l.265.265Z" />
                            </svg>
                        @endif
                    </div>
                    <div>
                        <p class="text-sm font-medium text-wc-text">Nutricion</p>
                        <p class="text-xs text-wc-text-tertiary">{{ $nutritionDone ? 'Plan seguido' : 'No completado' }}</p>
                    </div>
                </label>
            </div>

            {{-- Water + Sleep --}}
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label for="waterLiters" class="block text-sm font-medium text-wc-text-secondary">Agua (litros)</label>
                    <input type="number" step="0.1" min="0" max="10" id="waterLiters"
                           wire:model="waterLiters"
                           placeholder="Ej: 2.5"
                           class="mt-1.5 block w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2.5 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500">
                    @error('waterLiters')
                        <p class="mt-1 text-xs text-wc-accent">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="sleepHours" class="block text-sm font-medium text-wc-text-secondary">Sueno (horas)</label>
                    <input type="number" step="0.5" min="0" max="24" id="sleepHours"
                           wire:model="sleepHours"
                           placeholder="Ej: 7.5"
                           class="mt-1.5 block w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2.5 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500">
                    @error('sleepHours')
                        <p class="mt-1 text-xs text-wc-accent">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Note --}}
            <div>
                <label for="note" class="block text-sm font-medium text-wc-text-secondary">Nota del dia (opcional)</label>
                <textarea id="note"
                          wire:model="note"
                          rows="3"
                          placeholder="Como te sentiste hoy? Algo que destacar?"
                          class="mt-1.5 block w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2.5 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500 resize-none"></textarea>
                @error('note')
                    <p class="mt-1 text-xs text-wc-accent">{{ $message }}</p>
                @enderror
            </div>

            {{-- Submit --}}
            <div class="flex items-center gap-3 pt-2">
                <button type="submit"
                        class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-amber-500 to-amber-600 px-5 py-2.5 text-sm font-medium text-white hover:from-amber-600 hover:to-amber-700 transition-all">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                    </svg>
                    {{ $todaySaved ? 'Actualizar registro' : 'Guardar registro' }}
                </button>

                <div wire:loading wire:target="save" class="text-xs text-wc-text-tertiary">
                    Guardando...
                </div>
            </div>
        </form>
    </div>

</div>
