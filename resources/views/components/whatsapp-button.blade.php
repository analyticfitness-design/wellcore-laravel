{{-- WellCore Chat Widget (matches production chatbot) --}}
<div x-data="{ chatOpen: false }" class="fixed bottom-6 right-6 z-50">

    {{-- Chat Dialog --}}
    <div x-show="chatOpen"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-4 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 translate-y-4 scale-95"
         style="display: none;"
         class="absolute bottom-16 right-0 mb-2 w-80 overflow-hidden rounded-2xl border border-wc-border bg-wc-bg shadow-2xl sm:w-96">

        {{-- Header --}}
        <div class="flex items-center justify-between border-b border-wc-border bg-wc-bg-secondary px-4 py-3">
            <div class="flex items-center gap-3">
                <div class="flex h-9 w-9 items-center justify-center rounded-full bg-wc-accent">
                    <span class="font-display text-sm leading-none text-white">W</span>
                </div>
                <div>
                    <p class="text-sm font-semibold text-wc-text">WellCore</p>
                    <p class="text-[11px] text-wc-text-tertiary">Asistente de Fitness</p>
                </div>
            </div>
            <button x-on:click="chatOpen = false" class="flex h-8 w-8 items-center justify-center rounded-lg text-wc-text-tertiary hover:bg-wc-bg-tertiary hover:text-wc-text">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        {{-- Messages area --}}
        <div class="h-64 overflow-y-auto px-4 py-4">
            <div class="flex gap-2">
                <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-wc-accent/10">
                    <span class="text-[10px] font-bold text-wc-accent">W</span>
                </div>
                <div class="rounded-xl rounded-tl-sm bg-wc-bg-tertiary px-3 py-2 text-sm text-wc-text-secondary">
                    Hola! Soy el asistente de WellCore. Puedo ayudarte con informacion sobre nuestros planes, el metodo, precios o cualquier duda que tengas. Como puedo ayudarte?
                </div>
            </div>
        </div>

        {{-- Input --}}
        <div class="border-t border-wc-border px-3 py-3">
            <form class="flex gap-2" onsubmit="event.preventDefault();">
                <input type="text" placeholder="Escribe tu pregunta..." class="flex-1 rounded-lg border border-wc-border bg-wc-bg-secondary px-3 py-2 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent">
                <button type="submit" class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-wc-accent text-white hover:bg-wc-accent-hover">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" />
                    </svg>
                </button>
            </form>
        </div>

        {{-- Footer --}}
        <div class="border-t border-wc-border bg-wc-bg-secondary px-4 py-2 text-center text-[10px] text-wc-text-tertiary">
            Powered by WellCore · <a href="mailto:info@wellcorefitness.com" class="text-wc-accent hover:underline">info@wellcorefitness.com</a>
        </div>
    </div>

    {{-- Floating Button --}}
    <button
        x-on:click="chatOpen = !chatOpen"
        class="relative flex h-14 w-14 items-center justify-center rounded-full bg-wc-accent shadow-lg shadow-wc-accent/30 transition-transform duration-200 hover:scale-110 active:scale-95"
        aria-label="Abrir chat WellCore"
    >
        {{-- Chat icon (when closed) --}}
        <svg x-show="!chatOpen" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 9.75a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a5.969 5.969 0 0 1-.474-.065 4.48 4.48 0 0 0 .978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z" />
        </svg>
        {{-- Close icon (when open) --}}
        <svg x-show="chatOpen" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
        </svg>
        {{-- Notification badge --}}
        <span x-show="!chatOpen" class="absolute -right-0.5 -top-0.5 flex h-4 w-4 items-center justify-center rounded-full bg-emerald-500 text-[9px] font-bold text-white">1</span>
    </button>
</div>
