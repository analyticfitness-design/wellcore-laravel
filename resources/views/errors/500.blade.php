<x-layouts.public>
    <x-slot:title>Error del servidor — WellCore Fitness</x-slot:title>

    <div class="flex min-h-[60vh] items-center justify-center px-4">
        <div class="text-center">
            <p class="font-data text-7xl font-bold text-wc-accent">500</p>
            <h1 class="mt-4 font-display text-3xl tracking-wide text-wc-text sm:text-4xl">ERROR DEL SERVIDOR</h1>
            <p class="mx-auto mt-4 max-w-md text-wc-text-secondary">Algo salio mal de nuestro lado. Estamos trabajando para solucionarlo. Intenta de nuevo en unos minutos.</p>
            <div class="mt-8">
                <a href="{{ url('/') }}" class="rounded-full bg-wc-accent px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-wc-accent/20 hover:bg-wc-accent-hover">Ir al Inicio</a>
            </div>
            <p class="mt-6 text-xs text-wc-text-tertiary">Si el problema persiste, contactanos: info@wellcorefitness.com</p>
        </div>
    </div>
</x-layouts.public>
