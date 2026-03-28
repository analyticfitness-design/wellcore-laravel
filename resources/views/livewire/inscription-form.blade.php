<div class="min-h-screen bg-wc-bg">
    @if($submitted)
        {{-- Meta Pixel: Lead event --}}
        @if(config('app.meta_pixel_id'))
        <script>
            if (typeof fbq === 'function') {
                fbq('track', 'Lead', {
                    content_name: '{{ $plan ?? "WellCore" }}',
                    content_category: 'Inscription',
                });
            }
        </script>
        @endif
        {{-- Success --}}
        <div class="mx-auto max-w-2xl px-4 py-20 text-center sm:px-6 lg:px-8">
            <div class="rounded-2xl border border-wc-accent/30 bg-wc-bg-tertiary p-10 sm:p-16">
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-wc-accent/10">
                    <svg class="h-8 w-8 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                </div>
                <h1 class="mt-6 font-display text-4xl tracking-wide text-wc-text sm:text-5xl">FORMULARIO ENVIADO</h1>
                <p class="mt-4 text-wc-text-secondary">Tu inscripcion ha sido recibida correctamente.</p>
                <div class="mx-auto mt-8 max-w-md space-y-3 text-left">
                    <div class="flex items-start gap-3 text-sm text-wc-text-secondary">
                        <svg class="mt-0.5 h-4 w-4 shrink-0 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                        Tu coach te contactara por WhatsApp o email dentro de las proximas 24 horas
                    </div>
                    <div class="flex items-start gap-3 text-sm text-wc-text-secondary">
                        <svg class="mt-0.5 h-4 w-4 shrink-0 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                        Nuestro equipo revisara tu solicitud y te contactara dentro de 24 horas para activar tu cuenta y comenzar tu plan.
                    </div>
                    <div class="flex items-start gap-3 text-sm text-wc-text-secondary">
                        <svg class="mt-0.5 h-4 w-4 shrink-0 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                        Tu coach comenzara a disenar tu programa personalizado una vez actives tu cuenta.
                    </div>
                </div>
                <a href="{{ route('home') }}" class="mt-8 inline-flex items-center justify-center rounded-lg bg-wc-accent px-6 py-3 text-sm font-medium text-white hover:bg-wc-accent-hover">
                    Volver al inicio
                </a>
            </div>
        </div>
    @else
        <div class="mx-auto max-w-2xl px-4 py-12 sm:px-6 lg:px-8">
            {{-- Header --}}
            <div class="text-center">
                <p class="text-sm font-semibold uppercase tracking-widest text-wc-accent">Inscripcion WellCore</p>
                <h1 class="mt-2 font-display text-3xl tracking-wide text-wc-text sm:text-4xl">COMIENZA TU TRANSFORMACION</h1>
            </div>

            {{-- Progress --}}
            <div class="mt-8">
                <div class="flex items-center justify-center gap-2">
                    @for ($i = 0; $i < 8; $i++)
                        <div class="h-2 w-2 rounded-full transition-all duration-300 {{ $i === $step ? 'scale-150 bg-wc-accent' : ($i < $step ? 'bg-wc-accent/50' : 'bg-wc-border') }}"></div>
                    @endfor
                </div>
                <p class="mt-3 text-center text-xs text-wc-text-tertiary">Paso {{ $step + 1 }} de 8</p>
            </div>

            {{-- Form --}}
            <form wire:submit="{{ $step === 7 ? 'submit' : 'nextStep' }}" class="mt-8">
                {{-- Step 0: Plan --}}
                @if($step === 0)
                <div>
                    <h2 class="font-display text-xl tracking-wide text-wc-text">SELECCIONA TU PLAN</h2>
                    <p class="mt-1 text-sm text-wc-text-secondary">Elige el plan que mejor se adapte a tus objetivos.</p>
                    <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach([
                            'esencial'   => ['Esencial',   '$299,000/mes',          'Entrenamiento personalizado',               false],
                            'metodo'     => ['Metodo',     '$399,000/mes',          'Entreno + Nutricion + Seguimiento',          true],
                            'elite'      => ['Elite',      '$549,000/mes',          'Todo incluido + Check-ins 1:1',             false],
                            'rise'       => ['RISE',       '$99,900 pago unico',    'Programa grupal de 8 semanas',              false],
                            'presencial' => ['Presencial', '$450,000–$650,000/mes', 'Coaching 1:1 presencial en Bogota',         false],
                        ] as $key => [$name, $price, $desc, $popular])
                        <button type="button" wire:click="selectPlan('{{ $key }}')"
                            class="rounded-xl border-2 p-6 text-left transition-all {{ $plan === $key ? 'border-wc-accent bg-wc-accent/5' : 'border-wc-border bg-wc-bg-tertiary hover:border-wc-accent/40' }}">
                            @if($popular)
                                <span class="mb-2 inline-block rounded-full bg-wc-accent px-2 py-0.5 text-[10px] font-semibold text-white">MAS POPULAR</span>
                            @endif
                            <h3 class="font-display text-lg tracking-wide text-wc-text">{{ strtoupper($name) }}</h3>
                            <p class="mt-1 font-data text-lg font-bold text-wc-accent">{{ $price }}</p>
                            <p class="mt-2 text-xs text-wc-text-tertiary">{{ $desc }}</p>
                        </button>
                        @endforeach
                    </div>
                    @error('plan') <p class="mt-2 text-sm text-red-500">{{ $message }}</p> @enderror
                </div>
                @endif

                {{-- Step 1: Info Basica --}}
                @if($step === 1)
                <div>
                    <h2 class="font-display text-xl tracking-wide text-wc-text">INFORMACION BASICA</h2>
                    <p class="mt-1 text-sm text-wc-text-secondary">Datos esenciales para tu diagnostico.</p>
                    <div class="mt-6 space-y-4">
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label class="block text-xs font-medium text-wc-text-tertiary">Nombre *</label>
                                <input type="text" wire:model="nombre" class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg-tertiary px-4 py-2.5 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none" placeholder="Tu nombre">
                                @error('nombre') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-wc-text-tertiary">Apellido</label>
                                <input type="text" wire:model="apellido" class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg-tertiary px-4 py-2.5 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none" placeholder="Tu apellido">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-wc-text-tertiary">Email *</label>
                            <input type="email" wire:model="email" class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg-tertiary px-4 py-2.5 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none" placeholder="tu@email.com">
                            @error('email') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-wc-text-tertiary">WhatsApp *</label>
                            <input type="text" wire:model="whatsapp" class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg-tertiary px-4 py-2.5 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none" placeholder="+57 312 490 4720">
                            @error('whatsapp') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div class="grid grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-wc-text-tertiary">Edad *</label>
                                <input type="number" wire:model="edad" class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg-tertiary px-4 py-2.5 text-sm text-wc-text focus:border-wc-accent focus:outline-none" placeholder="25">
                                @error('edad') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-wc-text-tertiary">Peso (kg) *</label>
                                <input type="number" step="0.1" wire:model="peso" class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg-tertiary px-4 py-2.5 text-sm text-wc-text focus:border-wc-accent focus:outline-none" placeholder="70">
                                @error('peso') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-wc-text-tertiary">Estatura (cm) *</label>
                                <input type="number" wire:model="estatura" class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg-tertiary px-4 py-2.5 text-sm text-wc-text focus:border-wc-accent focus:outline-none" placeholder="170">
                                @error('estatura') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                            </div>
                        </div>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label class="block text-xs font-medium text-wc-text-tertiary">Genero *</label>
                                <select wire:model="genero" class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg-tertiary px-4 py-2.5 text-sm text-wc-text focus:border-wc-accent focus:outline-none">
                                    <option value="">Seleccionar</option>
                                    <option value="masculino">Masculino</option>
                                    <option value="femenino">Femenino</option>
                                    <option value="otro">Prefiero no especificar</option>
                                </select>
                                @error('genero') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-wc-text-tertiary">Objetivo principal *</label>
                                <select wire:model="objetivo" class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg-tertiary px-4 py-2.5 text-sm text-wc-text focus:border-wc-accent focus:outline-none">
                                    <option value="">Seleccionar</option>
                                    <option value="perder_grasa">Perder grasa</option>
                                    <option value="ganar_masa">Ganar masa muscular</option>
                                    <option value="recomposicion">Recomposicion corporal</option>
                                    <option value="rendimiento">Rendimiento deportivo</option>
                                    <option value="salud">Salud general</option>
                                </select>
                                @error('objetivo') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                            </div>
                        </div>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label class="block text-xs font-medium text-wc-text-tertiary">Ciudad</label>
                                <input type="text" wire:model="ciudad" class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg-tertiary px-4 py-2.5 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none" placeholder="Medellin">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-wc-text-tertiary">Pais</label>
                                <input type="text" wire:model="pais" class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg-tertiary px-4 py-2.5 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none" placeholder="Colombia">
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                {{-- Step 2: Experiencia --}}
                @if($step === 2)
                <div>
                    <h2 class="font-display text-xl tracking-wide text-wc-text">EXPERIENCIA DE ENTRENAMIENTO</h2>
                    <p class="mt-1 text-sm text-wc-text-secondary">Tu historial nos ayuda a personalizar tu programa.</p>
                    <div class="mt-6 space-y-4">
                        <div>
                            <label class="block text-xs font-medium text-wc-text-tertiary">Tiempo entrenando *</label>
                            <select wire:model="experiencia" class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg-tertiary px-4 py-2.5 text-sm text-wc-text focus:border-wc-accent focus:outline-none">
                                <option value="">Seleccionar</option>
                                <option value="menos_6m">Menos de 6 meses</option>
                                <option value="6m_2a">6 meses a 2 anos</option>
                                <option value="mas_2a">Mas de 2 anos</option>
                            </select>
                            @error('experiencia') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-wc-text-tertiary">Dias disponibles por semana *</label>
                            <select wire:model="dias_disponibles" class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg-tertiary px-4 py-2.5 text-sm text-wc-text focus:border-wc-accent focus:outline-none">
                                <option value="">Seleccionar</option>
                                <option value="2">2 dias</option>
                                <option value="3">3 dias</option>
                                <option value="4">4 dias</option>
                                <option value="5">5 dias</option>
                                <option value="6+">6 o mas dias</option>
                            </select>
                            @error('dias_disponibles') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-wc-text-tertiary">Equipamiento disponible *</label>
                            <select wire:model="equipamiento" class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg-tertiary px-4 py-2.5 text-sm text-wc-text focus:border-wc-accent focus:outline-none">
                                <option value="">Seleccionar</option>
                                <option value="gimnasio">Gimnasio completo</option>
                                <option value="home_gym">Home gym</option>
                                <option value="peso_corporal">Solo peso corporal</option>
                                <option value="mancuernas_bandas">Mancuernas + bandas</option>
                            </select>
                            @error('equipamiento') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-wc-text-tertiary">Coaching previo</label>
                            <select wire:model="coaching_previo" class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg-tertiary px-4 py-2.5 text-sm text-wc-text focus:border-wc-accent focus:outline-none">
                                <option value="">Seleccionar</option>
                                <option value="no">No, es mi primera vez</option>
                                <option value="si_buenos">Si, buenos resultados</option>
                                <option value="si_malos">Si, malos resultados</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-wc-text-tertiary">Describe tu rutina actual</label>
                            <textarea wire:model="rutina_actual" rows="3" class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg-tertiary px-4 py-2.5 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none" placeholder="Que estas haciendo actualmente..."></textarea>
                        </div>
                    </div>
                </div>
                @endif

                {{-- Step 3: Preferencias --}}
                @if($step === 3)
                <div>
                    <h2 class="font-display text-xl tracking-wide text-wc-text">PREFERENCIAS DE ENTRENAMIENTO</h2>
                    <p class="mt-1 text-sm text-wc-text-secondary">Opcional — nos ayuda a personalizar aun mas.</p>
                    <div class="mt-6 space-y-4">
                        <div>
                            <label class="block text-xs font-medium text-wc-text-tertiary">Tipo de entrenamiento preferido</label>
                            <select wire:model="tipo_entrenamiento" class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg-tertiary px-4 py-2.5 text-sm text-wc-text focus:border-wc-accent focus:outline-none">
                                <option value="">Sin preferencia</option>
                                <option value="pesas">Pesas</option>
                                <option value="funcional">Funcional</option>
                                <option value="cardio_pesas">Cardio + pesas</option>
                                <option value="calistenia">Calistenia</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-wc-text-tertiary">Duracion de sesion preferida</label>
                            <select wire:model="duracion_sesion" class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg-tertiary px-4 py-2.5 text-sm text-wc-text focus:border-wc-accent focus:outline-none">
                                <option value="">Seleccionar</option>
                                <option value="45">45 minutos</option>
                                <option value="60">60 minutos</option>
                                <option value="75_90">75-90 minutos</option>
                                <option value="90+">Mas de 90 minutos</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-wc-text-tertiary">Preferencias de horario</label>
                            <textarea wire:model="horario" rows="2" class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg-tertiary px-4 py-2.5 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none" placeholder="Mananas, tardes, noches..."></textarea>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-wc-text-tertiary">Restricciones de ejercicio</label>
                            <textarea wire:model="restricciones_ejercicio" rows="2" class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg-tertiary px-4 py-2.5 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none" placeholder="Ejercicios que no puedes o no quieres hacer..."></textarea>
                        </div>
                    </div>
                </div>
                @endif

                {{-- Step 4: Lesiones --}}
                @if($step === 4)
                <div>
                    <h2 class="font-display text-xl tracking-wide text-wc-text">LESIONES Y LIMITACIONES</h2>
                    <p class="mt-1 text-sm text-wc-text-secondary">Importante para disenar un programa seguro.</p>
                    <div class="mt-6 space-y-4">
                        <div>
                            <label class="block text-xs font-medium text-wc-text-tertiary">Tienes alguna lesion actual? *</label>
                            <select wire:model.live="lesion" class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg-tertiary px-4 py-2.5 text-sm text-wc-text focus:border-wc-accent focus:outline-none">
                                <option value="">Seleccionar</option>
                                <option value="no">No</option>
                                <option value="si">Si</option>
                                <option value="cronica">Condicion cronica</option>
                            </select>
                            @error('lesion') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        @if(in_array($lesion, ['si', 'cronica']))
                        <div>
                            <label class="block text-xs font-medium text-wc-text-tertiary">Describe la lesion</label>
                            <textarea wire:model="detalle_lesion" rows="3" class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg-tertiary px-4 py-2.5 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none" placeholder="Tipo de lesion, desde cuando, tratamiento..."></textarea>
                        </div>
                        @endif
                        <div>
                            <label class="block text-xs font-medium text-wc-text-tertiary">Condiciones medicas</label>
                            <textarea wire:model="condiciones_medicas" rows="2" class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg-tertiary px-4 py-2.5 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none" placeholder="Diabetes, hipertension, etc..."></textarea>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-wc-text-tertiary">Medicamentos que afecten el entrenamiento</label>
                            <textarea wire:model="medicamentos" rows="2" class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg-tertiary px-4 py-2.5 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none" placeholder="Lista de medicamentos relevantes..."></textarea>
                        </div>
                    </div>
                </div>
                @endif

                {{-- Step 5: Nutricion --}}
                @if($step === 5)
                <div>
                    <h2 class="font-display text-xl tracking-wide text-wc-text">INFORMACION NUTRICIONAL</h2>
                    <p class="mt-1 text-sm text-wc-text-secondary">Opcional — para planes con componente nutricional.</p>
                    <div class="mt-6 space-y-4">
                        <div>
                            <label class="block text-xs font-medium text-wc-text-tertiary">Dieta actual</label>
                            <select wire:model="dieta_actual" class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg-tertiary px-4 py-2.5 text-sm text-wc-text focus:border-wc-accent focus:outline-none">
                                <option value="">Seleccionar</option>
                                <option value="todo">Como de todo</option>
                                <option value="flexible">Flexible/IIFYM</option>
                                <option value="vegana">Vegana</option>
                                <option value="vegetariana">Vegetariana</option>
                                <option value="sin_gluten">Sin gluten</option>
                                <option value="keto">Keto</option>
                                <option value="otra">Otra</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-wc-text-tertiary">Alergias o intolerancias</label>
                            <textarea wire:model="alergias" rows="2" class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg-tertiary px-4 py-2.5 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none" placeholder="Lactosa, frutos secos, etc..."></textarea>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-wc-text-tertiary">Comidas por dia</label>
                            <select wire:model="comidas_dia" class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg-tertiary px-4 py-2.5 text-sm text-wc-text focus:border-wc-accent focus:outline-none">
                                <option value="">Seleccionar</option>
                                <option value="1_2">1-2 comidas</option>
                                <option value="3">3 comidas</option>
                                <option value="4_5">4-5 comidas</option>
                                <option value="varia">Varia mucho</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-wc-text-tertiary">Experiencia contando macros</label>
                            <select wire:model="experiencia_macros" class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg-tertiary px-4 py-2.5 text-sm text-wc-text focus:border-wc-accent focus:outline-none">
                                <option value="">Seleccionar</option>
                                <option value="nunca">Nunca lo he hecho</option>
                                <option value="intente">Lo intente pero no funciono</option>
                                <option value="experiencia">Tengo experiencia</option>
                                <option value="actualmente">Lo hago actualmente</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-wc-text-tertiary">Alimentos que no comes</label>
                            <textarea wire:model="alimentos_excluir" rows="2" class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg-tertiary px-4 py-2.5 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none" placeholder="Alimentos que no quieres en tu plan..."></textarea>
                        </div>
                    </div>
                </div>
                @endif

                {{-- Step 6: Horarios --}}
                @if($step === 6)
                <div>
                    <h2 class="font-display text-xl tracking-wide text-wc-text">HORARIOS Y HABITOS</h2>
                    <p class="mt-1 text-sm text-wc-text-secondary">Nos ayuda a adaptar el programa a tu vida real.</p>
                    <div class="mt-6 space-y-4">
                        <div>
                            <label class="block text-xs font-medium text-wc-text-tertiary">Horario de trabajo/estudio</label>
                            <textarea wire:model="horario_trabajo" rows="2" class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg-tertiary px-4 py-2.5 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none" placeholder="Lunes a viernes 8am-6pm..."></textarea>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-wc-text-tertiary">Frecuencia comiendo fuera</label>
                            <select wire:model="comer_fuera" class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg-tertiary px-4 py-2.5 text-sm text-wc-text focus:border-wc-accent focus:outline-none">
                                <option value="">Seleccionar</option>
                                <option value="nunca">Casi nunca</option>
                                <option value="algunas">Algunas veces por semana</option>
                                <option value="mayoria">La mayoria de los dias</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-wc-text-tertiary">Nivel de estres actual</label>
                            <select wire:model="nivel_estres" class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg-tertiary px-4 py-2.5 text-sm text-wc-text focus:border-wc-accent focus:outline-none">
                                <option value="">Seleccionar</option>
                                <option value="bajo">Bajo</option>
                                <option value="moderado">Moderado</option>
                                <option value="alto">Alto</option>
                                <option value="muy_alto">Muy alto</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-wc-text-tertiary">Horas promedio de sueno</label>
                            <select wire:model="horas_sueno" class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg-tertiary px-4 py-2.5 text-sm text-wc-text focus:border-wc-accent focus:outline-none">
                                <option value="">Seleccionar</option>
                                <option value="menos_5">Menos de 5 horas</option>
                                <option value="5_6">5-6 horas</option>
                                <option value="7_8">7-8 horas</option>
                                <option value="mas_8">Mas de 8 horas</option>
                            </select>
                        </div>
                    </div>
                </div>
                @endif

                {{-- Step 7: Finales --}}
                @if($step === 7)
                <div>
                    <h2 class="font-display text-xl tracking-wide text-wc-text">ULTIMOS DETALLES</h2>
                    <p class="mt-1 text-sm text-wc-text-secondary">Crea tu cuenta y envia el formulario.</p>
                    <div class="mt-6 space-y-4">
                        <div>
                            <label class="block text-xs font-medium text-wc-text-tertiary">Como conociste WellCore?</label>
                            <select wire:model="como_conocio" class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg-tertiary px-4 py-2.5 text-sm text-wc-text focus:border-wc-accent focus:outline-none">
                                <option value="">Seleccionar</option>
                                <option value="instagram">Instagram</option>
                                <option value="recomendacion">Recomendacion</option>
                                <option value="google">Google</option>
                                <option value="tiktok">TikTok</option>
                                <option value="otro">Otro</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-wc-text-tertiary">Notas adicionales</label>
                            <textarea wire:model="notas" rows="3" class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg-tertiary px-4 py-2.5 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none" placeholder="Algo mas que debamos saber..."></textarea>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-wc-text-tertiary">Contrasena *</label>
                            <input type="password" wire:model="password" class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg-tertiary px-4 py-2.5 text-sm text-wc-text focus:border-wc-accent focus:outline-none" placeholder="Minimo 8 caracteres">
                            @error('password') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-wc-text-tertiary">Confirmar contrasena *</label>
                            <input type="password" wire:model="password_confirmation" class="mt-1 w-full rounded-lg border border-wc-border bg-wc-bg-tertiary px-4 py-2.5 text-sm text-wc-text focus:border-wc-accent focus:outline-none" placeholder="Repite tu contrasena">
                        </div>
                        <div class="flex items-start gap-3">
                            <input type="checkbox" wire:model="terminos" id="terminos" class="mt-1 h-4 w-4 rounded border-wc-border bg-wc-bg-tertiary text-wc-accent focus:ring-wc-accent">
                            <label for="terminos" class="text-sm text-wc-text-secondary">
                                Acepto los <a href="#" class="text-wc-accent hover:underline">terminos de servicio</a> y la <a href="#" class="text-wc-accent hover:underline">politica de privacidad</a> *
                            </label>
                        </div>
                        @error('terminos') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                </div>
                @endif

                {{-- Navigation --}}
                <div class="mt-8 flex items-center justify-between">
                    @if($step > 0)
                        <button type="button" wire:click="previousStep" class="inline-flex items-center gap-2 rounded-lg border border-wc-border px-5 py-2.5 text-sm font-medium text-wc-text-secondary hover:text-wc-text">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
                            Anterior
                        </button>
                    @else
                        <div></div>
                    @endif

                    <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-wc-accent px-6 py-2.5 text-sm font-semibold text-white hover:bg-wc-accent-hover">
                        {{ $step === 7 ? 'Enviar Formulario' : 'Siguiente' }}
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" /></svg>
                    </button>
                </div>
            </form>
        </div>
    @endif
</div>
