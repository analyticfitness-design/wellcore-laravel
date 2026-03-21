{{-- PWA Custom Install Prompt --}}
<div x-data="{
        show: false,
        deferredPrompt: null,
        dismissed: localStorage.getItem('wc_pwa_dismissed'),
        init() {
            if (this.dismissed) return;
            window.addEventListener('beforeinstallprompt', (e) => {
                e.preventDefault();
                this.deferredPrompt = e;
                setTimeout(() => { this.show = true; }, 5000);
            });
        },
        async install() {
            if (!this.deferredPrompt) return;
            this.deferredPrompt.prompt();
            const { outcome } = await this.deferredPrompt.userChoice;
            this.deferredPrompt = null;
            this.show = false;
        },
        dismiss() {
            this.show = false;
            localStorage.setItem('wc_pwa_dismissed', '1');
        }
     }"
     x-show="show"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="translate-y-full opacity-0"
     x-transition:enter-end="translate-y-0 opacity-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="translate-y-0 opacity-100"
     x-transition:leave-end="translate-y-full opacity-0"
     x-cloak
     class="fixed inset-x-4 bottom-20 z-50 mx-auto max-w-md overflow-hidden rounded-2xl border border-wc-border bg-wc-bg-secondary shadow-2xl sm:bottom-6 sm:right-6 sm:left-auto sm:inset-x-auto">

    <div class="p-5">
        <div class="flex items-start gap-4">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-wc-accent/10">
                <img src="/images/logo-icon-dark.png" alt="WellCore" class="h-8 w-8 dark:hidden">
                <img src="/images/logo-icon-light.png" alt="WellCore" class="hidden h-8 w-8 dark:block">
            </div>
            <div class="flex-1">
                <p class="text-sm font-semibold text-wc-text">Instala WellCore</p>
                <p class="mt-1 text-xs text-wc-text-secondary">Accede mas rapido desde tu pantalla de inicio. Sin descargar nada.</p>
            </div>
            <button @click="dismiss()" class="shrink-0 text-wc-text-tertiary hover:text-wc-text">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
            </button>
        </div>
        <div class="mt-4 flex gap-2">
            <button @click="install()" class="btn-press flex-1 rounded-lg bg-wc-accent px-4 py-2.5 text-sm font-semibold text-white hover:bg-wc-accent-hover">
                Instalar App
            </button>
            <button @click="dismiss()" class="btn-press rounded-lg border border-wc-border px-4 py-2.5 text-sm font-medium text-wc-text-secondary hover:text-wc-text">
                Ahora no
            </button>
        </div>
    </div>
</div>
