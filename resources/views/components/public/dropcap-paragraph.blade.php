{{--
    <x-public.dropcap-paragraph> — Primer párrafo editorial con drop-cap roja.

    Spec: MASTER-DESIGN-SYSTEM-V2.md §5.6
    CSS: resources/css/v2-public.css (.dropcap-paragraph)

    Uso típico: primer párrafo de una sección long-form (metodo, nosotros, blog).
    El primer carácter del slot se renderiza con Bebas Neue 5.5rem flotante a la
    izquierda. NO funciona con texto que empiece con whitespace o tags HTML —
    el carácter literal debe estar al inicio.

    Slot:
        HTML/texto del párrafo. Se permite <strong>, <em>.

    Ejemplo:
        <x-public.dropcap-paragraph>
            El ochenta por ciento de las personas que comienzan un programa de
            ejercicio lo abandonan antes de los tres meses. <strong>No es falta
            de voluntad. Es falta de arquitectura.</strong>
        </x-public.dropcap-paragraph>
--}}
<p {{ $attributes->class(['dropcap-paragraph']) }}>{!! $slot !!}</p>
