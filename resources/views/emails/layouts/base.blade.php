<!doctype html>
<html lang="da" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <meta name="supported-color-schemes" content="light">
    <meta name="x-apple-disable-message-reformatting">
    <title>{{ $title }}</title>
    <!--[if mso]><xml><o:OfficeDocumentSettings><o:PixelsPerInch>96</o:PixelsPerInch></o:OfficeDocumentSettings></xml><![endif]-->
    <style>
        @media only screen and (max-width: 600px) {
            .email-logo-cell { padding: 24px 24px 0 !important; }
            .email-content { padding: 32px 24px 24px !important; }
            .email-heading { font-size: 25px !important; }
        }
    </style>
</head>
<body style="margin:0; padding:0; width:100%; background:#F7F7F5; -webkit-text-size-adjust:100%; -ms-text-size-adjust:100%; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;">
<div style="display:none; max-height:0; max-width:0; overflow:hidden; opacity:0; color:transparent; font-size:1px; line-height:1px; mso-hide:all;" aria-hidden="true">{{ $preheader }}</div>
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" bgcolor="#F7F7F5" style="width:100%; background:#F7F7F5; border-collapse:collapse; mso-table-lspace:0pt; mso-table-rspace:0pt;">
    <tr>
        <td align="center" style="padding:32px 16px;">
            <!--[if mso]><table role="presentation" width="600" cellspacing="0" cellpadding="0" border="0"><tr><td><![endif]-->
            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" bgcolor="#FFFFFF" style="width:100%; max-width:600px; background:#FFFFFF; border-radius:16px; mso-table-lspace:0pt; mso-table-rspace:0pt;">
                <tr>
                    <td class="email-logo-cell" align="center" style="padding:40px 40px 0;">
                        <x-email.logo />
                    </td>
                </tr>
                <tr>
                    <td class="email-content" style="padding:32px 40px 40px; color:#555550; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif; font-size:16px; line-height:1.6; overflow-wrap:break-word; word-wrap:break-word;">
                        @yield('content')
                        <p style="margin:28px 0 0; font-size:16px; line-height:1.6; color:#555550;">Venlig hilsen<br><strong style="color:#20201E;">Kanvi</strong></p>
                    </td>
                </tr>
            </table>
            <x-email.footer :reason="$reason" />
            <!--[if mso]></td></tr></table><![endif]-->
        </td>
    </tr>
</table>
</body>
</html>
