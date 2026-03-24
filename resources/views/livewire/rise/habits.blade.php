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
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-wc-accent/15">
                    <svg class="h-4 w-4 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
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
                <div class="flex flex-col items-center gap-1.5 rounded-lg p-2 {{ $day['isToday'] ? 'bg-wc-accent/5 ring-1 ring-wc-accent/20' : '' }}">
                    <span class="text-[11px] font-medium {{ $day['isToday'] ? 'text-wc-accent font-semibold' : 'text-wc-text-tertiary' }}">
                        {{ $day['label'] }}
                    </span>

                    @if($day['hasEntry'])
                        @php
                            $pct = $day['total'] > 0 ? round(($day['habitCount'] / $day['total']) * 100) : 0;
                            $color = $pct >= 80 ? 'bg-emerald-500/15 text-emerald-500' : ($pct >= 40 ? 'bg-wc-accent/15 text-wc-accent' : 'bg-wc-accent/10 text-wc-accent');
                        @endphp
                        <div class="flex h-9 w-9 items-center justify-center rounded-full {{ $color }}">
                            <span class="text-xs font-bold">{{ $day['habitCount'] }}</span>
                        </div>
                    @else
                        <div class="flex h-9 w-9 items-center justify-center rounded-full border border-wc-border {{ $day['isToday'] ? '!border-wc-accent/30' : '' }}">
                            @if($day['isToday'])
                                <div class="h-1.5 w-1.5 rounded-full bg-wc-accent"></div>
                            @endif
                        </div>
                    @endif

                    {{-- Micro indicators --}}
                    <div class="flex items-center gap-0.5">
                        <div class="h-1 w-1 rounded-full {{ ($day['habit_0'] ?? false) ? 'bg-emerald-500' : 'bg-wc-border' }}"></div>
                        <div class="h-1 w-1 rounded-full {{ ($day['habit_1'] ?? false) ? 'bg-amber-400' : 'bg-wc-border' }}"></div>
                        <div class="h-1 w-1 rounded-full {{ ($day['habit_2'] ?? false) ? 'bg-violet-400' : 'bg-wc-border' }}"></div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Micro indicators legend --}}
        <div class="mt-4 flex flex-wrap items-center gap-4 text-[11px] text-wc-text-tertiary">
            @foreach(array_slice($habitsPlan, 0, 3) as $i => $habito)
                @php
                    $dotColors = ['bg-emerald-500/50', 'bg-amber-400', 'bg-violet-400'];
                @endphp
                <div class="flex items-center gap-1.5">
                    <div class="h-2 w-2 rounded-full {{ $dotColors[$i] ?? 'bg-wc-accent' }}"></div>
                    {{ $habito['nombre'] ?? 'Habito '.($i+1) }}
                </div>
            @endforeach
            @if(empty($habitsPlan))
                <div class="flex items-center gap-1.5"><div class="h-2 w-2 rounded-full bg-emerald-500/50"></div>Entreno</div>
                <div class="flex items-center gap-1.5"><div class="h-2 w-2 rounded-full bg-amber-400"></div>Nutricion</div>
                <div class="flex items-center gap-1.5"><div class="h-2 w-2 rounded-full bg-violet-400"></div>Meditacion</div>
            @endif
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

        {{-- Progress indicator --}}
        @if(!empty($habitsPlan))
            @php
                $completedCount = count(array_filter($habitsDone));
                $totalHabits = count($habitsPlan);
                $pct = $totalHabits > 0 ? round(($completedCount / $totalHabits) * 100) : 0;
            @endphp
            <div class="flex items-center justify-between text-xs text-wc-text-tertiary mb-2 mt-5">
                <span>{{ $completedCount }}/{{ $totalHabits }} habitos</span>
                <span>{{ $pct }}%</span>
            </div>
            <div class="h-1.5 w-full rounded-full bg-wc-border overflow-hidden mb-4">
                <div class="h-full rounded-full bg-wc-accent transition-all duration-300" style="width: {{ $pct }}%"></div>
            </div>
        @endif

        <form wire:submit="save" class="mt-6 space-y-5">
            {{-- Dynamic habits from program --}}
            @if(!empty($habitsPlan))
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($habitsPlan as $index => $habito)
                        @php
                            $done = !empty($habitsDone[$index]);
                            $nombre = $habito['nombre'] ?? 'Habito';
                            $frecuencia = $habito['frecuencia'] ?? null;
                            $colorClasses = [
                                0 => ['border' => 'border-emerald-500/30', 'bg' => 'bg-emerald-500/5', 'icon_bg' => 'bg-emerald-500/15', 'icon' => 'text-emerald-500'],
                                1 => ['border' => 'border-wc-accent/30', 'bg' => 'bg-wc-accent/5', 'icon_bg' => 'bg-wc-accent/15', 'icon' => 'text-wc-accent'],
                                2 => ['border' => 'border-violet-500/30', 'bg' => 'bg-violet-500/5', 'icon_bg' => 'bg-violet-500/15', 'icon' => 'text-violet-500'],
                                3 => ['border' => 'border-sky-500/30', 'bg' => 'bg-sky-500/5', 'icon_bg' => 'bg-sky-500/15', 'icon' => 'text-sky-500'],
                                4 => ['border' => 'border-amber-500/30', 'bg' => 'bg-amber-500/5', 'icon_bg' => 'bg-amber-500/15', 'icon' => 'text-amber-500'],
                                5 => ['border' => 'border-orange-500/30', 'bg' => 'bg-orange-500/5', 'icon_bg' => 'bg-orange-500/15', 'icon' => 'text-orange-500'],
                            ];
                            $c = $colorClasses[$index % 6];
                        @endphp
                        <label class="flex cursor-pointer items-center gap-3 rounded-lg border p-4 transition-colors
                                      {{ $done ? $c['border'].' '.$c['bg'] : 'border-wc-border hover:border-wc-text-tertiary' }}">
                            <input type="checkbox"
                                   wire:model.live="habitsDone.{{ $index }}"
                                   value="1"
                                   class="sr-only">
                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg {{ $done ? $c['icon_bg'] : 'bg-wc-bg-secondary' }}">
                                @if($done)
                                    <svg class="h-4.5 w-4.5 {{ $c['icon'] }}" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                    </svg>
                                @else
                                    <svg class="h-4.5 w-4.5 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                    </svg>
                                @endif
                            </div>
                            <div>
                                <p class="text-sm font-medium text-wc-text">{{ $nombre }}</p>
                                <p class="text-xs text-wc-text-tertiary">
                                    {{ $done ? 'Completado' : 'Pendiente' }}
                                    @if($frecuencia) · {{ $frecuencia }} @endif
                                </p>
                            </div>
                        </label>
                    @endforeach
                </div>
            @else
                {{-- No habits defined in program yet --}}
                <div class="rounded-xl border border-wc-border bg-wc-bg-secondary px-4 py-6 text-center">
                    <p class="text-sm text-wc-text-tertiary">Tu coach esta definiendo tus habitos personalizados. Apareceran aqui pronto.</p>
                    <p class="mt-2 text-xs text-wc-text-tertiary">Por ahora, registra agua, sueno y pasos abajo.</p>
                </div>
            @endif

            {{-- Water + Sleep + Steps --}}
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div>
                    <label for="water" class="block text-sm font-medium text-wc-text-secondary">Agua (litros)</label>
                    <input type="number" step="0.1" min="0" max="10" id="water"
                           wire:model="water"
                           placeholder="Ej: 2.5"
                           class="mt-1.5 block w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-4 py-2 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent">
                    @error('water')
                        <p class="mt-1 text-xs text-wc-accent">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="sleep" class="block text-sm font-medium text-wc-text-secondary">Sueno (horas)</label>
                    <input type="number" step="0.5" min="0" max="24" id="sleep"
                           wire:model="sleep"
                           placeholder="Ej: 7.5"
                           class="mt-1.5 block w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-4 py-2 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent">
                    @error('sleep')
                        <p class="mt-1 text-xs text-wc-accent">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="steps" class="block text-sm font-medium text-wc-text-secondary">Pasos</label>
                    <input type="number" min="0" max="100000" id="steps"
                           wire:model="steps"
                           placeholder="Ej: 8000"
                           class="mt-1.5 block w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-4 py-2 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent">
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
                          class="mt-1.5 block w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-4 py-2 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent resize-none"></textarea>
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
