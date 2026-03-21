<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="dark">
    <meta name="supported-color-schemes" content="dark">
    <title>Lanzamiento Oficial — WellCore Fitness Abril 2026</title>
</head>
<body style="margin:0;padding:0;background-color:#09090B;font-family:Arial,Helvetica,sans-serif;-webkit-font-smoothing:antialiased;">
<table width="100%" cellpadding="0" cellspacing="0" style="background-color:#09090B;padding:40px 20px;">
<tr><td align="center">
<table width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;">

  {{-- ── LOGO HEADER ── --}}
  <tr><td style="padding:0 0 32px 0;text-align:center;">
    <div style="display:inline-flex;align-items:center;gap:10px;">
      <div style="display:inline-block;background:#DC2626;width:40px;height:40px;border-radius:8px;line-height:40px;text-align:center;color:white;font-weight:bold;font-size:20px;vertical-align:middle;">W</div>
      <span style="color:#FAFAFA;font-size:20px;font-weight:bold;letter-spacing:3px;vertical-align:middle;">WELLCORE</span>
    </div>
  </td></tr>

  {{-- ── LAUNCH BADGE ── --}}
  <tr><td style="text-align:center;padding-bottom:24px;">
    <span style="display:inline-block;background:rgba(220,38,38,0.15);border:1px solid rgba(220,38,38,0.35);border-radius:50px;padding:6px 20px;font-size:11px;font-weight:bold;letter-spacing:3px;text-transform:uppercase;color:#DC2626;">
      &#127881; Abril 2026 &bull; Lanzamiento Oficial
    </span>
  </td></tr>

  {{-- ── HERO CARD ── --}}
  <tr><td>
    <div style="background:linear-gradient(135deg,#18181B 0%,#1e1e22 50%,#18181B 100%);border-radius:16px;overflow:hidden;border:1px solid rgba(255,255,255,0.06);">

      {{-- Red accent top strip --}}
      <div style="height:3px;background:linear-gradient(90deg,transparent,#DC2626,transparent);"></div>

      <div style="padding:48px 40px 40px;text-align:center;">

        {{-- Live dot + text --}}
        <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:24px;">
          <tr><td align="center">
            <span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:#DC2626;vertical-align:middle;margin-right:8px;"></span>
            <span style="color:rgba(250,250,250,0.5);font-size:12px;letter-spacing:2px;text-transform:uppercase;vertical-align:middle;">EN VIVO — ABRIL 2026</span>
          </td></tr>
        </table>

        <h1 style="color:#FAFAFA;font-size:40px;font-weight:900;letter-spacing:2px;margin:0 0 8px 0;line-height:1.1;text-transform:uppercase;">
          LANZAMIENTO<br>
          <span style="color:#DC2626;">OFICIAL</span>
        </h1>
        <p style="color:rgba(250,250,250,0.55);font-size:16px;line-height:1.6;margin:16px 0 0 0;">
          La nueva plataforma WellCore llego con todo.<br>
          <strong style="color:#FAFAFA;">Y tienes 3 dias gratis para comprobarlo.</strong>
        </p>

        {{-- Trial highlight box --}}
        <table width="100%" cellpadding="0" cellspacing="0" style="margin:32px 0;">
          <tr>
            <td style="background:rgba(220,38,38,0.1);border:1px solid rgba(220,38,38,0.3);border-radius:12px;padding:24px 32px;text-align:center;">
              <p style="color:rgba(250,250,250,0.6);font-size:12px;letter-spacing:2px;text-transform:uppercase;margin:0 0 8px 0;">Oferta de lanzamiento</p>
              <p style="color:#DC2626;font-size:42px;font-weight:900;margin:0;line-height:1;letter-spacing:1px;">3 DIAS</p>
              <p style="color:#FAFAFA;font-size:20px;font-weight:bold;margin:4px 0 12px 0;">COMPLETAMENTE GRATIS</p>
              <p style="color:rgba(250,250,250,0.5);font-size:13px;margin:0;">Sin tarjeta de credito &bull; Sin trampa &bull; Acceso total al plan Metodo</p>
            </td>
          </tr>
        </table>

        {{-- CTA button --}}
        <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:20px;">
          <tr><td align="center">
            <a href="{{ $launchUrl ?? url('/lanzamiento') }}"
               style="display:inline-block;background:#DC2626;color:white;padding:16px 48px;border-radius:50px;text-decoration:none;font-weight:900;font-size:16px;letter-spacing:1px;text-transform:uppercase;box-shadow:0 8px 32px rgba(220,38,38,0.35);">
              ACTIVAR MI TRIAL GRATIS &#8594;
            </a>
          </td></tr>
        </table>

        <p style="color:rgba(250,250,250,0.3);font-size:12px;margin:0;">
          Cupos de fundador limitados &bull; Disponible solo en Abril 2026
        </p>

      </div>
    </div>
  </td></tr>

  {{-- ── SPACER ── --}}
  <tr><td style="height:24px;"></td></tr>

  {{-- ── WHAT'S NEW ── --}}
  <tr><td>
    <div style="background:#18181B;border-radius:16px;padding:36px 40px;border:1px solid rgba(255,255,255,0.06);">

      <h2 style="color:#FAFAFA;font-size:20px;font-weight:bold;letter-spacing:1px;margin:0 0 6px 0;text-transform:uppercase;">
        LO QUE ENCUENTRAS ADENTRO
      </h2>
      <p style="color:rgba(250,250,250,0.4);font-size:13px;margin:0 0 24px 0;">Todo nuevo. Todo para ti.</p>

      <table width="100%" cellpadding="0" cellspacing="0">
        @foreach([
            ['icon' => '&#129302;', 'title' => 'IA Coaching 24/7',           'desc' => 'Asistente inteligente entrenado en ciencia del ejercicio. Responde tus dudas, analiza tu progreso y ajusta tu plan.'],
            ['icon' => '&#128172;', 'title' => 'Chat en Tiempo Real',         'desc' => 'Mensajeria directa con tu coach. Fotos, videos, archivos. Respuesta garantizada en menos de 24 horas.'],
            ['icon' => '&#128200;', 'title' => 'Dashboards Interactivos',     'desc' => 'Graficas en tiempo real de peso, fuerza, grasa corporal y adherencia. Exporta tus datos cuando quieras.'],
            ['icon' => '&#129689;', 'title' => 'WellCoins — Recompensas',     'desc' => 'Sistema gamificado que convierte tu consistencia en beneficios. Gana monedas por entrenar y cumplir habitos.'],
            ['icon' => '&#127920;', 'title' => 'Retos y Comunidad',           'desc' => 'Rankings semanales, retos grupales y celebraciones de logros. Una comunidad que te impulsa a dar lo mejor.'],
            ['icon' => '&#128241;', 'title' => 'App Movil Optimizada',        'desc' => 'PWA instalable con modo offline. Timer de descanso, videos de ejercicios y modo gym sin distracciones.'],
            ['icon' => '&#128176;', 'title' => 'Pagos 100% Colombia',         'desc' => 'Wompi nativo: PSE, debito, credito, Nequi, Bancolomdia. Facturacion electronica incluida.'],
        ] as $feature)
        <tr>
          <td style="padding:0 0 18px 0;">
            <table width="100%" cellpadding="0" cellspacing="0">
              <tr>
                <td width="44" valign="top" style="padding-right:16px;">
                  <div style="width:40px;height:40px;background:rgba(220,38,38,0.12);border-radius:10px;text-align:center;line-height:40px;font-size:18px;">
                    {{ $feature['icon'] }}
                  </div>
                </td>
                <td valign="top">
                  <p style="color:#FAFAFA;font-size:14px;font-weight:bold;margin:0 0 3px 0;">{{ $feature['title'] }}</p>
                  <p style="color:rgba(250,250,250,0.5);font-size:13px;line-height:1.5;margin:0;">{{ $feature['desc'] }}</p>
                </td>
              </tr>
            </table>
          </td>
        </tr>
        @endforeach
      </table>

    </div>
  </td></tr>

  {{-- ── SPACER ── --}}
  <tr><td style="height:24px;"></td></tr>

  {{-- ── URGENCY SECTION ── --}}
  <tr><td>
    <div style="background:linear-gradient(135deg,rgba(220,38,38,0.1) 0%,rgba(220,38,38,0.05) 100%);border-radius:16px;padding:32px 40px;border:1px solid rgba(220,38,38,0.2);text-align:center;">
      <p style="color:rgba(250,250,250,0.5);font-size:12px;letter-spacing:2px;text-transform:uppercase;margin:0 0 10px 0;">Urgencia real</p>
      <h3 style="color:#FAFAFA;font-size:22px;font-weight:bold;margin:0 0 12px 0;">
        Cupos de Fundador<br><span style="color:#DC2626;">Solo Abril 2026</span>
      </h3>
      <p style="color:rgba(250,250,250,0.55);font-size:14px;line-height:1.6;margin:0 0 24px 0;">
        Los precios actuales estan congelados para todos los que se registren este mes.
        A partir de Mayo 2026, los planes subiran de precio. Entra ahora y bloquea tu tarifa de fundador.
      </p>
      <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
          <td width="33%" style="text-align:center;padding:0 8px;">
            <div style="background:rgba(255,255,255,0.04);border-radius:10px;padding:16px 8px;">
              <p style="color:#DC2626;font-size:28px;font-weight:900;margin:0;">$299k</p>
              <p style="color:rgba(250,250,250,0.6);font-size:11px;margin:4px 0 0 0;">Esencial/mes</p>
            </div>
          </td>
          <td width="33%" style="text-align:center;padding:0 8px;">
            <div style="background:rgba(220,38,38,0.12);border:1px solid rgba(220,38,38,0.3);border-radius:10px;padding:16px 8px;">
              <p style="color:#DC2626;font-size:28px;font-weight:900;margin:0;">$399k</p>
              <p style="color:rgba(250,250,250,0.6);font-size:11px;margin:4px 0 2px 0;">Metodo/mes</p>
              <p style="color:#DC2626;font-size:9px;font-weight:bold;letter-spacing:1px;margin:0;">POPULAR</p>
            </div>
          </td>
          <td width="33%" style="text-align:center;padding:0 8px;">
            <div style="background:rgba(255,255,255,0.04);border-radius:10px;padding:16px 8px;">
              <p style="color:#DC2626;font-size:28px;font-weight:900;margin:0;">$549k</p>
              <p style="color:rgba(250,250,250,0.6);font-size:11px;margin:4px 0 0 0;">Elite/mes</p>
            </div>
          </td>
        </tr>
      </table>
    </div>
  </td></tr>

  {{-- ── SPACER ── --}}
  <tr><td style="height:24px;"></td></tr>

  {{-- ── FINAL CTA ── --}}
  <tr><td style="text-align:center;">
    <div style="background:#18181B;border-radius:16px;padding:40px;border:1px solid rgba(255,255,255,0.06);">
      <p style="color:rgba(250,250,250,0.5);font-size:12px;letter-spacing:2px;text-transform:uppercase;margin:0 0 12px 0;">No esperes mas</p>
      <h3 style="color:#FAFAFA;font-size:26px;font-weight:900;text-transform:uppercase;letter-spacing:1px;margin:0 0 16px 0;">
        UNETE AL LANZAMIENTO
      </h3>
      <p style="color:rgba(250,250,250,0.55);font-size:14px;line-height:1.6;margin:0 0 28px 0;">
        Hola <strong style="color:#FAFAFA;">{{ $name ?? 'amigo/a' }}</strong>, tu trial gratuito te espera.<br>
        3 dias. Acceso total. Sin tarjeta. Sin riesgos.
      </p>
      <a href="{{ $launchUrl ?? url('/lanzamiento') }}"
         style="display:inline-block;background:#DC2626;color:white;padding:16px 52px;border-radius:50px;text-decoration:none;font-weight:900;font-size:16px;letter-spacing:1px;text-transform:uppercase;box-shadow:0 8px 32px rgba(220,38,38,0.35);margin-bottom:20px;">
        COMENZAR AHORA &#8594;
      </a>
      <p style="color:rgba(250,250,250,0.25);font-size:11px;margin:0;">
        Sin tarjeta &bull; Sin compromiso &bull; Cancela cuando quieras
      </p>
    </div>
  </td></tr>

  {{-- ── SPACER ── --}}
  <tr><td style="height:32px;"></td></tr>

  {{-- ── FOOTER ── --}}
  <tr><td style="padding:24px 0;text-align:center;border-top:1px solid rgba(255,255,255,0.06);">
    <div style="display:inline-block;background:#DC2626;width:28px;height:28px;border-radius:6px;line-height:28px;text-align:center;color:white;font-weight:bold;font-size:14px;margin-bottom:12px;">W</div>
    <p style="color:rgba(250,250,250,0.4);font-size:12px;line-height:1.5;margin:0 0 6px 0;">
      WellCore Fitness &copy; {{ date('Y') }}. Todos los derechos reservados.
    </p>
    <p style="margin:0 0 4px 0;">
      <a href="{{ url('/lanzamiento') }}" style="color:#DC2626;text-decoration:none;font-size:12px;">Ver pagina de lanzamiento</a>
      &nbsp;&bull;&nbsp;
      <a href="{{ url('/planes') }}" style="color:rgba(250,250,250,0.4);text-decoration:none;font-size:12px;">Ver planes</a>
    </p>
    <p style="color:rgba(250,250,250,0.2);font-size:10px;margin:8px 0 0 0;">
      Bucaramanga, Santander, Colombia &bull;
      <a href="{{ url('/privacidad') }}" style="color:rgba(250,250,250,0.2);text-decoration:none;">Privacidad</a>
    </p>
    @if(isset($unsubscribeUrl))
    <p style="color:rgba(250,250,250,0.15);font-size:10px;margin:8px 0 0 0;">
      <a href="{{ $unsubscribeUrl }}" style="color:rgba(250,250,250,0.2);text-decoration:none;">Cancelar suscripcion a emails de marketing</a>
    </p>
    @endif
  </td></tr>

</table>
</td></tr></table>
</body>
</html>
