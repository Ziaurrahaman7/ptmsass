@props(['forms', 'projects', 'slug', 'storeRoute'])

<div style="display:flex; align-items:flex-start; justify-content:space-between; gap:16px; margin-bottom:20px; flex-wrap:wrap;">
    <div style="display:flex; align-items:center; gap:12px;">
        <div style="width:40px; height:40px; border-radius:11px; background:linear-gradient(135deg, rgba(167,139,250,0.25), rgba(34,211,238,0.12)); border:1px solid rgba(167,139,250,0.3); display:flex; align-items:center; justify-content:center; flex-shrink:0;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#a78bfa" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
        </div>
        <div>
            <div style="font-size:18px; font-weight:600; letter-spacing:-0.3px; color:var(--text);">Intake forms</div>
            <div style="font-size:12px; color:var(--muted); margin-top:2px; max-width:520px; line-height:1.45;">
                Public links for clients or teammates without login. Each submission creates a task on the chosen project.
            </div>
        </div>
    </div>
</div>

@if(session('success'))
<div class="ptm-alert-success" style="padding:12px 16px; margin-bottom:16px; font-size:13px;">{{ session('success') }}</div>
@endif

<div class="ptm-card" style="padding:20px; margin-bottom:20px;">
    <div style="font-size:13px; font-weight:600; color:var(--text); margin-bottom:14px;">Create new form</div>
    <form method="POST" action="{{ $storeRoute }}" style="display:grid; grid-template-columns:1fr minmax(180px,240px) auto; gap:10px; align-items:end;">
        @csrf
        <div>
            <label style="display:block; font-size:10px; color:var(--muted); font-family:var(--mono); text-transform:uppercase; letter-spacing:0.06em; margin-bottom:6px;">Form name</label>
            <input name="name" class="ptm-input" style="width:100%;" placeholder="e.g. Client work request" required value="{{ old('name') }}">
        </div>
        <div>
            <label style="display:block; font-size:10px; color:var(--muted); font-family:var(--mono); text-transform:uppercase; letter-spacing:0.06em; margin-bottom:6px;">Target project</label>
            <select name="project_id" class="ptm-select" style="width:100%;" required>
                @forelse($projects as $project)
                <option value="{{ $project->id }}" @selected(old('project_id') == $project->id)>{{ $project->name }}</option>
                @empty
                <option value="" disabled>No projects — create a project first</option>
                @endforelse
            </select>
        </div>
        <button type="submit" class="ptm-btn-primary" style="white-space:nowrap;" @disabled($projects->isEmpty())>Create form</button>
    </form>
</div>

@if($forms->isEmpty())
<div class="ptm-card" style="padding:48px 24px; text-align:center;">
    <div style="font-size:15px; font-weight:500; color:var(--text); margin-bottom:6px;">No intake forms yet</div>
    <div style="font-size:12px; color:var(--muted);">Create one above and share the public URL.</div>
</div>
@else
<div style="display:flex; flex-direction:column; gap:10px;">
    @foreach($forms as $form)
    @php $publicUrl = url('/f/'.$form->token); @endphp
    <div class="ptm-card" style="padding:16px 18px;">
        <div style="display:flex; align-items:flex-start; justify-content:space-between; gap:14px; flex-wrap:wrap;">
            <div style="flex:1; min-width:200px;">
                <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                    <span style="font-size:15px; font-weight:600; color:var(--text);">{{ $form->name }}</span>
                    @if($form->is_active)
                    <span style="font-size:10px; font-family:var(--mono); padding:3px 8px; border-radius:6px; color:#4ade80; border:1px solid rgba(74,222,128,0.35); background:rgba(74,222,128,0.08);">Active</span>
                    @else
                    <span style="font-size:10px; font-family:var(--mono); padding:3px 8px; border-radius:6px; color:var(--muted); border:1px solid var(--border2);">Inactive</span>
                    @endif
                </div>
                <div style="font-size:12px; color:var(--muted); margin-top:6px;">
                    Tasks go to <span style="color:var(--text); font-weight:500;">{{ $form->project?->name ?? 'Unknown project' }}</span>
                </div>
            </div>
            <div style="display:flex; gap:8px; flex-wrap:wrap;">
                <a href="{{ $publicUrl }}" target="_blank" rel="noopener" class="ptm-btn-ghost" style="text-decoration:none; font-size:12px; display:inline-flex; align-items:center; gap:6px;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                    Preview
                </a>
                <button type="button" class="ptm-btn-primary" style="font-size:12px;" onclick="copyFormLink(@js($publicUrl), this)">Copy link</button>
            </div>
        </div>
        <div style="margin-top:12px; padding:10px 12px; background:var(--surface2); border:1px solid var(--border); border-radius:8px; display:flex; align-items:center; gap:8px;">
            <span style="font-size:10px; color:var(--muted); font-family:var(--mono); text-transform:uppercase; flex-shrink:0;">Public URL</span>
            <input type="text" readonly value="{{ $publicUrl }}" class="ptm-input" style="flex:1; font-size:11px; font-family:var(--mono); padding:6px 10px; background:transparent; border:none;">
        </div>
    </div>
    @endforeach
</div>
@endif

<script>
function copyFormLink(url, btn) {
    navigator.clipboard.writeText(url).then(() => {
        const prev = btn.textContent;
        btn.textContent = 'Copied';
        setTimeout(() => { btn.textContent = prev; }, 2000);
    });
}
</script>
