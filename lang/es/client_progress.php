<?php

return [
    // ===== CheckinForm.vue =====
    // Header
    'checkin_title' => 'Check-in semanal',
    'checkin_coach_replies_prefix' => 'Tu coach responde en',
    'checkin_coach_replies_value' => 'menos de 24 h',
    'checkin_week_label' => 'Semana {n}',
    'checkin_not_available_title' => 'Check-in no disponible hoy',
    'checkin_not_available_body_prefix' => 'El check-in semanal estará disponible el próximo',
    'checkin_not_available_body_days' => 'viernes o sábado',
    'checkin_not_available_body_suffix' => '. Sigue entrenando — la consistencia es tu superpoder.',
    'checkin_form_title_sr' => 'Formulario de check-in',
    'checkin_progress_aria' => 'Progreso del check-in',

    // Wizard steps
    'checkin_step_wellbeing' => 'Bienestar',
    'checkin_step_training' => 'Entreno',
    'checkin_step_nutrition' => 'Nutrición',
    'checkin_step_notes' => 'Notas',

    // Step 1: Bienestar
    'checkin_q_wellbeing_title' => 'Bienestar general',
    'checkin_q_wellbeing_hint' => '¿Cómo te has sentido en general esta semana? (energía, ánimo, descanso)',
    'checkin_q_wellbeing_label' => 'Tu nivel de bienestar',
    'checkin_scale_very_bad' => 'Muy mal',
    'checkin_scale_bad' => 'Mal',
    'checkin_scale_ok' => 'Normal',
    'checkin_scale_good' => 'Bien',
    'checkin_scale_very_good' => 'Muy bien',
    'checkin_scale_very_bad_hint' => 'Cansado, sin energía, ánimo bajo.',
    'checkin_scale_bad_hint' => 'No fue una buena semana en general.',
    'checkin_scale_ok_hint' => 'Equilibrado: ni alto ni bajo.',
    'checkin_scale_good_hint' => 'Buena energía y ánimo la mayor parte.',
    'checkin_scale_very_good_hint' => 'Semana excelente, energía top.',
    'checkin_err_wellbeing_required' => 'Selecciona tu nivel de bienestar (1-5)',
    'checkin_err_wellbeing_required_short' => 'Selecciona tu nivel de bienestar',

    // Step 2: Entrenamiento
    'checkin_q_training_title' => 'Entrenamiento',
    'checkin_q_training_hint' => '¿Cuántos días entrenaste? ¿Cómo se sintió la carga?',
    'checkin_q_days_trained' => 'Días entrenados',
    'checkin_q_days_picker_aria' => 'Días entrenados esta semana',
    'checkin_days_of_7' => 'de 7 días',
    'checkin_days_excellent' => 'Excelente consistencia.',
    'checkin_days_building' => 'Vas construyendo el hábito.',
    'checkin_days_quiet' => 'Esta semana fue tranquila — recupérate bien.',
    'checkin_q_rpe_label' => 'RPE promedio de la semana',
    'checkin_rpe_left' => 'Muy fácil (1)',
    'checkin_rpe_right' => 'Máximo esfuerzo (10)',
    'checkin_err_days_range' => 'Días entrenados debe estar entre 0 y 7',
    'checkin_err_rpe_range' => 'RPE debe estar entre 1 y 10',

    // Step 3: Nutrición
    'checkin_q_nutrition_title' => 'Nutrición',
    'checkin_q_nutrition_hint' => '¿Qué tan bien seguiste tu plan nutricional esta semana?',
    'checkin_nutrition_aria' => 'Adherencia al plan nutricional',
    'checkin_nutrition_followed_label' => 'La seguí bien',
    'checkin_nutrition_followed_hint' => 'Apegué mi plan al menos 80% de la semana.',
    'checkin_nutrition_partial_label' => 'Parcialmente',
    'checkin_nutrition_partial_hint' => 'Tuve algunos desvíos pero me mantuve en general.',
    'checkin_nutrition_no_label' => 'No la seguí',
    'checkin_nutrition_no_hint' => 'Esta semana se me complicó. Necesito apoyo.',
    'checkin_err_nutrition_required' => 'Selecciona una opción de nutrición',

    // Step 4: Notas / Submit
    'checkin_q_notes_title' => 'Notas para tu coach',
    'checkin_q_notes_hint' => 'Cuéntale cómo te fue, qué dudas tienes o qué quieres ajustar. (opcional)',
    'checkin_notes_label_sr' => 'Comentario para tu coach',
    'checkin_notes_placeholder' => 'Ej: Esta semana sentí el press inclinado más fuerte, llegué a 80kg × 8. La nutrición estuvo al 75% por una salida de trabajo. ¿Podemos ajustar las porciones del almuerzo?',
    'checkin_notes_realtime_hint' => 'Tu coach lo recibe al instante.',
    'checkin_err_notes_max' => 'El comentario no puede superar 1000 caracteres ({n}/1000)',
    'checkin_summary_title' => 'Resumen',
    'checkin_summary_wellbeing' => 'Bienestar',
    'checkin_summary_days' => 'Días',
    'checkin_summary_nutrition' => 'Nutrición',
    'checkin_summary_rpe' => 'RPE',
    'checkin_summary_nutrition_well' => 'Bien',
    'checkin_summary_nutrition_partial' => 'Parcial',
    'checkin_summary_nutrition_no' => 'No',

    // Buttons
    'checkin_btn_next' => 'Siguiente',
    'checkin_btn_back' => 'Atrás',
    'checkin_btn_back_aria' => 'Paso anterior',
    'checkin_btn_next_step' => 'Siguiente paso',
    'checkin_btn_sending' => 'Enviando...',
    'checkin_btn_submit' => 'Enviar check-in',
    'checkin_btn_unavailable' => 'Disponible el viernes',
    'checkin_btn_unavailable_title' => 'Solo disponible viernes y sábado',

    // Submit feedback
    'checkin_toast_sent' => 'Check-in enviado.',
    'checkin_toast_form_review' => 'Revisa los datos del formulario.',
    'checkin_toast_send_failed' => 'No pudimos enviar tu check-in.',
    'checkin_err_load' => 'Error al cargar check-in',
    'checkin_err_send_generic' => 'Error al enviar el check-in',

    // Success overlay
    'checkin_success_brand' => 'WellCore',
    'checkin_success_title' => 'Check-in enviado',
    'checkin_success_days_short' => 'días entren.',
    'checkin_success_wellbeing_short' => 'bienestar',
    'checkin_success_week_short' => 'semana',
    'checkin_success_week_prefix' => 'S',
    'checkin_success_body' => 'Tu coach revisará tu reporte esta semana. Sigue así.',
    'checkin_success_dismiss' => 'Perfecto',

    // Tutorial
    'checkin_tutorial_title' => 'Check-in semanal',
    'checkin_tutorial_close_aria' => 'Cerrar',
    'checkin_tutorial_step1_title' => '¿Qué es el check-in?',
    'checkin_tutorial_step1_body' => 'Es tu reporte semanal al coach. Con esta información tu coach ajusta tu plan de entrenamiento y nutrición para maximizar tus resultados semana a semana.',
    'checkin_tutorial_step2_title' => 'Sé honesto',
    'checkin_tutorial_step2_body' => 'No hay respuestas malas. Si tuviste una semana difícil, dilo. Tu coach solo puede ayudarte si conoce tu realidad — no la versión perfecta.',
    'checkin_tutorial_step3_title' => 'Hazlo cada semana',
    'checkin_tutorial_step3_body' => 'Los clientes que completan su check-in semanalmente progresan 3× más rápido. El seguimiento constante es lo que diferencia los resultados promedio de los extraordinarios.',
    'checkin_tutorial_back' => 'Atrás',
    'checkin_tutorial_next' => 'Siguiente',
    'checkin_tutorial_start' => 'Listo, comenzar',

    // Recent check-ins list
    'checkin_recent_title' => 'Check-ins anteriores',
    'checkin_recent_status_replied' => 'Respondido',
    'checkin_recent_status_pending' => 'En revisión',
    'checkin_recent_badge_wellbeing' => 'Bienestar {value}/5',
    'checkin_recent_badge_rpe' => 'RPE {value}/10',
    'checkin_recent_badge_days' => '{value}/7 días',
    'checkin_recent_nutrition_full' => 'Nutri 100%',
    'checkin_recent_nutrition_partial' => 'Nutri Parcial',
    'checkin_recent_nutrition_no' => 'Nutri No',
    'checkin_recent_coach_reply_label' => 'Respuesta del coach',

    // Date formatting
    'date_format_long' => '{d} de {month}, {year}',
    'month_jan' => 'enero',
    'month_feb' => 'febrero',
    'month_mar' => 'marzo',
    'month_apr' => 'abril',
    'month_may' => 'mayo',
    'month_jun' => 'junio',
    'month_jul' => 'julio',
    'month_aug' => 'agosto',
    'month_sep' => 'septiembre',
    'month_oct' => 'octubre',
    'month_nov' => 'noviembre',
    'month_dec' => 'diciembre',

    // ===== MetricsV2 / Metrics components =====
    'metrics_section_title' => 'Métricas',
    'metrics_breadcrumb_dashboard' => 'Dashboard',
    'metrics_breadcrumb_metrics' => 'Métricas',
    'metrics_hero_subtitle' => 'Tu peso, composición y mediciones — leídos en contexto por tu coach.',
    'metrics_streak_weeks_short' => '{n} sem',
    'metrics_streak_weeks_title' => '{n} semanas consecutivas',
    'metrics_last_prefix' => 'Último:',

    // Tutorial
    'metrics_tutorial_aria' => 'Bienvenida a Métricas',
    'metrics_tutorial_step1_title' => 'Bienvenido a Métricas',
    'metrics_tutorial_step1_body' => 'Registra tu peso y medidas para visualizar tu progreso a lo largo del tiempo.',
    'metrics_tutorial_step2_title' => 'Modo rápido vs. completo',
    'metrics_tutorial_step2_body' => 'Usa el modo rápido para registrar solo el peso. El modo completo incluye medidas corporales y composición.',
    'metrics_tutorial_step3_title' => 'Consistencia = resultados',
    'metrics_tutorial_step3_body' => 'Registra al menos una vez por semana, en ayunas y en las mismas condiciones, para obtener datos precisos.',
    'metrics_tutorial_next' => 'Siguiente',
    'metrics_tutorial_start' => 'Comenzar',
    'metrics_tutorial_skip' => 'Saltar',

    // Achievements
    'metrics_ach_weight_logged' => '¡Peso registrado!',
    'metrics_ach_full_logged' => '¡Registro completo guardado!',

    // Errors
    'metrics_retry' => 'Reintentar',
    'metrics_err_quick_invalid' => 'Ingresa un peso válido (20–300 kg)',
    'metrics_err_save' => 'Error al guardar. Intenta de nuevo.',

    // Stat cards
    'metrics_current_weight' => 'Peso actual',
    'metrics_monthly_change' => 'Cambio mensual',
    'metrics_goal' => 'Objetivo',
    'metrics_records' => 'Registros',
    'metrics_goal_current_prefix' => 'Actual',
    'metrics_goal_target_prefix' => 'Objetivo',

    // WeightChart
    'metrics_chart_title' => 'Peso Corporal',
    'metrics_chart_sub' => 'Evolución de {period}',
    'metrics_chart_period_aria' => 'Período del gráfico',
    'metrics_chart_legend_weight' => 'Peso',
    'metrics_chart_empty_title' => 'Sin datos de peso',
    'metrics_chart_empty_msg' => 'Registra tu primer peso para ver tu evolución en el tiempo.',
    'metrics_chart_empty_cta' => 'Registra tu primer dato →',

    // CheckinsStreak
    'metrics_streak_title' => 'Check-ins Semanales',
    'metrics_streak_sub' => 'Últimas 12 semanas',
    'metrics_streak_history_aria' => 'Historial de check-ins',
    'metrics_streak_week_n' => 'Semana {n}: {cnt} check-in(s)',
    'metrics_streak_empty' => 'Sin check-ins recientes',
    'metrics_streak_axis_past' => 'HACE 12 SEM',
    'metrics_streak_axis_today' => 'HOY',
    'metrics_streak_attendance_suffix' => '% asistencia',

    // CompositionPanel
    'metrics_composition_title' => 'Composición Corporal',
    'metrics_composition_last_prefix' => 'Última medición',
    'metrics_composition_muscle' => 'Músculo',
    'metrics_composition_fat' => 'Grasa',
    'metrics_composition_water' => 'Agua',
    'metrics_composition_empty' => 'Sin datos de composición',
    'metrics_chest' => 'Pecho',
    'metrics_waist' => 'Cintura',
    'metrics_hip' => 'Cadera',
    'metrics_thigh' => 'Muslo',
    'metrics_arm' => 'Brazo',

    // CoachInterpretation
    'metrics_coach_role' => 'Coach',
    'metrics_coach_view_checkin' => 'Ver check-in',

    // CrossLinkPhotos
    'metrics_photos_title' => 'Fotos de progreso',
    'metrics_photos_count_singular' => '{n} foto registrada',
    'metrics_photos_count_plural' => '{n} fotos registradas',
    'metrics_photos_empty' => 'La transformación visual supera a los números',
    'metrics_photos_view' => 'Ver fotos',

    // MetricsForm
    'metrics_form_title' => 'Nuevo registro',
    'metrics_form_sub' => 'Idealmente en ayunas, mismo día y hora cada semana.',
    'metrics_form_mode_quick' => 'Rápido',
    'metrics_form_mode_full' => 'Completo',

    // QuickLogInput
    'metrics_quick_label' => 'PESO',
    'metrics_quick_kg' => 'kg',
    'metrics_quick_hint' => 'en ayunas',
    'metrics_quick_aria_weight' => 'Peso en kilogramos',
    'metrics_quick_saving' => 'Guardando...',
    'metrics_quick_save' => 'Guardar',
    'metrics_quick_full' => 'Completo',

    // MeasurementsForm
    'metrics_meas_weight_kg' => 'Peso (kg)',
    'metrics_meas_required_aria' => 'requerido',
    'metrics_meas_muscle_pct' => '% Músculo',
    'metrics_meas_fat_pct' => '% Grasa',
    'metrics_meas_notes' => 'Notas',
    'metrics_meas_notes_placeholder' => 'En ayunas, post-entreno...',
    'metrics_meas_section_title' => 'Mediciones corporales',
    'metrics_meas_section_sub' => 'Mide con cinta métrica, en la mañana',
    'metrics_meas_chest_cm' => 'Pecho (cm)',
    'metrics_meas_waist_cm' => 'Cintura (cm)',
    'metrics_meas_hip_cm' => 'Cadera (cm)',
    'metrics_meas_thigh_cm' => 'Muslo (cm)',
    'metrics_meas_arm_cm' => 'Brazo (cm)',
    'metrics_meas_guide_toggle' => 'Cómo tomar las mediciones correctamente',
    'metrics_meas_guide_chest' => 'Cinta a la altura de los pezones. Brazos relajados. No inflar el pecho.',
    'metrics_meas_guide_waist' => 'En el punto más estrecho, 2-3 cm arriba del ombligo. Exhala normalmente.',
    'metrics_meas_guide_hip' => 'En el punto más ancho de los glúteos. Pies juntos, de pie recto.',
    'metrics_meas_guide_thigh' => 'En el punto más grueso, justo debajo del glúteo. Pierna relajada.',
    'metrics_meas_guide_arm' => 'En el punto más grueso del bíceps. Brazo relajado sin flexionar.',
    'metrics_meas_guide_tip' => 'Mide siempre en las mismas condiciones: por la mañana, antes de comer.',
    'metrics_meas_privacy' => 'Solo tú y tu coach pueden ver estos datos',
    'metrics_meas_save_draft' => 'Guardar borrador',
    'metrics_meas_saving' => 'Guardando...',
    'metrics_meas_save' => 'Guardar registro',

    // ===== FoodTracking.vue =====
    'food_title' => 'Mi alimentación',
    'food_subtitle' => 'Documenta cada comida y tu coach la revisa',
    'food_retry' => 'Reintentar',
    'food_streak_days' => '{n} días seguidos',
    'food_xp_today' => '+{n} XP hoy',
    'food_no_plan_notice' => 'Aún no tienes un plan de nutrición personalizado. Mientras tu coach lo arma, puedes documentar tus comidas con las categorías generales de abajo.',
    'food_progress_label' => 'Hoy llevas {done} de {total} comidas documentadas',
    'food_bonus_complete' => 'Bonus diario completo (+30 XP)',
    'food_xp_per_meal' => '+15 XP',
    'food_kcal_unit' => 'kcal',
    'food_meal_generic' => 'Comida',

    // Photo states
    'food_replace' => 'Reemplazar',
    'food_delete' => 'Eliminar',
    'food_reaction_good' => 'Bien',
    'food_reaction_improve' => 'Mejorar',
    'food_reaction_seen' => 'Vista',

    // Notes
    'food_your_description' => 'Tu descripción',
    'food_saving' => 'Guardando...',
    'food_describe_placeholder' => 'Describe lo que comiste o dicta con voz',
    'food_dictate_start' => 'Dictar por voz',
    'food_dictate_stop' => 'Detener dictado',
    'food_recording' => 'Grabando...',
    'food_what_you_ate' => '¿Qué comiste? (opcional)',
    'food_pre_placeholder' => 'Ej: huevos revueltos con avena, café sin azúcar',
    'food_pre_pending_pill' => 'Pendiente',
    'food_no_voice_hint' => 'Tu navegador no soporta dictado por voz. Usa el teclado.',
    'food_no_voice_alert' => 'Tu navegador no soporta dictado por voz',

    // Upload actions
    'food_cancel' => 'Cancelar',
    'food_uploading' => 'Subiendo...',
    'food_confirm_photo' => 'Confirmar foto',
    'food_upload_photo' => 'Subir foto',
    'food_coach_note_label' => 'Nota del coach',
    'food_alt_meal_photo' => 'Foto de {meal}',
    'food_alt_preview' => 'Preview {meal}',

    // Errors
    'food_err_format' => 'Solo se permiten imágenes (JPG, PNG, WebP).',
    'food_err_size' => 'La imagen no puede superar 10 MB.',
    'food_err_upload_generic' => 'Error al subir la foto. Intenta de nuevo.',
    'food_err_delete' => 'No se pudo eliminar la foto. Intenta de nuevo.',
    'food_err_save_note' => 'No se pudo guardar la nota. Intenta de nuevo.',
    'food_err_dictation' => 'No se pudo iniciar el dictado. Verifica los permisos del micrófono.',
    'food_confirm_delete' => '¿Eliminar esta foto?',

    // ===== PhotosV2 / Photos components =====
    // PhotosHero
    'photos_kicker' => 'Fotos de progreso',
    'photos_title_line1' => 'Tu cuerpo,',
    'photos_title_line2' => 'semana a semana.',
    'photos_hero_intro' => 'Estas fotos cuentan una historia que el peso no puede contar. Suben aquí solo para tu coach {coachName} — privadas, encriptadas, tuyas.',
    'photos_stat_sessions' => 'Sesiones',
    'photos_stat_weeks' => 'Semanas',
    'photos_stat_weeks_short' => 'sem',
    'photos_stat_latest' => 'Última',
    'photos_next_session' => 'Próxima sesión',
    'photos_next_today_prefix' => 'hoy — ',
    'photos_next_tomorrow_prefix' => 'mañana — ',
    'photos_next_in_days' => 'en {n} días — {date}',

    // PrivacyReassurance
    'photos_privacy_title' => 'Solo tu coach ve estas fotos',
    'photos_privacy_body' => 'Encriptadas en tránsito y en reposo. Nunca aparecen en tu perfil público ni se comparten con la comunidad.',
    'photos_privacy_only_coach' => 'Solo {coachName}',
    'photos_aes_encrypt' => 'AES-256',
    'photos_privacy_policy' => 'Política',

    // PhotoGuide
    'photos_guide_title' => 'Guía para tus fotos',
    'photos_guide_sub' => 'Cómo tomarte las fotos para un progreso preciso',
    'photos_guide_kicker' => '/ guía visual',
    'photos_guide_headline' => 'Tres ángulos. Una rutina.',
    'photos_guide_intro' => 'La técnica importa porque la comparativa solo es honesta si las fotos son consistentes.',

    // AnglesGrid
    'photos_angles_aria' => 'Ángulos requeridos',
    'photos_angle_req_short' => 'Req',
    'photos_front' => 'Frente',
    'photos_side' => 'Perfil',
    'photos_back' => 'Espalda',
    'photos_front_desc' => 'Mirando directo a la cámara, brazos relajados al lado del cuerpo. Pies separados al ancho de cadera.',
    'photos_side_desc' => 'De lado izquierdo, exactamente 90° a la cámara. Mira al horizonte, brazos sueltos.',
    'photos_back_desc' => 'De espaldas a la cámara, brazos al lado del cuerpo. Hombros relajados, mirada al frente.',

    // TipsList
    'photos_tips_kicker' => 'Cuatro reglas',
    'photos_tips_title' => 'Para que la comparación sea real',
    'photos_tips_aria' => 'Reglas para tomar fotos precisas',
    'photos_lighting' => 'Iluminación',
    'photos_lighting_body' => 'Luz natural de frente o lateral. Evita contraluz y lámparas directamente arriba — generan sombras que mienten.',
    'photos_clothing' => 'Vestimenta',
    'photos_clothing_body' => 'Ropa ajustada — short y top, o ropa interior. Lo holgado oculta los cambios reales del cuerpo.',
    'photos_distance' => 'Distancia',
    'photos_distance_body' => '1.5 a 2 metros de la cámara. Cuerpo entero en el encuadre, con espacio sobre la cabeza y bajo los pies.',
    'photos_consistency' => 'Consistencia',
    'photos_consistency_body' => 'Misma hora, mismo lugar, mismo fondo cada quincena. Así la única variable que cambia es tu cuerpo.',

    // UploadSessionBar
    'photos_upload_session_label' => 'Sesión',
    'photos_upload_session_angles' => 'Frente · Perfil · Espalda',
    'photos_upload_date_label' => 'Fecha de la sesión',
    'photos_upload_uploading' => 'Subiendo...',
    'photos_upload_pick_one' => 'Selecciona al menos 1 foto',
    'photos_upload_partial' => 'Subir {selected} de {total}',
    'photos_upload_session_cta' => 'Subir sesión',

    // PhotoUploadZone
    'photos_zone_req' => 'REQ',
    'photos_zone_uploading' => 'SUBIENDO',
    'photos_zone_review' => 'REVISAR',
    'photos_zone_ready' => 'LISTA',
    'photos_zone_upload_aria' => 'Subir foto de {label}',
    'photos_zone_drag_or_take' => 'Arrastra o toma la foto',
    'photos_zone_formats' => 'JPG · PNG · max 12MB',
    'photos_zone_preview_alt' => 'Preview {label}',
    'photos_zone_replace_aria' => 'Cambiar foto de {label}',
    'photos_zone_change' => 'Cambiar',
    'photos_zone_remove_aria' => 'Eliminar foto de {label}',
    'photos_zone_uploading_label' => 'Subiendo {label}...',

    // PhotoValidationChips
    'photos_chips_aria' => 'Validación de la foto',
    'photos_chips_lighting_low' => 'Luz baja',
    'photos_chips_lighting_ok' => 'Luz',
    'photos_chips_framing_warn' => 'Encuadre',
    'photos_chips_framing_ok' => 'Encuadre',

    // EmptyState
    'photos_empty_aria' => 'Sin fotos aún',
    'photos_empty_title_line1' => 'Tu primer día',
    'photos_empty_title_line2' => 'queda guardado para siempre.',
    'photos_empty_body' => 'Tres fotos, cinco minutos. Después vas a poder volver a esta misma página cada dos semanas y ver — literalmente — cómo cambia tu cuerpo.',
    'photos_empty_cta_start' => 'Tomar primera sesión',
    'photos_empty_cta_guide' => 'Ver guía primero',

    // Timeline
    'photos_history_title' => 'Tu historia',
    'photos_compare_open' => 'Comparar',
    'photos_compare_close' => 'Cerrar comparación',
    'photos_timeline_prev_aria' => 'Sesiones anteriores',
    'photos_timeline_next_aria' => 'Sesiones siguientes',
    'photos_week_first' => 'Inicio',
    'photos_week_latest' => 'Reciente',
    'photos_session_view_aria' => 'Ver {label} de la sesión',
    'photos_has_notes_aria' => 'Tiene notas del coach',
    'photos_meta_kg' => 'kg',
    'photos_meta_waist_cm' => 'cm cintura',

    // Comparison
    'photos_compare_aria' => 'Comparativa de fotos',
    'photos_compare_before' => 'Antes',
    'photos_compare_after' => 'Después',
    'photos_compare_select_a_aria' => 'Sesión antes',
    'photos_compare_select_b_aria' => 'Sesión después',
    'photos_compare_swap_aria' => 'Intercambiar fechas',
    'photos_compare_swap' => 'Invertir',
    'photos_compare_empty' => 'Elige dos sesiones para comparar.',
    'photos_compare_label' => 'comparativa',
    'photos_compare_no_photo' => 'Sin foto',

    // Feedback panel
    'photos_feedback_title' => 'Notas de tu coach',
    'photos_feedback_delete_aria' => 'Eliminar foto',
    'photos_feedback_close_aria' => 'Cerrar panel',
    'photos_feedback_coach_prefix' => 'Coach',
    'photos_feedback_coach_role' => 'Tu coach 1:1',
    'photos_feedback_time_relative' => 'hace 2 días',
    'photos_feedback_no_notes' => 'Tu coach aún no ha dejado notas en esta foto.',
    'photos_feedback_reply_label' => 'Responder al coach',
    'photos_feedback_reply_placeholder' => 'Responde a tu coach...',
    'photos_feedback_send' => 'Enviar',
    'photos_feedback_sending' => 'Enviando...',
    'photos_feedback_coach_fallback' => 'Tu coach',

    // Feedback badge
    'photos_badge_reviewed' => 'Revisada',
    'photos_badge_notes' => 'Notas',
    'photos_badge_notes_count' => 'Notas · {n}',
    'photos_badge_pending' => 'Pendiente',

    // Achievement overlay
    'photos_success_title' => '¡Sesión guardada!',
    'photos_success_brand' => 'WELLCORE',
    'photos_success_angles' => 'ángulos registrados',
    'photos_success_body' => 'Tu progreso queda registrado. ¡La constancia transforma!',
    'photos_success_dismiss' => '¡LISTO!',

    // Delete confirm
    'photos_delete_confirm_title' => '¿Eliminar foto?',
    'photos_delete_confirm_body' => 'Esta acción no se puede deshacer. La foto se borra para siempre.',
    'photos_delete_cancel' => 'Cancelar',
    'photos_delete_confirm' => 'Eliminar',
    'photos_delete_failed' => 'No pudimos eliminar la foto. Intenta de nuevo.',

    // Error state
    'photos_error_title' => 'Error al cargar',
    'photos_error_retry' => 'Reintentar',

    // DateField (helper)
    'photos_datefield_label_default' => 'Fecha',
];
