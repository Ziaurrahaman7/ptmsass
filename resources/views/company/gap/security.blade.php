<x-company-layout title="Security">
<div style="font-size:20px; font-weight:600; margin-bottom:16px;">Enterprise security</div>
<form method="POST" action="{{ route('company.security.update', $slug) }}" class="ptm-card" style="padding:16px; margin-bottom:16px; display:flex; flex-direction:column; gap:10px;">
    @csrf
    <label style="display:flex; gap:8px; align-items:center;">
        <input type="checkbox" name="mfa_required" value="1" @checked($company->mfa_required)>
        Require MFA for this company
    </label>
    <input name="trusted_domains" class="ptm-input" value="{{ implode(', ', $company->trusted_domains ?? []) }}" placeholder="Trusted email domains, comma separated">
    <button class="ptm-btn-primary">Save</button>
</form>
<div class="ptm-card" style="padding:16px;">
    <div class="ptm-section-title" style="margin-bottom:10px;">Audit log</div>
    @foreach($logs as $log)
    <div style="font-size:12px; color:var(--muted); padding:6px 0; border-bottom:1px solid var(--border);">{{ $log->created_at }} · {{ $log->action }} · {{ $log->ip }}</div>
    @endforeach
</div>
</x-company-layout>
