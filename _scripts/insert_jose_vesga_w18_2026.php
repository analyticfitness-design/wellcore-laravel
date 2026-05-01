<?php
// === JOSE VESGA — Coach #6 · W18-2026 ===
// Paso 1: Crea CoachMarketingProfile si no existe
// Paso 2: Inserta drop semanal en coach_content_drops
// Ejecutar: php artisan tinker < _scripts/insert_jose_vesga_w18_2026.php

use App\Models\Admin;
use App\Models\CoachContentDrop;
use App\Models\CoachMarketingProfile;
use App\Enums\Marketing\DropStatus;
use App\Enums\UserRole;
use App\Services\Marketing\DropSchemaValidator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

// ============================================================
// PARÁMETROS
// ============================================================
$coachId = 6;
$isoYear = 2026;
$isoWeek = 18;

// ============================================================
// 1. VERIFICAR COACH
// ============================================================
$coach = Admin::find($coachId);
if (!$coach) throw new \RuntimeException("Admin id={$coachId} no existe.");
if (!$coach->active) throw new \RuntimeException("Admin id={$coachId} está inactivo.");
if ($coach->role !== UserRole::Coach) {
    throw new \RuntimeException("Admin id={$coachId} no tiene role=Coach (tiene {$coach->role->value}).");
}
echo "✓ Coach: {$coach->name} (id={$coachId})\n";

// ============================================================
// 2. CREAR / VERIFICAR COACHING MARKETING PROFILE
// ============================================================
$profile = CoachMarketingProfile::where('coach_id', $coachId)->first();

if (!$profile) {
    echo "ℹ️  Profile no existe — creando cold-start para Jose Vesga...\n";

    $profileData = [
        'coach_id'                => $coachId,
        'brand_name'              => 'Jose Vesga',
        'specialty_primary'       => 'hipertrofia',
        'specialty_secondary'     => null,
        'differentiator'          => 'Creo planes completamente individualizados que se adaptan a cómo responde cada cuerpo. Uso periodización real por bloques y las herramientas de seguimiento de WellCore para ajustar semana a semana. El plan parte de quién eres tú, no de un promedio.',
        'audience_age_range'      => '25-35',
        'audience_gender'         => 'mixto',
        'audience_pain_main'      => 'Llevan meses siguiendo rutinas genéricas que no cambian. Sin seguimiento real, el plan no se adapta y el cuerpo tampoco responde. El esfuerzo está pero los resultados no llegan.',
        'audience_offer_main'     => 'metodo',
        'preferred_methodologies' => [
            'periodización por bloques',
            'sobrecarga progresiva',
            'individualización por datos del cliente',
            'ajuste semanal por respuesta al entrenamiento',
            'deload planificado',
        ],
        'content_topics'          => [
            'por qué los planes genéricos no funcionan',
            'individualización del entrenamiento',
            'periodización y fases de entrenamiento',
            'herramientas de seguimiento y progreso',
            'ajuste continuo del plan',
        ],
        'voice_adjectives'        => ['directo', 'técnico', 'cercano'],
        'active_offers'           => [
            ['name' => 'Método', 'price' => 120, 'currency' => 'USD', 'promo' => null],
        ],
        'voice_samples'           => [],
        'top_working_posts'       => [],
        'completed_at'            => now(),
    ];

    $profile = CoachMarketingProfile::create($profileData);
    echo "✅ Profile creado (id={$profile->id})\n";
} else {
    if ($profile->completed_at === null) {
        $profile->completed_at = now();
        $profile->save();
        echo "ℹ️  Profile existía sin completed_at — marcado como completo.\n";
    }
    echo "✓ Profile existente: {$profile->brand_name} | completed_at={$profile->completed_at}\n";
}

// ============================================================
// 3. JSON DEL DROP
// ============================================================
$content = json_decode(<<<'JSON'
{
  "schema_version": "coach_drop_v1",
  "brief": {
    "title": "W18-2026 · Jose Vesga — Individualización: el plan que cambia contigo",
    "objective": "Generar 5+ DMs con la palabra PLAN y 2-3 consultas calificadas sobre Método. Objetivo secundario: guardados en reel educativo.",
    "priority_offer": "metodo",
    "key_message": "**Un plan genérico no falla porque sea malo. Falla porque no es tuyo.** El que funciona parte de quién eres.",
    "target_metric": "DMs con 'PLAN' desde Story 5 + guardados reel #1",
    "weekly_theme": "Individualización: el plan que cambia contigo",
    "framing_copy": "Esta semana no vendemos coaching. Explicamos por qué los otros planes no funcionaron. Si lo entienden, el cierre lo hacen ellos solos."
  },
  "reels": [
    {
      "key": "reel_1",
      "type": "educativo",
      "title": "Por qué ningún plan genérico funciona",
      "format_meta": {
        "duration_sec_min": 30,
        "duration_sec_max": 45,
        "platforms": ["instagram", "tiktok"],
        "bpm_hint": "95-105"
      },
      "hook": {
        "text": "Si cambiaste de plan más de dos veces este año y seguiste igual, no es el plan. Es que ninguno era tuyo.",
        "rationale": "Nombra la experiencia exacta del pain: seguir planes sin resultado. 'Ninguno era tuyo' abre la promesa sin vender directamente."
      },
      "timecode_table": [
        {
          "time": "00:00-00:04",
          "dialogue": "Si cambiaste de plan más de dos veces este año y seguiste igual, no es el plan. Es que ninguno era tuyo.",
          "visual": "Plano medio frontal, coach a cámara, expresión directa y segura.",
          "edit_notes": "Texto Oswald uppercase blanco: 'NINGUNO ERA TUYO' aparece en rojo WellCore al final de la frase."
        },
        {
          "time": "00:04-00:14",
          "dialogue": "Un plan genérico usa promedios. Promedio de cuántas series aguanta la gente, de calorías, de progresión. Tú no eres un promedio.",
          "visual": "Coach gesticulando, con tablet o papeles a la mano.",
          "edit_notes": "Subtítulos siempre activos. Corte al beat en 'Tú no eres un promedio'."
        },
        {
          "time": "00:14-00:26",
          "dialogue": "Cuando trabajo con alguien, lo primero que hago es entender cómo responde su cuerpo. Su historial, su nivel actual, su disponibilidad real. El plan parte de eso, no de una plantilla.",
          "visual": "B-roll: coach revisando plan en laptop o plataforma.",
          "edit_notes": "Mogrt con 3 bullets: HISTORIAL · NIVEL · DISPONIBILIDAD. Aparecen uno por uno al ritmo."
        },
        {
          "time": "00:26-00:36",
          "dialogue": "Y después el plan cambia. Cada semana revisamos cómo respondiste. El plan no es fijo. Es vivo.",
          "visual": "Coach a cámara, tono tranquilo y seguro.",
          "edit_notes": "Texto overlay: 'EL PLAN ES VIVO' en rojo. Speed ramp suave al final del clip."
        },
        {
          "time": "00:36-00:42",
          "dialogue": "Guarda esto si entrenas y sientes que no avanzas. Y si quieres ver cómo sería un plan así para ti, escríbeme.",
          "visual": "Coach cierra mirando a cámara, expresión cálida.",
          "edit_notes": "CTA overlay grande: 'GUARDA ESTO'. Fade a negro 0.5s."
        }
      ],
      "caption": "Si cambiaste de plan más de dos veces este año y seguiste igual, no es el plan.\n\nEs que ninguno era tuyo.\n\nUn plan genérico usa promedios. Promedio de series, de calorías, de progresión.\n\nTú no eres un promedio.\n\nCuando trabajo con alguien, lo primero que hago es entender cómo responde su cuerpo. El plan parte de ahí.\n\nY después cambia. Cada semana. Porque el plan no es fijo — es vivo.\n\nGuarda esto si entrenas y sientes que no avanzas.",
      "music_note": "Beat 100 BPM instrumental minimal, electro suave. Cortes al cambio de escena. Volumen 25% durante voz.",
      "production_notes": "Grabar en gym o espacio limpio con luz frontal. Outfit sin logos externos. Lavalier o AirPods para audio limpio. 3 takes mínimo del hook. Tono directo y técnico, no motivacional agresivo."
    },
    {
      "key": "reel_2",
      "type": "conversion",
      "title": "Cómo empezamos desde el primer día",
      "format_meta": {
        "duration_sec_min": 25,
        "duration_sec_max": 35,
        "platforms": ["instagram"],
        "bpm_hint": "88-96"
      },
      "hook": {
        "text": "Cuando alguien empieza conmigo, lo primero que hago no es darle rutina. Es entender quién es.",
        "rationale": "Primera persona del coach + inversión de expectativa ('no rutina → entender'). Abre el proceso antes de mencionar la oferta."
      },
      "timecode_table": [
        {
          "time": "00:00-00:04",
          "dialogue": "Cuando alguien empieza conmigo, lo primero que hago no es darle rutina. Es entender quién es.",
          "visual": "Coach a cámara, plano medio, tono calmado y seguro.",
          "edit_notes": "Texto: 'NO ES DARLE RUTINA' en rojo WellCore, impacto en ese momento exacto."
        },
        {
          "time": "00:04-00:14",
          "dialogue": "Historial, nivel real, disponibilidad, cómo responde su cuerpo a la carga. Con eso armo el plan. No antes.",
          "visual": "B-roll: coach mostrando proceso en tablet, plataforma o dashboard.",
          "edit_notes": "Bullets aparecen en pantalla: HISTORIAL · NIVEL REAL · DISPONIBILIDAD · RESPUESTA."
        },
        {
          "time": "00:14-00:24",
          "dialogue": "Después ajustamos cada semana. Si tu cuerpo respondió bien, subimos. Si necesitaba más tiempo, esperamos. Eso es lo que hace que avances de verdad.",
          "visual": "B-roll de seguimiento en plataforma o gráfico de progreso.",
          "edit_notes": "Transición fluida al b-roll. Subtítulos activos siempre."
        },
        {
          "time": "00:24-00:30",
          "dialogue": "Si eso es lo que te falta, escríbeme PLAN por DM y vemos si podemos trabajar juntos.",
          "visual": "Coach a cámara, directo, expresión cálida.",
          "edit_notes": "CTA grande: 'PLAN' en rojo sobre negro. Fade."
        }
      ],
      "caption": "Cuando alguien empieza conmigo, lo primero que hago no es darle rutina.\n\nEs entender quién es.\n\nHistorial. Nivel real. Disponibilidad. Cómo responde su cuerpo.\n\nCon eso armo el plan. No antes.\n\nY después ajustamos cada semana. Si respondiste bien, subimos. Si necesitabas más tiempo, esperamos.\n\nEso es lo que hace que avances de verdad.\n\nSi eso es lo que te falta:\nEscríbeme PLAN por DM y vemos si podemos trabajar juntos.",
      "music_note": "Beat 92 BPM cálido, instrumental. Sin percusión agresiva. Volumen 20% durante voz.",
      "production_notes": "Plano medio frontal, luz natural si es posible. Tono cercano, no vendedor. El CTA es una invitación, no una presión. 3 takes del cierre con distintas inflexiones."
    }
  ],
  "stories": [
    {
      "day": "LUN",
      "pillar": "activacion",
      "slides": [
        {
          "kind": "text",
          "text": "Esta semana hablo de algo que veo seguido: gente que lleva meses entrenando sin ver cambios. No por falta de esfuerzo. Por falta del plan correcto. Esta semana te explico por qué.",
          "visual_hint": "Coach en gym o espacio limpio. Plano medio, expresión seria pero cercana.",
          "sticker": "none"
        },
        {
          "kind": "template",
          "text": "¿Te ha pasado?\n\nA) Sí, cambié de plan y nada cambia\nB) Tengo plan pero no avanzo\nC) Nunca tuve un plan de verdad",
          "visual_hint": "Fondo oscuro, texto centrado.",
          "sticker": "poll"
        }
      ],
      "dm_followup_hint": "A quienes voten A o C: '¿Cuánto tiempo llevas entrenando así? Cuéntame un poco.' Abrir conversación, no vender."
    },
    {
      "day": "MAR",
      "pillar": "nutricion",
      "slides": [
        {
          "kind": "visual",
          "text": "La nutrición también es parte del plan individualizado. No existe una dieta que le sirva a todos. Lo que comes depende de tu objetivo, tu metabolismo, tu rutina diaria. Cuando trabajo contigo, el plan nutricional se construye desde tus datos.",
          "visual_hint": "Foto de comida real, balanceada. Luz natural, plano cenital.",
          "sticker": "none"
        }
      ],
      "dm_followup_hint": "Si alguien responde con foto de su comida, dar 1-2 puntos concretos de feedback. No entregar plan completo en DM."
    },
    {
      "day": "MIE",
      "pillar": "spotlight",
      "slides": [
        {
          "kind": "text",
          "text": "Hace 10 semanas empezó a trabajar conmigo alguien que llevaba un año entrenando sin progresar. La primera semana le pregunté su historial real, cuánto descansaba, cómo se sentía después de entrenar. Nadie le había preguntado eso antes. En la semana 6 hizo su primer PR de peso muerto. No cambié la cantidad de trabajo. Cambié qué trabajo y cuándo.",
          "visual_hint": "Captura del PR o mensaje del cliente con permiso, sin cara ni nombre. Fondo oscuro WellCore.",
          "sticker": "none"
        },
        {
          "kind": "text",
          "text": "Eso es lo que hace la diferencia: un plan que parte de ti.",
          "visual_hint": "Texto grande sobre fondo oscuro, tipografía limpia.",
          "sticker": "question"
        }
      ],
      "dm_followup_hint": "Quien responda con su situación: escuchar primero, no vender inmediato. Si hay fit claro, mencionar Método de forma natural al final."
    },
    {
      "day": "JUE",
      "pillar": "bts",
      "slides": [
        {
          "kind": "visual",
          "text": "Así se ve armar un plan. Cada uno tarda entre 40 y 60 minutos. No es copiar y pegar. Es construir desde cero con los datos de esa persona específica. Así es como se hace bien.",
          "visual_hint": "Mano sobre laptop con plan abierto o plataforma visible. Café al lado. Luz cálida.",
          "sticker": "none"
        }
      ],
      "dm_followup_hint": "Sin follow-up específico. Responder a comentarios orgánicos."
    },
    {
      "day": "VIE",
      "pillar": "qa",
      "slides": [
        {
          "kind": "text",
          "text": "Viernes de preguntas. Manda lo que tengas: por qué no ves progreso, cómo periodizar, qué comer si entrenas de noche. Lo que sea. Respondo todo hoy.",
          "visual_hint": "Coach a cámara con sticker de preguntas activo.",
          "sticker": "question"
        }
      ],
      "dm_followup_hint": "Guardar las mejores preguntas como base para reels educativos futuros. Preguntas sobre periodización o progresión → candidatos naturales a Método."
    },
    {
      "day": "SAB",
      "pillar": "motivacion",
      "slides": [
        {
          "kind": "text",
          "text": "El progreso no es lineal. Hay semanas donde el cuerpo responde menos. No es señal de que algo falló. Es parte de la periodización. Lo importante es que el plan está ahí, ajustándose. Eso es lo que te mantiene avanzando cuando no tienes ganas.",
          "visual_hint": "Fondo oscuro minimalista, texto blanco grande. Sin foto.",
          "sticker": "slider"
        }
      ],
      "dm_followup_hint": ""
    },
    {
      "day": "DOM",
      "pillar": "reset",
      "slides": [
        {
          "kind": "text",
          "text": "Domingo. El deload también es parte del plan. No es perder lo ganado. Es la fase donde el cuerpo consolida el trabajo de la semana. Si tu plan no tiene deloads planificados, no tienes un plan completo.",
          "visual_hint": "Coach estirando o en posición de descanso activo. Ambiente tranquilo.",
          "sticker": "none"
        },
        {
          "kind": "template",
          "text": "¿Cómo fue tu semana de entrenamiento?\n\nDel 1 al 10",
          "visual_hint": "Slider numérico. Fondo oscuro.",
          "sticker": "slider"
        }
      ],
      "dm_followup_hint": "Quienes pongan 1-5: '¿Qué pasó esta semana? A veces con hablarlo se entiende qué ajustar.'"
    }
  ],
  "checklist": {
    "phases": [
      {
        "key": "pre",
        "title": "Pre-producción",
        "items": [
          {
            "title": "Confirmar hook del reel 1",
            "detail": "Leerlo en voz alta 3 veces. Si al tercer intento suena natural, está listo.",
            "subitems": ["Verificar que nombra experiencia específica, no abstracta", "Sin vocabulario prohibido del sistema"]
          },
          {
            "title": "Seleccionar música de fondo",
            "detail": "Confirmar disponible en Instagram antes de grabar. Verificar BPM hint del drop."
          },
          {
            "title": "Preparar b-roll",
            "detail": "Grabar 2-3 clips de laptop/tablet con plan abierto, o gym. 5-10 segundos cada uno."
          },
          {
            "title": "Preparar stories LUN-MIE antes del lunes",
            "detail": "Publicar LUN en la mañana (7-9am). MAR y MIE listos previo al lunes."
          }
        ]
      },
      {
        "key": "cam",
        "title": "Grabación",
        "items": [
          {
            "title": "Formato vertical 9:16, mínimo 1080p",
            "detail": "Para reels e historias. Grabar siempre en alta resolución."
          },
          {
            "title": "Iluminación frontal",
            "detail": "Luz de frente, nunca contraluz. Anillo de luz o ventana frontal."
          },
          {
            "title": "Audio limpio — lavalier o AirPods",
            "detail": "Probar 10 segundos antes del take definitivo. Sin eco ni reverb.",
            "subitems": ["Hablar directamente al lente", "Sin ruido de fondo de tráfico o música"]
          },
          {
            "title": "3 takes mínimo en clips clave",
            "detail": "Hook y CTA: 3 takes. Elegir el más natural, no el más perfecto."
          }
        ]
      },
      {
        "key": "edit",
        "title": "Edición",
        "items": [
          {
            "title": "Subtítulos en todo el reel",
            "detail": "60%+ ve sin sonido. Fuente Oswald o sans-serif limpia, tamaño legible."
          },
          {
            "title": "Cortar al beat de la música",
            "detail": "Marcar beats antes de pegar clips. Cada corte coincide con cambio rítmico."
          },
          {
            "title": "Texto overlay en hook y CTA",
            "detail": "Palabras de impacto en rojo WellCore #DC2626. Texto grande, sombra sutil."
          },
          {
            "title": "Revisar duración final",
            "detail": "Reel 1: 30-45s. Reel 2: 25-35s. Si se pasa, cortar del desarrollo, nunca del hook o CTA."
          }
        ]
      },
      {
        "key": "pub",
        "title": "Publicación",
        "items": [
          {
            "title": "Caption con CTA único",
            "detail": "Una sola acción por caption. Reel 1: guardar. Reel 2: escribir PLAN."
          },
          {
            "title": "Hashtags del set correspondiente",
            "detail": "Max 15 tags. General+Nicho para reel 1. Local+Educativo para reel 2."
          },
          {
            "title": "Horario: 7-9am o 6-8pm hora Colombia",
            "detail": "Martes a jueves rinden más. Publicar reels en esos días si es posible."
          },
          {
            "title": "Stories: una por día LUN-DOM",
            "detail": "La story del lunes activa el ciclo semanal. No saltarse días si es posible."
          }
        ]
      }
    ]
  },
  "bank": {
    "alt_hooks": [
      "Llevas meses entrenando y el cuerpo no cambia. No es falta de esfuerzo. Es que el plan no es tuyo.",
      "¿Cuántos planes distintos has probado? Si la respuesta es más de dos, ninguno estaba personalizado de verdad.",
      "El plan genérico usa promedios. Tú no eres un promedio.",
      "Cuando trabajo con alguien por primera vez, lo primero que le pregunto no es cuánto levanta. Es cómo se siente después de entrenar.",
      "Un plan que no cambia contigo ya dejó de funcionar. La periodización existe exactamente para eso."
    ],
    "alt_ctas": [
      "¿Cuánto tiempo llevas sin ver progreso real? Cuéntame en comentarios.",
      "Guarda esto antes de tu próximo entreno y dime si te suena conocido.",
      "Si quieres ver cómo sería un plan así para ti, escríbeme PLAN por DM y lo revisamos."
    ],
    "alt_captions": [
      "El plan genérico falla porque usa promedios.\n\nTú no eres un promedio.\n\nCuando empiezas conmigo, lo primero es entender cómo responde tu cuerpo específicamente. Historial, nivel, disponibilidad real. Con eso se construye el plan.\n\nY después ajustamos cada semana.\n\nGuarda esto si llevas tiempo entrenando sin avanzar.\n\n#planpersonalizado #hipertrofia #coachcolombia",
      "Llevas meses entrenando. El esfuerzo está. Los resultados no llegan.\n\nNo es flojera. Es que el plan no fue diseñado para ti.\n\nCuando trabajo con alguien, lo primero que hago es preguntar. Su historial, cómo responde, qué le cuesta. El plan sale de eso.\n\nSi eso es lo que te falta, escríbeme PLAN.\n\n#fitness #entrenamientopersonalizado #colombia",
      "¿Cambiaste de plan más de dos veces este año sin ver cambios?\n\nEl problema no es la disciplina. Es la individualización.\n\nUn plan que funciona parte de quién eres tú, no de quién es la persona promedio de un estudio.\n\nEso es lo que hacemos diferente.\n\nEscríbeme si quieres verlo aplicado a tu caso.\n\n#coachfitness #planentrenamiento #hipertrofia #wellcore"
    ]
  },
  "hashtags": {
    "sets": [
      {
        "name": "General + Nicho hipertrofia",
        "tags": ["#fitness", "#gym", "#workout", "#hipertrofia", "#planpersonalizado", "#entrenamientopersonalizado", "#musculos", "#progresion", "#fitnesslatam", "#gainz"]
      },
      {
        "name": "Local Colombia",
        "tags": ["#coachcolombia", "#fitnesscolombia", "#gymcolombia", "#entrenadorcolombia", "#fitlatam", "#wellcore"]
      },
      {
        "name": "Educativo basado en ciencia",
        "tags": ["#periodizacion", "#sobrecargaprogresiva", "#cienciafitness", "#fitnessbasadoenciencia", "#fisiologia", "#evidencebased", "#salud", "#bienestar"]
      },
      {
        "name": "Engagement comunidad",
        "tags": ["#fitnesscommunity", "#fitfam", "#workoutmotivation", "#fitnessjourney", "#progresoreal"]
      }
    ]
  }
}
JSON, true);

if ($content === null) {
    throw new \RuntimeException('JSON inválido: ' . json_last_error_msg());
}

// ============================================================
// 4. VALIDAR SCHEMA
// ============================================================
$validator = new DropSchemaValidator();
try {
    $validator->validate($content);
    echo "✅ Schema válido contra coach_drop_v1\n";
} catch (\App\Exceptions\Marketing\InvalidDropSchema $e) {
    echo "❌ Schema inválido:\n";
    foreach ($e->errors as $err) {
        echo "  - {$err['path']}: {$err['message']}\n";
    }
    throw $e;
}

// Validar cardinalidades del sistema
$days = array_map(fn($s) => $s['day'], $content['stories']);
if (count(array_unique($days)) !== 7) {
    throw new \RuntimeException('Stories: días no únicos. Deben ser LUN-DOM exactamente una vez.');
}

// ============================================================
// 5. CALCULAR SEMANA
// ============================================================
$monday = Carbon::now()->setISODate($isoYear, $isoWeek, 1)->startOfWeek();
$weekStartsOn = $monday->toDateString();
echo "ℹ️  Semana: {$isoYear}-W{$isoWeek} · lunes {$weekStartsOn}\n";

// ============================================================
// 6. UPSERT DROP
// ============================================================
DB::transaction(function () use ($coachId, $isoYear, $isoWeek, $weekStartsOn, $content, $profile) {
    $drop = CoachContentDrop::updateOrCreate(
        [
            'coach_id' => $coachId,
            'iso_year' => $isoYear,
            'iso_week' => $isoWeek,
        ],
        [
            'week_starts_on'   => $weekStartsOn,
            'status'           => DropStatus::InReview,
            'content'          => $content,
            'original_content' => $content,
            'intake_snapshot'  => $profile->toArray(),
            'schema_version'   => 'coach_drop_v1',
            'generated_at'     => now(),
        ]
    );
    echo "✅ Drop UPSERTed id={$drop->id}, status={$drop->status->value}\n";
});

// ============================================================
// 7. INVALIDAR CACHE
// ============================================================
Cache::forget("coach_drop_v3:{$coachId}:{$isoYear}:{$isoWeek}");
Cache::forget("coach_drop_history:{$coachId}");
echo "✅ Caches invalidados\n";

// ============================================================
// 8. NOTIFICACIÓN ADMIN
// ============================================================
if (class_exists(\App\Notifications\Marketing\NewDropPendingReview::class)) {
    Admin::whereIn('role', [UserRole::Admin, UserRole::Superadmin])
        ->each(fn($a) => $a->notify(new \App\Notifications\Marketing\NewDropPendingReview($coachId, $isoYear, $isoWeek)));
    echo "✅ Notificación enviada a admins\n";
} else {
    echo "ℹ️  Clase NewDropPendingReview no existe aún (M11) — skip\n";
}

// ============================================================
// 9. RESUMEN
// ============================================================
echo "\n=== RESUMEN ===\n";
echo "Coach:   {$coach->name} (id={$coachId})\n";
echo "Brand:   {$profile->brand_name}\n";
echo "Semana:  {$isoYear}-W{$isoWeek} (lunes {$weekStartsOn})\n";
echo "Reels:   2 (1 educativo + 1 conversión)\n";
echo "Stories: 7 (LUN→DOM)\n";
echo "Status:  in_review — esperar approval de Daniel en /admin/marketing/queue\n";
echo "Assets:  subir los 5 JPGs via UI admin en el drop review\n";
echo "===============\n";
