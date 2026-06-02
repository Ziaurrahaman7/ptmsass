<x-superadmin-layout title="Dashboard">

    <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:14px; margin-bottom:24px;">
        <div style="background:var(--surface); border:1px solid var(--border); border-radius:12px; padding:16px 18px;">
            <div style="font-size:11px; color:var(--muted); font-family:var(--mono); text-transform:uppercase; letter-spacing:0.06em; margin-bottom:8px;">Total Companies</div>
            <div style="font-size:26px; font-weight:600; color:#a78bfa;">{{ $totalCompanies }}</div>
            <div style="font-size:11px; color:#4ade80; margin-top:4px; font-family:var(--mono);">{{ $activeCompanies }} active</div>
        </div>
        <div style="background:var(--surface); border:1px solid var(--border); border-radius:12px; padding:16px 18px;">
            <div style="font-size:11px; color:var(--muted); font-family:var(--mono); text-transform:uppercase; letter-spacing:0.06em; margin-bottom:8px;">Total Users</div>
            <div style="font-size:26px; font-weight:600; color:#22d3ee;">{{ $totalUsers }}</div>
            <div style="font-size:11px; color:var(--muted); margin-top:4px; font-family:var(--mono);">Across all companies</div>
        </div>
        <div style="background:var(--surface); border:1px solid var(--border); border-radius:12px; padding:16px 18px;">
            <div style="font-size:11px; color:var(--muted); font-family:var(--mono); text-transform:uppercase; letter-spacing:0.06em; margin-bottom:8px;">Suspended</div>
            <div style="font-size:26px; font-weight:600; color:#f87171;">{{ $suspendedCompanies }}</div>
            <div style="font-size:11px; color:var(--muted); margin-top:4px; font-family:var(--mono);">Need attention</div>
        </div>
    </div>

    <div style="background:var(--surface); border:1px solid var(--border); border-radius:12px; overflow:hidden;">
        <div style="padding:14px 18px 12px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between;">
            <span style="font-size:11px; font-weight:600; color:var(--muted); font-family:var(--mono); text-transform:uppercase; letter-spacing:0.08em;">Recent Companies</span>
            <a href="{{ route('superadmin.companies.index') }}" style="font-size:12px; color:#a78bfa; text-decoration:none;">View all →</a>
        </div>
        <div>
            @forelse($recentCompanies as $company)
            <div style="padding:12px 18px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between;">
                <div>
                    <div style="font-size:13px; font-weight:500; color:var(--text);">{{ $company->name }}</div>
                    <div style="font-size:11px; color:var(--muted); font-family:var(--mono); margin-top:2px;">{{ $company->email }}</div>
                </div>
                <span style="font-size:11px; font-family:var(--mono); padding:3px 8px; border-radius:6px; border:1px solid;
                    {{ $company->status === 'active' ? 'color:#4ade80; border-color:rgba(74,222,128,0.3); background:rgba(74,222,128,0.08);' :
                       ($company->status === 'suspended' ? 'color:#f87171; border-color:rgba(248,113,113,0.3); background:rgba(248,113,113,0.08);' : 'color:var(--muted); border-color:var(--border2); background:transparent;') }}">
                    {{ ucfirst($company->status) }}
                </span>
            </div>
            @empty
            <div style="padding:32px; text-align:center; color:var(--muted); font-size:13px;">No companies yet.</div>
            @endforelse
        </div>
    </div>

</x-superadmin-layout>
