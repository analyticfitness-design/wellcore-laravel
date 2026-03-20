<div class="flex min-h-[calc(100vh-8rem)] items-center justify-center px-4 py-8">
    <div class="w-full max-w-md">
        <div class="overflow-hidden rounded-2xl border border-wc-border bg-wc-bg-tertiary p-8 shadow-xl sm:p-10">
            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-wc-accent/10">
                <svg class="h-7 w-7 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                </svg>
            </div>

            @if($sent)
                <div class="mt-6 text-center">
                    <h2 class="text-2xl font-bold text-wc-text">Email Enviado</h2>
                    <p class="mt-4 text-sm text-wc-text-secondary">
                        Si existe una cuenta con <span class="font-semibold text-wc-text">{{ $email }}</span>, recibiras un enlace para restablecer tu contrasena.
                    </p>
                    <p class="mt-2 text-xs text-wc-text-tertiary">Revisa tu carpeta de spam si no lo ves en unos minutos.</p>
                </div>
                <div class="mt-8 text-center">
                    <a href="{{ route('login') }}" class="inline-flex items-center gap-2 text-sm font-medium text-wc-accent hover:text-wc-accent-hover">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
                        Volver a Iniciar Sesion
                    </a>
                </div>
            @else
                <div class="mt-6 text-center">
                    <h2 class="text-2xl font-bold text-wc-text">Recuperar Contrasena</h2>
                    <p class="mt-2 text-sm text-wc-text-secondary">Ingresa tu email y te enviaremos un enlace para restablecer tu contrasena.</p>
                </div>

                <form wire:submit="sendReset" class="mt-8">
                    <div>
                        <label class="block text-xs font-medium text-wc-text-tertiary">Email</label>
                        <input type="email" wire:model="email" class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg px-4 py-2.5 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none" placeholder="tu@email.com" autofocus>
                        @error('email') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <button type="submit" class="mt-6 flex w-full items-center justify-center rounded-lg bg-wc-accent px-6 py-2.5 text-sm font-semibold text-white hover:bg-wc-accent-hover" wire:loading.attr="disabled">
                        <span wire:loading.remove>Enviar enlace de recuperacion</span>
                        <span wire:loading>Enviando...</span>
                    </button>
                </form>

                <div class="mt-6 text-center">
                    <a href="{{ route('login') }}" class="inline-flex items-center gap-2 text-sm font-medium text-wc-accent hover:text-wc-accent-hover">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
                        Volver a Iniciar Sesion
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
