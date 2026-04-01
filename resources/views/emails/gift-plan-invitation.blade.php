<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="dark">
    <title>Regalo WellCore - Plan {{ $plan['name'] }}</title>
</head>
<body style="margin:0;padding:0;background-color:#09090B;font-family:Arial,Helvetica,sans-serif;-webkit-font-smoothing:antialiased;">
<table width="100%" cellpadding="0" cellspacing="0" style="background-color:#09090B;padding:40px 16px;">
<tr><td align="center">
<table width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;">

  {{-- ═══ LOGO HEADER ═══ --}}
  <tr><td style="padding:0 0 32px 0;text-align:center;">
    <img src="https://wellcorefitness.com/images/logo-light.png" alt="WellCore Fitness" width="180" style="display:inline-block;height:auto;max-width:180px;" />
  </td></tr>

  {{-- ═══ GIFT BANNER ═══ --}}
  <tr><td>
    <div style="background:linear-gradient(135deg,rgba(16,185,129,0.15) 0%,rgba(16,185,129,0.05) 100%);border:1px solid rgba(16,185,129,0.3);border-radius:12px;padding:24px 28px;margin-bottom:20px;text-align:center;">
      <p style="font-size:36px;margin:0 0 8px 0;">&#127873;</p>
      <h2 style="color:#10B981;font-size:20px;font-weight:900;margin:0 0 6px 0;letter-spacing:1px;">TIENES UN REGALO</h2>
      <p style="color:rgba(250,250,250,0.64);font-size:15px;margin:0;">
        <strong style="color:#FAFAFA;">{{ $gifterName }}</strong> te ha regalado el plan <strong style="color:#10B981;">{{ $plan['name'] }}</strong> de WellCore Fitness
      </p>
    </div>
  </td></tr>

  {{-- ═══ MAIN CARD ═══ --}}
  <tr><td>
    <div style="background:#18181B;border-radius:16px;overflow:hidden;border:1px solid rgba(255,255,255,0.06);">

      {{-- Accent strip — emerald for gift --}}
      <div style="height:3px;background:linear-gradient(90deg,transparent,#10B981,transparent);"></div>

      <div style="padding:40px 36px;">

        {{-- Greeting --}}
        <p style="color:rgba(250,250,250,0.5);font-size:13px;letter-spacing:1px;text-transform:uppercase;margin:0 0 8px 0;">Regalo especial</p>
        <h1 style="color:#FAFAFA;font-size:28px;font-weight:900;margin:0 0 6px 0;line-height:1.2;">
          Hola {{ $recipientName }},
        </h1>
        <p style="color:rgba(250,250,250,0.64);font-size:15px;line-height:1.6;margin:0 0 28px 0;">
          {{ $gifterName }} quiere que comiences tu transformacion fisica con WellCore Fitness y te ha regalado acceso al plan <strong style="color:#10B981;">{{ $plan['name'] }}</strong>. Todo esta listo para que des el primer paso.
        </p>

        {{-- ═══ PERSONAL MESSAGE (if exists) ═══ --}}
        @if($giftMessage)
        <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:28px;">
          <tr><td style="background:rgba(16,185,129,0.06);border-radius:12px;padding:22px 24px;border-left:3px solid #10B981;">
            <p style="color:rgba(250,250,250,0.45);font-size:11px;font-weight:bold;letter-spacing:2px;text-transform:uppercase;margin:0 0 10px 0;">&#128172; Mensaje de {{ $gifterName }}</p>
            <p style="color:rgba(250,250,250,0.8);font-size:15px;line-height:1.7;margin:0;font-style:italic;">
              &ldquo;{{ $giftMessage }}&rdquo;
            </p>
          </td></tr>
        </table>
        @endif

        {{-- ═══ PLAN CARD ═══ --}}
        <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:28px;">
          <tr><td style="background:linear-gradient(135deg,rgba(16,185,129,0.12) 0%,rgba(16,185,129,0.04) 100%);border:1px solid rgba(16,185,129,0.25);border-radius:12px;padding:28px 24px;">

            {{-- Badge --}}
            <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:16px;">
              <tr>
                <td>
                  <span style="display:inline-block;background:rgba(16,185,129,0.2);border:1px solid rgba(16,185,129,0.4);border-radius:50px;padding:4px 14px;font-size:10px;font-weight:bold;letter-spacing:2px;text-transform:uppercase;color:#10B981;">{{ $plan['badge'] }}</span>
                </td>
                <td style="text-align:right;">
                  <span style="display:inline-block;background:rgba(16,185,129,0.15);border:1px solid rgba(16,185,129,0.3);border-radius:50px;padding:4px 14px;font-size:10px;font-weight:bold;letter-spacing:2px;text-transform:uppercase;color:#10B981;">&#127873; Regalo</span>
                </td>
              </tr>
            </table>

            {{-- Plan name + price --}}
            <h2 style="color:#FAFAFA;font-size:32px;font-weight:900;margin:0 0 4px 0;letter-spacing:2px;">{{ $plan['name'] }}</h2>
            <table cellpadding="0" cellspacing="0" style="margin-bottom:20px;">
              <tr>
                <td style="vertical-align:baseline;">
                  <span style="color:#10B981;font-size:{{ strlen($plan['price']) > 12 ? '28' : '36' }}px;font-weight:900;text-decoration:line-through;opacity:0.6;">{{ $plan['price'] }}</span>
                </td>
                <td style="vertical-align:baseline;padding-left:12px;">
                  <span style="color:#10B981;font-size:24px;font-weight:900;">GRATIS</span>
                </td>
              </tr>
            </table>

            {{-- Divider --}}
            <div style="height:1px;background:rgba(255,255,255,0.08);margin-bottom:20px;"></div>

            {{-- Features --}}
            <p style="color:#FAFAFA;font-size:13px;font-weight:bold;text-transform:uppercase;letter-spacing:1px;margin:0 0 14px 0;">Tu regalo incluye:</p>
            <table width="100%" cellpadding="0" cellspacing="0">
              @foreach($plan['features'] as $feature)
              <tr><td style="padding:6px 0;color:rgba(250,250,250,0.72);font-size:14px;">
                <span style="color:#10B981;font-weight:bold;margin-right:8px;">&#10003;</span> {!! $feature !!}
              </td></tr>
              @endforeach
            </table>

          </td></tr>
        </table>

        {{-- ═══ COMO ACTIVAR TU REGALO ═══ --}}
        <h3 style="color:#FAFAFA;font-size:16px;font-weight:bold;margin:0 0 4px 0;">Como activar tu regalo:</h3>
        <p style="color:rgba(250,250,250,0.4);font-size:12px;margin:0 0 16px 0;">Es rapido y sencillo — solo sigue estos pasos.</p>
        <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:28px;">
          @foreach($plan['steps'] as $i => $step)
          <tr>
            <td style="padding:12px 0;{{ $i < count($plan['steps']) - 1 ? 'border-bottom:1px solid rgba(255,255,255,0.06);' : '' }}">
              <table cellpadding="0" cellspacing="0"><tr>
                <td style="vertical-align:top;padding-right:14px;">
                  <div style="display:inline-block;background:#10B981;width:28px;height:28px;border-radius:50%;line-height:28px;text-align:center;color:white;font-weight:bold;font-size:13px;">{{ $i + 1 }}</div>
                </td>
                <td style="vertical-align:top;">
                  <p style="color:#FAFAFA;font-size:14px;font-weight:bold;margin:0 0 2px 0;">{{ $step['title'] }}</p>
                  <p style="color:rgba(250,250,250,0.5);font-size:13px;margin:0;line-height:1.5;">{{ $step['desc'] }}</p>
                </td>
              </tr></table>
            </td>
          </tr>
          @endforeach
        </table>

        {{-- ═══ CTA BUTTON ═══ --}}
        <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:20px;">
          <tr><td align="center">
            <a href="{{ $plan['ctaUrl'] }}"
               style="display:inline-block;background:#10B981;color:#ffffff;padding:16px 40px;border-radius:10px;text-decoration:none;font-weight:bold;font-size:16px;letter-spacing:0.5px;box-shadow:0 4px 14px rgba(16,185,129,0.4);">
              &#127873; {{ $plan['ctaText'] }} &rarr;
            </a>
          </td></tr>
        </table>

        <p style="color:rgba(250,250,250,0.35);font-size:12px;text-align:center;margin:0;">
          {{ $plan['billingNote'] }}
        </p>

      </div>
    </div>
  </td></tr>

  {{-- ═══ REFERENCIA ═══ --}}
  <tr><td>
    <table width="100%" cellpadding="0" cellspacing="0" style="margin-top:20px;margin-bottom:24px;">
      <tr><td style="background:#18181B;border-radius:12px;padding:24px;border:1px solid rgba(255,255,255,0.06);">
        <p style="color:#10B981;font-size:11px;font-weight:bold;letter-spacing:2px;text-transform:uppercase;margin:0 0 16px 0;">&#128278; Guarda este correo como referencia</p>
        <table width="100%" cellpadding="0" cellspacing="0">
          <tr>
            <td style="padding:6px 0;color:rgba(250,250,250,0.5);font-size:13px;width:120px;vertical-align:top;">Tu plan:</td>
            <td style="padding:6px 0;color:#FAFAFA;font-size:13px;font-weight:bold;">{{ $plan['name'] }} — {{ $plan['price'] }} {{ $plan['priceSuffix'] }}</td>
          </tr>
          <tr>
            <td style="padding:6px 0;color:rgba(250,250,250,0.5);font-size:13px;vertical-align:top;">Regalado por:</td>
            <td style="padding:6px 0;color:#FAFAFA;font-size:13px;font-weight:bold;">{{ $gifterName }}</td>
          </tr>
          <tr>
            <td style="padding:6px 0;color:rgba(250,250,250,0.5);font-size:13px;vertical-align:top;">Login:</td>
            <td style="padding:6px 0;font-size:13px;"><a href="{{ $plan['loginUrl'] }}" style="color:#10B981;text-decoration:none;">wellcorefitness.com/login</a></td>
          </tr>
          <tr>
            <td style="padding:6px 0;color:rgba(250,250,250,0.5);font-size:13px;vertical-align:top;">Soporte:</td>
            <td style="padding:6px 0;font-size:13px;"><a href="mailto:info@wellcorefitness.com" style="color:#10B981;text-decoration:none;">info@wellcorefitness.com</a></td>
          </tr>
        </table>
      </td></tr>
    </table>
  </td></tr>

  {{-- ═══ TRUST BAR ═══ --}}
  <tr><td style="padding:24px 0;text-align:center;">
    <table width="100%" cellpadding="0" cellspacing="0">
      <tr>
        <td style="text-align:center;padding:0 8px;">
          <p style="color:#10B981;font-size:20px;font-weight:900;margin:0;">100+</p>
          <p style="color:rgba(250,250,250,0.4);font-size:11px;margin:2px 0 0 0;">Clientes activos</p>
        </td>
        <td style="text-align:center;padding:0 8px;">
          <p style="color:#10B981;font-size:20px;font-weight:900;margin:0;">4.9/5</p>
          <p style="color:rgba(250,250,250,0.4);font-size:11px;margin:2px 0 0 0;">Satisfaccion</p>
        </td>
        <td style="text-align:center;padding:0 8px;">
          <p style="color:#10B981;font-size:20px;font-weight:900;margin:0;">LATAM</p>
          <p style="color:rgba(250,250,250,0.4);font-size:11px;margin:2px 0 0 0;">Cobertura</p>
        </td>
      </tr>
    </table>
  </td></tr>

  {{-- ═══ FOOTER ═══ --}}
  <tr><td style="padding:16px 0 0 0;text-align:center;border-top:1px solid rgba(255,255,255,0.06);">
    <p style="color:rgba(250,250,250,0.3);font-size:11px;line-height:1.6;margin:0;">
      WellCore Fitness &copy; {{ date('Y') }}. Todos los derechos reservados.<br>
      Coaching fitness basado en ciencia para LATAM.<br>
      <a href="https://wellcorefitness.com" style="color:#10B981;text-decoration:none;">wellcorefitness.com</a>
      &nbsp;&middot;&nbsp;
      <a href="https://instagram.com/wellcore.fitness" style="color:#10B981;text-decoration:none;">@wellcore.fitness</a>
    </p>
    <p style="color:rgba(250,250,250,0.2);font-size:10px;margin:12px 0 0 0;">
      Recibiste este correo porque {{ $gifterName }} te envio un regalo a traves de WellCore Fitness.<br>
      Si no te interesa, simplemente ignora este mensaje.
    </p>
  </td></tr>

</table>
</td></tr></table>
</body>
</html>
