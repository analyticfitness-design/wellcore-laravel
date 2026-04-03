<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error del servidor â€” WellCore Fitness</title>
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

        .contact-box {
            margin-top: 2.5rem;
            background: #18181B;
            border: 1px solid #27272A;
            border-radius: 0.75rem;
            padding: 1.25rem 1.5rem;
        }

        .contact-box p {
            font-size: 0.8125rem;
            color: #71717A;
            line-height: 1.6;
        }

        .contact-box a {
            color: #DC2626;
            text-decoration: none;
            font-weight: 500;
        }

        .contact-box a:hover {
            text-decoration: underline;
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
        <div class="error-code">500</div>
        <div class="divider"></div>
        <h1 class="title">ERROR DEL SERVIDOR</h1>
        <p class="message">Algo salio mal de nuestro lado. Estamos trabajando para solucionarlo. Por favor intenta de nuevo en unos minutos.</p>
        <a href="/" class="btn-primary">Volver al Inicio</a>

        <div class="contact-box">
            <p>Si el problema persiste, contactanos:<br>
            <a href="mailto:info@wellcorefitness.com">info@wellcorefitness.com</a><br>
            o a traves de Instagram <a href="https://www.instagram.com/wellcore.fitness/" target="_blank">@wellcore.fitness</a></p>
        </div>
    </div>

    <p class="footer-note">&copy; {{ date('Y') }} WellCore Fitness. Todos los derechos reservados.</p>
</body>
</html>
