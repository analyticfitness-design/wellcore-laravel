<?php

return [
    // ─── Dashboard ────────────────────────────────────────────────────────
    // Loading / error / retry
    'error_loading' => 'Error al cargar el dashboard. Intenta de nuevo.',
    'retry'         => 'Reintentar',

    // Hero alert card
    'hero_eyebrow_attention' => 'Atención requerida',
    'hero_eyebrow_attention_desktop' => 'Atención requerida hoy',
    'hero_eyebrow_on_track'  => 'Al día',
    'hero_clients_needing_attention' => '{n} cliente necesita atención|{n} clientes necesitan atención',
    'hero_title_all_clear'  => 'AL DÍA · SIN PENDIENTES',
    'hero_chip_pending_checkins' => '{n} check-ins',
    'hero_chip_pending_checkins_desktop' => '{n} check-ins pendientes',
    'hero_chip_unread'      => '{n} sin leer',
    'hero_cta_review'       => 'Ver urgente',
    'hero_cta_checkins'     => 'Revisar check-ins',

    // Quick actions (grouped action list)
    'qa_checkins'  => 'Check-ins',
    'qa_messages'  => 'Mensajes',
    'qa_tickets'   => 'Tickets',
    'qa_analytics' => 'Analítica',

    // KPI tiles
    'kpi_active_clients'    => 'Clientes Activos',
    'kpi_pending_checkins'  => 'Check-ins',
    'kpi_unread_messages'   => 'Mensajes',
    'kpi_open_tickets'      => 'Tickets',
    'kpi_open_tickets_desktop' => 'Tickets Abiertos',

    // Attention section
    'attention_title'        => 'Atención urgente',
    'attention_count'        => '{n} cliente|{n} clientes',
    'attention_sub_unanswered' => 'Sin responder: {value}',
    'attention_pending_placeholder' => 'pendiente',
    'attention_eta_days'     => '{n}d',
    'attention_empty_title'  => 'Todos los check-ins respondidos',
    'attention_empty_sub'    => 'Buen trabajo',
    'urgent_card_cta'        => 'Responder',

    // Today activity
    'activity_title'         => 'Actividad hoy',
    'activity_event_checkin' => '{name} envió su check-in semanal',
    'activity_event_checkin_short' => '{name} envió su check-in',
    'activity_event_training' => '{name} registró entrenamiento',
    'activity_event_message' => 'Nuevo mensaje de {name}',
    'activity_empty_title'   => 'Sin actividad reciente',
    'activity_empty_sub'     => 'Sin actividad en las últimas 24 horas',

    // Weekly analysis
    'weekly_title'           => 'Análisis de la semana',
    'weekly_checkins_label'  => 'Check-ins respondidos · esta semana',
    'weekly_aria'            => 'Check-ins por día',
    'weekly_dow_short'       => ['L', 'M', 'X', 'J', 'V', 'S', 'D'],
    'weekly_dow_long'        => ['Lunes', 'Martes', 'Miérc.', 'Jueves', 'Viernes', 'Sábado', 'Domingo'],

    // Messages & PRs
    'messages_title'         => 'Mensajes · PRs recientes',
    'messages_see_all'       => 'Ver todos →',
    'messages_empty_title'   => 'Sin mensajes recientes',
    'messages_empty_sub'     => 'Cuando un cliente responda, aparecerá aquí',

    // Tickets section
    'tickets_title'          => 'Tickets',
    'tickets_see_all'        => 'Ver todos →',
    'tickets_empty_title'    => 'Sin tickets abiertos',
    'tickets_empty_sub'      => 'Todos los tickets están resueltos',

    // ─── Client List (ClientList.vue) ─────────────────────────────────────
    'list_context'           => 'MIS CLIENTES',
    'list_title'             => 'CLIENTES',
    'list_subtitle'          => '{n} cliente activo|{n} clientes activos',
    'list_view_kanban'       => 'Vista Kanban',
    'list_search_placeholder' => 'Buscar por nombre...',
    'list_filter_all'        => 'Todos',
    'list_filter_active'     => 'Activos',
    'list_filter_risk'       => 'En riesgo',
    'list_filter_inactive'   => 'Inactivos',
    'list_no_plan'           => 'Sin plan',
    'list_pending_count'     => '{n} pendiente|{n} pendientes',
    'list_risk_badge'        => 'En riesgo',
    'list_last_checkin'      => 'Check-in: {value}',
    'list_last_message'      => 'Mensaje: {value}',
    'list_never'             => 'Nunca',
    'list_no_messages'       => 'Sin mensajes',
    'list_level_short'       => 'Nv. {n}',
    'list_detail_xp_total'   => 'XP Total',
    'list_detail_streak'     => 'Racha',
    'list_detail_streak_days' => '{n} días',
    'list_detail_start_date' => 'Fecha inicio',
    'list_detail_last_checkin' => 'Último check-in',
    'list_na'                => 'N/A',
    'list_action_checkins'   => 'Ver check-ins',
    'list_action_message'    => 'Enviar mensaje',
    'list_action_view_as'    => 'Ver como cliente',
    'list_restricted_title'  => 'Acciones restringidas',
    'list_restricted_sub'    => 'Requieren aprobación del equipo WellCore.',
    'list_request_deactivate' => 'Solicitar desactivación',
    'list_request_delete'    => 'Solicitar eliminación',
    'list_request_edit'      => 'Solicitar edición',
    'list_no_requests'       => 'Sin solicitudes previas.',
    'list_request_cancel'    => 'Cancelar',
    'list_admin_note'        => 'Admin: {note}',
    'list_empty_title'       => 'No se encontraron clientes',
    'list_empty_sub_search'  => 'No hay resultados para "{query}"',
    'list_empty_sub_default' => 'No tienes clientes asignados aún',

    // Request action labels (titles + short)
    'req_action_delete_title'     => 'Solicitar eliminación',
    'req_action_deactivate_title' => 'Solicitar desactivación',
    'req_action_edit_title'       => 'Solicitar edición',
    'req_action_delete_short'     => 'Eliminar',
    'req_action_deactivate_short' => 'Desactivar',
    'req_action_edit_short'       => 'Editar',

    // Request status labels
    'req_status_pending'   => 'Pendiente',
    'req_status_approved'  => 'Aprobada',
    'req_status_rejected'  => 'Rechazada',
    'req_status_cancelled' => 'Cancelada',

    // Request modal
    'req_modal_default_title' => 'Solicitud',
    'req_modal_client'      => 'Cliente:',
    'req_modal_reason'      => 'Razón',
    'req_modal_reason_placeholder' => 'Explica por qué solicitas esta acción (mínimo 10 caracteres)...',
    'req_modal_chars_min'   => '{n} / mínimo 10',
    'req_modal_min_chars_err' => 'La razón debe tener al menos 10 caracteres.',
    'req_modal_invalid'     => 'Datos inválidos.',
    'req_modal_generic_err' => 'No se pudo enviar la solicitud.',
    'req_modal_cancel'      => 'Cancelar',
    'req_modal_submit'      => 'Enviar solicitud',
    'req_modal_sending'     => 'Enviando...',
    'req_confirm_cancel'    => '¿Cancelar esta solicitud?',
    'req_cancelled_toast'   => 'Solicitud cancelada.',
    'req_cancel_failed'     => 'No se pudo cancelar.',
    'req_sent_toast'        => 'Solicitud enviada al equipo WellCore.',

    // Impersonation
    'impersonate_fail'      => 'No se pudo iniciar sesión como este cliente.',
    'impersonate_client_default' => 'Cliente',

    // ─── Client Kanban (ClientKanban.vue) ─────────────────────────────────
    'kanban_context'        => 'MIS CLIENTES',
    'kanban_title'          => 'KANBAN',
    'kanban_subtitle'       => '{n} cliente · Vista por actividad|{n} clientes · Vista por actividad',
    'kanban_search_placeholder' => 'Buscar cliente...',
    'kanban_view_list'      => 'Vista lista',
    'kanban_refresh'        => 'Actualizar',
    'kanban_col_new'        => 'Nuevos',
    'kanban_col_active'     => 'Activos',
    'kanban_col_risk'       => 'En Riesgo',
    'kanban_col_inactive'   => 'Inactivos',
    'kanban_card_pending_checkin' => '{n} check-in|{n} check-ins',
    'kanban_card_view_detail' => 'Ver detalle',
    'kanban_tooltip_activity' => 'Última actividad',
    'kanban_tooltip_checkin' => 'Último check-in',
    'kanban_tooltip_training' => 'Último entrenamiento',
    'kanban_activity_today' => 'Hoy',
    'kanban_activity_days'  => '{n}d',
    'kanban_empty_col_title' => 'Sin clientes',
    'kanban_empty_col_sub'  => 'Arrastra tarjetas aquí',
    'kanban_drag_hint'      => 'Arrastra las tarjetas entre columnas para reclasificar clientes',

    // Detail modal (inside ClientKanban)
    'detail_load_fail'      => 'No se pudo cargar el detalle del cliente.',
    'detail_close'          => 'Cerrar',
    'detail_xp_level'       => 'Nivel XP',
    'detail_xp_total'       => 'XP Total',
    'detail_streak'         => 'Racha (días)',
    'detail_start_date'     => 'Fecha inicio',
    'detail_last_checkin'   => 'Último check-in',
    'detail_wellbeing'      => 'Bienestar',
    'detail_active_plan'    => 'Plan activo',
    'detail_recent_notes'   => 'Notas recientes',
    'detail_action_checkins' => 'Check-ins',
    'detail_action_messages' => 'Mensajes',
    'detail_action_notes'   => 'Notas',
    'detail_impersonate'    => 'Ver portal como cliente',
    'detail_impersonate_loading' => 'Entrando…',
];
