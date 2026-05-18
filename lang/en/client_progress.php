<?php

return [
    // ===== CheckinForm.vue =====
    // Header
    'checkin_title' => 'Weekly check-in',
    'checkin_coach_replies_prefix' => 'Your coach replies in',
    'checkin_coach_replies_value' => 'under 24 hours',
    'checkin_week_label' => 'Week {n}',
    'checkin_not_available_title' => 'Check-in not available today',
    'checkin_not_available_body_prefix' => 'The weekly check-in opens',
    'checkin_not_available_body_days' => 'Friday or Saturday',
    'checkin_not_available_body_suffix' => '. Keep training — consistency is your superpower.',
    'checkin_form_title_sr' => 'Check-in form',
    'checkin_progress_aria' => 'Check-in progress',

    // Wizard steps
    'checkin_step_wellbeing' => 'Wellbeing',
    'checkin_step_training' => 'Training',
    'checkin_step_nutrition' => 'Nutrition',
    'checkin_step_notes' => 'Notes',

    // Step 1: Wellbeing
    'checkin_q_wellbeing_title' => 'Overall wellbeing',
    'checkin_q_wellbeing_hint' => 'How have you felt overall this week? (energy, mood, recovery)',
    'checkin_q_wellbeing_label' => 'Your wellbeing level',
    'checkin_scale_very_bad' => 'Very bad',
    'checkin_scale_bad' => 'Bad',
    'checkin_scale_ok' => 'OK',
    'checkin_scale_good' => 'Good',
    'checkin_scale_very_good' => 'Very good',
    'checkin_scale_very_bad_hint' => 'Wiped out, no energy, low mood.',
    'checkin_scale_bad_hint' => 'Not a good week overall.',
    'checkin_scale_ok_hint' => 'Balanced — neither high nor low.',
    'checkin_scale_good_hint' => 'Solid energy and mood most of the week.',
    'checkin_scale_very_good_hint' => 'Excellent week, top-tier energy.',
    'checkin_err_wellbeing_required' => 'Pick your wellbeing level (1-5)',
    'checkin_err_wellbeing_required_short' => 'Pick your wellbeing level',

    // Step 2: Training
    'checkin_q_training_title' => 'Training',
    'checkin_q_training_hint' => 'How many days did you train? How did the load feel?',
    'checkin_q_days_trained' => 'Days trained',
    'checkin_q_days_picker_aria' => 'Days trained this week',
    'checkin_days_of_7' => 'of 7 days',
    'checkin_days_excellent' => 'Excellent consistency.',
    'checkin_days_building' => 'You\'re building the habit.',
    'checkin_days_quiet' => 'A quiet week — recover well.',
    'checkin_q_rpe_label' => 'Average RPE for the week',
    'checkin_rpe_left' => 'Very easy (1)',
    'checkin_rpe_right' => 'Max effort (10)',
    'checkin_err_days_range' => 'Days trained must be between 0 and 7',
    'checkin_err_rpe_range' => 'RPE must be between 1 and 10',

    // Step 3: Nutrition
    'checkin_q_nutrition_title' => 'Nutrition',
    'checkin_q_nutrition_hint' => 'How well did you stick to your nutrition plan this week?',
    'checkin_nutrition_aria' => 'Nutrition plan adherence',
    'checkin_nutrition_followed_label' => 'I followed it well',
    'checkin_nutrition_followed_hint' => 'Stuck to my plan at least 80% of the week.',
    'checkin_nutrition_partial_label' => 'Partially',
    'checkin_nutrition_partial_hint' => 'Had some slip-ups but stayed mostly on track.',
    'checkin_nutrition_no_label' => 'I didn\'t follow it',
    'checkin_nutrition_no_hint' => 'This week was rough. I need some support.',
    'checkin_err_nutrition_required' => 'Pick a nutrition option',

    // Step 4: Notes / Submit
    'checkin_q_notes_title' => 'Notes for your coach',
    'checkin_q_notes_hint' => 'Tell your coach how it went, what questions you have, or what to tweak. (optional)',
    'checkin_notes_label_sr' => 'Comment for your coach',
    'checkin_notes_placeholder' => 'e.g. Incline press felt strong this week, hit 175 lb × 8. Nutrition was around 75% because of a work dinner. Can we adjust lunch portions?',
    'checkin_notes_realtime_hint' => 'Your coach gets it instantly.',
    'checkin_err_notes_max' => 'Comment can\'t exceed 1000 characters ({n}/1000)',
    'checkin_summary_title' => 'Summary',
    'checkin_summary_wellbeing' => 'Wellbeing',
    'checkin_summary_days' => 'Days',
    'checkin_summary_nutrition' => 'Nutrition',
    'checkin_summary_rpe' => 'RPE',
    'checkin_summary_nutrition_well' => 'On track',
    'checkin_summary_nutrition_partial' => 'Partial',
    'checkin_summary_nutrition_no' => 'Off',

    // Buttons
    'checkin_btn_next' => 'Next',
    'checkin_btn_back' => 'Back',
    'checkin_btn_back_aria' => 'Previous step',
    'checkin_btn_next_step' => 'Next step',
    'checkin_btn_sending' => 'Sending...',
    'checkin_btn_submit' => 'Send check-in',
    'checkin_btn_unavailable' => 'Available Friday',
    'checkin_btn_unavailable_title' => 'Only available Friday and Saturday',

    // Submit feedback
    'checkin_toast_sent' => 'Check-in sent.',
    'checkin_toast_form_review' => 'Review the form fields.',
    'checkin_toast_send_failed' => 'We couldn\'t send your check-in.',
    'checkin_err_load' => 'Error loading check-in',
    'checkin_err_send_generic' => 'Error sending the check-in',

    // Success overlay
    'checkin_success_brand' => 'WellCore',
    'checkin_success_title' => 'Check-in sent',
    'checkin_success_days_short' => 'days trained',
    'checkin_success_wellbeing_short' => 'wellbeing',
    'checkin_success_week_short' => 'week',
    'checkin_success_week_prefix' => 'W',
    'checkin_success_body' => 'Your coach will review your report this week. Keep it up.',
    'checkin_success_dismiss' => 'Got it',

    // Tutorial
    'checkin_tutorial_title' => 'Weekly check-in',
    'checkin_tutorial_close_aria' => 'Close',
    'checkin_tutorial_step1_title' => 'What is the check-in?',
    'checkin_tutorial_step1_body' => 'It\'s your weekly report to your coach. With this info your coach adjusts your training and nutrition plan to maximize your results week after week.',
    'checkin_tutorial_step2_title' => 'Be honest',
    'checkin_tutorial_step2_body' => 'There are no wrong answers. If you had a tough week, say it. Your coach can only help you if they know your reality — not the polished version.',
    'checkin_tutorial_step3_title' => 'Do it every week',
    'checkin_tutorial_step3_body' => 'Clients who complete their weekly check-in progress 3× faster. Consistent tracking is what separates average results from outstanding ones.',
    'checkin_tutorial_back' => 'Back',
    'checkin_tutorial_next' => 'Next',
    'checkin_tutorial_start' => 'Got it, let\'s start',

    // Recent check-ins list
    'checkin_recent_title' => 'Previous check-ins',
    'checkin_recent_status_replied' => 'Replied',
    'checkin_recent_status_pending' => 'In review',
    'checkin_recent_badge_wellbeing' => 'Wellbeing {value}/5',
    'checkin_recent_badge_rpe' => 'RPE {value}/10',
    'checkin_recent_badge_days' => '{value}/7 days',
    'checkin_recent_nutrition_full' => 'Nutrition 100%',
    'checkin_recent_nutrition_partial' => 'Nutrition partial',
    'checkin_recent_nutrition_no' => 'Nutrition off',
    'checkin_recent_coach_reply_label' => 'Coach reply',

    // Date formatting
    'date_format_long' => '{month} {d}, {year}',
    'month_jan' => 'January',
    'month_feb' => 'February',
    'month_mar' => 'March',
    'month_apr' => 'April',
    'month_may' => 'May',
    'month_jun' => 'June',
    'month_jul' => 'July',
    'month_aug' => 'August',
    'month_sep' => 'September',
    'month_oct' => 'October',
    'month_nov' => 'November',
    'month_dec' => 'December',

    // ===== MetricsV2 / Metrics components =====
    'metrics_section_title' => 'Metrics',
    'metrics_breadcrumb_dashboard' => 'Dashboard',
    'metrics_breadcrumb_metrics' => 'Metrics',
    'metrics_hero_subtitle' => 'Your weight, body composition and measurements — read in context by your coach.',
    'metrics_streak_weeks_short' => '{nw}',
    'metrics_streak_weeks_title' => '{n} weeks in a row',
    'metrics_last_prefix' => 'Last:',

    // Tutorial
    'metrics_tutorial_aria' => 'Welcome to Metrics',
    'metrics_tutorial_step1_title' => 'Welcome to Metrics',
    'metrics_tutorial_step1_body' => 'Log your weight and measurements to see your progress over time.',
    'metrics_tutorial_step2_title' => 'Quick vs. full mode',
    'metrics_tutorial_step2_body' => 'Use quick mode to log just your weight. Full mode includes body measurements and composition.',
    'metrics_tutorial_step3_title' => 'Consistency = results',
    'metrics_tutorial_step3_body' => 'Log at least once a week, fasted and under the same conditions, to get accurate data.',
    'metrics_tutorial_next' => 'Next',
    'metrics_tutorial_start' => 'Get started',
    'metrics_tutorial_skip' => 'Skip',

    // Achievements
    'metrics_ach_weight_logged' => 'Weight logged!',
    'metrics_ach_full_logged' => 'Full entry saved!',

    // Errors
    'metrics_retry' => 'Retry',
    'metrics_err_quick_invalid' => 'Enter a valid weight (20–300 kg)',
    'metrics_err_save' => 'Error saving. Try again.',

    // Stat cards
    'metrics_current_weight' => 'Current weight',
    'metrics_monthly_change' => 'Monthly change',
    'metrics_goal' => 'Goal',
    'metrics_records' => 'Entries',
    'metrics_goal_current_prefix' => 'Current',
    'metrics_goal_target_prefix' => 'Goal',

    // WeightChart
    'metrics_chart_title' => 'Body weight',
    'metrics_chart_sub' => '{period} evolution',
    'metrics_chart_period_aria' => 'Chart period',
    'metrics_chart_legend_weight' => 'Weight',
    'metrics_chart_empty_title' => 'No weight data',
    'metrics_chart_empty_msg' => 'Log your first weight to see your evolution over time.',
    'metrics_chart_empty_cta' => 'Log your first entry →',

    // CheckinsStreak
    'metrics_streak_title' => 'Weekly check-ins',
    'metrics_streak_sub' => 'Last 12 weeks',
    'metrics_streak_history_aria' => 'Check-in history',
    'metrics_streak_week_n' => 'Week {n}: {cnt} check-in(s)',
    'metrics_streak_empty' => 'No recent check-ins',
    'metrics_streak_axis_past' => '12 WEEKS AGO',
    'metrics_streak_axis_today' => 'TODAY',
    'metrics_streak_attendance_suffix' => '% attendance',

    // CompositionPanel
    'metrics_composition_title' => 'Body composition',
    'metrics_composition_last_prefix' => 'Latest measurement',
    'metrics_composition_muscle' => 'Muscle',
    'metrics_composition_fat' => 'Fat',
    'metrics_composition_water' => 'Water',
    'metrics_composition_empty' => 'No composition data',
    'metrics_chest' => 'Chest',
    'metrics_waist' => 'Waist',
    'metrics_hip' => 'Hips',
    'metrics_thigh' => 'Thigh',
    'metrics_arm' => 'Arm',

    // CoachInterpretation
    'metrics_coach_role' => 'Coach',
    'metrics_coach_view_checkin' => 'View check-in',

    // CrossLinkPhotos
    'metrics_photos_title' => 'Progress photos',
    'metrics_photos_count_singular' => '{n} photo logged',
    'metrics_photos_count_plural' => '{n} photos logged',
    'metrics_photos_empty' => 'The visual transformation tells what the numbers can\'t',
    'metrics_photos_view' => 'View photos',

    // MetricsForm
    'metrics_form_title' => 'New entry',
    'metrics_form_sub' => 'Ideally fasted, same day and time each week.',
    'metrics_form_mode_quick' => 'Quick',
    'metrics_form_mode_full' => 'Full',

    // QuickLogInput
    'metrics_quick_label' => 'WEIGHT',
    'metrics_quick_kg' => 'kg',
    'metrics_quick_hint' => 'fasted',
    'metrics_quick_aria_weight' => 'Weight in kilograms',
    'metrics_quick_saving' => 'Saving...',
    'metrics_quick_save' => 'Save',
    'metrics_quick_full' => 'Full',

    // MeasurementsForm
    'metrics_meas_weight_kg' => 'Weight (kg)',
    'metrics_meas_required_aria' => 'required',
    'metrics_meas_muscle_pct' => '% Muscle',
    'metrics_meas_fat_pct' => '% Fat',
    'metrics_meas_notes' => 'Notes',
    'metrics_meas_notes_placeholder' => 'Fasted, post-workout...',
    'metrics_meas_section_title' => 'Body measurements',
    'metrics_meas_section_sub' => 'Measure with a tape, in the morning',
    'metrics_meas_chest_cm' => 'Chest (cm)',
    'metrics_meas_waist_cm' => 'Waist (cm)',
    'metrics_meas_hip_cm' => 'Hips (cm)',
    'metrics_meas_thigh_cm' => 'Thigh (cm)',
    'metrics_meas_arm_cm' => 'Arm (cm)',
    'metrics_meas_guide_toggle' => 'How to take measurements correctly',
    'metrics_meas_guide_chest' => 'Tape at nipple line. Arms relaxed. Don\'t puff your chest.',
    'metrics_meas_guide_waist' => 'At the narrowest point, 2-3 cm above the navel. Exhale normally.',
    'metrics_meas_guide_hip' => 'At the widest point of the glutes. Feet together, standing tall.',
    'metrics_meas_guide_thigh' => 'At the thickest point, just below the glute. Leg relaxed.',
    'metrics_meas_guide_arm' => 'At the thickest point of the bicep. Arm relaxed, not flexed.',
    'metrics_meas_guide_tip' => 'Always measure under the same conditions: morning, before eating.',
    'metrics_meas_privacy' => 'Only you and your coach can see this data',
    'metrics_meas_save_draft' => 'Save draft',
    'metrics_meas_saving' => 'Saving...',
    'metrics_meas_save' => 'Save entry',

    // ===== FoodTracking.vue =====
    'food_title' => 'My nutrition',
    'food_subtitle' => 'Log every meal and your coach reviews it',
    'food_retry' => 'Retry',
    'food_streak_days' => '{n} days in a row',
    'food_xp_today' => '+{n} XP today',
    'food_no_plan_notice' => 'You don\'t have a personalized nutrition plan yet. While your coach builds it, you can log your meals using the general categories below.',
    'food_progress_label' => 'Today you\'ve logged {done} of {total} meals',
    'food_bonus_complete' => 'Daily bonus complete (+30 XP)',
    'food_xp_per_meal' => '+15 XP',
    'food_kcal_unit' => 'kcal',
    'food_meal_generic' => 'Meal',

    // Photo states
    'food_replace' => 'Replace',
    'food_delete' => 'Delete',
    'food_reaction_good' => 'Good',
    'food_reaction_improve' => 'Improve',
    'food_reaction_seen' => 'Seen',

    // Notes
    'food_your_description' => 'Your description',
    'food_saving' => 'Saving...',
    'food_describe_placeholder' => 'Describe what you ate or use voice dictation',
    'food_dictate_start' => 'Dictate by voice',
    'food_dictate_stop' => 'Stop dictation',
    'food_recording' => 'Recording...',
    'food_what_you_ate' => 'What did you eat? (optional)',
    'food_pre_placeholder' => 'e.g. scrambled eggs with oatmeal, black coffee',
    'food_pre_pending_pill' => 'Pending',
    'food_no_voice_hint' => 'Your browser doesn\'t support voice dictation. Use the keyboard.',
    'food_no_voice_alert' => 'Your browser doesn\'t support voice dictation',

    // Upload actions
    'food_cancel' => 'Cancel',
    'food_uploading' => 'Uploading...',
    'food_confirm_photo' => 'Confirm photo',
    'food_upload_photo' => 'Upload photo',
    'food_coach_note_label' => 'Coach note',
    'food_alt_meal_photo' => 'Photo of {meal}',
    'food_alt_preview' => 'Preview {meal}',

    // Errors
    'food_err_format' => 'Only images are allowed (JPG, PNG, WebP).',
    'food_err_size' => 'Image can\'t exceed 10 MB.',
    'food_err_upload_generic' => 'Error uploading the photo. Try again.',
    'food_err_delete' => 'We couldn\'t delete the photo. Try again.',
    'food_err_save_note' => 'We couldn\'t save the note. Try again.',
    'food_err_dictation' => 'We couldn\'t start dictation. Check microphone permissions.',
    'food_confirm_delete' => 'Delete this photo?',

    // ===== PhotosV2 / Photos components =====
    // PhotosHero
    'photos_kicker' => 'Progress photos',
    'photos_title_line1' => 'Your body,',
    'photos_title_line2' => 'week by week.',
    'photos_hero_intro' => 'These photos tell a story the scale can\'t. They go only to your coach {coachName} — private, encrypted, yours.',
    'photos_stat_sessions' => 'Sessions',
    'photos_stat_weeks' => 'Weeks',
    'photos_stat_weeks_short' => 'wk',
    'photos_stat_latest' => 'Latest',
    'photos_next_session' => 'Next session',
    'photos_next_today_prefix' => 'today — ',
    'photos_next_tomorrow_prefix' => 'tomorrow — ',
    'photos_next_in_days' => 'in {n} days — {date}',

    // PrivacyReassurance
    'photos_privacy_title' => 'Only your coach sees these photos',
    'photos_privacy_body' => 'Encrypted in transit and at rest. Never visible on your public profile and never shared with the community.',
    'photos_privacy_only_coach' => 'Only {coachName}',
    'photos_aes_encrypt' => 'AES-256',
    'photos_privacy_policy' => 'Policy',

    // PhotoGuide
    'photos_guide_title' => 'Photo guide',
    'photos_guide_sub' => 'How to take your photos for precise progress tracking',
    'photos_guide_kicker' => '/ visual guide',
    'photos_guide_headline' => 'Three angles. One routine.',
    'photos_guide_intro' => 'Technique matters because the comparison is only honest if the photos are consistent.',

    // AnglesGrid
    'photos_angles_aria' => 'Required angles',
    'photos_angle_req_short' => 'Req',
    'photos_front' => 'Front',
    'photos_side' => 'Side',
    'photos_back' => 'Back',
    'photos_front_desc' => 'Facing the camera directly, arms relaxed at your sides. Feet hip-width apart.',
    'photos_side_desc' => 'Left side facing the camera, exactly 90°. Look at the horizon, arms loose.',
    'photos_back_desc' => 'Back to the camera, arms at your sides. Shoulders relaxed, face forward.',

    // TipsList
    'photos_tips_kicker' => 'Four rules',
    'photos_tips_title' => 'So the comparison is real',
    'photos_tips_aria' => 'Rules for taking precise photos',
    'photos_lighting' => 'Lighting',
    'photos_lighting_body' => 'Natural light from the front or side. Avoid backlight and overhead lamps — they create shadows that lie.',
    'photos_clothing' => 'Clothing',
    'photos_clothing_body' => 'Fitted clothing — shorts and a top, or underwear. Loose clothing hides real body changes.',
    'photos_distance' => 'Distance',
    'photos_distance_body' => '1.5 to 2 meters from the camera. Full body in frame, with space above the head and below the feet.',
    'photos_consistency' => 'Consistency',
    'photos_consistency_body' => 'Same time, same place, same background every two weeks. That way the only variable changing is your body.',

    // UploadSessionBar
    'photos_upload_session_label' => 'Session',
    'photos_upload_session_angles' => 'Front · Side · Back',
    'photos_upload_date_label' => 'Session date',
    'photos_upload_uploading' => 'Uploading...',
    'photos_upload_pick_one' => 'Pick at least 1 photo',
    'photos_upload_partial' => 'Upload {selected} of {total}',
    'photos_upload_session_cta' => 'Upload session',

    // PhotoUploadZone
    'photos_zone_req' => 'REQ',
    'photos_zone_uploading' => 'UPLOADING',
    'photos_zone_review' => 'REVIEW',
    'photos_zone_ready' => 'READY',
    'photos_zone_upload_aria' => 'Upload {label} photo',
    'photos_zone_drag_or_take' => 'Drag in or take a photo',
    'photos_zone_formats' => 'JPG · PNG · max 12MB',
    'photos_zone_preview_alt' => 'Preview {label}',
    'photos_zone_replace_aria' => 'Replace {label} photo',
    'photos_zone_change' => 'Change',
    'photos_zone_remove_aria' => 'Remove {label} photo',
    'photos_zone_uploading_label' => 'Uploading {label}...',

    // PhotoValidationChips
    'photos_chips_aria' => 'Photo validation',
    'photos_chips_lighting_low' => 'Low light',
    'photos_chips_lighting_ok' => 'Light',
    'photos_chips_framing_warn' => 'Framing',
    'photos_chips_framing_ok' => 'Framing',

    // EmptyState
    'photos_empty_aria' => 'No photos yet',
    'photos_empty_title_line1' => 'Your day one',
    'photos_empty_title_line2' => 'gets saved forever.',
    'photos_empty_body' => 'Three photos, five minutes. Then you\'ll be able to come back to this same page every two weeks and see — literally — how your body changes.',
    'photos_empty_cta_start' => 'Take first session',
    'photos_empty_cta_guide' => 'See guide first',

    // Timeline
    'photos_history_title' => 'Your story',
    'photos_compare_open' => 'Compare',
    'photos_compare_close' => 'Close comparison',
    'photos_timeline_prev_aria' => 'Previous sessions',
    'photos_timeline_next_aria' => 'Next sessions',
    'photos_week_first' => 'Start',
    'photos_week_latest' => 'Latest',
    'photos_session_view_aria' => 'View session {label}',
    'photos_has_notes_aria' => 'Has coach notes',
    'photos_meta_kg' => 'kg',
    'photos_meta_waist_cm' => 'cm waist',

    // Comparison
    'photos_compare_aria' => 'Photo comparison',
    'photos_compare_before' => 'Before',
    'photos_compare_after' => 'After',
    'photos_compare_select_a_aria' => 'Before session',
    'photos_compare_select_b_aria' => 'After session',
    'photos_compare_swap_aria' => 'Swap dates',
    'photos_compare_swap' => 'Swap',
    'photos_compare_empty' => 'Pick two sessions to compare.',
    'photos_compare_label' => 'comparison',
    'photos_compare_no_photo' => 'No photo',

    // Feedback panel
    'photos_feedback_title' => 'Notes from your coach',
    'photos_feedback_delete_aria' => 'Delete photo',
    'photos_feedback_close_aria' => 'Close panel',
    'photos_feedback_coach_prefix' => 'Coach',
    'photos_feedback_coach_role' => 'Your 1:1 coach',
    'photos_feedback_time_relative' => '2 days ago',
    'photos_feedback_no_notes' => 'Your coach hasn\'t left notes on this photo yet.',
    'photos_feedback_reply_label' => 'Reply to coach',
    'photos_feedback_reply_placeholder' => 'Reply to your coach...',
    'photos_feedback_send' => 'Send',
    'photos_feedback_sending' => 'Sending...',
    'photos_feedback_coach_fallback' => 'Your coach',

    // Feedback badge
    'photos_badge_reviewed' => 'Reviewed',
    'photos_badge_notes' => 'Notes',
    'photos_badge_notes_count' => 'Notes · {n}',
    'photos_badge_pending' => 'Pending',

    // Achievement overlay
    'photos_success_title' => 'Session saved!',
    'photos_success_brand' => 'WELLCORE',
    'photos_success_angles' => 'angles logged',
    'photos_success_body' => 'Your progress is locked in. Consistency transforms!',
    'photos_success_dismiss' => 'DONE!',

    // Delete confirm
    'photos_delete_confirm_title' => 'Delete photo?',
    'photos_delete_confirm_body' => 'This can\'t be undone. The photo is deleted permanently.',
    'photos_delete_cancel' => 'Cancel',
    'photos_delete_confirm' => 'Delete',
    'photos_delete_failed' => 'We couldn\'t delete the photo. Try again.',

    // Error state
    'photos_error_title' => 'Error loading',
    'photos_error_retry' => 'Retry',

    // DateField (helper)
    'photos_datefield_label_default' => 'Date',
];
