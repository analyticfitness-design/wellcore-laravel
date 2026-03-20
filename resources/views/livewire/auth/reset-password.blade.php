<div class="flex min-h-[calc(100vh-8rem)] items-center justify-center px-4 py-8">
    <div class="w-full max-w-md">
        <div class="overflow-hidden rounded-2xl border border-wc-border bg-wc-bg-tertiary p-8 shadow-xl sm:p-10">
            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-wc-accent/10">
                <svg class="h-7 w-7 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                </svg>
            </div>

            @if($reset)
                <div class="mt-6 text-center">
                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-green-500/10">
                        <svg class="h-6 w-6 text-green-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                    </div>
                    <h2 class="mt-4 text-2xl font-bold text-wc-text">Contrasena Actualizada</h2>
                    <p class="mt-2 text-sm text-wc-text-secondary">Tu contrasena ha sido cambiada exitosamente.</p>
                    <a href="{{ route('login') }}" class="mt-6 inline-flex items-center justify-center rounded-lg bg-wc-accent px-6 py-2.5 text-sm font-semibold text-white hover:bg-wc-accent-hover">
                        Iniciar Sesion
                    </a>
                </div>
            @elseif($invalid)
                <div class="mt-6 text-center">
                    <h2 class="text-2xl font-bold text-wc-text">Enlace Invalido</h2>
                    <p class="mt-2 text-sm text-wc-text-secondary">El enlace de recuperacion ha expirado o no es valido. Solicita uno nuevo.</p>
                    <a href="{{ route('password.request') }}" class="mt-6 inline-flex items-center justify-center rounded-lg bg-wc-accent px-6 py-2.5 text-sm font-semibold text-white hover:bg-wc-accent-hover">
                        Solicitar nuevo enlace
                    </a>
                </div>
            @else
                <div class="mt-6 text-center">
                    <h2 class="text-2xl font-bold text-wc-text">Nueva Contrasena</h2>
                    <p class="mt-2 text-sm text-wc-text-secondary">Ingresa tu nueva contrasena.</p>
                </div>

                <form wire:submit="resetPassword" class="mt-8 space-y-4">
                    <div>
                        <label class="block text-xs font-medium text-wc-text-tertiary">Email</label>
                        <input type="email" wire:model="email" class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg px-4 py-2.5 text-sm text-wc-text focus:border-wc-accent focus:outline-none" placeholder="tu@email.com">
                        @error('email') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-wc-text-tertiary">Nueva contrasena</label>
                        <input type="password" wire:model="password" class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg px-4 py-2.5 text-sm text-wc-text focus:border-wc-accent focus:outline-none" placeholder="Minimo 8 caracteres">
                        @error('password') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-wc-text-tertiary">Confirmar contrasena</label>
                        <input type="password" wire:model="password_confirmation" class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg px-4 py-2.5 text-sm text-wc-text focus:border-wc-accent focus:outline-none" placeholder="Repite tu contrasena">
                    </div>
                    <button type="submit" class="flex w-full items-center justify-center rounded-lg bg-wc-accent px-6 py-2.5 text-sm font-semibold text-white hover:bg-wc-accent-hover">
                        Cambiar Contrasena
                    </button>
                </form>
            @endif
        </div>
    </div>
</div>
