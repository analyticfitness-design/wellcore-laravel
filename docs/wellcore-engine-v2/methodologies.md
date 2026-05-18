# Methodologies — Motor v2

> Auto-generado por `php artisan kb:export-methodologies-md`. NO editar a mano — editar el seeder.

Total: 8 methodologies en 5 verticales.

## Resumen por vertical

| Vertical | Count | Slugs |
|----------|-------|-------|
| entrenamiento | 3 | body_part_split_5d, ppl_6d, upper_lower_4d |
| nutricion | 2 | iifym_deficit, mediterranea_recomp |
| suplementacion | 1 | stack_basico |
| habitos | 1 | habitos_sueno_hidratacion_basico |
| ciclo | 1 | ciclo_hormonal_basico |

---

## entrenamiento

### `body_part_split_5d` — Body Part Split 5 días

- **vertical**: entrenamiento
- **target_days**: 5-5
- **target_level**: intermedio
- **target_goal**: hipertrofia
- **periodization_pattern**: {"fase":"Adaptación","weeks":1,"volumen_pct":70,"rir_objetivo":3}, {"fase":"Hipertrofia","weeks":1,"volumen_pct":100,"rir_objetivo":2}, {"fase":"Fuerza","weeks":1,"volumen_pct":90,"rir_objetivo":1}, {"fase":"Peak","weeks":1,"volumen_pct":75,"rir_objetivo":0}
- **status**: active

**Descripción:**

Split clásico por grupo muscular grande, 5 días por semana. Permite frecuencia 1× por grupo con volumen alto. Ideal para intermedio-avanzado buscando hipertrofia.

Distribución típica: Lunes Pecho+Tríceps, Martes Espalda+Bíceps, Miércoles Piernas Cuádriceps, Jueves Hombros+Brazos, Viernes Piernas Posterior+Glúteo.

Cardio post pesas opcional.

---

### `ppl_6d` — PPL (Push / Pull / Legs) 6 días

- **vertical**: entrenamiento
- **target_days**: 6-6
- **target_level**: avanzado
- **target_goal**: hipertrofia
- **periodization_pattern**: {"fase":"Adaptación","weeks":2,"volumen_pct":70,"rir_objetivo":3}, {"fase":"Hipertrofia","weeks":2,"volumen_pct":100,"rir_objetivo":2}, {"fase":"Fuerza","weeks":1,"volumen_pct":90,"rir_objetivo":1}, {"fase":"Peak","weeks":1,"volumen_pct":75,"rir_objetivo":0}
- **status**: active

**Descripción:**

División en empuje, jalón y piernas, repetido 2 veces por semana (6 días). Frecuencia 2× por patrón de movimiento. Volumen alto por grupo muscular — requiere buena recuperación.

Distribución típica: L Push A · Ma Pull A · Mi Legs A · J Push B · V Pull B · S Legs B.

Reservado a avanzados o intermedios con tiempo + buena recuperación.

---

### `upper_lower_4d` — Upper / Lower 4 días

- **vertical**: entrenamiento
- **target_days**: 4-4
- **target_level**: any
- **target_goal**: hipertrofia
- **periodization_pattern**: {"fase":"Adaptación","weeks":1,"volumen_pct":70,"rir_objetivo":3}, {"fase":"Hipertrofia","weeks":1,"volumen_pct":100,"rir_objetivo":2}, {"fase":"Fuerza","weeks":1,"volumen_pct":90,"rir_objetivo":1}, {"fase":"Peak","weeks":1,"volumen_pct":75,"rir_objetivo":0}
- **status**: active

**Descripción:**

Alternancia tren superior / tren inferior, 4 días por semana. Frecuencia 2× por grupo. Balance entre recuperación y volumen — ideal para principiantes que están subiendo de nivel y para intermedios que tienen 4 días disponibles.

Distribución típica: L Upper A · Ma Lower A · J Upper B · V Lower B.

Progresión ondulante (DUP) entre sesiones A y B.

---

## nutricion

### `iifym_deficit` — IIFYM con déficit calórico moderado

- **vertical**: nutricion
- **target_days**: -
- **target_level**: any
- **target_goal**: perdida_grasa
- **periodization_pattern**: 
- **status**: active

**Descripción:**

Flexible Dieting con déficit de 300-500 kcal sobre el GET. Proteína 1.8-2.4 g/kg (alto para preservar masa). Carbos y grasas distribuidos según preferencias y tolerancia.

Macros se calculan con Mifflin-St Jeor → GET → ajuste por objetivo. Comidas estructuradas en 5 (Desayuno, Snack AM, Almuerzo, Pre-entreno, Cena). Cada comida con 3 opciones intercambiables (A/B/C).

---

### `mediterranea_recomp` — Mediterránea para recomposición

- **vertical**: nutricion
- **target_days**: -
- **target_level**: any
- **target_goal**: recomposicion
- **periodization_pattern**: 
- **status**: active

**Descripción:**

Basada en alimentos enteros estilo mediterráneo: aceite de oliva, pescado, vegetales abundantes, granos integrales, lácteos moderados. Calorías ligeramente bajo mantenimiento o iso-calóricas según punto de partida.

Proteína 1.6-2.0 g/kg. Énfasis en saciedad y calidad cardiovascular. Buena opción para clientes con historial de dietas restrictivas que necesitan reset metabólico.

---

## suplementacion

### `stack_basico` — Stack Básico WellCore

- **vertical**: suplementacion
- **target_days**: -
- **target_level**: any
- **target_goal**: any
- **periodization_pattern**: 
- **status**: active

**Descripción:**

Stack mínimo basado en evidencia para soporte de cualquier objetivo. Compuesto por: proteína whey (30g post-entreno), creatina monohidrato (5g diario), multivitamínico (1× diario), vitamina D3 (2000-4000 IU según latitud), Omega-3 (1-2g EPA+DHA), magnesio (300-400mg antes de dormir).

No incluye ergogénicos avanzados ni quemadores. Costo mensual estimado: 80-120 USD según marca.

---

## habitos

### `habitos_sueno_hidratacion_basico` — Hábitos básicos: sueño + hidratación

- **vertical**: habitos
- **target_days**: -
- **target_level**: any
- **target_goal**: any
- **periodization_pattern**: 
- **status**: active

**Descripción:**

Pilares básicos no-negociables de rendimiento y recuperación. Sueño objetivo 7-9 horas consistentes (mismo horario fines de semana ±30 min). Hidratación mínima 35 ml/kg de peso corporal, más 500 ml por hora de entrenamiento.

Tracking diario via app WellCore. Apto para todos los niveles y todos los objetivos. Suele combinarse con plan de entrenamiento o nutrición.

---

## ciclo

### `ciclo_hormonal_basico` — Ciclo hormonal básico (Elite)

- **vertical**: ciclo
- **target_days**: -
- **target_level**: intermedio
- **target_goal**: any
- **periodization_pattern**: 
- **status**: experimental

**Descripción:**

Adaptación del entrenamiento y nutrición según fase del ciclo menstrual (folicular / ovulatoria / lútea / menstrual). Pensado para clientes Elite con tracking de ciclo activo.

Folicular: ventana de fuerza máxima, mayor volumen tolerable. Lútea: priorizar resistencia, evitar deficits calóricos severos. Suple recomendaciones específicas por fase (magnesio en lútea, hierro post-menstrual).

Requiere intake adicional: día 1 del último ciclo + duración promedio.

---

