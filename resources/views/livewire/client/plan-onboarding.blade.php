<div>
    @if($showOnboarding)
    <div
        x-data="{
            current: 0,
            total: {{ count($slides) }},
            next() { if (this.current < this.total - 1) this.current++ },
            prev() { if (this.current > 0) this.current-- },
            finish() { $wire.dismissOnboarding() }
        }"
        class="fixed inset-0 z-[70] flex items-center justify-center bg-black/70 backdrop-blur-sm p-4"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-cloak
    >
        <div class="relative w-full max-w-md rounded-2xl border border-wc-border bg-wc-bg-secondary shadow-2xl overflow-hidden">

            {{-- Close --}}
            <button @click="finish()" class="absolute top-4 right-4 z-10 text-wc-text-tertiary hover:text-wc-text transition-colors">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
            </button>

            {{-- Progress bar --}}
            <div class="h-1 bg-wc-bg-tertiary">
                <div class="h-full bg-wc-accent transition-all duration-500" :style="'width: ' + ((current + 1) / total * 100) + '%'"></div>
            </div>

            {{-- Slides --}}
            <div class="p-6 sm:p-8">
                @foreach($slides as $index => $slide)
                    <div x-show="current === {{ $index }}"
                        x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0 translate-x-4"
                        x-transition:enter-end="opacity-100 translate-x-0"
                        class="flex flex-col items-center text-center"
                    >
                        {{-- Icon --}}
                        <div class="mb-5 flex h-16 w-16 items-center justify-center rounded-2xl
                            {{ str_contains($slide['color'], 'wc-') ? 'bg-wc-accent/10' : 'bg-' . $slide['color'] . '/10' }}">
                            @switch($slide['icon'])
                                @case('sparkles')
                                    <svg class="h-8 w-8 {{ str_contains($slide['color'], 'wc-') ? 'text-wc-accent' : 'text-' . $slide['color'] }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 0 0-2.456 2.456Z" /></svg>
                                    @break
                                @case('dumbbell')
                                    <svg class="h-8 w-8 text-{{ $slide['color'] }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 0a1.5 1.5 0 0 1-1.5-1.5v-3a1.5 1.5 0 0 1 1.5-1.5h1.5a1.5 1.5 0 0 1 1.5 1.5v3m-4.5 0a1.5 1.5 0 0 0-1.5 1.5v3a1.5 1.5 0 0 0 1.5 1.5h1.5a1.5 1.5 0 0 0 1.5-1.5v-3m12-4.5v3a1.5 1.5 0 0 1-1.5 1.5h-1.5m3 0a1.5 1.5 0 0 1-1.5 1.5v3a1.5 1.5 0 0 1-1.5 1.5h-1.5a1.5 1.5 0 0 1-1.5-1.5v-3m0-4.5a1.5 1.5 0 0 0-1.5-1.5h-1.5a1.5 1.5 0 0 0-1.5 1.5v3" /></svg>
                                    @break
                                @case('nutrition')
                                    <svg class="h-8 w-8 text-{{ $slide['color'] }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" /></svg>
                                    @break
                                @case('habits')
                                    <svg class="h-8 w-8 text-{{ $slide['color'] }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                                    @break
                                @case('elite')
                                    <svg class="h-8 w-8 text-{{ $slide['color'] }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z" /></svg>
                                    @break
                                @case('fire')
                                    <svg class="h-8 w-8 text-{{ $slide['color'] }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.362 5.214A8.252 8.252 0 0 1 12 21 8.25 8.25 0 0 1 6.038 7.047 8.287 8.287 0 0 0 9 9.601a8.983 8.983 0 0 1 3.361-6.867 8.21 8.21 0 0 0 3 2.48Z" /></svg>
                                    @break
                                @case('chart')
                                    <svg class="h-8 w-8 text-{{ $slide['color'] }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" /></svg>
                                    @break
                                @default
                                    <svg class="h-8 w-8 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.59 14.37a6 6 0 0 1-5.84 7.38v-4.8m5.84-2.58a14.98 14.98 0 0 0 6.16-12.12A14.98 14.98 0 0 0 9.631 8.41m5.96 5.96a14.926 14.926 0 0 1-5.841 2.58m-.119-8.54a6 6 0 0 0-7.381 5.84h4.8m2.581-5.84a14.927 14.927 0 0 0-2.58 5.84m2.699 2.7c-.103.021-.207.041-.311.06a15.09 15.09 0 0 1-2.448-2.448 14.9 14.9 0 0 1 .06-.312m-2.24 2.39a4.493 4.493 0 0 0-1.757 4.306 4.493 4.493 0 0 0 4.306-1.758M16.5 9a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Z" /></svg>
                            @endswitch
                        </div>

                        {{-- Title --}}
                        <h2 class="font-display text-2xl tracking-wide text-wc-text">{{ strtoupper($slide['title']) }}</h2>

                        {{-- Description --}}
                        <p class="mt-3 text-sm text-wc-text-tertiary leading-relaxed max-w-sm">{{ $slide['description'] }}</p>

                        {{-- Features list --}}
                        @if(isset($slide['features']))
                            <div class="mt-5 w-full max-w-xs space-y-2">
                                @foreach($slide['features'] as $feature)
                                    <div class="flex items-center gap-2 text-left">
                                        <svg class="h-4 w-4 shrink-0 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                                        <span class="text-xs text-wc-text-secondary">{{ $feature }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        {{-- CTA on last slide --}}
                        @if(isset($slide['cta']))
                            <button @click="finish()"
                                class="mt-6 w-full max-w-xs rounded-xl bg-wc-accent px-6 py-3 font-display text-lg tracking-wider text-white shadow-lg shadow-wc-accent/20 transition-all hover:bg-wc-accent-hover btn-press">
                                COMENZAR
                            </button>
                        @endif
                    </div>
                @endforeach
            </div>

            {{-- Navigation --}}
            <div class="flex items-center justify-between border-t border-wc-border px-6 py-4">
                <button @click="prev()" x-show="current > 0"
                    class="text-sm text-wc-text-secondary hover:text-wc-text transition-colors">
                    Anterior
                </button>
                <div x-show="current === 0"></div>

                {{-- Dots --}}
                <div class="flex items-center gap-1.5">
                    @foreach($slides as $index => $slide)
                        <div class="h-1.5 rounded-full transition-all duration-300"
                            :class="current === {{ $index }} ? 'w-4 bg-wc-accent' : 'w-1.5 bg-wc-bg-tertiary'"
                        ></div>
                    @endforeach
                </div>

                <template x-if="current < total - 1">
                    <button @click="next()"
                        class="flex items-center gap-1 text-sm font-medium text-wc-accent hover:text-wc-accent-hover transition-colors">
                        Siguiente
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
                    </button>
                </template>
            </div>
        </div>
    </div>
    @endif
</div>
