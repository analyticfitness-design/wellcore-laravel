<div class="space-y-8" x-data="mindfulness()">

    {{-- Page Header --}}
    <div>
        <h1 class="font-display text-3xl tracking-wide text-wc-text">MINDFULNESS</h1>
        <p class="mt-1 text-sm text-wc-text-secondary">Respiración guiada y sesiones de bienestar mental para optimizar tu rendimiento.</p>
    </div>

    {{-- ================================================================
         SECTION 1: Guided Sessions (Livewire-driven)
         ================================================================ --}}
    <section aria-label="Sesiones guiadas">
        <h2 class="mb-4 font-display text-xl tracking-wide text-wc-text">SESIONES GUIADAS</h2>

        @if($sessionActive)
            {{-- Active Session: Timer View --}}
            <div
                x-data="{
                    total: {{ $sessionDuration }},
                    remaining: {{ $sessionDuration }},
                    running: false,
                    _interval: null,
                    start() {
                        if (this.running) return;
                        this.running = true;
                        this._interval = setInterval(() => {
                            if (this.remaining > 0) {
                                this.remaining--;
                            } else {
                                this.running = false;
                                clearInterval(this._interval);
                            }
                        }, 1000);
                    },
                    pause() {
                        this.running = false;
                        clearInterval(this._interval);
                        this._interval = null;
                    },
                    formatTime(secs) {
                        let m = Math.floor(secs / 60);
                        let s = secs % 60;
                        return String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
                    },
                    get progress() {
                        return this.total > 0 ? ((this.total - this.remaining) / this.total) * 100 : 0;
                    },
                    destroy() {
                        if (this._interval) clearInterval(this._interval);
                    }
                }"
                x-init="start()"
                class="rounded-xl border border-wc-accent/30 bg-wc-bg-tertiary p-8"
            >
                {{-- Session label --}}
                <div class="mb-6 flex items-center justify-between">
                    <div>
                        @php
                            $sessionLabels = [
                                'breathing'     => 'Respiración 4-7-8',
                                'meditation'    => 'Meditación de Atención Plena',
                                'body-scan'     => 'Body Scan',
                                'visualization' => 'Visualización de Rendimiento',
                            ];
                            $sessionEmojis = [
                                'breathing'     => '🌬️',
                                'meditation'    => '🧘',
                                'body-scan'     => '🔍',
                                'visualization' => '🏆',
                            ];
                        @endphp
                        <p class="text-xs font-semibold uppercase tracking-widest text-wc-accent">Sesión activa</p>
                        <h3 class="mt-1 font-display text-2xl tracking-wide text-wc-text">
                            {{ $sessionEmojis[$activeSession] ?? '' }}
                            {{ $sessionLabels[$activeSession] ?? $activeSession }}
                        </h3>
                    </div>
                    <button
                        wire:click="endSession"
                        aria-label="Terminar sesión"
                        class="rounded-lg border border-wc-border px-4 py-2 text-sm font-medium text-wc-text-secondary transition-colors hover:border-red-500/50 hover:text-red-400 focus:outline-none focus:ring-2 focus:ring-wc-accent"
                    >
                        Terminar sesión
                    </button>
                </div>

                {{-- Circular Timer --}}
                <div class="flex flex-col items-center gap-6">
                    <div class="relative flex h-52 w-52 items-center justify-center">
                        {{-- SVG Progress Ring --}}
                        <svg class="-rotate-90 absolute inset-0 h-full w-full" viewBox="0 0 200 200" aria-hidden="true">
                            <circle cx="100" cy="100" r="88" fill="none" stroke="currentColor" stroke-width="4" class="text-wc-border" />
                            <circle
                                cx="100" cy="100" r="88"
                                fill="none"
                                stroke="#DC2626"
                                stroke-width="4"
                                stroke-linecap="round"
                                :stroke-dasharray="2 * Math.PI * 88"
                                :stroke-dashoffset="2 * Math.PI * 88 * (1 - progress / 100)"
                                class="transition-all duration-1000"
                            />
                        </svg>

                        {{-- Countdown Text --}}
                        <div class="relative text-center" aria-live="polite" aria-atomic="true">
                            <span class="font-data text-5xl font-bold tabular-nums text-wc-text" x-text="formatTime(remaining)"></span>
                            <p class="mt-1 text-xs font-medium uppercase tracking-widest text-wc-text-secondary">restante</p>
                        </div>
                    </div>

                    {{-- Completed indicator --}}
                    <div x-show="remaining === 0 && !running" x-transition class="rounded-xl border border-green-500/30 bg-green-500/10 px-6 py-3 text-center">
                        <p class="text-sm font-semibold text-green-400">Sesión completada</p>
                        <p class="mt-1 text-xs text-wc-text-secondary">Buen trabajo. Tu mente y cuerpo lo agradecen.</p>
                    </div>

                    {{-- Pause / Resume controls --}}
                    <div class="flex items-center gap-3">
                        <button
                            x-show="running"
                            x-on:click="pause()"
                            class="rounded-lg border border-yellow-500/60 px-6 py-2.5 text-sm font-semibold text-yellow-400 transition-colors hover:bg-yellow-500/10 focus:outline-none focus:ring-2 focus:ring-wc-accent"
                        >
                            Pausar
                        </button>
                        <button
                            x-show="!running && remaining > 0"
                            x-on:click="start()"
                            class="rounded-lg bg-wc-accent px-6 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-wc-accent"
                        >
                            Reanudar
                        </button>
                    </div>
                </div>

                {{-- Progress bar (linear) --}}
                <div class="mt-6" role="progressbar" :aria-valuenow="Math.round(progress)" aria-valuemin="0" aria-valuemax="100">
                    <div class="h-1.5 w-full overflow-hidden rounded-full bg-wc-bg-secondary">
                        <div
                            class="h-full rounded-full bg-wc-accent transition-all duration-1000"
                            :style="'width:' + progress + '%'"
                        ></div>
                    </div>
                    <div class="mt-1.5 flex justify-between text-xs text-wc-text-secondary">
                        <span>Inicio</span>
                        <span x-text="Math.round(progress) + '%'"></span>
                        <span>Fin</span>
                    </div>
                </div>
            </div>

        @else
            {{-- Session Cards Grid --}}
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach($sessions as $session)
                    <button
                        wire:click="startSession('{{ $session['id'] }}')"
                        wire:loading.attr="disabled"
                        wire:target="startSession"
                        aria-label="Iniciar sesión: {{ $session['title'] }}"
                        class="group relative flex flex-col items-start rounded-xl border border-wc-border bg-wc-bg-tertiary p-5 text-left transition-all hover:border-wc-accent/50 hover:bg-wc-bg-secondary focus:outline-none focus:ring-2 focus:ring-wc-accent active:scale-[0.98]"
                    >
                        {{-- Loading state --}}
                        <div wire:loading wire:target="startSession('{{ $session['id'] }}')" class="absolute inset-0 flex items-center justify-center rounded-xl bg-wc-bg-tertiary/80">
                            <svg class="h-5 w-5 animate-spin text-wc-accent" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </div>

                        {{-- Emoji --}}
                        <span class="mb-3 text-3xl" aria-hidden="true">{{ $session['emoji'] }}</span>

                        {{-- Title --}}
                        <h3 class="font-display text-lg tracking-wide text-wc-text">{{ strtoupper($session['title']) }}</h3>

                        {{-- Description --}}
                        <p class="mt-1.5 text-xs leading-relaxed text-wc-text-secondary">{{ $session['description'] }}</p>

                        {{-- Footer meta --}}
                        <div class="mt-4 flex w-full items-center justify-between">
                            <span class="inline-flex items-center gap-1 rounded-full border border-wc-border bg-wc-bg-secondary px-2.5 py-0.5 text-xs font-medium text-wc-text-secondary">
                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                </svg>
                                {{ $session['duration'] }}
                            </span>
                            <span class="text-xs font-semibold text-wc-accent">{{ $session['benefit'] }}</span>
                        </div>

                        {{-- Hover arrow indicator --}}
                        <div class="absolute right-4 top-4 opacity-0 transition-opacity group-hover:opacity-100" aria-hidden="true">
                            <svg class="h-4 w-4 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                            </svg>
                        </div>
                    </button>
                @endforeach
            </div>
        @endif
    </section>

    {{-- ================================================================
         SECTION 2: Breathing Exercises (Alpine.js — fully client-side)
         ================================================================ --}}
    <section aria-label="Ejercicios de respiración">
        <h2 class="mb-4 font-display text-xl tracking-wide text-wc-text">EJERCICIOS DE RESPIRACIÓN</h2>

        {{-- Technique Tabs --}}
        <div class="flex flex-wrap gap-2" role="tablist" aria-label="Técnicas de respiración">
            <template x-for="t in techniques" :key="t.id">
                <button
                    role="tab"
                    :aria-selected="technique === t.id"
                    x-on:click="selectTechnique(t.id)"
                    :class="technique === t.id ? 'bg-wc-accent text-white' : 'bg-wc-bg-tertiary text-wc-text-secondary hover:text-wc-text'"
                    class="rounded-lg border border-wc-border px-4 py-2 text-sm font-medium transition-colors focus:outline-none focus:ring-2 focus:ring-wc-accent"
                    x-text="t.label"
                ></button>
            </template>
        </div>

        <div class="mx-auto mt-6 max-w-md">
            {{-- Breathing Circle SVG --}}
            <div class="relative mx-auto flex h-72 w-72 items-center justify-center sm:h-80 sm:w-80">
                {{-- Outer ambient glow --}}
                <div class="absolute inset-0 rounded-full transition-all duration-1000" :class="running ? (phase === 'inhala' ? 'shadow-[0_0_60px_rgba(220,38,38,0.15)]' : phase === 'exhala' ? 'shadow-[0_0_60px_rgba(59,130,246,0.15)]' : 'shadow-[0_0_60px_rgba(168,85,247,0.15)]') : ''"></div>

                {{-- Background dashed ring --}}
                <svg class="absolute inset-0" viewBox="0 0 200 200" aria-hidden="true">
                    <circle cx="100" cy="100" r="90" fill="none" stroke="currentColor" stroke-width="2" class="text-wc-border" stroke-dasharray="4 4" />
                </svg>

                {{-- Animated breathing circle --}}
                <svg class="absolute inset-0 transition-transform" viewBox="0 0 200 200" :style="{ transform: `scale(${breathScale})`, transformOrigin: 'center', transition: `transform ${phaseDuration}s ease-in-out` }" aria-hidden="true">
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
                <svg class="absolute inset-0 -rotate-90" viewBox="0 0 200 200" aria-hidden="true">
                    <circle cx="100" cy="100" r="92" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" class="text-wc-accent/40 transition-all duration-1000" :stroke-dasharray="totalCircumference" :stroke-dashoffset="progressOffset" />
                </svg>

                {{-- Center text --}}
                <div class="relative text-center" aria-live="polite" aria-atomic="true">
                    <p class="font-data text-5xl font-bold tabular-nums text-wc-text sm:text-6xl" x-text="phaseCountdown"></p>
                    <p class="mt-2 text-sm font-semibold uppercase tracking-widest transition-colors duration-500" :class="phase === 'inhala' ? 'text-wc-accent' : phase === 'exhala' ? 'text-blue-500' : 'text-purple-500'" x-text="phaseLabel" x-show="running || paused"></p>
                    <p class="mt-1 text-xs text-wc-text-secondary" x-show="running || paused">
                        Ciclo <span x-text="currentCycle"></span>/<span x-text="totalCycles"></span>
                    </p>
                    <p class="text-xs text-wc-text-secondary" x-show="!running && !paused">Presiona iniciar</p>
                </div>
            </div>

            {{-- Config panel (visible when stopped) --}}
            <div x-show="!running && !paused" class="mt-6 space-y-4 rounded-xl border border-wc-border bg-wc-bg-tertiary p-6">
                <div>
                    <h3 class="text-sm font-semibold text-wc-text" x-text="currentTechnique.label"></h3>
                    <p class="mt-1 text-xs text-wc-text-secondary" x-text="currentTechnique.description"></p>
                </div>

                {{-- Phase breakdown --}}
                <div class="flex flex-wrap items-center gap-2 text-xs">
                    <template x-for="(p, i) in currentTechnique.phases" :key="i">
                        <div class="flex items-center gap-1">
                            <span class="inline-block h-2 w-2 rounded-full" :class="p.type === 'inhala' ? 'bg-wc-accent' : p.type === 'exhala' ? 'bg-blue-500' : 'bg-purple-500'"></span>
                            <span class="text-wc-text-secondary" x-text="p.label + ' ' + p.seconds + 's'"></span>
                            <span x-show="i < currentTechnique.phases.length - 1" class="mx-1 text-wc-text-secondary">&rarr;</span>
                        </div>
                    </template>
                </div>

                {{-- Cycle count --}}
                <div>
                    <label class="block text-xs font-medium text-wc-text-secondary">Ciclos</label>
                    <input
                        type="number"
                        x-model.number="totalCycles"
                        min="1"
                        max="20"
                        aria-label="Número de ciclos"
                        class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg px-3 py-2 text-sm text-wc-text focus:border-wc-accent focus:outline-none focus:ring-2 focus:ring-wc-accent/20"
                    />
                    <p class="mt-1 text-xs text-wc-text-secondary">
                        Duración total: <span x-text="formatTime(totalCycles * currentTechnique.cycleDuration)"></span>
                    </p>
                </div>

                {{-- Sound toggle --}}
                <label class="flex cursor-pointer items-center gap-3">
                    <div class="relative">
                        <input type="checkbox" x-model="soundEnabled" class="sr-only peer" aria-label="Sonido ambiental" />
                        <div class="h-5 w-9 rounded-full bg-wc-border transition-colors peer-checked:bg-wc-accent"></div>
                        <div class="absolute left-0.5 top-0.5 h-4 w-4 rounded-full bg-white transition-transform peer-checked:translate-x-4"></div>
                    </div>
                    <span class="text-xs text-wc-text-secondary">Sonido ambiental guía</span>
                </label>
            </div>

            {{-- Controls --}}
            <div class="mt-6 flex items-center justify-center gap-4">
                <button
                    x-show="!running && !paused"
                    x-on:click="start()"
                    class="rounded-lg bg-wc-accent px-8 py-3 text-sm font-semibold text-white transition-colors hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-wc-accent"
                >
                    Iniciar
                </button>
                <button
                    x-show="running"
                    x-on:click="pause()"
                    class="rounded-lg border border-yellow-500 px-8 py-3 text-sm font-semibold text-yellow-400 transition-colors hover:bg-yellow-500/10 focus:outline-none focus:ring-2 focus:ring-wc-accent"
                >
                    Pausar
                </button>
                <button
                    x-show="paused"
                    x-on:click="resume()"
                    class="rounded-lg bg-wc-accent px-8 py-3 text-sm font-semibold text-white transition-colors hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-wc-accent"
                >
                    Reanudar
                </button>
                <button
                    x-show="running || paused"
                    x-on:click="stop()"
                    aria-label="Detener sesión de respiración"
                    class="rounded-lg border border-wc-border px-8 py-3 text-sm font-semibold text-wc-text-secondary transition-colors hover:text-wc-text focus:outline-none focus:ring-2 focus:ring-wc-accent"
                >
                    Detener
                </button>
            </div>

            {{-- Session complete banner --}}
            <div x-show="completed" x-transition class="mt-6 rounded-xl border border-green-500/30 bg-green-500/10 p-6 text-center" role="status">
                <svg class="mx-auto h-12 w-12 text-green-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                <p class="mt-3 text-sm font-semibold text-green-400">Sesión completada</p>
                <p class="mt-1 text-xs text-wc-text-secondary">
                    <span x-text="currentTechnique.label"></span> &middot; <span x-text="totalCycles"></span> ciclos &middot; <span x-text="formatTime(totalCycles * currentTechnique.cycleDuration)"></span>
                </p>
                <button
                    x-on:click="completed = false"
                    class="mt-4 rounded-lg border border-wc-border px-6 py-2 text-sm font-medium text-wc-text-secondary transition-colors hover:text-wc-text focus:outline-none focus:ring-2 focus:ring-wc-accent"
                >
                    Cerrar
                </button>
            </div>

            {{-- Technique info cards --}}
            <div class="mt-8 grid grid-cols-1 gap-3 sm:grid-cols-3">
                <div class="rounded-lg border border-wc-border bg-wc-bg-tertiary p-4">
                    <div class="flex items-center gap-2">
                        <span class="inline-block h-2.5 w-2.5 rounded-full bg-wc-accent"></span>
                        <p class="text-xs font-semibold text-wc-accent">4-7-8</p>
                    </div>
                    <p class="mt-2 text-xs text-wc-text-secondary">Técnica del Dr. Andrew Weil. Inhala 4s, mantén 7s, exhala 8s. Activa el sistema nervioso parasimpático para calma profunda.</p>
                </div>
                <div class="rounded-lg border border-wc-border bg-wc-bg-tertiary p-4">
                    <div class="flex items-center gap-2">
                        <span class="inline-block h-2.5 w-2.5 rounded-full bg-blue-500"></span>
                        <p class="text-xs font-semibold text-blue-400">Box Breathing</p>
                    </div>
                    <p class="mt-2 text-xs text-wc-text-secondary">Usada por Navy SEALs. 4 fases iguales de 4s. Equilibra el sistema nervioso autónomo y mejora el enfoque bajo presión.</p>
                </div>
                <div class="rounded-lg border border-wc-border bg-wc-bg-tertiary p-4">
                    <div class="flex items-center gap-2">
                        <span class="inline-block h-2.5 w-2.5 rounded-full bg-purple-500"></span>
                        <p class="text-xs font-semibold text-purple-400">Coherente</p>
                    </div>
                    <p class="mt-2 text-xs text-wc-text-secondary">Frecuencia de resonancia: 5.5 respiraciones/min. Sincroniza corazón, cerebro y sistema nervioso. Ideal pre-entrenamiento.</p>
                </div>
            </div>
        </div>
    </section>

    <script>
        function mindfulness() {
            const CIRC = 2 * Math.PI * 92;

            const TECHNIQUES = {
                '478': {
                    id: '478',
                    label: 'Respiración 4-7-8',
                    description: 'Técnica del Dr. Andrew Weil. Reduce ansiedad, mejora el sueño y activa la respuesta de relajación.',
                    cycleDuration: 19,
                    phases: [
                        { type: 'inhala', label: 'Inhala', seconds: 4 },
                        { type: 'manten', label: 'Mantén', seconds: 7 },
                        { type: 'exhala', label: 'Exhala', seconds: 8 },
                    ],
                },
                'box': {
                    id: 'box',
                    label: 'Box Breathing',
                    description: 'Técnica Navy SEAL. 4 fases iguales para control total del sistema nervioso bajo presión.',
                    cycleDuration: 16,
                    phases: [
                        { type: 'inhala', label: 'Inhala', seconds: 4 },
                        { type: 'manten', label: 'Mantén', seconds: 4 },
                        { type: 'exhala', label: 'Exhala', seconds: 4 },
                        { type: 'manten', label: 'Mantén', seconds: 4 },
                    ],
                },
                'coherent': {
                    id: 'coherent',
                    label: 'Respiración Coherente',
                    description: 'Frecuencia de resonancia a 5.5 respiraciones/min. Sincroniza ritmo cardíaco y sistema nervioso.',
                    cycleDuration: 11,
                    phases: [
                        { type: 'inhala', label: 'Inhala', seconds: 5.5 },
                        { type: 'exhala', label: 'Exhala', seconds: 5.5 },
                    ],
                },
            };

            return {
                techniques: [
                    { id: '478',      label: '4-7-8' },
                    { id: 'box',      label: 'Box Breathing' },
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
                    const labels = { inhala: 'INHALA', manten: 'MANTÉN', exhala: 'EXHALA' };
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

                    if (p.type === 'inhala') {
                        this.breathScale = 1.0;
                    } else if (p.type === 'exhala') {
                        this.breathScale = 0.6;
                    }

                    if (this.soundEnabled) {
                        this.playTone(p.type, p.seconds);
                    }
                },

                tick() {
                    const totalSessionDuration = this.totalCycles * this.currentTechnique.cycleDuration;
                    this.interval = setInterval(() => {
                        this.elapsed += 1;
                        this.phaseCountdown = Math.max(0, this.phaseCountdown - 1);

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
                        if (this.currentCycle >= this.totalCycles) {
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

                        const freqs = { inhala: 174, manten: 285, exhala: 136 };
                        this.oscillator.type = 'sine';
                        this.oscillator.frequency.setValueAtTime(freqs[type] || 174, ctx.currentTime);

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
                        const notes = [523.25, 659.25, 783.99]; // C5, E5, G5

                        notes.forEach((freq, i) => {
                            const osc  = ctx.createOscillator();
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
