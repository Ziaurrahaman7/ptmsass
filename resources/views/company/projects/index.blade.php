<x-company-layout title="Projects">

    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:22px;">
        <div>
            <div style="font-size:16px; font-weight:600; letter-spacing:-0.3px; color:var(--text);">All Projects</div>
            <div style="font-size:12px; color:var(--muted); margin-top:2px;">Manage your company's projects</div>
        </div>
        <a href="{{ route('company.projects.create', $slug) }}" class="ptm-btn-primary" style="text-decoration:none; display:flex; align-items:center; gap:7px;">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            New Project
        </a>
    </div>

    <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:14px;">
        @forelse($projects as $project)
        <div class="ptm-card" style="padding:16px 18px; transition:border-color 0.15s;" onmouseover="this.style.borderColor='rgba(255,255,255,0.13)'" onmouseout="this.style.borderColor='rgba(255,255,255,0.07)'">
            <div style="display:flex; align-items:flex-start; justify-content:space-between; margin-bottom:10px; gap:8px;">
                <div style="min-width:0;">
                    <a href="{{ route('company.projects.show', [$slug, $project]) }}" style="font-size:14px; font-weight:600; color:var(--text); text-decoration:none; display:block; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;" onmouseover="this.style.color='var(--accent)'" onmouseout="this.style.color='var(--text)'">{{ $project->name }}</a>
                    <div style="font-size:12px; color:var(--muted); margin-top:3px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ $project->description ?? 'No description' }}</div>
                </div>
                <span style="font-size:11px; font-family:var(--mono); padding:3px 8px; border-radius:6px; border:1px solid; white-space:nowrap; flex-shrink:0;
                    {{ $project->status === 'in_progress' ? 'color:#22d3ee; border-color:rgba(34,211,238,0.3); background:rgba(34,211,238,0.08);' :
                       ($project->status === 'completed' ? 'color:#4ade80; border-color:rgba(74,222,128,0.3); background:rgba(74,222,128,0.08);' :
                       ($project->status === 'on_hold' ? 'color:#fbbf24; border-color:rgba(251,191,36,0.3); background:rgba(251,191,36,0.08);' : 'color:var(--muted); border-color:var(--border2); background:transparent;')) }}">
                    {{ ucfirst(str_replace('_',' ',$project->status)) }}
                </span>
            </div>

            <div style="margin-bottom:10px;">
                <div style="display:flex; justify-content:space-between; font-size:11px; color:var(--muted); font-family:var(--mono); margin-bottom:5px;">
                    <span>Progress</span><span>{{ $project->progressPercentage() }}%</span>
                </div>
                <div style="height:3px; background:var(--border); border-radius:2px;">
                    <div style="height:100%; border-radius:2px; background:#4ade80; width:{{ $project->progressPercentage() }}%; transition:width 0.3s;"></div>
                </div>
            </div>

            <div style="font-size:11px; color:var(--muted); font-family:var(--mono); margin-bottom:14px;">
                {{ $project->tasks_count }} tasks · {{ $project->done_tasks_count }} done
                @if($project->due_date)
                · <span style="{{ $project->due_date->isPast() && $project->status !== 'completed' ? 'color:#f87171;' : '' }}">Due {{ $project->due_date->format('d M') }}</span>
                @endif
            </div>

            <div style="display:flex; gap:6px; padding-top:12px; border-top:1px solid var(--border);">
                <a href="{{ route('company.projects.show', [$slug, $project]) }}" style="flex:1; text-align:center; font-size:12px; font-weight:500; color:var(--accent); text-decoration:none; padding:6px; border-radius:6px; background:rgba(74,222,128,0.06); border:1px solid rgba(74,222,128,0.15);">Open</a>
                <a href="{{ route('company.projects.edit', [$slug, $project]) }}" style="flex:1; text-align:center; font-size:12px; font-weight:500; color:var(--muted); text-decoration:none; padding:6px; border-radius:6px; background:var(--surface2); border:1px solid var(--border);" onmouseover="this.style.color='var(--text)'" onmouseout="this.style.color='var(--muted)'">Edit</a>
                <form method="POST" action="{{ route('company.projects.destroy', [$slug, $project]) }}" onsubmit="return confirm('Delete this project?')">
                    @csrf @method('DELETE')
                    <button style="font-size:12px; font-weight:500; color:var(--danger); padding:6px 10px; border-radius:6px; background:rgba(248,113,113,0.06); border:1px solid rgba(248,113,113,0.15); cursor:pointer; font-family:var(--font);">Delete</button>
                </form>
            </div>
        </div>
        @empty
        <div style="grid-column:span 3; padding:60px 20px; text-align:center; color:var(--muted);">
            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,0.1)" stroke-width="1.5" style="margin:0 auto 12px;display:block;"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            <div style="font-size:15px; color:var(--text); margin-bottom:6px;">No projects yet</div>
            <a href="{{ route('company.projects.create', $slug) }}" style="font-size:13px; color:var(--accent); text-decoration:none;">Create your first project →</a>
        </div>
        @endforelse
    </div>

    @if($projects->hasPages())
    <div style="margin-top:20px;">{{ $projects->links() }}</div>
    @endif

</x-company-layout>
