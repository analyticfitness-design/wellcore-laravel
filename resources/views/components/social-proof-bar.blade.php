@props(['variant' => 'default'])

<section class="border-y border-wc-border bg-wc-bg" data-animate="fadeIn">
    <div class="mx-auto max-w-7xl px-4 py-5 sm:px-6 lg:px-8">
        <div class="flex flex-wrap items-center justify-center gap-6 sm:gap-10 text-center">
            {{-- Live indicator --}}
            <div class="flex items-center gap-2">
                <span class="relative flex h-2 w-2">
                    <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex h-2 w-2 rounded-full bg-emerald-400"></span>
                </span>
                <span class="text-sm text-wc-text-secondary">
                    <span class="font-data font-bold text-wc-text counter-highlight" data-counter="47" data-counter-suffix="+">0+</span> personas activas esta semana
                </span>
            </div>

            <div class="hidden h-4 w-px bg-wc-border sm:block" aria-hidden="true"></div>

            {{-- Satisfaction --}}
            <div class="flex items-center gap-2">
                <div class="flex -space-x-1">
                    <div class="h-6 w-6 rounded-full border-2 border-wc-bg bg-wc-accent/20 flex items-center justify-center">
                        <span class="text-[8px] font-bold text-wc-accent">D</span>
                    </div>
                    <div class="h-6 w-6 rounded-full border-2 border-wc-bg bg-emerald-400/20 flex items-center justify-center">
                        <span class="text-[8px] font-bold text-emerald-500">L</span>
                    </div>
                    <div class="h-6 w-6 rounded-full border-2 border-wc-bg bg-amber-400/20 flex items-center justify-center">
                        <span class="text-[8px] font-bold text-amber-500">C</span>
                    </div>
                </div>
                <span class="text-sm text-wc-text-secondary">
                    <span class="font-data font-bold text-wc-text counter-highlight" data-counter="94" data-counter-suffix="%">0%</span> satisfaccion
                </span>
            </div>

            <div class="hidden h-4 w-px bg-wc-border sm:block" aria-hidden="true"></div>

            {{-- Verified --}}
            <div class="flex items-center gap-1.5">
                <svg class="h-4 w-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                <span class="text-sm text-wc-text-secondary">Verificado por <span class="font-semibold text-wc-text">WellCore</span></span>
            </div>
        </div>
    </div>
</section>
