<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Authentication Language Lines
    |--------------------------------------------------------------------------
    | Latino neutro estricto — NUNCA voseo argentino ni castellano peninsular.
    | tú/puedes/quieres/empieza/cancelas — sin "vos/podés/empezá/cancelás".
    */

    'failed'   => 'Estas credenciales no coinciden con nuestros registros.',
    'password' => 'La contraseña proporcionada es incorrecta.',
    'throttle' => 'Demasiados intentos de inicio de sesión. Intenta de nuevo en :seconds segundos.',

    'login' => [
        'meta_title'        => 'Iniciar sesión — WellCore Fitness',
        'meta_description'  => 'Accede a tu plan, métricas y coach en WellCore Fitness.',

        'eyebrow'           => 'Acceso · Cliente',
        'hero_title_html'   => 'INICIAR<br>SESIÓN<em>.</em>',
        'sub'               => 'Vuelve a tu plan.',

        'identity_label'    => 'Email o usuario',
        'identity_placeholder' => 'tu@email.com o tu_usuario',
        'password_label'    => 'Contraseña',
        'password_placeholder' => '••••••••',

        'show_password'     => 'Mostrar contraseña',
        'hide_password'     => 'Ocultar contraseña',

        'remember'          => 'Recordarme',
        'remember_sub'      => 'Mantener la sesión iniciada en este dispositivo.',

        'forgot'            => '¿Olvidaste tu contraseña?',

        'submit'            => 'Iniciar sesión',
        'submit_loading'    => 'Verificando…',
        'submit_success'    => 'Acceso aprobado',

        'no_account'        => '¿No tienes cuenta?',
        'signup_link'       => 'Inscríbete',

        'trust' => [
            'tls'           => 'TLS 1.3',
            'csrf'          => 'CSRF · Protección activa',
            'wellcore'      => 'WellCore Fitness',
        ],

        'no_access_title'   => '¿Sin acceso?',
        'no_access_whatsapp' => 'Escríbenos por WhatsApp',

        'forgot_sheet' => [
            'title'         => 'Recuperar acceso',
            'sub'           => 'Ingresa el email asociado a tu cuenta. Te mandamos un link seguro para restablecer la contraseña.',
            'cancel'        => 'Cancelar',
        ],

        'errors' => [
            'identity_required' => 'Ingresa tu email o usuario.',
            'identity_min'      => 'El email o usuario debe tener al menos 3 caracteres.',
            'password_required' => 'Ingresa tu contraseña.',
            'invalid_credentials' => 'Email o contraseña incorrectos.',
            'not_found'         => 'No encontramos una cuenta con esas credenciales.',
        ],
    ],

    'forgot' => [
        'meta_title'        => 'Recuperar acceso — WellCore Fitness',
        'title'             => 'Recuperar contraseña',
        'sub'               => 'Ingresa tu email y te enviamos un link para restablecer la contraseña.',
        'email_label'       => 'Email',
        'email_placeholder' => 'tu@email.com',
        'submit'            => 'Enviar link',
        'submit_loading'    => 'Enviando…',
        'sent_title'        => 'Link enviado',
        'sent_body_html'    => 'Te enviamos un email a <b>:email</b>. Revisa tu bandeja en los próximos minutos.',
        'sent_spam_note'    => 'Si no llega, revisa la carpeta de spam.',
        'sent_expiry'       => 'El link expira en 1 hora.',
        'back_to_login'     => 'Volver a iniciar sesión',
        'errors' => [
            'email_required'    => 'Ingresa tu email.',
            'email_invalid'     => 'Ingresa un email válido.',
            'rate_limit'        => 'Has solicitado demasiados links. Intenta de nuevo en :minutes minuto(s).',
            'send_failed'       => 'No pudimos enviar el email. Intenta de nuevo en unos minutos.',
        ],
    ],
];
