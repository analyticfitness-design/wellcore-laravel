<?php

return [

    // -------------------------------------------------------------------------
    // Hero
    // -------------------------------------------------------------------------
    'hero' => [
        'label'       => 'Tu camino hacia los resultados',
        'title_line1' => 'ASI FUNCIONA',
        'title_line2' => 'EL PROCESO',
        'description' => 'De tu diagnostico inicial a tus primeros resultados: 4 fases, 12 semanas, 1 objetivo. Sin guias genericas. Sin programas copiados. Un proceso construido sobre tus datos reales.',
    ],

    // -------------------------------------------------------------------------
    // Stats Bar
    // -------------------------------------------------------------------------
    'stats' => [
        'phases'           => 'Fases definidas',
        'weeks'            => 'Semanas de programa',
        'coach'            => 'Con tu coach',
        'generic_templates' => 'Plantillas genericas',
    ],

    // -------------------------------------------------------------------------
    // Phase Navigation Pills
    // -------------------------------------------------------------------------
    'nav' => [
        'f01_name'  => 'Diagnostico',
        'f01_range' => 'Semanas 1-2',
        'f02_name'  => 'Diseno',
        'f02_range' => '48-72h',
        'f03_name'  => 'Ejecucion',
        'f03_range' => '9 semanas',
        'f04_name'  => 'Resultados',
        'f04_range' => 'Semana 12+',
    ],

    // -------------------------------------------------------------------------
    // FASE 01 — Diagnostico
    // -------------------------------------------------------------------------
    'fase01' => [
        'label'    => 'FASE 01',
        'range'    => 'Semanas 1-2',
        'title'    => 'DIAGNOSTICO',
        'subtitle' => 'Analisis de punto de partida',

        'description' => 'El diagnostico no es un formulario de bienvenida. Es la base de todo. Sin datos reales sobre tu cuerpo, tu historial y tus habitos, cualquier programa es una suposicion. Aqui recolectamos toda la informacion que necesitamos para disenar algo que realmente funcione para ti.',

        'badge_delivery'   => 'entrega',
        'badge_interview'  => 'entrevista',

        'checklist_title' => 'Que evaluamos',
        'checklist' => [
            'Formulario de diagnostico profundo — 40+ variables sobre tu cuerpo, habitos y estilo de vida',
            'Analisis de composicion corporal — peso, porcentaje de grasa, masa muscular estimada',
            'Evaluacion completa de historial de entrenamiento — que has hecho, que funciono y que no',
            'Entrevista 1:1 con tu coach asignado — 30 minutos de analisis en profundidad',
            'Analisis nutricional inicial — habitos actuales, preferencias y restricciones',
            'Registro de lesiones, limitaciones fisicas o condiciones medicas relevantes',
        ],

        'sidebar_title'    => 'Tu Ficha de Diagnostico Personalizada',
        'sidebar_subtitle' => 'Disponible en tu portal — 48h despues de completar el formulario',
        'sidebar_items' => [
            'Ficha de diagnostico completa con analisis de tu punto de partida real',
            'Identificacion de tus principales areas de mejora prioritarias',
            'Protocolo inicial de preparacion (habitos basicos para las primeras 48h)',
            'Acceso a tu portal de cliente con tus datos cargados',
            'Confirmacion del plan y modalidad de seguimiento seleccionada',
        ],
        'sidebar_footnote' => 'Sin datos reales no hay programa real. La mayoria de programas online fallan porque asumen que todos son iguales. En WellCore, el diagnostico es el paso mas importante.',
    ],

    // -------------------------------------------------------------------------
    // FASE 02 — Diseno
    // -------------------------------------------------------------------------
    'fase02' => [
        'label'    => 'FASE 02',
        'range'    => '48-72h',
        'title'    => 'DISEÑO DEL PROGRAMA',
        'subtitle' => 'Construccion basada en tus datos',

        'description' => 'Con los datos del diagnostico, tu coach disena tu programa completo. No es una plantilla modificada. Es una construccion desde cero basada en tus metricas, tu disponibilidad y tu objetivo especifico. El programa se entrega en tu portal antes de que empiece la ejecucion.',

        'badge_pdf'     => 'interactivo',
        'badge_custom'  => 'personalizado',

        'checklist_title' => 'Componentes del programa',
        'checklist' => [
            'Programa de entrenamiento semanal — dias, ejercicios, series, repeticiones, descansos',
            'Plan nutricional (plan Elite y Base) — calorias, macros y distribucion de comidas',
            'Protocolo de habitos (plan Elite) — rutinas de sueño, hidratacion y gestion del estres',
            'Calendario semanal estructurado — que hacer cada dia',
            'Guia de progresion — como avanzar semana a semana',
        ],

        'sidebar_title'    => 'Segun tu plan',
        'sidebar_subtitle' => 'PDF interactivo en tu portal — 48 a 72h despues del diagnostico',
        'plan_inicial_name' => 'Plan Inicial',
        'plan_inicial_desc' => 'Programa de entrenamiento + guia de nutricion basica',
        'plan_base_name'    => 'Plan Base',
        'plan_base_desc'    => 'Programa completo + plan nutricional detallado + seguimiento semanal',
        'plan_elite_name'   => 'Plan Elite',
        'plan_elite_desc'   => 'Todo lo anterior + protocolo de habitos + check-ins quincenales 1:1',
        'sidebar_footnote'  => 'Tu coach revisa el programa contigo en una llamada corta antes de que empieces. Si algo no tiene sentido para tu situacion, se ajusta antes de ejecutar.',
    ],

    // -------------------------------------------------------------------------
    // FASE 03 — Ejecucion
    // -------------------------------------------------------------------------
    'fase03' => [
        'label'    => 'FASE 03',
        'range'    => '9 semanas',
        'title'    => 'EJECUCION Y SEGUIMIENTO',
        'subtitle' => 'Donde pasan los resultados',

        'description' => 'Esta es la fase donde todo sucede. Son 9 semanas de ejecucion activa, seguimiento constante y ajustes en tiempo real. Tu coach no desaparece despues de entregarte el PDF. Esta presente en cada semana para analizar tu progreso y corregir el rumbo antes de que los problemas se acumulen.',

        'badge_tracking'   => 'seguimiento',
        'badge_wa'         => 'con tu coach',

        'weekly_cycle_title' => 'Ciclo de ajuste semanal',
        'cycle_step1' => 'Semana de entrenamiento',
        'cycle_step2' => 'Check-in de progreso',
        'cycle_step3' => 'Analisis del coach',
        'cycle_step4' => 'Ajuste del programa',

        'includes_title' => 'Que incluye',
        'includes' => [
            'Seguimiento semanal por WhatsApp directo con tu coach asignado',
            'Ajustes al programa de entrenamiento segun tu respuesta semana a semana',
            'Check-ins quincenales 1:1 en videollamada (plan Elite)',
            'Portal de cliente con registro de metricas: peso, medidas, rendimiento, fotos',
            'Tiempo de respuesta garantizado — maximo 24h en dias habiles',
        ],

        'how_it_works_title' => 'Como funciona el seguimiento',
        'how_it_works' => [
            'Cada domingo recibes un check-in de tu coach con preguntas especificas sobre la semana',
            'Respondes el check-in con tus datos: entrenamientos, alimentacion, sueño, energia',
            'Tu coach analiza los datos y ajusta el programa para la semana siguiente',
            'El lunes tienes tu programa actualizado listo en el portal antes de empezar',
            'Las metricas se actualizan en tu panel de cliente en tiempo real',
        ],

        'highlight' => 'La diferencia entre un programa que funciona y uno que no es el seguimiento. Sin ajustes, tu cuerpo se adapta al programa y los resultados se frenan. El modelo WellCore esta disenado para que eso no pase.',
    ],

    // -------------------------------------------------------------------------
    // FASE 04 — Resultados
    // -------------------------------------------------------------------------
    'fase04' => [
        'label'    => 'FASE 04',
        'range'    => 'Semana 12+',
        'title'    => 'RESULTADOS Y PROYECCION',
        'subtitle' => 'El punto de llegada — y de partida',

        'description' => 'La semana 12 no es solo el final — es donde medimos todo y proyectamos el siguiente ciclo. Comparamos punto de inicio vs. punto actual con datos objetivos, no impresiones. Si decides continuar (y la mayoria lo hace), empezamos un nuevo ciclo con todo lo aprendido.',

        'stat1_label' => 'Perdida de grasa promedio',
        'stat2_label' => 'Reduccion % grasa corporal',
        'stat3_label' => 'Adherencia promedio',
        'stat4_label' => 'Renuevan segundo ciclo',

        'eval_title' => 'Semana 12: evaluacion final',
        'eval' => [
            'Evaluacion final completa — las mismas metricas del dia 1 medidas nuevamente',
            'Comparacion visual y numerica inicial vs. final en tu portal',
            'Informe de rendimiento — que funciono, que se ajusto y por que',
            'Llamada de cierre 1:1 con tu coach — analisis del ciclo completo',
        ],

        'next_title' => 'Que sigue despues',
        'next' => [
            'Opcion de renovar con un nuevo ciclo de 12 semanas basado en tus nuevos datos',
            'Cambio de plan si tu nivel o necesidades evolucionaron',
            'Descuento de lealtad para clientes que renuevan dentro de los 30 dias',
            'Tu historial de datos permanece en el portal — el proximo ciclo empieza con ventaja',
        ],
    ],

    // -------------------------------------------------------------------------
    // FAQ
    // -------------------------------------------------------------------------
    'faq' => [
        'title'    => 'PREGUNTAS FRECUENTES',
        'subtitle' => 'Lo que mas preguntan.',

        'q1' => [
            'question' => '¿Cuanto tiempo tengo que dedicar por semana?',
            'answer'   => 'El programa se disena segun tu disponibilidad real. En el diagnostico declaras cuantas horas tienes disponibles por semana y el programa se construye sobre eso. El minimo practico es 3 sesiones de 45-60 minutos por semana. Si tienes mas disponibilidad, la aprovechamos. Si tienes menos, optimizamos lo que hay.',
        ],
        'q2' => [
            'question' => '¿El seguimiento es realmente semanal o solo al final?',
            'answer'   => 'Es semanal. Cada domingo tu coach te envia un check-in con preguntas especificas sobre tu semana. Tu respondes con tus datos y el lunes tienes el programa de la semana siguiente actualizado si fue necesario. No es un seguimiento pasivo: es una comunicacion activa y bidireccional cada semana de las 12.',
        ],
        'q3' => [
            'question' => '¿Que pasa si no puedo cumplir alguna semana?',
            'answer'   => 'La vida pasa. Viajes, enfermedad, trabajo intenso. Lo que diferencia a WellCore es que cuando eso ocurre, tu coach lo sabe porque lo comunicaste en el check-in y ajusta el plan en consecuencia. El programa no es rigido: se adapta a tu realidad semana a semana sin perder el hilo de los objetivos.',
        ],
        'q4' => [
            'question' => '¿Puedo cambiar de plan durante el proceso?',
            'answer'   => 'Si. El cambio de plan se aplica al inicio del siguiente ciclo, no en medio de las 12 semanas activas. Si decides que necesitas mas seguimiento del que contrataste, puedes hacer upgrade antes de que empiece tu nuevo ciclo. Tu coach te orienta sobre cual plan tiene mas sentido para tu situacion.',
        ],
    ],

    // -------------------------------------------------------------------------
    // Final CTA
    // -------------------------------------------------------------------------
    'cta' => [
        'label'         => 'El siguiente paso',
        'title'         => 'EMPIEZA HOY',
        'description'   => 'El proceso WellCore comienza con tu diagnostico. En menos de 48 horas tendras la base de tu programa personalizado lista. Sin compromisos a largo plazo, sin contratos.',
        'btn_primary'   => 'Comenzar el proceso',
        'btn_secondary' => 'Ver planes y precios',
        'footnote'      => 'Sin tarjeta de credito · Cancela cuando quieras',
    ],

];
