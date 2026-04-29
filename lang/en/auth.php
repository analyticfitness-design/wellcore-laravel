<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Authentication Language Lines (EN)
    |--------------------------------------------------------------------------
    | TODO i18n: revisar copy EN nativo. Pass-through inicial Sprint 4.
    */

    'failed'   => 'These credentials do not match our records.',
    'password' => 'The provided password is incorrect.',
    'throttle' => 'Too many login attempts. Please try again in :seconds seconds.',

    'login' => [
        'meta_title'        => 'Sign in — WellCore Fitness',
        'meta_description'  => 'Access your plan, metrics and coach at WellCore Fitness.',

        'eyebrow'           => 'Access · Member',
        'hero_title_html'   => 'SIGN<br>IN<em>.</em>',
        'sub'               => 'Back to your plan.',

        'identity_label'    => 'Email or username',
        'identity_placeholder' => 'you@email.com or your_username',
        'password_label'    => 'Password',
        'password_placeholder' => '••••••••',

        'show_password'     => 'Show password',
        'hide_password'     => 'Hide password',

        'remember'          => 'Remember me',
        'remember_sub'      => 'Keep me signed in on this device.',

        'forgot'            => 'Forgot your password?',

        'submit'            => 'Sign in',
        'submit_loading'    => 'Verifying…',
        'submit_success'    => 'Access granted',

        'no_account'        => 'No account?',
        'signup_link'       => 'Sign up',

        'trust' => [
            'tls'           => 'TLS 1.3',
            'csrf'          => 'CSRF · Active protection',
            'wellcore'      => 'WellCore Fitness',
        ],

        'no_access_title'   => 'No access?',
        'no_access_whatsapp' => 'Message us on WhatsApp',

        'forgot_sheet' => [
            'title'         => 'Recover access',
            'sub'           => 'Enter the email tied to your account. We send a secure link to reset your password.',
            'cancel'        => 'Cancel',
        ],

        'errors' => [
            'identity_required' => 'Enter your email or username.',
            'identity_min'      => 'Email or username must be at least 3 characters.',
            'password_required' => 'Enter your password.',
            'invalid_credentials' => 'Email or password is incorrect.',
            'not_found'         => 'We did not find an account with those credentials.',
        ],
    ],

    'forgot' => [
        'meta_title'        => 'Recover access — WellCore Fitness',
        'title'             => 'Recover password',
        'sub'               => 'Enter your email and we will send a link to reset your password.',
        'email_label'       => 'Email',
        'email_placeholder' => 'you@email.com',
        'submit'            => 'Send link',
        'submit_loading'    => 'Sending…',
        'sent_title'        => 'Link sent',
        'sent_body_html'    => 'We sent an email to <b>:email</b>. Check your inbox in the next few minutes.',
        'sent_spam_note'    => 'If you do not see it, check the spam folder.',
        'sent_expiry'       => 'The link expires in 1 hour.',
        'back_to_login'     => 'Back to sign in',
        'errors' => [
            'email_required'    => 'Enter your email.',
            'email_invalid'     => 'Enter a valid email.',
            'rate_limit'        => 'You requested too many links. Try again in :minutes minute(s).',
            'send_failed'       => 'We could not send the email. Try again in a few minutes.',
        ],
    ],
];
