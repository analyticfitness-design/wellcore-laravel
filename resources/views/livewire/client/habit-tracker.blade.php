<div class="space-y-6">
    {{-- Title --}}
    <div>
        <h1 class="font-display text-3xl tracking-wide text-wc-text">HABITOS DIARIOS</h1>
        <p class="mt-1 text-sm text-wc-text-secondary">
            {{ now()->locale('es')->isoFormat('dddd, D [de] MMMM') }}
        </p>
    </div>

    {{-- Today's Progress --}}
    <div class="rounded-[--radius-card] border border-wc-border bg-wc-bg-tertiary p-4">
        <div class="mb-3 flex items-center justify-between">
            <span class="text-sm font-medium text-wc-text-secondary">Progreso de hoy</span>
            <span class="font-data text-sm font-semibold text-wc-accent">{{ $completedToday }}/{{ $totalHabits }}</span>
        </div>
        <div class="h-2 w-full overflow-hidden rounded-full bg-wc-bg-secondary">
            <div
                class="h-full rounded-full transition-all duration-500 {{ $completedToday === $totalHabits ? 'bg-emerald-500' : 'bg-wc-accent' }}"
                style="width: {{ $totalHabits > 0 ? round(($completedToday / $totalHabits) * 100) : 0 }}%"
            ></div>
        </div>
    </div>

    {{-- Habit Cards --}}
    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($todayHabits as $type => $habit)
            <button
                wire:click="toggleHabit('{{ $type }}')"
                class="flex items-center gap-4 rounded-[--radius-card] border p-4 transition-all
                    {{ $habit['completed']
                        ? 'border-emerald-500/30 bg-emerald-500/10'
                        : 'border-wc-border bg-wc-bg-tertiary hover:border-wc-text-tertiary' }}"
            >
                {{-- Icon --}}
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl transition-colors
                    {{ $habit['completed'] ? 'bg-emerald-500 text-white' : 'bg-wc-bg-secondary text-wc-text-tertiary' }}"
                >
                    @switch($habit['icon'])
                        @case('water')
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 2.25c0 0-6.75 8.25-6.75 12.75a6.75 6.75 0 0 0 13.5 0C18.75 10.5 12 2.25 12 2.25Z" />
                            </svg>
                            @break
                        @case('moon')
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z" />
                            </svg>
                            @break
                        @case('dumbbell')
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 0a1.5 1.5 0 0 1-1.5-1.5v-3a1.5 1.5 0 0 1 1.5-1.5h1.5a1.5 1.5 0 0 1 1.5 1.5v3m-4.5 0a1.5 1.5 0 0 0-1.5 1.5v3a1.5 1.5 0 0 0 1.5 1.5h1.5a1.5 1.5 0 0 0 1.5-1.5v-3m12-4.5v3a1.5 1.5 0 0 1-1.5 1.5h-1.5m3 0a1.5 1.5 0 0 1-1.5 1.5v3a1.5 1.5 0 0 1-1.5 1.5h-1.5a1.5 1.5 0 0 1-1.5-1.5v-3m0-4.5a1.5 1.5 0 0 0-1.5-1.5h-1.5a1.5 1.5 0 0 0-1.5 1.5v3" />
                            </svg>
                            @break
                        @case('apple')
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c-1.2 0-2.1.6-2.7 1.2C8.7 4.8 8.4 5.4 8.4 6H7.2C5.4 6 4.2 7.8 3.6 9.6 3 11.4 3 13.2 3.6 15c.6 1.8 1.8 3.6 3.6 4.8.9.6 1.8.6 2.7.6h.3c.6 0 1.2-.3 1.8-.3.6 0 1.2.3 1.8.3h.3c.9 0 1.8 0 2.7-.6 1.8-1.2 3-3 3.6-4.8.6-1.8.6-3.6 0-5.4C19.8 7.8 18.6 6 16.8 6h-1.2c0-.6-.3-1.2-.9-1.8C14.1 3.6 13.2 3 12 3Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c0 0 1.5-1.5 3-1.5" />
                            </svg>
                            @break
                        @case('pill')
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m10.5 6 5.25 5.25M4.5 19.5l6.75-6.75m-3.75 3.75 9-9a3.182 3.182 0 0 0 0-4.5 3.182 3.182 0 0 0-4.5 0l-9 9a3.182 3.182 0 0 0 0 4.5 3.182 3.182 0 0 0 4.5 0Z" />
                            </svg>
                            @break
                    @endswitch
                </div>

                {{-- Label & State --}}
                <div class="flex-1 text-left">
                    <p class="text-sm font-medium {{ $habit['completed'] ? 'text-emerald-400' : 'text-wc-text' }}">
                        {{ $habit['label'] }}
                    </p>
                    <p class="text-xs {{ $habit['completed'] ? 'text-emerald-500/70' : 'text-wc-text-tertiary' }}">
                        {{ $habit['completed'] ? 'Completado' : 'Pendiente' }}
                    </p>
                </div>

                {{-- Toggle Indicator --}}
                <div class="flex h-7 w-12 items-center rounded-full px-0.5 transition-colors
                    {{ $habit['completed'] ? 'bg-emerald-500' : 'bg-wc-bg-secondary' }}"
                >
                    <div class="h-6 w-6 rounded-full bg-white shadow transition-transform
                        {{ $habit['completed'] ? 'translate-x-5' : 'translate-x-0' }}"
                    ></div>
                </div>
            </button>
        @endforeach
    </div>

    {{-- Weekly Overview --}}
    <div class="rounded-[--radius-card] border border-wc-border bg-wc-bg-tertiary p-5">
        <h2 class="mb-4 text-sm font-semibold uppercase tracking-wider text-wc-text-secondary">Resumen semanal</h2>

        <div class="grid grid-cols-7 gap-2">
            @foreach ($weeklyData as $day)
                <div class="flex flex-col items-center gap-2">
                    {{-- Day Name --}}
                    <span class="text-xs font-medium uppercase text-wc-text-tertiary">{{ $day['dayName'] }}</span>

                    {{-- Circle --}}
                    @php
                        $pct = $day['total'] > 0 ? ($day['completed'] / $day['total']) : 0;
                    @endphp
                    <div class="relative flex h-10 w-10 items-center justify-center rounded-full border-2 transition-colors
                        {{ $day['isToday'] ? 'border-wc-accent' : 'border-wc-border' }}
                        {{ $pct >= 1 ? 'bg-emerald-500 border-emerald-500' : ($pct > 0 ? 'bg-wc-accent/10' : '') }}"
                    >
                        <span class="font-data text-xs font-semibold {{ $pct >= 1 ? 'text-white' : ($day['isToday'] ? 'text-wc-accent' : 'text-wc-text') }}">
                            {{ $day['dayNumber'] }}
                        </span>
                    </div>

                    {{-- Count --}}
                    <span class="font-data text-xs {{ $pct >= 1 ? 'text-emerald-500 font-semibold' : 'text-wc-text-tertiary' }}">
                        {{ $day['completed'] }}/{{ $day['total'] }}
                    </span>
                </div>
            @endforeach
        </div>
    </div>
</div>
