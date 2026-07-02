<x-company-layout :title="$project->name">

    @php
        $priorityStyles = [
            'urgent' => 'color:#f87171; border-color:rgba(248,113,113,0.3); background:rgba(248,113,113,0.08);',
            'high'   => 'color:#fb923c; border-color:rgba(251,146,60,0.3); background:rgba(251,146,60,0.08);',
            'medium' => 'color:#fbbf24; border-color:rgba(251,191,36,0.3); background:rgba(251,191,36,0.08);',
            'low'    => 'color:var(--muted); border-color:var(--border2); background:transparent;',
        ];
        $statusMeta = [
            'todo'        => ['label' => 'To Do',       'color' => '#6b7385'],
            'in_progress' => ['label' => 'In Progress', 'color' => '#22d3ee'],
            'in_review'   => ['label' => 'In Review',   'color' => '#a78bfa'],
            'done'        => ['label' => 'Done',        'color' => '#4ade80'],
        ];
        // Ordered groups: each section, then a "(No section)" bucket.
        $groups = [];
        foreach ($sections as $section) {
            $groups[] = ['id' => $section->id, 'name' => $section->name, 'tasks' => $tasks->where('section_id', $section->id)];
        }
        $noSection = $tasks->whereNull('section_id');
        $groups[] = ['id' => null, 'name' => '(No section)', 'tasks' => $noSection];
        $cfWidths = str_repeat(' 160px', $customFields->count());
        $colGrid = 'grid-template-columns:minmax(360px,1.5fr) 150px 175px 150px 130px'.$cfWidths.' 44px;';
        $tableMinWidth = 360 + 649 + (160 * $customFields->count());
    @endphp

    <style>
        .al-cell { padding:9px 14px; border-right:1px solid var(--border); display:flex; align-items:center; min-width:0; }
        .al-row:hover { background:var(--surface2); }
        .al-row-actions { display:flex; align-items:center; gap:9px; flex-shrink:0; opacity:0; transition:opacity 0.12s; }
        .al-row:hover .al-row-actions { opacity:1; }
        .al-drag { flex-shrink:0; cursor:grab; color:var(--muted); opacity:0; transition:opacity 0.12s; display:flex; align-items:center; }
        .al-row:hover .al-drag { opacity:0.6; }
        .al-drag:hover { opacity:1 !important; }
        .al-drag:active { cursor:grabbing; }
        .sortable-ghost { opacity:0.35; }
        .col-menu.show { display:block !important; }
        .al-cfhead .al-cfdel { opacity:0; transition:opacity 0.12s; }
        .al-cfhead:hover .al-cfdel { opacity:1; }
        .al-cfinput { background:var(--surface2); border:1px solid var(--border2); border-radius:6px; color:var(--text); font-size:12px; font-family:var(--font); padding:6px 9px; width:100%; }
        .al-cfinput:focus { outline:none; border-color:var(--accent2); }
        .al-cfinput::placeholder { color:var(--muted); }
        .al-gridrow > .al-cell:last-child { border-right:none; }
        .al-metaicon { display:flex; align-items:center; gap:3px; cursor:pointer; color:var(--muted); font-size:11px; font-family:var(--mono); background:none; border:none; padding:2px; }
        .al-metaicon:hover { color:var(--accent2); }
        .al-name-input { background:transparent; border:1px solid transparent; border-radius:6px; color:var(--text); font-size:13px; font-weight:500; padding:5px 8px; width:100%; font-family:var(--font); }
        .al-name-input:hover { background:rgba(255,255,255,0.04); }
        .al-name-input:focus { outline:none; background:var(--surface2); border-color:var(--accent2); }
        .al-pill { font-size:12px; font-family:var(--font); border:1px solid transparent; border-radius:6px; padding:5px 10px; cursor:pointer; width:100%; -webkit-appearance:none; appearance:none; }
        .al-pill:focus { outline:none; border-color:var(--accent2); }
        .al-date { background:transparent; border:1px solid transparent; border-radius:6px; color:var(--muted); font-size:12px; font-family:var(--mono); padding:5px 6px; width:100%; cursor:pointer; }
        .al-date:hover { background:rgba(255,255,255,0.04); }
        .al-date:focus { outline:none; background:var(--surface2); border-color:var(--accent2); }
        .al-date::-webkit-calendar-picker-indicator { filter:invert(0.6); cursor:pointer; }
        .al-tab { font-size:13px; font-weight:500; color:var(--muted); padding:10px 4px; cursor:pointer; border-bottom:2px solid transparent; background:none; border-top:none; border-left:none; border-right:none; font-family:var(--font); }
        .al-tab:hover { color:var(--text); }
        .al-tab.active { color:var(--text); border-bottom-color:var(--accent); }
        .al-tool { display:flex; align-items:center; gap:6px; background:none; border:none; color:var(--muted); font-size:12px; font-family:var(--font); cursor:pointer; padding:6px 8px; border-radius:6px; }
        .al-tool:hover { color:var(--text); background:var(--surface2); }
        .al-avatar { width:24px; height:24px; border-radius:6px; background:rgba(74,222,128,0.2); color:#4ade80; font-size:10px; font-weight:600; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
        .al-addrow input { background:transparent; border:none; color:var(--text); font-size:13px; padding:7px 8px; width:100%; font-family:var(--font); }
        .al-addrow input:focus { outline:none; }
    </style>

    <div x-data="{ tab: 'list' }">

        {{-- Header --}}
        <div style="display:flex; align-items:flex-start; justify-content:space-between; gap:12px; margin-bottom:10px;">
            <div style="display:flex; align-items:center; gap:11px;">
                <div style="width:32px; height:32px; border-radius:8px; background:var(--accent); display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#0d0f12" stroke-width="2.5"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                </div>
                <div>
                    <div style="font-size:17px; font-weight:600; letter-spacing:-0.3px; color:var(--text);">{{ $project->name }}</div>
                    @if($project->description)<div style="font-size:12px; color:var(--muted); margin-top:1px;">{{ $project->description }}</div>@endif
                </div>
            </div>
            <div style="display:flex; align-items:center; gap:10px;">
                <span style="font-size:11px; font-family:var(--mono); padding:5px 10px; border-radius:6px; border:1px solid;
                    {{ $project->status === 'in_progress' ? 'color:#22d3ee; border-color:rgba(34,211,238,0.3); background:rgba(34,211,238,0.08);' :
                       ($project->status === 'completed' ? 'color:#4ade80; border-color:rgba(74,222,128,0.3); background:rgba(74,222,128,0.08);' :
                       ($project->status === 'on_hold' ? 'color:#fbbf24; border-color:rgba(251,191,36,0.3); background:rgba(251,191,36,0.08);' : 'color:var(--muted); border-color:var(--border2); background:transparent;')) }}">
                    {{ ucfirst(str_replace('_',' ',$project->status)) }}
                </span>
                <a href="{{ route('company.projects.edit', [$slug, $project]) }}" class="ptm-btn-ghost" style="text-decoration:none; font-size:12px; padding:6px 14px;">Edit Project</a>
            </div>
        </div>

        {{-- Progress / 6-month timeline / weekly execution (seo_dashboard style) --}}
        @php
            $timelineTasks = $tasks->map(fn($t) => [
                'id' => $t->id, 'title' => $t->title,
                'due' => $t->due_date?->format('Y-m-d'), 'status' => $t->status,
            ])->values();
            $timelineStart = ($project->start_date ?? $project->created_at)->format('Y-m-d');
        @endphp

        <style>
            .tl-metrics { display:grid; grid-template-columns:repeat(4,1fr); gap:12px; margin-bottom:14px; }
            .tl-mcard { background:var(--surface); border:1px solid var(--border); border-radius:12px; padding:14px 16px; }
            .tl-mlabel { font-size:11px; color:var(--muted); font-family:var(--mono); text-transform:uppercase; letter-spacing:0.06em; margin-bottom:6px; }
            .tl-mvalue { font-size:24px; font-weight:600; letter-spacing:-0.5px; }
            .tl-msub { font-size:12px; color:var(--muted); margin-top:4px; }
            .tl-pbar { height:3px; background:var(--border); border-radius:2px; margin-top:8px; }
            .tl-pfill { height:100%; border-radius:2px; transition:width 0.3s; }
            .tl-section { background:var(--surface); border:1px solid var(--border); border-radius:12px; padding:16px 18px; margin-bottom:14px; }
            .tl-stitle { font-size:11px; font-weight:600; color:var(--muted); text-transform:uppercase; letter-spacing:0.08em; font-family:var(--mono); margin-bottom:14px; }
            .tl-months { display:grid; grid-template-columns:repeat(6,1fr); gap:8px; }
            .tl-mblock { border:1px solid var(--border); border-radius:10px; overflow:hidden; cursor:pointer; transition:all 0.15s; }
            .tl-mblock:hover { border-color:var(--border2); }
            .tl-mblock.active { box-shadow:0 0 0 1px rgba(74,222,128,0.2); }
            .tl-mhead { padding:9px 11px; font-size:12px; font-weight:600; display:flex; align-items:center; justify-content:space-between; }
            .tl-mstatus { width:8px; height:8px; border-radius:50%; }
            .tl-mweeks { padding:0 11px 9px; display:flex; gap:4px; }
            .tl-wdot { flex:1; height:4px; border-radius:2px; }
            .tl-tabs { display:flex; gap:6px; margin-bottom:14px; flex-wrap:wrap; }
            .tl-tab { padding:6px 13px; border-radius:20px; border:1px solid var(--border); background:transparent; color:var(--muted); font-family:var(--font); font-size:12px; cursor:pointer; transition:all 0.15s; }
            .tl-tab:hover { border-color:var(--border2); color:var(--text); }
            .tl-tab.active { background:var(--surface2); border-color:var(--border2); color:var(--text); }
            .tl-tab.done { border-color:rgba(74,222,128,0.3); color:var(--accent); }
            .tl-task { display:flex; align-items:center; gap:10px; padding:9px 12px; background:var(--surface2); border-radius:8px; margin-bottom:6px; cursor:pointer; border:1px solid transparent; transition:all 0.15s; }
            .tl-task:hover { border-color:var(--border); }
            .tl-check { width:18px; height:18px; border-radius:5px; border:1.5px solid var(--border2); flex-shrink:0; display:flex; align-items:center; justify-content:center; transition:all 0.15s; }
            .tl-task.done .tl-check { background:var(--accent); border-color:var(--accent); }
        </style>

        <div id="timelineRoot"></div>

        <script>
        (function(){
            const TASKS = @json($timelineTasks);
            const GOALS = @json($project->month_goals ?: (object)[]);
            const PROJECT_ID = {{ $project->id }};
            const START = '{{ $timelineStart }}';
            const PAL = ['#4ade80','#22d3ee','#a78bfa','#fbbf24','#f87171','#fb923c'];
            const MN = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
            const pad = n => String(n).padStart(2,'0');
            const esc = s => (s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');

            const sd = new Date(parseInt(START.slice(0,4)), parseInt(START.slice(5,7))-1, 1);
            const months = [];
            for(let i=0;i<6;i++){ const d=new Date(sd.getFullYear(), sd.getMonth()+i, 1); months.push({ y:d.getFullYear(), m:d.getMonth()+1, key:d.getFullYear()+'-'+pad(d.getMonth()+1), label:MN[d.getMonth()]+' '+d.getFullYear(), color:PAL[i] }); }

            const weekRanges = (y,m)=>{ const dim=new Date(y,m,0).getDate(); return [[1,7],[8,14],[15,21],[22,dim]]; };
            const tasksInMonth = mo => TASKS.filter(t=>t.due && t.due.slice(0,7)===mo.key);
            const tasksInWeek = (mo,r)=> tasksInMonth(mo).filter(t=>{ const d=parseInt(t.due.slice(8,10)); return d>=r[0]&&d<=r[1]; });
            const weekDone = wt => wt.length>0 && wt.every(t=>t.status==='done');

            const now = new Date();
            const curKey = now.getFullYear()+'-'+pad(now.getMonth()+1);
            let activeMonth = months.findIndex(mo=>mo.key===curKey); if(activeMonth<0) activeMonth=0;
            let activeWeek = 0;

            function syncRow(id, ns){
                const sel=document.querySelector('#row-'+id+' .al-status'); if(sel){ sel.value=ns; if(typeof applyStatus==='function') applyStatus(sel); }
                if(typeof updateDone==='function') updateDone(id, ns);
            }
            window.tlSelectMonth = i => { activeMonth=i; activeWeek=0; render(); };
            window.tlSelectWeek = i => { activeWeek=i; render(); };
            window.tlSaveGoal = (month, val) => {
                GOALS[month] = val;
                fetch(`/${slug}/admin/projects/${PROJECT_ID}/goal`, { method:'PATCH', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrfToken,'Accept':'application/json'}, body:JSON.stringify({month, goal:val}) });
            };
            window.tlToggleTask = id => {
                const t=TASKS.find(x=>x.id===id); if(!t) return;
                const ns = t.status==='done' ? 'todo' : 'done'; t.status=ns;
                if(typeof patchField==='function') patchField(id,'status',ns);
                syncRow(id, ns); render();
            };
            window.tlToggleWeek = () => {
                const mo=months[activeMonth]; const wt=tasksInWeek(mo, weekRanges(mo.y,mo.m)[activeWeek]); if(!wt.length) return;
                const ns = wt.every(t=>t.status==='done') ? 'todo' : 'done';
                wt.forEach(t=>{ t.status=ns; if(typeof patchField==='function') patchField(t.id,'status',ns); syncRow(t.id, ns); });
                render();
            };

            function render(){
                const total=TASKS.length, done=TASKS.filter(t=>t.status==='done').length;
                const pct = total? Math.round(done/total*100):0;
                let doneWeeks=0; months.forEach(mo=>weekRanges(mo.y,mo.m).forEach(r=>{ if(weekDone(tasksInWeek(mo,r))) doneWeeks++; }));
                const am=months[activeMonth]; const amTasks=tasksInMonth(am);

                let h='';
                h+=`<div class="tl-metrics">
                    <div class="tl-mcard"><div class="tl-mlabel">Overall Progress</div><div class="tl-mvalue" style="color:${am.color}">${pct}%</div><div class="tl-pbar"><div class="tl-pfill" style="width:${pct}%;background:${am.color}"></div></div></div>
                    <div class="tl-mcard"><div class="tl-mlabel">Tasks Done</div><div class="tl-mvalue">${done}<span style="font-size:15px;color:var(--muted)"> / ${total}</span></div><div class="tl-msub">across all months</div></div>
                    <div class="tl-mcard"><div class="tl-mlabel">Weeks Complete</div><div class="tl-mvalue">${doneWeeks}<span style="font-size:15px;color:var(--muted)"> / 24</span></div><div class="tl-msub">fully-done weeks</div></div>
                    <div class="tl-mcard"><div class="tl-mlabel">Selected Month</div><div class="tl-mvalue" style="font-size:16px;line-height:1.3">${am.label}</div><div class="tl-msub">Month ${activeMonth+1} of 6 · ${amTasks.length} tasks</div></div>
                </div>`;

                h+=`<div class="tl-section"><div class="tl-stitle">6-Month Timeline</div><div class="tl-months">`;
                months.forEach((mo,mi)=>{
                    const ranges=weekRanges(mo.y,mo.m); const mt=tasksInMonth(mo);
                    const allDone=mt.length>0 && mt.every(t=>t.status==='done'); const someDone=mt.some(t=>t.status==='done');
                    const statusColor = allDone? '#4ade80' : someDone? mo.color : 'var(--border2)';
                    const active = mi===activeMonth;
                    h+=`<div class="tl-mblock ${active?'active':''}" onclick="tlSelectMonth(${mi})" style="${active?`border-color:${mo.color}`:''}">
                        <div class="tl-mhead" style="color:${active?mo.color:'var(--muted)'}">M${mi+1}<div class="tl-mstatus" style="background:${statusColor}"></div></div>
                        <div style="padding:0 11px 4px;font-size:11px;color:var(--muted);line-height:1.3">${mo.label}</div>
                        <div class="tl-mweeks">${ranges.map((r,wi)=>{ const wt=tasksInWeek(mo,r); const c=weekDone(wt)?mo.color:(active&&wi===activeWeek?mo.color+'55':(wt.length?'var(--border2)':'var(--border)')); return `<div class="tl-wdot" style="background:${c}"></div>`; }).join('')}</div>
                    </div>`;
                });
                h+=`</div></div>`;

                const goalVal = GOALS[activeMonth+1] || '';
                h+=`<div class="tl-section" style="border-color:${am.color}33;">
                    <div class="tl-stitle">Month ${activeMonth+1} Goal</div>
                    <textarea onchange="tlSaveGoal(${activeMonth+1}, this.value)" placeholder="Set the goal for ${am.label}..." style="width:100%;background:var(--surface2);border:1px solid var(--border2);border-radius:8px;color:var(--text);font-size:14px;font-family:var(--font);padding:11px 13px;resize:vertical;line-height:1.6;min-height:62px;">${esc(goalVal)}</textarea>
                </div>`;

                h+=`<div class="tl-section"><div class="tl-stitle">Weekly Execution — ${am.label}</div><div class="tl-tabs">`;
                const ranges=weekRanges(am.y,am.m);
                ranges.forEach((r,wi)=>{ const wt=tasksInWeek(am,r); const isDone=weekDone(wt); h+=`<button class="tl-tab ${wi===activeWeek?'active':''} ${isDone?'done':''}" onclick="tlSelectWeek(${wi})">${isDone?'✓ ':''}Week ${wi+1} · ${r[0]}–${r[1]} ${MN[am.m-1]} <span style="opacity:0.55">(${wt.length})</span></button>`; });
                h+=`</div>`;

                const wr=ranges[activeWeek]; const wt=tasksInWeek(am,wr);
                const wdoneCount=wt.filter(t=>t.status==='done').length; const wpct=wt.length?Math.round(wdoneCount/wt.length*100):0;
                h+=`<div style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px;margin-bottom:12px;">
                    <div><div style="font-size:15px;font-weight:600;">Week ${activeWeek+1}</div>
                    <div style="font-size:11px;color:var(--muted);margin-top:3px;">${wdoneCount}/${wt.length} tasks · ${wpct}% done</div>
                    <div class="tl-pbar" style="width:200px;margin-top:6px;"><div class="tl-pfill" style="width:${wpct}%;background:${am.color}"></div></div></div>
                    ${wt.length? `<button onclick="tlToggleWeek()" style="padding:7px 14px;border-radius:8px;border:1px solid ${wpct===100?am.color:'var(--border2)'};background:${wpct===100?am.color+'22':'transparent'};color:${wpct===100?am.color:'var(--muted)'};font-size:12px;cursor:pointer;font-family:var(--font);white-space:nowrap;">${wpct===100?'✓ Week Complete':'Mark all complete'}</button>`:''}
                </div>`;

                if(!wt.length){ h+=`<div style="font-size:12px;color:var(--muted);font-family:var(--mono);padding:6px 0;">No tasks scheduled for this week.</div>`; }
                else { h+=`<div>`; wt.forEach(t=>{ h+=`<div class="tl-task ${t.status==='done'?'done':''}" onclick="tlToggleTask(${t.id})">
                    <div class="tl-check"><svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="#0d0f12" stroke-width="3" style="opacity:${t.status==='done'?1:0}"><polyline points="20 6 9 17 4 12"/></svg></div>
                    <div style="font-size:13px;line-height:1.5;${t.status==='done'?'text-decoration:line-through;color:var(--muted);':''}">${esc(t.title)}</div>
                </div>`; }); h+=`</div>`; }
                h+=`</div>`;

                document.getElementById('timelineRoot').innerHTML=h;
            }
            render();
        })();
        </script>

        {{-- Tabs --}}
        <div style="display:flex; align-items:center; gap:20px; border-bottom:1px solid var(--border); margin-bottom:16px;">
            <button class="al-tab" :class="{ 'active': tab==='overview' }" @click="tab='overview'">Overview</button>
            <button class="al-tab" :class="{ 'active': tab==='list' }" @click="tab='list'">List</button>
            <button class="al-tab" :class="{ 'active': tab==='board' }" @click="tab='board'">Board</button>
            <button class="al-tab" :class="{ 'active': tab==='dashboard' }" @click="tab='dashboard'">Dashboard</button>
        </div>

        {{-- ============================ LIST TAB ============================ --}}
        <div x-show="tab==='list'">

            {{-- Toolbar --}}
            <div style="display:flex; align-items:center; justify-content:space-between; gap:10px; margin-bottom:12px;">
                <div style="display:flex; align-items:center; gap:8px;">
                    <button onclick="focusFirstAdd()" class="ptm-btn-primary" style="display:flex; align-items:center; gap:7px; font-size:13px;">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        Add task
                    </button>
                    <form method="POST" action="{{ route('company.sections.store', [$slug, $project]) }}" style="display:flex; align-items:center; gap:6px;">
                        @csrf
                        <input type="text" name="name" placeholder="Add section..." required class="ptm-input" style="font-size:12px; padding:7px 11px; width:170px;">
                        <button type="submit" class="ptm-btn-ghost" style="display:flex; align-items:center; gap:6px; font-size:12px; white-space:nowrap;">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                            Add section
                        </button>
                    </form>
                </div>
                <div style="display:flex; align-items:center; gap:2px;">
                    <div style="position:relative; display:flex; align-items:center;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--muted)" stroke-width="2" style="position:absolute; left:9px; pointer-events:none;"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        <input type="text" oninput="filterTasks(this.value)" placeholder="Search tasks..." class="ptm-input" style="font-size:12px; padding:6px 10px 6px 28px; width:180px;">
                    </div>
                </div>
            </div>

            {{-- Table --}}
            <div class="ptm-card" style="overflow-x:auto; overflow-y:visible;">
                <div class="al-table-inner" style="min-width:{{ $tableMinWidth }}px;">
                {{-- Column header --}}
                <div class="al-gridrow" style="display:grid; {{ $colGrid }} background:var(--surface2); border-bottom:1px solid var(--border); border-radius:12px 12px 0 0;">
                    <div class="al-cell ptm-section-title c-name">Name</div>
                    <div class="al-cell ptm-section-title c-due">Due date</div>
                    <div class="al-cell ptm-section-title c-assignee">Assignee</div>
                    <div class="al-cell ptm-section-title c-status">Status</div>
                    <div class="al-cell ptm-section-title c-priority">Priority</div>
                    @php $cfTypeIcon = ['text'=>'T','number'=>'#','date'=>'📅','select'=>'▾']; @endphp
                    @foreach($customFields as $cf)
                    <div class="al-cell ptm-section-title c-cf-{{ $cf->id }} al-cfhead" style="justify-content:space-between; gap:6px;">
                        <span style="display:flex; align-items:center; gap:6px; min-width:0;">
                            <span style="font-size:10px; color:var(--accent2); font-family:var(--mono); opacity:0.7; flex-shrink:0;">{{ $cfTypeIcon[$cf->type] ?? 'T' }}</span>
                            <span style="white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $cf->name }}</span>
                        </span>
                        <form method="POST" action="{{ route('company.custom_fields.destroy', [$slug, $cf->id]) }}" onsubmit="return confirm('Remove field “{{ addslashes($cf->name) }}”?')" class="al-cfdel" style="display:flex; flex-shrink:0;">
                            @csrf @method('DELETE')
                            <button type="submit" title="Remove field" style="background:none; border:none; color:var(--muted); cursor:pointer; padding:0 2px; font-size:11px;" onmouseover="this.style.color='var(--danger)'" onmouseout="this.style.color='var(--muted)'">✕</button>
                        </form>
                    </div>
                    @endforeach
                    <div class="al-cell c-actions" style="border-right:none; justify-content:center; position:relative; overflow:visible;">
                        <button onclick="event.stopPropagation(); document.getElementById('colMenu').classList.toggle('show')" title="Add / show fields" style="background:none; border:none; color:var(--muted); cursor:pointer; font-size:16px; line-height:1; padding:2px 6px; border-radius:6px;" onmouseover="this.style.color='var(--accent2)'; this.style.background='var(--surface)'" onmouseout="this.style.color='var(--muted)'; this.style.background='transparent'">+</button>
                        <div id="colMenu" class="col-menu" style="display:none; position:absolute; top:calc(100% + 6px); right:0; width:250px; background:var(--surface); border:1px solid var(--border2); border-radius:10px; box-shadow:0 10px 30px rgba(0,0,0,0.4); z-index:60; padding:10px; text-align:left;">
                            <div style="font-size:10px; color:var(--muted); font-family:var(--mono); text-transform:uppercase; letter-spacing:0.06em; margin-bottom:8px;">Show columns</div>
                            @foreach([['due','Due date'],['assignee','Assignee'],['status','Status'],['priority','Priority']] as $bc)
                            <label style="display:flex; align-items:center; gap:8px; padding:5px 6px; border-radius:6px; cursor:pointer; font-size:13px; color:var(--text);" onmouseover="this.style.background='var(--surface2)'" onmouseout="this.style.background='transparent'">
                                <input type="checkbox" class="col-toggle" value="{{ $bc[0] }}" checked onchange="toggleCol('{{ $bc[0] }}', this.checked)" style="width:15px; height:15px; cursor:pointer;">
                                {{ $bc[1] }}
                            </label>
                            @endforeach
                            @foreach($customFields as $cf)
                            <label style="display:flex; align-items:center; gap:8px; padding:5px 6px; border-radius:6px; cursor:pointer; font-size:13px; color:var(--text);" onmouseover="this.style.background='var(--surface2)'" onmouseout="this.style.background='transparent'">
                                <input type="checkbox" class="col-toggle" value="cf-{{ $cf->id }}" checked onchange="toggleCol('cf-{{ $cf->id }}', this.checked)" style="width:15px; height:15px; cursor:pointer;">
                                {{ $cf->name }} <span style="font-size:10px; color:var(--muted); font-family:var(--mono);">{{ $cf->type }}</span>
                            </label>
                            @endforeach
                            <div style="border-top:1px solid var(--border); margin:8px 0; padding-top:8px;">
                                <div style="font-size:10px; color:var(--muted); font-family:var(--mono); text-transform:uppercase; letter-spacing:0.06em; margin-bottom:8px;">Add new field</div>
                                <form method="POST" action="{{ route('company.custom_fields.store', [$slug, $project]) }}" style="display:flex; flex-direction:column; gap:7px;">
                                    @csrf
                                    <input type="text" name="name" placeholder="Field name" required class="ptm-input" style="font-size:12px; padding:7px 9px;">
                                    <select name="type" class="ptm-select" style="font-size:12px; padding:7px 9px;" onchange="document.getElementById('cfOpts').style.display = this.value==='select' ? 'block' : 'none'">
                                        <option value="text">Text</option>
                                        <option value="number">Number</option>
                                        <option value="date">Date</option>
                                        <option value="select">Dropdown</option>
                                    </select>
                                    <input type="text" id="cfOpts" name="options" placeholder="Options: a, b, c" class="ptm-input" style="font-size:12px; padding:7px 9px; display:none;">
                                    <button type="submit" class="ptm-btn-primary" style="font-size:12px; padding:7px 10px;">Add field</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                @foreach($groups as $group)
                <div x-data="{ open: true }" data-section-block>
                    {{-- Section header --}}
                    <div style="display:flex; align-items:center; gap:8px; padding:10px 14px; border-bottom:1px solid var(--border); background:var(--surface);">
                        <svg @click="open=!open" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" :style="open ? '' : 'transform:rotate(-90deg)'" style="color:var(--muted); transition:transform 0.15s; cursor:pointer; flex-shrink:0;"><path d="M19 9l-7 7-7-7"/></svg>

                        @if($group['id'])
                            <span id="secname-{{ $group['id'] }}" style="font-size:13px; font-weight:600; color:var(--text); cursor:pointer;" ondblclick="toggleSecRename({{ $group['id'] }})">{{ $group['name'] }}</span>
                            <form method="POST" action="{{ route('company.sections.update', [$slug, $group['id']]) }}" id="secform-{{ $group['id'] }}" style="display:none; gap:6px; align-items:center;">
                                @csrf @method('PATCH')
                                <input type="text" name="name" value="{{ $group['name'] }}" class="ptm-input" style="font-size:12px; padding:4px 8px; width:200px;" required>
                                <button type="submit" class="ptm-btn-primary" style="padding:4px 10px; font-size:11px;">Save</button>
                                <button type="button" onclick="toggleSecRename({{ $group['id'] }})" class="ptm-btn-ghost" style="padding:4px 9px; font-size:11px;">✕</button>
                            </form>
                        @else
                            <span style="font-size:13px; font-weight:600; color:var(--muted);">{{ $group['name'] }}</span>
                        @endif

                        <span style="font-size:11px; color:var(--muted); background:var(--surface2); padding:1px 7px; border-radius:10px; font-family:var(--mono);">{{ $group['tasks']->count() }}</span>

                        @if($group['id'])
                        <div style="margin-left:auto; display:flex; align-items:center; gap:2px;">
                            <button type="button" onclick="toggleSecRename({{ $group['id'] }})" title="Rename section" style="background:none; border:none; color:var(--muted); cursor:pointer; padding:4px;" onmouseover="this.style.color='var(--accent2)'" onmouseout="this.style.color='var(--muted)'">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                            </button>
                            <form method="POST" action="{{ route('company.sections.destroy', [$slug, $group['id']]) }}" onsubmit="return confirm('Delete section “{{ addslashes($group['name']) }}”? Its tasks move to (No section).')" style="display:inline;">
                                @csrf @method('DELETE')
                                <button type="submit" title="Delete section" style="background:none; border:none; color:var(--muted); cursor:pointer; padding:4px;" onmouseover="this.style.color='var(--danger)'" onmouseout="this.style.color='var(--muted)'">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                        </div>
                        @endif
                    </div>

                    {{-- Task rows --}}
                    <div x-show="open" class="al-tasklist" data-section-id="{{ $group['id'] }}">
                        @foreach($group['tasks'] as $task)
                            @php $sm = $statusMeta[$task->status] ?? $statusMeta['todo']; @endphp
                            <div class="al-row al-gridrow" id="row-{{ $task->id }}" data-title="{{ strtolower($task->title) }}" style="display:grid; {{ $colGrid }} border-bottom:1px solid var(--border); transition:background 0.1s;">
                                {{-- Name --}}
                                <div class="al-cell c-name" style="gap:6px;">
                                    <span class="al-drag" title="Drag to move / reorder">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><circle cx="9" cy="6" r="1.6"/><circle cx="15" cy="6" r="1.6"/><circle cx="9" cy="12" r="1.6"/><circle cx="15" cy="12" r="1.6"/><circle cx="9" cy="18" r="1.6"/><circle cx="15" cy="18" r="1.6"/></svg>
                                    </span>
                                    @if(($task->subtasks_count ?? 0) > 0)
                                    <span class="al-subtoggle" onclick="toggleSubs({{ $task->id }}, this)" title="{{ $task->subtasks_count }} subtask(s)" style="display:flex; align-items:center; gap:2px; cursor:pointer; color:var(--muted); flex-shrink:0; font-size:10px; font-family:var(--mono);">
                                        <svg class="al-chev" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="transform:rotate(-90deg); transition:transform 0.15s;"><path d="M19 9l-7 7-7-7"/></svg>
                                        {{ $task->subtasks_count }}
                                    </span>
                                    @else
                                    <span style="width:15px; flex-shrink:0;"></span>
                                    @endif
                                    <div id="done-{{ $task->id }}" onclick="cycleDone({{ $task->id }}, '{{ $task->status }}')" title="Toggle done" style="width:15px; height:15px; border-radius:50%; border:1.5px solid {{ $task->status === 'done' ? '#4ade80' : 'var(--border2)' }}; background:{{ $task->status === 'done' ? '#4ade80' : 'transparent' }}; flex-shrink:0; display:flex; align-items:center; justify-content:center; cursor:pointer;">
                                        @if($task->status === 'done')<svg width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="#0d0f12" stroke-width="3"><path d="M5 13l4 4L19 7"/></svg>@endif
                                    </div>
                                    <input class="al-name-input" value="{{ $task->title }}" onchange="patchField({{ $task->id }}, 'title', this.value)" onkeydown="if(event.key==='Enter'){this.blur();}">
                                    {{-- Hover-only meta/actions: comment · attachment · open details --}}
                                    <div class="al-row-actions">
                                        <button class="al-metaicon" onclick="openPanel({{ $task->id }})" title="Comments">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 11.5a8.38 8.38 0 01-.9 3.8 8.5 8.5 0 01-7.6 4.7 8.38 8.38 0 01-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 01-.9-3.8 8.5 8.5 0 014.7-7.6 8.38 8.38 0 013.8-.9h.5a8.48 8.48 0 018 8v.5z"/></svg>
                                            @if(($task->comments_count ?? 0) > 0){{ $task->comments_count }}@endif
                                        </button>
                                        <button class="al-metaicon" onclick="openPanel({{ $task->id }})" title="Attachments">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21.44 11.05l-9.19 9.19a6 6 0 01-8.49-8.49l9.19-9.19a4 4 0 015.66 5.66l-9.2 9.19a2 2 0 01-2.83-2.83l8.49-8.48"/></svg>
                                            @if(($task->attachments_count ?? 0) > 0){{ $task->attachments_count }}@endif
                                        </button>
                                        <button class="al-metaicon" onclick="openPanel({{ $task->id }})" title="Open details">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 3h6v6M14 10l7-7M21 14v5a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h5"/></svg>
                                        </button>
                                    </div>
                                </div>

                                {{-- Due date --}}
                                <div class="al-cell c-due">
                                    <input type="date" id="due-{{ $task->id }}" class="al-date" value="{{ $task->due_date?->format('Y-m-d') }}"
                                        style="{{ $task->due_date?->isPast() && $task->status !== 'done' ? 'color:#f87171;' : '' }}"
                                        onchange="patchField({{ $task->id }}, 'due_date', this.value).then(d => recolorDue({{ $task->id }}, d))">
                                </div>

                                {{-- Assignee --}}
                                <div class="al-cell c-assignee" x-data="{ open:false }" style="position:relative; overflow:visible;">
                                    <div @click="open=!open" id="asg-{{ $task->id }}" style="display:flex; align-items:center; gap:4px; cursor:pointer; min-height:24px; flex-wrap:wrap;">
                                        @forelse($task->assignees->take(3) as $a)
                                            <div class="al-avatar" title="{{ $a->name }}">{{ strtoupper(substr($a->name,0,1)) }}</div>
                                        @empty
                                            <span style="font-size:12px; color:var(--muted);">—</span>
                                        @endforelse
                                        @if($task->assignees->count() > 3)<span style="font-size:11px; color:var(--muted); font-family:var(--mono);">+{{ $task->assignees->count()-3 }}</span>@endif
                                    </div>
                                    <div class="asg-dropdown" x-show="open" @click.outside="open=false" x-cloak style="position:absolute; top:calc(100% + 4px); left:0; width:220px; background:var(--surface); border:1px solid var(--border2); border-radius:8px; box-shadow:0 8px 24px rgba(0,0,0,0.35); z-index:50; max-height:240px; overflow-y:auto; padding:6px;">
                                        @foreach($members as $member)
                                        <label style="display:flex; align-items:center; gap:8px; padding:7px 9px; border-radius:6px; cursor:pointer;" onmouseover="this.style.background='var(--surface2)'" onmouseout="this.style.background='transparent'">
                                            <input type="checkbox" value="{{ $member->id }}" {{ $task->assignees->contains('id',$member->id) ? 'checked' : '' }} onchange="onAssigneeChange(this, {{ $task->id }})" style="width:15px; height:15px; cursor:pointer;">
                                            <div class="al-avatar">{{ strtoupper(substr($member->name,0,1)) }}</div>
                                            <span style="font-size:13px; color:var(--text);">{{ $member->name }}</span>
                                        </label>
                                        @endforeach
                                    </div>
                                </div>

                                {{-- Status --}}
                                <div class="al-cell c-status">
                                    <select class="al-pill al-status" onchange="applyStatus(this); patchField({{ $task->id }}, 'status', this.value).then(()=>updateDone({{ $task->id }}, this.value))">
                                        @foreach(['todo'=>'To Do','in_progress'=>'In Progress','in_review'=>'In Review','done'=>'Done'] as $val=>$lbl)
                                        <option value="{{ $val }}" {{ $task->status===$val?'selected':'' }}>{{ $lbl }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Priority --}}
                                <div class="al-cell c-priority">
                                    <select class="al-pill al-pri" onchange="applyPri(this); patchField({{ $task->id }}, 'priority', this.value)">
                                        @foreach(['low'=>'Low','medium'=>'Medium','high'=>'High','urgent'=>'Urgent'] as $val=>$lbl)
                                        <option value="{{ $val }}" {{ $task->priority===$val?'selected':'' }}>{{ $lbl }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Custom fields --}}
                                @foreach($customFields as $cf)
                                    @php $cval = ($task->custom_values[$cf->id] ?? '') ?: (($task->custom_values[(string)$cf->id] ?? '')); @endphp
                                    <div class="al-cell c-cf-{{ $cf->id }}">
                                        @if($cf->type === 'select')
                                            <select class="al-pill" style="background:var(--surface2); color:var(--text);" onchange="saveCustom({{ $task->id }}, {{ $cf->id }}, this.value)">
                                                <option value="">—</option>
                                                @foreach(($cf->options ?? []) as $opt)
                                                <option value="{{ $opt }}" {{ $cval===$opt?'selected':'' }}>{{ $opt }}</option>
                                                @endforeach
                                            </select>
                                        @else
                                            <input type="{{ $cf->type === 'number' ? 'number' : ($cf->type === 'date' ? 'date' : 'text') }}" class="al-cfinput" value="{{ $cval }}" onchange="saveCustom({{ $task->id }}, {{ $cf->id }}, this.value)" placeholder="—">
                                        @endif
                                    </div>
                                @endforeach

                                {{-- Delete --}}
                                <div class="al-cell c-actions" style="border-right:none; justify-content:center;">
                                    <button onclick="deleteTask({{ $task->id }})" title="Delete task" style="background:none; border:none; color:var(--muted); cursor:pointer; padding:4px;" onmouseover="this.style.color='var(--danger)'" onmouseout="this.style.color='var(--muted)'">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </div>

                            {{-- Subtasks (collapsible) --}}
                            @if($task->subtasks->count() > 0)
                            <div id="subs-{{ $task->id }}" data-open="0" style="display:none;">
                                @foreach($task->subtasks as $sub)
                                <div class="al-subrow al-gridrow" id="row-{{ $sub->id }}" data-title="{{ strtolower($sub->title) }}" style="display:grid; {{ $colGrid }} border-bottom:1px solid var(--border); background:rgba(255,255,255,0.02); transition:background 0.1s;">
                                    <div class="al-cell c-name" style="gap:7px; padding-left:52px;">
                                        <div id="done-{{ $sub->id }}" onclick="cycleDone({{ $sub->id }}, '{{ $sub->status }}')" title="Toggle done" style="width:14px; height:14px; border-radius:50%; border:1.5px solid {{ $sub->status === 'done' ? '#4ade80' : 'var(--border2)' }}; background:{{ $sub->status === 'done' ? '#4ade80' : 'transparent' }}; flex-shrink:0; display:flex; align-items:center; justify-content:center; cursor:pointer;">
                                            @if($sub->status === 'done')<svg width="8" height="8" viewBox="0 0 24 24" fill="none" stroke="#0d0f12" stroke-width="3"><path d="M5 13l4 4L19 7"/></svg>@endif
                                        </div>
                                        <input class="al-name-input" value="{{ $sub->title }}" onchange="patchField({{ $sub->id }}, 'title', this.value)" onkeydown="if(event.key==='Enter'){this.blur();}" style="font-weight:400;">
                                        <button class="al-metaicon" onclick="openPanel({{ $sub->id }})" title="Open details" style="opacity:0.6;">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 3h6v6M14 10l7-7M21 14v5a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h5"/></svg>
                                        </button>
                                    </div>
                                    <div class="al-cell c-due">
                                        <input type="date" id="due-{{ $sub->id }}" class="al-date" value="{{ $sub->due_date?->format('Y-m-d') }}"
                                            style="{{ $sub->due_date?->isPast() && $sub->status !== 'done' ? 'color:#f87171;' : '' }}"
                                            onchange="patchField({{ $sub->id }}, 'due_date', this.value).then(d => recolorDue({{ $sub->id }}, d))">
                                    </div>
                                    <div class="al-cell c-assignee">
                                        @forelse($sub->assignees->take(3) as $a)
                                            <div class="al-avatar" title="{{ $a->name }}" style="margin-right:2px;">{{ strtoupper(substr($a->name,0,1)) }}</div>
                                        @empty
                                            <span style="font-size:12px; color:var(--muted);">—</span>
                                        @endforelse
                                    </div>
                                    <div class="al-cell c-status">
                                        <select class="al-pill al-status" onchange="applyStatus(this); patchField({{ $sub->id }}, 'status', this.value).then(()=>updateDone({{ $sub->id }}, this.value))">
                                            @foreach(['todo'=>'To Do','in_progress'=>'In Progress','in_review'=>'In Review','done'=>'Done'] as $val=>$lbl)
                                            <option value="{{ $val }}" {{ $sub->status===$val?'selected':'' }}>{{ $lbl }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="al-cell c-priority">
                                        <select class="al-pill al-pri" onchange="applyPri(this); patchField({{ $sub->id }}, 'priority', this.value)">
                                            @foreach(['low'=>'Low','medium'=>'Medium','high'=>'High','urgent'=>'Urgent'] as $val=>$lbl)
                                            <option value="{{ $val }}" {{ $sub->priority===$val?'selected':'' }}>{{ $lbl }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @foreach($customFields as $cf)
                                        @php $scval = ($sub->custom_values[$cf->id] ?? '') ?: (($sub->custom_values[(string)$cf->id] ?? '')); @endphp
                                        <div class="al-cell c-cf-{{ $cf->id }}">
                                            @if($cf->type === 'select')
                                                <select class="al-pill" style="background:var(--surface2); color:var(--text);" onchange="saveCustom({{ $sub->id }}, {{ $cf->id }}, this.value)">
                                                    <option value="">—</option>
                                                    @foreach(($cf->options ?? []) as $opt)
                                                    <option value="{{ $opt }}" {{ $scval===$opt?'selected':'' }}>{{ $opt }}</option>
                                                    @endforeach
                                                </select>
                                            @else
                                                <input type="{{ $cf->type === 'number' ? 'number' : ($cf->type === 'date' ? 'date' : 'text') }}" class="al-cfinput" value="{{ $scval }}" onchange="saveCustom({{ $sub->id }}, {{ $cf->id }}, this.value)" placeholder="—">
                                            @endif
                                        </div>
                                    @endforeach
                                    <div class="al-cell c-actions" style="border-right:none; justify-content:center;">
                                        <button onclick="deleteTask({{ $sub->id }})" title="Delete subtask" style="background:none; border:none; color:var(--muted); cursor:pointer; padding:4px;" onmouseover="this.style.color='var(--danger)'" onmouseout="this.style.color='var(--muted)'">
                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            @endif
                        @endforeach

                        {{-- Add task row --}}
                        <form method="POST" action="{{ route('company.tasks.quick_store', [$slug, $project]) }}" class="al-addrow" style="display:flex; align-items:center; gap:6px; padding:4px 14px; border-bottom:1px solid var(--border);">
                            @csrf
                            <input type="hidden" name="section_id" value="{{ $group['id'] }}">
                            <span style="color:var(--muted); font-size:14px; flex-shrink:0;">+</span>
                            <input type="text" name="title" placeholder="Add task..." required>
                        </form>
                    </div>
                </div>
                @endforeach
                </div>
            </div>
        </div>

        {{-- ============================ BOARD TAB ============================ --}}
        <div x-show="tab==='board'" x-cloak>
            <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:12px;">
                @foreach($statusMeta as $colStatus => $cfg)
                <div class="ptm-kanban-col" style="padding:12px;">
                    <div style="display:flex; align-items:center; gap:7px; margin-bottom:12px;">
                        <div style="width:7px; height:7px; border-radius:50%; background:{{ $cfg['color'] }};"></div>
                        <span style="font-size:10px; font-weight:600; color:var(--muted); text-transform:uppercase; letter-spacing:0.08em; font-family:var(--mono);">{{ $cfg['label'] }}</span>
                        <span style="margin-left:auto; font-size:11px; color:var(--muted); background:var(--surface2); padding:1px 7px; border-radius:10px; font-family:var(--mono);">{{ $tasks->where('status',$colStatus)->count() }}</span>
                    </div>
                    <div class="kanban-column" data-status="{{ $colStatus }}" style="display:flex; flex-direction:column; gap:8px; min-height:80px;">
                        @foreach($tasks->where('status',$colStatus) as $task)
                        <div class="kanban-task-wrapper" data-task-id="{{ $task->id }}" style="cursor:grab;">
                            <div class="ptm-kanban-card" style="padding:10px 12px;">
                                <div style="display:flex; align-items:flex-start; justify-content:space-between; gap:4px;">
                                    <a href="{{ route('company.tasks.show', [$slug, $task]) }}" class="task-title-link" style="font-size:13px; font-weight:500; color:var(--text); line-height:1.4; text-decoration:none; flex:1;">{{ $task->title }}</a>
                                    <button onclick="event.stopPropagation(); deleteTask({{ $task->id }})" class="task-actions" style="background:none; border:none; color:var(--muted); cursor:pointer; padding:2px; flex-shrink:0;" onmouseover="this.style.color='var(--danger)'" onmouseout="this.style.color='var(--muted)'">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                                @if($task->section)<div style="font-size:10px; font-family:var(--mono); color:var(--muted); margin-top:5px;">▸ {{ $task->section->name }}</div>@endif
                                <div style="display:flex; align-items:center; justify-content:space-between; margin-top:8px;">
                                    <span class="ptm-badge" style="border:1px solid; {{ $priorityStyles[$task->priority] ?? $priorityStyles['low'] }}">{{ ucfirst($task->priority) }}</span>
                                    <div style="display:flex; align-items:center; gap:6px;">
                                        @if($task->due_date)<span style="font-size:11px; font-family:var(--mono); {{ $task->due_date->isPast() && $task->status !== 'done' ? 'color:#f87171;' : 'color:var(--muted);' }}">{{ $task->due_date->format('d M') }}</span>@endif
                                        @foreach($task->assignees->take(2) as $a)<div class="al-avatar" style="width:20px; height:20px; font-size:9px;" title="{{ $a->name }}">{{ strtoupper(substr($a->name,0,1)) }}</div>@endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- ============================ OVERVIEW TAB ============================ --}}
        <div x-show="tab==='overview'" x-cloak>
            <div class="ptm-card" style="padding:18px 22px; max-width:640px;">
                <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:8px;">
                    <span style="font-size:13px; font-weight:600; color:var(--text);">Overall Progress</span>
                    <span style="font-size:14px; font-weight:600; font-family:var(--mono); color:#4ade80;">{{ $project->progressPercentage() }}%</span>
                </div>
                <div class="ptm-progress-track"><div class="ptm-progress-fill" style="width:{{ $project->progressPercentage() }}%;"></div></div>
                <div style="display:grid; grid-template-columns:repeat(2,1fr); gap:14px; margin-top:18px;">
                    <div><div style="font-size:11px; color:var(--muted); font-family:var(--mono);">TOTAL TASKS</div><div style="font-size:20px; font-weight:600; color:var(--text);">{{ $tasks->count() }}</div></div>
                    <div><div style="font-size:11px; color:var(--muted); font-family:var(--mono);">SECTIONS</div><div style="font-size:20px; font-weight:600; color:var(--text);">{{ $sections->count() }}</div></div>
                    <div><div style="font-size:11px; color:var(--muted); font-family:var(--mono);">IN PROGRESS</div><div style="font-size:20px; font-weight:600; color:#22d3ee;">{{ $tasks->where('status','in_progress')->count() }}</div></div>
                    <div><div style="font-size:11px; color:var(--muted); font-family:var(--mono);">DONE</div><div style="font-size:20px; font-weight:600; color:#4ade80;">{{ $tasks->where('status','done')->count() }}</div></div>
                </div>
                @if($project->description)<div style="margin-top:18px; padding-top:16px; border-top:1px solid var(--border); font-size:13px; color:var(--muted); line-height:1.6;">{{ $project->description }}</div>@endif
                @if($project->due_date)<div style="margin-top:12px; font-size:12px; font-family:var(--mono); {{ $project->due_date->isPast() && $project->status !== 'completed' ? 'color:#f87171;' : 'color:var(--muted);' }}">Due {{ $project->due_date->format('d M Y') }}</div>@endif
            </div>
        </div>

        {{-- ============================ DASHBOARD TAB ============================ --}}
        <div x-show="tab==='dashboard'" x-cloak>
            <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:12px;">
                @foreach($statusMeta as $st => $cfg)
                <div class="ptm-card" style="padding:16px 18px;">
                    <div style="display:flex; align-items:center; gap:7px; margin-bottom:10px;">
                        <div style="width:7px; height:7px; border-radius:50%; background:{{ $cfg['color'] }};"></div>
                        <span style="font-size:11px; color:var(--muted); font-family:var(--mono); text-transform:uppercase;">{{ $cfg['label'] }}</span>
                    </div>
                    <div style="font-size:28px; font-weight:600; color:var(--text);">{{ $tasks->where('status',$st)->count() }}</div>
                </div>
                @endforeach
            </div>
            <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:12px; margin-top:12px;">
                @foreach(['urgent'=>'#f87171','high'=>'#fb923c','medium'=>'#fbbf24','low'=>'#6b7385'] as $pr=>$clr)
                <div class="ptm-card" style="padding:16px 18px;">
                    <div style="font-size:11px; color:var(--muted); font-family:var(--mono); text-transform:uppercase; margin-bottom:10px;">{{ ucfirst($pr) }} priority</div>
                    <div style="font-size:28px; font-weight:600; color:{{ $clr }};">{{ $tasks->where('priority',$pr)->count() }}</div>
                </div>
                @endforeach
            </div>
        </div>

    </div>

    <script>
    const slug = '{{ $slug }}';
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    /* ---- field styling ---- */
    function statusStyle(v){
        const m={todo:['#6b7385','rgba(107,115,133,0.14)'],in_progress:['#22d3ee','rgba(34,211,238,0.14)'],in_review:['#a78bfa','rgba(167,139,250,0.14)'],done:['#4ade80','rgba(74,222,128,0.14)']};
        return m[v]||m.todo;
    }
    function applyStatus(sel){ const [c,b]=statusStyle(sel.value); sel.style.color=c; sel.style.background=b; }
    function priStyle(v){
        const m={urgent:['#f87171','rgba(248,113,113,0.12)'],high:['#fb923c','rgba(251,146,60,0.12)'],medium:['#fbbf24','rgba(251,191,36,0.12)'],low:['#9aa3b2','transparent']};
        return m[v]||m.low;
    }
    function applyPri(sel){ const [c,b]=priStyle(sel.value); sel.style.color=c; sel.style.background=b; }

    document.querySelectorAll('.al-status').forEach(applyStatus);
    document.querySelectorAll('.al-pri').forEach(applyPri);

    /* ---- inline PATCH ---- */
    function patchField(id, field, value){
        const body={}; body[field] = value;
        return fetch(`/${slug}/admin/tasks/${id}/inline`, {
            method:'PATCH',
            headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrfToken,'Accept':'application/json'},
            body: JSON.stringify(body)
        }).then(r=>r.json());
    }

    function recolorDue(id, d){
        const el=document.getElementById('due-'+id);
        if(!el) return;
        el.style.color = (d && d.task && d.task.overdue) ? '#f87171' : '';
    }

    function updateDone(id, status){
        const c=document.getElementById('done-'+id);
        if(!c) return;
        if(status==='done'){ c.style.background='#4ade80'; c.style.borderColor='#4ade80'; c.innerHTML='<svg width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="#0d0f12" stroke-width="3"><path d="M5 13l4 4L19 7"/></svg>'; }
        else { c.style.background='transparent'; c.style.borderColor='var(--border2)'; c.innerHTML=''; }
    }

    function cycleDone(id, current){
        const next = current==='done' ? 'todo' : 'done';
        const sel = document.querySelector(`#row-${id} .al-status`);
        if(sel){ sel.value=next; applyStatus(sel); }
        patchField(id,'status',next).then(()=>{ updateDone(id,next); document.getElementById('done-'+id).setAttribute('onclick', `cycleDone(${id}, '${next}')`); });
    }

    function onAssigneeChange(cb, id){
        const dd = cb.closest('.asg-dropdown');
        const ids = [...dd.querySelectorAll('input:checked')].map(c=>parseInt(c.value));
        patchField(id, 'assignees', ids).then(d=>renderAssignees(id, d.task.assignees));
    }
    function renderAssignees(id, arr){
        const cell=document.getElementById('asg-'+id);
        if(!cell) return;
        if(!arr.length){ cell.innerHTML='<span style="font-size:12px; color:var(--muted);">—</span>'; return; }
        let html = arr.slice(0,3).map(a=>`<div class="al-avatar" title="${a.name}">${a.initial}</div>`).join('');
        if(arr.length>3) html += `<span style="font-size:11px; color:var(--muted); font-family:var(--mono);">+${arr.length-3}</span>`;
        cell.innerHTML = html;
    }

    function deleteTask(id){
        if(!confirm('Delete this task?')) return;
        fetch(`/${slug}/admin/tasks/${id}`, { method:'DELETE', headers:{'X-CSRF-TOKEN':csrfToken,'Accept':'application/json'} })
            .then(r=>r.json()).then(()=>{ document.getElementById('row-'+id)?.remove(); document.querySelector(`.kanban-task-wrapper[data-task-id="${id}"]`)?.remove(); });
    }

    /* ---- section rename toggle ---- */
    function toggleSecRename(id){
        const form=document.getElementById('secform-'+id);
        const name=document.getElementById('secname-'+id);
        const showing = form.style.display==='flex';
        form.style.display = showing ? 'none' : 'flex';
        name.style.display = showing ? 'inline' : 'none';
        if(!showing){ form.querySelector('input[name=name]').focus(); }
    }

    /* ---- toolbar ---- */
    function focusFirstAdd(){
        const input=document.querySelector('.al-addrow input[name=title]');
        if(input){ input.focus(); input.scrollIntoView({block:'center', behavior:'smooth'}); }
    }
    function filterTasks(q){
        q=q.trim().toLowerCase();
        document.querySelectorAll('.al-row').forEach(row=>{
            row.style.display = (!q || row.dataset.title.includes(q)) ? '' : 'none';
        });
    }

    /* ---- board drag ---- */
    if (window.Sortable) {
        document.querySelectorAll('.kanban-column').forEach(function(column){
            Sortable.create(column, {
                group:'kanban', animation:150, ghostClass:'sortable-ghost', draggable:'.kanban-task-wrapper',
                filter:'.task-actions, a.task-title-link', preventOnFilter:false,
                onEnd:function(evt){
                    const taskId=evt.item.getAttribute('data-task-id');
                    const newStatus=evt.to.getAttribute('data-status');
                    const oldStatus=evt.from.getAttribute('data-status');
                    if(newStatus===oldStatus) return;
                    fetch('/'+slug+'/admin/tasks/'+taskId+'/status',{
                        method:'PATCH',
                        headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrfToken,'Accept':'application/json'},
                        body:JSON.stringify({status:newStatus})
                    }).then(r=>r.json()).then(d=>{ if(d.success) location.reload(); }).catch(()=>location.reload());
                }
            });
        });
    }

    /* ================= SUBTASK EXPAND / COLLAPSE ================= */
    function toggleSubs(id, el){
        const box=document.getElementById('subs-'+id); if(!box) return;
        const chev=el.querySelector('.al-chev');
        if(box.getAttribute('data-open')==='1'){ box.style.display='none'; box.setAttribute('data-open','0'); if(chev) chev.style.transform='rotate(-90deg)'; }
        else { box.style.display='block'; box.setAttribute('data-open','1'); if(chev) chev.style.transform='rotate(0deg)'; }
    }

    /* ================= CUSTOM FIELD VALUE ================= */
    function saveCustom(taskId, fieldId, value){
        fetch(`/${slug}/admin/tasks/${taskId}/custom`, {
            method:'PATCH',
            headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrfToken,'Accept':'application/json'},
            body: JSON.stringify({ field_id: fieldId, value: value })
        });
    }

    /* ================= COLUMN SHOW / HIDE ================= */
    const CF_IDS = @json($customFields->pluck('id'));
    const COL_DEFS = [
        {key:'name', w:'minmax(360px,1.5fr)', min:360},
        {key:'due', w:'150px', min:150},
        {key:'assignee', w:'175px', min:175},
        {key:'status', w:'150px', min:150},
        {key:'priority', w:'130px', min:130},
        ...CF_IDS.map(id=>({key:'cf-'+id, w:'160px', min:160})),
        {key:'actions', w:'44px', min:44},
    ];
    const COL_KEY = 'cols_hidden_{{ $project->id }}';
    function getHidden(){ try { return new Set(JSON.parse(localStorage.getItem(COL_KEY) || '[]')); } catch(e){ return new Set(); } }
    function applyCols(){
        const hidden = getHidden();
        COL_DEFS.forEach(c=>{
            if(c.key==='name' || c.key==='actions') return;
            const show = !hidden.has(c.key);
            document.querySelectorAll('.c-'+c.key).forEach(el=> el.style.display = show ? '' : 'none');
        });
        const visible = COL_DEFS.filter(c => c.key==='name' || c.key==='actions' || !hidden.has(c.key));
        const tmpl = visible.map(c=>c.w).join(' ');
        document.querySelectorAll('.al-gridrow').forEach(r=> r.style.gridTemplateColumns = tmpl);
        const inner = document.querySelector('.al-table-inner');
        if(inner) inner.style.minWidth = visible.reduce((a,c)=>a+c.min,0) + 'px';
        document.querySelectorAll('.col-toggle').forEach(cb=>{ cb.checked = !hidden.has(cb.value); });
    }
    function toggleCol(key, visible){
        const hidden = getHidden();
        if(visible) hidden.delete(key); else hidden.add(key);
        localStorage.setItem(COL_KEY, JSON.stringify([...hidden]));
        applyCols();
    }
    document.addEventListener('click', function(e){
        const menu=document.getElementById('colMenu');
        if(menu && menu.classList.contains('show') && !menu.contains(e.target) && !e.target.closest('[onclick*="colMenu"]')){
            menu.classList.remove('show');
        }
    });
    document.addEventListener('DOMContentLoaded', applyCols);
    applyCols();

    /* ================= LIST DRAG & DROP (reorder + move sections) ================= */
    if (window.Sortable) {
        document.querySelectorAll('.al-tasklist').forEach(function(list){
            Sortable.create(list, {
                group:'tasks', animation:150, handle:'.al-drag', draggable:'.al-row', ghostClass:'sortable-ghost',
                onEnd:function(evt){
                    const moved = evt.item;
                    // keep the moved task's subtask rows attached right below it
                    const mid = moved.id.replace('row-','');
                    const subs = document.getElementById('subs-'+mid);
                    if(subs){ moved.parentNode.insertBefore(subs, moved.nextSibling); }

                    const to = evt.to;
                    const sectionId = to.getAttribute('data-section-id') || '';
                    const ids = Array.from(to.querySelectorAll('.al-row')).map(r=>parseInt(r.id.replace('row-','')));
                    fetch(`/${slug}/admin/projects/{{ $project->id }}/tasks/reorder`, {
                        method:'POST',
                        headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrfToken,'Accept':'application/json'},
                        body:JSON.stringify({ section_id: sectionId, task_ids: ids })
                    });
                }
            });
        });
    }

    /* ================= TASK DETAIL PANEL (slide-in) ================= */
    let panelTaskId = null;
    let panelDirty = false;

    function openPanel(id){
        panelTaskId = id;
        document.getElementById('taskDrawer').classList.add('open');
        reloadPanel();
    }
    function closePanel(){
        document.getElementById('taskDrawer').classList.remove('open');
        if(panelDirty){ location.reload(); }
        panelTaskId = null;
    }
    function reloadPanel(){
        if(!panelTaskId) return;
        const body=document.getElementById('taskPanelBody');
        fetch(`/${slug}/admin/tasks/${panelTaskId}/panel`, { headers:{'Accept':'text/html'} })
            .then(r=>r.text()).then(html=>{
                body.innerHTML = html;
                body.querySelectorAll('.al-status').forEach(applyStatus);
                body.querySelectorAll('.al-pri').forEach(applyPri);
            });
    }

    function panelPatch(field, value){
        panelDirty = true;
        return patchField(panelTaskId, field, value);
    }
    function syncCompleteBtn(status){
        const btn=document.getElementById('panelComplete');
        if(!btn) return;
        if(status==='done'){ btn.style.background='rgba(74,222,128,0.15)'; btn.style.border='1px solid rgba(74,222,128,0.4)'; btn.style.color='#4ade80'; btn.innerHTML='<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 13l4 4L19 7"/></svg> Completed'; btn.setAttribute('onclick',"panelMarkComplete('done')"); }
        else { btn.style.background='var(--surface2)'; btn.style.border='1px solid var(--border2)'; btn.style.color='var(--text)'; btn.innerHTML='<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 13l4 4L19 7"/></svg> Mark complete'; btn.setAttribute('onclick',"panelMarkComplete('"+status+"')"); }
    }
    function panelMarkComplete(current){
        const next = current==='done' ? 'todo' : 'done';
        panelDirty = true;
        patchField(panelTaskId,'status',next).then(()=>reloadPanel());
    }
    function panelAssigneeChange(){
        const ids=[...document.querySelectorAll('.panel-asg-cb:checked')].map(c=>parseInt(c.value));
        panelDirty = true;
        patchField(panelTaskId,'assignees',ids).then(d=>{
            const sum=document.getElementById('panelAsgSummary');
            if(!sum) return;
            const a=d.task.assignees;
            if(!a.length){ sum.innerHTML='<div style="width:26px;height:26px;border-radius:50%;border:1.5px dashed var(--border2);display:flex;align-items:center;justify-content:center;color:var(--muted);"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2M9 7a4 4 0 108 0 4 4 0 00-8 0"/></svg></div><span style="font-size:13px;color:var(--muted);">No assignee</span>'; }
            else { sum.innerHTML = a.map(x=>`<div style="display:flex;align-items:center;gap:6px;"><div class="al-avatar">${x.initial}</div><span style="font-size:13px;color:var(--text);">${x.name}</span></div>`).join(''); }
        });
    }
    function panelAddComment(form){
        panelDirty = true;
        fetch(form.action, { method:'POST', headers:{'X-CSRF-TOKEN':csrfToken,'Accept':'application/json'}, body:new FormData(form) })
            .then(r=>r.json()).then(()=>reloadPanel());
        return false;
    }
    function panelDeleteComment(id){
        panelDirty = true;
        fetch(`/${slug}/admin/tasks/comments/${id}`, { method:'DELETE', headers:{'X-CSRF-TOKEN':csrfToken,'Accept':'application/json'} })
            .then(()=>reloadPanel());
    }
    function panelUpload(input){
        if(!input.files.length) return;
        panelDirty = true;
        const fd=new FormData(); fd.append('file', input.files[0]);
        fetch(input.dataset.action, { method:'POST', headers:{'X-CSRF-TOKEN':csrfToken,'Accept':'application/json'}, body:fd })
            .then(r=>r.json()).then(()=>reloadPanel());
    }
    function panelDeleteAttachment(id){
        panelDirty = true;
        fetch(`/${slug}/admin/tasks/attachments/${id}`, { method:'DELETE', headers:{'X-CSRF-TOKEN':csrfToken,'Accept':'application/json'} })
            .then(()=>reloadPanel());
    }
    function panelAddSubtask(form){
        panelDirty = true;
        fetch(form.action, { method:'POST', headers:{'X-CSRF-TOKEN':csrfToken,'Accept':'application/json'}, body:new FormData(form) })
            .then(r=>r.json()).then(()=>reloadPanel());
        return false;
    }
    function panelToggleSubtask(id, current){
        const next = current==='done' ? 'todo' : 'done';
        panelDirty = true;
        patchField(id,'status',next).then(()=>reloadPanel());
    }
    function panelDeleteTask(){
        if(!confirm('Delete this task?')) return;
        fetch(`/${slug}/admin/tasks/${panelTaskId}`, { method:'DELETE', headers:{'X-CSRF-TOKEN':csrfToken,'Accept':'application/json'} })
            .then(()=>{ panelDirty=false; document.getElementById('taskDrawer').classList.remove('open'); location.reload(); });
    }

    /* close assignee dropdown on outside click */
    document.addEventListener('click', function(e){
        const drop=document.getElementById('panelAsgDrop');
        const sum=document.getElementById('panelAsgSummary');
        if(drop && drop.classList.contains('show') && !drop.contains(e.target) && sum && !sum.contains(e.target)){
            drop.classList.remove('show');
        }
    });
    /* ESC closes panel */
    document.addEventListener('keydown', function(e){ if(e.key==='Escape'){ const d=document.getElementById('taskDrawer'); if(d.classList.contains('open')) closePanel(); } });
    </script>

    {{-- Slide-in task detail drawer --}}
    <div id="taskDrawer" class="task-drawer">
        <div class="task-drawer-overlay" onclick="closePanel()"></div>
        <div class="task-drawer-panel">
            <div style="display:flex; align-items:center; justify-content:space-between; padding:14px 20px; border-bottom:1px solid var(--border); flex-shrink:0;">
                <span style="font-size:12px; color:var(--muted); font-family:var(--mono); text-transform:uppercase; letter-spacing:0.06em;">Task details</span>
                <button onclick="closePanel()" style="background:none; border:none; color:var(--muted); cursor:pointer; padding:5px; display:flex;" onmouseover="this.style.color='var(--text)'" onmouseout="this.style.color='var(--muted)'">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>
            <div id="taskPanelBody" style="flex:1; overflow-y:auto; padding:22px 24px;">
                <div style="text-align:center; color:var(--muted); padding:40px; font-size:13px;">Loading…</div>
            </div>
        </div>
    </div>

    <style>
        [x-cloak]{display:none!important;}
        .task-drawer { position:fixed; inset:0; z-index:200; pointer-events:none; }
        .task-drawer .task-drawer-overlay { position:absolute; inset:0; background:rgba(0,0,0,0.5); opacity:0; transition:opacity 0.2s; }
        .task-drawer .task-drawer-panel { position:absolute; top:0; right:0; height:100%; width:560px; max-width:92vw; background:var(--surface); border-left:1px solid var(--border2); display:flex; flex-direction:column; transform:translateX(100%); transition:transform 0.25s ease; box-shadow:-10px 0 40px rgba(0,0,0,0.35); }
        .task-drawer.open { pointer-events:auto; }
        .task-drawer.open .task-drawer-overlay { opacity:1; }
        .task-drawer.open .task-drawer-panel { transform:translateX(0); }
        .panel-drop.show { display:block !important; }
    </style>

</x-company-layout>
