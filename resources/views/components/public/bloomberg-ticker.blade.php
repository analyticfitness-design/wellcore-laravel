{{--
    <x-public.bloomberg-ticker> — Ticker scroll horizontal infinito tipo Bloomberg.

    Spec: MASTER-DESIGN-SYSTEM-V2.md §5.3
    CSS: resources/css/v2-public.css (.ticker-wrap, .ticker-track, .ticker-item)

    Loop seamless: duplicamos los items en el render para que la animación
    translateX(-50%) cierre sin salto visual.

    Hover desktop pausa el scroll (animation-play-state: paused).
    prefers-reduced-motion: reduce desactiva el ticker (override en v2-public.css).

    Props:
        $items (array)   — array de items con keys:
                              name    (string)  — nombre o ticker símbolo
                              metric  (string)  — métrica destacada (ej: "−8 KG", "$450k")
                              detail  (string)  — sub-texto pequeño JetBrains Mono
                              negative (bool, opcional) — pinta metric en rojo en vez de verde
        $duration (int)  — segundos de un loop completo. Default 30.
        $aria_label (string) — accessibility label. Default "Testimonios y métricas".

    Ejemplo:
        <x-public.bloomberg-ticker
            :items="[
                ['name' => 'CR · Bogotá',     'metric' => '−8 KG',  'detail' => '12 SEM · MÉTODO'],
                ['name' => 'SM · Medellín',   'metric' => '+12% FUERZA', 'detail' => '8 SEM · ESENCIAL'],
                ['name' => 'VT · Cartagena',  'metric' => '−15%',   'detail' => 'GRASA CORP · ELITE', 'negative' => true],
            ]"
        />
--}}
@props([
    'items' => [],
    'duration' => 30,
    'aria_label' => 'Testimonios y métricas',
])

@php
    // Duplicamos los items para el loop infinito sin salto.
    $loopItems = array_merge($items, $items);
@endphp

<div {{ $attributes->class(['ticker-wrap']) }}
     role="marquee"
     aria-label="{{ $aria_label }}">
    <div class="ticker-track" style="animation-duration: {{ (int) $duration }}s;">
        @foreach($loopItems as $item)
            <div class="ticker-item">
                <span class="ticker-name">{{ $item['name'] ?? '' }}</span>
                <span class="ticker-metric {{ ($item['negative'] ?? false) ? 'neg' : '' }}">{{ $item['metric'] ?? '' }}</span>
                <span class="ticker-detail">{{ $item['detail'] ?? '' }}</span>
            </div>
        @endforeach
    </div>
</div>
