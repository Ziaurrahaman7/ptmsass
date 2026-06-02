<x-superadmin-layout title="New Company">

    <div style="max-width:600px;">
        <a href="{{ route('superadmin.companies.index') }}" style="font-size:12px; color:var(--muted); text-decoration:none; display:inline-block; margin-bottom:16px;" onmouseover="this.style.color='#a78bfa'" onmouseout="this.style.color='var(--muted)'">← Back to Companies</a>

        <div style="background:var(--surface); border:1px solid var(--border); border-radius:12px; padding:24px;">
            <div style="font-size:15px; font-weight:600; color:var(--text); margin-bottom:20px;">Create New Company</div>

            <form method="POST" action="{{ route('superadmin.companies.store') }}" style="display:flex; flex-direction:column; gap:16px;">
                @csrf

                <div style="font-size:10px; color:var(--muted); font-family:var(--mono); text-transform:uppercase; letter-spacing:0.1em; padding-bottom:4px; border-bottom:1px solid var(--border);">Company Info</div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px;">
                    <div>
                        <label style="display:block; font-size:11px; color:var(--muted); font-family:var(--mono); margin-bottom:6px;">COMPANY NAME *</label>
                        <input type="text" name="name" value="{{ old('name') }}" style="width:100%; background:var(--surface2); border:1px solid var(--border2); border-radius:8px; color:var(--text); font-family:var(--font); font-size:13px; padding:9px 12px;" @error('name') style="border-color:rgba(248,113,113,0.5);" @enderror>
                        @error('name')<div style="font-size:11px; color:#f87171; margin-top:4px;">{{ $message }}</div>@enderror
                    </div>
                    <div>
                        <label style="display:block; font-size:11px; color:var(--muted); font-family:var(--mono); margin-bottom:6px;">COMPANY EMAIL *</label>
                        <input type="email" name="email" value="{{ old('email') }}" style="width:100%; background:var(--surface2); border:1px solid var(--border2); border-radius:8px; color:var(--text); font-family:var(--font); font-size:13px; padding:9px 12px;">
                        @error('email')<div style="font-size:11px; color:#f87171; margin-top:4px;">{{ $message }}</div>@enderror
                    </div>
                    <div>
                        <label style="display:block; font-size:11px; color:var(--muted); font-family:var(--mono); margin-bottom:6px;">PHONE</label>
                        <input type="text" name="phone" value="{{ old('phone') }}" style="width:100%; background:var(--surface2); border:1px solid var(--border2); border-radius:8px; color:var(--text); font-family:var(--font); font-size:13px; padding:9px 12px;">
                    </div>
                    <div>
                        <label style="display:block; font-size:11px; color:var(--muted); font-family:var(--mono); margin-bottom:6px;">STATUS *</label>
                        <select name="status" style="width:100%; background:var(--surface2); border:1px solid var(--border2); border-radius:8px; color:var(--text); font-family:var(--font); font-size:13px; padding:9px 12px;">
                            <option value="active">Active</option><option value="inactive">Inactive</option><option value="suspended">Suspended</option>
                        </select>
                    </div>
                    <div>
                        <label style="display:block; font-size:11px; color:var(--muted); font-family:var(--mono); margin-bottom:6px;">TRIAL ENDS AT</label>
                        <input type="date" name="trial_ends_at" value="{{ old('trial_ends_at') }}" style="width:100%; background:var(--surface2); border:1px solid var(--border2); border-radius:8px; color:var(--text); font-family:var(--font); font-size:13px; padding:9px 12px;">
                    </div>
                </div>

                <div style="font-size:10px; color:var(--muted); font-family:var(--mono); text-transform:uppercase; letter-spacing:0.1em; padding-bottom:4px; border-bottom:1px solid var(--border);">Company Admin Account</div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px;">
                    <div>
                        <label style="display:block; font-size:11px; color:var(--muted); font-family:var(--mono); margin-bottom:6px;">ADMIN NAME *</label>
                        <input type="text" name="admin_name" value="{{ old('admin_name') }}" style="width:100%; background:var(--surface2); border:1px solid var(--border2); border-radius:8px; color:var(--text); font-family:var(--font); font-size:13px; padding:9px 12px;">
                        @error('admin_name')<div style="font-size:11px; color:#f87171; margin-top:4px;">{{ $message }}</div>@enderror
                    </div>
                    <div>
                        <label style="display:block; font-size:11px; color:var(--muted); font-family:var(--mono); margin-bottom:6px;">ADMIN EMAIL *</label>
                        <input type="email" name="admin_email" value="{{ old('admin_email') }}" style="width:100%; background:var(--surface2); border:1px solid var(--border2); border-radius:8px; color:var(--text); font-family:var(--font); font-size:13px; padding:9px 12px;">
                        @error('admin_email')<div style="font-size:11px; color:#f87171; margin-top:4px;">{{ $message }}</div>@enderror
                    </div>
                    <div>
                        <label style="display:block; font-size:11px; color:var(--muted); font-family:var(--mono); margin-bottom:6px;">ADMIN PASSWORD *</label>
                        <input type="password" name="admin_password" style="width:100%; background:var(--surface2); border:1px solid var(--border2); border-radius:8px; color:var(--text); font-family:var(--font); font-size:13px; padding:9px 12px;">
                        @error('admin_password')<div style="font-size:11px; color:#f87171; margin-top:4px;">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div style="display:flex; gap:10px; padding-top:6px;">
                    <button type="submit" style="background:rgba(167,139,250,0.12); border:1px solid rgba(167,139,250,0.3); color:#a78bfa; border-radius:8px; padding:9px 18px; font-family:var(--font); font-size:13px; font-weight:500; cursor:pointer;">Create Company</button>
                    <a href="{{ route('superadmin.companies.index') }}" style="background:transparent; border:1px solid var(--border2); color:var(--muted); border-radius:8px; padding:9px 18px; font-size:13px; text-decoration:none;">Cancel</a>
                </div>
            </form>
        </div>
    </div>

</x-superadmin-layout>
