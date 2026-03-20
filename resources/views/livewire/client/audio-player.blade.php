<div class="space-y-6" x-data="audioPlayer()" x-on:beforeunload.window="cleanup()">
    <div>
        <h1 class="font-display text-3xl tracking-wide text-wc-text">AUDIO COACHING</h1>
        <p class="mt-1 text-sm text-wc-text-secondary">Sesiones de coaching en audio: motivacion, tecnica y mindset para tu entrenamiento.</p>
    </div>

    {{-- Category tabs --}}
    <div class="flex flex-wrap gap-2">
        <button x-on:click="category = 'all'" :class="category === 'all' ? 'bg-wc-accent text-white' : 'bg-wc-bg-tertiary text-wc-text-secondary hover:text-wc-text'" class="rounded-lg border border-wc-border px-4 py-2 text-sm font-medium transition-colors">
            Todos <span class="ml-1 text-xs opacity-70" x-text="'(' + audios.length + ')'"></span>
        </button>
        <template x-for="c in categories" :key="c.id">
            <button x-on:click="category = c.id" :class="category === c.id ? 'bg-wc-accent text-white' : 'bg-wc-bg-tertiary text-wc-text-secondary hover:text-wc-text'" class="rounded-lg border border-wc-border px-4 py-2 text-sm font-medium transition-colors">
                <span x-text="c.icon + ' ' + c.label"></span>
            </button>
        </template>
    </div>

    {{-- Results count --}}
    <p class="text-xs text-wc-text-tertiary">
        <span x-text="filtered.length"></span> sesion<span x-show="filtered.length !== 1">es</span> disponible<span x-show="filtered.length !== 1">s</span>
    </p>

    {{-- Audio list --}}
    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
        <template x-for="track in filtered" :key="track.id">
            <div class="group cursor-pointer rounded-xl border border-wc-border bg-wc-bg-tertiary p-4 transition-all hover:border-wc-accent/40"
                 :class="currentTrack && currentTrack.id === track.id ? 'border-wc-accent/60 bg-wc-accent/5' : ''"
                 x-on:click="playTrack(track)">
                <div class="flex items-start gap-3">
                    {{-- Play indicator --}}
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg transition-colors"
                         :class="currentTrack && currentTrack.id === track.id && isPlaying ? 'bg-wc-accent text-white' : 'bg-wc-bg-secondary text-wc-text-tertiary group-hover:bg-wc-accent/20 group-hover:text-wc-accent'">
                        <svg x-show="!(currentTrack && currentTrack.id === track.id && isPlaying)" class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M8 5.14v14l11-7-11-7z"/>
                        </svg>
                        <svg x-show="currentTrack && currentTrack.id === track.id && isPlaying" class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24" x-cloak>
                            <path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/>
                        </svg>
                    </div>

                    <div class="flex-1 min-w-0">
                        <h3 class="text-sm font-semibold text-wc-text" x-text="track.title"></h3>
                        <p class="mt-0.5 text-xs text-wc-text-tertiary line-clamp-2" x-text="track.description"></p>
                        <div class="mt-2 flex items-center gap-3">
                            <span class="rounded-full px-2 py-0.5 text-[10px] font-medium"
                                  :class="{
                                      'bg-orange-500/10 text-orange-400': track.category === 'motivacion',
                                      'bg-blue-500/10 text-blue-400': track.category === 'tecnica',
                                      'bg-purple-500/10 text-purple-400': track.category === 'mindset'
                                  }"
                                  x-text="categories.find(c => c.id === track.category)?.label || ''"></span>
                            <span class="flex items-center gap-1 text-[10px] text-wc-text-tertiary">
                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                                <span x-text="formatTime(track.duration)"></span>
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Mini progress bar for current track --}}
                <div x-show="currentTrack && currentTrack.id === track.id" class="mt-3" x-cloak>
                    <div class="h-1 w-full overflow-hidden rounded-full bg-wc-bg-secondary">
                        <div class="h-full rounded-full bg-wc-accent transition-all duration-200" :style="{ width: progress + '%' }"></div>
                    </div>
                    <div class="mt-1 flex justify-between text-[10px] text-wc-text-tertiary">
                        <span x-text="formatTime(currentTime)"></span>
                        <span x-text="formatTime(track.duration)"></span>
                    </div>
                </div>
            </div>
        </template>
    </div>

    {{-- Persistent Player Bar --}}
    <div x-show="currentTrack" x-cloak
         class="fixed inset-x-0 bottom-0 z-50 border-t border-wc-border bg-wc-bg-secondary/95 backdrop-blur-xl lg:bottom-0"
         :class="window.innerWidth < 1024 ? 'bottom-[56px]' : 'bottom-0'">
        <div class="mx-auto max-w-7xl px-4 py-3 sm:px-6">
            {{-- Progress bar --}}
            <div class="group mb-2 cursor-pointer" x-on:click="seek($event)">
                <div class="h-1.5 w-full overflow-hidden rounded-full bg-wc-border group-hover:h-2 transition-all">
                    <div class="h-full rounded-full bg-wc-accent transition-all duration-200" :style="{ width: progress + '%' }"></div>
                </div>
            </div>

            <div class="flex items-center justify-between gap-4">
                {{-- Track info --}}
                <div class="flex items-center gap-3 min-w-0 flex-1">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-wc-accent/20">
                        <span class="text-lg" x-text="currentTrack?.icon || ''"></span>
                    </div>
                    <div class="min-w-0">
                        <p class="truncate text-sm font-semibold text-wc-text" x-text="currentTrack?.title || ''"></p>
                        <p class="text-xs text-wc-text-tertiary">
                            <span x-text="formatTime(currentTime)"></span> / <span x-text="formatTime(currentTrack?.duration || 0)"></span>
                        </p>
                    </div>
                </div>

                {{-- Controls --}}
                <div class="flex items-center gap-2">
                    {{-- Previous --}}
                    <button x-on:click="prevTrack()" class="flex h-8 w-8 items-center justify-center rounded-lg text-wc-text-secondary hover:text-wc-text transition-colors">
                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M6 6h2v12H6zm3.5 6 8.5 6V6z"/></svg>
                    </button>
                    {{-- Play/Pause --}}
                    <button x-on:click="togglePlay()" class="flex h-10 w-10 items-center justify-center rounded-full bg-wc-accent text-white hover:bg-wc-accent-hover transition-colors">
                        <svg x-show="!isPlaying" class="h-5 w-5 ml-0.5" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5.14v14l11-7-11-7z"/></svg>
                        <svg x-show="isPlaying" class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24" x-cloak><path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/></svg>
                    </button>
                    {{-- Next --}}
                    <button x-on:click="nextTrack()" class="flex h-8 w-8 items-center justify-center rounded-lg text-wc-text-secondary hover:text-wc-text transition-colors">
                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M6 18l8.5-6L6 6v12zM16 6v12h2V6h-2z"/></svg>
                    </button>
                </div>

                {{-- Volume (desktop) --}}
                <div class="hidden items-center gap-2 sm:flex">
                    <button x-on:click="volume = volume > 0 ? 0 : 0.7; updateVolume()" class="text-wc-text-secondary hover:text-wc-text transition-colors">
                        <svg x-show="volume > 0" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.114 5.636a9 9 0 0 1 0 12.728M16.463 8.288a5.25 5.25 0 0 1 0 7.424M6.75 8.25l4.72-4.72a.75.75 0 0 1 1.28.53v15.88a.75.75 0 0 1-1.28.53l-4.72-4.72H4.51c-.88 0-1.704-.507-1.938-1.354A9.009 9.009 0 0 1 2.25 12c0-.83.112-1.633.322-2.396C2.806 8.756 3.63 8.25 4.51 8.25H6.75Z" /></svg>
                        <svg x-show="volume === 0" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" x-cloak><path stroke-linecap="round" stroke-linejoin="round" d="M17.25 9.75 19.5 12m0 0 2.25 2.25M19.5 12l2.25-2.25M19.5 12l-2.25 2.25m-10.5-6 4.72-4.72a.75.75 0 0 1 1.28.53v15.88a.75.75 0 0 1-1.28.53l-4.72-4.72H4.51c-.88 0-1.704-.507-1.938-1.354A9.009 9.009 0 0 1 2.25 12c0-.83.112-1.633.322-2.396C2.806 8.756 3.63 8.25 4.51 8.25H6.75Z" /></svg>
                    </button>
                    <input type="range" min="0" max="1" step="0.05" x-model="volume" x-on:input="updateVolume()"
                           class="h-1 w-20 cursor-pointer appearance-none rounded-full bg-wc-border accent-wc-accent" />
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function audioPlayer() {
    return {
        category: 'all',
        currentTrack: null,
        isPlaying: false,
        currentTime: 0,
        progress: 0,
        volume: 0.7,
        audioContext: null,
        oscillator: null,
        gainNode: null,
        timer: null,

        categories: [
            { id: 'motivacion', label: 'Motivacion', icon: '🔥' },
            { id: 'tecnica', label: 'Tecnica', icon: '🎯' },
            { id: 'mindset', label: 'Mindset', icon: '🧠' }
        ],

        audios: [
            // Motivacion
            { id: 1, title: 'Poder Interior', description: 'Visualizacion de fuerza y determinacion para despertar tu potencial fisico y mental.', category: 'motivacion', icon: '💪', duration: 180, frequency: 440, waveform: 'sine' },
            { id: 2, title: 'Rompe Limites', description: 'Afirmaciones para superar barreras mentales que frenan tu progreso en el gym.', category: 'motivacion', icon: '⚡', duration: 240, frequency: 528, waveform: 'sine' },
            { id: 3, title: 'Mentalidad de Campeon', description: 'Coaching mental para competidores y atletas que buscan la excelencia.', category: 'motivacion', icon: '🏆', duration: 300, frequency: 396, waveform: 'sine' },
            { id: 4, title: 'Tu Mejor Version', description: 'Reflexion guiada sobre tu progreso personal y metas alcanzadas.', category: 'motivacion', icon: '🌟', duration: 210, frequency: 417, waveform: 'sine' },
            { id: 5, title: 'Accion Imparable', description: 'Energia y motivacion pura para entrenamientos de alta intensidad.', category: 'motivacion', icon: '🚀', duration: 180, frequency: 639, waveform: 'triangle' },
            // Tecnica
            { id: 6, title: 'Activacion de Gluteos', description: 'Tecnica de activacion muscular previa al entrenamiento de pierna y gluteo.', category: 'tecnica', icon: '🎯', duration: 150, frequency: 285, waveform: 'sine' },
            { id: 7, title: 'Respiracion en Press', description: 'Patron respiratorio optimo para movimientos de press y empuje.', category: 'tecnica', icon: '🫁', duration: 180, frequency: 432, waveform: 'sine' },
            { id: 8, title: 'Control del Core', description: 'Bracing y activacion abdominal para estabilidad en compuestos.', category: 'tecnica', icon: '🧱', duration: 210, frequency: 369, waveform: 'sine' },
            { id: 9, title: 'Ritmo de Sentadilla', description: 'Tempo y cadencia optima para maximizar la hipertrofia en sentadilla.', category: 'tecnica', icon: '🏋️', duration: 180, frequency: 396, waveform: 'triangle' },
            { id: 10, title: 'Conexion Mente-Musculo', description: 'Practica de conexion neuromuscular para aislamientos y accesorios.', category: 'tecnica', icon: '🧬', duration: 240, frequency: 528, waveform: 'sine' },
            // Mindset
            { id: 11, title: 'Meditacion Pre-Entreno', description: '5 minutos de meditacion enfocada para preparar mente y cuerpo antes del gym.', category: 'mindset', icon: '🧘', duration: 300, frequency: 174, waveform: 'sine' },
            { id: 12, title: 'Gestion del Esfuerzo', description: 'Aprender a distinguir entre esfuerzo productivo e innecesario durante el entrenamiento.', category: 'mindset', icon: '⚖️', duration: 210, frequency: 285, waveform: 'sine' },
            { id: 13, title: 'Paciencia y Proceso', description: 'Aceptar el proceso del fitness y confiar en la consistencia a largo plazo.', category: 'mindset', icon: '🌱', duration: 240, frequency: 396, waveform: 'sine' },
            { id: 14, title: 'Descanso Consciente', description: 'Meditacion de recuperacion post-entrenamiento para optimizar la restauracion.', category: 'mindset', icon: '😌', duration: 300, frequency: 432, waveform: 'sine' },
            { id: 15, title: 'Visualizacion de Metas', description: 'Sesion de visualizacion para conectar con tu cuerpo y rendimiento ideal.', category: 'mindset', icon: '🎯', duration: 240, frequency: 528, waveform: 'sine' }
        ],

        get filtered() {
            if (this.category === 'all') return this.audios;
            return this.audios.filter(a => a.category === this.category);
        },

        playTrack(track) {
            if (this.currentTrack && this.currentTrack.id === track.id) {
                this.togglePlay();
                return;
            }
            this.stopAudio();
            this.currentTrack = track;
            this.currentTime = 0;
            this.progress = 0;
            this.startAudio();
        },

        togglePlay() {
            if (!this.currentTrack) return;
            if (this.isPlaying) {
                this.pauseAudio();
            } else {
                this.startAudio();
            }
        },

        startAudio() {
            if (!this.currentTrack) return;

            try {
                if (!this.audioContext) {
                    this.audioContext = new (window.AudioContext || window.webkitAudioContext)();
                }

                if (this.audioContext.state === 'suspended') {
                    this.audioContext.resume();
                }

                this.oscillator = this.audioContext.createOscillator();
                this.gainNode = this.audioContext.createGain();

                this.oscillator.type = this.currentTrack.waveform || 'sine';
                this.oscillator.frequency.setValueAtTime(this.currentTrack.frequency, this.audioContext.currentTime);
                this.gainNode.gain.setValueAtTime(this.volume * 0.15, this.audioContext.currentTime);

                this.oscillator.connect(this.gainNode);
                this.gainNode.connect(this.audioContext.destination);
                this.oscillator.start();
            } catch (e) {
                console.warn('Web Audio API not available:', e);
            }

            this.isPlaying = true;
            this.timer = setInterval(() => {
                this.currentTime += 0.1;
                this.progress = (this.currentTime / this.currentTrack.duration) * 100;
                if (this.currentTime >= this.currentTrack.duration) {
                    this.nextTrack();
                }
            }, 100);
        },

        pauseAudio() {
            this.isPlaying = false;
            clearInterval(this.timer);
            try {
                if (this.oscillator) {
                    this.oscillator.stop();
                    this.oscillator.disconnect();
                    this.oscillator = null;
                }
            } catch (e) {}
        },

        stopAudio() {
            this.pauseAudio();
            this.currentTime = 0;
            this.progress = 0;
        },

        nextTrack() {
            if (!this.currentTrack) return;
            const list = this.filtered;
            const idx = list.findIndex(a => a.id === this.currentTrack.id);
            const next = list[(idx + 1) % list.length];
            this.stopAudio();
            this.currentTrack = next;
            this.startAudio();
        },

        prevTrack() {
            if (!this.currentTrack) return;
            if (this.currentTime > 3) {
                this.stopAudio();
                this.currentTrack = { ...this.currentTrack };
                this.startAudio();
                return;
            }
            const list = this.filtered;
            const idx = list.findIndex(a => a.id === this.currentTrack.id);
            const prev = list[(idx - 1 + list.length) % list.length];
            this.stopAudio();
            this.currentTrack = prev;
            this.startAudio();
        },

        seek(event) {
            if (!this.currentTrack) return;
            const bar = event.currentTarget;
            const rect = bar.getBoundingClientRect();
            const pct = Math.max(0, Math.min(1, (event.clientX - rect.left) / rect.width));
            this.currentTime = pct * this.currentTrack.duration;
            this.progress = pct * 100;
        },

        updateVolume() {
            try {
                if (this.gainNode) {
                    this.gainNode.gain.setValueAtTime(this.volume * 0.15, this.audioContext.currentTime);
                }
            } catch (e) {}
        },

        formatTime(seconds) {
            const s = Math.floor(seconds);
            const m = Math.floor(s / 60);
            const sec = s % 60;
            return m + ':' + (sec < 10 ? '0' : '') + sec;
        },

        cleanup() {
            this.stopAudio();
            if (this.audioContext) {
                this.audioContext.close();
            }
        }
    };
}
</script>
