@props(['label', 'value', 'icon' => null, 'trend' => null, 'trendUp' => true])

<div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
    <div class="flex items-center justify-between">
        <p class="text-sm font-medium text-wc-text-secondary">{{ $label }}</p>
        @if($icon)
            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-wc-accent/10">
                {!! $icon !!}
            </div>
        @endif
    </div>
    <p class="mt-2 font-data text-3xl font-bold tabular-nums text-wc-text">{{ $value }}</p>
    @if($trend)
        <p class="mt-1 text-sm {{ $trendUp ? 'text-emerald-500' : 'text-wc-accent' }}">
            {{ $trendUp ? '+' : '' }}{{ $trend }}
        </p>
    @endif
</div>
