<div class="flex min-h-screen items-center justify-center bg-wc-bg px-4 py-12">
    <div class="w-full max-w-md">
        <div class="rounded-2xl border border-wc-border bg-wc-bg-tertiary p-8 shadow-xl sm:p-10">

            {{-- Logo --}}
            <div class="mb-6 text-center">
                <img src="/images/logo-dark.png" class="h-10 mx-auto dark:hidden" alt="WellCore">
                <img src="/images/logo-light.png" class="hidden h-10 mx-auto dark:block" alt="WellCore">
            </div>

            @if($reset)
                {{-- Success State --}}
                <div class="text-center">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-green-500/10">
                        <svg class="h-7 w-7 text-green-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                        </svg>
                    </div>
                    <h2 class="mt-5 text-2xl font-bold text-wc-text">Contrasena Actualizada</h2>
                    <p class="mt-3 text-sm text-wc-text-secondary">Tu contrasena ha sido cambiada exitosamente. Ya puedes iniciar sesion con tu nueva contrasena.</p>
                    <a href="{{ route('login') }}" wire:navigate class="mt-6 inline-flex items-center justify-center rounded-full bg-wc-accent px-8 py-3 text-sm font-semibold text-white transition-all hover:bg-wc-accent-hover active:scale-[0.98]">
                        Iniciar Sesion
                    </a>
                </div>

            @elseif($invalid)
                {{-- Invalid / Expired Token --}}
                <div class="text-center">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-red-500/10">
                        <svg class="h-7 w-7 text-red-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                        </svg>
                    </div>
                    <h2 class="mt-5 text-2xl font-bold text-wc-text">Enlace Invalido o Expirado</h2>
                    <p class="mt-3 text-sm text-wc-text-secondary">
                        Este enlace de recuperacion ha expirado o ya fue utilizado. Los enlaces son validos por 1 hora.
                    </p>
                    <a href="{{ route('password.request') }}" wire:navigate class="mt-6 inline-flex items-center justify-center rounded-full bg-wc-accent px-8 py-3 text-sm font-semibold text-white transition-all hover:bg-wc-accent-hover active:scale-[0.98]">
                        Solicitar nuevo enlace
                    </a>
                    <div class="mt-4">
                        <a href="{{ route('login') }}" class="inline-flex items-center gap-2 text-sm text-wc-accent hover:underline" wire:navigate>
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
                            Volver a Iniciar Sesion
                        </a>
                    </div>
                </div>

            @else
                {{-- Reset Form --}}
                <div class="text-center">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-wc-accent/10">
                        <svg class="h-7 w-7 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                        </svg>
                    </div>
                    <h2 class="mt-5 text-2xl font-bold text-wc-text">Nueva Contrasena</h2>
                    <p class="mt-2 text-sm text-wc-text-secondary">Elige una contrasena segura de al menos 8 caracteres.</p>
                </div>

                <form wire:submit="resetPassword" class="mt-8 space-y-5">
                    {{-- Hidden email (pre-filled from URL) --}}
                    <input type="hidden" wire:model="email">

                    <div>
                        <label for="rp-email" class="mb-1.5 block text-sm font-medium text-wc-text-secondary">Email</label>
                        <input
                            type="email"
                            id="rp-email"
                            wire:model="email"
                            readonly
                            class="block w-full rounded-lg border border-wc-border bg-wc-bg px-4 py-3 text-sm text-wc-text-secondary cursor-not-allowed opacity-60"
                        >
                        @error('email') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div x-data="{ showPassword: false }">
                        <label for="rp-password" class="mb-1.5 block text-sm font-medium text-wc-text-secondary">Nueva contrasena</label>
                        <div class="relative">
                            <input
                                :type="showPassword ? 'text' : 'password'"
                                id="rp-password"
                                wire:model="password"
                                placeholder="Minimo 8 caracteres"
                                autofocus
                                class="block w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-4 py-3 pr-10 text-sm text-wc-text placeholder-wc-text-tertiary transition-colors focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent"
                            >
                            <button type="button" x-on:click="showPassword = !showPassword" class="absolute right-3 top-1/2 -translate-y-1/2 text-wc-text-tertiary hover:text-wc-text" tabindex="-1">
                                <svg x-show="!showPassword" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                                <svg x-show="showPassword" x-cloak class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12c1.292 4.338 5.31 7.5 10.066 7.5.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" /></svg>
                            </button>
                        </div>
                        @error('password') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="rp-confirm" class="mb-1.5 block text-sm font-medium text-wc-text-secondary">Confirmar contrasena</label>
                        <input
                            type="password"
                            id="rp-confirm"
                            wire:model="password_confirmation"
                            placeholder="Repite tu contrasena"
                            class="block w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-4 py-3 text-sm text-wc-text placeholder-wc-text-tertiary transition-colors focus:border-wc-accent focus:outline-none focus:ring-1 focus:ring-wc-accent"
                        >
                    </div>

                    <button
                        type="submit"
                        wire:loading.attr="disabled"
                        class="flex w-full items-center justify-center rounded-full bg-wc-accent py-3 font-semibold text-white transition-all hover:bg-wc-accent-hover active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-60"
                    >
                        <span wire:loading.remove wire:target="resetPassword">Cambiar Contrasena</span>
                        <span wire:loading wire:target="resetPassword" class="flex items-center gap-2">
                            <svg class="h-5 w-5 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Guardando...
                        </span>
                    </button>
                </form>

                <div class="mt-8 text-center">
                    <a href="{{ route('login') }}" class="inline-flex items-center gap-2 text-sm text-wc-accent hover:underline" wire:navigate>
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
                        Volver a Iniciar Sesion
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
