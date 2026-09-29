@props(['reason' => null])
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="width:100%; max-width:600px; mso-table-lspace:0pt; mso-table-rspace:0pt;">
    <tr>
        <td align="center" style="padding:24px 20px; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif; font-size:13px; line-height:1.5; color:#858580;">
            <strong style="color:#555550;">Kanvi</strong>
            <p style="margin:8px 0 16px;">Vi gør det nemmere at finde ud af det sammen.</p>
            <a href="{{ config('app.url') }}" style="color:#555550; text-decoration:underline;">kanvi.dk</a>
            @if ($reason)
                <p style="margin:16px 0 0;">{{ $reason }}</p>
            @endif
            <p style="margin:16px 0 0;">© {{ now()->year }} Kanvi</p>
        </td>
    </tr>
</table>
