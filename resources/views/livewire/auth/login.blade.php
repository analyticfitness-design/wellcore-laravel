<div
    x-data="{
        showPassword: false,
        init() {
            Livewire.on('login-success', (data) => {
                // Store token in localStorage for vanilla PHP app compatibility
                localStorage.setItem('wc_token', data.token);
                localStorage.setItem('wc_user_type', data.userType);

                // Small delay so the user sees the success state
                setTimeout(() => {
                    window.location.href = data.redirectUrl;
                }, 600);
            });
        }
    }"
    class="flex min-h-[calc(100vh-8rem)] items-center justify-center px-4 py-8"
>
    <div class="w-full max-w-5xl overflow-hidden rounded-2xl border border-wc-border bg-wc-bg-tertiary shadow-xl lg:grid lg:grid-cols-2">

        {{-- Left Panel: Branding --}}
        <div class="hidden items-center justify-center bg-gradient-to-br from-neutral-900 via-neutral-800 to-neutral-900 p-10 lg:flex">
            <div class="max-w-sm text-center">
                {{-- WELLCORE Wordmark --}}
                <h1 class="font-display text-6xl tracking-wider text-white">WELLCORE</h1>
                <div class="mt-2 flex items-center justify-center gap-2">
                    <span class="h-px w-8 bg-red-600"></span>
                    <span class="text-xs font-semibold uppercase tracking-[0.25em] text-red-500">Fitness Platform</span>
                    <span class="h-px w-8 bg-red-600"></span>
                </div>

                <p class="mt-6 text-sm leading-relaxed text-neutral-400">
                    Tu plataforma integral de fitness. Entrena, mide tu progreso y alcanza tus metas con el acompañamiento de coaches profesionales.
                </p>

                {{-- Stats --}}
                <div class="mt-10 grid grid-cols-3 gap-4">
                    <div class="rounded-xl border border-white/10 bg-white/5 p-4">
                        <div class="font-display text-2xl text-white">200+</div>
                        <div class="mt-1 text-xs text-neutral-500">Clientes</div>
                    </div>
                    <div class="rounded-xl border border-white/10 bg-white/5 p-4">
                        <div class="font-display text-2xl text-white">3</div>
                        <div class="mt-1 text-xs text-neutral-500">Planes</div>
                    </div>
                    <div class="rounded-xl border border-white/10 bg-white/5 p-4">
                        <div class="font-display text-2xl text-white">24/7</div>
                        <div class="mt-1 text-xs text-neutral-500">Acceso</div>
                    </div>
                </div>

                <p class="mt-8 text-xs text-neutral-600">&copy; {{ date('Y') }} WellCore Fitness</p>
            </div>
        </div>

        {{-- Right Panel: Login Form --}}
        <div class="flex items-center justify-center p-8 sm:p-10">
            <div class="w-full max-w-sm">
                {{-- Mobile logo (visible on small screens only) --}}
                <div class="mb-8 text-center lg:hidden">
                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-xl bg-wc-accent">
                        <span class="font-display text-2xl leading-none text-white">W</span>
                    </div>
                    <h1 class="mt-3 font-display text-3xl tracking-wider text-wc-text">WELLCORE</h1>
                </div>

                {{-- Heading --}}
                <div class="mb-8">
                    <h2 class="text-2xl font-bold text-wc-text">Iniciar Sesión</h2>
                    <p class="mt-1 text-sm text-wc-text-secondary">Ingresa tus credenciales para acceder</p>
                </div>

                {{-- Error Message --}}
                @if ($errorMessage)
                    <div class="mb-6 flex items-start gap-3 rounded-xl border border-red-500/30 bg-red-500/10 p-4">
                        <svg class="mt-0.5 h-5 w-5 flex-shrink-0 text-red-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                        </svg>
                        <p class="text-sm text-red-400">{{ $errorMessage }}</p>
                    </div>
                @endif

                {{-- Login Form --}}
                <form wire:submit="login" class="space-y-5">
                    {{-- Identity (Email or Username) --}}
                    <div>
                        <label for="identity" class="mb-1.5 block text-sm font-medium text-wc-text-secondary">
                            Email o nombre de usuario
                        </label>
                        <div class="relative">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
                                <svg class="h-5 w-5 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                                </svg>
                            </div>
                            <input
                                wire:model="identity"
                                type="text"
                                id="identity"
                                autocomplete="username"
                                placeholder="tu@email.com o tu_usuario"
                                class="block w-full rounded-xl border border-wc-border bg-wc-bg-secondary py-3 pl-11 pr-4 text-sm text-wc-text placeholder-wc-text-tertiary transition-colors focus:border-wc-accent focus:outline-none focus:ring-2 focus:ring-wc-accent/20"
                            >
                        </div>
                        @error('identity')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Password --}}
                    <div>
                        <label for="password" class="mb-1.5 block text-sm font-medium text-wc-text-secondary">
                            Contraseña
                        </label>
                        <div class="relative">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
                                <svg class="h-5 w-5 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                                </svg>
                            </div>
                            <input
                                wire:model="password"
                                :type="showPassword ? 'text' : 'password'"
                                id="password"
                                autocomplete="current-password"
                                placeholder="Tu contraseña"
                                class="block w-full rounded-xl border border-wc-border bg-wc-bg-secondary py-3 pl-11 pr-12 text-sm text-wc-text placeholder-wc-text-tertiary transition-colors focus:border-wc-accent focus:outline-none focus:ring-2 focus:ring-wc-accent/20"
                            >
                            {{-- Show/Hide password toggle --}}
                            <button
                                type="button"
                                x-on:click="showPassword = !showPassword"
                                class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-wc-text-tertiary hover:text-wc-text"
                            >
                                <template x-if="!showPassword">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                    </svg>
                                </template>
                                <template x-if="showPassword">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                                    </svg>
                                </template>
                            </button>
                        </div>
                        @error('password')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Remember me + Forgot password --}}
                    <div class="flex items-center justify-between">
                        <label class="flex cursor-pointer items-center gap-2">
                            <input
                                wire:model="rememberMe"
                                type="checkbox"
                                class="h-4 w-4 rounded border-wc-border bg-wc-bg-secondary text-wc-accent focus:ring-wc-accent/30"
                            >
                            <span class="text-sm text-wc-text-secondary">Recuérdame</span>
                        </label>
                        <a href="{{ route('password.request') }}" class="text-sm font-medium text-wc-accent hover:text-wc-accent-hover" wire:navigate>
                            ¿Olvidaste tu contraseña?
                        </a>
                    </div>

                    {{-- Submit Button --}}
                    <button
                        type="submit"
                        wire:loading.attr="disabled"
                        @class([
                            'flex w-full items-center justify-center gap-2 rounded-xl px-4 py-3 text-sm font-semibold text-white transition-all',
                            'bg-green-600 hover:bg-green-600' => $loginSuccess,
                            'bg-wc-accent hover:bg-wc-accent-hover active:scale-[0.98]' => ! $loginSuccess,
                            'disabled:cursor-not-allowed disabled:opacity-60',
                        ])
                    >
                        @if ($loginSuccess)
                            {{-- Success state --}}
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                            </svg>
                            <span>Redirigiendo...</span>
                        @else
                            {{-- Loading spinner --}}
                            <svg wire:loading wire:target="login" class="h-5 w-5 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span wire:loading.remove wire:target="login">Iniciar Sesión</span>
                            <span wire:loading wire:target="login">Verificando...</span>
                        @endif
                    </button>
                </form>

                {{-- Divider --}}
                <div class="my-6 flex items-center gap-3">
                    <span class="h-px flex-1 bg-wc-border"></span>
                    <span class="text-xs text-wc-text-tertiary">o continúa con</span>
                    <span class="h-px flex-1 bg-wc-border"></span>
                </div>

                {{-- Google OAuth (placeholder) --}}
                <button
                    type="button"
                    disabled
                    class="flex w-full items-center justify-center gap-3 rounded-xl border border-wc-border bg-wc-bg-secondary px-4 py-3 text-sm font-medium text-wc-text-secondary transition-colors hover:bg-wc-bg disabled:cursor-not-allowed disabled:opacity-50"
                >
                    <svg class="h-5 w-5" viewBox="0 0 24 24">
                        <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 0 1-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z" fill="#4285F4"/>
                        <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                        <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                        <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                    </svg>
                    <span>Google (Próximamente)</span>
                </button>

                {{-- Footer note --}}
                <p class="mt-8 text-center text-xs text-wc-text-tertiary">
                    ¿Problemas para ingresar?
                    <a href="mailto:info@wellcorefitness.com" class="font-medium text-wc-accent hover:text-wc-accent-hover">Contáctanos</a>
                </p>
            </div>
        </div>
    </div>
</div>
