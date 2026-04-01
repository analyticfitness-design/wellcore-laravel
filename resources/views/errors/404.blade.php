<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagina no encontrada â€” WellCore Fitness</title>
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
            max-width: 480px;
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
            transition: background 0.15s ease;
            box-shadow: 0 4px 24px rgba(220, 38, 38, 0.25);
        }

        .btn-primary:hover {
            background: #B91C1C;
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
        <div class="error-code">404</div>
        <div class="divider"></div>
        <h1 class="title">PAGINA NO ENCONTRADA</h1>
        <p class="message">La pagina que buscas no existe o fue movida a otra direccion. Verifica la URL o regresa al inicio.</p>
        <a href="/" class="btn-primary">Volver al Inicio</a>
    </div>

    <p class="footer-note">&copy; {{ date('Y') }} WellCore Fitness. Todos los derechos reservados.</p>
</body>
</html>
