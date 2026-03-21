<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"></head>
<body style="margin:0;padding:0;background-color:#09090B;font-family:Arial,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background-color:#09090B;padding:40px 20px;">
<tr><td align="center">
<table width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;">
  <!-- Logo header -->
  <tr><td style="padding:20px 0;text-align:center;">
    <div style="display:inline-block;background:#DC2626;width:40px;height:40px;border-radius:8px;line-height:40px;text-align:center;color:white;font-weight:bold;font-size:20px;">W</div>
    <span style="color:#FAFAFA;font-size:20px;font-weight:bold;letter-spacing:3px;margin-left:10px;vertical-align:middle;">WELLCORE</span>
  </td></tr>
  <!-- Content card -->
  <tr><td style="background:#18181B;border-radius:12px;padding:40px;border:1px solid rgba(255,255,255,0.08);">
    <h1 style="color:#FAFAFA;font-size:24px;margin:0 0 8px 0;">Bienvenido a WellCore, {{ $clientName }}!</h1>
    <p style="color:rgba(250,250,250,0.64);font-size:15px;line-height:1.6;margin:0 0 24px 0;">
      Estamos emocionados de que formes parte de la comunidad WellCore Fitness. Tu viaje hacia una mejor version de ti mismo comienza hoy.
    </p>

    <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:24px;">
      <tr>
        <td style="background:rgba(220,38,38,0.1);border-radius:8px;padding:16px;border-left:3px solid #DC2626;">
          <p style="color:#DC2626;font-size:13px;font-weight:bold;margin:0 0 4px 0;text-transform:uppercase;letter-spacing:1px;">Tu Plan</p>
          <p style="color:#FAFAFA;font-size:18px;font-weight:bold;margin:0;">{{ $planName }}</p>
        </td>
      </tr>
    </table>

    <p style="color:#FAFAFA;font-size:15px;font-weight:bold;margin:0 0 12px 0;">Que puedes esperar:</p>
    <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:24px;">
      <tr>
        <td style="padding:8px 0;border-bottom:1px solid rgba(255,255,255,0.08);">
          <span style="color:#DC2626;font-size:16px;font-weight:bold;margin-right:10px;">01</span>
          <span style="color:rgba(250,250,250,0.64);font-size:14px;">Plan de entrenamiento personalizado para tus objetivos</span>
        </td>
      </tr>
      <tr>
        <td style="padding:8px 0;border-bottom:1px solid rgba(255,255,255,0.08);">
          <span style="color:#DC2626;font-size:16px;font-weight:bold;margin-right:10px;">02</span>
          <span style="color:rgba(250,250,250,0.64);font-size:14px;">Seguimiento semanal con tu coach asignado</span>
        </td>
      </tr>
      <tr>
        <td style="padding:8px 0;border-bottom:1px solid rgba(255,255,255,0.08);">
          <span style="color:#DC2626;font-size:16px;font-weight:bold;margin-right:10px;">03</span>
          <span style="color:rgba(250,250,250,0.64);font-size:14px;">Acceso a la plataforma con metricas y progreso en tiempo real</span>
        </td>
      </tr>
    </table>

    <p style="color:#FAFAFA;font-size:15px;font-weight:bold;margin:0 0 8px 0;">Tu Coach:</p>
    <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:28px;">
      <tr>
        <td style="background:rgba(255,255,255,0.04);border-radius:8px;padding:16px;">
          <span style="display:inline-block;background:#DC2626;width:36px;height:36px;border-radius:50%;line-height:36px;text-align:center;color:white;font-weight:bold;font-size:14px;vertical-align:middle;">{{ strtoupper(substr($coachName, 0, 1)) }}</span>
          <span style="color:#FAFAFA;font-size:15px;font-weight:bold;margin-left:12px;vertical-align:middle;">{{ $coachName }}</span>
        </td>
      </tr>
    </table>

    <p style="color:rgba(250,250,250,0.64);font-size:14px;line-height:1.6;margin:0 0 8px 0;">
      <strong style="color:#FAFAFA;">Proximos pasos:</strong> Completa tu perfil y realiza tu primer check-in para que tu coach pueda crear tu plan personalizado.
    </p>

    <table width="100%" cellpadding="0" cellspacing="0" style="margin-top:24px;">
      <tr>
        <td align="center">
          <a href="https://wellcorefitness.com/dashboard" style="display:inline-block;background:#DC2626;color:white;padding:12px 28px;border-radius:8px;text-decoration:none;font-weight:bold;font-size:14px;">Ir a Mi Dashboard</a>
        </td>
      </tr>
    </table>
  </td></tr>
  <!-- Footer -->
  <tr><td style="padding:20px 0;text-align:center;color:rgba(250,250,250,0.4);font-size:12px;">
    WellCore Fitness &copy; 2026. Todos los derechos reservados.<br>
    <a href="https://wellcorefitness.com" style="color:#DC2626;text-decoration:none;">wellcorefitness.com</a>
  </td></tr>
</table>
</td></tr></table>
</body></html>
