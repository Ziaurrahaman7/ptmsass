<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body style="margin:0; padding:0; background:#f4f5f7; font-family:Arial, Helvetica, sans-serif; color:#1f2937;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f4f5f7; padding:28px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="560" cellspacing="0" cellpadding="0" style="background:#ffffff; border:1px solid #e5e7eb; border-radius:8px;">
                    <tr>
                        <td style="padding:28px 28px 8px; font-size:20px; font-weight:700; color:#111827;">
                            Join {{ $invitation->company?->name ?? config('app.name') }}
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:8px 28px 0; font-size:15px; line-height:1.6; color:#374151;">
                            Hi {{ $invitation->name }},
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:10px 28px 20px; font-size:15px; line-height:1.6; color:#374151;">
                            {{ $invitation->inviter?->name ?? 'A teammate' }}
                            ({{ $invitation->inviter?->email }}) added you to
                            <strong>{{ $invitation->company?->name }}</strong>
                            as {{ $invitation->roleLabel() }}.
                            Create your password to open the workspace. This link expires in 7 days.
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:0 28px 22px;">
                            <a href="{{ $acceptUrl }}" style="display:inline-block; background:#4f46e5; color:#ffffff; text-decoration:none; font-weight:600; font-size:14px; padding:12px 20px; border-radius:6px;">
                                Set your password
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:0 28px 24px; font-size:13px; line-height:1.55; color:#6b7280; word-break:break-all;">
                            Or open this address in your browser:<br>
                            <a href="{{ $acceptUrl }}" style="color:#4f46e5;">{{ $acceptUrl }}</a>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:16px 28px 24px; border-top:1px solid #e5e7eb; font-size:12px; line-height:1.5; color:#9ca3af;">
                            You received this because {{ $invitation->inviter?->name ?? 'an admin' }} invited
                            {{ $invitation->email }} to {{ $invitation->company?->name }}.
                            If you were not expecting this, you can ignore the message.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
