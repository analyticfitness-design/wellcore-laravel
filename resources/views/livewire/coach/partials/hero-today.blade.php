{{-- ─── MOBILE: Hero card with gradient ─── --}}
<div class="lg:hidden wc-hero-accent wc-noise relative rounded-card border-l-[3px] p-4 mb-3"
     style="border-left-color: var(--color-wc-accent)">
    <div class="font-mono text-[10px] text-wc-text-tertiary uppercase tracking-wider">{{ $todayDateLabel }}</div>

    @if($urgentClientsCount > 0)
        <div class="mt-2 font-display text-xl uppercase text-wc-text">
            {{ $urgentClientsCount }} CLIENTES NECESITAN ATENCIÓN
        </div>
        <div class="text-sm text-wc-text-secondary mt-0.5">
            {{ $pendingCheckins }} check-ins · {{ $unreadMessages }} mensajes sin leer
        </div>
    @else
        <div class="mt-2 font-display text-xl uppercase text-emerald-400">
            AL DÍA · SIN PENDIENTES
        </div>
    @endif

    {{-- Progress bar --}}
    <div class="mt-3">
        <div class="flex items-center justify-between text-[10px] text-wc-text-tertiary mb-1">
            <span>Tareas hoy</span>
            <span>{{ $pendingCheckins > 0 ? 0 : 4 }}/4</span>
        </div>
        <div class="h-1.5 rounded-full overflow-hidden" style="background: var(--color-wc-border)">
            <div class="h-full rounded-full transition-all duration-700"
                 style="width: {{ $pendingCheckins > 0 ? '25' : '100' }}%; background: var(--color-wc-accent)">
            </div>
        </div>
    </div>
</div>

{{-- ─── DESKTOP: H1 heading ─── --}}
<div class="hidden lg:block mb-6">
    <div class="font-mono text-xs text-wc-text-tertiary uppercase tracking-widest mb-1">{{ $todayDateLabel }}</div>
    <h1 class="font-display text-4xl uppercase tracking-wide text-wc-text">
        {{ __('coach_dashboard.hoy') }}
    </h1>
    <p class="mt-1 text-sm text-wc-text-secondary">
        @if($urgentClientsCount > 0)
            {{ $urgentClientsCount }} clientes en riesgo
            · {{ $pendingCheckins }} check-ins pendientes
            · {{ $unreadMessages }} mensajes sin leer
        @else
            Todo al día · Sin pendientes urgentes
        @endif
    </p>
    <div class="mt-4 flex items-center gap-2 flex-wrap">
        <a wire:navigate href="{{ route('coach.checkins') }}"
           class="inline-flex items-center gap-2 rounded-lg border border-wc-border px-4 py-2 text-sm font-medium text-wc-text hover:bg-wc-bg-tertiary transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
            Revisar check-ins
        </a>
        <a wire:navigate href="{{ route('coach.messages') }}"
           class="inline-flex items-center gap-2 rounded-lg border border-wc-border px-4 py-2 text-sm font-medium text-wc-text hover:bg-wc-bg-tertiary transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" />
            </svg>
            Enviar mensaje
        </a>
        <a wire:navigate href="{{ route('coach.clients') }}"
           class="inline-flex items-center gap-2 rounded-lg bg-wc-accent px-4 py-2 text-sm font-medium text-white hover:bg-wc-accent-hover transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            + Crear ticket
        </a>
    </div>
</div>
