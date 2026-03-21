<x-layouts.public>
    <x-slot:title>Pagina no encontrada — WellCore Fitness</x-slot:title>

    <div class="flex min-h-[60vh] items-center justify-center px-4">
        <div class="text-center">
            <p class="font-data text-7xl font-bold text-wc-accent">404</p>
            <h1 class="mt-4 font-display text-3xl tracking-wide text-wc-text sm:text-4xl">PAGINA NO ENCONTRADA</h1>
            <p class="mx-auto mt-4 max-w-md text-wc-text-secondary">La pagina que buscas no existe o fue movida. Verifica la URL o vuelve al inicio.</p>
            <div class="mt-8 flex flex-wrap justify-center gap-3">
                <a href="{{ url('/') }}" class="rounded-full bg-wc-accent px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-wc-accent/20 hover:bg-wc-accent-hover">Ir al Inicio</a>
                <a href="{{ url('/planes') }}" class="rounded-full px-6 py-3 text-sm font-semibold text-wc-text hover:bg-wc-bg-secondary">Ver Planes</a>
            </div>
        </div>
    </div>
</x-layouts.public>
