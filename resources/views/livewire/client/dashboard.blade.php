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

    {{-- Stats cards --}}
    <div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
        {{-- Streak --}}
        <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-4 sm:p-5">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Racha</span>
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-orange-500/10">
                    <svg class="h-4 w-4 text-orange-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.362 5.214A8.252 8.252 0 0 1 12 21 8.25 8.25 0 0 1 6.038 7.047 8.287 8.287 0 0 0 9 9.601a8.983 8.983 0 0 1 3.361-6.867 8.21 8.21 0 0 0 3 2.48Z" />
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
        </div>

        {{-- Days trained this week --}}
        <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-4 sm:p-5">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">Esta semana</span>
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-sky-500/10">
                    <svg class="h-4 w-4 text-sky-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                    </svg>
                </div>
            </div>
            <p class="mt-3 font-data text-3xl font-bold text-wc-text">{{ $trainedThisWeek }}</p>
            <p class="mt-0.5 text-xs text-wc-text-tertiary">dias entrenados</p>
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
