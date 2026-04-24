@props([
    'label'       => '',
    'value'       => 0,
    'delta'       => '',
    'heroClass'   => 'wc-hero-blue',
    'accentColor' => '#3B82F6',
    'spark'       => [],
    'delay'       => 0,
])

<div class="stat-card {{ $heroClass }} wc-noise relative rounded-card overflow-hidden p-4 border border-wc-border"
     style="animation-delay: {{ $delay }}ms"
     x-data="{{ json_encode(['counter' => 0, 'target' => (int) $value]) }}"
     x-init="setTimeout(() => {
         if (target === 0) return;
         let s = Math.ceil(target / 20);
         let i = setInterval(() => {
             counter = Math.min(counter + s, target);
             if (counter >= target) clearInterval(i);
         }, 40);
     }, {{ $delay }})">

    {{-- Sparkline top-right --}}
    @if(!empty($spark))
    <svg class="absolute top-3 right-3 opacity-60" width="60" height="24" viewBox="0 0 60 24" aria-hidden="true">
        @php
            $max = max($spark) ?: 1;
            $points = collect($spark)->map(fn($v, $i) =>
                round(($i / (count($spark) - 1)) * 60, 1) . ',' . round(24 - ($v / $max * 20), 1)
            )->implode(' ');
        @endphp
        <polyline points="{{ $points }}" fill="none" stroke="{{ $accentColor }}" stroke-width="1.5"
                  stroke-linecap="round" stroke-linejoin="round"/>
    </svg>
    @endif

    {{-- Delta badge --}}
    @if($delta)
    <span class="inline-block text-[10px] font-bold px-1.5 py-0.5 rounded-full
                 {{ str_starts_with($delta, '+') ? 'bg-emerald-500/20 text-emerald-400' : 'bg-wc-text-tertiary/20 text-wc-text-tertiary' }}">
        {{ $delta }}
    </span>
    @endif

    {{-- Counter number --}}
    <div class="mt-4 font-display text-4xl leading-none text-wc-text" x-text="counter">0</div>
    <div class="mt-1 text-[10px] font-bold uppercase tracking-wider text-wc-text-secondary">{{ $label }}</div>
</div>
