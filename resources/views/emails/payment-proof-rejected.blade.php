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
    <h1 style="color:#FAFAFA;font-size:22px;margin:0 0 8px 0;">Comprobante de pago revisado</h1>
    <p style="color:rgba(250,250,250,0.64);font-size:15px;line-height:1.6;margin:0 0 28px 0;">
      Hola {{ $coachName }}, te informamos sobre el comprobante de pago que fue enviado.
    </p>

    <!-- Client + Plan info -->
    <table width="100%" cellpadding="0" cellspacing="0" style="background:rgba(255,255,255,0.04);border-radius:8px;margin-bottom:24px;">
      <tr>
        <td style="padding:16px 20px;border-bottom:1px solid rgba(255,255,255,0.08);">
          <span style="color:rgba(250,250,250,0.64);font-size:13px;text-transform:uppercase;letter-spacing:1px;">Cliente</span><br>
          <span style="color:#FAFAFA;font-size:15px;font-weight:bold;">{{ $clientEmail }}</span>
        </td>
      </tr>
      <tr>
        <td style="padding:16px 20px;">
          <span style="color:rgba(250,250,250,0.64);font-size:13px;text-transform:uppercase;letter-spacing:1px;">Plan</span><br>
          <span style="color:#FAFAFA;font-size:15px;font-weight:bold;">{{ $planLabel }}</span>
        </td>
      </tr>
    </table>

    <!-- Rejection reason -->
    <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:28px;">
      <tr>
        <td style="background:rgba(220,38,38,0.08);border-radius:8px;padding:16px 20px;border-left:3px solid #DC2626;">
          <p style="color:#DC2626;font-size:13px;font-weight:bold;margin:0 0 6px 0;text-transform:uppercase;letter-spacing:1px;">Motivo del rechazo</p>
          <p style="color:rgba(250,250,250,0.80);font-size:14px;line-height:1.6;margin:0;">{{ $reason }}</p>
        </td>
      </tr>
    </table>

    <p style="color:rgba(250,250,250,0.64);font-size:14px;line-height:1.6;margin:0 0 28px 0;">
      Puedes subir un nuevo comprobante desde tu perfil en la plataforma WellCore o contactar al equipo de soporte si necesitas ayuda.
    </p>

    <!-- CTA button -->
    <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:16px;">
      <tr>
        <td align="center">
          <a href="{{ url('/coach/profile') }}" style="display:inline-block;background:#DC2626;color:white;padding:14px 32px;border-radius:8px;text-decoration:none;font-weight:bold;font-size:15px;">Ir a mi perfil</a>
        </td>
      </tr>
    </table>

    <p style="color:rgba(250,250,250,0.4);font-size:12px;text-align:center;margin:0;">
      Soporte: <a href="mailto:soporte@wellcorefitness.com" style="color:#DC2626;text-decoration:none;">soporte@wellcorefitness.com</a>
    </p>
  </td></tr>
  <!-- Footer -->
  <tr><td style="padding:20px 0;text-align:center;color:rgba(250,250,250,0.4);font-size:12px;">
    WellCore Fitness &copy; 2026. Todos los derechos reservados.<br>
    <a href="https://wellcorefitness.com" style="color:#DC2626;text-decoration:none;">wellcorefitness.com</a>
  </td></tr>
</table>
</td></tr></table>
</body></html>
