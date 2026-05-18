<?php

return [

    // ═══════════════════════════════════════════════════════════════════
    // PLANS MANAGER (Coach/PlansManager.vue)
    // ═══════════════════════════════════════════════════════════════════

    'plans_context_label' => 'ÁREA DE TRABAJO',
    'plans_title' => 'PLANES',
    'plans_subtitle' => 'Tus templates y planes asignados',

    'plans_tickets_cta_msg' => 'Los planes nuevos se crean desde Tickets de Plan',
    'plans_tickets_cta_link' => 'Crear ticket →',

    'plans_tab_my_templates' => 'Mis Templates',
    'plans_tab_assigned' => 'Asignados',

    'plans_load_error' => 'No se pudieron cargar los planes. Intenta de nuevo.',
    'plans_retry' => 'Reintentar',

    'plans_stat_total' => 'Total',
    'plans_stat_training' => 'Entrenamiento',
    'plans_stat_nutrition' => 'Nutrición',
    'plans_stat_habits' => 'Hábitos',

    'plans_search_placeholder' => 'Buscar templates...',
    'plans_filter_all_types' => 'Todos los tipos',
    'plans_type_training' => 'Entrenamiento',
    'plans_type_nutrition' => 'Nutrición',
    'plans_type_habits' => 'Hábitos',
    'plans_type_supplements' => 'Suplementación',

    'plans_template_duration_na' => 'N/A',
    'plans_template_meta' => '{type} · {duration}',

    'plans_empty_templates_title' => 'Sin templates',
    'plans_empty_templates_subtitle' => 'No se encontraron templates con esos filtros',

    'plans_assigned_status_active' => 'Activo',
    'plans_assigned_status_finished' => 'Finalizado',
    'plans_assigned_meta' => '{plan} · {type}',

    'plans_empty_assigned_title' => 'Sin planes asignados',
    'plans_empty_assigned_subtitle' => 'Aún no has asignado planes a tus clientes',

    // ═══════════════════════════════════════════════════════════════════
    // PLAN TICKETS LIST (PlanTickets/CoachPlanTicketsList.vue)
    // ═══════════════════════════════════════════════════════════════════

    'tickets_context_label' => 'ÁREA DE TRABAJO',
    'tickets_title' => 'TICKETS DE PLAN',
    'tickets_subtitle' => 'Gestiona tus envíos al admin',
    'tickets_create_cta' => 'Crear ticket',

    'tickets_tab_all' => 'Todos',
    'tickets_tab_drafts' => 'Borradores',
    'tickets_tab_sent' => 'Enviados',
    'tickets_tab_under_review' => 'En revisión',
    'tickets_tab_completed' => 'Completados',
    'tickets_tab_rejected' => 'Rechazados',

    'tickets_plan_type_essential' => 'Esencial',
    'tickets_plan_type_method' => 'Método',
    'tickets_plan_type_elite' => 'Élite',

    'tickets_category_new_plan' => 'Plan nuevo',
    'tickets_category_adjustment' => 'Ajuste',

    'tickets_status_draft' => 'Borrador',
    'tickets_status_sent' => 'Enviado',
    'tickets_status_under_review' => 'En revisión',
    'tickets_status_completed' => 'Completado',
    'tickets_status_rejected' => 'Rechazado',

    'tickets_empty_msg' => 'Aún no has creado tickets. Empieza uno para tu primer cliente.',
    'tickets_empty_cta' => 'Crear nuevo ticket',

    'tickets_no_client_name' => 'Cliente sin nombre',
    'tickets_created_at' => 'Creado {date}',
    'tickets_submitted_at' => '· Enviado {date}',

    'tickets_action_duplicate' => 'Duplicar',
    'tickets_action_duplicate_progress' => '...',
    'tickets_action_duplicate_title' => 'Duplicar ticket',
    'tickets_action_edit' => 'Editar',
    'tickets_action_view' => 'Ver',

    'tickets_toast_duplicate_success' => 'Ticket duplicado. Abriendo borrador...',
    'tickets_toast_duplicate_error' => 'No se pudo duplicar el ticket.',

    // ═══════════════════════════════════════════════════════════════════
    // PLAN TICKETS WIZARD (PlanTickets/CoachPlanTicketWizard.vue)
    // ═══════════════════════════════════════════════════════════════════

    // Header
    'wizard_back_to_list' => 'Volver al listado',
    'wizard_title_new' => 'NUEVO TICKET DE PLAN',
    'wizard_title_edit' => 'TICKET DE PLAN',
    'wizard_subtitle' => '{client} · {plan}',
    'wizard_subtitle_placeholder_client' => '...',

    // Resubmitted badge
    'wizard_resubmitted_title' => 'Reenviado {date}',
    'wizard_resubmitted_label' => 'Editado tras envío · {date}',

    // Saving indicator
    'wizard_saving' => 'Guardando...',
    'wizard_saved' => 'Guardado',

    // Readonly banner
    'wizard_readonly_title' => 'Ticket en estado {status} — solo lectura',
    'wizard_banner_under_review' => 'Este ticket está siendo revisado por el equipo WellCore. No se puede editar.',
    'wizard_banner_completed' => 'Este ticket ya fue completado. El plan está asignado al cliente.',
    'wizard_banner_rejected' => 'Este ticket fue rechazado por el equipo WellCore. Revisa los comentarios y crea un ticket nuevo si es necesario.',

    // Progress
    'wizard_step_label' => 'Paso {current} de {total} · {label}',

    // Step names
    'wizard_step_client' => 'Cliente y Plan',
    'wizard_step_general' => 'Datos Generales',
    'wizard_step_training' => 'Entrenamiento',
    'wizard_step_nutrition' => 'Nutrición',
    'wizard_step_habits' => 'Hábitos',
    'wizard_step_supplements' => 'Suplementación',
    'wizard_step_cycle' => 'Ciclo Hormonal',
    'wizard_step_attachments' => 'Adjuntos',
    'wizard_step_review' => 'Revisión y Envío',

    // STEP 1: client + plan
    'wizard_s1_heading' => '1. Cliente y tipo de plan',
    'wizard_s1_client_label' => 'Cliente',
    'wizard_s1_client_placeholder' => 'Selecciona un cliente...',
    'wizard_s1_loading_clients' => 'Cargando clientes...',
    'wizard_s1_plan_type_label' => 'Tipo de plan',
    'wizard_s1_plan_essential_label' => 'Esencial',
    'wizard_s1_plan_essential_desc' => 'Entrenamiento, nutrición, hábitos y suplementación.',
    'wizard_s1_plan_method_label' => 'Método',
    'wizard_s1_plan_method_desc' => 'Plan completo con seguimiento avanzado.',
    'wizard_s1_plan_elite_label' => 'Élite',
    'wizard_s1_plan_elite_desc' => 'Plan completo + ciclo hormonal.',
    'wizard_s1_category_label' => 'Tipo de solicitud',
    'wizard_s1_new_plan_label' => 'Plan nuevo',
    'wizard_s1_new_plan_desc' => 'Cliente nuevo o plan completo desde cero. Requiere todas las secciones.',
    'wizard_s1_adjustment_label' => 'Ajuste de plan',
    'wizard_s1_adjustment_desc' => 'Cliente existente que necesita ajustes. Solo llena las secciones que cambian.',

    'wizard_s1_summary_client' => 'Cliente',
    'wizard_s1_summary_plan_type' => 'Tipo de plan',
    'wizard_s1_summary_category' => 'Tipo de solicitud',
    'wizard_s1_lock_notice' => 'El cliente y tipo de plan no se pueden modificar una vez creado el ticket.',

    'wizard_s1_dup_title' => 'Duplicar desde ticket previo',
    'wizard_s1_dup_desc' => 'Este cliente tiene planes previos. Puedes clonar uno para acelerar el brief.',
    'wizard_s1_dup_placeholder' => 'Selecciona un ticket previo completado...',
    'wizard_s1_dup_option_no_date' => 'sin fecha',
    'wizard_s1_dup_button' => 'Duplicar y editar',
    'wizard_s1_dup_button_progress' => 'Duplicando...',
    'wizard_s1_dup_loading' => 'Buscando tickets previos del cliente...',
    'wizard_s1_dup_confirm' => '¿Crear un nuevo borrador duplicando este ticket previo?',

    // STEP 2: general data
    'wizard_s2_heading' => '2. Datos generales',
    'wizard_s2_autofill_btn' => 'Pre-llenar desde el perfil del cliente',
    'wizard_s2_autofill_btn_loading' => 'Cargando...',
    'wizard_s2_field_name' => 'Nombre del cliente',
    'wizard_s2_field_plan' => 'Plan',
    'wizard_s2_field_plan_placeholder' => 'Selecciona...',
    'wizard_s2_field_age' => 'Edad',
    'wizard_s2_field_gender' => 'Género',
    'wizard_s2_field_weight' => 'Peso (kg)',
    'wizard_s2_field_height' => 'Estatura (cm)',
    'wizard_s2_field_activity' => 'Nivel de actividad diario',
    'wizard_s2_field_activity_placeholder' => 'Selecciona...',
    'wizard_s2_field_goal' => 'Objetivo principal',
    'wizard_s2_field_goal_placeholder' => 'Describe el objetivo del cliente en sus propias palabras...',

    'wizard_s2_autofill_no_client' => 'No hay cliente asociado al ticket.',
    'wizard_s2_autofill_none' => 'No hay datos previos para pre-llenar.',
    'wizard_s2_autofill_filled_one' => '{n} campo rellenado desde el perfil.',
    'wizard_s2_autofill_filled_other' => '{n} campos rellenados desde el perfil.',
    'wizard_s2_autofill_error' => 'No se pudieron cargar los datos del perfil.',

    // STEP 3: training
    'wizard_s3_heading' => '3. Plan de entrenamiento',
    'wizard_s3_adjustment_notice' => 'Este es un ticket de ajuste. Solo llena esta sección si hay algo que cambiar.',
    'wizard_s3_place_label' => 'Lugar de entrenamiento',
    'wizard_s3_implements_label' => 'Implementos disponibles',
    'wizard_s3_days_label' => 'Días por semana',
    'wizard_s3_days_placeholder' => 'Selecciona...',
    'wizard_s3_days_option' => '{n} días',
    'wizard_s3_strength_time_label' => 'Tiempo pesas (min)',
    'wizard_s3_cardio_time_label' => 'Tiempo cardio (min)',
    'wizard_s3_cardio_pref_label' => 'Preferencia de cardio',
    'wizard_s3_cardio_modality_label' => 'Modalidad de cardio',
    'wizard_s3_level_label' => 'Nivel',
    'wizard_s3_level_beginner_desc' => 'Menos de 6 meses entrenando.',
    'wizard_s3_level_intermediate_desc' => '6-24 meses con técnica sólida.',
    'wizard_s3_level_advanced_desc' => 'Más de 2 años, carga alta.',
    'wizard_s3_injuries_label' => 'Lesiones',
    'wizard_s3_restrictions_label' => 'Restricciones',
    'wizard_s3_restrictions_placeholder' => 'Ej: No incluir peso muerto, no caminadora.',
    'wizard_s3_split_label' => 'Split semanal',
    'wizard_s3_split_priority_placeholder' => 'Prioridad (opcional): ej. glúteo alto',

    // STEP 4: nutrition
    'wizard_s4_heading' => '4. Plan nutricional',
    'wizard_s4_adjustment_notice' => 'Este es un ticket de ajuste. Solo llena esta sección si hay algo que cambiar.',
    'wizard_s4_goal_label' => 'Objetivo nutricional',
    'wizard_s4_meals_label' => 'Cuántas comidas al día',
    'wizard_s4_methodology_label' => 'Metodología',
    'wizard_s4_methodology_placeholder' => 'Selecciona...',
    'wizard_s4_times_label' => 'Horarios de comida',
    'wizard_s4_times_placeholder' => 'Ej: 7:00 am',
    'wizard_s4_times_add' => 'Agregar',
    'wizard_s4_excluded_foods_label' => 'Alimentos que NO incluir',
    'wizard_s4_prioritize_foods_label' => 'Alimentos a priorizar',
    'wizard_s4_meal_config_label' => 'Descripción y configuración de comidas',
    'wizard_s4_meal_config_placeholder' => 'Ej: desayuno con huevo y otras con avena; snack AM con frutas...',

    // STEP 5: habits
    'wizard_s5_heading' => '5. Plan de hábitos',
    'wizard_s5_adjustment_notice' => 'Este es un ticket de ajuste. Solo llena esta sección si hay algo que cambiar.',
    'wizard_s5_focus_label' => 'Áreas de foco',
    'wizard_s5_morning_label' => 'Rutina matutina',
    'wizard_s5_night_label' => 'Rutina nocturna',
    'wizard_s5_other_label' => 'Otros hábitos',

    // STEP 6: supplements
    'wizard_s6_heading' => 'Plan de suplementación',
    'wizard_s6_adjustment_notice' => 'Este es un ticket de ajuste. Solo llena esta sección si hay algo que cambiar.',
    'wizard_s6_goal_label' => 'Objetivo del stack',
    'wizard_s6_goal_placeholder' => 'Para qué se optimiza — ej. Rendimiento base, Recomposición, Recuperación...',
    'wizard_s6_supplements_label' => 'Suplementos',
    'wizard_s6_add_supplement' => 'Añadir suplemento',
    'wizard_s6_empty_supplements' => 'Agrega al menos un suplemento.',
    'wizard_s6_supplement_remove' => 'Eliminar',
    'wizard_s6_field_name' => 'Nombre',
    'wizard_s6_field_name_placeholder' => 'Ej: Proteína de Suero, Creatina Monohidrato',
    'wizard_s6_field_dose' => 'Dosis',
    'wizard_s6_field_dose_placeholder' => 'Ej: 30g, 5g',
    'wizard_s6_field_timing' => 'Momento',
    'wizard_s6_field_timing_placeholder' => 'Ej: Post-entrenamiento, Antes de dormir',
    'wizard_s6_field_frequency' => 'Frecuencia',
    'wizard_s6_field_frequency_placeholder' => 'Selecciona...',
    'wizard_s6_field_notes' => 'Notas (opcional)',
    'wizard_s6_field_notes_placeholder' => 'Ej: tomar con agua, evitar con cafeína',
    'wizard_s6_coach_notes_label' => 'Notas del coach para el stack (opcional)',
    'wizard_s6_coach_notes_placeholder' => 'Indicaciones generales, advertencias, periodo de ciclado...',
    'wizard_s6_frequency_daily' => 'Diario',
    'wizard_s6_frequency_training_days' => 'Días de entrenamiento',
    'wizard_s6_frequency_3x_week' => '3 veces por semana',
    'wizard_s6_frequency_cyclic' => 'Cíclico',

    // STEP 7: cycle (Elite only)
    'wizard_s7_heading' => '6. Ciclo hormonal',
    'wizard_s7_last_period_label' => 'Fecha última menstruación',
    'wizard_s7_cycle_duration_label' => 'Duración ciclo (días)',
    'wizard_s7_symptoms_label' => 'Síntomas',
    'wizard_s7_contraceptive_label' => 'Anticonceptivo',
    'wizard_s7_notes_label' => 'Notas adicionales',

    // STEP 8: attachments
    'wizard_s8_heading' => 'Adjuntos (opcional)',
    'wizard_s8_subtitle' => 'Fotos de progreso, laboratorios, documentos médicos, etc. Máx 10MB por archivo.',
    'wizard_s8_category_label' => 'Categoría del archivo',
    'wizard_s8_category_none' => 'Sin categoría',
    'wizard_s8_category_progress_photo' => 'Foto de progreso',
    'wizard_s8_category_lab' => 'Laboratorio',
    'wizard_s8_category_medical' => 'Documento médico',
    'wizard_s8_category_other' => 'Otro',
    'wizard_s8_dropzone_idle' => 'Arrastra un archivo o haz click para subir',
    'wizard_s8_dropzone_uploading' => 'Subiendo...',
    'wizard_s8_dropzone_hint' => 'JPG, PNG, WEBP, HEIC, PDF o DOCX · máx 10MB',
    'wizard_s8_list_label' => 'Archivos ({n})',
    'wizard_s8_empty_list' => 'Sin archivos adjuntos todavía.',
    'wizard_s8_action_view' => 'Ver',
    'wizard_s8_action_delete' => 'Eliminar',
    'wizard_s8_uploader_fallback' => 'Coach',
    'wizard_s8_file_too_large' => 'El archivo excede 10MB.',
    'wizard_s8_file_type_not_allowed' => 'Tipo de archivo no permitido.',
    'wizard_s8_toast_uploaded' => 'Archivo subido',
    'wizard_s8_toast_upload_error' => 'No se pudo subir el archivo.',
    'wizard_s8_confirm_delete' => '¿Eliminar este archivo?',
    'wizard_s8_toast_deleted' => 'Archivo eliminado',
    'wizard_s8_toast_delete_error' => 'No se pudo eliminar.',

    // STEP 9: review
    'wizard_s9_heading' => 'Revisión final',
    'wizard_s9_summary_client' => 'Cliente',
    'wizard_s9_summary_plan' => 'Plan',
    'wizard_s9_summary_age_gender' => 'Edad / Género',
    'wizard_s9_summary_weight_height' => 'Peso / Estatura',
    'wizard_s9_summary_weight_height_value' => '{weight} kg · {height} cm',
    'wizard_s9_summary_place_days' => 'Lugar entrenamiento',
    'wizard_s9_summary_place_days_value' => '{place} · {days} días',
    'wizard_s9_summary_level' => 'Nivel',
    'wizard_s9_summary_nutrition' => 'Nutrición',
    'wizard_s9_summary_nutrition_value' => '{meals} comidas · {methodology}',
    'wizard_s9_summary_no_methodology' => 'sin metodología',
    'wizard_s9_summary_habits' => 'Hábitos',
    'wizard_s9_summary_habits_value_one' => '{n} área de foco',
    'wizard_s9_summary_habits_value_other' => '{n} áreas de foco',
    'wizard_s9_summary_supplements' => 'Suplementación',
    'wizard_s9_summary_supplements_value_one' => '{n} suplemento',
    'wizard_s9_summary_supplements_value_other' => '{n} suplementos',
    'wizard_s9_summary_supplement_name_empty' => '(sin nombre)',
    'wizard_s9_summary_cycle' => 'Ciclo hormonal',
    'wizard_s9_summary_cycle_value' => '{date} · {days} días',

    'wizard_s9_missing_fields_title' => 'Campos faltantes:',

    'wizard_s9_responsibility_title' => 'ANTES DE ENVIAR ESTE TICKET',
    'wizard_s9_responsibility_adjustment' => 'Describe con claridad qué ajuste necesita el cliente.',
    'wizard_s9_responsibility_bullet1' => 'Lo que tú escribes aquí define directamente la calidad del plan que recibirá tu cliente.',
    'wizard_s9_responsibility_bullet2_pre' => 'Esta validación 1-a-1 con el cliente es una responsabilidad clave del coach —',
    'wizard_s9_responsibility_bullet2_strong' => 'conversa de manera humana, cercana y analiza sus respuestas',
    'wizard_s9_responsibility_bullet2_post' => 'para darnos contexto real.',
    'wizard_s9_responsibility_bullet3' => 'Cuanto más detalle y contexto brindes, más personalizado y efectivo será el plan.',
    'wizard_s9_responsibility_bullet4' => 'Es tu responsabilidad ante la empresa hacer esto bien. Tu trabajo aquí es el primer filtro que determina el éxito del asesorado.',
    'wizard_s9_responsibility_close_strong' => 'Lo que esperamos de ti:',
    'wizard_s9_responsibility_close_text' => 'que seas excelente haciéndolo. Los asesorados confían en tu profesionalismo — que ese sea el estándar que reflejen estos tickets.',

    // Submit actions
    'wizard_delete_draft' => 'Eliminar borrador',
    'wizard_delete_draft_progress' => 'Eliminando...',
    'wizard_save_as_draft' => 'Guardar como borrador',
    'wizard_submit_ticket' => 'Enviar ticket',
    'wizard_submit_ticket_progress' => 'Enviando...',

    // Navigation
    'wizard_nav_prev' => 'Anterior',
    'wizard_nav_next' => 'Siguiente',
    'wizard_nav_create_and_continue' => 'Crear y continuar',

    // Toasts / confirms (wizard)
    'wizard_toast_create_missing' => 'Selecciona un cliente y un tipo de plan.',
    'wizard_toast_created' => 'Ticket creado. Completa el brief.',
    'wizard_toast_create_error' => 'No se pudo crear el ticket.',
    'wizard_toast_load_error' => 'No se pudo cargar el ticket.',
    'wizard_toast_save_error' => 'No se pudo guardar.',
    'wizard_toast_submitted' => 'Ticket enviado al equipo WellCore.',
    'wizard_toast_submit_missing_fields' => 'Faltan campos para enviar el ticket.',
    'wizard_toast_submit_error' => 'No se pudo enviar el ticket.',
    'wizard_toast_dup_prev_success' => 'Ticket duplicado. Abriendo borrador...',
    'wizard_toast_dup_prev_error' => 'No se pudo duplicar el ticket previo.',
    'wizard_toast_delete_success' => 'Borrador eliminado.',
    'wizard_toast_delete_error' => 'No se pudo eliminar.',
    'wizard_confirm_delete_draft' => '¿Eliminar este borrador? No se puede deshacer.',

    // Static catalogs (days, activity levels, methodologies)
    'wizard_day_monday' => 'Lunes',
    'wizard_day_tuesday' => 'Martes',
    'wizard_day_wednesday' => 'Miércoles',
    'wizard_day_thursday' => 'Jueves',
    'wizard_day_friday' => 'Viernes',
    'wizard_day_saturday' => 'Sábado',
    'wizard_day_sunday' => 'Domingo',

    'wizard_activity_sedentary' => 'Sedentario (trabajo de oficina, poco movimiento)',
    'wizard_activity_light' => 'Ligero (ejercicio 1-2x por semana)',
    'wizard_activity_moderate' => 'Moderado (ejercicio 3-4x por semana)',
    'wizard_activity_active' => 'Activo (ejercicio 5-6x por semana)',
    'wizard_activity_very_active' => 'Muy activo (trabajo físico + ejercicio)',

    'wizard_method_deficit_label' => 'Déficit calórico',
    'wizard_method_deficit_desc' => 'Pérdida de grasa con déficit sostenible.',
    'wizard_method_flexible_label' => 'Flexible / IIFYM',
    'wizard_method_flexible_desc' => 'Macros flexibles, preferencias libres.',
    'wizard_method_carb_cycling_label' => 'Carb cycling',
    'wizard_method_carb_cycling_desc' => 'Ciclado de carbohidratos alto/bajo.',
    'wizard_method_fasting_label' => 'Ayuno intermitente',
    'wizard_method_fasting_desc' => 'Ventana de alimentación (16:8 / 18:6).',
    'wizard_method_maintenance_label' => 'Mantenimiento',
    'wizard_method_maintenance_desc' => 'Calorías de mantenimiento para recomposición.',
    'wizard_method_lean_bulk_label' => 'Volumen limpio',
    'wizard_method_lean_bulk_desc' => 'Superávit calórico controlado.',

    // Relative time
    'wizard_time_just_now' => 'hace un momento',
    'wizard_time_minutes' => 'hace {n} min',
    'wizard_time_hours' => 'hace {n} h',
    'wizard_time_days' => 'hace {n} d',

    // ═══════════════════════════════════════════════════════════════════
    // ANALYTICS (Coach/Analytics.vue)
    // ═══════════════════════════════════════════════════════════════════

    'analytics_context_label' => 'PRINCIPAL',
    'analytics_title' => 'ANALYTICS',
    'analytics_subtitle' => 'Rendimiento y métricas de tu equipo',

    'analytics_range_month' => 'Mes',
    'analytics_range_quarter' => 'Trimestre',
    'analytics_range_year' => 'Año',
    'analytics_range_all' => 'Todo',

    'analytics_loading' => 'Actualizando métricas...',

    'analytics_empty_title' => 'Sin métricas todavía',
    'analytics_empty_subtitle' => 'Las métricas aparecerán cuando tus clientes comiencen a registrar check-ins y actividad.',

    'analytics_coach_score' => 'Coach Score',
    'analytics_coach_score_subtitle' => 'Puntuación compuesta de rendimiento',
    'analytics_score_label_excellent' => 'Excelente',
    'analytics_score_label_regular' => 'Regular',
    'analytics_score_label_needs_improvement' => 'Necesita mejora',

    'analytics_metric_response' => 'Respuesta',
    'analytics_metric_response_value' => '{hours}h',
    'analytics_metric_reply_rate' => 'Reply Rate',
    'analytics_metric_retention' => 'Retención',
    'analytics_metric_wellbeing' => 'Bienestar',
    'analytics_metric_wellbeing_value' => '{value}/10',
    'analytics_metric_checkins' => 'Check-ins',
    'analytics_metric_messages' => 'Mensajes',

    'analytics_sla_title' => 'SLA de Respuesta',
    'analytics_sla_within_24h' => 'Dentro de 24h',
    'analytics_sla_24_48h' => '24-48h',
    'analytics_sla_over_48h' => 'Más de 48h',

    'analytics_revenue_title' => 'Revenue',
    'analytics_revenue_total' => 'Total',
    'analytics_revenue_monthly' => 'Mensual',
    'analytics_revenue_active_clients' => 'Clientes activos',

    'analytics_overview_title' => 'Resumen de Clientes',
    'analytics_overview_col_client' => 'Cliente',
    'analytics_overview_col_wellbeing' => 'Bienestar',
    'analytics_overview_col_checkins' => 'Check-ins',
    'analytics_overview_col_adherence' => 'Adherencia',
    'analytics_overview_empty' => 'Sin datos de clientes',
    'analytics_overview_no_value' => '-',

    // ═══════════════════════════════════════════════════════════════════
    // NOTIFICATIONS PREFERENCES (Coach/NotificationsPreferences.vue)
    // ═══════════════════════════════════════════════════════════════════

    'notif_title' => 'Notificaciones',
    'notif_subtitle' => 'Decide qué eventos de tu equipo quieres seguir y cómo recibirlos.',

    'notif_channels_heading' => 'Canales',
    'notif_push_label' => 'Push (browser)',
    'notif_push_desc' => 'Notificaciones del navegador en tiempo real.',
    'notif_push_granted' => 'Activado',
    'notif_push_request' => 'Activar',
    'notif_push_blocked' => 'Bloqueado',
    'notif_in_app_label' => 'In-app (campana)',
    'notif_in_app_desc' => 'Aparecen en el ícono de campana del topbar.',

    'notif_events_heading' => 'Cuándo notificarme',
    'notif_event_pr_broken' => 'Cuando un cliente rompe un PR',
    'notif_event_streak_milestone' => 'Cuando un cliente alcanza un milestone (7/30/100 días)',
    'notif_event_post_created' => 'Cuando un cliente hace un post (silencioso por defecto)',
    'notif_event_comment_on_reply' => 'Cuando alguien comenta después de mi respuesta',
    'notif_event_at_risk_client' => 'Cuando un cliente lleva 5+ días sin actividad',
    'notif_event_official_engagement' => 'Cuando un cliente reacciona a mi post oficial',
    'notif_event_admin_broadcast' => 'Cuando WellCore admin envía un anuncio',

    'notif_saving' => 'Guardando…',

    'notif_load_error' => 'No pudimos cargar preferencias.',
    'notif_save_error' => 'No pudimos guardar.',
    'notif_push_granted_toast' => 'Notificaciones browser activadas.',

];
