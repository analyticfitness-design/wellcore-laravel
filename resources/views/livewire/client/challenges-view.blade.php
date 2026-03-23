<div class="space-y-6">

    {{-- ================================================================ --}}
    {{-- HEADER                                                           --}}
    {{-- ================================================================ --}}
    <div class="relative overflow-hidden rounded-2xl border border-wc-border bg-gradient-to-br from-wc-bg-secondary via-wc-bg-tertiary to-wc-bg-secondary p-6">
        <div class="relative z-10">
            <p class="mb-1 text-xs font-semibold uppercase tracking-widest text-wc-accent">WellCore</p>
            <h1 class="font-display text-4xl tracking-wide text-wc-text">RETOS</h1>
            <p class="mt-1 text-sm text-wc-text-secondary">Supera tus límites · Compite · Gana reconocimientos</p>

            {{-- Stats --}}
            <div class="mt-4 flex items-center gap-4">
                @php
                    $joinedCount = $participations->count();
                    $completedCount = $participations->filter(fn($p) => $p->completed)->count();
                @endphp
                <div class="flex items-center gap-1.5">
                    <div class="flex h-6 w-6 items-center justify-center rounded-full bg-wc-accent/20">
                        <svg class="h-3.5 w-3.5 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                        </svg>
                    </div>
                    <span class="text-sm text-wc-text-secondary"><span class="font-data font-bold text-wc-text">{{ $joinedCount }}</span> activos</span>
                </div>
                @if($completedCount > 0)
                    <div class="flex items-center gap-1.5">
                        <div class="flex h-6 w-6 items-center justify-center rounded-full bg-green-500/20">
                            <svg class="h-3.5 w-3.5 text-green-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z" />
                            </svg>
                        </div>
                        <span class="text-sm text-wc-text-secondary"><span class="font-data font-bold text-green-400">{{ $completedCount }}</span> completados</span>
                    </div>
                @endif
            </div>
        </div>
        {{-- Decorative trophy --}}
        <div class="absolute -right-4 -top-4 h-32 w-32 opacity-5 text-wc-accent">
            <svg viewBox="0 0 100 100" fill="currentColor" class="h-full w-full">
                <path d="M50 5 L60 35 L90 35 L67 54 L76 84 L50 65 L24 84 L33 54 L10 35 L40 35 Z"/>
            </svg>
        </div>
    </div>

    {{-- ================================================================ --}}
    {{-- CHALLENGES GRID                                                  --}}
    {{-- ================================================================ --}}
    @if($challenges->isEmpty())
        <div class="rounded-2xl border border-dashed border-wc-border bg-wc-bg-tertiary/50 p-16 text-center">
            <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-2xl bg-wc-accent/10">
                <svg class="h-10 w-10 text-wc-accent/50" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 0 1 3 3h-15a3 3 0 0 1 3-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.007 0H9.497m5.007 0a7.454 7.454 0 0 1-.982-3.172M9.497 14.25a7.454 7.454 0 0 0 .981-3.172M5.25 4.236c-.996.178-1.768-.767-1.287-1.64a11.164 11.164 0 0 1 6.076-5.044.753.753 0 0 1 .923.497l.006.02a.748.748 0 0 1-.395.91A9.664 9.664 0 0 0 5.25 4.236Z" />
                </svg>
            </div>
            <h3 class="mt-5 font-display text-2xl tracking-wide text-wc-text">SIN RETOS ACTIVOS</h3>
            <p class="mt-2 text-sm text-wc-text-secondary max-w-xs mx-auto">No hay retos disponibles en este momento. Tu coach los activará pronto.</p>
        </div>
    @else
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($challenges as $challenge)
                @php
                    $participation = $participations->get($challenge->id);
                    $isJoined    = $participation !== null;
                    $isCompleted = $participation?->completed ?? false;
                    $progressPct = $challenge->my_progress_pct;
                    $daysLeft    = (int) now()->diffInDays($challenge->end_date, false);
                    $isExpired   = $daysLeft < 0;

                    // Color by type
                    $typeConfig = match($challenge->challenge_type ?? 'general') {
                        'fuerza'    => ['accent' => 'red',    'label' => 'Fuerza'],
                        'cardio'    => ['accent' => 'orange', 'label' => 'Cardio'],
                        'nutricion' => ['accent' => 'green',  'label' => 'Nutrición'],
                        'habitos'   => ['accent' => 'blue',   'label' => 'Hábitos'],
                        default     => ['accent' => 'red',    'label' => 'General'],
                    };

                    $urgencyColor = $daysLeft <= 3 && !$isExpired ? 'text-orange-400' : 'text-wc-text-tertiary';
                @endphp

                <div class="group relative flex flex-col overflow-hidden rounded-2xl border bg-wc-bg-tertiary transition-all duration-300 hover:shadow-lg hover:shadow-black/10
                    {{ $isCompleted ? 'border-green-500/40 hover:border-green-500/60' : ($isJoined ? 'border-wc-accent/30 hover:border-wc-accent/60' : 'border-wc-border hover:border-wc-border/80') }}"
                    wire:key="challenge-{{ $challenge->id }}">

                    {{-- Top accent strip --}}
                    <div class="h-1 w-full {{ $isCompleted ? 'bg-gradient-to-r from-green-500 to-green-500/20' : ($isJoined ? 'bg-gradient-to-r from-wc-accent to-wc-accent/20' : 'bg-transparent') }}"></div>

                    {{-- Status badge --}}
                    @if($isCompleted)
                        <div class="absolute right-3 top-4 z-10">
                            <span class="flex items-center gap-1 rounded-full bg-green-500/20 border border-green-500/30 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-green-400">
                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                                Completado
                            </span>
                        </div>
                    @elseif($isJoined)
                        <div class="absolute right-3 top-4 z-10">
                            <span class="flex items-center gap-1 rounded-full bg-wc-accent/10 border border-wc-accent/20 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-wc-accent">
                                Participando
                            </span>
                        </div>
                    @endif

                    <div class="flex flex-1 flex-col p-5">
                        {{-- Icon + title --}}
                        <div class="mb-4 flex items-start gap-3">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-wc-accent/10 border border-wc-accent/20">
                                @if($challenge->badge_icon)
                                    <span class="text-2xl">{{ $challenge->badge_icon }}</span>
                                @else
                                    <svg class="h-6 w-6 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 0 1 3 3h-15a3 3 0 0 1 3-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.007 0H9.497" />
                                    </svg>
                                @endif
                            </div>
                            <div class="min-w-0 flex-1 {{ ($isCompleted || $isJoined) ? 'pr-24' : '' }}">
                                <h3 class="font-display text-lg leading-tight tracking-wide text-wc-text">
                                    {{ strtoupper($challenge->title) }}
                                </h3>
                                @if($challenge->challenge_type)
                                    <span class="mt-0.5 inline-block text-[10px] font-semibold uppercase tracking-wider text-wc-text-tertiary">
                                        {{ $typeConfig['label'] }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{-- Description --}}
                        @if($challenge->description)
                            <p class="mb-4 text-sm leading-relaxed text-wc-text-secondary">
                                {{ \Illuminate\Support\Str::limit($challenge->description, 100) }}
                            </p>
                        @endif

                        {{-- Progress ring + bar (if joined) --}}
                        @if($isJoined && $challenge->goal_value)
                            <div class="mb-4">
                                <div class="mb-1.5 flex items-center justify-between">
                                    <span class="text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Progreso</span>
                                    <span class="font-data text-sm font-bold {{ $isCompleted ? 'text-green-400' : 'text-wc-accent' }}">{{ $progressPct }}%</span>
                                </div>
                                <div class="h-2 overflow-hidden rounded-full bg-wc-bg-secondary">
                                    <div class="h-full rounded-full transition-all duration-700 {{ $isCompleted ? 'bg-green-500' : 'bg-wc-accent' }}"
                                         style="width: {{ $progressPct }}%"></div>
                                </div>
                                <div class="mt-1 flex items-center justify-between text-[10px] text-wc-text-tertiary font-data">
                                    <span>{{ number_format($participation->progress ?? 0, 1) }}</span>
                                    <span>{{ number_format($challenge->goal_value) }} {{ $challenge->unit }}</span>
                                </div>
                            </div>
                        @elseif($challenge->goal_value)
                            <div class="mb-4 flex items-center gap-2 rounded-xl bg-wc-bg-secondary px-3 py-2">
                                <svg class="h-4 w-4 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 3v1.5M3 21v-6m0 0 2.77-.693a9 9 0 0 1 6.208.682l.108.054a9 9 0 0 0 6.086.71l3.114-.732a48.524 48.524 0 0 1-.005-10.499l-3.11.732a9 9 0 0 1-6.085-.711l-.108-.054a9 9 0 0 0-6.208-.682L3 4.5M3 15V4.5" />
                                </svg>
                                <span class="font-data text-sm text-wc-text">
                                    Meta: <span class="font-bold">{{ number_format($challenge->goal_value) }} {{ $challenge->unit }}</span>
                                </span>
                            </div>
                        @endif

                        {{-- Rank badge --}}
                        @if($isJoined && $participation->rank)
                            <div class="mb-3 flex items-center gap-2">
                                <span class="text-lg">{{ $participation->rank <= 3 ? ['🥇','🥈','🥉'][$participation->rank - 1] : '🏅' }}</span>
                                <span class="font-data text-sm font-bold text-yellow-500">Posición #{{ $participation->rank }}</span>
                            </div>
                        @endif

                        {{-- Dates + time left --}}
                        <div class="mt-auto flex items-center justify-between pt-3 border-t border-wc-border/50">
                            @if($challenge->start_date && $challenge->end_date)
                                <span class="text-[11px] text-wc-text-tertiary">
                                    {{ $challenge->start_date->format('d M') }} → {{ $challenge->end_date->format('d M') }}
                                </span>
                            @endif
                            @if($challenge->end_date)
                                <span class="text-[11px] font-semibold {{ $isExpired ? 'text-red-400' : $urgencyColor }}">
                                    @if($isExpired)
                                        Finalizado
                                    @elseif($daysLeft === 0)
                                        ¡Hoy termina!
                                    @else
                                        {{ $daysLeft }}d restantes
                                    @endif
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- Action footer --}}
                    <div class="px-5 pb-5">
                        @if($isCompleted)
                            <div class="flex items-center justify-center gap-2 rounded-xl bg-green-500/10 border border-green-500/20 py-2.5 text-sm font-semibold text-green-400">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z" />
                                </svg>
                                Reto completado
                            </div>
                        @elseif($isJoined)
                            <div class="flex items-center justify-center gap-2 rounded-xl bg-wc-bg-secondary border border-wc-border py-2.5 text-sm text-wc-text-secondary">
                                <svg class="h-4 w-4 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.362 5.214A8.252 8.252 0 0 1 12 21 8.25 8.25 0 0 1 6.038 7.047 8.287 8.287 0 0 0 9 9.601a8.983 8.983 0 0 1 3.361-6.867 8.21 8.21 0 0 0 3 2.48Z" />
                                </svg>
                                En progreso
                            </div>
                        @elseif(!$isExpired)
                            <button
                                wire:click="join({{ $challenge->id }})"
                                wire:loading.attr="disabled"
                                wire:target="join({{ $challenge->id }})"
                                class="btn-press w-full flex items-center justify-center gap-2 rounded-xl bg-wc-accent py-2.5 text-sm font-bold text-white transition-all hover:bg-wc-accent/90 disabled:opacity-60 shadow-lg shadow-wc-accent/20">
                                <svg wire:loading.remove wire:target="join({{ $challenge->id }})" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.362 5.214A8.252 8.252 0 0 1 12 21 8.25 8.25 0 0 1 6.038 7.047 8.287 8.287 0 0 0 9 9.601a8.983 8.983 0 0 1 3.361-6.867 8.21 8.21 0 0 0 3 2.48Z" />
                                </svg>
                                <svg wire:loading wire:target="join({{ $challenge->id }})" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span wire:loading.remove wire:target="join({{ $challenge->id }})">UNIRME AL RETO</span>
                                <span wire:loading wire:target="join({{ $challenge->id }})">Uniendo...</span>
                            </button>
                        @else
                            <div class="flex items-center justify-center rounded-xl bg-wc-bg-secondary py-2.5 text-xs text-wc-text-tertiary">
                                Reto finalizado
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
