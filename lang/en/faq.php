<?php

return [

    // SEO
    'title'           => 'FAQ — WellCore Fitness',
    'meta_description'=> 'Everything you need to know about WellCore: plans, payments, online coaching, personalized training and support. No fluff.',

    // -- HERO -----------------------------------------------------------------
    'hero_eyebrow'   => 'FAQ · WHAT YOU WANT TO KNOW',
    'hero_h1_line1'  => 'ASK',
    'hero_h1_accent' => 'EVERYTHING.',
    'hero_sub'       => 'No fluff. No disclaimers. The answers you need, straight up.',

    // -- BACK COMPAT ---------------------------------------------------------
    'hero_h1'        => 'FREQUENTLY ASKED',

    // -- SEARCH ---------------------------------------------------------------
    'buscar'              => 'Search questions...',
    'meta_total'          => ':count answers',
    'meta_categories'     => ':count categories',
    'meta_separator'      => '·',
    'search_clear_aria'   => 'Clear search',
    'search_label'        => 'Search frequently asked questions',

    // -- TABS -----------------------------------------------------------------
    'tabs' => [
        'general'       => 'General',
        'planes'        => 'Plans',
        'pagos'         => 'Payments',
        'entrenamiento' => 'Training',
        'soporte'       => 'Support',
    ],

    // -- ITEMS (25 questions — v1 content preserved verbatim) ----------------
    'items' => [
        // -- general --
        ['id' => 'g1', 'cat' => 'general', 'q' => 'What exactly is WellCore Fitness?',
         'a' => 'WellCore is an evidence-based online coaching platform. Every program is 100% personalized by a certified coach — tailored to your goals, experience level, and lifestyle. Think of it as having a personal trainer, nutritionist, and accountability partner in your pocket.'],
        ['id' => 'g2', 'cat' => 'general', 'q' => 'How does online coaching work?',
         'a' => 'Once you enroll, you receive a personalized training and nutrition protocol. Your dedicated coach monitors your progress, adjusts the program, and communicates with you via direct messaging. Weekly check-ins ensure continuous progress tracking and program optimization.'],
        ['id' => 'g3', 'cat' => 'general', 'q' => 'Do I need any prior experience?',
         'a' => 'Not at all. Our programs adapt to any experience level. Complete beginners receive detailed execution guides and reference videos for every exercise. Experienced lifters get advanced programming.'],
        ['id' => 'g4', 'cat' => 'general', 'q' => 'Can I train at home or do I need a gym?',
         'a' => 'Either works. When you sign up, you specify your available equipment and your coach builds the program around it. Most plans are gym-optimized, but we have complete home and bodyweight-only alternatives.'],
        ['id' => 'g5', 'cat' => 'general', 'q' => 'Which countries do you serve?',
         'a' => 'We operate across Latin America: Colombia, Mexico, Chile, Peru, Argentina, and Ecuador. Since coaching is 100% online, you can train from anywhere in the world.'],

        // -- plans --
        ['id' => 'p1', 'cat' => 'planes', 'q' => 'What plans do you offer?',
         'a' => 'Three main tiers: <strong>Essential</strong> ($65/mo — custom programming), <strong>Method</strong> ($95/mo — weekly coaching + precision nutrition + community), and <strong>Elite</strong> ($150/mo — full 1:1 coaching + video sessions). We also offer <strong>RISE</strong> (30-day transformation challenge) and <strong>In-Person</strong> (Bucaramanga only).'],
        ['id' => 'p2', 'cat' => 'planes', 'q' => 'Which plan is right for me?',
         'a' => '<strong>Essential</strong> if you have experience and just need smart programming. <strong>Method</strong> is our most popular — ideal if you want weekly coaching with nutrition and community access. <strong>Elite</strong> for those who want the highest level of personalization with dedicated 1:1 coaching.'],
        ['id' => 'p3', 'cat' => 'planes', 'q' => 'What is the RISE program?',
         'a' => 'A 30-day transformation challenge with personalized training, detailed nutrition, daily habit tracking, and an exclusive community. Designed for an accelerated transformation with measurable, data-backed results.'],
        ['id' => 'p4', 'cat' => 'planes', 'q' => 'How often is my program updated?',
         'a' => 'With <strong>Essential</strong>, program updates are monthly. With <strong>Method</strong> and <strong>Elite</strong>, adjustments are weekly based on your check-in data. The training program is periodized and renewed every 4-6 weeks.'],
        ['id' => 'p5', 'cat' => 'planes', 'q' => 'Can I switch plans?',
         'a' => 'Absolutely. Upgrade or downgrade anytime. Changes apply in the next billing period — seamless and hassle-free.'],

        // -- payments --
        ['id' => 'pa1', 'cat' => 'pagos', 'q' => 'What payment methods do you accept?',
         'a' => 'Credit/debit cards and bank transfers via Wompi. Prices listed in USD. For Colombia, we also accept COP at the daily exchange rate.'],
        ['id' => 'pa2', 'cat' => 'pagos', 'q' => 'Can I cancel at any time?',
         'a' => 'Yes. Cancellation takes effect at the end of your current billing period. No contracts, no penalties, no hidden fees. RISE is the exception — it\'s a 30-day commitment.'],
        ['id' => 'pa3', 'cat' => 'pagos', 'q' => 'Do you offer refunds?',
         'a' => '7-day money-back guarantee for new clients. After that window, no refund but you keep full access through the end of your paid period. See our complete <a href=":url" class="text-wc-accent hover:underline">refund policy</a>.'],
        ['id' => 'pa4', 'cat' => 'pagos', 'q' => 'Is billing monthly or annual?',
         'a' => 'Monthly auto-billing by default. Annual plans are not currently available.'],
        ['id' => 'pa5', 'cat' => 'pagos', 'q' => 'What happens if my payment fails?',
         'a' => 'We retry automatically. If it persists, you\'ll receive a notification to update your payment method. Access is paused after 5 days without a successful payment.'],

        // -- training --
        ['id' => 'e1', 'cat' => 'entrenamiento', 'q' => 'What does the nutrition protocol include?',
         'a' => 'Personalized macro calculations, meal timing optimized for your schedule, food lists with alternatives, and flexible guidelines — not a rigid diet. Adjustments are made based on your progress data.'],
        ['id' => 'e2', 'cat' => 'entrenamiento', 'q' => 'How do check-ins work?',
         'a' => 'A weekly form covering weight, measurements, progress photos, and training feedback. Your coach reviews all the data and optimizes the program accordingly. It\'s your weekly performance review.'],
        ['id' => 'e3', 'cat' => 'entrenamiento', 'q' => 'Can I combine cardio and weights?',
         'a' => 'Yes. Your program integrates both strategically based on your goals. Building muscle? Strength is prioritized. Losing fat? Cardio and resistance training are balanced for optimal results.'],
        ['id' => 'e4', 'cat' => 'entrenamiento', 'q' => 'What if I have an injury?',
         'a' => 'Notify your coach immediately. The program is adjusted to work around the injury safely. For serious conditions, we\'ll recommend medical clearance before continuing training.'],
        ['id' => 'e5', 'cat' => 'entrenamiento', 'q' => 'How long are the workouts?',
         'a' => 'Typically 45-75 minutes depending on your plan and goals. Includes warm-up and cool-down. Recommended frequency: 3-6 sessions per week.'],

        // -- support --
        ['id' => 's1', 'cat' => 'soporte', 'q' => 'How do I reach support?',
         'a' => 'Email <a href="mailto:info@wellcorefitness.com" class="text-wc-accent hover:underline">info@wellcorefitness.com</a>, in-app chat inside your dashboard, or WhatsApp. Elite clients get VIP support with sub-12-hour response times.'],
        ['id' => 's2', 'cat' => 'soporte', 'q' => 'Can I switch coaches?',
         'a' => 'Yes. Request a coach change through support and a new coach is assigned within 48 hours. No additional cost.'],
        ['id' => 's3', 'cat' => 'soporte', 'q' => 'What if I can\'t train due to travel or illness?',
         'a' => 'You can pause your plan for up to 30 days at no cost. Contact support to activate the pause — it resumes whenever you\'re ready.'],
        ['id' => 's4', 'cat' => 'soporte', 'q' => 'How do I access the platform?',
         'a' => 'Via web at wellcorefitness.com. Log in with your email and password. You can also install the app as a PWA on your phone for quick home-screen access.'],
        ['id' => 's5', 'cat' => 'soporte', 'q' => 'Is support available in English?',
         'a' => 'Currently our primary support language is Spanish. Full English and Portuguese support are rolling out in 2026.'],
    ],

    // -- EMPTY STATE ----------------------------------------------------------
    'empty_title'    => 'No results',
    'empty_body'     => 'We couldn\'t find answers for ":query". Try another keyword or message us directly.',
    'no_results'     => 'No results found for ":query".',

    // -- CTA ------------------------------------------------------------------
    'cta_eyebrow'    => 'STILL UNSURE',
    'cta_h2'         => 'DIDN\'T FIND YOUR',
    'cta_h2_accent'  => 'ANSWER?',
    'cta_sub'        => 'Reach out directly. A real person responds — not a bot.',
    'cta_contact'    => 'Contact Us',
    'cta_whatsapp'   => 'Direct WhatsApp',
    'cta_whatsapp_msg' => 'Hi WellCore, I have a question about your plans.',
];
