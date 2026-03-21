{{-- Cookie Consent Banner — Discrete bottom-right card --}}
<div x-data="{ show: false }"
     x-init="setTimeout(() => { show = !localStorage.getItem('cookieConsent') }, 2000)"
     x-show="show"
     x-transition:enter="transition ease-out duration-500"
     x-transition:enter-start="translate-y-8 opacity-0"
     x-transition:enter-end="translate-y-0 opacity-100"
     x-transition:leave="transition ease-in duration-300"
     x-transition:leave-start="translate-y-0 opacity-100"
     x-transition:leave-end="translate-y-8 opacity-0"
     x-cloak
     class="fixed bottom-20 right-6 z-40 w-80 overflow-hidden rounded-2xl border border-wc-border bg-wc-bg-secondary/95 shadow-2xl shadow-black/10 backdrop-blur-xl sm:bottom-6 sm:right-6">

    <div class="p-5">
        {{-- Cookie icon + title --}}
        <div class="flex items-center gap-2.5">
            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-wc-accent/10">
                <svg class="h-4 w-4 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 0 0 8.716-6.747M12 21a9.004 9.004 0 0 1-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 0 1 7.843 4.582M12 3a8.997 8.997 0 0 0-7.843 4.582m15.686 0A11.953 11.953 0 0 1 12 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0 1 21 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0 1 12 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 0 1 3 12c0-1.605.42-3.113 1.157-4.418" />
                </svg>
            </div>
            <p class="text-sm font-semibold text-wc-text">{{ __('cookie.title') }}</p>
        </div>

        <p class="mt-3 text-xs leading-relaxed text-wc-text-secondary">
            {{ __('cookie.body') }}
            <a href="{{ route('cookies') }}" class="text-wc-accent hover:underline">{{ __('cookie.read_more') }}</a>
        </p>

        <div class="mt-4 flex gap-2">
            <button
                x-on:click="localStorage.setItem('cookieConsent', 'accepted'); show = false"
                class="btn-press flex-1 rounded-lg bg-wc-accent px-4 py-2 text-xs font-semibold text-white hover:bg-wc-accent-hover">
                {{ __('cookie.accept') }}
            </button>
            <button
                x-on:click="localStorage.setItem('cookieConsent', 'declined'); show = false"
                class="btn-press flex-1 rounded-lg border border-wc-border bg-wc-bg-tertiary px-4 py-2 text-xs font-medium text-wc-text-secondary hover:text-wc-text">
                {{ __('cookie.decline') }}
            </button>
        </div>
    </div>
</div>
