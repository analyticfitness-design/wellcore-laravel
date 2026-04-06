<script setup>
import { ref, computed, reactive, onMounted, onBeforeUnmount } from 'vue';
import { useRouter } from 'vue-router';
import { useApi } from '../../composables/useApi';
import ClientLayout from '../../layouts/ClientLayout.vue';
import {
  PhFlask, PhBarbell, PhPill, PhFish, PhSun, PhOrange, PhStar, PhMoon,
  PhAtom, PhLeaf, PhLightning, PhDna, PhFire, PhBone, PhTestTube, PhBed,
  PhCrown, PhBrain, PhMagnet, PhShield, PhDrop, PhTreeStructure
} from '@phosphor-icons/vue';

const api = useApi();
const router = useRouter();

// State
const loading = ref(true);
const error = ref(null);
const activeTab = ref('entrenamiento');
const currentWeek = ref(1);

// Plan data
const trainingPlan = ref(null);
const nutritionPlan = ref(null);

// Macro helpers — soporta claves en español (proteina_g, carbohidratos_g, grasas_g),
// inglés (protein_g, carbs_g, fat_g) y formato anidado (macros.{key})
const macroP = computed(() => {
  const p = nutritionPlan.value;
  if (!p) return 0;
  return p.macros?.proteina_g ?? p.macros?.proteinas_g ?? p.macros?.protein_g
      ?? p.proteina_g ?? p.proteinas_g ?? p.protein_g ?? 0;
});
const macroC = computed(() => {
  const p = nutritionPlan.value;
  if (!p) return 0;
  return p.macros?.carbohidratos_g ?? p.macros?.carbs_g ?? p.macros?.carbohidratos
      ?? p.carbohidratos_g ?? p.carbs_g ?? p.carbohidratos ?? 0;
});
const macroF = computed(() => {
  const p = nutritionPlan.value;
  if (!p) return 0;
  return p.macros?.grasas_g ?? p.macros?.fat_g ?? p.macros?.grasas
      ?? p.grasas_g ?? p.fat_g ?? p.grasas ?? 0;
});
const totalKcalNutrition = computed(() =>
  Math.max(1, macroP.value * 4 + macroC.value * 4 + macroF.value * 9)
);
const supplementPlan = ref(null);
const cicloPlan = ref(null);
const clientPlanType = ref('basico');
const planStartDate = ref(null);

// Habit data (from API buildHabitData — plan endpoint, used as fallback)
const habitData = ref([]);
const habitCompliance = ref(0);

// Live habit data — from GET /api/v/client/habits (loaded when tab activates)
const habitsLive = ref(null);   // null = not yet loaded
const habitsLoading = ref(false);
const habitsToggling = ref({});  // { [habit_type]: true } while POST in flight

// Nutrition tab accordion state
const openNutrMeals = ref({});
const activeNutrOption = ref({});

function toggleNutrMeal(idx) {
  openNutrMeals.value[idx] = !openNutrMeals.value[idx];
}

function setNutrOption(idx, option) {
  activeNutrOption.value[idx] = option;
}

function getNutrMealColor(nombre) {
  const n = (nombre || '').toLowerCase();
  if (n.includes('desayuno')) return 'bg-amber-500/10 text-amber-400';
  if (n.includes('pre-entreno') || n.includes('pre entreno')) return 'bg-green-500/10 text-green-400';
  if (n.includes('almuerzo') || n.includes('post-entreno') || n.includes('post entreno')) return 'bg-blue-500/10 text-blue-400';
  if (n.includes('cena')) return 'bg-indigo-500/10 text-indigo-400';
  if (n.includes('snack') || n.includes('merienda') || n.includes('media mañana') || n.includes('media manana')) return 'bg-pink-500/10 text-pink-400';
  return 'bg-wc-accent/10 text-wc-accent';
}

function formatNutrAlimento(alimento) {
  if (typeof alimento === 'string') return alimento;
  if (typeof alimento === 'object' && alimento !== null) {
    const name = alimento.nombre || alimento.alimento || alimento.name || '';
    const qty = alimento.cantidad || alimento.porcion || alimento.quantity || alimento.amount || '';
    if (name && qty) return `${name} — ${qty}`;
    return name || qty || '';
  }
  return String(alimento);
}

function foodIcon(name) {
  const lower = (typeof name === 'string' ? name : formatNutrAlimento(name)).toLowerCase();
  const map = [
    [['pollo','pechuga','chicken','pavo'], '\u{1F357}'],
    [['carne','res','steak','lomo','cerdo'], '\u{1F969}'],
    [['salm\u00F3n','salmon','at\u00FAn','atun','tilapia','pescado','corvina','trucha'], '\u{1F41F}'],
    [['huevo','clara','claras'], '\u{1F95A}'],
    [['yogur','yogurt','leche'], '\u{1F95B}'],
    [['queso','reques\u00F3n','requeson'], '\u{1F9C0}'],
    [['avena','granola','oatmeal'], '\u{1F963}'],
    [['arroz','rice','quinoa'], '\u{1F35A}'],
    [['pasta'], '\u{1F35D}'],
    [['pan','tostada'], '\u{1F35E}'],
    [['arepa','tortilla'], '\u{1FAD3}'],
    [['papa'], '\u{1F954}'],
    [['batata','camote'], '\u{1F360}'],
    [['banana','banano','pl\u00E1tano','platano'], '\u{1F34C}'],
    [['manzana'], '\u{1F34E}'],
    [['fresa','fresas'], '\u{1F353}'],
    [['fruta','frutas'], '\u{1F347}'],
    [['br\u00F3coli','brocoli'], '\u{1F966}'],
    [['espinaca','lechuga'], '\u{1F96C}'],
    [['ensalada','vegetal','vegetales'], '\u{1F957}'],
    [['tomate'], '\u{1F345}'],
    [['aguacate','avocado'], '\u{1F951}'],
    [['nuez','nueces','almendra','man\u00ED','mani'], '\u{1F95C}'],
    [['aceite','oliva'], '\u{1FAD2}'],
    [['prote\u00EDna','proteina','whey'], '\u{1F9EA}'],
    [['agua'], '\u{1F4A7}'],
    [['caf\u00E9','cafe'], '\u2615'],
    [['miel'], '\u{1F36F}'],
    [['frijol','lenteja'], '\u{1FAD8}'],
    [['jugo','mango','maracuy'], '\u{1F9C3}'],
  ];
  for (const [keywords, emoji] of map) {
    if (keywords.some(k => lower.includes(k))) return emoji;
  }
  return null;
}

function getNutrMealOpciones(meal) {
  // Returns a map of { a: [...], b: [...], c: [...] } if multi-option, otherwise null
  const keys = ['opcion_a', 'option_a'];
  const found = keys.some(k => meal[k] && Array.isArray(meal[k]) && meal[k].length > 0);
  if (!found && !(meal.opciones && (meal.opciones.opcion_a || meal.opciones.option_a))) return null;
  const src = meal.opciones || meal;
  const a = src.opcion_a || src.option_a || null;
  const b = src.opcion_b || src.option_b || null;
  const c = src.opcion_c || src.option_c || null;
  return { a, b, c };
}

// Bloodwork
const bloodworkResults = ref([]);
const bwSaving = ref(false);
const bwShowSuccess = ref(false);
const bwErrors = ref({});
const bwForm = reactive({
  testName: '',
  value: '',
  unit: '',
  referenceRange: '',
  testDate: '',
});
const bwFormOpen = ref(false);
let bwSuccessTimer = null;

// Tabs definition (matching blade order)
const tabs = [
  { key: 'entrenamiento', label: 'Entrenamiento' },
  { key: 'habitos', label: 'Habitos' },
  { key: 'nutricion', label: 'Nutricion' },
  { key: 'suplementacion', label: 'Suplementos' },
  { key: 'ciclo', label: 'Ciclo' },
  { key: 'bloodwork', label: 'Bloodwork' },
];

const canAccessNutricion = computed(() => {
  return ['esencial', 'metodo', 'elite', 'presencial', 'rise'].includes(clientPlanType.value);
});

const canAccessElite = computed(() => {
  return ['elite'].includes(clientPlanType.value);
});

function isTabLocked(key) {
  if (['nutricion', 'suplementacion'].includes(key) && !canAccessNutricion.value) return true;
  if (['ciclo', 'bloodwork'].includes(key) && !canAccessElite.value) return true;
  return false;
}

function setTab(key) {
  if (!isTabLocked(key)) {
    activeTab.value = key;
    if (key === 'habitos' && habitsLive.value === null) {
      fetchHabits();
    }
  }
}

// Training computed
const totalWeeks = computed(() => {
  if (!trainingPlan.value?.semanas) return 1;
  return trainingPlan.value.semanas.length;
});

const progressPct = computed(() => {
  if (totalWeeks.value <= 1) return 0;
  return Math.min(((currentWeek.value) / totalWeeks.value) * 100, 100);
});

const planObjetivo = computed(() => {
  if (!trainingPlan.value) return null;
  return trainingPlan.value.objetivo || trainingPlan.value.objetivo_general || null;
});

const planFrecuencia = computed(() => {
  if (!trainingPlan.value) return null;
  return trainingPlan.value.frecuencia || null;
});

const planSplit = computed(() => {
  if (!trainingPlan.value) return null;
  return trainingPlan.value.split || trainingPlan.value.metodologia || null;
});

const semanas = computed(() => {
  if (!trainingPlan.value?.semanas) return [];
  return trainingPlan.value.semanas;
});

// Week accordion state
const openWeeks = ref({});

function toggleWeek(weekNum) {
  openWeeks.value[weekNum] = !openWeeks.value[weekNum];
}

function isWeekOpen(weekNum) {
  return !!openWeeks.value[weekNum];
}

// Type badge colors (matches blade $tipoBadgeClass)
function tipoBadgeClass(tipo) {
  const t = (tipo || '').toLowerCase();
  const map = {
    empuje: 'bg-orange-500/10 text-orange-400',
    push: 'bg-orange-500/10 text-orange-400',
    jale: 'bg-blue-500/10 text-blue-400',
    pull: 'bg-blue-500/10 text-blue-400',
    piernas: 'bg-violet-500/10 text-violet-400',
    legs: 'bg-violet-500/10 text-violet-400',
    pierna: 'bg-violet-500/10 text-violet-400',
    full: 'bg-emerald-500/10 text-emerald-400',
    'full body': 'bg-emerald-500/10 text-emerald-400',
    cardio: 'bg-sky-500/10 text-sky-400',
    upper: 'bg-rose-500/10 text-rose-400',
    'tren superior': 'bg-rose-500/10 text-rose-400',
    lower: 'bg-teal-500/10 text-teal-400',
    'tren inferior': 'bg-teal-500/10 text-teal-400',
  };
  return map[t] || 'bg-wc-accent/10 text-wc-accent';
}

// RIR badge color
function rirClass(rir) {
  if (rir === null || rir === undefined) return '';
  if (rir >= 3) return 'bg-emerald-500/15 text-emerald-400';
  if (rir >= 2) return 'bg-amber-500/15 text-amber-400';
  return 'bg-red-500/15 text-red-400';
}

// Supplement timing icons
function getTimingIcon(timing) {
  const t = (timing || '').toLowerCase();
  if (t.includes('ma\u00f1ana') || t.includes('manana') || t.includes('morning')) return '\u{1F305}';
  if (t.includes('pre-entreno') || t.includes('pre entreno') || t.includes('pre-workout')) return '\u{26A1}';
  if (t.includes('post-entreno') || t.includes('post entreno') || t.includes('post-workout')) return '\u{1F504}';
  if (t.includes('con comida')) return '\u{1F37D}';
  if (t.includes('noche') || t.includes('night') || t.includes('antes de dormir')) return '\u{1F319}';
  return '\u{1F48A}';
}

// Supplement priority colors (matches blade $prioridadColor)
function prioridadStyle(prioridad) {
  const p = (prioridad || '').toLowerCase();
  if (p === 'esencial') return { text: 'text-wc-accent', bg: 'bg-wc-accent/10' };
  if (p === 'recomendado') return { text: 'text-amber-400', bg: 'bg-amber-500/10' };
  return { text: 'text-wc-text-tertiary', bg: 'bg-wc-bg-secondary' };
}

// Supplement category styles (matches blade $catIconMap)
function getCatStyle(nombre) {
  const lower = (nombre || '').toLowerCase();
  if (lower.includes('rendimiento')) return { icon: '\u{26A1}', color: 'text-amber-400', bg: 'bg-amber-500/10', border: 'border-amber-500/20' };
  if (lower.includes('protecci') || lower.includes('proteccion')) return { icon: '\u{1F6E1}\uFE0F', color: 'text-emerald-400', bg: 'bg-emerald-500/10', border: 'border-emerald-500/20' };
  if (lower.includes('salud')) return { icon: '\u{2764}\uFE0F', color: 'text-sky-400', bg: 'bg-sky-500/10', border: 'border-sky-500/20' };
  if (lower.includes('recuperaci') || lower.includes('recuperacion')) return { icon: '\u{1F504}', color: 'text-purple-400', bg: 'bg-purple-500/10', border: 'border-purple-500/20' };
  return { icon: '\u{1F48A}', color: 'text-wc-text-secondary', bg: 'bg-wc-bg-secondary', border: 'border-wc-border' };
}

// Supplement icon by keyword mapping → returns Phosphor component name
function supplementIcon(name) {
  const n = (name || '').toLowerCase();
  const map = [
    [['whey','proteina','protein','isolate','caseina','casein'], 'PhFlask'],
    [['creatina','creatine'], 'PhBarbell'],
    [['multivitam','animal pak','opti-men','centrum','complejo'], 'PhPill'],
    [['omega','epa','dha','fish oil','aceite de pescado'], 'PhFish'],
    [['vitamina d','vit d','d3','vitamin d'], 'PhSun'],
    [['vitamina c','vit c','ester','ascorbic'], 'PhOrange'],
    [['vitamina b','complejo b','b12','b6','niacin'], 'PhStar'],
    [['magnesio','magnesium','glicinato'], 'PhMoon'],
    [['zinc','selenio','minerales','mineral'], 'PhAtom'],
    [['ashwa','rhodiola','ginseng','adaptogen','tongkat'], 'PhLeaf'],
    [['pre-entreno','pre workout','pump','neuro','beta-alanina','beta alanina'], 'PhLightning'],
    [['bcaa','amino','glutamina','glutamine','l-carnitina','aminoacid'], 'PhDna'],
    [['quemador','fat burner','termo','cla','citrulina','citrulline'], 'PhFire'],
    [['colageno','collagen','articula'], 'PhBone'],
    [['probiotic','prebiotic','digestivo','enzima'], 'PhTestTube'],
    [['melatonina','dormir','gaba','sleep','noche'], 'PhBed'],
    [['testo','tribulus','d-aspartic'], 'PhCrown'],
    [['nootropic','focus','cafeina','cafe','citicoline','alpha gpc'], 'PhBrain'],
    [['hierro','iron','ferro'], 'PhMagnet'],
    [['calcio','calcium'], 'PhBone'],
    [['cortisol','adrenal','phosphatidyl'], 'PhShield'],
    [['electrolitos','electrolyte','sales','potasio','sodium'], 'PhDrop'],
    [['hgh','peptid','grow'], 'PhTreeStructure'],
  ];
  for (const [keys, icon] of map) {
    if (keys.some(k => n.includes(k))) return icon;
  }
  return 'PhPill';
}

// Habit accent colors (matches blade $habitAccents)
const habitAccents = {
  agua:          { bg: 'bg-blue-500/10',    border: 'border-blue-500/25',    text: 'text-blue-400',    bar: 'bg-blue-500' },
  sueno:         { bg: 'bg-indigo-500/10',  border: 'border-indigo-500/25',  text: 'text-indigo-400',  bar: 'bg-indigo-500' },
  entrenamiento: { bg: 'bg-wc-accent/10',   border: 'border-wc-accent/25',  text: 'text-wc-accent',   bar: 'bg-wc-accent' },
  nutricion:     { bg: 'bg-emerald-500/10', border: 'border-emerald-500/25', text: 'text-emerald-400', bar: 'bg-emerald-500' },
  suplementos:   { bg: 'bg-violet-500/10',  border: 'border-violet-500/25',  text: 'text-violet-400',  bar: 'bg-violet-500' },
};

function getHabitAccent(type) {
  return habitAccents[type] || habitAccents.entrenamiento;
}

// Ciclo phase colors (matches blade $phaseColors)
function getPhaseColor(nombre) {
  const lower = (nombre || '').toLowerCase();
  if (lower.includes('iniciaci') || lower.includes('iniciacion')) return { bg: 'bg-sky-500/10', border: 'border-sky-500/25', text: 'text-sky-400', dot: 'bg-sky-400' };
  if (lower.includes('pico')) return { bg: 'bg-wc-accent/[0.08]', border: 'border-wc-accent/25', text: 'text-wc-accent', dot: 'bg-wc-accent' };
  if (lower.includes('tapering')) return { bg: 'bg-amber-500/10', border: 'border-amber-500/25', text: 'text-amber-400', dot: 'bg-amber-400' };
  if (lower.includes('pct') || lower.includes('post')) return { bg: 'bg-emerald-500/10', border: 'border-emerald-500/25', text: 'text-emerald-400', dot: 'bg-emerald-400' };
  return { bg: 'bg-wc-bg-secondary', border: 'border-wc-border', text: 'text-wc-text-secondary', dot: 'bg-wc-text-tertiary' };
}

// Bloodwork status helper (matches blade $bwStatus)
function bwStatus(result) {
  const range = result.reference_range || '';
  const val = parseFloat(result.value || 0);
  if (!range || val <= 0) return 'neutral';
  const m = range.match(/(\d+[.,]?\d*)\s*[-\u2013]\s*(\d+[.,]?\d*)/);
  if (!m) return 'neutral';
  const lo = parseFloat(m[1].replace(',', '.'));
  const hi = parseFloat(m[2].replace(',', '.'));
  if (val >= lo && val <= hi) return 'ok';
  return 'flag';
}

// Spectrum position for bloodwork value
function bwSpectrumPct(result) {
  const range = result.reference_range || '';
  const val = parseFloat(result.value || 0);
  if (!range || val <= 0) return null;
  const m = range.match(/(\d+[.,]?\d*)\s*[-\u2013]\s*(\d+[.,]?\d*)/);
  if (!m) return null;
  const lo = parseFloat(m[1].replace(',', '.'));
  const hi = parseFloat(m[2].replace(',', '.'));
  const r = hi - lo;
  if (r <= 0) return null;
  const visMin = lo - r * 0.4;
  const visMax = hi + r * 0.4;
  return Math.max(2, Math.min(98, ((val - visMin) / (visMax - visMin)) * 100));
}

// Latest per test for summary cards
const latestByTest = computed(() => {
  const map = {};
  // bloodworkResults is already ordered desc by test_date from API
  // Reverse to process oldest first, so newest overwrites
  const reversed = [...bloodworkResults.value].reverse();
  for (const r of reversed) {
    if (!map[r.test_name]) {
      map[r.test_name] = r;
    }
  }
  return map;
});

// Ciclo hormonal femenino local state
const cicloStartDate = ref('');
const cicloCycleLength = ref(28);
const cicloShowConfig = ref(false);

function initCicloFromStorage() {
  cicloStartDate.value = localStorage.getItem('wc_cycle_start') || '';
  cicloCycleLength.value = parseInt(localStorage.getItem('wc_cycle_length')) || 28;
  cicloShowConfig.value = !cicloStartDate.value;
}

function saveCicloConfig() {
  localStorage.setItem('wc_cycle_start', cicloStartDate.value);
  localStorage.setItem('wc_cycle_length', String(cicloCycleLength.value));
  cicloShowConfig.value = false;
}

const cicloCurrentDay = computed(() => {
  if (!cicloStartDate.value) return null;
  const start = new Date(cicloStartDate.value + 'T00:00:00');
  const today = new Date(); today.setHours(0, 0, 0, 0);
  const diff = Math.floor((today - start) / 86400000);
  const d = (diff % cicloCycleLength.value) + 1;
  return d > 0 ? d : null;
});

const cicloPhaseKey = computed(() => {
  const d = cicloCurrentDay.value;
  if (!d) return '';
  if (d <= 5) return 'menstrual';
  if (d <= 13) return 'folicular';
  if (d <= 16) return 'ovulatoria';
  return 'lutea';
});

const cicloPhaseMap = {
  menstrual:  { name: 'Menstrual',  emoji: '\u{1F311}', ring: '#f87171', bg: 'bg-red-500/10',    border: 'border-red-500/25',    text: 'text-red-400',    train: 'Ejercicio de baja intensidad: yoga, caminata ligera, estiramientos. Reduce cargas y escucha tu cuerpo.', nutrition: 'Aumenta hierro y magnesio. Prioriza alimentos anti-inflamatorios: salmon, nueces, verduras de hoja.', energy: 3 },
  folicular:  { name: 'Folicular',  emoji: '\u{1F331}', ring: '#4ade80', bg: 'bg-green-500/10',  border: 'border-green-500/25',  text: 'text-green-400',  train: 'Tu energia aumenta. Ideal para fuerza, HIIT y aumentar cargas. Aprovecha la ventana anabolica.', nutrition: 'Soporta la sintesis de estrogeno con zinc y B6. Proteina moderada-alta para soportar el volumen.', energy: 7 },
  ovulatoria: { name: 'Ovulatoria', emoji: '\u{2728}',  ring: '#fbbf24', bg: 'bg-amber-500/10',  border: 'border-amber-500/25',  text: 'text-amber-400',  train: 'Pico maximo de energia y fuerza. Momento ideal para PRs, sesiones de alta intensidad y nuevos records.', nutrition: 'Mantiene proteina alta. Antioxidantes para reducir inflamacion post-esfuerzo. Hidratacion optima.', energy: 10 },
  lutea:      { name: 'Lutea',      emoji: '\u{1F319}', ring: '#c084fc', bg: 'bg-purple-500/10', border: 'border-purple-500/25', text: 'text-purple-400', train: 'Energia moderada. Enfocate en tecnica, estabilidad y recuperacion activa. Reduce intensidad al final.', nutrition: 'Sube el apetito, es normal. Prioriza fibra, magnesio y calcio para reducir sintomas de SPM.', energy: 6 },
};

const cicloPhaseData = computed(() => cicloPhaseMap[cicloPhaseKey.value] || null);

const cicloDaysUntilNext = computed(() => {
  if (!cicloCurrentDay.value) return null;
  return cicloCycleLength.value - cicloCurrentDay.value + 1;
});

const cicloPhaseArcs = computed(() => {
  const r = 54;
  const circ = 2 * Math.PI * r;
  const cl = cicloCycleLength.value;
  const gap = 2.5;
  return [
    { color: '#f87171', start: 0, days: 5 },
    { color: '#4ade80', start: 5, days: 8 },
    { color: '#fbbf24', start: 13, days: 3 },
    { color: '#c084fc', start: 16, days: cl - 16 },
  ].map(p => {
    const arcLen = Math.max(0, (p.days / cl) * circ - gap);
    return {
      color: p.color,
      dasharray: arcLen.toFixed(1) + ' ' + (circ * 2).toFixed(1),
      dashoffset: (-(p.start / cl) * circ).toFixed(1),
    };
  });
});

const cicloDotOffset = computed(() => {
  if (!cicloCurrentDay.value) return null;
  const r = 54;
  const circ = 2 * Math.PI * r;
  return (-(((cicloCurrentDay.value - 0.5) / cicloCycleLength.value) * circ)).toFixed(1);
});

// Phase reference cards for feminine cycle
const phaseCards = [
  { dot: 'bg-red-400',    text: 'text-red-400',    border: 'border-red-500/25',    bgF: 'bg-red-500/10',    bgB: 'bg-red-500/20',    name: 'Menstrual',  days: '1\u20135',  sub: 'Descanso activo',    train: 'Yoga, caminata, movilidad. Reduce cargas. Recuperacion activa prioritaria.', nutr: 'Hierro, magnesio, omega-3 y alimentos anti-inflamatorios.' },
  { dot: 'bg-green-400',  text: 'text-green-400',  border: 'border-green-500/25',  bgF: 'bg-green-500/10',  bgB: 'bg-green-500/20',  name: 'Folicular',  days: '6\u201313', sub: 'Fuerza e intensidad',  train: 'Fuerza maxima, HIIT, aumentar cargas. Ventana anabolica optima.', nutr: 'Proteina alta, zinc, vitamina B6 para sintesis de estrogeno.' },
  { dot: 'bg-amber-400',  text: 'text-amber-400',  border: 'border-amber-500/25',  bgF: 'bg-amber-500/10',  bgB: 'bg-amber-500/20',  name: 'Ovulatoria', days: '14\u201316', sub: 'Pico de rendimiento', train: 'Pico de fuerza y energia. Ideal para PRs y nuevos records.', nutr: 'Antioxidantes, proteina alta, hidratacion optima.' },
  { dot: 'bg-purple-400', text: 'text-purple-400', border: 'border-purple-500/25', bgF: 'bg-purple-500/10', bgB: 'bg-purple-500/20', name: 'Lutea',      days: '17\u201328', sub: 'Tecnica y estabilidad', train: 'Tecnica, estabilidad, recuperacion activa. Reduce intensidad al final.', nutr: 'Fibra, magnesio y calcio. El apetito sube \u2014 es normal.' },
];

// Ciclo efectos accordion
const openEfecto = ref(null);
function toggleEfecto(idx) {
  openEfecto.value = openEfecto.value === idx ? null : idx;
}

// Bloodwork test options (matches blade select)
const bwTestOptions = [
  { group: 'Metabolismo', tests: ['Glucosa', 'HbA1c', 'Insulina'] },
  { group: 'Lipidos', tests: ['Colesterol Total', 'HDL', 'LDL', 'Trigliceridos'] },
  { group: 'Hormonas', tests: ['Testosterona', 'TSH', 'T3 Libre', 'T4 Libre', 'Cortisol', 'DHEA-S'] },
  { group: 'Hematologia', tests: ['Hemoglobina', 'Hematocrito', 'Ferritina', 'Hierro'] },
  { group: 'Vitaminas y Minerales', tests: ['Vitamina D', 'Vitamina B12', 'Zinc', 'Magnesio'] },
  { group: 'Funcion Renal/Hepatica', tests: ['Creatinina', 'ALT/TGP', 'AST/TGO'] },
];

// Fetch
async function fetchPlan() {
  loading.value = true;
  error.value = null;
  try {
    const response = await api.get('/api/v/client/plan');
    const d = response.data;
    trainingPlan.value = d.training_plan || null;
    nutritionPlan.value = d.nutrition_plan || null;
    supplementPlan.value = d.supplement_plan || null;
    cicloPlan.value = d.ciclo_plan || null;
    clientPlanType.value = d.plan_type || 'basico';
    planStartDate.value = d.plan_start_date || null;
    currentWeek.value = d.current_week || 1;
    habitData.value = d.habit_data || [];
    habitCompliance.value = d.habit_compliance || 0;
    bloodworkResults.value = d.bloodwork || [];

    // Auto-open current week
    if (currentWeek.value) {
      openWeeks.value[currentWeek.value] = true;
    }

    // Auto-open bloodwork form if no results
    if (bloodworkResults.value.length === 0) {
      bwFormOpen.value = true;
    }

    // Init ciclo local storage
    initCicloFromStorage();
  } catch (err) {
    error.value = err.response?.data?.message || 'Error al cargar el plan';
  } finally {
    loading.value = false;
  }
}

function goToWorkout(dayIndex) {
  router.push({ name: 'client-workout', params: { day: dayIndex } });
}

// ── Live habits ──────────────────────────────────────────────────────────────

async function fetchHabits() {
  habitsLoading.value = true;
  try {
    const res = await api.get('/api/v/client/habits');
    habitsLive.value = res.data;
    // Sync compliance bar from live data
    if (res.data.weekly_compliance !== undefined) {
      habitCompliance.value = res.data.weekly_compliance ?? habitCompliance.value;
    }
  } catch {
    // silently fall back to plan data already loaded
  } finally {
    habitsLoading.value = false;
  }
}

async function doToggleHabit(habitType) {
  if (habitsToggling.value[habitType]) return;
  habitsToggling.value[habitType] = true;
  try {
    const res = await api.post('/api/v/client/habits/toggle', { habit_type: habitType });
    // Update local state optimistically confirmed by server
    if (habitsLive.value?.today_habits?.[habitType] !== undefined) {
      habitsLive.value.today_habits[habitType].completed = res.data.completed;
      if (res.data.completed) {
        habitsLive.value.today_habits[habitType].streak = (habitsLive.value.today_habits[habitType].streak || 0) + 1;
      }
    }
  } catch {
    // ignore — user can retry
  } finally {
    habitsToggling.value[habitType] = false;
  }
}

// Computed list of habits merging live data with fallback plan data
const habitCards = computed(() => {
  if (habitsLive.value?.today_habits) {
    return Object.entries(habitsLive.value.today_habits).map(([type, h]) => ({
      type,
      label: h.label,
      icon: h.icon,
      tip: h.tip,
      streak: h.streak ?? 0,
      average: h.compliance ?? 0,
      completed: h.completed ?? false,
      last7: [],
    }));
  }
  // fallback: plan data (no completed info)
  return habitData.value.map(h => ({ ...h, completed: false }));
});

// Bloodwork save
async function saveBloodwork() {
  bwSaving.value = true;
  bwErrors.value = {};
  bwShowSuccess.value = false;
  try {
    await api.post('/api/v/client/bloodwork', {
      test_name: bwForm.testName,
      value: bwForm.value,
      unit: bwForm.unit,
      reference_range: bwForm.referenceRange,
      test_date: bwForm.testDate,
    });
    bwShowSuccess.value = true;
    bwForm.testName = '';
    bwForm.value = '';
    bwForm.unit = '';
    bwForm.referenceRange = '';
    bwForm.testDate = '';
    // Refresh bloodwork data
    const response = await api.get('/api/v/client/plan');
    bloodworkResults.value = response.data.bloodwork || [];
    clearTimeout(bwSuccessTimer);
    bwSuccessTimer = setTimeout(() => { bwShowSuccess.value = false; }, 5000);
  } catch (err) {
    if (err.response?.status === 422) {
      bwErrors.value = err.response.data.errors || {};
    }
  } finally {
    bwSaving.value = false;
  }
}

async function deleteBloodwork(id) {
  if (!confirm('\u00bfEliminar este resultado?')) return;
  try {
    await api.delete(`/api/v/client/bloodwork/${id}`);
    bloodworkResults.value = bloodworkResults.value.filter(r => r.id !== id);
  } catch (e) {
    // silent
  }
}

// Format date for display
function formatDate(dateStr) {
  if (!dateStr) return '';
  const d = new Date(dateStr);
  const months = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
  return `${String(d.getDate()).padStart(2, '0')} ${months[d.getMonth()]} ${d.getFullYear()}`;
}

function formatDateShort(dateStr) {
  if (!dateStr) return '';
  const d = new Date(dateStr);
  return `${String(d.getDate()).padStart(2, '0')}/${String(d.getMonth() + 1).padStart(2, '0')}/${d.getFullYear()}`;
}

// Phase card flip state
const flippedCards = ref({});
function toggleFlip(idx) {
  flippedCards.value[idx] = !flippedCards.value[idx];
}

onMounted(() => {
  fetchPlan();
});

onBeforeUnmount(() => {
  clearTimeout(bwSuccessTimer);
});
</script>

<template>
  <ClientLayout>
    <div class="space-y-6">
      <!-- Header -->
      <div class="mb-8">
        <h1 class="font-display text-3xl tracking-wide text-wc-text">MI PLAN</h1>
        <p class="mt-1 text-sm text-wc-text-secondary">Tu programacion personalizada, disenada por tu coach</p>
      </div>

      <!-- Loading skeleton -->
      <template v-if="loading">
        <div class="space-y-4 animate-pulse">
          <div class="h-12 rounded-xl bg-wc-bg-tertiary"></div>
          <div class="h-48 rounded-xl bg-wc-bg-tertiary"></div>
          <div class="h-32 rounded-xl bg-wc-bg-tertiary"></div>
          <div class="h-64 rounded-xl bg-wc-bg-tertiary"></div>
        </div>
      </template>

      <!-- Error state -->
      <div v-else-if="error" class="rounded-xl border border-red-500/30 bg-red-500/10 p-6 text-center">
        <svg class="mx-auto h-10 w-10 text-red-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
        </svg>
        <p class="mt-3 text-sm text-red-400">{{ error }}</p>
        <button @click="fetchPlan" class="mt-4 rounded-lg bg-wc-accent px-4 py-2 text-sm font-medium text-white hover:bg-red-700 transition-colors">
          Reintentar
        </button>
      </div>

      <!-- Content -->
      <template v-else>
        <!-- Tabs -->
        <div class="wc-glass mb-6 rounded-xl border border-wc-border bg-wc-bg-tertiary p-1" role="tablist" aria-label="Secciones del plan">
          <div class="flex gap-1 overflow-x-auto">
            <button
              v-for="tab in tabs"
              :key="tab.key"
              @click="setTab(tab.key)"
              role="tab"
              :aria-selected="activeTab === tab.key ? 'true' : 'false'"
              :class="[
                'shrink-0 flex-1 rounded-lg px-4 py-2.5 text-sm font-medium transition-all whitespace-nowrap focus:outline-none focus:ring-2 focus:ring-wc-accent focus:ring-offset-1',
                activeTab === tab.key
                  ? 'bg-wc-accent text-white shadow-sm'
                  : isTabLocked(tab.key)
                    ? 'cursor-not-allowed opacity-40 text-wc-text-secondary'
                    : 'text-wc-text-secondary hover:text-wc-text hover:bg-wc-bg-secondary'
              ]"
            >
              {{ tab.label }}
              <span v-if="isTabLocked(tab.key)" class="ml-1 text-xs">&#x1F512;</span>
            </button>
          </div>
        </div>

        <!-- ==================== TAB: ENTRENAMIENTO ==================== -->
        <div v-if="activeTab === 'entrenamiento'">
          <template v-if="trainingPlan">
            <!-- Program Overview Card -->
            <div class="wc-topline wc-grain relative mb-6 overflow-hidden rounded-xl border border-wc-accent/20 bg-gradient-to-br from-wc-accent/[0.08] via-wc-bg-tertiary to-transparent p-5 sm:p-6">
              <div class="wc-orb-tr" aria-hidden="true"></div>
              <div class="pointer-events-none absolute -right-8 -top-8 h-32 w-32 rounded-full bg-wc-accent/[0.06]"></div>
              <div class="pointer-events-none absolute -right-3 -top-3 h-16 w-16 rounded-full bg-wc-accent/10"></div>

              <div class="relative">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                  <div class="flex-1">
                    <!-- Plan badge -->
                    <div class="flex items-center gap-2">
                      <span class="inline-flex items-center gap-1 rounded-full bg-gradient-to-r from-wc-accent/15 to-red-400/10 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-wc-accent">
                        <svg class="h-2.5 w-2.5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                        Plan {{ clientPlanType.charAt(0).toUpperCase() + clientPlanType.slice(1) }}
                      </span>
                      <span class="rounded-full bg-emerald-500/10 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wider text-emerald-400">Activo</span>
                    </div>

                    <p v-if="planStartDate" class="mt-2 text-sm text-wc-text-secondary">Inicio: {{ planStartDate }}</p>

                    <!-- Attributes -->
                    <div class="mt-3 flex flex-wrap gap-2">
                      <span v-if="planFrecuencia" class="inline-flex items-center gap-1 rounded-full bg-wc-accent/10 px-2.5 py-1 text-xs font-medium text-wc-accent">
                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" /></svg>
                        {{ planFrecuencia }}
                      </span>
                      <span v-if="planSplit" class="inline-flex items-center gap-1 rounded-full bg-wc-bg-secondary px-2.5 py-1 text-xs font-medium text-wc-text-secondary">
                        <svg class="h-3 w-3 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 0a1.5 1.5 0 0 1-1.5-1.5v-3a1.5 1.5 0 0 1 1.5-1.5h1.5a1.5 1.5 0 0 1 1.5 1.5v3m-4.5 0a1.5 1.5 0 0 0-1.5 1.5v3a1.5 1.5 0 0 0 1.5 1.5h1.5a1.5 1.5 0 0 0 1.5-1.5v-3m12-4.5v3a1.5 1.5 0 0 1-1.5 1.5h-1.5m3 0a1.5 1.5 0 0 1-1.5 1.5v3a1.5 1.5 0 0 1-1.5 1.5h-1.5a1.5 1.5 0 0 1-1.5-1.5v-3m0-4.5a1.5 1.5 0 0 0-1.5-1.5h-1.5a1.5 1.5 0 0 0-1.5 1.5v3" /></svg>
                        {{ planSplit }}
                      </span>
                    </div>
                  </div>

                  <!-- Week counter -->
                  <div v-if="totalWeeks > 1" class="flex shrink-0 flex-col items-end">
                    <div class="flex items-baseline gap-1">
                      <span class="font-data text-4xl font-bold tabular-nums text-wc-accent">{{ currentWeek }}</span>
                      <span class="text-sm text-wc-text-tertiary">/ {{ totalWeeks }}</span>
                    </div>
                    <p class="text-xs font-semibold tracking-widest uppercase text-wc-text-secondary">Semana actual</p>
                  </div>
                </div>

                <!-- Progress bar -->
                <div v-if="totalWeeks > 1" class="mt-5">
                  <div class="flex items-center justify-between text-sm text-wc-text-tertiary mb-1.5">
                    <span>Progreso del programa</span>
                    <span class="font-data font-semibold text-wc-accent">{{ Math.round(progressPct) }}%</span>
                  </div>
                  <div class="h-2 w-full overflow-hidden rounded-full bg-wc-bg-secondary">
                    <div class="h-full rounded-full bg-gradient-to-r from-wc-accent to-red-400 transition-all duration-700" :style="{ width: progressPct + '%' }"></div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Notas del coach (entrenamiento) -->
            <div v-if="trainingPlan?.notas_coach" class="mb-5 rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
              <div class="flex items-start gap-3">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-wc-accent/10">
                  <svg class="h-5 w-5 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 0 1 .865-.501 48.172 48.172 0 0 0 3.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z"/>
                  </svg>
                </div>
                <div class="min-w-0 flex-1">
                  <p class="mb-1.5 text-xs font-semibold tracking-widest uppercase text-wc-text-secondary">Notas de tu coach</p>
                  <p class="text-base leading-relaxed text-wc-text-secondary">{{ trainingPlan.notas_coach }}</p>
                </div>
              </div>
            </div>

            <!-- Objetivo banner -->
            <div v-if="planObjetivo" class="mb-5 flex items-start gap-3 rounded-xl border border-wc-accent/20 bg-wc-accent/5 p-4">
              <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-wc-accent/15">
                <svg class="h-4 w-4 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.362 5.214A8.252 8.252 0 0 1 12 21 8.25 8.25 0 0 1 6.038 7.047 8.287 8.287 0 0 0 9 9.601a8.983 8.983 0 0 1 3.361-6.867 8.21 8.21 0 0 0 3 2.48Z" /></svg>
              </div>
              <div>
                <p class="text-sm font-semibold uppercase tracking-wider text-wc-accent/70">Objetivo del plan</p>
                <p class="mt-0.5 text-sm leading-relaxed text-wc-text-secondary">{{ planObjetivo }}</p>
              </div>
            </div>

            <!-- Weeks accordion -->
            <div v-if="semanas.length > 0" class="space-y-3">
              <div
                v-for="(semana, sIdx) in semanas"
                :key="sIdx"
                class="overflow-hidden rounded-xl border transition-colors"
                :class="(semana.numero || sIdx + 1) === currentWeek ? 'wc-topline border-wc-accent/30 bg-wc-accent/5' : 'border-wc-border bg-wc-bg-tertiary'"
              >
                <!-- Week header -->
                <button
                  @click="toggleWeek(semana.numero || sIdx + 1)"
                  class="flex w-full items-center justify-between px-4 py-4 text-left transition-colors hover:bg-black/5 dark:hover:bg-white/5 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-wc-accent"
                  :aria-expanded="isWeekOpen(semana.numero || sIdx + 1)"
                >
                  <div class="flex items-center gap-3">
                    <div
                      class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg font-data text-sm font-bold"
                      :class="(semana.numero || sIdx + 1) === currentWeek ? 'bg-wc-accent text-white' : 'bg-wc-bg-secondary text-wc-text-tertiary'"
                    >
                      {{ semana.numero || sIdx + 1 }}
                    </div>
                    <div>
                      <div class="flex items-center gap-2 flex-wrap">
                        <span class="text-lg font-semibold text-wc-text">Semana {{ semana.numero || sIdx + 1 }}</span>
                        <span
                          v-if="(semana.numero || sIdx + 1) === currentWeek"
                          class="rounded-full bg-wc-accent px-2 py-0.5 text-xs font-semibold tracking-widest uppercase text-white"
                        >Semana actual</span>
                        <span v-if="semana.fase" class="rounded-full bg-wc-bg-secondary px-2 py-0.5 text-[10px] font-medium text-wc-text-tertiary">{{ semana.fase }}</span>
                      </div>
                      <p v-if="(semana.dias || []).length > 0" class="mt-0.5 text-sm text-wc-text-secondary">
                        {{ (semana.dias || []).length }} dia{{ (semana.dias || []).length !== 1 ? 's' : '' }} de entrenamiento
                      </p>
                    </div>
                  </div>
                  <svg
                    class="h-4 w-4 shrink-0 text-wc-text-tertiary transition-transform duration-200"
                    :class="{ 'rotate-180': isWeekOpen(semana.numero || sIdx + 1) }"
                    fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                  ><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>
                </button>

                <!-- Week body -->
                <div v-show="isWeekOpen(semana.numero || sIdx + 1)">
                  <div class="space-y-3 border-t border-wc-border/50 px-4 pb-4 pt-4">
                    <div
                      v-for="(dia, dIdx) in (semana.dias || [])"
                      :key="dIdx"
                      class="wc-lift rounded-xl border border-wc-border bg-wc-bg-secondary"
                    >
                      <!-- Day header -->
                      <div class="flex items-center justify-between gap-3 px-4 py-3.5">
                        <div class="flex items-center gap-3 min-w-0">
                          <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-wc-accent/10">
                            <svg class="h-4 w-4 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 0a1.5 1.5 0 0 1-1.5-1.5v-3a1.5 1.5 0 0 1 1.5-1.5h1.5a1.5 1.5 0 0 1 1.5 1.5v3m-4.5 0a1.5 1.5 0 0 0-1.5 1.5v3a1.5 1.5 0 0 0 1.5 1.5h1.5a1.5 1.5 0 0 0 1.5-1.5v-3m12-4.5v3a1.5 1.5 0 0 1-1.5 1.5h-1.5m3 0a1.5 1.5 0 0 1-1.5 1.5v3a1.5 1.5 0 0 1-1.5 1.5h-1.5a1.5 1.5 0 0 1-1.5-1.5v-3m0-4.5a1.5 1.5 0 0 0-1.5-1.5h-1.5a1.5 1.5 0 0 0-1.5 1.5v3" /></svg>
                          </div>
                          <div class="min-w-0">
                            <p class="truncate text-base font-semibold text-wc-text">{{ dia.nombre || dia.name || dia.dia || ('Dia ' + (dIdx + 1)) }}</p>
                            <p v-if="(dia.ejercicios || []).length > 0" class="text-sm text-wc-text-secondary">
                              {{ (dia.ejercicios || []).length }} ejercicio{{ (dia.ejercicios || []).length !== 1 ? 's' : '' }}
                            </p>
                          </div>
                        </div>
                        <div class="flex shrink-0 items-center gap-2">
                          <span
                            v-if="dia.tipo || dia.grupo_muscular || dia.muscle_group"
                            class="rounded-full px-2.5 py-1 text-[10px] font-semibold"
                            :class="tipoBadgeClass(dia.tipo || dia.grupo_muscular || dia.muscle_group)"
                          >{{ dia.tipo || dia.grupo_muscular || dia.muscle_group }}</span>
                          <span
                            v-if="dia.duracion || (dia.ejercicios || []).length > 0"
                            class="hidden rounded-full bg-wc-bg-tertiary px-2.5 py-1 text-[10px] font-medium text-wc-text-tertiary sm:inline-flex items-center gap-1"
                          >
                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                            {{ dia.duracion || ('~' + Math.max((dia.ejercicios || []).length * 6, 15) + ' min') }}
                          </span>
                        </div>
                      </div>

                      <!-- Warmup -->
                      <div v-if="dia.calentamiento || dia.warmup" class="flex items-start gap-3 border-t border-amber-500/20 bg-gradient-to-r from-amber-500/[0.08] to-transparent px-4 py-3">
                        <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-amber-500/15">
                          <svg class="h-3.5 w-3.5 text-amber-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.362 5.214A8.252 8.252 0 0 1 12 21 8.25 8.25 0 0 1 6.038 7.047 8.287 8.287 0 0 0 9 9.601a8.983 8.983 0 0 1 3.361-6.867 8.21 8.21 0 0 0 3 2.48Z" /></svg>
                        </div>
                        <div>
                          <p class="text-xs font-semibold tracking-widest uppercase text-amber-400">Calentamiento</p>
                          <p class="mt-0.5 text-sm leading-relaxed text-wc-text-secondary">{{ dia.calentamiento || dia.warmup }}</p>
                        </div>
                      </div>

                      <!-- Workout launch button -->
                      <div v-if="(dia.ejercicios || []).length > 0" class="border-t border-wc-border/40 px-4 py-2.5">
                        <button
                          @click="goToWorkout(dIdx + 1)"
                          class="btn-ripple btn-press shadow-lg shadow-wc-accent/20 flex w-full items-center justify-center gap-2 rounded-lg bg-wc-accent px-4 py-2 text-sm font-semibold text-white hover:bg-red-700 transition-colors"
                        >
                          <svg class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.347a1.125 1.125 0 0 1 0 1.972l-11.54 6.347a1.125 1.125 0 0 1-1.667-.986V5.653Z"/></svg>
                          Entrenar este dia
                        </button>
                      </div>

                      <!-- Exercises list -->
                      <div v-if="(dia.ejercicios || []).length > 0" class="divide-y divide-wc-border/40 border-t border-wc-border/40">
                        <div v-for="(ejercicio, eIdx) in (dia.ejercicios || [])" :key="eIdx" class="flex items-start gap-3 px-4 py-3">
                          <!-- GIF thumbnail o número de índice -->
                          <div class="mt-0.5 shrink-0">
                            <div v-if="typeof ejercicio === 'object' && ejercicio.gif_url" class="relative h-12 w-12 overflow-hidden rounded-lg bg-wc-bg-secondary">
                              <img :src="ejercicio.gif_url" :alt="ejercicio.nombre || 'ejercicio'" class="h-full w-full object-cover" loading="lazy" />
                              <span class="absolute bottom-0 right-0 flex h-4 w-4 items-center justify-center rounded-tl-md bg-black/60 text-[9px] font-bold text-white">{{ eIdx + 1 }}</span>
                            </div>
                            <span v-else class="flex h-6 w-6 items-center justify-center rounded-md bg-wc-accent/10 font-sans text-[11px] font-bold text-wc-accent">{{ eIdx + 1 }}</span>
                          </div>
                          <div class="flex-1 min-w-0">
                            <p class="text-base font-medium text-wc-text">{{ typeof ejercicio === 'string' ? ejercicio : (ejercicio.nombre || ejercicio.name || ejercicio.ejercicio || 'Ejercicio') }}</p>
                            <div v-if="typeof ejercicio === 'object'" class="mt-1.5 flex flex-wrap gap-1.5">
                              <span
                                v-if="(ejercicio.series || ejercicio.sets) || (ejercicio.repeticiones || ejercicio.reps)"
                                class="rounded-full bg-wc-bg-tertiary px-2 py-0.5 text-sm font-medium text-wc-text-secondary"
                              >
                                <template v-if="(ejercicio.series || ejercicio.sets) && (ejercicio.repeticiones || ejercicio.reps)">
                                  {{ ejercicio.series || ejercicio.sets }} x {{ ejercicio.repeticiones || ejercicio.reps }}
                                </template>
                                <template v-else-if="ejercicio.series || ejercicio.sets">{{ ejercicio.series || ejercicio.sets }} series</template>
                                <template v-else>{{ ejercicio.repeticiones || ejercicio.reps }} reps</template>
                              </span>
                              <span
                                v-if="ejercicio.descanso || ejercicio.rest || ejercicio.rest_seconds"
                                class="inline-flex items-center gap-1 rounded-full bg-wc-bg-tertiary px-2 py-0.5 text-sm text-wc-text-tertiary"
                              >
                                <svg class="h-2.5 w-2.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                                {{ typeof (ejercicio.descanso || ejercicio.rest || ejercicio.rest_seconds) === 'number' ? (ejercicio.descanso || ejercicio.rest || ejercicio.rest_seconds) + 's' : (ejercicio.descanso || ejercicio.rest || ejercicio.rest_seconds) }}
                              </span>
                              <span
                                v-if="ejercicio.rir !== undefined && ejercicio.rir !== null"
                                class="rounded-full px-2 py-0.5 text-[10px] font-black"
                                :class="rirClass(ejercicio.rir)"
                              >RIR{{ ejercicio.rir }}</span>
                            </div>
                            <p v-if="typeof ejercicio === 'object' && (ejercicio.notas || ejercicio.notes)" class="mt-1.5 text-base leading-relaxed text-wc-text-secondary">{{ ejercicio.notas || ejercicio.notes }}</p>
                          </div>
                        </div>
                      </div>

                      <!-- Cooldown -->
                      <div v-if="dia.vuelta_calma || dia.cooldown" class="flex items-start gap-3 border-t border-sky-500/20 bg-gradient-to-r from-sky-500/[0.08] to-transparent px-4 py-3">
                        <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-sky-500/15">
                          <svg class="h-3.5 w-3.5 text-sky-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79Z" /></svg>
                        </div>
                        <div>
                          <p class="text-xs font-semibold tracking-widest uppercase text-sky-400">Vuelta a la calma</p>
                          <p class="mt-0.5 text-sm leading-relaxed text-wc-text-secondary">{{ dia.vuelta_calma || dia.cooldown }}</p>
                        </div>
                      </div>
                    </div>

                    <div v-if="(semana.dias || []).length === 0" class="rounded-xl border border-wc-border bg-wc-bg-secondary px-4 py-6 text-center">
                      <p class="text-sm text-wc-text-tertiary">Sin dias asignados esta semana.</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </template>

          <!-- Empty training -->
          <div v-else class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-8 text-center">
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-wc-bg-secondary">
              <svg class="h-8 w-8 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15a2.25 2.25 0 0 1 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z" /></svg>
            </div>
            <h2 class="mt-5 font-display text-xl tracking-wide text-wc-text">PLAN EN PREPARACION</h2>
            <p class="mt-2 text-sm text-wc-text-secondary">Tu coach esta disenando tu plan de entrenamiento.</p>
          </div>
        </div>

        <!-- ==================== TAB: HABITOS ==================== -->
        <div v-else-if="activeTab === 'habitos'">
          <div class="space-y-6">
            <!-- Compliance bar -->
            <div class="relative overflow-hidden rounded-xl border border-wc-accent/20 bg-gradient-to-br from-wc-accent/[0.08] via-wc-bg-tertiary to-transparent p-5">
              <div class="pointer-events-none absolute -right-6 -top-6 h-24 w-24 rounded-full bg-wc-accent/[0.06]"></div>
              <div class="pointer-events-none absolute -right-2 -top-2 h-12 w-12 rounded-full bg-wc-accent/10"></div>
              <div class="relative flex items-center justify-between">
                <div>
                  <h3 class="font-display text-lg tracking-wide text-wc-text">CUMPLIMIENTO MENSUAL</h3>
                  <p class="mt-0.5 text-sm text-wc-text-secondary">Dias con al menos 1 habito registrado este mes</p>
                </div>
                <span class="font-data text-4xl font-bold tabular-nums text-wc-accent">{{ habitCompliance }}%</span>
              </div>
              <div class="relative mt-3 h-2 w-full overflow-hidden rounded-full bg-wc-bg-secondary">
                <div class="h-full rounded-full bg-gradient-to-r from-wc-accent to-red-400 transition-all duration-700" :style="{ width: habitCompliance + '%' }"></div>
              </div>
            </div>

            <!-- Loading skeleton -->
            <template v-if="habitsLoading">
              <div v-for="n in 4" :key="n" class="animate-pulse rounded-xl border border-wc-border bg-wc-bg-tertiary h-28"></div>
            </template>

            <!-- Habit Cards -->
            <div v-else-if="habitCards.length > 0" class="grid gap-4 sm:grid-cols-2">
              <div
                v-for="habit in habitCards"
                :key="habit.type"
                class="rounded-xl border p-5 transition-all duration-300"
                :class="[
                  getHabitAccent(habit.type).border,
                  habit.completed ? getHabitAccent(habit.type).bg : 'bg-wc-bg-tertiary',
                ]"
              >
                <!-- Header row: icon + title + check-in button -->
                <div class="flex items-start justify-between gap-3">
                  <div class="flex items-start gap-3 min-w-0">
                    <!-- Icon -->
                    <div class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-xl" :class="getHabitAccent(habit.type).bg">
                      <svg v-if="habit.icon === 'water' || habit.icon === 'droplet'" class="h-4.5 w-4.5" :class="getHabitAccent(habit.type).text" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 21c-4.97 0-9-4.03-9-9 0-3.87 4.5-9.5 7.68-12.38a1.74 1.74 0 012.64 0C16.5 2.5 21 8.13 21 12c0 4.97-4.03 9-9 9z"/></svg>
                      <svg v-else-if="habit.icon === 'moon'" class="h-4.5 w-4.5" :class="getHabitAccent(habit.type).text" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12.79A9 9 0 1111.21 3a7 7 0 009.79 9.79z"/></svg>
                      <svg v-else-if="habit.icon === 'dumbbell'" class="h-4.5 w-4.5" :class="getHabitAccent(habit.type).text" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 0a1.5 1.5 0 0 1-1.5-1.5v-3a1.5 1.5 0 0 1 1.5-1.5h1.5a1.5 1.5 0 0 1 1.5 1.5v3m-4.5 0a1.5 1.5 0 0 0-1.5 1.5v3a1.5 1.5 0 0 0 1.5 1.5h1.5a1.5 1.5 0 0 0 1.5-1.5v-3m12-4.5v3a1.5 1.5 0 0 1-1.5 1.5h-1.5m3 0a1.5 1.5 0 0 1-1.5 1.5v3a1.5 1.5 0 0 1-1.5 1.5h-1.5a1.5 1.5 0 0 1-1.5-1.5v-3m0-4.5a1.5 1.5 0 0 0-1.5-1.5h-1.5a1.5 1.5 0 0 0-1.5 1.5v3" /></svg>
                      <svg v-else-if="habit.icon === 'apple' || habit.icon === 'utensils'" class="h-4.5 w-4.5" :class="getHabitAccent(habit.type).text" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2v6m0 0c1.66 0 3-1.34 3-3S13.66 2 12 2s-3 1.34-3 3 1.34 3 3 3zm0 0v14m6-20v8a2 2 0 01-2 2h-1v10"/></svg>
                      <svg v-else-if="habit.icon === 'pill'" class="h-4.5 w-4.5" :class="getHabitAccent(habit.type).text" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m10.5 6 6.5 6.5-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364L10.5 6Z M17 6.5l-4.5 4.5" /></svg>
                      <svg v-else class="h-4.5 w-4.5" :class="getHabitAccent(habit.type).text" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <div class="min-w-0">
                      <h4 class="font-display text-base tracking-wide text-wc-text leading-tight">{{ (habit.label || '').toUpperCase() }}</h4>
                      <p class="mt-0.5 text-sm text-wc-text-tertiary">
                        Racha: <span class="font-data font-semibold" :class="getHabitAccent(habit.type).text">{{ habit.streak }} dias</span>
                        <span class="mx-1 text-wc-border">·</span>
                        Cumplimiento: <span class="font-data font-semibold text-wc-text">{{ habit.average }}%</span>
                      </p>
                    </div>
                  </div>

                  <!-- Check-in button -->
                  <button
                    v-if="!habit.completed"
                    @click="doToggleHabit(habit.type)"
                    :disabled="habitsToggling[habit.type]"
                    class="shrink-0 flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-semibold transition-all duration-200 border"
                    :class="[
                      getHabitAccent(habit.type).border,
                      getHabitAccent(habit.type).text,
                      habitsToggling[habit.type]
                        ? 'opacity-50 cursor-not-allowed'
                        : 'hover:bg-wc-bg-secondary active:scale-95',
                    ]"
                  >
                    <svg v-if="habitsToggling[habit.type]" class="h-3.5 w-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
                    <svg v-else class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    Registrar hoy
                  </button>

                  <!-- Completed badge -->
                  <div
                    v-else
                    class="shrink-0 flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-semibold"
                    :class="[getHabitAccent(habit.type).bg, getHabitAccent(habit.type).text]"
                  >
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    Hecho hoy
                  </div>
                </div>

                <!-- Tip -->
                <p v-if="habit.tip" class="mt-3 text-sm text-wc-text-tertiary leading-relaxed border-t border-wc-border/50 pt-3">{{ habit.tip }}</p>
              </div>
            </div>

            <!-- Empty state -->
            <div v-else-if="!habitsLoading" class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-8 text-center">
              <p class="text-sm text-wc-text-secondary">Aun no tienes habitos configurados en tu plan.</p>
              <p class="mt-1 text-sm text-wc-text-tertiary">Tu coach activara los habitos cuando asigne tu plan.</p>
            </div>
          </div>
        </div>

        <!-- ==================== TAB: NUTRICION ==================== -->
        <div v-else-if="activeTab === 'nutricion'">
          <template v-if="canAccessNutricion && nutritionPlan">
            <div class="space-y-5">

              <!-- Hero: Calorías totales + objetivo -->
              <div
                v-if="(nutritionPlan.objetivo_cal || nutritionPlan.calorias_diarias || nutritionPlan.calorias)"
                class="rounded-xl border border-wc-border bg-wc-bg-tertiary overflow-hidden"
              >
                <div class="flex flex-col sm:flex-row sm:items-center gap-4 px-5 py-4">
                  <div class="flex items-center gap-4 flex-1">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-wc-accent/10">
                      <svg class="h-6 w-6 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.362 5.214A8.252 8.252 0 0 1 12 21 8.25 8.25 0 0 1 6.038 7.047 8.287 8.287 0 0 0 9 9.601a8.983 8.983 0 0 1 3.361-6.867 8.21 8.21 0 0 0 3 2.48Z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 18a3.75 3.75 0 0 0 .495-7.468 5.99 5.99 0 0 0-1.925 3.547 5.975 5.975 0 0 1-2.133-1.001A3.75 3.75 0 0 0 12 18Z"/>
                      </svg>
                    </div>
                    <div>
                      <p class="text-xs font-semibold tracking-widest uppercase text-wc-text-secondary">Calorías diarias</p>
                      <p class="font-data text-3xl font-bold text-wc-text leading-none mt-0.5">
                        {{ (nutritionPlan.objetivo_cal || nutritionPlan.calorias_diarias || nutritionPlan.calorias).toLocaleString() }}
                        <span class="text-sm font-normal text-wc-text-tertiary ml-1">kcal</span>
                      </p>
                    </div>
                  </div>
                  <div v-if="nutritionPlan.objetivo" class="sm:max-w-xs">
                    <span class="rounded-full border border-wc-accent/30 bg-wc-accent/5 px-3 py-1.5 text-xs text-wc-accent font-medium leading-snug">
                      {{ nutritionPlan.objetivo }}
                    </span>
                  </div>
                </div>
              </div>

              <!-- Macros visuales con barras de progreso -->
              <div v-if="macroP || macroC || macroF">
                <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
                  <p class="text-xs font-semibold tracking-widest uppercase text-wc-text-secondary mb-4">Distribución de macros</p>
                  <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">

                    <!-- Proteína -->
                    <div class="space-y-2">
                      <div class="flex items-end justify-between">
                        <div>
                          <p class="text-xs font-semibold tracking-widest uppercase text-wc-text-secondary">Proteína</p>
                          <p class="font-data text-2xl font-bold text-wc-text leading-none">
                            {{ macroP }}<span class="text-xs font-normal text-wc-text-tertiary">g</span>
                          </p>
                        </div>
                        <span class="font-data text-xs font-semibold text-wc-accent">
                          {{ Math.round((macroP * 4) / totalKcalNutrition * 100) }}%
                        </span>
                      </div>
                      <div class="h-2 w-full rounded-full bg-wc-bg-secondary overflow-hidden">
                        <div class="h-full rounded-full bg-wc-accent transition-all duration-700"
                             :style="{ width: Math.round((macroP * 4) / totalKcalNutrition * 100) + '%' }"></div>
                      </div>
                      <p class="text-sm text-wc-text-tertiary">{{ macroP * 4 }} kcal</p>
                    </div>

                    <!-- Carbohidratos -->
                    <div class="space-y-2">
                      <div class="flex items-end justify-between">
                        <div>
                          <p class="text-xs font-semibold tracking-widest uppercase text-wc-text-secondary">Carbohidratos</p>
                          <p class="font-data text-2xl font-bold text-wc-text leading-none">
                            {{ macroC }}<span class="text-xs font-normal text-wc-text-tertiary">g</span>
                          </p>
                        </div>
                        <span class="font-data text-xs font-semibold text-blue-400">
                          {{ Math.round((macroC * 4) / totalKcalNutrition * 100) }}%
                        </span>
                      </div>
                      <div class="h-2 w-full rounded-full bg-wc-bg-secondary overflow-hidden">
                        <div class="h-full rounded-full bg-blue-400 transition-all duration-700"
                             :style="{ width: Math.round((macroC * 4) / totalKcalNutrition * 100) + '%' }"></div>
                      </div>
                      <p class="text-sm text-wc-text-tertiary">{{ macroC * 4 }} kcal</p>
                    </div>

                    <!-- Grasas -->
                    <div class="space-y-2">
                      <div class="flex items-end justify-between">
                        <div>
                          <p class="text-xs font-semibold tracking-widest uppercase text-wc-text-secondary">Grasas</p>
                          <p class="font-data text-2xl font-bold text-wc-text leading-none">
                            {{ macroF }}<span class="text-xs font-normal text-wc-text-tertiary">g</span>
                          </p>
                        </div>
                        <span class="font-data text-xs font-semibold text-amber-400">
                          {{ Math.round((macroF * 9) / totalKcalNutrition * 100) }}%
                        </span>
                      </div>
                      <div class="h-2 w-full rounded-full bg-wc-bg-secondary overflow-hidden">
                        <div class="h-full rounded-full bg-amber-400 transition-all duration-700"
                             :style="{ width: Math.round((macroF * 9) / totalKcalNutrition * 100) + '%' }"></div>
                      </div>
                      <p class="text-sm text-wc-text-tertiary">{{ macroF * 9 }} kcal</p>
                    </div>

                  </div>
                </div>
              </div>

              <!-- Notas del coach -->
              <div v-if="nutritionPlan.notas_coach" class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
                <div class="flex items-start gap-3">
                  <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-wc-accent/10">
                    <svg class="h-5 w-5 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 0 1 .865-.501 48.172 48.172 0 0 0 3.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z"/>
                    </svg>
                  </div>
                  <div class="min-w-0 flex-1">
                    <p class="text-xs font-semibold tracking-widest uppercase text-wc-text-secondary mb-1.5">Notas de tu coach</p>
                    <p class="text-base leading-relaxed text-wc-text-secondary">{{ nutritionPlan.notas_coach }}</p>
                  </div>
                </div>
              </div>

              <!-- Tips nutricionales -->
              <div v-if="nutritionPlan.tips && nutritionPlan.tips.length > 0" class="rounded-xl border border-emerald-500/20 bg-emerald-500/[0.04] p-5">
                <p class="text-xs font-semibold tracking-widest uppercase text-emerald-400 mb-3">Consejos de tu coach</p>
                <ul class="space-y-2.5">
                  <li v-for="(tip, tIdx) in nutritionPlan.tips" :key="tIdx" class="flex items-start gap-2.5">
                    <svg class="mt-0.5 h-4 w-4 shrink-0 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
                    </svg>
                    <span class="text-sm leading-relaxed text-wc-text-secondary">{{ tip }}</span>
                  </li>
                </ul>
              </div>

              <!-- Comidas (formato completo: comidas[]) — collapsible meal cards -->
              <div v-if="nutritionPlan.comidas && nutritionPlan.comidas.length > 0" class="space-y-3">
                <p class="text-xs font-semibold tracking-widest uppercase text-wc-text-secondary px-0.5">Plan de comidas</p>
                <div
                  v-for="(meal, mIdx) in nutritionPlan.comidas"
                  :key="mIdx"
                  class="overflow-hidden rounded-xl border border-wc-border bg-wc-bg-secondary"
                >
                  <!-- Header (clickable) -->
                  <button
                    @click="toggleNutrMeal(mIdx)"
                    class="flex w-full items-center gap-3 p-4 text-left transition hover:bg-wc-bg-tertiary"
                  >
                    <!-- Colored number badge -->
                    <div
                      class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg"
                      :class="getNutrMealColor(meal.nombre || meal.name)"
                    >
                      <span class="font-data text-sm font-bold">{{ meal.numero ?? (mIdx + 1) }}</span>
                    </div>

                    <!-- Name -->
                    <div class="min-w-0 flex-1">
                      <p class="truncate font-display text-sm tracking-wide text-wc-text">
                        {{ (meal.nombre || meal.name || ('Comida ' + (mIdx + 1))).toUpperCase() }}
                      </p>
                      <p v-if="meal.hora || meal.time" class="text-sm text-wc-text-tertiary">{{ meal.hora || meal.time }}</p>
                    </div>

                    <!-- Macro chips (desktop) -->
                    <div class="hidden items-center gap-1.5 sm:flex">
                      <span
                        v-if="meal.macros && (meal.macros.proteina > 0 || meal.macros.proteina_g > 0)"
                        class="rounded-full px-2 py-0.5 text-[10px] font-semibold"
                        style="background:rgba(220,38,38,0.12); color:#F87171;"
                      >P {{ meal.macros.proteina || meal.macros.proteina_g }}g</span>
                      <span
                        v-if="meal.macros && (meal.macros.carbohidratos > 0 || meal.macros.carbohidratos_g > 0)"
                        class="rounded-full px-2 py-0.5 text-[10px] font-semibold"
                        style="background:rgba(59,130,246,0.12); color:#60A5FA;"
                      >C {{ meal.macros.carbohidratos || meal.macros.carbohidratos_g }}g</span>
                      <span
                        v-if="meal.macros && (meal.macros.grasas > 0 || meal.macros.grasas_g > 0)"
                        class="rounded-full px-2 py-0.5 text-[10px] font-semibold"
                        style="background:rgba(245,158,11,0.12); color:#FBBF24;"
                      >G {{ meal.macros.grasas || meal.macros.grasas_g }}g</span>
                    </div>

                    <!-- kcal + chevron -->
                    <div class="ml-2 flex shrink-0 items-center gap-3">
                      <span
                        v-if="meal.kcal || meal.calorias || meal.calories"
                        class="font-data text-sm font-bold tabular-nums text-wc-text"
                      >{{ meal.kcal || meal.calorias || meal.calories }}<span class="text-xs font-normal text-wc-text-tertiary"> kcal</span></span>
                      <svg
                        class="h-4 w-4 text-wc-text-tertiary transition-transform duration-200"
                        :class="{ 'rotate-180': openNutrMeals[mIdx] }"
                        fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"
                      >
                        <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                      </svg>
                    </div>
                  </button>

                  <!-- Expandable body -->
                  <Transition name="accordion">
                    <div v-show="openNutrMeals[mIdx]" class="border-t border-wc-border">
                      <div class="space-y-3 p-4">

                        <!-- Mobile macro chips -->
                        <div class="flex flex-wrap gap-1.5 sm:hidden">
                          <span
                            v-if="meal.macros && (meal.macros.proteina > 0 || meal.macros.proteina_g > 0)"
                            class="rounded-full px-2.5 py-1 text-xs font-semibold"
                            style="background:rgba(220,38,38,0.12); color:#F87171;"
                          >P {{ meal.macros.proteina || meal.macros.proteina_g }}g</span>
                          <span
                            v-if="meal.macros && (meal.macros.carbohidratos > 0 || meal.macros.carbohidratos_g > 0)"
                            class="rounded-full px-2.5 py-1 text-xs font-semibold"
                            style="background:rgba(59,130,246,0.12); color:#60A5FA;"
                          >C {{ meal.macros.carbohidratos || meal.macros.carbohidratos_g }}g</span>
                          <span
                            v-if="meal.macros && (meal.macros.grasas > 0 || meal.macros.grasas_g > 0)"
                            class="rounded-full px-2.5 py-1 text-xs font-semibold"
                            style="background:rgba(245,158,11,0.12); color:#FBBF24;"
                          >G {{ meal.macros.grasas || meal.macros.grasas_g }}g</span>
                        </div>

                        <!-- Multi-option tabs (opcion_a / b / c) -->
                        <template v-if="getNutrMealOpciones(meal)">
                          <!-- Option selector -->
                          <div class="flex gap-1.5">
                            <button
                              v-for="(optAlimentos, optKey) in Object.fromEntries(Object.entries(getNutrMealOpciones(meal)).filter(([,v]) => v && v.length > 0))"
                              :key="optKey"
                              @click="setNutrOption(mIdx, optKey)"
                              class="rounded-full px-3 py-1 text-xs font-semibold transition"
                              :class="(activeNutrOption[mIdx] || 'a') === optKey
                                ? 'bg-wc-accent text-white'
                                : 'bg-wc-bg-tertiary text-wc-text-secondary hover:text-wc-text border border-wc-border'"
                            >Opción {{ optKey.toUpperCase() }}</button>
                          </div>
                          <!-- Option alimentos -->
                          <ul class="space-y-1.5">
                            <li
                              v-for="(alimento, ai) in (getNutrMealOpciones(meal)[(activeNutrOption[mIdx] || 'a')] || [])"
                              :key="ai"
                              class="flex items-start gap-2.5"
                            >
                              <span class="mt-2 h-1 w-1 shrink-0 rounded-full bg-wc-accent"></span>
                              <span class="text-sm leading-relaxed text-wc-text-secondary">{{ formatNutrAlimento(alimento) }}</span>
                            </li>
                          </ul>
                        </template>

                        <!-- Standard alimentos list -->
                        <ul
                          v-else-if="(meal.alimentos || meal.foods || []).length > 0"
                          class="space-y-1.5"
                        >
                          <li
                            v-for="(alimento, ai) in (meal.alimentos || meal.foods)"
                            :key="ai"
                            class="flex items-start gap-2.5"
                          >
                            <span v-if="foodIcon(alimento)" class="shrink-0 text-base leading-none">{{ foodIcon(alimento) }}</span>
                            <span v-else class="mt-2 h-1 w-1 shrink-0 rounded-full bg-wc-accent"></span>
                            <span class="text-sm leading-relaxed text-wc-text-secondary">{{ formatNutrAlimento(alimento) }}</span>
                          </li>
                        </ul>

                        <!-- Notas -->
                        <div v-if="meal.notas" class="rounded-lg border border-wc-border bg-wc-bg-tertiary px-3.5 py-3">
                          <p class="text-sm leading-relaxed text-wc-text-tertiary">{{ meal.notas }}</p>
                        </div>
                      </div>
                    </div>
                  </Transition>
                </div>
              </div>

              <!-- Comidas sugeridas (formato RISE: comidas_sugeridas[]) — also collapsible -->
              <div v-else-if="nutritionPlan.comidas_sugeridas && nutritionPlan.comidas_sugeridas.length > 0" class="space-y-3">
                <p class="text-xs font-semibold tracking-widest uppercase text-wc-text-secondary px-0.5">Comidas sugeridas</p>
                <div
                  v-for="(meal, mIdx) in nutritionPlan.comidas_sugeridas"
                  :key="mIdx"
                  class="overflow-hidden rounded-xl border border-wc-border bg-wc-bg-secondary"
                >
                  <button
                    @click="toggleNutrMeal('s' + mIdx)"
                    class="flex w-full items-center gap-3 p-4 text-left transition hover:bg-wc-bg-tertiary"
                  >
                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg" :class="getNutrMealColor(meal.nombre || meal.name)">
                      <span class="font-data text-sm font-bold">{{ mIdx + 1 }}</span>
                    </div>
                    <div class="min-w-0 flex-1">
                      <p class="truncate font-display text-sm tracking-wide text-wc-text">
                        {{ (meal.nombre || meal.name || ('Comida ' + (mIdx + 1))).toUpperCase() }}
                      </p>
                    </div>
                    <svg
                      class="h-4 w-4 text-wc-text-tertiary transition-transform duration-200"
                      :class="{ 'rotate-180': openNutrMeals['s' + mIdx] }"
                      fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"
                    >
                      <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                    </svg>
                  </button>
                  <Transition name="accordion">
                    <div v-show="openNutrMeals['s' + mIdx]" class="border-t border-wc-border">
                      <div class="p-4">
                        <p v-if="meal.descripcion" class="text-sm leading-relaxed text-wc-text-secondary">{{ meal.descripcion }}</p>
                        <ul v-if="(meal.alimentos || meal.foods || []).length > 0" class="space-y-1.5">
                          <li v-for="(alimento, ai) in (meal.alimentos || meal.foods)" :key="ai" class="flex items-start gap-2.5">
                            <span v-if="foodIcon(alimento)" class="shrink-0 text-base leading-none">{{ foodIcon(alimento) }}</span>
                            <span v-else class="mt-2 h-1 w-1 shrink-0 rounded-full bg-wc-accent"></span>
                            <span class="text-sm leading-relaxed text-wc-text-secondary">{{ formatNutrAlimento(alimento) }}</span>
                          </li>
                        </ul>
                      </div>
                    </div>
                  </Transition>
                </div>
              </div>

              <!-- Empty state: no meals at all -->
              <div v-else class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-6 text-center">
                <p class="text-sm text-wc-text-secondary">Tu coach esta preparando tu plan de nutricion.</p>
              </div>

            </div>
          </template>

          <div v-else-if="!canAccessNutricion" class="rounded-xl border border-wc-accent/20 bg-wc-accent/5 p-8 text-center">
            <p class="font-display text-xl text-wc-text">Nutricion Premium</p>
            <p class="mt-2 text-sm text-wc-text-secondary">Disponible en planes Metodo y Elite.</p>
            <a href="/planes" class="mt-4 inline-block rounded-full bg-wc-accent px-6 py-2.5 text-sm font-semibold text-white">Upgrade</a>
          </div>

          <div v-else class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-6 text-center">
            <p class="text-sm text-wc-text-secondary">Tu coach esta preparando tu plan de nutricion.</p>
          </div>
        </div>

        <!-- ==================== TAB: SUPLEMENTACION ==================== -->
        <div v-else-if="activeTab === 'suplementacion'">
          <template v-if="canAccessNutricion && supplementPlan">
            <div class="space-y-6 wc-stagger-enter">
              <!-- HERO HEADER -->
              <div class="relative overflow-hidden rounded-2xl border border-wc-border wc-card-hero wc-lift">
                <div class="wc-orb-tr"></div>
                <div class="wc-grain absolute inset-0 pointer-events-none opacity-40"></div>
                <div class="wc-topline"></div>
                <div class="relative p-6 sm:p-8">
                  <div class="flex items-start gap-4">
                    <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl border border-wc-accent/30 bg-wc-accent/10 text-3xl shadow-lg shadow-wc-accent/10">
                      <span>🧬</span>
                    </div>
                    <div class="min-w-0 flex-1">
                      <p class="text-[10px] font-bold tracking-[0.25em] uppercase text-wc-accent">Stack Personalizado</p>
                      <h2 class="mt-1 font-display text-3xl sm:text-4xl tracking-wide wc-text-gradient leading-none">SUPLEMENTACION</h2>
                      <p v-if="supplementPlan.descripcion_protocolo || supplementPlan.descripcion" class="mt-3 text-sm leading-relaxed text-wc-text-secondary">{{ supplementPlan.descripcion_protocolo || supplementPlan.descripcion }}</p>
                      <p v-if="supplementPlan.perfil_cliente" class="mt-1.5 text-xs italic text-wc-text-tertiary">{{ supplementPlan.perfil_cliente }}</p>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Advertencia -->
              <div v-if="supplementPlan.advertencia" class="rounded-xl border border-amber-500/30 bg-amber-500/[0.08] p-4">
                <div class="flex items-start gap-2">
                  <svg class="mt-0.5 h-4 w-4 shrink-0 text-amber-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/></svg>
                  <p class="text-sm leading-relaxed text-amber-300">{{ supplementPlan.advertencia }}</p>
                </div>
              </div>

              <!-- STRUCTURE A: Categorias (IA format) -->
              <template v-if="supplementPlan.categorias && supplementPlan.categorias.length > 0">
                <div class="space-y-6">
                  <div v-for="(cat, cIdx) in supplementPlan.categorias" :key="cIdx">
                    <!-- Category header -->
                    <div class="mb-3 flex items-center gap-3 px-1">
                      <span class="text-2xl">{{ getCatStyle(cat.nombre || '').icon }}</span>
                      <h3 class="font-display text-xl tracking-wider" :class="getCatStyle(cat.nombre || '').color">{{ (cat.nombre || 'Suplementos').toUpperCase() }}</h3>
                      <div class="h-px flex-1" :class="getCatStyle(cat.nombre || '').bg"></div>
                      <span class="rounded-full border border-wc-border bg-wc-bg-secondary px-2.5 py-0.5 text-[10px] font-bold text-wc-text-tertiary">
                        {{ (cat.suplementos || []).length }}
                      </span>
                    </div>
                    <!-- Supplement cards grid -->
                    <div class="grid gap-3 sm:grid-cols-2">
                      <div
                        v-for="(sup, sIdx) in (cat.suplementos || [])"
                        :key="sIdx"
                        class="group relative overflow-hidden rounded-2xl border border-wc-border bg-wc-bg-tertiary p-5 transition-all duration-300 hover:border-wc-accent/40 hover:shadow-xl hover:shadow-wc-accent/10 wc-lift"
                      >
                        <div class="wc-topline opacity-60 group-hover:opacity-100 transition-opacity"></div>
                        <div class="pointer-events-none absolute -right-8 -top-8 h-32 w-32 rounded-full bg-wc-accent/0 blur-3xl transition-all duration-500 group-hover:bg-wc-accent/20"></div>
                        <div class="relative flex items-start gap-4">
                          <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl border border-wc-border bg-wc-bg-secondary transition-transform duration-300 group-hover:scale-110 group-hover:border-wc-accent/40">
                            <component :is="supplementIcon(typeof sup === 'string' ? sup : (sup.nombre || sup.name))" weight="duotone" class="h-8 w-8 text-wc-accent" />
                          </div>
                          <div class="min-w-0 flex-1">
                            <div class="flex items-start justify-between gap-2">
                              <h4 class="font-display text-lg leading-tight tracking-wide text-wc-text">{{ (typeof sup === 'string' ? sup : (sup.nombre || sup.name || 'Suplemento')).toUpperCase() }}</h4>
                              <span
                                v-if="typeof sup === 'object' && sup.prioridad"
                                class="shrink-0 rounded-full px-2 py-0.5 text-[9px] font-black uppercase tracking-wider wc-pr-badge"
                                :class="[prioridadStyle(sup.prioridad).text, prioridadStyle(sup.prioridad).bg]"
                              >{{ sup.prioridad }}</span>
                            </div>
                            <div v-if="typeof sup === 'object' && (sup.dosis || sup.dose)" class="mt-2">
                              <p class="font-data text-2xl font-black leading-none wc-text-gradient">{{ sup.dosis || sup.dose }}</p>
                              <p class="mt-0.5 text-[9px] font-bold uppercase tracking-widest text-wc-text-tertiary">Dosis</p>
                            </div>
                            <div v-if="typeof sup === 'object' && (sup.timing || sup.momento || sup.horario)" class="mt-3 inline-flex items-center gap-1.5 rounded-full border border-wc-border bg-wc-bg-secondary px-3 py-1">
                              <span class="text-sm">{{ getTimingIcon(sup.timing || sup.momento || sup.horario) }}</span>
                              <span class="text-xs font-semibold text-wc-text-secondary">{{ sup.timing || sup.momento || sup.horario }}</span>
                            </div>
                            <p v-if="typeof sup === 'object' && (sup.frecuencia || sup.frequency)" class="mt-2 text-xs text-wc-text-tertiary">
                              <span class="font-semibold text-wc-text-secondary">Frecuencia:</span> {{ sup.frecuencia || sup.frequency }}
                            </p>
                            <p v-if="typeof sup === 'object' && (sup.notas || sup.notes)" class="mt-2 text-xs leading-relaxed text-wc-text-tertiary">{{ sup.notas || sup.notes }}</p>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </template>

              <!-- STRUCTURE B: Flat list -->
              <template v-else-if="(supplementPlan.suplementos || supplementPlan.supplements || supplementPlan.protocolo || []).length > 0">
                <div>
                  <div class="mb-4 flex items-center gap-3 px-1">
                    <h3 class="font-display text-2xl tracking-wider wc-text-gradient">PROTOCOLO</h3>
                    <div class="h-px flex-1 bg-wc-border"></div>
                    <span class="rounded-full bg-wc-accent/10 px-3 py-1 text-xs font-bold text-wc-accent">{{ (supplementPlan.suplementos || supplementPlan.supplements || supplementPlan.protocolo || []).length }} items</span>
                  </div>
                  <div class="grid gap-3 sm:grid-cols-2">
                    <div
                      v-for="(sup, sIdx) in (supplementPlan.suplementos || supplementPlan.supplements || supplementPlan.protocolo || [])"
                      :key="sIdx"
                      class="group relative overflow-hidden rounded-2xl border border-wc-border bg-wc-bg-tertiary p-5 transition-all duration-300 hover:border-wc-accent/40 hover:shadow-xl hover:shadow-wc-accent/10 wc-lift"
                    >
                      <div class="wc-topline opacity-60 group-hover:opacity-100 transition-opacity"></div>
                      <div class="pointer-events-none absolute -right-8 -top-8 h-32 w-32 rounded-full bg-wc-accent/0 blur-3xl transition-all duration-500 group-hover:bg-wc-accent/20"></div>
                      <div class="relative flex items-start gap-4">
                        <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl border border-wc-border bg-wc-bg-secondary transition-transform duration-300 group-hover:scale-110 group-hover:border-wc-accent/40">
                          <component :is="supplementIcon(typeof sup === 'string' ? sup : (sup.nombre || sup.name))" weight="duotone" class="h-8 w-8 text-wc-accent" />
                        </div>
                        <div class="min-w-0 flex-1">
                          <div class="flex items-start justify-between gap-2">
                            <h4 class="font-display text-lg leading-tight tracking-wide text-wc-text">{{ (typeof sup === 'string' ? sup : (sup.nombre || sup.name || 'Suplemento')).toUpperCase() }}</h4>
                            <span
                              v-if="typeof sup === 'object' && sup.prioridad"
                              class="shrink-0 rounded-full px-2 py-0.5 text-[9px] font-black uppercase tracking-wider wc-pr-badge"
                              :class="[prioridadStyle(sup.prioridad).text, prioridadStyle(sup.prioridad).bg]"
                            >{{ sup.prioridad }}</span>
                          </div>
                          <div v-if="typeof sup === 'object' && (sup.dosis || sup.dose)" class="mt-2">
                            <p class="font-data text-2xl font-black leading-none wc-text-gradient">{{ sup.dosis || sup.dose }}</p>
                            <p class="mt-0.5 text-[9px] font-bold uppercase tracking-widest text-wc-text-tertiary">Dosis</p>
                          </div>
                          <div v-if="typeof sup === 'object' && (sup.momento || sup.timing || sup.horario)" class="mt-3 inline-flex items-center gap-1.5 rounded-full border border-wc-border bg-wc-bg-secondary px-3 py-1">
                            <span class="text-sm">{{ getTimingIcon(sup.momento || sup.timing || sup.horario) }}</span>
                            <span class="text-xs font-semibold text-wc-text-secondary">{{ sup.momento || sup.timing || sup.horario }}</span>
                          </div>
                          <p v-if="typeof sup === 'object' && (sup.frecuencia || sup.frequency)" class="mt-2 text-xs text-wc-text-tertiary">
                            <span class="font-semibold text-wc-text-secondary">Frecuencia:</span> {{ sup.frecuencia || sup.frequency }}
                          </p>
                          <p v-if="typeof sup === 'object' && (sup.notas || sup.notes)" class="mt-2 text-xs leading-relaxed text-wc-text-tertiary">{{ sup.notas || sup.notes }}</p>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </template>

              <div v-else class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-6 text-center">
                <p class="text-sm text-wc-text-secondary">Tu coach esta preparando tu protocolo de suplementacion.</p>
              </div>

              <!-- Timing Diario (timing_diario[]) -->
              <div v-if="supplementPlan.timing_diario && supplementPlan.timing_diario.length > 0" class="overflow-hidden rounded-xl border border-wc-border bg-wc-bg-tertiary">
                <div class="border-b border-wc-border px-5 py-4">
                  <h3 class="font-display text-sm tracking-wider text-wc-text">PROTOCOLO DIARIO</h3>
                </div>
                <div class="divide-y divide-wc-border">
                  <div v-for="(momento, mIdx) in supplementPlan.timing_diario" :key="mIdx" class="flex items-start gap-4 px-5 py-3.5">
                    <span class="mt-0.5 shrink-0 text-base">{{ getTimingIcon(typeof momento === 'object' ? (momento.momento || '') : '') }}</span>
                    <div class="min-w-0 flex-1">
                      <p class="text-sm font-semibold text-wc-text">{{ typeof momento === 'object' ? (momento.momento || '') : '' }}</p>
                      <p class="mt-0.5 text-sm text-wc-text-secondary">{{ typeof momento === 'object' ? (momento.suplementos || '') : momento }}</p>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Timing Groups (old format: timing or horarios) -->
              <div v-if="(supplementPlan.timing || supplementPlan.horarios) && typeof (supplementPlan.timing || supplementPlan.horarios) === 'object' && !Array.isArray(supplementPlan.timing || supplementPlan.horarios)" class="space-y-3">
                <h3 class="font-display text-sm tracking-wider text-wc-text-tertiary uppercase px-1">PROTOCOLO POR MOMENTO</h3>
                <div v-for="(items, moment) in (supplementPlan.timing || supplementPlan.horarios)" :key="moment" class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-4">
                  <p class="mb-3 font-display text-sm tracking-wide text-wc-text">{{ getTimingIcon(moment) }} {{ moment.toUpperCase() }}</p>
                  <ul class="space-y-1.5">
                    <li v-for="(item, iIdx) in (Array.isArray(items) ? items : [items])" :key="iIdx" class="flex items-center gap-2 text-sm text-wc-text-secondary">
                      <span class="h-1.5 w-1.5 rounded-full bg-wc-accent shrink-0"></span>
                      {{ typeof item === 'object' ? (item.nombre || item.name || JSON.stringify(item)) : item }}
                    </li>
                  </ul>
                </div>
              </div>

              <!-- Sinergias -->
              <div v-if="supplementPlan.sinergias && supplementPlan.sinergias.length > 0" class="overflow-hidden rounded-xl border border-sky-500/20 bg-sky-500/5">
                <div class="border-b border-sky-500/20 px-5 py-3">
                  <h3 class="font-display text-sm tracking-wider text-sky-400">SINERGIAS CLAVE</h3>
                </div>
                <div class="divide-y divide-sky-500/10">
                  <div v-for="(sinergia, sIdx) in supplementPlan.sinergias" :key="sIdx" class="px-5 py-4">
                    <p class="text-sm font-semibold text-sky-300">{{ sinergia.titulo || '' }}</p>
                    <p class="mt-1 text-sm leading-relaxed text-wc-text-secondary">{{ sinergia.explicacion || '' }}</p>
                  </div>
                </div>
              </div>

              <!-- Coach notes -->
              <div v-if="supplementPlan.notas_coach || supplementPlan.coach_notes || supplementPlan.notas" class="rounded-xl border-l-4 border-wc-accent bg-wc-bg-tertiary p-5">
                <p class="mb-2 text-sm font-semibold uppercase tracking-wider text-wc-accent">Notas del coach</p>
                <p class="text-sm leading-relaxed text-wc-text-secondary">{{ supplementPlan.notas_coach || supplementPlan.coach_notes || supplementPlan.notas }}</p>
                <p v-if="supplementPlan.mensaje_final" class="mt-3 text-sm italic text-wc-text-tertiary">{{ supplementPlan.mensaje_final }}</p>
              </div>
            </div>
          </template>

          <div v-else-if="!canAccessNutricion" class="rounded-xl border border-wc-accent/20 bg-wc-accent/5 p-8 text-center">
            <p class="font-display text-xl text-wc-text">Suplementacion Premium</p>
            <p class="mt-2 text-sm text-wc-text-secondary">Disponible en planes Metodo y Elite.</p>
            <a href="/planes" class="mt-4 inline-block rounded-full bg-wc-accent px-6 py-2.5 text-sm font-semibold text-white">Upgrade</a>
          </div>

          <div v-else class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-6 text-center">
            <p class="text-sm text-wc-text-secondary">Tu coach esta preparando tu protocolo de suplementacion.</p>
          </div>
        </div>

        <!-- ==================== TAB: CICLO ==================== -->
        <div v-else-if="activeTab === 'ciclo'">
          <!-- Locked -->
          <div v-if="!canAccessElite" class="rounded-xl border border-wc-accent/20 bg-wc-accent/5 p-8 text-center">
            <p class="font-display text-xl text-wc-text">Ciclo Hormonal Personalizado</p>
            <p class="mt-2 text-sm text-wc-text-secondary">Disponible exclusivamente en el plan Elite.</p>
            <a href="/planes" class="mt-4 inline-block rounded-full bg-wc-accent px-6 py-2.5 text-sm font-semibold text-white">Upgrade a Elite</a>
          </div>

          <!-- Masculine: Steroid/AE Protocol -->
          <template v-else-if="cicloPlan && cicloPlan.compounds">
            <div class="space-y-5">
              <!-- Warning -->
              <div v-if="cicloPlan.warning || cicloPlan.advertencia" class="rounded-xl border border-amber-500/30 bg-amber-500/[0.08] p-4">
                <div class="flex items-start gap-3">
                  <svg class="mt-0.5 h-5 w-5 shrink-0 text-amber-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/></svg>
                  <p class="text-sm leading-relaxed text-amber-300">{{ cicloPlan.warning || cicloPlan.advertencia }}</p>
                </div>
              </div>

              <!-- Protocol header -->
              <div class="rounded-2xl border border-wc-border bg-wc-bg-tertiary p-5">
                <div class="flex items-start justify-between gap-4">
                  <div>
                    <p class="text-xs font-semibold tracking-widest uppercase text-wc-text-secondary">Protocolo Activo</p>
                    <h2 class="mt-1 font-display text-2xl tracking-wide text-wc-text">{{ (cicloPlan.name || cicloPlan.nombre || 'Protocolo Hormonal').toUpperCase() }}</h2>
                    <p v-if="cicloPlan.duration || cicloPlan.duracion" class="mt-1 text-sm text-wc-text-secondary">Duracion: <span class="font-semibold text-wc-text">{{ cicloPlan.duration || cicloPlan.duracion }}</span></p>
                    <p v-if="cicloPlan.descripcion_protocolo || cicloPlan.descripcion" class="mt-2 text-sm leading-relaxed text-wc-text-tertiary">{{ cicloPlan.descripcion_protocolo || cicloPlan.descripcion }}</p>
                  </div>
                  <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-wc-accent/10">
                    <svg class="h-7 w-7 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v5.714a2.25 2.25 0 0 1-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 0 1 4.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0 1 12 15a9.065 9.065 0 0 1-6.23-.693L5 14.5m14.8.8 1.402 1.402c1 1 .03 2.798-1.421 2.798H4.062c-1.451 0-2.42-1.798-1.42-2.798L4 14.5" /></svg>
                  </div>
                </div>

                <!-- Metrics strip -->
                <div v-if="cicloPlan.metricas" class="mt-4 grid grid-cols-4 gap-2 border-t border-wc-border pt-4">
                  <div v-if="cicloPlan.metricas.duracion" class="text-center"><p class="font-data text-lg font-black text-wc-accent">{{ cicloPlan.metricas.duracion }}</p><p class="text-[9px] uppercase tracking-wider text-wc-text-tertiary">Duracion</p></div>
                  <div v-if="cicloPlan.metricas.compuestos" class="text-center"><p class="font-data text-lg font-black text-wc-text">{{ cicloPlan.metricas.compuestos }}</p><p class="text-[9px] uppercase tracking-wider text-wc-text-tertiary">Compuestos</p></div>
                  <div v-if="cicloPlan.metricas.fases" class="text-center"><p class="font-data text-lg font-black text-wc-text">{{ cicloPlan.metricas.fases }}</p><p class="text-[9px] uppercase tracking-wider text-wc-text-tertiary">Fases</p></div>
                  <div v-if="cicloPlan.metricas.labs_requeridos" class="text-center"><p class="font-data text-lg font-black text-wc-text">{{ cicloPlan.metricas.labs_requeridos }}</p><p class="text-[9px] uppercase tracking-wider text-wc-text-tertiary">Labs req.</p></div>
                </div>
              </div>

              <!-- Compounds Table -->
              <div v-if="cicloPlan.compounds && cicloPlan.compounds.length > 0" class="overflow-hidden rounded-xl border border-wc-border bg-wc-bg-tertiary">
                <div class="border-b border-wc-border px-5 py-4">
                  <h3 class="font-display text-sm tracking-wider text-wc-text">COMPUESTOS</h3>
                </div>
                <div class="divide-y divide-wc-border">
                  <div v-for="(compound, cIdx) in cicloPlan.compounds" :key="cIdx" class="px-5 py-4">
                    <div class="flex flex-wrap items-center gap-2">
                      <span class="font-semibold text-wc-text">{{ typeof compound === 'object' ? (compound.nombre || compound.name || '') : compound }}</span>
                      <span v-if="typeof compound === 'object' && (compound.dosis || compound.dose)" class="rounded bg-wc-accent/10 px-2 py-0.5 font-data text-xs font-bold text-wc-accent">{{ compound.dosis || compound.dose }}</span>
                      <span v-if="typeof compound === 'object' && (compound.semanas || compound.weeks)" class="rounded-full border border-wc-border px-2 py-0.5 text-[10px] text-wc-text-tertiary">Sem {{ compound.semanas || compound.weeks }}</span>
                    </div>
                    <p v-if="typeof compound === 'object' && (compound.frecuencia || compound.frequency)" class="mt-1 text-sm text-wc-text-secondary">&#x1F5D3;&#xFE0F; {{ compound.frecuencia || compound.frequency }}</p>
                    <p v-if="typeof compound === 'object' && (compound.notas || compound.notes)" class="mt-1 text-sm leading-relaxed text-wc-text-tertiary">{{ compound.notas || compound.notes }}</p>
                  </div>
                </div>
              </div>

              <!-- Phase timeline -->
              <div v-if="(cicloPlan.phases || cicloPlan.fases || []).length > 0">
                <h3 class="mb-3 font-display text-sm tracking-wider text-wc-text-tertiary uppercase px-1">FASES DEL CICLO</h3>
                <!-- Visual timeline bar -->
                <div class="mb-4 flex h-2.5 w-full overflow-hidden rounded-full">
                  <div
                    v-for="(phase, pi) in (cicloPlan.phases || cicloPlan.fases)"
                    :key="pi"
                    class="h-full flex-1"
                    :class="getPhaseColor(typeof phase === 'object' ? (phase.nombre || phase.name || '') : phase).dot"
                    :style="{ opacity: 0.5 + (pi / (cicloPlan.phases || cicloPlan.fases).length) * 0.5 }"
                  ></div>
                </div>
                <div class="grid gap-3 sm:grid-cols-2">
                  <div
                    v-for="(phase, pIdx) in (cicloPlan.phases || cicloPlan.fases)"
                    :key="pIdx"
                    class="rounded-xl border p-4"
                    :class="[getPhaseColor(typeof phase === 'object' ? (phase.nombre || phase.name || '') : phase).border, getPhaseColor(typeof phase === 'object' ? (phase.nombre || phase.name || '') : phase).bg]"
                  >
                    <div class="flex items-center gap-2 mb-2">
                      <div class="h-2 w-2 rounded-full shrink-0" :class="getPhaseColor(typeof phase === 'object' ? (phase.nombre || phase.name || '') : phase).dot"></div>
                      <p class="font-display text-sm tracking-wide" :class="getPhaseColor(typeof phase === 'object' ? (phase.nombre || phase.name || '') : phase).text">{{ (typeof phase === 'object' ? (phase.nombre || phase.name || '') : phase).toUpperCase() }}</p>
                      <span v-if="typeof phase === 'object' && (phase.semanas || phase.weeks)" class="ml-auto text-[10px] text-wc-text-tertiary">Sem {{ phase.semanas || phase.weeks }}</span>
                    </div>
                    <p v-if="typeof phase === 'object' && (phase.descripcion || phase.description)" class="text-sm leading-relaxed text-wc-text-secondary">{{ phase.descripcion || phase.description }}</p>
                  </div>
                </div>
              </div>

              <!-- PCT -->
              <div v-if="(cicloPlan.pct || []).length > 0" class="overflow-hidden rounded-xl border border-emerald-500/20 bg-emerald-500/5">
                <div class="border-b border-emerald-500/20 px-5 py-3">
                  <h3 class="font-display text-sm tracking-wider text-emerald-400">POST CYCLE THERAPY (PCT)</h3>
                </div>
                <div class="divide-y divide-emerald-500/10">
                  <div v-for="(pct, pIdx) in cicloPlan.pct" :key="pIdx" class="flex items-center gap-4 px-5 py-3.5">
                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-emerald-500/15">
                      <svg class="h-4 w-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                    </div>
                    <div class="min-w-0 flex-1">
                      <p class="font-semibold text-wc-text text-sm">{{ typeof pct === 'object' ? (pct.nombre || pct.name || '') : pct }}</p>
                      <p v-if="typeof pct === 'object' && (pct.dosis || pct.dose)" class="text-sm text-emerald-400 font-data font-bold mt-0.5">{{ pct.dosis || pct.dose }}</p>
                      <p v-if="typeof pct === 'object' && (pct.semanas || pct.weeks)" class="text-sm text-wc-text-tertiary mt-0.5">{{ pct.semanas || pct.weeks }}</p>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Labs -->
              <div v-if="(cicloPlan.labs || []).length > 0" class="overflow-hidden rounded-xl border border-sky-500/20 bg-sky-500/5">
                <div class="border-b border-sky-500/20 px-5 py-3">
                  <h3 class="font-display text-sm tracking-wider text-sky-400">ANALISIS DE LABORATORIO</h3>
                </div>
                <div class="divide-y divide-sky-500/10">
                  <div v-for="(lab, lIdx) in cicloPlan.labs" :key="lIdx" class="px-5 py-4">
                    <div class="flex items-center justify-between gap-2">
                      <p class="font-semibold text-sky-300 text-sm">{{ typeof lab === 'object' ? (lab.nombre || lab.name || '') : lab }}</p>
                      <span v-if="typeof lab === 'object' && (lab.cuando || lab.when)" class="shrink-0 rounded-full bg-sky-500/15 px-2.5 py-0.5 text-[10px] font-bold text-sky-400 uppercase tracking-wide">{{ lab.cuando || lab.when }}</span>
                    </div>
                    <p v-if="typeof lab === 'object' && (lab.marcadores || lab.markers)" class="mt-1.5 text-sm leading-relaxed text-wc-text-tertiary">{{ lab.marcadores || lab.markers }}</p>
                  </div>
                </div>
              </div>

              <!-- Daily monitoring -->
              <div v-if="(cicloPlan.monitoreo_diario || []).length > 0" class="overflow-hidden rounded-xl border border-wc-border bg-wc-bg-tertiary">
                <div class="border-b border-wc-border px-5 py-4">
                  <h3 class="font-display text-sm tracking-wider text-wc-text">MONITOREO DIARIO</h3>
                </div>
                <div class="divide-y divide-wc-border">
                  <div v-for="(item, mIdx) in cicloPlan.monitoreo_diario" :key="mIdx" class="flex items-start gap-3 px-5 py-3.5">
                    <div class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded border border-wc-accent/40 bg-wc-accent/10">
                      <svg class="h-3 w-3 text-wc-accent" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75"/></svg>
                    </div>
                    <div class="flex-1">
                      <div class="flex items-center gap-2">
                        <p class="text-sm font-medium text-wc-text">{{ typeof item === 'object' ? (item.item || '') : item }}</p>
                        <span v-if="typeof item === 'object' && item.frecuencia" class="text-[10px] text-wc-text-tertiary">{{ item.frecuencia }}</span>
                      </div>
                      <p v-if="typeof item === 'object' && item.detalle" class="mt-0.5 text-sm text-wc-text-tertiary">{{ item.detalle }}</p>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Side effects accordion -->
              <div v-if="(cicloPlan.efectos_secundarios || []).length > 0" class="space-y-2">
                <h3 class="font-display text-sm tracking-wider text-wc-text-tertiary uppercase px-1">EFECTOS SECUNDARIOS & MANEJO</h3>
                <div v-for="(efecto, eIdx) in cicloPlan.efectos_secundarios" :key="eIdx" class="rounded-xl border border-wc-border bg-wc-bg-tertiary overflow-hidden">
                  <button
                    @click="toggleEfecto(eIdx)"
                    class="flex w-full items-center justify-between px-5 py-3.5 text-left"
                  >
                    <span class="text-sm font-medium text-wc-text">{{ typeof efecto === 'object' ? (efecto.efecto || '') : efecto }}</span>
                    <svg
                      class="h-4 w-4 shrink-0 text-wc-text-tertiary transition-transform"
                      :class="openEfecto === eIdx ? 'rotate-180' : ''"
                      fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                    ><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg>
                  </button>
                  <Transition name="fade">
                    <div v-if="openEfecto === eIdx" class="border-t border-wc-border px-5 py-3.5 bg-wc-bg-secondary">
                      <p class="text-sm leading-relaxed text-wc-text-secondary">{{ typeof efecto === 'object' ? (efecto.manejo || efecto.management || '') : '' }}</p>
                    </div>
                  </Transition>
                </div>
              </div>

              <!-- Emergency signals -->
              <div v-if="(cicloPlan.emergencia || []).length > 0" class="overflow-hidden rounded-xl border border-red-500/30 bg-red-500/5">
                <div class="border-b border-red-500/30 px-5 py-3">
                  <div class="flex items-center gap-2">
                    <svg class="h-4 w-4 text-red-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.362 5.214A8.252 8.252 0 0 1 12 21 8.25 8.25 0 0 1 6.038 7.047 8.287 8.287 0 0 0 9 9.601a8.983 8.983 0 0 1 3.361-6.867 8.21 8.21 0 0 0 3 2.48Z"/></svg>
                    <h3 class="font-display text-sm tracking-wider text-red-400">SENALES DE EMERGENCIA</h3>
                  </div>
                </div>
                <div class="divide-y divide-red-500/15">
                  <div v-for="(emerg, emIdx) in cicloPlan.emergencia" :key="emIdx" class="px-5 py-4">
                    <p class="text-sm font-semibold text-red-300">&#x26A0;&#xFE0F; {{ typeof emerg === 'object' ? (emerg.sintoma || '') : emerg }}</p>
                    <p v-if="typeof emerg === 'object' && emerg.accion" class="mt-1.5 text-sm leading-relaxed text-wc-text-secondary">{{ emerg.accion }}</p>
                  </div>
                </div>
              </div>

              <!-- Coach notes -->
              <div v-if="cicloPlan.notas_coach" class="rounded-xl border-l-4 border-wc-accent bg-wc-bg-tertiary p-5">
                <p class="mb-2 text-sm font-semibold uppercase tracking-wider text-wc-accent">Notas del coach</p>
                <p class="text-sm leading-relaxed text-wc-text-secondary">{{ cicloPlan.notas_coach }}</p>
              </div>
            </div>
          </template>

          <!-- Feminine: Menstrual Cycle Tracker -->
          <template v-else>
            <div class="space-y-5">
              <!-- Hero ring + phase -->
              <div
                class="relative overflow-hidden rounded-2xl border p-6"
                :class="cicloPhaseData ? [cicloPhaseData.bg, cicloPhaseData.border] : ['bg-wc-bg-tertiary', 'border-wc-border']"
              >
                <template v-if="cicloCurrentDay && cicloPhaseData">
                  <div class="flex flex-col items-center sm:flex-row sm:items-center sm:gap-8">
                    <div class="relative shrink-0">
                      <svg width="140" height="140" viewBox="0 0 140 140" class="-rotate-90">
                        <circle cx="70" cy="70" r="54" fill="none" stroke-width="10" style="stroke: currentColor; opacity: 0.12;" class="text-wc-text"/>
                        <circle
                          v-for="(arc, aIdx) in cicloPhaseArcs"
                          :key="aIdx"
                          cx="70" cy="70" r="54" fill="none"
                          :stroke="arc.color"
                          stroke-width="10"
                          stroke-linecap="butt"
                          :stroke-dasharray="arc.dasharray"
                          :stroke-dashoffset="arc.dashoffset"
                        />
                        <circle
                          v-if="cicloCurrentDay"
                          cx="70" cy="70" r="54" fill="none" stroke="white" stroke-width="4" stroke-linecap="round"
                          :stroke-dasharray="'4 ' + (2 * Math.PI * 54)"
                          :stroke-dashoffset="cicloDotOffset"
                          style="filter: drop-shadow(0 0 4px rgba(255,255,255,0.9));"
                        />
                      </svg>
                      <div class="absolute inset-0 flex flex-col items-center justify-center text-center">
                        <span class="text-2xl">{{ cicloPhaseData.emoji }}</span>
                        <span class="font-data text-2xl font-black leading-none" :class="cicloPhaseData.text">{{ cicloCurrentDay }}</span>
                        <span class="text-sm text-wc-text-tertiary font-medium uppercase tracking-wide">dia</span>
                      </div>
                    </div>
                    <div class="mt-4 sm:mt-0 text-center sm:text-left flex-1">
                      <p class="text-xs font-semibold tracking-widest uppercase text-wc-text-secondary mb-1">Fase actual</p>
                      <h2 class="font-display text-4xl tracking-wide leading-none" :class="cicloPhaseData.text">{{ cicloPhaseData.name }}</h2>
                      <p class="mt-2 font-data text-sm text-wc-text-secondary">Dia <span class="font-bold text-wc-text">{{ cicloCurrentDay }}</span> de {{ cicloCycleLength }}</p>
                      <p class="mt-1 text-sm text-wc-text-tertiary">Proximo ciclo en <span class="font-semibold text-wc-text-secondary">{{ cicloDaysUntilNext }}</span> dias</p>
                      <div class="mt-3 flex items-center gap-1.5">
                        <span class="text-sm font-medium text-wc-text-tertiary uppercase tracking-wider">Energia</span>
                        <div class="flex gap-0.5">
                          <div
                            v-for="i in 10"
                            :key="i"
                            class="h-2 w-2 rounded-full transition-colors"
                            :class="i <= cicloPhaseData.energy ? cicloPhaseData.text.replace('text-','bg-') : 'bg-wc-bg-secondary'"
                          ></div>
                        </div>
                      </div>
                      <button @click="cicloShowConfig = !cicloShowConfig" class="mt-3 text-[11px] text-wc-text-tertiary hover:text-wc-text-secondary transition-colors flex items-center gap-1">
                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                        Ajustar configuracion
                      </button>
                    </div>
                  </div>
                </template>

                <template v-else>
                  <div class="py-4 text-center">
                    <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-full bg-wc-bg-secondary">
                      <svg class="h-7 w-7 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" /></svg>
                    </div>
                    <p class="font-display text-lg tracking-wide text-wc-text">CONFIGURA TU CICLO</p>
                    <p class="mt-1 text-sm text-wc-text-secondary">Ingresa la fecha del inicio de tu ultimo ciclo para ver tu fase actual.</p>
                  </div>
                </template>
              </div>

              <!-- Config form -->
              <Transition name="fade">
                <div v-if="cicloShowConfig" class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
                  <h3 class="font-display text-base tracking-wide text-wc-text mb-4">CONFIGURACION DEL CICLO</h3>
                  <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                      <label class="block text-sm font-medium text-wc-text-tertiary mb-1.5">&#x1F4C5; Fecha de inicio del ultimo ciclo</label>
                      <input type="date" v-model="cicloStartDate" class="w-full rounded-lg border border-wc-border bg-wc-bg py-2.5 px-3 text-sm text-wc-text focus:border-wc-accent focus:outline-none"/>
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-wc-text-tertiary mb-1.5">&#x1F504; Duracion del ciclo (dias)</label>
                      <input type="number" v-model.number="cicloCycleLength" min="21" max="40" class="w-full rounded-lg border border-wc-border bg-wc-bg py-2.5 px-3 text-sm text-wc-text focus:border-wc-accent focus:outline-none"/>
                    </div>
                  </div>
                  <div class="mt-4 flex items-center gap-3">
                    <button @click="saveCicloConfig" class="rounded-lg bg-wc-accent px-5 py-2 text-sm font-medium text-white hover:bg-wc-accent/90 transition-colors">Guardar</button>
                    <p class="text-sm text-wc-text-tertiary">Los datos se guardan localmente en tu dispositivo.</p>
                  </div>
                </div>
              </Transition>

              <!-- Recommendations -->
              <template v-if="cicloCurrentDay && cicloPhaseData">
                <div class="grid gap-4 sm:grid-cols-2">
                  <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
                    <div class="flex items-center gap-2 mb-3">
                      <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-lg" :class="cicloPhaseData.bg">&#x1F3CB;&#xFE0F;</span>
                      <h4 class="font-display text-sm tracking-wide text-wc-text">ENTRENAMIENTO</h4>
                    </div>
                    <p class="text-sm leading-relaxed text-wc-text-secondary">{{ cicloPhaseData.train }}</p>
                  </div>
                  <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
                    <div class="flex items-center gap-2 mb-3">
                      <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-lg" :class="cicloPhaseData.bg">&#x1F957;</span>
                      <h4 class="font-display text-sm tracking-wide text-wc-text">NUTRICION</h4>
                    </div>
                    <p class="text-sm leading-relaxed text-wc-text-secondary">{{ cicloPhaseData.nutrition }}</p>
                  </div>
                </div>
              </template>

              <!-- Phase timeline reference -->
              <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
                <h3 class="font-display text-sm tracking-wide text-wc-text mb-4">FASES DEL CICLO</h3>
                <div class="mb-4 flex h-3 w-full overflow-hidden rounded-full">
                  <div class="h-full transition-all" style="width: 18%; background:#f87171;"></div>
                  <div class="h-full transition-all" style="width: 32%; background:#4ade80;"></div>
                  <div class="h-full transition-all" style="width: 11%; background:#fbbf24;"></div>
                  <div class="h-full flex-1" style="background:#c084fc;"></div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                  <div
                    v-for="(pc, pcIdx) in phaseCards"
                    :key="pcIdx"
                    @click="toggleFlip(pcIdx)"
                    class="cursor-pointer select-none"
                    style="perspective: 700px; height: 130px;"
                  >
                    <div
                      style="position: relative; width: 100%; height: 100%; transform-style: preserve-3d; transition: transform 0.45s cubic-bezier(.4,0,.2,1);"
                      :style="flippedCards[pcIdx] ? 'transform:rotateY(180deg)' : 'transform:rotateY(0deg)'"
                    >
                      <div class="absolute inset-0 rounded-xl p-3" :class="[pc.border, pc.bgF]" style="backface-visibility:hidden;">
                        <div class="flex items-center gap-1.5 mb-1.5"><div class="h-2 w-2 shrink-0 rounded-full" :class="pc.dot"></div><p class="text-sm font-semibold" :class="pc.text">{{ pc.name }}</p></div>
                        <p class="text-sm text-wc-text-tertiary">Dias {{ pc.days }}</p>
                        <p class="text-sm text-wc-text-tertiary mt-0.5">{{ pc.sub }}</p>
                      </div>
                      <div class="absolute inset-0 overflow-y-auto rounded-xl p-3" :class="[pc.border, pc.bgB]" style="backface-visibility:hidden; transform:rotateY(180deg);">
                        <p class="text-sm font-bold mb-1" :class="pc.text">&#x1F3CB;&#xFE0F; Entreno</p>
                        <p class="text-sm text-wc-text-secondary leading-relaxed">{{ pc.train }}</p>
                        <div class="mt-2 border-t pt-2" :class="pc.border">
                          <p class="text-sm font-bold mb-1" :class="pc.text">&#x1F957; Nutricion</p>
                          <p class="text-sm text-wc-text-secondary leading-relaxed">{{ pc.nutr }}</p>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </template>
        </div>

        <!-- ==================== TAB: BLOODWORK ==================== -->
        <div v-else-if="activeTab === 'bloodwork'">
          <!-- Locked -->
          <div v-if="!canAccessElite" class="rounded-xl border border-wc-accent/20 bg-wc-accent/5 p-8 text-center">
            <p class="font-display text-xl text-wc-text">Bloodwork &amp; Analisis Laboratorio</p>
            <p class="mt-2 text-sm text-wc-text-secondary">Disponible exclusivamente en el plan Elite.</p>
            <a href="/planes" class="mt-4 inline-block rounded-full bg-wc-accent px-6 py-2.5 text-sm font-semibold text-white">Upgrade a Elite</a>
          </div>

          <template v-else>
            <div class="space-y-6">
              <!-- Latest values summary -->
              <div v-if="Object.keys(latestByTest).length > 0">
                <h3 class="font-display text-sm tracking-wide text-wc-text-secondary mb-3">ULTIMOS VALORES</h3>
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
                  <div
                    v-for="(r, testName) in latestByTest"
                    :key="testName"
                    class="rounded-xl border bg-wc-bg-tertiary p-3.5"
                    :class="bwStatus(r) === 'ok' ? 'border-emerald-500/25' : bwStatus(r) === 'flag' ? 'border-amber-500/30' : 'border-wc-border'"
                  >
                    <div class="flex items-start justify-between gap-1 mb-2">
                      <p class="text-sm font-medium text-wc-text-secondary leading-tight">{{ testName }}</p>
                      <span v-if="bwStatus(r) === 'ok'" class="shrink-0 flex h-4 w-4 items-center justify-center rounded-full bg-emerald-500/20">
                        <svg class="h-2.5 w-2.5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                      </span>
                      <span v-else-if="bwStatus(r) === 'flag'" class="shrink-0 flex h-4 w-4 items-center justify-center rounded-full bg-amber-500/20">
                        <svg class="h-2.5 w-2.5 text-amber-400" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/></svg>
                      </span>
                    </div>
                    <div class="flex items-baseline gap-1">
                      <span class="font-data text-xl font-black text-wc-text tabular-nums">{{ r.value }}</span>
                      <span class="text-sm text-wc-text-tertiary">{{ r.unit }}</span>
                    </div>
                    <div
                      v-if="bwSpectrumPct(r) !== null"
                      class="mt-2 relative h-1.5 w-full overflow-hidden rounded-full"
                      style="background: linear-gradient(to right, #ef4444 0%, #fbbf24 20%, #4ade80 32%, #4ade80 68%, #fbbf24 80%, #ef4444 100%);"
                    >
                      <div class="absolute top-0 h-full w-0.5 rounded-full bg-white shadow" :style="{ left: bwSpectrumPct(r).toFixed(1) + '%', transform: 'translateX(-50%)' }"></div>
                    </div>
                    <p v-else-if="r.reference_range" class="mt-1 text-[9px] text-wc-text-tertiary">Ref: {{ r.reference_range }}</p>
                    <p class="mt-1 text-[9px] text-wc-text-tertiary">{{ formatDateShort(r.test_date) }}</p>
                  </div>
                </div>
              </div>

              <!-- Add result form -->
              <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary p-5">
                <button @click="bwFormOpen = !bwFormOpen" class="flex w-full items-center justify-between">
                  <h3 class="font-display text-base tracking-wide text-wc-text">AGREGAR RESULTADO</h3>
                  <svg class="h-5 w-5 text-wc-text-tertiary transition-transform" :class="bwFormOpen && 'rotate-180'" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>
                </button>

                <div v-show="bwFormOpen">
                  <!-- Success message -->
                  <Transition name="fade">
                    <div v-if="bwShowSuccess" class="mt-4 flex items-center gap-2 rounded-lg border border-emerald-500/30 bg-emerald-500/10 px-4 py-2.5 text-sm text-emerald-400">
                      <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                      Resultado guardado correctamente.
                    </div>
                  </Transition>

                  <form @submit.prevent="saveBloodwork" class="mt-4 space-y-4">
                    <div class="grid gap-4 sm:grid-cols-2">
                      <div>
                        <label class="block text-sm font-medium text-wc-text-tertiary mb-1.5">&#x1F9EA; Prueba</label>
                        <select v-model="bwForm.testName" class="w-full rounded-lg border border-wc-border bg-wc-bg py-2.5 px-3 text-sm text-wc-text focus:border-wc-accent focus:outline-none">
                          <option value="">Seleccionar prueba...</option>
                          <optgroup v-for="group in bwTestOptions" :key="group.group" :label="group.group">
                            <option v-for="test in group.tests" :key="test" :value="test">{{ test }}</option>
                          </optgroup>
                        </select>
                        <span v-if="bwErrors.test_name" class="mt-1 block text-sm text-red-400">{{ bwErrors.test_name[0] }}</span>
                      </div>

                      <div>
                        <label class="block text-sm font-medium text-wc-text-tertiary mb-1.5">&#x1F4C5; Fecha</label>
                        <input type="date" v-model="bwForm.testDate" class="w-full rounded-lg border border-wc-border bg-wc-bg py-2.5 px-3 text-sm text-wc-text focus:border-wc-accent focus:outline-none" />
                        <span v-if="bwErrors.test_date" class="mt-1 block text-sm text-red-400">{{ bwErrors.test_date[0] }}</span>
                      </div>

                      <div>
                        <label class="block text-sm font-medium text-wc-text-tertiary mb-1.5">&#x1F4CA; Valor</label>
                        <input type="number" step="0.01" v-model="bwForm.value" placeholder="ej: 95.5" class="w-full rounded-lg border border-wc-border bg-wc-bg py-2.5 px-3 text-sm text-wc-text focus:border-wc-accent focus:outline-none" />
                        <span v-if="bwErrors.value" class="mt-1 block text-sm text-red-400">{{ bwErrors.value[0] }}</span>
                      </div>

                      <div>
                        <label class="block text-sm font-medium text-wc-text-tertiary mb-1.5">&#x1F52C; Unidad</label>
                        <input type="text" v-model="bwForm.unit" placeholder="ej: mg/dL" class="w-full rounded-lg border border-wc-border bg-wc-bg py-2.5 px-3 text-sm text-wc-text focus:border-wc-accent focus:outline-none" />
                        <span v-if="bwErrors.unit" class="mt-1 block text-sm text-red-400">{{ bwErrors.unit[0] }}</span>
                      </div>

                      <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-wc-text-tertiary mb-1.5">&#x1F4CB; Rango de referencia <span class="font-normal text-wc-text-tertiary">(opcional - ej: 70-100)</span></label>
                        <input type="text" v-model="bwForm.referenceRange" placeholder="ej: 70-100 mg/dL" class="w-full rounded-lg border border-wc-border bg-wc-bg py-2.5 px-3 text-sm text-wc-text focus:border-wc-accent focus:outline-none" />
                      </div>
                    </div>

                    <button
                      type="submit"
                      :disabled="bwSaving"
                      class="rounded-xl bg-wc-accent px-5 py-2.5 text-sm font-medium text-white hover:bg-wc-accent/90 transition-colors disabled:opacity-50"
                    >
                      <span v-if="!bwSaving">Guardar Resultado</span>
                      <span v-else class="inline-flex items-center gap-2">
                        <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                        Guardando...
                      </span>
                    </button>
                  </form>
                </div>
              </div>

              <!-- Results history -->
              <div class="rounded-xl border border-wc-border bg-wc-bg-tertiary overflow-hidden">
                <div class="flex items-center justify-between px-5 py-4 border-b border-wc-border">
                  <h3 class="font-display text-base tracking-wide text-wc-text">HISTORIAL</h3>
                  <span v-if="bloodworkResults.length > 0" class="rounded-full bg-wc-bg-secondary px-2.5 py-0.5 text-xs font-medium text-wc-text-secondary">
                    {{ bloodworkResults.length }} registros
                  </span>
                </div>

                <div v-if="bloodworkResults.length > 0">
                  <div
                    v-for="(result, rIdx) in [...bloodworkResults].reverse()"
                    :key="result.id || rIdx"
                    class="px-5 py-3.5"
                    :class="rIdx < [...bloodworkResults].reverse().length - 1 ? 'border-b border-wc-border/60' : ''"
                  >
                    <div class="flex items-center gap-4">
                      <!-- Status dot -->
                      <div class="shrink-0 flex h-8 w-8 items-center justify-center rounded-full"
                        :class="bwStatus(result) === 'ok' ? 'bg-emerald-500/15' : bwStatus(result) === 'flag' ? 'bg-amber-500/15' : 'bg-wc-bg-secondary'"
                      >
                        <svg v-if="bwStatus(result) === 'ok'" class="h-4 w-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                        <svg v-else-if="bwStatus(result) === 'flag'" class="h-4 w-4 text-amber-400" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/></svg>
                        <svg v-else class="h-4 w-4 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                      </div>

                      <!-- Test name + date -->
                      <div class="min-w-0 flex-1">
                        <p class="text-sm font-medium text-wc-text truncate">{{ result.test_name }}</p>
                        <p class="text-sm text-wc-text-tertiary">{{ formatDate(result.test_date) }}</p>
                      </div>

                      <!-- Value + unit -->
                      <div class="shrink-0 text-right">
                        <p class="font-data text-base font-bold text-wc-text tabular-nums">
                          {{ result.value }}
                          <span class="text-xs font-normal text-wc-text-tertiary">{{ result.unit }}</span>
                        </p>
                        <p v-if="result.reference_range" class="text-sm text-wc-text-tertiary">{{ result.reference_range }}</p>
                      </div>

                      <!-- Delete -->
                      <button
                        @click="deleteBloodwork(result.id)"
                        class="shrink-0 flex h-7 w-7 items-center justify-center rounded-lg text-wc-text-tertiary hover:bg-red-500/10 hover:text-red-400 transition-colors"
                        title="Eliminar"
                      >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
                      </button>
                    </div>
                    <!-- Spectrum bar -->
                    <div
                      v-if="bwSpectrumPct(result) !== null"
                      class="mt-2 px-12 relative h-1.5 w-full overflow-hidden rounded-full"
                      style="background: linear-gradient(to right, #ef4444 0%, #fbbf24 20%, #4ade80 32%, #4ade80 68%, #fbbf24 80%, #ef4444 100%);"
                    >
                      <div class="absolute top-0 h-full w-0.5 rounded-full bg-white shadow" :style="{ left: bwSpectrumPct(result).toFixed(1) + '%', transform: 'translateX(-50%)' }"></div>
                    </div>
                  </div>
                </div>

                <div v-else class="py-10 text-center">
                  <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-full bg-wc-bg-secondary">
                    <svg class="h-7 w-7 text-wc-text-tertiary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v5.714a2.25 2.25 0 0 1-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 0 1 4.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0 1 12 15a9.065 9.065 0 0 1-6.23-.693L5 14.5m14.8.8 1.402 1.402c1.232 1.232.65 3.318-1.067 3.611A48.309 48.309 0 0 1 12 21c-2.773 0-5.491-.235-8.135-.687-1.718-.293-2.3-2.379-1.067-3.61L5 14.5"/></svg>
                  </div>
                  <p class="font-display text-base tracking-wide text-wc-text">SIN RESULTADOS AUN</p>
                  <p class="mt-1 text-sm text-wc-text-secondary">Agrega tus resultados de laboratorio para llevar un seguimiento de tu salud.</p>
                </div>
              </div>
            </div>
          </template>
        </div>
      </template>
    </div>
  </ClientLayout>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.2s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
.accordion-enter-active, .accordion-leave-active { transition: max-height 0.3s ease, opacity 0.2s ease; overflow: hidden; max-height: 600px; }
.accordion-enter-from, .accordion-leave-to { max-height: 0; opacity: 0; }
</style>
