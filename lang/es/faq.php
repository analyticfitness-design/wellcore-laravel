<?php

return [

    // SEO
    'title'           => 'Preguntas Frecuentes — WellCore Fitness',
    'meta_description'=> 'Todo lo que necesitas saber sobre WellCore: planes, pagos, coaching online, entrenamiento personalizado y soporte. Sin rodeos.',

    // ── HERO ──────────────────────────────────────────────────────────────────
    'hero_eyebrow'   => 'FAQ · LO QUE QUIERES SABER',
    'hero_h1_line1'  => 'PREGUNTA',
    'hero_h1_accent' => 'TODO.',
    'hero_sub'       => 'Sin rodeos. Sin disclaimers. Las respuestas que necesitas, directas.',

    // ── BACK COMPAT (lang anterior — mantenido para tooling externo) ─────────
    'hero_h1'   => 'PREGUNTAS',
    // Note: hero_h1_accent reused above (FRECUENTES → TODO.) — old callers fall through harmless.

    // ── SEARCH ────────────────────────────────────────────────────────────────
    'buscar'              => 'Buscar pregunta...',
    'meta_total'          => ':count respuestas',
    'meta_categories'     => ':count categorías',
    'meta_separator'      => '·',
    'search_clear_aria'   => 'Limpiar búsqueda',
    'search_label'        => 'Buscar en preguntas frecuentes',

    // ── TABS ──────────────────────────────────────────────────────────────────
    'tabs' => [
        'general'       => 'General',
        'planes'        => 'Planes',
        'pagos'         => 'Pagos',
        'entrenamiento' => 'Entrenamiento',
        'soporte'       => 'Soporte',
    ],

    // ── ITEMS (25 preguntas — contenido v1 preservado verbatim) ──────────────
    'items' => [
        // ── general ──
        ['id' => 'g1', 'cat' => 'general', 'q' => 'Que es WellCore Fitness?',
         'a' => 'WellCore es una plataforma de coaching fitness online basada en ciencia. Cada programa es 100% personalizado por un coach certificado, adaptado a tus objetivos, nivel de experiencia y estilo de vida.'],
        ['id' => 'g2', 'cat' => 'general', 'q' => 'Como funciona el coaching online?',
         'a' => 'Al inscribirte recibes un programa de entrenamiento y nutricion personalizado. Tu coach monitorea tu progreso, ajusta el plan y se comunica contigo via chat. Los check-ins semanales permiten hacer seguimiento continuo de tu avance.'],
        ['id' => 'g3', 'cat' => 'general', 'q' => 'Necesito experiencia previa?',
         'a' => 'No. Nuestros programas se adaptan a cualquier nivel de experiencia. Los principiantes reciben guias detalladas de ejecucion y videos de referencia para cada ejercicio.'],
        ['id' => 'g4', 'cat' => 'general', 'q' => 'Puedo entrenar en casa o necesito gimnasio?',
         'a' => 'Ambos. Al iniciar indicas tu equipamiento disponible y tu coach adapta el programa. La mayoria de planes estan optimizados para gimnasio, pero tambien hay alternativas para entrenamiento en casa.'],
        ['id' => 'g5', 'cat' => 'general', 'q' => 'En que paises estan disponibles?',
         'a' => 'Operamos en toda LATAM: Colombia, Mexico, Chile, Peru, Argentina y Ecuador. El coaching es 100% online, asi que puedes entrenar desde cualquier lugar.'],

        // ── planes ──
        ['id' => 'p1', 'cat' => 'planes', 'q' => 'Que planes ofrecen?',
         'a' => 'Tres planes principales: <strong>Esencial</strong> ($65/mes, programacion basica), <strong>Metodo</strong> ($95/mes, seguimiento semanal + comunidad) y <strong>Elite</strong> ($150/mes, coaching 1:1 + video check-ins). Tambien ofrecemos <strong>RISE</strong> (programa intensivo de 12 semanas) y <strong>Presencial</strong> (solo en Bucaramanga).'],
        ['id' => 'p2', 'cat' => 'planes', 'q' => 'Que plan me conviene?',
         'a' => '<strong>Esencial</strong> si ya tienes experiencia y solo necesitas programacion. <strong>Metodo</strong> es el mas popular, ideal con seguimiento semanal y acceso a comunidad. <strong>Elite</strong> para quienes buscan el maximo nivel de personalizacion con coaching 1:1.'],
        ['id' => 'p3', 'cat' => 'planes', 'q' => 'Que es el programa RISE?',
         'a' => 'Programa intensivo de 12 semanas con entrenamiento periodizado, nutricion detallada, check-ins semanales obligatorios y comunidad exclusiva. Disenado para una transformacion acelerada con resultados visibles.'],
        ['id' => 'p4', 'cat' => 'planes', 'q' => 'Cada cuanto se actualiza mi programa?',
         'a' => 'En el plan <strong>Esencial</strong>, los ajustes son mensuales. En <strong>Metodo</strong> y <strong>Elite</strong>, los ajustes son semanales basados en tus check-ins. El programa de entrenamiento se renueva cada 4-6 semanas segun la periodizacion planificada.'],
        ['id' => 'p5', 'cat' => 'planes', 'q' => 'Puedo cambiar de plan?',
         'a' => 'Si. Puedes subir o bajar de plan en cualquier momento. El cambio aplica en el siguiente periodo de facturacion.'],

        // ── pagos ──
        ['id' => 'pa1', 'cat' => 'pagos', 'q' => 'Cuales son los metodos de pago?',
         'a' => 'Tarjeta de credito/debito y transferencias bancarias via Wompi. Precios en USD. Para Colombia tambien aceptamos COP al tipo de cambio del dia.'],
        ['id' => 'pa2', 'cat' => 'pagos', 'q' => 'Puedo cancelar en cualquier momento?',
         'a' => 'Si. La cancelacion es efectiva al final del periodo vigente. Sin contratos ni penalizaciones. RISE es la excepcion ya que es un compromiso de 12 semanas.'],
        ['id' => 'pa3', 'cat' => 'pagos', 'q' => 'Ofrecen reembolsos?',
         'a' => 'Garantia de 7 dias para nuevos clientes. Despues de ese periodo, no hay reembolso pero mantienes acceso hasta el fin del periodo pagado. Consulta nuestra <a href=":url" class="text-wc-accent hover:underline">politica de reembolso</a> completa.'],
        ['id' => 'pa4', 'cat' => 'pagos', 'q' => 'El pago es mensual o anual?',
         'a' => 'Mensual automatico. No ofrecemos planes anuales actualmente.'],
        ['id' => 'pa5', 'cat' => 'pagos', 'q' => 'Que pasa si falla mi pago?',
         'a' => 'Se reintenta automaticamente. Si persiste, recibes una notificacion para actualizar tu metodo de pago. El acceso se pausa tras 5 dias sin pago exitoso.'],

        // ── entrenamiento ──
        ['id' => 'e1', 'cat' => 'entrenamiento', 'q' => 'Que incluye el plan nutricional?',
         'a' => 'Calculo de macros personalizado, estructura de comidas adaptada a tu horario, lista de alimentos y alternativas. Es un marco flexible, no una dieta rigida. Los ajustes se hacen segun tu progreso.'],
        ['id' => 'e2', 'cat' => 'entrenamiento', 'q' => 'Como son los check-ins?',
         'a' => 'Formulario semanal con peso, medidas, fotos de progreso y feedback del entrenamiento. Tu coach revisa toda la informacion y ajusta el programa si es necesario.'],
        ['id' => 'e3', 'cat' => 'entrenamiento', 'q' => 'Puedo hacer cardio y pesas al mismo tiempo?',
         'a' => 'Si. El programa integra ambos segun tu objetivo. Para ganancia muscular se prioriza fuerza; para perdida de grasa se balancea cardio y pesas de forma estrategica.'],
        ['id' => 'e4', 'cat' => 'entrenamiento', 'q' => 'Que hago si tengo una lesion?',
         'a' => 'Informa a tu coach inmediatamente. Se ajusta el programa para trabajar alrededor de la lesion. En casos graves se recomienda consulta medica antes de continuar el entrenamiento.'],
        ['id' => 'e5', 'cat' => 'entrenamiento', 'q' => 'Cuanto tiempo dura cada sesion de entrenamiento?',
         'a' => 'Tipicamente 45-75 minutos dependiendo del plan y objetivo. Incluye calentamiento y vuelta a la calma. Frecuencia sugerida: 3-6 dias por semana.'],

        // ── soporte ──
        ['id' => 's1', 'cat' => 'soporte', 'q' => 'Como contacto a soporte?',
         'a' => 'Email <a href="mailto:info@wellcorefitness.com" class="text-wc-accent hover:underline">info@wellcorefitness.com</a>, chat dentro del dashboard, o WhatsApp. Los clientes Elite tienen soporte prioritario con respuesta en menos de 12 horas.'],
        ['id' => 's2', 'cat' => 'soporte', 'q' => 'Puedo cambiar de coach?',
         'a' => 'Si. Solicita el cambio a traves de soporte y se asigna un nuevo coach en 48 horas. Sin costo adicional.'],
        ['id' => 's3', 'cat' => 'soporte', 'q' => 'Que pasa si no puedo entrenar por viaje o enfermedad?',
         'a' => 'Puedes pausar tu plan hasta por 30 dias sin costo. Contacta a soporte para activar la pausa y se retoma cuando estes listo.'],
        ['id' => 's4', 'cat' => 'soporte', 'q' => 'Como accedo a la plataforma?',
         'a' => 'Via web en wellcorefitness.com. Inicia sesion con tu email y contrasena. Puedes instalar la app como PWA en tu telefono para acceso rapido desde la pantalla de inicio.'],
        ['id' => 's5', 'cat' => 'soporte', 'q' => 'Ofrecen soporte en otros idiomas?',
         'a' => 'Actualmente solo en espanol. Estamos trabajando en soporte en ingles y portugues para 2026.'],
    ],

    // ── EMPTY STATE ───────────────────────────────────────────────────────────
    'empty_title'    => 'Sin resultados',
    'empty_body'     => 'No encontramos respuestas para ":query". Intenta con otra palabra o escríbenos directo.',
    'no_results'     => 'No se encontraron resultados para ":query".',

    // ── CTA ───────────────────────────────────────────────────────────────────
    'cta_eyebrow'    => 'AÚN CON DUDAS',
    'cta_h2'         => 'NO ENCONTRASTE TU',
    'cta_h2_accent'  => 'RESPUESTA?',
    'cta_sub'        => 'Escríbenos directo. Te responde una persona, no un bot.',
    'cta_contact'    => 'Contactar',
    'cta_whatsapp'   => 'WhatsApp directo',
    'cta_whatsapp_msg' => 'Hola WellCore, tengo una pregunta sobre los planes.',
];
