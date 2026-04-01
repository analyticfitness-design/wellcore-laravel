import json, base64

def ex(nombre, series, reps, descanso, notas=None):
    e = {"nombre": nombre, "series": series, "repeticiones": reps, "descanso": descanso}
    if notas:
        e["notas"] = notas
    return e

piernas_a = [
    ex("Sentadilla con barra", 4, "8-10", "120s", "Profundidad completa, rodillas alineadas con puntas"),
    ex("Prensa 45 grados", 4, "10-12", "90s"),
    ex("Hip Thrust con barra", 4, "10-12", "90s", "Contraccion maxima en el tope del movimiento"),
    ex("Extension de cuadriceps en maquina", 3, "12-15", "60s"),
    ex("Zancadas caminando con mancuernas", 3, "12 c/pierna", "60s"),
]

empuje = [
    ex("Press de banca plano con barra", 4, "8-10", "90s"),
    ex("Press inclinado con mancuernas", 3, "10-12", "90s"),
    ex("Press militar con barra de pie", 4, "8-10", "90s"),
    ex("Extensiones de triceps en polea alta", 3, "12-15", "60s"),
    ex("Elevaciones laterales con mancuernas", 4, "15", "60s"),
]

espalda = [
    ex("Dominadas con agarre prono o jalon al pecho", 4, "8", "90s", "Agrega lastre si superas 10 reps facilmente"),
    ex("Remo con barra Pendlay", 4, "8-10", "90s"),
    ex("Remo en maquina o polea baja", 3, "12", "60s"),
    ex("Curl de biceps con barra", 4, "10-12", "60s"),
    ex("Curl martillo con mancuernas", 3, "12", "60s"),
]

piernas_b = [
    ex("Peso muerto rumano con barra", 4, "8-10", "120s", "Rodillas levemente flexionadas, tiro desde cadera"),
    ex("Curl de isquiotibiales en maquina acostado", 4, "12-15", "60s"),
    ex("Hip Thrust unilateral con barra", 3, "10 c/pierna", "60s"),
    ex("Sentadilla sumo con mancuerna goblet", 4, "10-12", "90s"),
    ex("Abductores en maquina", 3, "15-20", "60s"),
]

core = [
    ex("Peso muerto convencional tecnica", 3, "5", "120s", "Carga moderada — foco en activacion lumbar y tension"),
    ex("Plancha frontal", 4, "45 segundos", "45s"),
    ex("Elevaciones de piernas colgado en barra", 3, "15", "60s"),
    ex("Russian twist con disco", 3, "20 rotaciones", "45s"),
    ex("Hip Thrust con barra finisher", 3, "10", "90s"),
]

titles = [
    "Base y Activacion — Domina el patron de movimiento",
    "Progresion de Carga — +5 al 10 porciento en compuestos",
    "Semana de Volumen Maximo — Series extra en principales",
    "Intensidad Pico — PRs y Drop Sets",
]

notas_sem = [
    "Semana 1: Carga moderada. Prioriza tecnica perfecta en sentadilla e hip thrust.",
    "Semana 2: Sube 5-10% en sentadilla, press de banca y peso muerto rumano vs semana 1.",
    "Semana 3: Agrega 1 serie extra en Sentadilla e Hip Thrust. Busca un nuevo RM en Hip Thrust.",
    "Semana 4: Maxima intensidad. Drop set en el ultimo set de cada ejercicio compuesto.",
]

def make_week(week_num):
    i = week_num - 1
    dias = [
        {"nombre": "Dia 1 — Piernas A: Cuadriceps y Gluteos", "tipo": "piernas", "ejercicios": piernas_a},
        {"nombre": "Dia 2 — Empuje: Pecho, Hombros y Triceps", "tipo": "empuje", "ejercicios": empuje},
        {"nombre": "Dia 3 — Espalda y Biceps", "tipo": "espalda", "ejercicios": espalda},
        {"nombre": "Dia 4 — Piernas B: Posterior y Gluteos", "tipo": "piernas", "ejercicios": piernas_b},
        {"nombre": "Dia 5 — Core y Accesorios", "tipo": "core", "ejercicios": core},
        {"nombre": "Dia 6 — Descanso Activo (caminata 20 min)", "tipo": "descanso", "ejercicios": []},
        {"nombre": "Dia 7 — Descanso Total", "tipo": "descanso", "ejercicios": []},
    ]
    return {"semana": week_num, "titulo": titles[i], "nota": notas_sem[i], "dias": dias}

plan_entrenamiento = {
    "duracion_semanas": 4,
    "dias_por_semana": 5,
    "objetivo": "Hipertrofia muscular con enfasis en piernas (cuadriceps y gluteos)",
    "nivel": "avanzado",
    "lugar": "gimnasio",
    "semanas": [make_week(w) for w in range(1, 5)],
}

plan_nutricion = {
    "calorias_diarias": 2800,
    "proteina_g": 170,
    "carbohidratos_g": 325,
    "grasas_g": 78,
    "objetivo": "Superavit calorico moderado (+300-400 kcal) para maximizar ganancia muscular. Alta proteina para soportar el volumen de entrenamiento avanzado.",
    "tips": [
        "Consume 40-50g de proteina dentro de los 45 min post-entrenamiento (shake + comida solida)",
        "Los carbohidratos son tu aliado — come la mayoria pre y post entreno para rendir y recuperar",
        "Creatina monohidratada: 5g diarios, funciona mejor con consistencia de 4+ semanas",
        "6 horas de sueno no es suficiente — cada hora extra equivale a mas testosterona y mas musculo",
        "Distribuye la proteina en 4-5 comidas (30-45g por comida) para maximizar sintesis muscular",
    ],
    "comidas_sugeridas": [
        {
            "nombre": "Desayuno",
            "opciones": [
                "4 huevos revueltos + avena con leche + platano",
                "Batido: 2 scoops whey + avena + leche + mantequilla de mani + platano",
                "3 huevos enteros + 2 tostadas integrales + aguacate + jugo de naranja",
            ],
        },
        {
            "nombre": "Almuerzo",
            "opciones": [
                "200g pechuga de pollo + 1 taza arroz integral + aguacate + verduras",
                "200g res magra + papa cocida + ensalada de tomate + aceite de oliva",
                "150g salmon + quinoa + brocoli al vapor + aceite de oliva",
            ],
        },
        {
            "nombre": "Pre-entreno (1-2h antes)",
            "opciones": [
                "1 taza arroz + 150g pollo + fruta",
                "Pan integral con crema de mani + platano + cafe",
                "Avena con leche + 1 scoop proteina",
            ],
        },
        {
            "nombre": "Post-entreno",
            "opciones": [
                "1 scoop whey + 300ml leche + platano inmediato, comida solida 30-45min despues",
                "200g pollo + arroz blanco + fruta",
                "Batido de proteina + papa cocida + atun",
            ],
        },
        {
            "nombre": "Cena",
            "opciones": [
                "200g tilapia o pollo al horno + camote + ensalada verde",
                "4 huevos + tortilla integral + verduras salteadas",
                "150g carne magra + pure de papa + brocoli",
            ],
        },
    ],
}

plan_habitos = [
    {
        "nombre": "Sueno de calidad — objetivo 8 horas",
        "descripcion": "Acostarse antes de las 11pm y dormir minimo 8 horas. Apagar pantallas 30 min antes. Pasar de 6h actuales a 8h.",
        "razon": "El 70% de la sintesis muscular ocurre durante el sueno profundo. Con 6h estas dejando masa muscular sobre la mesa — esta mejora vale tanto como el entrenamiento mismo.",
        "frecuencia": "Diario",
    },
    {
        "nombre": "Completar la sesion RISE del dia",
        "descripcion": "Realizar el entrenamiento completo sin omitir series ni ejercicios. Registrar los pesos usados en cada set.",
        "razon": "La consistencia es el factor numero 1 en hipertrofia avanzada. Cada sesion perdida rompe el estimulo de adaptacion acumulada.",
        "frecuencia": "5 dias por semana",
    },
    {
        "nombre": "Proteina en cada comida",
        "descripcion": "Incluir 30-45g de proteina de calidad en cada una de las 4-5 comidas del dia. Priorizar pollo, res, huevo, salmon y whey.",
        "razon": "La sintesis proteica muscular se maximiza distribuyendo la proteina uniformemente durante el dia, no consumiendola toda en 1-2 comidas.",
        "frecuencia": "Diario",
    },
    {
        "nombre": "Hidratacion — 3 litros diarios",
        "descripcion": "Beber minimo 3 litros de agua al dia. Llevar botella de 1L al gym y terminarla durante el entrenamiento.",
        "razon": "La deshidratacion del 2% reduce la fuerza hasta un 10%. Para un atleta avanzado, esto representa la diferencia entre un PR y una sesion mediocre.",
        "frecuencia": "Diario",
    },
    {
        "nombre": "Creatina diaria",
        "descripcion": "Tomar 5g de creatina monohidratada diariamente, preferiblemente post-entreno con carbohidratos.",
        "razon": "La creatina es el suplemento mas validado para hipertrofia. Aumenta fuerza 5-15% y volumen muscular con consistencia de minimo 4 semanas continuas.",
        "frecuencia": "Diario",
    },
]

# Build PHP script — data sections use base64_decode to avoid PHP syntax errors
php_template = """<?php
$pdo = new PDO("mysql:host=wellcorefitness_wellcorefitness-mysql;dbname=wellcorefitness","wellcorefitness","fYCVgn4XZ7twq34");

$stmt = $pdo->query("SELECT id, personalized_program FROM rise_programs WHERE client_id=11 ORDER BY id DESC LIMIT 1");
$row = $stmt->fetch(PDO::FETCH_ASSOC);
$rise_id = $row['id'];
$program = json_decode($row['personalized_program'], true) ?? [];

$program['plan_entrenamiento'] = json_decode(base64_decode('PLAN_ENT'), true);
$program['plan_nutricion']     = json_decode(base64_decode('PLAN_NUT'), true);
$program['plan_habitos']       = json_decode(base64_decode('PLAN_HAB'), true);

$json = json_encode($program, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
$update = $pdo->prepare("UPDATE rise_programs SET personalized_program = ? WHERE id = ?");
$update->execute([$json, $rise_id]);
echo "OK: " . $update->rowCount() . " fila actualizada (rise_id=" . $rise_id . ")" . PHP_EOL;

$v = $pdo->query("SELECT JSON_LENGTH(JSON_EXTRACT(personalized_program,'$.plan_entrenamiento.semanas')) as sem, JSON_UNQUOTE(JSON_EXTRACT(personalized_program,'$.plan_nutricion.calorias_diarias')) as cals, JSON_LENGTH(JSON_EXTRACT(personalized_program,'$.plan_habitos')) as hab FROM rise_programs WHERE id=" . $rise_id)->fetch(PDO::FETCH_ASSOC);
echo "Semanas: ".$v['sem']." | Calorias: ".$v['cals']." | Habitos: ".$v['hab'] . PHP_EOL;
"""

ent_b64 = base64.b64encode(json.dumps(plan_entrenamiento, ensure_ascii=False).encode('utf-8')).decode('ascii')
nut_b64 = base64.b64encode(json.dumps(plan_nutricion, ensure_ascii=False).encode('utf-8')).decode('ascii')
hab_b64 = base64.b64encode(json.dumps(plan_habitos, ensure_ascii=False).encode('utf-8')).decode('ascii')

php_script = php_template.replace("PLAN_ENT", ent_b64).replace("PLAN_NUT", nut_b64).replace("PLAN_HAB", hab_b64)

encoded = base64.b64encode(php_script.encode('utf-8')).decode('ascii')
print(f"Script size: {len(php_script)} bytes")
print(f"Base64 length: {len(encoded)}")
print("---BASE64---")
print(encoded)
