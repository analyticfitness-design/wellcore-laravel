{{--
    <x-public.chapter-header> — Header editorial pre-capítulo: número/eyebrow + título display.

    Spec: prompt-implementacion-blade.md §10 (Cap headers)
    CSS:  resources/css/v2-public.css (.chapter-header-v2)

    Props:
        $numText  (string)  — eyebrow JetBrains Mono mayúsculas: "01 · Por qué la mayoría falla".
        $titleHtml (string) — título Oswald grande con <em> rojo permitido y <br> manual.

    Ejemplo:
        <x-public.chapter-header
            num-text="01 · Por qué la mayoría falla"
            title-html="EL<br><em>PROBLEMA</em>"
        />
--}}
@props([
    'numText' => '',
    'titleHtml' => '',
])

<div {{ $attributes->class(['chapter-header-v2']) }} data-animate="fadeInUp">
    @if($numText !== '')
        <div class="chapter-header-num">{{ $numText }}</div>
    @endif
    @if($titleHtml !== '')
        <h2 class="chapter-header-title">{!! $titleHtml !!}</h2>
    @endif
</div>
