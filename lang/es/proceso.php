<?php

/*
 |---------------------------------------------------------------------
 | /proceso · long-form storytelling v2 — 5 steps con mockup viz.
 |
 | Reorganizado Sprint 2 (porting v2) — antes 4 fases, ahora 5 steps
 | con visualizaciones mockup intercaladas (form, coaches, plan PDF,
 | check-in dashboard, journey progress).
 |
 | Voz: latino neutro estricto (tú/puedes/quieres). NO voseo argentino.
 | Step2 NUNCA menciona IA/algoritmo/ML — usa "sistema de match" o
 | "compatibilidad por afinidad".
 | Datos viz: TODOS demo — disclaimer obligatorio bajo cada uno.
 |---------------------------------------------------------------------
 */

return [

    // -------------------------------------------------------------------------
    // Meta — head
    // -------------------------------------------------------------------------
    'meta_title'       => 'El Proceso · Cómo trabajamos contigo | WellCore Fitness',
    'meta_description' => 'Diagnóstico, asignación de coach, plan en tu panel, check-ins y seguimiento real. Coaching humano 1:1, sin algoritmos opacos ni promesas vacías.',

    // -------------------------------------------------------------------------
    // Sidebar editorial (≥1024px)
    // -------------------------------------------------------------------------
    'sidebar' => [
        'subtitle'       => 'El Proceso · 2026',
        'progress_label' => 'Progreso',
        'cta'            => 'EMPEZAR',
    ],

    // -------------------------------------------------------------------------
    // Capítulos / pasos (sidebar nav + chapter pill)
    // -------------------------------------------------------------------------
    'chapters' => [
        'cap00' => [
            'pill'       => 'Cap 00 · Portada',
            'nav_title'  => 'Portada',
        ],
        's1' => [
            'pill'       => 'Paso 01 · Diagnóstico',
            'nav_title'  => 'Diagnóstico',
            'short'      => '01',
            'label'      => 'DIAGNÓSTICO',
        ],
        's2' => [
            'pill'       => 'Paso 02 · El Match',
            'nav_title'  => 'El Match',
            'short'      => '02',
            'label'      => 'EL MATCH',
        ],
        's3' => [
            'pill'       => 'Paso 03 · Tu Plan',
            'nav_title'  => 'Tu Plan',
            'short'      => '03',
            'label'      => 'TU PLAN',
        ],
        's4' => [
            'pill'       => 'Paso 04 · Check-ins',
            'nav_title'  => 'Check-ins',
            'short'      => '04',
            'label'      => 'CHECK-INS',
        ],
        's5' => [
            'pill'       => 'Paso 05 · Resultados',
            'nav_title'  => 'Resultados',
            'short'      => '05',
            'label'      => 'RESULTADOS',
        ],
        'cta' => [
            'pill'       => 'Empezar el proceso',
            'nav_title'  => 'Empezar',
        ],
    ],

    // -------------------------------------------------------------------------
    // Hero
    // -------------------------------------------------------------------------
    'hero' => [
        'eyebrow'      => 'WELLCORE FITNESS · EL PROCESO',
        'title_html'   => 'EL<br><span class="accent">CAMINO.</span>',
        'sub'          => '5 pasos. Sin atajos. Sin promesas vacías.',
        'scroll_hint'  => 'Desliza para recorrer el proceso',
    ],

    // -------------------------------------------------------------------------
    // Manifesto (debajo del hero, antes de Step 1)
    // -------------------------------------------------------------------------
    'manifesto' => [
        'kicker' => 'PUNTO DE PARTIDA',
        'body'   => 'No vendemos motivación. Vendemos un proceso. Cinco pasos que transforman tus datos reales en un plan que tu cuerpo puede ejecutar y que tu coach humano ajusta según los check-ins que hacés. No hay magia. No hay algoritmo. Hay método y trabajo 1:1.',
    ],

    // -------------------------------------------------------------------------
    // Step 01 — Diagnóstico
    // -------------------------------------------------------------------------
    'step1' => [
        'meta_index'   => 'PASO 01',
        'meta_timing'  => '5–8 MIN',
        'title_html'   => 'DIAGNÓS-<br>TICO<br>INICIAL',
        'desc'         => 'Todo empieza con un cuestionario web de 8 pasos: tu objetivo, biometría (peso, estatura, edad), experiencia entrenando, lesiones, nutrición y hábitos. Sin trampa, sin compromiso, sin tarjeta de crédito.',
        'detail'       => 'Sin pago hasta confirmar match con coach',
        'viz' => [
            'header_label' => 'Diagnóstico inicial',
            'duration'     => '5 min',
            'question'     => '¿Cuál es tu objetivo principal?',
            'opts' => [
                'Pérdida de grasa',
                'Hipertrofia muscular',
                'Rendimiento deportivo',
            ],
            'slider_q'      => '¿Cuántos días disponibles por semana?',
            'slider_labels' => [
                '1 día', '3 días', '6 días',
            ],
            'submit'        => 'COMENZAR →',
        ],
        'disclaimer' => 'Vista de ejemplo · datos demostrativos',
    ],

    // -------------------------------------------------------------------------
    // Step 02 — Match con coach
    // -------------------------------------------------------------------------
    'step2' => [
        'meta_index'   => 'PASO 02',
        'meta_timing'  => '24–48 H',
        'title_html'   => 'COACH<br>HUMANO<br>1:1',
        'desc'         => 'Nuestro equipo revisa tu inscripción y te asigna un coach especializado en tu objetivo. La asignación es manual: priorizamos especialidad, género y zona horaria. No es un algoritmo opaco — es una decisión humana.',
        'detail'       => 'Asignación manual · Coach experto en tu objetivo',
        'best_label'   => 'TU COACH ASIGNADO',
        'coaches' => [
            [
                'initials' => 'LC',
                'name'     => 'COACH SENIOR',
                'spec'     => 'HIPERTROFIA',
                'match'    => null,
                'best'     => false,
            ],
            [
                'initials' => 'MA',
                'name'     => 'TU COACH',
                'spec'     => 'PÉRDIDA GRASA',
                'match'    => null,
                'best'     => true,
            ],
            [
                'initials' => 'SO',
                'name'     => 'COACH SENIOR',
                'spec'     => 'RENDIMIENTO',
                'match'    => null,
                'best'     => false,
            ],
        ],
        'disclaimer' => 'Vista de ejemplo · representativa de los perfiles del equipo',
    ],

    // -------------------------------------------------------------------------
    // Step 03 — Plan personalizado
    // -------------------------------------------------------------------------
    'step3' => [
        'meta_index'   => 'PASO 03',
        'meta_timing'  => '3–5 DÍAS',
        'title_html'   => 'PLAN<br>EN TU<br>PANEL.',
        'desc'         => 'Tu coach diseña entrenamiento, nutrición y hábitos según tu inscripción y la conversación inicial. Lo cargamos directo a tu panel cliente — sin PDFs sueltos. Cada variable se ajusta a tus datos.',
        'detail'       => 'Entrenamiento · Nutrición · Hábitos',
        'viz' => [
            'pdf_filename'      => 'TU PANEL · SEMANA 1',
            'pdf_meta'          => 'ACTUALIZADO POR TU COACH',
            'pdf_download'      => 'ABRIR PANEL',
            'th_day'            => 'DÍA',
            'th_session'        => 'SESIÓN',
            'th_vol'            => 'VOL',
            'th_kcal'           => 'KCAL',
            'th_type'           => 'TIPO',
            'rows' => [
                ['day' => 'LUN', 'session' => 'Upper A',     'vol' => '18', 'kcal' => '2,340', 'type' => 'FUERZA', 'type_color' => 'red',   'focus' => true],
                ['day' => 'MAR', 'session' => 'Cardio LISS', 'vol' => '0',  'kcal' => '2,100', 'type' => 'CARDIO', 'type_color' => 'green', 'focus' => false],
                ['day' => 'MIÉ', 'session' => 'Lower A',     'vol' => '20', 'kcal' => '2,340', 'type' => 'FUERZA', 'type_color' => 'red',   'focus' => true],
                ['day' => 'JUE', 'session' => 'Descanso',    'vol' => '—',  'kcal' => '1,900', 'type' => 'REST',   'type_color' => 'green', 'focus' => false],
                ['day' => 'VIE', 'session' => 'Upper B',     'vol' => '16', 'kcal' => '2,340', 'type' => 'FUERZA', 'type_color' => 'red',   'focus' => true],
            ],
        ],
        'disclaimer' => 'Vista de ejemplo · datos demostrativos · tu plan real vive en el panel cliente',
    ],

    // -------------------------------------------------------------------------
    // Pull-quote brutal (entre Step 3 y Step 4)
    // -------------------------------------------------------------------------
    'pullquote' => [
        'label'     => 'PUNTO DE QUIEBRE · MITAD DEL CAMINO',
        'text_html' => 'NO MÁS<br>APPS <em>GENÉRICAS.</em><br>ESTO ES TUYO.',
        'cite'      => 'WellCore · El Proceso',
    ],

    // -------------------------------------------------------------------------
    // Step 04 — Check-ins (chat + delta dashboard)
    // -------------------------------------------------------------------------
    'step4' => [
        'meta_index'   => 'PASO 04',
        'meta_timing'  => 'SEGÚN TU PLAN',
        'title_html'   => 'DATA.<br>AJUSTE.<br>AVANCE.',
        'desc'         => 'Loggeás tu check-in en el panel: bienestar, días entrenados, nutrición, comentario. Tu coach lo revisa y responde con ajustes — vía panel y WhatsApp. Frecuencia según tu plan: mensual (Esencial), quincenal (Método), semanal (Elite).',
        'detail'       => 'Coach humano · Sin bots · Sin auto-respuestas',
        'viz' => [
            'coach_avatar'   => 'M',
            'coach_name'     => 'Coach Marcos',
            'coach_status'   => 'En línea',
            'msgs' => [
                ['role' => 'coach', 'text' => '¿Cómo te fue esta semana? ¿Pudiste cumplir los 3 días?'],
                ['role' => 'user',  'text' => 'Sí, cumplí los 3. El miércoles costó pero lo hice.'],
                ['role' => 'coach', 'text' => 'Bien hecho. Vi que bajaste 0.8 kg. Seguimos igual — el cuerpo está respondiendo.'],
            ],
            'msg_ts'         => 'Hoy · 09:14',
            'delta_header'   => 'DELTA QUINCENAL · SEMANA 4',
            'delta_metric_1' => [
                'label' => 'PESO',
                'value' => '−1.6',
                'unit'  => 'kg',
                'desc'  => 'vs inicio',
                'tone'  => 'neg',
                'pct'   => 65,
            ],
            'delta_metric_2' => [
                'label' => 'ADHERENCIA',
                'value' => '87',
                'unit'  => '%',
                'desc'  => 'sesiones completadas',
                'tone'  => 'neutral',
                'pct'   => 87,
            ],
        ],
        'disclaimer' => 'Vista de ejemplo · datos demostrativos',
    ],

    // -------------------------------------------------------------------------
    // Step 05 — Resultados (journey progress chart)
    // -------------------------------------------------------------------------
    'step5' => [
        'meta_index'   => 'PASO 05',
        'meta_timing'  => 'SEGUIMIENTO CONTINUO',
        'title_html'   => 'RESUL-<br>TADOS<br>REALES.',
        'desc'         => 'No promesas. Métricas. Cada semana ves cambios en peso, composición corporal y adherencia desde tu panel. A las 8–12 semanas tu coach hace una evaluación completa con datos reales — no curvas inventadas.',
        'detail'       => 'Métricas en tu panel · Seguimiento continuo',
        'viz' => [
            'chart_label'  => 'PESO CORPORAL · KG',
            'chart_value'  => '−5.4',
            'chart_pill'   => '▼ 7.3% · 8 SEM',
            'axis_labels'  => [
                'INICIO', 'SEM 4', 'SEM 8',
            ],
            'weeks' => [
                'SEM 1–2 ✓',
                'SEM 3–4 ✓',
                'SEM 5–6 ✓',
                'SEM 7–8 ✓',
            ],
        ],
        'disclaimer' => 'Vista de ejemplo · datos demostrativos',
    ],

    // -------------------------------------------------------------------------
    // Divider entre Step 5 y CTA Final
    // -------------------------------------------------------------------------
    'divider' => 'CIENCIA · MÉTODO · 2026',

    // -------------------------------------------------------------------------
    // CTA Final
    // -------------------------------------------------------------------------
    'cta_final' => [
        'kicker'        => 'EL PROCESO ESTÁ CLARO · EL SIGUIENTE PASO, TAMBIÉN',
        'title_html'    => 'EMPEZAR<br><span class="accent">EL PROCESO</span>',
        'sub'           => 'Sin lista de espera. Sin contratos. Empezás con el diagnóstico hoy y tu coach te contacta dentro de 24–48 h.',
        'btn_primary'   => 'Empezar el proceso',
        'btn_secondary' => 'Ver planes y precios',
        'stats' => [
            ['val' => '5–8',   'label' => 'Min en el diagnóstico'],
            ['val' => '24–48', 'label' => 'Horas hasta tu coach'],
            ['val' => '8–12',  'label' => 'Semanas de protocolo'],
        ],
        'trust_items' => [
            'Sin tarjeta de crédito en el diagnóstico',
            'Coach humano 1:1 · sin bots',
            'Plan en tu panel · sin PDFs sueltos',
        ],
    ],

    // -------------------------------------------------------------------------
    // Sticky mobile CTA
    // -------------------------------------------------------------------------
    'sticky' => [
        'text_strong' => 'Empezar el proceso',
        'text'        => '5 minutos · sin compromiso',
        'cta'         => 'Inscribirme',
    ],

];
