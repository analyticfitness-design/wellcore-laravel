<!DOCTYPE html>
<html lang="es" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="color-scheme" content="dark">
  <meta name="supported-color-schemes" content="dark">
  <!--[if mso]>
  <noscript><xml><o:OfficeDocumentSettings><o:PixelPerInch>96</o:PixelPerInch></o:OfficeDocumentSettings></xml></noscript>
  <![endif]-->
  <title>@yield('title', 'WellCore Fitness')</title>
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Inter:wght@400;600&display=swap');
    * { box-sizing: border-box; }
    body, #bodyTable { margin: 0; padding: 0; background-color: #09090B; width: 100% !important; }
    img { border: 0; display: block; max-width: 100%; height: auto; }
    a { color: #DC2626; text-decoration: none; }
    .email-wrapper { background-color: #09090B; padding: 24px 16px; }
    .container { max-width: 600px; margin: 0 auto; }
    .card { background-color: #18181B; border-radius: 8px; overflow: hidden; }
    .header-cell { background-color: #09090B; padding: 24px; text-align: center; border-bottom: 1px solid #27272A; }
    .content-cell { padding: 32px 24px; }
    .footer-cell { background-color: #09090B; padding: 24px; text-align: center; border-top: 1px solid #27272A; }
    h1, h2, h3 { font-family: 'Bebas Neue', Arial, sans-serif; color: #FAFAFA; margin: 0 0 16px; }
    p { font-family: 'Inter', Arial, sans-serif; color: #A1A1AA; line-height: 1.6; font-size: 15px; margin: 0 0 16px; }
    .text-white { color: #FAFAFA !important; }
    .text-accent { color: #DC2626 !important; }
    .text-muted { color: #71717A; font-size: 13px; }
    .btn-primary {
      display: inline-block;
      background-color: #DC2626;
      color: #FAFAFA !important;
      font-family: 'Inter', Arial, sans-serif;
      font-weight: bold;
      font-size: 16px;
      text-decoration: none;
      padding: 16px 32px;
      border-radius: 6px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      mso-padding-alt: 0;
    }
    <!--[if mso]>
    .btn-primary { padding: 0 !important; }
    <![endif]-->
  </style>
</head>
<body>
  <!--[if mso | IE]>
  <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" bgcolor="#09090B">
  <tr><td>
  <![endif]-->
  <div class="email-wrapper">
    <div class="container">

      <!-- HEADER -->
      <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
        <tr>
          <td class="header-cell">
            <!-- Logo WellCore — usar imagen hosted o inline SVG/PNG -->
            <img src="{{ config('wellcore.base_url', config('app.url')) }}/images/wellcore-logo-email.png"
                 alt="WellCore Fitness" width="120" height="auto"
                 style="display:block;margin:0 auto;">
          </td>
        </tr>
      </table>

      <!-- CARD PRINCIPAL -->
      <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
        <tr>
          <td class="card">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
              <tr>
                <td class="content-cell">
                  @yield('content')
                </td>
              </tr>
            </table>
          </td>
        </tr>
      </table>

      <!-- FOOTER -->
      <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
        <tr>
          <td class="footer-cell">
            @yield('footer_extra')
            <p class="text-muted" style="margin-bottom:8px;">
              &copy; {{ date('Y') }} WellCore Fitness &middot; Todos los derechos reservados
            </p>
            <p class="text-muted">
              <a href="{{ config('wellcore.base_url', config('app.url')) }}/privacidad"
                 style="color:#71717A;text-decoration:underline;">Privacidad</a>
              &nbsp;&middot;&nbsp;
              <a href="{{ config('wellcore.base_url', config('app.url')) }}/terminos"
                 style="color:#71717A;text-decoration:underline;">Terminos</a>
            </p>
          </td>
        </tr>
      </table>

    </div>
  </div>
  <!--[if mso | IE]>
  </td></tr></table>
  <![endif]-->
</body>
</html>
