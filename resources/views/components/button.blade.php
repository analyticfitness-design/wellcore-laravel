@props(['variant' => 'primary', 'size' => 'md', 'href' => null])

@php
$variants = [
    'primary' => 'bg-wc-accent text-white hover:bg-wc-accent-hover focus:ring-wc-accent/50',
    'secondary' => 'border border-wc-border bg-wc-bg-secondary text-wc-text hover:bg-wc-bg-tertiary',
    'ghost' => 'text-wc-text-secondary hover:text-wc-text hover:bg-wc-bg-secondary',
    'danger' => 'bg-red-600 text-white hover:bg-red-700 focus:ring-red-500/50',
];
$sizes = [
    'sm' => 'px-3 py-1.5 text-sm',
    'md' => 'px-4 py-2 text-sm',
    'lg' => 'px-6 py-3 text-base',
];
$cls = ($variants[$variant] ?? $variants['primary']) . ' ' . ($sizes[$size] ?? $sizes['md']);
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => "inline-flex items-center justify-center gap-2 rounded-lg font-medium focus:outline-none focus:ring-2 focus:ring-offset-2 transition-colors $cls"]) }}>
        {{ $slot }}
    </a>
@else
    <button {{ $attributes->merge(['class' => "inline-flex items-center justify-center gap-2 rounded-lg font-medium focus:outline-none focus:ring-2 focus:ring-offset-2 transition-colors $cls"]) }}>
        {{ $slot }}
    </button>
@endif
