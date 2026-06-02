<x-superadmin-layout title="Companies">

    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:22px;">
        <div>
            <div style="font-size:16px; font-weight:600; letter-spacing:-0.3px; color:var(--text);">All Companies</div>
            <div style="font-size:12px; color:var(--muted); margin-top:2px;">Manage all registered companies</div>
        </div>
        <a href="{{ route('superadmin.companies.create') }}" style="background:rgba(167,139,250,0.12); border:1px solid rgba(167,139,250,0.3); color:#a78bfa; border-radius:8px; padding:8px 16px; font-size:13px; font-weight:500; text-decoration:none; display:flex; align-items:center; gap:7px; transition:all 0.15s;" onmouseover="this.style.background='rgba(167,139,250,0.2)'" onmouseout="this.style.background='rgba(167,139,250,0.12)'">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            New Company
        </a>
    </div>

    <div style="background:var(--surface); border:1px solid var(--border); border-radius:12px; overflow:hidden;">
        <table style="width:100%; border-collapse:collapse;">
            <thead style="background:var(--surface2);">
                <tr>
                    @foreach(['#','Company','Email','Users','Trial Ends','Status','Actions'] as $h)
                    <th style="padding:12px 18px; text-align:left; font-size:11px; color:var(--muted); font-family:var(--mono); text-transform:uppercase; letter-spacing:0.06em; font-weight:500;">{{ $h }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse($companies as $company)
                <tr style="border-bottom:1px solid var(--border); transition:background 0.1s;" onmouseover="this.style.background='var(--surface2)'" onmouseout="this.style.background='transparent'">
                    <td style="padding:12px 18px; font-size:12px; color:var(--muted); font-family:var(--mono);">{{ $company->id }}</td>
                    <td style="padding:12px 18px;">
                        <div style="font-size:13px; font-weight:500; color:var(--text);">{{ $company->name }}</div>
                        <div style="font-size:11px; color:var(--muted); font-family:var(--mono);">{{ $company->phone }}</div>
                    </td>
                    <td style="padding:12px 18px; font-size:13px; color:var(--muted); font-family:var(--mono);">{{ $company->email }}</td>
                    <td style="padding:12px 18px; font-size:13px; color:var(--muted); font-family:var(--mono);">{{ $company->users_count }}</td>
                    <td style="padding:12px 18px; font-size:12px; color:var(--muted); font-family:var(--mono);">{{ $company->trial_ends_at ? $company->trial_ends_at->format('d M Y') : '—' }}</td>
                    <td style="padding:12px 18px;">
                        <span style="font-size:11px; font-family:var(--mono); padding:3px 8px; border-radius:6px; border:1px solid;
                            {{ $company->status === 'active' ? 'color:#4ade80; border-color:rgba(74,222,128,0.3); background:rgba(74,222,128,0.08);' :
                               ($company->status === 'suspended' ? 'color:#f87171; border-color:rgba(248,113,113,0.3); background:rgba(248,113,113,0.08);' : 'color:#fbbf24; border-color:rgba(251,191,36,0.3); background:rgba(251,191,36,0.08);') }}">
                            {{ ucfirst($company->status) }}
                        </span>
                    </td>
                    <td style="padding:12px 18px;">
                        <div style="display:flex; align-items:center; gap:14px;">
                            <a href="{{ route('superadmin.companies.edit', $company) }}" style="font-size:12px; color:var(--accent2); text-decoration:none; font-family:var(--mono);">Edit</a>
                            <form method="POST" action="{{ route('superadmin.companies.toggle', $company) }}" style="display:inline;">
                                @csrf @method('PATCH')
                                <button style="background:none; border:none; font-size:12px; font-family:var(--mono); cursor:pointer; {{ $company->status === 'active' ? 'color:#fbbf24;' : 'color:#4ade80;' }}">
                                    {{ $company->status === 'active' ? 'Suspend' : 'Activate' }}
                                </button>
                            </form>
                            <form method="POST" action="{{ route('superadmin.companies.destroy', $company) }}" style="display:inline;" onsubmit="return confirm('Delete {{ $company->name }}?')">
                                @csrf @method('DELETE')
                                <button style="background:none; border:none; font-size:12px; font-family:var(--mono); cursor:pointer; color:var(--danger);">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" style="padding:48px; text-align:center; color:var(--muted); font-size:13px;">No companies found.</td></tr>
                @endforelse
            </tbody>
        </table>
        @if($companies->hasPages())
        <div style="padding:12px 18px; border-top:1px solid var(--border);">{{ $companies->links() }}</div>
        @endif
    </div>

</x-superadmin-layout>
