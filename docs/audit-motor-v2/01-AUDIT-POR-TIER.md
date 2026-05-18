# AUDIT POR TIER — Pre-trabajo para Motor v2

**Fecha:** 2026-05-16
**Ejecutado por:** Claude Opus 4.7 vía MCP Chrome DevTools + lectura de MDs
**Auditor:** sesión impersonificando 4 clientes reales de wellcorefitness.com como Daniel.esparza
**Output:** insumo de Pieza 1 (Catálogos curados) del plan motor v2

---

## TL;DR (5 líneas)

1. **Schema de plan tiene drift entre clientes**: Silvia Elite y Daniel Elite tienen JSONs con keys distintas — el motor v2 debe **canonicalizar primero**.
2. **Ciclo hormonal NO existe en datos reales** (Silvia: `{test:?}`, Daniel: `{}`). El schema masculino (TRT) está en MD 16d, pero femenino (menstrual) está SIN documentar. Es vertical a diseñar desde cero.
3. **Bloodwork count=0 en TODOS los clientes** auditados. Tabla `bloodwork_results` existe; UI existe; data no se ha cargado nunca.
4. **Método está en mal estado en prod** (1 sin plan, 2 expirados). Poco corpus histórico para entrenar el motor en esta vertical.
5. **Corpus seed del motor v2 vendrá EXCLUSIVAMENTE de planes reales curados + manuales Daniel/coach**, NO de AIPlanGenerator (decisión de producto 2026-05-16: AIPlanGenerator gasta API y se reemplaza por Claude Code orquestador local).

---

## 1. CLIENTES AUDITADOS

| Cliente | Tier | Estado | Coach asignado | Duración plan |
|---------|------|--------|----------------|---------------|
| Lizetd Tatiana Chávez Díaz | Esencial | Activa (DÍA -1, empieza 18 may) | Daniel Esparza Nuñez | 4 semanas |
| Karen Vanessa Gómez Lagos | Método | EN PREPARACIÓN (sin plan) | Héctor Leonardo Pérez Torres | 12 semanas (vacío) |
| Lizeth Ojeda | Método | EXPIRADO 18 abril | Daniel Esparza - CEO | (no accesible vía API) |
| Tatis Hortua | Método | EXPIRADO | Daniel Esparza - CEO | (no accesible) |
| Silvia Gomez Roa | Elite | ACTIVA (DÍA 27, expira en 3d) | Daniel Esparza - CEO | **4 semanas** ⚠️ anómalo |
| Daniel Esparza Nuñez | Elite | ACTIVO (DÍA 59) | Daniel Esparza - CEO | 12 semanas |

**Observación**: el 100% de los Método activos audited NO tenían contenido renderizable (preparación o expirados). El motor v2 tendrá poco "histórico curado" de Método para usar como corpus.

---

## 2. ESENCIAL — Lo que tiene + lo que necesita el motor v2

### Estructura observada (Lizetd Tatiana, JSON real)

**Training plan (29 keys):**
```
plan_type, titulo, programa, cliente, plan, objetivo, genero, nivel,
metodologia, frecuencia, frecuencia_dias, duracion_sesion, equipamiento,
duracion_semanas, peso_cliente, estatura, fecha_inicio, fecha_fin,
split, tecnicas_avanzadas, principios, semanas, notas_generales, notas_coach,
[ENRICHED por backend:]
objetivo_bloque, is_expired, weekly_schedule, total_series_semana, dias_semana,
rir_objetivo, volumen_label
```

- **Periodización**: 4 semanas con progresión real (141 → 147 → 151 → 159 series semana 1→4) ✅
- **Metodología**: "Body Part Split 5 días + HIIT sábado · Periodización lineal Adaptación → Hipertrofia → Fuerza → Peak"
- **Volumen**: ALTO
- **Frecuencia**: 6 días/semana
- **RIR objetivo**: 3 (principiante)
- **Técnicas avanzadas**: 4 (sobrecarga progresiva, drop set en sem 4, etc.)

**Nutrition plan:**
- **1650 kcal** (déficit agresivo)
- 150g P / 125g C / 60g G
- 4 comidas (no las 5 estándar)
- 20 tips_nutricionales
- `plan_dia_descanso` separado (variación de calorías días sin entreno)
- Periodización dias_entrenamiento vs dias_descanso

**Supplement plan:**
- 7 suplementos (Whey Concentrada, Creatina, Magnesio, Omega-3, Multi, Vit D, Cafeína)
- Tiene `descripcion_protocolo`, `perfil_cliente`, `advertencia` (legal disclaimers)

**Ciclo hormonal:** locked (correcto — Esencial no tiene acceso)
**Bloodwork:** locked (correcto)

### Lo que el motor v2 necesita para Esencial

**Knowledge base que tiene que consultar:**

1. **Tabla `methodologies` filtrada por tier=esencial**:
   - "Body Part Split 5 días" (1 método observado, pero necesitamos 5-8 más para variedad)
   - Upper/Lower 4d
   - Full body 3d
   - PPL 6d

2. **Tabla `exercise_metadata` con metadata canónica**:
   - GIF alias del catálogo de 265
   - Muscle groups
   - Equipment requerido
   - Variation reference (qué ejercicio sirve de variación)
   - Level mínimo (principiante/intermedio/avanzado)

3. **Tabla `nutrition_foods` con macros pre-calculados** (alimentos comunes Colombia/LATAM):
   - Por la observación: Lizetd tiene comidas con "Huevos enteros (3 unidades)", "Tostadas integrales (2 rebanadas, 60g)" — necesitamos catálogo con macros para sustitución sin recálculo manual

4. **Tabla `supplement_stacks` con stacks pre-armados**:
   - Stack "Mujer perdida grasa intermedia" (7 suplementos observados)
   - Stack "Hombre volumen avanzado" (Daniel)
   - Etc.

**Bugs detectados que el linter pre-INSERT debe atrapar:**

- 🐛 **Header dice "SEMANA 1 / 12"** pero `duracion_semanas=4`. Hardcoded vs JSON value.
- 🐛 **Chip "CARDIO LISS"** en día rotulado "Cardio HIIT puro" (Sábado). Inconsistencia semántica entre `cardio_type` y rótulo del día.
- 🐛 Plan empieza el 18 may pero header dice "DÍA -1" (correcto técnicamente, confuso para usuario).

---

## 3. MÉTODO — Lo que tiene + lo que necesita el motor v2

### Estructura observada

**Los 3 clientes Método auditados estaban inservibles:**
- Karen: EN PREPARACIÓN (training_plan existe pero vacío, `total_weeks=1` default)
- Lizeth Ojeda: API 403 (plan expirado 18 abril)
- Tatis Hortua: API 403 (idem)

**Lo que sí sabemos por MD 04:**
- Duración estándar: **12 semanas**
- Estructura sugerida: 3 Adaptación + 3 Hipertrofia + 3 Fuerza Máxima + 3 Peak
- Mismo set de tabs que Esencial (Entrenamiento, Hábitos, Nutrición, Suplementos)
- Ciclo + Bloodwork: 🔒 locked

### Lo que el motor v2 necesita para Método

Lo mismo que Esencial **más**:

1. **Métodos avanzados habilitados** (no solo "Body Part Split"):
   - DUP (Daily Undulating Periodization)
   - Upper/Lower con ondulante
   - Push/Pull/Legs avanzado
   - 5x5 / 3x5 fuerza
   - Periodización por bloques 3-3-3-3

2. **Progresión semanal MÁS AGRESIVA**:
   - Esencial: 3×12 → 4×10 → 4×8 → 5×6 (4 sem)
   - Método: ESCALAR a 12 semanas con DUP o periodización ondulante
   - Decision rule: nivel intermedio/avanzado → metodología candidata

3. **Catálogo de ejercicios "intermedios/avanzados"**:
   - Diferenciar `level: principiante|intermedio|avanzado` en `exercise_metadata`
   - El motor selecciona pool según nivel del cliente

**Gap observado:** No hay data real de Método activo para muestrear como template. **Daniel debe armar 2-3 templates de referencia** antes de que el motor v2 tenga corpus.

---

## 4. ELITE MUJER (Silvia Gomez Roa) — Lo que tiene + lo que necesita

### Estructura observada (JSON real)

**Training plan:**
- 4 semanas (FUERA del rango oficial 12-16 que dice MD 04 ⚠️)
- Metodología: "Periodización por bloques · Adaptación → Progresión → Progresión → Pico"
- Volumen ALTO, 120 series semana 1 → 155 semana 4 (+29%)
- **4 técnicas avanzadas**: Pirámide Ascendente, Drop Set, Rest Pause, Drop Set Triple
- 4 principios: sobrecarga, **conexión mente-músculo** (extra vs Esencial), técnica primero, registro
- RIR objetivo: 1 (avanzado)
- Frecuencia: 5 días

**Nutrition plan:**
- 1800 kcal
- 130P / 185C / 60G (recomposición moderada)
- **5 comidas** (Desayuno, Snack AM, Almuerzo, Snack PM, Cena) — más que Esencial
- 6 tips (curiosamente MENOS tips que Esencial)
- `plan_dia_descanso_exists: false` — Elite NO tiene plan separado (¿simplificación?)

**Supplement plan (más sofisticado):**
- 7 suplementos: Proteína **Isolada** (vs Concentrada), Creatina, Omega-3, **Vit D3+K2**, **Magnesio Glicinato**, **Multivitamínico femenino**, **Colágeno+Vit C**
- Forma química específica (Glicinato, Isolada) → mayor expertise
- Stack más enfocado en salud femenina

**Ciclo hormonal:** 🚨 **Solo placeholder `{test: ?}` — VACÍO funcionalmente**
**Bloodwork:** count 0

### Lo que el motor v2 necesita para Elite Mujer

1. **Ciclo menstrual femenino DESDE CERO** (no está documentado en MD 16d que solo cubre TRT masculino):

```
Vertical NUEVA: ciclo_menstrual
Schema propuesto:
- fase_actual (folicular_temprana | folicular_tardía | ovulación | lútea_temprana | lútea_tardía | menstrual)
- duracion_ciclo (días, default 28)
- primer_dia_ultimo_ciclo (fecha)
- ajustes_entrenamiento_por_fase:
    folicular: volumen ↑, intensidad ↑, sensibilidad insulina ↑
    ovulación: peak fuerza, ojo lesiones (laxitud ligamentaria)
    lutea_temprana: mantener volumen, recuperación ↑
    lutea_tardía: bajar intensidad, foco técnica, accept fatiga
    menstrual: volumen ↓50%, opcional descanso activo
- ajustes_nutricion_por_fase:
    folicular: carbohidratos ↑, calorías base
    ovulación: cuidar inflamación, anti-inflamatorios
    lutea: calorías +100-200 (cortisol/cravings), magnesio ↑
    menstrual: hierro, calorías mantenimiento
- sintomas_a_trackear: cólicos, cambio_animo, antojos, energia, sueno
- birth_control_status (afecta el ciclo natural)
- referencias_cientificas[]
```

**Esto NO existe hoy. Es vertical NUEVA.** Daniel + coach mujer + Anderson deberían validar científicamente. Posibles fuentes: Stacy Sims (Roar), Lauren Colenso-Semple, Brad Schoenfeld (entrenamiento femenino), Helen Kollias.

2. **Suplementos por género** (catálogo bifurcado):
   - Stack salud femenina intermedia (lo que tiene Silvia)
   - Stack salud femenina ciclo (hierro, magnesio, vit B6, evening primrose)
   - Stack performance femenina (creatina dosis 3-5g, omega-3 alto EPA)

3. **Técnicas avanzadas habilitadas**: Pirámide Ascendente, Drop Set, Rest Pause, Drop Set Triple — el motor v2 las debe poder componer.

---

## 5. ELITE HOMBRE (Daniel Esparza Nuñez) — Lo que tiene + lo que necesita

### Estructura observada (¡SHAPE DISTINTO a Silvia!)

🚨 **El JSON de Daniel tiene keys completamente distintas al de Silvia** — el motor v2 debe canonicalizar:

| Concepto | Silvia (Elite) | Daniel (Elite) |
|---|---|---|
| Top-level keys | metodologia, tecnicas_avanzadas[], principios{}, semanas[]con fase canónica | dias_por_semana, objetivo_principal, principios_clave, dias, cardio_post_entreno, progresion_semanal |
| Fase semanal | "adaptación neuromuscular" | "acumul" (truncado) |
| Técnicas avanzadas count | 4 | **0** (no las usa en el shape) |
| Semanas array | 4 entries | **1 entry** (probable JSON parcial) |

**Nutrition (mucho más rico que Silvia):**
- **6 comidas** (vs 5 Silvia, 4 Lizetd Esencial)
- Keys extras: `alimentos_base_colombianos`, `preparacion_practica`, `enfoque_culinario`, `semanas_progresion`, `horario_entrenamiento`, `objetivo_cal` (no `objetivo_calorico`)
- Macros: **200P / 525C / 100G** (volumen agresivo, ~3700 kcal)
- Macros incluye `nota`: "Proteína 2.4g/kg (peso objetivo 83kg). Carbohidratos en torno al entrenamiento..."

**Supplement plan (más sofisticado que Silvia, distinto enfoque):**
- 7 suplementos: Whey Isolate, Multivit, **Neuro-Freak (pre-entreno)**, Creatina, **L-Glutamina**, **Beta-Alanina**, **L-Citrulina Malato**
- Diferencia vs Silvia: foco PERFORMANCE (Beta-Alanina, Citrulina) vs SALUD (Magnesio, Vit D, Colágeno)
- Keys extras: `stack_completo`, `stack_prioridad`, `protocolo_dia_tipico`, `interacciones_y_seguridad`, `timing_diario`

**Ciclo hormonal:** `{}` totalmente vacío — peor que Silvia (placeholder)
**Bloodwork:** count 0

### Lo que el motor v2 necesita para Elite Hombre

1. **Ciclo hormonal MASCULINO (TRT/ergogénicas)** — el schema YA EXISTE en MD 16d:

```
Schema documentado en MD 16d (TRT Fase 1, 12 semanas):
- nombre, descripcion_protocolo, duracion, advertencia
- metricas (testo_total_baseline, estradiol_baseline, testo_objetivo)
- compounds[] (testo enantato, dosis, frecuencia, días, vía IM)
- phases[] (estabilización 1-4 / ajuste 5-8 / mantenimiento 9-12)
- pct[] (HCG, clomifeno, etc.)
- labs[] (qué pruebas en semana 0/6/12)
- efectos_secundarios[]
- monitoreo_diario[]
- emergencia[] (señales de alerta médica)
- notas_coach
```

**Pero schema EXISTE, datos NO**. Daniel debe definir 3-4 protocolos canónicos:
- TRT mantenimiento (testo enantato 100mg/sem)
- TRT alto rango (cipionato 200mg/sem)
- Ciclo natural ergogénico (sin esteroides: tribulus, ashwagandha, fadogia agrestis, tongkat ali)
- PCT post-ciclo (clomifeno + tamoxifeno + HCG)

⚠️ **Risk legal**: Daniel debe definir explícitamente qué prescribe WellCore vs qué solo "documenta lo que el cliente ya hace bajo supervisión médica externa". El MD 16d ya tiene `advertencia` field — usarlo siempre.

2. **Bloodwork con knowledge médica**:
   - Catálogo de tests con rangos de referencia (testo total, testo libre, estradiol, LH, FSH, SHBG, hemoglobina, hematocrito, perfil lipídico, PSA, glucosa, A1c, ALT, AST, creatinina, BUN, TSH, T3, T4, ferritina, vitamina D, vitamina B12, magnesio, zinc)
   - Categorización por relevancia: atletas BB, diabetes, condiciones clínicas, salud general
   - Interpretación contextual (rango "normal" lab vs rango "óptimo" performance)

3. **Stack de suplementos avanzado masculino**:
   - Pre-workout (Neuro-Freak, C4, etc.)
   - Performance (Beta-Alanina, Citrulina, Creatina monohidrato, HMB)
   - Recuperación (L-Glutamina, Magnesio, ZMA)
   - Salud male (Vit D, Omega-3, Tribulus opcional, Ashwagandha)

---

## 6. BLOODWORK — Vertical totalmente nueva

**Observación**: TODOS los 4 clientes auditados tienen `bloodwork: []` vacío.

**Tabla existe** (`bloodwork_results` con columns: id, client_id, test_name, value, unit, reference_range, test_date). **UI existe** (tab 🔒 Bloodwork para Elite).

**Lo que falta para el motor v2**:

| Componente | Estado | Esfuerzo |
|---|---|---|
| Catálogo de tests con rangos referencia | NO existe | 8-12h con coach Anderson + revisión médica |
| Reglas de interpretación (qué decir si testo < 300 ng/dL) | NO existe | 4h por panel |
| Categorización por relevancia | NO existe | 2h |
| Workflow de carga (cliente sube fotos de labs → OCR → DB) | NO existe | Feature aparte |
| Integración con `ciclo_hormonal.labs[]` | Documentada en MD 16d | Code en motor v2 |

**Daniel dijo**: "bloodwork tiene sentido para atletas de bodybuilding o personas con condiciones especiales como diabetes o aspectos clínicos (esto hay que averiguar bien porque esa metodología tiene que ser basada en ciencia)".

**Recomendación honesta**: Bloodwork debería ser **vertical OPCIONAL Sprint 5+**, no parte del MVP. Razón: requiere validación médica formal y datos reales para entrenarse. Hoy hay 0 entries — empezarlo desde 0 es high-effort, low-MVP-value.

---

## 7. RISE — Vertical separada (no auditada por tiempo)

**Por MD 04 y memoria de proyecto:**
- 28 días (no 4 semanas en bucle, días únicos)
- Identidad visual dorada `#D4AF37` (no rojo WellCore)
- 6 clientes RISE activos observados en lista admin
- Tabla separada: `rise_programs` + `rise_days` + `rise_day_logs` + `rise_check_ins`

**Para motor v2**: RISE es **bounded context aparte**. El motor v2 puede generar planes "tipo RISE" pero el flujo de almacenamiento es distinto (NO usa `assigned_plans`).

**Recomendación**: RISE en sprint 5+, después de validar el motor con las 5 verticales base.

---

## 8. PIEZA 1 — CATÁLOGOS A CURAR (prioridad ranqueada)

Daniel: esto es lo que tenés que ir armando EN PARALELO mientras Opus diseña el motor v2.

### 8.1 Sprint 0 (esta semana) — Catálogos críticos para MVP

| # | Catálogo | Filas mínimas | Fuente | Esfuerzo |
|---|----------|---------------|--------|----------|
| 1 | **Metodologías de entrenamiento** | 10-15 | MD 08 actual (13 métodos) + tu cabeza | 2-3h |
| 2 | **Ejercicios core con metadata** | 100 (top 80% uso) | Catálogo 265 GIFs + filtrar uso real | 4-6h |
| 3 | **Alimentos colombianos con macros** | 60-80 | MD 04 sección 2.6 + USDA / FoodData | 3-4h |
| 4 | **Suplementos con dosis canónicas** | 25-30 | Stacks de Silvia + Daniel + MD 04 sec 2.5 | 2h |
| 5 | **Templates por tier+objetivo** | 8 (Esencial × 2 obj, Método × 2, Elite × 2, RISE) | Plan Lizetd + Silvia + Daniel | 6-8h |

### 8.2 Sprint 1 (próxima semana)

| # | Catálogo | Filas | Fuente | Esfuerzo |
|---|----------|-------|--------|----------|
| 6 | **Hábitos sugeridos** | 20 | MD 16d parte 1 (5 fijos + custom) | 1h |
| 7 | **Tips nutricionales reusables** | 50-80 | Tips de Lizetd (20) + Silvia (6) + corpus nuevo | 2h |
| 8 | **Tips de entrenamiento** | 30-50 | MD 08 + tu cabeza | 2h |
| 9 | **Notas técnicas de ejercicio** | 100 (1 por ejercicio core) | Por defecto del ejercicio | 4h |

### 8.3 Sprint 2-3 — Verticales nuevas

| # | Catálogo | Esfuerzo | Notas |
|---|----------|----------|-------|
| 10 | **Ciclo menstrual femenino** | 12-16h | Vertical NUEVA. Validar científicamente. Stacy Sims / Lauren Colenso-Semple. |
| 11 | **Protocolos TRT/PCT/ergogénicas** | 8-12h | Schema existe (MD 16d). Datos NO. Validación legal: prescribe vs documenta. |
| 12 | **Panel bloodwork con rangos referencia** | 10-15h | Coach Anderson + ref médica. Solo si decides incluir en MVP. |

### 8.4 Sprint 4+ — Polish

| # | Catálogo | |
|---|----------|---|
| 13 | RISE 28 días templates | Bounded context propio |
| 14 | Recomposición específica | Variante combinado |
| 15 | Cardio standalone | Raro pero existe |

---

## 9. SCHEMA DE `wellcore_kb` (DB local) — propuesta inicial

Para que el motor v2 consulte. Migraciones aditivas LOCALES (NO tocan `wellcore_fitness` de producción).

```sql
-- Catálogo central de metodologías
CREATE TABLE methodologies (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  slug VARCHAR(80) UNIQUE,           -- "body-part-split-5d", "dup-upper-lower", etc.
  name VARCHAR(160),
  type ENUM('entrenamiento','nutricion','suplementacion','habitos','ciclo_menstrual','ciclo_hormonal'),
  description TEXT,
  periodization_pattern JSON,         -- {"semanas":4,"fases":["Adaptación","Hipertrofia","Fuerza","Peak"]}
  applicable_tiers JSON,              -- ["esencial","metodo","elite"]
  applicable_levels JSON,             -- ["principiante","intermedio","avanzado"]
  applicable_objectives JSON,         -- ["perdida_grasa","aumento_muscular","recomposicion"]
  applicable_gender JSON,             -- ["masculino","femenino","ambos"]
  default_frequency_days TINYINT,
  default_volume_label VARCHAR(40),
  scientific_references JSON,         -- ["Schoenfeld 2017","Helms 2019"]
  version INT DEFAULT 1,
  active BOOLEAN DEFAULT TRUE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Catálogo de ejercicios enriquecido
CREATE TABLE exercise_metadata (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  alias VARCHAR(120) UNIQUE,          -- "press-banca-barra" (matchea GIF)
  name_es VARCHAR(160),
  name_en VARCHAR(160),
  muscle_groups JSON,                 -- ["pecho","triceps","hombro_anterior"]
  primary_muscle VARCHAR(40),
  secondary_muscles JSON,
  equipment JSON,                     -- ["barra","banco_plano"]
  is_compound BOOLEAN,
  is_unilateral BOOLEAN,
  is_cardio BOOLEAN,
  cardio_type VARCHAR(30),            -- continuous_low|intervals|tabata|null
  level_min ENUM('principiante','intermedio','avanzado'),
  variation_aliases JSON,             -- ["press-banca-mancuernas","press-banca-maquina"]
  common_mistakes JSON,
  default_notes TEXT,
  default_tecnica_ejecucion TEXT,
  gif_url VARCHAR(255),               -- URL completa GitHub raw
  contraindications JSON,             -- ["lesion_hombro","cirugia_lumbar_reciente"]
  version INT DEFAULT 1
);

-- Catálogo de alimentos con macros
CREATE TABLE nutrition_foods (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(160) UNIQUE,
  category ENUM('proteina','carbohidrato','grasa','vegetal','fruta','suplemento','lacteo','bebida'),
  unit_default VARCHAR(20),           -- "g","unidad","scoop","cda"
  protein_per_100g DECIMAL(6,2),
  carbs_per_100g DECIMAL(6,2),
  fat_per_100g DECIMAL(6,2),
  kcal_per_100g DECIMAL(7,2),
  availability_country JSON,          -- ["colombia","mexico","argentina"]
  alternatives JSON,                  -- alias_id de sustitutos
  is_vegetarian BOOLEAN,
  is_vegan BOOLEAN,
  is_gluten_free BOOLEAN,
  notes TEXT
);

-- Catálogo de suplementos
CREATE TABLE supplement_catalog (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(160),
  category ENUM('proteina','creatina','vitamina','mineral','pre_workout','recovery','performance','salud_femenina','salud_masculina','adaptogeno','ergogenico'),
  scientific_name VARCHAR(160),
  dosis_default VARCHAR(80),
  dosis_range VARCHAR(80),
  timing_default VARCHAR(120),
  frecuencia_default VARCHAR(80),
  contraindications JSON,
  interactions JSON,
  legal_disclaimer TEXT,
  evidence_level ENUM('alta','moderada','baja','anecdotica'),
  applicable_gender JSON,
  scientific_references JSON,
  version INT
);

-- Stacks pre-armados (combinaciones)
CREATE TABLE supplement_stacks (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(160),
  tier ENUM('esencial','metodo','elite'),
  objective VARCHAR(80),
  gender VARCHAR(20),
  level VARCHAR(20),
  components JSON,                    -- [{supplement_id, dosis, timing, priority}]
  description TEXT,
  notes_coach TEXT
);

-- Templates de planes completos (corpus base + crece con uso)
-- ⚠️ source EXCLUYE 'ai_generated' por decisión de producto 2026-05-16:
--    Motor v2 NO consume del AIPlanGenerator (gasta API). Todo el corpus
--    viene de planes reales curados + manuales coach + literatura.
CREATE TABLE plan_templates_local (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(160),
  tier ENUM('esencial','metodo','elite','rise','presencial','trial'),
  plan_type ENUM('entrenamiento','nutricion','suplementacion','habitos','ciclo_menstrual','ciclo_hormonal'),
  methodology_id BIGINT FK,
  objective VARCHAR(80),
  level VARCHAR(20),
  gender VARCHAR(20),
  duracion_semanas TINYINT,
  content_json JSON,                  -- el plan completo en schema canónico 16a/b/c/d
  source ENUM('curated_literature','from_real_client','manual_daniel','manual_coach'),
  source_client_id BIGINT NULL,       -- si viene de un cliente real (anonimizado)
  is_validated BOOLEAN DEFAULT FALSE, -- coach revisó y aprobó
  validated_by VARCHAR(40),           -- "daniel" | "anderson" | "coach_X"
  validated_at TIMESTAMP NULL,
  use_count INT DEFAULT 0,
  success_score TINYINT,              -- 0-10 según outcomes
  version INT,
  active BOOLEAN
);

-- Reglas de decisión (rules engine)
CREATE TABLE decision_rules (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(120),
  rule_type ENUM('select_methodology','select_split','select_volume','select_supplement_stack','select_diet_approach'),
  input_pattern JSON,                 -- {"tier":"esencial","level":"principiante","days":3-4,"gender":"any"}
  output JSON,                        -- {"methodology_id":1,"confidence":0.9}
  confidence DECIMAL(3,2),            -- 0.00-1.00
  rationale TEXT,                     -- por qué esta regla
  priority INT,                       -- orden de evaluación
  active BOOLEAN
);

-- Reglas del linter pre-INSERT
CREATE TABLE lint_rules (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(120) UNIQUE,
  applies_to ENUM('entrenamiento','nutricion','suplementacion','habitos','ciclo_hormonal','ciclo_menstrual','any'),
  severity ENUM('error','warning','info'),
  check_function VARCHAR(80),         -- nombre del check PHP: "no_monotonia_repeticiones", "fase_oficial"
  parameters JSON,                    -- {"max_pct_misma_progresion":0.6}
  message_template TEXT,              -- "El {n}% de ejercicios usa misma progresión 3×12-15"
  auto_fix_function VARCHAR(80) NULL, -- nombre del fix PHP si auto-corregible
  active BOOLEAN
);

-- Casos exitosos (corpus de RAG futuro)
CREATE TABLE success_cases (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  client_id_prod BIGINT,              -- referencia al cliente real
  plan_template_id BIGINT FK,
  outcome ENUM('progreso_excelente','progreso_aceptable','sin_progreso','abandono'),
  adherence_pct TINYINT,
  duration_completed_weeks TINYINT,
  before_after JSON,                  -- {peso_inicio, peso_fin, medidas, fotos}
  client_feedback TEXT,
  coach_notes TEXT,
  embeddings_vector BLOB NULL,        -- para RAG en sprint posterior
  is_anonymized BOOLEAN DEFAULT FALSE,
  active BOOLEAN
);
```

**Migración**: Estas tablas viven en **`wellcore_kb` schema LOCAL en Herd**, NO en producción.

---

## 10. PRIORIDADES ACCIONABLES PARA HOY

Si Daniel solo puede arrancar UNA cosa hoy mientras Opus diseña:

### Opción A (recomendada): Tabla `methodologies` + 10 entries

Abrí un Google Sheet o markdown con esta tabla y empezá a llenarla:

| slug | name | type | duracion_default | periodizacion | aplicable_tiers | aplicable_levels |
|------|------|------|------------------|---------------|-----------------|------------------|
| body-part-split-5d | Body Part Split 5 días | entrenamiento | 4 | Adapt→Hipert→Fuerza→Peak | esencial,metodo | intermedio,avanzado |
| upper-lower-4d | Upper/Lower 4 días | entrenamiento | 8 | DUP | esencial,metodo | principiante,intermedio |
| ppl-6d | Push/Pull/Legs 6 días | entrenamiento | 12 | Lineal | metodo,elite | avanzado |
| full-body-3d | Full Body 3 días | entrenamiento | 4 | Lineal | esencial,trial | principiante |
| dup-block-12 | DUP por bloques 12 sem | entrenamiento | 12 | Ondulante | metodo,elite | intermedio,avanzado |
| ... | ... | ... | ... | ... | ... | ... |

### Opción B: Tabla `exercise_metadata` con top 100

Tomá el catálogo de 265 GIFs en GitHub y filtrá los 100 que SÍ se usan en los planes reales (Silvia, Daniel, Lizetd, Cristian, Lizeth). Anotá metadata: muscle_groups, equipment, level, variations.

### Opción C: Validar contenido bloodwork con un médico aliado

Si tenés acceso a un médico/endocrinólogo, **valida ahora** qué tests quieres ofrecer y los rangos de referencia. Esto desbloquea sprint 5 de antemano.

---

## 11. RIESGOS Y GAPS DETECTADOS

| Riesgo | Impacto | Mitigación |
|---|---|---|
| Schema heterogéneo Silvia vs Daniel (Elite) | Motor v2 produce JSONs diferentes entre clientes Elite | Linter pre-INSERT con schema canónico estricto |
| Ciclo menstrual NO documentado | Vertical Elite femenina queda débil | Sprint dedicado con validación científica |
| Bloodwork sin data real | Tab existe pero vacío para todos | Sprint 5+, validar con médico antes |
| Método activos = 0 en prod | Poco corpus para entrenar motor en esta vertical | Daniel arma 2-3 templates manuales primero |
| AIPlanGenerator del admin gasta API y se evita | Decisión de producto 2026-05-16: NO se usa como corpus, NO se actualiza, queda como feature legacy | Motor v2 orquesta TODO con Claude Code local. Corpus viene de planes reales + manual coach + literatura. AIPlanGenerator queda detrás de killswitch `WC_AI_GENERATOR_ENABLED=false` |
| RPE vs RIR mismatch | Confusión semántica documentada en MD 24 | Motor v2 usa RIR siempre, convierte si recibe RPE |
| `assigned_plans` no tiene `updated_at` ni `version` | Si motor v2 lo intenta, INSERT falla | Linter atrapa, doc en spec |

---

## 12. RECOMENDACIÓN FINAL: ORDEN DE TRABAJO

**Mientras Opus diseña los 9 docs del motor v2 (5-6 semanas):**

| Semana | Tú haces | Opus diseña |
|---|---|---|
| **1** (ahora) | Methodologies (10 entries) + exercise_metadata top 50 | Doc 01 HF pattern + Doc 02 current state |
| **2** | exercise_metadata top 100 + nutrition_foods top 60 | Doc 03 KB schema + Doc 04 stages |
| **3** | supplement_catalog 30 + supplement_stacks 8 | Doc 05 decision engine + Doc 06 lint rules |
| **4** | plan_templates (8 templates desde clientes reales) | Doc 07 strangler-fig + Doc 08 weekly loop |
| **5** | Ciclo menstrual (con validación científica) | Doc 09 risks + arrancar implementación |
| **6** | Protocolos TRT/ergogénicas + bloodwork research | Sprint 1 implementación (linter aislado) |

**Cuándo escalás complejidad:**
- Bloodwork: sprint 5+ (después de validar con médico)
- RISE: sprint 6+ (bounded context propio)
- Multi-coach (planes de Anderson + otros): sprint 7+

---

**Próximo paso recomendado**: cuando Opus entregue el doc 01 del motor v2, comparalo con este audit. Si Opus propone algo que contradice esta realidad observada, paralo y mostrame el conflicto.

---

## ANEXOS

- Screenshots: `docs/audit-motor-v2/esencial-plan-entrenamiento.jpeg` (Lizetd)
- JSONs muestreados disponibles en task metadata (#2, #4, #5)
- Catálogo GIFs canónico: https://github.com/analyticfitness-design/wellcore-exercise-gifs (265 entries)
- MD 04 referencia de duraciones oficiales por tier
- MD 16d schema TRT masculino (ya documentado)
- MD 24 inconsistencias del AIPlanGenerator existente
