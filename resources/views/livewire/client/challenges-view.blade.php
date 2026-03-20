<div>
    <div class="mb-8">
        <h1 class="font-display text-3xl tracking-wide text-wc-text">RETOS</h1>
        <p class="mt-1 text-sm text-wc-text-secondary">Participa en retos grupales y gana XP</p>
    </div>

    @if(isset($activeChallenges) && $activeChallenges->count() > 0)
        <div class="grid gap-4 sm:grid-cols-2">
            @foreach($activeChallenges as $challenge)
                <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
                    <div class="flex items-start justify-between">
                        <div>
                            <h3 class="font-display text-lg tracking-wide text-wc-text">{{ strtoupper($challenge->title) }}</h3>
                            <p class="mt-1 text-sm text-wc-text-secondary">{{ $challenge->description }}</p>
                        </div>
                        <span class="rounded-full bg-wc-accent/10 px-2.5 py-0.5 text-xs font-medium text-wc-accent">
                            {{ ucfirst($challenge->challenge_type) }}
                        </span>
                    </div>

                    {{-- Progress --}}
                    @php
                        $participation = $challenge->participants->firstWhere('client_id', auth('wellcore')->id());
                        $progress = $participation?->progress ?? 0;
                        $pct = $challenge->goal_value > 0 ? min(100, ($progress / $challenge->goal_value) * 100) : 0;
                    @endphp

                    <div class="mt-4">
                        <div class="flex items-center justify-between text-xs text-wc-text-tertiary">
                            <span>{{ number_format($progress, 0) }} / {{ $challenge->goal_value }} {{ $challenge->unit }}</span>
                            <span>{{ number_format($pct, 0) }}%</span>
                        </div>
                        <div class="mt-1.5 h-2 overflow-hidden rounded-full bg-wc-bg-secondary">
                            <div class="h-full rounded-full bg-wc-accent transition-all" style="width: {{ $pct }}%"></div>
                        </div>
                    </div>

                    {{-- Dates --}}
                    <div class="mt-3 flex items-center gap-4 text-xs text-wc-text-tertiary">
                        <span>{{ \Carbon\Carbon::parse($challenge->start_date)->format('d M') }} — {{ \Carbon\Carbon::parse($challenge->end_date)->format('d M Y') }}</span>
                    </div>

                    {{-- Action --}}
                    <div class="mt-4">
                        @if($participation)
                            @if($participation->completed)
                                <span class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-500/10 px-3 py-1.5 text-sm font-medium text-emerald-500">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                                    Completado
                                </span>
                            @else
                                <span class="text-xs text-wc-text-tertiary">Participando</span>
                            @endif
                        @else
                            <button wire:click="joinChallenge({{ $challenge->id }})" class="rounded-lg bg-wc-accent px-4 py-2 text-sm font-medium text-white transition hover:bg-wc-accent-hover">
                                Participar
                            </button>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <x-empty-state
            title="SIN RETOS ACTIVOS"
            message="No hay retos activos en este momento. Vuelve pronto para nuevos desafios."
        />
    @endif
</div>
