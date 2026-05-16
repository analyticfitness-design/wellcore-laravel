# Cardio Module Design — WellCore WorkoutPlayer
**Fecha:** 2026-05-16
**Autor:** Staff Product/Sports-Tech Engineer (sesión Claude Code)
**Estado:** Propuesta de diseño — pendiente validación de Daniel
**Alcance:** Diseño únicamente. NO implementar sin luz verde.

---

## Resumen ejecutivo

### Top 3 hallazgos del audit
1. **La infraestructura cardio ya está parcialmente construida.** `workout_logs` tiene 6 columnas dedicadas (`is_cardio`, `duration_minutes`, `speed_kmh`, `incline_percent`, `heart_rate_avg`, `duration_seconds`). El frontend `SetRow.vue` tiene una variante cardio (`isCardio` prop) con campos tiempo/velocidad/inclinación. El endpoint `/api/v/client/workout/complete-set` ya acepta payload cardio. **El gap NO es de plomería: es de metodología.** El sistema modela un solo arquetipo (LISS-en-caminadora con vel/inc) y trata cualquier otra cosa (HIIT, Tabata, AMRAP) como “cardio genérico con N sets manuales”.
2. **Hay una asimetría crítica entre planificación y ejecución.** El coach prescribe en `MD 07-CARDIO-REGLAS` con riqueza (4 tipos: LISS, HIIT, Steady moderado, Funcional) pero el JSON del plan colapsa todo a `is_cardio: true + duracion_min + velocidad_kmh + inclinacion_pct`. El cliente nunca ve cronómetro de intervalos, ni audio cues trabajo/descanso, ni round counter, ni RPE post-sesión. Para HIIT prescrito tipo “8 rondas 30/30”, el cliente ve una card cardio con un input de “seg” sin guía.
3. **El XP penaliza accidentalmente las sesiones cardio-puro.** `WorkoutSession::awardXp()` da 40 base + 25 bonus si “todos los logs tienen weight_kg registrado”. En el día sábado HIIT de Lizeth (cardio-only, sin pesas), todos los logs tendrán `weight_kg=null` → no obtiene el bonus. El cliente que cumple disciplinadamente su HIIT del sábado recibe el mismo XP que si solo registró 2 sets de pesas. Hay que revisar la fórmula para cardio.

### Top 3 decisiones de diseño
1. **Modelar 5 arquetipos cardio en lugar de 11.** LISS continuo, MISS continuo, Intervalos clásicos, Tabata, AMRAP/EMOM (combinado). Cubre 95 % de prescripciones realistas WellCore sin sobre-ingeniería. Otros casos (Fartlek, Z2 puro estricto, Sprints ATP-PC) van como `cardio_type: "free"` con nota libre del coach.
2. **Schema aditivo `cardio_type` + `protocol` opcional al objeto ejercicio.** Cero migración destructiva. Planes existentes siguen funcionando sin tocar nada. Inferencia automática para retrocompatibilidad.
3. **Componente `CardioPlayer.vue` como wrapper polimorfo** dentro de `resources/js/vue/components/workout/`. Recibe el ejercicio cardio y rutea a `CardioContinuous` / `CardioIntervals` / `CardioTabata` / `CardioAMRAP`. El actual `SetRow` variante cardio se mantiene como fallback (`free`) hasta que todos los arquetipos estén live.

### Costo estimado de implementación
| Fase | Días-persona | Riesgo | Reversible |
|---|---|---|---|
| F0 — Schema + inferencia | 1 | Bajo | Sí (campos opcionales) |
| F1 — `CardioContinuous` + `CardioIntervals` | 3 | Medio | Sí (feature flag) |
| F2 — `CardioTabata` + `CardioAMRAP/EMOM` | 3 | Medio | Sí (feature flag) |
| F3 — Tracking extendido (RPE, rounds, distancia) + UI coach | 4 | Medio-alto | Migración aditiva |
| F4 — Zonas FC + audio cues + offline | 5 | Alto | Sí (feature flag) |
| **Total MVP útil (F0-F2)** | **7 días-persona** | — | — |
| **Total completo (F0-F4)** | **16 días-persona** | — | — |

Recomendación: **arrancar con F0+F1 (4 días) que ya entrega valor real al cliente sin riesgo de regresión**. F2-F4 según prioridad de Daniel.

---

## Fase 1 — Estado actual del WorkoutPlayer

### 1.1 Arquitectura

```
routes/web.php
  └─ /client/workout/:day?  →  WorkoutPlayer.vue (50 líneas, dispatcher)
                                ├─ wc_force_workout_player_v2 = '1' → V2
                                ├─ ff `workout_player_legacy` → legacy
                                └─ Default (2026-05-06+) → V2

WorkoutPlayerV2.vue (1136 líneas, página)
  ├─ usePlanLock          (lock por membresía expirada)
  ├─ useVoiceLogger       (registro por voz, fuerza)
  ├─ useWorkoutProgress   (agregados: sets, reps, volumen, % progreso)
  ├─ Componentes hijos:
  │   ├─ WorkoutHero          — header con timer total + progreso
  │   ├─ DayPickerStrip       — selector de día (1-6)
  │   ├─ ExerciseCard         — card por ejercicio (recibe isCardio)
  │   │   └─ ExerciseCardHead — name, badge, gif thumb
  │   │   └─ SetRow           — variante fuerza | variante cardio
  │   │   └─ VoiceCTA         — mic para voice logger
  │   ├─ RestTimerCard        — cronómetro de descanso entre sets
  │   ├─ ExerciseMediaModal   — modal GIF/video
  │   └─ WorkoutBottomBar     — bottom bar mobile con CTA "Finalizar"
  │
  └─ Composables soporte:
      ├─ useWorkoutSessionTimer (80 líneas)  — timer total ascendente,
      │                                        resistente a background suspend
      └─ useWorkoutProgress     (143 líneas) — totales (orientado a fuerza)
```

**Lifecycle observado en V2 (lectura del código + MD 25):**
1. `mounted` → `GET /api/v/client/workout/{day}` → carga ejercicios desde `assigned_plans.content.semanas[w].dias[d]`
2. Click “INICIAR” → `POST /api/v/client/workout/start` → crea `workout_sessions` row, arranca `workoutStartTimestamp`
3. Cada set completado → `POST /api/v/client/workout/complete-set` → crea `workout_logs` row, marca `set.completed=true`, arranca rest timer si hay `exDescanso(ex)`
4. Click “FINALIZAR” → `POST /api/v/client/workout/finish` → cierra session, calcula totales + XP + racha, redirect a summary

**Persistencia local:** El estado de la sesión (`setData`) NO se persiste en localStorage según lo leído. Si el cliente recarga la página durante una sesión activa, la API repuebla desde `workout_logs`. El timer total se recupera desde `workout_sessions.started_at` vía `resumeTimer(startTime)`.

### 1.2 Modelo de datos del ejercicio (estado actual)

**Para ejercicios de fuerza** — extraídos del template + MD 23:

| Campo JSON | Tipo | Renderiza |
|---|---|---|
| `nombre` | string | Heading de la card |
| `series` | int | `getSetRows()` genera N filas |
| `repeticiones` | string | Target reps en `SetRow` (placeholder + label) |
| `descanso` | string ("90s", "2 min") | `parseRestSeconds()` → segundos del cronómetro post-set |
| `rir` | int 0-5 | Badge en card head |
| `notas` | string | Texto italic colapsable |
| `gif_url` | URL | Thumbnail en card + modal media |
| `variacion` | object | Toggle (V2 renderiza variation si `activeVariations[idx]`) |
| `bloque` | "normal"\|"superset"\|"circuito" | Border accent + group header |
| `grupo_id` | string | Agrupa ejercicios del mismo bloque |
| `rondas` | int | (Para circuitos/supersets) |

**Para ejercicios cardio** — extraídos de SetRow.vue líneas 182-223 + completeCardioSet líneas 324-360:

| Campo JSON | Tipo | Renderiza |
|---|---|---|
| `is_cardio` | bool | Cambia la variante de SetRow |
| `nombre` | string | Heading de la card |
| `series` | int | N sets de cardio (default 1) |
| `repeticiones` | string ("30 min") | Mostrado como target en card head |
| `duracion_min` | int | (Solo en `dia.cardio`, NO en ejercicios sueltos) |
| `velocidad_kmh` | string ("5-6") | Mostrado como target |
| `inclinacion_pct` | string ("10-12") | Mostrado como target |
| `momento` | string ("Post pesas") | Subtítulo |
| `notas` | string | Texto italic |
| `gif_url` | URL | Thumbnail |
| `variacion` | object | Toggle de variación |

El cliente al ejecutar registra:
- `duration` (input, step 30, label “seg” — pero la API lo recibe como `duration_minutes` ⚠ ambigüedad)
- `speed` (input, step 0.5)
- `incline` (input, step 1)

### 1.3 Modos de presentación

- **Preview** (días no activos): `ExerciseCardHead` con nombre + chips (series × reps, descanso, RIR) + gif thumbnail + botón “Variación”. No expande.
- **Active** (día actual): card expandible con `SetRow[]`, cronómetro de descanso al completar set, voice CTA, modal media.

**Para cardio en preview:** se ve igual que fuerza pero con chips distintos (duración + velocidad + inclinación).
**Para cardio en active:** el `SetRow` cardio aparece UNA vez por set (default 1 set). El cliente edita los 3 campos y aprieta “Completar”.

### 1.4 Casos reales (lectura del plan de Lizeth 188 + estructura conocida)

| Plan | Caso cardio observado | Modelado actual |
|---|---|---|
| **Lizeth 188** (Esencial F) | L/X/V: escaladora 25 min post-pesas | `dia.cardio` con `{nombre, gif_url, duracion_min:25, momento, notas, variacion}` — campo separado al final del día, NO en `ejercicios[]` |
| **Lizeth 188** | M/J: HIIT 20 min circuito 4 estaciones | `dia.cardio` con `{nombre:"HIIT 20 min", duracion_min:20, is_hiit:true, notas:"30s/30s rotando..."}` — el coach lo describe en `notas` libre |
| **Lizeth 188** | Sábado: HIIT 30 min puro como 4 ejercicios | `ejercicios[]` con 4 entradas tipo `{is_cardio:true, bloque:"circuito", rondas:6-10, grupo_id:"HIIT_SAB", repeticiones:"30 seg", descanso:"15 seg (pasar a siguiente)"}` — el bloque circuito hackea para simular intervalos |
| **Carolina 93** (precedente) | Mismo patrón LISS post-pesas | Similar a Lizeth L/X/V |
| **Cristian 156** | LISS caminadora estándar | Idem |

**Insight:** los planes usan **dos** patrones simultáneos sin coherencia:
- **Patrón A:** `dia.cardio = {…}` (objeto separado al final del día) — preferido en planes nuevos
- **Patrón B:** ejercicio normal en `ejercicios[]` con `is_cardio:true` — patrón legacy/Cristian
- **Patrón C (hack):** `bloque:"circuito"` + múltiples ejercicios bodyweight para simular HIIT, sin guía de cronómetro automático

### 1.5 Tracking de cardio

`workout_logs` ya tiene los campos pero **están subutilizados**:
- `is_cardio` ✓ se guarda
- `duration_minutes` ✓ se guarda (pero hay ambigüedad min/seg en UI)
- `speed_kmh` ✓ se guarda
- `incline_percent` ✓ se guarda
- `heart_rate_avg` ❌ campo existe pero NO hay UI para capturarlo
- `duration_seconds` ❌ campo existe pero NO se usa

**`WorkoutSession::awardXp()`:**
```php
$base = 40;
$allWeightsLogged = $this->logs()
    ->where('completed', true)
    ->whereNull('weight_kg')
    ->doesntExist();
$bonus = $allWeightsLogged ? 25 : 0;
return $base + $bonus;
```

→ Una sesión cardio-puro (todos los logs con `weight_kg=null` porque es cardio) hace que `whereNull('weight_kg')` SÍ exista → bonus = 0 → XP final = 40.
→ Una sesión de pesas con peso completo → XP final = 65.
→ **El cardio puro está condenado a 40 XP por bug de diseño**, no por elección de producto.

### 1.6 Gap analysis

Lista priorizada de qué **NO** soporta hoy el componente:

| # | Gap | Impacto | Frecuencia caso |
|---|---|---|---|
| 1 | Cronómetro de intervalos automático (work/rest cycle con audio cues) | Alto | Lizeth, Silvia Elite, todo cliente con HIIT |
| 2 | Diferenciación visual LISS vs HIIT vs Tabata vs AMRAP | Medio-alto | Universal |
| 3 | RPE post-sesión cardio (sustituto pulsómetro) | Alto | Universal — único proxy intensidad sin wearable |
| 4 | Round counter manual para AMRAP/EMOM | Medio | Pocos casos hoy, alto si Daniel quiere ampliar |
| 5 | Zonas de FC visibles + cálculo desde edad cliente | Medio | Educativo, no gate |
| 6 | Distancia (km) tracking | Bajo | Trotadores/ciclistas — minoría WellCore |
| 7 | XP cardio justo | Alto | Universal — bug actual |
| 8 | Warmup + cooldown como bloques estructurados | Bajo | Hoy en `dia.calentamiento` (string libre) — funciona |
| 9 | Inferencia de tipo cardio desde planes existentes | Crítico | Retrocompatibilidad — sin esto, breaks |
| 10 | Audio cues opcional (beep/voz/vibración) | Medio | Mobile-first |

---

## Fase 2 — Estado del arte

### 2.1 Taxonomía fisiológica condensada

Sintetizando ACSM, NSCA y trabajo de Stephen Seiler sobre Z2:

| Metodología | Trabajo | Descanso | Intensidad | Adaptación primaria |
|---|---|---|---|---|
| **LISS** (Low Intensity Steady State) | 30-60 min continuo | — | 50-65 % FCmax, Z2 | Oxidación grasa, base aeróbica |
| **MISS** (Moderate) | 20-45 min continuo | — | 65-75 % FCmax, Z3 | Capacidad aeróbica |
| **Threshold** | 20-30 min | — | 80-87 % FCmax, Z4 | Umbral láctico |
| **Tabata** | 20 s | 10 s | máx (>95 %) | VO2max, anaerobio |
| **HIIT clásico** | 30 s – 2 min | 30 s – 2 min | 85-95 %, Z4-Z5 | VO2max + EPOC |
| **EMOM** | variable hasta llenar 1 min | resto del minuto | submáx | Capacidad de trabajo |
| **AMRAP** | bloque fijo 8-20 min | mínimo | submáx sostenido | Resistencia muscular |
| **Fartlek** | libre variable | libre variable | Z2-Z4 | Mixta |
| **Sprints ATP-PC** | 10-30 s | 60-180 s | máxima | Potencia |
| **Z2 puro (Seiler)** | 45-120 min | — | 60-70 % estricto | Mitocondrial profundo |

### 2.2 Cómo lo modelan plataformas líderes

| Plataforma | Modelo | Pros | Contras para WellCore |
|---|---|---|---|
| **TrainingPeaks** (Garmin/triatlón) | Structured Workouts con bloques anidados (`warmup → main → recovery → cooldown`) y archivos `.zwo`/`.erg` | Máxima precisión, intervalos exactos | Sobre-ingeniería para nuestro público; requiere wearable |
| **TrueCoach / Trainerize** | Ejercicio cardio con `duration`, `distance`, `intensity_level` (texto libre) | Simple, similar a WellCore actual | No diferencia metodologías; HIIT queda como nota |
| **Hevy / Strong** | Cardio como nota textual libre, sin tracking estructurado | Mínima fricción | Cero guía al cliente; no sirve para HIIT |
| **FitBod** | Auto-genera cardio adaptativo, integra wearables | Adaptativo | Caja negra; coach no controla |
| **Wodify / SugarWOD** (Crossfit) | AMRAP/EMOM/RFT como ciudadanos de primera clase con cronómetros nativos | Modelado correcto de circuitos | Mundo Crossfit ≠ mundo WellCore (vocabulario, intención) |
| **Apple Fitness+ / Peloton** | Pre-grabados con segmentos cronometrados | Engagement alto | Pre-grabado, no prescripción 1:1 |

**Patrón ganador para WellCore (mix de TrueCoach + Wodify):**
- **Modelo base simple** (TrueCoach): cardio = ejercicio con duración + intensidad
- **Bloques estructurados solo cuando aportan** (Wodify): intervalos/Tabata/AMRAP merecen cronómetro nativo
- **Sin obligar wearable** (todos): la guía es por sensación (RPE) o cronómetro, FC es opcional

### 2.3 Filtro WellCore — 5 arquetipos accionables

Descarte:
- ❌ **Fartlek**: ambiguo, el coach lo prescribiría como “alterna ritmos a tu gusto” — cabe en `free`
- ❌ **Threshold puro**: la mayoría no entrena con potenciómetro; queda como “MISS-alto”
- ❌ **Sprints ATP-PC**: solo público performance (no foco WellCore)
- ❌ **Z2-Seiler estricto 90+ min**: pocos clientes entrenan tan largo; cabe en LISS

Arquetipos finales:

| `cardio_type` | Caso WellCore | Coach prescribe | Cliente ve | Tracking | Equipo típico |
|---|---|---|---|---|---|
| `continuous_low` (LISS) | Pérdida de grasa post-pesas | "25 min escaladora ritmo cómodo" | Cronómetro descendente + barra progreso + chip "Z2" | Duración real, RPE final | Caminadora, escaladora, bici, elíptica |
| `continuous_moderate` (MISS) | Recomposición cardio-only | "30 min trote ritmo medio" | Idem + chip "Z3" | Idem | Caminadora, bici, elíptica |
| `intervals` | HIIT, sprints prescritos | "8 rondas 30 s sprint / 30 s caminar" | Cronómetro de fase trabajo↔descanso con audio + counter rondas | Rondas planificadas vs completadas, RPE | Caminadora, escaladora, bici, bodyweight |
| `tabata` | Pérdida grasa agresiva, fin de sesión | "Tabata 8×20/10 jumping jacks" | Cronómetro Tabata visual (preset 20/10×8) | Rondas, RPE | Bodyweight, salto cuerda |
| `circuit` (AMRAP/EMOM combinado) | Cardio funcional bodyweight | "AMRAP 12 min: 10 squats + 15 push-ups + 20 mountain climbers" | Cronómetro ascendente + round tracker manual + lista ejercicios | Rondas completadas, RPE | Bodyweight |
| `free` (fallback) | Cualquier prescripción exótica | "Lo que el coach escriba" | Card simple con `notas` + timer manual | Duración manual, RPE | Cualquiera |

---

## Fase 3 — Propuesta de diseño

### 3.1 Schema JSON aditivo

**Cero cambios al schema actual.** Todos los campos nuevos son **opcionales** con fallback al comportamiento existente vía inferencia (sección 3.7).

```json
{
  "nombre": "Caminadora inclinada",
  "is_cardio": true,
  "gif_url": "https://.../caminadora-inclinada.gif",

  // 🆕 NUEVO — opcional
  "cardio_type": "continuous_low",   // continuous_low | continuous_moderate | intervals | tabata | circuit | free

  // 🆕 Guía de intensidad (opcional, todos los tipos)
  "intensidad": {
    "zona_fc": 2,                    // 1-5
    "porcentaje_fcmax": "60-70",     // string range, guía no gate
    "rpe": "4-5",                    // 0-10
    "descripcion_cliente": "Ritmo donde puedas hablar pero no cantar"
  },

  // 🆕 Solo para tipos estructurados (intervals/tabata/circuit/emom)
  "protocol": {
    "warmup_min": 5,                 // opcional
    "work_seconds": 30,              // required para intervals/tabata
    "rest_seconds": 30,              // required para intervals/tabata
    "rounds": 8,                     // required para intervals/tabata
    "block_duration_min": 12,        // required para circuit/amrap (cuenta ascendente)
    "exercises": [                   // required para circuit
      { "nombre": "Sentadillas",   "reps": 10 },
      { "nombre": "Push-ups",      "reps": 15 },
      { "nombre": "Mountain climbers", "reps": 20 }
    ],
    "cooldown_min": 5                // opcional
  },

  // Campos existentes: comportamiento idéntico
  "duracion_min": 25,                // total de la sesión (para continuous + tabata calculado)
  "velocidad_kmh": "5-6",            // solo continuous con cinta
  "inclinacion_pct": "10-12",        // solo continuous con cinta
  "momento": "Post pesas",
  "notas": "Mantén la cadencia donde puedas hablar pero no cantar.",
  "variacion": { "nombre": "...", "gif_url": "..." }
}
```

**Reglas de combinación por `cardio_type`:**

| `cardio_type` | `duracion_min` | `protocol.*` | `velocidad_kmh`+`inclinacion_pct` | Validación |
|---|---|---|---|---|
| `continuous_low` | required | — | optional | `duracion_min ≥ 10` |
| `continuous_moderate` | required | — | optional | `duracion_min ≥ 10` |
| `intervals` | calculado | `work_seconds`, `rest_seconds`, `rounds` req | — | `rounds ≥ 2`, `work ≥ 10`, `rest ≥ 5` |
| `tabata` | 4 fijo | `work=20`, `rest=10`, `rounds=8` defaults | — | defaults aplicados |
| `circuit` | calculado o req | `block_duration_min` + `exercises[]` req | — | mínimo 2 exercises |
| `free` | optional | — | optional | sin validación |

### 3.2 Componentes Vue propuestos

**Nuevos en `resources/js/vue/components/workout/cardio/`:**

```
cardio/
├── CardioPlayer.vue          ← wrapper polimorfo, recibe ejercicio, rutea por cardio_type
├── CardioContinuous.vue       ← continuous_low + continuous_moderate
├── CardioIntervals.vue        ← intervals
├── CardioTabata.vue           ← tabata (preset 20/10×8 + audio cues nativos)
├── CardioCircuit.vue          ← circuit/amrap (cronómetro ascendente + round counter)
├── CardioFree.vue             ← free (idéntico a SetRow cardio actual = fallback)
└── shared/
    ├── CardioIntensityChip.vue   ← chip "Z2 · RPE 4-5 · Ritmo cómodo"
    ├── CardioTimerDown.vue       ← cronómetro descendente reutilizable
    ├── CardioTimerUp.vue         ← cronómetro ascendente reutilizable
    ├── CardioIntervalEngine.vue  ← motor work/rest cycle (sin UI, lógica pura)
    ├── CardioRoundCounter.vue    ← +1 / -1 con tap
    └── CardioRPEPrompt.vue       ← modal final "¿cómo te sentiste? 1-10"
```

**Por qué wrapper en lugar de modificar ExerciseCard:**
- Aislamiento de regresiones (la fuerza nunca toca este código)
- Pruebas independientes
- Cada arquetipo evoluciona a su ritmo
- Feature flags por sub-componente

**Integración con `ExerciseCard.vue`:**

```vue
<!-- ExerciseCard.vue, dentro del template (línea ~418 hoy renderiza SetRow cardio) -->
<template v-if="isCardio">
  <!-- Nuevo: si tiene cardio_type estructurado, renderiza CardioPlayer -->
  <CardioPlayer
    v-if="cardioV2Enabled && exercise.cardio_type && exercise.cardio_type !== 'free'"
    :exercise="exercise"
    :exercise-index="exerciseIndex"
    :session-id="sessionId"
    @complete="$emit('complete-cardio-set', $event)"
    @uncomplete="$emit('uncomplete-set', $event)"
  />
  <!-- Fallback: SetRow cardio actual (free o sin cardio_type) -->
  <SetRow v-else ... is-cardio ... />
</template>
```

### 3.3 Cronómetros y audio

**`CardioTimerDown.vue`** (continuous):
- Display grande `MM:SS` countdown
- Barra de progreso al fondo
- Botón pause/resume
- Al llegar a 0: 3 beeps + vibration + abre `CardioRPEPrompt`

**`CardioIntervalEngine.vue`** (intervals, tabata):
- Estado: `{ phase: 'warmup'|'work'|'rest'|'cooldown'|'done', currentRound, secondsInPhase }`
- `setTimeout` por fase + tick cada 200 ms con `requestAnimationFrame` para resistencia mobile
- Emite events: `@phase-change`, `@round-complete`, `@all-done`
- Audio cues:
  - Inicio trabajo: 1 beep agudo (880 Hz, 100 ms)
  - Últimos 3 s de trabajo: 3 beeps cortos
  - Inicio descanso: 1 beep grave (440 Hz, 150 ms)
  - Última ronda: voz TTS "Última ronda" (opt-in)
- Vibración pattern: `[100, 50, 100]` al cambio de fase
- Persistencia: el componente NO persiste su estado interno entre recargas. Si el cliente recarga, vuelve a empezar el cardio (decisión de UX: cardio es corto, recargar implica re-empezar).

**Toggle de audio:** localStorage `wc_cardio_audio_enabled` (default `true`). Primer click del cliente arranca el `AudioContext` (browsers bloquean autoplay).

### 3.4 Tracking — extensión a `workout_logs`

**Opción A — Tabla nueva `cardio_logs`** (clean, queryable):
- Pros: separación de concerns, queries de cardio simples, no infla `workout_logs`
- Contras: requiere migración nueva, doble join al consultar “toda la sesión”

**Opción B — Columnas adicionales en `workout_logs`** (Strangler Fig friendly):
- Pros: una sola tabla, migración aditiva más simple
- Contras: muchos NULL por log de fuerza

**Recomendación: Opción B + JSON adicional** — añadir 3 columnas + 1 JSON:

```sql
ALTER TABLE workout_logs
  ADD COLUMN cardio_type        VARCHAR(32) NULL AFTER is_cardio,
  ADD COLUMN rounds_planned     SMALLINT NULL AFTER duration_seconds,
  ADD COLUMN rounds_completed   SMALLINT NULL AFTER rounds_planned,
  ADD COLUMN rpe                TINYINT NULL AFTER rounds_completed,
  ADD COLUMN cardio_metadata    JSON NULL AFTER rpe;
```

`cardio_metadata` guarda:
```json
{
  "protocol_snapshot": { /* protocol del plan en el momento de la sesión */ },
  "phase_log": [
    { "phase": "warmup", "duration_actual_sec": 305 },
    { "phase": "work", "round": 1, "duration_actual_sec": 30 },
    { "phase": "rest", "round": 1, "duration_actual_sec": 30 },
    /* ... */
  ],
  "distance_km": null,
  "heart_rate_max": null,
  "heart_rate_avg": null,
  "calories_estimated": null
}
```

→ Permite reconstruir cualquier sesión cardio sin esquema rígido. Útil cuando se agregue Garmin/Apple Watch en Fase 4: solo agrega keys a `cardio_metadata`.

### 3.5 XP cardio justo

Reescribir `WorkoutSession::awardXp()`:

```php
public function awardXp(): int
{
    $base = 40;
    $strengthLogs = $this->logs()->where('completed', true)->where('is_cardio', false);
    $cardioLogs   = $this->logs()->where('completed', true)->where('is_cardio', true);

    // Bonus pesas: registró peso en TODOS sus logs de fuerza
    $strengthBonus = 0;
    if ($strengthLogs->exists()) {
        $allWeightsLogged = $strengthLogs->whereNull('weight_kg')->doesntExist();
        $strengthBonus = $allWeightsLogged ? 25 : 0;
    }

    // Bonus cardio: completó al menos 1 sesión cardio estructurada + reportó RPE
    $cardioBonus = 0;
    if ($cardioLogs->exists()) {
        $hasRpe = $cardioLogs->whereNotNull('rpe')->exists();
        $hasStructured = $cardioLogs->whereNotNull('cardio_type')
                                    ->where('cardio_type', '!=', 'free')->exists();
        $cardioBonus = ($hasRpe || $hasStructured) ? 25 : 15;
    }

    return $base + max($strengthBonus, $cardioBonus); // no se suman, se toma el mayor
}
```

**Lógica:** sesión cardio-puro con RPE o tipo estructurado → 65 XP (igual que pesas con peso). Sesión cardio sin RPE → 55 XP. Sesión mixta → 65 XP. Sesión pesas-only con peso → 65 XP. **Cero penalty al cardio**.

### 3.6 UI del coach (admin)

**Hoy:** el coach edita JSON crudo o usa `AIPlanGenerator` (bug conocido, MD 24).

**Propuesta mínima (incluida en F3):** dropdown `cardio_type` en el editor del ejercicio admin con campos condicionales:

```
┌─────────────────────────────────────────┐
│ Tipo de cardio: [continuous_low ▾]      │
├─────────────────────────────────────────┤
│ Duración:        [25] min               │
│ Velocidad:       [5-6] km/h             │
│ Inclinación:     [10-12] %              │
│ Zona FC objetivo: [Z2 ▾]                │
│ RPE objetivo:    [4-5]                  │
│ Descripción cliente: [...]              │
└─────────────────────────────────────────┘

(Si selecciona "intervals" cambia a:)
┌─────────────────────────────────────────┐
│ Tipo de cardio: [intervals ▾]           │
├─────────────────────────────────────────┤
│ Warmup:    [5] min                      │
│ Trabajo:   [30] seg                     │
│ Descanso:  [30] seg                     │
│ Rondas:    [8]                          │
│ Cooldown:  [5] min                      │
│ Total:     ~13 min (auto-calculado)    │
└─────────────────────────────────────────┘
```

Componente: `resources/js/vue/components/admin/CardioEditor.vue` (NO admin.AIPlanGenerator porque ese es legacy a reemplazar).

**No bloquear:** coach técnico siempre puede pegar JSON crudo. La UI es atajo, no jaula.

### 3.7 Inferencia para retrocompatibilidad

`CardioPlayer.vue` debe inferir `cardio_type` para planes existentes sin tocar el JSON en DB:

```js
function inferCardioType(exercise) {
  if (exercise.cardio_type) return exercise.cardio_type; // explícito gana

  const name = (exercise.nombre || '').toLowerCase();
  const notas = (exercise.notas || '').toLowerCase();
  const haystack = name + ' ' + notas;

  // Patrón hack circuito (Lizeth sábado): is_cardio + bloque:'circuito' + grupo_id
  if (exercise.is_cardio && exercise.bloque === 'circuito' && exercise.grupo_id) {
    return 'intervals'; // o 'circuit' si hay >1 estación
  }

  if (/tabata/.test(haystack)) return 'tabata';
  if (/hiit|intervalos?|sprints?|30\s*\/\s*30/.test(haystack)) return 'intervals';
  if (/amrap|emom|rounds for time/.test(haystack)) return 'circuit';

  const duration = parseInt(exercise.duracion_min || exercise.duration || '0');
  if (duration >= 20) return 'continuous_low'; // LISS default
  if (duration > 0)   return 'continuous_moderate';

  return 'free';
}
```

**Inferencia vive en el componente, NO toca DB.** Planes de Lizeth/Cristian/Silvia siguen renderizando idéntico — solo que ahora pasan por el dispatcher correcto en lugar de SetRow genérico.

### 3.8 Plan de implementación por fases

| Fase | Entregables | Riesgo | Tiempo | Feature flag |
|---|---|---|---|---|
| **F0** | Schema docs en MD 16a + 07 + 20 + 23. Migración `ALTER TABLE workout_logs ADD COLUMN cardio_type, rounds_planned, rounds_completed, rpe, cardio_metadata`. Inferencia function pura (testeable). | Bajo | 1 día | — (solo aditivo) |
| **F1a** | `CardioPlayer.vue` dispatcher + `CardioContinuous.vue` + `CardioIntensityChip.vue` + `CardioTimerDown.vue` + `CardioRPEPrompt.vue`. ExerciseCard integra dispatcher con flag. XP cardio fix en `WorkoutSession::awardXp()`. | Bajo | 2 días | `cardio_v2_continuous` |
| **F1b** | `CardioIntervals.vue` + `CardioIntervalEngine.vue` + audio cues + vibración. | Medio | 2 días | `cardio_v2_intervals` |
| **F2a** | `CardioTabata.vue` con preset hardcoded 20/10×8. | Bajo | 1 día | `cardio_v2_tabata` |
| **F2b** | `CardioCircuit.vue` (AMRAP/EMOM) con cronómetro ascendente + round counter. | Medio | 2 días | `cardio_v2_circuit` |
| **F3** | Editor admin `CardioEditor.vue`. Migración planes existentes (inferencia → JSON update opcional). UI tracking historico cardio en dashboard cliente. | Medio-alto | 3-4 días | `cardio_v2_editor_admin` |
| **F4** | Integración pulsómetro (Web Bluetooth API). Zonas FC dinámicas. Distancia (geolocation API). Offline mode con service worker. | Alto | 5+ días | `cardio_v2_zones` |

**Camino recomendado para arrancar (MVP útil): F0 + F1a + F1b = 5 días** — entrega LISS estructurado + intervalos HIIT con cronómetro automático. Cubre 90 % de los casos reales auditados (Lizeth, Cristian, Silvia, etc.).

---

## Decisiones que requieren Daniel

1. **¿Tabla nueva `cardio_logs` o columnas adicionales a `workout_logs`?**
   Mi recomendación: columnas + `cardio_metadata JSON`. Strangler-Fig friendly, una sola tabla para queries de “toda la sesión”.

2. **¿Cuántos arquetipos al MVP?**
   Mi recomendación: 5 (LISS, MISS, Intervals, Tabata, Circuit) + `free` fallback. Si tu prioridad es ganar tiempo, recortar a 3 (LISS, Intervals, free) para F1a+F1b en 5 días.

3. **¿Integrar pulsómetro nativo (Garmin, Apple Watch) en este ciclo o postponer a F4?**
   Recomendación: postponer. Mayoría del público no tiene wearable; FC objetivo se queda como guía visual + RPE como tracking real.

4. **¿Reescribir `WorkoutSession::awardXp()` ya en F1a o esperar?**
   Recomendación: incluir en F1a. Es un fix de bug, no feature, y desbloquea valor real (Lizeth ya tiene 4 cardio-only días/semana).

5. **¿Migrar JSONs existentes a `cardio_type` explícito (script único) o dejar la inferencia como default permanente?**
   Recomendación: dejar inferencia. Cero riesgo de regresión. Si en el futuro la inferencia falla para algún caso edge, se sobrescribe puntualmente con `cardio_type`.

6. **¿UI editor cardio en `AIPlanGenerator` (legacy) o componente nuevo `CardioEditor`?**
   Recomendación: nuevo. `AIPlanGenerator` ya tiene problemas conocidos (MD 24). Aislar el editor cardio.

7. **¿`cardio_type: 'circuit'` debe poder anidar ejercicios bodyweight de los 265 GIFs del catálogo, o aceptar solo strings libres?**
   Recomendación: anidar del catálogo (consistencia con la LEY GIF). Cada `protocol.exercises[]` tiene `nombre + reps + gif_url`.

---

## Riesgos y mitigaciones

| Riesgo | Probabilidad | Impacto | Mitigación |
|---|---|---|---|
| Romper render de planes existentes al integrar dispatcher | Baja | Alto | Inferencia con fallback explícito a `free` → SetRow cardio actual. Feature flag global `cardio_v2_enabled` que se puede apagar. |
| Cronómetro de intervalos pierde fase por background suspend del browser mobile | Media | Medio | `requestAnimationFrame` + `visibilitychange` listener + recálculo basado en timestamps absolutos (mismo patrón que `useWorkoutSessionTimer`). Documentar en QA testing. |
| Audio cues no funcionan en Safari iOS por restricción autoplay | Alta | Bajo | Primer click del cliente arranca `AudioContext`. Si falla, vibration API como fallback (Safari iOS la soporta). Toggle visible. |
| Coach prescribe HIIT mal (work_seconds = 0 o rounds = 0) | Media | Medio | Validación en `CardioEditor` + validación frontend al cargar el plan + fallback a `free` si validación falla. |
| Migración aditiva de columnas en `workout_logs` afecta la app vanilla PHP | Baja | Crítico (ADR-0003) | Migración solo ADD COLUMN con DEFAULT NULL. La app vanilla nunca consulta esas columnas → invisible para ella. Verificar antes del deploy con `mysql -e "SHOW COLUMNS FROM workout_logs"` en container y diff vs schema esperado. |
| `cardio_metadata` JSON crece sin control | Baja | Bajo | Cap a 16 KB por log + truncate de `phase_log` si supera. |

---

## Anexos

### A. Casos auditados (estructura simplificada, sin PII)

```json
// PLAN 188 (Lizeth, Esencial F, 2026-05-16) — patrón mixto
{
  "semanas": [{ "dias": [
    { "dia_semana": "Lunes", "ejercicios": [/* 5 ejs glúteo */],
      "cardio": {
        "nombre": "Escaladora", "gif_url": "...escaladora.gif",
        "duracion_min": 25, "momento": "Post pesas",
        "notas": "25 min ritmo constante zona 2..."
      }
    },
    { "dia_semana": "Sábado", "ejercicios": [
      // Hack circuito para HIIT
      { "is_cardio": true, "bloque": "circuito", "grupo_id": "HIIT_SAB",
        "rondas": 6, "repeticiones": "30 seg", "descanso": "15 seg (pasar a siguiente)",
        "nombre": "Jumping jacks", "gif_url": "..." },
      // 3 estaciones más con el mismo patrón
    ]}
  ]}]
}
```

**Bajo la propuesta esto se transforma a:**

```json
{
  "dia_semana": "Lunes",
  "ejercicios": [/* glúteo */],
  "cardio": {
    "nombre": "Escaladora",
    "is_cardio": true,
    "cardio_type": "continuous_low",
    "gif_url": "...",
    "duracion_min": 25,
    "intensidad": {
      "zona_fc": 2, "porcentaje_fcmax": "60-70", "rpe": "4-5",
      "descripcion_cliente": "Ritmo donde puedas hablar pero no cantar"
    },
    "momento": "Post pesas",
    "notas": "..."
  }
},
{
  "dia_semana": "Sábado",
  "ejercicios": [],          // ← se vacía
  "cardio": {
    "nombre": "HIIT 30 min — Circuito 4 estaciones",
    "is_cardio": true,
    "cardio_type": "circuit",
    "intensidad": { "rpe": "8-9", "descripcion_cliente": "Vas con todo, descanso mínimo entre estaciones" },
    "protocol": {
      "warmup_min": 5,
      "block_duration_min": 20,
      "exercises": [
        { "nombre": "Jumping jacks", "gif_url": "...", "reps": "30 seg" },
        { "nombre": "Salto de cuerda", "gif_url": "...", "reps": "30 seg" },
        { "nombre": "Escaladores", "gif_url": "...", "reps": "30 seg" },
        { "nombre": "Sentadilla con salto", "gif_url": "...", "reps": "30 seg" }
      ],
      "cooldown_min": 5
    },
    "notas": "Rota 4 estaciones. Trabajo 30s / descanso 15s entre estaciones. Haz las rondas que indica la semana."
  }
}
```

### B. Mapa de cambios de código por fase

```
F0 (1d):
  ├─ database/migrations/2026_05_17_add_cardio_fields_to_workout_logs.php
  ├─ resources/js/vue/composables/useCardioInference.js  ← función pura inferCardioType
  ├─ tests/Unit/CardioInferenceTest.php  ← unit tests inferencia
  ├─ E:\...\SISTEMA-CREACION-PLANES\16a-...md  ← agregar sección cardio_type
  ├─ E:\...\SISTEMA-CREACION-PLANES\07-CARDIO-REGLAS.md  ← agregar 5 arquetipos
  └─ E:\...\SISTEMA-CREACION-PLANES\23-NAMING-...md  ← campos nuevos

F1a (2d):
  ├─ resources/js/vue/components/workout/cardio/CardioPlayer.vue
  ├─ resources/js/vue/components/workout/cardio/CardioContinuous.vue
  ├─ resources/js/vue/components/workout/cardio/shared/CardioTimerDown.vue
  ├─ resources/js/vue/components/workout/cardio/shared/CardioIntensityChip.vue
  ├─ resources/js/vue/components/workout/cardio/shared/CardioRPEPrompt.vue
  ├─ resources/js/vue/components/workout/ExerciseCard.vue  ← integración dispatcher
  ├─ app/Models/WorkoutSession.php  ← awardXp() reescrito
  ├─ app/Http/Controllers/Api/V/Client/WorkoutController.php  ← acepta rpe en complete-set
  └─ tests/Feature/AwardXpCardioTest.php

F1b (2d):
  ├─ resources/js/vue/components/workout/cardio/CardioIntervals.vue
  ├─ resources/js/vue/components/workout/cardio/shared/CardioIntervalEngine.vue
  └─ tests/Browser/CardioIntervalsTest.php  ← Playwright + audio mute

F2a (1d):
  └─ resources/js/vue/components/workout/cardio/CardioTabata.vue

F2b (2d):
  ├─ resources/js/vue/components/workout/cardio/CardioCircuit.vue
  ├─ resources/js/vue/components/workout/cardio/shared/CardioTimerUp.vue
  └─ resources/js/vue/components/workout/cardio/shared/CardioRoundCounter.vue

F3 (3-4d):
  ├─ resources/js/vue/components/admin/CardioEditor.vue
  ├─ resources/js/vue/pages/Client/Dashboard.vue  ← widget cardio histórico
  ├─ database/migrations/2026_06_xx_add_distance_to_cardio_logs.php  ← opcional
  └─ app/Services/CardioStatsService.php

F4 (5d+):
  └─ Web Bluetooth + geolocation + service worker offline
```

### C. Estructura de carpetas resultante

```
resources/js/vue/
├── pages/Client/
│   ├── WorkoutPlayer.vue          (dispatcher, sin cambios)
│   ├── WorkoutPlayerV2.vue        (integra cardio dispatcher)
│   └── WorkoutPlayer.legacy.vue   (sin cambios)
├── components/
│   ├── workout/
│   │   ├── ExerciseCard.vue       (integra dispatcher en sección cardio)
│   │   ├── ExerciseCardHead.vue
│   │   ├── SetRow.vue             (variante cardio se mantiene como `free` fallback)
│   │   ├── RestTimerCard.vue
│   │   ├── WorkoutHero.vue
│   │   ├── DayPickerStrip.vue
│   │   ├── WorkoutBottomBar.vue
│   │   ├── ExerciseMediaModal.vue
│   │   ├── VoiceCTA.vue
│   │   ├── LastSessionStrip.vue
│   │   └── cardio/                                ← 🆕 nueva carpeta
│   │       ├── CardioPlayer.vue                   ← dispatcher
│   │       ├── CardioContinuous.vue
│   │       ├── CardioIntervals.vue
│   │       ├── CardioTabata.vue
│   │       ├── CardioCircuit.vue
│   │       ├── CardioFree.vue
│   │       └── shared/
│   │           ├── CardioTimerDown.vue
│   │           ├── CardioTimerUp.vue
│   │           ├── CardioIntervalEngine.vue       ← lógica pura
│   │           ├── CardioRoundCounter.vue
│   │           ├── CardioIntensityChip.vue
│   │           ├── CardioPhaseChip.vue            ← "TRABAJO" / "DESCANSO" / "PREPARÁ"
│   │           ├── CardioAudioToggle.vue
│   │           └── CardioRPEPrompt.vue            ← modal final 1-10
│   └── admin/
│       └── CardioEditor.vue                       ← 🆕 F3
├── composables/
│   ├── useWorkoutProgress.js
│   ├── useWorkoutSessionTimer.js
│   ├── useCardioInference.js                      ← 🆕 F0 (función pura)
│   └── useCardioAudio.js                          ← 🆕 F1b (audio context manager)
```

---

## Próximos pasos sugeridos

1. **Daniel revisa este MD** y responde las 7 decisiones de la sección anterior.
2. Si aprobado, **sesión nueva de implementación** arranca F0 + F1a + F1b (5 días estimados) como MVP.
3. **Plan de QA** previo a F1a deploy: cargar plan de Lizeth (188), validar que las 3 sesiones cardio (escaladora L/M/V, HIIT M/J, HIIT-only S) renderizan correctamente con el dispatcher inferido (sin tocar el JSON), comparar contra cómo se ve hoy. Cero diff visual = listo para deploy.
4. **Rollout F1a**: feature flag `cardio_v2_continuous` al 10 % → 50 % → 100 % en 3 días, con smoke test diario.

---

**Fin del documento. Listo para validación.**
