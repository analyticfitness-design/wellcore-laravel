{{--
    <x-public.s-divider> — Section divider editorial con label centrado.

    Spec: MASTER-DESIGN-SYSTEM-V2.md §5.5
    CSS: resources/css/v2-public.css (.s-divider)

    Props:
        $label (string)            — texto del label uppercase. Default vacío.
        $align (string)            — left | center | right (alignment del label dentro). Default center.

    Slot:
        Si no se pasa $label, el slot reemplaza el contenido del label.

    Ejemplo:
        <x-public.s-divider label="Comparador · Planes" />
        <x-public.s-divider><span class="text-wc-accent">Sección destacada</span></x-public.s-divider>
--}}
@props([
    'label' => '',
    'align' => 'center',
])

<div {{ $attributes->class(['s-divider', "s-divider-{$align}"]) }}>
    <span class="s-divider-lbl">{{ $label !== '' ? $label : $slot }}</span>
</div>
