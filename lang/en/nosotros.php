<?php

/*
 |---------------------------------------------------------------------
 | /nosotros · brand storytelling editorial v2 — EN translations.
 | Mirrors lang/es/nosotros.php structure (Sprint 2026-04-29 porting).
 |---------------------------------------------------------------------
 */

return [

    // -------------------------------------------------------------------------
    // Meta — head
    // -------------------------------------------------------------------------
    'meta_title'       => 'Our Story · WellCore Fitness',
    'meta_description' => 'WellCore Fitness started because it happened to us. Evidence-based coaching, no miracles. Meet the team, the story, the values.',

    // -------------------------------------------------------------------------
    // Sidebar editorial (≥1024px)
    // -------------------------------------------------------------------------
    'sidebar' => [
        'subtitle'       => 'Our Story · 2026',
        'progress_label' => 'Progress',
        'cta'            => 'EXPLORE THE METHOD',
    ],

    // -------------------------------------------------------------------------
    // Capítulos / secciones
    // -------------------------------------------------------------------------
    'chapters' => [
        'cap00' => ['pill' => 'Ch. 00 · Cover',     'nav_title' => 'Cover'],
        's1'    => ['pill' => 'Ch. 01 · Manifesto', 'nav_title' => 'Manifesto'],
        's2'    => ['pill' => 'Ch. 02 · History',   'nav_title' => 'History'],
        's3'    => ['pill' => 'Ch. 03 · Team',      'nav_title' => 'Team'],
        's4'    => ['pill' => 'Ch. 04 · Values',    'nav_title' => 'Values'],
        'cta'   => ['pill' => 'If you think it\'s for you', 'nav_title' => 'Talk to us'],
    ],

    // -------------------------------------------------------------------------
    // Hero founder
    // -------------------------------------------------------------------------
    'hero' => [
        'eyebrow'    => 'WC · OUR STORY · 2018 — 2026 · LATAM',
        'title_html' => 'WE STARTED<br>BECAUSE IT HAPPENED<br>TO <span class="ac">US.</span>',
        'sub'        => '"WellCore is what we needed when nobody was offering it."',
        'meta' => [
            ['k' => 'Founded',   'v' => '2018 · Bucaramanga'],
            ['k' => 'Core team', 'v' => '06 people'],
            ['k' => 'Coverage',  'v' => 'LATAM'],
            ['k' => 'Philosophy','v' => 'No miracles'],
        ],
    ],

    // -------------------------------------------------------------------------
    // Manifiesto editorial
    // -------------------------------------------------------------------------
    'manifiesto' => [
        'tag'      => '§ 01  ·  Manifesto · Daniel Esparza',
        'p1_html'  => 'I used to train without direction. Paying coaches who promised impossible things and delivered copy-paste plans. I was looking for <strong>science, not charisma</strong>. Method, not Instagram motivation. <em>What I needed didn\'t exist</em> — so we built it.',
        'p2_html'  => 'WellCore wasn\'t born from a pitch deck or a funding round. It was born from the honest frustration of not finding what should have existed: evidence-based coaching, no shortcuts, no manipulated transformations, no fake urgency. We started the way I needed someone to start with me.',
        'p3_html'  => 'Years later we\'re still on the same line. We\'re a small team that <strong>trains what it teaches</strong>, a platform built by hand, and a simple promise: if we can\'t explain the scientific reason behind what we ask of you, we don\'t ask it.',
        'p4_html'  => 'This isn\'t a fitness company. It\'s a method with a product on top. And it keeps growing because <em>it actually works.</em>',
        'sig_name' => 'DANIEL ESPARZA',
        'sig_role' => 'CEO · Founder · WellCore Fitness',
    ],

    // -------------------------------------------------------------------------
    // Timeline historia
    // -------------------------------------------------------------------------
    'timeline' => [
        'intro'     => 'From 1:1 coach in Bucaramanga to LATAM platform. Five milestones of method, not miracles.',
        'intro_sub' => '2018 → 2026 · Five milestones',
        'items' => [
            [
                'year'       => '2018',
                'tag'        => 'origin',
                'state'      => 'done',
                'title_html' => 'WELLCORE<br>FOUNDED',
                'desc'       => 'Daniel starts as a 1:1 coach in Bucaramanga, Colombia. Spreadsheets, manual check-ins, and a clear method: science first, everything else later. The method existed before the product.',
                'meta'       => ['Bucaramanga', '1 athlete', 'no platform'],
            ],
            [
                'year'       => '2020',
                'tag'        => 'online',
                'state'      => 'done',
                'title_html' => 'FIRST FULL<br>ONLINE PLAN',
                'desc'       => 'The first integrated 100% online plan: training, nutrition, and biweekly check-ins packaged as a product, not a loose PDF. Science finally enters structured.',
                'meta'       => ['v0 online', 'biweekly check-ins', 'method packaged'],
            ],
            [
                'year'       => '2022',
                'tag'        => 'scale',
                'state'      => 'done',
                'title_html' => 'COACHING 1:1<br>SCALED',
                'desc'       => 'We grew from one coach to a small team of certified coaches with the WellCore method. More voices, same rigor. Each coach passes an evidence and workload filter before joining.',
                'meta'       => ['core team', 'shared method', 'uniform rigor'],
            ],
            [
                'year'       => '2024',
                'tag'        => 'platform',
                'state'      => 'done',
                'title_html' => 'OUR OWN<br>PLATFORM',
                'desc'       => 'We launched our own platform: client panel, coach panel, exercise library, weekly tracking. For the first time the method lives inside software we built ourselves.',
                'meta'       => ['client panel', 'coach panel', 'in-house product'],
            ],
            [
                'year'       => '2026',
                'tag'        => 'in progress',
                'state'      => 'future',
                'title_html' => 'LATAM<br>EXPANSION',
                'desc'       => 'Active expansion into more LATAM countries with local pricing, neutral Spanish content, and a complete editorial redesign. Same method, wider reach.',
                'meta'       => ['v2 redesign', 'local pricing', 'neutral Spanish'],
            ],
        ],
    ],

    // -------------------------------------------------------------------------
    // Equipo
    // -------------------------------------------------------------------------
    'equipo' => [
        'intro'     => '"The people behind the method. Every one of them trains what they teach."',
        'intro_sub' => '06 people · LATAM',
        'members' => [
            [
                'num'        => '01 / 06',
                'initials'   => 'DE',
                'name_html'  => 'DANIEL ESPARZA',
                'role'       => 'CEO · Founder · Bucaramanga',
                'quote'      => 'We built it because we needed it.',
                'bio'        => 'Founder of WellCore Fitness. Specialist in exercise physiology and sports nutrition. Designed the method the team shares today: science first, no miracles.',
                'featured'   => true,
            ],
            [
                'num' => '02 / 06', 'initials' => 'CR', 'name_html' => 'CR',
                'role' => 'Senior coach',
                'quote' => 'Progress isn\'t improvised. It\'s programmed.',
                'bio' => null, 'featured' => false,
            ],
            [
                'num' => '03 / 06', 'initials' => 'MV', 'name_html' => 'MV',
                'role' => 'Clinical nutritionist',
                'quote' => 'Nutrition is half the job. Almost always the worse half.',
                'bio' => null, 'featured' => false,
            ],
            [
                'num' => '04 / 06', 'initials' => 'LM', 'name_html' => 'LM',
                'role' => 'Women\'s coaching specialist',
                'quote' => 'Every cycle counts. Programming around that changes everything.',
                'bio' => null, 'featured' => false,
            ],
            [
                'num' => '05 / 06', 'initials' => 'JR', 'name_html' => 'JR',
                'role' => 'Senior performance coach',
                'quote' => 'Measurable progress or it\'s not progress. It\'s hope.',
                'bio' => null, 'featured' => false,
            ],
            [
                'num' => '06 / 06', 'initials' => 'SB', 'name_html' => 'SB',
                'role' => 'Sports nutritionist',
                'quote' => 'Science needs to reach the people who need it.',
                'bio' => null, 'featured' => false,
            ],
        ],
    ],

    // -------------------------------------------------------------------------
    // Valores
    // -------------------------------------------------------------------------
    'valores' => [
        'intro'    => 'Three hard lines',
        'headline' => 'What we don\'t negotiate — even if it costs us clients, virality, or shortcuts.',
        'items' => [
            [
                'num'            => '/01',
                'statement_html' => 'WE DON\'T PROMISE<br><span class="red">MIRACLES.</span><br>WE PROMISE METHOD.',
                'context_html'   => 'Every plan we deliver has evidence behind it. If we can\'t explain the <strong>scientific reason</strong> behind what we ask of you, we don\'t ask it. Motivation runs out; method stays.',
            ],
            [
                'num'            => '/02',
                'statement_html' => 'YOUR PROGRESS<br>IS <span class="red">YOURS.</span><br>NOT OURS.',
                'context_html'   => 'We don\'t use your transformation as marketing bait. <strong>Your body, your story, your result.</strong> We\'re the method; you\'re the protagonist. No manipulated before/after, no paid testimonials.',
            ],
            [
                'num'            => '/03',
                'statement_html' => 'SCIENCE<br>ISN\'T <span class="red">OPTIONAL.</span>',
                'context_html'   => 'We train with up-to-date evidence. <strong>Not with trends, not with intuition.</strong> If there\'s a paper behind it, we cite it. If there isn\'t, we don\'t use it. If the evidence changes, so does the plan.',
            ],
        ],
    ],

    // -------------------------------------------------------------------------
    // CTA suave
    // -------------------------------------------------------------------------
    'cta_suave' => [
        'kicker'        => 'NEXT STEP',
        'title_html'    => 'IF YOU THINK IT\'S<br>FOR <span class="red">YOU,</span> LET\'S TALK.',
        'sub'           => '"No pressure, no countdowns. If you want to see how we work, the method and the plans are one click away."',
        'btn_primary'   => 'EXPLORE THE METHOD',
        'btn_secondary' => 'See plans',
        'foot_line'     => 'Take your time · No urgency · You won\'t get 17 emails',
    ],
];
