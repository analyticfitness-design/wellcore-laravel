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
            this._savedScrollY = window.scrollY;
            document.body.style.cssText = `position:fixed;top:-${this._savedScrollY}px;width:100%;overflow:hidden;`;
            this.$nextTick(() => this.start());
        },

        closeTimer() {
            this.pause();
            this.show = false;
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
    x-on:close-rest-timer.window="closeTimer()"
>
    <div
        x-show="show"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-[100] flex items-center justify-center"
        @keydown.escape.window="closeTimer()"
        style="display:none"
    >
        {{-- Full-screen dark overlay --}}
        <div class="absolute inset-0 bg-[#0a0a0b]/95 backdrop-blur-sm" @click="closeTimer()"></div>

        {{-- Timer Card --}}
        <div class="relative mx-4 w-full max-w-sm overflow-hidden rounded-3xl border border-white/[0.06] shadow-2xl"
             style="background: linear-gradient(170deg, #111113 0%, #0d0d0f 50%, #111113 100%);">

            {{-- Decorative glow --}}
            <div class="pointer-events-none absolute -top-20 left-1/2 h-40 w-40 -translate-x-1/2 rounded-full"
                 :style="remaining <= 0 ? 'background: radial-gradient(circle, rgba(34,197,94,0.15) 0%, transparent 70%)' : 'background: radial-gradient(circle, rgba(220,38,38,0.12) 0%, transparent 70%)'"></div>

            {{-- Close button --}}
            <button @click="closeTimer()" class="absolute right-4 top-4 z-10 flex h-8 w-8 items-center justify-center rounded-full bg-white/5 text-white/40 hover:bg-white/10 hover:text-white/70 transition-all" aria-label="Cerrar">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>

            <div class="relative z-10 px-6 pb-6 pt-8">
                {{-- Title --}}
                <h3 class="text-center font-display text-sm tracking-[0.2em] text-white/50 mb-6">DESCANSO</h3>

                {{-- Circular Timer --}}
                <div class="relative mx-auto h-56 w-56">
                    {{-- Outer ring glow --}}
                    <div class="absolute inset-0 rounded-full"
                         :class="remaining <= 0 ? 'shadow-[0_0_40px_rgba(34,197,94,0.15)]' : (remaining <= 10 ? 'shadow-[0_0_40px_rgba(220,38,38,0.2)]' : '')"></div>

                    <svg class="h-full w-full -rotate-90" viewBox="0 0 200 200">
                        {{-- Background track --}}
                        <circle cx="100" cy="100" r="90" fill="none" stroke="rgba(255,255,255,0.04)" stroke-width="4" />
                        {{-- Progress arc --}}
                        <circle cx="100" cy="100" r="90" fill="none"
                            stroke-width="4"
                            :stroke="remaining <= 0 ? '#22c55e' : (remaining <= 10 ? '#ef4444' : '#DC2626')"
                            :stroke-dasharray="circumference"
                            :stroke-dashoffset="strokeDashoffset"
                            stroke-linecap="round"
                            class="transition-all duration-1000" />
                        {{-- Dot at progress tip --}}
                        <circle r="5"
                            :cx="100 + 90 * Math.cos((progress / 100) * 2 * Math.PI - Math.PI/2)"
                            :cy="100 + 90 * Math.sin((progress / 100) * 2 * Math.PI - Math.PI/2)"
                            :fill="remaining <= 0 ? '#22c55e' : '#DC2626'"
                            class="transition-all duration-1000"
                            x-show="progress > 0 && remaining > 0" />
                    </svg>

                    {{-- Center display --}}
                    <div class="absolute inset-0 flex flex-col items-center justify-center">
                        <span class="font-data text-6xl font-bold tabular-nums tracking-tight"
                              :class="remaining <= 0 ? 'text-emerald-400' : (remaining <= 10 ? 'text-red-400' : 'text-white')"
                              x-text="minutes + ':' + seconds"></span>
                        <span class="mt-1 text-xs font-semibold uppercase tracking-widest"
                              :class="remaining <= 0 ? 'text-emerald-400/70' : 'text-white/20'"
                              x-text="remaining <= 0 ? 'LISTO' : ''"></span>
                    </div>
                </div>

                {{-- Controls --}}
                <div class="mt-6 flex items-center justify-center gap-4">
                    {{-- Play/Pause --}}
                    <button @click="running ? pause() : start()"
                        class="flex h-14 w-14 items-center justify-center rounded-full transition-all"
                        :class="running
                            ? 'bg-white/10 text-white hover:bg-white/15'
                            : 'bg-wc-accent text-white shadow-lg shadow-wc-accent/30 hover:bg-red-600'"
                        :aria-label="running ? 'Pausar' : 'Iniciar'">
                        <template x-if="running">
                            <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24"><path d="M6 4h4v16H6V4zm8 0h4v16h-4V4z"/></svg>
                        </template>
                        <template x-if="!running">
                            <svg class="h-6 w-6 ml-0.5" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                        </template>
                    </button>

                    {{-- Reset --}}
                    <button @click="reset()"
                        class="flex h-11 w-11 items-center justify-center rounded-full border border-white/10 text-white/40 hover:border-white/20 hover:text-white/70 transition-all"
                        aria-label="Reiniciar">
                        <svg class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    </button>
                </div>

                {{-- Duration Presets --}}
                <div class="mt-6 flex items-center justify-center gap-1.5">
                    <template x-for="preset in [30, 60, 90, 120, 180]" :key="preset">
                        <button @click="setDuration(preset)"
                            class="rounded-full px-3.5 py-2 text-xs font-bold tracking-wide transition-all"
                            :class="duration === preset
                                ? 'bg-wc-accent text-white shadow-md shadow-wc-accent/20'
                                : 'bg-white/[0.04] text-white/40 hover:bg-white/[0.08] hover:text-white/70 border border-white/[0.06]'"
                            x-text="preset >= 120 ? (preset/60) + 'min' : preset + 's'">
                        </button>
                    </template>
                </div>
            </div>
        </div>
    </div>
</div>
