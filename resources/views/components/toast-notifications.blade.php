{{-- Toast Notification System --}}
<div x-data="toastSystem()" x-on:toast.window="add($event.detail)" class="fixed right-4 top-20 z-[60] flex flex-col gap-3" style="pointer-events: none;">
    <template x-for="toast in toasts" :key="toast.id">
        <div x-show="toast.visible"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="translate-x-full opacity-0"
             x-transition:enter-end="translate-x-0 opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="translate-x-0 opacity-100"
             x-transition:leave-end="translate-x-full opacity-0"
             class="relative min-w-[300px] max-w-sm overflow-hidden rounded-xl border shadow-lg"
             style="pointer-events: auto;"
             :class="{
                 'border-emerald-500/30 bg-emerald-950/90 text-emerald-200': toast.type === 'success',
                 'border-red-500/30 bg-red-950/90 text-red-200': toast.type === 'error',
                 'border-amber-500/30 bg-amber-950/90 text-amber-200': toast.type === 'warning',
                 'border-sky-500/30 bg-sky-950/90 text-sky-200': toast.type === 'info'
             }">
            <div class="flex items-start gap-3 px-4 py-3">
                {{-- Icon --}}
                <div class="mt-0.5 shrink-0">
                    <template x-if="toast.type === 'success'">
                        <svg class="h-5 w-5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </template>
                    <template x-if="toast.type === 'error'">
                        <svg class="h-5 w-5 text-red-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" /></svg>
                    </template>
                    <template x-if="toast.type === 'warning'">
                        <svg class="h-5 w-5 text-amber-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" /></svg>
                    </template>
                    <template x-if="toast.type === 'info'">
                        <svg class="h-5 w-5 text-sky-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" /></svg>
                    </template>
                </div>
                {{-- Message --}}
                <p class="text-sm font-medium" x-text="toast.message"></p>
                {{-- Close --}}
                <button x-on:click="remove(toast.id)" class="ml-auto shrink-0 opacity-60 hover:opacity-100">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
            {{-- Progress bar --}}
            <div class="h-0.5 w-full"
                 :class="{
                     'bg-emerald-500/20': toast.type === 'success',
                     'bg-red-500/20': toast.type === 'error',
                     'bg-amber-500/20': toast.type === 'warning',
                     'bg-sky-500/20': toast.type === 'info'
                 }">
                <div class="h-full transition-all duration-100 ease-linear"
                     :class="{
                         'bg-emerald-400': toast.type === 'success',
                         'bg-red-400': toast.type === 'error',
                         'bg-amber-400': toast.type === 'warning',
                         'bg-sky-400': toast.type === 'info'
                     }"
                     :style="'width: ' + toast.progress + '%'"></div>
            </div>
        </div>
    </template>
</div>

{{-- Achievement Toast with Confetti --}}
<div x-data="achievementToast()"
     x-on:achievement-unlocked.window="show($event.detail)"
     x-show="visible"
     x-transition:enter="transition ease-out duration-500"
     x-transition:enter-start="translate-y-8 opacity-0 scale-95"
     x-transition:enter-end="translate-y-0 opacity-100 scale-100"
     x-transition:leave="transition ease-in duration-300"
     x-transition:leave-start="translate-y-0 opacity-100 scale-100"
     x-transition:leave-end="translate-y-8 opacity-0 scale-95"
     class="fixed left-1/2 top-20 z-[70] -translate-x-1/2"
     style="display: none;">
    {{-- Toast card --}}
    <div class="relative min-w-[320px] max-w-sm overflow-hidden rounded-xl border border-amber-500/40 bg-gradient-to-br from-amber-950/95 to-amber-900/90 shadow-2xl shadow-amber-500/10">
        <div class="flex items-center gap-4 px-5 py-4">
            {{-- Trophy icon --}}
            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-amber-500/20">
                <svg class="h-6 w-6 text-amber-400" viewBox="0 0 24 24" fill="currentColor">
                    <path fill-rule="evenodd" d="M5.166 2.621v.858c-1.035.148-2.059.33-3.071.543a.75.75 0 0 0-.584.859 6.753 6.753 0 0 0 6.138 5.6 6.73 6.73 0 0 0 2.743 1.346A6.707 6.707 0 0 1 9.279 15H8.54c-1.036 0-1.875.84-1.875 1.875V19.5h-.75a.75.75 0 0 0 0 1.5h12.17a.75.75 0 0 0 0-1.5h-.75v-2.625c0-1.036-.84-1.875-1.875-1.875h-.739a6.707 6.707 0 0 1-1.112-3.173 6.73 6.73 0 0 0 2.743-1.347 6.753 6.753 0 0 0 6.139-5.6.75.75 0 0 0-.585-.858 47.077 47.077 0 0 0-3.07-.543V2.62a.75.75 0 0 0-.658-.744 49.22 49.22 0 0 0-6.093-.377c-2.063 0-4.096.128-6.093.377a.75.75 0 0 0-.657.744Zm0 2.629c0 1.196.312 2.32.857 3.294A5.266 5.266 0 0 1 3.16 5.337a45.6 45.6 0 0 1 2.006-.343v.256Zm13.5 0v-.256c.674.1 1.343.214 2.006.343a5.265 5.265 0 0 1-2.863 3.207 6.72 6.72 0 0 0 .857-3.294Z" clip-rule="evenodd" />
                </svg>
            </div>
            {{-- Text --}}
            <div class="min-w-0 flex-1">
                <p class="text-xs font-semibold uppercase tracking-wider text-amber-400">Logro desbloqueado!</p>
                <p class="mt-0.5 text-sm font-bold text-amber-100" x-text="name"></p>
                <p class="mt-0.5 text-xs text-amber-300/70" x-text="description"></p>
            </div>
            {{-- Close --}}
            <button x-on:click="dismiss()" class="shrink-0 text-amber-400/60 hover:text-amber-300">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>
        {{-- Shimmer bar --}}
        <div class="h-0.5 w-full bg-amber-500/20">
            <div class="h-full bg-amber-400 transition-all duration-100 ease-linear" :style="'width:' + progress + '%'"></div>
        </div>
    </div>
    {{-- Confetti particles --}}
    <template x-if="showConfetti">
        <div>
            <template x-for="p in particles" :key="p.id">
                <div class="confetti-particle"
                     :style="'left:' + p.left + '%;background:' + p.color + ';animation:confettiFall ' + p.duration + 's ease-in ' + p.delay + 's forwards;width:' + p.size + 'px;height:' + p.size + 'px;border-radius:' + p.radius + 'px;'">
                </div>
            </template>
        </div>
    </template>
</div>

<script>
    function toastSystem() {
        return {
            toasts: [],
            add(detail) {
                const id = Date.now() + Math.random();
                const toast = {
                    id,
                    type: detail.type || 'info',
                    message: detail.message || '',
                    visible: true,
                    progress: 100
                };
                this.toasts.push(toast);

                const duration = detail.duration || 4000;
                const interval = 50;
                const step = (100 / duration) * interval;

                const timer = setInterval(() => {
                    const t = this.toasts.find(t => t.id === id);
                    if (!t) { clearInterval(timer); return; }
                    t.progress = Math.max(0, t.progress - step);
                    if (t.progress <= 0) {
                        clearInterval(timer);
                        this.remove(id);
                    }
                }, interval);
            },
            remove(id) {
                const toast = this.toasts.find(t => t.id === id);
                if (toast) toast.visible = false;
                setTimeout(() => {
                    this.toasts = this.toasts.filter(t => t.id !== id);
                }, 300);
            }
        };
    }

    function achievementToast() {
        const confettiColors = ['#DC2626', '#FBBF24', '#22C55E', '#3B82F6', '#A855F7'];
        return {
            visible: false,
            showConfetti: false,
            name: '',
            description: '',
            progress: 100,
            particles: [],
            _timer: null,
            show(detail) {
                this.name = detail.name || 'Logro';
                this.description = detail.description || '';
                this.progress = 100;
                this.visible = true;

                // Generate 20 confetti particles
                this.particles = Array.from({ length: 20 }, (_, i) => ({
                    id: i,
                    left: Math.random() * 100,
                    color: confettiColors[Math.floor(Math.random() * confettiColors.length)],
                    duration: 2 + Math.random() * 2,
                    delay: Math.random() * 0.8,
                    size: 6 + Math.random() * 6,
                    radius: Math.random() > 0.5 ? 50 : 2
                }));
                this.showConfetti = true;

                // Auto-dismiss after 5s with progress bar
                const duration = 5000;
                const interval = 50;
                const step = (100 / duration) * interval;
                if (this._timer) clearInterval(this._timer);
                this._timer = setInterval(() => {
                    this.progress = Math.max(0, this.progress - step);
                    if (this.progress <= 0) {
                        clearInterval(this._timer);
                        this.dismiss();
                    }
                }, interval);

                // Remove confetti particles after animation completes
                setTimeout(() => { this.showConfetti = false; }, 5000);
            },
            dismiss() {
                if (this._timer) clearInterval(this._timer);
                this.visible = false;
                this.showConfetti = false;
            }
        };
    }
</script>
