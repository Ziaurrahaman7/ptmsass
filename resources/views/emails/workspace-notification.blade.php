<!DOCTYPE html>
<html>
<head><meta charset="utf-8"></head>
<body style="margin:0; padding:0; background:#f4f5f7; font-family:Arial, Helvetica, sans-serif; color:#1f2937;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f4f5f7; padding:28px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="560" cellspacing="0" cellpadding="0" style="background:#ffffff; border:1px solid #e5e7eb; border-radius:8px;">
                    <tr>
                        <td style="padding:28px 28px 8px; font-size:18px; font-weight:700; color:#111827;">
                            {{ $notification->title }}
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:8px 28px 20px; font-size:15px; line-height:1.6; color:#374151;">
                            {{ $notification->message }}
                        </td>
                    </tr>
                    @if($notification->link)
                    <tr>
                        <td style="padding:0 28px 24px;">
                            <a href="{{ $notification->link }}" style="display:inline-block; background:#4f46e5; color:#ffffff; text-decoration:none; font-weight:600; font-size:14px; padding:12px 20px; border-radius:6px;">Open task</a>
                        </td>
                    </tr>
                    @endif
                    <tr>
                        <td style="padding:16px 28px 24px; border-top:1px solid #e5e7eb; font-size:12px; color:#9ca3af;">
                            You received this because you follow this task or were mentioned. You can ignore it if it was sent in error.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
