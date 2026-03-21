<div>
    {{-- Header --}}
    <section class="bg-wc-bg-tertiary">
        <div class="mx-auto max-w-7xl px-4 py-16 text-center sm:px-6 sm:py-20 lg:px-8">
            <h1 class="font-display text-4xl tracking-wide text-wc-text sm:text-5xl lg:text-6xl">APLICA COMO COACH</h1>
            <p class="mx-auto mt-4 max-w-2xl text-lg text-wc-text-secondary">
                Completa el formulario y nuestro equipo revisara tu aplicacion en las proximas 48 horas.
            </p>
        </div>
    </section>

    <section class="bg-wc-bg">
        <div class="mx-auto max-w-3xl px-4 py-16 sm:px-6 lg:px-8">

            @if ($submitted)
                {{-- Success State --}}
                <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-10 text-center">
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-green-500/10">
                        <svg class="h-8 w-8 text-green-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                        </svg>
                    </div>
                    <h2 class="mt-6 font-display text-2xl tracking-wide text-wc-text sm:text-3xl">APLICACION ENVIADA</h2>
                    <p class="mt-4 text-wc-text-secondary">
                        Gracias por tu interes en unirte al equipo WellCore. Hemos recibido tu aplicacion
                        y la revisaremos en las proximas 48 horas. Te contactaremos al correo o WhatsApp que proporcionaste.
                    </p>
                    <div class="mt-8">
                        <a href="{{ route('home') }}" class="inline-flex items-center justify-center rounded-lg bg-wc-accent px-6 py-3 text-sm font-medium text-white hover:bg-wc-accent-hover">
                            Volver al Inicio
                        </a>
                    </div>
                </div>
            @else
                {{-- Application Form --}}
                <form wire:submit="submit" class="space-y-6">

                    {{-- Name, Email, WhatsApp row --}}
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
                        {{-- Name --}}
                        <div>
                            <label for="name" class="block text-sm font-medium text-wc-text">Nombre completo <span class="text-wc-accent">*</span></label>
                            <input
                                type="text"
                                id="name"
                                wire:model="name"
                                placeholder="Tu nombre"
                                class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-4 py-2 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-red-500 focus:ring-1 focus:ring-red-500 focus:outline-none"
                            >
                            @error('name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>

                        {{-- Email --}}
                        <div>
                            <label for="email" class="block text-sm font-medium text-wc-text">Correo electronico <span class="text-wc-accent">*</span></label>
                            <input
                                type="email"
                                id="email"
                                wire:model="email"
                                placeholder="coach@ejemplo.com"
                                class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-4 py-2 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-red-500 focus:ring-1 focus:ring-red-500 focus:outline-none"
                            >
                            @error('email') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>

                        {{-- WhatsApp --}}
                        <div>
                            <label for="whatsapp" class="block text-sm font-medium text-wc-text">WhatsApp <span class="text-wc-accent">*</span></label>
                            <input
                                type="text"
                                id="whatsapp"
                                wire:model="whatsapp"
                                placeholder="+52 555 123 4567"
                                class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-4 py-2 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-red-500 focus:ring-1 focus:ring-red-500 focus:outline-none"
                            >
                            @error('whatsapp') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    {{-- City --}}
                    <div>
                        <label for="city" class="block text-sm font-medium text-wc-text">Ciudad <span class="text-wc-accent">*</span></label>
                        <input
                            type="text"
                            id="city"
                            wire:model="city"
                            placeholder="Tu ciudad"
                            class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-4 py-2 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-red-500 focus:ring-1 focus:ring-red-500 focus:outline-none"
                        >
                        @error('city') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    {{-- Bio --}}
                    <div>
                        <label for="bio" class="block text-sm font-medium text-wc-text">Sobre ti <span class="text-wc-accent">*</span></label>
                        <textarea
                            id="bio"
                            wire:model="bio"
                            rows="4"
                            placeholder="Cuentanos sobre tu experiencia y enfoque como coach..."
                            class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-4 py-2 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-red-500 focus:ring-1 focus:ring-red-500 focus:outline-none"
                        ></textarea>
                        <p class="mt-1 text-xs text-wc-text-tertiary">Minimo 50 caracteres</p>
                        @error('bio') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    {{-- Experience & Plan & Current Clients row --}}
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
                        {{-- Experience --}}
                        <div>
                            <label for="experience" class="block text-sm font-medium text-wc-text">Anos de experiencia <span class="text-wc-accent">*</span></label>
                            <select
                                id="experience"
                                wire:model="experience"
                                class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-4 py-2 text-sm text-wc-text focus:border-red-500 focus:ring-1 focus:ring-red-500 focus:outline-none"
                            >
                                <option value="">Seleccionar...</option>
                                <option value="1-2">1-2 años</option>
                                <option value="3-5">3-5 años</option>
                                <option value="5-10">5-10 años</option>
                                <option value="10+">10+ años</option>
                            </select>
                            @error('experience') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>

                        {{-- Plan --}}
                        <div>
                            <label for="plan" class="block text-sm font-medium text-wc-text">Tipo de coaching <span class="text-wc-accent">*</span></label>
                            <select
                                id="plan"
                                wire:model="plan"
                                class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-4 py-2 text-sm text-wc-text focus:border-red-500 focus:ring-1 focus:ring-red-500 focus:outline-none"
                            >
                                <option value="">Seleccionar...</option>
                                <option value="training">Entrenamiento</option>
                                <option value="nutrition">Nutricion</option>
                                <option value="both">Ambos</option>
                            </select>
                            @error('plan') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>

                        {{-- Current Clients --}}
                        <div>
                            <label for="current_clients" class="block text-sm font-medium text-wc-text">Clientes actuales <span class="text-wc-accent">*</span></label>
                            <select
                                id="current_clients"
                                wire:model="current_clients"
                                class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-4 py-2 text-sm text-wc-text focus:border-red-500 focus:ring-1 focus:ring-red-500 focus:outline-none"
                            >
                                <option value="">Seleccionar...</option>
                                <option value="0">0</option>
                                <option value="1-5">1-5</option>
                                <option value="6-15">6-15</option>
                                <option value="16+">16+</option>
                            </select>
                            @error('current_clients') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    {{-- Specializations --}}
                    <div>
                        <label class="block text-sm font-medium text-wc-text">Especializaciones <span class="text-wc-accent">*</span></label>
                        <p class="mt-1 text-xs text-wc-text-tertiary">Selecciona todas las que apliquen.</p>
                        <div class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-2">
                            @php
                                $specs = [
                                    'fuerza' => 'Fuerza',
                                    'hipertrofia' => 'Hipertrofia',
                                    'perdida_grasa' => 'Perdida de grasa',
                                    'nutricion_deportiva' => 'Nutricion deportiva',
                                    'rehabilitacion' => 'Rehabilitacion',
                                    'funcional' => 'Entrenamiento funcional',
                                    'fitness_femenino' => 'Fitness femenino',
                                    'adultos_mayores' => 'Adultos mayores',
                                ];
                            @endphp
                            @foreach ($specs as $value => $label)
                                <label class="flex items-center gap-3 rounded-lg border border-wc-border bg-wc-bg-secondary px-4 py-3 text-sm text-wc-text cursor-pointer hover:border-wc-accent/50 transition-colors has-[:checked]:border-wc-accent has-[:checked]:bg-wc-accent/5">
                                    <input
                                        type="checkbox"
                                        wire:model="specializations"
                                        value="{{ $value }}"
                                        class="h-4 w-4 rounded border-wc-border bg-wc-bg-secondary text-wc-accent focus:ring-red-500"
                                    >
                                    {{ $label }}
                                </label>
                            @endforeach
                        </div>
                        @error('specializations') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    {{-- Referral --}}
                    <div>
                        <label for="referral" class="block text-sm font-medium text-wc-text">Como nos encontraste? <span class="text-wc-text-tertiary text-xs">(opcional)</span></label>
                        <input
                            type="text"
                            id="referral"
                            wire:model="referral"
                            placeholder="Instagram, referido, Google..."
                            class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-4 py-2 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-red-500 focus:ring-1 focus:ring-red-500 focus:outline-none"
                        >
                        @error('referral') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    {{-- Submit --}}
                    <div class="pt-4">
                        <button
                            type="submit"
                            class="w-full rounded-lg bg-wc-accent px-6 py-3 text-sm font-medium text-white hover:bg-wc-accent-hover disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
                            wire:loading.attr="disabled"
                        >
                            <span wire:loading.remove wire:target="submit">Enviar Aplicacion</span>
                            <span wire:loading wire:target="submit" class="flex items-center gap-2">
                                <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Enviando...
                            </span>
                        </button>
                    </div>
                </form>
            @endif

        </div>
    </section>
</div>
