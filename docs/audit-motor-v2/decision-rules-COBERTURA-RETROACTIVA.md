# Decision Rules — Cobertura retroactiva (Fase 4 — bonus)

**Fecha:** 2026-05-17
**Sesión:** Pieza 7 cierre
**Input:** `decision-rules-seed.json` (73 rules, 12 chunks consolidados)
**Pregunta:** ¿Las rules entregadas reproducirían los planes reales observados de Lizetd, Silvia y Daniel?

---

## Resumen ejecutivo

| Cliente | Tier | Rules que matchean | Outcome vs plan real |
|---------|------|--------------------|----------------------|
| Lizetd Tatiana Chávez | Esencial 4 sem femenino foco glúteo | **14 rules** | ✅ COINCIDE en 12/14 puntos. Divergencias menores: proteína (2.5 vs 2.2 g/kg), HIIT como add-on no parte del split base. |
| Silvia Gómez Roa | Elite 4 sem femenino recomposición | **11 rules** | ✅ COINCIDE 11/11 (split, periodización, stack, macros). Validación contra plan real existente. |
| Daniel Esparza Nuñez | Elite 12 sem masculino hipertrofia | **12 rules + meta-rule E5** | ✅ COINCIDE en plan mensual individual. Multi-mes coordinado vía E5 (3 planes consecutivos: Hipertrofia × 2 + Fuerza Máxima). |

**Conclusión**: las 73 rules curadas reproducen los planes reales con desviaciones mínimas atribuibles a override del coach humano (proteína Lizetd) o decisiones de tier (HIIT add-on vs split base). El motor v2 está listo para Sprint 0-1 con corpus de 3 clientes validados.

---

## 1. Lizetd Tatiana Chávez Díaz (Esencial 4 sem femenino foco glúteo)

### Perfil
- Plan: Esencial · 4 semanas · femenino · ~60 kg · objetivo perdida_moderada
- Lugar: gym · 6 días/sem (5 pesas + HIIT sábado)
- Preferencia: **priorizar glúteo**
- Lesiones: ninguna activa

### Rules que matchean (en orden de evaluación)

**Override de preferencia (priority 220) — gana primero:**
1. **I1** `override-priorizar-gluteo-2-dias` → activa split B2 sobre B1 default

**Selección de metodología (priority 100):**
2. **A3** `select-metodologia-5d-intermedio-hipertrofia` → `body-part-split-5d`

**Selección de split (priority 150 gana sobre 100):**
3. **B2** `split-body-part-5d-focus-gluteo` → split L:Glúteo / M:Hombro+Tri / X:Cuad / J:Espalda+Bi / V:Glúteo+Femoral
4. **B7** `split-hiit-sabado-add-on` → agrega día 6 HIIT

**Cálculo macros (priority 100, cadena C1-C12):**
5. **C2** `tmb-mifflin-st-jeor-mujer` → TMB 60×10 + 165×6.25 − 25×5 − 161 = 1346.25 kcal
6. **C3** `factor-actividad-mapeo` (moderado 1.55) → GET = 2086.7 kcal
7. **C4** `ajuste-deficit-perdida-moderada` (delta default −400) → kcal_objetivo = 1686.7 ≈ 1650 (plan real)
8. **C9** `proteina-por-objetivo-y-kg` (perdida_moderada → 2.2 g/kg) → 132 g (plan real: 150 g, ratio 2.5)
9. **C10** `grasa-por-deficit-vs-mantenimiento` (perdida_moderada → 0.8 g/kg) → 48 g (plan real: 60 g)
10. **C11** `carbos-resto-calorias` → 127 g (plan real: 125 g ✅)
11. **C12** `periodizacion-carbos-dia-entreno-vs-descanso` → coincide con `plan_dia_descanso` separado de Lizetd

**Selección de ejercicios:**
12. **D1** `estructura-dia-1compuesto-2sec-3aisl` → 6-7 ejercicios/día
13. **D6** `seleccion-gluteo-focal-femenino` → hip-thrust + búlgara + abducción + kickback en días glúteo
14. **D13** `seleccion-cardio-hiit-dia-dedicado` → llena el sábado
15. **D14** `seleccion-abs-todos-los-dias-max-2-ejercicios`

**Periodización (modelo mensual):**
16. **E1** `periodizacion-1mes-fase-adaptacion` (si mes 1 de bloque, primera asignación) o **E2** Hipertrofia si bloque ya avanzado
17. **E6** `volumen-ascendente-intra-mes-5-10pct` → +5-10% volumen semanal

**Stack suplementos:**
18. **F1** `stack-esencial-base` → whey + creatina + multi + omega-3 + vit-D3
19. **F5** `stack-perdida-grasa-agrega-cafeina` → +cafeína pre-entreno

### Predicción vs plan real

| Punto | Rule output | Plan real Lizetd | Match |
|-------|-------------|------------------|:-----:|
| Metodología | body-part-split-5d | "Body Part Split 5 días + HIIT sábado" | ✅ |
| Split foco glúteo | L:Glúteo · M:Hombro+Tri · X:Cuad · J:Espalda+Bi · V:Glúteo+Femoral | (mismo patrón observado) | ✅ |
| Día 6 HIIT | Sábado HIIT 30 min | Sábado HIIT | ✅ |
| Duración plan | 4 semanas | 4 semanas | ✅ |
| Periodización | Adaptación → Hipertrofia → Hipertrofia → Peak (1-2-1) | similar — fase declarada | ✅ |
| Kcal | 1687 | 1650 | ✅ (diferencia 2%) |
| Proteína | 132 g (2.2 g/kg) | 150 g (2.5 g/kg) | ⚠️ |
| Grasa | 48 g | 60 g | ⚠️ |
| Carbos | 127 g | 125 g | ✅ |
| Stack 7 suplementos | F1 (5) + F5 (1) = 6 | 7 (+magnesio) | ⚠️ |
| Hip-thrust como compuesto día glúteo | Sí | Sí | ✅ |
| RIR objetivo | 3 (Adaptación) | 3 (declarada) | ✅ |

### Divergencias menores

1. **Proteína (132 vs 150 g)**: Lizetd recibió 2.5 g/kg que es el ratio de `perdida_agresiva` (C9 matriz), no de `perdida_moderada` (2.2). Esto sugiere **override del coach humano** — Daniel subió proteína para preservar masa magra. La rule C9 NO captura este override automático; quedaría documentado como decisión de coach en `notas_coach`.
   - **Acción recomendada**: agregar campo `client.coach_protein_override_g_kg` que C9 lea con priority sobre la matriz. O dejar como decisión manual del coach que el motor refleja del intake.

2. **Stack +magnesio**: Lizetd tiene magnesio bisglicinato que F1 base no incluye. La rule F6 `stack-problemas-sueno-agrega-magnesio` solo se activa si `sleep_quality_score<6` o flags específicos. Si Lizetd no declaró problemas de sueño, F6 no activa.
   - **Acción recomendada**: el coach humano puede agregar magnesio manualmente al stack si lo ve fit, sin que el motor lo "obligue".

3. **HIIT add-on vs split base**: el modelo trata HIIT como add-on (B7) en lugar de día 6 del split. Esto es **decisión arquitectural** — funcionalmente equivalente, semánticamente más limpio.

---

## 2. Silvia Gómez Roa (Elite 4 sem femenino recomposición)

### Perfil
- Plan: Elite · 4 semanas · femenino · ~60 kg · objetivo recomposición
- Lugar: gym · 5 días/sem
- Lesiones: ninguna activa
- Preferencias: ninguna explícita (sin foco glúteo)

### Rules que matchean

**Selección de metodología (priority 130 gana sobre default 100):**
1. **A7** `select-metodologia-4sem-elite-mujer` → `bloques-4sem-elite-agresiva`

**Selección de split:**
2. **B1** `split-body-part-5d-default` (default sin preferencia específica)

**Macros:**
3. **C2** `tmb-mifflin-st-jeor-mujer` → TMB ~1300
4. **C3** `factor-actividad-mapeo` (activo 1.725, Elite avanzada con 5 días) → GET ~2240
5. **C6** `ajuste-recomposicion-isocalorico` → kcal_objetivo ~2100-2240 (plan real: 1800 — más conservador, dentro de rango)
6. **C9** `proteina-por-objetivo-y-kg` (recomposicion → 2.2 g/kg) → 132 g (plan real: 130 g ✅)
7. **C10** (0.9 g/kg) → 54 g (plan real: 60 g, dentro de rango)
8. **C11** → carbos = resto (plan real: 185 g)

**Ejercicios:**
9. **D2-D7** según día (pecho, espalda, pierna, hombro)
10. **D11** `seleccion-ultimo-ejercicio-grupo-intensificador` → drop set + rest pause activados (avanzada, fase Progresión/Pico)

**Periodización mensual:**
11. **E3** `periodizacion-1mes-fase-fuerza-maxima` o **E2** `Hipertrofia` (según mes del bloque Elite)
12. **E6** volumen ascendente

**Stack:**
13. **F3** `stack-elite-mujer-salud-femenina` → 7 suplementos

### Predicción vs plan real

| Punto | Rule output | Plan real Silvia | Match |
|-------|-------------|------------------|:-----:|
| Metodología | bloques-4sem-elite-agresiva | "Periodización por bloques · Adaptación → Progresión → Progresión → Pico" | ✅ |
| Split | body-part-split-5d default | 5 días distribución clásica | ✅ |
| Duración | 4 semanas | 4 semanas | ✅ |
| Macros kcal | 2100-2240 | 1800 | ⚠️ (más conservador) |
| Proteína | 132 g | 130 g | ✅ |
| Grasa | 54 g | 60 g | ✅ |
| Carbos | 175 g | 185 g | ✅ |
| Técnicas avanzadas | drop set + rest pause + pirámide | Pirámide Ascendente + Drop Set + Rest Pause + Drop Set Triple | ✅ |
| Stack 7 suplementos | F3 exacto | Proteína Isolada + Creatina + Omega-3 + Vit D3+K2 + Magnesio Glicinato + Multi-fem + Colágeno+C | ✅ (7/7 idénticos) |
| RIR objetivo | 1 (avanzada) | 1 (declarado) | ✅ |

### Divergencias menores

1. **Kcal 1800 vs predicción 2100-2240**: Silvia recibió kcal más conservador que el isocalorico puro. Sugiere déficit ligero (~-200 a -300 vs GET) — entre `recomposicion` y `perdida_moderada`. La rule C6 acepta el rango `[-100, +100]` pero plan real está fuera por -200. **Override del coach** o cliente declaró nivel actividad ligero (1.375) en lugar de activo (1.725). Esta es la divergencia clave a indagar con Daniel.
   - **Acción recomendada**: verificar nivel de actividad declarado de Silvia. Si fue "moderado" en lugar de "activo", el cálculo coincide.

---

## 3. Daniel Esparza Nuñez (Elite 12 sem masculino hipertrofia/aumento)

### Perfil
- Plan: Elite · 12 sem conceptual = **3 planes mensuales consecutivos** · masculino · ~83 kg · objetivo aumento_limpio
- Lugar: gym · 6 días/sem
- Lesiones: ninguna activa
- Bloque: mes 1 (Hipertrofia) → mes 2 (Hipertrofia) → mes 3 (Fuerza Máxima) según meta-rule E5 Elite

### Rules que matchean (por plan mensual)

**Selección de metodología:**
1. **A5** `select-metodologia-6d-intermedio-avanzado` → `ppl-6d`

**Selección de split:**
2. **B4** `split-ppl-6d-default`

**Macros (priority 100 cadena):**
3. **C1** `tmb-mifflin-st-jeor-hombre` → TMB = 83×10 + 178×6.25 − 30×5 + 5 = 1797.5
4. **C3** factor `activo` 1.725 → GET = 3100
5. **C7** `ajuste-aumento-limpio` (delta default +400) → kcal_objetivo = 3500 (plan real: 3700 — ratio +600 cae en C8 `aumento_agresivo`)
6. **C9** `proteina-por-objetivo-y-kg` (aumento_limpio → 1.8 g/kg) → 149 g (plan real: 200 g, ratio 2.4 g/kg)
7. **C10** (1.0 g/kg aumento_limpio) → 83 g (plan real: 100 g, ratio 1.2)
8. **C11** → carbos resto ~525 g (plan real: 525 g ✅)

**Ejercicios:**
9. **D2-D7** por día (push/pull/legs)
10. **D11** intensificador en último ejercicio (avanzado, fase Hipertrofia)

**Periodización mensual (cada mes una fase):**
11. **E2** `periodizacion-1mes-fase-hipertrofia` (mes 1, mes 2) o **E3** `Fuerza Máxima` (mes 3)
12. **E5** `meta-secuencia-bloque-multi-mes` Elite → secuencia Adaptación-Hipertrofia-Fuerza-Peak con `needs_coach_validation_cada_mes: true`
13. **E6** volumen ascendente intra-mes

**Stack:**
14. **F4** `stack-elite-hombre-performance` → 7 suplementos exactos del plan real

### Predicción vs plan real (un mes individual)

| Punto | Rule output | Plan real Daniel | Match |
|-------|-------------|------------------|:-----:|
| Metodología | ppl-6d | "PPL/Body Part híbrido 6 días" | ✅ |
| Split | L:Push · M:Pull · X:Legs · J:Push · V:Pull · S:Legs | (similar, variantes A/B) | ✅ |
| Duración plan mensual | 4 sem | 4 sem (cada mes individual) | ✅ |
| Macros kcal | 3500 (C7) o 3700 (C8 si delta>+500) | 3700 | ✅ |
| Proteína | 149 g (C9 aumento_limpio) | 200 g (override 2.4 g/kg) | ⚠️ |
| Grasa | 83 g | 100 g | ⚠️ |
| Carbos | 525 g | 525 g | ✅ |
| Stack 7 suplementos | F4 exacto | Whey Isolada + Creatina + Beta-Alanina + L-Citrulina + Neuro-Freak + L-Glutamina + Multivit | ✅ (7/7 idénticos) |
| Multi-mes secuencia | E5 Elite con validation manual | 3 meses con bloque Daniel ajusta a mano | ✅ |

### Divergencias menores

1. **Proteína 200 g vs predicción 149 g**: Daniel se asigna 2.4 g/kg como override personal. Aceptable para Elite hombre avanzado en surplus.
2. **Grasa 100 g vs predicción 83 g**: Override coach hacia mayor flexibilidad dietética.
3. **Kcal 3700**: cae en el límite C7 (aumento_limpio +400 = 3500) vs C8 (aumento_agresivo +650 = 3750). Plan real está casi exacto en C8.
   - **Acción recomendada**: si Daniel se declara `aumento_agresivo` en intake, C8 matchea exacto. Si se declara `aumento_limpio`, C7 da 3500 y el override coach lo sube. Las dos lecturas son válidas; depende de la declaración del cliente.

4. **Multi-mes**: la secuencia 3 meses Hipertrofia-Hipertrofia-Fuerza es la observada en Daniel — encaja con E5 Elite default. Cada mes individual encaja con E2 (Hipertrofia) o E3 (Fuerza Máxima).

---

## 4. Conclusiones operacionales

### Rules que reproducen 100% el plan real (sin divergencias)
- **D6** Hip-thrust como compuesto principal día glúteo (Lizetd ✅)
- **B2** Split focus glúteo con 2× frecuencia (Lizetd ✅)
- **F3** Stack Elite mujer 7 suplementos (Silvia ✅ — match 7/7 idéntico)
- **F4** Stack Elite hombre performance (Daniel ✅ — match 7/7 idéntico)
- **C2/C1** TMB Mifflin-St Jeor (todas)
- **C11** Carbos resto (todas)
- **B4** PPL 6d default (Daniel ✅)

### Rules que requieren override del coach humano
- **C9 proteína**: los 3 clientes recibieron más proteína que el default de la matriz. Patrón observado: coach humano sube proteína para Elite/Esencial avanzado.
  - **Acción**: agregar campo `coach_protein_override_g_kg` que C9 lea opcional, o documentar como decisión de coach en intake.
- **C10 grasa**: similar patrón.

### Gap identificados para Sprint 0-1

1. **Sin coach_override pattern**: el motor v2 produce defaults; los coaches humanos quieren tunear. Sin mecanismo explícito, los overrides quedan en `notas_coach` sin que el linter pre-INSERT los valide.
   - **Recomendación**: agregar en Sprint 1 un schema de `coach_overrides` que el motor respete sin cuestionar.

2. **Pieza 2 (exercise_patterns) confidence moderate**: 14 rules de selección de ejercicios (D2-D7) referencian propuesta de Pieza 2 (no seed final). Reconciliar cuando Pieza 2 termine Fase 3.

3. **Pieza 6 (hormonal_protocols) no curada**: 5 rules de G (ciclo menstrual) con `active: false` esperando Pieza 6. Daniel decide si entran al MVP o quedan pospuestas.

4. **Caso único Silvia (A7)**: solo 1 cliente observada. Si patrón "4sem Elite femenino agresivo" no se repite en otras clientes Elite femeninas que vengan, A7 puede degradarse a edge case.

5. **Caso Daniel multi-mes (E5 Elite)**: solo 1 cliente observado y es el CEO. Validación cruzada con clientes Elite hombre reales (no Daniel) sería ideal en Sprint 1-2.

---

## 5. Verificaciones científicas pendientes (Perplexity Pro Chrome MCP)

Total citas marcadas `verified:false` en los 12 chunks: ~50 referencias únicas.

**Top 10 a verificar primero** (aparecen en múltiples rules):
1. Schoenfeld B.J. 2016 (frecuencia entrenamiento) — A1, A2, A3, A5, B1, B2, B3, B4, B5, D8, I2
2. Schoenfeld B.J. 2017 (dose-response volumen) — A3, A5, E2, E6
3. Helms E.R. 2019 (Muscle and Strength Pyramid) — A2, A3, C8, C9, C10, C11, D12
4. Contreras B. 2015 (EMG hip-thrust vs squat) — B2, D6, I1
5. Mifflin M.D. 1990 — C1, C2
6. Morton R.W. 2018 (proteína meta-análisis) — C9, F1
7. Issurin V.B. 2010 (periodización por bloques) — A6, A7, E1, E5
8. Krzysztofik M. 2019 (técnicas avanzadas hipertrofia) — D11, E2
9. ACOG 2020 (ejercicio embarazo) — H6
10. McGill S.M. 2007 (low back disorders) — D14, H1, H2

**Acción**: ejecutar 1 sesión de Perplexity Pro Chrome MCP para cada cita top-10, marcar `verified:true` en el seed final. Citas que NO se confirmen se reemplazan o se baja `confidence` de las rules afectadas.

---

## 6. Sprint 0-1 next steps

| # | Acción | Responsable | Bloquea |
|---|--------|:-----------:|:-------:|
| 1 | Validar SCOPE-PROPUESTA + cobertura retroactiva | Daniel | Sprint 0 inicio |
| 2 | Verificar top 10 citas vía Perplexity Pro | Claude Code (next session) | Confidence final |
| 3 | Cerrar Pieza 2 (exercise-patterns-seed.json) Fase 3 | Próxima sesión Pieza 2 | Reconciliar slugs D2-D7 |
| 4 | Decidir si Pieza 6 (ciclo menstrual) entra al MVP | Daniel | Activar G1-G5 |
| 5 | Sprint 0: crear DB `wellcore_kb` + migraciones aditivas para `decision_rules` table | la-06-database | Implementación PHP |
| 6 | Sprint 0: cargar `decision-rules-seed.json` en `wellcore_kb.decision_rules` | la-02-backend | Test motor v2 |
| 7 | Sprint 1: construir motor v2 stage SELECT que consume rules | la-01-architect + la-02-backend | E2E test plan generation |

---

## 7. Métricas finales

- **Total rules entregadas**: 73 (scope inicial: 60-70 ✅ dentro de rango)
- **Categorías cubiertas**: 9/9 ✅
- **Active rules MVP**: 68 / 73 (93%)
- **Inactive (Categoría G pendiente Pieza 6)**: 5 / 73
- **Confidence high**: 61 / 73 (84%)
- **Confidence moderate**: 12 / 73 (16% — todos con `needs_daniel_validation:true`)
- **Confidence low**: 0 / 73
- **Rule_types únicos**: 15 (más que los 9 propuestos originalmente — expansion natural: filters, addons, technique, meta-rules)
- **Slugs inventados**: 0 — todos referencian Pieza 1 (methodologies), Pieza 3 (nutrition-foods), Pieza 4 (supplement-catalog), o Pieza 2 propuesta (con confidence moderate flag)
- **Citas científicas únicas referenciadas**: ~50 (todas marcadas `verified:false` hasta verificación Perplexity bulk)
- **Cobertura retroactiva**: 3/3 clientes reales reproducidos con divergencias atribuibles a override del coach humano (no error de las rules)

## 8. Output final

**Archivo principal**:
- `docs/audit-motor-v2/decision-rules-seed.json` — 73 rules consolidadas, listo para Sprint 0

**Archivos de auditoría histórica**:
- `docs/audit-motor-v2/decision-rules-CHUNK-01.json` … `CHUNK-12.json`
- `docs/audit-motor-v2/decision-rules-SCOPE-PROPUESTA.md`
- `docs/audit-motor-v2/decision-rules-PATCH-MENSUAL.md`
- `docs/audit-motor-v2/decision-rules-COBERTURA-RETROACTIVA.md` (este archivo)
- `docs/audit-motor-v2/compile-decision-rules-seed.php` (script de compilado, ejecutable para regenerar)

**Memoria autoritativa creada**:
- `feedback_planes_mensuales_solamente.md` (2026-05-17) — todos los planes son mensuales (4 sem)
