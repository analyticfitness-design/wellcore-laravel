{{-- Cookie Consent Banner — Compact pill bottom-left --}}
<div x-data="{ show: false }"
     x-init="setTimeout(() => { show = !localStorage.getItem('cookieConsent') }, 2000)"
     x-show="show"
     x-transition:enter="transition ease-out duration-400"
     x-transition:enter-start="translate-y-4 opacity-0"
     x-transition:enter-end="translate-y-0 opacity-100"
     x-transition:leave="transition ease-in duration-250"
     x-transition:leave-start="translate-y-0 opacity-100"
     x-transition:leave-end="translate-y-4 opacity-0"
     x-cloak
     class="fixed bottom-20 left-4 z-40 sm:bottom-5 sm:left-5">

    <div class="flex items-center gap-3 rounded-full border border-wc-border bg-wc-bg-secondary/95 px-4 py-2.5 shadow-lg shadow-black/20 backdrop-blur-xl">
        {{-- Icon --}}
        <div class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-wc-accent/15">
            <svg class="h-3 w-3 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 0 0 8.716-6.747M12 21a9.004 9.004 0 0 1-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 0 1 7.843 4.582M12 3a8.997 8.997 0 0 0-7.843 4.582m15.686 0A11.953 11.953 0 0 1 12 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0 1 21 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0 1 12 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 0 1 3 12c0-1.605.42-3.113 1.157-4.418" />
            </svg>
        </div>

        {{-- Text --}}
        <p class="text-xs text-wc-text-secondary">
            {{ __('cookie.body') }}
            <a href="{{ route('cookies') }}" class="text-red-700 dark:text-red-400 underline">{{ __('cookie.read_more') }}</a>
        </p>

        {{-- Actions --}}
        <div class="flex shrink-0 items-center gap-1.5">
            <button
                x-on:click="localStorage.setItem('cookieConsent', 'accepted'); show = false"
                class="rounded-full bg-wc-accent px-3 py-1 text-[11px] font-semibold text-white transition hover:brightness-110">
                {{ __('cookie.accept') }}
            </button>
            <button
                x-on:click="localStorage.setItem('cookieConsent', 'declined'); show = false"
                class="rounded-full border border-wc-border px-3 py-1 text-[11px] font-medium text-wc-text-tertiary transition hover:text-wc-text-secondary">
                {{ __('cookie.decline') }}
            </button>
        </div>
    </div>
</div>
