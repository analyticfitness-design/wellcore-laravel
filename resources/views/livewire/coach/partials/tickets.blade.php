<section class="mb-4">
    <div class="flex items-center justify-between mb-3">
        <h2 class="font-display text-sm uppercase tracking-wider text-wc-text">
            {{ __('coach_dashboard.section_tickets') }}
        </h2>
        <a wire:navigate href="{{ route('coach.checkins') }}"
           class="text-[11px] text-wc-accent hover:underline">
            {{ __('coach_dashboard.btn_view_all') }}
        </a>
    </div>

    @forelse($openTicketsList ?? [] as $ticket)
    <div class="flex items-center justify-between py-2 border-b border-wc-border last:border-0">
        <div class="min-w-0 mr-3">
            <div class="text-sm font-medium text-wc-text truncate">{{ $ticket['title'] ?? 'Ticket' }}</div>
            <div class="text-[11px] text-wc-text-tertiary">
                {{ $ticket['client_name'] ?? '' }}
                @if(!empty($ticket['created_ago']))
                    · {{ $ticket['created_ago'] }}
                @endif
            </div>
        </div>
        <span class="shrink-0 text-[10px] font-bold px-1.5 py-0.5 rounded
            {{ ($ticket['priority'] ?? '') === 'high'
                ? 'bg-red-500/20 text-red-400'
                : 'bg-amber-500/20 text-amber-400' }}">
            {{ strtoupper($ticket['priority'] ?? 'LOW') }}
        </span>
    </div>
    @empty
    <p class="text-sm text-wc-text-tertiary">{{ __('coach_dashboard.empty_tickets') }}</p>
    @endforelse
</section>
