#!/usr/bin/env python3
"""
Plan Julie Rodriguez — ID 57 — Plan Esencial
Science-Based Full Body 3x + Cardio Activo
Énfasis: Glúteos, Piernas, Brazos
Generado: 2026-03-30
"""
import json

# ─── ENTRENAMIENTO ──────────────────────────────────────────────────────────

entrenamiento = {
    "objetivo": "Recomposición corporal: perder grasa y ganar músculo. Énfasis en Glúteos, Piernas y Brazos. Progresión 4 semanas: Sem1 RIR 3-4 → Sem2 RIR 2-3 → Sem3 RIR 1-2 → Sem4 Deload.",
    "metodologia": "Science-Based Full Body 3x/semana",
    "nivel": "Principiante",
    "duracion_semanas": 4,
    "equipo": ["Mancuernas", "Poleas", "Bancos", "Bandas de resistencia"],
    "dias": [
        {
            "nombre": "Full Body A — Glúteos y Piernas",
            "dia": "Lunes",
            "grupo_muscular": "Glúteos/Piernas",
            "notas": "Calentamiento 10 min: movilidad articular + activación glúteos con banda. Cardio finisher: caminadora inclinación 4% · 5.5 km/h · 20-25 min.",
            "ejercicios": [
                {
                    "nombre": "Hip Thrust con Mancuerna en Banco",
                    "series": 3,
                    "repeticiones": "12-15",
                    "rir": 3,
                    "descanso": "90 seg",
                    "notas": "Hombros sobre el banco, mancuerna en cadera con toalla. Empuja cadera hasta extensión completa. Aprieta glúteos 1 seg arriba. Sem1 RIR3 → Sem2 RIR2 → Sem3 RIR1."
                },
                {
                    "nombre": "Sentadilla Goblet con Mancuerna",
                    "series": 3,
                    "repeticiones": "10-12",
                    "rir": 3,
                    "descanso": "90 seg",
                    "notas": "Mancuerna vertical contra el pecho. Pies ancho de hombros, pies ligeramente afuera. Rodillas siguen dirección de los pies. Espalda recta."
                },
                {
                    "nombre": "Press de Banca con Mancuernas",
                    "series": 3,
                    "repeticiones": "10-12",
                    "rir": 3,
                    "descanso": "90 seg",
                    "notas": "Banco plano. Mancuernas a altura del pecho, codos a 45°. Control en la bajada (2 seg). No trabes codos completamente arriba."
                },
                {
                    "nombre": "Remo con Mancuerna 1 Brazo",
                    "series": 3,
                    "repeticiones": "10-12 c/lado",
                    "rir": 3,
                    "descanso": "60 seg",
                    "notas": "Rodilla y mano apoyadas en banco. Espalda paralela al suelo. Lleva el codo hacia la cadera. Hombro estable, no rotes el tronco."
                },
                {
                    "nombre": "Zancada Reversa con Mancuernas",
                    "series": 3,
                    "repeticiones": "10 c/pierna",
                    "rir": 3,
                    "descanso": "90 seg",
                    "notas": "Paso atrás, rodilla trasera casi toca el suelo. Tronco erguido. Empuja con el talón de la pierna delantera para volver. Alterna piernas."
                },
                {
                    "nombre": "Curl de Bíceps Alterno con Mancuernas",
                    "series": 3,
                    "repeticiones": "12 c/brazo",
                    "rir": 3,
                    "descanso": "60 seg",
                    "notas": "Codos pegados al cuerpo. Supina la muñeca al subir. Baja controlado 2 seg. No uses el cuerpo para impulsar."
                },
                {
                    "nombre": "Extensión de Tríceps Sobre Cabeza (1 Mancuerna)",
                    "series": 3,
                    "repeticiones": "12",
                    "rir": 3,
                    "descanso": "60 seg",
                    "notas": "Sostén la mancuerna con ambas manos detrás de la cabeza. Codos apuntando al techo, fijos. Extiende sin mover los codos. Máximo estiramiento del tríceps largo."
                },
                {
                    "nombre": "Plancha Frontal",
                    "series": 3,
                    "repeticiones": "30-40 seg",
                    "rir": None,
                    "descanso": "45 seg",
                    "notas": "Cuerpo recto de cabeza a talones. Ombligo adentro. Respira de forma continua. Progresa: Sem1→30s · Sem2→35s · Sem3→40s · Sem4→25s."
                }
            ]
        },
        {
            "nombre": "Cardio MISS — Caminadora o Elíptica",
            "dia": "Martes",
            "grupo_muscular": "Cardio",
            "notas": "30-40 minutos a intensidad moderada sostenida. FC objetivo: 120-140 ppm (60-70% FCmax). Ideal en ayunas o 2h después de comer.",
            "ejercicios": [
                {
                    "nombre": "Caminadora con Inclinación o Elíptica",
                    "series": 1,
                    "repeticiones": "30-40 min",
                    "descanso": "—",
                    "notas": "Caminadora: velocidad 5-6 km/h · inclinación 3-5%. Elíptica: resistencia media. Mantén FC entre 120-140 ppm. Esta intensidad quema grasa sin comprometer recuperación muscular."
                }
            ]
        },
        {
            "nombre": "Full Body B — Isquiotibiales y Hombros",
            "dia": "Miércoles",
            "grupo_muscular": "Isquiotibiales/Hombros",
            "notas": "Calentamiento 10 min: movilidad de cadera + activación isquiotibiales. Cardio finisher: elíptica resistencia media 20-25 min.",
            "ejercicios": [
                {
                    "nombre": "Romanian Deadlift con Mancuernas",
                    "series": 3,
                    "repeticiones": "10-12",
                    "rir": 3,
                    "descanso": "90 seg",
                    "notas": "Rodillas ligeramente flexionadas, fijas. Empuja cadera hacia atrás mientras bajas las mancuernas por las piernas. Siente el estiramiento en isquiotibiales. Espalda recta siempre."
                },
                {
                    "nombre": "Kickback de Glúteo con Banda",
                    "series": 3,
                    "repeticiones": "15 c/pierna",
                    "rir": 3,
                    "descanso": "45 seg",
                    "notas": "Banda en los pies. 4 puntos de apoyo (manos y rodillas). Lleva el pie hacia el techo con rodilla a 90°. Aprieta glúteo arriba. Cadera completamente estable."
                },
                {
                    "nombre": "Press Militar con Mancuernas de Pie",
                    "series": 3,
                    "repeticiones": "10-12",
                    "rir": 3,
                    "descanso": "90 seg",
                    "notas": "Mancuernas a altura de hombros, palmas al frente. Abdomen apretado. Empuja hacia arriba sin arquear la espalda lumbar. Control en la bajada."
                },
                {
                    "nombre": "Jalón al Pecho en Polea",
                    "series": 3,
                    "repeticiones": "10-12",
                    "rir": 3,
                    "descanso": "90 seg",
                    "notas": "Agarre prono ancho. Ligeramente inclinada hacia atrás. Lleva la barra/manija hacia la clavícula. Codos viajan abajo y afuera. Aprieta espalda en la posición baja."
                },
                {
                    "nombre": "Sentadilla Sumo con Mancuerna",
                    "series": 3,
                    "repeticiones": "12-15",
                    "rir": 3,
                    "descanso": "90 seg",
                    "notas": "Pies más abiertos que hombros, puntas afuera 45°. Mancuerna colgando al centro. Rodillas siguen dirección de los pies. Activa glúteos y aductores al subir."
                },
                {
                    "nombre": "Curl de Bíceps en Polea Baja",
                    "series": 3,
                    "repeticiones": "12",
                    "rir": 3,
                    "descanso": "60 seg",
                    "notas": "Polea baja con barra corta o manija. Codos fijos pegados al cuerpo. Tensión constante (ventaja vs mancuerna). Control en la bajada."
                },
                {
                    "nombre": "Tríceps en Polea Alta",
                    "series": 3,
                    "repeticiones": "12",
                    "rir": 3,
                    "descanso": "60 seg",
                    "notas": "Cuerda o barra en V en la polea alta. Codos pegados a los costados, fijos. Empuja hacia abajo hasta extensión completa. Abre ligeramente al final."
                },
                {
                    "nombre": "Dead Bug",
                    "series": 3,
                    "repeticiones": "8 c/lado",
                    "rir": None,
                    "descanso": "45 seg",
                    "notas": "Espalda baja pegada al suelo todo el tiempo. Baja brazo y pierna opuestos lentamente (4 seg). Exhala al bajar. Excelente para estabilidad lumbar y core profundo."
                }
            ]
        },
        {
            "nombre": "Cardio Activo (Opcional)",
            "dia": "Jueves",
            "grupo_muscular": "Cardio",
            "notas": "Día opcional. Si tienes energía: 30 min caminadora suave o elíptica. Si estás cansada, descansa. Escucha tu cuerpo — la recuperación también es parte del programa.",
            "ejercicios": [
                {
                    "nombre": "Elíptica o Caminadora Suave",
                    "series": 1,
                    "repeticiones": "30 min (opcional)",
                    "descanso": "—",
                    "notas": "Intensidad baja: 50-60% FCmax. Recuperación activa, no entrenamiento duro. Solo si te sientes bien y sin dolor muscular severo."
                }
            ]
        },
        {
            "nombre": "Full Body C — Glúteos y Brazos",
            "dia": "Viernes",
            "grupo_muscular": "Glúteos/Brazos",
            "notas": "Calentamiento 10 min: activación glúteos con banda. Cardio finisher: 20-25 min caminadora ritmo moderado.",
            "ejercicios": [
                {
                    "nombre": "Hip Thrust con Banda de Resistencia",
                    "series": 4,
                    "repeticiones": "15-20",
                    "rir": 3,
                    "descanso": "90 seg",
                    "notas": "Banda justo encima de las rodillas + peso corporal. 4 series este día para máximo volumen de glúteo de la semana. Progresa añadiendo peso/banda más gruesa cada semana."
                },
                {
                    "nombre": "Clamshell con Banda",
                    "series": 3,
                    "repeticiones": "15 c/lado",
                    "rir": 3,
                    "descanso": "45 seg",
                    "notas": "Banda sobre las rodillas. Acostada de lado, caderas y rodillas a 90°. Abre la rodilla superior como almeja. Cadera no se mueve. Activa glúteo medio perfectamente."
                },
                {
                    "nombre": "Remo Sentado en Polea Baja",
                    "series": 3,
                    "repeticiones": "10-12",
                    "rir": 3,
                    "descanso": "90 seg",
                    "notas": "Siéntate frente a la polea baja, pies en el apoyo. Espalda recta. Lleva los codos hacia atrás juntando omóplatos. Pecho fuera durante el movimiento. No uses la inercia."
                },
                {
                    "nombre": "Elevaciones Laterales con Mancuernas",
                    "series": 3,
                    "repeticiones": "12-15",
                    "rir": 3,
                    "descanso": "60 seg",
                    "notas": "Ligeramente inclinada al frente. Sube los brazos hasta la altura de los hombros. Pulgares apuntando ligeramente abajo (como vaciando un vaso). Peso ligero, ejecución perfecta."
                },
                {
                    "nombre": "Curl Martillo con Mancuernas",
                    "series": 3,
                    "repeticiones": "12 c/brazo",
                    "rir": 3,
                    "descanso": "60 seg",
                    "notas": "Agarre neutro (palmas enfrentadas). Trabaja bíceps braquial + braquiorradial. Codos fijos. Movimiento más pesado que el curl supinado — ideal para volumen de brazo."
                },
                {
                    "nombre": "Press de Tríceps en Polea Alta (Cuerda)",
                    "series": 3,
                    "repeticiones": "12",
                    "rir": 3,
                    "descanso": "60 seg",
                    "notas": "Cuerda en la polea alta. Inclínate ligeramente hacia la polea. Codos fijos a los costados. Empuja hacia abajo y separa la cuerda al final. Tensión constante en tríceps."
                },
                {
                    "nombre": "Abducción de Cadera con Banda de Pie",
                    "series": 3,
                    "repeticiones": "15 c/pierna",
                    "rir": 3,
                    "descanso": "60 seg",
                    "notas": "Banda encima de rodillas. Apóyate en una pared. Levanta la pierna lateral a 45°. Sin inclinar el tronco. Excelente para glúteo medio — zona clave para forma del glúteo."
                },
                {
                    "nombre": "Crunch Abdominal Controlado",
                    "series": 3,
                    "repeticiones": "15",
                    "rir": None,
                    "descanso": "45 seg",
                    "notas": "Manos detrás de la cabeza (sin jalar el cuello). Sube solo la parte superior del tronco. Exhala al subir. Contrae el abdomen durante todo el movimiento."
                }
            ]
        },
        {
            "nombre": "Cardio o Descanso Activo",
            "dia": "Sábado",
            "grupo_muscular": "Cardio",
            "notas": "Caminata activa, elíptica suave, yoga o stretching. El objetivo es mantenerte activa sin fatigar los músculos. Estiramiento post-semana: cuádriceps, isquiotibiales, cadera y hombros.",
            "ejercicios": [
                {
                    "nombre": "Caminata Activa o Sesión de Estiramientos",
                    "series": 1,
                    "repeticiones": "30-45 min",
                    "descanso": "—",
                    "notas": "Intensidad muy baja. Prioridad: recuperación activa y bienestar. Aprovecha para estirar todos los grupos musculares trabajados durante la semana."
                }
            ]
        }
    ]
}

# ─── NUTRICIÓN ──────────────────────────────────────────────────────────────

nutricion = {
    "objetivo_cal": 1800,
    "proteina_g": 133,
    "carbohidratos_g": 170,
    "grasas_g": 62,
    "notas": "Plan diseñado para recomposición corporal. Incluye proteína isolate + creatina 5g en el desayuno post-entreno. Sin carne de res. 4 comidas principales al día. Ajusta porciones si el peso baja más de 0.5kg/semana o si tienes hambre excesiva.",
    "suplementacion_diaria": "Creatina monohidrato 5g con el desayuno · Proteína Isolate 1 scoop (25-27g P) con el desayuno",
    "comidas": [
        {
            "nombre": "Desayuno Post-Entreno (~8am)",
            "hora_sugerida": "08:00",
            "notas_comida": "Incluir siempre: 1 scoop proteína Isolate + 5g creatina. El batido va CON la comida sólida.",
            "opciones": [
                {
                    "nombre": "Avena con Banana y Mantequilla de Maní + Isolate",
                    "alimentos": [
                        "60g avena en hojuelas (cocida en agua o leche light)",
                        "1 banana pequeña (80g) o 1 taza de fresas",
                        "1 cdita mantequilla de maní natural (15g)",
                        "1 scoop proteína Isolate (batido con agua)",
                        "5g creatina monohidrato"
                    ],
                    "calorias": 510,
                    "proteina_g": 40,
                    "carbohidratos_g": 62,
                    "grasas_g": 14
                },
                {
                    "nombre": "Huevos Scrambled + Tostada Integral + Isolate",
                    "alimentos": [
                        "3 huevos enteros scrambled (con spray o sin aceite)",
                        "2 rebanadas pan integral tostado",
                        "1/4 aguacate + tomate cherry",
                        "1 scoop proteína Isolate (batido con agua)",
                        "5g creatina"
                    ],
                    "calorias": 525,
                    "proteina_g": 44,
                    "carbohidratos_g": 45,
                    "grasas_g": 22
                },
                {
                    "nombre": "Yogur Griego + Granola + Fruta + Isolate",
                    "alimentos": [
                        "200g yogur griego 0% natural",
                        "35g granola sin azúcar añadida",
                        "1/2 taza arándanos o fresas (80g)",
                        "1 scoop proteína Isolate (batido con agua)",
                        "5g creatina"
                    ],
                    "calorias": 495,
                    "proteina_g": 44,
                    "carbohidratos_g": 52,
                    "grasas_g": 10
                },
                {
                    "nombre": "Pancakes de Avena-Huevo + Isolate",
                    "alimentos": [
                        "60g avena + 2 huevos + 1/2 banana (batir y cocinar en sartén sin aceite)",
                        "1 cdita miel o 1 cda mantequilla de maní",
                        "1 scoop proteína Isolate (batido con agua)",
                        "5g creatina"
                    ],
                    "calorias": 505,
                    "proteina_g": 41,
                    "carbohidratos_g": 58,
                    "grasas_g": 16
                }
            ]
        },
        {
            "nombre": "Almuerzo (~1:00 - 2:00pm)",
            "hora_sugerida": "13:00",
            "notas_comida": "Comida principal del día. Incluir siempre una fuente de proteína magra, carbohidrato complejo y vegetales.",
            "opciones": [
                {
                    "nombre": "Pechuga de Pollo + Arroz + Ensalada",
                    "alimentos": [
                        "160g pechuga de pollo a la plancha o al horno",
                        "170g arroz blanco cocido (~65g crudo)",
                        "Ensalada: lechuga, tomate, pepino, limón",
                        "1 cdita aceite de oliva para la ensalada"
                    ],
                    "calorias": 545,
                    "proteina_g": 40,
                    "carbohidratos_g": 56,
                    "grasas_g": 14
                },
                {
                    "nombre": "Salmón al Horno + Papa + Brócoli",
                    "alimentos": [
                        "160g filete de salmón al horno con limón y hierbas",
                        "1 papa mediana al horno (180g)",
                        "150g brócoli al vapor con ajo",
                        "1 cdita aceite de oliva"
                    ],
                    "calorias": 545,
                    "proteina_g": 38,
                    "carbohidratos_g": 42,
                    "grasas_g": 22
                },
                {
                    "nombre": "Bowl de Atún + Pasta Integral + Vegetales",
                    "alimentos": [
                        "1.5 latas atún en agua (210g escurrido)",
                        "80g pasta integral cocida (~55g cruda)",
                        "Tomate, pimentón, cebolla, ajo salteados",
                        "1 cdita aceite de oliva"
                    ],
                    "calorias": 540,
                    "proteina_g": 42,
                    "carbohidratos_g": 58,
                    "grasas_g": 12
                },
                {
                    "nombre": "Pollo + Quinoa + Aguacate",
                    "alimentos": [
                        "150g pechuga de pollo cocida o desmenuzada",
                        "120g quinoa cocida (~45g cruda)",
                        "1/4 aguacate (40g)",
                        "Pepino, tomate, cebolla morada, limón"
                    ],
                    "calorias": 550,
                    "proteina_g": 40,
                    "carbohidratos_g": 50,
                    "grasas_g": 18
                }
            ]
        },
        {
            "nombre": "Snack Media Tarde (~5:00pm)",
            "hora_sugerida": "17:00",
            "notas_comida": "Snack ligero para llegar con buen apetito a la cena pero sin llegar con hambre extrema. Alta proteína, moderados carbos.",
            "opciones": [
                {
                    "nombre": "Yogur Griego + Manzana",
                    "alimentos": [
                        "200g yogur griego 0% natural",
                        "1 manzana mediana (150g)"
                    ],
                    "calorias": 230,
                    "proteina_g": 20,
                    "carbohidratos_g": 33,
                    "grasas_g": 2
                },
                {
                    "nombre": "Huevos Duros + Naranja",
                    "alimentos": [
                        "2 huevos duros",
                        "1 naranja grande (180g)"
                    ],
                    "calorias": 235,
                    "proteina_g": 16,
                    "carbohidratos_g": 24,
                    "grasas_g": 10
                },
                {
                    "nombre": "Requesón (Cottage Cheese) + Fruta",
                    "alimentos": [
                        "150g requesón o cottage cheese bajo en grasa",
                        "1 taza de uvas o 1 durazno mediano"
                    ],
                    "calorias": 220,
                    "proteina_g": 21,
                    "carbohidratos_g": 22,
                    "grasas_g": 6
                },
                {
                    "nombre": "Mini Avena Proteica",
                    "alimentos": [
                        "35g avena en agua o leche light",
                        "100g yogur griego 0%",
                        "Canela al gusto · opcional: 1 cdita miel"
                    ],
                    "calorias": 225,
                    "proteina_g": 17,
                    "carbohidratos_g": 32,
                    "grasas_g": 4
                }
            ]
        },
        {
            "nombre": "Cena (~7:30 - 8:30pm)",
            "hora_sugerida": "20:00",
            "notas_comida": "Cena balanceada. Incluir carbohidrato para recuperación nocturna y reposición de glucógeno del entrenamiento de la mañana.",
            "opciones": [
                {
                    "nombre": "Pollo a la Plancha + Yuca o Plátano + Vegetales",
                    "alimentos": [
                        "140g pechuga de pollo a la plancha",
                        "150g yuca cocida O 1 plátano maduro mediano",
                        "150g brócoli o espárragos al vapor",
                        "1 cdita aceite de oliva"
                    ],
                    "calorias": 490,
                    "proteina_g": 37,
                    "carbohidratos_g": 44,
                    "grasas_g": 13
                },
                {
                    "nombre": "Salmón a la Plancha + Quinoa + Espinacas",
                    "alimentos": [
                        "150g salmón a la plancha con limón",
                        "100g quinoa cocida (~40g cruda)",
                        "100g espinacas salteadas con ajo y aceite de oliva"
                    ],
                    "calorias": 510,
                    "proteina_g": 38,
                    "carbohidratos_g": 36,
                    "grasas_g": 22
                },
                {
                    "nombre": "Tortilla de Huevo + Papa al Horno + Ensalada",
                    "alimentos": [
                        "3 huevos enteros + 1/2 taza espinacas (tortilla en sartén con spray)",
                        "1 papa mediana al horno (150g)",
                        "Ensalada verde mixta con limón y aceite"
                    ],
                    "calorias": 480,
                    "proteina_g": 27,
                    "carbohidratos_g": 39,
                    "grasas_g": 20
                },
                {
                    "nombre": "Atún + Arroz Integral + Ensalada",
                    "alimentos": [
                        "1.5 latas atún en agua (210g escurrido)",
                        "120g arroz integral cocido (~45g crudo)",
                        "Lechuga, tomate, zanahoria rallada",
                        "1 cdita aceite de oliva + limón"
                    ],
                    "calorias": 495,
                    "proteina_g": 42,
                    "carbohidratos_g": 48,
                    "grasas_g": 9
                }
            ]
        }
    ]
}

# ─── SAVE FILES ─────────────────────────────────────────────────────────────

with open('/tmp/julie_entrenamiento.json', 'w', encoding='utf-8') as f:
    json.dump(entrenamiento, f, ensure_ascii=False, indent=2)
    print(f"entrenamiento.json: {len(json.dumps(entrenamiento))} bytes")

with open('/tmp/julie_nutricion.json', 'w', encoding='utf-8') as f:
    json.dump(nutricion, f, ensure_ascii=False, indent=2)
    print(f"nutricion.json: {len(json.dumps(nutricion))} bytes")

print("Done! Files saved to /tmp/")
