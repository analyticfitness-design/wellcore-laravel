<?php

/**
 * /metodo — i18n (EN) · v2 long-form editorial.
 *
 * Estado: TRADUCCIÓN PARCIAL — claves nuevas (Cap03–Cap07, pullquotes, inline_ctas,
 * sticky, sidebar, chapters) son pass-through del ES. Las claves legacy (hero,
 * stats, problem.fp1/fp2/fp3, pillars, comparison, faq, cta) mantienen su
 * traducción EN existente.
 *
 * TODO traducir post-launch: marcado con // EN-TODO.
 */

return [

    // -------------------------------------------------------------------------
    // SEO
    // -------------------------------------------------------------------------
    'meta_title'       => 'The Method — WellCore Fitness · Evidence-Based Protocol', // EN-TODO refine
    'meta_description' => 'Long-form editorial about the WellCore method: science, periodization, 1:1 human coaching. No miracles. Just evidence applied to your real body.',

    // -------------------------------------------------------------------------
    // Sidebar editorial
    // -------------------------------------------------------------------------
    'sidebar' => [
        'subtitle'       => 'The Method · 2026',
        'progress_label' => 'Progress',
        'cta'            => 'GET STARTED',
    ],

    // -------------------------------------------------------------------------
    // Chapters
    // -------------------------------------------------------------------------
    'chapters' => [
        'cap00' => [
            'nav_title' => 'Cover',
            'divider'   => 'COVER · WELLCORE 2026',
            'pill'      => 'Cover',
        ],
        'cap01' => [
            'nav_title' => 'The Problem',
            'divider'   => 'CHAPTER 01 · THE PROBLEM',
            'pill'      => 'Ch 01 · The Problem',
            'num_text'  => '01 · Why most people fail',
            'title_html'=> 'THE<br><em>PROBLEM</em>',
        ],
        'cap02' => [
            'nav_title' => 'The Method',
            'divider'   => 'CHAPTER 02 · THE METHOD',
            'pill'      => 'Ch 02 · The Method',
            'num_text'  => '02 · The 5 pillars of the protocol',
            'title_html'=> 'THE<br><em>METHOD</em>',
        ],
        'cap03' => [
            'nav_title' => 'The Science',
            'divider'   => 'CHAPTER 03 · THE SCIENCE',
            'pill'      => 'Ch 03 · The Science',
            'num_text'  => '03 · How real hypertrophy works',
            'title_html'=> 'THE<br><em>SCIENCE</em>',
        ],
        'cap04' => [
            'nav_title' => 'The Plan',
            'divider'   => 'CHAPTER 04 · THE PLAN',
            'pill'      => 'Ch 04 · The Plan',
            'num_text'  => '04 · How your protocol is built',
            'title_html'=> 'THE<br><em>PLAN</em>',
        ],
        'cap05' => [
            'nav_title' => 'The Coach',
            'divider'   => 'CHAPTER 05 · THE COACH',
            'pill'      => 'Ch 05 · The Coach',
            'num_text'  => '05 · Who they are and how they work',
            'title_html'=> 'THE<br><em>COACH</em>',
        ],
        'cap06' => [
            'nav_title' => 'The Check-ins',
            'divider'   => 'CHAPTER 06 · THE CHECK-INS',
            'pill'      => 'Ch 06 · The Check-ins',
            'num_text'  => '06 · What happens every week',
            'title_html'=> 'THE<br><em>CHECK-INS</em>',
        ],
        'cap07' => [
            'nav_title' => 'Objections',
            'divider'   => 'CHAPTER 07 · OBJECTIONS',
            'pill'      => 'Ch 07 · Objections',
            'num_text'  => '07 · The questions nobody asks out loud',
            'title_html'=> '<em>OBJECTIONS</em>',
        ],
    ],

    // -------------------------------------------------------------------------
    // Hero
    // -------------------------------------------------------------------------
    'hero' => [
        'kicker'         => 'Evidence-based protocol · WellCore Fitness · 2026',
        'pullquote_html' => 'TRAINING WITHOUT<br>PROGRESSION<br>IS JUST <em>REPETITION.</em>',
        'sub'            => 'This is what changes with WellCore.',
        'scroll_hint'    => 'Scroll',

        // Legacy compat:
        'label'       => 'Evidence-Based Training Protocol',
        'title'       => 'THE METHOD',
        'subtitle'    => 'We don\'t chase trends. We follow the evidence.',
        'description' => 'WellCore isn\'t a workout app or a cookie-cutter 30-day plan. It\'s a scientific protocol — 100% personalized, with dedicated human coach oversight. Every variable in your training exists for a proven, evidence-based reason.',
    ],

    // -------------------------------------------------------------------------
    // Stats
    // -------------------------------------------------------------------------
    'stats' => [
        'adherence_value'       => '87%',
        'adherence'             => 'Average adherence rate',
        'visible_results_value' => '12',
        'visible_results'       => 'Weeks to visible results',
        'attention_value'       => '1:1',
        'attention'             => 'Real human coach, no bots',
    ],

    // -------------------------------------------------------------------------
    // Cap01 — The Problem (EN-TODO refine wording)
    // -------------------------------------------------------------------------
    'problem' => [
        'intro_p1_html' => '80% of people who start a fitness program quit within three months. <strong>It\'s not a willpower problem. It\'s an architecture problem.</strong> Most programs assume every body responds the same way, ignore individual history, and deliver generic templates disguised as personalization.',
        'intro_p2_html' => 'Without a real assessment, the plan was never yours. A 12-week program designed on day 1 is already outdated by week 4: your body adapts, the variables change, and without active feedback the program becomes obsolete before you see results. <strong>The problem isn\'t the exercise. It\'s the lack of a system.</strong>',
        'intro_p3_html' => 'The third mistake is the quietest: when the plan ends, the client doesn\'t know what to do next. Programs that don\'t educate create dependency. WellCore builds understanding. Why you train what you train. Why you eat what you eat. <em>How your body responds.</em>',

        'data_cells' => [
            ['value' => '8/10', 'label' => 'programs fail before 90 days'],
            ['value' => '67%',  'label' => 'drop off in weeks 1–4'],
            ['value' => '54%',  'label' => 'no clear goal at the start'],
            ['value' => '87%',  'label' => 'WellCore average adherence', 'accent' => true],
        ],
        'source' => 'Source: NSCA Journal of Strength & Conditioning Research, 2024 · ACSM, 2023.',
    ],

    // -------------------------------------------------------------------------
    // Cap02 — The Method
    // -------------------------------------------------------------------------
    'pillars' => [
        'intro_p1_html' => 'Five pillars. Each one exists because there\'s a paper backing it. They\'re not marketing categories or arbitrary content divisions. They\'re the five vectors scientific literature identifies as determinants of long-term progress in body composition and performance.',
        'margin_note'   => '"Each pillar exists because a paper backs it. They\'re not marketing categories — they\'re the five vectors evidence identifies as determinants of progress."',

        'p1' => [
            'name'        => 'PROGRESSIVE OVERLOAD',
            'description' => 'Systematic increase of load to drive continuous adaptation. Without progression, there\'s no stimulus. Without stimulus, there\'s no change. The body is brutally efficient: if you don\'t force it to adapt, it won\'t.',
            'cite'        => 'Schoenfeld 2017 · NSCA Specificity Principle',
        ],
        'p2' => [
            'name'        => 'INTELLIGENT PERIODIZATION',
            'description' => 'Structuring training phases to maximize gains and minimize overtraining. Every week has a specific purpose. Adaptation → hypertrophy → strength → deload. It\'s not randomness disguised as variety.',
            'cite'        => 'Haff & Triplett 2016 · Periodization Theory',
        ],
        'p3' => [
            'name'        => 'PRECISION NUTRITION',
            'description' => 'Macronutrients calculated for your specific goal, your metabolism, and your real activity level. No generic diets. No fads. Individualized nutritional protocols that adjust each cycle based on real progress data.',
            'cite'        => 'Morton et al. 2018 · Systematic review of protein supplementation',
        ],
        'p4' => [
            'name'        => 'OPTIMIZED RECOVERY',
            'description' => 'Growth happens in recovery, not training. Sleep protocols, stress management, and active rest built into the program. Accumulated fatigue without management quietly destroys progress.',
            'cite'        => 'Simpson et al. 2017 · Sleep and Athletic Performance',
        ],
        'p5' => [
            'name'        => 'BEHAVIORAL ADHERENCE',
            'description' => 'The best program is the one you actually follow. Habit psychology, barrier management, and direct coach communication built in to maximize consistency. A perfect plan that gets abandoned is worth less than a good plan that gets sustained.',
            'cite'        => 'Gardner et al. 2012 · Making health habitual',
        ],

        // Legacy compat:
        'title'    => 'THE FRAMEWORK',
        'subtitle' => 'The 5 Pillars of The Method',
        'note'     => 'Each pillar is backed by research published in peer-reviewed journals.',
    ],

    'comparison' => [
        'title'    => 'WELLCORE VS. THE REST',
        'subtitle' => 'How we compare to generic apps and traditional gym trainers.',

        'col_feature'  => 'Feature',
        'col_wellcore' => 'WellCore',
        'col_app'      => 'Generic App',
        'col_gym'      => 'Gym PT',

        'rows' => [
            'r1' => [
                'feature'  => 'Initial assessment',
                'wellcore' => '40+ variables',
                'app'      => 'No',
                'gym'      => 'Partial',
            ],
            'r2' => [
                'feature'  => '100% custom program',
                'wellcore' => 'Built from scratch',
                'app'      => 'Templates',
                'gym'      => 'Partial',
            ],
            'r3' => [
                'feature'  => 'Weekly tracking',
                'wellcore' => '1:1 coach',
                'app'      => 'No',
                'gym'      => 'Sessions only',
            ],
            'r4' => [
                'feature'  => 'Real-time adjustments',
                'wellcore' => 'Every week',
                'app'      => 'No',
                'gym'      => 'Rare',
            ],
            'r5' => [
                'feature'  => 'Nutrition plan included',
                'wellcore' => 'Essential, Method & Elite',
                'app'      => 'Paid add-on',
                'gym'      => 'No',
            ],
            'r6' => [
                'feature'  => 'Final report with data',
                'wellcore' => 'Week 12',
                'app'      => 'No',
                'gym'      => 'Rare',
            ],
        ],
        'footnote' => 'Comparison based on standard market offerings. Actual features may vary by provider.',
    ],

    // -------------------------------------------------------------------------
    // Cap03 — The Science
    // -------------------------------------------------------------------------
    'ciencia' => [
        'body_p1_html' => 'Hypertrophy isn\'t a mystery. It\'s biology with controllable variables. Muscle grows when it receives sufficient mechanical stimulus — tension, damage, metabolic stress — and when recovery allows protein synthesis to occur. The problem is that most people train with perceived intensity, not measured intensity.',
        'body_p2_html' => '<strong>Relative intensity</strong> is the most precise way to quantify effort without a physiology lab behind you. If you can do three more reps before technical failure, you\'re in a medium intensity zone. The optimal range for hypertrophy sits close to failure, but without crossing it on every set. <strong>Below that, the stimulus is insufficient. Above it, overtraining.</strong> Schoenfeld (2017) showed that effective weekly volume is the most robust predictor of muscle gain when relative intensity is controlled.',
        'body_p3_html' => 'Progress isn\'t linear. It\'s a curve with phases of adaptation, acceleration, and plateau. What separates those who reach week 12 from those who quit at week 4 is reading that curve — and having a human coach who manages it with you.',
        'svg_label'    => 'Fig. 01 — WellCore progression curve · Weeks 1–12 · Relative strength (%)',
        'svg_legend_wc'  => 'WellCore',
        'svg_legend_avg' => 'General average',
        'svg_dot1' => '+8% strength',
        'svg_dot2' => '+22% strength',
        'svg_dot3' => '+34% strength',
        'source'  => 'Schoenfeld 2017 · Journal of Strength & Conditioning Research — representative data of average progress in WellCore clients.',
    ],

    // -------------------------------------------------------------------------
    // Cap04 — The Plan
    // -------------------------------------------------------------------------
    'plan' => [
        'body_p1_html' => 'Before writing a single set, your coach completes a 40+ variable assessment. Training history. Injuries. Available equipment. Average sleep hours. Chronic stress levels. Concrete goals — not "lose weight," but how much, in what timeframe, with what real constraints. That assessment defines your starting point. Nothing more.',
        'body_p2_html' => 'With that data, periodization is built. Four phases distributed across 12 weeks. Each phase has a distinct physiological objective, specific volume and intensity variables, and weekly adjustment criteria. <strong>A day-1 plan is already outdated by week 4</strong> — that\'s why the weekly check-in isn\'t optional: it\'s the central mechanism of the system.',
        'margin_note'  => '"A day-1 plan is already outdated by week 4. The weekly check-in isn\'t optional — it\'s the central mechanism of the system."',

        'period_headers' => [
            'Phase',
            'Weeks',
            'Physiological objective',
            'Relative intensity',
            'Volume',
        ],

        'period' => [
            'adapt' => [
                'tag'       => 'ADAPTATION',
                'name'      => 'Adaptation',
                'weeks'     => '1–3',
                'objective' => 'Neuromuscular coordination, technique, real load assessment',
                'intensity' => 'Moderate–low',
                'volume'    => 'Moderate',
            ],
            'hyper' => [
                'tag'       => 'HYPERTROPHY',
                'name'      => 'Hypertrophy',
                'weeks'     => '4–7',
                'objective' => 'Maximize effective volume · sustained mechanical tension',
                'intensity' => 'High',
                'volume'    => 'High',
            ],
            'fuerza' => [
                'tag'       => 'STRENGTH',
                'name'      => 'Strength',
                'weeks'     => '8–11',
                'objective' => 'Neuromuscular efficiency, high loads, lower total volume',
                'intensity' => 'Very high',
                'volume'    => 'Moderate–low',
            ],
            'desc' => [
                'tag'       => 'DELOAD',
                'name'      => 'Deload',
                'weeks'     => '12',
                'objective' => 'Active recovery · adaptation consolidation',
                'intensity' => 'Low',
                'volume'    => 'Low',
            ],
        ],

        'source' => 'Haff & Triplett 2016 · Periodization Theory · adapted to the WellCore protocol.',
    ],

    // -------------------------------------------------------------------------
    // Cap05 — The Coach
    // -------------------------------------------------------------------------
    'coach' => [
        'body_p1_html' => 'A human coach doesn\'t hand you a plan. They hand you a conversation. The difference between a PDF of routines and a real coaching protocol is the human presence that updates it, interprets it, and adapts it when real life interrupts the theoretical plan — because it always does.',
        'body_p2_html' => 'At WellCore, every coach manages a limited number of active clients. Assignment is never random: it\'s based on the client profile, the goal, and the coach\'s specialization. Once assigned, that coach is your point of contact for the entire protocol. <strong>Response guaranteed in less than 24 hours.</strong> No bots. No auto-replies. No template messages.',
        'body_p3_html' => 'We don\'t replace your coach with automation. Automation saves time where it doesn\'t add value — reminders, metric input, scheduling. The interpretation of your data, the decision to progress or pull back a week, the conversation when something falls apart — that\'s done by a person. <em>That\'s the only way the plan stays yours.</em>',
        'margin_note'  => '"Automation saves time where it doesn\'t add value. Interpretation is done by a person. That\'s the only way the plan stays yours."',
    ],

    // -------------------------------------------------------------------------
    // Cap06 — The Check-ins
    // -------------------------------------------------------------------------
    'checkins' => [
        'body_p1_html' => 'The weekly check-in is the heart of the protocol. It\'s not a formality or a satisfaction survey. It\'s the mechanism by which the plan stays alive. You report real metrics — loads, reps, energy, sleep, nutritional adherence — and your coach processes them to decide if the next week continues the same, advances, or steps back.',
        'body_p2_html' => 'Week 1: assessment and base plan construction. Weeks 2–4: adaptation phase, initial load adjustment. Weeks 5–8: hypertrophy block, maximum effective volume. Weeks 9–11: strength intensification. Week 12: deload and <strong>final body composition report</strong> with start vs. close data.',

        'ticker' => [
            ['name' => 'S.V. · CO',  'metric' => '−6.2 kg',     'detail' => 'BODY FAT · 12 WK · MÉTODO',  'negative' => true],
            ['name' => 'C.R. · MX',  'metric' => '+22 kg',      'detail' => 'SQUAT · 12 WK · ELITE'],
            ['name' => 'F.M. · CO',  'metric' => '+14 kg',      'detail' => 'BENCH · 12 WK · MÉTODO'],
            ['name' => 'A.L. · AR',  'metric' => '−4.8 kg',     'detail' => 'BODY FAT · 12 WK · MÉTODO',  'negative' => true],
            ['name' => 'J.B. · PE',  'metric' => '87%',         'detail' => 'ADHERENCE · ELITE'],
            ['name' => 'M.G. · CL',  'metric' => '+18 kg',      'detail' => 'DEADLIFT · 12 WK · MÉTODO'],
            ['name' => 'D.T. · EC',  'metric' => '−5.5 kg',     'detail' => 'BODY COMP · ELITE',           'negative' => true],
            ['name' => 'P.O. · UY',  'metric' => '+11 kg',      'detail' => 'OHP · 12 WK · ESENCIAL'],
            ['name' => 'L.K. · CO',  'metric' => '+12% STRENGTH', 'detail' => '10 WK · MÉTODO'],
            ['name' => 'R.S. · MX',  'metric' => '−8 kg',       'detail' => 'BODY FAT · 16 WK · ELITE',   'negative' => true],
        ],
        'ticker_label' => 'Anonymized results · active WellCore clients',
        'source' => 'Representative data from active clients. Individual results vary based on starting point and adherence.',
    ],

    // -------------------------------------------------------------------------
    // Cap07 — Objections
    // -------------------------------------------------------------------------
    'objections' => [
        'body_intro_html' => 'There are questions people Google at 2 AM before deciding whether to sign up. Questions that seem reasonable but that really hide a single doubt: <em>is this going to work for me?</em> We answer them straight.',

        'list' => [
            'o1' => [
                'mark' => '01',
                'q'    => 'Do I need previous experience to start?',
                'a'    => 'No. The initial assessment determines your exact starting level. The program is built from there. We have clients with no prior experience and clients with ten years of training. The protocol adapts to where you are — not where someone generic should be.',
            ],
            'o2' => [
                'mark' => '02',
                'q'    => 'Can I train at home without equipment?',
                'a'    => 'Yes. During the assessment we document your available equipment and the program is designed specifically for that context. If you only have your bodyweight, the program works. If you have access to a full gym, we leverage everything available. <strong>The method is the same — the tools vary.</strong>',
            ],
            'o3' => [
                'mark' => '03',
                'q'    => 'How long until I see the first result?',
                'a'    => 'Body composition changes start to show between weeks 6 and 10, depending on your starting point. Before that, the results are internal: more energy, better sleep, increased strength. Visible results take time. <strong>The WellCore average is 8 to 12 weeks for measurable changes with data.</strong>',
            ],
            'o4' => [
                'mark' => '04',
                'q'    => 'What if I have an injury or physical limitation?',
                'a'    => 'Injuries and limitations are documented during assessment and incorporated into the program from day one. We don\'t ignore problems: we integrate them into the design. If a new injury appears during the program, the plan is adjusted immediately at no extra cost.',
            ],
            'o5' => [
                'mark' => '05',
                'q'    => 'Is it expensive compared to a gym?',
                'a'    => '1:1 coaching costs more than a gym membership because it delivers more than a gym membership. Full assessment, personalized plan, weekly tracking, real-time adjustments, nutrition plan, final report. <strong>The right question isn\'t how much WellCore costs. It\'s how much getting no results has already cost you.</strong>',
            ],
        ],
    ],

    // -------------------------------------------------------------------------
    // Pull-quotes
    // -------------------------------------------------------------------------
    'pullquotes' => [
        'q1' => [
            'text_html' => 'IT\'S NOT LACK OF<br><em>WILLPOWER.</em><br>IT\'S LACK OF<br>STRUCTURE.',
            'cite'      => 'WellCore · The Problem',
        ],
        'q2' => [
            'text_html' => 'THE BEST PROGRAM<br>IS THE ONE <em>YOU FOLLOW.</em>',
            'cite'      => 'Pillar 05 · Behavioral adherence',
        ],
        'q3' => [
            'text_html' => 'SCIENCE<br>DOESN\'T OPINE.<br>SCIENCE <em>MEASURES.</em>',
            'cite'      => 'WellCore · The Science',
        ],
        'q4' => [
            'text_html' => 'A HUMAN COACH<br>DOESN\'T HAND YOU A PLAN.<br>THEY HAND YOU A <em>CONVERSATION.</em>',
            'cite'      => 'WellCore · The Coach',
        ],
        'q5' => [
            'text_html' => 'THE QUESTION<br>ISN\'T IF YOU <em>CAN.</em><br>IT\'S IF YOU WANT TO<br>DO IT WELL.',
            'cite'      => 'WellCore · Objections',
        ],
    ],

    // -------------------------------------------------------------------------
    // Inline CTAs
    // -------------------------------------------------------------------------
    'inline_ctas' => [
        'c1' => [
            'label' => 'Next step',
            'title' => 'SEE MY PERSONALIZED PLAN',
            'btn'   => 'See my plan',
        ],
        'c2' => [
            'label' => 'Ready to start?',
            'title' => 'MEET YOUR ASSIGNED COACH',
            'btn'   => 'Meet your coach',
        ],
        'c3' => [
            'label' => 'The protocol works',
            'title' => 'START WITH THE METHOD',
            'btn'   => 'Get started now',
            'btn_secondary' => 'See the process',
        ],
    ],

    // -------------------------------------------------------------------------
    // CTA Final
    // -------------------------------------------------------------------------
    'cta_final' => [
        'kicker'     => 'The next step is yours',
        'title_html' => 'START<br>WITH THE <em>METHOD</em>',
        'sub'        => '87% adherence rate. 12 weeks. A protocol designed exclusively for you. No templates. Just evidence applied to your real body.',
        'btn_primary'   => 'See plans & pricing',
        'btn_secondary' => 'How the process works',
        'trust_items' => [
            'No contracts',
            'Real 1:1 human coach',
            '24h response',
            'Cancel anytime',
        ],
    ],

    // Legacy compat:
    'cta' => [
        'label'         => 'Your next move',
        'title'         => 'START WITH THE METHOD',
        'description'   => '87% adherence rate. 12 weeks. A protocol designed exclusively for you.',
        'btn_primary'   => 'Get Started',
        'btn_secondary' => 'See The Process',
        'trust1'        => 'No contracts',
        'trust2'        => 'Real 1:1 human coach',
        'trust3'        => 'Response within 24h',
        'trust4'        => 'Cancel anytime',
    ],

    // -------------------------------------------------------------------------
    // Sticky mobile CTA
    // -------------------------------------------------------------------------
    'sticky' => [
        'text_strong' => 'WellCore · The Method',
        'text'        => '1:1 evidence-based protocol',
        'cta'         => 'Get started',
    ],

];
