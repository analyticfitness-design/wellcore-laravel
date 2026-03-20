<div class="space-y-6" x-data="workoutTimer()">
    <div>
        <h1 class="font-display text-3xl tracking-wide text-wc-text">TIMER</h1>
        <p class="mt-1 text-sm text-wc-text-secondary">4 modos de entrenamiento con temporizador visual.</p>
    </div>

    {{-- Mode Tabs --}}
    <div class="flex flex-wrap gap-2">
        <template x-for="m in modes" :key="m.id">
            <button x-on:click="selectMode(m.id)" :class="mode === m.id ? 'bg-wc-accent text-white' : 'bg-wc-bg-tertiary text-wc-text-secondary hover:text-wc-text'" class="rounded-lg border border-wc-border px-4 py-2 text-sm font-medium transition-colors" x-text="m.label"></button>
        </template>
    </div>

    <div class="mx-auto max-w-md">
        {{-- SVG Ring Timer --}}
        <div class="relative mx-auto flex h-64 w-64 items-center justify-center sm:h-80 sm:w-80">
            <svg class="absolute inset-0 -rotate-90" viewBox="0 0 200 200">
                <circle cx="100" cy="100" r="90" fill="none" stroke="currentColor" stroke-width="4" class="text-wc-border" />
                <circle cx="100" cy="100" r="90" fill="none" stroke="currentColor" stroke-width="6" stroke-linecap="round" class="text-wc-accent transition-all duration-1000" :stroke-dasharray="circumference" :stroke-dashoffset="dashOffset" />
            </svg>
            <div class="text-center">
                <p class="font-data text-5xl font-bold text-wc-text sm:text-6xl" x-text="display"></p>
                <p class="mt-2 text-sm font-medium" :class="isWork ? 'text-wc-accent' : 'text-green-500'" x-text="phase" x-show="running || paused"></p>
                <p class="mt-1 text-xs text-wc-text-tertiary" x-show="running || paused">
                    Ronda <span x-text="currentRound"></span>/<span x-text="totalRounds"></span>
                </p>
            </div>
        </div>

        {{-- Config (visible when stopped) --}}
        <div x-show="!running && !paused" class="mt-6 rounded-xl border border-wc-border bg-wc-bg-tertiary p-6">
            {{-- Timer mode --}}
            <template x-if="mode === 'timer'">
                <div class="space-y-4">
                    <h3 class="text-sm font-semibold text-wc-text">Temporizador Simple</h3>
                    <div>
                        <label class="block text-xs font-medium text-wc-text-tertiary">Minutos</label>
                        <input type="number" x-model.number="config.minutes" min="1" max="60" class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none" placeholder="5">
                    </div>
                </div>
            </template>

            {{-- Tabata mode --}}
            <template x-if="mode === 'tabata'">
                <div class="space-y-4">
                    <h3 class="text-sm font-semibold text-wc-text">Tabata (20s trabajo / 10s descanso)</h3>
                    <div>
                        <label class="block text-xs font-medium text-wc-text-tertiary">Rondas</label>
                        <input type="number" x-model.number="config.rounds" min="1" max="20" class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none" placeholder="8">
                    </div>
                    <p class="text-xs text-wc-text-tertiary">Total: <span x-text="config.rounds * 30"></span> segundos (<span x-text="(config.rounds * 30 / 60).toFixed(1)"></span> min)</p>
                </div>
            </template>

            {{-- AMRAP mode --}}
            <template x-if="mode === 'amrap'">
                <div class="space-y-4">
                    <h3 class="text-sm font-semibold text-wc-text">AMRAP (As Many Reps As Possible)</h3>
                    <div>
                        <label class="block text-xs font-medium text-wc-text-tertiary">Minutos</label>
                        <input type="number" x-model.number="config.minutes" min="1" max="30" class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none" placeholder="12">
                    </div>
                </div>
            </template>

            {{-- EMOM mode --}}
            <template x-if="mode === 'emom'">
                <div class="space-y-4">
                    <h3 class="text-sm font-semibold text-wc-text">EMOM (Every Minute On the Minute)</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-wc-text-tertiary">Minutos totales</label>
                            <input type="number" x-model.number="config.minutes" min="1" max="30" class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none" placeholder="10">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-wc-text-tertiary">Trabajo (seg)</label>
                            <input type="number" x-model.number="config.workSec" min="10" max="50" class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none" placeholder="40">
                        </div>
                    </div>
                    <p class="text-xs text-wc-text-tertiary">Descanso: <span x-text="60 - config.workSec"></span>s por ronda &middot; <span x-text="config.minutes"></span> rondas</p>
                </div>
            </template>
        </div>

        {{-- Controls --}}
        <div class="mt-6 flex items-center justify-center gap-4">
            <button x-show="!running && !paused" x-on:click="start()" class="rounded-lg bg-wc-accent px-8 py-3 text-sm font-semibold text-white hover:bg-wc-accent-hover">
                Iniciar
            </button>
            <button x-show="running" x-on:click="pause()" class="rounded-lg border border-yellow-500 px-8 py-3 text-sm font-semibold text-yellow-500 hover:bg-yellow-500/10">
                Pausar
            </button>
            <button x-show="paused" x-on:click="resume()" class="rounded-lg bg-wc-accent px-8 py-3 text-sm font-semibold text-white hover:bg-wc-accent-hover">
                Reanudar
            </button>
            <button x-show="running || paused" x-on:click="stop()" class="rounded-lg border border-wc-border px-8 py-3 text-sm font-semibold text-wc-text-secondary hover:text-wc-text">
                Detener
            </button>
        </div>

        {{-- Mode descriptions --}}
        <div class="mt-8 grid grid-cols-2 gap-3">
            <div class="rounded-lg border border-wc-border bg-wc-bg-tertiary p-4">
                <p class="text-xs font-semibold text-wc-accent">Timer</p>
                <p class="mt-1 text-xs text-wc-text-tertiary">Cuenta regresiva simple. Ideal para descansos entre series.</p>
            </div>
            <div class="rounded-lg border border-wc-border bg-wc-bg-tertiary p-4">
                <p class="text-xs font-semibold text-wc-accent">Tabata</p>
                <p class="mt-1 text-xs text-wc-text-tertiary">20s trabajo / 10s descanso. Protocolo HIIT clasico de 4 minutos.</p>
            </div>
            <div class="rounded-lg border border-wc-border bg-wc-bg-tertiary p-4">
                <p class="text-xs font-semibold text-wc-accent">AMRAP</p>
                <p class="mt-1 text-xs text-wc-text-tertiary">Maximo reps en tiempo fijo. Mide capacidad de trabajo.</p>
            </div>
            <div class="rounded-lg border border-wc-border bg-wc-bg-tertiary p-4">
                <p class="text-xs font-semibold text-wc-accent">EMOM</p>
                <p class="mt-1 text-xs text-wc-text-tertiary">Trabajo al inicio de cada minuto. Descanso = lo que sobre.</p>
            </div>
        </div>
    </div>

    <script>
        function workoutTimer() {
            const C = 2 * Math.PI * 90;
            return {
                modes: [
                    { id: 'timer', label: 'Timer' },
                    { id: 'tabata', label: 'Tabata' },
                    { id: 'amrap', label: 'AMRAP' },
                    { id: 'emom', label: 'EMOM' },
                ],
                mode: 'timer',
                config: { minutes: 5, rounds: 8, workSec: 40 },
                running: false, paused: false, interval: null,
                seconds: 0, totalSeconds: 0,
                currentRound: 1, totalRounds: 1,
                isWork: true, phase: '',
                circumference: C, dashOffset: 0,
                get display() {
                    const m = Math.floor(this.seconds / 60);
                    const s = this.seconds % 60;
                    return String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
                },
                selectMode(m) { this.stop(); this.mode = m; },
                start() {
                    if (this.mode === 'timer') {
                        this.totalSeconds = this.config.minutes * 60;
                        this.seconds = this.totalSeconds;
                        this.totalRounds = 1; this.currentRound = 1;
                        this.phase = 'Timer'; this.isWork = true;
                    } else if (this.mode === 'tabata') {
                        this.totalRounds = this.config.rounds;
                        this.currentRound = 1; this.isWork = true;
                        this.seconds = 20; this.totalSeconds = 20;
                        this.phase = 'TRABAJO';
                    } else if (this.mode === 'amrap') {
                        this.totalSeconds = this.config.minutes * 60;
                        this.seconds = this.totalSeconds;
                        this.totalRounds = 1; this.currentRound = 1;
                        this.phase = 'AMRAP'; this.isWork = true;
                    } else if (this.mode === 'emom') {
                        this.totalRounds = this.config.minutes;
                        this.currentRound = 1; this.isWork = true;
                        this.seconds = this.config.workSec;
                        this.totalSeconds = this.config.workSec;
                        this.phase = 'TRABAJO';
                    }
                    this.running = true; this.paused = false;
                    this.tick();
                },
                tick() {
                    this.interval = setInterval(() => {
                        if (this.seconds <= 0) {
                            this.nextPhase();
                            return;
                        }
                        this.seconds--;
                        this.dashOffset = C * (1 - this.seconds / this.totalSeconds);
                    }, 1000);
                },
                nextPhase() {
                    if (this.mode === 'timer' || this.mode === 'amrap') {
                        this.beep(); this.stop(); return;
                    }
                    if (this.mode === 'tabata') {
                        if (this.isWork) {
                            this.isWork = false; this.phase = 'DESCANSO';
                            this.seconds = 10; this.totalSeconds = 10;
                            this.beep();
                        } else {
                            if (this.currentRound >= this.totalRounds) { this.beep(); this.stop(); return; }
                            this.currentRound++; this.isWork = true; this.phase = 'TRABAJO';
                            this.seconds = 20; this.totalSeconds = 20;
                            this.beep();
                        }
                    }
                    if (this.mode === 'emom') {
                        if (this.isWork) {
                            this.isWork = false; this.phase = 'DESCANSO';
                            const rest = 60 - this.config.workSec;
                            this.seconds = rest; this.totalSeconds = rest;
                            this.beep();
                        } else {
                            if (this.currentRound >= this.totalRounds) { this.beep(); this.stop(); return; }
                            this.currentRound++; this.isWork = true; this.phase = 'TRABAJO';
                            this.seconds = this.config.workSec; this.totalSeconds = this.config.workSec;
                            this.beep();
                        }
                    }
                    this.dashOffset = 0;
                },
                pause() { clearInterval(this.interval); this.running = false; this.paused = true; },
                resume() { this.running = true; this.paused = false; this.tick(); },
                stop() {
                    clearInterval(this.interval);
                    this.running = false; this.paused = false;
                    this.seconds = 0; this.dashOffset = 0;
                    this.phase = ''; this.currentRound = 1;
                },
                beep() {
                    try {
                        const ctx = new (window.AudioContext || window.webkitAudioContext)();
                        const osc = ctx.createOscillator();
                        const gain = ctx.createGain();
                        osc.connect(gain); gain.connect(ctx.destination);
                        osc.frequency.value = 880; gain.gain.value = 0.3;
                        osc.start(); osc.stop(ctx.currentTime + 0.15);
                    } catch(e) {}
                }
            };
        }
    </script>
</div>
