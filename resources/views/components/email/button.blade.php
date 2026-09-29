@props(['href'])
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="width:100%; border-collapse:separate; mso-table-lspace:0pt; mso-table-rspace:0pt;">
    <tr>
        <td align="center" bgcolor="#138448" style="background:#138448; border-radius:10px; mso-padding-alt:14px 24px;">
            <a href="{{ $href }}" style="display:block; background:#138448; border:1px solid #138448; border-radius:10px; padding:14px 24px; color:#FFFFFF; text-decoration:none; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif; font-size:16px; font-weight:600; line-height:20px; text-align:center; mso-padding-alt:0;">
                {{ $slot }}
            </a>
        </td>
    </tr>
</table>
