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
    'meta_title'       => 'El Proceso · 5 pasos para tu transformación | WellCore Fitness',
    'meta_description' => 'El camino WellCore en 5 pasos: diagnóstico, match con coach, plan personalizado, check-ins y resultados verificables. Sin atajos. Sin promesas vacías.',

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
        'body'   => 'No vendemos motivación. Vendemos un proceso. Cinco pasos que transforman tus datos reales en un plan que tu cuerpo puede ejecutar y que tu coach va ajustando cada quincena. No hay magia. Hay método.',
    ],

    // -------------------------------------------------------------------------
    // Step 01 — Diagnóstico
    // -------------------------------------------------------------------------
    'step1' => [
        'meta_index'   => 'PASO 01',
        'meta_timing'  => '5 MIN',
        'title_html'   => 'DIAGNÓS-<br>TICO<br>INICIAL',
        'desc'         => 'Todo empieza con cinco minutos de tu tiempo. Un formulario corto —sin trampa— que nos dice dónde estás y a dónde quieres llegar.',
        'detail'       => 'Anónimo hasta el match · Sin compromiso',
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
        'meta_timing'  => 'MATCHMAKING',
        'title_html'   => 'COACH<br>A<br>MEDIDA',
        'desc'         => 'No eliges al azar. Nuestro sistema de match cruza tus datos con la especialidad, disponibilidad horaria y metodología de cada coach. Te asignamos el match más alto.',
        'detail'       => 'Compatibilidad por afinidad · Confirmado en 24 h',
        'best_label'   => 'MEJOR MATCH',
        'coaches' => [
            [
                'initials' => 'LC',
                'name'     => 'LAURA',
                'spec'     => 'HIPERTROFIA',
                'match'    => 64,
                'best'     => false,
            ],
            [
                'initials' => 'MA',
                'name'     => 'MARCOS',
                'spec'     => 'PÉRDIDA GRASA',
                'match'    => 92,
                'best'     => true,
            ],
            [
                'initials' => 'SO',
                'name'     => 'SOFÍA',
                'spec'     => 'RENDIMIENTO',
                'match'    => 46,
                'best'     => false,
            ],
        ],
        'disclaimer' => 'Vista de ejemplo · datos demostrativos',
    ],

    // -------------------------------------------------------------------------
    // Step 03 — Plan personalizado
    // -------------------------------------------------------------------------
    'step3' => [
        'meta_index'   => 'PASO 03',
        'meta_timing'  => '72 H ENTREGA',
        'title_html'   => 'PLAN<br>PROPIO,<br>TUYO.',
        'desc'         => 'Entrenamiento + nutrición + hábitos. Diseñado para tu cuerpo, tu agenda y tu nivel. No hay plan genérico: cada variable se ajusta a tus datos.',
        'detail'       => 'Entrenamiento · Nutrición · Hábitos',
        'viz' => [
            'pdf_filename'      => 'plan_marcos_sem01.pdf',
            'pdf_meta'          => 'GENERADO 28 ABR 2026 · 4.2 MB',
            'pdf_download'      => '↓ ABRIR',
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
        'disclaimer' => 'Vista de ejemplo · datos demostrativos',
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
        'meta_timing'  => 'QUINCENAL',
        'title_html'   => 'DATA.<br>AJUSTE.<br>AVANCE.',
        'desc'         => 'Cada dos semanas: check-in por chat con tu coach. Revisamos peso, adherencia y recuperación. Si algo no funciona, lo cambiamos. Sin esperar al mes.',
        'detail'       => 'Cada 14 días · Ajuste basado en data',
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
        'meta_timing'  => '8–12 SEMANAS',
        'title_html'   => 'RESUL-<br>TADOS<br>REALES.',
        'desc'         => 'No promesas. Métricas. A las 8 semanas tienes datos verificables: peso, composición corporal, adherencia y rendimiento. Todo documentado.',
        'detail'       => 'Métricas verificadas · Protocolo 8/12 semanas',
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
        'sub'           => 'Sin lista de espera. Sin contratos. Empieza con el diagnóstico hoy.',
        'btn_primary'   => 'Empezar el proceso',
        'btn_secondary' => 'Ver planes y precios',
        'stats' => [
            ['val' => '47+',   'label' => 'Activos ahora'],
            ['val' => '94%',   'label' => 'Satisfacción'],
            ['val' => '8 sem', 'label' => 'Resultados verificables'],
        ],
        'trust_items' => [
            'Sin tarjeta de crédito',
            'Cancelas cuando quieras',
            'Soporte humano real',
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
