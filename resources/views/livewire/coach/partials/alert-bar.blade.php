@if($urgentClientsCount > 0)
<div class="hidden lg:flex items-center justify-between px-5 py-3 rounded-card border-l-4 bg-wc-accent/10 mb-4"
     style="border-left-color: var(--color-wc-accent)">
    <div class="flex items-center gap-2 text-sm text-wc-text">
        <svg class="w-4 h-4 text-wc-accent shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
        </svg>
        {{ trans_choice('coach_dashboard.alert_urgent', $urgentClientsCount, ['count' => $urgentClientsCount]) }}
    </div>
    <a wire:navigate href="{{ route('coach.checkins') }}"
       class="text-sm font-medium text-wc-accent hover:underline shrink-0 ml-4">
        {{ __('coach_dashboard.alert_view_all') }}
    </a>
</div>
@endif
