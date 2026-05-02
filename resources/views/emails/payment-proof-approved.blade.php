<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"></head>
<body style="margin:0;padding:0;background-color:#09090B;font-family:Arial,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background-color:#09090B;padding:40px 20px;">
<tr><td align="center">
<table width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;">

  <!-- Logo header -->
  <tr><td style="padding:28px 0 24px 0;text-align:center;">
    <img src="{{ url('/images/logo-wellcore-email.png') }}" alt="WellCore Fit" width="200" style="max-width:200px;height:auto;display:inline-block;">
  </td></tr>

  <!-- Content card -->
  <tr><td style="background:#18181B;border-radius:12px;padding:40px;border:1px solid rgba(255,255,255,0.08);">

    <!-- Success icon -->
    <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:24px;">
      <tr>
        <td align="center">
          <div style="display:inline-block;background:rgba(34,197,94,0.15);width:56px;height:56px;border-radius:50%;line-height:56px;text-align:center;color:#22C55E;font-size:28px;">&#10003;</div>
        </td>
      </tr>
    </table>

    <h1 style="color:#FAFAFA;font-size:24px;margin:0 0 10px 0;text-align:center;">¡Tu acceso está listo, {{ $clientName }}!</h1>
    <p style="color:rgba(250,250,250,0.64);font-size:15px;line-height:1.7;margin:0 0 28px 0;text-align:center;">
      Tu comprobante de pago fue verificado y tu cuenta en WellCore Fit ya está activa. A continuación encontrarás toda la información que necesitas para comenzar.
    </p>

    <!-- Plan + Coach + Email info -->
    <table width="100%" cellpadding="0" cellspacing="0" style="background:rgba(255,255,255,0.04);border-radius:8px;margin-bottom:28px;">
      <tr>
        <td style="padding:16px 20px;border-bottom:1px solid rgba(255,255,255,0.08);">
          <span style="color:rgba(250,250,250,0.5);font-size:12px;text-transform:uppercase;letter-spacing:1px;">Plan activo</span><br>
          <span style="color:#FAFAFA;font-size:16px;font-weight:bold;">{{ $planLabel }}</span>
        </td>
      </tr>
      <tr>
        <td style="padding:16px 20px;border-bottom:1px solid rgba(255,255,255,0.08);">
          <span style="color:rgba(250,250,250,0.5);font-size:12px;text-transform:uppercase;letter-spacing:1px;">Tu Coach</span><br>
          <span style="color:#FAFAFA;font-size:16px;font-weight:bold;">{{ $coachName }}</span>
        </td>
      </tr>
      <tr>
        <td style="padding:16px 20px;">
          <span style="color:rgba(250,250,250,0.5);font-size:12px;text-transform:uppercase;letter-spacing:1px;">Tu usuario de acceso</span><br>
          <span style="color:#FAFAFA;font-size:16px;font-weight:bold;">{{ $clientEmail }}</span>
        </td>
      </tr>
    </table>

    @if($resetUrl)
    <p style="color:rgba(250,250,250,0.64);font-size:14px;line-height:1.7;margin:0 0 20px 0;">
      Para acceder por primera vez, es necesario que crees tu contraseña. Haz clic en el botón a continuación — el proceso toma menos de un minuto. El enlace es válido durante <strong style="color:#FAFAFA;">60 minutos</strong>. Si expira, puedes solicitar uno nuevo usando la opción "¿Olvidaste tu contraseña?" en el login.
    </p>
    <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:36px;">
      <tr>
        <td align="center">
          <a href="{{ $resetUrl }}" style="display:inline-block;background:#DC2626;color:white;padding:15px 36px;border-radius:8px;text-decoration:none;font-weight:bold;font-size:15px;letter-spacing:0.3px;">Crear contraseña e ingresar</a>
        </td>
      </tr>
    </table>
    @else
    <p style="color:rgba(250,250,250,0.64);font-size:14px;line-height:1.7;margin:0 0 20px 0;">
      Ingresa a la plataforma con tu correo electrónico y contraseña habitual para comenzar.
    </p>
    <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:36px;">
      <tr>
        <td align="center">
          <a href="{{ $loginUrl }}" style="display:inline-block;background:#DC2626;color:white;padding:15px 36px;border-radius:8px;text-decoration:none;font-weight:bold;font-size:15px;letter-spacing:0.3px;">Ingresar a WellCore Fit</a>
        </td>
      </tr>
    </table>
    @endif

    <!-- Divider -->
    <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:28px;">
      <tr><td style="border-top:1px solid rgba(255,255,255,0.08);font-size:0;">&nbsp;</td></tr>
    </table>

    <!-- Primeros pasos -->
    <h2 style="color:#FAFAFA;font-size:17px;font-weight:bold;margin:0 0 20px 0;letter-spacing:0.2px;">Tus primeros pasos en la plataforma</h2>

    <!-- Paso 1 -->
    <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:18px;">
      <tr>
        <td width="36" valign="top" style="padding-top:1px;">
          <div style="background:#DC2626;color:white;width:26px;height:26px;border-radius:50%;text-align:center;line-height:26px;font-size:12px;font-weight:bold;">1</div>
        </td>
        <td valign="top">
          <span style="color:#FAFAFA;font-size:14px;font-weight:bold;display:block;margin-bottom:4px;">Ingresa a la plataforma</span>
          <span style="color:rgba(250,250,250,0.60);font-size:13px;line-height:1.6;">Tu usuario es tu correo electrónico: <strong style="color:rgba(250,250,250,0.88);">{{ $clientEmail }}</strong>. Una vez dentro, tendrás acceso a tu programa personalizado, plan de entrenamiento y comunicación directa con tu coach.</span>
        </td>
      </tr>
    </table>

    <!-- Paso 2 -->
    <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:18px;">
      <tr>
        <td width="36" valign="top" style="padding-top:1px;">
          <div style="background:#DC2626;color:white;width:26px;height:26px;border-radius:50%;text-align:center;line-height:26px;font-size:12px;font-weight:bold;">2</div>
        </td>
        <td valign="top">
          <span style="color:#FAFAFA;font-size:14px;font-weight:bold;display:block;margin-bottom:4px;">Completa tu perfil en Configuración</span>
          <span style="color:rgba(250,250,250,0.60);font-size:13px;line-height:1.6;">Agrega tu foto de perfil e información personal relevante. Esto le permite a tu coach conocerte mejor desde el primer día y personalizar tu plan con mayor precisión.</span>
        </td>
      </tr>
    </table>

    <!-- Paso 3 -->
    <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:18px;">
      <tr>
        <td width="36" valign="top" style="padding-top:1px;">
          <div style="background:#DC2626;color:white;width:26px;height:26px;border-radius:50%;text-align:center;line-height:26px;font-size:12px;font-weight:bold;">3</div>
        </td>
        <td valign="top">
          <span style="color:#FAFAFA;font-size:14px;font-weight:bold;display:block;margin-bottom:4px;">Registra tus métricas corporales <span style="color:rgba(250,250,250,0.38);font-weight:normal;font-size:12px;">(recomendado)</span></span>
          <span style="color:rgba(250,250,250,0.60);font-size:13px;line-height:1.6;">Ingresa tus medidas corporales iniciales en la sección de Métricas. No es obligatorio, pero contar con este punto de partida permite hacer un seguimiento objetivo y preciso de tu evolución a lo largo de todo el proceso.</span>
        </td>
      </tr>
    </table>

    <!-- Paso 4 -->
    <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:32px;">
      <tr>
        <td width="36" valign="top" style="padding-top:1px;">
          <div style="background:#DC2626;color:white;width:26px;height:26px;border-radius:50%;text-align:center;line-height:26px;font-size:12px;font-weight:bold;">4</div>
        </td>
        <td valign="top">
          <span style="color:#FAFAFA;font-size:14px;font-weight:bold;display:block;margin-bottom:4px;">Sube tus fotos de inicio <span style="color:#DC2626;font-size:12px;font-weight:normal;">— muy importante</span></span>
          <span style="color:rgba(250,250,250,0.60);font-size:13px;line-height:1.6;">Las fotos de inicio son fundamentales para llevar un registro visual de tu transformación y para que tu coach pueda brindarte una asesoría de alto nivel. Este paso es clave para el seguimiento de tu progreso desde el primer día.</span>
        </td>
      </tr>
    </table>

    <!-- Support -->
    <table width="100%" cellpadding="0" cellspacing="0" style="background:rgba(255,255,255,0.03);border-radius:8px;">
      <tr>
        <td style="padding:16px 20px;">
          <p style="color:rgba(250,250,250,0.40);font-size:12px;text-align:center;margin:0;line-height:1.7;">
            ¿Tienes alguna pregunta o necesitas asistencia? Escríbenos a<br>
            <a href="mailto:info@wellcorefitness.com" style="color:#DC2626;text-decoration:none;font-weight:bold;">info@wellcorefitness.com</a>
          </p>
        </td>
      </tr>
    </table>

  </td></tr>

  <!-- Footer -->
  <tr><td style="padding:24px 0 0 0;text-align:center;color:rgba(250,250,250,0.30);font-size:12px;line-height:1.8;">
    WellCore Fit &copy; 2026. Todos los derechos reservados.<br>
    <a href="https://wellcorefitness.com" style="color:rgba(220,38,38,0.7);text-decoration:none;">wellcorefitness.com</a>
  </td></tr>

</table>
</td></tr></table>
</body></html>
