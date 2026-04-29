<?php

/*
 |---------------------------------------------------------------------
 | /nosotros · brand storytelling editorial v2 — porting Sprint 2026-04-29.
 |
 | Estructura nueva:
 |   - Hero founder (Daniel Esparza)
 |   - Manifiesto editorial (4 párrafos + firma)
 |   - Timeline historia (5 hitos: 2018, 2020, 2022, 2024, 2026)
 |   - Equipo 6 personas (1 founder con bio + 5 placeholders iniciales)
 |   - 3 valores pull-quote brutales (literales del prompt)
 |   - CTA suave (invitación, no urgencia)
 |
 | Voz: latino neutro estricto (tú/puedes/quieres/sabes).
 | NO voseo argentino. NO castellano peninvular.
 | NO mencionar IA/Claude/GPT/algoritmo/machine learning.
 |---------------------------------------------------------------------
 */

return [

    // -------------------------------------------------------------------------
    // Meta — head
    // -------------------------------------------------------------------------
    'meta_title'       => 'Nosotros · WellCore Fitness',
    'meta_description' => 'WellCore Fitness empezó porque a nosotros nos pasó. Coaching basado en ciencia, sin milagros. Conoce al equipo, la historia y los valores.',

    // -------------------------------------------------------------------------
    // Sidebar editorial (≥1024px)
    // -------------------------------------------------------------------------
    'sidebar' => [
        'subtitle'       => 'Nosotros · 2026',
        'progress_label' => 'Progreso',
        'cta'            => 'CONOCER EL MÉTODO',
    ],

    // -------------------------------------------------------------------------
    // Capítulos / secciones (sidebar nav + chapter pill)
    // -------------------------------------------------------------------------
    'chapters' => [
        'cap00' => [
            'pill'      => 'Cap 00 · Portada',
            'nav_title' => 'Portada',
        ],
        's1' => [
            'pill'      => 'Cap 01 · Manifiesto',
            'nav_title' => 'Manifiesto',
        ],
        's2' => [
            'pill'      => 'Cap 02 · Historia',
            'nav_title' => 'Historia',
        ],
        's3' => [
            'pill'      => 'Cap 03 · Equipo',
            'nav_title' => 'Equipo',
        ],
        's4' => [
            'pill'      => 'Cap 04 · Valores',
            'nav_title' => 'Valores',
        ],
        'cta' => [
            'pill'      => 'Si crees que es para ti',
            'nav_title' => 'Hablemos',
        ],
    ],

    // -------------------------------------------------------------------------
    // Hero founder
    // -------------------------------------------------------------------------
    'hero' => [
        'eyebrow'   => 'WC · NOSOTROS · 2018 — 2026 · LATAM',
        'title_html' => 'EMPEZAMOS<br>PORQUE NOS PASÓ<br>A <span class="ac">NOSOTROS.</span>',
        'sub'       => '"WellCore es lo que necesitábamos cuando nadie nos lo daba."',
        'meta' => [
            ['k' => 'Fundado',         'v' => '2018 · Bucaramanga'],
            ['k' => 'Equipo core',     'v' => '06 personas'],
            ['k' => 'Cobertura',       'v' => 'LATAM'],
            ['k' => 'Filosofía',       'v' => 'Sin milagros'],
        ],
    ],

    // -------------------------------------------------------------------------
    // Manifiesto editorial (4 párrafos + firma)
    // -------------------------------------------------------------------------
    'manifiesto' => [
        'tag'      => '§ 01  ·  Manifiesto · Daniel Esparza',
        'p1_html'  => 'En su momento yo entrenaba sin dirección. Pagaba coaches que prometían cosas imposibles y entregaban planes copiados. Buscaba <strong>ciencia, no carisma</strong>. Buscaba método, no motivación de Instagram. <em>Lo que necesitaba no existía</em> — así que lo construimos.',
        'p2_html'  => 'WellCore no nació de un pitch deck ni de una ronda. Nació de la frustración honesta de no encontrar lo que tenía que existir: coaching basado en evidencia, sin atajos, sin transformaciones manipuladas, sin urgencia falsa. Empezamos como yo necesitaba que alguien empezara conmigo.',
        'p3_html'  => 'Años después seguimos en la misma línea. Somos un equipo chico que <strong>entrena lo que enseña</strong>, una plataforma construida con las manos, y una promesa simple: si no podemos explicar el porqué científico de lo que te pedimos, no te lo pedimos.',
        'p4_html'  => 'Esto no es una empresa de fitness. Es un método con producto encima. Y sigue creciendo porque <em>funciona en serio.</em>',
        'sig_name' => 'DANIEL ESPARZA',
        'sig_role' => 'CEO · Fundador · WellCore Fitness',
    ],

    // -------------------------------------------------------------------------
    // Timeline historia (5 hitos)
    // -------------------------------------------------------------------------
    'timeline' => [
        'intro'     => 'De coach 1:1 en Bucaramanga a plataforma LATAM. Cinco hitos de método, no de milagros.',
        'intro_sub' => '2018 → 2026 · Cinco hitos',
        'items' => [
            [
                'year'       => '2018',
                'tag'        => 'origen',
                'state'      => 'done',
                'title_html' => 'FUNDACIÓN<br>WELLCORE',
                'desc'       => 'Daniel arranca como coach 1:1 en Bucaramanga, Colombia. Planillas, check-ins manuales y un método claro: ciencia primero, todo lo demás después. El método ya existía antes que el producto.',
                'meta'       => ['Bucaramanga', '1 atleta', 'sin plataforma'],
            ],
            [
                'year'       => '2020',
                'tag'        => 'online',
                'state'      => 'done',
                'title_html' => 'PRIMER PLAN<br>ONLINE COMPLETO',
                'desc'       => 'El primer plan integral 100% online: entrenamiento, nutrición y check-ins quincenales empaquetados como producto, no como PDF suelto. La ciencia entra estructurada.',
                'meta'       => ['v0 online', 'check-ins quincenales', 'método empaquetado'],
            ],
            [
                'year'       => '2022',
                'tag'        => 'escala',
                'state'      => 'done',
                'title_html' => 'COACHING 1:1<br>ESCALADO',
                'desc'       => 'Pasamos de un coach a un equipo chico de coaches certificados con el método WellCore. Más voces, mismo rigor. Cada coach pasa filtro de evidencia y carga de trabajo antes de entrar.',
                'meta'       => ['equipo core', 'método compartido', 'rigor uniforme'],
            ],
            [
                'year'       => '2024',
                'tag'        => 'plataforma',
                'state'      => 'done',
                'title_html' => 'PLATAFORMA<br>PROPIA',
                'desc'       => 'Lanzamos la plataforma propia: panel cliente, panel coach, biblioteca de ejercicios, seguimiento semanal. Por primera vez el método vive en software construido por nosotros.',
                'meta'       => ['panel cliente', 'panel coach', 'producto propio'],
            ],
            [
                'year'       => '2026',
                'tag'        => 'en curso',
                'state'      => 'future',
                'title_html' => 'EXPANSIÓN<br>LATAM',
                'desc'       => 'Expansión activa a más países de LATAM con pricing local, contenido en español neutro y rediseño editorial de cero. Mismo método, más alcance.',
                'meta'       => ['rediseño v2', 'pricing local', 'español neutro'],
            ],
        ],
    ],

    // -------------------------------------------------------------------------
    // Equipo (6 personas — 1 founder con bio + 5 placeholders)
    // Ver IMPORTANTES decisiones del prompt: Daniel con bio completa,
    // los 5 demás solo iniciales + rol genérico (sin autorización para bios).
    // -------------------------------------------------------------------------
    'equipo' => [
        'intro'     => '"Las personas detrás del método. Todas entrenan lo que enseñan."',
        'intro_sub' => 'Daniel + 9 coaches certificados · LATAM',
        'members' => [
            [
                'num'        => '01 / 10',
                'initials'   => 'DE',
                'name_html'  => 'DANIEL ESPARZA',
                'role'       => 'CEO · Fundador · Bucaramanga',
                'quote'      => 'Lo construimos porque lo necesitábamos.',
                'bio'        => 'Fundador de WellCore Fitness. Especialista en fisiología del ejercicio y nutrición deportiva. Diseñó el método que hoy comparte el equipo: ciencia primero, sin milagros.',
                'featured'   => true,
            ],
            [
                'num'        => '02 / 10',
                'initials'   => 'CS',
                'name_html'  => 'COACH<br>SENIOR',
                'role'       => 'Especialidad: hipertrofia y fuerza',
                'quote'      => 'El progreso no se improvisa. Se programa.',
                'bio'        => null,
                'featured'   => false,
            ],
            [
                'num'        => '03 / 10',
                'initials'   => 'NC',
                'name_html'  => 'NUTRI-<br>CIONISTA',
                'role'       => 'Plan alimenticio · macros · clínica',
                'quote'      => 'La nutrición es la mitad del trabajo. Casi siempre la peor hecha.',
                'bio'        => null,
                'featured'   => false,
            ],
            [
                'num'        => '04 / 10',
                'initials'   => 'CM',
                'name_html'  => 'COACH<br>SENIOR',
                'role'       => 'Especialidad: mujeres · ciclo hormonal',
                'quote'      => 'Cada ciclo cuenta. Programar entendiendo eso cambia todo.',
                'bio'        => null,
                'featured'   => false,
            ],
            [
                'num'        => '05 / 10',
                'initials'   => 'CP',
                'name_html'  => 'COACH<br>SENIOR',
                'role'       => 'Especialidad: performance · rendimiento',
                'quote'      => 'Progreso medible o no es progreso. Es esperanza.',
                'bio'        => null,
                'featured'   => false,
            ],
            [
                'num'        => '06 / 10',
                'initials'   => 'ND',
                'name_html'  => 'NUTRI-<br>CIONISTA',
                'role'       => 'Deportiva · suplementación · timing',
                'quote'      => 'La ciencia tiene que llegar a quien la necesita.',
                'bio'        => null,
                'featured'   => false,
            ],
        ],
        'team_more' => '+ 4 coaches del equipo en perfil privado · perfiles completos disponibles tras inscripción y match.',
    ],

    // -------------------------------------------------------------------------
    // Valores (3 pull-quote brutales — literales del prompt)
    // -------------------------------------------------------------------------
    'valores' => [
        'intro'    => 'Tres líneas duras',
        'headline' => 'Lo que no negociamos — aunque cueste clientes, viralización o atajos.',
        'items' => [
            [
                'num'             => '/01',
                'statement_html'  => 'NO PROMETEMOS<br><span class="red">MILAGROS.</span><br>PROMETEMOS MÉTODO.',
                'context_html'    => 'Cada plan que entregamos tiene evidencia detrás. Si no podemos explicar el <strong>por qué científico</strong> de lo que te pedimos, no te lo pedimos. La motivación se acaba; el método queda.',
            ],
            [
                'num'             => '/02',
                'statement_html'  => 'TU PROGRESO<br>ES <span class="red">TUYO.</span><br>NO NUESTRO.',
                'context_html'    => 'No usamos tu transformación como anzuelo de marketing. <strong>Tu cuerpo, tu historia, tu resultado.</strong> Nosotros somos el método; tú eres el protagonista. Sin antes/después manipulados, sin testimonios pagados.',
            ],
            [
                'num'             => '/03',
                'statement_html'  => 'LA CIENCIA<br>NO ES <span class="red">OPCIONAL.</span>',
                'context_html'    => 'Entrenamos con evidencia actualizada. <strong>No con modas, no con intuición.</strong> Si hay un paper detrás, lo citamos. Si no lo hay, no lo usamos. Si la evidencia cambia, el plan también.',
            ],
        ],
    ],

    // -------------------------------------------------------------------------
    // CTA suave (invitación, no urgencia)
    // -------------------------------------------------------------------------
    'cta_suave' => [
        'kicker'      => 'SIGUIENTE PASO',
        'title_html'  => 'SI CREES QUE ES<br>PARA <span class="red">TI,</span> HABLEMOS.',
        'sub'         => '"Sin presión, sin countdowns. Si quieres ver cómo trabajamos, te dejamos el método y los planes a un click."',
        'btn_primary' => 'CONOCER EL MÉTODO',
        'btn_secondary' => 'Ver planes',
        'foot_line'   => 'Tomate tu tiempo · No hay urgencia · No vas a recibir 17 emails',
    ],
];
