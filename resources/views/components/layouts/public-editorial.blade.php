{{--
    <x-layouts.public-editorial> — Layout editorial long-form con sidebar.

    Spec: MASTER-DESIGN-SYSTEM-V2.md (sidebar §editorial mode)
          IMPLEMENTATION-PLAN-MASTER §"Layouts nuevos"
          RULES-RESPONSIVE.md §4 (Desktop magazine grid)

    Compone <x-layouts.public> como base — hereda nav, footer, atmósfera global,
    fonts, dark-mode toggle. NO duplica chrome.

    Comportamiento responsive:
        - Mobile (<1024px): stack vertical. Sidebar oculto. Chapter-pill sticky top center
          (si se pasa el slot $chapterPill).
        - Desktop (≥1024px): grid 220px sidebar + 1fr main. Sidebar sticky bajo el topbar.
          Chapter-pill oculto (sidebar ya navega).

    Slots:
        $title          (string)            — title de la página (default: 'WellCore Fitness').
        $description    (string)            — meta description.
        $sidebar        (slot)              — contenido del aside editorial (TOC, índice de capítulos).
        $chapterPill    (slot, optional)    — pill mobile sticky top con capítulo activo.

    Slot principal (default $slot): el contenido de la página long-form.

    Ejemplo:
        <x-layouts.public-editorial>
            <x-slot:title>El Método — WellCore</x-slot>
            <x-slot:description>...</x-slot>
            <x-slot:sidebar>
                <nav class="public-editorial-toc">...</nav>
            </x-slot>
            <x-slot:chapterPill>Cap 03 · Volumen progresivo</x-slot>

            <article>
                <x-public.dropcap-paragraph>...</x-public.dropcap-paragraph>
                <x-public.pullquote cite="...">...</x-public.pullquote>
                ...
            </article>
        </x-layouts.public-editorial>
--}}

<x-layouts.public>
    <x-slot:title>{{ $title ?? 'WellCore Fitness' }}</x-slot>
    <x-slot:description>{{ $description ?? '' }}</x-slot>

    {{--
        Editorial root — el x-data del page factory (metodoPage, procesoPage,
        nosotrosPage) DEBE vivir aqui para que englobe los 3 hijos
        (chapterPill, sidebar, main). Antes vivia solo en .public-editorial-main
        y los slots chapterPill + sidebar quedaban fuera del scope → Alpine
        ReferenceError: activePill/scrollProgress/activeChapter is not defined
        (bug detectado en audit Sprint 4).

        Si la pagina no necesita un factory, $pageFactory queda vacio y el
        atributo no se renderea (no afecta paginas no-editoriales).
    --}}
    <div class="public-editorial-root"
        @if(! empty($pageFactory))
            x-data="{{ $pageFactory }}"
            x-init="typeof init === 'function' && init()"
            @beforeunload.window="typeof destroy === 'function' && destroy()"
        @endif
    >
        @isset($chapterPill)
            <div class="public-editorial-chapter-pill" role="status" aria-label="Capítulo actual">
                {{ $chapterPill }}
            </div>
        @endisset

        <div class="public-editorial-wrap">
            <aside class="public-editorial-sidebar" aria-label="Navegación editorial">
                {{ $sidebar ?? '' }}
            </aside>

            <div class="public-editorial-main">
                {{ $slot }}
            </div>
        </div>
    </div>
</x-layouts.public>
