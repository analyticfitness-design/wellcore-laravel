<div class="space-y-6" x-data="videoLibrary()">
    <div>
        <h1 class="font-display text-3xl tracking-wide text-wc-text">VIDEO LIBRARY</h1>
        <p class="mt-1 text-sm text-wc-text-secondary">Biblioteca de ejercicios con demostraciones y tecnica correcta.</p>
    </div>

    {{-- Search + filter bar --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
        {{-- Search --}}
        <div class="relative flex-1">
            <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
            </svg>
            <input type="text" x-model="search" placeholder="Buscar ejercicio..." class="w-full rounded-lg border border-wc-border bg-wc-bg-tertiary py-2.5 pl-10 pr-4 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none" />
        </div>

        {{-- Difficulty filter --}}
        <select x-model="difficulty" class="rounded-lg border border-wc-border bg-wc-bg-tertiary px-3 py-2.5 text-sm text-wc-text focus:border-wc-accent focus:outline-none">
            <option value="all">Todos los niveles</option>
            <option value="principiante">Principiante</option>
            <option value="intermedio">Intermedio</option>
            <option value="avanzado">Avanzado</option>
        </select>

        {{-- Equipment filter --}}
        <select x-model="equipment" class="rounded-lg border border-wc-border bg-wc-bg-tertiary px-3 py-2.5 text-sm text-wc-text focus:border-wc-accent focus:outline-none">
            <option value="all">Todo equipo</option>
            <option value="sin_equipo">Sin equipo</option>
            <option value="mancuernas">Mancuernas</option>
            <option value="barra">Barra</option>
            <option value="maquina">Maquina</option>
            <option value="bandas">Bandas</option>
            <option value="cable">Cable</option>
        </select>
    </div>

    {{-- Muscle group tabs --}}
    <div class="flex flex-wrap gap-2">
        <button x-on:click="group = 'all'" :class="group === 'all' ? 'bg-wc-accent text-white' : 'bg-wc-bg-tertiary text-wc-text-secondary hover:text-wc-text'" class="rounded-lg border border-wc-border px-4 py-2 text-sm font-medium transition-colors">
            Todos <span class="ml-1 text-xs opacity-70" x-text="'(' + exercises.length + ')'"></span>
        </button>
        <template x-for="g in muscleGroups" :key="g.id">
            <button x-on:click="group = g.id" :class="group === g.id ? 'bg-wc-accent text-white' : 'bg-wc-bg-tertiary text-wc-text-secondary hover:text-wc-text'" class="rounded-lg border border-wc-border px-4 py-2 text-sm font-medium transition-colors">
                <span x-text="g.icon + ' ' + g.label"></span>
                <span class="ml-1 text-xs opacity-70" x-text="'(' + exercises.filter(e => e.group === g.id).length + ')'"></span>
            </button>
        </template>
    </div>

    {{-- Results count --}}
    <p class="text-xs text-wc-text-tertiary">
        <span x-text="filtered.length"></span> ejercicio<span x-show="filtered.length !== 1">s</span>
        <span x-show="group !== 'all'"> en <span x-text="muscleGroups.find(g => g.id === group)?.label || ''"></span></span>
        <span x-show="search.length > 0"> que coinciden con "<span x-text="search" class="text-wc-accent"></span>"</span>
    </p>

    {{-- Exercise grid --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <template x-for="ex in filtered" :key="ex.id">
            <div class="group cursor-pointer rounded-xl border border-wc-border bg-wc-bg-tertiary transition-all hover:border-wc-accent/40" x-on:click="openExercise(ex)">
                {{-- Video thumbnail placeholder --}}
                <div class="relative aspect-video overflow-hidden rounded-t-xl bg-wc-bg-secondary">
                    <div class="flex h-full items-center justify-center">
                        <div class="text-center">
                            <span class="text-4xl" x-text="muscleGroups.find(g => g.id === ex.group)?.icon || ''"></span>
                            <p class="mt-1 text-xs text-wc-text-tertiary" x-text="ex.name"></p>
                        </div>
                    </div>
                    {{-- Play overlay --}}
                    <div class="absolute inset-0 flex items-center justify-center bg-black/30 opacity-0 transition-opacity group-hover:opacity-100">
                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-wc-accent/90">
                            <svg class="ml-0.5 h-5 w-5 text-white" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M8 5v14l11-7z" />
                            </svg>
                        </div>
                    </div>
                    {{-- Difficulty badge --}}
                    <span class="absolute right-2 top-2 rounded-full px-2 py-0.5 text-[10px] font-semibold" :class="ex.difficulty === 'principiante' ? 'bg-green-500/20 text-green-400' : ex.difficulty === 'intermedio' ? 'bg-yellow-500/20 text-yellow-400' : 'bg-red-500/20 text-red-400'" x-text="ex.difficulty"></span>
                </div>

                {{-- Info --}}
                <div class="p-4">
                    <h3 class="text-sm font-semibold text-wc-text" x-text="ex.name"></h3>
                    <div class="mt-2 flex flex-wrap items-center gap-2">
                        <span class="rounded-md bg-wc-bg-secondary px-2 py-0.5 text-[10px] font-medium text-wc-text-secondary" x-text="muscleGroups.find(g => g.id === ex.group)?.label || ''"></span>
                        <span class="rounded-md bg-wc-bg-secondary px-2 py-0.5 text-[10px] font-medium text-wc-text-tertiary" x-text="ex.equipment"></span>
                    </div>
                </div>
            </div>
        </template>
    </div>

    {{-- Empty state --}}
    <div x-show="filtered.length === 0" class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-12 text-center">
        <svg class="mx-auto h-12 w-12 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
        </svg>
        <p class="mt-3 text-sm text-wc-text-secondary">No se encontraron ejercicios.</p>
        <p class="text-xs text-wc-text-tertiary">Intenta cambiar los filtros o la busqueda.</p>
    </div>

    {{-- Exercise detail modal --}}
    <div x-show="modal" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-4" x-on:click.self="modal = false" x-on:keydown.escape.window="modal = false" x-cloak>
        <div class="w-full max-w-2xl rounded-2xl border border-wc-border bg-wc-bg-secondary" x-show="modal" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="scale-95 opacity-0" x-transition:enter-end="scale-100 opacity-100">
            {{-- Modal header --}}
            <div class="flex items-center justify-between border-b border-wc-border px-6 py-4">
                <h2 class="font-display text-xl tracking-wide text-wc-text" x-text="selected?.name || ''"></h2>
                <button x-on:click="modal = false" class="flex h-8 w-8 items-center justify-center rounded-lg text-wc-text-secondary hover:text-wc-text">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- Modal body --}}
            <div class="max-h-[70vh] overflow-y-auto px-6 py-5 space-y-5">
                {{-- Video placeholder --}}
                <div class="flex aspect-video items-center justify-center rounded-xl bg-wc-bg">
                    <div class="text-center">
                        <span class="text-6xl" x-text="muscleGroups.find(g => g.id === selected?.group)?.icon || ''"></span>
                        <p class="mt-2 text-sm text-wc-text-tertiary">Video demo proximamente</p>
                    </div>
                </div>

                {{-- Badges --}}
                <div class="flex flex-wrap gap-2">
                    <span class="rounded-lg bg-wc-accent/10 px-3 py-1 text-xs font-semibold text-wc-accent" x-text="muscleGroups.find(g => g.id === selected?.group)?.label || ''"></span>
                    <span class="rounded-lg px-3 py-1 text-xs font-semibold" :class="selected?.difficulty === 'principiante' ? 'bg-green-500/10 text-green-400' : selected?.difficulty === 'intermedio' ? 'bg-yellow-500/10 text-yellow-400' : 'bg-red-500/10 text-red-400'" x-text="selected?.difficulty || ''"></span>
                    <span class="rounded-lg bg-wc-bg-tertiary px-3 py-1 text-xs font-medium text-wc-text-secondary" x-text="selected?.equipment || ''"></span>
                </div>

                {{-- Muscles --}}
                <div>
                    <h4 class="text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Musculos trabajados</h4>
                    <div class="mt-2 flex flex-wrap gap-1.5">
                        <template x-for="m in (selected?.muscles || [])" :key="m">
                            <span class="rounded-md border border-wc-border bg-wc-bg px-2.5 py-1 text-xs text-wc-text-secondary" x-text="m"></span>
                        </template>
                    </div>
                </div>

                {{-- Instructions --}}
                <div>
                    <h4 class="text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Instrucciones</h4>
                    <ol class="mt-2 space-y-2">
                        <template x-for="(step, i) in (selected?.steps || [])" :key="i">
                            <li class="flex gap-3 text-sm text-wc-text-secondary">
                                <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-wc-accent/10 text-[10px] font-bold text-wc-accent" x-text="i + 1"></span>
                                <span x-text="step"></span>
                            </li>
                        </template>
                    </ol>
                </div>

                {{-- Tips --}}
                <div x-show="selected?.tips?.length > 0">
                    <h4 class="text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Tips de tecnica</h4>
                    <ul class="mt-2 space-y-1.5">
                        <template x-for="(tip, i) in (selected?.tips || [])" :key="i">
                            <li class="flex items-start gap-2 text-xs text-wc-text-tertiary">
                                <svg class="mt-0.5 h-3 w-3 shrink-0 text-wc-accent" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" />
                                </svg>
                                <span x-text="tip"></span>
                            </li>
                        </template>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <script>
        function videoLibrary() {
            const GROUPS = [
                { id: 'pecho', label: 'Pecho', icon: '💪' },
                { id: 'espalda', label: 'Espalda', icon: '🔙' },
                { id: 'hombros', label: 'Hombros', icon: '🏋️' },
                { id: 'brazos', label: 'Brazos', icon: '💪' },
                { id: 'piernas', label: 'Piernas', icon: '🦵' },
                { id: 'gluteos', label: 'Gluteos', icon: '🍑' },
                { id: 'core', label: 'Core', icon: '🎯' },
                { id: 'cardio', label: 'Cardio', icon: '❤️' },
            ];

            const EXERCISES = [
                // PECHO
                { id: 1, name: 'Press de Banca', group: 'pecho', difficulty: 'intermedio', equipment: 'Barra', muscles: ['Pectoral mayor', 'Deltoides anterior', 'Triceps'], steps: ['Acuestate en el banco con pies firmes en el piso.', 'Agarra la barra con grip ligeramente mas ancho que los hombros.', 'Baja la barra controladamente hasta el pecho medio.', 'Empuja la barra hacia arriba hasta extension completa.'], tips: ['Retrae las escapulas para proteger los hombros.', 'Mantén el arco natural de la espalda baja.', 'No rebotes la barra en el pecho.'] },
                { id: 2, name: 'Flexiones (Push-ups)', group: 'pecho', difficulty: 'principiante', equipment: 'Sin equipo', muscles: ['Pectoral mayor', 'Triceps', 'Core'], steps: ['Posicion de plancha con manos a la anchura de los hombros.', 'Baja el pecho hasta casi tocar el suelo.', 'Empuja hacia arriba manteniendo el cuerpo recto.', 'Repite sin dejar caer las caderas.'], tips: ['Mantén los codos a 45 grados del cuerpo.', 'Aprieta los gluteos para mantener alineacion.'] },
                { id: 3, name: 'Press Inclinado Mancuernas', group: 'pecho', difficulty: 'intermedio', equipment: 'Mancuernas', muscles: ['Pectoral superior', 'Deltoides anterior', 'Triceps'], steps: ['Ajusta el banco a 30-45 grados de inclinacion.', 'Sube las mancuernas a la posicion inicial sobre el pecho.', 'Baja las mancuernas controladamente hasta sentir estiramiento.', 'Empuja hacia arriba juntando ligeramente las mancuernas arriba.'], tips: ['No inclines el banco mas de 45 grados para evitar activar demasiado el hombro.', 'Controla la fase excentrica (bajada) 2-3 segundos.'] },
                { id: 4, name: 'Aperturas con Cable', group: 'pecho', difficulty: 'intermedio', equipment: 'Cable', muscles: ['Pectoral mayor', 'Deltoides anterior'], steps: ['Coloca las poleas a la altura de los hombros.', 'Da un paso al frente con una pierna para estabilidad.', 'Lleva los brazos al frente en arco con codos ligeramente flexionados.', 'Regresa controladamente a la posicion inicial.'], tips: ['Imagina que abrazas un arbol grande.', 'Manten una ligera flexion de codo constante.'] },

                // ESPALDA
                { id: 5, name: 'Dominadas (Pull-ups)', group: 'espalda', difficulty: 'avanzado', equipment: 'Barra', muscles: ['Dorsal ancho', 'Biceps', 'Romboides'], steps: ['Agarra la barra con grip prono (palmas hacia afuera) mas ancho que hombros.', 'Desde extension completa, tira hacia arriba liderando con el pecho.', 'Sube hasta que la barbilla pase la barra.', 'Baja controladamente hasta extension completa.'], tips: ['Inicia el movimiento retrayendo las escapulas.', 'Evita el balanceo del cuerpo (kipping) para maximo beneficio.'] },
                { id: 6, name: 'Remo con Barra', group: 'espalda', difficulty: 'intermedio', equipment: 'Barra', muscles: ['Dorsal ancho', 'Romboides', 'Trapecio medio', 'Biceps'], steps: ['Inclinate hacia adelante ~45 grados con rodillas ligeramente flexionadas.', 'Agarra la barra con grip prono a la anchura de los hombros.', 'Tira la barra hacia el abdomen bajo apretando las escapulas.', 'Baja la barra controladamente.'], tips: ['Mantén la espalda recta durante todo el movimiento.', 'Piensa en llevar los codos hacia atras, no tirar con las manos.'] },
                { id: 7, name: 'Remo con Mancuerna', group: 'espalda', difficulty: 'principiante', equipment: 'Mancuernas', muscles: ['Dorsal ancho', 'Romboides', 'Biceps'], steps: ['Apoya una mano y rodilla en el banco.', 'Con la otra mano toma la mancuerna con brazo extendido.', 'Tira la mancuerna hacia la cadera retrayendo la escapula.', 'Baja controladamente y repite.'], tips: ['No rotes el torso para subir mas peso.', 'Manten el core apretado para estabilidad.'] },
                { id: 8, name: 'Jalon al Pecho', group: 'espalda', difficulty: 'principiante', equipment: 'Maquina', muscles: ['Dorsal ancho', 'Biceps', 'Romboides'], steps: ['Sientate con los muslos asegurados bajo las almohadillas.', 'Agarra la barra ancha con grip prono.', 'Tira la barra hacia el pecho superior sacando pecho.', 'Regresa controladamente arriba.'], tips: ['No te inclines demasiado hacia atras.', 'Aprieta las escapulas al final del movimiento.'] },

                // HOMBROS
                { id: 9, name: 'Press Militar', group: 'hombros', difficulty: 'intermedio', equipment: 'Barra', muscles: ['Deltoides anterior', 'Deltoides lateral', 'Triceps'], steps: ['De pie con la barra a la altura de las claviculas.', 'Empuja la barra verticalmente hasta extension completa.', 'Baja controladamente hasta las claviculas.', 'Mantén el core apretado durante todo el movimiento.'], tips: ['No arquees excesivamente la espalda baja.', 'Exhala al empujar, inhala al bajar.'] },
                { id: 10, name: 'Elevaciones Laterales', group: 'hombros', difficulty: 'principiante', equipment: 'Mancuernas', muscles: ['Deltoides lateral'], steps: ['De pie con mancuernas a los lados.', 'Eleva los brazos lateralmente hasta la altura de los hombros.', 'Mantén una ligera flexion de codo.', 'Baja controladamente sin dejar caer los brazos.'], tips: ['Usa peso moderado para no compensar con impulso.', 'Lidera con los codos, no con las manos.'] },
                { id: 11, name: 'Face Pulls', group: 'hombros', difficulty: 'principiante', equipment: 'Cable', muscles: ['Deltoides posterior', 'Trapecio', 'Rotadores externos'], steps: ['Coloca la polea a la altura de la cara.', 'Agarra la cuerda con grip neutro.', 'Tira hacia la cara separando los extremos de la cuerda.', 'Aprieta las escapulas y rota externamente al final.'], tips: ['Excelente para salud del hombro y postura.', 'Incluye en cada sesion de tren superior.'] },

                // BRAZOS
                { id: 12, name: 'Curl con Barra', group: 'brazos', difficulty: 'principiante', equipment: 'Barra', muscles: ['Biceps braquial', 'Braquial'], steps: ['De pie con la barra en grip supino (palmas hacia arriba).', 'Flexiona los codos llevando la barra hacia los hombros.', 'Mantén los codos pegados al cuerpo.', 'Baja controladamente hasta extension.'], tips: ['No balancees el cuerpo para subir el peso.', 'Aprieta el biceps al tope del movimiento.'] },
                { id: 13, name: 'Extensiones de Triceps', group: 'brazos', difficulty: 'principiante', equipment: 'Cable', muscles: ['Triceps'], steps: ['Agarra la barra o cuerda de la polea alta.', 'Mantén los codos pegados al cuerpo.', 'Extiende los brazos completamente hacia abajo.', 'Regresa controladamente sin mover los codos.'], tips: ['Mantén el torso erguido.', 'No uses impulso del cuerpo.'] },
                { id: 14, name: 'Curl Martillo', group: 'brazos', difficulty: 'principiante', equipment: 'Mancuernas', muscles: ['Braquiorradial', 'Biceps', 'Braquial'], steps: ['De pie con mancuernas en grip neutro (palmas enfrentadas).', 'Flexiona los codos sin rotar los antebrazos.', 'Sube hasta contraccion completa.', 'Baja controladamente.'], tips: ['Excelente para desarrollo del antebrazo.', 'Puedes hacerlo alternando brazos.'] },

                // PIERNAS
                { id: 15, name: 'Sentadilla (Squat)', group: 'piernas', difficulty: 'intermedio', equipment: 'Barra', muscles: ['Cuadriceps', 'Gluteos', 'Isquiotibiales', 'Core'], steps: ['Coloca la barra en la parte superior de la espalda (trapecios).', 'Pies a la anchura de los hombros con puntas ligeramente hacia afuera.', 'Baja flexionando caderas y rodillas como si te sentaras.', 'Baja hasta que los muslos esten paralelos al piso o mas abajo.', 'Sube empujando el piso con los pies.'], tips: ['Las rodillas deben seguir la linea de los pies.', 'Mantén el pecho arriba durante todo el movimiento.', 'Respira profundo antes de bajar (bracing).'] },
                { id: 16, name: 'Peso Muerto Rumano', group: 'piernas', difficulty: 'intermedio', equipment: 'Barra', muscles: ['Isquiotibiales', 'Gluteos', 'Erectores espinales'], steps: ['De pie con la barra al frente, grip a la anchura de los hombros.', 'Empuja las caderas hacia atras manteniendo las piernas casi rectas.', 'Baja la barra a lo largo de las piernas hasta sentir estiramiento.', 'Regresa apretando gluteos al tope.'], tips: ['Mantén la barra pegada a las piernas.', 'No redondees la espalda baja.', 'Siente el estiramiento en los isquiotibiales.'] },
                { id: 17, name: 'Zancadas (Lunges)', group: 'piernas', difficulty: 'principiante', equipment: 'Mancuernas', muscles: ['Cuadriceps', 'Gluteos', 'Isquiotibiales'], steps: ['De pie con mancuernas a los lados.', 'Da un paso largo hacia adelante.', 'Baja la rodilla trasera casi hasta el piso.', 'Empuja con el pie delantero para volver a la posicion inicial.'], tips: ['Mantén el torso erguido.', 'La rodilla delantera no debe pasar la punta del pie.'] },
                { id: 18, name: 'Prensa de Piernas', group: 'piernas', difficulty: 'principiante', equipment: 'Maquina', muscles: ['Cuadriceps', 'Gluteos'], steps: ['Sientate en la prensa con pies a la anchura de los hombros.', 'Baja la plataforma flexionando las rodillas a ~90 grados.', 'Empuja la plataforma sin bloquear completamente las rodillas.', 'Repite con movimiento controlado.'], tips: ['No bloquees las rodillas al extender.', 'Pies mas altos = mas gluteos, mas bajos = mas cuadriceps.'] },

                // GLUTEOS
                { id: 19, name: 'Hip Thrust', group: 'gluteos', difficulty: 'intermedio', equipment: 'Barra', muscles: ['Gluteo mayor', 'Isquiotibiales'], steps: ['Apoya la parte superior de la espalda en un banco.', 'Coloca la barra sobre las caderas (usa pad).', 'Empuja las caderas hacia arriba apretando gluteos.', 'Baja controladamente hasta que los gluteos casi toquen el piso.'], tips: ['Menton al pecho al subir para evitar hiperextension lumbar.', 'Aprieta gluteos 1-2 segundos arriba.', 'El mejor ejercicio para activacion de gluteos segun la ciencia.'] },
                { id: 20, name: 'Sentadilla Sumo', group: 'gluteos', difficulty: 'principiante', equipment: 'Mancuernas', muscles: ['Gluteos', 'Aductores', 'Cuadriceps'], steps: ['Pies mas anchos que los hombros, puntas a ~45 grados.', 'Sostén una mancuerna con ambas manos al frente.', 'Baja manteniendo la espalda recta y rodillas hacia afuera.', 'Sube apretando gluteos y aductores.'], tips: ['Mayor activacion de gluteos e inner thighs que sentadilla regular.', 'Mantén las rodillas alineadas con los pies.'] },
                { id: 21, name: 'Patada de Gluteo (Cable)', group: 'gluteos', difficulty: 'principiante', equipment: 'Cable', muscles: ['Gluteo mayor'], steps: ['Coloca el anclaje de tobillo en la polea baja.', 'Apoyate en la estructura de la maquina.', 'Extiende la pierna hacia atras con el gluteo.', 'Regresa controladamente sin impulso.'], tips: ['Manten una ligera flexion de rodilla.', 'Enfocate en apretar el gluteo, no en subir la pierna alto.'] },

                // CORE
                { id: 22, name: 'Plancha (Plank)', group: 'core', difficulty: 'principiante', equipment: 'Sin equipo', muscles: ['Recto abdominal', 'Transverso', 'Oblicuos'], steps: ['Posicion de plancha sobre los antebrazos.', 'Cuerpo en linea recta de cabeza a pies.', 'Aprieta abdomen y gluteos.', 'Mantén la posicion sin dejar caer las caderas.'], tips: ['No levantes las caderas en forma de V.', 'Respira normalmente durante la plancha.'] },
                { id: 23, name: 'Crunch con Cable', group: 'core', difficulty: 'intermedio', equipment: 'Cable', muscles: ['Recto abdominal'], steps: ['Arrodillate frente a la polea alta con la cuerda.', 'Sostén la cuerda detras de la cabeza.', 'Flexiona el torso hacia abajo contrayendo el abdomen.', 'Regresa controladamente.'], tips: ['No tires con los brazos, el movimiento es del core.', 'Permite agregar carga progresiva al abdomen.'] },
                { id: 24, name: 'Rueda Abdominal', group: 'core', difficulty: 'avanzado', equipment: 'Sin equipo', muscles: ['Recto abdominal', 'Transverso', 'Serrato'], steps: ['De rodillas con la rueda al frente.', 'Rueda hacia adelante extendiendo el cuerpo.', 'Extiende lo mas lejos posible sin tocar el piso.', 'Regresa a la posicion inicial con el core.'], tips: ['Mantén el core apretado todo el tiempo.', 'No arquees la espalda baja.', 'Avanza la distancia gradualmente.'] },

                // CARDIO
                { id: 25, name: 'Burpees', group: 'cardio', difficulty: 'avanzado', equipment: 'Sin equipo', muscles: ['Cuerpo completo', 'Sistema cardiovascular'], steps: ['De pie, baja a posicion de sentadilla con manos en el piso.', 'Lanza los pies hacia atras a posicion de plancha.', 'Realiza una flexion de pecho.', 'Recoge los pies y salta explosivamente con brazos arriba.'], tips: ['Escala la velocidad a tu nivel.', 'Version principiante: elimina la flexion y el salto.'] },
                { id: 26, name: 'Mountain Climbers', group: 'cardio', difficulty: 'principiante', equipment: 'Sin equipo', muscles: ['Core', 'Cuadriceps', 'Hombros'], steps: ['Posicion de plancha alta con brazos extendidos.', 'Lleva una rodilla al pecho rapidamente.', 'Alterna las piernas en movimiento de carrera.', 'Mantén las caderas bajas y estables.'], tips: ['Velocidad controla la intensidad.', 'Mantén el core activado para no rotar las caderas.'] },
                { id: 27, name: 'Saltos de Cuerda', group: 'cardio', difficulty: 'principiante', equipment: 'Sin equipo', muscles: ['Pantorrillas', 'Core', 'Sistema cardiovascular'], steps: ['Sostén la cuerda con ambas manos a la altura de las caderas.', 'Gira la cuerda con las munecas, no con los brazos.', 'Salta ligeramente (2-3 cm del piso).', 'Aterriza suave en la punta de los pies.'], tips: ['Comienza con saltos sencillos antes de dobles.', 'Excelente para calentamiento o finisher.'] },
                { id: 28, name: 'Jumping Jacks', group: 'cardio', difficulty: 'principiante', equipment: 'Sin equipo', muscles: ['Deltoides', 'Pantorrillas', 'Sistema cardiovascular'], steps: ['De pie con brazos a los lados.', 'Salta abriendo piernas y elevando brazos.', 'Regresa de un salto a la posicion inicial.', 'Mantén un ritmo constante.'], tips: ['Aterriza suave con las rodillas ligeramente flexionadas.', 'Ideal para calentamiento y cardio HIIT.'] },

                // EXTRAS - Bandas
                { id: 29, name: 'Caminata Lateral con Banda', group: 'gluteos', difficulty: 'principiante', equipment: 'Bandas', muscles: ['Gluteo medio', 'Gluteo menor'], steps: ['Coloca la banda mini justo arriba de las rodillas.', 'Posicion de media sentadilla con pies a la anchura de los hombros.', 'Da pasos laterales manteniendo tension en la banda.', 'Realiza la misma cantidad de pasos en ambas direcciones.'], tips: ['Mantén las rodillas empujando hacia afuera.', 'Excelente para activacion de gluteos pre-entrenamiento.'] },
                { id: 30, name: 'Pull-Apart con Banda', group: 'espalda', difficulty: 'principiante', equipment: 'Bandas', muscles: ['Romboides', 'Deltoides posterior', 'Trapecio'], steps: ['Sostén la banda al frente con brazos extendidos.', 'Separa las manos tirando la banda hacia los lados.', 'Aprieta las escapulas al final del movimiento.', 'Regresa controladamente.'], tips: ['Ideal para postura y salud del hombro.', 'Puedes hacerlo entre series de press.'] },
            ];

            return {
                muscleGroups: GROUPS,
                exercises: EXERCISES,
                group: 'all',
                search: '',
                difficulty: 'all',
                equipment: 'all',
                modal: false,
                selected: null,

                get filtered() {
                    return this.exercises.filter(ex => {
                        if (this.group !== 'all' && ex.group !== this.group) return false;
                        if (this.difficulty !== 'all' && ex.difficulty !== this.difficulty) return false;
                        if (this.equipment !== 'all') {
                            const eqMap = {
                                'sin_equipo': 'Sin equipo',
                                'mancuernas': 'Mancuernas',
                                'barra': 'Barra',
                                'maquina': 'Maquina',
                                'bandas': 'Bandas',
                                'cable': 'Cable',
                            };
                            if (ex.equipment !== eqMap[this.equipment]) return false;
                        }
                        if (this.search.length > 1) {
                            const s = this.search.toLowerCase();
                            const haystack = (ex.name + ' ' + ex.muscles.join(' ')).toLowerCase();
                            if (!haystack.includes(s)) return false;
                        }
                        return true;
                    });
                },

                openExercise(ex) {
                    this.selected = ex;
                    this.modal = true;
                },
            };
        }
    </script>
</div>
