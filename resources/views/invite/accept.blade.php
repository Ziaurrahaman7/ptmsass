<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Accept invite — {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:opsz,wght@9..40,400;9..40,500;9..40,600&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #0d0f12; --surface: #13161b; --surface2: #1a1e25;
            --border: rgba(255,255,255,0.07); --border2: rgba(255,255,255,0.13);
            --text: #e8eaf0; --muted: #6b7385; --accent: #a78bfa; --danger: #f87171;
            --font: 'DM Sans', sans-serif; --mono: 'DM Mono', monospace;
        }
        * { box-sizing: border-box; }
        body { margin:0; min-height:100vh; background:var(--bg); color:var(--text); font-family:var(--font); display:flex; align-items:center; justify-content:center; padding:24px; }
        .card { width:100%; max-width:420px; background:var(--surface); border:1px solid var(--border); border-radius:16px; padding:28px; }
        label { display:block; font-size:11px; color:var(--muted); font-family:var(--mono); margin-bottom:6px; letter-spacing:0.04em; }
        input { width:100%; background:var(--surface2); border:1px solid var(--border2); border-radius:8px; color:var(--text); font-family:var(--font); font-size:13px; padding:10px 12px; }
        input:focus { outline:none; border-color:var(--accent); }
        .btn { width:100%; background:rgba(167,139,250,0.14); border:1px solid rgba(167,139,250,0.35); color:#c4b5fd; border-radius:8px; padding:11px 16px; font-family:var(--font); font-size:14px; font-weight:600; cursor:pointer; }
        .err { font-size:12px; color:var(--danger); margin-top:6px; }
    </style>
</head>
<body>
    <div class="card">
        @if(! $invitation)
            <div style="font-size:18px; font-weight:600; margin-bottom:8px;">Invite not found</div>
            <div style="font-size:13px; color:var(--muted); line-height:1.5;">This link is invalid. Ask your admin to send a new invite.</div>
        @elseif($invitation->accepted_at)
            <div style="font-size:18px; font-weight:600; margin-bottom:8px;">Already accepted</div>
            <div style="font-size:13px; color:var(--muted); line-height:1.5; margin-bottom:18px;">This invite was already used. Log in with the password you set.</div>
            <a href="{{ route('login') }}" style="color:var(--accent); text-decoration:none; font-size:13px; font-weight:600;">Go to login →</a>
        @elseif($invitation->expires_at->isPast())
            <div style="font-size:18px; font-weight:600; margin-bottom:8px;">Invite expired</div>
            <div style="font-size:13px; color:var(--muted); line-height:1.5;">This link expired. Ask {{ $invitation->company?->name ?? 'your admin' }} to resend the invite.</div>
        @else
            <div style="font-size:11px; color:var(--muted); font-family:var(--mono); text-transform:uppercase; letter-spacing:0.08em; margin-bottom:8px;">{{ $invitation->company?->name }}</div>
            <div style="font-size:20px; font-weight:600; margin-bottom:6px;">Join as {{ $invitation->roleLabel() }}</div>
            <div style="font-size:13px; color:var(--muted); margin-bottom:22px;">Hi {{ $invitation->name }} — set a password to create your account.</div>

            <form method="POST" action="{{ route('invite.store', $token) }}" style="display:flex; flex-direction:column; gap:14px;">
                @csrf
                <div>
                    <label>EMAIL</label>
                    <input type="email" value="{{ $invitation->email }}" disabled>
                </div>
                <div>
                    <label>PASSWORD *</label>
                    <input type="password" name="password" required minlength="8" autocomplete="new-password">
                    @error('password')<div class="err">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label>CONFIRM PASSWORD *</label>
                    <input type="password" name="password_confirmation" required minlength="8" autocomplete="new-password">
                </div>
                <button type="submit" class="btn">Create account</button>
            </form>
        @endif
    </div>
</body>
</html>
