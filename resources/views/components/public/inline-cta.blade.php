{{--
    <x-public.inline-cta> — CTA editorial intercalado entre capítulos long-form.

    Spec: prompt-implementacion-blade.md §10 (InlineCTA × 3)
    CSS:  resources/css/v2-public.css (.inline-cta-v2)

    Bloque pleno-ancho dentro del flujo del artículo: kicker mono + título Oswald +
    botón rojo. En desktop alinea los items en row.

    Props:
        $label    (string)        — kicker JetBrains Mono uppercase (ej: "Siguiente paso").
        $title    (string)        — título grande Oswald (ej: "VER MI PLAN PERSONALIZADO").
        $href     (string)        — destino del CTA primario.
        $ctaText  (string)        — texto del botón (ej: "Ver mi plan →").
        $secondaryHref (string|null) — segundo CTA opcional (ghost button).
        $secondaryText (string|null) — texto del segundo CTA.

    Ejemplo:
        <x-public.inline-cta
            :label="__('metodo.inline_ctas.c1.label')"
            :title="__('metodo.inline_ctas.c1.title')"
            :href="route('planes')"
            :cta-text="__('metodo.inline_ctas.c1.btn')"
        />
--}}
@props([
    'label' => '',
    'title' => '',
    'href' => '#',
    'ctaText' => 'Empezar',
    'secondaryHref' => null,
    'secondaryText' => null,
])

<div {{ $attributes->class(['inline-cta-v2']) }} data-animate="fadeInUp">
    @if($label)
        <p class="inline-cta-v2-label">{{ $label }}</p>
    @endif
    @if($title)
        <p class="inline-cta-v2-title">{{ $title }}</p>
    @endif
    <div class="inline-cta-v2-actions">
        <a href="{{ $href }}" class="btn-primary-v2">
            <span>{{ $ctaText }}</span>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M5 12h14M13 5l7 7-7 7"/>
            </svg>
        </a>
        @if($secondaryHref && $secondaryText)
            <a href="{{ $secondaryHref }}" class="btn-ghost-v2">{{ $secondaryText }}</a>
        @endif
    </div>
</div>
