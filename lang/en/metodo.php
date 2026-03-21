<?php

return [

    // -------------------------------------------------------------------------
    // Hero
    // -------------------------------------------------------------------------
    'hero' => [
        'label'       => 'Evidence-Based Training Protocol',
        'title'       => 'THE METHOD',
        'subtitle'    => 'We don\'t follow trends. We follow science.',
        'description' => 'WellCore is not a routine app or a 30-day plan. It is a scientific protocol, 100% personalized, with real coach oversight. Every variable in your training exists for a proven reason.',
    ],

    // -------------------------------------------------------------------------
    // Stats Bar
    // -------------------------------------------------------------------------
    'stats' => [
        'adherence'       => 'Average adherence',
        'visible_results' => 'Average time to visible results',
        'attention'       => 'Real attention, no bots',
    ],

    // -------------------------------------------------------------------------
    // Section 01 — The Problem
    // -------------------------------------------------------------------------
    'problem' => [
        'title'    => 'THE PROBLEM',
        'subtitle' => 'Why most people fail',
        'intro'    => ':percent of people who start an exercise program quit before 3 months. It\'s not a lack of willpower. It\'s a lack of scientific structure.',
        // Note: :percent is replaced with the counter span HTML

        'fp1' => [
            'title'       => 'No real diagnosis, only generic solutions',
            'description' => 'Most programs assume every body responds the same. They ignore history, body type, functional capacity, and individual goals. The result: a plan not designed for you can never take you where you want to go.',
            'solution'    => 'Complete functional assessment in week 1',
        ],
        'fp2' => [
            'title'       => 'No follow-up, no real-time adjustments',
            'description' => 'A 12-week plan created on day 1 is already outdated by week 4. The body adapts. Variables change. Without an active feedback system, the program becomes obsolete before you see results.',
            'solution'    => 'Weekly review and variable adjustment every cycle',
        ],
        'fp3' => [
            'title'       => 'No education, no long-term autonomy',
            'description' => 'Programs that don\'t educate create dependency. When the plan ends, the client doesn\'t know what to do. WellCore builds understanding: why you train what you train, why you eat what you eat, how your body responds.',
            'solution'    => 'Continuous education integrated into the protocol',
        ],
        'solution_label' => 'WellCore Solution',

        'stats' => [
            's1_label' => 'Programs fail before 90 days',
            's2_label' => 'Drop-off in weeks 1–4',
            's3_label' => 'No clear goal',
            's4_label' => 'WellCore adherence',
            's5_label' => 'More results with coaching',
        ],
        'source' => 'Source: NSCA Journal of Strength and Conditioning Research, 2024 — American College of Sports Medicine, 2023',
    ],

    // -------------------------------------------------------------------------
    // Section 02 — The 5 Pillars
    // -------------------------------------------------------------------------
    'pillars' => [
        'title'    => 'THE STRUCTURE',
        'subtitle' => 'The 5 Pillars of The Method',
        'note'     => 'Each pillar is backed by research published in peer-reviewed journals.',

        'p1' => [
            'name'        => 'Progressive Overload',
            'description' => 'Systematic increase of training load to drive continuous adaptations. Without progression, there is no stimulus. Without stimulus, there is no change.',
        ],
        'p2' => [
            'name'        => 'Intelligent Periodization',
            'description' => 'Structuring training phases to maximize gains and minimize overtraining risk. Every week has a specific purpose.',
        ],
        'p3' => [
            'name'        => 'Precision Nutrition',
            'description' => 'Macronutrients calculated according to your specific goal, your metabolism, and your activity level. No generic diets. Individualized nutritional protocols.',
        ],
        'p4' => [
            'name'        => 'Optimized Recovery',
            'description' => 'Growth happens during recovery, not during training. Sleep protocols, stress management, and active rest integrated into the program.',
        ],
        'p5' => [
            'name'        => 'Behavioral Adherence',
            'description' => 'The best program is the one you follow. Habit psychology, barrier management, and direct communication with your coach integrated to maximize your consistency.',
        ],
    ],

    // -------------------------------------------------------------------------
    // Section 03 — The Difference
    // -------------------------------------------------------------------------
    'comparison' => [
        'title'    => 'THE DIFFERENCE',
        'subtitle' => 'WellCore vs. The Rest',

        'col_feature'  => 'Feature',
        'col_wellcore' => 'WellCore',
        'col_app'      => 'Generic App',
        'col_gym'      => 'Gym PT',

        'rows' => [
            'r1' => [
                'feature'  => 'Complete initial assessment',
                'wellcore' => '40+ variables',
                'app'      => 'No',
                'gym'      => 'Partial',
            ],
            'r2' => [
                'feature'  => '100% personalized program',
                'wellcore' => 'Built from scratch',
                'app'      => 'No (templates)',
                'gym'      => 'Partial',
            ],
            'r3' => [
                'feature'  => 'Weekly follow-up',
                'wellcore' => 'Coach 1:1',
                'app'      => 'No',
                'gym'      => 'Sessions only',
            ],
            'r4' => [
                'feature'  => 'Real-time adjustments',
                'wellcore' => 'Weekly',
                'app'      => 'No',
                'gym'      => 'Rarely',
            ],
            'r5' => [
                'feature'  => 'Nutrition plan included',
                'wellcore' => 'Base & Elite plans',
                'app'      => 'Extra',
                'gym'      => 'No',
            ],
            'r6' => [
                'feature'  => 'Final results report',
                'wellcore' => 'Week 12',
                'app'      => 'No',
                'gym'      => 'Rarely',
            ],
        ],
        'footnote' => 'Comparison based on standard market offerings. Conditions may vary.',
    ],

    // -------------------------------------------------------------------------
    // Section 04 — FAQ
    // -------------------------------------------------------------------------
    'faq' => [
        'title'    => 'MOST ASKED QUESTIONS',
        'subtitle' => 'If you have more questions, we are available on WhatsApp or you can check our full FAQ section.',
        'see_all'  => 'View all frequently asked questions',

        'q1' => [
            'question' => 'Do I need prior experience to start?',
            'answer'   => 'No. The initial assessment determines your exact starting level. The program is built from there. We have clients with no prior experience and clients with years of training. The protocol adapts to where you are, not where a generic person should be.',
        ],
        'q2' => [
            'question' => 'Can I train at home without equipment?',
            'answer'   => 'Yes. During the assessment we record your available equipment and the program is designed specifically for that context. If you only have your own bodyweight, the program works just as well. If you have access to a full gym, we make the most of everything available.',
        ],
        'q3' => [
            'question' => 'How long does it take to see the first results?',
            'answer'   => 'Changes in body composition begin to show between weeks 6 and 10, depending on starting point and goal. Before that, results are internal: more energy, better sleep, greater strength. Visible results take time. The WellCore average is 8–12 weeks for photo-worthy changes.',
        ],
        'q4' => [
            'question' => 'What if I have an injury or physical limitation?',
            'answer'   => 'Injuries and limitations are documented during the assessment and the program incorporates them from the start. We don\'t ignore problems: we integrate them into the design. If a new injury appears during the program, the plan is adjusted immediately at no extra cost.',
        ],
    ],

    // -------------------------------------------------------------------------
    // Final CTA
    // -------------------------------------------------------------------------
    'cta' => [
        'label'         => 'Next step',
        'title'         => 'START WITH THE METHOD',
        'description'   => '87% adherence. 12 weeks. A protocol designed just for you. No templates, no generics. Just science applied to your real body.',
        'btn_primary'   => 'Start now',
        'btn_secondary' => 'See The Process',
        'trust1'        => 'No contracts',
        'trust2'        => 'Real 1:1 coach',
        'trust3'        => 'Response within 24h',
        'trust4'        => 'Cancel anytime',
    ],

];
