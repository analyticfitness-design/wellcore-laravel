<div class="space-y-6" x-data="recipeDatabase()">
    <div>
        <h1 class="font-display text-3xl tracking-wide text-wc-text">RECETAS</h1>
        <p class="mt-1 text-sm text-wc-text-secondary">Recetas saludables con macros calculados, filtrables por objetivo y tipo de comida.</p>
    </div>

    {{-- Search + filters --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
        <div class="relative flex-1">
            <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
            </svg>
            <input type="text" x-model="search" placeholder="Buscar receta o ingrediente..." class="w-full rounded-lg border border-wc-border bg-wc-bg-tertiary py-2.5 pl-10 pr-4 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none" />
        </div>
        <select x-model="meal" class="rounded-lg border border-wc-border bg-wc-bg-tertiary px-3 py-2.5 text-sm text-wc-text focus:border-wc-accent focus:outline-none">
            <option value="all">Toda comida</option>
            <option value="desayuno">Desayuno</option>
            <option value="almuerzo">Almuerzo</option>
            <option value="cena">Cena</option>
            <option value="snack">Snack</option>
        </select>
        <select x-model="time" class="rounded-lg border border-wc-border bg-wc-bg-tertiary px-3 py-2.5 text-sm text-wc-text focus:border-wc-accent focus:outline-none">
            <option value="all">Cualquier tiempo</option>
            <option value="15">15 min o menos</option>
            <option value="30">30 min o menos</option>
            <option value="60">60 min o menos</option>
        </select>
    </div>

    {{-- Goal tabs --}}
    <div class="flex flex-wrap gap-2">
        <button x-on:click="goal = 'all'" :class="goal === 'all' ? 'bg-wc-accent text-white' : 'bg-wc-bg-tertiary text-wc-text-secondary hover:text-wc-text'" class="rounded-lg border border-wc-border px-4 py-2 text-sm font-medium transition-colors">
            Todas <span class="ml-1 text-xs opacity-70" x-text="'(' + recipes.length + ')'"></span>
        </button>
        <template x-for="g in goals" :key="g.id">
            <button x-on:click="goal = g.id" :class="goal === g.id ? 'bg-wc-accent text-white' : 'bg-wc-bg-tertiary text-wc-text-secondary hover:text-wc-text'" class="rounded-lg border border-wc-border px-4 py-2 text-sm font-medium transition-colors">
                <span x-text="g.icon + ' ' + g.label"></span>
            </button>
        </template>
    </div>

    {{-- Results count --}}
    <p class="text-xs text-wc-text-tertiary">
        <span x-text="filtered.length"></span> receta<span x-show="filtered.length !== 1">s</span>
        <span x-show="goal !== 'all'"> para <span x-text="goals.find(g => g.id === goal)?.label || ''" class="text-wc-accent"></span></span>
    </p>

    {{-- Recipe grid --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <template x-for="r in filtered" :key="r.id">
            <div class="group cursor-pointer rounded-xl border border-wc-border bg-wc-bg-tertiary transition-all hover:border-wc-accent/40" x-on:click="openRecipe(r)">
                {{-- Image placeholder --}}
                <div class="relative aspect-[4/3] overflow-hidden rounded-t-xl bg-wc-bg-secondary">
                    <div class="flex h-full items-center justify-center">
                        <span class="text-5xl" x-text="r.emoji"></span>
                    </div>
                    {{-- Time badge --}}
                    <span class="absolute left-2 top-2 flex items-center gap-1 rounded-full bg-black/60 px-2 py-0.5 text-[10px] font-medium text-white">
                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                        <span x-text="r.prepTime + ' min'"></span>
                    </span>
                    {{-- Meal badge --}}
                    <span class="absolute right-2 top-2 rounded-full bg-wc-accent/80 px-2 py-0.5 text-[10px] font-semibold text-white" x-text="r.meal"></span>
                </div>

                {{-- Info --}}
                <div class="p-4">
                    <h3 class="text-sm font-semibold text-wc-text" x-text="r.name"></h3>
                    <p class="mt-1 text-xs text-wc-text-tertiary line-clamp-2" x-text="r.description"></p>

                    {{-- Macro bar --}}
                    <div class="mt-3 grid grid-cols-4 gap-1 text-center">
                        <div>
                            <p class="font-data text-sm font-bold text-wc-text" x-text="r.macros.cal"></p>
                            <p class="text-[9px] text-wc-text-tertiary">kcal</p>
                        </div>
                        <div>
                            <p class="font-data text-sm font-bold text-blue-400" x-text="r.macros.protein + 'g'"></p>
                            <p class="text-[9px] text-wc-text-tertiary">Proteina</p>
                        </div>
                        <div>
                            <p class="font-data text-sm font-bold text-yellow-400" x-text="r.macros.carbs + 'g'"></p>
                            <p class="text-[9px] text-wc-text-tertiary">Carbos</p>
                        </div>
                        <div>
                            <p class="font-data text-sm font-bold text-orange-400" x-text="r.macros.fat + 'g'"></p>
                            <p class="text-[9px] text-wc-text-tertiary">Grasa</p>
                        </div>
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
        <p class="mt-3 text-sm text-wc-text-secondary">No se encontraron recetas.</p>
        <p class="text-xs text-wc-text-tertiary">Intenta cambiar los filtros.</p>
    </div>

    {{-- Recipe detail modal --}}
    <div x-show="modal" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-4" x-on:click.self="modal = false" x-on:keydown.escape.window="modal = false" x-cloak>
        <div class="w-full max-w-2xl rounded-2xl border border-wc-border bg-wc-bg-secondary" x-show="modal" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="scale-95 opacity-0" x-transition:enter-end="scale-100 opacity-100">
            {{-- Modal header --}}
            <div class="flex items-center justify-between border-b border-wc-border px-6 py-4">
                <div class="flex items-center gap-3">
                    <span class="text-2xl" x-text="selected?.emoji || ''"></span>
                    <h2 class="font-display text-xl tracking-wide text-wc-text" x-text="selected?.name || ''"></h2>
                </div>
                <button x-on:click="modal = false" class="flex h-8 w-8 items-center justify-center rounded-lg text-wc-text-secondary hover:text-wc-text">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- Modal body --}}
            <div class="max-h-[70vh] overflow-y-auto px-6 py-5 space-y-5">
                {{-- Description --}}
                <p class="text-sm text-wc-text-secondary" x-text="selected?.description || ''"></p>

                {{-- Macro cards --}}
                <div class="grid grid-cols-4 gap-3">
                    <div class="rounded-lg border border-wc-border bg-wc-bg p-3 text-center">
                        <p class="font-data text-xl font-bold text-wc-text" x-text="selected?.macros?.cal || 0"></p>
                        <p class="text-[10px] text-wc-text-tertiary">kcal</p>
                    </div>
                    <div class="rounded-lg border border-blue-500/20 bg-blue-500/5 p-3 text-center">
                        <p class="font-data text-xl font-bold text-blue-400" x-text="(selected?.macros?.protein || 0) + 'g'"></p>
                        <p class="text-[10px] text-blue-400/60">Proteina</p>
                    </div>
                    <div class="rounded-lg border border-yellow-500/20 bg-yellow-500/5 p-3 text-center">
                        <p class="font-data text-xl font-bold text-yellow-400" x-text="(selected?.macros?.carbs || 0) + 'g'"></p>
                        <p class="text-[10px] text-yellow-400/60">Carbos</p>
                    </div>
                    <div class="rounded-lg border border-orange-500/20 bg-orange-500/5 p-3 text-center">
                        <p class="font-data text-xl font-bold text-orange-400" x-text="(selected?.macros?.fat || 0) + 'g'"></p>
                        <p class="text-[10px] text-orange-400/60">Grasa</p>
                    </div>
                </div>

                {{-- Meta --}}
                <div class="flex flex-wrap gap-2">
                    <span class="rounded-lg bg-wc-accent/10 px-3 py-1 text-xs font-semibold text-wc-accent" x-text="selected?.meal || ''"></span>
                    <span class="rounded-lg bg-wc-bg-tertiary px-3 py-1 text-xs font-medium text-wc-text-secondary">
                        <span x-text="selected?.prepTime || 0"></span> min
                    </span>
                    <span class="rounded-lg bg-wc-bg-tertiary px-3 py-1 text-xs font-medium text-wc-text-secondary" x-text="selected?.servings || ''"></span>
                    <template x-for="tag in (selected?.tags || [])" :key="tag">
                        <span class="rounded-lg bg-wc-bg-tertiary px-3 py-1 text-xs font-medium text-wc-text-tertiary" x-text="tag"></span>
                    </template>
                </div>

                {{-- Ingredients --}}
                <div>
                    <h4 class="text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Ingredientes</h4>
                    <ul class="mt-2 space-y-1.5">
                        <template x-for="(ing, i) in (selected?.ingredients || [])" :key="i">
                            <li class="flex items-center gap-2 text-sm text-wc-text-secondary">
                                <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-wc-accent"></span>
                                <span x-text="ing"></span>
                            </li>
                        </template>
                    </ul>
                </div>

                {{-- Instructions --}}
                <div>
                    <h4 class="text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Preparacion</h4>
                    <ol class="mt-2 space-y-3">
                        <template x-for="(step, i) in (selected?.steps || [])" :key="i">
                            <li class="flex gap-3 text-sm text-wc-text-secondary">
                                <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-wc-accent/10 text-[10px] font-bold text-wc-accent" x-text="i + 1"></span>
                                <span x-text="step"></span>
                            </li>
                        </template>
                    </ol>
                </div>

                {{-- Coach tip --}}
                <div x-show="selected?.coachTip" class="rounded-lg border border-wc-accent/20 bg-wc-accent/5 p-4">
                    <p class="text-xs font-semibold text-wc-accent">Tip del Coach</p>
                    <p class="mt-1 text-xs text-wc-text-secondary" x-text="selected?.coachTip || ''"></p>
                </div>
            </div>
        </div>
    </div>

    <script>
        function recipeDatabase() {
            const GOALS = [
                { id: 'perder_grasa', label: 'Perder grasa', icon: '🔥' },
                { id: 'ganar_musculo', label: 'Ganar musculo', icon: '💪' },
                { id: 'mantenimiento', label: 'Mantenimiento', icon: '⚖️' },
                { id: 'energia', label: 'Energia', icon: '⚡' },
            ];

            const RECIPES = [
                // DESAYUNOS
                { id: 1, name: 'Bowl de Avena Proteica', emoji: '🥣', meal: 'Desayuno', goal: 'ganar_musculo', prepTime: 10, servings: '1 porcion', description: 'Bowl de avena con whey protein, frutas y semillas. Alto en proteina para empezar el dia con energia sostenida.', macros: { cal: 420, protein: 35, carbs: 48, fat: 12 }, ingredients: ['60g avena en hojuelas', '1 scoop whey protein vainilla', '200ml leche de almendras', '1/2 banano en rodajas', '10g semillas de chia', '5 fresas picadas', '10g mantequilla de mani'], steps: ['Cocina la avena con la leche de almendras a fuego medio 3-4 min.', 'Retira del fuego y mezcla el whey protein.', 'Sirve en un bowl y agrega banano, fresas y semillas.', 'Termina con un chorrito de mantequilla de mani.'], tags: ['Alta proteina', 'Fibra'], coachTip: 'Prepara la avena la noche anterior en la nevera (overnight oats) para ganar tiempo en la manana.' },
                { id: 2, name: 'Huevos Revueltos con Espinaca', emoji: '🥚', meal: 'Desayuno', goal: 'perder_grasa', prepTime: 10, servings: '1 porcion', description: 'Huevos revueltos con espinaca y tomate. Bajo en carbos, alto en proteina para deficit calorico.', macros: { cal: 280, protein: 24, carbs: 6, fat: 18 }, ingredients: ['3 huevos enteros', '1 taza de espinaca fresca', '1/2 tomate picado', '1 cdta aceite de oliva', 'Sal y pimienta al gusto', 'Oregano opcional'], steps: ['Calienta el aceite en sarten a fuego medio.', 'Saltea la espinaca hasta que se reduzca (1 min).', 'Agrega el tomate y cocina 1 min mas.', 'Vierte los huevos batidos y revuelve hasta coccion deseada.'], tags: ['Low carb', 'Keto friendly'], coachTip: 'Si estas en deficit, usa 2 huevos enteros + 2 claras para reducir 60 kcal sin perder volumen.' },
                { id: 3, name: 'Pancakes de Banano', emoji: '🥞', meal: 'Desayuno', goal: 'energia', prepTime: 15, servings: '2 porciones', description: 'Pancakes esponjosos de banano y avena. Perfectos como pre-entreno por su carga glucemica moderada.', macros: { cal: 350, protein: 18, carbs: 52, fat: 8 }, ingredients: ['1 banano maduro', '2 huevos', '40g avena en hojuelas', '1/2 scoop whey protein', '1 cdta polvo para hornear', 'Canela al gusto', 'Miel o fruta para servir'], steps: ['Licua banano, huevos, avena, protein y polvo para hornear.', 'Calienta sarten antiadherente a fuego medio-bajo.', 'Vierte porciones de mezcla y cocina 2 min por lado.', 'Sirve con miel o fruta fresca.'], tags: ['Pre-entreno', 'Sin gluten opcion'], coachTip: 'Comelos 60-90 min antes de entrenar para tener energia sostenida durante la sesion.' },
                { id: 4, name: 'Yogurt Griego con Granola', emoji: '🫐', meal: 'Desayuno', goal: 'mantenimiento', prepTime: 5, servings: '1 porcion', description: 'Yogurt griego natural con granola casera y arandanos. Balance perfecto de macros.', macros: { cal: 320, protein: 22, carbs: 35, fat: 10 }, ingredients: ['200g yogurt griego natural', '30g granola sin azucar', '50g arandanos frescos', '5g semillas de linaza', '1 cdta miel de abejas'], steps: ['Coloca el yogurt en un bowl.', 'Agrega la granola y los arandanos.', 'Espolvorea las semillas de linaza.', 'Termina con un toque de miel.'], tags: ['Rapido', 'Probioticos'], coachTip: 'El yogurt griego tiene el doble de proteina que el regular. Siempre elige la version natural sin azucar.' },

                // ALMUERZOS
                { id: 5, name: 'Pollo a la Plancha con Arroz', emoji: '🍗', meal: 'Almuerzo', goal: 'ganar_musculo', prepTime: 25, servings: '1 porcion', description: 'Clasico del fitness: pechuga a la plancha con arroz integral y vegetales. Simple, efectivo, comprobado.', macros: { cal: 520, protein: 45, carbs: 55, fat: 10 }, ingredients: ['180g pechuga de pollo', '100g arroz integral (peso crudo)', '1 taza brocoli', '1/2 zanahoria rallada', '1 cdta aceite de oliva', 'Limon, ajo, sal, pimienta'], steps: ['Cocina el arroz integral segun instrucciones del paquete.', 'Sazona el pollo con ajo, limon, sal y pimienta.', 'Cocina en sarten caliente con aceite 5-6 min por lado.', 'Cocina el brocoli al vapor 4 min.', 'Sirve todo junto con zanahoria rallada.'], tags: ['Meal prep', 'Clasico fitness'], coachTip: 'Prepara 4-5 porciones el domingo para tener almuerzo listo toda la semana (meal prep).' },
                { id: 6, name: 'Ensalada de Atun Mediterranea', emoji: '🥗', meal: 'Almuerzo', goal: 'perder_grasa', prepTime: 10, servings: '1 porcion', description: 'Ensalada fresca con atun, aceitunas y vegetales. Baja en calorias, alta en proteina y grasas saludables.', macros: { cal: 310, protein: 32, carbs: 12, fat: 16 }, ingredients: ['1 lata atun en agua (160g)', '2 tazas lechuga mixta', '1/2 pepino en rodajas', '10 tomates cherry', '5 aceitunas negras', '1/4 cebolla morada', '1 cda aceite de oliva', 'Jugo de 1/2 limon'], steps: ['Lava y pica todos los vegetales.', 'Escurre el atun y desmenuzalo.', 'Mezcla todo en un bowl grande.', 'Adreza con aceite de oliva y limon.'], tags: ['Sin coccion', 'Rapido'], coachTip: 'El atun en agua tiene la mitad de calorias que en aceite. Para deficit calorico siempre elige en agua.' },
                { id: 7, name: 'Bowl de Quinoa con Salmon', emoji: '🐟', meal: 'Almuerzo', goal: 'mantenimiento', prepTime: 30, servings: '1 porcion', description: 'Bowl nutritivo con salmon, quinoa y aguacate. Rico en omega-3 y proteina completa.', macros: { cal: 530, protein: 38, carbs: 42, fat: 22 }, ingredients: ['150g filete de salmon', '80g quinoa (peso crudo)', '1/2 aguacate', '1 taza espinaca', '1/2 pepino en cubos', '1 cda salsa de soya', 'Sesamo y limon'], steps: ['Cocina la quinoa en agua con sal por 15 min.', 'Sazona el salmon y cocina en sarten 4 min por lado.', 'Monta el bowl: quinoa base, espinaca, pepino.', 'Coloca el salmon encima con aguacate en laminas.', 'Termina con soya, sesamo y limon.'], tags: ['Omega-3', 'Grasas saludables'], coachTip: 'La quinoa es uno de los pocos granos con proteina completa (todos los aminoacidos esenciales).' },
                { id: 8, name: 'Wrap de Pollo y Vegetales', emoji: '🌯', meal: 'Almuerzo', goal: 'energia', prepTime: 15, servings: '1 porcion', description: 'Wrap integral con pollo desmenuzado y vegetales frescos. Facil de llevar y comer en cualquier parte.', macros: { cal: 410, protein: 35, carbs: 38, fat: 14 }, ingredients: ['1 tortilla integral grande', '150g pollo desmenuzado', '1/4 aguacate', '1/2 tomate en rodajas', '1/4 taza zanahoria rallada', 'Lechuga', '1 cda hummus'], steps: ['Unta el hummus en la tortilla.', 'Coloca la lechuga como base.', 'Agrega el pollo, tomate, zanahoria y aguacate.', 'Enrolla firme doblando los extremos.', 'Corta a la mitad para servir.'], tags: ['Portable', 'Para llevar'], coachTip: 'Perfecto para comer en la oficina. Preparalo en la manana y envuelvelo en papel aluminio.' },

                // CENAS
                { id: 9, name: 'Salmon al Horno con Vegetales', emoji: '🐟', meal: 'Cena', goal: 'perder_grasa', prepTime: 30, servings: '1 porcion', description: 'Salmon al horno con especias y vegetales asados. Alto en omega-3 y bajo en carbos para la noche.', macros: { cal: 380, protein: 36, carbs: 14, fat: 20 }, ingredients: ['180g filete de salmon', '1 taza esparragos', '1/2 pimenton rojo', '1/2 calabacin en rodajas', '1 cda aceite de oliva', 'Ajo en polvo, paprika, sal'], steps: ['Precalienta el horno a 200C.', 'Coloca salmon y vegetales en bandeja con aceite y especias.', 'Hornea 18-22 min hasta que el salmon este cocido.', 'Sirve directamente de la bandeja.'], tags: ['Low carb', 'Omega-3', 'Una bandeja'], coachTip: 'Las grasas del salmon no te engordan — los omega-3 son antiinflamatorios y mejoran la recuperacion muscular.' },
                { id: 10, name: 'Pechuga Rellena de Espinaca', emoji: '🍗', meal: 'Cena', goal: 'ganar_musculo', prepTime: 35, servings: '1 porcion', description: 'Pechuga de pollo rellena con espinaca y queso. Elegante, alta en proteina y sorprendentemente facil.', macros: { cal: 420, protein: 48, carbs: 5, fat: 22 }, ingredients: ['200g pechuga de pollo', '1 taza espinaca', '30g queso mozzarella', '1 diente de ajo picado', '1 cdta aceite de oliva', 'Sal, pimienta, paprika'], steps: ['Corta la pechuga por la mitad horizontalmente sin separar completamente.', 'Saltea el ajo y la espinaca hasta que se reduzca.', 'Rellena la pechuga con espinaca y queso.', 'Cierra con palillos y sazona por fuera.', 'Cocina en sarten 6 min por lado a fuego medio.'], tags: ['Alta proteina', 'Low carb'], coachTip: '48g de proteina en una sola comida. Ideal como cena post-entrenamiento nocturno.' },
                { id: 11, name: 'Sopa de Lentejas', emoji: '🍲', meal: 'Cena', goal: 'mantenimiento', prepTime: 40, servings: '3 porciones', description: 'Sopa reconfortante de lentejas con vegetales. Rica en fibra, hierro y proteina vegetal.', macros: { cal: 340, protein: 22, carbs: 48, fat: 6 }, ingredients: ['200g lentejas secas', '1 zanahoria picada', '1 papa pequena en cubos', '1/2 cebolla picada', '2 dientes de ajo', '1 tomate rallado', 'Comino, cilantro, sal'], steps: ['Remoja las lentejas 30 min y escurre.', 'Sofrie cebolla y ajo hasta dorar.', 'Agrega tomate, zanahoria y papa.', 'Anade las lentejas con 4 tazas de agua.', 'Cocina a fuego medio 25-30 min hasta que las lentejas esten tiernas.', 'Sazona con comino, cilantro y sal.'], tags: ['Vegetariano', 'Fibra', 'Batch cooking'], coachTip: 'Las lentejas son la proteina vegetal mas economica del mercado. 200g secas rinden 3 porciones completas.' },
                { id: 12, name: 'Tacos de Carne Magra', emoji: '🌮', meal: 'Cena', goal: 'energia', prepTime: 20, servings: '2 porciones', description: 'Tacos con carne molida magra 95/5 y toppings frescos. Comida divertida sin sacrificar los macros.', macros: { cal: 440, protein: 36, carbs: 34, fat: 18 }, ingredients: ['200g carne molida 95% magra', '4 tortillas de maiz', '1/2 cebolla picada', '1 tomate picado', 'Cilantro fresco', '1/2 aguacate', 'Limon, sal, comino'], steps: ['Cocina la carne con cebolla, comino y sal hasta dorar.', 'Calienta las tortillas en sarten seco.', 'Arma los tacos con carne, tomate, cilantro y aguacate.', 'Termina con un chorrito de limon.'], tags: ['Divertido', 'Social'], coachTip: 'La carne 95/5 tiene la mitad de grasa que la 80/20 pero la misma proteina. Vale la diferencia de precio.' },

                // SNACKS
                { id: 13, name: 'Batido Post-Entreno', emoji: '🥤', meal: 'Snack', goal: 'ganar_musculo', prepTime: 5, servings: '1 porcion', description: 'Batido rapido de whey protein con banano y avena. Ventana anabolica: tomar dentro de 30 min post-entreno.', macros: { cal: 380, protein: 35, carbs: 45, fat: 6 }, ingredients: ['1 scoop whey protein chocolate', '1 banano congelado', '30g avena', '250ml leche de almendras', '5 cubos de hielo'], steps: ['Agrega todos los ingredientes a la licuadora.', 'Licua a velocidad alta por 30 segundos.', 'Sirve inmediatamente.'], tags: ['Post-entreno', 'Rapido'], coachTip: 'El banano congelado le da textura de milkshake. Congela bananos maduros cortados en zip-lock para tenerlos siempre listos.' },
                { id: 14, name: 'Bolitas de Energia', emoji: '🟤', meal: 'Snack', goal: 'energia', prepTime: 15, servings: '10 bolitas', description: 'Bolitas de avena, mantequilla de mani y chocolate. Sin hornear, perfectas como pre-entreno rapido.', macros: { cal: 95, protein: 4, carbs: 10, fat: 5 }, ingredients: ['100g avena', '60g mantequilla de mani', '40g miel', '20g chips de chocolate oscuro', '1 cda semillas de chia', '1 cdta extracto de vainilla'], steps: ['Mezcla todos los ingredientes en un bowl.', 'Refrigera la mezcla 15 min para que sea mas facil de moldear.', 'Forma bolitas del tamano de una nuez con las manos.', 'Guarda en contenedor hermetico en la nevera.'], tags: ['Sin horno', 'Batch', 'Portable'], coachTip: 'Duran 7 dias en la nevera. Prepara un batch el domingo y lleva 2 al gym como pre-entreno.' },
                { id: 15, name: 'Manzana con Mantequilla de Mani', emoji: '🍎', meal: 'Snack', goal: 'mantenimiento', prepTime: 3, servings: '1 porcion', description: 'El snack mas simple y efectivo del fitness. Carbos de la fruta + grasas saludables + proteina.', macros: { cal: 250, protein: 7, carbs: 30, fat: 14 }, ingredients: ['1 manzana mediana', '1.5 cdas mantequilla de mani natural'], steps: ['Corta la manzana en laminas.', 'Unta cada lamina con mantequilla de mani.', 'Listo.'], tags: ['3 minutos', 'Sin coccion'], coachTip: 'Elige mantequilla de mani que solo tenga un ingrediente: mani. Evita las que agregan aceites y azucares.' },
                { id: 16, name: 'Palitos de Vegetales con Hummus', emoji: '🥕', meal: 'Snack', goal: 'perder_grasa', prepTime: 5, servings: '1 porcion', description: 'Vegetales crudos con hummus casero. Volumen alto, calorias bajas. Perfecto para deficit sin pasar hambre.', macros: { cal: 180, protein: 8, carbs: 22, fat: 8 }, ingredients: ['1 zanahoria en palitos', '1 pepino en palitos', '1/2 pimenton en tiras', '3 tallos de apio', '60g hummus'], steps: ['Corta todos los vegetales en palitos.', 'Sirve con el hummus al centro.', 'Sumerge y disfruta.'], tags: ['Volumen', 'Bajo en calorias'], coachTip: 'Puedes comer una bandeja entera por 180 kcal. Cuando sientas ansiedad, este snack te salva el deficit.' },

                // EXTRAS
                { id: 17, name: 'Arepa Fitness de Pollo', emoji: '🫓', meal: 'Desayuno', goal: 'ganar_musculo', prepTime: 20, servings: '1 porcion', description: 'Arepa de maiz rellena de pollo desmenuzado, aguacate y queso. Adaptacion fitness del clasico LATAM.', macros: { cal: 450, protein: 38, carbs: 40, fat: 16 }, ingredients: ['80g harina de maiz precocida', '120g pollo desmenuzado', '1/4 aguacate', '20g queso rallado', 'Sal al gusto'], steps: ['Amasa la harina con agua tibia y sal hasta consistencia suave.', 'Forma la arepa y cocina en sarten 4 min por lado.', 'Abre y rellena con pollo, aguacate y queso.'], tags: ['LATAM', 'Clasico adaptado'], coachTip: 'La harina de maiz precocida tiene menos procesamiento que la harina de trigo. Buena fuente de carbos complejos.' },
                { id: 18, name: 'Bowl de Arroz con Carne y Frijoles', emoji: '🍚', meal: 'Almuerzo', goal: 'ganar_musculo', prepTime: 30, servings: '1 porcion', description: 'Bowl completo con arroz, carne molida sazonada y frijoles negros. Proteina de dos fuentes, carbos complejos.', macros: { cal: 560, protein: 42, carbs: 58, fat: 16 }, ingredients: ['100g arroz integral', '150g carne molida magra', '80g frijoles negros cocidos', '1/2 tomate picado', 'Cilantro fresco', '1/2 limon', 'Comino, ajo, sal'], steps: ['Cocina el arroz integral.', 'Saltea la carne con ajo, comino y sal hasta dorar.', 'Calienta los frijoles con un poco de su liquido.', 'Monta el bowl: arroz, carne, frijoles, tomate y cilantro.', 'Exprime limon encima.'], tags: ['LATAM', 'Proteina completa', 'Meal prep'], coachTip: 'Arroz + frijoles = proteina completa. Los aminoacidos que le faltan al arroz, los tiene el frijol y viceversa.' },
                { id: 19, name: 'Tortilla Espanola Fit', emoji: '🥚', meal: 'Cena', goal: 'mantenimiento', prepTime: 25, servings: '2 porciones', description: 'Version fitness de la tortilla espanola. Menos aceite, misma satisfaccion. Perfecta para cenar ligero.', macros: { cal: 320, protein: 26, carbs: 22, fat: 14 }, ingredients: ['4 huevos', '1 papa mediana en laminas finas', '1/2 cebolla en laminas', '1 cda aceite de oliva', 'Sal al gusto'], steps: ['Cocina las papas y cebolla en sarten con aceite a fuego bajo 10 min.', 'Bate los huevos con sal.', 'Mezcla las papas con los huevos batidos.', 'Vierte en sarten y cocina a fuego bajo 5 min.', 'Voltea con ayuda de un plato y cocina 3 min mas.'], tags: ['Clasico adaptado', 'Economico'], coachTip: 'La version fit usa 1 cda de aceite vs las 3-4 de la receta tradicional. Misma textura, menos grasa.' },
                { id: 20, name: 'Smoothie Verde Detox', emoji: '🥬', meal: 'Snack', goal: 'perder_grasa', prepTime: 5, servings: '1 porcion', description: 'Smoothie verde con espinaca, pepino y jengibre. Bajo en calorias, alto en micronutrientes y antiinflamatorio.', macros: { cal: 120, protein: 3, carbs: 24, fat: 2 }, ingredients: ['1 taza espinaca', '1/2 pepino', '1 manzana verde', '1 cm jengibre fresco', '1/2 limon (jugo)', '200ml agua fria'], steps: ['Agrega todos los ingredientes a la licuadora.', 'Licua hasta obtener consistencia suave.', 'Sirve con hielo si deseas.'], tags: ['Detox', 'Micronutrientes', 'Antiinflamatorio'], coachTip: 'No lo uses como reemplazo de comida. Es un complemento — tus comidas principales deben tener proteina solida.' },
            ];

            return {
                goals: GOALS,
                recipes: RECIPES,
                goal: 'all',
                search: '',
                meal: 'all',
                time: 'all',
                modal: false,
                selected: null,

                get filtered() {
                    return this.recipes.filter(r => {
                        if (this.goal !== 'all' && r.goal !== this.goal) return false;
                        if (this.meal !== 'all' && r.meal.toLowerCase() !== this.meal) return false;
                        if (this.time !== 'all' && r.prepTime > parseInt(this.time)) return false;
                        if (this.search.length > 1) {
                            const s = this.search.toLowerCase();
                            const haystack = (r.name + ' ' + r.description + ' ' + r.ingredients.join(' ') + ' ' + (r.tags || []).join(' ')).toLowerCase();
                            if (!haystack.includes(s)) return false;
                        }
                        return true;
                    });
                },

                openRecipe(r) {
                    this.selected = r;
                    this.modal = true;
                },
            };
        }
    </script>
</div>
