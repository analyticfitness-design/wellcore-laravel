<section class="mb-4">
    <div class="flex items-center justify-between mb-3">
        <h2 class="font-display text-sm uppercase tracking-wider text-wc-text">
            {{ __('coach_dashboard.section_urgent') }}
        </h2>
        @if($urgentClientsCount > 0)
        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-wc-accent/15 text-wc-accent">
            {{ $urgentClientsCount }} CLIENTES
        </span>
        @endif
    </div>

    @forelse($attentionClients as $client)
    <div class="swipe-item relative mb-2">
        {{-- Card --}}
        <div class="rounded-card border-l-[3px] bg-wc-bg-tertiary border border-wc-border p-3"
             style="border-left-color: var(--color-wc-accent)">
            <div class="flex items-start justify-between gap-2">
                {{-- Avatar + info --}}
                <div class="flex items-center gap-2 min-w-0">
                    <div class="w-8 h-8 rounded-full bg-wc-accent/20 flex items-center justify-center text-wc-accent text-xs font-bold shrink-0">
                        {{ strtoupper(substr($client['name'], 0, 1)) }}
                    </div>
                    <div class="min-w-0">
                        <div class="font-medium text-wc-text text-sm truncate">{{ $client['name'] }}</div>
                        <div class="text-[11px] text-wc-text-tertiary">
                            Sin responder: {{ $client['oldest_checkin'] }}
                        </div>
                    </div>
                </div>
                {{-- Badges + action --}}
                <div class="flex flex-col items-end gap-1 shrink-0">
                    <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-orange-500/20 text-orange-400">
                        ⚠ {{ $client['pending_checkins'] }}d
                    </span>
                    <a wire:navigate href="{{ route('coach.checkins') }}"
                       class="text-[10px] font-medium text-wc-accent hover:underline">
                        {{ __('coach_dashboard.btn_respond') }}
                    </a>
                </div>
            </div>
        </div>
        {{-- Swipe-to-reveal action button --}}
        <div class="absolute inset-y-0 right-0 flex items-center overflow-hidden rounded-r-card" style="width: 80px">
            <button class="h-full w-full bg-emerald-600 text-white text-[10px] font-bold flex items-center justify-center"
                    aria-label="Responder a {{ $client['name'] }}">
                Responder
            </button>
        </div>
    </div>
    @empty
    <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-6 text-center">
        <div class="text-2xl mb-2">✓</div>
        <div class="font-medium text-wc-text text-sm">{{ __('coach_dashboard.empty_urgent') }}</div>
        <div class="text-xs text-wc-text-tertiary mt-0.5">{{ __('coach_dashboard.empty_urgent_sub') }}</div>
    </div>
    @endforelse
</section>
