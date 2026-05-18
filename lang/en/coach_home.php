<?php

return [
    // ─── Dashboard ────────────────────────────────────────────────────────
    // Loading / error / retry
    'error_loading' => 'Could not load the dashboard. Try again.',
    'retry'         => 'Retry',

    // Hero alert card
    'hero_eyebrow_attention' => 'Needs attention',
    'hero_eyebrow_attention_desktop' => 'Needs attention today',
    'hero_eyebrow_on_track'  => 'All caught up',
    'hero_clients_needing_attention' => '{n} CLIENT NEEDS ATTENTION|{n} CLIENTS NEED ATTENTION',
    'hero_title_all_clear'  => 'ALL CAUGHT UP · NOTHING PENDING',
    'hero_chip_pending_checkins' => '{n} check-ins',
    'hero_chip_pending_checkins_desktop' => '{n} pending check-ins',
    'hero_chip_unread'      => '{n} unread',
    'hero_cta_review'       => 'Open urgent',
    'hero_cta_checkins'     => 'Review check-ins',

    // Quick actions (grouped action list)
    'qa_checkins'  => 'Check-ins',
    'qa_messages'  => 'Messages',
    'qa_tickets'   => 'Tickets',
    'qa_analytics' => 'Analytics',

    // KPI tiles
    'kpi_active_clients'    => 'Active clients',
    'kpi_pending_checkins'  => 'Check-ins',
    'kpi_unread_messages'   => 'Messages',
    'kpi_open_tickets'      => 'Tickets',
    'kpi_open_tickets_desktop' => 'Open tickets',

    // Attention section
    'attention_title'        => 'Needs attention',
    'attention_count'        => '{n} client|{n} clients',
    'attention_sub_unanswered' => 'Unanswered: {value}',
    'attention_pending_placeholder' => 'pending',
    'attention_eta_days'     => '{n}d',
    'attention_empty_title'  => 'All check-ins answered',
    'attention_empty_sub'    => 'Nice work',
    'urgent_card_cta'        => 'Respond',

    // Today activity
    'activity_title'         => 'Today',
    'activity_event_checkin' => '{name} submitted their weekly check-in',
    'activity_event_checkin_short' => '{name} submitted a check-in',
    'activity_event_training' => '{name} logged a workout',
    'activity_event_message' => 'New message from {name}',
    'activity_empty_title'   => 'No recent activity',
    'activity_empty_sub'     => 'Nothing in the last 24 hours',

    // Weekly analysis
    'weekly_title'           => 'Weekly analysis',
    'weekly_checkins_label'  => 'Check-ins answered · this week',
    'weekly_aria'            => 'Check-ins per day',
    'weekly_dow_short'       => ['M', 'T', 'W', 'T', 'F', 'S', 'S'],
    'weekly_dow_long'        => ['Monday', 'Tuesday', 'Wed.', 'Thursday', 'Friday', 'Saturday', 'Sunday'],

    // Messages & PRs
    'messages_title'         => 'Messages · Recent PRs',
    'messages_see_all'       => 'See all →',
    'messages_empty_title'   => 'No recent messages',
    'messages_empty_sub'     => "When a client replies, you'll see it here",

    // Tickets section
    'tickets_title'          => 'Tickets',
    'tickets_see_all'        => 'See all →',
    'tickets_empty_title'    => 'No open tickets',
    'tickets_empty_sub'      => 'Everything is resolved',

    // ─── Client List (ClientList.vue) ─────────────────────────────────────
    'list_context'           => 'MY CLIENTS',
    'list_title'             => 'CLIENTS',
    'list_subtitle'          => '{n} active client|{n} active clients',
    'list_view_kanban'       => 'Kanban view',
    'list_search_placeholder' => 'Search by name...',
    'list_filter_all'        => 'All',
    'list_filter_active'     => 'Active',
    'list_filter_risk'       => 'At risk',
    'list_filter_inactive'   => 'Inactive',
    'list_no_plan'           => 'No plan',
    'list_pending_count'     => '{n} pending|{n} pending',
    'list_risk_badge'        => 'At risk',
    'list_last_checkin'      => 'Check-in: {value}',
    'list_last_message'      => 'Message: {value}',
    'list_never'             => 'Never',
    'list_no_messages'       => 'No messages',
    'list_level_short'       => 'Lv. {n}',
    'list_detail_xp_total'   => 'Total XP',
    'list_detail_streak'     => 'Streak',
    'list_detail_streak_days' => '{n} days',
    'list_detail_start_date' => 'Start date',
    'list_detail_last_checkin' => 'Last check-in',
    'list_na'                => 'N/A',
    'list_action_checkins'   => 'View check-ins',
    'list_action_message'    => 'Send message',
    'list_action_view_as'    => 'View as client',
    'list_restricted_title'  => 'Restricted actions',
    'list_restricted_sub'    => 'Pending team approval.',
    'list_request_deactivate' => 'Request deactivation',
    'list_request_delete'    => 'Request deletion',
    'list_request_edit'      => 'Request edit',
    'list_no_requests'       => 'No prior requests.',
    'list_request_cancel'    => 'Cancel',
    'list_admin_note'        => 'Admin: {note}',
    'list_empty_title'       => 'No clients found',
    'list_empty_sub_search'  => 'No results for "{query}"',
    'list_empty_sub_default' => 'No clients assigned yet',

    // Request action labels (titles + short)
    'req_action_delete_title'     => 'Request deletion',
    'req_action_deactivate_title' => 'Request deactivation',
    'req_action_edit_title'       => 'Request edit',
    'req_action_delete_short'     => 'Delete',
    'req_action_deactivate_short' => 'Deactivate',
    'req_action_edit_short'       => 'Edit',

    // Request status labels
    'req_status_pending'   => 'Pending',
    'req_status_approved'  => 'Approved',
    'req_status_rejected'  => 'Rejected',
    'req_status_cancelled' => 'Cancelled',

    // Request modal
    'req_modal_default_title' => 'Request',
    'req_modal_client'      => 'Client:',
    'req_modal_reason'      => 'Reason',
    'req_modal_reason_placeholder' => 'Tell us why you need this (10 characters minimum)...',
    'req_modal_chars_min'   => '{n} / 10 min',
    'req_modal_min_chars_err' => 'Reason must be at least 10 characters.',
    'req_modal_invalid'     => 'Invalid data.',
    'req_modal_generic_err' => 'Could not submit the request.',
    'req_modal_cancel'      => 'Cancel',
    'req_modal_submit'      => 'Submit request',
    'req_modal_sending'     => 'Sending...',
    'req_confirm_cancel'    => 'Cancel this request?',
    'req_cancelled_toast'   => 'Request cancelled.',
    'req_cancel_failed'     => 'Could not cancel.',
    'req_sent_toast'        => 'Request sent to the WellCore team.',

    // Impersonation
    'impersonate_fail'      => 'Could not sign in as this client.',
    'impersonate_client_default' => 'Client',

    // ─── Client Kanban (ClientKanban.vue) ─────────────────────────────────
    'kanban_context'        => 'MY CLIENTS',
    'kanban_title'          => 'PIPELINE',
    'kanban_subtitle'       => '{n} client · Activity view|{n} clients · Activity view',
    'kanban_search_placeholder' => 'Search client...',
    'kanban_view_list'      => 'List view',
    'kanban_refresh'        => 'Refresh',
    'kanban_col_new'        => 'New',
    'kanban_col_active'     => 'Active',
    'kanban_col_risk'       => 'At Risk',
    'kanban_col_inactive'   => 'Inactive',
    'kanban_card_pending_checkin' => '{n} check-in|{n} check-ins',
    'kanban_card_view_detail' => 'View detail',
    'kanban_tooltip_activity' => 'Last activity',
    'kanban_tooltip_checkin' => 'Last check-in',
    'kanban_tooltip_training' => 'Last workout',
    'kanban_activity_today' => 'Today',
    'kanban_activity_days'  => '{n}d',
    'kanban_empty_col_title' => 'No clients',
    'kanban_empty_col_sub'  => 'Drag cards here',
    'kanban_drag_hint'      => 'Drag cards between columns to reclassify clients',

    // Detail modal (inside ClientKanban)
    'detail_load_fail'      => 'Could not load client detail.',
    'detail_close'          => 'Close',
    'detail_xp_level'       => 'XP Level',
    'detail_xp_total'       => 'Total XP',
    'detail_streak'         => 'Streak (days)',
    'detail_start_date'     => 'Start date',
    'detail_last_checkin'   => 'Last check-in',
    'detail_wellbeing'      => 'Wellness',
    'detail_active_plan'    => 'Active plan',
    'detail_recent_notes'   => 'Recent notes',
    'detail_action_checkins' => 'Check-ins',
    'detail_action_messages' => 'Messages',
    'detail_action_notes'   => 'Notes',
    'detail_impersonate'    => 'Open client portal',
    'detail_impersonate_loading' => 'Entering…',
];
