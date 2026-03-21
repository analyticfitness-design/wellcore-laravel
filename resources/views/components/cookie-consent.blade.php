{{-- Cookie Consent Banner --}}
<div x-data="{ show: !localStorage.getItem('cookieConsent') }"
     x-show="show"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="translate-y-full opacity-0"
     x-transition:enter-end="translate-y-0 opacity-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="translate-y-0 opacity-100"
     x-transition:leave-end="translate-y-full opacity-0"
     x-cloak
     class="fixed inset-x-0 bottom-0 z-50 border-t border-wc-border bg-wc-bg-secondary/95 backdrop-blur-xl">
    <div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-4 px-4 py-4 sm:flex-row sm:px-6 lg:px-8">
        <p class="text-sm text-wc-text-secondary">
            Usamos cookies para mejorar tu experiencia. Al continuar navegando, aceptas nuestra
            <a href="{{ route('cookies') }}" class="text-wc-accent hover:underline">politica de cookies</a>.
        </p>
        <div class="flex shrink-0 gap-3">
            <button
                x-on:click="localStorage.setItem('cookieConsent', 'accepted'); show = false"
                class="rounded-lg bg-wc-accent px-5 py-2 text-sm font-medium text-white hover:bg-wc-accent-hover">
                Aceptar
            </button>
            <button
                x-on:click="localStorage.setItem('cookieConsent', 'declined'); show = false"
                class="rounded-lg border border-wc-border bg-wc-bg-tertiary px-5 py-2 text-sm font-medium text-wc-text-secondary hover:text-wc-text">
                Rechazar
            </button>
        </div>
    </div>
</div>
