# Exercise Patterns — Propuesta de agrupación (Fase 2 de Pieza 2)

**Fecha:** 2026-05-16
**Sesión:** Pieza 2 — Curación exercise_metadata
**Estado:** ⏸ ESPERANDO VALIDACIÓN DE DANIEL antes de pasar a Fase 3 (curación JSON)
**Fuente única de verdad:** 220 GIFs físicos en `E:\WELLCORE FITNESS PLATAFORMA\Recursos\GIF EJERCICIOS 220\`

---

## Resumen ejecutivo

- **52 movimientos patrón** propuestos (no llegamos a 60-80 sugeridos en prompt — la realidad biomecánica de los 220 GIFs no da para más sin sobre-fragmentar)
- **220 GIFs cubiertos al 100%** (excepto 3-4 casos ambiguos marcados)
- **Compuestos críticos con `needs_daniel_validation: true`** (~14 patrones): sentadilla, peso-muerto, peso-muerto-rumano, press-banca, dominada, jalon-vertical, remo-horizontal, press-militar, hip-thrust, zancada, sentadilla-unilateral, peso-muerto-unilateral, fondos-pecho, fondos-triceps

### Decisiones de diseño aplicadas (puedo revertir si no estás de acuerdo)

1. **Press de banca dividido en 2 patrones** (`press-banca` peso libre vs `press-pecho-maquina` máquina/polea) porque `stability_demand`, `level_min` y `load_potential` son distintos — al motor v2 le sirve diferenciarlos para clientes con/sin equipo libre.
2. **Aperturas + crossover unificadas** en un solo `apertura-pecho` (9 variaciones) — biomecánicamente son fly horizontal con distintas curvas de resistencia (mancuerna vs polea).
3. **Jalones (vertical pull) separados de straight-arm pulldown** porque la cinemática de codo difiere (flexion vs extension shoulder).
4. **Patadas tríceps separadas de pushdowns** — kickback es shoulder-extended elbow-extension, pushdown es shoulder-flexed.
5. **3 GIFs ambiguos** que necesitan tu clarificación:
   - `patada-lateral-en-polea` → ¿es kickback de tríceps lateral o abducción cadera?
   - `patada-trasera-en-maquina` y `patada-trasera-en-polea` → asumí glúteo (hip-extension), no tríceps. Confirmar.
   - `curl-interno-en-polea-de-pie` → asumí curl supinado clásico unilateral interno. Si es spider curl o algo distinto, decime.

---

## Cobertura por grupo muscular primario

| Grupo | Patrones | GIFs |
|---|---:|---:|
| Core / Abdomen | 5 | 22 |
| Cadera (abd/add) | 2 | 5 |
| Pecho (horizontal push + apertura) | 6 | 32 |
| Espalda (pull + extension) | 7 | 37 |
| Hombros (vertical push + isolations) | 6 | 29 |
| Bíceps / Antebrazo | 7 | 23 |
| Tríceps | 5 | 24 |
| Piernas (squat + hinge + lunge + iso) | 13 | 47 |
| Funcional / Cardio core | 1 | 1 |
| **TOTAL** | **52** | **220** |

---

## Catálogo de patrones (52)

### CORE / ABDOMEN (5 patrones — 22 GIFs)

#### 1. `abdominal-rueda` — 1 GIF — `core-stability` — intermedio
- abdominales-con-rueda-arrodillado

#### 2. `crunch` — 9 GIFs — `core-dynamic` (anti-flexion → flexion) — principiante
- bicicleta-crunch
- crunch-abdominal-en-maquina-total
- crunch-acostado-en-maquina
- crunch-codo-a-rodilla
- crunch-en-polea-arrodillado
- crunch-sentado-en-maquina
- crunches-abdominales
- crunches-sobre-pelota-de-estabilidad
- abdominales-en-banco-declinado

#### 3. `crunch-oblicuo` — 4 GIFs — `rotation` / `core-lateral` — principiante
- crunches-oblicuos-acostado
- cruzado-crunch
- giro-abdominal
- oblicuos-banco-45

#### 4. `elevacion-piernas` — 7 GIFs — `hip-flexion` + anti-extension — intermedio
- elevacion-cadera-acostado-flexionado (reverse crunch)
- elevacion-de-piernas-acostado
- elevacion-de-piernas-captain-chair
- elevacion-de-piernas-dragon-flag ⚠️ avanzado
- elevacion-de-piernas-sentado
- inclinacion-piernas-cadera-banco-inclinado
- jackknife-abdominales

#### 5. `hollow-hold` — 1 GIF — `core-stability` isométrico — intermedio
- hollow-mantenimiento

---

### CADERA LATERAL (2 patrones — 5 GIFs)

#### 6. `abduccion-cadera` — 2 GIFs — `hip-abduction` — principiante — glúteo medio
- abduccion-de-cadera-de-pie-en-maquina
- abduccion-de-cadera-sentado-en-maquina

#### 7. `aduccion-cadera` — 3 GIFs — `hip-adduction` — principiante — aductor
- aduccion-de-cadera-de-pie-en-maquina
- aduccion-de-cadera-en-polea
- aduccion-de-cadera-sentado-en-maquina

---

### PECHO (6 patrones — 32 GIFs)

#### 8. `press-banca` ⭐ — 8 GIFs — `horizontal-push` — peso libre — **needs_daniel_validation**
- press-banca-barra
- press-banca-con-barra-cerrado
- press-banca-con-mancuerna-en-banco-declinado
- press-banca-declinado-con-barra-abierto
- press-banca-inclinado-con-barra
- press-de-banca-con-mancuernas
- hammer-press-acostado-con-mancuerna
- press-pecho-con-mancuerna-agarre-cerrado-banco-inclinado

#### 9. `press-pecho-maquina` — 6 GIFs — `horizontal-push` — máquina/polea — principiante
- press-de-pecho-en-maquina
- press-de-pecho-en-maquina-declinado
- press-de-pecho-hammer-en-maquina
- press-de-pecho-inclinado-en-maquina
- press-de-pecho-interno-en-maquina
- press-de-pecho-sentado-en-polea

#### 10. `flexion-pecho` — 3 GIFs — `horizontal-push` — peso corporal — principiante
- flexiones-de-pecho-abiertas
- flexiones-de-pecho-cerradas
- flexiones-de-pecho-declinado

#### 11. `fondos-pecho` ⭐ — 2 GIFs — `horizontal-push` torso-inclinado — **needs_daniel_validation**
- fondos-de-pecho-en-maquina
- fondos-pecho

#### 12. `apertura-pecho` — 9 GIFs — `shoulder-horizontal-adduction` — aislamiento — principiante/intermedio
- apertura-de-pecho-declinado-con-mancuerna
- apertura-en-poleas-al-medio
- aperturas-de-pecho-con-mancuerna-en-banco
- aperturas-en-maquina
- aperturas-en-peck-deck
- aperturas-en-polea-de-pie
- crossover-de-pecho-polea-alta
- crossover-en-maquina
- crossover-en-polea-de-pie

#### 13. `pullover` — 4 GIFs — `shoulder-extension` — dorsal+pecho serrato — intermedio
- pull-over-con-barra
- pullover-con-mancuerna
- pullover-con-mancuerna-brazo-recto
- pullover-en-polea-con-cuerda

---

### ESPALDA (7 patrones — 37 GIFs)

#### 14. `dominada` ⭐ — 1 GIF — `vertical-pull` — peso corporal — **needs_daniel_validation**
- dominadas

#### 15. `jalon-vertical` ⭐ — 11 GIFs — `vertical-pull` — polea — **needs_daniel_validation**
- jalon-agarre-neutro-en-polea
- jalon-al-pecho-agarre-cerrado
- jalon-al-pecho-agarre-supino
- jalon-al-pecho-en-maquina
- jalon-al-pecho-en-maquina-supino
- jalon-atras-del-cuello-en-polea ⚠️ avanzado, riesgo cervical
- jalon-en-polea
- jalon-en-polea-con-agarre-v
- jalon-en-polea-dorsal
- jalon-unilateral-en-polea
- pulldown-en-polea

#### 16. `straight-arm-pulldown` — 2 GIFs — `shoulder-extension` — aislamiento dorsal — intermedio
- jalon-entre-piernas-en-polea
- jalon-lateral-en-polea-con-cuerda

#### 17. `remo-horizontal` ⭐ — 10 GIFs — `horizontal-pull` — peso libre / soportado — **needs_daniel_validation**
- remo-barra-t-en-maquina
- remo-con-barra
- remo-con-barra-en-banco-inclinado (chest-supported)
- remo-con-mancuerna-a-una-mano
- remo-con-mancuernas
- remo-con-mancuernas-sobre-banco-inclinado
- remo-inclinado-con-agarre-v
- remo-inclinado-en-polea
- remo-invertido (bodyweight)
- remo-alto-de-rodillas-en-polea

#### 18. `remo-sentado` — 7 GIFs — `horizontal-pull` — máquina/polea — principiante
- remo-en-maquina-agarre-supino
- remo-en-polea-con-banco-inclinado
- remo-en-polea-sentado
- remo-sentado-a-un-brazo
- remo-sentado-con-v-barra-sentado
- remo-sentado-en-maquina
- remo-sentado-en-polea-agarre-abierto

#### 19. `remo-al-menton` — 4 GIFs — `vertical-pull` corto — trapecio+hombro lateral — intermedio (riesgo hombro)
- remo-al-menton-con-banda
- remo-al-menton-con-barra
- remo-al-menton-con-mancuerna
- remo-al-menton-en-polea

#### 20. `extension-espalda` — 2 GIFs — `hip-extension` torso — erector espinal — principiante
- hiperextension
- extension-de-espalda-en-maquina

---

### HOMBROS (6 patrones — 29 GIFs)

#### 21. `press-militar` ⭐ — 6 GIFs — `vertical-push` — **needs_daniel_validation**
- press-arnold-con-mancuerna
- press-de-hombro-con-mancuerna
- press-de-hombro-en-maquina
- press-de-hombro-en-maquina-sentado
- press-de-hombro-en-polea
- press-militar-con-barra-de-pie

#### 22. `encogimiento-trapecio` — 3 GIFs — `shoulder-elevation` — trapecio superior
- encogimiento-barra
- encogimiento-con-mancuernas
- encogimiento-de-hombros-con-barra-detras

#### 23. `elevacion-lateral` — 6 GIFs — `shoulder-isolation-lateral` — deltoides medio
- elevacion-lateral-a-frontal-con-mancuerna
- elevacion-lateral-con-mancuerna
- elevacion-lateral-en-polea
- elevacion-lateral-mancuerna-banco-inclinado
- elevaciones-laterales-en-maquina
- elevaciones-laterales-en-polea-inclinado

#### 24. `elevacion-frontal` — 6 GIFs — `shoulder-isolation-frontal` — deltoides anterior
- elevacion-fronta-en-polea-barra (typo en archivo)
- elevacion-frontal-con-banda
- elevacion-frontal-con-barra
- elevacion-frontal-con-mancuerna
- elevacion-frontal-en-polea
- elevaciones-frontales-agarre-supino

#### 25. `face-pull` — 3 GIFs — `horizontal-pull` codos altos — posterior delt + rotadores externos
- facepull-en-polea
- polea-posterior-drive
- remo-polea-para-deltoides

#### 26. `elevacion-posterior-fly` — 5 GIFs — `shoulder-horizontal-abduction` — posterior delt
- apertura-posteriores-con-mancuerna-sentado
- apertura-posteriores-sentado-en-maquina
- elevacion-posterior-con-mancuerna
- elevaciones-posteriores-en-polea
- elevaciones-posteriores-sobre-banco-inclinado

---

### BÍCEPS / ANTEBRAZO (7 patrones — 23 GIFs)

#### 27. `curl-biceps` — 8 GIFs — `elbow-flexion` supinado — bíceps brachii
- curl-biceps-barra-ez
- curl-biceps-con-barra
- curl-biceps-con-mancuerna
- curl-biceps-en-polea
- curl-biceps-en-polea-agarre-cerrado
- curl-de-bicep-a-un-brazo-en-polea
- curl-de-biceps-alternado-en-maquina
- curl-interno-en-polea-de-pie ⚠️ confidence moderate

#### 28. `curl-incline-supinado` — 4 GIFs — `elbow-flexion` con shoulder-extension — foco cabeza larga
- curl-bicep-en-polea-acostado
- curl-biceps-con-barra-recostado-en-banco-inclinado
- curl-biceps-con-mancuerna-en-banco-inclinado
- mancuerna-inclinado-curl

#### 29. `curl-concentrado` — 1 GIF — `elbow-flexion` aislado peak
- curl-concentrado-con-mancuerna

#### 30. `curl-predicador` — 5 GIFs — `elbow-flexion` preacher — braquial + cabeza corta
- curl-predicador-con-barra
- curl-predicador-con-barra-ez
- curl-predicador-con-mancuerna
- curl-predicador-en-maquina
- curl-predicador-inverso-con-barra

#### 31. `curl-martillo` — 3 GIFs — `elbow-flexion` neutro — braquial + braquiorradial
- curl-martillo-con-mancuerna
- curl-martillo-con-mancuerna-en-banco-inclinado
- curl-martillo-en-polea-con-cuerda

#### 32. `curl-inverso` — 1 GIF — `elbow-flexion` pronado — antebrazo + braquiorradial
- curl-inverso-biceps-con-barra

#### 33. `curl-muñeca` — 1 GIF — `wrist-flexion` — antebrazo (flexor carpi)
- curl-muñeca-con-barra (único GIF con `ñ` — verificar URL encoding)

---

### TRÍCEPS (5 patrones — 24 GIFs)

#### 34. `pushdown-triceps` — 6 GIFs — `elbow-extension` shoulder-flexed (codo al lado) — todas cabezas
- empuje-de-triceps-en-polea-unilateral
- extension-de-triceps-en-maquina
- extension-de-triceps-en-polea-agarre-inverso
- extension-de-triceps-en-polea-con-cuerda
- extension-de-triceps-unilateral-en-polea
- extension-de-triceps-unilateral-en-polea-alta

#### 35. `triceps-overhead` — 6 GIFs — `elbow-extension` shoulder-flexed-180° — foco cabeza larga
- extension-de-triceps-a-un-brazo-con-mancuerna
- extension-de-triceps-con-mancuerna
- extension-de-triceps-con-mancuerna-banco-inclinado
- extension-de-triceps-con-mancuerna-en-banco-declinado
- extension-de-triceps-sobre-cabeza-con-cuerda
- extension-de-triceps-sobre-cabeza-en-polea-con-cuerda

#### 36. `kickback-triceps` — 3 GIFs — `elbow-extension` shoulder-extended — foco cabeza lateral/medial
- extension-de-triceps-cruzado-en-polea-alta
- patada-de-triceps-con-mancuerna
- patada-de-triceps-en-polea
- ⚠️ `patada-lateral-en-polea` posible 4° aquí o en kickback-gluteo (confirmar)

#### 37. `press-frances` — 5 GIFs — `elbow-extension` acostado — skull crusher / JM press
- jm-press-banca-barra-ez
- press-frances-barra-acostado
- press-frances-con-barra
- press-frances-con-barra-ez
- triceps-press ⚠️ confidence moderate (variante ambigua)

#### 38. `fondos-triceps` ⭐ — 4 GIFs — `vertical-push` torso vertical — peso corporal — **needs_daniel_validation**
- fondos-de-triceps
- fondos-de-triceps-en-maquina
- fondos-en-banco (bench dips)
- fondos-sentado-en-maquina

---

### PIERNAS (13 patrones — 47 GIFs)

#### 39. `sentadilla` ⭐ — 7 GIFs — `squat` bilateral — **needs_daniel_validation**
- sentadilla-con-barra
- sentadilla-con-barra-en-banco
- sentadilla-con-mancuernas
- sentadilla-frontal-en-landmine
- sentadilla-goblet
- sentadilla-hacka
- sentadilla-parcial-con-barra

#### 40. `sentadilla-isometrica` — 1 GIF — `squat-hold` isométrico
- sentadilla-isometrica

#### 41. `sentadilla-unilateral` ⭐ — 3 GIFs — `squat` unilateral — **needs_daniel_validation**
- sentadilla-a-una-pierna-con-barra
- sentadilla-bulgara-mancuerna
- sentadilla-stepdown

#### 42. `prensa-piernas` — 2 GIFs — `squat` máquina — knee+hip-extension
- prensa-de-piernas-cerrado
- presa-de-piernas-abierto (typo en archivo: "presa")

#### 43. `extension-cuadriceps` — 1 GIF — `knee-extension` aislada
- extension-de-piernas-en-maquina

#### 44. `curl-femoral` — 4 GIFs — `knee-flexion` aislada — isquios
- curl-femora-en-polea (typo: "femora")
- curl-femoral-acostado-en-maquina
- curl-femoral-arrodillado-en-maquina
- curl-femoral-sentado

#### 45. `peso-muerto` ⭐ — 4 GIFs — `hinge` bilateral — **needs_daniel_validation**
- peso-muerto-con-barra
- peso-muerto-con-mancuernas
- peso-muerto-sumo-con-barra
- rack-pul-barra (rack pull, hinge parcial)

#### 46. `peso-muerto-rumano` ⭐ — 4 GIFs — `hinge` RDL — isquios+glúteos — **needs_daniel_validation**
- peso-muerto-pierna-rigida-con-mancuerna
- peso-muerto-rumano-con-barra
- peso-muerto-rumano-con-mancuerna
- peso-muerto-rumano-en-landmine

#### 47. `peso-muerto-unilateral` ⭐ — 1 GIF — `hinge` unilateral — **needs_daniel_validation**
- peso-muerto-a-una-pierna-con-mancuernas

#### 48. `hip-thrust` ⭐ — 4 GIFs — `hip-extension` — glúteo mayor — **needs_daniel_validation**
- hipthrust-a-una-pierna-con-barra
- hipthrust-con-barra
- puente-de-gluteo-con-barra
- puente-de-gluteo-con-mancuerna

#### 49. `zancada` ⭐ — 8 GIFs — `lunge` — cuádriceps + glúteo — **needs_daniel_validation**
- zancada (forward básico)
- zancada-curtsy-con-mancuerna
- zancada-dinamica
- zancada-frontal-con-mancuerna
- zancada-inversa-con-mancuernas
- zancada-lateral-con-barra
- zancada-lateral-con-mancuerna
- zancada-reversa-con-barra-en-step

#### 50. `step-up` — 1 GIF — `knee-flexion` unilateral con elevación
- step-up-mancuerna

#### 51. `elevacion-talones` — 4 GIFs — `calf-raise` — gastrocnemio/soleo
- elevacion-de-talones-con-mancuerna
- elevacion-de-talones-en-maquina
- elevacion-de-talones-sentado (soleo)
- pantorrillas-en-prensa-de-pierna

#### 52. `kickback-gluteo` — 2-3 GIFs — `hip-extension` unilateral — glúteo
- patada-trasera-en-maquina
- patada-trasera-en-polea
- ⚠️ `patada-lateral-en-polea` posible aquí (kick lateral abductor) o en kickback-triceps (confirmar)

---

### FUNCIONAL / CARDIO CORE (1 patrón — 1 GIF)

#### 53. `escaladores` — 1 GIF — `cardio-explosive` / `core-dynamic` — mountain climbers
- escaladores

---

## GIFs cuestionables — necesito tu OK

| GIF | Mi propuesta | Alternativa | Por qué tengo duda |
|---|---|---|---|
| `patada-lateral-en-polea` | kickback-triceps | kickback-gluteo (abducción) | El término "patada lateral" en español puede ser ambas cosas — depende de la cadena cinética que muestra el GIF |
| `patada-trasera-en-maquina` | kickback-gluteo | kickback-triceps | "Patada trasera" típicamente glúteo, pero en máquina podría ser variante de tríceps |
| `patada-trasera-en-polea` | kickback-gluteo | kickback-triceps | Idem |
| `curl-interno-en-polea-de-pie` | curl-biceps | curl-incline-supinado o spider curl | No tengo certeza del posicionamiento exacto del cuerpo |
| `triceps-press` | press-frances | pushdown-triceps | Nombre genérico — podría ser cualquiera |

---

## GIFs ausentes vs catálogo del MD 20 (informativo, no bloquea)

El MD 20 lista 265 aliases. La carpeta `GIF EJERCICIOS 220` tiene 220. La diferencia (45 ausentes) incluye:
- **Cardio standalone** (críticos para módulo cardio): `caminadora-inclinada`, `escaladora`, `salto-cuerda`, `jumping-jack`, `bicicleta-estatica` — NO ESTÁN en los 220, motor cardio necesitará placeholder o GIF nuevo
- **Plancha**: `plancha-abdominal`, `plancha-lateral`, `plancha-de-rodillas`, `plancha-lateral-aduccion-de-cadera` — no están
- **Buenos días**: `buenos-dias-con-barra` — no está (sustituir por peso-muerto-rumano)
- **Otras planchas/cardio**: `flexion-de-piernas-sobre-pelota-de-estabilidad`, `caminata-granjeros`, `toque-de-talones-acostado`, `rotacion-de-hombro-en-polea`, etc.

**Acción:** Estos van a `exercise-patterns-MISSING-GIFS.md` cuando termine Fase 4.

---

## Preguntas para Daniel antes de Fase 3

1. **¿La división `press-banca` (peso libre) vs `press-pecho-maquina` (máquina) tiene sentido?** O preferís 1 solo patrón `press-banca` con 14 variaciones todas mezcladas.

2. **¿`fondos-pecho` vs `fondos-triceps` separados?** Biomecánicamente la diferencia es torso vertical (tríceps) vs torso inclinado (pecho). Tener 2 patrones permite al motor v2 elegir según objetivo, pero son patrones cortos.

3. **GIFs ambiguos**: ¿podés mirar los 5 que listé en la tabla y decirme la clasificación correcta?

4. **¿Falta algún `needs_daniel_validation: true`?** Mis 14 candidatos son: sentadilla, sentadilla-unilateral, peso-muerto, peso-muerto-rumano, peso-muerto-unilateral, press-banca, dominada, jalon-vertical, remo-horizontal, press-militar, hip-thrust, zancada, fondos-pecho, fondos-triceps. Si querés agregar (ej. `pullover` por riesgo hombro avanzado), decime.

5. **Voz coach en `default_tecnica_ejecucion`**: ¿quiero firmarlas con tuteo neutro "Acuéstate..." o usar tu voz personal Bucaramanga ("Te acostás..."/"Acuéstate parce...")? Memoria `feedback_voz_wellcore_no_anderson` dice que la voz es WellCore (marca, estilo), no firmar como Anderson — pero la voz dentro del texto puede tener color regional.

6. **¿Avanzo a Fase 3 (curación JSON en chunks de 5) ahora con esta agrupación, o iteramos primero?**

---

## Siguiente paso

Esperando tu OK (o cambios). Cuando me digas "avanzá", arranco la curación JSON con el primer chunk:
1. press-banca (compuesto crítico)
2. peso-muerto (compuesto crítico)
3. sentadilla (compuesto crítico)
4. dominada (compuesto crítico)
5. hip-thrust (compuesto crítico)

Los 5 primeros son todos `needs_daniel_validation: true` para que veas el patrón completo antes de seguir con aislamientos.
