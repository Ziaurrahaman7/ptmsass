<x-superadmin-layout>
<div style="padding:8px 0 18px; font-size:20px; font-weight:600;">Security controls</div>
<form method="POST" action="{{ route('superadmin.security.update') }}" style="background:var(--surface); border:1px solid var(--border); border-radius:12px; padding:16px; max-width:520px;">
    @csrf
    <select name="company_id" class="ptm-select" style="width:100%; margin-bottom:10px; background:var(--surface2); color:var(--text); padding:8px; border-radius:8px;">
        @foreach($companies as $company)
        <option value="{{ $company->id }}">{{ $company->name }}</option>
        @endforeach
    </select>
    <label style="display:flex; gap:8px; margin-bottom:12px;"><input type="checkbox" name="mfa_required" value="1"> Require MFA</label>
    <button style="background:rgba(167,139,250,.15); color:var(--accent); border:1px solid rgba(167,139,250,.3); padding:8px 14px; border-radius:8px;">Save</button>
</form>
<div style="margin-top:18px; font-size:12px; color:var(--muted);">
    @foreach($logs as $log)
    <div style="padding:6px 0; border-bottom:1px solid var(--border);">{{ $log->created_at }} · {{ $log->action }}</div>
    @endforeach
</div>
</x-superadmin-layout>
