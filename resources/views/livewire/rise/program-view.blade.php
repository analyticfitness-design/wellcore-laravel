<div class="space-y-6">

    {{-- Page header --}}
    <div>
        <h1 class="font-display text-3xl tracking-wide text-wc-text sm:text-4xl">Mi Programa</h1>
        <p class="mt-1 text-sm text-wc-text-tertiary">Tu programa RISE personalizado de 12 semanas.</p>
    </div>

    @if($hasProgram)
        {{-- Program overview card --}}
        <div class="relative overflow-hidden rounded-card border border-amber-500/20 bg-gradient-to-r from-amber-500/5 via-amber-400/5 to-transparent p-5 sm:p-6">
            <div class="absolute -right-6 -top-6 h-24 w-24 rounded-full bg-amber-500/5"></div>
            <div class="absolute -right-2 -top-2 h-12 w-12 rounded-full bg-amber-500/10"></div>

            <div class="relative">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <h2 class="font-display text-xl tracking-wide text-wc-text">Programa RISE</h2>
                        <p class="mt-1 text-sm text-wc-text-tertiary">{{ $startDate }} — {{ $endDate }}</p>

                        {{-- Program attributes --}}
                        <div class="mt-3 flex flex-wrap gap-2">
                            @if($experienceLevel)
                                <span class="inline-flex items-center gap-1 rounded-full bg-wc-bg-secondary px-2.5 py-1 text-xs font-medium text-wc-text-secondary">
                                    <svg class="h-3 w-3 text-amber-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                                    </svg>
                                    {{ ucfirst($experienceLevel) }}
                                </span>
                            @endif
                            @if($trainingLocation)
                                <span class="inline-flex items-center gap-1 rounded-full bg-wc-bg-secondary px-2.5 py-1 text-xs font-medium text-wc-text-secondary">
                                    <svg class="h-3 w-3 text-amber-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                                    </svg>
                                    {{ ucfirst($trainingLocation) }}
                                </span>
                            @endif
                            @if($gender)
                                <span class="inline-flex items-center gap-1 rounded-full bg-wc-bg-secondary px-2.5 py-1 text-xs font-medium text-wc-text-secondary">
                                    <svg class="h-3 w-3 text-amber-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                                    </svg>
                                    {{ ucfirst($gender) }}
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="text-right">
                        <p class="font-data text-2xl font-bold text-amber-500">Semana {{ $currentWeek }}</p>
                        <p class="text-xs text-wc-text-tertiary">de 12</p>
                    </div>
                </div>

                {{-- Progress bar --}}
                <div class="mt-4">
                    <div class="h-2 w-full overflow-hidden rounded-full bg-wc-bg-secondary">
                        <div class="h-full rounded-full bg-gradient-to-r from-amber-500 to-amber-400 transition-all duration-500"
                             style="width: {{ $progressPct }}%"></div>
                    </div>
                    <p class="mt-1.5 text-xs text-wc-text-tertiary">{{ number_format($progressPct, 0) }}% completado</p>
                </div>
            </div>
        </div>

        {{-- Program content --}}
        @if($program)
            <div class="space-y-4">
                {{-- If program has weeks/phases --}}
                @if(isset($program['weeks']) && is_array($program['weeks']))
                    @foreach($program['weeks'] as $weekIndex => $week)
                        @php
                            $weekNum = $weekIndex + 1;
                            $isCurrentWeek = $weekNum === $currentWeek;
                        @endphp
                        <div class="rounded-card border {{ $isCurrentWeek ? 'border-amber-500/30 bg-amber-500/5' : 'border-wc-border bg-wc-bg-tertiary' }} p-5"
                             x-data="{ open: {{ $isCurrentWeek ? 'true' : 'false' }} }">
                            <button x-on:click="open = !open" class="flex w-full items-center justify-between text-left">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-8 w-8 items-center justify-center rounded-lg {{ $isCurrentWeek ? 'bg-amber-500/15 text-amber-500' : 'bg-wc-bg-secondary text-wc-text-tertiary' }}">
                                        <span class="font-data text-sm font-bold">{{ $weekNum }}</span>
                                    </div>
                                    <div>
                                        <h3 class="text-sm font-semibold text-wc-text">
                                            Semana {{ $weekNum }}
                                            @if($isCurrentWeek)
                                                <span class="ml-1 text-[10px] font-bold text-amber-500">ACTUAL</span>
                                            @endif
                                        </h3>
                                        @if(isset($week['title']))
                                            <p class="text-xs text-wc-text-tertiary">{{ $week['title'] }}</p>
                                        @endif
                                    </div>
                                </div>
                                <svg class="h-4 w-4 text-wc-text-tertiary transition-transform" :class="{ 'rotate-180': open }" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                                </svg>
                            </button>

                            <div x-show="open" x-collapse class="mt-4 space-y-3">
                                {{-- Training days --}}
                                @if(isset($week['days']) && is_array($week['days']))
                                    @foreach($week['days'] as $dayIndex => $day)
                                        <div class="rounded-lg border border-wc-border bg-wc-bg-secondary p-4">
                                            <h4 class="text-sm font-semibold text-wc-text">
                                                {{ $day['name'] ?? 'Dia ' . ($dayIndex + 1) }}
                                            </h4>
                                            @if(isset($day['focus']))
                                                <p class="mt-0.5 text-xs text-amber-500">{{ $day['focus'] }}</p>
                                            @endif

                                            @if(isset($day['exercises']) && is_array($day['exercises']))
                                                <div class="mt-3 space-y-2">
                                                    @foreach($day['exercises'] as $exercise)
                                                        <div class="flex items-start gap-3 text-sm">
                                                            <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded bg-amber-500/10 text-[10px] font-bold text-amber-500">
                                                                {{ $loop->iteration }}
                                                            </span>
                                                            <div class="flex-1">
                                                                <p class="font-medium text-wc-text">{{ $exercise['name'] ?? 'Ejercicio' }}</p>
                                                                <p class="text-xs text-wc-text-tertiary">
                                                                    @if(isset($exercise['sets'])){{ $exercise['sets'] }} series @endif
                                                                    @if(isset($exercise['reps']))x {{ $exercise['reps'] }} reps @endif
                                                                    @if(isset($exercise['rest']))— Descanso: {{ $exercise['rest'] }}@endif
                                                                </p>
                                                                @if(isset($exercise['notes']))
                                                                    <p class="mt-0.5 text-xs italic text-wc-text-tertiary">{{ $exercise['notes'] }}</p>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                @endif

                                {{-- Week notes --}}
                                @if(isset($week['notes']))
                                    <div class="rounded-lg border border-amber-500/10 bg-amber-500/5 p-3">
                                        <p class="text-xs text-wc-text-secondary">{{ $week['notes'] }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach

                {{-- If program is a flat structure (training_days at root) --}}
                @elseif(isset($program['training_days']) && is_array($program['training_days']))
                    <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-5">
                        <h2 class="font-display text-lg tracking-wide text-wc-text">Plan de entrenamiento</h2>
                        <div class="mt-4 space-y-3">
                            @foreach($program['training_days'] as $dayIndex => $day)
                                <div class="rounded-lg border border-wc-border bg-wc-bg-secondary p-4">
                                    <h4 class="text-sm font-semibold text-wc-text">
                                        {{ $day['name'] ?? 'Dia ' . ($dayIndex + 1) }}
                                    </h4>
                                    @if(isset($day['focus']))
                                        <p class="mt-0.5 text-xs text-amber-500">{{ $day['focus'] }}</p>
                                    @endif

                                    @if(isset($day['exercises']) && is_array($day['exercises']))
                                        <div class="mt-3 space-y-2">
                                            @foreach($day['exercises'] as $exercise)
                                                <div class="flex items-start gap-3 text-sm">
                                                    <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded bg-amber-500/10 text-[10px] font-bold text-amber-500">
                                                        {{ $loop->iteration }}
                                                    </span>
                                                    <div class="flex-1">
                                                        <p class="font-medium text-wc-text">{{ $exercise['name'] ?? 'Ejercicio' }}</p>
                                                        <p class="text-xs text-wc-text-tertiary">
                                                            @if(isset($exercise['sets'])){{ $exercise['sets'] }} series @endif
                                                            @if(isset($exercise['reps']))x {{ $exercise['reps'] }} reps @endif
                                                            @if(isset($exercise['rest']))— Descanso: {{ $exercise['rest'] }}@endif
                                                        </p>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>

                {{-- Fallback: render the raw program data --}}
                @else
                    <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-5">
                        <h2 class="font-display text-lg tracking-wide text-wc-text">Detalles del programa</h2>
                        <div class="mt-4 space-y-3">
                            @foreach($program as $key => $value)
                                <div class="rounded-lg border border-wc-border bg-wc-bg-secondary p-4">
                                    <p class="text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">{{ str_replace('_', ' ', $key) }}</p>
                                    @if(is_array($value))
                                        <pre class="mt-2 whitespace-pre-wrap font-mono text-xs text-wc-text-secondary">{{ json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                    @else
                                        <p class="mt-1 text-sm text-wc-text">{{ $value }}</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        @else
            <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-6 text-center">
                <svg class="mx-auto h-10 w-10 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                </svg>
                <p class="mt-3 text-sm font-medium text-wc-text">Programa aun no asignado</p>
                <p class="mt-1 text-xs text-wc-text-tertiary">Tu coach esta preparando tu programa personalizado.</p>
            </div>
        @endif

    @else
        {{-- No active program --}}
        <div class="rounded-card border border-amber-500/20 bg-amber-500/5 p-8 text-center">
            <svg class="mx-auto h-12 w-12 text-amber-500/40" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
            </svg>
            <p class="mt-4 text-base font-medium text-wc-text">No tienes un programa RISE activo</p>
            <p class="mt-2 text-sm text-wc-text-tertiary">Contacta a tu coach para activar tu programa RISE de 12 semanas.</p>
        </div>
    @endif

</div>
