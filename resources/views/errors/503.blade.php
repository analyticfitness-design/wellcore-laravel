<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>En mantenimiento â€” WellCore Fitness</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;500;600;700&family=Raleway:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            background-color: #09090B;
            color: #FAFAFA;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }

        .logo {
            font-family: 'Bebas Neue', 'Inter', sans-serif;
            font-size: 1.75rem;
            letter-spacing: 0.15em;
            color: #FAFAFA;
            text-decoration: none;
            margin-bottom: 3rem;
        }

        .logo span {
            color: #DC2626;
        }

        .card {
            text-align: center;
            max-width: 520px;
            width: 100%;
        }

        .error-code {
            font-family: 'Bebas Neue', 'Inter', sans-serif;
            font-size: 8rem;
            line-height: 1;
            color: #DC2626;
            letter-spacing: 0.05em;
        }

        .divider {
            width: 48px;
            height: 3px;
            background: #DC2626;
            margin: 1.25rem auto;
            border-radius: 2px;
        }

        .title {
            font-family: 'Bebas Neue', 'Inter', sans-serif;
            font-size: 2rem;
            letter-spacing: 0.1em;
            color: #FAFAFA;
            margin-bottom: 1rem;
        }

        .message {
            font-size: 0.9375rem;
            color: #A1A1AA;
            line-height: 1.6;
            margin-bottom: 2.5rem;
        }

        .social-box {
            background: #18181B;
            border: 1px solid #27272A;
            border-radius: 0.75rem;
            padding: 1.5rem;
        }

        .social-box p {
            font-size: 0.875rem;
            color: #71717A;
            margin-bottom: 1rem;
        }

        .social-links {
            display: flex;
            justify-content: center;
            gap: 1.5rem;
        }

        .social-links a {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            font-size: 0.875rem;
            font-weight: 500;
            color: #A1A1AA;
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 9999px;
            border: 1px solid #27272A;
            transition: border-color 0.15s, color 0.15s;
        }

        .social-links a:hover {
            border-color: #DC2626;
            color: #DC2626;
        }

        .pulse {
            display: inline-block;
            width: 8px;
            height: 8px;
            background: #DC2626;
            border-radius: 50%;
            margin-right: 0.5rem;
            animation: pulse 1.5s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.4; transform: scale(0.85); }
        }

        .status-line {
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8125rem;
            color: #DC2626;
            margin-bottom: 2rem;
            font-weight: 500;
        }

        .footer-note {
            margin-top: 3rem;
            font-size: 0.75rem;
            color: #52525B;
        }
    </style>
</head>
<body>
    <a href="/" class="logo">WELL<span>CORE</span></a>

    <div class="card">
        <div class="error-code">503</div>
        <div class="divider"></div>
        <h1 class="title">EN MANTENIMIENTO</h1>

        <div class="status-line">
            <span class="pulse"></span>
            Actualizacion en progreso
        </div>

        <p class="message">Estamos realizando mejoras en la plataforma para brindarte una mejor experiencia. Volveremos muy pronto. Gracias por tu paciencia.</p>

        <div class="social-box">
            <p>Mientras esperas, siguenos en redes sociales:</p>
            <div class="social-links">
                <a href="https://www.instagram.com/wellcore.fitness/" target="_blank" rel="noopener">
                    Instagram
                </a>
                <a href="https://www.youtube.com/@Wellcorefitness" target="_blank" rel="noopener">
                    YouTube
                </a>
            </div>
        </div>
    </div>

    <p class="footer-note">&copy; {{ date('Y') }} WellCore Fitness. Todos los derechos reservados.</p>
</body>
</html>
