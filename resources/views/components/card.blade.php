@props(['title' => null, 'subtitle' => null, 'padding' => true])

<div {{ $attributes->merge(['class' => 'rounded-xl border border-wc-border bg-wc-bg-tertiary shadow-sm']) }}>
    @if($title)
        <div class="border-b border-wc-border px-6 py-4">
            <h3 class="text-base font-semibold text-wc-text">{{ $title }}</h3>
            @if($subtitle)
                <p class="mt-1 text-sm text-wc-text-secondary">{{ $subtitle }}</p>
            @endif
        </div>
    @endif
    <div @class(['px-6 py-4' => $padding, '' => !$padding])>
        {{ $slot }}
    </div>
</div>
