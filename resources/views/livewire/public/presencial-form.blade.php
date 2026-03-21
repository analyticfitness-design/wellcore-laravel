<div>
    <section class="bg-wc-bg-tertiary">
        <div class="mx-auto max-w-7xl px-4 py-16 text-center sm:px-6 sm:py-20 lg:px-8">
            <h1 class="font-display text-4xl tracking-wide text-wc-text sm:text-5xl">INSCRIPCION PRESENCIAL</h1>
            <p class="mx-auto mt-4 max-w-2xl text-wc-text-secondary">Completa el formulario y nos pondremos en contacto para agendar tu primera sesion.</p>
        </div>
    </section>

    <section class="bg-wc-bg">
        <div class="mx-auto max-w-2xl px-4 py-16 sm:px-6 lg:px-8">
            @if($submitted)
                <div class="rounded-xl border border-emerald-500/30 bg-emerald-950/20 p-8 text-center">
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-emerald-500/10">
                        <svg class="h-8 w-8 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                    </div>
                    <h2 class="mt-4 font-display text-2xl tracking-wide text-wc-text">INSCRIPCION RECIBIDA</h2>
                    <p class="mt-2 text-wc-text-secondary">Te contactaremos por WhatsApp en las proximas 24 horas para agendar tu primera sesion.</p>
                    <a href="{{ route('home') }}" class="mt-6 inline-flex rounded-full bg-wc-accent px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-wc-accent/20 hover:bg-wc-accent-hover">Volver al Inicio</a>
                </div>
            @else
                <form wire:submit="submit" class="space-y-6">
                    {{-- Name row --}}
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-wc-text">Nombre *</label>
                            <input wire:model="nombre" type="text" class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-4 py-2 text-sm text-wc-text focus:border-red-500 focus:ring-1 focus:ring-red-500">
                            @error('nombre') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-wc-text">Apellido *</label>
                            <input wire:model="apellido" type="text" class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-4 py-2 text-sm text-wc-text focus:border-red-500 focus:ring-1 focus:ring-red-500">
                            @error('apellido') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    {{-- Contact row --}}
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-wc-text">Email *</label>
                            <input wire:model="email" type="email" class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-4 py-2 text-sm text-wc-text focus:border-red-500 focus:ring-1 focus:ring-red-500">
                            @error('email') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-wc-text">WhatsApp *</label>
                            <input wire:model="whatsapp" type="text" placeholder="+57 300 123 4567" class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-4 py-2 text-sm text-wc-text focus:border-red-500 focus:ring-1 focus:ring-red-500">
                            @error('whatsapp') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    {{-- Age --}}
                    <div>
                        <label class="block text-sm font-medium text-wc-text">Edad *</label>
                        <input wire:model="edad" type="number" min="16" max="80" class="mt-1 w-32 rounded-lg border border-wc-border bg-wc-bg-secondary px-4 py-2 text-sm text-wc-text focus:border-red-500 focus:ring-1 focus:ring-red-500">
                        @error('edad') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    {{-- Objective --}}
                    <div>
                        <label class="block text-sm font-medium text-wc-text">Objetivo principal *</label>
                        <textarea wire:model="objetivo" rows="3" placeholder="Describe tu objetivo: perder grasa, ganar musculo, mejorar rendimiento..." class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-4 py-2 text-sm text-wc-text focus:border-red-500 focus:ring-1 focus:ring-red-500"></textarea>
                        @error('objetivo') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    {{-- Experience + Days --}}
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-wc-text">Nivel de experiencia *</label>
                            <select wire:model="experiencia" class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-4 py-2 text-sm text-wc-text focus:border-red-500 focus:ring-1 focus:ring-red-500">
                                <option value="">Selecciona</option>
                                <option value="principiante">Principiante (0-1 ano)</option>
                                <option value="intermedio">Intermedio (1-3 anos)</option>
                                <option value="avanzado">Avanzado (3+ anos)</option>
                            </select>
                            @error('experiencia') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-wc-text">Dias por semana *</label>
                            <select wire:model="dias_disponibles" class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-4 py-2 text-sm text-wc-text focus:border-red-500 focus:ring-1 focus:ring-red-500">
                                <option value="">Selecciona</option>
                                <option value="3">3 dias/semana</option>
                                <option value="4">4 dias/semana</option>
                                <option value="5">5 dias/semana</option>
                            </select>
                            @error('dias_disponibles') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    {{-- Schedule --}}
                    <div>
                        <label class="block text-sm font-medium text-wc-text">Horario preferido *</label>
                        <select wire:model="horario" class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-4 py-2 text-sm text-wc-text focus:border-red-500 focus:ring-1 focus:ring-red-500">
                            <option value="">Selecciona</option>
                            <option value="6:00 - 8:00 AM">6:00 - 8:00 AM</option>
                            <option value="9:00 - 11:00 AM">9:00 - 11:00 AM</option>
                            <option value="5:00 - 8:00 PM">5:00 - 8:00 PM</option>
                            <option value="Sabados 8:00 - 12:00">Sabados 8:00 - 12:00 PM</option>
                        </select>
                        @error('horario') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    {{-- Injury --}}
                    <div>
                        <label class="block text-sm font-medium text-wc-text">Tienes alguna lesion actual? *</label>
                        <select wire:model="lesion" class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-4 py-2 text-sm text-wc-text focus:border-red-500 focus:ring-1 focus:ring-red-500">
                            <option value="">Selecciona</option>
                            <option value="no">No</option>
                            <option value="si">Si</option>
                        </select>
                        @error('lesion') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    @if($lesion === 'si')
                        <div>
                            <label class="block text-sm font-medium text-wc-text">Describe tu lesion</label>
                            <textarea wire:model="detalle_lesion" rows="2" placeholder="Describe la lesion, ubicacion y si estas en tratamiento..." class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-4 py-2 text-sm text-wc-text focus:border-red-500 focus:ring-1 focus:ring-red-500"></textarea>
                        </div>
                    @endif

                    <button type="submit" wire:loading.attr="disabled" class="w-full rounded-full bg-wc-accent px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-wc-accent/20 hover:bg-wc-accent-hover disabled:opacity-50">
                        <span wire:loading.remove>Enviar Inscripcion</span>
                        <span wire:loading>Enviando...</span>
                    </button>
                </form>
            @endif
        </div>
    </section>
</div>
