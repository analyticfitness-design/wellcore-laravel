<?php

return [

    // ─── Shared / context label ──────────────────────────────────────────
    'context_main' => 'MAIN',

    // ─────────────────────────────────────────────────────────────────────
    // CheckinReview.vue — client check-in review
    // ─────────────────────────────────────────────────────────────────────
    'checkins_title' => 'CHECK-INS',
    'checkins_subtitle_pending_one' => '1 pending reply',
    'checkins_subtitle_pending_other' => '{count} pending replies',
    'checkins_subtitle_all_caught_up' => 'All caught up',

    'checkins_filter_show_all' => 'Showing all',
    'checkins_filter_pending_only' => 'Pending only',

    'checkins_load_error' => 'We couldn’t load the check-ins.',
    'checkins_reply_error' => 'We couldn’t send the reply. Try again.',

    'checkins_badge_pending' => 'Pending',
    'checkins_badge_replied' => 'Replied',

    'checkins_metric_wellbeing' => 'Wellbeing',
    'checkins_metric_days_trained' => 'Days trained',
    'checkins_metric_days_trained_of' => 'of 7 days',
    'checkins_metric_nutrition' => 'Nutrition',
    'checkins_metric_rpe' => 'RPE',
    'checkins_metric_rpe_label' => 'perceived effort',

    'checkins_client_comment_label' => 'Client comment',
    'checkins_your_reply_label' => 'Your reply',
    'checkins_replied_at_prefix' => '— {when}',
    'checkins_replied_at_now' => 'Just now',

    'checkins_reply_form_label' => 'Your reply',
    'checkins_reply_placeholder' => 'Write your reply to the check-in…',
    'checkins_reply_send' => 'Send reply',
    'checkins_reply_cancel' => 'Cancel',
    'checkins_reply_cta' => 'Reply',

    'checkins_empty_pending_title' => 'All check-ins replied',
    'checkins_empty_pending_subtitle' => 'Great work — your clients are all caught up.',
    'checkins_empty_all_title' => 'No check-ins recorded',
    'checkins_empty_all_subtitle' => 'Your clients haven’t submitted any check-ins yet.',

    // ─────────────────────────────────────────────────────────────────────
    // MessageCenter.vue — client messaging
    // ─────────────────────────────────────────────────────────────────────
    'messages_title' => 'MESSAGES',
    'messages_subtitle' => 'Conversations with your clients',

    'messages_panel_clients_title' => 'Clients',
    'messages_panel_clients_error' => 'Couldn’t load clients.',
    'messages_panel_retry' => 'Retry',

    'messages_no_messages_preview' => 'No messages',
    'messages_empty_clients_title' => 'No clients assigned',
    'messages_empty_clients_subtitle' => 'When a client gets assigned to you, they’ll show up here.',

    'messages_client_no_plan' => 'No plan',
    'messages_live_indicator' => 'Live',

    'messages_load_error' => 'We couldn’t load the messages.',

    'messages_empty_thread_title' => 'Start the conversation',
    'messages_empty_thread_subtitle' => 'Send the first message to get the chat going.',

    'messages_send_error' => 'We couldn’t send the message.',

    'messages_templates_title' => 'Templates',
    'messages_templates_tooltip' => 'Quick reply templates',
    'messages_templates_search_placeholder' => 'Search templates…',
    'messages_templates_no_results' => 'No templates found for "{query}"',
    'messages_templates_footer_one' => '1 template available',
    'messages_templates_footer_other' => '{count} templates available',

    'messages_input_placeholder' => 'Type a message…',

    'messages_sent_now' => 'Just now',

    'messages_no_client_selected_title' => 'Pick a client',
    'messages_no_client_selected_subtitle' => 'Choose a client from the left panel to view the conversation.',

    // Quick-reply templates
    'tpl_welcome_title' => 'Welcome',
    'tpl_welcome_body' => 'Welcome to WellCore! I’m here to guide you through your transformation. Take a look at your training plan and feel free to message me with any questions.',
    'tpl_checkin_reminder_title' => 'Check-in reminder',
    'tpl_checkin_reminder_body' => 'Remember to complete your weekly check-in so I can review your progress and adjust the plan if needed. Your feedback is key to your results.',
    'tpl_congrats_title' => 'General shout-out',
    'tpl_congrats_body' => 'Just wanted to say great job on your commitment and consistency. The results are showing and I want you to know your effort is paying off. Keep it up!',
    'tpl_routine_reminder_title' => 'Routine reminder',
    'tpl_routine_reminder_body' => 'Your new routine is live in the dashboard. Take a look at the exercises and let me know if you have any questions before starting.',
    'tpl_weekly_followup_title' => 'Weekly follow-up',
    'tpl_weekly_followup_body' => 'How’s training going this week? Just doing a quick check-in. Let me know how you’ve been feeling and whether you’ve had any issues with the exercises.',
    'tpl_availability_title' => 'Availability',
    'tpl_availability_body' => 'I’m here for any questions you have. You can message me here or open a ticket if you need something specific. We’ve got you.',

    // ─────────────────────────────────────────────────────────────────────
    // FoodPhotoReview.vue — food photo review
    // ─────────────────────────────────────────────────────────────────────
    'food_title' => 'FOOD PHOTOS',
    'food_pending_count_one' => '1 photo pending review',
    'food_pending_count_other' => '{count} photos pending review',

    'food_filter_all_clients' => 'All clients',
    'food_filter_view_pending' => 'View pending',
    'food_filter_view_reviewed' => 'View reviewed',

    'food_load_error' => 'We couldn’t load the photos.',
    'food_react_error' => 'We couldn’t save the reaction. Try again.',
    'food_save_note_error' => 'We couldn’t save the note.',

    'food_empty_pending_title' => 'No photos pending',
    'food_empty_pending_subtitle' => 'Nice work! You’re all caught up reviewing your clients’ photos.',
    'food_empty_reviewed_title' => 'No photos reviewed yet',
    'food_empty_reviewed_subtitle' => 'When you approve or suggest improvements, photos will move to history.',

    'food_photo_alt' => 'Photo of {meal}',
    'food_client_description' => 'Client description',

    'food_reaction_good' => 'Good',
    'food_reaction_improve' => 'Needs work',
    'food_reaction_seen' => 'Seen without reaction',
    'food_btn_good' => 'Good',
    'food_btn_improve' => 'Improve',

    'food_note_placeholder' => 'Optional note for client',
    'food_note_saving' => 'Saving…',
    'food_showing_latest' => 'Showing the 40 most recent',
    'food_retry' => 'Retry',

    // ─────────────────────────────────────────────────────────────────────
    // Community.vue — Coach Community (5 tabs)
    // ─────────────────────────────────────────────────────────────────────
    'community_title' => 'Community',
    'community_subtitle' => 'Your clients’ community. Moderate, motivate, connect.',
    'community_message_team' => 'Message the team',

    'community_tab_pulse' => 'Team Pulse',
    'community_tab_posts' => 'Posts',
    'community_tab_threads' => 'Threads',
    'community_tab_stories' => 'Stories',
    'community_tab_wins' => 'Wins',

    'community_quick_msg_template' => 'Hey {name}, I noticed you’ve been inactive for {days} days. How can I help?',
    'community_quick_msg_days_default' => 'a few',

    // Team Pulse tab
    'pulse_retry' => 'Retry',
    'pulse_empty_title' => 'Your team has no activity yet',
    'pulse_empty_subtitle' => 'Once a client hits a PR or completes a check-in, this view will fill up with insights.',
    'pulse_team_ring_label' => 'Team Pulse',
    'pulse_computed_at' => 'Computed at {time} · refreshes every 60s',
    'pulse_top_performers_title' => 'Top performers (7D)',
    'pulse_no_top_performers' => 'No top performers yet this week.',
    'pulse_at_risk_title' => 'Churn risk (5+ days inactive)',
    'pulse_refresh_now' => 'Refresh now',

    // Posts tab
    'posts_filter_all' => 'All',
    'posts_filter_pinned' => 'Pinned',
    'posts_filter_reported' => 'Reported',
    'posts_filter_achievements' => 'Achievements',
    'posts_filter_prs' => 'PRs',

    'posts_new_post_one' => '↑ 1 new post',
    'posts_new_post_other' => '↑ {count} new posts',

    'posts_retry' => 'Retry',
    'posts_empty_title' => 'Your team isn’t posting yet',
    'posts_empty_subtitle' => 'When a client shares a PR, photo, or thought, it’ll show up here.',
    'posts_empty_cta' => 'Message the team',

    'posts_author_fallback' => 'Client',
    'posts_reactions_count_one' => '1 reaction',
    'posts_reactions_count_other' => '{count} reactions',
    'posts_comments_count_one' => '1 comment',
    'posts_comments_count_other' => '{count} comments',
    'posts_report_count_one' => '⚠️ 1 report',
    'posts_report_count_other' => '⚠️ {count} reports',

    'posts_loading_more' => 'Loading more…',

    // Threads tab
    'threads_filter_all' => 'All',
    'threads_filter_unanswered' => 'No coach reply',
    'threads_filter_large' => '+50 comments',
    'threads_filter_conflicted' => 'Conflicts',

    'threads_empty_title' => 'No recent threads',
    'threads_empty_subtitle' => 'Encourage them to interact with a message to the team.',

    'threads_comments_count_one' => '1 comment',
    'threads_comments_count_other' => '{count} comments',
    'threads_participants_count_one' => '1 participant',
    'threads_participants_count_other' => '{count} participants',

    'threads_time_ago_minutes' => '{value}m ago',
    'threads_time_ago_hours' => '{value}h ago',
    'threads_time_ago_days' => '{value}d ago',

    'threads_status_replied' => 'You replied',
    'threads_status_unanswered' => '⚠️ No reply',
    'threads_status_conflicted' => 'Heads up',

    // Stories tab
    'stories_empty_title' => 'No active stories',
    'stories_empty_subtitle' => 'Stories last 24–48h. When a client uploads one, it’ll show up here in priority order.',
    'stories_client_fallback' => 'Client',

    // Wins tab
    'wins_period_week' => 'This week',
    'wins_period_month' => 'This month',
    'wins_period_all' => 'All time',

    'wins_streak_banner_one' => 'Team on a roll — 1 PR and {achievements} achievements this period',
    'wins_streak_banner_other' => 'Team on a roll — {prs} PRs and {achievements} achievements this period',

    'wins_empty_title' => 'No wins yet',
    'wins_empty_subtitle' => 'Be proactive: motivate the client closest to a PR.',

    'wins_pr_label' => 'PR on {exercise}: {weight}kg',
    'wins_congratulate' => 'Send props',
    'wins_congrats_sent' => 'Props sent to {name}.',
    'wins_congrats_error' => 'We couldn’t send the props.',
];
