<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

/**
 * Jose Leonel Vanegas — coach_id=10
 * Drop W18-2026 (Apr 27 → May 3)
 * Tema: WellCore en tu rutina diaria — así se produce el resultado
 *
 * PRE-REQUISITO: leonel_intake_insert.php debe haberse corrido primero.
 *
 * Ejecutar en EasyPanel consola:
 *   cd /code && php artisan tinker < /tmp/leonel_drop_w18_insert.php
 */

use App\Models\Admin;
use App\Models\CoachContentDrop;
use App\Models\CoachMarketingProfile;
use App\Enums\Marketing\DropStatus;
use App\Enums\UserRole;
use App\Services\Marketing\DropSchemaValidator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

$coachId = 10;
$isoYear = 2026;
$isoWeek = 18;

// ============================================================================
// CONTENIDO DEL DROP — coach_drop_v1
// ============================================================================
$content = [
    'schema_version' => 'coach_drop_v1',

    // ─────────────────────────────────────────────────────────────── BRIEF
    'brief' => [
        'title'          => 'Semana 18 — El sistema WellCore en acción',
        'objective'      => 'Posicionar a Leonel como el coach con el sistema de seguimiento más completo del mercado al mostrar WellCore en acción real. Generar 10+ DMs calificados con "MÉTODO" + 3 conversiones de asesoría.',
        'priority_offer' => 'metodo',
        'key_message'    => 'Improvisar no es entrenar. Trabajar con **un sistema real** cambia todo.',
        'target_metric'  => 'Guardados en reel #1 + DMs entrantes con "MÉTODO" + nuevos seguidores semana 18',
        'weekly_theme'   => 'WellCore en tu rutina diaria: así se produce el resultado',
        'framing_copy'   => 'Esta semana no hablo. Muestro. El sistema que uso con mis clientes — la plataforma, el registro, el plan de comida, el acompañamiento real. Si nunca has visto cómo funciona por dentro, esta semana lo entiendes.',
    ],

    // ─────────────────────────────────────────────────────────────── REELS (2)
    'reels' => [

        // REEL 1 — Educativo visual (55-65s, 100% sin voz — solo texto en pantalla)
        [
            'key'   => 'reel_1',
            'type'  => 'educativo',
            'title' => 'Un día real con WellCore: de la app al resultado',
            'format_meta' => [
                'duration_sec_min' => 55,
                'duration_sec_max' => 65,
                'platforms'        => ['instagram', 'tiktok'],
                'bpm_hint'         => '88-96',
            ],
            'hook' => [
                'text'      => 'Esto es lo que pasa cuando dejas de improvisar y empiezas a trabajar con **un plan real**.',
                'rationale' => 'Texto en pantalla los primeros 3s — sin voz. El viewer ve algo que nunca antes ha visto: el interior de una plataforma de coaching real en acción. Retiene atención por curiosidad genuina, no por shock.',
            ],
            'timecode_table' => [
                [
                    'time'       => '00:00-00:04',
                    'dialogue'   => '[Texto pantalla] "Lunes. Mismo sistema. Cada semana."',
                    'visual'     => 'Coach llegando al gym, abre WellCore en el cel. Plano sobre el hombro mostrando la pantalla de inicio de la app.',
                    'edit_notes' => 'Fade in desde negro. Texto Oswald uppercase blanco con sombra roja, fade-in suave. Beat de intro arranca en este corte.',
                ],
                [
                    'time'       => '00:04-00:10',
                    'dialogue'   => '[Texto pantalla] "Rutina del día. Ya estaba lista."',
                    'visual'     => 'Pantalla del cel mostrando la rutina del día en WellCore — lista de ejercicios, series y reps claramente visibles. Coach desplazando suavemente la rutina.',
                    'edit_notes' => 'Pan lento hacia la pantalla del cel. Zoom suave sobre la lista de ejercicios. Texto overlay fades in con la misma tipografía.',
                ],
                [
                    'time'       => '00:10-00:22',
                    'dialogue'   => '[Texto pantalla] "Cada serie, registrada. Nada de memoria."',
                    'visual'     => 'Montaje: coach haciendo ejercicio (press de banca o sentadilla), para entre series, abre el app y registra el peso. Close-up manos tocando la pantalla para ingresar el dato.',
                    'edit_notes' => 'Cortes rápidos al beat: clip ejercicio → close-up pantalla app → ejercicio → pantalla. Mogrt con el peso animado "80 kg · 3×8" aparece sobre el plano del cel.',
                ],
                [
                    'time'       => '00:22-00:28',
                    'dialogue'   => '[Texto pantalla] "2 minutos de descanso. El app lleva la cuenta."',
                    'visual'     => 'Coach sentado descansando, cel en mano mostrando el timer de descanso de WellCore corriendo. La cuenta regresiva es claramente visible en pantalla.',
                    'edit_notes' => 'Slow-mo 50% del timer corriendo. Breathing cut — plano más amplio y calmado. Este es el único plano estático del reel.',
                ],
                [
                    'time'       => '00:28-00:36',
                    'dialogue'   => '[Texto pantalla] "Ejercicio 1. Ejercicio 2. Ejercicio 3. Sin improvisar nada."',
                    'visual'     => 'Montaje rápido: de un ejercicio al siguiente, con micro-cortes al app entre cada uno mostrando el avance de la rutina (barra de progreso o checkmarks por ejercicio completado).',
                    'edit_notes' => 'Quick cuts al ritmo del beat — máximo 1.5s por clip. Progress bar de la rutina visible en transición. Ritmo acelerado aquí para generar energía.',
                ],
                [
                    'time'       => '00:36-00:44',
                    'dialogue'   => '[Texto pantalla] "Entrenamiento guardado. Nuevo récord personal."',
                    'visual'     => 'Coach completa el último ejercicio. Abre el app y guarda el entrenamiento — aparece el modal de WellCore con resumen del entreno y el badge de PR destacado.',
                    'edit_notes' => 'Zoom in al modal en pantalla. Texto "NUEVO PR" en rojo WellCore con glow pulse de 0.5s. Este es el momento más satisfactorio del reel — beat peak aquí.',
                ],
                [
                    'time'       => '00:44-00:54',
                    'dialogue'   => '[Texto pantalla] "En casa. La nutrición también está en el plan."',
                    'visual'     => 'Coach en cocina preparando comida. Cel apoyado en la encimera mostrando el plan de alimentación de WellCore — macros del día visibles. Coach sirve el plato mientras el app está visible.',
                    'edit_notes' => 'Cambio de ambiente: edición más calmada. B-roll cocina en tonos cálidos. Cel en foco con plan de nutrición legible. Texto overlay fades in.',
                ],
                [
                    'time'       => '00:54-01:05',
                    'dialogue'   => '[Texto pantalla] "Esto es WellCore. Así gestiono mis asesorías." → "Descubre el sistema → Link en bio"',
                    'visual'     => 'Shot final: coach sostiene el cel mostrando el dashboard de WellCore. Fade suave a negro con logo WellCore animado y texto CTA.',
                    'edit_notes' => 'Fade a negro gradual. Logo WellCore aparece con reveal de izquierda a derecha. Texto "Descubre el sistema → Link en bio" fade-in en Raleway. Beat decae para cerrar.',
                ],
            ],
            'caption'          => "Así se ve un entrenamiento cuando tienes un sistema real.\n\nSin improvisar. Sin olvidar los pesos. Sin preguntarte si progresas.\n\n— Rutina del día lista en la app\n— Registro de pesos por serie\n— Timer de descanso automático\n— Plan de alimentación incluido\n— PRs detectados automáticamente\n\nGuarda este video y compártelo con alguien que todavía entrena sin sistema.\n\n#wellcore #coachingonline #entrenamientoonline #fitness #hipertrofia #sistemaentrenamiento #registrodeentrenamiento #workout",
            'music_note'       => 'Beat cinematográfico 88-96 BPM, electrónica instrumental tipo documental deportivo. Sin letra. Cortes al beat en cada cambio de escena. Volumen 65% todo el reel — no hay voz. Peak de beat coincide con el frame del modal de PR.',
            'production_notes' => 'Reel 100% visual — sin voz. Grabar vertical 9:16, iluminación natural o cenital en gym. Pantalla del cel al 100% de brillo — debe ser legible en video. Secuencia cronológica: gym → app → entreno → PRs → casa → comida → cierre. Outfit oscuro, sin logos externos. Mínimo 2 takes de cada escena. El cel no puede tener notificaciones visibles — activar modo no molestar antes de grabar.',
        ],

        // REEL 2 — Conversión (25-35s, voz)
        [
            'key'   => 'reel_2',
            'type'  => 'conversion',
            'title' => 'Si llevas meses entrenando sin saber si progresaste',
            'format_meta' => [
                'duration_sec_min' => 25,
                'duration_sec_max' => 35,
                'platforms'        => ['instagram'],
                'bpm_hint'         => '85-95',
            ],
            'hook' => [
                'text'      => 'Si llevas meses entrenando pero no sabes exactamente qué **progresaste esta semana**, tengo algo para contarte.',
                'rationale' => 'Pain específico y medible — no "no ves resultados" sino "no sabes qué progresaste esta semana". Esto apunta al problema real: falta de sistema de medición. El viewer que se identifica queda atrapado en los primeros 3s.',
            ],
            'timecode_table' => [
                [
                    'time'       => '00:00-00:03',
                    'dialogue'   => 'Si llevas meses entrenando pero no sabes exactamente qué progresaste esta semana, esto es para ti.',
                    'visual'     => 'Coach a cámara, plano medio, fondo gym oscuro. Expresión directa, sin sonrisa forzada.',
                    'edit_notes' => 'Texto pain overlay en rojo animado con la frase clave. Subtítulos activos desde el primer segundo.',
                ],
                [
                    'time'       => '00:03-00:13',
                    'dialogue'   => 'Yo trabajo con personas así. El problema no es la constancia — es que entrenan sin datos. Sin registro. Sin un plan que ajuste semana a semana con lo que realmente pasó.',
                    'visual'     => 'Coach hablando, cortes a shots del app de WellCore mostrando datos: tabla de pesos, historial de sesiones, progresión.',
                    'edit_notes' => 'Subtítulos activos. Corte al app en "sin datos" y "sin registro" — mostrar exactamente eso en pantalla. Color grade: contraste alto, oscuro.',
                ],
                [
                    'time'       => '00:13-00:23',
                    'dialogue'   => 'Método Leonel es la asesoría online donde tienes rutina personalizada, registro de pesos, plan de alimentación y yo haciendo los ajustes cada quince días con datos reales de tu entrenamiento.',
                    'visual'     => 'Shots del app: plan de entrenamiento personalizado, registro de pesos, plan de nutrición, comunicación coach-cliente.',
                    'edit_notes' => 'Mogrt con 4 bullets animados uno por uno: Rutina / Registro / Nutrición / Seguimiento. Cada bullet aparece al ritmo del diálogo.',
                ],
                [
                    'time'       => '00:23-00:32',
                    'dialogue'   => 'Si quieres entrar al próximo grupo, escríbeme MÉTODO por DM y vemos si es para ti.',
                    'visual'     => 'Coach a cámara, expresión cálida y directa. Plano medio.',
                    'edit_notes' => 'Texto "MÉTODO" grande overlay rojo WellCore con pulse de entrada. Subtítulo de cierre. Fade a negro suave.',
                ],
            ],
            'caption'          => "Si entrenas hace meses sin saber exactamente qué progresaste esta semana, no es flojera. Es falta de sistema.\n\nMétodo Leonel:\n— Rutina 100% personalizada para ti\n— Registro de pesos y PRs semana a semana\n— Plan de alimentación ajustado a tus metas\n— Seguimiento real cada 15 días conmigo\n\nSi quieres entrar al próximo grupo, escríbeme MÉTODO por DM y vemos si es para ti.\n\n#metodoleonel #coachingonline #entrenamientoonline #fitness #hipertrofia",
            'music_note'       => 'Beat 88-92 BPM instrumental, cálido, sin letra. No compite con la voz. Volumen 25% durante todo el reel. Subtítulos siempre activos.',
            'production_notes' => 'Grabar en gym con luz frontal o cenital. Plano medio, cámara a altura de ojos. 3 takes del hook y 3 del CTA con distintas inflexiones. Subtítulos obligatorios — 60% de la audiencia ve sin sonido. Sin ruido de fondo en el audio.',
        ],
    ],

    // ─────────────────────────────────────────────────────────────── STORIES (7)
    'stories' => [
        [
            'day'    => 'LUN',
            'pillar' => 'activacion',
            'slides' => [
                [
                    'kind'        => 'text',
                    'text'        => 'Lunes. Primera sesión de la semana registrada en el app. Tip: antes de entrar al gym, abre el plan del día. Si sabes qué vas a hacer antes de llegar, usas el tiempo mejor y empiezas con foco — no con duda.',
                    'visual_hint' => 'Coach en la entrada del gym, abriendo WellCore en el cel. Plano medio.',
                    'sticker'     => 'none',
                ],
                [
                    'kind'        => 'template',
                    'text'        => "¿Ya tienes tu entrenamiento de hoy planificado?\n\nA) Sí, lo tengo claro ✅\nB) Voy viendo qué hago 😅",
                    'visual_hint' => 'Fondo oscuro con gradiente sutil, texto centrado. Sin foto.',
                    'sticker'     => 'poll',
                ],
            ],
            'dm_followup_hint' => "A los que respondan B → DM breve: '¿Quieres que te arme algo para hoy?'",
        ],
        [
            'day'    => 'MAR',
            'pillar' => 'nutricion',
            'slides' => [
                [
                    'kind'        => 'visual',
                    'text'        => "Almuerzo de hoy. Todo calculado en el plan:\n\nPollo 35g proteína · Arroz integral energía sostenida · Aguacate grasa buena · Ensalada volumen sin calorías.\n\n~520 kcal. Llenado 4+ horas. Sin necesidad de contar nada a mano — el app lo tiene calculado.",
                    'visual_hint' => 'Foto cenital del plato real, luz natural. Cel con el plan de nutrición de WellCore visible al lado del plato.',
                    'sticker'     => 'none',
                ],
            ],
            'dm_followup_hint' => "Si alguien pregunta '¿cómo calculo mis macros?' → responder con CTA suave a DM 'MÉTODO'.",
        ],
        [
            'day'    => 'MIE',
            'pillar' => 'spotlight',
            'slides' => [
                [
                    'kind'        => 'text',
                    'text'        => 'Un cliente lleva 10 semanas con Método Leonel. Cuando llegó, entrenaba 4 veces por semana sin ver cambios. Ajustamos el volumen, empezamos a registrar pesos, estructuramos la alimentación. Esta semana hizo su primer PR en press banca a 80 kg. Eso pasa cuando el plan es tuyo, no de una app genérica.',
                    'visual_hint' => 'Captura del PR en WellCore (con permiso del cliente, sin cara visible). Fondo gym oscuro.',
                    'sticker'     => 'none',
                ],
                [
                    'kind'        => 'text',
                    'text'        => "En sus propias palabras: 'Por primera vez sé exactamente qué hice, qué pesé y qué mejoré semana a semana.'",
                    'visual_hint' => 'Texto sobre fondo oscuro, tipografía limpia. Sin adornos.',
                    'sticker'     => 'none',
                ],
            ],
            'dm_followup_hint' => "Cualquier respuesta tipo 'qué cambio' o 'cómo empiezo' → responder con CTA suave: 'Escríbeme MÉTODO por DM y vemos si aplica para ti'.",
        ],
        [
            'day'    => 'JUE',
            'pillar' => 'bts',
            'slides' => [
                [
                    'kind'        => 'visual',
                    'text'        => 'Preparando los planes de la próxima semana para mis clientes. Cada ajuste tarda ~40 min por persona. Reviso los datos de la semana — pesos levantados, adherencia al plan, feedback del cliente. Así sé qué cambiar y qué mantener. Eso es seguimiento real, no suposiciones.',
                    'visual_hint' => 'Manos sobre laptop con el dashboard de WellCore abierto mostrando datos de clientes. Café o mate al lado. Luz cálida.',
                    'sticker'     => 'none',
                ],
            ],
            'dm_followup_hint' => 'Sin follow-up obligatorio. Responder a quien comente o pregunte sobre el proceso.',
        ],
        [
            'day'    => 'VIE',
            'pillar' => 'qa',
            'slides' => [
                [
                    'kind'        => 'text',
                    'text'        => 'Viernes de responder preguntas. Tengo 30 minutos. Manda tu duda de entrenamiento, nutrición, progresión de pesos, lo que necesites — respondo todo hoy sin filtro.',
                    'visual_hint' => 'Coach a cámara con caja de preguntas activa. Expresión abierta, relajada.',
                    'sticker'     => 'question',
                ],
            ],
            'dm_followup_hint' => "Guardar preguntas técnicas recurrentes → temas de próximos reels educativos gratuitos.",
        ],
        [
            'day'    => 'SAB',
            'pillar' => 'motivacion',
            'slides' => [
                [
                    'kind'        => 'text',
                    'text'        => "La diferencia entre quien progresa y quien no: uno registra, ajusta y vuelve. El otro entrena de memoria, sin saber si mejoró o solo se cansó.\n\nLa consistencia sola no alcanza. La consistencia con datos es lo que mueve el marcador.",
                    'visual_hint' => 'Fondo oscuro, tipografía Oswald grande. Sin foto. Texto como único protagonista.',
                    'sticker'     => 'slider',
                ],
            ],
            'dm_followup_hint' => "A quienes respondan bajo (1-4 en slider) → DM: '¿Qué te frenó esta semana?'",
        ],
        [
            'day'    => 'DOM',
            'pillar' => 'reset',
            'slides' => [
                [
                    'kind'        => 'text',
                    'text'        => 'Domingo. El día que más gente ignora y más importa. El músculo no crece en el gym. Crece aquí: en el descanso, en la comida de hoy, en las 8 horas que duermes esta noche. Hoy: camina, estira, prepara tu semana. Eso también es parte del plan.',
                    'visual_hint' => 'Foto de piernas descansando en sofá o espalda en cama. Luz cálida, ambiente de recuperación.',
                    'sticker'     => 'none',
                ],
                [
                    'kind'        => 'template',
                    'text'        => "Para arrancar bien el lunes:\n✓ Revisa tu plan de la semana en el app\n✓ Prepara ropa del gym la noche del domingo\n✓ Planea al menos 3 comidas de la semana\n✓ Duerme antes de las 11pm\n\nLa disciplina se construye la noche anterior, no el día de.",
                    'visual_hint' => 'Checklist visual sobre fondo oscuro. Sin foto. Cada ítem con checkmark verde.',
                    'sticker'     => 'slider',
                ],
            ],
            'dm_followup_hint' => "A quienes respondan 1-4 en el slider de '¿cómo fue tu semana?' → DM con '¿Qué pasó esta semana?' + oferta de consulta inicial.",
        ],
    ],

    // ─────────────────────────────────────────────────────────────── CHECKLIST
    'checklist' => [
        'phases' => [
            [
                'key'   => 'pre',
                'title' => 'Pre-producción',
                'items' => [
                    [
                        'title'  => 'Planear la secuencia de escenas del reel visual',
                        'detail' => 'Definir exactamente qué grabar en cada momento del día de gym. El reel #1 sigue el orden: gym → app → entreno → pesos → timer → PR → casa → comida. No improvisar la secuencia.',
                    ],
                    [
                        'title'  => 'Preparar el cel para grabar',
                        'detail' => 'Modo No Molestar activo. Brillo al 100%. Notificaciones y chats cerrados. La pantalla del app debe verse perfectamente en video.',
                    ],
                    [
                        'title'  => 'Seleccionar y descargar la música antes de grabar',
                        'detail' => 'Verificar disponibilidad en Instagram y TikTok. Para reel #1: beat instrumental cinematográfico 88-96 BPM. Para reel #2: beat cálido 88-92 BPM.',
                        'subitems' => ['Verificar que no tenga restricción de derechos en IG', 'Ajustar cortes de clips al beat antes de exportar'],
                    ],
                    [
                        'title'  => 'Memorizar el guion de reel #2',
                        'detail' => 'El hook y el CTA deben sonar naturales. Ensayar frente al espejo. El hook tiene 3s — cada palabra cuenta.',
                    ],
                ],
            ],
            [
                'key'   => 'cam',
                'title' => 'Grabación',
                'items' => [
                    [
                        'title'  => 'Cámara vertical 9:16 mínimo 1080p',
                        'detail' => 'Para reel y stories. Si grabas horizontal, la calidad se pierde al recortar.',
                    ],
                    [
                        'title'  => 'Pantalla del cel legible en todo momento',
                        'detail' => 'El reel #1 vive en las pantallas del app. Brillo al máximo. Sostener el cel firmemente — no puede temblar al enfocar la pantalla.',
                    ],
                    [
                        'title'  => 'Iluminación frontal en el gym',
                        'detail' => 'Luz frontal o cenital. Nunca contra la ventana. Si hay zonas oscuras en el gym, evitarlas — la pantalla del cel no se verá.',
                    ],
                    [
                        'title'  => '3 takes mínimo de cada escena',
                        'detail' => 'Especialmente: modal de PR (difícil de repetir), registro de pesos (debe verse el número), y CTA del reel #2.',
                    ],
                ],
            ],
            [
                'key'   => 'edit',
                'title' => 'Edición',
                'items' => [
                    [
                        'title'  => 'Reel #1: cortes al beat, sin voz',
                        'detail' => 'Marcar los beats del audio antes de pegar los clips. Cada cambio de escena coincide con el beat. El plano del timer es el único lento (slow-mo 50%).',
                    ],
                    [
                        'title'  => 'Texto Oswald uppercase en cada escena del reel #1',
                        'detail' => 'Cada escena lleva su texto en pantalla. Ver dialogo del timecode_table — esas son las frases exactas. Tamaño grande, sombra sutil, posición consistente.',
                    ],
                    [
                        'title'  => 'Subtítulos activos en reel #2',
                        'detail' => 'Obligatorio. 60% de la audiencia ve sin sonido. Sin subtítulos, pierdes más de la mitad del alcance.',
                    ],
                    [
                        'title'  => 'Color grade: contraste +15%, negro profundo',
                        'detail' => 'Estética WellCore: oscuro, cinematográfico, limpio. Vignette sutil en los bordes. Tonos de piel naturales.',
                    ],
                ],
            ],
            [
                'key'   => 'pub',
                'title' => 'Publicación',
                'items' => [
                    [
                        'title'  => 'Caption reel #1: empezar con la frase fuerte, no con saludo',
                        'detail' => '"Así se ve un entrenamiento cuando tienes un sistema real." — ese es el primer renglón. El algoritmo lee los primeros 125 caracteres para mostrar el preview.',
                    ],
                    [
                        'title'  => 'Caption reel #2: CTA al final con palabra clave MÉTODO',
                        'detail' => 'Siempre terminar con: "Escríbeme MÉTODO por DM y vemos si es para ti."',
                    ],
                    [
                        'title'  => 'Hashtags del set activo',
                        'detail' => 'Máximo 15. Mezcla: grande (#fitness) + medio (#coachingonline) + nicho (#sistemaentrenamiento). Ver sets del drop.',
                    ],
                    [
                        'title'  => 'Horario óptimo de publicación',
                        'detail' => '7-9am o 6-8pm hora Colombia. Mar-Jue mayor alcance. Reel #1 publicar primero (lunes o martes), reel #2 jueves o viernes.',
                    ],
                ],
            ],
        ],
    ],

    // ─────────────────────────────────────────────────────────────── BANCO
    'bank' => [
        'alt_hooks' => [
            '¿Cuánto pesaste en tu última sentadilla hace 3 semanas? Si no lo sabes exactamente, esto te cambia la perspectiva.',
            'Lo que nadie ve detrás de un resultado real: el sistema de registro que funciona semana a semana sin fallar.',
            'Tres meses con el mismo peso en barra no es plateau. Es falta de sistema de progresión.',
            'Así se ve la plataforma que uso para llevar el seguimiento real de mis clientes semana a semana.',
            'El gym sin datos es esfuerzo sin dirección. Te muestro cómo cambia eso con **un sistema real**.',
        ],
        'alt_ctas' => [
            '¿Tienes registrados los pesos exactos de tu última semana? Cuéntame en comentarios.',
            'Guarda este video y compártelo con alguien que todavía entrena sin registrar nada.',
            'Si quieres que yo lleve tu seguimiento con datos reales, escríbeme MÉTODO por DM.',
        ],
        'alt_captions' => [
            "Un entrenamiento sin registro es un entrenamiento sin memoria. Tu progreso vive en los datos — no en la impresión del momento.\n\nLo que mides, mejora. Lo que no mides, se repite.\n\nGuarda esto.\n\n#wellcore #fitness #progresion #sistemaentrenamiento",
            "¿Cuánto levantaste hace 4 semanas en el banco? Si no lo sabes, ese es el primer problema. El segundo: cómo progresar la próxima semana sin esa información.\n\nEl registro no es obsesión — es el único mapa que te dice si avanzas o caminas en círculos.\n\n#coachingonline #entrenamientoonline #tracking #fitness",
            "Así se ve trabajar con estructura real:\n— Rutina personalizada y lista antes de entrar al gym\n— Pesos registrados por serie\n— Alimentación calculada\n— Ajuste quincenal con datos reales\n\nSi esto es lo que te falta, hablemos.\n\nEscríbeme MÉTODO por DM.\n\n#metodoleonel #coaching #hipertrofia",
        ],
    ],

    // ─────────────────────────────────────────────────────────────── HASHTAGS
    'hashtags' => [
        'sets' => [
            [
                'name' => 'General + Coaching Online',
                'tags' => ['#fitness', '#gym', '#workout', '#wellcore', '#coachingonline', '#entrenamientoonline', '#sistemaentrenamiento', '#hipertrofia', '#recomposicion'],
            ],
            [
                'name' => 'Local Colombia',
                'tags' => ['#fitnesscolombia', '#gymcolombia', '#coachcolombia', '#entrenadorcolombia', '#fitlatam', '#colombiafit', '#entrenamiento'],
            ],
            [
                'name' => 'Educativo y Registro',
                'tags' => ['#progresion', '#sobrecargaprogresiva', '#registroentrenamiento', '#trackingfitness', '#bienestar', '#salud', '#evidencebased'],
            ],
            [
                'name' => 'Comunidad Engagement',
                'tags' => ['#fitnesscommunity', '#fitfam', '#workoutmotivation', '#fitnessjourney', '#metodoleonel'],
            ],
        ],
    ],
];

// ============================================================================
// 1. VERIFICACIONES
// ============================================================================

$coach = Admin::find($coachId);
if (! $coach) {
    throw new \RuntimeException("Admin id={$coachId} no existe.");
}
if ($coach->role !== UserRole::Coach) {
    throw new \RuntimeException("Admin id={$coachId} no tiene role=Coach (tiene {$coach->role->value}).");
}

$profile = CoachMarketingProfile::where('coach_id', $coachId)->first();
if (! $profile) {
    throw new \RuntimeException("Coach id={$coachId} no tiene CoachMarketingProfile. Correr leonel_intake_insert.php primero.");
}
if ($profile->completed_at === null) {
    throw new \RuntimeException("Profile de coach_id={$coachId} no está completo (completed_at=null).");
}

// ============================================================================
// 2. VALIDAR JSON
// ============================================================================
$validator = new DropSchemaValidator();
try {
    $validator->validate($content);
    echo "✅ Schema validado.\n";
} catch (\App\Exceptions\Marketing\InvalidDropSchema $e) {
    echo "❌ Schema inválido:\n";
    foreach ($e->errors as $err) {
        echo "  - {$err['path']}: {$err['message']}\n";
    }
    throw $e;
}

// ============================================================================
// 3. CALCULAR week_starts_on
// ============================================================================
$monday = Carbon::now()->setISODate($isoYear, $isoWeek, 1)->startOfWeek();
$weekStartsOn = $monday->toDateString();

// ============================================================================
// 4. UPSERT
// ============================================================================
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

// ============================================================================
// 5. INVALIDAR CACHES
// ============================================================================
Cache::forget("coach_drop_v3:{$coachId}:{$isoYear}:{$isoWeek}");
Cache::forget("coach_drop_history:{$coachId}");
echo "✅ Caches invalidados.\n";

// ============================================================================
// 6. NOTIFICAR ADMIN
// ============================================================================
if (class_exists(\App\Notifications\Marketing\NewDropPendingReview::class)) {
    Admin::whereIn('role', [UserRole::Admin, UserRole::Superadmin])
        ->each(fn ($a) => $a->notify(new \App\Notifications\Marketing\NewDropPendingReview($coachId, $isoYear, $isoWeek)));
    echo "✅ Notificación enviada a admins.\n";
} else {
    echo "ℹ️  NewDropPendingReview no existe aún (M11). Skip notificación.\n";
}

// ============================================================================
// 7. RESUMEN
// ============================================================================
echo "\n=== RESUMEN ===\n";
echo "Coach: {$coach->name} (id={$coachId})\n";
echo "Brand: {$profile->brand_name}\n";
echo "Semana: {$isoYear}-W{$isoWeek} (lunes {$weekStartsOn})\n";
echo "Status: in_review — esperando approval de Daniel\n";
echo "URL admin: panel.wellcorefitness.com/admin/marketing/queue\n";
echo "===============\n";
