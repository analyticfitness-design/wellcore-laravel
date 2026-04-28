{{--
    <x-public.pullquote> — Pull-quote brutal Bebas Neue all-caps.

    Spec: MASTER-DESIGN-SYSTEM-V2.md §5.1
    CSS: resources/css/v2-public.css (.pullquote)

    Props:
        $cite (string|null)   — sub-line citation con formato JetBrains Mono uppercase.

    Slot:
        Texto principal. Usar <em>...</em> para palabras coloreadas en rojo accent
        (CSS lo desactiva el italic, mantiene el color). Usar <br> para line breaks
        manuales si querés controlar la composición visual.

    Ejemplo:
        <x-public.pullquote cite="WellCore · El Problema">
            NO ES FALTA DE<br><em>VOLUNTAD.</em><br>ES FALTA DE<br>ESTRUCTURA.
        </x-public.pullquote>
--}}
@props([
    'cite' => null,
])

<div {{ $attributes->class(['pullquote']) }} data-animate="fadeInUp">
    <p class="pullquote-text">{!! $slot !!}</p>
    @if($cite)
        <p class="pullquote-cite">{{ $cite }}</p>
    @endif
</div>
