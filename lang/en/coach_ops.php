<?php

return [

    // ═══════════════════════════════════════════════════════════════════
    // PLANS MANAGER (Coach/PlansManager.vue)
    // ═══════════════════════════════════════════════════════════════════

    'plans_context_label' => 'WORKSPACE',
    'plans_title' => 'PLANS',
    'plans_subtitle' => 'Your templates and assigned plans',

    'plans_tickets_cta_msg' => 'New plans are created from Plan tickets',
    'plans_tickets_cta_link' => 'Create ticket →',

    'plans_tab_my_templates' => 'My templates',
    'plans_tab_assigned' => 'Assigned',

    'plans_load_error' => 'We couldn’t load your plans. Try again.',
    'plans_retry' => 'Retry',

    'plans_stat_total' => 'Total',
    'plans_stat_training' => 'Training',
    'plans_stat_nutrition' => 'Nutrition',
    'plans_stat_habits' => 'Habits',

    'plans_search_placeholder' => 'Search templates…',
    'plans_filter_all_types' => 'All types',
    'plans_type_training' => 'Training',
    'plans_type_nutrition' => 'Nutrition',
    'plans_type_habits' => 'Habits',
    'plans_type_supplements' => 'Supplements',

    'plans_template_duration_na' => 'N/A',
    'plans_template_meta' => '{type} · {duration}',

    'plans_empty_templates_title' => 'No templates',
    'plans_empty_templates_subtitle' => 'No templates match those filters',

    'plans_assigned_status_active' => 'Active',
    'plans_assigned_status_finished' => 'Finished',
    'plans_assigned_meta' => '{plan} · {type}',

    'plans_empty_assigned_title' => 'No assigned plans',
    'plans_empty_assigned_subtitle' => 'You haven’t assigned any plans yet',

    // ═══════════════════════════════════════════════════════════════════
    // PLAN TICKETS LIST (PlanTickets/CoachPlanTicketsList.vue)
    // ═══════════════════════════════════════════════════════════════════

    'tickets_context_label' => 'WORKSPACE',
    'tickets_title' => 'PLAN TICKETS',
    'tickets_subtitle' => 'Manage submissions to admin',
    'tickets_create_cta' => 'Create ticket',

    'tickets_tab_all' => 'All',
    'tickets_tab_drafts' => 'Drafts',
    'tickets_tab_sent' => 'Sent',
    'tickets_tab_under_review' => 'Under review',
    'tickets_tab_completed' => 'Completed',
    'tickets_tab_rejected' => 'Rejected',

    'tickets_plan_type_essential' => 'Essential',
    'tickets_plan_type_method' => 'Method',
    'tickets_plan_type_elite' => 'Elite',

    'tickets_category_new_plan' => 'New plan',
    'tickets_category_adjustment' => 'Adjustment',

    'tickets_status_draft' => 'Draft',
    'tickets_status_sent' => 'Sent',
    'tickets_status_under_review' => 'Under review',
    'tickets_status_completed' => 'Completed',
    'tickets_status_rejected' => 'Rejected',

    'tickets_empty_msg' => 'You haven’t created any tickets yet. Start one for your first client.',
    'tickets_empty_cta' => 'Create a new ticket',

    'tickets_no_client_name' => 'Unnamed client',
    'tickets_created_at' => 'Created {date}',
    'tickets_submitted_at' => '· Sent {date}',

    'tickets_action_duplicate' => 'Duplicate',
    'tickets_action_duplicate_progress' => '…',
    'tickets_action_duplicate_title' => 'Duplicate ticket',
    'tickets_action_edit' => 'Edit',
    'tickets_action_view' => 'View',

    'tickets_toast_duplicate_success' => 'Ticket duplicated. Opening draft…',
    'tickets_toast_duplicate_error' => 'We couldn’t duplicate the ticket.',

    // ═══════════════════════════════════════════════════════════════════
    // PLAN TICKETS WIZARD (PlanTickets/CoachPlanTicketWizard.vue)
    // ═══════════════════════════════════════════════════════════════════

    // Header
    'wizard_back_to_list' => 'Back to list',
    'wizard_title_new' => 'NEW PLAN TICKET',
    'wizard_title_edit' => 'PLAN TICKET',
    'wizard_subtitle' => '{client} · {plan}',
    'wizard_subtitle_placeholder_client' => '…',

    // Resubmitted badge
    'wizard_resubmitted_title' => 'Resubmitted {date}',
    'wizard_resubmitted_label' => 'Edited after submission · {date}',

    // Saving indicator
    'wizard_saving' => 'Saving…',
    'wizard_saved' => 'Saved',

    // Readonly banner
    'wizard_readonly_title' => 'Ticket status {status} — read only',
    'wizard_banner_under_review' => 'This ticket is being reviewed by the WellCore team. It can’t be edited.',
    'wizard_banner_completed' => 'This ticket is already completed. The plan is assigned to the client.',
    'wizard_banner_rejected' => 'This ticket was rejected by the WellCore team. Review the comments and create a new ticket if needed.',

    // Progress
    'wizard_step_label' => 'Step {current} of {total} · {label}',

    // Step names
    'wizard_step_client' => 'Client and plan',
    'wizard_step_general' => 'General data',
    'wizard_step_training' => 'Training',
    'wizard_step_nutrition' => 'Nutrition',
    'wizard_step_habits' => 'Habits',
    'wizard_step_supplements' => 'Supplements',
    'wizard_step_cycle' => 'Hormonal cycle',
    'wizard_step_attachments' => 'Attachments',
    'wizard_step_review' => 'Review and submit',

    // STEP 1: client + plan
    'wizard_s1_heading' => '1. Client and plan type',
    'wizard_s1_client_label' => 'Client',
    'wizard_s1_client_placeholder' => 'Select a client…',
    'wizard_s1_loading_clients' => 'Loading clients…',
    'wizard_s1_plan_type_label' => 'Plan type',
    'wizard_s1_plan_essential_label' => 'Essential',
    'wizard_s1_plan_essential_desc' => 'Training, nutrition, habits and supplements.',
    'wizard_s1_plan_method_label' => 'Method',
    'wizard_s1_plan_method_desc' => 'Full plan with advanced tracking.',
    'wizard_s1_plan_elite_label' => 'Elite',
    'wizard_s1_plan_elite_desc' => 'Full plan + hormonal cycle.',
    'wizard_s1_category_label' => 'Request type',
    'wizard_s1_new_plan_label' => 'New plan',
    'wizard_s1_new_plan_desc' => 'New client or full plan from scratch. Requires all sections.',
    'wizard_s1_adjustment_label' => 'Plan adjustment',
    'wizard_s1_adjustment_desc' => 'Existing client who needs adjustments. Fill in only the sections that change.',

    'wizard_s1_summary_client' => 'Client',
    'wizard_s1_summary_plan_type' => 'Plan type',
    'wizard_s1_summary_category' => 'Request type',
    'wizard_s1_lock_notice' => 'Client and plan type can’t be changed after the ticket is created.',

    'wizard_s1_dup_title' => 'Duplicate from a previous ticket',
    'wizard_s1_dup_desc' => 'This client has previous plans. You can clone one to speed up the brief.',
    'wizard_s1_dup_placeholder' => 'Select a completed previous ticket…',
    'wizard_s1_dup_option_no_date' => 'no date',
    'wizard_s1_dup_button' => 'Duplicate and edit',
    'wizard_s1_dup_button_progress' => 'Duplicating…',
    'wizard_s1_dup_loading' => 'Looking up previous tickets for this client…',
    'wizard_s1_dup_confirm' => 'Create a new draft duplicating this previous ticket?',

    // STEP 2: general data
    'wizard_s2_heading' => '2. General data',
    'wizard_s2_autofill_btn' => 'Pre-fill from client profile',
    'wizard_s2_autofill_btn_loading' => 'Loading…',
    'wizard_s2_field_name' => 'Client name',
    'wizard_s2_field_plan' => 'Plan',
    'wizard_s2_field_plan_placeholder' => 'Select…',
    'wizard_s2_field_age' => 'Age',
    'wizard_s2_field_gender' => 'Gender',
    'wizard_s2_field_weight' => 'Weight (kg)',
    'wizard_s2_field_height' => 'Height (cm)',
    'wizard_s2_field_activity' => 'Daily activity level',
    'wizard_s2_field_activity_placeholder' => 'Select…',
    'wizard_s2_field_goal' => 'Main goal',
    'wizard_s2_field_goal_placeholder' => 'Describe the client’s goal in their own words…',

    'wizard_s2_autofill_no_client' => 'No client linked to this ticket.',
    'wizard_s2_autofill_none' => 'No previous data to pre-fill.',
    'wizard_s2_autofill_filled_one' => '{n} field filled from the profile.',
    'wizard_s2_autofill_filled_other' => '{n} fields filled from the profile.',
    'wizard_s2_autofill_error' => 'We couldn’t load the profile data.',

    // STEP 3: training
    'wizard_s3_heading' => '3. Training plan',
    'wizard_s3_adjustment_notice' => 'This is an adjustment ticket. Only fill this section if something needs to change.',
    'wizard_s3_place_label' => 'Training location',
    'wizard_s3_implements_label' => 'Available equipment',
    'wizard_s3_days_label' => 'Days per week',
    'wizard_s3_days_placeholder' => 'Select…',
    'wizard_s3_days_option' => '{n} days',
    'wizard_s3_strength_time_label' => 'Strength time (min)',
    'wizard_s3_cardio_time_label' => 'Cardio time (min)',
    'wizard_s3_cardio_pref_label' => 'Cardio preference',
    'wizard_s3_cardio_modality_label' => 'Cardio modality',
    'wizard_s3_level_label' => 'Level',
    'wizard_s3_level_beginner_desc' => 'Less than 6 months training.',
    'wizard_s3_level_intermediate_desc' => '6-24 months with solid technique.',
    'wizard_s3_level_advanced_desc' => 'More than 2 years, high load.',
    'wizard_s3_injuries_label' => 'Injuries',
    'wizard_s3_restrictions_label' => 'Restrictions',
    'wizard_s3_restrictions_placeholder' => 'E.g.: No deadlifts, no treadmill.',
    'wizard_s3_split_label' => 'Weekly split',
    'wizard_s3_split_priority_placeholder' => 'Priority (optional): e.g. upper glutes',

    // STEP 4: nutrition
    'wizard_s4_heading' => '4. Nutrition plan',
    'wizard_s4_adjustment_notice' => 'This is an adjustment ticket. Only fill this section if something needs to change.',
    'wizard_s4_goal_label' => 'Nutrition goal',
    'wizard_s4_meals_label' => 'Meals per day',
    'wizard_s4_methodology_label' => 'Methodology',
    'wizard_s4_methodology_placeholder' => 'Select…',
    'wizard_s4_times_label' => 'Meal times',
    'wizard_s4_times_placeholder' => 'E.g.: 7:00 am',
    'wizard_s4_times_add' => 'Add',
    'wizard_s4_excluded_foods_label' => 'Foods to EXCLUDE',
    'wizard_s4_prioritize_foods_label' => 'Foods to prioritize',
    'wizard_s4_meal_config_label' => 'Meal description and configuration',
    'wizard_s4_meal_config_placeholder' => 'E.g.: breakfast with eggs and other meals with oatmeal; AM snack with fruit…',

    // STEP 5: habits
    'wizard_s5_heading' => '5. Habits plan',
    'wizard_s5_adjustment_notice' => 'This is an adjustment ticket. Only fill this section if something needs to change.',
    'wizard_s5_focus_label' => 'Focus areas',
    'wizard_s5_morning_label' => 'Morning routine',
    'wizard_s5_night_label' => 'Night routine',
    'wizard_s5_other_label' => 'Other habits',

    // STEP 6: supplements
    'wizard_s6_heading' => 'Supplements plan',
    'wizard_s6_adjustment_notice' => 'This is an adjustment ticket. Only fill this section if something needs to change.',
    'wizard_s6_goal_label' => 'Stack goal',
    'wizard_s6_goal_placeholder' => 'What it’s optimized for — e.g. baseline performance, recomposition, recovery…',
    'wizard_s6_supplements_label' => 'Supplements',
    'wizard_s6_add_supplement' => 'Add supplement',
    'wizard_s6_empty_supplements' => 'Add at least one supplement.',
    'wizard_s6_supplement_remove' => 'Remove',
    'wizard_s6_field_name' => 'Name',
    'wizard_s6_field_name_placeholder' => 'E.g.: Whey protein, Creatine monohydrate',
    'wizard_s6_field_dose' => 'Dose',
    'wizard_s6_field_dose_placeholder' => 'E.g.: 30g, 5g',
    'wizard_s6_field_timing' => 'Timing',
    'wizard_s6_field_timing_placeholder' => 'E.g.: Post-workout, before bed',
    'wizard_s6_field_frequency' => 'Frequency',
    'wizard_s6_field_frequency_placeholder' => 'Select…',
    'wizard_s6_field_notes' => 'Notes (optional)',
    'wizard_s6_field_notes_placeholder' => 'E.g.: take with water, avoid with caffeine',
    'wizard_s6_coach_notes_label' => 'Coach notes for the stack (optional)',
    'wizard_s6_coach_notes_placeholder' => 'General guidance, warnings, cycle window…',
    'wizard_s6_frequency_daily' => 'Daily',
    'wizard_s6_frequency_training_days' => 'Training days',
    'wizard_s6_frequency_3x_week' => '3x per week',
    'wizard_s6_frequency_cyclic' => 'Cyclic',

    // STEP 7: cycle (Elite only)
    'wizard_s7_heading' => '6. Hormonal cycle',
    'wizard_s7_last_period_label' => 'Last period date',
    'wizard_s7_cycle_duration_label' => 'Cycle length (days)',
    'wizard_s7_symptoms_label' => 'Symptoms',
    'wizard_s7_contraceptive_label' => 'Contraceptive',
    'wizard_s7_notes_label' => 'Additional notes',

    // STEP 8: attachments
    'wizard_s8_heading' => 'Attachments (optional)',
    'wizard_s8_subtitle' => 'Progress photos, lab results, medical documents, etc. Max 10MB per file.',
    'wizard_s8_category_label' => 'File category',
    'wizard_s8_category_none' => 'No category',
    'wizard_s8_category_progress_photo' => 'Progress photo',
    'wizard_s8_category_lab' => 'Lab result',
    'wizard_s8_category_medical' => 'Medical document',
    'wizard_s8_category_other' => 'Other',
    'wizard_s8_dropzone_idle' => 'Drag a file or click to upload',
    'wizard_s8_dropzone_uploading' => 'Uploading…',
    'wizard_s8_dropzone_hint' => 'JPG, PNG, WEBP, HEIC, PDF or DOCX · max 10MB',
    'wizard_s8_list_label' => 'Files ({n})',
    'wizard_s8_empty_list' => 'No attachments yet.',
    'wizard_s8_action_view' => 'View',
    'wizard_s8_action_delete' => 'Delete',
    'wizard_s8_uploader_fallback' => 'Coach',
    'wizard_s8_file_too_large' => 'File exceeds 10MB.',
    'wizard_s8_file_type_not_allowed' => 'File type not allowed.',
    'wizard_s8_toast_uploaded' => 'File uploaded',
    'wizard_s8_toast_upload_error' => 'We couldn’t upload the file.',
    'wizard_s8_confirm_delete' => 'Delete this file?',
    'wizard_s8_toast_deleted' => 'File deleted',
    'wizard_s8_toast_delete_error' => 'We couldn’t delete the file.',

    // STEP 9: review
    'wizard_s9_heading' => 'Final review',
    'wizard_s9_summary_client' => 'Client',
    'wizard_s9_summary_plan' => 'Plan',
    'wizard_s9_summary_age_gender' => 'Age / Gender',
    'wizard_s9_summary_weight_height' => 'Weight / Height',
    'wizard_s9_summary_weight_height_value' => '{weight} kg · {height} cm',
    'wizard_s9_summary_place_days' => 'Training location',
    'wizard_s9_summary_place_days_value' => '{place} · {days} days',
    'wizard_s9_summary_level' => 'Level',
    'wizard_s9_summary_nutrition' => 'Nutrition',
    'wizard_s9_summary_nutrition_value' => '{meals} meals · {methodology}',
    'wizard_s9_summary_no_methodology' => 'no methodology',
    'wizard_s9_summary_habits' => 'Habits',
    'wizard_s9_summary_habits_value_one' => '{n} focus area',
    'wizard_s9_summary_habits_value_other' => '{n} focus areas',
    'wizard_s9_summary_supplements' => 'Supplements',
    'wizard_s9_summary_supplements_value_one' => '{n} supplement',
    'wizard_s9_summary_supplements_value_other' => '{n} supplements',
    'wizard_s9_summary_supplement_name_empty' => '(no name)',
    'wizard_s9_summary_cycle' => 'Hormonal cycle',
    'wizard_s9_summary_cycle_value' => '{date} · {days} days',

    'wizard_s9_missing_fields_title' => 'Missing fields:',

    'wizard_s9_responsibility_title' => 'BEFORE YOU SUBMIT THIS TICKET',
    'wizard_s9_responsibility_adjustment' => 'Describe clearly what adjustment the client needs.',
    'wizard_s9_responsibility_bullet1' => 'What you write here directly defines the quality of the plan your client will receive.',
    'wizard_s9_responsibility_bullet2_pre' => 'This 1-on-1 validation with the client is a core responsibility of the coach —',
    'wizard_s9_responsibility_bullet2_strong' => 'talk to them like a human, stay close, and read between their answers',
    'wizard_s9_responsibility_bullet2_post' => 'to give us real context.',
    'wizard_s9_responsibility_bullet3' => 'The more detail and context you provide, the more personalized and effective the plan will be.',
    'wizard_s9_responsibility_bullet4' => 'This is your responsibility to the company — your work here is the first filter that drives the client’s success.',
    'wizard_s9_responsibility_close_strong' => 'What we expect from you:',
    'wizard_s9_responsibility_close_text' => 'that you be excellent at it. Clients trust your professionalism — let that be the standard these tickets reflect.',

    // Submit actions
    'wizard_delete_draft' => 'Delete draft',
    'wizard_delete_draft_progress' => 'Deleting…',
    'wizard_save_as_draft' => 'Save as draft',
    'wizard_submit_ticket' => 'Submit ticket',
    'wizard_submit_ticket_progress' => 'Submitting…',

    // Navigation
    'wizard_nav_prev' => 'Previous',
    'wizard_nav_next' => 'Next',
    'wizard_nav_create_and_continue' => 'Create and continue',

    // Toasts / confirms (wizard)
    'wizard_toast_create_missing' => 'Select a client and a plan type.',
    'wizard_toast_created' => 'Ticket created. Complete the brief.',
    'wizard_toast_create_error' => 'We couldn’t create the ticket.',
    'wizard_toast_load_error' => 'We couldn’t load the ticket.',
    'wizard_toast_save_error' => 'We couldn’t save.',
    'wizard_toast_submitted' => 'Ticket sent to the WellCore team.',
    'wizard_toast_submit_missing_fields' => 'Some required fields are missing.',
    'wizard_toast_submit_error' => 'We couldn’t submit the ticket.',
    'wizard_toast_dup_prev_success' => 'Ticket duplicated. Opening draft…',
    'wizard_toast_dup_prev_error' => 'We couldn’t duplicate the previous ticket.',
    'wizard_toast_delete_success' => 'Draft deleted.',
    'wizard_toast_delete_error' => 'We couldn’t delete it.',
    'wizard_confirm_delete_draft' => 'Delete this draft? This can’t be undone.',

    // Static catalogs (days, activity levels, methodologies)
    'wizard_day_monday' => 'Monday',
    'wizard_day_tuesday' => 'Tuesday',
    'wizard_day_wednesday' => 'Wednesday',
    'wizard_day_thursday' => 'Thursday',
    'wizard_day_friday' => 'Friday',
    'wizard_day_saturday' => 'Saturday',
    'wizard_day_sunday' => 'Sunday',

    'wizard_activity_sedentary' => 'Sedentary (desk job, little movement)',
    'wizard_activity_light' => 'Light (exercise 1-2x per week)',
    'wizard_activity_moderate' => 'Moderate (exercise 3-4x per week)',
    'wizard_activity_active' => 'Active (exercise 5-6x per week)',
    'wizard_activity_very_active' => 'Very active (physical job + exercise)',

    'wizard_method_deficit_label' => 'Caloric deficit',
    'wizard_method_deficit_desc' => 'Fat loss with a sustainable deficit.',
    'wizard_method_flexible_label' => 'Flexible / IIFYM',
    'wizard_method_flexible_desc' => 'Flexible macros, open preferences.',
    'wizard_method_carb_cycling_label' => 'Carb cycling',
    'wizard_method_carb_cycling_desc' => 'High/low carb cycling.',
    'wizard_method_fasting_label' => 'Intermittent fasting',
    'wizard_method_fasting_desc' => 'Eating window (16:8 / 18:6).',
    'wizard_method_maintenance_label' => 'Maintenance',
    'wizard_method_maintenance_desc' => 'Maintenance calories for recomposition.',
    'wizard_method_lean_bulk_label' => 'Lean bulk',
    'wizard_method_lean_bulk_desc' => 'Controlled caloric surplus.',

    // Relative time
    'wizard_time_just_now' => 'a moment ago',
    'wizard_time_minutes' => '{n} min ago',
    'wizard_time_hours' => '{n} h ago',
    'wizard_time_days' => '{n} d ago',

    // ═══════════════════════════════════════════════════════════════════
    // ANALYTICS (Coach/Analytics.vue)
    // ═══════════════════════════════════════════════════════════════════

    'analytics_context_label' => 'MAIN',
    'analytics_title' => 'ANALYTICS',
    'analytics_subtitle' => 'Performance and team metrics',

    'analytics_range_month' => 'Month',
    'analytics_range_quarter' => 'Quarter',
    'analytics_range_year' => 'Year',
    'analytics_range_all' => 'All time',

    'analytics_loading' => 'Refreshing metrics…',

    'analytics_empty_title' => 'No metrics yet',
    'analytics_empty_subtitle' => 'Metrics will appear once your clients start logging check-ins and activity.',

    'analytics_coach_score' => 'Coach Score',
    'analytics_coach_score_subtitle' => 'Composite performance score',
    'analytics_score_label_excellent' => 'Excellent',
    'analytics_score_label_regular' => 'Average',
    'analytics_score_label_needs_improvement' => 'Needs improvement',

    'analytics_metric_response' => 'Response',
    'analytics_metric_response_value' => '{hours}h',
    'analytics_metric_reply_rate' => 'Reply rate',
    'analytics_metric_retention' => 'Retention',
    'analytics_metric_wellbeing' => 'Well-being',
    'analytics_metric_wellbeing_value' => '{value}/10',
    'analytics_metric_checkins' => 'Check-ins',
    'analytics_metric_messages' => 'Messages',

    'analytics_sla_title' => 'Response SLA',
    'analytics_sla_within_24h' => 'Within 24h',
    'analytics_sla_24_48h' => '24-48h',
    'analytics_sla_over_48h' => 'Over 48h',

    'analytics_revenue_title' => 'Revenue',
    'analytics_revenue_total' => 'Total',
    'analytics_revenue_monthly' => 'Monthly',
    'analytics_revenue_active_clients' => 'Active clients',

    'analytics_overview_title' => 'Client overview',
    'analytics_overview_col_client' => 'Client',
    'analytics_overview_col_wellbeing' => 'Well-being',
    'analytics_overview_col_checkins' => 'Check-ins',
    'analytics_overview_col_adherence' => 'Adherence',
    'analytics_overview_empty' => 'No client data yet',
    'analytics_overview_no_value' => '-',

    // ═══════════════════════════════════════════════════════════════════
    // NOTIFICATIONS PREFERENCES (Coach/NotificationsPreferences.vue)
    // ═══════════════════════════════════════════════════════════════════

    'notif_title' => 'Notifications',
    'notif_subtitle' => 'Choose which team events to follow and how to receive them.',

    'notif_channels_heading' => 'Channels',
    'notif_push_label' => 'Push (browser)',
    'notif_push_desc' => 'Real-time browser notifications.',
    'notif_push_granted' => 'Enabled',
    'notif_push_request' => 'Enable',
    'notif_push_blocked' => 'Blocked',
    'notif_in_app_label' => 'In-app (bell)',
    'notif_in_app_desc' => 'Appear in the bell icon on the topbar.',

    'notif_events_heading' => 'When to notify me',
    'notif_event_pr_broken' => 'When a client hits a new PR',
    'notif_event_streak_milestone' => 'When a client reaches a milestone (7 / 30 / 100 days)',
    'notif_event_post_created' => 'When a client creates a post (silent by default)',
    'notif_event_comment_on_reply' => 'When someone comments after my reply',
    'notif_event_at_risk_client' => 'When a client has been inactive for 5+ days',
    'notif_event_official_engagement' => 'When a client reacts to my official post',
    'notif_event_admin_broadcast' => 'When WellCore admin sends an announcement',

    'notif_saving' => 'Saving…',

    'notif_load_error' => 'We couldn’t load your preferences.',
    'notif_save_error' => 'We couldn’t save.',
    'notif_push_granted_toast' => 'Browser notifications enabled.',

];
