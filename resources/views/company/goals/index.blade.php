<x-company-layout :title="'Goals'">

<style>
.goals-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:18px; }
.goals-title { font-size:22px; font-weight:700; color:var(--text); margin:0; }
.btn-primary { display:inline-flex; align-items:center; gap:6px; background:#4573d2; color:#fff; border:none; border-radius:8px; padding:8px 16px; font-size:13px; font-weight:600; cursor:pointer; font-family:var(--font); transition:background .15s; }
.btn-primary:hover { background:#3a62bb; }

/* Tabs */
.goal-tabs { display:flex; gap:22px; border-bottom:1px solid var(--border); margin-bottom:22px; }
.goal-tab { font-size:14px; font-weight:600; color:var(--muted); padding-bottom:10px; border-bottom:2px solid transparent; cursor:pointer; transition:color .15s; }
.goal-tab:hover { color:var(--text); }
.goal-tab.active { color:var(--text); border-bottom-color:var(--text); }
.goal-panel { display:none; }
.goal-panel.active { display:block; }

/* Goal card (shared by list + strategy map) */
.goal-card {
    display:flex; align-items:flex-start; gap:12px;
    background:var(--surface); border:1px solid var(--border); border-radius:12px;
    padding:14px 16px; margin-bottom:10px;
}
.goal-status-dot { width:10px; height:10px; border-radius:50%; margin-top:5px; flex-shrink:0; }
.goal-main { flex:1; min-width:0; }
.goal-title-row { display:flex; align-items:center; gap:10px; margin-bottom:8px; }
.goal-title { font-size:14px; font-weight:600; color:var(--text); }
.goal-status-badge { font-size:10px; font-weight:600; padding:2px 8px; border-radius:20px; }
.goal-progress-row { display:flex; align-items:center; gap:8px; margin-bottom:8px; }
.goal-progress-track { flex:1; max-width:220px; height:6px; background:var(--border); border-radius:3px; overflow:hidden; }
.goal-progress-fill { height:100%; border-radius:3px; }
.goal-progress-pct { font-size:11px; color:var(--muted); font-family:var(--mono); width:34px; }
.goal-chips { display:flex; flex-wrap:wrap; gap:6px; }
.goal-chip { font-size:10px; color:var(--muted); background:var(--surface2); border:1px solid var(--border); border-radius:6px; padding:2px 8px; }
.goal-meta { display:flex; flex-direction:column; align-items:flex-end; gap:6px; flex-shrink:0; }
.goal-owner { width:24px; height:24px; border-radius:50%; background:#7c3aed; color:#fff; font-size:10px; font-weight:700; display:flex; align-items:center; justify-content:center; }
.goal-due { font-size:11px; color:var(--muted); font-family:var(--mono); }
.goal-edit-btn { background:none; border:none; color:var(--muted); cursor:pointer; padding:3px; border-radius:6px; transition:color .15s, background .15s; }
.goal-edit-btn:hover { color:var(--text); background:var(--surface2); }

/* Strategy map */
.goal-node-children { margin-left:26px; padding-left:16px; border-left:2px solid var(--border); margin-top:2px; margin-bottom:10px; }

/* Team goals grouping */
.goal-team-heading { display:flex; align-items:center; gap:8px; font-size:13px; font-weight:600; color:var(--text); margin:18px 0 10px; }
.goal-team-heading:first-child { margin-top:0; }

.goal-empty { text-align:center; padding:50px 20px; color:var(--muted); font-size:13px; }

/* Modal */
.modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.55); z-index:1000; align-items:center; justify-content:center; }
.modal-overlay.open { display:flex; }
.modal-box { background:var(--surface); border:1px solid var(--border); border-radius:16px; padding:26px; width:520px; max-width:95vw; max-height:88vh; overflow-y:auto; }
.modal-title { font-size:16px; font-weight:700; color:var(--text); margin-bottom:18px; }
.form-row { margin-bottom:14px; }
.form-grid-2 { display:grid; grid-template-columns:1fr 1fr; gap:12px; }
.form-label { font-size:11px; color:var(--muted); font-family:var(--mono); text-transform:uppercase; letter-spacing:.06em; margin-bottom:6px; display:block; }
.form-control {
    width:100%; background:var(--bg); border:1px solid var(--border); border-radius:8px;
    padding:8px 12px; color:var(--text); font-size:13px; font-family:var(--font); outline:none;
    transition:border-color .15s; box-sizing:border-box; appearance:none;
}
.form-control:focus { border-color:var(--accent); }
textarea.form-control { resize:vertical; }
.goal-project-list { max-height:140px; overflow-y:auto; border:1px solid var(--border); border-radius:8px; padding:8px 10px; }
.goal-project-check { display:flex; align-items:center; gap:8px; padding:4px 0; font-size:13px; color:var(--text); }
.modal-footer { display:flex; justify-content:space-between; align-items:center; gap:10px; margin-top:20px; }
.btn-cancel { background:none; border:1px solid var(--border2); color:var(--muted); border-radius:8px; padding:8px 16px; font-size:13px; cursor:pointer; font-family:var(--font); }
.btn-save { background:var(--accent); border:none; color:#0a0f1a; border-radius:8px; padding:8px 20px; font-size:13px; font-weight:600; cursor:pointer; font-family:var(--font); }
.btn-delete-goal { background:none; border:none; color:var(--muted); font-size:12px; cursor:pointer; font-family:var(--font); }
.btn-delete-goal:hover { color:var(--danger); }
</style>

<div class="goals-header">
    <h1 class="goals-title">Goals</h1>
    <button class="btn-primary" onclick="openGoalModal(null)">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Create goal
    </button>
</div>

<div class="goal-tabs">
    <div class="goal-tab active" data-tab="strategy" onclick="switchGoalTab('strategy')">Strategy map</div>
    <div class="goal-tab" data-tab="company" onclick="switchGoalTab('company')">Company goals</div>
    <div class="goal-tab" data-tab="team" onclick="switchGoalTab('team')">Team goals</div>
    <div class="goal-tab" data-tab="my" onclick="switchGoalTab('my')">My goals</div>
</div>

{{-- STRATEGY MAP --}}
<div class="goal-panel active" id="goal-panel-strategy">
    @if($rootGoals->isEmpty())
        <div class="goal-empty">No goals yet. Create a company goal to start your strategy map — team and sub-goals nest underneath it.</div>
    @else
        @foreach($rootGoals as $goal)
            @include('company.goals.partials.goal-node', ['goal' => $goal])
        @endforeach
    @endif
</div>

{{-- COMPANY GOALS --}}
<div class="goal-panel" id="goal-panel-company">
    @if($companyGoals->isEmpty())
        <div class="goal-empty">No company-level goals yet.</div>
    @else
        @foreach($companyGoals as $goal)
            @include('company.goals.partials.goal-card', ['goal' => $goal])
        @endforeach
    @endif
</div>

{{-- TEAM GOALS --}}
<div class="goal-panel" id="goal-panel-team">
    @if($teamGoals->isEmpty())
        <div class="goal-empty">No team goals yet.</div>
    @else
        @foreach($teamGoals as $teamName => $goalsInTeam)
        <div class="goal-team-heading">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color:var(--muted);"><path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            {{ $teamName }}
        </div>
        @foreach($goalsInTeam as $goal)
            @include('company.goals.partials.goal-card', ['goal' => $goal])
        @endforeach
        @endforeach
    @endif
</div>

{{-- MY GOALS --}}
<div class="goal-panel" id="goal-panel-my">
    @if($myGoals->isEmpty())
        <div class="goal-empty">You don't own any goals yet.</div>
    @else
        @foreach($myGoals as $goal)
            @include('company.goals.partials.goal-card', ['goal' => $goal])
        @endforeach
    @endif
</div>

{{-- Create / Edit Goal Modal --}}
<div class="modal-overlay" id="goalModal">
    <div class="modal-box">
        <div class="modal-title" id="goalModalTitle">Create goal</div>

        <div class="form-row">
            <label class="form-label">Title</label>
            <input type="text" id="goalTitle" class="form-control" placeholder="e.g. Grow active customers by 20%">
        </div>
        <div class="form-row">
            <label class="form-label">Description</label>
            <textarea id="goalDescription" class="form-control" rows="2" placeholder="Optional details"></textarea>
        </div>

        <div class="form-grid-2">
            <div class="form-row">
                <label class="form-label">Scope</label>
                <select id="goalScope" class="form-control" onchange="toggleScopeFields()">
                    <option value="company">Company goal</option>
                    <option value="team">Team goal</option>
                </select>
            </div>
            <div class="form-row" id="goalTeamRow" style="display:none;">
                <label class="form-label">Team</label>
                <select id="goalTeam" class="form-control">
                    @foreach($teams as $t)
                    <option value="{{ $t->id }}">{{ $t->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="form-grid-2">
            <div class="form-row">
                <label class="form-label">Owner</label>
                <select id="goalOwner" class="form-control">
                    @foreach($members as $m)
                    <option value="{{ $m->id }}" {{ $m->id === auth()->id() ? 'selected' : '' }}>{{ $m->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-row">
                <label class="form-label">Parent goal</label>
                <select id="goalParent" class="form-control">
                    <option value="">No parent</option>
                    @foreach($parentOptions as $po)
                    <option value="{{ $po->id }}">{{ $po->title }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="form-grid-2">
            <div class="form-row">
                <label class="form-label">Status</label>
                <select id="goalStatus" class="form-control">
                    <option value="on_track">On track</option>
                    <option value="at_risk">At risk</option>
                    <option value="off_track">Off track</option>
                    <option value="done">Done</option>
                </select>
            </div>
            <div class="form-row">
                <label class="form-label">Progress</label>
                <select id="goalProgressMode" class="form-control" onchange="toggleProgressMode()">
                    <option value="manual">Manual %</option>
                    <option value="projects">From linked projects</option>
                </select>
            </div>
        </div>

        <div class="form-row" id="goalManualRow">
            <label class="form-label">Progress % <span id="goalManualVal">0%</span></label>
            <input type="range" id="goalManualProgress" min="0" max="100" step="5" value="0" style="width:100%;" oninput="document.getElementById('goalManualVal').textContent = this.value + '%'">
        </div>

        <div class="form-row" id="goalProjectsRow" style="display:none;">
            <label class="form-label">Linked projects</label>
            <div class="goal-project-list">
                @foreach($projects as $proj)
                <label class="goal-project-check">
                    <input type="checkbox" class="goal-project-checkbox" value="{{ $proj->id }}">
                    {{ $proj->name }}
                </label>
                @endforeach
            </div>
        </div>

        <div class="form-grid-2">
            <div class="form-row">
                <label class="form-label">Start date</label>
                <input type="date" id="goalStartDate" class="form-control">
            </div>
            <div class="form-row">
                <label class="form-label">Due date</label>
                <input type="date" id="goalDueDate" class="form-control">
            </div>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn-delete-goal" id="goalDeleteBtn" onclick="deleteGoal()" style="display:none;">Delete goal</button>
            <div style="flex:1;"></div>
            <button type="button" class="btn-cancel" onclick="closeGoalModal()">Cancel</button>
            <button type="button" class="btn-save" id="goalSaveBtn" onclick="saveGoal()">Save</button>
        </div>
    </div>
</div>

<script>
const GOALS_DATA = @json($goalsForJs->keyBy('id'));
const CSRF = document.querySelector('meta[name="csrf-token"]').content;
let editingGoalId = null;

function switchGoalTab(tab) {
    document.querySelectorAll('.goal-tab').forEach(t => t.classList.toggle('active', t.dataset.tab === tab));
    document.querySelectorAll('.goal-panel').forEach(p => p.classList.toggle('active', p.id === 'goal-panel-' + tab));
}

function toggleScopeFields() {
    document.getElementById('goalTeamRow').style.display = document.getElementById('goalScope').value === 'team' ? 'block' : 'none';
}

function toggleProgressMode() {
    const isProjects = document.getElementById('goalProgressMode').value === 'projects';
    document.getElementById('goalManualRow').style.display = isProjects ? 'none' : 'block';
    document.getElementById('goalProjectsRow').style.display = isProjects ? 'block' : 'none';
}

function resetGoalForm() {
    document.getElementById('goalTitle').value = '';
    document.getElementById('goalDescription').value = '';
    document.getElementById('goalScope').value = 'company';
    document.getElementById('goalTeam').value = '';
    document.getElementById('goalOwner').value = '{{ auth()->id() }}';
    document.getElementById('goalParent').value = '';
    document.getElementById('goalStatus').value = 'on_track';
    document.getElementById('goalProgressMode').value = 'manual';
    document.getElementById('goalManualProgress').value = 0;
    document.getElementById('goalManualVal').textContent = '0%';
    document.getElementById('goalStartDate').value = '';
    document.getElementById('goalDueDate').value = '';
    document.querySelectorAll('.goal-project-checkbox').forEach(cb => cb.checked = false);
    toggleScopeFields();
    toggleProgressMode();
}

function openGoalModal(goalId) {
    resetGoalForm();
    editingGoalId = goalId;

    document.getElementById('goalModalTitle').textContent = goalId ? 'Edit goal' : 'Create goal';
    document.getElementById('goalDeleteBtn').style.display = goalId ? 'inline-block' : 'none';

    if (goalId) {
        const g = GOALS_DATA[goalId];
        if (g) {
            document.getElementById('goalTitle').value = g.title || '';
            document.getElementById('goalDescription').value = g.description || '';
            document.getElementById('goalScope').value = g.scope || 'company';
            document.getElementById('goalTeam').value = g.team_id || '';
            document.getElementById('goalOwner').value = g.owner_id || '';
            document.getElementById('goalParent').value = g.parent_goal_id || '';
            document.getElementById('goalStatus').value = g.status || 'on_track';
            document.getElementById('goalProgressMode').value = g.progress_mode || 'manual';
            document.getElementById('goalManualProgress').value = g.manual_progress || 0;
            document.getElementById('goalManualVal').textContent = (g.manual_progress || 0) + '%';
            document.getElementById('goalStartDate').value = g.start_date || '';
            document.getElementById('goalDueDate').value = g.due_date || '';
            (g.project_ids || []).forEach(id => {
                const cb = document.querySelector('.goal-project-checkbox[value="' + id + '"]');
                if (cb) cb.checked = true;
            });
            toggleScopeFields();
            toggleProgressMode();

            // A goal can't be its own parent
            const parentSelect = document.getElementById('goalParent');
            Array.from(parentSelect.options).forEach(opt => { opt.disabled = (opt.value == goalId); });
        }
    }

    document.getElementById('goalModal').classList.add('open');
}

function closeGoalModal() {
    document.getElementById('goalModal').classList.remove('open');
}
document.getElementById('goalModal').addEventListener('click', function (e) {
    if (e.target === this) closeGoalModal();
});

function saveGoal() {
    const title = document.getElementById('goalTitle').value.trim();
    if (!title) { document.getElementById('goalTitle').focus(); return; }

    const projectIds = Array.from(document.querySelectorAll('.goal-project-checkbox:checked')).map(cb => cb.value);

    const payload = {
        title: title,
        description: document.getElementById('goalDescription').value.trim(),
        scope: document.getElementById('goalScope').value,
        team_id: document.getElementById('goalTeam').value,
        owner_id: document.getElementById('goalOwner').value,
        parent_goal_id: document.getElementById('goalParent').value,
        status: document.getElementById('goalStatus').value,
        progress_mode: document.getElementById('goalProgressMode').value,
        manual_progress: document.getElementById('goalManualProgress').value,
        start_date: document.getElementById('goalStartDate').value,
        due_date: document.getElementById('goalDueDate').value,
        project_ids: projectIds,
    };

    const btn = document.getElementById('goalSaveBtn');
    btn.disabled = true; btn.textContent = 'Saving…';

    const url = editingGoalId
        ? '{{ url("/{$slug}/admin/goals") }}/' + editingGoalId
        : '{{ route("company.goals.store", $slug) }}';

    fetch(url, {
        method: editingGoalId ? 'PATCH' : 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
        body: JSON.stringify(payload)
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) { window.location.reload(); return; }
        btn.disabled = false; btn.textContent = 'Save';
    })
    .catch(() => { btn.disabled = false; btn.textContent = 'Save'; });
}

function deleteGoal() {
    if (!editingGoalId || !confirm('Delete this goal? Sub-goals nested under it will be un-nested, not deleted.')) return;

    fetch('{{ url("/{$slug}/admin/goals") }}/' + editingGoalId, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => { if (data.success) window.location.reload(); });
}
</script>

</x-company-layout>
