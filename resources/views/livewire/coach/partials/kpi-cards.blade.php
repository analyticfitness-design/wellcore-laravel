{{-- ─── Quick actions strip (mobile only) ─── --}}
<div class="lg:hidden flex gap-2 overflow-x-auto no-scrollbar pb-1 mb-3">

    {{-- Check-ins --}}
    <a wire:navigate href="{{ route('coach.checkins') }}"
       class="nav-tap shrink-0 flex flex-col items-center justify-center gap-1 w-[4.5rem] h-[4.5rem] rounded-card bg-wc-bg-tertiary border border-wc-border px-3 py-3 relative">
        <svg class="w-5 h-5 text-wc-text-secondary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
        </svg>
        <span class="text-[10px] font-medium text-wc-text-secondary">Check-ins</span>
        @if($pendingCheckins > 0)
        <span class="absolute top-1.5 right-1.5 min-w-[16px] h-4 px-1 rounded-full bg-wc-accent text-[8px] font-bold text-white flex items-center justify-center">
            {{ $pendingCheckins }}
        </span>
        @endif
    </a>

    {{-- Mensajes --}}
    <a wire:navigate href="{{ route('coach.messages') }}"
       class="nav-tap shrink-0 flex flex-col items-center justify-center gap-1 w-[4.5rem] h-[4.5rem] rounded-card bg-wc-bg-tertiary border border-wc-border px-3 py-3 relative">
        <svg class="w-5 h-5 text-wc-text-secondary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a5.969 5.969 0 0 1-.474-.065 4.48 4.48 0 0 0 .978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z" />
        </svg>
        <span class="text-[10px] font-medium text-wc-text-secondary">Mensajes</span>
        @if($unreadMessages > 0)
        <span class="absolute top-1.5 right-1.5 min-w-[16px] h-4 px-1 rounded-full bg-wc-accent animate-wc-breathe text-[8px] font-bold text-white flex items-center justify-center">
            {{ $unreadMessages }}
        </span>
        @endif
    </a>

    {{-- Tickets --}}
    <a wire:navigate href="{{ route('coach.checkins') }}"
       class="nav-tap shrink-0 flex flex-col items-center justify-center gap-1 w-[4.5rem] h-[4.5rem] rounded-card bg-wc-bg-tertiary border border-wc-border px-3 py-3">
        <svg class="w-5 h-5 text-wc-text-secondary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 0 1 0 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 0 1 0-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375Z" />
        </svg>
        <span class="text-[10px] font-medium text-wc-text-secondary">Tickets</span>
    </a>

    {{-- Analítica --}}
    <a wire:navigate href="{{ route('coach.analytics') }}"
       class="nav-tap shrink-0 flex flex-col items-center justify-center gap-1 w-[4.5rem] h-[4.5rem] rounded-card bg-wc-bg-tertiary border border-wc-border px-3 py-3">
        <svg class="w-5 h-5 text-wc-text-secondary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
        </svg>
        <span class="text-[10px] font-medium text-wc-text-secondary">Analítica</span>
    </a>

</div>

{{-- ─── KPI stats grid: 2×2 mobile / 4×1 desktop ─── --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-4">

    <x-coach.stat-card
        label="{{ __('coach_dashboard.stat_clients') }}"
        :value="$activeClients"
        delta="+12%"
        heroClass="wc-hero-blue"
        accentColor="#3B82F6"
        :spark="$sparklines['clients'] ?? []"
        :delay="100" />

    <x-coach.stat-card
        label="{{ __('coach_dashboard.stat_checkins') }}"
        :value="$pendingCheckins"
        delta=""
        heroClass="wc-hero-amber"
        accentColor="#F59E0B"
        :spark="$sparklines['checkins'] ?? []"
        :delay="200" />

    <x-coach.stat-card
        label="{{ __('coach_dashboard.stat_messages') }}"
        :value="$unreadMessages"
        delta=""
        heroClass="wc-hero-accent"
        accentColor="#DC2626"
        :spark="$sparklines['messages'] ?? []"
        :delay="300" />

    <x-coach.stat-card
        label="{{ __('coach_dashboard.stat_tickets') }}"
        :value="$openTickets"
        delta=""
        heroClass="wc-hero-emerald"
        accentColor="#10B981"
        :spark="$sparklines['tickets'] ?? []"
        :delay="400" />

</div>
