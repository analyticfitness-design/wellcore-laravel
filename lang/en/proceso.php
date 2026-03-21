<?php

return [

    // -------------------------------------------------------------------------
    // Hero
    // -------------------------------------------------------------------------
    'hero' => [
        'label'       => 'Your path to results',
        'title_line1' => 'THIS IS HOW',
        'title_line2' => 'THE PROCESS WORKS',
        'description' => 'From your initial assessment to your first results: 4 phases, 12 weeks, 1 goal. No generic guides. No copied programs. A process built on your real data.',
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
    // FASE 01 — Assessment
    // -------------------------------------------------------------------------
    'fase01' => [
        'label'    => 'PHASE 01',
        'range'    => 'Weeks 1-2',
        'title'    => 'ASSESSMENT',
        'subtitle' => 'Starting-point analysis',

        'description' => 'The assessment is not a welcome form. It is the foundation of everything. Without real data about your body, your history, and your habits, any program is just a guess. Here we collect all the information we need to design something that will actually work for you.',

        'badge_delivery'   => 'delivery',
        'badge_interview'  => 'interview',

        'checklist_title' => 'What we evaluate',
        'checklist' => [
            'In-depth diagnostic form — 40+ variables about your body, habits, and lifestyle',
            'Body composition analysis — weight, body fat percentage, estimated muscle mass',
            'Full training history evaluation — what you\'ve done, what worked and what didn\'t',
            '1:1 interview with your assigned coach — 30 minutes of in-depth analysis',
            'Initial nutritional analysis — current habits, preferences, and restrictions',
            'Record of injuries, physical limitations, or relevant medical conditions',
        ],

        'sidebar_title'    => 'Your Personalized Assessment Report',
        'sidebar_subtitle' => 'Available in your portal — 48h after completing the form',
        'sidebar_items' => [
            'Complete assessment report with analysis of your real starting point',
            'Identification of your main priority areas for improvement',
            'Initial preparation protocol (basic habits for the first 48h)',
            'Access to your client portal with your data loaded',
            'Confirmation of the selected plan and follow-up mode',
        ],
        'sidebar_footnote' => 'Without real data there is no real program. Most online programs fail because they assume everyone is the same. At WellCore, the assessment is the most important step.',
    ],

    // -------------------------------------------------------------------------
    // FASE 02 — Design
    // -------------------------------------------------------------------------
    'fase02' => [
        'label'    => 'PHASE 02',
        'range'    => '48-72h',
        'title'    => 'PROGRAM DESIGN',
        'subtitle' => 'Built on your data',

        'description' => 'With the assessment data, your coach designs your complete program. It is not a modified template. It is built from scratch based on your metrics, your availability, and your specific goal. The program is delivered to your portal before execution begins.',

        'badge_pdf'    => 'interactive',
        'badge_custom' => 'personalized',

        'checklist_title' => 'Program components',
        'checklist' => [
            'Weekly training program — days, exercises, sets, reps, rest periods',
            'Nutrition plan (Elite and Base plans) — calories, macros, and meal distribution',
            'Habits protocol (Elite plan) — sleep routines, hydration, and stress management',
            'Structured weekly calendar — what to do each day',
            'Progression guide — how to advance week by week',
        ],

        'sidebar_title'    => 'According to your plan',
        'sidebar_subtitle' => 'Interactive PDF in your portal — 48 to 72h after the assessment',
        'plan_inicial_name' => 'Starter Plan',
        'plan_inicial_desc' => 'Training program + basic nutrition guide',
        'plan_base_name'    => 'Base Plan',
        'plan_base_desc'    => 'Full program + detailed nutrition plan + weekly follow-up',
        'plan_elite_name'   => 'Elite Plan',
        'plan_elite_desc'   => 'All of the above + habits protocol + bi-weekly 1:1 check-ins',
        'sidebar_footnote'  => 'Your coach reviews the program with you in a short call before you start. If anything doesn\'t make sense for your situation, it is adjusted before execution.',
    ],

    // -------------------------------------------------------------------------
    // FASE 03 — Execution
    // -------------------------------------------------------------------------
    'fase03' => [
        'label'    => 'PHASE 03',
        'range'    => '9 weeks',
        'title'    => 'EXECUTION & FOLLOW-UP',
        'subtitle' => 'Where results happen',

        'description' => 'This is the phase where everything happens. Nine weeks of active execution, constant follow-up, and real-time adjustments. Your coach doesn\'t disappear after handing you the PDF. They are present every week to analyze your progress and course-correct before problems accumulate.',

        'badge_tracking' => 'follow-up',
        'badge_wa'       => 'with your coach',

        'weekly_cycle_title' => 'Weekly adjustment cycle',
        'cycle_step1' => 'Training week',
        'cycle_step2' => 'Progress check-in',
        'cycle_step3' => 'Coach analysis',
        'cycle_step4' => 'Program adjustment',

        'includes_title' => 'What is included',
        'includes' => [
            'Weekly follow-up via direct WhatsApp with your assigned coach',
            'Training program adjustments based on your week-by-week response',
            'Bi-weekly 1:1 video check-ins (Elite plan)',
            'Client portal with metric tracking: weight, measurements, performance, photos',
            'Guaranteed response time — maximum 24h on business days',
        ],

        'how_it_works_title' => 'How follow-up works',
        'how_it_works' => [
            'Every Sunday you receive a check-in from your coach with specific questions about the week',
            'You respond to the check-in with your data: workouts, nutrition, sleep, energy',
            'Your coach analyzes the data and adjusts the program for the following week',
            'On Monday your updated program is ready in the portal before you start',
            'Metrics are updated in your client dashboard in real time',
        ],

        'highlight' => 'The difference between a program that works and one that doesn\'t is follow-up. Without adjustments, your body adapts to the program and results stall. The WellCore model is designed to prevent that.',
    ],

    // -------------------------------------------------------------------------
    // FASE 04 — Results
    // -------------------------------------------------------------------------
    'fase04' => [
        'label'    => 'PHASE 04',
        'range'    => 'Week 12+',
        'title'    => 'RESULTS & PROJECTION',
        'subtitle' => 'The finishing line — and the starting line',

        'description' => 'Week 12 is not just the end — it\'s where we measure everything and project the next cycle. We compare starting point vs. current point with objective data, not impressions. If you decide to continue (and most do), we start a new cycle with everything learned.',

        'stat1_label' => 'Average fat loss',
        'stat2_label' => 'Body fat % reduction',
        'stat3_label' => 'Average adherence',
        'stat4_label' => 'Renew a second cycle',

        'eval_title' => 'Week 12: final evaluation',
        'eval' => [
            'Full final evaluation — the same day-1 metrics measured again',
            'Visual and numerical before vs. after comparison in your portal',
            'Performance report — what worked, what was adjusted and why',
            '1:1 closing call with your coach — full cycle analysis',
        ],

        'next_title' => 'What comes next',
        'next' => [
            'Option to renew with a new 12-week cycle based on your new data',
            'Plan change if your level or needs have evolved',
            'Loyalty discount for clients who renew within 30 days',
            'Your data history stays in the portal — the next cycle starts with an advantage',
        ],
    ],

    // -------------------------------------------------------------------------
    // FAQ
    // -------------------------------------------------------------------------
    'faq' => [
        'title'    => 'FREQUENTLY ASKED QUESTIONS',
        'subtitle' => 'The most common questions.',

        'q1' => [
            'question' => 'How much time do I need to commit per week?',
            'answer'   => 'The program is designed according to your real availability. During the assessment you declare how many hours you have available per week and the program is built around that. The practical minimum is 3 sessions of 45–60 minutes per week. If you have more availability, we use it. If you have less, we optimize what is there.',
        ],
        'q2' => [
            'question' => 'Is follow-up really weekly or only at the end?',
            'answer'   => 'It is weekly. Every Sunday your coach sends you a check-in with specific questions about your week. You respond with your data and on Monday you have the updated program for the following week if needed. It is not passive follow-up: it is an active, two-way communication every week of the 12.',
        ],
        'q3' => [
            'question' => 'What happens if I can\'t keep up one week?',
            'answer'   => 'Life happens. Travel, illness, intense work. What sets WellCore apart is that when that occurs, your coach knows because you communicated it in the check-in and adjusts the plan accordingly. The program is not rigid: it adapts to your reality week by week without losing sight of the goals.',
        ],
        'q4' => [
            'question' => 'Can I change plans during the process?',
            'answer'   => 'Yes. The plan change applies at the start of the next cycle, not mid-way through the active 12 weeks. If you decide you need more follow-up than you contracted, you can upgrade before your new cycle starts. Your coach will guide you on which plan makes the most sense for your situation.',
        ],
    ],

    // -------------------------------------------------------------------------
    // Final CTA
    // -------------------------------------------------------------------------
    'cta' => [
        'label'         => 'Next step',
        'title'         => 'START TODAY',
        'description'   => 'The WellCore process starts with your assessment. In less than 48 hours you will have the foundation of your personalized program ready. No long-term commitments, no contracts.',
        'btn_primary'   => 'Start the process',
        'btn_secondary' => 'See plans & pricing',
        'footnote'      => 'No credit card required · Cancel anytime',
    ],

];
