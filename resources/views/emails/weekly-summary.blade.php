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
    <p style="color:#DC2626;font-size:13px;font-weight:bold;text-transform:uppercase;letter-spacing:2px;margin:0 0 4px 0;">Semana {{ $weekNumber }}</p>
    <h1 style="color:#FAFAFA;font-size:24px;margin:0 0 8px 0;">Tu Resumen Semanal</h1>
    <p style="color:rgba(250,250,250,0.64);font-size:15px;line-height:1.6;margin:0 0 28px 0;">
      Hola {{ $clientName }}, aqui tienes un vistazo a tu semana. Cada entrenamiento cuenta.
    </p>

    <!-- Stats Grid -->
    <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:24px;">
      <tr>
        <td width="50%" style="padding-right:8px;">
          <table width="100%" cellpadding="0" cellspacing="0">
            <tr>
              <td style="background:rgba(255,255,255,0.04);border-radius:8px;padding:20px;text-align:center;border:1px solid rgba(255,255,255,0.08);">
                <p style="color:rgba(250,250,250,0.64);font-size:12px;text-transform:uppercase;letter-spacing:1px;margin:0 0 8px 0;">Entrenamientos</p>
                <p style="color:#FAFAFA;font-size:36px;font-weight:bold;margin:0;">{{ $workoutsCompleted }}</p>
                <p style="color:rgba(250,250,250,0.64);font-size:12px;margin:4px 0 0 0;">completados</p>
              </td>
            </tr>
          </table>
        </td>
        <td width="50%" style="padding-left:8px;">
          <table width="100%" cellpadding="0" cellspacing="0">
            <tr>
              <td style="background:rgba(255,255,255,0.04);border-radius:8px;padding:20px;text-align:center;border:1px solid rgba(255,255,255,0.08);">
                <p style="color:rgba(250,250,250,0.64);font-size:12px;text-transform:uppercase;letter-spacing:1px;margin:0 0 8px 0;">Adherencia</p>
                <p style="font-size:36px;font-weight:bold;margin:0;color:{{ $adherencePercent >= 80 ? '#22C55E' : ($adherencePercent >= 50 ? '#EAB308' : '#DC2626') }};">{{ $adherencePercent }}%</p>
                <p style="color:rgba(250,250,250,0.64);font-size:12px;margin:4px 0 0 0;">del plan</p>
              </td>
            </tr>
          </table>
        </td>
      </tr>
    </table>

    <!-- Adherence bar -->
    <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:28px;">
      <tr>
        <td>
          <p style="color:rgba(250,250,250,0.64);font-size:13px;margin:0 0 8px 0;">Progreso de adherencia</p>
          <table width="100%" cellpadding="0" cellspacing="0">
            <tr>
              <td style="background:rgba(255,255,255,0.08);border-radius:4px;height:8px;">
                <div style="background:{{ $adherencePercent >= 80 ? '#22C55E' : ($adherencePercent >= 50 ? '#EAB308' : '#DC2626') }};width:{{ min($adherencePercent, 100) }}%;height:8px;border-radius:4px;"></div>
              </td>
            </tr>
          </table>
        </td>
      </tr>
    </table>

    <!-- Encouragement message -->
    <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:28px;">
      <tr>
        <td style="background:rgba({{ $adherencePercent >= 80 ? '34,197,94' : ($adherencePercent >= 50 ? '234,179,8' : '220,38,38') }},0.1);border-radius:8px;padding:16px;border-left:3px solid {{ $adherencePercent >= 80 ? '#22C55E' : ($adherencePercent >= 50 ? '#EAB308' : '#DC2626') }};">
          @if($adherencePercent >= 80)
            <p style="color:#FAFAFA;font-size:15px;font-weight:bold;margin:0 0 4px 0;">Excelente semana!</p>
            <p style="color:rgba(250,250,250,0.64);font-size:14px;margin:0;">Tu dedicacion esta dando frutos. Mantener una adherencia del {{ $adherencePercent }}% es clave para resultados sostenibles. Sigue asi!</p>
          @elseif($adherencePercent >= 50)
            <p style="color:#FAFAFA;font-size:15px;font-weight:bold;margin:0 0 4px 0;">Buen esfuerzo, puedes mejorar!</p>
            <p style="color:rgba(250,250,250,0.64);font-size:14px;margin:0;">Vas por buen camino con {{ $adherencePercent }}% de adherencia. Un par de sesiones mas esta semana y estaras en la zona optima.</p>
          @else
            <p style="color:#FAFAFA;font-size:15px;font-weight:bold;margin:0 0 4px 0;">No te rindas!</p>
            <p style="color:rgba(250,250,250,0.64);font-size:14px;margin:0;">Esta semana fue dificil, pero cada nuevo dia es una oportunidad. Tu coach puede ayudarte a ajustar tu plan. Lo importante es seguir adelante.</p>
          @endif
        </td>
      </tr>
    </table>

    <table width="100%" cellpadding="0" cellspacing="0">
      <tr>
        <td align="center">
          <a href="https://wellcorefitness.com/dashboard" style="display:inline-block;background:#DC2626;color:white;padding:12px 28px;border-radius:8px;text-decoration:none;font-weight:bold;font-size:14px;">Registrar Mi Proximo Entrenamiento</a>
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
