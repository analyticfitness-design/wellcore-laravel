@props(['color' => 'default'])

@php
$colors = [
    'default' => 'bg-wc-bg-secondary text-wc-text-secondary',
    'success' => 'bg-emerald-500/10 text-emerald-500',
    'warning' => 'bg-amber-500/10 text-amber-500',
    'danger'  => 'bg-wc-accent/10 text-wc-accent',
    'info'    => 'bg-blue-500/10 text-blue-500',
    'accent'  => 'bg-wc-accent text-white',
];
$cls = $colors[$color] ?? $colors['default'];
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium $cls"]) }}>
    {{ $slot }}
</span>
