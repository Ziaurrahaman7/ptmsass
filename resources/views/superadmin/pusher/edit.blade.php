<x-superadmin-layout title="Pusher / Realtime">

    <div style="max-width:640px;">
        <div style="font-size:12px; color:var(--muted); margin-bottom:16px; line-height:1.5;">
            Live inbox updates for company admins and employees. Create a Channels app at pusher.com and paste the credentials here. The secret is stored encrypted.
        </div>

        <div style="background:var(--surface); border:1px solid var(--border); border-radius:12px; padding:24px; margin-bottom:16px;">
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:18px;">
                <div style="font-size:15px; font-weight:600; color:var(--text);">Pusher settings</div>
                @if($settings->exists && $settings->is_enabled)
                    <span style="font-size:11px; font-family:var(--mono); padding:3px 8px; border-radius:6px; color:#4ade80; border:1px solid rgba(74,222,128,0.3); background:rgba(74,222,128,0.08);">Enabled</span>
                @else
                    <span style="font-size:11px; font-family:var(--mono); padding:3px 8px; border-radius:6px; color:var(--muted); border:1px solid var(--border2);">Disabled</span>
                @endif
            </div>

            <form method="POST" action="{{ route('superadmin.pusher.update') }}" style="display:flex; flex-direction:column; gap:14px;">
                @csrf
                @method('PUT')

                <label style="display:flex; align-items:center; gap:8px; font-size:13px; color:var(--text); cursor:pointer;">
                    <input type="checkbox" name="is_enabled" value="1" {{ old('is_enabled', $settings->is_enabled) ? 'checked' : '' }}>
                    Enable realtime notifications
                </label>

                <div>
                    <label style="display:block; font-size:11px; color:var(--muted); font-family:var(--mono); margin-bottom:6px;">APP ID *</label>
                    <input type="text" name="app_id" value="{{ old('app_id', $settings->app_id) }}" autocomplete="off" style="width:100%; background:var(--surface2); border:1px solid var(--border2); border-radius:8px; color:var(--text); font-family:var(--font); font-size:13px; padding:9px 12px;">
                    @error('app_id')<div style="font-size:11px; color:#f87171; margin-top:4px;">{{ $message }}</div>@enderror
                </div>

                <div>
                    <label style="display:block; font-size:11px; color:var(--muted); font-family:var(--mono); margin-bottom:6px;">KEY *</label>
                    <input type="text" name="key" value="{{ old('key', $settings->key) }}" autocomplete="off" style="width:100%; background:var(--surface2); border:1px solid var(--border2); border-radius:8px; color:var(--text); font-family:var(--font); font-size:13px; padding:9px 12px;">
                    @error('key')<div style="font-size:11px; color:#f87171; margin-top:4px;">{{ $message }}</div>@enderror
                </div>

                <div>
                    <label style="display:block; font-size:11px; color:var(--muted); font-family:var(--mono); margin-bottom:6px;">SECRET {{ $settings->exists ? '(leave blank to keep)' : '*' }}</label>
                    <input type="password" name="secret" autocomplete="new-password" style="width:100%; background:var(--surface2); border:1px solid var(--border2); border-radius:8px; color:var(--text); font-family:var(--font); font-size:13px; padding:9px 12px;">
                    @error('secret')<div style="font-size:11px; color:#f87171; margin-top:4px;">{{ $message }}</div>@enderror
                </div>

                <div>
                    <label style="display:block; font-size:11px; color:var(--muted); font-family:var(--mono); margin-bottom:6px;">CLUSTER *</label>
                    <input type="text" name="cluster" value="{{ old('cluster', $settings->cluster ?: 'ap2') }}" placeholder="ap2" style="width:100%; background:var(--surface2); border:1px solid var(--border2); border-radius:8px; color:var(--text); font-family:var(--font); font-size:13px; padding:9px 12px;">
                    @error('cluster')<div style="font-size:11px; color:#f87171; margin-top:4px;">{{ $message }}</div>@enderror
                </div>

                <div style="padding-top:4px;">
                    <button type="submit" style="background:rgba(167,139,250,0.12); border:1px solid rgba(167,139,250,0.3); color:#a78bfa; border-radius:8px; padding:9px 18px; font-family:var(--font); font-size:13px; font-weight:500; cursor:pointer;">Save Pusher</button>
                </div>
            </form>
        </div>

        <div style="background:var(--surface); border:1px solid var(--border); border-radius:12px; padding:24px;">
            <div style="font-size:15px; font-weight:600; color:var(--text); margin-bottom:8px;">Send test event</div>
            <div style="font-size:12px; color:var(--muted); margin-bottom:14px;">Save and enable first. Pushes a ping to Pusher so you can confirm the credentials.</div>
            <form method="POST" action="{{ route('superadmin.pusher.test') }}">
                @csrf
                <button type="submit" style="background:transparent; border:1px solid var(--border2); color:var(--text); border-radius:8px; padding:9px 16px; font-family:var(--font); font-size:13px; cursor:pointer;">Send test</button>
            </form>
        </div>
    </div>

</x-superadmin-layout>
