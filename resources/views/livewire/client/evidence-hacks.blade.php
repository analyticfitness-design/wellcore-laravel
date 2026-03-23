<div class="space-y-6" x-data="evidenceHacks()">
    <div>
        <h1 class="font-display text-3xl tracking-wide text-wc-text">EVIDENCE-BASED HACKS</h1>
        <p class="mt-1 text-sm text-wc-text-secondary">Estrategias respaldadas por ciencia para optimizar tu rendimiento, recuperacion y bienestar.</p>
    </div>

    {{-- Search + filter --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
        <div class="relative flex-1">
            <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
            </svg>
            <input type="text" x-model="search" placeholder="Buscar hack, tema o autor..." class="w-full rounded-lg border border-wc-border bg-wc-bg-tertiary py-2.5 pl-10 pr-4 text-sm text-wc-text placeholder-wc-text-tertiary focus:border-wc-accent focus:outline-none" />
        </div>
    </div>

    {{-- Category tabs --}}
    <div class="flex flex-wrap gap-2">
        <button x-on:click="category = 'all'" :class="category === 'all' ? 'bg-wc-accent text-white' : 'bg-wc-bg-tertiary text-wc-text-secondary hover:text-wc-text'" class="rounded-lg border border-wc-border px-4 py-2 text-sm font-medium transition-colors">
            Todos <span class="ml-1 text-xs opacity-70" x-text="'(' + hacks.length + ')'"></span>
        </button>
        <template x-for="c in categories" :key="c.id">
            <button x-on:click="category = c.id" :class="category === c.id ? 'bg-wc-accent text-white' : 'bg-wc-bg-tertiary text-wc-text-secondary hover:text-wc-text'" class="rounded-lg border border-wc-border px-4 py-2 text-sm font-medium transition-colors">
                <span x-text="c.icon + ' ' + c.label"></span>
                <span class="ml-1 text-xs opacity-70" x-text="'(' + hacks.filter(h => h.category === c.id).length + ')'"></span>
            </button>
        </template>
    </div>

    {{-- Results count --}}
    <p class="text-xs text-wc-text-tertiary">
        <span x-text="filtered.length"></span> hack<span x-show="filtered.length !== 1">s</span>
        <span x-show="category !== 'all'"> en <span x-text="categories.find(c => c.id === category)?.label || ''" class="text-wc-accent"></span></span>
    </p>

    {{-- Hacks grid --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <template x-for="hack in filtered" :key="hack.id">
            <div class="group cursor-pointer rounded-xl border border-wc-border bg-wc-bg-tertiary p-5 transition-all hover:border-wc-accent/40" x-on:click="openHack(hack)">
                <div class="flex items-start gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg text-lg"
                         :class="{
                             'bg-indigo-500/10': hack.category === 'sueno',
                             'bg-green-500/10': hack.category === 'nutricion',
                             'bg-orange-500/10': hack.category === 'training',
                             'bg-blue-500/10': hack.category === 'recovery',
                             'bg-purple-500/10': hack.category === 'stress'
                         }">
                        <span x-text="hack.icon"></span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="text-sm font-semibold text-wc-text" x-text="hack.title"></h3>
                        <p class="mt-1 text-xs text-wc-text-tertiary line-clamp-2" x-text="hack.description"></p>
                    </div>
                </div>

                {{-- Source badge --}}
                <div class="mt-3 flex items-center justify-between">
                    <span class="rounded-full px-2 py-0.5 text-[10px] font-medium"
                          :class="{
                              'bg-indigo-500/10 text-indigo-400': hack.category === 'sueno',
                              'bg-green-500/10 text-green-400': hack.category === 'nutricion',
                              'bg-orange-500/10 text-orange-400': hack.category === 'training',
                              'bg-blue-500/10 text-blue-400': hack.category === 'recovery',
                              'bg-purple-500/10 text-purple-400': hack.category === 'stress'
                          }"
                          x-text="categories.find(c => c.id === hack.category)?.label || ''"></span>
                    <span class="text-[10px] text-wc-text-tertiary" x-text="hack.source.author + ' (' + hack.source.year + ')'"></span>
                </div>
            </div>
        </template>
    </div>

    {{-- Empty state --}}
    <div x-show="filtered.length === 0" class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-12 text-center">
        <svg class="mx-auto h-12 w-12 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
        </svg>
        <p class="mt-3 text-sm text-wc-text-secondary">No se encontraron hacks con esos criterios.</p>
        <button x-on:click="search = ''; category = 'all'" class="mt-2 text-xs text-wc-accent hover:underline">Limpiar filtros</button>
    </div>

    {{-- Detail Modal --}}
    <div x-show="selectedHack" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        {{-- Overlay --}}
        <div class="absolute inset-0 bg-black/60" x-on:click="selectedHack = null"></div>

        {{-- Modal content --}}
        <div class="relative w-full max-w-lg max-h-[85vh] overflow-y-auto rounded-2xl border border-wc-border bg-wc-bg-secondary p-6 shadow-xl"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95">

            {{-- Close button --}}
            <button x-on:click="selectedHack = null" class="absolute right-4 top-4 flex h-8 w-8 items-center justify-center rounded-lg text-wc-text-tertiary hover:text-wc-text transition-colors">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
            </button>

            {{-- Header --}}
            <div class="flex items-center gap-3">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl text-2xl"
                     :class="{
                         'bg-indigo-500/10': selectedHack?.category === 'sueno',
                         'bg-green-500/10': selectedHack?.category === 'nutricion',
                         'bg-orange-500/10': selectedHack?.category === 'training',
                         'bg-blue-500/10': selectedHack?.category === 'recovery',
                         'bg-purple-500/10': selectedHack?.category === 'stress'
                     }">
                    <span x-text="selectedHack?.icon"></span>
                </div>
                <div>
                    <h2 class="text-lg font-semibold text-wc-text" x-text="selectedHack?.title"></h2>
                    <span class="rounded-full px-2 py-0.5 text-[10px] font-medium"
                          :class="{
                              'bg-indigo-500/10 text-indigo-400': selectedHack?.category === 'sueno',
                              'bg-green-500/10 text-green-400': selectedHack?.category === 'nutricion',
                              'bg-orange-500/10 text-orange-400': selectedHack?.category === 'training',
                              'bg-blue-500/10 text-blue-400': selectedHack?.category === 'recovery',
                              'bg-purple-500/10 text-purple-400': selectedHack?.category === 'stress'
                          }"
                          x-text="categories.find(c => c.id === selectedHack?.category)?.label"></span>
                </div>
            </div>

            {{-- Description --}}
            <div class="mt-4">
                <h3 class="text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">Que hacer</h3>
                <p class="mt-1 text-sm text-wc-text" x-text="selectedHack?.description"></p>
            </div>

            {{-- Scientific explanation --}}
            <div class="mt-4 rounded-xl bg-wc-bg-tertiary p-4">
                <h3 class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-wc-text-tertiary">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v5.714a2.25 2.25 0 0 1-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 0 1 4.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0 1 12 15a9.065 9.065 0 0 0-6.23.693L5 14.5m14.8.8 1.402 1.402c1.232 1.232.65 3.318-1.067 3.611A48.309 48.309 0 0 1 12 21c-2.773 0-5.491-.235-8.135-.687-1.718-.293-2.3-2.379-1.067-3.61L5 14.5" /></svg>
                    Por que funciona
                </h3>
                <p class="mt-2 text-sm leading-relaxed text-wc-text-secondary" x-text="selectedHack?.explanation"></p>
            </div>

            {{-- Source reference --}}
            <div class="mt-4 flex items-start gap-3 rounded-lg border border-wc-border bg-wc-bg p-3">
                <svg class="h-4 w-4 mt-0.5 shrink-0 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" /></svg>
                <div>
                    <p class="text-xs font-semibold text-wc-text" x-text="selectedHack?.source.author"></p>
                    <p class="text-[11px] text-wc-text-secondary italic" x-text="selectedHack?.source.journal + ' (' + selectedHack?.source.year + ')'"></p>
                </div>
            </div>
        </div>
    </div>
</div>

@script
<script>
Alpine.data('evidenceHacks', () => ({
        category: 'all',
        search: '',
        selectedHack: null,

        categories: [
            { id: 'sueno', label: 'Sueno', icon: '🌙' },
            { id: 'nutricion', label: 'Nutricion', icon: '🥗' },
            { id: 'training', label: 'Training', icon: '🏋️' },
            { id: 'recovery', label: 'Recovery', icon: '💆' },
            { id: 'stress', label: 'Stress', icon: '🧘' }
        ],

        hacks: [
            // === SUENO ===
            {
                id: 1, category: 'sueno', icon: '☀️',
                title: 'Luz solar en los primeros 30 min',
                description: 'Exponerte a luz solar directa en los primeros 30 minutos despues de despertar para anclar tu reloj circadiano.',
                explanation: 'La luz solar matutina activa las celulas ganglionares retinales intrinsecamente fotosensibles (ipRGCs) que envian senales al nucleo supraquiasmatico, reseteando tu reloj biologico. Esto adelanta la liberacion de cortisol matutino y programa la liberacion de melatonina 14-16 horas despues, mejorando drasticamente la calidad del sueno.',
                source: { author: 'Huberman, A.D.', journal: 'Neuron, Cell Press', year: 2021 }
            },
            {
                id: 2, category: 'sueno', icon: '🌡️',
                title: 'Cuarto a 18-19°C para dormir',
                description: 'Mantener la temperatura de tu cuarto entre 18-19°C (65-67°F) para optimizar el sueno profundo.',
                explanation: 'Tu cuerpo necesita reducir su temperatura central en ~1°C para iniciar el sueno. Un ambiente fresco facilita esta caida termica. Estudios muestran que dormir en habitaciones frescas aumenta el porcentaje de sueno de ondas lentas (deep sleep) hasta en un 20%, que es la fase mas restaurativa para la recuperacion muscular.',
                source: { author: 'Walker, M.', journal: 'Why We Sleep, Scribner', year: 2017 }
            },
            {
                id: 3, category: 'sueno', icon: '📱',
                title: 'No pantallas 1h antes de dormir',
                description: 'Evitar pantallas con luz azul al menos 60 minutos antes de acostarte.',
                explanation: 'La luz azul de pantallas (460-480nm) suprime la produccion de melatonina hasta en un 50% y retrasa su liberacion en hasta 3 horas. Esto reduce el sueno REM y afecta la consolidacion de memoria motora, critica para el aprendizaje de patrones de movimiento en el entrenamiento.',
                source: { author: 'Chang, A.M. et al.', journal: 'Proceedings of the National Academy of Sciences', year: 2015 }
            },
            {
                id: 4, category: 'sueno', icon: '💊',
                title: 'Magnesio glicinato antes de dormir',
                description: 'Tomar 200-400mg de magnesio glicinato 30-60 minutos antes de acostarte.',
                explanation: 'El magnesio actua como agonista de receptores GABA-A, el principal neurotransmisor inhibitorio del sistema nervioso. La forma glicinato tiene mayor biodisponibilidad y el aminoacido glicina adicional tiene propiedades sedantes propias. Estudios muestran mejora significativa en eficiencia del sueno y reduccion de despertares nocturnos.',
                source: { author: 'Abbasi, B. et al.', journal: 'Journal of Research in Medical Sciences', year: 2012 }
            },
            {
                id: 5, category: 'sueno', icon: '⏰',
                title: 'Protocolo 10-3-2-1',
                description: '10h sin cafeina, 3h sin comida, 2h sin trabajo, 1h sin pantallas antes de dormir.',
                explanation: 'La cafeina tiene una vida media de 5-6 horas pero sus metabolitos activos persisten hasta 10h. La digestion activa aumenta la temperatura core, antagonizando la caida termica necesaria para dormir. El trabajo activa el sistema nervioso simpatico y las pantallas suprimen melatonina. Este protocolo sistematiza la higiene del sueno en una regla simple.',
                source: { author: 'Irish, L.A. et al.', journal: 'Sleep Medicine Reviews', year: 2015 }
            },

            // === NUTRICION ===
            {
                id: 6, category: 'nutricion', icon: '🥩',
                title: 'Proteina en cada comida (20-40g)',
                description: 'Distribuir la ingesta de proteina en 20-40g por comida, 3-5 veces al dia.',
                explanation: 'La sintesis de proteina muscular (MPS) se maximiza con ~0.4g/kg por comida. Distribuir la proteina en lugar de concentrarla aumenta la MPS total en 24h hasta un 25%. El umbral de leucina (~2.5g) necesario para activar mTOR se alcanza consistentemente con 20-40g de proteina de alta calidad por sesion.',
                source: { author: 'Schoenfeld, B.J. & Aragon, A.A.', journal: 'Journal of the International Society of Sports Nutrition', year: 2018 }
            },
            {
                id: 7, category: 'nutricion', icon: '💧',
                title: 'Agua fria al despertar',
                description: 'Beber 500ml de agua fria en los primeros 15 minutos despues de despertar.',
                explanation: 'Despues de 7-8h de sueno, tu cuerpo esta deshidratado en un 1-2%. Esta deshidratacion leve reduce el rendimiento cognitivo un 12% y la fuerza un 2-3%. El agua fria ademas activa la termogenesis: tu cuerpo gasta energia calentando el agua a temperatura corporal, aumentando la tasa metabolica un 30% durante 30-40 minutos.',
                source: { author: 'Boschmann, M. et al.', journal: 'Journal of Clinical Endocrinology & Metabolism', year: 2003 }
            },
            {
                id: 8, category: 'nutricion', icon: '🫘',
                title: 'Creatina 3-5g diarios',
                description: 'Suplementar con 3-5g de monohidrato de creatina todos los dias, sin necesidad de fase de carga.',
                explanation: 'La creatina es el suplemento con mayor evidencia en ciencia del deporte. Aumenta las reservas de fosfocreatina muscular, mejorando el rendimiento en ejercicios de alta intensidad un 5-15%. Tambien tiene beneficios cognitivos (mejora memoria de trabajo), aumenta la hidratacion celular y puede tener efectos neuroprotectores. Es segura a largo plazo.',
                source: { author: 'Kreider, R.B. et al.', journal: 'Journal of the International Society of Sports Nutrition', year: 2017 }
            },
            {
                id: 9, category: 'nutricion', icon: '☕',
                title: 'Cafeina pre-entreno estrategica',
                description: 'Consumir 3-6mg/kg de cafeina 60 minutos antes del entrenamiento.',
                explanation: 'La cafeina bloquea receptores de adenosina A1 y A2A, reduciendo la percepcion de esfuerzo en un 5-6% (RPE). Aumenta la oxidacion de grasa, mejora la fuerza maximal en un 3-5% y la resistencia muscular en un 8-14%. El timing de 60 minutos permite alcanzar la concentracion plasmatica maxima. Ciclar (2 semanas on, 1 off) previene tolerancia.',
                source: { author: 'Goldstein, E.R. et al.', journal: 'Journal of the International Society of Sports Nutrition', year: 2010 }
            },
            {
                id: 10, category: 'nutricion', icon: '🥜',
                title: 'Masticar 25-40 veces cada bocado',
                description: 'Masticar cada bocado entre 25 y 40 veces antes de tragar para mejorar saciedad y digestion.',
                explanation: 'La masticacion prolongada aumenta la liberacion de hormonas de saciedad (GLP-1, PYY) y reduce los niveles de grelina (hormona del hambre). Estudios muestran una reduccion del 12% en ingesta calorica cuando se mastica 40 vs 15 veces. Tambien mejora la absorcion de nutrientes al aumentar la superficie de contacto con enzimas digestivas.',
                source: { author: 'Li, J. et al.', journal: 'American Journal of Clinical Nutrition', year: 2011 }
            },

            // === TRAINING ===
            {
                id: 11, category: 'training', icon: '📊',
                title: 'Sobrecarga progresiva semanal',
                description: 'Aumentar peso, repeticiones o volumen cada semana de forma sistematica en tus ejercicios principales.',
                explanation: 'El principio de sobrecarga progresiva es el mecanismo fundamental de la adaptacion muscular. Sin un estimulo creciente, el musculo se adapta y detiene su crecimiento (principio de acomodacion). Incrementos del 2-5% en carga semanal o 1-2 repeticiones adicionales por serie son suficientes para mantener la progresion continua sin riesgo excesivo de lesion.',
                source: { author: 'Kraemer, W.J. & Ratamess, N.A.', journal: 'Medicine & Science in Sports & Exercise', year: 2004 }
            },
            {
                id: 12, category: 'training', icon: '⏱️',
                title: 'Tempo excentrico 3-4 segundos',
                description: 'Controlar la fase excentrica (bajar el peso) durante 3-4 segundos en ejercicios de hipertrofia.',
                explanation: 'La fase excentrica genera mayor tension mecanica por fibra muscular con menor costo metabolico. Un tempo de 3-4s en la excentrica aumenta el tiempo bajo tension total y maximiza el dano muscular controlado, estimulo clave para la hipertrofia. Esto recluta mas unidades motoras de fibras tipo II y aumenta la respuesta de IGF-1 local.',
                source: { author: 'Schoenfeld, B.J. et al.', journal: 'European Journal of Sport Science', year: 2017 }
            },
            {
                id: 13, category: 'training', icon: '⏸️',
                title: 'Descanso 2-3 min para fuerza',
                description: 'Descansar 2-3 minutos entre series en ejercicios compuestos de fuerza.',
                explanation: 'La resintesis de ATP y fosfocreatina requiere 2-3 minutos para alcanzar el 85-95%. Descansos mas cortos limitan la capacidad de generar fuerza maxima en la siguiente serie, reduciendo el estimulo mecanico. Estudios muestran que descansos de 3 min producen mayor hipertrofia que 1 min, contrario a la creencia popular del "pump".',
                source: { author: 'Schoenfeld, B.J. et al.', journal: 'Journal of Strength and Conditioning Research', year: 2016 }
            },
            {
                id: 14, category: 'training', icon: '🎯',
                title: 'No todas las series al fallo',
                description: 'Entrenar 1-3 RIR (repeticiones en reserva) en la mayoria de series, y solo llegar al fallo en la ultima serie.',
                explanation: 'Entrenar consistentemente al fallo muscular aumenta la fatiga central y periferica desproporcionadamente, requiriendo mayor tiempo de recuperacion. Estudios muestran que detenerse 1-3 repeticiones antes del fallo produce hipertrofia similar (~95%) con significativamente menos fatiga, permitiendo mayor volumen semanal total, que es un predictor superior de crecimiento.',
                source: { author: 'Moran-Navarro, R. et al.', journal: 'Journal of Strength and Conditioning Research', year: 2012 }
            },
            {
                id: 15, category: 'training', icon: '📈',
                title: 'Volumen semanal: 10-20 series/musculo',
                description: 'Programar entre 10-20 series efectivas por grupo muscular por semana para hipertrofia.',
                explanation: 'Meta-analisis muestran una relacion dosis-respuesta entre volumen semanal e hipertrofia hasta ~20 series. Por debajo de 10, la estimulacion es suboptima. Por encima de 20, los rendimientos decrecen y el riesgo de sobreentrenamiento aumenta. Distribuir el volumen en 2-3 sesiones por musculo optimiza la MPS y permite mayor volumen total recuperable.',
                source: { author: 'Schoenfeld, B.J. et al.', journal: 'Journal of Sports Sciences', year: 2017 }
            },

            // === RECOVERY ===
            {
                id: 16, category: 'recovery', icon: '😴',
                title: '7-9 horas de sueno para anabolismo',
                description: 'Priorizar 7-9 horas de sueno cada noche como la herramienta de recuperacion mas poderosa.',
                explanation: 'El 95% de la hormona de crecimiento se libera durante el sueno de ondas lentas (fases 3-4). La privacion de sueno (<6h) reduce la testosterona un 10-15% y aumenta el cortisol un 37%. El sueno tambien es cuando ocurre la consolidacion de la memoria motora: los patrones de movimiento aprendidos se "guardan" en la corteza motora durante el REM.',
                source: { author: 'Dattilo, M. et al.', journal: 'Medical Hypotheses', year: 2011 }
            },
            {
                id: 17, category: 'recovery', icon: '🧊',
                title: 'Contraste frio-calor post-entreno',
                description: 'Alternar 1 min agua fria (10-15°C) y 2 min agua caliente (38-40°C), 3-4 ciclos post-entrenamiento.',
                explanation: 'El contraste termico actua como una "bomba vascular": el frio causa vasoconstriccion y el calor vasodilatacion. Esta alternancia acelera el flujo sanguineo y linfatico, removiendo metabolitos de fatiga (lactato, iones H+) un 30% mas rapido. Tambien reduce la inflamacion sin bloquearla completamente (a diferencia de solo hielo), preservando la senal adaptativa.',
                source: { author: 'Versey, N.G. et al.', journal: 'Sports Medicine', year: 2013 }
            },
            {
                id: 18, category: 'recovery', icon: '🧹',
                title: 'Foam rolling 5-10 min post-entreno',
                description: 'Usar foam roller durante 5-10 minutos en los musculos entrenados inmediatamente despues del entrenamiento.',
                explanation: 'El foam rolling (auto-liberacion miofascial) reduce el DOMS (dolor muscular post-entrenamiento) en un 30% en las 72h siguientes. Mejora el rango de movimiento sin reducir la fuerza (a diferencia del stretching estatico pre-entreno). El mecanismo es neurológico: reduce la excitabilidad de los husos musculares y activa las interneuronas inhibitorias.',
                source: { author: 'Pearcey, G.E. et al.', journal: 'Journal of Athletic Training', year: 2015 }
            },
            {
                id: 19, category: 'recovery', icon: '🍌',
                title: 'Proteina + carbs en ventana post-entreno',
                description: 'Consumir 20-40g de proteina + 40-80g de carbohidratos dentro de las 2 horas post-entrenamiento.',
                explanation: 'Aunque la "ventana anabolica" no es tan estrecha como se creia, la combinacion de proteina y carbohidratos post-entreno tiene beneficios claros: la insulina del carbohidrato potencia la captacion de aminoacidos un 30-40%, acelera la resintesis de glucogeno un 50% vs. solo carbs, y reduce el cortisol post-ejercicio. Esto es especialmente critico si entrenas 2+ veces al dia.',
                source: { author: 'Aragon, A.A. & Schoenfeld, B.J.', journal: 'Journal of the International Society of Sports Nutrition', year: 2013 }
            },
            {
                id: 20, category: 'recovery', icon: '🚶',
                title: 'Descanso activo entre dias de entreno',
                description: 'Realizar actividad ligera (caminata, yoga, natacion suave) en dias de descanso en lugar de inactividad total.',
                explanation: 'El descanso activo a baja intensidad (RPE 3-4/10) aumenta el flujo sanguineo a los musculos danados sin generar fatiga adicional. Esto acelera la entrega de nutrientes y la remocion de desechos metabolicos. Estudios muestran que el descanso activo reduce el DOMS un 20-25% comparado con el descanso pasivo total, y mantiene la movilidad articular.',
                source: { author: 'Bishop, P.A. et al.', journal: 'Sports Medicine', year: 2008 }
            },

            // === STRESS ===
            {
                id: 21, category: 'stress', icon: '🫁',
                title: 'Respiracion 4-7-8 para cortisol',
                description: 'Practicar la tecnica 4-7-8 (inhalar 4s, retener 7s, exhalar 8s) 4 ciclos cuando sientas estres.',
                explanation: 'La exhalacion prolongada activa el nervio vago, estimulando el sistema nervioso parasimpatico. Esto reduce la frecuencia cardiaca, la presion arterial y los niveles de cortisol en minutos. La retencion de 7 segundos aumenta el CO2 sanguineo levemente, lo que paradojicamente mejora la oxigenacion celular via el efecto Bohr. 4 ciclos son suficientes para un reset autonomico.',
                source: { author: 'Ma, X. et al.', journal: 'Frontiers in Psychology', year: 2017 }
            },
            {
                id: 22, category: 'stress', icon: '🌿',
                title: 'Caminar 20 min en naturaleza',
                description: 'Caminar al menos 20 minutos en un entorno natural (parque, bosque, playa) para reducir cortisol.',
                explanation: 'El "forest bathing" (shinrin-yoku) reduce el cortisol salival un 12.4% en solo 20 minutos. La exposicion a fitoncidas (compuestos volatiles de plantas) aumenta las celulas NK (natural killer) del sistema inmune un 50%. Ademas, los patrones fractales de la naturaleza inducen un estado de "atencion blanda" que restaura la funcion ejecutiva y reduce la fatiga mental.',
                source: { author: 'Hunter, M.R. et al.', journal: 'Frontiers in Psychology', year: 2019 }
            },
            {
                id: 23, category: 'stress', icon: '📝',
                title: 'Journaling 15 minutos diarios',
                description: 'Escribir durante 15 minutos sobre pensamientos y emociones del dia para procesar estres.',
                explanation: 'La escritura expresiva activa la corteza prefrontal, que regula la amigdala (centro del miedo). Al poner en palabras las emociones, se reduce su intensidad neural un 30% (efecto de etiquetado afectivo). Estudios de 4 semanas muestran reduccion de visitas medicas un 50%, mejora en funcion inmune (aumento de linfocitos T) y reduccion de sintomas de ansiedad.',
                source: { author: 'Smyth, J.M. et al.', journal: 'JAMA', year: 1999 }
            },
            {
                id: 24, category: 'stress', icon: '🚿',
                title: 'Duchas frias de 2-3 minutos',
                description: 'Terminar cada ducha con 2-3 minutos de agua fria (10-15°C) para activar resiliencia al estres.',
                explanation: 'La exposicion al frio agudo activa el sistema noradrenergico, aumentando la norepinefrina hasta un 530%. Esto produce un efecto antidepresivo natural y mejora la tolerancia al estres (hormesis). El frio tambien activa la grasa parda (BAT), que quema calorias para generar calor, y reduce la inflamacion sistemica via la via colinergica anti-inflamatoria.',
                source: { author: 'Shevchuk, N.A.', journal: 'Medical Hypotheses', year: 2008 }
            },
            {
                id: 25, category: 'stress', icon: '🧠',
                title: 'Meditacion 10 min reduce ansiedad',
                description: 'Practicar 10 minutos de meditacion mindfulness diaria para reducir ansiedad y mejorar enfoque.',
                explanation: 'Meta-analisis de 47 ensayos clinicos muestra que la meditacion mindfulness reduce la ansiedad (efecto moderado d=0.38) y la depresion comparablemente a los antidepresivos. Solo 10 minutos diarios durante 8 semanas producen cambios medibles en la densidad de materia gris de la amigdala (-reduccion) y la corteza prefrontal (+aumento), areas clave de regulacion emocional.',
                source: { author: 'Goyal, M. et al.', journal: 'JAMA Internal Medicine', year: 2014 }
            }
        ],

        get filtered() {
            let result = this.hacks;
            if (this.category !== 'all') {
                result = result.filter(h => h.category === this.category);
            }
            if (this.search.trim()) {
                const q = this.search.toLowerCase();
                result = result.filter(h =>
                    h.title.toLowerCase().includes(q) ||
                    h.description.toLowerCase().includes(q) ||
                    h.source.author.toLowerCase().includes(q) ||
                    h.source.journal.toLowerCase().includes(q)
                );
            }
            return result;
        },

        openHack(hack) {
            this.selectedHack = hack;
        }
    }));
</script>
@endscript
