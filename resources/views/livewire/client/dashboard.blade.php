<div class="space-y-6">

    {{-- Greeting section --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="font-display text-3xl tracking-wide text-wc-text sm:text-4xl">
                {{ $greeting }}, {{ $clientName }}
            </h1>
            @if($planLabel)
                <div class="mt-2 flex items-center gap-2">
                    <span class="inline-flex rounded-full bg-wc-accent/10 px-3 py-1 text-xs font-semibold text-wc-accent">
                        Plan {{ $planLabel }}
                    </span>
                </div>
            @endif
        </div>

        {{-- Quick actions (desktop) --}}
        <div class="hidden sm:flex items-center gap-2">
            <a href="{{ route('client.plan') }}"
               class="inline-flex items-center gap-2 rounded-lg bg-wc-accent px-4 py-2 text-sm font-medium text-white hover:bg-wc-accent-hover transition-colors">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Registrar entrenamiento
            </a>
            <a href="{{ route('client.checkin') }}"
               class="inline-flex items-center gap-2 rounded-lg border border-wc-border bg-wc-bg-tertiary px-4 py-2 text-sm font-medium text-wc-text hover:bg-wc-bg-secondary transition-colors">
                Hacer check-in
            </a>
        </div>
    </div>

    {{-- Plan alert --}}
    @if(!$hasActivePlan)
        <div class="flex items-start gap-4 rounded-xl border border-wc-accent/30 bg-wc-accent/5 p-4">
            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-wc-accent/10">
                <svg class="h-5 w-5 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-wc-text">No tienes un plan activo</p>
                <p class="mt-0.5 text-xs text-wc-text-tertiary">Contacta a tu coach para que te asigne un plan de entrenamiento o nutricion.</p>
            </div>
            <a href="{{ route('client.chat') }}"
               class="shrink-0 inline-flex items-center gap-1.5 rounded-lg bg-wc-accent px-3 py-1.5 text-xs font-medium text-white hover:bg-wc-accent-hover transition-colors">
                Contactar coach
            </a>
        </div>
    @else
        <div class="flex items-center gap-3 rounded-xl border border-wc-border bg-wc-bg-tertiary px-4 py-3">
            <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-emerald-500/10">
                <svg class="h-4 w-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
            </div>
            <span class="text-xs text-wc-text-tertiary">
                Plan
                @if($planPhase) <span class="font-semibold capitalize text-wc-text">{{ $planPhase }}</span> @endif
                activo &mdash; dia <span class="font-semibold text-wc-text">{{ $planDaysActive }}</span>
            </span>
        </div>
    @endif

    {{-- Stats cards --}}
    <div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
        {{-- Streak with Flame Animation --}}
        <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-4 sm:p-5">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Racha</span>
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-orange-500/10 {{ $streakDays >= 3 ? 'flame-active' : '' }}">
                    <svg class="h-4 w-4 text-orange-500" viewBox="0 0 24 24" fill="currentColor">
                        <path fill-rule="evenodd" d="M12.963 2.286a.75.75 0 0 0-1.071-.136 9.742 9.742 0 0 0-3.539 6.176A7.547 7.547 0 0 1 6.648 6.61a.75.75 0 0 0-1.152.082A9 9 0 1 0 15.68 4.534a7.46 7.46 0 0 1-2.717-2.248ZM15.75 14.25a3.75 3.75 0 1 1-7.313-1.172c.628.465 1.35.81 2.133 1a5.99 5.99 0 0 1 1.925-3.546 3.75 3.75 0 0 1 3.255 3.718Z" clip-rule="evenodd" />
                    </svg>
                </div>
            </div>
            <p class="mt-3 font-data text-3xl font-bold text-wc-text">{{ $streakDays }}</p>
            <p class="mt-0.5 text-xs text-wc-text-tertiary">dias consecutivos</p>
        </div>

        {{-- Check-ins this month --}}
        <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-4 sm:p-5">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Check-ins</span>
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-500/10">
                    <svg class="h-4 w-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </div>
            </div>
            <p class="mt-3 font-data text-3xl font-bold text-wc-text">{{ $checkinsThisMonth }}</p>
            <p class="mt-0.5 text-xs text-wc-text-tertiary">este mes</p>
        </div>

        {{-- XP + Level --}}
        <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-4 sm:p-5">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Nivel {{ $level }}</span>
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-violet-500/10">
                    <svg class="h-4 w-4 text-violet-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z" />
                    </svg>
                </div>
            </div>
            <p class="mt-3 font-data text-3xl font-bold text-wc-text">{{ number_format($xpTotal) }}</p>
            <p class="mt-0.5 text-xs text-wc-text-tertiary">XP total</p>
            {{-- XP Progress bar --}}
            <div class="mt-3">
                <div class="h-1.5 w-full overflow-hidden rounded-full bg-wc-bg-secondary">
                    <div class="h-full rounded-full bg-violet-500 transition-all duration-500"
                         style="width: {{ $xpProgress }}%"></div>
                </div>
                <p class="mt-1 text-[10px] text-wc-text-tertiary">
                    {{ number_format($xpTotal % $xpForNextLevel) }} / {{ number_format($xpForNextLevel) }} XP
                </p>
            </div>
        </div>

        {{-- Days trained this week — Progress Ring --}}
        <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-4 sm:p-5">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Esta semana</span>
            </div>
            <div class="mt-3 flex items-center gap-3">
                {{-- SVG Progress Ring ~60px --}}
                @php
                    $circumference = 251;
                    $progressOffset = $circumference - ($circumference * min($trainedThisWeek, 7) / 7);
                @endphp
                <svg width="60" height="60" viewBox="0 0 86 86" class="shrink-0">
                    {{-- Background track --}}
                    <circle cx="43" cy="43" r="40"
                            fill="none"
                            stroke="var(--color-wc-border)"
                            stroke-width="6" />
                    {{-- Animated progress arc --}}
                    <circle cx="43" cy="43" r="40"
                            fill="none"
                            stroke="#DC2626"
                            stroke-width="6"
                            stroke-linecap="round"
                            stroke-dasharray="{{ $circumference }}"
                            stroke-dashoffset="{{ $progressOffset }}"
                            class="progress-ring-circle" />
                    {{-- Center text --}}
                    <text x="43" y="43"
                          text-anchor="middle"
                          dominant-baseline="central"
                          fill="var(--color-wc-text)"
                          font-family="var(--font-data)"
                          font-size="18"
                          font-weight="700">{{ $trainedThisWeek }}/7</text>
                </svg>
                <div>
                    <p class="text-xs text-wc-text-tertiary">dias entrenados</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Daily missions --}}
    <div>
        <div class="mb-3 flex items-center justify-between">
            <h2 class="font-display text-lg tracking-wide text-wc-text">Misiones del dia</h2>
            <span class="text-xs text-wc-text-tertiary">
                {{ collect($dailyMissions)->where('completed', true)->count() }}/{{ count($dailyMissions) }} completadas
            </span>
        </div>
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
            @foreach($dailyMissions as $mission)
                <a href="{{ $mission['route'] }}"
                   class="group flex items-center gap-3 rounded-xl border p-4 transition-colors
                          {{ $mission['completed']
                              ? 'border-emerald-500/30 bg-emerald-500/5 hover:bg-emerald-500/10'
                              : 'border-wc-border bg-wc-bg-tertiary hover:bg-wc-bg-secondary' }}">

                    {{-- Status icon --}}
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full
                                {{ $mission['completed'] ? 'bg-emerald-500/15' : 'border-2 border-wc-border' }}">
                        @if($mission['completed'])
                            <svg class="h-5 w-5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                            </svg>
                        @else
                            @if($mission['icon'] === 'dumbbell')
                                <svg class="h-4 w-4 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                                </svg>
                            @elseif($mission['icon'] === 'checkin')
                                <svg class="h-4 w-4 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                </svg>
                            @elseif($mission['icon'] === 'scale')
                                <svg class="h-4 w-4 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v17.25m0 0c-1.472 0-2.882.265-4.185.75M12 20.25c1.472 0 2.882.265 4.185.75M18.75 4.97A48.416 48.416 0 0 0 12 4.5c-2.291 0-4.545.16-6.75.47m13.5 0c1.01.143 2.01.317 3 .52m-3-.52 2.62 10.726c.122.499-.106 1.028-.589 1.202a5.988 5.988 0 0 1-2.031.352 5.988 5.988 0 0 1-2.031-.352c-.483-.174-.711-.703-.589-1.202L18.75 4.97Zm-16.5.52c.99-.203 1.99-.377 3-.52m0 0 2.62 10.726c.122.499-.106 1.028-.589 1.202a5.989 5.989 0 0 1-2.031.352 5.989 5.989 0 0 1-2.031-.352c-.483-.174-.711-.703-.589-1.202L5.25 4.97Z" />
                                </svg>
                            @else
                                <svg class="h-4 w-4 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                                </svg>
                            @endif
                        @endif
                    </div>

                    {{-- Text --}}
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-medium leading-tight
                                  {{ $mission['completed'] ? 'text-emerald-600 dark:text-emerald-400' : 'text-wc-text' }}">
                            {{ $mission['title'] }}
                        </p>
                        <p class="mt-0.5 text-[11px] text-wc-text-tertiary">
                            {{ $mission['completed'] ? 'Completado' : 'Pendiente' }}
                        </p>
                    </div>

                    {{-- Arrow --}}
                    @if(!$mission['completed'])
                        <svg class="h-4 w-4 shrink-0 text-wc-text-tertiary group-hover:text-wc-text transition-colors" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                        </svg>
                    @endif
                </a>
            @endforeach
        </div>
    </div>

    {{-- Weekly overview + Recent activity --}}
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">

        {{-- Weekly training overview --}}
        <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-5 lg:col-span-2">
            <h2 class="font-display text-lg tracking-wide text-wc-text">Semana de entrenamiento</h2>
            <p class="mt-1 text-xs text-wc-text-tertiary">Semana {{ now()->isoWeek() }} del {{ now()->year }}</p>

            <div class="mt-5 flex items-center justify-between gap-2 sm:justify-start sm:gap-4">
                @foreach($weekDays as $day)
                    <div class="flex flex-col items-center gap-2">
                        <span class="text-[11px] font-medium text-wc-text-tertiary {{ $day['isToday'] ? '!text-wc-accent font-semibold' : '' }}">
                            {{ $day['label'] }}
                        </span>
                        @if($day['completed'])
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-500/15 sm:h-12 sm:w-12">
                                <svg class="h-5 w-5 text-emerald-500 sm:h-6 sm:w-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                </svg>
                            </div>
                        @else
                            <div class="flex h-10 w-10 items-center justify-center rounded-full border-2 border-wc-border sm:h-12 sm:w-12 {{ $day['isToday'] ? '!border-wc-accent/40' : '' }}">
                                @if($day['isToday'])
                                    <div class="h-2 w-2 rounded-full bg-wc-accent"></div>
                                @endif
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

            <div class="mt-5 flex items-center gap-4 text-xs text-wc-text-tertiary">
                <div class="flex items-center gap-1.5">
                    <div class="h-2.5 w-2.5 rounded-full bg-emerald-500/40"></div>
                    Completado
                </div>
                <div class="flex items-center gap-1.5">
                    <div class="h-2.5 w-2.5 rounded-full border border-wc-border"></div>
                    Pendiente
                </div>
                <div class="flex items-center gap-1.5">
                    <div class="h-2.5 w-2.5 rounded-full bg-wc-accent"></div>
                    Hoy
                </div>
            </div>
        </div>

        {{-- Recent activity --}}
        <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-5">
            <h2 class="font-display text-lg tracking-wide text-wc-text">Actividad reciente</h2>

            @if(count($recentActivity) > 0)
                <ul class="mt-4 space-y-3">
                    @foreach($recentActivity as $activity)
                        <li class="flex items-start gap-3">
                            {{-- Icon --}}
                            <div class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-full
                                @if($activity['type'] === 'training') bg-emerald-500/10
                                @elseif($activity['type'] === 'checkin') bg-sky-500/10
                                @elseif($activity['type'] === 'payment') bg-violet-500/10
                                @else bg-wc-bg-secondary
                                @endif
                            ">
                                @if($activity['type'] === 'training')
                                    <svg class="h-3.5 w-3.5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                    </svg>
                                @elseif($activity['type'] === 'checkin')
                                    <svg class="h-3.5 w-3.5 text-sky-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                    </svg>
                                @elseif($activity['type'] === 'payment')
                                    <svg class="h-3.5 w-3.5 text-violet-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" />
                                    </svg>
                                @endif
                            </div>

                            {{-- Text --}}
                            <div class="min-w-0 flex-1">
                                <p class="text-sm text-wc-text">{{ $activity['description'] }}</p>
                                <p class="text-xs text-wc-text-tertiary">{{ $activity['timeAgo'] }}</p>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @else
                <div class="mt-6 flex flex-col items-center py-4 text-center">
                    <svg class="h-8 w-8 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                    <p class="mt-2 text-sm text-wc-text-tertiary">Sin actividad reciente</p>
                </div>
            @endif
        </div>
    </div>

    {{-- Quick actions (mobile) --}}
    <div class="grid grid-cols-1 gap-3 sm:hidden">
        <a href="{{ route('client.plan') }}"
           class="flex items-center justify-center gap-2 rounded-lg bg-wc-accent px-4 py-3 text-sm font-medium text-white hover:bg-wc-accent-hover transition-colors">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Registrar entrenamiento
        </a>
        <a href="{{ route('client.checkin') }}"
           class="flex items-center justify-center gap-2 rounded-lg border border-wc-border bg-wc-bg-tertiary px-4 py-3 text-sm font-medium text-wc-text hover:bg-wc-bg-secondary transition-colors">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
            Hacer check-in
        </a>
        <a href="{{ route('client.plan') }}"
           class="flex items-center justify-center gap-2 rounded-lg border border-wc-border bg-wc-bg-tertiary px-4 py-3 text-sm font-medium text-wc-text hover:bg-wc-bg-secondary transition-colors">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
            </svg>
            Ver mi plan
        </a>
    </div>

</div>
