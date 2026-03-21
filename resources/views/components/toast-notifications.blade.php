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
</script>
