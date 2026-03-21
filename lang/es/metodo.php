<?php

return [

    // -------------------------------------------------------------------------
    // Hero
    // -------------------------------------------------------------------------
    'hero' => [
        'label'       => 'Protocolo de Entrenamiento Basado en Evidencia',
        'title'       => 'EL METODO',
        'subtitle'    => 'No seguimos modas. Seguimos la ciencia.',
        'description' => 'WellCore no es una app de rutinas ni un plan de 30 dias. Es un protocolo cientifico, personalizado al 100%, con seguimiento real de coach. Cada variable de tu entrenamiento existe por una razon demostrada.',
    ],

    // -------------------------------------------------------------------------
    // Stats Bar
    // -------------------------------------------------------------------------
    'stats' => [
        'adherence'       => 'Adherencia promedio',
        'visible_results' => 'Promedio hasta resultados visibles',
        'attention'       => 'Atencion real, sin bots',
    ],

    // -------------------------------------------------------------------------
    // Section 01 — El Problema
    // -------------------------------------------------------------------------
    'problem' => [
        'title'    => 'EL PROBLEMA',
        'subtitle' => 'Por que fallan la mayoria',
        'intro'    => 'El :percent de las personas que comienzan un programa de ejercicio lo abandonan antes de los 3 meses. No es falta de voluntad. Es falta de estructura cientifica.',
        // Note: :percent is replaced with the counter span HTML

        'fp1' => [
            'title'       => 'Sin diagnostico real, solo solucion generica',
            'description' => 'La mayoria de los programas asumen que todos los cuerpos responden igual. Ignoran historial, biotipo, capacidades funcionales y objetivos individuales. El resultado: un plan que no fue disenado para ti nunca puede llevarte a donde quieres llegar.',
            'solution'    => 'Diagnostico funcional completo en la semana 1',
        ],
        'fp2' => [
            'title'       => 'Sin seguimiento, sin ajustes en tiempo real',
            'description' => 'Un plan de 12 semanas creado en el dia 1 ya esta desactualizado en la semana 4. El cuerpo adapta. Las variables cambian. Sin un sistema de retroalimentacion activa, el programa se vuelve obsoleto antes de que veas resultados.',
            'solution'    => 'Revision semanal y ajuste de variables cada ciclo',
        ],
        'fp3' => [
            'title'       => 'Sin educacion, sin autonomia a largo plazo',
            'description' => 'Los programas que no educan crean dependencia. Cuando el plan termina, el cliente no sabe que hacer. WellCore construye comprension: por que entrenas lo que entrenas, por que comes lo que comes, como tu cuerpo responde.',
            'solution'    => 'Educacion continua integrada al protocolo',
        ],
        'solution_label' => 'Solucion WellCore',

        'stats' => [
            's1_label' => 'Programas fracasan antes de los 90 dias',
            's2_label' => 'Abandono semanas 1-4',
            's3_label' => 'Sin objetivo claro',
            's4_label' => 'Adherencia WellCore',
            's5_label' => 'Mas resultados con coaching',
        ],
        'source' => 'Fuente: NSCA Journal of Strength and Conditioning Research, 2024 — American College of Sports Medicine, 2023',
    ],

    // -------------------------------------------------------------------------
    // Section 02 — Los 5 Pilares
    // -------------------------------------------------------------------------
    'pillars' => [
        'title'    => 'LA ESTRUCTURA',
        'subtitle' => 'Los 5 Pilares del Metodo',
        'note'     => 'Cada pilar esta respaldado por investigacion publicada en journals de referencia.',

        'p1' => [
            'name'        => 'Sobrecarga Progresiva',
            'description' => 'Incremento sistematico de la carga de entrenamiento para provocar adaptaciones continuas. Sin progresion, no hay estimulo. Sin estimulo, no hay cambio.',
        ],
        'p2' => [
            'name'        => 'Periodizacion Inteligente',
            'description' => 'Estructuracion de fases de entrenamiento para maximizar ganancias y minimizar el riesgo de sobreentrenamiento. Cada semana tiene un proposito especifico.',
        ],
        'p3' => [
            'name'        => 'Nutricion de Precision',
            'description' => 'Macronutrientes calculados segun tu objetivo especifico, tu metabolismo y tu nivel de actividad. No dietas genericas. Protocolos nutricionales individualizados.',
        ],
        'p4' => [
            'name'        => 'Recuperacion Optimizada',
            'description' => 'El crecimiento ocurre en la recuperacion, no en el entrenamiento. Protocolos de sueño, gestion del estres y descanso activo integrados al programa.',
        ],
        'p5' => [
            'name'        => 'Adherencia Conductual',
            'description' => 'El mejor programa es el que se sigue. Psicologia del habito, gestion de barreras y comunicacion directa con tu coach integradas para maximizar tu consistencia.',
        ],
    ],

    // -------------------------------------------------------------------------
    // Section 03 — La Diferencia
    // -------------------------------------------------------------------------
    'comparison' => [
        'title'    => 'LA DIFERENCIA',
        'subtitle' => 'WellCore vs. el Resto',

        'col_feature'  => 'Caracteristica',
        'col_wellcore' => 'WellCore',
        'col_app'      => 'App Generica',
        'col_gym'      => 'Gym PT',

        'rows' => [
            'r1' => [
                'feature'  => 'Diagnostico inicial completo',
                'wellcore' => '40+ variables',
                'app'      => 'No',
                'gym'      => 'Parcial',
            ],
            'r2' => [
                'feature'  => 'Programa 100% personalizado',
                'wellcore' => 'Desde cero',
                'app'      => 'No (plantillas)',
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
                'wellcore' => 'Semanal',
                'app'      => 'No',
                'gym'      => 'Raro',
            ],
            'r5' => [
                'feature'  => 'Plan nutricional incluido',
                'wellcore' => 'Planes Base y Elite',
                'app'      => 'Extra',
                'gym'      => 'No',
            ],
            'r6' => [
                'feature'  => 'Informe final de resultados',
                'wellcore' => 'Semana 12',
                'app'      => 'No',
                'gym'      => 'Raro',
            ],
        ],
        'footnote' => 'Comparativa basada en oferta estandar de mercado. Las condiciones pueden variar.',
    ],

    // -------------------------------------------------------------------------
    // Section 04 — FAQ
    // -------------------------------------------------------------------------
    'faq' => [
        'title'    => 'LO QUE MAS NOS PREGUNTAN',
        'subtitle' => 'Si tienes mas dudas, estamos disponibles por WhatsApp o puedes revisar nuestra seccion de FAQ completa.',
        'see_all'  => 'Ver todas las preguntas frecuentes',

        'q1' => [
            'question' => '¿Necesito experiencia previa para empezar?',
            'answer'   => 'No. El diagnostico inicial determina exactamente tu nivel de partida. El programa se construye desde ahi. Tenemos clientes sin experiencia previa y clientes con años de entrenamiento. El protocolo se adapta a donde estas, no a donde deberia estar alguien generico.',
        ],
        'q2' => [
            'question' => '¿Puedo entrenar en casa sin equipamiento?',
            'answer'   => 'Si. Durante el diagnostico indicamos el equipamiento disponible y el programa se disena especificamente para ese contexto. Si solo tienes tu propio peso corporal, el programa funciona igual. Si tienes acceso a un gym completo, aprovechamos todo lo disponible.',
        ],
        'q3' => [
            'question' => '¿Cuanto tiempo tarda en verse el primer resultado?',
            'answer'   => 'Los cambios en composicion corporal comienzan a verse entre las semanas 6 y 10, dependiendo del punto de partida y el objetivo. Antes de eso, los resultados son internos: mas energia, mejor sueño, mayor fuerza. Los resultados visibles requieren tiempo. El promedio en WellCore es 8-12 semanas para cambios fotografiables.',
        ],
        'q4' => [
            'question' => '¿Que pasa si tengo una lesion o limitacion fisica?',
            'answer'   => 'Las lesiones y limitaciones se documentan en el diagnostico y el programa las incorpora desde el principio. No ignoramos los problemas: los integramos al diseño. Si durante el programa aparece una lesion nueva, el plan se ajusta de inmediato sin costo adicional.',
        ],
    ],

    // -------------------------------------------------------------------------
    // Final CTA
    // -------------------------------------------------------------------------
    'cta' => [
        'label'       => 'El siguiente paso',
        'title'       => 'EMPIEZA CON EL METODO',
        'description' => '87% de adherencia. 12 semanas. Un protocolo disenado solo para ti. Sin plantillas, sin generico. Solo ciencia aplicada a tu cuerpo real.',
        'btn_primary' => 'Comenzar ahora',
        'btn_secondary' => 'Ver el Proceso',
        'trust1'      => 'Sin contratos',
        'trust2'      => 'Coach 1:1 real',
        'trust3'      => 'Respuesta en 24h',
        'trust4'      => 'Cancela cuando quieras',
    ],

];
