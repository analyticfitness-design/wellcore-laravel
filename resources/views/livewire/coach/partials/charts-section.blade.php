<section x-data="{ expanded: false }" class="mb-4">
    {{-- Collapsible header --}}
    <button @click="expanded = !expanded"
            class="flex items-center gap-2 w-full mb-3 text-left"
            :aria-expanded="expanded">
        <h2 class="font-display text-sm uppercase tracking-wider text-wc-text flex-1">
            {{ __('coach_dashboard.section_analysis') }}
        </h2>
        <svg class="w-3 h-3 text-wc-text-tertiary transition-transform"
             :class="expanded ? 'rotate-180' : ''"
             fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
             aria-hidden="true">
            <polyline points="6 9 12 15 18 9"/>
        </svg>
    </button>

    <div x-show="expanded"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-1"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-cloak>
        <div class="grid lg:grid-cols-2 gap-4">

            {{-- Chart 1: Active clients over 7 days --}}
            <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-4">
                <div class="text-xs font-medium text-wc-text-secondary mb-3">
                    Clientes activos · 7 días
                </div>
                <svg viewBox="0 0 200 60" class="w-full h-16" aria-hidden="true">
                    @php
                        $data = $sparklines['clients'] ?? array_fill(0, 7, 0);
                        $max  = max($data) ?: 1;
                        $pts  = collect($data)->map(
                            fn($v, $i) => round($i * (200 / 6), 1) . ',' . round(60 - ($v / $max * 50), 1)
                        )->implode(' ');
                    @endphp
                    <polyline points="{{ $pts }}" fill="none" stroke="#3B82F6"
                              stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>

            {{-- Chart 2: Check-in frequency bars --}}
            <div class="rounded-card border border-wc-border bg-wc-bg-tertiary p-4">
                <div class="text-xs font-medium text-wc-text-secondary mb-3">
                    Check-ins · 7 días
                </div>
                <svg viewBox="0 0 200 60" class="w-full h-16" aria-hidden="true">
                    @php
                        $data = $sparklines['checkins'] ?? array_fill(0, 7, 0);
                        $max  = max($data) ?: 1;
                    @endphp
                    @foreach($data as $i => $v)
                    @php $x = round($i * (200 / 6), 1); $h = round($v / $max * 50, 1); @endphp
                    <rect x="{{ $x }}" y="{{ 60 - $h }}" width="22" height="{{ $h }}"
                          rx="3" fill="#DC2626" opacity="0.7"/>
                    @endforeach
                </svg>
            </div>

        </div>
    </div>
</section>
