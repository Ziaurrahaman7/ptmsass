<x-superadmin-layout title="Edit Company">

    <div style="max-width:600px;">
        <a href="{{ route('superadmin.companies.index') }}" style="font-size:12px; color:var(--muted); text-decoration:none; display:inline-block; margin-bottom:16px;" onmouseover="this.style.color='#a78bfa'" onmouseout="this.style.color='var(--muted)'">← Back to Companies</a>

        <div style="background:var(--surface); border:1px solid var(--border); border-radius:12px; padding:24px; margin-bottom:16px;">
            <div style="font-size:15px; font-weight:600; color:var(--text); margin-bottom:20px;">Edit: {{ $company->name }}</div>

            <form method="POST" action="{{ route('superadmin.companies.update', $company) }}" style="display:flex; flex-direction:column; gap:16px;">
                @csrf @method('PUT')

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px;">
                    <div>
                        <label style="display:block; font-size:11px; color:var(--muted); font-family:var(--mono); margin-bottom:6px;">COMPANY NAME *</label>
                        <input type="text" name="name" value="{{ old('name', $company->name) }}" style="width:100%; background:var(--surface2); border:1px solid var(--border2); border-radius:8px; color:var(--text); font-family:var(--font); font-size:13px; padding:9px 12px;">
                        @error('name')<div style="font-size:11px; color:#f87171; margin-top:4px;">{{ $message }}</div>@enderror
                    </div>
                    <div>
                        <label style="display:block; font-size:11px; color:var(--muted); font-family:var(--mono); margin-bottom:6px;">COMPANY EMAIL *</label>
                        <input type="email" name="email" value="{{ old('email', $company->email) }}" style="width:100%; background:var(--surface2); border:1px solid var(--border2); border-radius:8px; color:var(--text); font-family:var(--font); font-size:13px; padding:9px 12px;">
                        @error('email')<div style="font-size:11px; color:#f87171; margin-top:4px;">{{ $message }}</div>@enderror
                    </div>
                    <div>
                        <label style="display:block; font-size:11px; color:var(--muted); font-family:var(--mono); margin-bottom:6px;">PHONE</label>
                        <input type="text" name="phone" value="{{ old('phone', $company->phone) }}" style="width:100%; background:var(--surface2); border:1px solid var(--border2); border-radius:8px; color:var(--text); font-family:var(--font); font-size:13px; padding:9px 12px;">
                    </div>
                    <div>
                        <label style="display:block; font-size:11px; color:var(--muted); font-family:var(--mono); margin-bottom:6px;">STATUS *</label>
                        <select name="status" style="width:100%; background:var(--surface2); border:1px solid var(--border2); border-radius:8px; color:var(--text); font-family:var(--font); font-size:13px; padding:9px 12px;">
                            @foreach(['active','inactive','suspended'] as $s)
                            <option value="{{ $s }}" {{ old('status',$company->status)===$s?'selected':'' }}>{{ ucfirst($s) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label style="display:block; font-size:11px; color:var(--muted); font-family:var(--mono); margin-bottom:6px;">TRIAL ENDS AT</label>
                        <input type="date" name="trial_ends_at" value="{{ old('trial_ends_at', $company->trial_ends_at?->format('Y-m-d')) }}" style="width:100%; background:var(--surface2); border:1px solid var(--border2); border-radius:8px; color:var(--text); font-family:var(--font); font-size:13px; padding:9px 12px;">
                    </div>
                </div>

                <div style="display:flex; gap:10px; padding-top:6px;">
                    <button type="submit" style="background:rgba(167,139,250,0.12); border:1px solid rgba(167,139,250,0.3); color:#a78bfa; border-radius:8px; padding:9px 18px; font-family:var(--font); font-size:13px; font-weight:500; cursor:pointer;">Save Changes</button>
                    <a href="{{ route('superadmin.companies.index') }}" style="background:transparent; border:1px solid var(--border2); color:var(--muted); border-radius:8px; padding:9px 18px; font-size:13px; text-decoration:none;">Cancel</a>
                </div>
            </form>
        </div>

        {{-- Users --}}
        <div style="background:var(--surface); border:1px solid var(--border); border-radius:12px; overflow:hidden;">
            <div style="padding:14px 18px 12px; border-bottom:1px solid var(--border);">
                <span style="font-size:11px; font-weight:600; color:var(--muted); font-family:var(--mono); text-transform:uppercase; letter-spacing:0.08em;">Users in this Company</span>
            </div>
            <table style="width:100%; border-collapse:collapse;">
                <thead style="background:var(--surface2);">
                    <tr>
                        @foreach(['Name','Email','Role','Status'] as $h)
                        <th style="padding:10px 18px; text-align:left; font-size:11px; color:var(--muted); font-family:var(--mono); text-transform:uppercase; letter-spacing:0.06em; font-weight:500;">{{ $h }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse($company->users as $user)
                    <tr style="border-bottom:1px solid var(--border);">
                        <td style="padding:10px 18px; font-size:13px; font-weight:500; color:var(--text);">{{ $user->name }}</td>
                        <td style="padding:10px 18px; font-size:12px; color:var(--muted); font-family:var(--mono);">{{ $user->email }}</td>
                        <td style="padding:10px 18px;">
                            <span style="font-size:11px; font-family:var(--mono); padding:2px 7px; border-radius:4px; border:1px solid; {{ $user->role === 'company_admin' ? 'color:#22d3ee; border-color:rgba(34,211,238,0.3); background:rgba(34,211,238,0.06);' : 'color:var(--muted); border-color:var(--border2); background:transparent;' }}">
                                {{ ucfirst(str_replace('_',' ',$user->role)) }}
                            </span>
                        </td>
                        <td style="padding:10px 18px;">
                            <span style="font-size:11px; font-family:var(--mono); padding:2px 7px; border-radius:4px; border:1px solid; {{ $user->is_active ? 'color:#4ade80; border-color:rgba(74,222,128,0.3); background:rgba(74,222,128,0.06);' : 'color:#f87171; border-color:rgba(248,113,113,0.3); background:rgba(248,113,113,0.06);' }}">
                                {{ $user->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="4" style="padding:24px; text-align:center; color:var(--muted); font-size:13px;">No users.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</x-superadmin-layout>
