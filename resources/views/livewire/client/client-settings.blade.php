<div
    x-data="{
        tab: 'perfil',
        profileSaved: @entangle('profileSaved'),
        passwordSaved: @entangle('passwordSaved'),
        notifications: JSON.parse(localStorage.getItem('wc_notifications') || JSON.stringify({
            checkin: true,
            coach: true,
            achievements: true,
            weekly: true
        })),
        saveNotifications() {
            localStorage.setItem('wc_notifications', JSON.stringify(this.notifications));
        },
        init() {
            this.$watch('notifications', () => this.saveNotifications(), { deep: true });

            Livewire.on('profile-updated', () => {
                this.profileSaved = true;
                setTimeout(() => { this.profileSaved = false; }, 3000);
            });
            Livewire.on('password-changed', () => {
                this.passwordSaved = true;
                setTimeout(() => { this.passwordSaved = false; }, 3000);
            });
        }
    }"
>

    {{-- Success Toasts --}}
    <div
        x-show="profileSaved"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-2"
        class="fixed bottom-6 right-6 z-50 flex items-center gap-3 rounded-xl border border-green-500/30 bg-green-500/10 px-5 py-3 shadow-lg backdrop-blur-sm"
        x-cloak
    >
        <svg class="h-5 w-5 text-green-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
        </svg>
        <span class="text-sm font-medium text-green-400">Perfil actualizado correctamente</span>
    </div>

    <div
        x-show="passwordSaved"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-2"
        class="fixed bottom-6 right-6 z-50 flex items-center gap-3 rounded-xl border border-green-500/30 bg-green-500/10 px-5 py-3 shadow-lg backdrop-blur-sm"
        x-cloak
    >
        <svg class="h-5 w-5 text-green-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
        </svg>
        <span class="text-sm font-medium text-green-400">Contrasena actualizada correctamente</span>
    </div>

    {{-- Header --}}
    <div class="mb-8">
        <h1 class="font-display text-3xl tracking-wide text-wc-text">CONFIGURACION</h1>
        <p class="mt-1 text-sm text-wc-text-secondary">Gestiona tu cuenta, preferencias y seguridad</p>
    </div>

    {{-- Tab Buttons --}}
    <div class="mb-6 flex flex-wrap gap-2">
        <button
            @click="tab = 'perfil'"
            :class="tab === 'perfil'
                ? 'bg-wc-accent text-white'
                : 'bg-wc-bg-tertiary text-wc-text-secondary hover:text-wc-text'"
            class="flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-medium transition-colors"
        >
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
            </svg>
            Perfil
        </button>
        <button
            @click="tab = 'notificaciones'"
            :class="tab === 'notificaciones'
                ? 'bg-wc-accent text-white'
                : 'bg-wc-bg-tertiary text-wc-text-secondary hover:text-wc-text'"
            class="flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-medium transition-colors"
        >
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
            </svg>
            Notificaciones
        </button>
        <button
            @click="tab = 'apariencia'"
            :class="tab === 'apariencia'
                ? 'bg-wc-accent text-white'
                : 'bg-wc-bg-tertiary text-wc-text-secondary hover:text-wc-text'"
            class="flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-medium transition-colors"
        >
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.098 19.902a3.75 3.75 0 0 0 5.304 0l6.401-6.402M6.75 21A3.75 3.75 0 0 1 3 17.25V4.125C3 3.504 3.504 3 4.125 3h5.25c.621 0 1.125.504 1.125 1.125v4.072M6.75 21a3.75 3.75 0 0 0 3.75-3.75V8.197M6.75 21h13.125c.621 0 1.125-.504 1.125-1.125v-5.25c0-.621-.504-1.125-1.125-1.125h-4.072M10.5 8.197l2.88-2.88c.438-.439 1.15-.439 1.59 0l3.712 3.713c.44.44.44 1.152 0 1.59l-2.879 2.88M6.75 17.25h.008v.008H6.75v-.008Z" />
            </svg>
            Apariencia
        </button>
        <button
            @click="tab = 'seguridad'"
            :class="tab === 'seguridad'
                ? 'bg-wc-accent text-white'
                : 'bg-wc-bg-tertiary text-wc-text-secondary hover:text-wc-text'"
            class="flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-medium transition-colors"
        >
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
            </svg>
            Seguridad
        </button>
    </div>

    {{-- ─── TAB: PERFIL ─────────────────────────────────────────────────── --}}
    <div x-show="tab === 'perfil'" x-cloak>
        <div class="max-w-xl rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
            <div class="mb-5 flex items-center gap-3">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-wc-accent/10">
                    <svg class="h-5 w-5 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                    </svg>
                </div>
                <div>
                    <h2 class="font-display text-lg tracking-wide text-wc-text">DATOS DE PERFIL</h2>
                    <p class="text-xs text-wc-text-tertiary">Actualiza tu informacion personal</p>
                </div>
            </div>

            <form wire:submit.prevent="updateProfile" class="space-y-4">
                {{-- Name --}}
                <div>
                    <label for="settings-name" class="block text-xs font-medium text-wc-text-tertiary mb-1">Nombre completo</label>
                    <input
                        wire:model="name"
                        type="text"
                        id="settings-name"
                        class="w-full rounded-lg border border-wc-border bg-wc-bg-tertiary py-2.5 px-3 text-sm text-wc-text focus:border-wc-accent focus:outline-none"
                        placeholder="Tu nombre"
                    >
                    @error('name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                {{-- Email --}}
                <div>
                    <label for="settings-email" class="block text-xs font-medium text-wc-text-tertiary mb-1">Correo electronico</label>
                    <input
                        wire:model="email"
                        type="email"
                        id="settings-email"
                        class="w-full rounded-lg border border-wc-border bg-wc-bg-tertiary py-2.5 px-3 text-sm text-wc-text focus:border-wc-accent focus:outline-none"
                        placeholder="tu@email.com"
                    >
                    @error('email') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                {{-- Phone --}}
                <div>
                    <label for="settings-phone" class="block text-xs font-medium text-wc-text-tertiary mb-1">Telefono</label>
                    <input
                        wire:model="phone"
                        type="tel"
                        id="settings-phone"
                        class="w-full rounded-lg border border-wc-border bg-wc-bg-tertiary py-2.5 px-3 text-sm text-wc-text focus:border-wc-accent focus:outline-none"
                        placeholder="+52 123 456 7890"
                    >
                    @error('phone') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <div class="pt-2">
                    <button
                        type="submit"
                        wire:loading.attr="disabled"
                        class="flex items-center gap-2 rounded-lg bg-wc-accent px-6 py-2.5 text-sm font-semibold text-white transition-all hover:opacity-90 active:scale-[0.98] disabled:opacity-60"
                    >
                        <svg wire:loading wire:target="updateProfile" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span wire:loading.remove wire:target="updateProfile">Guardar cambios</span>
                        <span wire:loading wire:target="updateProfile">Guardando...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ─── TAB: NOTIFICACIONES ─────────────────────────────────────────── --}}
    <div x-show="tab === 'notificaciones'" x-cloak>
        <div class="max-w-xl rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
            <div class="mb-5 flex items-center gap-3">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-wc-accent/10">
                    <svg class="h-5 w-5 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                    </svg>
                </div>
                <div>
                    <h2 class="font-display text-lg tracking-wide text-wc-text">PREFERENCIAS DE NOTIFICACION</h2>
                    <p class="text-xs text-wc-text-tertiary">Configuracion guardada localmente en tu dispositivo</p>
                </div>
            </div>

            <div class="space-y-4">
                {{-- Check-in reminders --}}
                <div class="flex items-center justify-between rounded-lg border border-wc-border bg-wc-bg p-4">
                    <div>
                        <p class="text-sm font-medium text-wc-text">Recordatorios de check-in</p>
                        <p class="mt-0.5 text-xs text-wc-text-tertiary">Recibe avisos para no olvidar tu check-in semanal</p>
                    </div>
                    <button
                        type="button"
                        @click="notifications.checkin = !notifications.checkin"
                        :class="notifications.checkin ? 'bg-wc-accent' : 'bg-wc-bg-secondary'"
                        class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 focus:outline-none"
                        role="switch"
                        :aria-checked="notifications.checkin.toString()"
                    >
                        <span
                            :class="notifications.checkin ? 'translate-x-5' : 'translate-x-0'"
                            class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200"
                        ></span>
                    </button>
                </div>

                {{-- Coach messages --}}
                <div class="flex items-center justify-between rounded-lg border border-wc-border bg-wc-bg p-4">
                    <div>
                        <p class="text-sm font-medium text-wc-text">Mensajes del coach</p>
                        <p class="mt-0.5 text-xs text-wc-text-tertiary">Notificaciones cuando tu coach te envia feedback</p>
                    </div>
                    <button
                        type="button"
                        @click="notifications.coach = !notifications.coach"
                        :class="notifications.coach ? 'bg-wc-accent' : 'bg-wc-bg-secondary'"
                        class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 focus:outline-none"
                        role="switch"
                        :aria-checked="notifications.coach.toString()"
                    >
                        <span
                            :class="notifications.coach ? 'translate-x-5' : 'translate-x-0'"
                            class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200"
                        ></span>
                    </button>
                </div>

                {{-- Achievement alerts --}}
                <div class="flex items-center justify-between rounded-lg border border-wc-border bg-wc-bg p-4">
                    <div>
                        <p class="text-sm font-medium text-wc-text">Alertas de logros</p>
                        <p class="mt-0.5 text-xs text-wc-text-tertiary">Celebra cuando alcances nuevos records personales</p>
                    </div>
                    <button
                        type="button"
                        @click="notifications.achievements = !notifications.achievements"
                        :class="notifications.achievements ? 'bg-wc-accent' : 'bg-wc-bg-secondary'"
                        class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 focus:outline-none"
                        role="switch"
                        :aria-checked="notifications.achievements.toString()"
                    >
                        <span
                            :class="notifications.achievements ? 'translate-x-5' : 'translate-x-0'"
                            class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200"
                        ></span>
                    </button>
                </div>

                {{-- Weekly summary --}}
                <div class="flex items-center justify-between rounded-lg border border-wc-border bg-wc-bg p-4">
                    <div>
                        <p class="text-sm font-medium text-wc-text">Resumen semanal</p>
                        <p class="mt-0.5 text-xs text-wc-text-tertiary">Recibe un resumen de tu progreso cada semana</p>
                    </div>
                    <button
                        type="button"
                        @click="notifications.weekly = !notifications.weekly"
                        :class="notifications.weekly ? 'bg-wc-accent' : 'bg-wc-bg-secondary'"
                        class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 focus:outline-none"
                        role="switch"
                        :aria-checked="notifications.weekly.toString()"
                    >
                        <span
                            :class="notifications.weekly ? 'translate-x-5' : 'translate-x-0'"
                            class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200"
                        ></span>
                    </button>
                </div>
            </div>

                {{-- Training completion sound --}}
                <div class="flex items-center justify-between rounded-lg border border-wc-border bg-wc-bg p-4"
                     x-data="{ soundEnabled: localStorage.getItem('wc_sound_enabled') !== 'false' }"
                     x-init="$watch('soundEnabled', val => localStorage.setItem('wc_sound_enabled', val ? 'true' : 'false'))"
                >
                    <div>
                        <p class="text-sm font-medium text-wc-text">Sonido al completar entrenamiento</p>
                        <p class="mt-0.5 text-xs text-wc-text-tertiary">Reproduce un sonido sutil cuando completas tu entrenamiento</p>
                    </div>
                    <button
                        type="button"
                        @click="soundEnabled = !soundEnabled"
                        :class="soundEnabled ? 'bg-wc-accent' : 'bg-wc-bg-secondary'"
                        class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 focus:outline-none"
                        role="switch"
                        :aria-checked="soundEnabled.toString()"
                    >
                        <span
                            :class="soundEnabled ? 'translate-x-5' : 'translate-x-0'"
                            class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200"
                        ></span>
                    </button>
                </div>
            </div>

            <p class="mt-4 text-xs text-wc-text-tertiary">Las preferencias se guardan automaticamente en este dispositivo.</p>
        </div>
    </div>

    {{-- ─── TAB: APARIENCIA ─────────────────────────────────────────────── --}}
    <div x-show="tab === 'apariencia'" x-cloak>
        <div class="max-w-xl rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
            <div class="mb-5 flex items-center gap-3">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-wc-accent/10">
                    <svg class="h-5 w-5 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.098 19.902a3.75 3.75 0 0 0 5.304 0l6.401-6.402M6.75 21A3.75 3.75 0 0 1 3 17.25V4.125C3 3.504 3.504 3 4.125 3h5.25c.621 0 1.125.504 1.125 1.125v4.072M6.75 21a3.75 3.75 0 0 0 3.75-3.75V8.197M6.75 21h13.125c.621 0 1.125-.504 1.125-1.125v-5.25c0-.621-.504-1.125-1.125-1.125h-4.072M10.5 8.197l2.88-2.88c.438-.439 1.15-.439 1.59 0l3.712 3.713c.44.44.44 1.152 0 1.59l-2.879 2.88M6.75 17.25h.008v.008H6.75v-.008Z" />
                    </svg>
                </div>
                <div>
                    <h2 class="font-display text-lg tracking-wide text-wc-text">APARIENCIA</h2>
                    <p class="text-xs text-wc-text-tertiary">Personaliza el aspecto visual de la plataforma</p>
                </div>
            </div>

            {{-- Dark / Light mode toggle --}}
            <div class="rounded-lg border border-wc-border bg-wc-bg p-4">
                <p class="mb-4 text-sm font-medium text-wc-text">Modo de visualizacion</p>

                <div class="grid grid-cols-2 gap-3">
                    {{-- Light mode card --}}
                    <button
                        type="button"
                        @click="$root.closest('[x-data]').__x.$data.darkMode = false; darkMode = false"
                        :class="!darkMode ? 'border-wc-accent ring-2 ring-wc-accent/30' : 'border-wc-border hover:border-wc-border/80'"
                        class="relative flex flex-col items-center gap-2 rounded-xl border bg-white p-4 transition-all"
                        x-data="{ get darkMode() { return document.documentElement.classList.contains('dark') } }"
                    >
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-amber-100">
                            <svg class="h-5 w-5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" />
                            </svg>
                        </div>
                        <span class="text-xs font-medium text-gray-700">Modo Claro</span>
                        <div x-show="!darkMode" class="absolute top-2 right-2">
                            <div class="flex h-4 w-4 items-center justify-center rounded-full bg-wc-accent">
                                <svg class="h-3 w-3 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                </svg>
                            </div>
                        </div>
                    </button>

                    {{-- Dark mode card --}}
                    <button
                        type="button"
                        @click="$root.closest('[x-data]').__x.$data.darkMode = true; darkMode = true"
                        :class="darkMode ? 'border-wc-accent ring-2 ring-wc-accent/30' : 'border-wc-border hover:border-wc-border/80'"
                        class="relative flex flex-col items-center gap-2 rounded-xl border bg-gray-900 p-4 transition-all"
                        x-data="{ get darkMode() { return document.documentElement.classList.contains('dark') } }"
                    >
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-indigo-500/20">
                            <svg class="h-5 w-5 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z" />
                            </svg>
                        </div>
                        <span class="text-xs font-medium text-gray-300">Modo Oscuro</span>
                        <div x-show="darkMode" class="absolute top-2 right-2">
                            <div class="flex h-4 w-4 items-center justify-center rounded-full bg-wc-accent">
                                <svg class="h-3 w-3 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                </svg>
                            </div>
                        </div>
                    </button>
                </div>

                {{-- Quick toggle button --}}
                <div class="mt-4 flex items-center justify-between rounded-lg border border-wc-border bg-wc-bg-secondary p-3">
                    <div class="flex items-center gap-2">
                        <template x-if="!document.documentElement.classList.contains('dark')">
                            <svg class="h-4 w-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" />
                            </svg>
                        </template>
                        <template x-if="document.documentElement.classList.contains('dark')">
                            <svg class="h-4 w-4 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z" />
                            </svg>
                        </template>
                        <span class="text-sm text-wc-text-secondary">Cambio rapido de tema</span>
                    </div>
                    <button
                        type="button"
                        x-on:click="$dispatch('toggle-dark-mode')"
                        @click="
                            const html = document.documentElement;
                            const isDark = html.classList.contains('dark');
                            if (isDark) {
                                html.classList.remove('dark');
                                localStorage.setItem('darkMode', 'false');
                            } else {
                                html.classList.add('dark');
                                localStorage.setItem('darkMode', 'true');
                            }
                        "
                        class="flex h-8 w-8 items-center justify-center rounded-lg border border-wc-border bg-wc-bg-tertiary text-wc-text-secondary hover:text-wc-text transition-colors"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ─── TAB: SEGURIDAD ──────────────────────────────────────────────── --}}
    <div x-show="tab === 'seguridad'" x-cloak>
        <div class="max-w-xl rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
            <div class="mb-5 flex items-center gap-3">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-wc-accent/10">
                    <svg class="h-5 w-5 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                    </svg>
                </div>
                <div>
                    <h2 class="font-display text-lg tracking-wide text-wc-text">CAMBIAR CONTRASENA</h2>
                    <p class="text-xs text-wc-text-tertiary">Actualiza tu contrasena de acceso</p>
                </div>
            </div>

            {{-- Error message --}}
            @if($passwordError)
                <div class="mb-4 flex items-center gap-2 rounded-lg border border-red-500/30 bg-red-500/10 px-4 py-3">
                    <svg class="h-4 w-4 shrink-0 text-red-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                    </svg>
                    <p class="text-sm text-red-400">{{ $passwordError }}</p>
                </div>
            @endif

            <form wire:submit.prevent="changePassword" class="space-y-4">
                {{-- Current password --}}
                <div>
                    <label for="currentPassword" class="block text-xs font-medium text-wc-text-tertiary mb-1">Contrasena actual</label>
                    <input
                        wire:model="currentPassword"
                        type="password"
                        id="currentPassword"
                        autocomplete="current-password"
                        class="w-full rounded-lg border border-wc-border bg-wc-bg-tertiary py-2.5 px-3 text-sm text-wc-text focus:border-wc-accent focus:outline-none"
                        placeholder="••••••••"
                    >
                </div>

                {{-- New password --}}
                <div>
                    <label for="newPassword" class="block text-xs font-medium text-wc-text-tertiary mb-1">Nueva contrasena</label>
                    <input
                        wire:model="newPassword"
                        type="password"
                        id="newPassword"
                        autocomplete="new-password"
                        class="w-full rounded-lg border border-wc-border bg-wc-bg-tertiary py-2.5 px-3 text-sm text-wc-text focus:border-wc-accent focus:outline-none"
                        placeholder="Minimo 8 caracteres"
                    >
                </div>

                {{-- Confirm new password --}}
                <div>
                    <label for="confirmPassword" class="block text-xs font-medium text-wc-text-tertiary mb-1">Confirmar nueva contrasena</label>
                    <input
                        wire:model="confirmPassword"
                        type="password"
                        id="confirmPassword"
                        autocomplete="new-password"
                        class="w-full rounded-lg border border-wc-border bg-wc-bg-tertiary py-2.5 px-3 text-sm text-wc-text focus:border-wc-accent focus:outline-none"
                        placeholder="Repite la nueva contrasena"
                    >
                </div>

                <div class="pt-2">
                    <button
                        type="submit"
                        wire:loading.attr="disabled"
                        class="flex items-center gap-2 rounded-lg bg-wc-accent px-6 py-2.5 text-sm font-semibold text-white transition-all hover:opacity-90 active:scale-[0.98] disabled:opacity-60"
                    >
                        <svg wire:loading wire:target="changePassword" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span wire:loading.remove wire:target="changePassword">Actualizar contrasena</span>
                        <span wire:loading wire:target="changePassword">Actualizando...</span>
                    </button>
                </div>
            </form>

            {{-- Security info --}}
            <div class="mt-6 rounded-lg border border-wc-border bg-wc-bg p-4">
                <p class="mb-2 text-xs font-medium text-wc-text-tertiary uppercase tracking-wide">Recomendaciones de seguridad</p>
                <ul class="space-y-1.5">
                    <li class="flex items-center gap-2 text-xs text-wc-text-secondary">
                        <svg class="h-3.5 w-3.5 text-wc-accent shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                        </svg>
                        Usa al menos 8 caracteres
                    </li>
                    <li class="flex items-center gap-2 text-xs text-wc-text-secondary">
                        <svg class="h-3.5 w-3.5 text-wc-accent shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                        </svg>
                        Combina letras, numeros y simbolos
                    </li>
                    <li class="flex items-center gap-2 text-xs text-wc-text-secondary">
                        <svg class="h-3.5 w-3.5 text-wc-accent shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                        </svg>
                        No uses la misma contrasena en otros sitios
                    </li>
                </ul>
            </div>
        </div>
    </div>

</div>
