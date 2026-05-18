# Catálogo de Principles motor v2

> Generado automáticamente: `2026-05-17T21:19:46-05:00`  
> Total principles: **28**  
> Fuente: `wellcore_kb.principles`  
> Comando: `php artisan kb:export-principles-md`

---

Estos principles son inyectados automáticamente por el motor v2 en `notas_coach` y `tips[]` de los planes generados. Cada plan recibe los 3 principles más relevantes según vertical + tags del cliente (level, goal, condiciones especiales).

Scoring: vertical match +20, tag overlap +5 cada, fundamental +3.

---

## Índice por vertical

- [**entrenamiento** (7)](#vertical-entrenamiento)
- [**nutricion** (7)](#vertical-nutricion)
- [**suplementacion** (5)](#vertical-suplementacion)
- [**habitos** (6)](#vertical-habitos)
- [**ciclo** (3)](#vertical-ciclo)

---

## Vertical: `entrenamiento` (7 principles)
<a name="vertical-entrenamiento"></a>

### `sobrecarga_progresiva` — Sobrecarga progresiva

**Tags**: `hipertrofia, fuerza, fundamental`

**Resumen**: Aumentar progresivamente la demanda (peso, reps, tempo, densidad) para forzar adaptación.

**Descripción completa**:

La sobrecarga progresiva es el motor de cualquier ganancia muscular o de fuerza. No basta con entrenar duro — hay que entrenar progresivamente más duro semana a semana.

Variables progresables (en orden de prioridad para hipertrofia): 1) número de series por grupo muscular, 2) reps por serie, 3) carga, 4) tiempo bajo tensión, 5) densidad (reducir descanso).

No todas las semanas se progresa todo. Lo común es subir 1 variable a la vez y consolidar.

**Cuándo aplicar**:

En todos los planes de entrenamiento. Especialmente importante mencionar en semana 2 cuando el cliente empieza a calibrar y entender que el plan crece con él.

**Ejemplo de uso**:

> Notas del coach semana 2: "Si la semana 1 completaste todas las series con la técnica perfecta, esta semana subí 2.5-5kg en los compuestos. Si te quedaste corto, repetí pesos pero ajustá la técnica primero."

---

### `tecnica_primero` — Técnica antes que carga

**Tags**: `fundamental, principiante, prevencion_lesiones`

**Resumen**: Ningún peso vale más que la articulación. Si la técnica se rompe, bajar peso o terminar la serie.

**Descripción completa**:

La técnica precede a la carga. Levantar mucho con técnica mala acumula daño articular invisible que aparece años después (hombro, lumbar, rodilla).

Reglas operativas: 1) calentamiento específico con barra vacía o pesos muy bajos para repasar patrón, 2) si en una serie de trabajo la última rep pierde rango o forma, NO contar reps adicionales, 3) ante duda de técnica, grabar video y revisar.

**Cuándo aplicar**:

Principiantes siempre. Intermedios cuando empiezan ejercicio nuevo. Avanzados en deload o post-lesión.

**Ejemplo de uso**:

> Notas del coach: "El primer mes no me interesa cuánto cargás. Me interesa que la sentadilla baje hasta romper paralela con la espalda en neutro. El peso viene después."

---

### `variacion_estimulos` — Variación de estímulos sin caos

**Tags**: `hipertrofia, intermedio, avanzado`

**Resumen**: Rotar metodologías (series rectas, drop, superset) cada 4-6 semanas para evitar estancamiento sin perder progresión.

**Descripción completa**:

El músculo se adapta al estímulo repetido. Después de 4-6 semanas con la misma metodología, las ganancias se reducen.

Variar NO significa cambiar de plan cada semana. Significa rotar técnicas avanzadas (drop sets, supersets, pirámides) en los ejercicios accesorios, manteniendo los compuestos principales con series rectas y progresión lineal.

**Cuándo aplicar**:

Intermedios+ después de 4 semanas en el mismo bloque. Para principiantes NO aplica — ellos progresan con series rectas largas (3-6 meses).

**Ejemplo de uso**:

> Bloque 2 semana 1: "Esta semana introducimos un drop set al final del press de banca. Es la única técnica avanzada del día — no la metas en los demás ejercicios."

---

### `recuperacion_es_entrenamiento` — La recuperación es parte del entrenamiento

**Tags**: `fundamental, recuperacion, prevencion_lesiones`

**Resumen**: El músculo crece entre sesiones, no durante. Sueño + nutrición + descanso entre series son tan importantes como el ejercicio.

**Descripción completa**:

El estímulo de entrenamiento daña fibras musculares. La adaptación (hipertrofia, fuerza) ocurre en la recuperación. Si no se recupera, no se progresa.

Factores de recuperación: 1) sueño 7-9h consistentes, 2) proteína distribuida a lo largo del día, 3) descanso entre series suficiente para mantener carga (90-180s compuestos), 4) descanso entre sesiones del mismo grupo (48-72h).

**Cuándo aplicar**:

Cuando el cliente reporta fatiga acumulada, dolor articular persistente, o pérdida de fuerza inesperada. Recomendar deload antes que insistir.

**Ejemplo de uso**:

> Si el cliente dice "estoy cansado pero quiero entrenar igual": "Te entiendo. Hagamos esto: bajá 30% el volumen hoy. Si mañana te sentís bien, retomamos. Forzar sobre fatiga te lleva a lesión."

---

### `consistencia_sobre_intensidad` — Consistencia gana a intensidad

**Tags**: `fundamental, adherencia, realismo`

**Resumen**: 4 entrenos por semana durante 12 semanas vencen a 7 entrenos perfectos durante 3 semanas + 9 semanas perdidas.

**Descripción completa**:

La adaptación muscular y de fuerza requiere ESTÍMULO REPETIDO en el tiempo. Un cliente que entrena 4 días consistentes durante 6 meses obtiene mejores resultados que uno que entrena 6 días extremos durante 3 semanas y se quema.

Mejor adaptar el plan a la vida real del cliente que pretender que la vida se adapte al plan ideal.

**Cuándo aplicar**:

En la primera sesión / intake. Cuando un cliente con vida ocupada pide planes muy ambiciosos. Cuando reaparece tras una pausa.

**Ejemplo de uso**:

> En notas_coach iniciales: "El plan está diseñado para 4 días — sostener 4 días durante 12 semanas seguidas vale más que cualquier intento de hacer 6 días los primeros 15 y desaparecer."

---

### `deload_planificado` — Deload planificado cada 4-6 semanas

**Tags**: `intermedio, avanzado, recuperacion`

**Resumen**: Semana ligera con -30% volumen y -70% intensidad cada 4-6 semanas previene overtraining y consolida ganancias.

**Descripción completa**:

El deload no es debilidad ni vagancia — es necesidad biológica. Cada 4-6 semanas de carga progresiva, una semana de reducción permite que el sistema nervioso, articulaciones y tejido conectivo se actualicen al nuevo nivel.

Protocolo estándar: mantener los ejercicios y la frecuencia, reducir volumen a 60-70% y carga a 70-80% del trabajo. Sensación: salir del gym 'queriendo entrenar más'.

**Cuándo aplicar**:

En todo bloque de 4+ semanas. Especialmente importante mencionarlo en el plan antes de la semana 4 para que el cliente no piense que es regresión.

**Ejemplo de uso**:

> Notas semana 4 (peak): "La próxima semana es DELOAD — vas a entrenar igual, pero con menos peso y menos series. No es vagancia, es ciencia. Salí del gym con energía. Después arrancamos bloque nuevo."

---

### `carga_progresiva_post_lesion` — Carga progresiva post-lesión

**Tags**: `lesion, rehabilitacion, prevencion`

**Resumen**: Volver del 0%. Empezar con 40-50% del peso anterior y subir 5-10% por semana solo si no hay dolor.

**Descripción completa**:

Post-lesión (incluso lesiones menores como tendinitis o esguinces grado 1), el músculo se atrofia y la propiocepción se altera. Volver con el peso de antes garantiza recaída.

Protocolo: 1) confirmar alta médica si hubo lesión moderada+, 2) primera semana 40-50% del peso pre-lesión, 3) +5-10% semanal solo si NO hay dolor durante ni después, 4) si reaparece dolor, parar y bajar.

**Cuándo aplicar**:

Intake del cliente menciona lesión activa o reciente (<6 meses). Sustituir ejercicios contraindicados por alternativas.

**Ejemplo de uso**:

> Notas del ejercicio donde aplica: "Por el hombro que te molestaba, en lugar de press militar parado vamos con press sentado en máquina. Arrancamos en 60% de tu peso usual. Subimos cuando lleves 2 semanas sin molestia."

---

---

## Vertical: `nutricion` (7 principles)
<a name="vertical-nutricion"></a>

### `proteina_primero` — Proteína primero

**Tags**: `fundamental, macros`

**Resumen**: Si no llegás a las calorías del día pero cumpliste tu proteína, el día sigue siendo productivo.

**Descripción completa**:

La proteína es el macronutriente más crítico para preservar (y construir) músculo. Distribución típica: 1.8-2.4 g/kg de peso corporal para personas entrenando con peso.

Prioridad operativa: si el día se complicó y solo podés controlar UNA cosa, asegurate de cumplir tu objetivo de proteína. Los carbos y grasas tienen más flexibilidad.

**Cuándo aplicar**:

En todo plan nutricional. Especialmente importante para clientes en déficit (perdida_grasa) o que vienen de dietas muy bajas en proteína.

**Ejemplo de uso**:

> tips_nutricionales: "Si te quedaste corto de calorías pero cumpliste tus 180g de proteína, el día sigue contando. La proteína es la única no-negociable."

---

### `distribucion_de_proteina` — Distribución de proteína en 4-5 comidas

**Tags**: `macros, timing`

**Resumen**: Distribuir la proteína en 4-5 comidas con 30-40g cada una maximiza síntesis proteica vs concentrarla en 1-2 comidas grandes.

**Descripción completa**:

La síntesis proteica muscular tiene un techo por comida (~0.4 g/kg). Comer 100g de proteína en 1 comida no es 2.5× más anabólico que 40g — el excedente se metaboliza para energía o se almacena.

Distribución óptima: 4-5 comidas con 30-45g de proteína cada una, espaciadas 3-4h. Incluye fuentes variadas: animal completa (huevo, pollo, pescado, lácteos) + vegetal complementaria.

**Cuándo aplicar**:

Al armar el meal plan. Si el cliente prefiere ayuno intermitente, ajustar a 3 comidas más grandes pero no menos.

**Ejemplo de uso**:

> Cada comida del plan tiene su objetivo de proteína específico (ej. desayuno 30g, almuerzo 40g, pre-entreno 25g, cena 40g, snack PM 25g = 160g).

---

### `hidratacion_minima` — Hidratación mínima 35 ml/kg/día

**Tags**: `fundamental, hidratacion`

**Resumen**: Mínimo 35 ml de agua por kg de peso corporal al día, +500 ml por hora de entrenamiento intenso.

**Descripción completa**:

La deshidratación leve (-2% del peso corporal en líquidos) reduce fuerza, resistencia y enfoque cognitivo significativamente. La sed NO es indicador temprano — cuando sentís sed ya estás deshidratado.

Cálculo: peso × 0.035 = L base diarios. Más 0.5L por cada hora de entrenamiento. En climas cálidos o altitud, sumar 20-30%.

**Cuándo aplicar**:

En todo plan nutricional. Especialmente para clientes en Colombia/LATAM costeros (clima cálido) o ciudades de altura (Bogotá +2600m).

**Ejemplo de uso**:

> Sección hidratación del JSON: "Tu mínimo diario son 3.2 L de agua (90kg × 35ml). Los días que entrenás, sumá 500ml extra durante la sesión y otros 500ml en la hora siguiente."

---

### `timing_pre_entreno` — Pre-entreno 60-90 min antes

**Tags**: `timing, rendimiento`

**Resumen**: Comida pre-entreno con carbo + proteína 60-90 min antes da combustible sin pesadez digestiva.

**Descripción completa**:

El pre-entreno ideal es una comida moderada (300-500 kcal) con 30-50g de carbo y 20-30g de proteína, consumida 60-90 min antes del gym. Da glucógeno disponible y aminoácidos circulantes sin causar pesadez.

Ejemplos: arroz + pollo + verduras, avena + claras + banano, batata + atún. Evitar grasas altas y fibra excesiva (retrasan digestión).

**Cuándo aplicar**:

Cuando el cliente entrena en horario fijo. Si entrena en ayunas, ajustar (BCAAs o proteína líquida) y mover macros a la primera comida post.

**Ejemplo de uso**:

> Si el cliente entrena 18:00, pre-entreno es 16:30-17:00. Cena fuerte después del entreno (19:30-20:00) con proteína + carbos para recuperación.

---

### `post_entreno_proteina_carbo` — Post-entreno: proteína + carbo dentro de 60 min

**Tags**: `timing, macros, recuperacion`

**Resumen**: La ventana anabólica existe pero es ancha (~3h). Lo importante es reponer proteína + carbo dentro de la primera hora post-entreno.

**Descripción completa**:

La ventana anabólica tradicional ('30 min después del entreno') está sobre-vendida — la ventana real es ~3 horas. Pero reponer dentro de la primera hora optimiza:

- Síntesis proteica muscular: 25-40g proteína de alta calidad
- Reposición de glucógeno: 0.5-1.0 g/kg de carbo en la primera hora

Ejemplos: batido whey + banano, yogur griego + miel + granola, sandwich de pollo con pan integral.

Si el entreno termina cerca de una comida normal (cena, almuerzo), esa comida cumple ese rol — no necesitás algo extra.

**Cuándo aplicar**:

Cuando el cliente reporta entrenar y tardar 2-3h en comer después. Especialmente en hipertrofia y recomposición.

**Ejemplo de uso**:

> Notas: 'Después del gym (18hs), tu cena (19hs-20hs) ya cuenta como post-entreno. No necesitás batido extra — la cena con proteína + carbo es suficiente.'

---

### `vegetariano_completar_aminoacidos` — Vegetariano: combinar fuentes para perfil completo

**Tags**: `macros, vegetariano, adherencia`

**Resumen**: Las proteínas vegetales son completas si combinás 2 fuentes. Arroz+lenteja, garbanzo+quinoa, trigo+soja.

**Descripción completa**:

Cada proteína vegetal individual es 'incompleta' (faltan 1-2 aminoácidos esenciales), pero combinando 2 fuentes el perfil se completa. Las combinaciones clásicas LATAM:

- Arroz + lentejas (clásico colombiano)
- Garbanzo + quinoa
- Frijoles + tortilla de maíz
- Tofu + arroz integral

NO hace falta combinarlas en la MISMA comida — basta con consumirlas a lo largo del día (el pool de aminoácidos del cuerpo se mantiene varias horas).

Veganos estrictos: considerar suplementación con B12, hierro y omega-3 (DHA algal).

**Cuándo aplicar**:

Cliente vegetariano o vegano. Especialmente si entrena con foco hipertrofia.

**Ejemplo de uso**:

> Notas: 'Como sos vegetariana, combiná arroz+lenteja en almuerzo y quinoa+garbanzo en cena. NO necesitás que cada comida tenga combo — el cuerpo lo arma a lo largo del día.'

---

### `frecuencia_comidas_flexible` — 5 comidas no es ley — 3, 4, 5 o 6 son válidas

**Tags**: `adherencia, realismo, macros`

**Resumen**: El estudio clave (Schoenfeld 2015) confirma: 3-6 comidas dan igual resultado SI los macros totales se cumplen.

**Descripción completa**:

El mito del metabolismo acelerado por 5-6 comidas pequeñas está derribado por la literatura (Schoenfeld & Aragon 2018). Lo que importa:

- TOTAL diario de calorías + macros (esto manda)
- Mínimo 3 comidas con ~30g de proteína cada una (saturar síntesis proteica)
- Adherencia a la vida real del cliente

Clientes que trabajan oficina: 3-4 comidas suelen funcionar mejor (menos preparación). Atletas serios: 5-6 si toleran. Ayuno intermitente 16/8: 2-3 comidas dentro de la ventana.

NO hay 'correcta' — solo la que el cliente puede SOSTENER.

**Cuándo aplicar**:

Cuando el cliente reporta no poder cumplir 5 comidas/día por vida ocupada. O cuando pregunta si tiene que comer cada 3 horas (no, no tiene).

**Ejemplo de uso**:

> Notas: 'Si 5 comidas no te encajan, hacé 3 con porciones más grandes. El total de proteína/carbos/grasas es lo que importa — no el número de meals.'

---

---

## Vertical: `suplementacion` (5 principles)
<a name="vertical-suplementacion"></a>

### `creatina_basal` — Creatina monohidrato 5g/día (basal)

**Tags**: `basal, evidencia_alta, fundamental`

**Resumen**: El suplemento más estudiado y efectivo que existe. 5g diarios constantes — sin fases de carga ni descansos.

**Descripción completa**:

Creatina monohidrato (no HCl, no Kre-Alkalyn — el monohidrato es el que tiene 100+ estudios) aumenta fuerza ~5-10% y volumen muscular ~1-2 kg en intermedios+ tras 4-8 semanas.

Protocolo simple: 5g todos los días (con o sin entreno, con o sin comida). Las fases de carga (20g × 5 días) son innecesarias — el efecto es el mismo al mes 1 con 5g/día. No requiere descansos cíclicos.

**Cuándo aplicar**:

Todo plan de suplementación. Excepción: clientes con problemas renales preexistentes (consultar médico).

**Ejemplo de uso**:

> Lista de suplementos del plan: "Creatina Monohidrato 5g — Cualquier momento del día, todos los días incluyendo descansos. Suplemento basal, no opcional."

---

### `whey_no_es_obligatoria` — Whey es opcional, no obligatoria

**Tags**: `macros, realismo, adherencia`

**Resumen**: Whey ayuda si no llegás a tu target proteico con alimentos enteros. Si llegás, podés ahorrarte la plata.

**Descripción completa**:

La whey es UNA forma cómoda de cubrir gaps de proteína — no un macronutriente mágico. Cliente que llega tranquilamente a 2g/kg con pollo+huevos+lácteos NO necesita whey.

Useful cuando: cliente con vida ocupada que no come bien al mediodía, post-entreno cuando no hay tiempo de cocinar, viaje. NO útil: marketing que dice 'whey = más músculo'.

**Cuándo aplicar**:

Considerar whey solo si el cliente reporta no llegar a target proteico con alimentos. Si llega, sugerir invertir en mejor calidad de alimentos enteros.

**Ejemplo de uso**:

> Notas del coach: "Whey en este stack es opcional. Si llegás a tus 150g de proteína con comida real, no es necesaria. La metí para casos en que tu agenda no permita 5 comidas."

---

### `cafeina_timing_no_dosis` — Cafeína: timing importa más que dosis

**Tags**: `timing, rendimiento`

**Resumen**: Cafeína 30-60 min pre-entreno mejora performance 3-7%. Más dosis NO mejora resultado — y empeora sueño.

**Descripción completa**:

Dosis efectiva: 3-6 mg/kg, 30-60 min antes del entreno. Por encima de 6 mg/kg los efectos secundarios (ansiedad, taquicardia, mal sueño) anulan el beneficio.

Regla: NO consumir cafeína 8h antes de dormir (vida media ~5h en adultos). Si entrenás 18hs, café 17:00 es OK. Si entrenás 20hs, mejor té verde o sin cafeína post-17:00.

Tolerancia: ciclar 2-3 semanas + 1 semana sin para mantener sensibilidad.

**Cuándo aplicar**:

Clientes que ya consumen café/té y reportan necesitar dosis altas. O quienes entrenan tarde y reportan mal sueño.

**Ejemplo de uso**:

> Notas: "Si tu entreno es a las 18hs, tomá café o pre-workout entre 17:00-17:30. NADA de cafeína después de las 17:00 si te cuesta dormir. La dosis óptima no es la máxima — es la mínima que rinde."

---

### `no_apilar_estimulantes` — Nunca apilar 2 estimulantes el mismo día

**Tags**: `fundamental, prevencion, seguridad`

**Resumen**: Pre-workout + café + termogénico = riesgo cardiovascular real (taquicardia, presión alta, ansiedad). Elegir UNO.

**Descripción completa**:

Cliente típico: toma 1 café por la mañana + pre-workout 17hs + termogénico al mediodía. Total: 400+ mg cafeína + sinefrina + yohimbina = arritmia + presión arterial peligrosa.

Regla operativa: si el cliente toma pre-workout, NO suplementar termogénico. Si toma 2-3 cafés al día, NO agregar pre-workout con cafeína (usar versión sin caf).

Señales de alarma: temblor en manos, palpitaciones, dolor de cabeza vespertino, insomnio inexplicado.

**Cuándo aplicar**:

Cliente reporta tomar pre-workout + café (varios) + termogénico simultáneamente. Educar sobre suma de estimulantes y posibles efectos cardio.

**Ejemplo de uso**:

> Notas del coach: "Si tomás este pre-workout, NO sumes termogénico ni más de 1 café al día. El total de cafeína diaria no debe superar 400mg para vos."

---

### `suplemento_completa_no_sustituye` — Los suplementos complementan, NO sustituyen

**Tags**: `fundamental, adherencia, realismo`

**Resumen**: Ningún suplemento compensa mal sueño, dieta basura o entrenamiento mal hecho. Son la cereza, no el pastel.

**Descripción completa**:

Marketing fitness vende suplementos como atajos — la realidad es que el 90% del resultado viene de los pilares básicos: entrenamiento progresivo, dieta consistente, sueño adecuado.

Un cliente con stack premium ($300 USD/mes) pero que duerme 5hs y come fast food NO va a progresar. Un cliente sin nada de suplementos pero con los pilares cumplidos va a progresar.

Orden de prioridad: 1) sueño, 2) entreno, 3) comida, 4) hidratación, 5) suplementos (último 10% optimización).

**Cuándo aplicar**:

Cuando el cliente pregunta '¿qué suplemento debería tomar?' antes de tener fundamentos sólidos. Educar antes de vender.

**Ejemplo de uso**:

> Notas del coach: "Antes de gastar en suplementos avanzados, revisemos tu sueño y tu adherencia al plan nutricional. Si esos 2 están sólidos, los suplementos rinden mucho más."

---

---

## Vertical: `habitos` (6 principles)
<a name="vertical-habitos"></a>

### `sueno_es_anabolico` — Sueño es anabólico

**Tags**: `fundamental, recuperacion`

**Resumen**: Dormir 7-9h consistentes vale más que cualquier suplemento. La GH y la recuperación muscular pico ocurren en sueño profundo.

**Descripción completa**:

Las horas de sueño son cuando ocurre la mayoría del trabajo de recuperación muscular: pico de hormona de crecimiento (GH), reparación de fibras, consolidación neurológica del patrón motor entrenado.

Dormir 5-6h consistentes anula la mitad de las ganancias de entrenamiento — está documentado en múltiples estudios. Prioridad: horario fijo (mismo ±30 min entre semana y fin de semana), oscuridad total, temperatura fresca, sin pantallas 1h antes.

**Cuándo aplicar**:

En todo plan, especialmente cuando el cliente reporta progreso lento sin causa clara o fatiga sostenida.

**Ejemplo de uso**:

> Pilar del plan de hábitos: "Sueño es no-negociable. 7.5h promedio semanal. Si trabajás de noche, hablalo con el coach y armamos un plan ajustado."

---

### `registro_es_clave` — Registro es clave (anotás o no progresás)

**Tags**: `fundamental, progresion`

**Resumen**: Anotar peso, reps y RIR de cada serie es lo que permite progresión consciente vs entrenar de memoria.

**Descripción completa**:

Sin registro, no hay sobrecarga progresiva real — solo recuerdo selectivo de la última sesión. El cliente que anota peso, reps y RIR sabe exactamente cuándo subir carga, cuándo está estancado, y cuándo necesita deload.

Métodos: app WellCore (preferido), libreta, notas en celular. Lo importante es CONSISTENCIA. Registrar 80% del tiempo es 100× mejor que registrar 100% del primer mes y nada después.

**Cuándo aplicar**:

En la primera sesión / intake. Si el cliente entrena hace tiempo pero no registra, mencionar como cambio importante.

**Ejemplo de uso**:

> Tip del plan: "Anotá peso, reps y RIR de cada serie en la app WellCore después de cada ejercicio. No al final del día — en el momento."

---

### `adherencia_sobre_perfeccion` — Adherencia 80% sostenida > perfección 100% efímera

**Tags**: `fundamental, adherencia, realismo`

**Resumen**: Cumplir el plan al 80% durante 12 semanas vence a cumplirlo al 100% durante 2 y abandonar.

**Descripción completa**:

El error clásico: plan perfecto en papel, ejecutado al 100% por 2 semanas, abandonado por agotamiento o vida real. Resultado neto: cero ganancias y frustración.

Diseñar planes para 80% de adherencia: días flexibles, opciones de comida realistas para la vida del cliente, margen para imprevistos. El 20% de imperfección es feature, no bug.

**Cuándo aplicar**:

Cuando el cliente reporta culpa por días saltados o comidas off-plan. Recordar el principio antes de que abandone por sentir que ya falló.

**Ejemplo de uso**:

> En notas_coach iniciales: "Vas a tener semanas perfectas y semanas raras. Si cumplís 80% sostenido, ganamos. No me importa la semana perfecta seguida de tres ausentes — me importa la semana de 4/5 sostenida 12 semanas."

---

### `postura_escritorio_pausas` — Postura escritorio: pausas activas cada 60-90 min

**Tags**: `fundamental, prevencion_lesiones, postura`

**Resumen**: 8h sentado al día genera tensión cervical y debilidad postural. Pausas de 2 min cada 60-90 min revierten parte del daño.

**Descripción completa**:

Estar sentado 8+ horas activa el patrón de 'síndrome cruzado superior' (hombros adelantados, cuello en flexión, espalda media débil). Esto NO se compensa con 1 hora de gym.

Pausas efectivas (2 min cada 60-90 min de trabajo):
- 10 face-pulls con banda elástica (escapulares retractores)
- 10 thoracic extensions sobre silla
- 30 seg estiramiento de psoas (zancada estática)

Levantar la pantalla a altura de ojos previene flexión cervical crónica. Si no podés, comprar elevador de monitor (15 USD).

**Cuándo aplicar**:

Clientes con trabajo de oficina o estudio prolongado. Especialmente quienes reportan dolor cervical o lumbar crónico bajo.

**Ejemplo de uso**:

> Tip del plan: "Cada hora de trabajo, levantate 2 min: 10 face-pulls con banda + estirar psoas. No es opcional si pasás 8+ hs sentado."

---

### `gestion_estres_cortisol` — Gestión de estrés: 10 min/día reduce cortisol crónico

**Tags**: `recuperacion, fundamental, adherencia`

**Resumen**: Cortisol crónico bloquea recomposición. 10 min/día de respiración consciente bajan cortisol salival ~20%.

**Descripción completa**:

Estrés crónico (trabajo + entreno + vida) eleva cortisol basal. Cortisol alto sostenido bloquea: pérdida de grasa abdominal, recuperación muscular, calidad del sueño.

Métodos validados (10 min/día):
- Respiración 4-7-8 (4 inhalar, 7 sostener, 8 exhalar) × 4 rondas
- Meditación guiada (apps: Calm, Insight Timer)
- Caminata silenciosa sin teléfono
- Journaling 3 cosas del día

NO es 'wellness para venderte cursos' — es respuesta vagal medible. Hasta 70% de clientes en estancamiento prolongado mejoran al sumar este hábito.

**Cuándo aplicar**:

Cliente que reporta no progresar a pesar de plan + nutrición perfectos. O quienes describen estrés crónico (trabajo intenso, problemas familiares, falta de tiempo).

**Ejemplo de uso**:

> Notas: "Sumá 10 minutos al día de algo que te baje el sistema nervioso — respiración 4-7-8, caminata sin teléfono, lo que prefieras. Esto NO es relajación opcional, es parte del plan."

---

### `neat_diario_pasos` — NEAT: 7-10k pasos diarios + actividad no-entreno

**Tags**: `adherencia, macros, progresion`

**Resumen**: NEAT (Non-Exercise Activity Thermogenesis) suma 200-500 kcal/día. Más impacto en composición que el cardio formal.

**Descripción completa**:

NEAT es el gasto calórico de TODO lo que NO es entreno formal: pasos, escaleras, gestos, postura. En personas sedentarias representa ~200 kcal/día; en personas activas, hasta 1000 kcal/día.

Objetivos prácticos:
- 7,000 pasos: mínimo viable para no-sedentario
- 10,000 pasos: target sostenible para clientes promedio
- 12,000+ pasos: clientes en déficit calórico agresivo

Subir escaleras en lugar de ascensor, caminar al supermercado, levantarse del escritorio — todo cuenta. Smartwatch o app de pasos hace tracking pasivo.

LECCIÓN CLAVE: 30 min de cardio formal NO compensan 8h sentado.

**Cuándo aplicar**:

TODO plan. Especialmente en perdida_grasa donde el déficit calórico real depende del NEAT, no solo del entreno.

**Ejemplo de uso**:

> Tip del plan: "Apuntá a 10,000 pasos diarios. Bajate del bus 2 paradas antes, subí escaleras, andá al kiosco caminando. Esto vale más para tu composición que 1 hora extra de cardio."

---

---

## Vertical: `ciclo` (3 principles)
<a name="vertical-ciclo"></a>

### `ciclo_no_es_excusa_es_dato` — El ciclo es info, no excusa

**Tags**: `fundamental, realismo, progresion`

**Resumen**: Conocer en qué fase estás permite ajustar carga inteligentemente. No es para 'explicar' días flojos.

**Descripción completa**:

El ciclo modula recuperación, fuerza y respuesta hormonal — pero la diferencia entre fases es del 5-10% en performance, no del 50%. Mujeres entrenadas hacen PRs durante menstruación.

Usá el tracking como info para ajustar volumen/intensidad cuando aplica (lútea tardía: +200 kcal, bajar volumen 10%). NO uses la fase para justificar días sin entrenar — eso no es plan, es excusa.

La fase NO determina tu performance — tu dedicación sí.

**Cuándo aplicar**:

Cliente femenina con tracking activo. Especialmente cuando reporta que ciertas semanas 'rinde menos' sistemáticamente.

**Ejemplo de uso**:

> Notas: "La fase lútea tardía pide más comfort food y menos volumen — perfecto, ajustá. Pero esos PRs los vamos a hacer en fase folicular, no son imposibles en otras fases."

---

### `lutea_tardia_sumar_calorias` — Lútea tardía: sumar 100-200 kcal (no es 'romper la dieta')

**Tags**: `macros, recuperacion, adherencia`

**Resumen**: En la semana antes de la menstruación, el cuerpo pide más calorías. Sumalas planificado, no por antojo.

**Descripción completa**:

Durante la fase lútea tardía (1 semana antes del sangrado), la temperatura corporal sube ~0.5°C y el gasto basal aumenta 5-10%. Eso son 100-200 kcal extra diarias que el cuerpo pide.

Ignorar esa señal lleva a antojos descontrolados (binge) que rompen la adherencia. Sumarla planificada (más arroz, más fruta, más grasa buena) la mantiene en macros.

NO es 'darse permiso' — es matemática de gasto vs ingesta.

**Cuándo aplicar**:

Cliente en déficit calórico que reporta antojos fuertes en una semana específica del mes. Cross-check con tracking de ciclo.

**Ejemplo de uso**:

> Notas: "Días 22-28 de tu ciclo, sumá 1 porción extra de carbohidrato o fruta al almuerzo. Tu cuerpo lo pide y es legítimo. NO es romper el plan — es leerlo bien."

---

### `anticonceptivos_son_dato_no_obstaculo` — Anticonceptivos hormonales: dato, no obstáculo

**Tags**: `adherencia, recuperacion`

**Resumen**: Con COC/IUD hormonal la variabilidad del ciclo natural está suprimida. Igual hay ajustes finos según efectos secundarios.

**Descripción completa**:

Mujeres con anticonceptivos hormonales (píldora combinada, IUD hormonal, implante) tienen ciclo menos variable que el natural — pero NO totalmente plano. Efectos secundarios típicos modulan performance:

- Retención líquidos: peso fluctúa ±1-2 kg sin cambio de composición
- Cefalea cíclica: ajustar carga ese día
- Cambio de mood: contemplar en notas_coach

La fase placebo (4-7 días sin hormonas) puede mimic los efectos del ciclo natural a escala reducida.

**Cuándo aplicar**:

Cliente con anticonceptivos hormonales. Suele requerir menos ajustes pero NO ninguno.

**Ejemplo de uso**:

> Notas: "Con tu IUD hormonal el ciclo no fluctúa tanto. Pero los días de placebo (si aplican) pueden traer cefalea — ajustá carga el día que sientas eso, sin culpa."

---

---


*Fin del catálogo.*
