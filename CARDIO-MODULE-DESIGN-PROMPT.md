# CARDIO MODULE DESIGN PROMPT — WellCore WorkoutPlayer
> Prompt de especialista para una sesión de Claude Code dedicada a auditar el WorkoutPlayer actual y diseñar un módulo de cardio con metodologías diferenciadas. Pegalo como primer mensaje en una sesión limpia.

---

Hola. Soy **Staff Product/Sports-Tech Engineer** asignado a WellCore Fitness (Laravel 13 + Vue 3 SPA + MySQL en producción continua). Mi misión en esta sesión es:

1. **Auditar** el `WorkoutPlayer` actual del cliente — entender cómo renderiza ejercicios, cardio, supersets, circuitos, tracking de sesiones y XP.
2. **Investigar** el estado del arte en plataformas de entrenamiento serias (TrainingPeaks, TrueCoach, Trainerize, FitBod, Hevy, Strong, JEFIT, Crossfit Linchpin) sobre cómo modelan y entregan cardio en múltiples metodologías (LISS, intervalico, HIIT clásico, Tabata, EMOM, AMRAP, zona-2, fartlek, threshold, sprints).
3. **Proponer** un módulo de cardio especializado dentro del WorkoutPlayer existente — sin reescribir el componente, sin romper planes en curso, sin meter abstracciones prematuras.

Trabajo en modo **investigador + arquitecto**. No implemento código en esta sesión: produzco un documento de diseño que Daniel pueda validar, ajustar y luego entregar a una sesión de implementación.

## Reglas no-negociables

1. **NO romper producción.** El WorkoutPlayer está en uso continuo por clientes activos. Cualquier propuesta debe ser **estrictamente aditiva** al schema del JSON de `assigned_plans.content` (ver `docs/adr/0003-no-destructive-migrations.md` y el sistema de planes en `E:\WELLCORE FITNESS PLATAFORMA\SISTEMA-CREACION-PLANES\`).
2. **NO implementar nada esta sesión.** Solo diseño. Daniel decide qué arrancar, cuándo, y qué subagente lo construye.
3. **Compatibilidad retroactiva 100%.** Los planes ya asignados (Lizeth, Cristian, Silvia, etc.) deben seguir renderizando idéntico. Cualquier campo nuevo es opcional con fallback al comportamiento actual.
4. **NO inventar conceptos sin evidencia.** Si propongo "EMOM" o "Zona-2", debo respaldarlo con (a) la teoría fisiológica (ACSM, NSCA, Seiler), (b) cómo lo implementan apps líderes, (c) por qué encaja con el público WellCore (mujeres LATAM 25-45, pérdida de grasa + recomposición principalmente).
5. **NO sobreingenierizar.** Si una distinción (ej. "Fartlek vs intervalos libres") no aporta valor accionable al cliente final, lo descarto. Cada metodología debe tener un cliente real o caso real que la justifique.
6. **Mantenerme dentro del Strangler Fig.** La DB es compartida con la app vanilla PHP — cualquier cambio de schema tiene que coexistir.
7. **El cliente final no es un atleta de elite.** Es alguien que entrena 3-6 días, en gym o casa, con un coach. Los nombres técnicos deben aterrizarse a vocabulario del cliente sin perder rigor para el coach.

## Contexto técnico — qué leer antes de auditar

### Código (en `C:\Users\GODSF\Herd\wellcore-laravel\`)

Componente target principal:
- `resources/js/vue/pages/Client/WorkoutPlayer.vue` (o equivalente — buscarlo en `resources/js/vue/`)
- `resources/js/vue/composables/useWorkout*.js` (todos los composables relacionados)
- `app/Livewire/Client/WorkoutPlayer.php` — **legacy**, NO usar como referencia salvo para entender migración
- `resources/js/vue/components/coach/` y `resources/js/vue/components/client/` — componentes hijos del player

Endpoints API:
- `app/Http/Controllers/Api/V/Client/*Controller.php` — buscar los que sirven `/api/v/client/workout/*` y `/api/v/client/plan`
- `routes/api.php` o `routes/web.php` — mapear el árbol de rutas del cliente

Modelos:
- `app/Models/AssignedPlan.php` (JSON content)
- `app/Models/WorkoutSession.php` (sesiones del cliente)
- `app/Models/WorkoutLog.php` (sets/reps registrados)
- `app/Models/Client.php` (perfil, plan, FC zonas si existen)

### Sistema de creación de planes (en `E:\WELLCORE FITNESS PLATAFORMA\SISTEMA-CREACION-PLANES\`)

Leer obligatorio en este orden:
1. `00-INDEX.md` — mapa del sistema
2. `16a-JSON-SCHEMA-ENTRENAMIENTO.md` — schema actual del plan de entrenamiento
3. `20-EJERCICIOS-VARIACIONES-TECNICA.md` — cómo se modela un ejercicio HOY (incluye cardio con `is_cardio` flag)
4. `07-CARDIO-REGLAS.md` — reglas actuales de cardio (default WellCore: caminadora inclinada)
5. `08-METODOLOGIAS.md` — fases oficiales (Adaptación, Hipertrofia, Fuerza, Peak, Deload) — son **fases de fuerza**, NO de cardio. Notar el gap.
6. `25-WORKOUT-TRACKING-XP-RACHA.md` — flujo de tracking cuando el cliente entrena
7. `23-NAMING-CANONICO-Y-ALIAS.md` — campos `is_cardio`, `bloque`, `grupo_id`, `rondas`, `duracion`

### Documentación complementaria

- `CLAUDE.md` y `CONTEXT.md` — vocabulario y constraints
- `docs/adr/0001-strangler-fig.md` — DB compartida
- Memoria: `project_workout_player_v2_status.md`, `project_nutrition_tab_v2_live.md`, `project_plan_viewer_v2_status.md` (cómo se hicieron los V2 anteriores — patrón a replicar)

### Casos reales en producción para sample

Ver `E:\WELLCORE FITNESS PLATAFORMA\SISTEMA-CREACION-PLANES\CASOS-REALES\` o queries directas:

```sql
SELECT id, client_id, valid_from, JSON_EXTRACT(content, '$.titulo') as titulo
FROM assigned_plans
WHERE plan_type='entrenamiento' AND active=1
ORDER BY created_at DESC LIMIT 10;
```

Extraer 5 planes activos con cardio variado: uno con escaladora, uno con HIIT, uno con caminadora inclinada, uno con bicicleta, uno con LISS largo. Documentar cómo está modelado HOY el cardio en cada caso real.

---

## Fase 1 — Auditoría del WorkoutPlayer actual

Producir una sección **"Estado actual"** que responda:

### 1.1 Arquitectura del componente
- Mapa de componentes: ¿es un componente monolítico, una página con sub-componentes, o una composición de cards?
- Composables que usa: estado local, persistencia (localStorage, IndexedDB), API calls
- Lifecycle: ¿cuándo carga el plan? ¿cuándo guarda progreso? ¿qué pasa al salir y volver?
- Polling/realtime: ¿hay WebSocket via Reverb? ¿polling 30s?

### 1.2 Modelo de datos del ejercicio (HOY)
Documentar **exactamente** qué campos del JSON usa el player para cardio y para fuerza, basándote en `MD 20-EJERCICIOS` y leyendo el código:
- Para **fuerza**: `nombre`, `series`, `repeticiones`, `descanso`, `rir`, `notas`, `gif_url`, `variacion`, `bloque/grupo_id/rondas`
- Para **cardio actual**: `is_cardio`, `nombre`, `duracion_min` o `repeticiones` ("30 min"), `velocidad_kmh`, `inclinacion_pct`, `notas`, `gif_url`

### 1.3 Modos de presentación
- "Preview" (lista de ejercicios del día antes de empezar) vs "Active" (ejercicio actual durante la sesión)
- Cómo se renderiza superset/circuito en cada modo
- Cómo se renderiza cardio en cada modo
- Cronómetro de descanso: ¿cómo arranca, se pausa, se salta?
- Tracking de peso/reps: ¿auto-fill desde sesión previa? ¿persistencia? ¿edición post-set?

### 1.4 Casos reales — cómo se ve hoy el cardio
Extraer del DB 5 planes con cardio y documentar:
- Qué campos del JSON tiene cada uno (HOY no hay estandar — algunos planes usan `duracion_min: 30`, otros `repeticiones: "30 min"`, otros `bloque: circuito` con estaciones)
- Cómo lo renderiza el player en cada caso
- Qué falla o queda subóptimo desde la perspectiva del cliente

### 1.5 Tracking de cardio
- ¿Se loguea la sesión de cardio? ¿Con qué métricas? (FC promedio, distancia, kcal estimadas, duración real, RPE)
- ¿Hay XP/racha por cardio o solo por pesas?
- ¿Hay diferencia en cómo se cuenta una sesión HIIT vs LISS hoy?

### 1.6 Gap analysis
Lista concreta de qué **NO** soporta el componente actual y limita la experiencia:
- Ej.: "No soporta intervalos con cronómetro automático trabajo/descanso"
- Ej.: "No diferencia HIIT de LISS visualmente — ambos son cards iguales"
- Ej.: "No hay zonas de FC para validar intensidad"
- Ej.: "No registra distancia ni kcal — solo tiempo"
- Ej.: "El coach solo puede prescribir 1 cardio LISS o un circuito; no hay forma natural de prescribir intervalos sprint/walk"

---

## Fase 2 — Investigación: metodologías de cardio

Producir una sección **"Estado del arte"** con investigación independiente.

### 2.1 Taxonomía fisiológica
Documentar las metodologías de cardio con criterios objetivos, NO subjetivos:

| Metodología | Duración trabajo | Duración descanso | Intensidad (% FCmax o W) | Zona FC | Objetivo fisiológico |
|---|---|---|---|---|---|
| LISS (Low Intensity Steady State) | 30-60 min continuo | — | 50-65% FCmax | Z2 | Oxidación grasa, recuperación activa |
| MISS (Moderate) | 20-45 min continuo | — | 65-75% FCmax | Z3 | Capacidad aeróbica |
| Threshold | 20-30 min continuo | — | 80-87% FCmax | Z4 | Umbral láctico |
| Tabata | 20s | 10s | máxima (>95%) | Z5 | VO2max, anaerobio |
| HIIT clásico | 30s-2min | 30s-2min | 85-95% FCmax | Z4-Z5 | VO2max, EPOC |
| EMOM (Every Minute) | variable | hasta completar minuto | submáxima | Z3-Z4 | Capacidad de trabajo |
| AMRAP (As Many Rounds As Possible) | bloque fijo (8-20 min) | mínimo | submáxima sostenida | Z3-Z4 | Capacidad/resistencia |
| Fartlek | variable libre | variable libre | 60-90% (varía) | Z2-Z4 | Mixta |
| Sprints | 10-30s | 60-180s | máxima | Z5+ | Potencia/CP system |
| Zona 2 puro (Seiler) | 45-120 min | — | 60-70% FCmax estricto | Z2 | Mitocondrial, base aeróbica |

Marcar cuáles son relevantes para WellCore (cliente típico: mujer LATAM 25-45, pérdida de grasa) y cuáles no (ej. ATP-PC pure sprints probablemente no aplican al 90% de clientes).

### 2.2 Cómo lo modelan apps líderes
Investigar (vía WebSearch o referencias documentadas) **cómo apps de coaching/tracking serias modelan cardio**:

- **TrainingPeaks** (deportistas de resistencia): structured workouts con bloques `Warmup → Main → Recovery → Cooldown`. Cada bloque tiene `duration`, `target_power_or_pace`, `repeat`. Las apps cargan el archivo .erg/.zwo y guían al usuario.
- **TrueCoach / Trainerize**: cardio como ejercicio con campos `duration`, `distance`, `intensity_level`. Sin segmentación de intervalos avanzada.
- **Hevy / Strong**: principalmente fuerza, cardio como nota libre.
- **FitBod**: auto-genera cardio interno.
- **Crossfit boxes (apps tipo Wodify)**: AMRAP/EMOM/RFT (Rounds For Time) son ciudadanos de primera clase, no improvisados.

Extraer **patrones reutilizables**:
- Modelo de bloques anidados (`structure: [{type:'warmup', duration:5}, {type:'interval', work:30, rest:30, rounds:8}, {type:'cooldown', duration:5}]`) vs flat
- Cómo manejan zonas de FC sin obligar al cliente a tener pulsómetro
- Cómo guían al cliente sin volverse "una app de Garmin"

### 2.3 ¿Qué realmente sirve a WellCore?
Filtrar la taxonomía del 2.1 con criterio:
- **Cliente típico no tiene pulsómetro** — la prescripción por % FCmax queda como guía, NO como gate
- **Coach prescribe en lenguaje simple** — "30 min ritmo donde puedas hablar pero no cantar" debe convertirse internamente a Zona 2
- **Equipamiento**: caminadora, escaladora, bicicleta, elíptica, remo ergómetro, jumping jacks, salto cuerda, escaladores (bodyweight HIIT)
- **Objetivos típicos**: pérdida de grasa (LISS + HIIT corto), recomposición (mix), recuperación activa (LISS muy suave), performance (intervalos)

Recomendación: **NO** modelar las 11 metodologías. Modelar **4-5 arquetipos** que el coach pueda combinar:

| Arquetipo | Lo que el coach prescribe | Lo que el cliente ve | Tracking |
|---|---|---|---|
| **Continuo suave (LISS/Z2)** | "30 min caminadora inclinada, 5.5 km/h, 10-12% inclinación" | Cronómetro descendente + indicador de intensidad | Duración real, distancia opcional, RPE |
| **Continuo moderado (MISS/Z3)** | "25 min escaladora, ritmo donde puedas hablar entrecortado" | Idem + chip "ritmo medio" | Idem |
| **Intervalos clásicos (HIIT)** | "8 rondas de 30s sprint / 30s caminar en caminadora" | Cronómetro de intervalos automático trabajo/descanso con audio | Rondas completadas + RPE |
| **Tabata** | "4 min: 8×(20s máximo / 10s descanso) — jumping jacks" | Cronómetro Tabata visual con audio | Rondas + RPE |
| **Circuito metabólico** | "20 min AMRAP: 10 burpees + 15 sentadillas + 20 mountain climbers" | Round tracker + cronómetro ascendente | Rondas completadas + RPE |

Más allá de estos 5, **NO inventar**. Si el coach quiere algo exótico, escribe nota libre.

---

## Fase 3 — Diseño del módulo

Producir una sección **"Propuesta de diseño"** con suficiente detalle para que una sesión de implementación pueda ejecutarla.

### 3.1 Schema JSON propuesto (aditivo)

Diseñar el schema sin romper el actual. Hoy un cardio es:

```json
{
  "nombre": "Caminadora Inclinada",
  "is_cardio": true,
  "duracion_min": 30,
  "velocidad_kmh": "5-6",
  "inclinacion_pct": "10-12",
  "gif_url": "..."
}
```

Proponer extensión opcional con `cardio_type` y `cardio_protocol`:

```json
{
  "nombre": "...",
  "is_cardio": true,
  "cardio_type": "continuous_low" | "continuous_moderate" | "intervals" | "tabata" | "amrap" | "emom",
  "duracion_min": 30,                  // sigue siendo el TOTAL
  "intensidad_objetivo": {              // guía, no gate
    "zona_fc": 2,                       // 1-5
    "porcentaje_fcmax": "60-70",
    "rpe": "4-5",                       // 0-10
    "descripcion_cliente": "Ritmo donde puedas hablar pero no cantar"
  },
  "protocol": {                         // solo si cardio_type es intervalos/tabata/amrap/emom
    "type": "intervals",
    "work_seconds": 30,
    "rest_seconds": 30,
    "rounds": 8,
    "warmup_min": 5,
    "cooldown_min": 5
  },
  // Campos existentes mantienen comportamiento:
  "velocidad_kmh": "5-6",
  "inclinacion_pct": "10-12",
  "gif_url": "..."
}
```

Documentar para cada `cardio_type` qué campos del `protocol` son obligatorios y cuáles no. Fallback: si no hay `cardio_type`, el player se comporta exactamente como hoy.

### 3.2 Componente Vue propuesto

Propuesta concreta de componentes nuevos dentro de `resources/js/vue/components/client/`:

- `CardioPlayer.vue` — wrapper que recibe el ejercicio y rutea al sub-componente según `cardio_type`
  - `CardioContinuous.vue` — cronómetro descendente con barra de progreso, indicador zona, RPE al final
  - `CardioIntervals.vue` — cronómetro de fase (trabajo/descanso/preparación), counter de rondas, audio cues
  - `CardioTabata.vue` — preset específico de 20/10 × 8, visualización distintiva
  - `CardioAMRAP.vue` — cronómetro ascendente + round tracker manual
  - `CardioEMOM.vue` — beep cada 60s, tracker de minutos completados

Cada sub-componente persiste su estado en localStorage por session_id para resiliencia (si el cliente cierra el browser por accidente).

### 3.3 Audio y guidance

- Beeps de inicio/fin de intervalo (opcional, toggle)
- Voz sintética (TTS browser) opcional para "Trabajo!" / "Descanso!" / "Última ronda"
- Vibración mobile (API `navigator.vibrate`) para evitar dependencia de audio
- **NO autoplay agresivo** — primer click del cliente arranca el audio context

### 3.4 Tracking

Propuesta de extensión a `workout_logs` o tabla nueva `cardio_logs`:

| Campo | Tipo | Notas |
|---|---|---|
| `session_id` | int | FK a `workout_sessions` |
| `exercise_index` | int | Posición en el día |
| `cardio_type` | string | Replicado del plan |
| `duration_planned_sec` | int | |
| `duration_actual_sec` | int | Lo que el cliente realmente hizo |
| `rounds_planned` | int? | Solo intervalos |
| `rounds_completed` | int? | |
| `rpe` | int 1-10 | Auto-prompt al finalizar |
| `notes` | text | Opcional cliente |
| `created_at` | timestamp | |

Decisión: ¿tabla nueva o columna `cardio_data JSON` en `workout_logs` existente? Argumentar trade-off (tabla nueva = más limpio queryable; columna JSON = menos migración, alineado con Strangler Fig).

### 3.5 Integración con XP/racha

Cómo se cuenta una sesión de cardio:
- ¿Misma XP que una sesión de pesas? ¿Menos? ¿Más por HIIT?
- Si el cliente solo hace cardio (días HIIT-only en plan de Lizeth, ej. sábado), ¿cuenta para racha?
- ¿Se considera "entreno completado" con solo cardio? (Hoy probablemente sí — revisar)

### 3.6 UI del coach (admin)

El coach hoy escribe el JSON manualmente o usa AI Generator. Propuesta:
- Editor visual mínimo en `app/Livewire/Admin/AIPlanGenerator.php` o sucesor: dropdown `cardio_type` + campos contextuales según tipo
- Validación frontend: si elige `intervals`, exige `work_seconds`, `rest_seconds`, `rounds`
- Preview del cardio renderizado antes de asignar

NO bloquear: el coach técnico siempre puede escribir el JSON crudo. La UI es atajo, no jaula.

### 3.7 Migración de planes existentes

Estrategia retrocompatible:
- Planes sin `cardio_type` → se infiere automáticamente: si tiene `duracion_min ≥ 20`, asumir `continuous_low/moderate` por keyword del nombre; si tiene `bloque: circuito` y duración corta, asumir `intervals` o `amrap`
- **NO modificar JSONs ya asignados** — el motor de inferencia vive en el componente, no toca DB
- Plan nuevo desde 2026-05-XX usa el schema completo

### 3.8 Plan de implementación por fases

Sugerir 3-4 fases incrementales, cada una entregable y reversible vía feature flag:

| Fase | Alcance | Riesgo | Tiempo estimado | Feature flag |
|---|---|---|---|---|
| Fase 0 | Schema + inferencia automática (sin UI nueva) | Bajo | 1 día | — |
| Fase 1 | `CardioContinuous` + `CardioIntervals` componentes | Medio | 2-3 días | `cardio_v2_intervals` |
| Fase 2 | `CardioTabata` + `CardioAMRAP` + `CardioEMOM` | Medio | 2-3 días | `cardio_v2_advanced` |
| Fase 3 | Tracking extendido + UI coach + métricas | Medio-alto | 3-5 días | `cardio_v2_tracking` |
| Fase 4 | Integración FC zonas + audio cues + offline | Alto | 5+ días | `cardio_v2_zones` |

---

## Reglas de diseño WellCore (no negociables)

1. **Vocabulario del cliente, no del coach**: el cliente NO debe ver "Z3" ni "85% FCmax" como primer dato. Ve "ritmo medio, donde puedas hablar entrecortado". El número técnico está disponible si lo busca.
2. **Sin elite-shaming**: una abuela haciendo Tabata cuenta tanto como un atleta. No mostrar "leaderboards" de RPE ni rondas.
3. **Sin condicionalidad agresiva**: el plan se ve completo aunque el cliente no tenga pulsómetro/Garmin/Whoop.
4. **Mobile-first**: el cardio se ejecuta en celular en el gym. Cronómetros legibles a 2 metros, audio siempre opcional.
5. **Offline-tolerant**: el cronómetro debe funcionar si pierde internet a mitad de sesión.
6. **Voz WellCore** (la marca, no un coach específico): las notas inline en español neutro/LATAM, tuteo, sin marketing, sin "usted", sin frases motivacionales de Instagram. WellCore tiene varios coaches (Anderson Ardila, Daniel Esparza Nuñez, etc.) y el plan refleja la voz del coach asignado a ese cliente — NO universalizar a Anderson. La voz de la marca (tono, vocabulario, estructura) es consistente; el nombre del coach varía. Ver `05-LENGUAJE-Y-VOZ.md` para el tono base.
7. **NO IA visible al cliente**: si hay sugerencias automáticas (ej. inferir tipo de cardio), atribución es "tu coach", no "IA".

## Formato del entregable final

Un solo archivo en `/tmp/CARDIO-MODULE-DESIGN-{YYYY-MM-DD}.md` con:

1. **Resumen ejecutivo** (½ página): top 3 hallazgos del audit, top 3 decisiones de diseño, costo aproximado de implementación en días-persona.
2. **Estado actual** (Fase 1) — auditoría con paths y line numbers reales.
3. **Estado del arte** (Fase 2) — investigación con citas (apps, ACSM/NSCA si aplica).
4. **Propuesta de diseño** (Fase 3) — schema, componentes, tracking, fases.
5. **Decisiones que requieren Daniel**: lista de bifurcaciones donde el owner debe elegir (ej. "¿tabla nueva o columna JSON?", "¿cuántos arquetipos modelar?", "¿integrar pulsómetros nativos o solo guía?").
6. **Riesgos** y **mitigaciones**: qué puede romper, qué planes existentes podrían verse afectados.
7. **Anexos**: muestras reales de JSON de 5 planes auditados (sin PII de clientes — solo estructura).

## Lo que NO está en alcance

- Implementar código (esta sesión es diseño)
- Integrar wearables (Garmin, Apple Watch, Whoop) en esta fase — propuesto para fase 4 futura
- Redesign visual del WorkoutPlayer completo — solo el módulo de cardio
- Cambiar el modelo de XP/racha (solo proponer cómo encaja cardio en él)
- Tocar la app vanilla PHP

## Tu primer paso

1. Leer en orden: `CLAUDE.md` → `CONTEXT.md` → `16a-JSON-SCHEMA-ENTRENAMIENTO.md` → `20-EJERCICIOS-VARIACIONES-TECNICA.md` → `07-CARDIO-REGLAS.md` → `08-METODOLOGIAS.md` → `25-WORKOUT-TRACKING-XP-RACHA.md`
2. Localizar el componente `WorkoutPlayer.vue` y sus composables; reportarme la ruta exacta antes de seguir
3. Hacer la query SQL sugerida (sección 1.4) y reportarme los 5 planes muestra que vas a auditar
4. **PAUSA aquí**. Antes de profundizar en Fase 2, confirmar conmigo (Daniel):
   - El alcance está completo o si quito/agrego algo
   - Si los 5 arquetipos de cardio (LISS/MISS/Intervals/Tabata/AMRAP/EMOM) son los correctos o quiero más/menos
   - Si hay un coach o cliente específico cuyo cardio actual quiera que mires con lupa
5. Después de la pausa, arrancar Fase 1 (audit) → Fase 2 (research) → Fase 3 (diseño) en orden secuencial. Reportar cada fase completa antes de avanzar.

Adelante.
