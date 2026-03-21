<div>
    <section class="bg-wc-bg-tertiary">
        <div class="mx-auto max-w-7xl px-4 py-16 text-center sm:px-6 sm:py-20 lg:px-8">
            <span class="inline-flex rounded-full bg-wc-accent/10 px-3 py-1 text-xs font-semibold text-wc-accent">12 Semanas de Transformacion</span>
            <h1 class="mt-4 font-display text-4xl tracking-wide text-wc-text sm:text-5xl">INSCRIPCION RISE</h1>
            <p class="mx-auto mt-4 max-w-2xl text-wc-text-secondary">Completa los 3 pasos para asegurar tu lugar en el programa.</p>

            {{-- Step indicator --}}
            <div class="mx-auto mt-8 flex max-w-md items-center justify-center gap-2">
                @for($i = 1; $i <= 3; $i++)
                    <div class="flex items-center gap-2">
                        <div class="flex h-8 w-8 items-center justify-center rounded-full text-sm font-semibold {{ $step >= $i ? 'bg-wc-accent text-white' : 'border border-wc-border bg-wc-bg-secondary text-wc-text-tertiary' }}">
                            {{ $i }}
                        </div>
                        @if($i < 3)
                            <div class="h-0.5 w-12 {{ $step > $i ? 'bg-wc-accent' : 'bg-wc-border' }}"></div>
                        @endif
                    </div>
                @endfor
            </div>
        </div>
    </section>

    <section class="bg-wc-bg">
        <div class="mx-auto max-w-2xl px-4 py-16 sm:px-6 lg:px-8">

            @if($submitted)
                <div class="rounded-xl border border-emerald-500/30 bg-emerald-950/20 p-8 text-center">
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-emerald-500/10">
                        <svg class="h-8 w-8 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                    </div>
                    <h2 class="mt-4 font-display text-2xl tracking-wide text-wc-text">INSCRIPCION EXITOSA</h2>
                    <p class="mt-2 text-wc-text-secondary">Bienvenido al programa RISE. Recibirás un correo con los proximos pasos y la fecha de inicio.</p>
                    <a href="{{ route('home') }}" class="mt-6 inline-flex rounded-lg bg-wc-accent px-6 py-3 text-sm font-medium text-white hover:bg-wc-accent-hover">Volver al Inicio</a>
                </div>
            @else

                {{-- Step 1: Personal Data --}}
                @if($step === 1)
                    <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-8">
                        <h2 class="font-display text-xl tracking-wide text-wc-text">DATOS PERSONALES</h2>
                        <p class="mt-1 text-sm text-wc-text-tertiary">Informacion basica para personalizar tu programa.</p>

                        <div class="mt-6 space-y-4">
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
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-4">
                                <div>
                                    <label class="block text-sm font-medium text-wc-text">Edad *</label>
                                    <input wire:model="edad" type="number" min="16" max="80" class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-4 py-2 text-sm text-wc-text focus:border-red-500 focus:ring-1 focus:ring-red-500">
                                    @error('edad') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-wc-text">Peso (kg) *</label>
                                    <input wire:model="peso" type="number" step="0.1" class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-4 py-2 text-sm text-wc-text focus:border-red-500 focus:ring-1 focus:ring-red-500">
                                    @error('peso') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-wc-text">Estatura (cm) *</label>
                                    <input wire:model="estatura" type="number" step="0.1" class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-4 py-2 text-sm text-wc-text focus:border-red-500 focus:ring-1 focus:ring-red-500">
                                    @error('estatura') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-wc-text">Genero *</label>
                                    <select wire:model="genero" class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-4 py-2 text-sm text-wc-text focus:border-red-500 focus:ring-1 focus:ring-red-500">
                                        <option value="">Selecciona</option>
                                        <option value="male">Hombre</option>
                                        <option value="female">Mujer</option>
                                        <option value="other">Otro</option>
                                    </select>
                                    @error('genero') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-wc-text">Ciudad *</label>
                                <input wire:model="ciudad" type="text" class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-4 py-2 text-sm text-wc-text focus:border-red-500 focus:ring-1 focus:ring-red-500">
                                @error('ciudad') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="mt-8 flex justify-end">
                            <button wire:click="nextStep" class="rounded-lg bg-wc-accent px-6 py-3 text-sm font-medium text-white hover:bg-wc-accent-hover">Siguiente</button>
                        </div>
                    </div>
                @endif

                {{-- Step 2: Goals & Level --}}
                @if($step === 2)
                    <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-8">
                        <h2 class="font-display text-xl tracking-wide text-wc-text">OBJETIVOS Y NIVEL</h2>
                        <p class="mt-1 text-sm text-wc-text-tertiary">Ayudanos a disenar tu programa ideal.</p>

                        <div class="mt-6 space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-wc-text">Objetivo principal *</label>
                                <textarea wire:model="objetivo" rows="3" placeholder="Que quieres lograr en 12 semanas?" class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-4 py-2 text-sm text-wc-text focus:border-red-500 focus:ring-1 focus:ring-red-500"></textarea>
                                @error('objetivo') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                            </div>
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <label class="block text-sm font-medium text-wc-text">Experiencia *</label>
                                    <select wire:model="experiencia" class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-4 py-2 text-sm text-wc-text focus:border-red-500 focus:ring-1 focus:ring-red-500">
                                        <option value="">Selecciona</option>
                                        <option value="principiante">Principiante (0-1 ano)</option>
                                        <option value="intermedio">Intermedio (1-3 anos)</option>
                                        <option value="avanzado">Avanzado (3+ anos)</option>
                                    </select>
                                    @error('experiencia') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-wc-text">Donde entrenas *</label>
                                    <select wire:model="ubicacion_entrenamiento" class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-4 py-2 text-sm text-wc-text focus:border-red-500 focus:ring-1 focus:ring-red-500">
                                        <option value="">Selecciona</option>
                                        <option value="gym">Gimnasio</option>
                                        <option value="home">Casa</option>
                                        <option value="hybrid">Ambos</option>
                                    </select>
                                    @error('ubicacion_entrenamiento') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-wc-text">Dias disponibles por semana *</label>
                                <select wire:model="dias_disponibles" class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-4 py-2 text-sm text-wc-text focus:border-red-500 focus:ring-1 focus:ring-red-500">
                                    <option value="">Selecciona</option>
                                    <option value="3">3 dias</option>
                                    <option value="4">4 dias</option>
                                    <option value="5">5 dias</option>
                                    <option value="6">6 dias</option>
                                </select>
                                @error('dias_disponibles') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-wc-text">Lesion actual? *</label>
                                <select wire:model="lesion" class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-4 py-2 text-sm text-wc-text focus:border-red-500 focus:ring-1 focus:ring-red-500">
                                    <option value="">Selecciona</option>
                                    <option value="no">No</option>
                                    <option value="si">Si</option>
                                </select>
                                @error('lesion') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                            </div>
                            @if($lesion === 'si')
                                <div>
                                    <label class="block text-sm font-medium text-wc-text">Detalle de lesion</label>
                                    <textarea wire:model="detalle_lesion" rows="2" class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-4 py-2 text-sm text-wc-text focus:border-red-500 focus:ring-1 focus:ring-red-500"></textarea>
                                </div>
                            @endif
                            <div>
                                <label class="block text-sm font-medium text-wc-text">Por que quieres unirte a RISE? *</label>
                                <textarea wire:model="motivacion" rows="3" placeholder="Cuentanos tu motivacion..." class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg-secondary px-4 py-2 text-sm text-wc-text focus:border-red-500 focus:ring-1 focus:ring-red-500"></textarea>
                                @error('motivacion') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="mt-8 flex justify-between">
                            <button wire:click="prevStep" class="rounded-lg border border-wc-border bg-wc-bg-secondary px-6 py-3 text-sm font-medium text-wc-text hover:bg-wc-bg">Anterior</button>
                            <button wire:click="nextStep" class="rounded-lg bg-wc-accent px-6 py-3 text-sm font-medium text-white hover:bg-wc-accent-hover">Siguiente</button>
                        </div>
                    </div>
                @endif

                {{-- Step 3: Payment --}}
                @if($step === 3)
                    <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-8">
                        <h2 class="font-display text-xl tracking-wide text-wc-text">PAGO</h2>
                        <p class="mt-1 text-sm text-wc-text-tertiary">Inversion unica para las 12 semanas del programa.</p>

                        <div class="mt-6 rounded-xl border border-wc-border bg-wc-bg p-6 text-center">
                            <p class="text-sm text-wc-text-secondary">Programa RISE — 12 Semanas</p>
                            <p class="mt-2 font-data text-4xl font-bold text-wc-text">$99,900</p>
                            <p class="text-sm text-wc-text-tertiary">COP (pago unico)</p>
                        </div>

                        <div class="mt-6 space-y-3">
                            <h3 class="text-sm font-semibold text-wc-text">Incluye:</h3>
                            <ul class="space-y-2">
                                @foreach(['Programa de entrenamiento periodizado 12 semanas', 'Plan nutricional personalizado con macros', 'Check-ins semanales obligatorios', 'Comunidad exclusiva RISE', 'Guia de suplementacion', 'Acceso completo a la plataforma WellCore'] as $item)
                                    <li class="flex items-start gap-2 text-sm text-wc-text-secondary">
                                        <svg class="mt-0.5 h-4 w-4 shrink-0 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                                        {{ $item }}
                                    </li>
                                @endforeach
                            </ul>
                        </div>

                        <div class="mt-8 rounded-xl border border-amber-500/30 bg-amber-950/20 p-4">
                            <p class="text-sm text-amber-300">El pago se procesa a traves de Wompi (pasarela segura). Seras redirigido al completar la inscripcion.</p>
                        </div>

                        <div class="mt-8 flex justify-between">
                            <button wire:click="prevStep" class="rounded-lg border border-wc-border bg-wc-bg-secondary px-6 py-3 text-sm font-medium text-wc-text hover:bg-wc-bg">Anterior</button>
                            <button wire:click="submit" wire:loading.attr="disabled" class="rounded-lg bg-wc-accent px-8 py-3 text-sm font-medium text-white hover:bg-wc-accent-hover disabled:opacity-50">
                                <span wire:loading.remove>Confirmar Inscripcion</span>
                                <span wire:loading>Procesando...</span>
                            </button>
                        </div>
                    </div>
                @endif

            @endif
        </div>
    </section>
</div>
