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
    <h1 style="color:#FAFAFA;font-size:24px;margin:0 0 8px 0;">Es hora de tu Check-in!</h1>
    <p style="color:rgba(250,250,250,0.64);font-size:15px;line-height:1.6;margin:0 0 24px 0;">
      Hola {{ $clientName }}, han pasado <strong style="color:#DC2626;">{{ $daysSinceLastCheckin }} dias</strong> desde tu ultimo check-in. Tu coach esta esperando saber de ti.
    </p>

    <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:24px;">
      <tr>
        <td style="background:rgba(220,38,38,0.1);border-radius:8px;padding:20px;text-align:center;">
          <p style="color:rgba(250,250,250,0.64);font-size:13px;margin:0 0 4px 0;">Dias sin check-in</p>
          <p style="color:#DC2626;font-size:36px;font-weight:bold;margin:0;">{{ $daysSinceLastCheckin }}</p>
        </td>
      </tr>
    </table>

    <p style="color:#FAFAFA;font-size:15px;font-weight:bold;margin:0 0 12px 0;">Por que es importante tu check-in?</p>
    <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:24px;">
      <tr>
        <td style="padding:10px 0;color:rgba(250,250,250,0.64);font-size:14px;line-height:1.5;">
          <span style="color:#DC2626;margin-right:8px;">&#9679;</span> Tu coach ajusta tu plan segun tu progreso real
        </td>
      </tr>
      <tr>
        <td style="padding:10px 0;color:rgba(250,250,250,0.64);font-size:14px;line-height:1.5;">
          <span style="color:#DC2626;margin-right:8px;">&#9679;</span> Detectamos a tiempo si algo no funciona
        </td>
      </tr>
      <tr>
        <td style="padding:10px 0;color:rgba(250,250,250,0.64);font-size:14px;line-height:1.5;">
          <span style="color:#DC2626;margin-right:8px;">&#9679;</span> Mantienes la consistencia que genera resultados
        </td>
      </tr>
    </table>

    <table width="100%" cellpadding="0" cellspacing="0" style="background:rgba(255,255,255,0.04);border-radius:8px;padding:16px;margin-bottom:28px;">
      <tr>
        <td style="padding:16px;">
          <p style="color:rgba(250,250,250,0.64);font-size:14px;line-height:1.6;margin:0;font-style:italic;">
            "La consistencia no es hacer todo perfecto, es seguir apareciendo. Tu check-in toma solo 2 minutos."
          </p>
        </td>
      </tr>
    </table>

    <table width="100%" cellpadding="0" cellspacing="0">
      <tr>
        <td align="center">
          <a href="https://wellcorefitness.com/dashboard/checkin" style="display:inline-block;background:#DC2626;color:white;padding:12px 28px;border-radius:8px;text-decoration:none;font-weight:bold;font-size:14px;">Hacer Mi Check-in Ahora</a>
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
