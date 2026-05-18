# Decision Rules — Propuesta de scope MVP (Fase 2 de Pieza 7)

**Fecha:** 2026-05-17
**Sesión:** Pieza 7 — Curación decision_rules (motor v2)
**Estado:** ⏸ ESPERANDO VALIDACIÓN DE DANIEL antes de Fase 3 (curación JSON en chunks)
**Output esperado al final:** `docs/audit-motor-v2/decision-rules-seed.json`
**Voz:** voseo colombiano neutro amable en `name`, `rationale` y `scientific_rationale`.

---

## Resumen ejecutivo

- **63 rules propuestas** en 9 categorías (objetivo prompt: 50-70 — quedamos dentro).
- **Slugs reales disponibles**: Pieza 1 (15 metodologías) y Pieza 4 (~25 suplementos) tienen JSON seed final. Pieza 3 (nutrition-foods) tiene seed con `alternatives[]+ratio` que simplifica reglas de sustitución.
- **Slugs marcados `confidence: moderate`**: los de `exercise_patterns` referencian la **propuesta de agrupación** de Pieza 2 (no seed final), por lo cual se reconcilian cuando Daniel apruebe Pieza 2 Fase 3.
- **Cobertura del audit**: las 3 clientes reales (Lizetd, Silvia, Daniel) tienen al menos una rule que matchea su contexto principal; un override de preferencia explícito modela el caso atípico de Lizetd (foco glúteo 2× frecuencia).
- **Ciclo menstrual (categoría G)**: incluyo 5 rules con `confidence: moderate` y `needs_daniel_validation: true` porque Pieza 6 (hormonal_protocols) no se ha curado todavía. Si Daniel prefiere posponerlas, las marcamos como `active: false` en el JSON final y entran en sprint posterior.

---

## Distribución por categoría

| # | Categoría | Rules | Priority típica |
|---|-----------|------:|----------------:|
| A | Selección de metodología | 9 | 100-150 |
| B | Selección de split | 7 | 100 |
| C | Cálculo de macros | 12 | 100 |
| D | Selección de ejercicios | 14 | 100 |
| E | Periodización | 7 | 100 |
| F | Selección de stack suplementos | 7 | 100-110 |
| G | Ajustes por ciclo menstrual | 5 | 110-130 |
| H | Ajustes por lesiones | 7 | 150-180 |
| I | Overrides por preferencia | 5 | 200-220 |
| | **TOTAL** | **63** | |

Distribución de priority:
- **50-100** (defaults razonables): ~38 rules
- **100-150** (casos específicos): ~15 rules
- **150-200** (casos especiales médicos): ~7 rules
- **200+** (overrides explícitos del cliente): ~5 rules

---

## A. SELECCIÓN DE METODOLOGÍA (9 rules)

Input: `client.days_per_week + client.level + client.objective + client.duracion_semanas_solicitada`.
Output: `methodology_slug` con confidence + alternatives.

| # | slug | input → output | priority |
|---|------|---|---:|
| A1 | `select-metodologia-3d-principiante` | 3 días + principiante + cualquier objetivo → `full-body-3d` | 100 |
| A2 | `select-metodologia-4d-cualquier-nivel` | 4 días + cualquier nivel + hipertrofia/recomp → `upper-lower-4d` | 100 |
| A3 | `select-metodologia-5d-intermedio-hipertrofia` | 5 días + intermedio + hipertrofia/recomp + gym → `body-part-split-5d` | 100 |
| A4 | `select-metodologia-5d-avanzado-fuerza` | 5 días + avanzado + fuerza primario → `phat-power-hypertrophy` | 110 |
| A5 | `select-metodologia-6d-intermedio-avanzado` | 6 días + intermedio/avanzado + hipertrofia → `ppl-6d` | 100 |
| A6 | `select-metodologia-12sem-metodo-tier` | 12 sem + tier=metodo + intermedio → `bloques-12sem-metodo` | 110 |
| A7 | `select-metodologia-4sem-elite-mujer` | 4 sem + tier=elite + femenino + avanzado → `bloques-4sem-elite-agresiva` | 130 |
| A8 | `select-metodologia-cliente-vuelve-pausa-larga` | client.return_from_pause_months ≥ 3 → `entrenamiento-femenino-autoregulado` o `full-body-3d` con fase Preparación | 150 |
| A9 | `select-metodologia-recomposicion-8sem` | objetivo=recomposicion + duracion≥8sem → `recomposicion-8sem-2bloques` | 120 |

**Notas**:
- A4 usa `phat-power-hypertrophy` en lugar de `5-3-1-wendler` (el prompt sugería Wendler, pero el seed de Pieza 1 tiene PHAT, no Wendler).
- A7 modela el caso anómalo Silvia (4 sem Elite femenino agresivo).
- A8 cubre readaptación.

---

## B. SELECCIÓN DE SPLIT (7 rules)

Input: `methodology + days + focus_muscle_group(opcional)`.
Output: objeto `{dias: [{nombre, grupos_musculares, posicion}]}`.

| # | slug | input → output | priority |
|---|------|---|---:|
| B1 | `split-body-part-5d-default` | `body-part-split-5d` sin foco → L:Pecho/Tri · M:Espalda/Bi · X:Pierna Q · J:Hombro/Brazo · V:Pierna F | 100 |
| B2 | `split-body-part-5d-focus-gluteo` | `body-part-split-5d` + foco=gluteo + femenino → L:Glúteo · M:Hombro+Tri · X:Cuad+Aductor · J:Espalda+Bi · V:Glúteo+Femoral (2× glúteo) | 150 |
| B3 | `split-upper-lower-4d-default` | `upper-lower-4d` → L:Upper · M:Lower · J:Upper · V:Lower | 100 |
| B4 | `split-ppl-6d-default` | `ppl-6d` → L:Push · M:Pull · X:Legs · J:Push · V:Pull · S:Legs | 100 |
| B5 | `split-ppl-6d-focus-pierna` | `ppl-6d` + foco=pierna → variante con Legs en L y V (mejor recuperación) | 130 |
| B6 | `split-full-body-3d-compuestos-rotatorios` | `full-body-3d` → L/X/V con compuesto principal rotando (sentadilla / peso muerto / press) | 100 |
| B7 | `split-hiit-sabado-add-on` | Esencial 4-sem + femenino + objetivo=perdida_grasa + days_per_week=6 → agregar día 6 = HIIT sábado | 110 |

**Notas**:
- B2 modela el caso Lizetd directamente (2× glúteo).
- B7 modela el "6to día HIIT" de Lizetd como add-on, no como split base.

---

## C. CÁLCULO DE MACROS (12 rules)

Input: `client.peso_kg + estatura_cm + edad + sexo + objetivo + nivel_actividad`.
Output: `{kcal_total, proteina_g, carbos_g, grasas_g, hidratacion_ml}` + flag `dia_descanso_delta`.

| # | slug | input → output | priority |
|---|------|---|---:|
| C1 | `tmb-mifflin-st-jeor-hombre` | sexo=hombre → TMB = 10×kg + 6.25×cm − 5×edad + 5 | 100 |
| C2 | `tmb-mifflin-st-jeor-mujer` | sexo=mujer → TMB = 10×kg + 6.25×cm − 5×edad − 161 | 100 |
| C3 | `factor-actividad-mapeo` | nivel_actividad → factor (sedentario 1.2, ligero 1.375, moderado 1.55, activo 1.725, muy_activo 1.9) | 100 |
| C4 | `ajuste-deficit-perdida-moderada` | objetivo=perdida_moderada → GET − 300 a −500 kcal | 100 |
| C5 | `ajuste-deficit-perdida-agresiva` | objetivo=perdida_agresiva → GET − 500 a −800 kcal + flag advertencia | 110 |
| C6 | `ajuste-recomposicion-isocalorico` | objetivo=recomposicion → GET ± 100 kcal | 100 |
| C7 | `ajuste-aumento-limpio` | objetivo=aumento_limpio → GET + 300 a +500 kcal | 100 |
| C8 | `ajuste-aumento-agresivo` | objetivo=aumento_agresivo → GET + 500 a +800 kcal | 110 |
| C9 | `proteina-por-objetivo-y-kg` | matriz: perdida_agresiva 2.4g/kg · recomp 2.2g/kg · hipertrofia 1.8g/kg · mantenimiento 1.6g/kg | 100 |
| C10 | `grasa-por-deficit-vs-mantenimiento` | déficit: 0.7-0.8 g/kg; mantenimiento/aumento: 0.8-1.0 g/kg | 100 |
| C11 | `carbos-resto-calorias` | carbos_g = (kcal_objetivo − proteina_kcal − grasa_kcal) / 4 | 100 |
| C12 | `periodizacion-carbos-dia-entreno-vs-descanso` | día_entreno: +25g carbos · día_descanso: −25g carbos | 100 |

**Notas**:
- Hidratación se calcula directo (35-40 ml/kg/día + add-ons) en un campo derivado, no rule separada.

---

## D. SELECCIÓN DE EJERCICIOS (14 rules)

Input: `grupo_muscular_del_dia + level + equipment + injuries`.
Output: `{compuestos: [slugs], secundarios: [slugs], aislamientos: [slugs], orden}`.

⚠️ **Confidence moderate**: los slugs referencian la propuesta de agrupación de Pieza 2 (no seed final).

| # | slug | input → output | priority |
|---|------|---|---:|
| D1 | `estructura-dia-1compuesto-2sec-3aisl` | día con 6-10 ejercicios → 1 compuesto + 1-2 secundarios + 2-3 aislamientos + abs | 100 |
| D2 | `seleccion-pecho-gym-completo` | grupo=pecho + equipment=gym → press-banca (compuesto) + press-pecho-maquina o apertura-pecho (sec) + crossover (aisl) | 100 |
| D3 | `seleccion-espalda-gym-completo` | grupo=espalda + gym → dominada/jalon-vertical (compuesto) + remo-horizontal (sec) + straight-arm-pulldown o face-pull (aisl) | 100 |
| D4 | `seleccion-pierna-quad-dominante` | grupo=pierna_quad + gym → sentadilla (compuesto) + prensa-piernas (sec) + extension-cuadriceps (aisl) | 100 |
| D5 | `seleccion-pierna-fem-dominante` | grupo=pierna_femoral + gym → peso-muerto-rumano (compuesto) + curl-femoral (sec) + extension-espalda (aisl) | 100 |
| D6 | `seleccion-gluteo-focal-femenino` | grupo=gluteo_focal + femenino → hip-thrust (compuesto principal, NO sentadilla) + sentadilla-unilateral búlgara + abduccion-cadera + kickback-gluteo | 130 |
| D7 | `seleccion-hombro-tri-superset` | día=hombro+tri → press-militar + elevacion-lateral + elevacion-posterior-fly + pushdown-triceps + triceps-overhead | 100 |
| D8 | `seleccion-principiante-solo-level-min` | level=principiante → filtrar exercise_patterns.level_min == "principiante" | 110 |
| D9 | `seleccion-casa-solo-mancuernas` | equipment=casa_mancuernas → variations donde equipment incluye "mancuerna" Y NOT "barra_olimpica" | 110 |
| D10 | `seleccion-casa-sin-pesas` | equipment=casa_sin_pesas → variations con "peso_corporal" o "banda_elastica" priorizadas (flexion-pecho, dominada en barra puerta, zancada bodyweight) | 110 |
| D11 | `seleccion-ultimo-ejercicio-grupo-intensificador` | última posición del grupo + level≥intermedio + fase≥Hipertrofia → ejercicio con compatible_techniques incluye drop-set o rest-pause | 100 |
| D12 | `seleccion-cardio-liss-post-pesas-default` | día=entreno_pesas + cardio_required → caminadora-inclinada 30 min al final (LISS, no en exercise_patterns aún) | 100 |
| D13 | `seleccion-cardio-hiit-dia-dedicado` | día=hiit_dedicado → escaladores + sprints + jumping-jack (cardio-explosive) | 100 |
| D14 | `seleccion-abs-todos-los-dias-max-2-ejercicios` | cualquier día → 1-2 ejercicios de abs (crunch, elevacion-piernas, hollow-hold) al final | 100 |

---

## E. PERIODIZACIÓN (7 rules)

Input: `duracion_semanas + nivel + objetivo + tier`.
Output: `secuencia de fases [{semana, fase, rir_target, volumen_factor}]`.

| # | slug | input → output | priority |
|---|------|---|---:|
| E1 | `periodizacion-4sem-esencial-1-2-1` | 4 sem + tier=esencial → 1 Adaptación (RIR 3) + 2 Hipertrofia (RIR 2) + 1 Peak (RIR 0-1) | 100 |
| E2 | `periodizacion-12sem-metodo-3-3-3-3` | 12 sem + tier=metodo → 3 Adaptación + 3 Hipertrofia + 3 Fuerza Máxima + 3 Peak (lineal) | 100 |
| E3 | `periodizacion-4sem-elite-agresiva-1-2-1` | 4 sem + tier=elite + femenino → 1 Adaptación + 2 Progresión + 1 Pico (variante Silvia) | 130 |
| E4 | `periodizacion-12sem-elite-validacion-manual` | 12 sem + tier=elite → individualizado, marca `needs_daniel_validation: true` (Daniel ajusta) | 120 |
| E5 | `volumen-ascendente-5-10pct-semanal` | cualquier periodización → total_series_semana sube 5-10% por semana hasta Peak | 100 |
| E6 | `rir-descendente-por-bloque` | bloque progresivo → Adaptación 3 → Hipertrofia 2 → Fuerza 1 → Peak 0 | 100 |
| E7 | `deload-obligatorio-cada-4sem-si-≥8sem` | duracion ≥ 8 sem → insertar Deload (1 sem al 60% volumen) cada 4 sem | 100 |

---

## F. SELECCIÓN DE STACK BASE DE SUPLEMENTOS (7 rules)

Input: `tier + género + objetivo + condiciones`.
Output: `[{supplement_slug, dosis_default, timing, priority}]`.

| # | slug | input → output | priority |
|---|------|---|---:|
| F1 | `stack-esencial-base` | tier=esencial → proteina-whey-concentrada + creatina-monohidrato + multivitaminico-base + omega-3-epa-dha + vitamina-d3 | 100 |
| F2 | `stack-metodo-agrega-recovery` | tier=metodo → base esencial + magnesio-bisglicinato + (cafeina-anhidra si pre-workout deseable) | 100 |
| F3 | `stack-elite-mujer-salud-femenina` | tier=elite + femenino → proteina-whey-isolada + creatina-monohidrato + omega-3-epa-dha + vitamina-d3-k2 + magnesio-glicinato + multivitaminico-femenino + colageno-vit-c (stack Silvia) | 110 |
| F4 | `stack-elite-hombre-performance` | tier=elite + masculino + objetivo=hipertrofia/performance → whey-isolada + creatina + beta-alanina + l-citrulina-malato + neuro-freak-blend-pre-workout + l-glutamina + multivitaminico-base (stack Daniel) | 110 |
| F5 | `stack-perdida-grasa-cafeina` | objetivo=perdida_grasa → AGREGAR cafeina-anhidra 100-200mg pre-entreno (si tolerada) | 100 |
| F6 | `stack-problemas-sueno-magnesio` | flag=sueno_problema → AGREGAR magnesio-bisglicinato 200-400mg 30min pre-cama | 110 |
| F7 | `stack-mujer-flujo-abundante-hierro` | femenino + flag=flujo_menstrual_abundante → AGREGAR hierro-bisglicinato + vitamina-b12 + advertencia consulta médica | 130 |

---

## G. AJUSTES POR CICLO MENSTRUAL (5 rules)

⚠️ **Confidence moderate** — Pieza 6 (`hormonal-protocols-seed.json`) no curada todavía. Estas rules son draft basadas en literatura general (Stacy Sims, Lauren Colenso-Semple). Daniel decide si entran al MVP o se posponen.

Input: `cliente_femenina + tracking_ciclo_activo + fase_actual_ciclo`.
Output: `deltas en {volumen, intensidad, calorias, suplementos_extra, notas_coach}`.

| # | slug | input → output | priority |
|---|------|---|---:|
| G1 | `ciclo-folicular-temprana-peak-fuerza` | fase=folicular_temprana → volumen +5% · intensidad +5% · sensibilidad insulina alta · ventana óptima fuerza | 110 |
| G2 | `ciclo-ovulacion-cuidado-laxitud` | fase=ovulacion → mantener volumen + warn laxitud ligamentaria + reducir cardio explosivo | 110 |
| G3 | `ciclo-lutea-temprana-mantener` | fase=lutea_temprana → mantener volumen + foco recuperación | 110 |
| G4 | `ciclo-lutea-tardia-bajar-intensidad` | fase=lutea_tardia → intensidad −10% · foco técnica · +150 kcal · magnesio extra · accept fatiga | 120 |
| G5 | `ciclo-menstrual-volumen-bajo-opcional` | fase=menstrual → volumen −30 a −50% · opcional descanso activo · hierro extra | 110 |

---

## H. AJUSTES POR LESIONES / CONTRAINDICACIONES (7 rules)

Input: `cliente.active_injuries[]`.
Output: `{excluded_patterns: [slugs], substitutions: [{from, to, rationale}]}`.

| # | slug | input → output | priority |
|---|------|---|---:|
| H1 | `injury-lumbalgia-aguda-excluir-hinge` | lumbalgia_aguda → excluir movement_pattern=hinge · sustituir hip-thrust por puente-glúteo con banda | 180 |
| H2 | `injury-lumbalgia-cronica-modificar-hinge` | lumbalgia_cronica → permitir hinge solo con mancuernas (peso-muerto-rumano DB), excluir peso-muerto convencional | 170 |
| H3 | `injury-lesion-hombro-modificar-press` | lesion_hombro_aguda → excluir press-militar overhead · sustituir por press-pecho-maquina inclinado 30° · excluir jalón detrás del cuello | 180 |
| H4 | `injury-lesion-rodilla-modificar-squat` | lesion_rodilla → excluir sentadilla profunda · sustituir por sentadilla parcial o sentadilla-isometrica + step-up + extension-cuadriceps + curl-femoral · excluir saltos | 180 |
| H5 | `injury-lesion-codo-preferir-polea` | lesion_codo → preferir variations en polea · excluir press-frances con barra, curl-biceps barra recta | 160 |
| H6 | `injury-embarazo-2do-trimestre` | embarazo_2do_trimestre → excluir hinge + abdominales decúbito supino + saltos · permitir cardio caminadora + sentadilla goblet + press-pecho-maquina inclinado | 200 |
| H7 | `injury-hipertension-no-controlada-evitar-valsalva` | hipertension_no_controlada → excluir maniobras Valsalva (peso muerto pesado, sentadilla pesada) · preferir series moderadas RIR 2-3 | 200 |

---

## I. OVERRIDES POR PREFERENCIA EXPLÍCITA (5 rules)

Priority 200+ — sobreescriben defaults. Mecanismo "el cliente sabe lo que quiere".

| # | slug | input → output | priority |
|---|------|---|---:|
| I1 | `override-priorizar-gluteo-2-dias` | client.preferences includes "priorizar_gluteo" → forzar split B2 (`split-body-part-5d-focus-gluteo`) sobre B1 (caso Lizetd directo) | 220 |
| I2 | `override-tiempo-minimo-full-body` | client.preferences includes "tiempo_minimo" → forzar `full-body-3d` incluso con 5+ días disponibles | 200 |
| I3 | `override-vegetariano-excluir-carne` | client.dietary_flags=vegetariano → excluir nutrition_foods con `is_vegetarian: false` (carnes, pollo, pescado) · usar `alternatives[]` con ratio | 210 |
| I4 | `override-sin-lacteos` | client.dietary_flags=sin_lacteos → excluir foods con category=lacteo · sustituir whey-concentrada → proteina-vegetal-blend | 210 |
| I5 | `override-intolerancia-cafeina` | client.intolerances includes "cafeina" → excluir cafeina-anhidra · pre-workout sin cafeína (l-citrulina-malato + l-tirosina) | 210 |

---

## COBERTURA RETROACTIVA — clientes del audit

Pre-flight check: ¿las rules propuestas reproducirían los planes reales observados?

### Lizetd Tatiana (Esencial 4 sem, femenino, foco glúteo, 6 días)

Rules que matchean en orden de evaluación:
1. **I1** `override-priorizar-gluteo-2-dias` (priority 220) ← gana sobre A3
2. **A3** `select-metodologia-5d-intermedio-hipertrofia` → `body-part-split-5d` (con foco override de I1)
3. **B2** `split-body-part-5d-focus-gluteo` (priority 150) ← gana sobre B1
4. **B7** `split-hiit-sabado-add-on` → agrega día 6 HIIT
5. **C2 + C3 + C4 + C9 + C10 + C11 + C12** macros (~1650 kcal, déficit moderado mujer)
6. **D6** `seleccion-gluteo-focal-femenino` (hip-thrust como compuesto)
7. **E1** `periodizacion-4sem-esencial-1-2-1`
8. **F1 + F5** `stack-esencial-base + stack-perdida-grasa-cafeina`

**Predicción vs plan real**:
- ✅ Split 5d + foco glúteo: COINCIDE
- ✅ HIIT sábado: COINCIDE
- ✅ Periodización 4 sem: COINCIDE
- ✅ Stack 7 suplementos: ~COINCIDE (plan real tiene whey CONCENTRADA, magnesio, omega-3, multi, vit-d3, creatina, cafeína = stack-esencial-base + stack-perdida-grasa)
- ⚠️ Macros: plan real 1650 kcal / 150P / 125C / 60G — habría que verificar el factor actividad asumido y el delta exacto

### Silvia Gomez Roa (Elite 4 sem, femenino avanzado, 5 días, recomposición)

Rules que matchean:
1. **A7** `select-metodologia-4sem-elite-mujer` (priority 130) ← gana sobre A3
2. **B1** `split-body-part-5d-default` (default sin foco)
3. **C2 + C3 + C6 + C9** macros (recomposición ~1800 kcal)
4. **D11** `seleccion-ultimo-ejercicio-grupo-intensificador` (drop set, rest-pause)
5. **E3** `periodizacion-4sem-elite-agresiva-1-2-1`
6. **F3** `stack-elite-mujer-salud-femenina`

**Predicción vs plan real**: ✅ COINCIDE en split, periodización, stack. Macros (1800/130P/185C/60G) compatible con recomposición moderada.

### Daniel Esparza Nuñez (Elite 12 sem, masculino, 6 días, hipertrofia alto volumen)

Rules que matchean:
1. **A6** `select-metodologia-12sem-metodo-tier` no aplica directo (es elite, no metodo) → cae a **A5** `select-metodologia-6d-intermedio-avanzado` → `ppl-6d`
2. **B4** `split-ppl-6d-default`
3. **C1 + C3 + C8 + C9** macros (~3700 kcal aumento)
4. **E4** `periodizacion-12sem-elite-validacion-manual` (Daniel ajusta a mano)
5. **F4** `stack-elite-hombre-performance`

**Predicción vs plan real**: ✅ COINCIDE en stack (whey-isolada, creatina, beta-alanina, citrulina, neuro-freak, glutamina, multi). E4 marca validación manual, lo que respeta la realidad de que Daniel se programa solo.

**Gap**: A6 dice `metodo`, pero Daniel es `elite`. Falta una rule específica para Elite hombre 12 sem hipertrofia — propongo agregar **A10** si lo aprobás:
- `select-metodologia-12sem-elite-hombre-hipertrofia` → `ppl-6d` o `phat-power-hypertrophy` según preferencia. Priority 130.

---

## PREGUNTAS PARA DANIEL antes de Fase 3

1. **¿63 rules es el scope correcto?** El prompt sugiere 50-70. Si querés bajar, las categorías más fáciles de podar son G (ciclo menstrual, 5) y I (overrides, 5).

2. **Categoría G (ciclo menstrual)**: ¿entra al MVP o lo posponemos al sprint que toque Pieza 6 (hormonal_protocols)? Si entra ahora, marco las 5 rules como `confidence: moderate + needs_daniel_validation: true`.

3. **Caso Silvia (A7)**: ¿la variante "4 sem Elite femenino agresivo" es un patrón repetible (otras mujeres Elite van a tener esto) o fue ad-hoc para Silvia? Si es ad-hoc, A7 sale y queda solo A6 + E4 con override manual.

4. **A10 (Elite hombre 12 sem)**: ¿agrego la rule específica o cae en A5 (`ppl-6d` genérico) + E4 (validación manual)? Mi recomendación: agregar A10 con priority 130 → quedarían **64 rules** en total.

5. **Slugs `confidence: moderate` por Pieza 2**: las 14 rules de categoría D referencian patrones de la propuesta de agrupación (no seed final). Cuando Pieza 2 termine Fase 3, reconcilio. ¿OK con eso o preferís esperar Pieza 2 antes de arrancar Pieza 7 Fase 3?

6. **Scientific rationale via Perplexity Pro Chrome MCP**: para los chunks de Fase 3 voy a buscar citas reales (Schoenfeld 2017, Helms 2019, Stacy Sims, etc.) con Perplexity vía MCP — no inventar refs. ¿OK?

7. **Ajustes a las priorities propuestas**: ¿alguna rule debería tener priority distinto? (ej. ¿H6 embarazo debería estar más arriba que 200?)

---

## Próximo paso

Cuando aprueben (o ajusten) este scope, arranco Fase 3 con el primer chunk:

**Chunk 1 — Categoría A (9 rules de selección de metodología)**: schema completo con `input_conditions`, `output.primary + alternatives`, `scientific_rationale + sources` (via Perplexity Pro Chrome MCP), `observed_in_real_clients`.

Después: Chunks 2-12 siguiendo el orden A → B → C → D → E → F → G → H → I.

Total estimado: 10-12 turnos de validación + compilado final + reporte de cobertura retroactiva.
