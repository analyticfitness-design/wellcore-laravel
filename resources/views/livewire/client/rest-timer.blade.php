<div
    x-data="{
        show: false,
        duration: 90,
        remaining: 90,
        running: false,
        interval: null,

        _savedScrollY: 0,

        openTimer(seconds) {
            this.setDuration(seconds);
            this.show = true;
            // iOS-safe scroll lock: position:fixed preserves scroll position
            this._savedScrollY = window.scrollY;
            document.body.style.cssText = `position:fixed;top:-${this._savedScrollY}px;width:100%;overflow:hidden;`;
            this.$nextTick(() => this.start());
        },

        closeTimer() {
            this.pause();
            this.show = false;
            // Restore body + scroll position atomically
            document.body.style.cssText = '';
            window.scrollTo(0, this._savedScrollY);
            this._savedScrollY = 0;
        },

        start() {
            if (this.remaining <= 0) this.remaining = this.duration;
            this.running = true;
            this.interval = setInterval(() => {
                this.remaining--;
                if (this.remaining <= 0) this.finish();
            }, 1000);
        },

        pause() {
            this.running = false;
            clearInterval(this.interval);
        },

        reset() {
            this.pause();
            this.remaining = this.duration;
        },

        setDuration(s) {
            this.pause();
            this.duration = s;
            this.remaining = s;
        },

        finish() {
            this.pause();
            this.remaining = 0;
            this.playSound();
        },

        playSound() {
            try {
                const ctx = new (window.AudioContext || window.webkitAudioContext)();
                const notes = [523.25, 659.25, 783.99];
                notes.forEach((freq, i) => {
                    const osc = ctx.createOscillator();
                    const gain = ctx.createGain();
                    osc.connect(gain);
                    gain.connect(ctx.destination);
                    osc.frequency.value = freq;
                    osc.type = 'sine';
                    gain.gain.setValueAtTime(0.15, ctx.currentTime + i * 0.15);
                    gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + i * 0.15 + 0.4);
                    osc.start(ctx.currentTime + i * 0.15);
                    osc.stop(ctx.currentTime + i * 0.15 + 0.4);
                });
            } catch(e) {}
        },

        get progress() {
            return this.duration > 0 ? ((this.duration - this.remaining) / this.duration) * 100 : 0;
        },

        get minutes() {
            return Math.floor(this.remaining / 60).toString().padStart(2, '0');
        },

        get seconds() {
            return (this.remaining % 60).toString().padStart(2, '0');
        },

        get circumference() {
            return 2 * Math.PI * 90;
        },

        get strokeDashoffset() {
            return this.circumference - (this.progress / 100) * this.circumference;
        }
    }"
    x-on:open-rest-timer.window="openTimer($event.detail.seconds)"
>
    {{-- Overlay: x-show keeps DOM intact → no morphdom → no iOS scroll jump --}}
    <div
        x-show="show"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-[100] flex items-center justify-center bg-black/80"
        @keydown.escape.window="closeTimer()"
        style="display:none"
    >
        {{-- Timer Card --}}
        <div class="relative mx-4 w-full max-w-sm rounded-2xl border border-wc-border bg-wc-bg p-6 shadow-2xl"
             @click.outside="closeTimer()">

            {{-- Close button --}}
            <button @click="closeTimer()" class="absolute right-4 top-4 text-wc-text-tertiary hover:text-wc-text transition-colors" aria-label="Cerrar timer">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>

            {{-- Title --}}
            <h3 class="text-center text-sm font-semibold uppercase tracking-wider text-wc-text-secondary mb-4">Descanso entre series</h3>

            {{-- Circular Timer --}}
            <div class="relative mx-auto h-52 w-52">
                <svg class="h-full w-full -rotate-90" viewBox="0 0 200 200">
                    <circle cx="100" cy="100" r="90" fill="none" stroke="currentColor" stroke-width="6" class="text-wc-bg-secondary" />
                    <circle cx="100" cy="100" r="90" fill="none" stroke="currentColor" stroke-width="6"
                        class="text-wc-accent transition-all duration-1000"
                        :stroke-dasharray="circumference"
                        :stroke-dashoffset="strokeDashoffset"
                        stroke-linecap="round" />
                </svg>
                <div class="absolute inset-0 flex flex-col items-center justify-center">
                    <span class="font-data text-5xl font-bold text-wc-text tabular-nums" x-text="minutes + ':' + seconds"></span>
                    <span class="mt-1 text-xs text-wc-text-tertiary" x-show="remaining <= 0">Listo!</span>
                </div>
            </div>

            {{-- Controls --}}
            <div class="mt-5 flex items-center justify-center gap-3">
                <button @click="running ? pause() : start()"
                    class="btn-press flex h-12 w-12 items-center justify-center rounded-full bg-wc-accent text-white shadow-lg shadow-wc-accent/20 transition-transform hover:bg-wc-accent-hover"
                    :aria-label="running ? 'Pausar' : 'Iniciar'">
                    <template x-if="running">
                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M6 4h4v16H6V4zm8 0h4v16h-4V4z"/></svg>
                    </template>
                    <template x-if="!running">
                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                    </template>
                </button>
                <button @click="reset()"
                    class="btn-press flex h-10 w-10 items-center justify-center rounded-full border border-wc-border bg-wc-bg-secondary text-wc-text-secondary transition-colors hover:bg-wc-bg-tertiary"
                    aria-label="Reiniciar">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                </button>
            </div>

            {{-- Duration Presets --}}
            <div class="mt-4 flex items-center justify-center gap-2">
                <template x-for="preset in [60, 90, 120, 180]" :key="preset">
                    <button @click="setDuration(preset)"
                        class="btn-press rounded-lg px-3 py-1.5 text-sm font-medium transition-colors"
                        :class="duration === preset ? 'bg-wc-accent text-white shadow-sm' : 'bg-wc-bg-secondary text-wc-text-secondary hover:bg-wc-bg-tertiary'"
                        x-text="preset >= 120 ? (preset/60) + 'min' : preset + 's'">
                    </button>
                </template>
            </div>
        </div>
    </div>
</div>
