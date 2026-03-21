<?php

return [

    // -------------------------------------------------------------------------
    // Hero
    // -------------------------------------------------------------------------
    'hero' => [
        'label'       => 'Your roadmap to results',
        'title_line1' => 'HOW IT',
        'title_line2' => 'WORKS',
        'description' => 'From your initial assessment to measurable outcomes: 4 phases, 12 weeks, 1 goal. No generic templates. No cookie-cutter programs. A protocol built on your real data.',
    ],

    // -------------------------------------------------------------------------
    // Stats Bar
    // -------------------------------------------------------------------------
    'stats' => [
        'phases'            => 'Defined phases',
        'weeks'             => 'Program weeks',
        'coach'             => 'With your coach',
        'generic_templates' => 'Generic templates',
    ],

    // -------------------------------------------------------------------------
    // Phase Navigation Pills
    // -------------------------------------------------------------------------
    'nav' => [
        'f01_name'  => 'Assessment',
        'f01_range' => 'Weeks 1-2',
        'f02_name'  => 'Design',
        'f02_range' => '48-72h',
        'f03_name'  => 'Execution',
        'f03_range' => '9 weeks',
        'f04_name'  => 'Results',
        'f04_range' => 'Week 12+',
    ],

    // -------------------------------------------------------------------------
    // PHASE 01 — Assessment
    // -------------------------------------------------------------------------
    'fase01' => [
        'label'    => 'PHASE 01',
        'range'    => 'Weeks 1-2',
        'title'    => 'ASSESSMENT',
        'subtitle' => 'Comprehensive baseline evaluation',

        'description' => 'This isn\'t a welcome form. It\'s the foundation of everything. Without real data about your body, your history, and your habits, any program is just a guess. Here we collect every data point needed to build something that actually works for you.',

        'badge_delivery'   => 'delivery',
        'badge_interview'  => 'interview',

        'checklist_title' => 'What we evaluate',
        'checklist' => [
            'In-depth diagnostic form — 40+ variables covering body, habits, and lifestyle',
            'Body composition analysis — weight, body fat percentage, estimated lean mass',
            'Full training history review — what you\'ve done, what worked, and what didn\'t',
            '1:1 consultation with your assigned coach — 30-minute deep-dive analysis',
            'Baseline nutritional assessment — current habits, food preferences, and restrictions',
            'Documentation of injuries, physical limitations, or relevant medical conditions',
        ],

        'sidebar_title'    => 'Your Personalized Assessment Report',
        'sidebar_subtitle' => 'Available in your dashboard — 48h after completing the form',
        'sidebar_items' => [
            'Complete assessment report analyzing your real starting point',
            'Identification of your primary areas for improvement',
            'Initial prep protocol (foundational habits for the first 48h)',
            'Full access to your client dashboard with all data loaded',
            'Confirmation of selected plan and coaching cadence',
        ],
        'sidebar_footnote' => 'Without real data there is no real program. Most online programs fail because they assume everyone is the same. At WellCore, the assessment is the most critical step.',
    ],

    // -------------------------------------------------------------------------
    // PHASE 02 — Design
    // -------------------------------------------------------------------------
    'fase02' => [
        'label'    => 'PHASE 02',
        'range'    => '48-72h',
        'title'    => 'PROGRAM DESIGN',
        'subtitle' => 'Custom-built on your data',

        'description' => 'With your assessment data in hand, your coach designs your complete program. This isn\'t a modified template — it\'s built from scratch based on your metrics, availability, and specific goals. The program is delivered to your dashboard before execution begins.',

        'badge_pdf'    => 'interactive',
        'badge_custom' => 'personalized',

        'checklist_title' => 'Program components',
        'checklist' => [
            'Weekly training program — days, exercises, sets, reps, rest periods',
            'Nutrition protocol (Method & Elite plans) — calories, macros, and meal timing',
            'Behavioral habits protocol (Elite plan) — sleep routines, hydration, stress management',
            'Structured weekly calendar — exactly what to do each day',
            'Progression roadmap — how to advance week by week',
        ],

        'sidebar_title'    => 'Based on your plan',
        'sidebar_subtitle' => 'Interactive program in your dashboard — 48-72h after assessment',
        'plan_inicial_name' => 'Essential Plan',
        'plan_inicial_desc' => 'Custom training program + basic nutrition guide',
        'plan_base_name'    => 'Method Plan',
        'plan_base_desc'    => 'Full program + precision nutrition protocol + weekly check-ins',
        'plan_elite_name'   => 'Elite Plan',
        'plan_elite_desc'   => 'All of the above + behavioral protocol + bi-weekly 1:1 coaching sessions',
        'sidebar_footnote'  => 'Your coach walks through the program with you in a kickoff call before you start. If anything doesn\'t fit your situation, it\'s adjusted before execution begins.',
    ],

    // -------------------------------------------------------------------------
    // PHASE 03 — Execution
    // -------------------------------------------------------------------------
    'fase03' => [
        'label'    => 'PHASE 03',
        'range'    => '9 weeks',
        'title'    => 'EXECUTION & TRACKING',
        'subtitle' => 'Where the results happen',

        'description' => 'This is where everything comes together. Nine weeks of active execution, real-time tracking, and data-driven adjustments. Your coach doesn\'t disappear after handing you a PDF. They\'re present every week — analyzing your progress and course-correcting before problems compound.',

        'badge_tracking' => 'tracking',
        'badge_wa'       => 'with your coach',

        'weekly_cycle_title' => 'Weekly optimization cycle',
        'cycle_step1' => 'Training week',
        'cycle_step2' => 'Progress check-in',
        'cycle_step3' => 'Coach analysis',
        'cycle_step4' => 'Program adjustment',

        'includes_title' => 'What\'s included',
        'includes' => [
            'Weekly coaching via direct WhatsApp with your assigned coach',
            'Training program adjustments based on your week-by-week response data',
            'Bi-weekly 1:1 video coaching sessions (Elite plan)',
            'Client dashboard with full metric tracking: weight, measurements, performance, photos',
            'Guaranteed response time — 24h maximum on business days',
        ],

        'how_it_works_title' => 'How the coaching loop works',
        'how_it_works' => [
            'Every Sunday your coach sends a check-in with targeted questions about the week',
            'You respond with your data: workouts completed, nutrition adherence, sleep, energy levels',
            'Your coach analyzes the data and optimizes the program for the following week',
            'Monday morning your updated program is ready in the dashboard before you train',
            'Performance metrics update in your client dashboard in real time',
        ],

        'highlight' => 'The difference between a program that works and one that doesn\'t is accountability. Without adjustments, your body adapts and results plateau. The WellCore model is designed to prevent that.',
    ],

    // -------------------------------------------------------------------------
    // PHASE 04 — Results
    // -------------------------------------------------------------------------
    'fase04' => [
        'label'    => 'PHASE 04',
        'range'    => 'Week 12+',
        'title'    => 'RESULTS & PROJECTION',
        'subtitle' => 'The finish line — and the new starting line',

        'description' => 'Week 12 isn\'t just the end — it\'s where we measure everything and project the next cycle. We compare your starting point vs. current state with hard data, not feelings. If you decide to continue (and most do), we start a new cycle with everything we\'ve learned.',

        'stat1_label' => 'Avg. fat loss',
        'stat2_label' => 'Body fat % reduction',
        'stat3_label' => 'Average adherence',
        'stat4_label' => 'Renew for a second cycle',

        'eval_title' => 'Week 12: Final evaluation',
        'eval' => [
            'Full reassessment — the same day-1 metrics measured again for direct comparison',
            'Visual and data-driven before vs. after comparison in your dashboard',
            'Performance report — what worked, what was adjusted, and why',
            '1:1 closing session with your coach — complete cycle analysis',
        ],

        'next_title' => 'What comes next',
        'next' => [
            'Option to renew with a new 12-week cycle based on your updated data',
            'Plan upgrade available if your level or goals have evolved',
            'Loyalty pricing for clients who renew within 30 days',
            'Your complete data history stays in the dashboard — the next cycle starts with an advantage',
        ],
    ],

    // -------------------------------------------------------------------------
    // FAQ
    // -------------------------------------------------------------------------
    'faq' => [
        'title'    => 'GOT QUESTIONS?',
        'subtitle' => 'The most common questions about the process.',

        'q1' => [
            'question' => 'How much time do I need to commit per week?',
            'answer'   => 'The program is designed around your real availability. During the assessment you tell us how many hours you have per week and we build around that. The practical minimum is 3 sessions of 45-60 minutes per week. More availability? We\'ll use it. Less? We optimize what\'s there.',
        ],
        'q2' => [
            'question' => 'Is coaching really weekly or just at the end?',
            'answer'   => 'It\'s weekly. Every Sunday your coach sends a check-in with targeted questions about your week. You respond with your data and Monday morning your optimized program is ready. This isn\'t passive check-ins — it\'s active, two-way coaching every single week of the 12.',
        ],
        'q3' => [
            'question' => 'What happens if I have an off week?',
            'answer'   => 'Life happens. Travel, sickness, insane work schedules. What sets WellCore apart is that your coach knows because you flagged it in the check-in, and the program adapts accordingly. It\'s not rigid — it flexes with your reality without losing sight of the end goal.',
        ],
        'q4' => [
            'question' => 'Can I switch plans during the program?',
            'answer'   => 'Yes. Plan changes apply at the start of the next cycle, not mid-program. If you realize you need more coaching than your current plan provides, you can upgrade before your new cycle starts. Your coach will recommend the best plan for your situation.',
        ],
    ],

    // -------------------------------------------------------------------------
    // Final CTA
    // -------------------------------------------------------------------------
    'cta' => [
        'label'         => 'Your next move',
        'title'         => 'START TODAY',
        'description'   => 'The WellCore process begins with your assessment. Within 48 hours you\'ll have the foundation of your personalized program ready. No long-term commitments, no contracts, no pressure.',
        'btn_primary'   => 'Start the Process',
        'btn_secondary' => 'See Pricing',
        'footnote'      => 'No credit card required · Cancel anytime',
    ],

];
