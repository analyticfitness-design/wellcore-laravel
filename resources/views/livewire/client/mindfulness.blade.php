<div class="space-y-6" x-data="mindfulness()">
    <div>
        <h1 class="font-display text-3xl tracking-wide text-wc-text">MINDFULNESS</h1>
        <p class="mt-1 text-sm text-wc-text-secondary">3 tecnicas de respiracion guiada con animacion visual y sonido ambiental.</p>
    </div>

    {{-- Technique Tabs --}}
    <div class="flex flex-wrap gap-2">
        <template x-for="t in techniques" :key="t.id">
            <button x-on:click="selectTechnique(t.id)" :class="technique === t.id ? 'bg-wc-accent text-white' : 'bg-wc-bg-tertiary text-wc-text-secondary hover:text-wc-text'" class="rounded-lg border border-wc-border px-4 py-2 text-sm font-medium transition-colors" x-text="t.label"></button>
        </template>
    </div>

    <div class="mx-auto max-w-md">
        {{-- Breathing Circle SVG --}}
        <div class="relative mx-auto flex h-72 w-72 items-center justify-center sm:h-80 sm:w-80">
            {{-- Outer ambient glow --}}
            <div class="absolute inset-0 rounded-full transition-all duration-1000" :class="running ? (phase === 'inhala' ? 'shadow-[0_0_60px_rgba(220,38,38,0.15)]' : phase === 'exhala' ? 'shadow-[0_0_60px_rgba(59,130,246,0.15)]' : 'shadow-[0_0_60px_rgba(168,85,247,0.15)]') : ''"></div>

            {{-- Background circle --}}
            <svg class="absolute inset-0" viewBox="0 0 200 200">
                <circle cx="100" cy="100" r="90" fill="none" stroke="currentColor" stroke-width="2" class="text-wc-border" stroke-dasharray="4 4" />
            </svg>

            {{-- Animated breathing circle --}}
            <svg class="absolute inset-0 transition-transform" viewBox="0 0 200 200" :style="{ transform: `scale(${breathScale})`, transformOrigin: 'center', transition: `transform ${phaseDuration}s ease-in-out` }">
                <defs>
                    <radialGradient id="breathGrad" cx="50%" cy="50%" r="50%">
                        <stop offset="0%" :stop-color="phase === 'inhala' ? '#DC2626' : phase === 'exhala' ? '#3B82F6' : '#A855F7'" stop-opacity="0.15" />
                        <stop offset="100%" :stop-color="phase === 'inhala' ? '#DC2626' : phase === 'exhala' ? '#3B82F6' : '#A855F7'" stop-opacity="0.02" />
                    </radialGradient>
                </defs>
                <circle cx="100" cy="100" r="70" fill="url(#breathGrad)" />
                <circle cx="100" cy="100" r="70" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" :class="phase === 'inhala' ? 'text-wc-accent' : phase === 'exhala' ? 'text-blue-500' : 'text-purple-500'" class="transition-colors duration-500" />
            </svg>

            {{-- Progress ring --}}
            <svg class="absolute inset-0 -rotate-90" viewBox="0 0 200 200">
                <circle cx="100" cy="100" r="92" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" class="text-wc-accent/40 transition-all duration-1000" :stroke-dasharray="totalCircumference" :stroke-dashoffset="progressOffset" />
            </svg>

            {{-- Center text --}}
            <div class="relative text-center">
                <p class="font-data text-5xl font-bold text-wc-text sm:text-6xl" x-text="phaseCountdown"></p>
                <p class="mt-2 text-sm font-semibold uppercase tracking-widest transition-colors duration-500" :class="phase === 'inhala' ? 'text-wc-accent' : phase === 'exhala' ? 'text-blue-500' : 'text-purple-500'" x-text="phaseLabel" x-show="running || paused"></p>
                <p class="mt-1 text-xs text-wc-text-tertiary" x-show="running || paused">
                    Ciclo <span x-text="currentCycle"></span>/<span x-text="totalCycles"></span>
                </p>
                <p class="text-xs text-wc-text-tertiary" x-show="!running && !paused">Presiona iniciar</p>
            </div>
        </div>

        {{-- Config (visible when stopped) --}}
        <div x-show="!running && !paused" class="mt-6 rounded-xl border border-wc-border bg-wc-bg-tertiary p-6 space-y-4">
            {{-- Technique description --}}
            <div>
                <h3 class="text-sm font-semibold text-wc-text" x-text="currentTechnique.label"></h3>
                <p class="mt-1 text-xs text-wc-text-tertiary" x-text="currentTechnique.description"></p>
            </div>

            {{-- Phase breakdown --}}
            <div class="flex items-center gap-2 text-xs">
                <template x-for="(p, i) in currentTechnique.phases" :key="i">
                    <div class="flex items-center gap-1">
                        <span class="inline-block h-2 w-2 rounded-full" :class="p.type === 'inhala' ? 'bg-wc-accent' : p.type === 'exhala' ? 'bg-blue-500' : 'bg-purple-500'"></span>
                        <span class="text-wc-text-secondary" x-text="p.label + ' ' + p.seconds + 's'"></span>
                        <span x-show="i < currentTechnique.phases.length - 1" class="text-wc-text-tertiary mx-1">&rarr;</span>
                    </div>
                </template>
            </div>

            {{-- Cycles config --}}
            <div>
                <label class="block text-xs font-medium text-wc-text-tertiary">Ciclos</label>
                <input type="number" x-model.number="totalCycles" min="1" max="20" class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none" />
                <p class="mt-1 text-xs text-wc-text-tertiary">
                    Duracion total: <span x-text="formatTime(totalCycles * currentTechnique.cycleDuration)"></span>
                </p>
            </div>

            {{-- Sound toggle --}}
            <label class="flex items-center gap-3 cursor-pointer">
                <div class="relative">
                    <input type="checkbox" x-model="soundEnabled" class="sr-only peer" />
                    <div class="h-5 w-9 rounded-full bg-wc-border peer-checked:bg-wc-accent transition-colors"></div>
                    <div class="absolute left-0.5 top-0.5 h-4 w-4 rounded-full bg-white transition-transform peer-checked:translate-x-4"></div>
                </div>
                <span class="text-xs text-wc-text-secondary">Sonido ambiental guia</span>
            </label>
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

        {{-- Session complete overlay --}}
        <div x-show="completed" x-transition class="mt-6 rounded-xl border border-green-500/30 bg-green-500/10 p-6 text-center">
            <svg class="mx-auto h-12 w-12 text-green-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
            <p class="mt-3 text-sm font-semibold text-green-400">Sesion completada</p>
            <p class="mt-1 text-xs text-wc-text-tertiary">
                <span x-text="currentTechnique.label"></span> &middot; <span x-text="totalCycles"></span> ciclos &middot; <span x-text="formatTime(totalCycles * currentTechnique.cycleDuration)"></span>
            </p>
            <button x-on:click="completed = false" class="mt-4 rounded-lg border border-wc-border px-6 py-2 text-sm font-medium text-wc-text-secondary hover:text-wc-text">
                Cerrar
            </button>
        </div>

        {{-- Technique cards --}}
        <div class="mt-8 grid grid-cols-1 gap-3 sm:grid-cols-3">
            <div class="rounded-lg border border-wc-border bg-wc-bg-tertiary p-4">
                <div class="flex items-center gap-2">
                    <span class="inline-block h-2.5 w-2.5 rounded-full bg-wc-accent"></span>
                    <p class="text-xs font-semibold text-wc-accent">4-7-8</p>
                </div>
                <p class="mt-2 text-xs text-wc-text-tertiary">Tecnica del Dr. Andrew Weil. Inhala 4s, manten 7s, exhala 8s. Activa el sistema nervioso parasimpatico para calma profunda.</p>
            </div>
            <div class="rounded-lg border border-wc-border bg-wc-bg-tertiary p-4">
                <div class="flex items-center gap-2">
                    <span class="inline-block h-2.5 w-2.5 rounded-full bg-blue-500"></span>
                    <p class="text-xs font-semibold text-blue-500">Box Breathing</p>
                </div>
                <p class="mt-2 text-xs text-wc-text-tertiary">Usada por Navy SEALs. 4 fases iguales de 4s. Equilibra el sistema nervioso autonomo y mejora el enfoque bajo presion.</p>
            </div>
            <div class="rounded-lg border border-wc-border bg-wc-bg-tertiary p-4">
                <div class="flex items-center gap-2">
                    <span class="inline-block h-2.5 w-2.5 rounded-full bg-purple-500"></span>
                    <p class="text-xs font-semibold text-purple-500">Coherente</p>
                </div>
                <p class="mt-2 text-xs text-wc-text-tertiary">Frecuencia de resonancia: 5.5 respiraciones/min. Sincroniza corazon, cerebro y sistema nervioso. Ideal pre-entrenamiento.</p>
            </div>
        </div>
    </div>

    <script>
        function mindfulness() {
            const CIRC = 2 * Math.PI * 92;

            const TECHNIQUES = {
                '478': {
                    id: '478',
                    label: 'Respiracion 4-7-8',
                    description: 'Tecnica del Dr. Andrew Weil. Reduce ansiedad, mejora el sueno y activa la respuesta de relajacion.',
                    cycleDuration: 19,
                    phases: [
                        { type: 'inhala', label: 'Inhala', seconds: 4 },
                        { type: 'manten', label: 'Manten', seconds: 7 },
                        { type: 'exhala', label: 'Exhala', seconds: 8 },
                    ],
                },
                'box': {
                    id: 'box',
                    label: 'Box Breathing',
                    description: 'Tecnica Navy SEAL. 4 fases iguales para control total del sistema nervioso bajo presion.',
                    cycleDuration: 16,
                    phases: [
                        { type: 'inhala', label: 'Inhala', seconds: 4 },
                        { type: 'manten', label: 'Manten', seconds: 4 },
                        { type: 'exhala', label: 'Exhala', seconds: 4 },
                        { type: 'manten', label: 'Manten', seconds: 4 },
                    ],
                },
                'coherent': {
                    id: 'coherent',
                    label: 'Respiracion Coherente',
                    description: 'Frecuencia de resonancia a 5.5 respiraciones/min. Sincroniza ritmo cardiaco y sistema nervioso.',
                    cycleDuration: 11,
                    phases: [
                        { type: 'inhala', label: 'Inhala', seconds: 5.5 },
                        { type: 'exhala', label: 'Exhala', seconds: 5.5 },
                    ],
                },
            };

            return {
                techniques: [
                    { id: '478', label: '4-7-8' },
                    { id: 'box', label: 'Box Breathing' },
                    { id: 'coherent', label: 'Coherente' },
                ],
                technique: '478',
                totalCycles: 4,
                currentCycle: 1,
                soundEnabled: true,
                running: false,
                paused: false,
                completed: false,
                phase: 'inhala',
                phaseIndex: 0,
                phaseCountdown: 0,
                phaseDuration: 1,
                breathScale: 0.6,
                interval: null,
                elapsed: 0,
                totalCircumference: CIRC,
                progressOffset: CIRC,
                audioCtx: null,
                oscillator: null,
                gainNode: null,

                get currentTechnique() {
                    return TECHNIQUES[this.technique];
                },
                get phaseLabel() {
                    const labels = { inhala: 'INHALA', manten: 'MANTEN', exhala: 'EXHALA' };
                    return labels[this.phase] || '';
                },

                selectTechnique(id) {
                    this.stop();
                    this.technique = id;
                },

                formatTime(secs) {
                    const m = Math.floor(secs / 60);
                    const s = Math.round(secs % 60);
                    return m > 0 ? m + 'min ' + s + 's' : s + 's';
                },

                start() {
                    this.completed = false;
                    this.currentCycle = 1;
                    this.phaseIndex = 0;
                    this.elapsed = 0;
                    this.running = true;
                    this.paused = false;
                    this.startPhase();
                    this.tick();
                },

                startPhase() {
                    const phases = this.currentTechnique.phases;
                    const p = phases[this.phaseIndex];
                    this.phase = p.type;
                    this.phaseDuration = p.seconds;
                    this.phaseCountdown = Math.ceil(p.seconds);

                    // Animate breath scale
                    if (p.type === 'inhala') {
                        this.breathScale = 1.0;
                    } else if (p.type === 'exhala') {
                        this.breathScale = 0.6;
                    }
                    // 'manten' keeps current scale

                    // Sound
                    if (this.soundEnabled) {
                        this.playTone(p.type, p.seconds);
                    }
                },

                tick() {
                    const totalSessionDuration = this.totalCycles * this.currentTechnique.cycleDuration;
                    this.interval = setInterval(() => {
                        this.elapsed += 1;
                        this.phaseCountdown = Math.max(0, this.phaseCountdown - 1);

                        // Update progress ring
                        const progress = this.elapsed / totalSessionDuration;
                        this.progressOffset = CIRC * (1 - Math.min(progress, 1));

                        if (this.phaseCountdown <= 0) {
                            this.nextPhase();
                        }
                    }, 1000);
                },

                nextPhase() {
                    const phases = this.currentTechnique.phases;
                    this.phaseIndex++;

                    if (this.phaseIndex >= phases.length) {
                        // Cycle complete
                        if (this.currentCycle >= this.totalCycles) {
                            // Session complete
                            this.completeSession();
                            return;
                        }
                        this.currentCycle++;
                        this.phaseIndex = 0;
                    }

                    this.startPhase();
                },

                completeSession() {
                    this.stopAudio();
                    clearInterval(this.interval);
                    this.running = false;
                    this.completed = true;
                    this.breathScale = 0.6;
                    this.progressOffset = 0;

                    // Completion chime
                    if (this.soundEnabled) {
                        this.playChime();
                    }
                },

                pause() {
                    clearInterval(this.interval);
                    this.running = false;
                    this.paused = true;
                    this.stopAudio();
                },

                resume() {
                    this.running = true;
                    this.paused = false;
                    this.tick();
                    if (this.soundEnabled) {
                        const p = this.currentTechnique.phases[this.phaseIndex];
                        this.playTone(p.type, this.phaseCountdown);
                    }
                },

                stop() {
                    clearInterval(this.interval);
                    this.stopAudio();
                    this.running = false;
                    this.paused = false;
                    this.completed = false;
                    this.phaseCountdown = 0;
                    this.breathScale = 0.6;
                    this.progressOffset = CIRC;
                    this.elapsed = 0;
                    this.phase = 'inhala';
                    this.phaseIndex = 0;
                    this.currentCycle = 1;
                },

                // --- Audio ---
                initAudio() {
                    if (!this.audioCtx) {
                        this.audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                    }
                    if (this.audioCtx.state === 'suspended') {
                        this.audioCtx.resume();
                    }
                },

                playTone(type, duration) {
                    try {
                        this.stopAudio();
                        this.initAudio();
                        const ctx = this.audioCtx;

                        this.oscillator = ctx.createOscillator();
                        this.gainNode = ctx.createGain();

                        // Different frequencies per phase type
                        const freqs = { inhala: 174, manten: 285, exhala: 136 };
                        this.oscillator.type = 'sine';
                        this.oscillator.frequency.setValueAtTime(freqs[type] || 174, ctx.currentTime);

                        // Gentle volume envelope
                        this.gainNode.gain.setValueAtTime(0, ctx.currentTime);
                        this.gainNode.gain.linearRampToValueAtTime(0.08, ctx.currentTime + 0.5);
                        this.gainNode.gain.linearRampToValueAtTime(0.06, ctx.currentTime + duration * 0.5);
                        this.gainNode.gain.linearRampToValueAtTime(0, ctx.currentTime + duration);

                        this.oscillator.connect(this.gainNode);
                        this.gainNode.connect(ctx.destination);

                        this.oscillator.start(ctx.currentTime);
                        this.oscillator.stop(ctx.currentTime + duration);
                    } catch (e) {}
                },

                stopAudio() {
                    try {
                        if (this.oscillator) {
                            this.oscillator.stop();
                            this.oscillator.disconnect();
                            this.oscillator = null;
                        }
                        if (this.gainNode) {
                            this.gainNode.disconnect();
                            this.gainNode = null;
                        }
                    } catch (e) {}
                },

                playChime() {
                    try {
                        this.initAudio();
                        const ctx = this.audioCtx;
                        const notes = [523.25, 659.25, 783.99]; // C5, E5, G5 major chord

                        notes.forEach((freq, i) => {
                            const osc = ctx.createOscillator();
                            const gain = ctx.createGain();
                            osc.type = 'sine';
                            osc.frequency.value = freq;
                            gain.gain.setValueAtTime(0, ctx.currentTime + i * 0.2);
                            gain.gain.linearRampToValueAtTime(0.12, ctx.currentTime + i * 0.2 + 0.1);
                            gain.gain.linearRampToValueAtTime(0, ctx.currentTime + i * 0.2 + 1.5);
                            osc.connect(gain);
                            gain.connect(ctx.destination);
                            osc.start(ctx.currentTime + i * 0.2);
                            osc.stop(ctx.currentTime + i * 0.2 + 1.5);
                        });
                    } catch (e) {}
                },
            };
        }
    </script>
</div>
