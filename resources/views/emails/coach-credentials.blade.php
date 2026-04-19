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
    <h1 style="color:#FAFAFA;font-size:24px;margin:0 0 8px 0;">
      @if($isReset)
        Tu contrasena ha sido reestablecida
      @else
        Bienvenido al equipo WellCore, {{ $coachName }}!
      @endif
    </h1>

    <p style="color:rgba(250,250,250,0.64);font-size:15px;line-height:1.6;margin:0 0 24px 0;">
      @if($isReset)
        Un administrador ha generado una nueva contrasena temporal para tu cuenta de coach. Usa las credenciales de abajo para iniciar sesion.
      @else
        Te damos la bienvenida al equipo de coaches de WellCore Fitness. Tu cuenta del portal ya esta activa y lista para que empieces a trabajar con tus clientes.
      @endif
    </p>

    <!-- Credentials card -->
    <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:24px;">
      <tr>
        <td style="background:rgba(220,38,38,0.1);border-radius:8px;padding:20px;border-left:3px solid #DC2626;">
          <p style="color:#DC2626;font-size:13px;font-weight:bold;margin:0 0 12px 0;text-transform:uppercase;letter-spacing:1px;">Tus credenciales</p>

          <p style="color:rgba(250,250,250,0.64);font-size:13px;margin:0 0 4px 0;">Usuario</p>
          <p style="color:#FAFAFA;font-size:16px;font-weight:bold;margin:0 0 14px 0;font-family:'Courier New',monospace;">{{ $username }}</p>

          <p style="color:rgba(250,250,250,0.64);font-size:13px;margin:0 0 4px 0;">Contrasena temporal</p>
          <p style="color:#FAFAFA;font-size:16px;font-weight:bold;margin:0;font-family:'Courier New',monospace;">{{ $temporaryPassword }}</p>
        </td>
      </tr>
    </table>

    <!-- Warning -->
    <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:28px;">
      <tr>
        <td style="background:rgba(255,255,255,0.04);border-radius:8px;padding:14px 16px;">
          <p style="color:#FAFAFA;font-size:14px;font-weight:bold;margin:0 0 4px 0;">Importante</p>
          <p style="color:rgba(250,250,250,0.64);font-size:13px;margin:0;line-height:1.5;">
            Por seguridad, te pediremos cambiar esta contrasena en tu primer inicio de sesion.
          </p>
        </td>
      </tr>
    </table>

    <!-- CTA -->
    <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:28px;">
      <tr>
        <td align="center">
          <a href="{{ $loginUrl }}" style="display:inline-block;background:#DC2626;color:#FFFFFF;text-decoration:none;padding:14px 36px;border-radius:8px;font-weight:bold;font-size:15px;letter-spacing:1px;">
            INICIAR SESION
          </a>
        </td>
      </tr>
    </table>

    @unless($isReset)
    <p style="color:#FAFAFA;font-size:15px;font-weight:bold;margin:0 0 12px 0;">Onboarding:</p>
    <p style="color:rgba(250,250,250,0.64);font-size:14px;line-height:1.6;margin:0 0 8px 0;">
      Al entrar por primera vez veras un tour guiado que te presentara las herramientas del portal de coach: gestion de clientes, check-ins, plan tickets, mensajeria y analitica.
    </p>
    @endunless

    <p style="color:rgba(250,250,250,0.40);font-size:12px;margin:24px 0 0 0;">
      Si no solicitaste este acceso, contacta al administrador.
    </p>
  </td></tr>

  <!-- Footer -->
  <tr><td style="padding:20px 0;text-align:center;">
    <p style="color:rgba(250,250,250,0.32);font-size:12px;margin:0;">
      WellCore Fitness &middot; <a href="https://wellcorefitness.com" style="color:rgba(250,250,250,0.5);text-decoration:none;">wellcorefitness.com</a>
    </p>
  </td></tr>
</table>
</td></tr>
</table>
</body>
</html>
