<div x-data="{ expandedGuide: null }" class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="font-display text-2xl tracking-wide text-wc-text sm:text-3xl">RECURSOS DEL COACH</h1>
            <p class="mt-1 text-sm text-wc-text-secondary">Guias, protocolos, herramientas y contenido educativo</p>
        </div>
    </div>

    {{-- Flash message --}}
    @if($flashMessage)
        <div class="flex items-center gap-2 rounded-lg border px-4 py-3 text-sm
                    {{ $flashType === 'success' ? 'border-emerald-500/30 bg-emerald-500/10 text-emerald-400' : 'border-red-500/30 bg-red-500/10 text-red-400' }}">
            @if($flashType === 'success')
                <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
            @else
                <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" /></svg>
            @endif
            {{ $flashMessage }}
        </div>
    @endif

    {{-- Layout: sidebar nav + content --}}
    <div class="flex flex-col gap-6 lg:flex-row">

        {{-- Sidebar navigation --}}
        <nav class="w-full shrink-0 lg:w-56">
            <div class="rounded-xl border border-wc-border bg-wc-bg-secondary p-2 space-y-0.5">
                @php
                    $modules = [
                        ['key' => 'guides', 'label' => 'Guias', 'icon' => 'book-open'],
                        ['key' => 'protocols', 'label' => 'Protocolos', 'icon' => 'clipboard'],
                        ['key' => 'videos', 'label' => 'Videos', 'icon' => 'play-circle'],
                        ['key' => 'articles', 'label' => 'Articulos', 'icon' => 'newspaper'],
                        ['key' => 'tools', 'label' => 'Herramientas', 'icon' => 'wrench'],
                        ['key' => 'academy', 'label' => 'Academia', 'icon' => 'academic-cap'],
                    ];
                @endphp

                @foreach($modules as $mod)
                    <button
                        wire:click="switchModule('{{ $mod['key'] }}')"
                        class="flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-colors
                               {{ $activeModule === $mod['key']
                                   ? 'border-l-2 border-wc-accent bg-wc-accent/10 text-wc-text'
                                   : 'text-wc-text-secondary hover:bg-wc-bg-tertiary hover:text-wc-text' }}"
                    >
                        @switch($mod['icon'])
                            @case('book-open')
                                <svg class="h-4.5 w-4.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" /></svg>
                                @break
                            @case('clipboard')
                                <svg class="h-4.5 w-4.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15a2.25 2.25 0 0 1 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z" /></svg>
                                @break
                            @case('play-circle')
                                <svg class="h-4.5 w-4.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0ZM15.91 11.672a.375.375 0 0 1 0 .656l-5.603 3.113a.375.375 0 0 1-.557-.328V8.887c0-.286.307-.466.557-.327l5.603 3.112Z" /></svg>
                                @break
                            @case('newspaper')
                                <svg class="h-4.5 w-4.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 7.5h1.5m-1.5 3h1.5m-7.5 3h7.5m-7.5 3h7.5m3-9h3.375c.621 0 1.125.504 1.125 1.125V18a2.25 2.25 0 0 1-2.25 2.25M16.5 7.5V18a2.25 2.25 0 0 0 2.25 2.25M16.5 7.5V4.875c0-.621-.504-1.125-1.125-1.125H4.125C3.504 3.75 3 4.254 3 4.875V18a2.25 2.25 0 0 0 2.25 2.25h13.5M6 7.5h3v3H6v-3Z" /></svg>
                                @break
                            @case('wrench')
                                <svg class="h-4.5 w-4.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17 17.25 21A2.652 2.652 0 0 0 21 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 1 1-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 0 0 4.486-6.336l-3.276 3.277a3.004 3.004 0 0 1-2.25-2.25l3.276-3.276a4.5 4.5 0 0 0-6.336 4.486c.048.58.024 1.194-.14 1.743" /></svg>
                                @break
                            @case('academic-cap')
                                <svg class="h-4.5 w-4.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5" /></svg>
                                @break
                        @endswitch
                        {{ $mod['label'] }}
                    </button>
                @endforeach
            </div>
        </nav>

        {{-- Content area --}}
        <div class="min-w-0 flex-1">

            {{-- ============================================================ --}}
            {{--  GUIDES MODULE                                                --}}
            {{-- ============================================================ --}}
            @if($activeModule === 'guides')
                <div class="space-y-4">
                    <div class="flex items-center gap-2">
                        <svg class="h-5 w-5 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" /></svg>
                        <h2 class="font-display text-xl tracking-wide text-wc-text">GUIAS DE COACHING</h2>
                    </div>
                    <p class="text-sm text-wc-text-secondary">Material de referencia para tu practica diaria como coach.</p>

                    @foreach($guides as $index => $guide)
                        <div class="rounded-xl border border-wc-border bg-wc-bg-secondary overflow-hidden">
                            {{-- Guide header (clickable) --}}
                            <button
                                x-on:click="expandedGuide === {{ $index }} ? expandedGuide = null : expandedGuide = {{ $index }}"
                                class="flex w-full items-center justify-between px-5 py-4 text-left hover:bg-wc-bg-tertiary transition-colors"
                            >
                                <div class="flex items-center gap-3">
                                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-wc-accent/10">
                                        @switch($guide['icon'])
                                            @case('clipboard-check')
                                                <svg class="h-5 w-5 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M11.35 3.836c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15a2.25 2.25 0 0 1 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m8.9-4.414c.376.023.75.05 1.124.08 1.131.094 1.976 1.057 1.976 2.192V16.5A2.25 2.25 0 0 1 18 18.75h-2.25m-7.5-10.5H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V18.75m-7.5-10.5h6.375c.621 0 1.125.504 1.125 1.125v9.375m-8.25-3 1.5 1.5 3-3.75" /></svg>
                                                @break
                                            @case('adjustments')
                                                <svg class="h-5 w-5 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-9.75 0h9.75" /></svg>
                                                @break
                                            @case('chat')
                                                <svg class="h-5 w-5 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 0 1-.825-.242m9.345-8.334a2.126 2.126 0 0 0-.476-.095 48.64 48.64 0 0 0-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0 0 11.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155" /></svg>
                                                @break
                                            @case('shield')
                                                <svg class="h-5 w-5 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" /></svg>
                                                @break
                                            @case('nutrition')
                                                <svg class="h-5 w-5 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v5.714a2.25 2.25 0 0 1-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 0 1 4.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0 1 12 15a9.065 9.065 0 0 0-6.23.693L5 14.5m14.8.8 1.402 1.402c1.232 1.232.65 3.318-1.067 3.611A48.309 48.309 0 0 1 12 21c-2.773 0-5.491-.235-8.135-.687-1.718-.293-2.3-2.379-1.067-3.61L5 14.5" /></svg>
                                                @break
                                        @endswitch
                                    </div>
                                    <span class="font-semibold text-wc-text">{{ $guide['title'] }}</span>
                                </div>
                                <svg
                                    class="h-5 w-5 text-wc-text-secondary transition-transform duration-200"
                                    :class="expandedGuide === {{ $index }} ? 'rotate-180' : ''"
                                    fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                                </svg>
                            </button>

                            {{-- Expandable content --}}
                            <div
                                x-show="expandedGuide === {{ $index }}"
                                x-collapse
                                class="border-t border-wc-border"
                            >
                                <div class="space-y-4 px-5 py-4">
                                    @foreach($guide['sections'] as $section)
                                        <div>
                                            <h4 class="mb-1 text-sm font-semibold text-wc-accent">{{ $section['heading'] }}</h4>
                                            <p class="text-sm leading-relaxed text-wc-text-secondary">{{ $section['content'] }}</p>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

            {{-- ============================================================ --}}
            {{--  PROTOCOLS MODULE                                             --}}
            {{-- ============================================================ --}}
            @elseif($activeModule === 'protocols')
                <div class="space-y-4">
                    <div class="flex items-center gap-2">
                        <svg class="h-5 w-5 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15a2.25 2.25 0 0 1 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z" /></svg>
                        <h2 class="font-display text-xl tracking-wide text-wc-text">PROTOCOLOS Y TEMPLATES</h2>
                    </div>
                    <p class="text-sm text-wc-text-secondary">Templates de planes accesibles para ti. Usa estos como base para crear planes personalizados.</p>

                    @if($templates->isEmpty())
                        <div class="flex flex-col items-center justify-center rounded-xl border border-dashed border-wc-border bg-wc-bg-secondary py-16">
                            <svg class="h-12 w-12 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>
                            <p class="mt-3 text-sm font-medium text-wc-text-secondary">No hay templates disponibles aun</p>
                            <p class="mt-1 text-xs text-wc-text-tertiary">Los templates creados por ti o marcados como publicos apareceran aqui.</p>
                        </div>
                    @else
                        <div class="grid gap-4 sm:grid-cols-2">
                            @foreach($templates as $template)
                                <div x-data="{ open: false }" class="rounded-xl border border-wc-border bg-wc-bg-secondary overflow-hidden">
                                    <div class="p-4">
                                        <div class="flex items-start justify-between gap-2">
                                            <div class="min-w-0 flex-1">
                                                <h3 class="font-semibold text-wc-text truncate">{{ $template->name }}</h3>
                                                <div class="mt-1 flex flex-wrap items-center gap-2">
                                                    <span class="inline-flex rounded-full bg-wc-accent/10 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-wc-accent">
                                                        {{ $template->plan_type }}
                                                    </span>
                                                    @if($template->methodology)
                                                        <span class="text-xs text-wc-text-tertiary">{{ $template->methodology }}</span>
                                                    @endif
                                                    @if($template->is_public)
                                                        <span class="inline-flex rounded-full bg-blue-500/10 px-2 py-0.5 text-[10px] font-semibold text-blue-400">Publico</span>
                                                    @endif
                                                    @if($template->ai_generated)
                                                        <span class="inline-flex rounded-full bg-purple-500/10 px-2 py-0.5 text-[10px] font-semibold text-purple-400">IA</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        @if($template->description)
                                            <p class="mt-2 text-xs leading-relaxed text-wc-text-secondary line-clamp-2">{{ $template->description }}</p>
                                        @endif
                                        <div class="mt-3 flex items-center justify-between">
                                            <span class="text-[10px] text-wc-text-tertiary">{{ $template->created_at?->diffForHumans() ?? '' }}</span>
                                            <button
                                                x-on:click="open = !open"
                                                class="text-xs font-medium text-wc-accent hover:text-wc-accent/80 transition-colors"
                                            >
                                                <span x-text="open ? 'Ocultar' : 'Ver contenido'"></span>
                                            </button>
                                        </div>
                                    </div>
                                    {{-- Preview content --}}
                                    <div x-show="open" x-collapse class="border-t border-wc-border bg-wc-bg-tertiary/50 px-4 py-3">
                                        @if(is_array($template->content_json))
                                            <pre class="max-h-60 overflow-auto text-xs text-wc-text-secondary font-mono whitespace-pre-wrap">{{ json_encode($template->content_json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                        @else
                                            <p class="text-xs text-wc-text-tertiary">Sin contenido detallado</p>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

            {{-- ============================================================ --}}
            {{--  VIDEOS MODULE                                                --}}
            {{-- ============================================================ --}}
            @elseif($activeModule === 'videos')
                <div class="space-y-4">
                    <div class="flex items-center gap-2">
                        <svg class="h-5 w-5 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0ZM15.91 11.672a.375.375 0 0 1 0 .656l-5.603 3.113a.375.375 0 0 1-.557-.328V8.887c0-.286.307-.466.557-.327l5.603 3.112Z" /></svg>
                        <h2 class="font-display text-xl tracking-wide text-wc-text">VIDEO TIPS</h2>
                    </div>
                    <p class="text-sm text-wc-text-secondary">Videos cortos con tips y demostraciones para compartir con clientes.</p>

                    @if($videoTips->isEmpty())
                        <div class="flex flex-col items-center justify-center rounded-xl border border-dashed border-wc-border bg-wc-bg-secondary py-16">
                            <svg class="h-12 w-12 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m15.75 10.5 4.72-4.72a.75.75 0 0 1 1.28.53v11.38a.75.75 0 0 1-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 0 0 2.25-2.25v-9a2.25 2.25 0 0 0-2.25-2.25h-9A2.25 2.25 0 0 0 2.25 7.5v9a2.25 2.25 0 0 0 2.25 2.25Z" /></svg>
                            <p class="mt-3 text-sm font-medium text-wc-text-secondary">No hay video tips aun</p>
                            <p class="mt-1 text-xs text-wc-text-tertiary">Los video tips activos se mostraran aqui.</p>
                        </div>
                    @else
                        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach($videoTips as $tip)
                                <div class="group rounded-xl border border-wc-border bg-wc-bg-secondary overflow-hidden hover:border-wc-accent/30 transition-colors">
                                    {{-- Thumbnail --}}
                                    <div class="relative aspect-video bg-wc-bg-tertiary">
                                        @if($tip->thumbnail_url)
                                            <img src="{{ $tip->thumbnail_url }}" alt="{{ $tip->title }}" class="h-full w-full object-cover" loading="lazy" decoding="async">
                                        @else
                                            <div class="flex h-full w-full items-center justify-center">
                                                <svg class="h-12 w-12 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0ZM15.91 11.672a.375.375 0 0 1 0 .656l-5.603 3.113a.375.375 0 0 1-.557-.328V8.887c0-.286.307-.466.557-.327l5.603 3.112Z" /></svg>
                                            </div>
                                        @endif
                                        {{-- Play overlay --}}
                                        <a href="{{ $tip->video_url }}" target="_blank"
                                           class="absolute inset-0 flex items-center justify-center bg-black/30 opacity-0 group-hover:opacity-100 transition-opacity">
                                            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-wc-accent/90">
                                                <svg class="h-6 w-6 text-white ml-0.5" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                                            </div>
                                        </a>
                                        {{-- Duration badge --}}
                                        @if($tip->duration_sec > 0)
                                            <span class="absolute bottom-2 right-2 rounded bg-black/70 px-1.5 py-0.5 text-[10px] font-mono text-white">
                                                {{ floor($tip->duration_sec / 60) }}:{{ str_pad($tip->duration_sec % 60, 2, '0', STR_PAD_LEFT) }}
                                            </span>
                                        @endif
                                    </div>
                                    <div class="p-3">
                                        <h3 class="text-sm font-semibold text-wc-text line-clamp-2">{{ $tip->title }}</h3>
                                        <p class="mt-1 text-[10px] text-wc-text-tertiary">{{ $tip->created_at?->diffForHumans() ?? '' }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

            {{-- ============================================================ --}}
            {{--  ARTICLES MODULE                                              --}}
            {{-- ============================================================ --}}
            @elseif($activeModule === 'articles')
                <div class="space-y-4">
                    <div class="flex items-center gap-2">
                        <svg class="h-5 w-5 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 7.5h1.5m-1.5 3h1.5m-7.5 3h7.5m-7.5 3h7.5m3-9h3.375c.621 0 1.125.504 1.125 1.125V18a2.25 2.25 0 0 1-2.25 2.25M16.5 7.5V18a2.25 2.25 0 0 0 2.25 2.25M16.5 7.5V4.875c0-.621-.504-1.125-1.125-1.125H4.125C3.504 3.75 3 4.254 3 4.875V18a2.25 2.25 0 0 0 2.25 2.25h13.5M6 7.5h3v3H6v-3Z" /></svg>
                        <h2 class="font-display text-xl tracking-wide text-wc-text">ARTICULOS Y CONOCIMIENTO</h2>
                    </div>
                    <p class="text-sm text-wc-text-secondary">Posts de la comunidad de coaches y base de conocimiento.</p>

                    @if($articles->isEmpty())
                        <div class="flex flex-col items-center justify-center rounded-xl border border-dashed border-wc-border bg-wc-bg-secondary py-16">
                            <svg class="h-12 w-12 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 7.5h1.5m-1.5 3h1.5m-7.5 3h7.5m-7.5 3h7.5m3-9h3.375c.621 0 1.125.504 1.125 1.125V18a2.25 2.25 0 0 1-2.25 2.25M16.5 7.5V18a2.25 2.25 0 0 0 2.25 2.25M16.5 7.5V4.875c0-.621-.504-1.125-1.125-1.125H4.125C3.504 3.75 3 4.254 3 4.875V18a2.25 2.25 0 0 0 2.25 2.25h13.5M6 7.5h3v3H6v-3Z" /></svg>
                            <p class="mt-3 text-sm font-medium text-wc-text-secondary">No hay articulos publicados</p>
                            <p class="mt-1 text-xs text-wc-text-tertiary">Los posts y tips de coaches se mostraran aqui.</p>
                        </div>
                    @else
                        <div class="space-y-3">
                            @foreach($articles as $article)
                                <div class="rounded-xl border border-wc-border bg-wc-bg-secondary p-4 hover:border-wc-accent/20 transition-colors">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="min-w-0 flex-1">
                                            <div class="flex items-center gap-2 mb-2">
                                                <span class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide
                                                    {{ match($article->type) {
                                                        'tip' => 'bg-amber-500/10 text-amber-400',
                                                        'achievement' => 'bg-emerald-500/10 text-emerald-400',
                                                        default => 'bg-blue-500/10 text-blue-400',
                                                    } }}">
                                                    {{ match($article->type) { 'tip' => 'Tip', 'achievement' => 'Logro', default => 'Post' } }}
                                                </span>
                                                <span class="text-[10px] text-wc-text-tertiary">{{ $article->created_at?->diffForHumans() ?? '' }}</span>
                                            </div>
                                            <p class="text-sm leading-relaxed text-wc-text-secondary">
                                                {{ \Illuminate\Support\Str::limit($article->content, 300) }}
                                            </p>
                                        </div>
                                        @if($article->likes > 0)
                                            <div class="flex shrink-0 items-center gap-1 text-wc-text-tertiary">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" /></svg>
                                                <span class="text-xs font-medium">{{ $article->likes }}</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

            {{-- ============================================================ --}}
            {{--  TOOLS MODULE                                                 --}}
            {{-- ============================================================ --}}
            @elseif($activeModule === 'tools')
                <div class="space-y-4">
                    <div class="flex items-center gap-2">
                        <svg class="h-5 w-5 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17 17.25 21A2.652 2.652 0 0 0 21 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 1 1-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 0 0 4.486-6.336l-3.276 3.277a3.004 3.004 0 0 1-2.25-2.25l3.276-3.276a4.5 4.5 0 0 0-6.336 4.486c.048.58.024 1.194-.14 1.743" /></svg>
                        <h2 class="font-display text-xl tracking-wide text-wc-text">HERRAMIENTAS</h2>
                    </div>
                    <p class="text-sm text-wc-text-secondary">Herramientas disponibles en la plataforma que puedes recomendar a tus clientes.</p>

                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach($tools as $tool)
                            <a href="{{ route($tool['route']) }}" target="_blank"
                               class="group flex flex-col rounded-xl border border-wc-border bg-wc-bg-secondary p-5 hover:border-wc-accent/30 hover:bg-wc-bg-tertiary/50 transition-all">
                                <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-lg bg-wc-accent/10 group-hover:bg-wc-accent/20 transition-colors">
                                    @switch($tool['icon'])
                                        @case('calculator')
                                            <svg class="h-5 w-5 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 15.75V18m-7.5-6.75h.008v.008H8.25v-.008Zm0 2.25h.008v.008H8.25V13.5Zm0 2.25h.008v.008H8.25v-.008Zm0 2.25h.008v.008H8.25V18Zm2.498-6.75h.007v.008h-.007v-.008Zm0 2.25h.007v.008h-.007V13.5Zm0 2.25h.007v.008h-.007v-.008Zm0 2.25h.007v.008h-.007V18Zm2.504-6.75h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V13.5Zm0 2.25h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V18Zm2.498-6.75h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V13.5ZM8.25 6h7.5v2.25h-7.5V6ZM12 2.25c-1.892 0-3.758.11-5.593.322C5.307 2.7 4.5 3.65 4.5 4.757V19.5a2.25 2.25 0 0 0 2.25 2.25h10.5a2.25 2.25 0 0 0 2.25-2.25V4.757c0-1.108-.806-2.057-1.907-2.185A48.507 48.507 0 0 0 12 2.25Z" /></svg>
                                            @break
                                        @case('clock')
                                            <svg class="h-5 w-5 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                                            @break
                                        @case('sparkles')
                                            <svg class="h-5 w-5 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.455 2.456L21.75 6l-1.036.259a3.375 3.375 0 0 0-2.455 2.456ZM16.894 20.567 16.5 21.75l-.394-1.183a2.25 2.25 0 0 0-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 0 0 1.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 0 0 1.423 1.423l1.183.394-1.183.394a2.25 2.25 0 0 0-1.423 1.423Z" /></svg>
                                            @break
                                        @case('book-open')
                                            <svg class="h-5 w-5 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" /></svg>
                                            @break
                                        @case('trophy')
                                            <svg class="h-5 w-5 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 0 1 3 3h-15a3 3 0 0 1 3-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.007 0H9.497m5.007 0a7.454 7.454 0 0 1-.982-3.172M9.497 14.25a7.454 7.454 0 0 0 .981-3.172M5.25 4.236c-.982.143-1.954.317-2.916.52A6.003 6.003 0 0 0 7.73 9.728M5.25 4.236V4.5c0 2.108.966 3.99 2.48 5.228M5.25 4.236V2.721C7.456 2.41 9.71 2.25 12 2.25c2.291 0 4.545.16 6.75.47v1.516M18.75 4.236c.982.143 1.954.317 2.916.52A6.003 6.003 0 0 1 16.27 9.728M18.75 4.236V4.5c0 2.108-.966 3.99-2.48 5.228m0 0a6.023 6.023 0 0 1-3.52 1.522m0 0a6.023 6.023 0 0 1-3.52-1.522" /></svg>
                                            @break
                                        @case('play')
                                            <svg class="h-5 w-5 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0ZM15.91 11.672a.375.375 0 0 1 0 .656l-5.603 3.113a.375.375 0 0 1-.557-.328V8.887c0-.286.307-.466.557-.327l5.603 3.112Z" /></svg>
                                            @break
                                    @endswitch
                                </div>
                                <h3 class="font-semibold text-wc-text">{{ $tool['title'] }}</h3>
                                <p class="mt-1 flex-1 text-xs leading-relaxed text-wc-text-secondary">{{ $tool['description'] }}</p>
                                <div class="mt-3 flex items-center gap-1 text-xs font-medium text-wc-accent">
                                    Abrir herramienta
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" /></svg>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>

            {{-- ============================================================ --}}
            {{--  ACADEMY MODULE (CRUD)                                        --}}
            {{-- ============================================================ --}}
            @elseif($activeModule === 'academy')
                <div class="space-y-4">
                    <div class="flex items-center justify-between gap-4">
                        <div class="flex items-center gap-2">
                            <svg class="h-5 w-5 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5" /></svg>
                            <h2 class="font-display text-xl tracking-wide text-wc-text">ACADEMIA</h2>
                        </div>
                        <button
                            wire:click="openCreateModal"
                            class="inline-flex items-center gap-2 rounded-lg bg-wc-accent px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-wc-accent/90 transition-colors"
                        >
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                            Nuevo Contenido
                        </button>
                    </div>
                    <p class="text-sm text-wc-text-secondary">Gestiona el contenido educativo que tus clientes ven en la seccion Academia.</p>

                    @if($academyItems->isEmpty())
                        <div class="flex flex-col items-center justify-center rounded-xl border border-dashed border-wc-border bg-wc-bg-secondary py-16">
                            <svg class="h-12 w-12 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5" /></svg>
                            <p class="mt-3 text-sm font-medium text-wc-text-secondary">No hay contenido en la Academia</p>
                            <p class="mt-1 text-xs text-wc-text-tertiary">Crea tu primer contenido educativo para los clientes.</p>
                            <button
                                wire:click="openCreateModal"
                                class="mt-4 inline-flex items-center gap-2 rounded-lg bg-wc-accent px-4 py-2 text-sm font-semibold text-white hover:bg-wc-accent/90 transition-colors"
                            >
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                                Crear contenido
                            </button>
                        </div>
                    @else
                        {{-- Academy content table --}}
                        <div class="overflow-hidden rounded-xl border border-wc-border bg-wc-bg-secondary">
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm">
                                    <thead>
                                        <tr class="border-b border-wc-border bg-wc-bg-tertiary/50">
                                            <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-widest text-wc-text-tertiary">Orden</th>
                                            <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-widest text-wc-text-tertiary">Titulo</th>
                                            <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-widest text-wc-text-tertiary">Categoria</th>
                                            <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-widest text-wc-text-tertiary">Tipo</th>
                                            <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-widest text-wc-text-tertiary">Audiencia</th>
                                            <th class="px-4 py-3 text-center text-[10px] font-semibold uppercase tracking-widest text-wc-text-tertiary">Estado</th>
                                            <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-widest text-wc-text-tertiary">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-wc-border">
                                        @foreach($academyItems as $item)
                                            <tr class="hover:bg-wc-bg-tertiary/30 transition-colors" wire:key="academy-{{ $item->id }}">
                                                {{-- Order + reorder --}}
                                                <td class="px-4 py-3">
                                                    <div class="flex items-center gap-1">
                                                        <span class="font-mono text-xs text-wc-text-tertiary">{{ $item->sort_order }}</span>
                                                        <div class="flex flex-col">
                                                            <button wire:click="moveUp({{ $item->id }})" class="text-wc-text-tertiary hover:text-wc-accent p-0.5" title="Subir">
                                                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 15.75 7.5-7.5 7.5 7.5" /></svg>
                                                            </button>
                                                            <button wire:click="moveDown({{ $item->id }})" class="text-wc-text-tertiary hover:text-wc-accent p-0.5" title="Bajar">
                                                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </td>
                                                {{-- Title --}}
                                                <td class="px-4 py-3">
                                                    <span class="font-medium text-wc-text">{{ $item->title }}</span>
                                                    @if($item->description)
                                                        <p class="mt-0.5 text-[10px] text-wc-text-tertiary line-clamp-1">{{ $item->description }}</p>
                                                    @endif
                                                </td>
                                                {{-- Category --}}
                                                <td class="px-4 py-3">
                                                    <span class="inline-flex rounded-full bg-wc-accent/10 px-2 py-0.5 text-[10px] font-semibold uppercase text-wc-accent">
                                                        {{ $item->category }}
                                                    </span>
                                                </td>
                                                {{-- Type --}}
                                                <td class="px-4 py-3">
                                                    <span class="text-xs text-wc-text-secondary">
                                                        {{ match($item->content_type) { 'video' => 'Video', 'pdf' => 'PDF', 'article' => 'Articulo', 'guide' => 'Guia', default => $item->content_type } }}
                                                    </span>
                                                </td>
                                                {{-- Audience --}}
                                                <td class="px-4 py-3">
                                                    <span class="text-xs text-wc-text-secondary capitalize">{{ $item->audience }}</span>
                                                </td>
                                                {{-- Status --}}
                                                <td class="px-4 py-3 text-center">
                                                    <button wire:click="toggleActive({{ $item->id }})" title="{{ $item->active ? 'Desactivar' : 'Activar' }}">
                                                        @if($item->active)
                                                            <span class="inline-flex rounded-full bg-emerald-500/10 px-2 py-0.5 text-[10px] font-semibold text-emerald-400">Activo</span>
                                                        @else
                                                            <span class="inline-flex rounded-full bg-zinc-500/10 px-2 py-0.5 text-[10px] font-semibold text-zinc-400">Inactivo</span>
                                                        @endif
                                                    </button>
                                                </td>
                                                {{-- Actions --}}
                                                <td class="px-4 py-3 text-right">
                                                    <div class="flex items-center justify-end gap-1">
                                                        <button
                                                            wire:click="openEditModal({{ $item->id }})"
                                                            class="rounded-lg p-1.5 text-wc-text-secondary hover:bg-wc-bg-tertiary hover:text-wc-accent transition-colors"
                                                            title="Editar"
                                                        >
                                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125" /></svg>
                                                        </button>
                                                        <button
                                                            wire:click="confirmDelete({{ $item->id }})"
                                                            class="rounded-lg p-1.5 text-wc-text-secondary hover:bg-red-500/10 hover:text-red-400 transition-colors"
                                                            title="Eliminar"
                                                        >
                                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- ==================== Create/Edit Modal ==================== --}}
                @if($showAcademyModal)
                    <div class="fixed inset-0 z-[60] flex items-center justify-center p-4" x-data x-on:keydown.escape.window="$wire.closeModal()">
                        {{-- Backdrop --}}
                        <div class="absolute inset-0 bg-black/60" wire:click="closeModal"></div>

                        {{-- Modal --}}
                        <div class="relative z-10 w-full max-w-2xl max-h-[90vh] overflow-y-auto rounded-2xl border border-wc-border bg-wc-bg-secondary shadow-2xl">
                            <div class="sticky top-0 flex items-center justify-between border-b border-wc-border bg-wc-bg-secondary px-6 py-4 rounded-t-2xl">
                                <h3 class="font-display text-lg tracking-wide text-wc-text">
                                    {{ $isEditing ? 'EDITAR CONTENIDO' : 'NUEVO CONTENIDO' }}
                                </h3>
                                <button wire:click="closeModal" class="rounded-lg p-1.5 text-wc-text-secondary hover:bg-wc-bg-tertiary hover:text-wc-text transition-colors">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                                </button>
                            </div>

                            <form wire:submit="saveAcademy" class="space-y-5 p-6">
                                {{-- Title --}}
                                <div>
                                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-widest text-wc-text-tertiary">Titulo *</label>
                                    <input type="text" wire:model="acTitle"
                                           class="w-full rounded-lg border border-wc-border bg-wc-bg px-3 py-2.5 text-sm text-wc-text placeholder:text-wc-text-tertiary focus:border-wc-accent focus:ring-1 focus:ring-wc-accent focus:outline-none"
                                           placeholder="Titulo del contenido">
                                    @error('acTitle') <span class="mt-1 text-xs text-red-400">{{ $message }}</span> @enderror
                                </div>

                                {{-- Category + Content Type row --}}
                                <div class="grid gap-4 sm:grid-cols-2">
                                    <div>
                                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-widest text-wc-text-tertiary">Categoria *</label>
                                        <input type="text" wire:model="acCategory"
                                               class="w-full rounded-lg border border-wc-border bg-wc-bg px-3 py-2.5 text-sm text-wc-text placeholder:text-wc-text-tertiary focus:border-wc-accent focus:ring-1 focus:ring-wc-accent focus:outline-none"
                                               placeholder="ej: nutricion, entrenamiento, mindset">
                                        @error('acCategory') <span class="mt-1 text-xs text-red-400">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-widest text-wc-text-tertiary">Tipo de contenido *</label>
                                        <select wire:model="acContentType"
                                                class="w-full rounded-lg border border-wc-border bg-wc-bg px-3 py-2.5 text-sm text-wc-text focus:border-wc-accent focus:ring-1 focus:ring-wc-accent focus:outline-none">
                                            <option value="article">Articulo</option>
                                            <option value="video">Video</option>
                                            <option value="pdf">PDF</option>
                                            <option value="guide">Guia</option>
                                        </select>
                                    </div>
                                </div>

                                {{-- Audience + Sort Order row --}}
                                <div class="grid gap-4 sm:grid-cols-2">
                                    <div>
                                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-widest text-wc-text-tertiary">Audiencia</label>
                                        <select wire:model="acAudience"
                                                class="w-full rounded-lg border border-wc-border bg-wc-bg px-3 py-2.5 text-sm text-wc-text focus:border-wc-accent focus:ring-1 focus:ring-wc-accent focus:outline-none">
                                            <option value="client">Clientes</option>
                                            <option value="coach">Coaches</option>
                                            <option value="both">Ambos</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-widest text-wc-text-tertiary">Orden</label>
                                        <input type="number" wire:model="acSortOrder" min="0"
                                               class="w-full rounded-lg border border-wc-border bg-wc-bg px-3 py-2.5 text-sm text-wc-text focus:border-wc-accent focus:ring-1 focus:ring-wc-accent focus:outline-none">
                                    </div>
                                </div>

                                {{-- Description --}}
                                <div>
                                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-widest text-wc-text-tertiary">Descripcion</label>
                                    <textarea wire:model="acDescription" rows="2"
                                              class="w-full rounded-lg border border-wc-border bg-wc-bg px-3 py-2.5 text-sm text-wc-text placeholder:text-wc-text-tertiary focus:border-wc-accent focus:ring-1 focus:ring-wc-accent focus:outline-none resize-y"
                                              placeholder="Breve descripcion del contenido"></textarea>
                                </div>

                                {{-- Body HTML --}}
                                <div>
                                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-widest text-wc-text-tertiary">Contenido (HTML)</label>
                                    <textarea wire:model="acBodyHtml" rows="6"
                                              class="w-full rounded-lg border border-wc-border bg-wc-bg px-3 py-2.5 text-sm font-mono text-wc-text placeholder:text-wc-text-tertiary focus:border-wc-accent focus:ring-1 focus:ring-wc-accent focus:outline-none resize-y"
                                              placeholder="<p>Contenido del articulo...</p>"></textarea>
                                </div>

                                {{-- URLs row --}}
                                <div class="grid gap-4 sm:grid-cols-2">
                                    <div>
                                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-widest text-wc-text-tertiary">URL del contenido</label>
                                        <input type="url" wire:model="acContentUrl"
                                               class="w-full rounded-lg border border-wc-border bg-wc-bg px-3 py-2.5 text-sm text-wc-text placeholder:text-wc-text-tertiary focus:border-wc-accent focus:ring-1 focus:ring-wc-accent focus:outline-none"
                                               placeholder="https://...">
                                        @error('acContentUrl') <span class="mt-1 text-xs text-red-400">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-widest text-wc-text-tertiary">URL de thumbnail</label>
                                        <input type="url" wire:model="acThumbnailUrl"
                                               class="w-full rounded-lg border border-wc-border bg-wc-bg px-3 py-2.5 text-sm text-wc-text placeholder:text-wc-text-tertiary focus:border-wc-accent focus:ring-1 focus:ring-wc-accent focus:outline-none"
                                               placeholder="https://...">
                                        @error('acThumbnailUrl') <span class="mt-1 text-xs text-red-400">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                                {{-- Active toggle --}}
                                <div class="flex items-center gap-3">
                                    <button type="button" wire:click="$toggle('acActive')"
                                            class="relative h-6 w-11 rounded-full transition-colors {{ $acActive ? 'bg-wc-accent' : 'bg-wc-bg-tertiary' }}">
                                        <span class="absolute left-0.5 top-0.5 h-5 w-5 rounded-full bg-white shadow transition-transform {{ $acActive ? 'translate-x-5' : '' }}"></span>
                                    </button>
                                    <span class="text-sm text-wc-text-secondary">{{ $acActive ? 'Publicado (activo)' : 'Borrador (inactivo)' }}</span>
                                </div>

                                {{-- Actions --}}
                                <div class="flex items-center justify-end gap-3 pt-2 border-t border-wc-border">
                                    <button type="button" wire:click="closeModal"
                                            class="rounded-lg border border-wc-border bg-wc-bg px-4 py-2 text-sm font-medium text-wc-text-secondary hover:bg-wc-bg-tertiary transition-colors">
                                        Cancelar
                                    </button>
                                    <button type="submit"
                                            wire:loading.attr="disabled"
                                            class="btn-press inline-flex items-center gap-2 rounded-lg bg-wc-accent px-5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-wc-accent/90 transition-colors disabled:opacity-50">
                                        <svg wire:loading.remove class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                                        <svg wire:loading class="h-4 w-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                                        <span wire:loading.remove>{{ $isEditing ? 'Guardar Cambios' : 'Crear Contenido' }}</span>
                                        <span wire:loading>Guardando...</span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                @endif

                {{-- ==================== Delete Confirmation ==================== --}}
                @if($showDeleteConfirm)
                    <div class="fixed inset-0 z-[60] flex items-center justify-center p-4">
                        <div class="absolute inset-0 bg-black/60" wire:click="cancelDelete"></div>
                        <div class="relative z-10 w-full max-w-sm rounded-2xl border border-wc-border bg-wc-bg-secondary p-6 shadow-2xl">
                            <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-red-500/10">
                                <svg class="h-6 w-6 text-red-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" /></svg>
                            </div>
                            <h3 class="font-semibold text-wc-text">Eliminar contenido</h3>
                            <p class="mt-2 text-sm text-wc-text-secondary">Estas seguro de que deseas eliminar <span class="font-medium text-wc-text">"{{ $deletingTitle }}"</span>? Esta accion no se puede deshacer.</p>
                            <div class="mt-5 flex items-center justify-end gap-3">
                                <button wire:click="cancelDelete"
                                        class="rounded-lg border border-wc-border bg-wc-bg px-4 py-2 text-sm font-medium text-wc-text-secondary hover:bg-wc-bg-tertiary transition-colors">
                                    Cancelar
                                </button>
                                <button wire:click="deleteAcademy"
                                        class="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700 transition-colors">
                                    Eliminar
                                </button>
                            </div>
                        </div>
                    </div>
                @endif

            @endif
        </div>
    </div>
</div>
