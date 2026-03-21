@props(['lines' => 3, 'avatar' => false, 'card' => false, 'chart' => false])

@if($card)
<div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5 animate-pulse">
    <div class="flex items-center justify-between mb-4">
        <div class="h-4 w-1/3 rounded bg-wc-bg-secondary"></div>
        <div class="h-8 w-8 rounded-lg bg-wc-bg-secondary"></div>
    </div>
    <div class="h-8 w-1/2 rounded bg-wc-bg-secondary mb-2"></div>
    <div class="h-3 w-2/3 rounded bg-wc-bg-secondary"></div>
</div>
@elseif($chart)
<div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5 animate-pulse">
    <div class="h-4 w-1/4 rounded bg-wc-bg-secondary mb-4"></div>
    <div class="flex items-end gap-2 h-32">
        @php $chartHeights = [45, 70, 30, 85, 55, 100, 65]; @endphp
        @for($i = 0; $i < 7; $i++)
        <div class="flex-1 rounded-t bg-wc-bg-secondary" style="height: {{ $chartHeights[$i] }}%"></div>
        @endfor
    </div>
</div>
@else
<div class="animate-pulse space-y-3">
    @if($avatar)
    <div class="flex items-center gap-3">
        <div class="h-10 w-10 shrink-0 rounded-full bg-wc-bg-secondary"></div>
        <div class="flex-1 space-y-2">
            <div class="h-3 w-3/4 rounded bg-wc-bg-secondary"></div>
            <div class="h-2 w-1/2 rounded bg-wc-bg-secondary"></div>
        </div>
    </div>
    @endif
    @php $lineWidths = [100, 85, 70, 90, 60, 75, 95]; @endphp
    @for($i = 0; $i < $lines; $i++)
    <div class="h-3 rounded bg-wc-bg-secondary" style="width: {{ $lineWidths[$i % 7] }}%"></div>
    @endfor
</div>
@endif
