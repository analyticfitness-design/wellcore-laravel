{{--
    <x-public.sticky-mobile-cta> — CTA fijo bottom mobile con visibility por scroll.

    Spec: MASTER-DESIGN-SYSTEM-V2.md §6.2 + RULES-RESPONSIVE §6
    CSS: resources/css/v2-public.css (.sticky-mobile-cta-v2)

    DIFERENTE del .hp-sticky-cta legacy (pill flotante). Este es full-bleed con
    gradient mask editorial. El legacy permanece para páginas que ya lo usan.

    Comportamiento:
        - Aparece cuando scrollY > umbral (default 50% viewport).
        - Se oculta automáticamente cuando un elemento con id $hideAt entra al viewport
          (típicamente el CTAFinal de la página) — usa @alpinejs/intersect.
        - Hidden en desktop (≥1024px) via CSS.

    Props:
        $href      (string, required)
        $label     (string, required)
        $price     (string|null) — sub-line opcional con precio (Barlow tabular).
        $hideAt    (string|null) — id del elemento que cuando entra al viewport oculta el CTA.
        $variant   (string|null) — null (default rojo) | 'fit' (rosa Silvia, redundante si body tiene .fit-page).
        $threshold (int)         — pixels desde top antes de mostrar. Default 600.

    Ejemplo:
        <x-public.sticky-mobile-cta
            href="{{ route('inscripcion') }}"
            label="Empezar mi plan"
            price="desde $254.150 COP/mes"
            hide-at="cta-final"
            threshold="600"
        />
--}}
@props([
    'href' => '#',
    'label' => 'Empezar',
    'price' => null,
    'hideAt' => null,
    'variant' => null,
    'threshold' => 600,
])

<div
    x-data="{
        visible: false,
        passedFinal: false,
        onScroll() {
            this.visible = window.scrollY > {{ (int) $threshold }};
        }
    }"
    x-init="onScroll(); window.addEventListener('scroll', () => onScroll(), { passive: true });"
    @if($hideAt)
        x-on:cta-final-in.window="passedFinal = true"
        x-on:cta-final-out.window="passedFinal = false"
    @endif
    :class="(!visible || passedFinal) ? 'is-hidden' : ''"
    {{ $attributes->class(['sticky-mobile-cta-v2', $variant === 'fit' ? 'fit-variant' : '']) }}
    role="region"
    aria-label="Acción principal"
>
    <a href="{{ $href }}" class="sticky-cta-pill">
        <span class="sticky-cta-label">{{ $label }}</span>
        @if($price)
            <span class="sticky-cta-price">{{ $price }}</span>
        @endif
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M5 12h14M13 5l7 7-7 7"/>
        </svg>
    </a>

    @if($hideAt)
        {{-- Sentinel @alpinejs/intersect — emite eventos cuando $hideAt entra/sale del viewport.
             El sticky CTA escucha esos eventos y se oculta cuando el CTA Final está visible
             (evita doble-CTA pegado en pantalla). Se monta tras el slot del CTA target en el blade. --}}
        <div
            x-intersect:enter.window="$dispatch('cta-final-in')"
            x-intersect:leave.window="$dispatch('cta-final-out')"
            data-target="{{ $hideAt }}"
            class="sr-only"
            aria-hidden="true"
        ></div>
    @endif
</div>
