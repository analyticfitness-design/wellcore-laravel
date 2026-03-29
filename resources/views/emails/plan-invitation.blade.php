<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="dark">
    <title>Invitacion WellCore - Plan {{ $plan['name'] }}</title>
</head>
<body style="margin:0;padding:0;background-color:#09090B;font-family:Arial,Helvetica,sans-serif;-webkit-font-smoothing:antialiased;">
<table width="100%" cellpadding="0" cellspacing="0" style="background-color:#09090B;padding:40px 16px;">
<tr><td align="center">
<table width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;">

  {{-- ═══ LOGO HEADER ═══ --}}
  <tr><td style="padding:0 0 32px 0;text-align:center;">
    <img src="https://wellcorefitness.com/images/logo-light.png" alt="WellCore Fitness" width="180" style="display:inline-block;height:auto;max-width:180px;" />
  </td></tr>

  {{-- ═══ MAIN CARD ═══ --}}
  <tr><td>
    <div style="background:#18181B;border-radius:16px;overflow:hidden;border:1px solid rgba(255,255,255,0.06);">

      {{-- Accent strip --}}
      @if($plan['isPremium'])
      <div style="height:3px;background:linear-gradient(90deg,transparent,#DC2626 30%,#FF6B35 70%,transparent);"></div>
      @else
      <div style="height:3px;background:linear-gradient(90deg,transparent,#DC2626,transparent);"></div>
      @endif

      <div style="padding:40px 36px;">

        {{-- Greeting --}}
        <p style="color:rgba(250,250,250,0.5);font-size:13px;letter-spacing:1px;text-transform:uppercase;margin:0 0 8px 0;">Invitacion exclusiva</p>
        <h1 style="color:#FAFAFA;font-size:28px;font-weight:900;margin:0 0 6px 0;line-height:1.2;">
          Hola {{ $recipientName }},
        </h1>
        <p style="color:rgba(250,250,250,0.64);font-size:15px;line-height:1.6;margin:0 0 28px 0;">
          {!! $plan['intro'] !!}
        </p>

        {{-- ═══ PLAN CARD ═══ --}}
        <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:28px;">
          <tr><td style="background:linear-gradient(135deg,rgba(220,38,38,{{ $plan['isPremium'] ? '0.15' : '0.12' }}) 0%,rgba({{ $plan['isPremium'] ? '255,107,53,0.06' : '220,38,38,0.04' }}) 100%);border:1px solid rgba(220,38,38,{{ $plan['isPremium'] ? '0.3' : '0.25' }});border-radius:12px;padding:28px 24px;">

            {{-- Badge --}}
            <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:16px;">
              <tr>
                <td>
                  <span style="display:inline-block;background:rgba(220,38,38,0.2);border:1px solid rgba(220,38,38,0.4);border-radius:50px;padding:4px 14px;font-size:10px;font-weight:bold;letter-spacing:2px;text-transform:uppercase;color:#DC2626;">{!! $plan['badge'] !!}</span>
                </td>
                <td style="text-align:right;">
                  <span style="color:rgba(250,250,250,0.4);font-size:11px;letter-spacing:1px;">{{ $plan['badgeRight'] }}</span>
                </td>
              </tr>
            </table>

            {{-- Plan name + price --}}
            <h2 style="color:#FAFAFA;font-size:32px;font-weight:900;margin:0 0 4px 0;letter-spacing:2px;">{{ $plan['name'] }}</h2>
            <table cellpadding="0" cellspacing="0" style="margin-bottom:20px;">
              <tr>
                <td style="vertical-align:baseline;">
                  <span style="color:#DC2626;font-size:{{ strlen($plan['price']) > 12 ? '28' : '36' }}px;font-weight:900;">{{ $plan['price'] }}</span>
                </td>
                <td style="vertical-align:baseline;padding-left:8px;">
                  <span style="color:rgba(250,250,250,0.5);font-size:14px;">{{ $plan['priceSuffix'] }}</span><br>
                  <span style="color:rgba(250,250,250,0.35);font-size:12px;">{{ $plan['priceUsd'] }}</span>
                </td>
              </tr>
            </table>

            {{-- Divider --}}
            <div style="height:1px;background:rgba(255,255,255,0.08);margin-bottom:20px;"></div>

            {{-- Features --}}
            <p style="color:#FAFAFA;font-size:13px;font-weight:bold;text-transform:uppercase;letter-spacing:1px;margin:0 0 14px 0;">{{ $plan['isPremium'] ? 'Todo incluido:' : 'Que incluye:' }}</p>
            <table width="100%" cellpadding="0" cellspacing="0">
              @foreach($plan['features'] as $feature)
              <tr><td style="padding:6px 0;color:rgba(250,250,250,0.72);font-size:14px;">
                <span style="color:#DC2626;font-weight:bold;margin-right:8px;">&#10003;</span> {!! $feature !!}
              </td></tr>
              @endforeach
            </table>

          </td></tr>
        </table>

        {{-- ═══ TU RUTA DE INSCRIPCION ═══ --}}
        <h3 style="color:#FAFAFA;font-size:16px;font-weight:bold;margin:0 0 4px 0;">Tu ruta de inscripcion:</h3>
        <p style="color:rgba(250,250,250,0.4);font-size:12px;margin:0 0 16px 0;">Sigue estos pasos — es rapido y seguro.</p>
        <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:28px;">
          @foreach($plan['steps'] as $i => $step)
          <tr>
            <td style="padding:12px 0;{{ $i < count($plan['steps']) - 1 ? 'border-bottom:1px solid rgba(255,255,255,0.06);' : '' }}">
              <table cellpadding="0" cellspacing="0"><tr>
                <td style="vertical-align:top;padding-right:14px;">
                  <div style="display:inline-block;background:#DC2626;width:28px;height:28px;border-radius:50%;line-height:28px;text-align:center;color:white;font-weight:bold;font-size:13px;">{{ $i + 1 }}</div>
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

        {{-- ═══ METODO DE SEGUIMIENTO ═══ --}}
        <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:{{ $plan['locationNote'] ? '24' : '32' }}px;">
          <tr><td style="background:rgba(255,255,255,0.03);border-radius:10px;padding:20px 22px;border:1px solid rgba(255,255,255,0.06);">
            <p style="color:#DC2626;font-size:11px;font-weight:bold;letter-spacing:2px;text-transform:uppercase;margin:0 0 10px 0;">&#9889; Metodo de seguimiento{{ $plan['isPremium'] ? ' Elite' : '' }}</p>
            <p style="color:rgba(250,250,250,0.64);font-size:13px;line-height:1.7;margin:0;">
              {!! $plan['followUp'] !!}
            </p>
          </td></tr>
        </table>

        {{-- Location note (Presencial only) --}}
        @if($plan['locationNote'])
        <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:28px;">
          <tr><td style="background:rgba(220,38,38,0.06);border-radius:8px;padding:14px 18px;border:1px solid rgba(220,38,38,0.15);">
            <p style="color:rgba(250,250,250,0.64);font-size:13px;margin:0;line-height:1.5;">
              {!! $plan['locationNote'] !!}
            </p>
          </td></tr>
        </table>
        @endif

        {{-- ═══ CTA BUTTON ═══ --}}
        <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:20px;">
          <tr><td align="center">
            <a href="{{ $plan['ctaUrl'] }}"
               style="display:inline-block;background:{{ $plan['isPremium'] ? 'linear-gradient(135deg,#DC2626,#B91C1C)' : '#DC2626' }};color:#ffffff;padding:16px 40px;border-radius:10px;text-decoration:none;font-weight:bold;font-size:16px;letter-spacing:0.5px;box-shadow:0 4px 14px rgba(220,38,38,0.4);">
              {{ $plan['ctaText'] }} &rarr;
            </a>
          </td></tr>
        </table>

        <p style="color:rgba(250,250,250,0.35);font-size:12px;text-align:center;margin:0;">
          {{ $plan['billingNote'] }}
        </p>

      </div>
    </div>
  </td></tr>

  {{-- ═══ REFERENCIA PERMANENTE ═══ --}}
  <tr><td>
    <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:24px;">
      <tr><td style="background:#18181B;border-radius:12px;padding:24px;border:1px solid rgba(255,255,255,0.06);">
        <p style="color:#DC2626;font-size:11px;font-weight:bold;letter-spacing:2px;text-transform:uppercase;margin:0 0 16px 0;">&#128278; Guarda este correo como referencia</p>
        <table width="100%" cellpadding="0" cellspacing="0">
          <tr>
            <td style="padding:6px 0;color:rgba(250,250,250,0.5);font-size:13px;width:120px;vertical-align:top;">Tu plan:</td>
            <td style="padding:6px 0;color:#FAFAFA;font-size:13px;font-weight:bold;">{{ $plan['name'] }} — {{ $plan['price'] }} {{ $plan['priceSuffix'] }}</td>
          </tr>
          <tr>
            <td style="padding:6px 0;color:rgba(250,250,250,0.5);font-size:13px;vertical-align:top;">Link de pago:</td>
            <td style="padding:6px 0;font-size:13px;"><a href="{{ $plan['ctaUrl'] }}" style="color:#DC2626;text-decoration:none;word-break:break-all;">{{ $plan['ctaUrl'] }}</a></td>
          </tr>
          <tr>
            <td style="padding:6px 0;color:rgba(250,250,250,0.5);font-size:13px;vertical-align:top;">Login:</td>
            <td style="padding:6px 0;font-size:13px;"><a href="{{ $plan['loginUrl'] }}" style="color:#DC2626;text-decoration:none;">wellcorefitness.com/login</a></td>
          </tr>
          <tr>
            <td style="padding:6px 0;color:rgba(250,250,250,0.5);font-size:13px;vertical-align:top;">Soporte:</td>
            <td style="padding:6px 0;font-size:13px;"><a href="mailto:info@wellcorefitness.com" style="color:#DC2626;text-decoration:none;">info@wellcorefitness.com</a></td>
          </tr>
        </table>
        <p style="color:rgba(250,250,250,0.3);font-size:11px;margin:12px 0 0 0;line-height:1.5;">
          Si pierdes conexion o cierras la pagina, vuelve a este correo y usa los links de arriba para retomar donde lo dejaste.
        </p>
      </td></tr>
    </table>
  </td></tr>

  {{-- ═══ TRUST BAR ═══ --}}
  <tr><td style="padding:24px 0;text-align:center;">
    <table width="100%" cellpadding="0" cellspacing="0">
      <tr>
        <td style="text-align:center;padding:0 8px;">
          <p style="color:#DC2626;font-size:20px;font-weight:900;margin:0;">100+</p>
          <p style="color:rgba(250,250,250,0.4);font-size:11px;margin:2px 0 0 0;">Clientes activos</p>
        </td>
        <td style="text-align:center;padding:0 8px;">
          <p style="color:#DC2626;font-size:20px;font-weight:900;margin:0;">4.9/5</p>
          <p style="color:rgba(250,250,250,0.4);font-size:11px;margin:2px 0 0 0;">Satisfaccion</p>
        </td>
        <td style="text-align:center;padding:0 8px;">
          <p style="color:#DC2626;font-size:20px;font-weight:900;margin:0;">LATAM</p>
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
      <a href="https://wellcorefitness.com" style="color:#DC2626;text-decoration:none;">wellcorefitness.com</a>
      &nbsp;&middot;&nbsp;
      <a href="https://instagram.com/wellcore.fitness" style="color:#DC2626;text-decoration:none;">@wellcore.fitness</a>
    </p>
    <p style="color:rgba(250,250,250,0.2);font-size:10px;margin:12px 0 0 0;">
      Recibiste este correo porque alguien de WellCore te envio una invitacion.<br>
      Si no te interesa, simplemente ignora este mensaje.
    </p>
  </td></tr>

</table>
</td></tr></table>
</body>
</html>
