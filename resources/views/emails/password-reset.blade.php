<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"></head>
<body style="margin:0;padding:0;background-color:#09090B;font-family:Arial,Helvetica,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background-color:#09090B;padding:40px 20px;">
<tr><td align="center">
<table width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;">

  {{-- Logo header --}}
  <tr><td style="padding:20px 0;text-align:center;">
    <div style="display:inline-block;background:#DC2626;width:40px;height:40px;border-radius:8px;line-height:40px;text-align:center;color:white;font-weight:bold;font-size:20px;">W</div>
    <span style="color:#FAFAFA;font-size:20px;font-weight:bold;letter-spacing:3px;margin-left:10px;vertical-align:middle;">WELLCORE</span>
  </td></tr>

  {{-- Content card --}}
  <tr><td style="background:#18181B;border-radius:12px;padding:40px;border:1px solid rgba(255,255,255,0.08);">

    {{-- Lock icon --}}
    <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:24px;">
      <tr><td align="center">
        <div style="display:inline-block;background:rgba(220,38,38,0.1);width:56px;height:56px;border-radius:50%;line-height:56px;text-align:center;">
          <span style="font-size:24px;">&#128274;</span>
        </div>
      </td></tr>
    </table>

    <h1 style="color:#FAFAFA;font-size:24px;margin:0 0 8px 0;text-align:center;">Restablecer Contrasena</h1>
    <p style="color:rgba(250,250,250,0.64);font-size:15px;line-height:1.6;margin:0 0 24px 0;text-align:center;">
      Hola <strong style="color:#FAFAFA;">{{ $name }}</strong>, recibimos una solicitud para restablecer la contrasena de tu cuenta WellCore Fitness.
    </p>

    {{-- Reset button --}}
    <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:24px;">
      <tr><td align="center">
        <a href="{{ $resetUrl }}" style="display:inline-block;background:#DC2626;color:white;padding:14px 36px;border-radius:50px;text-decoration:none;font-weight:bold;font-size:15px;letter-spacing:0.5px;">
          Restablecer mi Contrasena
        </a>
      </td></tr>
    </table>

    {{-- Expiry notice --}}
    <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:24px;">
      <tr>
        <td style="background:rgba(220,38,38,0.06);border-radius:8px;padding:14px 16px;border-left:3px solid #DC2626;">
          <p style="color:rgba(250,250,250,0.64);font-size:13px;line-height:1.5;margin:0;">
            <strong style="color:#FAFAFA;">Este enlace expira en 1 hora.</strong><br>
            Despues de ese tiempo, deberas solicitar un nuevo enlace de recuperacion.
          </p>
        </td>
      </tr>
    </table>

    {{-- Fallback URL --}}
    <p style="color:rgba(250,250,250,0.4);font-size:12px;line-height:1.5;margin:0 0 24px 0;text-align:center;">
      Si el boton no funciona, copia y pega este enlace en tu navegador:<br>
      <a href="{{ $resetUrl }}" style="color:#DC2626;text-decoration:none;word-break:break-all;font-size:11px;">{{ $resetUrl }}</a>
    </p>

    {{-- Security notice --}}
    <table width="100%" cellpadding="0" cellspacing="0">
      <tr>
        <td style="border-top:1px solid rgba(255,255,255,0.08);padding-top:20px;">
          <p style="color:rgba(250,250,250,0.4);font-size:12px;line-height:1.5;margin:0;">
            <strong style="color:rgba(250,250,250,0.5);">No solicitaste esto?</strong><br>
            Si no solicitaste restablecer tu contrasena, puedes ignorar este email con seguridad. Tu contrasena actual seguira siendo la misma. Nadie puede acceder a tu cuenta sin este enlace.
          </p>
        </td>
      </tr>
    </table>

  </td></tr>

  {{-- Footer --}}
  <tr><td style="padding:24px 0;text-align:center;">
    <p style="color:rgba(250,250,250,0.3);font-size:11px;line-height:1.5;margin:0 0 8px 0;">
      Este email fue enviado a {{ $name }} porque se solicito un restablecimiento de contrasena para tu cuenta WellCore Fitness.
    </p>
    <p style="color:rgba(250,250,250,0.4);font-size:12px;margin:0;">
      WellCore Fitness &copy; {{ date('Y') }}. Todos los derechos reservados.<br>
      <a href="https://wellcorefitness.com" style="color:#DC2626;text-decoration:none;">wellcorefitness.com</a>
    </p>
    <p style="color:rgba(250,250,250,0.2);font-size:10px;margin:8px 0 0 0;">
      Bucaramanga, Santander, Colombia
    </p>
  </td></tr>

</table>
</td></tr></table>
</body></html>
