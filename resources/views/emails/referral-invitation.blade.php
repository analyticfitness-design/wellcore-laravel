<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invitación WellCore Fitness</title>
</head>
<body style="font-family: Arial, sans-serif; background: #0a0a0a; color: #ffffff; padding: 40px; margin: 0;">
    <div style="max-width: 600px; margin: 0 auto; background: #111111; border-radius: 12px; padding: 40px; border: 1px solid #333333;">

        <h1 style="color: #DC2626; margin-top: 0; font-size: 28px; letter-spacing: 1px;">WellCore Fitness</h1>

        <h2 style="color: #ffffff; font-size: 22px; margin-bottom: 16px;">
            {{ $referrerName }} te invitó a transformar tu cuerpo
        </h2>

        <p style="color: #aaaaaa; line-height: 1.6; margin-bottom: 24px;">
            Tu amigo {{ $referrerName }} quiere que seas parte de WellCore —
            la plataforma de coaching fitness con ciencia real para el mercado LATAM.
            Entrenamiento personalizado, nutrición y seguimiento continuo de tu coach.
        </p>

        <a href="{{ $referralLink }}"
           style="display: inline-block; background: #DC2626; color: #ffffff; padding: 14px 28px; border-radius: 8px; text-decoration: none; font-weight: bold; font-size: 16px; margin: 8px 0 28px;">
            Comenzar mi transformación
        </a>

        <hr style="border: none; border-top: 1px solid #333333; margin: 28px 0;">

        <p style="color: #555555; font-size: 12px; line-height: 1.6; margin: 0;">
            Si no esperabas este correo, puedes ignorarlo. No se realizó ninguna acción en tu cuenta.<br>
            WellCore Fitness &middot; <a href="https://wellcorefitness.com" style="color: #DC2626; text-decoration: none;">wellcorefitness.com</a>
        </p>
    </div>
</body>
</html>
