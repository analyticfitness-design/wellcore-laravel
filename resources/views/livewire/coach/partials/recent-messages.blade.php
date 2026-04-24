<section class="mb-4">
    <div class="flex items-center justify-between mb-3">
        <h2 class="font-display text-sm uppercase tracking-wider text-wc-text">
            {{ __('coach_dashboard.section_messages') }}
        </h2>
        <a wire:navigate href="{{ route('coach.messages') }}"
           class="text-[11px] text-wc-accent hover:underline">
            {{ __('coach_dashboard.btn_view_all') }}
        </a>
    </div>

    @forelse($recentMessages as $msg)
    <div class="flex items-center gap-2 py-2 border-b border-wc-border last:border-0">
        {{-- Avatar with unread dot --}}
        <div class="relative shrink-0">
            <div class="w-8 h-8 rounded-full bg-wc-bg-secondary flex items-center justify-center text-xs font-bold text-wc-text">
                {{ strtoupper(substr($msg['client_name'], 0, 1)) }}
            </div>
            @if(!$msg['is_read'])
            <span class="absolute -top-0.5 -right-0.5 w-2.5 h-2.5 rounded-full bg-wc-accent animate-wc-breathe border-2 border-wc-bg-tertiary"></span>
            @endif
        </div>
        {{-- Message preview --}}
        <div class="min-w-0 flex-1">
            <div class="flex items-center justify-between gap-1">
                <span class="text-sm font-medium text-wc-text truncate">{{ $msg['client_name'] }}</span>
                <span class="text-[10px] text-wc-text-tertiary shrink-0">{{ $msg['time_ago'] }}</span>
            </div>
            <div class="text-xs text-wc-text-tertiary truncate">{{ $msg['message'] }}</div>
        </div>
    </div>
    @empty
    <p class="text-sm text-wc-text-tertiary">{{ __('coach_dashboard.empty_messages') }}</p>
    @endforelse
</section>
