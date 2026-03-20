<div class="flex min-h-[calc(100vh-8rem)] items-center justify-center px-4 py-8">
    <div class="w-full max-w-md">
        <div class="overflow-hidden rounded-2xl border border-wc-border bg-wc-bg-tertiary p-8 shadow-xl sm:p-10">
            {{-- Icon --}}
            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-wc-accent/10">
                <svg class="h-7 w-7 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                </svg>
            </div>

            {{-- Heading --}}
            <div class="mt-6 text-center">
                <h2 class="text-2xl font-bold text-wc-text">Recuperar Contraseña</h2>
                <p class="mt-2 text-sm text-wc-text-secondary">
                    Por el momento, la recuperación de contraseña es manual.
                </p>
            </div>

            {{-- Instructions --}}
            <div class="mt-8 rounded-xl border border-wc-border bg-wc-bg-secondary p-6 text-center">
                <p class="text-sm text-wc-text-secondary">
                    Envíanos un email con tu nombre completo y correo registrado a:
                </p>
                <a
                    href="mailto:info@wellcorefitness.com"
                    class="mt-3 inline-flex items-center gap-2 text-lg font-semibold text-wc-accent hover:text-wc-accent-hover"
                >
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                    </svg>
                    info@wellcorefitness.com
                </a>
                <p class="mt-4 text-xs text-wc-text-tertiary">
                    Te responderemos en un plazo máximo de 24 horas.
                </p>
            </div>

            {{-- Back to login --}}
            <div class="mt-6 text-center">
                <a
                    href="{{ route('login') }}"
                    class="inline-flex items-center gap-2 text-sm font-medium text-wc-accent hover:text-wc-accent-hover"
                    wire:navigate
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                    </svg>
                    Volver a Iniciar Sesión
                </a>
            </div>
        </div>
    </div>
</div>
