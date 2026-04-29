{{--
    <x-public.faq-accordion> — FAQ accordion con search opcional + JSON-LD friendly.

    Spec: MASTER-DESIGN-SYSTEM-V2.md §5 + RULES-RESPONSIVE §10
    CSS: resources/css/v2-public.css (.faq-accordion, .faq-accordion-item, .faq-accordion-summary)

    Usa <details>/<summary> nativo — accesible por keyboard, expandible sin JS.
    Si $search=true se monta un Alpine search filter.

    Props:
        $items (array)   — array de items con keys:
                              q (string)         — pregunta
                              a (string|html)    — respuesta. Se renderiza con {!! !!} (cuidado XSS).
                              cat (string|null)  — categoría opcional para filtros
                              id (string|null)   — id ancla. Si no, se autogenera de slug(q).
        $search (bool)   — agrega input de búsqueda Alpine. Default false.
        $jsonld (bool)   — agrega JSON-LD FAQPage. Default true.

    Ejemplo:
        <x-public.faq-accordion
            :search="true"
            :items="[
                ['q' => '¿Cuánto dura el plan?', 'a' => 'El compromiso mínimo es 3 meses.', 'cat' => 'planes'],
                ['q' => '¿Puedo cancelar?',      'a' => 'Sí, con 15 días de aviso previo.', 'cat' => 'planes'],
            ]"
        />
--}}
@props([
    'items' => [],
    'search' => false,
    'jsonld' => true,
])

@php
    $accordionId = 'faq-' . substr(md5(json_encode(array_column($items, 'q'))), 0, 6);
@endphp

<div
    {{ $attributes->class(['faq-accordion-wrap']) }}
    @if($search)
        x-data="{
            query: '',
            match(text) {
                if (!this.query) return true;
                return text.toLowerCase().includes(this.query.toLowerCase());
            }
        }"
    @endif
>
    @if($search)
        <input
            type="search"
            class="faq-accordion-search"
            placeholder="Buscar pregunta…"
            x-model.debounce.250ms="query"
            aria-label="Buscar en FAQ"
        >
    @endif

    <div class="faq-accordion" id="{{ $accordionId }}">
        @foreach($items as $i => $item)
            @php
                $q = $item['q'] ?? '';
                $a = $item['a'] ?? '';
                $cat = $item['cat'] ?? null;
                $itemId = $item['id'] ?? \Illuminate\Support\Str::slug($q) . '-' . $i;
            @endphp
            <details
                class="faq-accordion-item"
                id="{{ $itemId }}"
                @if($cat) data-cat="{{ $cat }}" @endif
                @if($search) x-show="match(@js($q . ' ' . strip_tags($a)))" x-cloak @endif
            >
                <summary class="faq-accordion-summary">
                    <span>{{ $q }}</span>
                    <svg class="faq-accordion-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" aria-hidden="true">
                        <path d="M12 5v14M5 12h14"/>
                    </svg>
                </summary>
                <div class="faq-accordion-body">{!! $a !!}</div>
            </details>
        @endforeach
    </div>

    @if($jsonld && count($items) > 0)
        <script type="application/ld+json">
            {!! json_encode([
                '@context' => 'https://schema.org',
                '@type' => 'FAQPage',
                'mainEntity' => array_map(function ($item) {
                    return [
                        '@type' => 'Question',
                        'name' => $item['q'] ?? '',
                        'acceptedAnswer' => [
                            '@type' => 'Answer',
                            'text' => strip_tags($item['a'] ?? ''),
                        ],
                    ];
                }, $items),
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
        </script>
    @endif
</div>
