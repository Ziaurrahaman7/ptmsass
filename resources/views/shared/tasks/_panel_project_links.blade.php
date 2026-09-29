@php
    $linkedProjects = $task->relationLoaded('projects') ? $task->projects : $task->projects()->orderBy('name')->get();
    $primaryId = (int) $task->project_id;
    $attachRoute = $attachRoute ?? 'company.tasks.projects.attach';
    $detachRoute = $detachRoute ?? 'company.tasks.projects.detach';
@endphp
<div style="display:grid; grid-template-columns:120px 1fr; align-items:start; gap:10px; padding:8px 0;">
    <span style="font-size:12px; color:var(--muted); font-weight:500;">Projects</span>
    <div>
        <div style="font-size:11px; color:var(--muted); font-family:var(--mono); margin-bottom:6px;">HOME PROJECT</div>
        @if($task->project)
        <a href="{{ route(str_starts_with($attachRoute, 'employee.') ? 'employee.projects.show' : 'company.projects.show', [$slug, $task->project]) }}" style="font-size:13px; color:var(--accent2); text-decoration:none;">{{ $task->project->name }}</a>
        @else
        <span style="font-size:13px; color:var(--purple, #a78bfa); font-family:var(--mono);">Personal</span>
        @endif

        @if($linkedProjects->where('id', '!=', $primaryId)->isNotEmpty())
        <div style="font-size:11px; color:var(--muted); font-family:var(--mono); margin:12px 0 6px;">ALSO ON</div>
        <div style="display:flex; flex-wrap:wrap; gap:6px;">
            @foreach($linkedProjects as $lp)
            @if((int) $lp->id === $primaryId)
                @continue
            @endif
            <span style="display:inline-flex; align-items:center; gap:6px; font-size:12px; padding:4px 10px; border-radius:8px; background:var(--surface2); border:1px solid var(--border2); color:var(--text);">
                {{ $lp->name }}
                @if($canManageProjectLinks ?? false)
                <button type="button" onclick="panelDetachProject({{ $lp->id }})" title="Remove from this project" style="background:none; border:none; color:var(--muted); cursor:pointer; padding:0; line-height:1; font-size:14px;">×</button>
                @endif
            </span>
            @endforeach
        </div>
        @endif

        @if(($canManageProjectLinks ?? false) && ($attachableProjects ?? collect())->isNotEmpty())
        <div style="display:flex; gap:8px; margin-top:12px; flex-wrap:wrap;">
            <select id="panelAttachProjectSelect" class="ptm-select" style="flex:1; min-width:140px; font-size:12px;">
                @foreach($attachableProjects as $ap)
                <option value="{{ $ap->id }}">{{ $ap->name }}</option>
                @endforeach
            </select>
            <button type="button" class="ptm-btn-primary" style="font-size:12px; white-space:nowrap;" onclick="panelAttachProject(document.getElementById('panelAttachProjectSelect').value)">Add project</button>
        </div>
        @endif
        @if($canManageProjectLinks ?? false)
        <div style="font-size:11px; color:var(--muted); margin-top:8px; line-height:1.45;">Same task appears on every linked project — edits sync automatically.</div>
        @endif
    </div>
</div>
