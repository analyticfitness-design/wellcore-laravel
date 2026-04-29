<div
    x-data="{
        showPassword: false,
        init() {
            Livewire.on('login-success', (params) => {
                // Livewire 3 passes named params as first element of array
                const data = Array.isArray(params) ? params[0] : params;
                const token = data?.token || params?.token;
                const userType = data?.userType || params?.userType;
                const redirectUrl = data?.redirectUrl || params?.redirectUrl;

                if (token) {
                    localStorage.setItem('wc_token', token);
                    localStorage.setItem('wc_user_type', userType || '');
                }

                setTimeout(() => {
                    window.location.href = redirectUrl || '/client';
                }, 600);
            });
        }
    }"
    class="flex min-h-screen items-center justify-center bg-wc-bg px-4 py-12"
>
    <div class="w-full max-w-md">
        <div class="rounded-2xl border border-wc-border bg-wc-bg-tertiary p-8 shadow-xl sm:p-10">

            {{-- Logo --}}
            <div class="mb-8 text-center">
                <img src="/images/logo-dark.png" class="h-10 mx-auto dark:hidden" alt="WellCore">
                <img src="/images/logo-light.png" class="hidden h-10 mx-auto dark:block" alt="WellCore">
            </div>

            {{-- Heading --}}
            <div class="mb-8 text-center">
                <h2 class="text-2xl font-bold text-wc-text">Iniciar Sesion</h2>
                <p class="mt-1 text-sm text-wc-text-secondary">Ingresa tus credenciales para acceder</p>
            </div>

            {{-- Error Message (Livewire validation / login errors) --}}
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
                            class="block w-full rounded-lg border border-wc-border bg-wc-bg-secondary py-3 pl-11 pr-4 text-sm text-wc-text placeholder-wc-text-tertiary transition-colors focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent"
                        >
                    </div>
                    @error('identity')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Password --}}
                <div>
                    <label for="password" class="mb-1.5 block text-sm font-medium text-wc-text-secondary">
                        Contrasena
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
                            placeholder="Tu contrasena"
                            class="block w-full rounded-lg border border-wc-border bg-wc-bg-secondary py-3 pl-11 pr-12 text-sm text-wc-text placeholder-wc-text-tertiary transition-colors focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent"
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
                        <span class="text-sm text-wc-text-secondary">Recuerdame</span>
                    </label>
                    <a href="{{ route('password.request') }}" class="text-sm text-wc-accent hover:underline" wire:navigate>
                        Olvidaste tu contrasena?
                    </a>
                </div>

                {{-- Submit Button --}}
                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    @class([
                        'flex w-full items-center justify-center gap-2 rounded-full px-4 py-3 font-semibold text-white transition-all',
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
                        <span wire:loading.remove wire:target="login">Iniciar Sesion</span>
                        <span wire:loading wire:target="login">Verificando...</span>
                    @endif
                </button>
            </form>

            {{-- Footer note --}}
            <p class="mt-8 text-center text-xs text-wc-text-tertiary">
                Problemas para ingresar?
                <a href="mailto:info@wellcorefitness.com" class="font-medium text-wc-accent hover:underline">Contactanos</a>
            </p>
        </div>
    </div>
</div>
