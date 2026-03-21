{{--
    Quick Response Template Selector

    Usage:
    <x-template-selector
        context="checkin"           {{-- checkin, video, ticket, message, notes --}}
        target="replyText"          {{-- Livewire property name to fill --}}
        :contexts="['checkin','video']"  {{-- Optional: show multiple categories --}}
        position="bottom"           {{-- Optional: bottom (default) or top --}}
    />

    The 'target' is the Livewire property that will be set when a template is selected.
    For Alpine-only textareas, use target-el="textarea-id" instead.
--}}

@props([
    'context' => 'checkin',
    'target' => 'replyText',
    'targetEl' => null,
    'contexts' => null,
    'position' => 'bottom',
])

@php
    use App\Services\ResponseTemplateService;

    $contextList = $contexts ?? [$context];
    $allTemplates = ResponseTemplateService::getTemplates();
    $filteredTemplates = collect($allTemplates)->only($contextList)->toArray();
    $selectorId = 'tpl-' . uniqid();
@endphp

<div
    x-data="{
        open: false,
        search: '',
        get filteredCount() {
            if (!this.search) return 999;
            return this.$refs.templateList?.querySelectorAll('[data-template]:not([style*=\'display: none\'])').length ?? 0;
        }
    }"
    class="relative inline-flex"
    x-id="['{{ $selectorId }}']"
>
    {{-- Trigger Button --}}
    <button
        @click="open = !open; $nextTick(() => { if (open) $refs.searchInput?.focus() })"
        type="button"
        class="btn-press inline-flex items-center gap-1.5 rounded-lg bg-wc-bg-secondary px-3 py-1.5 text-xs font-medium text-wc-text-secondary hover:bg-wc-bg-tertiary hover:text-wc-text border border-wc-border transition-colors"
        title="Plantillas de respuesta rapida"
    >
        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
        Plantillas
    </button>

    {{-- Dropdown Panel --}}
    <div
        x-show="open"
        @click.outside="open = false"
        @keydown.escape.window="open = false"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="absolute z-50 w-80 rounded-xl border border-wc-border bg-wc-bg shadow-2xl
               {{ $position === 'top' ? 'bottom-full mb-2' : 'top-full mt-2' }}
               right-0 sm:right-auto sm:left-0"
        x-cloak
    >
        {{-- Search Header --}}
        <div class="border-b border-wc-border p-3">
            <div class="relative">
                <svg class="absolute left-2.5 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                </svg>
                <input
                    x-ref="searchInput"
                    x-model="search"
                    type="text"
                    placeholder="Buscar plantilla..."
                    class="w-full rounded-lg border border-wc-border bg-wc-bg-secondary py-1.5 pl-8 pr-3 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent"
                    @keydown.escape="open = false"
                >
            </div>
        </div>

        {{-- Template List --}}
        <div x-ref="templateList" class="max-h-72 overflow-y-auto p-1.5 scrollbar-thin">
            @foreach($filteredTemplates as $catKey => $category)
                {{-- Category Header --}}
                @if(count($filteredTemplates) > 1)
                    <div class="px-2.5 pb-1 pt-2.5 first:pt-1.5">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-wc-text-tertiary">
                            {{ $category['label'] }}
                        </span>
                    </div>
                @endif

                {{-- Templates --}}
                @foreach($category['templates'] as $idx => $template)
                    <button
                        data-template
                        data-search-text="{{ strtolower($template['title'] . ' ' . $template['body']) }}"
                        x-show="!search || $el.dataset.searchText.includes(search.toLowerCase())"
                        @click="
                            @if($targetEl)
                                document.getElementById('{{ $targetEl }}').value = {{ json_encode($template['body']) }};
                                document.getElementById('{{ $targetEl }}').dispatchEvent(new Event('input', { bubbles: true }));
                            @else
                                $wire.set('{{ $target }}', {{ json_encode($template['body']) }});
                            @endif
                            open = false;
                            search = '';
                        "
                        type="button"
                        class="w-full rounded-lg px-2.5 py-2 text-left transition-colors hover:bg-wc-bg-secondary group"
                    >
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-medium text-wc-text group-hover:text-wc-accent transition-colors">
                                {{ $template['title'] }}
                            </span>
                            @if(count($filteredTemplates) > 1)
                                <span class="rounded-full bg-wc-bg-tertiary px-1.5 py-0.5 text-[9px] font-medium text-wc-text-tertiary">
                                    {{ $category['label'] }}
                                </span>
                            @endif
                        </div>
                        <p class="mt-0.5 text-xs leading-relaxed text-wc-text-tertiary line-clamp-2">
                            {{ $template['body'] }}
                        </p>
                    </button>
                @endforeach
            @endforeach

            {{-- No results --}}
            <div
                x-show="search && !$refs.templateList?.querySelector('[data-template]:not([style*=\'display: none\'])')"
                class="px-3 py-6 text-center"
            >
                <p class="text-xs text-wc-text-tertiary">No se encontraron plantillas para "<span x-text="search" class="font-medium text-wc-text-secondary"></span>"</p>
            </div>
        </div>

        {{-- Footer hint --}}
        <div class="border-t border-wc-border px-3 py-2">
            <p class="text-[10px] text-wc-text-tertiary text-center">
                {{ collect($filteredTemplates)->sum(fn($c) => count($c['templates'])) }} plantillas disponibles
            </p>
        </div>
    </div>
</div>
