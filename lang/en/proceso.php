<?php

/*
 |---------------------------------------------------------------------
 | /proceso · long-form storytelling v2 — 5 steps with mockup viz.
 |
 | Reorganized Sprint 2 (porting v2). Step2 NEVER mentions AI/algorithm/ML.
 | Use "matching system" or "compatibility scoring" instead.
 | Viz data: ALL demo — disclaimer required under each one.
 |---------------------------------------------------------------------
 */

return [

    'meta_title'       => 'The Process · 5 steps for your transformation | WellCore Fitness',
    'meta_description' => 'The WellCore path in 5 steps: assessment, coach match, personalized plan, check-ins, and verifiable results. No shortcuts. No empty promises.',

    'sidebar' => [
        'subtitle'       => 'The Process · 2026',
        'progress_label' => 'Progress',
        'cta'            => 'GET STARTED',
    ],

    'chapters' => [
        'cap00' => [
            'pill'       => 'Ch 00 · Cover',
            'nav_title'  => 'Cover',
        ],
        's1' => [
            'pill'       => 'Step 01 · Assessment',
            'nav_title'  => 'Assessment',
            'short'      => '01',
            'label'      => 'ASSESSMENT',
        ],
        's2' => [
            'pill'       => 'Step 02 · The Match',
            'nav_title'  => 'The Match',
            'short'      => '02',
            'label'      => 'THE MATCH',
        ],
        's3' => [
            'pill'       => 'Step 03 · Your Plan',
            'nav_title'  => 'Your Plan',
            'short'      => '03',
            'label'      => 'YOUR PLAN',
        ],
        's4' => [
            'pill'       => 'Step 04 · Check-ins',
            'nav_title'  => 'Check-ins',
            'short'      => '04',
            'label'      => 'CHECK-INS',
        ],
        's5' => [
            'pill'       => 'Step 05 · Results',
            'nav_title'  => 'Results',
            'short'      => '05',
            'label'      => 'RESULTS',
        ],
        'cta' => [
            'pill'       => 'Start the process',
            'nav_title'  => 'Get Started',
        ],
    ],

    'hero' => [
        'eyebrow'      => 'WELLCORE FITNESS · THE PROCESS',
        'title_html'   => 'THE<br><span class="accent">PATH.</span>',
        'sub'          => '5 steps. No shortcuts. No empty promises.',
        'scroll_hint'  => 'Scroll to walk through the process',
    ],

    'manifesto' => [
        'kicker' => 'STARTING POINT',
        'body'   => 'We don\'t sell motivation. We sell a process. Five steps that turn your real data into a plan your body can execute, with your coach adjusting it every two weeks. No magic. Just method.',
    ],

    'step1' => [
        'meta_index'   => 'STEP 01',
        'meta_timing'  => '5 MIN',
        'title_html'   => 'INITIAL<br>ASSESS-<br>MENT',
        'desc'         => 'Everything begins with five minutes of your time. A short form —no tricks— that tells us where you are and where you want to go.',
        'detail'       => 'No payment until you confirm coach match',
        'viz' => [
            'header_label' => 'Initial assessment',
            'duration'     => '5 min',
            'question'     => 'What is your main goal?',
            'opts' => [
                'Fat loss',
                'Muscle hypertrophy',
                'Athletic performance',
            ],
            'slider_q'      => 'How many days a week are you available?',
            'slider_labels' => [
                '1 day', '3 days', '6 days',
            ],
            'submit'        => 'BEGIN →',
        ],
        'disclaimer' => 'Sample view · demo data',
    ],

    'step2' => [
        'meta_index'   => 'STEP 02',
        'meta_timing'  => '24–48 H',
        'title_html'   => 'HUMAN<br>COACH<br>1:1',
        'desc'         => 'Our team reviews your sign-up and assigns a coach who specializes in your goal. The assignment is manual: we prioritize specialty, gender, and time zone. It\'s not an opaque algorithm — it\'s a human decision.',
        'detail'       => 'Manual assignment · Coach expert in your goal',
        'best_label'   => 'YOUR ASSIGNED COACH',
        'coaches' => [
            [
                'initials' => 'LC',
                'name'     => 'SENIOR COACH',
                'spec'     => 'HYPERTROPHY',
                'match'    => null,
                'best'     => false,
            ],
            [
                'initials' => 'MA',
                'name'     => 'YOUR COACH',
                'spec'     => 'FAT LOSS',
                'match'    => null,
                'best'     => true,
            ],
            [
                'initials' => 'SO',
                'name'     => 'SENIOR COACH',
                'spec'     => 'PERFORMANCE',
                'match'    => null,
                'best'     => false,
            ],
        ],
        'disclaimer' => 'Sample view · representative of our team\'s profiles',
    ],

    'step3' => [
        'meta_index'   => 'STEP 03',
        'meta_timing'  => '3–5 DAYS',
        'title_html'   => 'PLAN<br>IN YOUR<br>DASHBOARD.',
        'desc'         => 'Your coach designs training, nutrition, and habits based on your sign-up and the initial conversation. We load it directly to your client dashboard — no loose PDFs. Every variable is tuned to your data.',
        'detail'       => 'Training · Nutrition · Habits',
        'viz' => [
            'pdf_filename'      => 'YOUR DASHBOARD · WEEK 1',
            'pdf_meta'          => 'UPDATED BY YOUR COACH',
            'pdf_download'      => 'OPEN DASHBOARD',
            'th_day'            => 'DAY',
            'th_session'        => 'SESSION',
            'th_vol'            => 'VOL',
            'th_kcal'           => 'KCAL',
            'th_type'           => 'TYPE',
            'rows' => [
                ['day' => 'MON', 'session' => 'Upper A',     'vol' => '18', 'kcal' => '2,340', 'type' => 'STRENGTH', 'type_color' => 'red',   'focus' => true],
                ['day' => 'TUE', 'session' => 'Cardio LISS', 'vol' => '0',  'kcal' => '2,100', 'type' => 'CARDIO',   'type_color' => 'green', 'focus' => false],
                ['day' => 'WED', 'session' => 'Lower A',     'vol' => '20', 'kcal' => '2,340', 'type' => 'STRENGTH', 'type_color' => 'red',   'focus' => true],
                ['day' => 'THU', 'session' => 'Rest',        'vol' => '—',  'kcal' => '1,900', 'type' => 'REST',     'type_color' => 'green', 'focus' => false],
                ['day' => 'FRI', 'session' => 'Upper B',     'vol' => '16', 'kcal' => '2,340', 'type' => 'STRENGTH', 'type_color' => 'red',   'focus' => true],
            ],
        ],
        'disclaimer' => 'Sample view · demo data · your real plan lives in the client dashboard',
    ],

    'pullquote' => [
        'label'     => 'BREAK POINT · MID-JOURNEY',
        'text_html' => 'NO MORE<br>GENERIC <em>APPS.</em><br>THIS IS YOURS.',
        'cite'      => 'WellCore · The Process',
    ],

    'step4' => [
        'meta_index'   => 'STEP 04',
        'meta_timing'  => 'PER YOUR PLAN',
        'title_html'   => 'DATA.<br>ADJUST.<br>ADVANCE.',
        'desc'         => 'You log your check-in in the dashboard: well-being, days trained, nutrition, comments. Your coach reviews and replies with adjustments — through the dashboard and WhatsApp. Frequency depends on your plan: monthly (Esencial), bi-weekly (Método), weekly (Elite).',
        'detail'       => 'Human coach · No bots · No auto-replies',
        'viz' => [
            'coach_avatar'   => 'M',
            'coach_name'     => 'Coach Marcos',
            'coach_status'   => 'Online',
            'msgs' => [
                ['role' => 'coach', 'text' => 'How did your week go? Did you make all 3 sessions?'],
                ['role' => 'user',  'text' => 'Yes, hit all 3. Wednesday was tough but I did it.'],
                ['role' => 'coach', 'text' => 'Well done. I saw you dropped 0.8 kg. We stay the course — your body is responding.'],
            ],
            'msg_ts'         => 'Today · 09:14',
            'delta_header'   => 'BI-WEEKLY DELTA · WEEK 4',
            'delta_metric_1' => [
                'label' => 'WEIGHT',
                'value' => '−1.6',
                'unit'  => 'kg',
                'desc'  => 'vs start',
                'tone'  => 'neg',
                'pct'   => 65,
            ],
            'delta_metric_2' => [
                'label' => 'ADHERENCE',
                'value' => '87',
                'unit'  => '%',
                'desc'  => 'sessions completed',
                'tone'  => 'neutral',
                'pct'   => 87,
            ],
        ],
        'disclaimer' => 'Sample view · demo data',
    ],

    'step5' => [
        'meta_index'   => 'STEP 05',
        'meta_timing'  => 'CONTINUOUS TRACKING',
        'title_html'   => 'REAL<br>RESULTS.<br>VERIFIABLE.',
        'desc'         => 'Not promises. Metrics. Every week you see changes in weight, body composition, and adherence from your dashboard. At weeks 8–12 your coach delivers a complete evaluation with real data — not invented curves.',
        'detail'       => 'Metrics in your dashboard · Continuous tracking',
        'viz' => [
            'chart_label'  => 'BODY WEIGHT · KG',
            'chart_value'  => '−5.4',
            'chart_pill'   => '▼ 7.3% · 8 WK',
            'axis_labels'  => [
                'START', 'WK 4', 'WK 8',
            ],
            'weeks' => [
                'WK 1–2 ✓',
                'WK 3–4 ✓',
                'WK 5–6 ✓',
                'WK 7–8 ✓',
            ],
        ],
        'disclaimer' => 'Sample view · demo data',
    ],

    'divider' => 'SCIENCE · METHOD · 2026',

    'cta_final' => [
        'kicker'        => 'THE PROCESS IS CLEAR · SO IS THE NEXT STEP',
        'title_html'    => 'START<br><span class="accent">THE PROCESS</span>',
        'sub'           => 'No waiting list. No contracts. You start with the assessment today and your coach contacts you within 24–48 h.',
        'btn_primary'   => 'Start the process',
        'btn_secondary' => 'See plans and pricing',
        'stats' => [
            ['val' => '5–8',   'label' => 'Min in the assessment'],
            ['val' => '24–48', 'label' => 'Hours to your coach'],
            ['val' => '8–12',  'label' => 'Weeks of protocol'],
        ],
        'trust_items' => [
            'No credit card at the assessment',
            'Human coach 1:1 · no bots',
            'Plan in your dashboard · no loose PDFs',
        ],
    ],

    'sticky' => [
        'text_strong' => 'Start the process',
        'text'        => '5 minutes · no commitment',
        'cta'         => 'Sign me up',
    ],

];
