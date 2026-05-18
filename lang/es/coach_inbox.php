<?php

return [

    // ─── Shared / context label ──────────────────────────────────────────
    'context_main' => 'PRINCIPAL',

    // ─────────────────────────────────────────────────────────────────────
    // CheckinReview.vue — revisión de check-ins de clientes
    // ─────────────────────────────────────────────────────────────────────
    'checkins_title' => 'CHECK-INS',
    'checkins_subtitle_pending_one' => '1 respuesta pendiente',
    'checkins_subtitle_pending_other' => '{count} respuestas pendientes',
    'checkins_subtitle_all_caught_up' => 'Todos al día',

    'checkins_filter_show_all' => 'Mostrando todos',
    'checkins_filter_pending_only' => 'Solo pendientes',

    'checkins_load_error' => 'No se pudieron cargar los check-ins.',
    'checkins_reply_error' => 'No se pudo enviar la respuesta. Intenta de nuevo.',

    'checkins_badge_pending' => 'Pendiente',
    'checkins_badge_replied' => 'Respondido',

    'checkins_metric_wellbeing' => 'Bienestar',
    'checkins_metric_days_trained' => 'Días entrenados',
    'checkins_metric_days_trained_of' => 'de 7 días',
    'checkins_metric_nutrition' => 'Nutrición',
    'checkins_metric_rpe' => 'RPE',
    'checkins_metric_rpe_label' => 'esfuerzo percibido',

    'checkins_client_comment_label' => 'Comentario del cliente',
    'checkins_your_reply_label' => 'Tu respuesta',
    'checkins_replied_at_prefix' => '— {when}',
    'checkins_replied_at_now' => 'Ahora',

    'checkins_reply_form_label' => 'Tu respuesta',
    'checkins_reply_placeholder' => 'Escribe tu respuesta al check-in…',
    'checkins_reply_send' => 'Enviar respuesta',
    'checkins_reply_cancel' => 'Cancelar',
    'checkins_reply_cta' => 'Responder',

    'checkins_empty_pending_title' => 'Todos los check-ins respondidos',
    'checkins_empty_pending_subtitle' => 'Excelente trabajo — tus clientes están al día.',
    'checkins_empty_all_title' => 'No hay check-ins registrados',
    'checkins_empty_all_subtitle' => 'Tus clientes aún no han enviado check-ins.',

    // ─────────────────────────────────────────────────────────────────────
    // MessageCenter.vue — mensajería con clientes
    // ─────────────────────────────────────────────────────────────────────
    'messages_title' => 'MENSAJES',
    'messages_subtitle' => 'Conversaciones con tus clientes',

    'messages_panel_clients_title' => 'Clientes',
    'messages_panel_clients_error' => 'Error al cargar clientes.',
    'messages_panel_retry' => 'Reintentar',

    'messages_no_messages_preview' => 'Sin mensajes',
    'messages_empty_clients_title' => 'Sin clientes asignados',
    'messages_empty_clients_subtitle' => 'Cuando se te asigne un cliente, aparecerá aquí.',

    'messages_client_no_plan' => 'Sin plan',
    'messages_live_indicator' => 'En vivo',

    'messages_load_error' => 'No se pudieron cargar los mensajes.',

    'messages_empty_thread_title' => 'Inicia la conversación',
    'messages_empty_thread_subtitle' => 'Envía el primer mensaje para empezar el chat.',

    'messages_send_error' => 'No se pudo enviar el mensaje.',

    'messages_templates_title' => 'Plantillas',
    'messages_templates_tooltip' => 'Plantillas de respuesta rápida',
    'messages_templates_search_placeholder' => 'Buscar plantilla…',
    'messages_templates_no_results' => 'No se encontraron plantillas para "{query}"',
    'messages_templates_footer_one' => '1 plantilla disponible',
    'messages_templates_footer_other' => '{count} plantillas disponibles',

    'messages_input_placeholder' => 'Escribe un mensaje…',

    'messages_sent_now' => 'Ahora',

    'messages_no_client_selected_title' => 'Selecciona un cliente',
    'messages_no_client_selected_subtitle' => 'Elige un cliente del panel izquierdo para ver la conversación.',

    // Plantillas (response templates)
    'tpl_welcome_title' => 'Bienvenida',
    'tpl_welcome_body' => '¡Bienvenida a WellCore! Estoy aquí para guiarte en tu transformación. Revisa tu plan de entrenamiento y no dudes en escribirme si tenés preguntas.',
    'tpl_checkin_reminder_title' => 'Recordatorio check-in',
    'tpl_checkin_reminder_body' => 'Recordá completar tu check-in semanal para que pueda evaluar tu progreso y ajustar el plan si es necesario. Tu feedback es clave para tus resultados.',
    'tpl_congrats_title' => 'Felicitación general',
    'tpl_congrats_body' => 'Quería felicitarte por tu compromiso y dedicación. Los resultados se están notando y quiero que sepas que tu esfuerzo vale la pena. ¡Seguí así!',
    'tpl_routine_reminder_title' => 'Recordatorio rutina',
    'tpl_routine_reminder_body' => 'Recordá que tu nueva rutina ya está disponible en el dashboard. Revisá los ejercicios y avisame si tenés alguna duda antes de empezar.',
    'tpl_weekly_followup_title' => 'Seguimiento semanal',
    'tpl_weekly_followup_body' => '¿Cómo vas con el entrenamiento esta semana? Quería hacer un seguimiento rápido. Contame cómo te has sentido y si has tenido algún problema con los ejercicios.',
    'tpl_availability_title' => 'Disponibilidad',
    'tpl_availability_body' => 'Estoy disponible para resolver cualquier duda que tengas. Podés escribirme por aquí o crear un ticket si necesitás algo específico. Estamos para ayudarte.',

    // ─────────────────────────────────────────────────────────────────────
    // FoodPhotoReview.vue — revisión de fotos de comida
    // ─────────────────────────────────────────────────────────────────────
    'food_title' => 'FOTOS DE COMIDA',
    'food_pending_count_one' => '1 pendiente de revisión',
    'food_pending_count_other' => '{count} pendientes de revisión',

    'food_filter_all_clients' => 'Todos los clientes',
    'food_filter_view_pending' => 'Ver pendientes',
    'food_filter_view_reviewed' => 'Ver revisadas',

    'food_load_error' => 'No se pudieron cargar las fotos.',
    'food_react_error' => 'No se pudo registrar la reacción. Intenta de nuevo.',
    'food_save_note_error' => 'No se pudo guardar la nota.',

    'food_empty_pending_title' => 'Sin fotos pendientes',
    'food_empty_pending_subtitle' => '¡Buen trabajo! Estás al día con la revisión de fotos de tus clientes.',
    'food_empty_reviewed_title' => 'No has revisado fotos aún',
    'food_empty_reviewed_subtitle' => 'Cuando apruebes o sugieras mejoras, las fotos pasarán al historial.',

    'food_photo_alt' => 'Foto de {meal}',
    'food_client_description' => 'Descripción del cliente',

    'food_reaction_good' => 'Bien',
    'food_reaction_improve' => 'Por mejorar',
    'food_reaction_seen' => 'Vista sin reacción',
    'food_btn_good' => 'Bien',
    'food_btn_improve' => 'Mejorar',

    'food_note_placeholder' => 'Nota opcional para el cliente',
    'food_note_saving' => 'Guardando…',
    'food_showing_latest' => 'Mostrando los 40 más recientes',
    'food_retry' => 'Reintentar',

    // ─────────────────────────────────────────────────────────────────────
    // Community.vue — Comunidad coach (5 tabs)
    // ─────────────────────────────────────────────────────────────────────
    'community_title' => 'Comunidad',
    'community_subtitle' => 'La comunidad de tus clientes. Modera, motiva, conecta.',
    'community_message_team' => 'Mensaje al equipo',

    'community_tab_pulse' => 'Latido del Equipo',
    'community_tab_posts' => 'Posts',
    'community_tab_threads' => 'Conversaciones',
    'community_tab_stories' => 'Pulsos',
    'community_tab_wins' => 'Logros',

    'community_quick_msg_template' => 'Hola {name}, vi que llevás {days} días sin actividad. ¿Cómo te puedo ayudar?',
    'community_quick_msg_days_default' => 'unos',

    // Latido tab
    'pulse_retry' => 'Reintentar',
    'pulse_empty_title' => 'Tu equipo aún no tiene actividad',
    'pulse_empty_subtitle' => 'Cuando uno de tus clientes rompa un PR o complete un check-in, esta vista se llenará de insights.',
    'pulse_team_ring_label' => 'Latido del Equipo',
    'pulse_computed_at' => 'Calculado a las {time} · refresca cada 60s',
    'pulse_top_performers_title' => 'Top performers (7D)',
    'pulse_no_top_performers' => 'Aún no hay top performers esta semana.',
    'pulse_at_risk_title' => 'Riesgo de churn (5+ días sin actividad)',
    'pulse_refresh_now' => 'Actualizar ahora',

    // Posts tab
    'posts_filter_all' => 'Todos',
    'posts_filter_pinned' => 'Fijados',
    'posts_filter_reported' => 'Reportados',
    'posts_filter_achievements' => 'Logros',
    'posts_filter_prs' => 'PRs',

    'posts_new_post_one' => '↑ 1 post nuevo',
    'posts_new_post_other' => '↑ {count} posts nuevos',

    'posts_retry' => 'Reintentar',
    'posts_empty_title' => 'Tu equipo aún no postea',
    'posts_empty_subtitle' => 'Cuando un cliente comparta un PR, foto o pensamiento, aparecerá aquí.',
    'posts_empty_cta' => 'Mensaje al equipo',

    'posts_author_fallback' => 'Cliente',
    'posts_reactions_count_one' => '1 reacción',
    'posts_reactions_count_other' => '{count} reacciones',
    'posts_comments_count_one' => '1 comentario',
    'posts_comments_count_other' => '{count} comentarios',
    'posts_report_count_one' => '⚠️ 1 reporte',
    'posts_report_count_other' => '⚠️ {count} reportes',

    'posts_loading_more' => 'Cargando más…',

    // Conversaciones tab
    'threads_filter_all' => 'Todos',
    'threads_filter_unanswered' => 'Sin respuesta de coach',
    'threads_filter_large' => '+50 comentarios',
    'threads_filter_conflicted' => 'Conflictos',

    'threads_empty_title' => 'Sin conversaciones recientes',
    'threads_empty_subtitle' => 'Anímalos a interactuar con un mensaje al equipo.',

    'threads_comments_count_one' => '1 comentario',
    'threads_comments_count_other' => '{count} comentarios',
    'threads_participants_count_one' => '1 participante',
    'threads_participants_count_other' => '{count} participantes',

    'threads_time_ago_minutes' => 'hace {value}m',
    'threads_time_ago_hours' => 'hace {value}h',
    'threads_time_ago_days' => 'hace {value}d',

    'threads_status_replied' => 'Respondiste',
    'threads_status_unanswered' => '⚠️ Sin respuesta',
    'threads_status_conflicted' => 'Atención',

    // Pulsos tab
    'stories_empty_title' => 'Sin pulsos activos',
    'stories_empty_subtitle' => 'Los pulsos duran 24-48h. Cuando un cliente suba uno, aparecerá aquí en orden de prioridad.',
    'stories_client_fallback' => 'Cliente',

    // Logros tab
    'wins_period_week' => 'Esta semana',
    'wins_period_month' => 'Este mes',
    'wins_period_all' => 'Histórico',

    'wins_streak_banner_one' => 'Equipo en racha — 1 PR y {achievements} logros este período',
    'wins_streak_banner_other' => 'Equipo en racha — {prs} PRs y {achievements} logros este período',

    'wins_empty_title' => 'Aún no hay logros',
    'wins_empty_subtitle' => 'Sé proactivo: motiva al cliente que esté cerca de un PR.',

    'wins_pr_label' => 'PR de {exercise}: {weight}kg',
    'wins_congratulate' => 'Felicitar',
    'wins_congrats_sent' => 'Felicitación enviada a {name}.',
    'wins_congrats_error' => 'No pudimos enviar la felicitación.',
];
