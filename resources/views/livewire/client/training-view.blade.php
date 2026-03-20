<div class="space-y-6">
    {{-- Title --}}
    <div class="flex items-center justify-between">
        <h1 class="font-display text-3xl tracking-wide text-wc-text">MI ENTRENAMIENTO</h1>
    </div>

    {{-- Week Navigation --}}
    <div class="flex items-center justify-between rounded-[--radius-card] border border-wc-border bg-wc-bg-tertiary p-4">
        <button
            wire:click="previousWeek"
            class="flex h-10 w-10 items-center justify-center rounded-[--radius-button] border border-wc-border bg-wc-bg-secondary text-wc-text-secondary hover:text-wc-text transition-colors"
            aria-label="Semana anterior"
        >
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
            </svg>
        </button>

        <div class="text-center">
            <span class="font-data text-lg font-semibold text-wc-text">Semana {{ $week }}</span>
            <span class="ml-2 text-sm text-wc-text-secondary">{{ $year }}</span>
            @if (! $isCurrentWeek)
                <button
                    wire:click="goToCurrentWeek"
                    class="ml-3 rounded-full bg-wc-accent/10 px-3 py-1 text-xs font-medium text-wc-accent hover:bg-wc-accent/20 transition-colors"
                >
                    Hoy
                </button>
            @endif
        </div>

        <button
            wire:click="nextWeek"
            class="flex h-10 w-10 items-center justify-center rounded-[--radius-button] border border-wc-border bg-wc-bg-secondary text-wc-text-secondary hover:text-wc-text transition-colors"
            aria-label="Semana siguiente"
        >
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
            </svg>
        </button>
    </div>

    {{-- Weekly Grid --}}
    <div class="grid grid-cols-7 gap-2 sm:gap-3">
        @foreach ($days as $day)
            <button
                wire:click="toggleDay('{{ $day['date'] }}')"
                class="group flex flex-col items-center gap-2 rounded-[--radius-card] border p-3 sm:p-4 transition-all
                    {{ $day['isToday'] ? 'border-wc-accent/50 bg-wc-accent/5' : 'border-wc-border bg-wc-bg-tertiary hover:border-wc-text-tertiary' }}"
            >
                {{-- Day Name --}}
                <span class="text-xs font-medium uppercase tracking-wider text-wc-text-tertiary">
                    {{ $day['dayName'] }}
                </span>

                {{-- Day Number --}}
                <span class="font-data text-lg font-semibold {{ $day['isToday'] ? 'text-wc-accent' : 'text-wc-text' }}">
                    {{ $day['dayNumber'] }}
                </span>

                {{-- Toggle Circle --}}
                <div class="flex h-10 w-10 items-center justify-center rounded-full transition-all
                    {{ $day['completed']
                        ? 'bg-emerald-500 text-white'
                        : 'border-2 border-wc-border text-wc-text-tertiary group-hover:border-wc-text-secondary' }}"
                >
                    @if ($day['completed'])
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                        </svg>
                    @else
                        <svg class="h-5 w-5 opacity-40" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m6-6H6" />
                        </svg>
                    @endif
                </div>
            </button>
        @endforeach
    </div>

    {{-- Stats Row --}}
    <div class="rounded-[--radius-card] border border-wc-border bg-wc-bg-tertiary p-5">
        {{-- Completion Text --}}
        <div class="mb-3 flex items-center justify-between">
            <span class="text-sm font-medium text-wc-text-secondary">
                <span class="font-data text-lg font-semibold text-wc-text">{{ $completedCount }}</span>
                de 7 dias completados
            </span>
            <span class="font-data text-sm font-semibold text-wc-accent">
                {{ $completedCount > 0 ? round(($completedCount / 7) * 100) : 0 }}%
            </span>
        </div>

        {{-- Progress Bar --}}
        <div class="h-2 w-full overflow-hidden rounded-full bg-wc-bg-secondary">
            <div
                class="h-full rounded-full bg-wc-accent transition-all duration-500"
                style="width: {{ round(($completedCount / 7) * 100) }}%"
            ></div>
        </div>

        {{-- Extra Stats --}}
        <div class="mt-4 grid grid-cols-2 gap-4 border-t border-wc-border pt-4">
            <div>
                <p class="text-xs text-wc-text-tertiary">Sesiones esta semana</p>
                <p class="font-data text-2xl font-semibold text-wc-text">{{ $completedCount }}</p>
            </div>
            <div>
                <p class="text-xs text-wc-text-tertiary">Sesiones este mes</p>
                <p class="font-data text-2xl font-semibold text-wc-text">{{ $monthSessions }}</p>
            </div>
        </div>
    </div>
</div>
