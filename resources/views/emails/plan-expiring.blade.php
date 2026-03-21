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
    <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:24px;">
      <tr>
        <td align="center">
          <div style="display:inline-block;background:rgba(234,179,8,0.15);width:56px;height:56px;border-radius:50%;line-height:56px;text-align:center;color:#EAB308;font-size:28px;">&#9888;</div>
        </td>
      </tr>
    </table>

    <h1 style="color:#FAFAFA;font-size:24px;margin:0 0 8px 0;text-align:center;">Tu Plan Expira Pronto</h1>
    <p style="color:rgba(250,250,250,0.64);font-size:15px;line-height:1.6;margin:0 0 24px 0;text-align:center;">
      Hola {{ $clientName }}, tu plan esta por vencer. No pierdas tu progreso.
    </p>

    <table width="100%" cellpadding="0" cellspacing="0" style="background:rgba(255,255,255,0.04);border-radius:8px;margin-bottom:24px;">
      <tr>
        <td style="padding:16px 20px;border-bottom:1px solid rgba(255,255,255,0.08);">
          <span style="color:rgba(250,250,250,0.64);font-size:13px;">Plan Actual</span><br>
          <span style="color:#FAFAFA;font-size:15px;font-weight:bold;">{{ $planName }}</span>
        </td>
      </tr>
      <tr>
        <td style="padding:16px 20px;border-bottom:1px solid rgba(255,255,255,0.08);">
          <span style="color:rgba(250,250,250,0.64);font-size:13px;">Fecha de Expiracion</span><br>
          <span style="color:#EAB308;font-size:15px;font-weight:bold;">{{ $expiryDate }}</span>
        </td>
      </tr>
      <tr>
        <td style="padding:16px 20px;">
          <span style="color:rgba(250,250,250,0.64);font-size:13px;">Renovacion</span><br>
          <span style="color:#FAFAFA;font-size:22px;font-weight:bold;">{{ $renewalAmount }}</span>
        </td>
      </tr>
    </table>

    <p style="color:#FAFAFA;font-size:15px;font-weight:bold;margin:0 0 12px 0;">Lo que perderas si no renuevas:</p>
    <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:24px;">
      <tr>
        <td style="padding:8px 0;color:rgba(250,250,250,0.64);font-size:14px;">
          <span style="color:#DC2626;margin-right:8px;">&#10005;</span> Acceso a tu plan de entrenamiento personalizado
        </td>
      </tr>
      <tr>
        <td style="padding:8px 0;color:rgba(250,250,250,0.64);font-size:14px;">
          <span style="color:#DC2626;margin-right:8px;">&#10005;</span> Seguimiento 1-a-1 con tu coach
        </td>
      </tr>
      <tr>
        <td style="padding:8px 0;color:rgba(250,250,250,0.64);font-size:14px;">
          <span style="color:#DC2626;margin-right:8px;">&#10005;</span> Historial de progreso y metricas
        </td>
      </tr>
      <tr>
        <td style="padding:8px 0;color:rgba(250,250,250,0.64);font-size:14px;">
          <span style="color:#DC2626;margin-right:8px;">&#10005;</span> Acceso a recursos y comunidad
        </td>
      </tr>
    </table>

    <table width="100%" cellpadding="0" cellspacing="0" style="background:rgba(220,38,38,0.1);border-radius:8px;padding:16px;margin-bottom:28px;">
      <tr>
        <td style="padding:16px;">
          <p style="color:rgba(250,250,250,0.64);font-size:14px;line-height:1.6;margin:0;">
            <strong style="color:#FAFAFA;">No pierdas tu racha.</strong> Los clientes que mantienen continuidad logran <strong style="color:#DC2626;">3x mejores resultados</strong> que quienes pausan y retoman.
          </p>
        </td>
      </tr>
    </table>

    <table width="100%" cellpadding="0" cellspacing="0">
      <tr>
        <td align="center">
          <a href="https://wellcorefitness.com/dashboard/plan" style="display:inline-block;background:#DC2626;color:white;padding:12px 28px;border-radius:8px;text-decoration:none;font-weight:bold;font-size:14px;">Renovar Mi Plan</a>
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
