{{--
    <x-public.editorial-sidebar> — Sidebar editorial 220px sticky con progress + nav 8 chapters + CTA.

    Spec: prompt-implementacion-blade.md §10.2
    CSS:  resources/css/v2-public.css (.metodo-sidebar-*)

    Solo visible ≥1024px (CSS responde a la clase del wrapper editorial).
    Estado activo + progress bar manejado por window.metodoPage() (Alpine factory).

    Props:
        $brandSub  (string)            — sublabel pequeño bajo "WELLCORE" (ej: "El Método · 2026").
        $chapters  (array of objects)  — items con keys: id, num, title.
        $ctaHref   (string)            — href del CTA principal del sidebar.
        $ctaText   (string)            — texto del CTA principal.
        $navLinks  (array of objects)  — items con keys: href, text. Footer del sidebar.

    Ejemplo:
        <x-public.editorial-sidebar
            :brand-sub="__('metodo.sidebar.subtitle')"
            :chapters="[
                ['id' => 'cap-hero', 'num' => '00', 'title' => 'Portada'],
                ['id' => 'cap-01',   'num' => '01', 'title' => 'El Problema'],
                ...
            ]"
            :cta-href="route('planes')"
            :cta-text="__('metodo.sidebar.cta')"
            :nav-links="[
                ['href' => route('proceso'), 'text' => 'Proceso'],
                ...
            ]"
        />
--}}
@props([
    'brandSub' => '',
    'chapters' => [],
    'ctaHref' => '#',
    'ctaText' => 'EMPEZAR',
    'navLinks' => [],
    'progressLabel' => 'Progreso',
])

<div class="metodo-sidebar">
    <div class="metodo-sidebar-brand">
        <a href="{{ route('home') }}" aria-label="WellCore Fitness — inicio">
            <div class="metodo-sidebar-brand-name">WELL<span class="accent">CORE</span></div>
            @if($brandSub)
                <div class="metodo-sidebar-brand-sub">{{ $brandSub }}</div>
            @endif
        </a>
    </div>

    <div class="metodo-sidebar-progress-wrap" aria-hidden="true">
        <div class="metodo-sidebar-progress-label">
            <span>{{ $progressLabel }}</span>
            <span x-text="`${Math.round(scrollProgress)}%`">0%</span>
        </div>
        <div class="metodo-sidebar-progress-track">
            <div class="metodo-sidebar-progress-fill" :style="`width: ${scrollProgress}%`"></div>
        </div>
    </div>

    <nav class="metodo-sidebar-nav" aria-label="Capítulos del método">
        @foreach($chapters as $chapter)
            @php
                $chId = $chapter['id'] ?? '';
                $chNum = $chapter['num'] ?? '';
                $chTitle = $chapter['title'] ?? '';
            @endphp
            <a class="metodo-sidebar-nav-item"
               :class="{ active: activeChapter === '{{ $chId }}' }"
               href="#{{ $chId }}"
               data-target="{{ $chId }}"
               @click.prevent="scrollToChapter('{{ $chId }}', $event)">
                <span class="metodo-sidebar-nav-dot" aria-hidden="true"></span>
                <span class="metodo-sidebar-nav-num">{{ $chNum }}</span>
                <span class="metodo-sidebar-nav-title">{{ $chTitle }}</span>
            </a>
        @endforeach
    </nav>

    <div class="metodo-sidebar-footer">
        <a href="{{ $ctaHref }}" class="metodo-sidebar-cta">{{ $ctaText }}</a>
        @if(!empty($navLinks))
            <div class="metodo-sidebar-nav-links">
                @foreach($navLinks as $link)
                    <a href="{{ $link['href'] ?? '#' }}">{{ $link['text'] ?? '' }}</a>
                @endforeach
            </div>
        @endif
    </div>
</div>
