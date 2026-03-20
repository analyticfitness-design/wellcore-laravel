<div>
    {{-- Header --}}
    <div class="mb-8">
        <h1 class="font-display text-3xl tracking-wide text-wc-text">RETOS</h1>
        <p class="mt-1 text-sm text-wc-text-secondary">Participa en retos y compite con la comunidad WellCore</p>
    </div>

    @if($challenges->isEmpty())
        {{-- Empty State --}}
        <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-12 text-center">
            <svg class="mx-auto h-16 w-16 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.362 5.214A8.252 8.252 0 0 1 12 21 8.25 8.25 0 0 1 6.038 7.047 8.287 8.287 0 0 0 9 9.601a8.983 8.983 0 0 1 3.361-6.867 8.21 8.21 0 0 0 3 2.48Z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 18a3.75 3.75 0 0 0 .495-7.468 5.99 5.99 0 0 0-1.925 3.547 5.975 5.975 0 0 1-2.133-1.001A3.75 3.75 0 0 0 12 18Z" />
            </svg>
            <h3 class="mt-4 font-display text-xl text-wc-text">SIN RETOS ACTIVOS</h3>
            <p class="mt-2 text-sm text-wc-text-secondary">No hay retos activos en este momento. Vuelve pronto para nuevos desafios.</p>
        </div>
    @else
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($challenges as $challenge)
                @php
                    $participation = $participations->get($challenge->id);
                    $isJoined = $participation !== null;
                    $isCompleted = $participation?->completed ?? false;
                    $progressPct = $this->getProgressPercentage($challenge->id);
                    $daysLeft = now()->diffInDays($challenge->end_date, false);
                    $isExpired = $daysLeft < 0;
                @endphp

                <div class="relative flex flex-col overflow-hidden rounded-xl border border-wc-border bg-wc-bg-tertiary transition-all hover:border-wc-accent/30">
                    {{-- Completed Badge --}}
                    @if($isCompleted)
                        <div class="absolute right-3 top-3 z-10">
                            <div class="flex items-center gap-1.5 rounded-full bg-green-500/20 px-3 py-1">
                                <svg class="h-4 w-4 text-green-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                </svg>
                                <span class="text-xs font-semibold text-green-400">Completado</span>
                            </div>
                        </div>
                    @endif

                    {{-- Card Header --}}
                    <div class="p-5">
                        {{-- Badge Icon --}}
                        <div class="mb-3 flex items-center gap-3">
                            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-wc-accent/10">
                                @if($challenge->badge_icon)
                                    <span class="text-2xl">{{ $challenge->badge_icon }}</span>
                                @else
                                    <svg class="h-6 w-6 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 0 1 3 3h-15a3 3 0 0 1 3-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.007 0H9.497m5.007 0a7.454 7.454 0 0 1-.982-3.172M9.497 14.25a7.454 7.454 0 0 0 .981-3.172M5.25 4.236c-.996.178-1.768-.767-1.287-1.64a11.164 11.164 0 0 1 6.076-5.044.753.753 0 0 1 .923.497l.006.02a.748.748 0 0 1-.395.91A9.664 9.664 0 0 0 5.25 4.236ZM18.75 4.236c.996.178 1.768-.767 1.287-1.64A11.164 11.164 0 0 0 13.96 2.596a.753.753 0 0 0-.922.497l-.007.02a.748.748 0 0 0 .396.91 9.664 9.664 0 0 1 5.323 4.213Z" />
                                    </svg>
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="font-display text-lg tracking-wide text-wc-text truncate">{{ strtoupper($challenge->title) }}</h3>
                                @if($challenge->challenge_type)
                                    <span class="text-xs text-wc-text-tertiary">{{ ucfirst($challenge->challenge_type) }}</span>
                                @endif
                            </div>
                        </div>

                        {{-- Description --}}
                        @if($challenge->description)
                            <p class="mb-4 text-sm leading-relaxed text-wc-text-secondary">{{ \Illuminate\Support\Str::limit($challenge->description, 120) }}</p>
                        @endif

                        {{-- Goal Info --}}
                        @if($challenge->goal_value)
                            <div class="mb-3 flex items-center gap-2">
                                <svg class="h-4 w-4 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 3v1.5M3 21v-6m0 0 2.77-.693a9 9 0 0 1 6.208.682l.108.054a9 9 0 0 0 6.086.71l3.114-.732a48.524 48.524 0 0 1-.005-10.499l-3.11.732a9 9 0 0 1-6.085-.711l-.108-.054a9 9 0 0 0-6.208-.682L3 4.5M3 15V4.5" />
                                </svg>
                                <span class="font-data text-sm text-wc-text">
                                    Meta: <span class="font-bold">{{ number_format($challenge->goal_value) }}</span>
                                    @if($challenge->unit) {{ $challenge->unit }} @endif
                                </span>
                            </div>
                        @endif

                        {{-- Progress Bar (if joined) --}}
                        @if($isJoined && $challenge->goal_value)
                            <div class="mb-3">
                                <div class="mb-1 flex items-center justify-between">
                                    <span class="text-xs text-wc-text-tertiary">Progreso</span>
                                    <span class="font-data text-xs font-bold text-wc-text">{{ $progressPct }}%</span>
                                </div>
                                <div class="h-2.5 overflow-hidden rounded-full bg-wc-bg-secondary">
                                    <div
                                        class="h-full rounded-full transition-all duration-500 {{ $isCompleted ? 'bg-green-500' : 'bg-wc-accent' }}"
                                        style="width: {{ $progressPct }}%"
                                    ></div>
                                </div>
                                <div class="mt-1 flex items-center justify-between">
                                    <span class="font-data text-xs text-wc-text-tertiary">{{ number_format($participation->progress ?? 0, 1) }}</span>
                                    <span class="font-data text-xs text-wc-text-tertiary">{{ number_format($challenge->goal_value) }} {{ $challenge->unit }}</span>
                                </div>
                            </div>
                        @endif

                        {{-- Dates --}}
                        <div class="flex items-center gap-4 text-xs text-wc-text-tertiary">
                            @if($challenge->start_date)
                                <div class="flex items-center gap-1">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                                    </svg>
                                    {{ $challenge->start_date->format('d M') }}
                                </div>
                            @endif
                            @if($challenge->end_date)
                                <div class="flex items-center gap-1">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                    </svg>
                                    @if($isExpired)
                                        <span class="text-red-400">Finalizado</span>
                                    @else
                                        {{ $daysLeft }} {{ $daysLeft === 1 ? 'dia' : 'dias' }} restantes
                                    @endif
                                </div>
                            @endif
                        </div>

                        {{-- Rank (if joined) --}}
                        @if($isJoined && $participation->rank)
                            <div class="mt-3 flex items-center gap-2">
                                <svg class="h-4 w-4 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z" />
                                </svg>
                                <span class="font-data text-sm font-bold text-yellow-500">Posicion #{{ $participation->rank }}</span>
                            </div>
                        @endif
                    </div>

                    {{-- Action Footer --}}
                    <div class="mt-auto border-t border-wc-border px-5 py-3">
                        @if($isCompleted)
                            <div class="flex items-center justify-center gap-2 py-1 text-sm font-semibold text-green-400">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z" />
                                </svg>
                                Reto completado
                            </div>
                        @elseif($isJoined)
                            <div class="flex items-center justify-center gap-2 py-1 text-sm font-medium text-wc-text-secondary">
                                <svg class="h-4 w-4 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                </svg>
                                Participando
                            </div>
                        @elseif(!$isExpired)
                            <button
                                wire:click="join({{ $challenge->id }})"
                                wire:loading.attr="disabled"
                                wire:target="join({{ $challenge->id }})"
                                class="flex w-full items-center justify-center gap-2 rounded-lg bg-wc-accent py-2 text-sm font-semibold text-white transition-all hover:bg-wc-accent-hover active:scale-[0.98] disabled:opacity-60"
                            >
                                <svg wire:loading.remove wire:target="join({{ $challenge->id }})" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.362 5.214A8.252 8.252 0 0 1 12 21 8.25 8.25 0 0 1 6.038 7.047 8.287 8.287 0 0 0 9 9.601a8.983 8.983 0 0 1 3.361-6.867 8.21 8.21 0 0 0 3 2.48Z" />
                                </svg>
                                <svg wire:loading wire:target="join({{ $challenge->id }})" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span wire:loading.remove wire:target="join({{ $challenge->id }})">Participar</span>
                                <span wire:loading wire:target="join({{ $challenge->id }})">Uniendo...</span>
                            </button>
                        @else
                            <div class="py-1 text-center text-sm text-wc-text-tertiary">
                                Reto finalizado
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
