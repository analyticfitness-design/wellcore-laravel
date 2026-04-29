<?php

/**
 * /metodo — i18n (ES) · v2 long-form editorial.
 *
 * Estructura:
 *   meta_*           → SEO + open graph.
 *   sidebar.*        → editorial-sidebar (brand_sub, cta, progress label).
 *   chapters.cap00..cap07 → labels para sec-divider, nav del sidebar, header del cap.
 *   hero.*           → portada (kicker, pullquote_html, sub, scroll_hint).
 *   stats.*          → stats bar (3 KPIs).
 *   problem.*        → Cap01 (intro_p1..p3, data cells, source).
 *   pillars.*        → Cap02 (5 pilares con cite académico).
 *   comparison.*     → tabla Bloomberg Cap02.
 *   ciencia.*        → Cap03 (3 párrafos + svg labels).
 *   plan.*           → Cap04 (2 párrafos + period table + margin note).
 *   coach.*          → Cap05 (3 párrafos + margin note).
 *   checkins.*       → Cap06 (2 párrafos + ticker items + source note).
 *   objections.*     → Cap07 (intro + 5 objeciones).
 *   pullquotes.q1..q5 → 5 pull-quotes brutales intercalados.
 *   inline_ctas.c1..c3 → 3 CTAs intercalados.
 *   cta_final.*      → CTA final masivo + trust strip.
 *   sticky.*         → sticky-mobile-cta.
 *
 * Voz: latino neutro estricto (tú/puedes/quieres/sabes/empieza/cancelas).
 *      NO voseo argentino (vos/podés/empezá), NO castellano peninsular,
 *      NO regionalismos colombianos, NO mención IA/Claude/GPT (Cap05).
 *      "RIR" reemplazado por "intensidad relativa" en period table.
 */

return [

    // -------------------------------------------------------------------------
    // SEO
    // -------------------------------------------------------------------------
    'meta_title'       => 'El Método — WellCore Fitness · Protocolo basado en evidencia',
    'meta_description' => 'Long-form editorial sobre el método WellCore: ciencia, periodización, coach 1:1 humano. Sin milagros. Solo evidencia aplicada a tu cuerpo real.',

    // Compatibilidad con templates legacy que aún miran 'meta_title' separado:
    // (se preservan claves antiguas mínimas como pass-through si el código viejo
    // lo necesita — prefiere las nuevas).

    // -------------------------------------------------------------------------
    // Sidebar editorial
    // -------------------------------------------------------------------------
    'sidebar' => [
        'subtitle'       => 'El Método · 2026',
        'progress_label' => 'Progreso',
        'cta'            => 'EMPEZAR',
    ],

    // -------------------------------------------------------------------------
    // Chapters — labels para sidebar nav + sec-divider + header del cap.
    // -------------------------------------------------------------------------
    'chapters' => [
        'cap00' => [
            'nav_title' => 'Portada',
            'divider'   => 'PORTADA · WELLCORE 2026',
            'pill'      => 'Portada',
        ],
        'cap01' => [
            'nav_title' => 'El Problema',
            'divider'   => 'CAPÍTULO 01 · EL PROBLEMA',
            'pill'      => 'Cap 01 · El Problema',
            'num_text'  => '01 · Por qué la mayoría falla',
            'title_html'=> 'EL<br><em>PROBLEMA</em>',
        ],
        'cap02' => [
            'nav_title' => 'El Método',
            'divider'   => 'CAPÍTULO 02 · EL MÉTODO',
            'pill'      => 'Cap 02 · El Método',
            'num_text'  => '02 · Los 5 pilares del protocolo',
            'title_html'=> 'EL<br><em>MÉTODO</em>',
        ],
        'cap03' => [
            'nav_title' => 'La Ciencia',
            'divider'   => 'CAPÍTULO 03 · LA CIENCIA',
            'pill'      => 'Cap 03 · La Ciencia',
            'num_text'  => '03 · Cómo funciona la hipertrofia real',
            'title_html'=> 'LA<br><em>CIENCIA</em>',
        ],
        'cap04' => [
            'nav_title' => 'El Plan',
            'divider'   => 'CAPÍTULO 04 · EL PLAN',
            'pill'      => 'Cap 04 · El Plan',
            'num_text'  => '04 · Cómo se construye tu protocolo',
            'title_html'=> 'EL<br><em>PLAN</em>',
        ],
        'cap05' => [
            'nav_title' => 'El Coach',
            'divider'   => 'CAPÍTULO 05 · EL COACH',
            'pill'      => 'Cap 05 · El Coach',
            'num_text'  => '05 · Quién es y cómo trabaja',
            'title_html'=> 'EL<br><em>COACH</em>',
        ],
        'cap06' => [
            'nav_title' => 'Los Check-ins',
            'divider'   => 'CAPÍTULO 06 · LOS CHECK-INS',
            'pill'      => 'Cap 06 · Los Check-ins',
            'num_text'  => '06 · Qué pasa cada semana',
            'title_html'=> 'LOS<br><em>CHECK-INS</em>',
        ],
        'cap07' => [
            'nav_title' => 'Las Objeciones',
            'divider'   => 'CAPÍTULO 07 · LAS OBJECIONES',
            'pill'      => 'Cap 07 · Las Objeciones',
            'num_text'  => '07 · Las preguntas que nadie hace en voz alta',
            'title_html'=> 'LAS<br><em>OBJECIONES</em>',
        ],
    ],

    // -------------------------------------------------------------------------
    // Hero — portada
    // -------------------------------------------------------------------------
    'hero' => [
        'kicker'         => 'Protocolo basado en evidencia · WellCore Fitness · 2026',
        'pullquote_html' => 'ENTRENAR SIN<br>PROGRESIÓN<br>ES SOLO <em>REPETICIÓN.</em>',
        'sub'            => 'Esto es lo que cambia con WellCore.',
        'scroll_hint'    => 'Scroll',

        // Legacy keys preservadas (algún componente viejo todavía las puede leer):
        'label'    => 'Protocolo basado en evidencia',
        'title'    => 'EL MÉTODO',
        'subtitle' => 'No seguimos modas. Seguimos la evidencia.',
        'description' => 'WellCore no es una app de rutinas ni un plan de 30 días. Es un protocolo científico personalizado al 100%, con seguimiento real de un coach humano. Cada variable de tu entrenamiento existe por una razón demostrada.',
    ],

    // -------------------------------------------------------------------------
    // Stats Bar — 3 KPIs
    // -------------------------------------------------------------------------
    'stats' => [
        'adherence_value'       => '87%',
        'adherence'             => 'Adherencia promedio',
        'visible_results_value' => '12',
        'visible_results'       => 'Semanas hasta resultados visibles',
        'attention_value'       => '1:1',
        'attention'             => 'Coach humano real, sin bots',
    ],

    // -------------------------------------------------------------------------
    // Cap01 — El Problema
    // -------------------------------------------------------------------------
    'problem' => [
        'intro_p1_html' => 'El ochenta por ciento de las personas que comienzan un programa de ejercicio lo abandonan antes de los tres meses. <strong>No es falta de voluntad. Es falta de arquitectura.</strong> La mayoría de los programas asumen que todos los cuerpos responden igual, ignoran el historial individual y entregan plantillas genéricas disfrazadas de personalización.',
        'intro_p2_html' => 'Sin diagnóstico real, el plan nunca fue tuyo. Un programa de 12 semanas diseñado el día 1 ya está desactualizado en la semana 4: el cuerpo se adapta, las variables cambian, y sin retroalimentación activa el plan se vuelve obsoleto antes de que veas resultados. <strong>El problema no es el ejercicio. Es la falta de sistema.</strong>',
        'intro_p3_html' => 'El tercer error es el más silencioso: cuando el plan termina, el cliente no sabe qué hacer. Los programas que no educan crean dependencia. WellCore construye comprensión. Por qué entrenas lo que entrenas. Por qué comes lo que comes. <em>Cómo tu cuerpo responde.</em>',

        'data_cells' => [
            ['value' => '8/10', 'label' => 'programas fracasan antes de 90 días'],
            ['value' => '67%',  'label' => 'abandona entre semanas 1–4'],
            ['value' => '54%',  'label' => 'sin objetivo claro al iniciar'],
            ['value' => '87%',  'label' => 'adherencia WellCore promedio', 'accent' => true],
        ],
        'source' => 'Fuente: NSCA Journal of Strength & Conditioning Research, 2024 · ACSM, 2023.',
    ],

    // -------------------------------------------------------------------------
    // Cap02 — El Método (5 pilares + comparativa)
    // -------------------------------------------------------------------------
    'pillars' => [
        'intro_p1_html' => 'Cinco pilares. Cada uno existe porque hay un paper que lo respalda. No son categorías de marketing ni divisiones arbitrarias de contenido. Son los cinco vectores que la literatura científica identifica como determinantes del progreso en composición corporal y rendimiento a largo plazo.',
        'margin_note'   => '"Cada pilar existe porque un paper lo respalda. No son categorías de marketing — son los cinco vectores que la evidencia identifica como determinantes del progreso."',

        'p1' => [
            'name'        => 'SOBRECARGA PROGRESIVA',
            'description' => 'Incremento sistemático de la carga para provocar adaptaciones continuas. Sin progresión, no hay estímulo. Sin estímulo, no hay cambio. El cuerpo es brutalmente eficiente: si no lo fuerzas a adaptarse, no lo hace.',
            'cite'        => 'Schoenfeld 2017 · Principio de especificidad NSCA',
        ],
        'p2' => [
            'name'        => 'PERIODIZACIÓN INTELIGENTE',
            'description' => 'Estructuración de fases de entrenamiento para maximizar ganancias y minimizar el sobreentrenamiento. Cada semana tiene un propósito específico. Adaptación → hipertrofia → fuerza → descarga. No es aleatoriedad disfrazada de variedad.',
            'cite'        => 'Haff & Triplett 2016 · Periodization Theory',
        ],
        'p3' => [
            'name'        => 'NUTRICIÓN DE PRECISIÓN',
            'description' => 'Macronutrientes calculados según tu objetivo, tu metabolismo y tu nivel de actividad real. No dietas genéricas. No modas. Protocolos nutricionales individualizados que se ajustan cada ciclo según los datos de progreso reales.',
            'cite'        => 'Morton et al. 2018 · Systematic review of protein supplementation',
        ],
        'p4' => [
            'name'        => 'RECUPERACIÓN OPTIMIZADA',
            'description' => 'El crecimiento ocurre en la recuperación, no en el entrenamiento. Protocolos de sueño, manejo del estrés y descanso activo integrados al programa. La fatiga acumulada sin gestión destruye el progreso silenciosamente.',
            'cite'        => 'Simpson et al. 2017 · Sleep and Athletic Performance',
        ],
        'p5' => [
            'name'        => 'ADHERENCIA CONDUCTUAL',
            'description' => 'El mejor programa es el que se sigue. Psicología del hábito, manejo de barreras y comunicación directa con tu coach integrados para maximizar la consistencia. Un plan perfecto que se abandona vale menos que un plan bueno que se sostiene.',
            'cite'        => 'Gardner et al. 2012 · Making health habitual',
        ],

        // Legacy compat (algún componente viejo todavía las puede pedir):
        'title'    => 'LA ESTRUCTURA',
        'subtitle' => 'Los 5 pilares del método',
        'note'     => 'Cada pilar está respaldado por investigación publicada en journals de referencia.',
    ],

    'comparison' => [
        'title'    => 'WELLCORE VS. EL RESTO',
        'subtitle' => 'Comparativa frente a apps genéricas y entrenadores tradicionales.',

        'col_feature'  => 'Característica',
        'col_wellcore' => 'WellCore',
        'col_app'      => 'App Genérica',
        'col_gym'      => 'Gym PT',

        'rows' => [
            'r1' => [
                'feature'  => 'Diagnóstico inicial',
                'wellcore' => '40+ variables',
                'app'      => 'No',
                'gym'      => 'Parcial',
            ],
            'r2' => [
                'feature'  => 'Programa 100% personalizado',
                'wellcore' => 'Desde cero',
                'app'      => 'Plantillas',
                'gym'      => 'Parcial',
            ],
            'r3' => [
                'feature'  => 'Seguimiento semanal',
                'wellcore' => 'Coach 1:1',
                'app'      => 'No',
                'gym'      => 'Solo sesiones',
            ],
            'r4' => [
                'feature'  => 'Ajustes en tiempo real',
                'wellcore' => 'Cada semana',
                'app'      => 'No',
                'gym'      => 'Raro',
            ],
            'r5' => [
                'feature'  => 'Plan nutricional incluido',
                'wellcore' => 'Esencial, Método y Elite',
                'app'      => 'Extra (pago)',
                'gym'      => 'No',
            ],
            'r6' => [
                'feature'  => 'Informe final con datos',
                'wellcore' => 'Semana 12',
                'app'      => 'No',
                'gym'      => 'Raro',
            ],
        ],
        'footnote' => 'Comparativa basada en oferta estándar de mercado. Las condiciones pueden variar.',
    ],

    // -------------------------------------------------------------------------
    // Cap03 — La Ciencia (curva progresión)
    // -------------------------------------------------------------------------
    'ciencia' => [
        'body_p1_html' => 'La hipertrofia no es un misterio. Es biología con variables controlables. El músculo crece cuando recibe un estímulo mecánico suficiente — tensión, daño, estrés metabólico — y cuando la recuperación permite que ocurra la síntesis proteica. El problema es que la mayoría entrenas con intensidad percibida, no con intensidad medida.',
        'body_p2_html' => 'La <strong>intensidad relativa</strong> es la forma más precisa de cuantificar el esfuerzo sin un laboratorio de fisiología detrás. Si puedes hacer tres repeticiones más antes del fallo técnico, estás en una zona de intensidad media. El rango óptimo para hipertrofia se encuentra cerca del fallo, pero sin cruzarlo en cada serie. <strong>Por debajo hay estímulo insuficiente. Por encima, sobreentrenamiento.</strong> Schoenfeld (2017) demostró que el volumen semanal efectivo es el predictor más robusto de la ganancia muscular cuando la intensidad relativa se controla.',
        'body_p3_html' => 'El progreso no es lineal. Es una curva con fases de adaptación, aceleración y meseta. Lo que distingue a quienes llegan a la semana 12 de quienes abandonan en la semana 4 es saber leer esa curva — y tener un coach humano que la gestione contigo.',
        'svg_label'    => 'Fig. 01 — Curva de progresión WellCore · Semanas 1–12 · Fuerza relativa (%)',
        'svg_legend_wc'  => 'WellCore',
        'svg_legend_avg' => 'Promedio general',
        'svg_dot1' => '+8% fuerza',
        'svg_dot2' => '+22% fuerza',
        'svg_dot3' => '+34% fuerza',
        'source'  => 'Schoenfeld 2017 · Journal of Strength & Conditioning Research — datos representativos de progreso promedio en clientes WellCore.',
    ],

    // -------------------------------------------------------------------------
    // Cap04 — El Plan (periodización)
    // -------------------------------------------------------------------------
    'plan' => [
        'body_p1_html' => 'Antes de escribir una sola serie, tu coach completa un diagnóstico de 40+ variables. Historial de entrenamiento. Lesiones. Equipamiento disponible. Horas de sueño promedio. Nivel de estrés crónico. Objetivos concretos — no "bajar de peso", sino cuánto, en qué plazo, con qué restricciones reales. Ese diagnóstico define el punto de partida. Nada más.',
        'body_p2_html' => 'Con esos datos se construye la periodización. Cuatro fases distribuidas en 12 semanas. Cada fase tiene un objetivo fisiológico distinto, variables específicas de volumen e intensidad y criterios de ajuste semanales. <strong>Un plan del día 1 ya está desactualizado en la semana 4</strong> — por eso el check-in semanal no es opcional: es el mecanismo central del sistema.',
        'margin_note'  => '"Un plan del día 1 ya está desactualizado en la semana 4. El check-in semanal no es opcional — es el mecanismo central del sistema."',

        'period_headers' => [
            'Fase',
            'Semanas',
            'Objetivo fisiológico',
            'Intensidad relativa',
            'Volumen',
        ],

        'period' => [
            'adapt' => [
                'tag'       => 'ADAPTACIÓN',
                'name'      => 'Adaptación',
                'weeks'     => '1–3',
                'objective' => 'Coordinación neuromuscular, técnica, diagnóstico de carga real',
                'intensity' => 'Moderada–baja',
                'volume'    => 'Moderado',
            ],
            'hyper' => [
                'tag'       => 'HIPERTROFIA',
                'name'      => 'Hipertrofia',
                'weeks'     => '4–7',
                'objective' => 'Maximizar volumen efectivo · tensión mecánica sostenida',
                'intensity' => 'Alta',
                'volume'    => 'Alto',
            ],
            'fuerza' => [
                'tag'       => 'FUERZA',
                'name'      => 'Fuerza',
                'weeks'     => '8–11',
                'objective' => 'Eficiencia neuromuscular, cargas altas, menor volumen total',
                'intensity' => 'Muy alta',
                'volume'    => 'Moderado–bajo',
            ],
            'desc' => [
                'tag'       => 'DESCARGA',
                'name'      => 'Descarga',
                'weeks'     => '12',
                'objective' => 'Recuperación activa · consolidación de adaptaciones',
                'intensity' => 'Baja',
                'volume'    => 'Bajo',
            ],
        ],

        'source' => 'Haff & Triplett 2016 · Periodization Theory · adaptado al protocolo WellCore.',
    ],

    // -------------------------------------------------------------------------
    // Cap05 — El Coach (coach humano 1:1, NUNCA mencionar IA)
    // -------------------------------------------------------------------------
    'coach' => [
        'body_p1_html' => 'Un coach humano no te entrega un plan. Te entrega una conversación. La diferencia entre un PDF de rutinas y un protocolo de coaching real es la presencia humana que lo actualiza, lo interpreta y lo adapta cuando la vida real interrumpe el plan teórico — porque siempre lo interrumpe.',
        'body_p2_html' => 'En WellCore cada coach lleva un número limitado de clientes activos. La asignación nunca es aleatoria: se hace según el perfil del cliente, el objetivo y la especialización del coach. Una vez asignado, ese coach es tu punto de contacto durante todo el protocolo. <strong>Respuesta garantizada en menos de 24 horas.</strong> Sin bots. Sin respuestas automáticas. Sin plantillas de mensaje.',
        'body_p3_html' => 'No reemplazamos a tu coach con automatización. La automatización ahorra tiempo donde no aporta — recordatorios, ingreso de métricas, agenda. La interpretación de tus datos, la decisión de progresar o retroceder una semana, la conversación cuando algo se cae, eso lo hace una persona. <em>Esa es la única forma de que el plan siga siendo tuyo.</em>',
        'margin_note'  => '"La automatización ahorra tiempo donde no aporta. La interpretación la hace una persona. Esa es la única forma de que el plan siga siendo tuyo."',
    ],

    // -------------------------------------------------------------------------
    // Cap06 — Los Check-ins (ticker Bloomberg anonimizado)
    // -------------------------------------------------------------------------
    'checkins' => [
        'body_p1_html' => 'El check-in semanal es el corazón del protocolo. No es una formalidad ni un formulario de satisfacción. Es el mecanismo por el que el plan se mantiene vivo. Reportas métricas reales — cargas, repeticiones, energía, sueño, adherencia nutricional — y tu coach las procesa para decidir si la semana siguiente continúa igual, avanza o retrocede.',
        'body_p2_html' => 'Semana 1: diagnóstico y construcción del plan base. Semanas 2–4: fase de adaptación, ajuste de cargas iniciales. Semanas 5–8: bloque de hipertrofia, máximo volumen efectivo. Semanas 9–11: intensificación de fuerza. Semana 12: descarga y <strong>informe final de composición corporal</strong> con datos de inicio vs cierre.',

        'ticker' => [
            ['name' => 'S.V. · CO',  'metric' => '−6.2 kg',     'detail' => 'GRASA · 12 SEM · MÉTODO',  'negative' => true],
            ['name' => 'C.R. · MX',  'metric' => '+22 kg',      'detail' => 'SENTADILLA · 12 SEM · ELITE'],
            ['name' => 'F.M. · CO',  'metric' => '+14 kg',      'detail' => 'PRESS BANCA · 12 SEM · MÉTODO'],
            ['name' => 'A.L. · AR',  'metric' => '−4.8 kg',     'detail' => 'GRASA · 12 SEM · MÉTODO',  'negative' => true],
            ['name' => 'J.B. · PE',  'metric' => '87%',         'detail' => 'ADHERENCIA · ELITE'],
            ['name' => 'M.G. · CL',  'metric' => '+18 kg',      'detail' => 'PESO MUERTO · 12 SEM · MÉTODO'],
            ['name' => 'D.T. · EC',  'metric' => '−5.5 kg',     'detail' => 'COMP. CORPORAL · ELITE',   'negative' => true],
            ['name' => 'P.O. · UY',  'metric' => '+11 kg',      'detail' => 'PRESS MILITAR · 12 SEM · ESENCIAL'],
            ['name' => 'L.K. · CO',  'metric' => '+12% FUERZA', 'detail' => '10 SEM · MÉTODO'],
            ['name' => 'R.S. · MX',  'metric' => '−8 kg',       'detail' => 'GRASA · 16 SEM · ELITE',   'negative' => true],
        ],
        'ticker_label' => 'Resultados anonimizados · clientes activos WellCore',
        'source' => 'Datos representativos de clientes activos. Resultados individuales varían según punto de partida y adherencia.',
    ],

    // -------------------------------------------------------------------------
    // Cap07 — Las Objeciones (5 preguntas reales, voz directa)
    // -------------------------------------------------------------------------
    'objections' => [
        'body_intro_html' => 'Hay preguntas que la gente busca en Google a las 2 AM antes de decidir si inscribirse. Preguntas que parecen razonables pero que en realidad esconden una sola duda: <em>¿esto va a funcionar para mí?</em> Las respondemos sin rodeos.',

        'list' => [
            'o1' => [
                'mark' => '01',
                'q'    => '¿Necesito experiencia previa para empezar?',
                'a'    => 'No. El diagnóstico inicial determina exactamente tu nivel de partida. El programa se construye desde ahí. Tenemos clientes sin experiencia previa y clientes con diez años de entrenamiento. El protocolo se adapta a donde estás — no a donde debería estar alguien genérico.',
            ],
            'o2' => [
                'mark' => '02',
                'q'    => '¿Puedo entrenar en casa sin equipamiento?',
                'a'    => 'Sí. Durante el diagnóstico documentamos el equipamiento disponible y el programa se diseña específicamente para ese contexto. Si solo tienes tu peso corporal, el programa funciona. Si tienes acceso a un gym completo, aprovechamos todo lo disponible. <strong>El método es el mismo — las herramientas varían.</strong>',
            ],
            'o3' => [
                'mark' => '03',
                'q'    => '¿Cuánto tiempo tarda en verse el primer resultado?',
                'a'    => 'Los cambios en composición corporal empiezan a verse entre las semanas 6 y 10, dependiendo del punto de partida. Antes de eso, los resultados son internos: más energía, mejor sueño, mayor fuerza. Los resultados visibles requieren tiempo. <strong>El promedio en WellCore es de 8 a 12 semanas para cambios medibles con datos.</strong>',
            ],
            'o4' => [
                'mark' => '04',
                'q'    => '¿Qué pasa si tengo una lesión o limitación física?',
                'a'    => 'Las lesiones y limitaciones se documentan en el diagnóstico y el programa las incorpora desde el principio. No ignoramos los problemas: los integramos al diseño. Si durante el programa aparece una lesión nueva, el plan se ajusta de inmediato sin costo adicional.',
            ],
            'o5' => [
                'mark' => '05',
                'q'    => '¿Es caro en comparación con un gym?',
                'a'    => 'El coaching 1:1 cuesta más que una membresía de gym porque entrega más que una membresía de gym. Diagnóstico completo, plan personalizado, seguimiento semanal, ajustes en tiempo real, plan nutricional, informe final. <strong>La pregunta correcta no es cuánto cuesta WellCore. Es cuánto te ha costado hasta ahora no tener resultados.</strong>',
            ],
        ],
    ],

    // -------------------------------------------------------------------------
    // Pull-quotes (5) — intercalados a lo largo del recorrido.
    // -------------------------------------------------------------------------
    'pullquotes' => [
        'q1' => [
            'text_html' => 'NO ES FALTA DE<br><em>VOLUNTAD.</em><br>ES FALTA DE<br>ESTRUCTURA.',
            'cite'      => 'WellCore · El Problema',
        ],
        'q2' => [
            'text_html' => 'EL MEJOR PROGRAMA<br>ES EL QUE <em>SE SIGUE.</em>',
            'cite'      => 'Pilar 05 · Adherencia conductual',
        ],
        'q3' => [
            'text_html' => 'LA CIENCIA<br>NO OPINA.<br>LA CIENCIA <em>MIDE.</em>',
            'cite'      => 'WellCore · La Ciencia',
        ],
        'q4' => [
            'text_html' => 'UN COACH HUMANO<br>NO TE DA UN PLAN.<br>TE DA UNA <em>CONVERSACIÓN.</em>',
            'cite'      => 'WellCore · El Coach',
        ],
        'q5' => [
            'text_html' => 'LA PREGUNTA<br>NO ES SI <em>PUEDES.</em><br>ES SI QUIERES<br>HACERLO BIEN.',
            'cite'      => 'WellCore · Las Objeciones',
        ],
    ],

    // -------------------------------------------------------------------------
    // Inline CTAs (3) — intercalados después de Cap02, Cap04, Cap06.
    // -------------------------------------------------------------------------
    'inline_ctas' => [
        'c1' => [
            'label' => 'Siguiente paso',
            'title' => 'VER MI PLAN PERSONALIZADO',
            'btn'   => 'Ver mi plan',
        ],
        'c2' => [
            'label' => '¿Listo para empezar?',
            'title' => 'CONOCE A TU COACH ASIGNADO',
            'btn'   => 'Conocer al coach',
        ],
        'c3' => [
            'label' => 'El protocolo funciona',
            'title' => 'EMPEZAR CON EL MÉTODO',
            'btn'   => 'Empezar ahora',
            'btn_secondary' => 'Ver el proceso',
        ],
    ],

    // -------------------------------------------------------------------------
    // CTA Final masivo
    // -------------------------------------------------------------------------
    'cta_final' => [
        'kicker'     => 'El siguiente paso es tuyo',
        'title_html' => 'EMPEZAR<br>CON EL <em>MÉTODO</em>',
        'sub'        => '87% de adherencia. 12 semanas. Un protocolo diseñado solo para ti. Sin plantillas, sin genérico. Solo evidencia aplicada a tu cuerpo real.',
        'btn_primary'   => 'Ver planes y precios',
        'btn_secondary' => 'Cómo funciona el proceso',
        'trust_items' => [
            'Sin contratos',
            'Coach 1:1 humano',
            'Respuesta en 24 h',
            'Cancelas cuando quieras',
        ],
    ],

    // Legacy compat (algunas páginas vinculadas todavía piden estas keys).
    'cta' => [
        'label'         => 'El siguiente paso',
        'title'         => 'EMPIEZA CON EL MÉTODO',
        'description'   => '87% de adherencia. 12 semanas. Un protocolo diseñado solo para ti.',
        'btn_primary'   => 'Comenzar ahora',
        'btn_secondary' => 'Ver el proceso',
        'trust1'        => 'Sin contratos',
        'trust2'        => 'Coach 1:1 humano',
        'trust3'        => 'Respuesta en 24 h',
        'trust4'        => 'Cancelas cuando quieras',
    ],

    // -------------------------------------------------------------------------
    // Sticky mobile CTA
    // -------------------------------------------------------------------------
    'sticky' => [
        'text_strong' => 'WellCore · El Método',
        'text'        => 'Protocolo 1:1 basado en evidencia',
        'cta'         => 'Empezar',
    ],

];
