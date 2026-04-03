<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso denegado â€” WellCore Fitness</title>
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
            font-family: 'Oswald', Impact, sans-serif;
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
            max-width: 480px;
            width: 100%;
        }

        .error-code {
            font-family: 'Oswald', Impact, sans-serif;
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
            font-family: 'Oswald', Impact, sans-serif;
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

        .actions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            justify-content: center;
        }

        .btn-primary {
            display: inline-block;
            background: #DC2626;
            color: #FFFFFF;
            font-size: 0.875rem;
            font-weight: 600;
            padding: 0.75rem 1.75rem;
            border-radius: 9999px;
            text-decoration: none;
            letter-spacing: 0.02em;
            box-shadow: 0 4px 24px rgba(220, 38, 38, 0.25);
        }

        .btn-primary:hover {
            background: #B91C1C;
        }

        .btn-secondary {
            display: inline-block;
            background: transparent;
            color: #A1A1AA;
            font-size: 0.875rem;
            font-weight: 600;
            padding: 0.75rem 1.75rem;
            border-radius: 9999px;
            text-decoration: none;
            letter-spacing: 0.02em;
            border: 1px solid #27272A;
        }

        .btn-secondary:hover {
            border-color: #3F3F46;
            color: #FAFAFA;
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
        <div class="error-code">403</div>
        <div class="divider"></div>
        <h1 class="title">ACCESO DENEGADO</h1>
        <p class="message">No tienes permiso para acceder a esta seccion. Si crees que es un error, inicia sesion con tu cuenta o contacta a soporte.</p>
        <div class="actions">
            <a href="/" class="btn-primary">Volver al Inicio</a>
            <a href="/login" class="btn-secondary">Iniciar Sesion</a>
        </div>
    </div>

    <p class="footer-note">&copy; {{ date('Y') }} WellCore Fitness. Todos los derechos reservados.</p>
</body>
</html>
