<x-superadmin-layout title="SMTP / Email">

    <div style="max-width:640px;">
        <div style="font-size:12px; color:var(--muted); margin-bottom:16px; line-height:1.5;">
            Platform mail for member invites. Company admins do not set SMTP — they only send invites.
        </div>

        <div style="background:var(--surface); border:1px solid var(--border); border-radius:12px; padding:24px; margin-bottom:16px;">
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:18px;">
                <div style="font-size:15px; font-weight:600; color:var(--text);">SMTP settings</div>
                @if($settings->exists && $settings->is_enabled)
                    <span style="font-size:11px; font-family:var(--mono); padding:3px 8px; border-radius:6px; color:#4ade80; border:1px solid rgba(74,222,128,0.3); background:rgba(74,222,128,0.08);">Enabled</span>
                @else
                    <span style="font-size:11px; font-family:var(--mono); padding:3px 8px; border-radius:6px; color:var(--muted); border:1px solid var(--border2);">Disabled</span>
                @endif
            </div>

            <form method="POST" action="{{ route('superadmin.smtp.update') }}" style="display:flex; flex-direction:column; gap:14px;">
                @csrf
                @method('PUT')

                <label style="display:flex; align-items:center; gap:8px; font-size:13px; color:var(--text); cursor:pointer;">
                    <input type="checkbox" name="is_enabled" value="1" {{ old('is_enabled', $settings->is_enabled) ? 'checked' : '' }}>
                    Enable SMTP (send invite emails)
                </label>

                <div style="display:grid; grid-template-columns:1fr 140px; gap:14px;">
                    <div>
                        <label style="display:block; font-size:11px; color:var(--muted); font-family:var(--mono); margin-bottom:6px;">HOST *</label>
                        <input type="text" name="host" value="{{ old('host', $settings->host) }}" placeholder="smtp.hostinger.com" style="width:100%; background:var(--surface2); border:1px solid var(--border2); border-radius:8px; color:var(--text); font-family:var(--font); font-size:13px; padding:9px 12px;">
                        @error('host')<div style="font-size:11px; color:#f87171; margin-top:4px;">{{ $message }}</div>@enderror
                    </div>
                    <div>
                        <label style="display:block; font-size:11px; color:var(--muted); font-family:var(--mono); margin-bottom:6px;">PORT *</label>
                        <input type="number" name="port" value="{{ old('port', $settings->port) }}" style="width:100%; background:var(--surface2); border:1px solid var(--border2); border-radius:8px; color:var(--text); font-family:var(--font); font-size:13px; padding:9px 12px;">
                        @error('port')<div style="font-size:11px; color:#f87171; margin-top:4px;">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div>
                    <label style="display:block; font-size:11px; color:var(--muted); font-family:var(--mono); margin-bottom:6px;">ENCRYPTION *</label>
                    <select name="encryption" style="width:100%; background:var(--surface2); border:1px solid var(--border2); border-radius:8px; color:var(--text); font-family:var(--font); font-size:13px; padding:9px 12px;">
                        <option value="ssl" {{ old('encryption', $settings->encryption()) === 'ssl' ? 'selected' : '' }}>SSL / TLS (port 465) — Hostinger</option>
                        <option value="tls" {{ old('encryption', $settings->encryption()) === 'tls' ? 'selected' : '' }}>STARTTLS (port 587)</option>
                    </select>
                </div>

                <div>
                    <label style="display:block; font-size:11px; color:var(--muted); font-family:var(--mono); margin-bottom:6px;">USERNAME *</label>
                    <input type="text" name="username" value="{{ old('username', $settings->username) }}" placeholder="aman@neoindexai.com" autocomplete="off" style="width:100%; background:var(--surface2); border:1px solid var(--border2); border-radius:8px; color:var(--text); font-family:var(--font); font-size:13px; padding:9px 12px;">
                    @error('username')<div style="font-size:11px; color:#f87171; margin-top:4px;">{{ $message }}</div>@enderror
                </div>

                <div>
                    <label style="display:block; font-size:11px; color:var(--muted); font-family:var(--mono); margin-bottom:6px;">PASSWORD {{ $settings->exists ? '(leave blank to keep)' : '*' }}</label>
                    <input type="password" name="password" autocomplete="new-password" style="width:100%; background:var(--surface2); border:1px solid var(--border2); border-radius:8px; color:var(--text); font-family:var(--font); font-size:13px; padding:9px 12px;">
                    @error('password')<div style="font-size:11px; color:#f87171; margin-top:4px;">{{ $message }}</div>@enderror
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px;">
                    <div>
                        <label style="display:block; font-size:11px; color:var(--muted); font-family:var(--mono); margin-bottom:6px;">FROM ADDRESS *</label>
                        <input type="email" name="from_address" value="{{ old('from_address', $settings->from_address) }}" placeholder="aman@neoindexai.com" style="width:100%; background:var(--surface2); border:1px solid var(--border2); border-radius:8px; color:var(--text); font-family:var(--font); font-size:13px; padding:9px 12px;">
                        @error('from_address')<div style="font-size:11px; color:#f87171; margin-top:4px;">{{ $message }}</div>@enderror
                    </div>
                    <div>
                        <label style="display:block; font-size:11px; color:var(--muted); font-family:var(--mono); margin-bottom:6px;">FROM NAME *</label>
                        <input type="text" name="from_name" value="{{ old('from_name', $settings->from_name) }}" placeholder="NeoIndex AI" style="width:100%; background:var(--surface2); border:1px solid var(--border2); border-radius:8px; color:var(--text); font-family:var(--font); font-size:13px; padding:9px 12px;">
                        @error('from_name')<div style="font-size:11px; color:#f87171; margin-top:4px;">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div style="padding-top:4px;">
                    <button type="submit" style="background:rgba(167,139,250,0.12); border:1px solid rgba(167,139,250,0.3); color:#a78bfa; border-radius:8px; padding:9px 18px; font-family:var(--font); font-size:13px; font-weight:500; cursor:pointer;">Save SMTP</button>
                </div>
            </form>
        </div>

        <div style="background:var(--surface); border:1px solid var(--border); border-radius:12px; padding:24px;">
            <div style="font-size:15px; font-weight:600; color:var(--text); margin-bottom:8px;">Send test email</div>
            <div style="font-size:12px; color:var(--muted); margin-bottom:14px;">Save settings first. Sends a short message from the From address above.</div>
            <form method="POST" action="{{ route('superadmin.smtp.test') }}" style="display:flex; gap:10px; align-items:flex-end;">
                @csrf
                <div style="flex:1;">
                    <label style="display:block; font-size:11px; color:var(--muted); font-family:var(--mono); margin-bottom:6px;">TEST TO</label>
                    <input type="email" name="test_email" value="{{ old('test_email', auth()->user()->email) }}" required style="width:100%; background:var(--surface2); border:1px solid var(--border2); border-radius:8px; color:var(--text); font-family:var(--font); font-size:13px; padding:9px 12px;">
                    @error('test_email')<div style="font-size:11px; color:#f87171; margin-top:4px;">{{ $message }}</div>@enderror
                </div>
                <button type="submit" style="background:transparent; border:1px solid var(--border2); color:var(--text); border-radius:8px; padding:9px 16px; font-family:var(--font); font-size:13px; cursor:pointer; white-space:nowrap;">Send test</button>
            </form>
        </div>
    </div>

</x-superadmin-layout>
