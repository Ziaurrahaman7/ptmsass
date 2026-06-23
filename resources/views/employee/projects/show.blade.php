<x-employee-layout :title="$project->name">

    @php
        $myId = auth()->id();
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
        $groups = [];
        foreach ($sections as $section) {
            $groups[] = ['id' => $section->id, 'name' => $section->name, 'tasks' => $tasks->where('section_id', $section->id)];
        }
        $noSection = $tasks->whereNull('section_id');
        $groups[] = ['id' => null, 'name' => '(No section)', 'tasks' => $noSection];
        $colGrid = 'grid-template-columns:minmax(0,1fr) 150px 175px 150px 130px;';
    @endphp

    <style>
        .al-cell { padding:9px 14px; border-right:1px solid var(--border); display:flex; align-items:center; min-width:0; }
        .al-row:hover { background:var(--surface2); }
        .al-pill { font-size:12px; font-family:var(--font); border:1px solid transparent; border-radius:6px; padding:5px 10px; cursor:pointer; width:100%; -webkit-appearance:none; appearance:none; }
        .al-pill:focus { outline:none; border-color:var(--accent2); }
        .al-tab { font-size:13px; font-weight:500; color:var(--muted); padding:10px 4px; cursor:pointer; border-bottom:2px solid transparent; background:none; border-top:none; border-left:none; border-right:none; font-family:var(--font); }
        .al-tab:hover { color:var(--text); }
        .al-tab.active { color:var(--text); border-bottom-color:var(--accent); }
        .al-avatar { width:24px; height:24px; border-radius:6px; background:rgba(74,222,128,0.2); color:#4ade80; font-size:10px; font-weight:600; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
        .al-badge { font-family:var(--mono); font-size:11px; border-radius:6px; padding:4px 9px; border:1px solid; }
        .al-kcol { background:var(--surface); border:1px solid var(--border); border-radius:12px; }
        .al-kcard { background:var(--surface2); border:1px solid transparent; border-radius:8px; transition:border 0.15s; }
        .al-kcard:hover { border-color:var(--border2); }
        .al-ptrack { height:4px; background:var(--border); border-radius:2px; }
        .al-pfill { height:100%; border-radius:2px; background:var(--accent); }
        [x-cloak]{display:none!important;}
    </style>

    <div x-data="{ tab: 'list' }">

        {{-- Header --}}
        <div style="display:flex; align-items:flex-start; justify-content:space-between; gap:12px; margin-bottom:10px;">
            <div style="display:flex; align-items:center; gap:11px;">
                <div style="width:32px; height:32px; border-radius:8px; background:var(--accent2); display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#0d0f12" stroke-width="2.5"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                </div>
                <div>
                    <div style="font-size:17px; font-weight:600; letter-spacing:-0.3px; color:var(--text);">{{ $project->name }}</div>
                    @if($project->description)<div style="font-size:12px; color:var(--muted); margin-top:1px;">{{ $project->description }}</div>@endif
                </div>
            </div>
            <span style="font-size:11px; font-family:var(--mono); padding:5px 10px; border-radius:6px; border:1px solid;
                {{ $project->status === 'in_progress' ? 'color:#22d3ee; border-color:rgba(34,211,238,0.3); background:rgba(34,211,238,0.08);' :
                   ($project->status === 'completed' ? 'color:#4ade80; border-color:rgba(74,222,128,0.3); background:rgba(74,222,128,0.08);' :
                   ($project->status === 'on_hold' ? 'color:#fbbf24; border-color:rgba(251,191,36,0.3); background:rgba(251,191,36,0.08);' : 'color:var(--muted); border-color:var(--border2); background:transparent;')) }}">
                {{ ucfirst(str_replace('_',' ',$project->status)) }}
            </span>
        </div>

        {{-- Progress / 6-month timeline / weekly execution (seo_dashboard style) --}}
        @php
            $timelineTasks = $tasks->map(fn($t) => [
                'id' => $t->id, 'title' => $t->title,
                'due' => $t->due_date?->format('Y-m-d'), 'status' => $t->status,
                'mine' => ($t->assigned_to === $myId || $t->assignees->contains('id', $myId)),
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
            .tl-task { display:flex; align-items:center; gap:10px; padding:9px 12px; background:var(--surface2); border-radius:8px; margin-bottom:6px; border:1px solid transparent; transition:all 0.15s; }
            .tl-task.mine { cursor:pointer; }
            .tl-task.mine:hover { border-color:var(--border); }
            .tl-check { width:18px; height:18px; border-radius:5px; border:1.5px solid var(--border2); flex-shrink:0; display:flex; align-items:center; justify-content:center; transition:all 0.15s; }
            .tl-task.done .tl-check { background:var(--accent); border-color:var(--accent); }
        </style>

        <div id="timelineRoot"></div>

        <script>
        (function(){
            const TASKS = @json($timelineTasks);
            const GOALS = @json($project->month_goals ?: (object)[]);
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

            function setStatus(id, ns){
                fetch(`/${slug}/tasks/${id}/status`, { method:'PATCH', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrfToken,'Accept':'application/json'}, body:JSON.stringify({status:ns}) });
            }
            window.tlSelectMonth = i => { activeMonth=i; activeWeek=0; render(); };
            window.tlSelectWeek = i => { activeWeek=i; render(); };
            window.tlToggleTask = id => {
                const t=TASKS.find(x=>x.id===id); if(!t || !t.mine) return;
                const ns = t.status==='done' ? 'todo' : 'done'; t.status=ns; setStatus(id, ns); render();
            };
            window.tlToggleWeek = () => {
                const mo=months[activeMonth]; const wt=tasksInWeek(mo, weekRanges(mo.y,mo.m)[activeWeek]).filter(t=>t.mine); if(!wt.length) return;
                const ns = wt.every(t=>t.status==='done') ? 'todo' : 'done';
                wt.forEach(t=>{ t.status=ns; setStatus(t.id, ns); });
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
                    <div style="font-size:14px;color:${goalVal?'var(--text)':'var(--muted)'};line-height:1.6;white-space:pre-wrap;">${goalVal? esc(goalVal) : 'No goal set for this month.'}</div>
                </div>`;

                h+=`<div class="tl-section"><div class="tl-stitle">Weekly Execution — ${am.label}</div><div class="tl-tabs">`;
                const ranges=weekRanges(am.y,am.m);
                ranges.forEach((r,wi)=>{ const wt=tasksInWeek(am,r); const isDone=weekDone(wt); h+=`<button class="tl-tab ${wi===activeWeek?'active':''} ${isDone?'done':''}" onclick="tlSelectWeek(${wi})">${isDone?'✓ ':''}Week ${wi+1} · ${r[0]}–${r[1]} ${MN[am.m-1]} <span style="opacity:0.55">(${wt.length})</span></button>`; });
                h+=`</div>`;

                const wr=ranges[activeWeek]; const wt=tasksInWeek(am,wr);
                const wdoneCount=wt.filter(t=>t.status==='done').length; const wpct=wt.length?Math.round(wdoneCount/wt.length*100):0;
                const mineInWeek=wt.filter(t=>t.mine).length;
                h+=`<div style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px;margin-bottom:12px;">
                    <div><div style="font-size:15px;font-weight:600;">Week ${activeWeek+1}</div>
                    <div style="font-size:11px;color:var(--muted);margin-top:3px;">${wdoneCount}/${wt.length} tasks · ${wpct}% done</div>
                    <div class="tl-pbar" style="width:200px;margin-top:6px;"><div class="tl-pfill" style="width:${wpct}%;background:${am.color}"></div></div></div>
                    ${mineInWeek? `<button onclick="tlToggleWeek()" style="padding:7px 14px;border-radius:8px;border:1px solid var(--border2);background:transparent;color:var(--muted);font-size:12px;cursor:pointer;font-family:var(--font);white-space:nowrap;">Toggle my tasks</button>`:''}
                </div>`;

                if(!wt.length){ h+=`<div style="font-size:12px;color:var(--muted);font-family:var(--mono);padding:6px 0;">No tasks scheduled for this week.</div>`; }
                else { h+=`<div>`; wt.forEach(t=>{ h+=`<div class="tl-task ${t.status==='done'?'done':''} ${t.mine?'mine':''}" ${t.mine?`onclick="tlToggleTask(${t.id})"`:'title="Not assigned to you"'}>
                    <div class="tl-check"><svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="#0d0f12" stroke-width="3" style="opacity:${t.status==='done'?1:0}"><polyline points="20 6 9 17 4 12"/></svg></div>
                    <div style="font-size:13px;line-height:1.5;flex:1;${t.status==='done'?'text-decoration:line-through;color:var(--muted);':''}">${esc(t.title)}</div>
                    ${t.mine?'':'<span style="font-size:10px;color:var(--muted);font-family:var(--mono);">read-only</span>'}
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

            {{-- Toolbar (read-only: search only) --}}
            <div style="display:flex; align-items:center; justify-content:flex-end; gap:10px; margin-bottom:12px;">
                <div style="position:relative; display:flex; align-items:center;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--muted)" stroke-width="2" style="position:absolute; left:9px; pointer-events:none;"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    <input type="text" oninput="filterTasks(this.value)" placeholder="Search tasks..." class="ptm-input" style="font-size:12px; padding:6px 10px 6px 28px; width:180px;">
                </div>
            </div>

            <div class="ptm-card" style="overflow:hidden;">
                {{-- Column header --}}
                <div style="display:grid; {{ $colGrid }} background:var(--surface2); border-bottom:1px solid var(--border);">
                    <div class="al-cell ptm-section-title">Name</div>
                    <div class="al-cell ptm-section-title">Due date</div>
                    <div class="al-cell ptm-section-title">Assignee</div>
                    <div class="al-cell ptm-section-title">Status</div>
                    <div class="al-cell ptm-section-title" style="border-right:none;">Priority</div>
                </div>

                @foreach($groups as $group)
                <div x-data="{ open: true }">
                    {{-- Section header --}}
                    <div style="display:flex; align-items:center; gap:8px; padding:10px 14px; border-bottom:1px solid var(--border); background:var(--surface);">
                        <svg @click="open=!open" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" :style="open ? '' : 'transform:rotate(-90deg)'" style="color:var(--muted); transition:transform 0.15s; cursor:pointer; flex-shrink:0;"><path d="M19 9l-7 7-7-7"/></svg>
                        <span style="font-size:13px; font-weight:600; color:{{ $group['id'] ? 'var(--text)' : 'var(--muted)' }};">{{ $group['name'] }}</span>
                        <span style="font-size:11px; color:var(--muted); background:var(--surface2); padding:1px 7px; border-radius:10px; font-family:var(--mono);">{{ $group['tasks']->count() }}</span>
                    </div>

                    {{-- Rows --}}
                    <div x-show="open">
                        @forelse($group['tasks'] as $task)
                            @php
                                $isMine = $task->assigned_to === $myId || $task->assignees->contains('id', $myId);
                                $sm = $statusMeta[$task->status] ?? $statusMeta['todo'];
                            @endphp
                            <div class="al-row" data-title="{{ strtolower($task->title) }}" style="display:grid; {{ $colGrid }} border-bottom:1px solid var(--border); transition:background 0.1s;">
                                {{-- Name --}}
                                <div class="al-cell" style="gap:9px;">
                                    <div style="width:15px; height:15px; border-radius:50%; border:1.5px solid {{ $task->status === 'done' ? '#4ade80' : 'var(--border2)' }}; background:{{ $task->status === 'done' ? '#4ade80' : 'transparent' }}; flex-shrink:0; display:flex; align-items:center; justify-content:center;">
                                        @if($task->status === 'done')<svg width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="#0d0f12" stroke-width="3"><path d="M5 13l4 4L19 7"/></svg>@endif
                                    </div>
                                    <span onclick="openPanel({{ $task->id }})" style="font-size:13px; font-weight:500; color:var(--text); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; cursor:pointer; flex:1;" onmouseover="this.style.color='var(--accent2)'" onmouseout="this.style.color='var(--text)'">{{ $task->title }}</span>
                                    @if(($task->subtasks_count ?? 0) > 0)
                                    <span title="{{ $task->subtasks_count }} subtask(s)" style="display:flex; align-items:center; gap:3px; flex-shrink:0; color:var(--muted); font-size:11px; font-family:var(--mono);">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 3v12"/><circle cx="18" cy="6" r="3"/><circle cx="6" cy="18" r="3"/><path d="M18 9a9 9 0 01-9 9"/></svg>
                                        {{ $task->subtasks_count }}
                                    </span>
                                    @endif
                                    <button onclick="openPanel({{ $task->id }})" title="Open details" style="flex-shrink:0; color:var(--muted); background:none; border:none; cursor:pointer; display:flex; align-items:center; gap:3px; padding:3px 5px; border-radius:6px;" onmouseover="this.style.color='var(--accent2)'; this.style.background='var(--surface2)'" onmouseout="this.style.color='var(--muted)'; this.style.background='transparent'">
                                        @if(($task->comments_count ?? 0) > 0)<span style="font-size:11px; font-family:var(--mono);">{{ $task->comments_count }}</span><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 11.5a8.38 8.38 0 01-.9 3.8 8.5 8.5 0 01-7.6 4.7 8.38 8.38 0 01-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 01-.9-3.8 8.5 8.5 0 014.7-7.6 8.38 8.38 0 013.8-.9h.5a8.48 8.48 0 018 8v.5z"/></svg>@endif
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 3h6v6M14 10l7-7M21 14v5a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h5"/></svg>
                                    </button>
                                </div>

                                {{-- Due date --}}
                                <div class="al-cell" style="font-size:12px; font-family:var(--mono); {{ $task->due_date?->isPast() && $task->status !== 'done' ? 'color:#f87171;' : 'color:var(--muted);' }}">
                                    {{ $task->due_date?->format('d M Y') ?? '—' }}
                                </div>

                                {{-- Assignee --}}
                                <div class="al-cell">
                                    @if($task->assignees->count() > 0)
                                        <div style="display:flex; align-items:center; gap:4px;">
                                            @foreach($task->assignees->take(3) as $a)
                                                <div class="al-avatar" title="{{ $a->name }}">{{ strtoupper(substr($a->name,0,1)) }}</div>
                                            @endforeach
                                            @if($task->assignees->count() > 3)<span style="font-size:11px; color:var(--muted); font-family:var(--mono);">+{{ $task->assignees->count()-3 }}</span>@endif
                                        </div>
                                    @else
                                        <span style="font-size:12px; color:var(--muted);">—</span>
                                    @endif
                                </div>

                                {{-- Status (editable only for own tasks) --}}
                                <div class="al-cell">
                                    @if($isMine)
                                        <form method="POST" action="{{ route('employee.tasks.status', [$slug, $task]) }}" style="width:100%;">
                                            @csrf @method('PATCH')
                                            <select name="status" class="al-pill al-status" onchange="applyStatus(this); this.form.submit()">
                                                @foreach(['todo'=>'To Do','in_progress'=>'In Progress','in_review'=>'In Review','done'=>'Done'] as $val=>$lbl)
                                                    <option value="{{ $val }}" {{ $task->status===$val?'selected':'' }}>{{ $lbl }}</option>
                                                @endforeach
                                            </select>
                                        </form>
                                    @else
                                        <span class="al-badge" style="border-color:var(--border2); color:{{ $sm['color'] }};">{{ $sm['label'] }}</span>
                                    @endif
                                </div>

                                {{-- Priority (read-only) --}}
                                <div class="al-cell" style="border-right:none;">
                                    <span class="al-badge" style="{{ $priorityStyles[$task->priority] ?? $priorityStyles['low'] }}">{{ ucfirst($task->priority) }}</span>
                                </div>
                            </div>
                        @empty
                            <div style="padding:11px 14px 11px 35px; font-size:12px; color:var(--muted); font-family:var(--mono); border-bottom:1px solid var(--border);">No tasks in this section</div>
                        @endforelse
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- ============================ BOARD TAB (read-only) ============================ --}}
        <div x-show="tab==='board'" x-cloak>
            <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:12px;">
                @foreach($statusMeta as $colStatus => $cfg)
                <div class="al-kcol" style="padding:12px;">
                    <div style="display:flex; align-items:center; gap:7px; margin-bottom:12px;">
                        <div style="width:7px; height:7px; border-radius:50%; background:{{ $cfg['color'] }};"></div>
                        <span style="font-size:10px; font-weight:600; color:var(--muted); text-transform:uppercase; letter-spacing:0.08em; font-family:var(--mono);">{{ $cfg['label'] }}</span>
                        <span style="margin-left:auto; font-size:11px; color:var(--muted); background:var(--surface2); padding:1px 7px; border-radius:10px; font-family:var(--mono);">{{ $tasks->where('status',$colStatus)->count() }}</span>
                    </div>
                    <div style="display:flex; flex-direction:column; gap:8px; min-height:60px;">
                        @foreach($tasks->where('status',$colStatus) as $task)
                        <a href="{{ route('employee.tasks.show', [$slug, $task]) }}" class="al-kcard" style="padding:10px 12px; text-decoration:none; display:block;">
                            <div style="font-size:13px; font-weight:500; color:var(--text); line-height:1.4;">{{ $task->title }}</div>
                            @if($task->section)<div style="font-size:10px; font-family:var(--mono); color:var(--muted); margin-top:5px;">▸ {{ $task->section->name }}</div>@endif
                            <div style="display:flex; align-items:center; justify-content:space-between; margin-top:8px;">
                                <span class="al-badge" style="font-size:10px; padding:2px 7px; {{ $priorityStyles[$task->priority] ?? $priorityStyles['low'] }}">{{ ucfirst($task->priority) }}</span>
                                <div style="display:flex; align-items:center; gap:6px;">
                                    @if($task->due_date)<span style="font-size:11px; font-family:var(--mono); {{ $task->due_date->isPast() && $task->status !== 'done' ? 'color:#f87171;' : 'color:var(--muted);' }}">{{ $task->due_date->format('d M') }}</span>@endif
                                    @foreach($task->assignees->take(2) as $a)<div class="al-avatar" style="width:20px; height:20px; font-size:9px;" title="{{ $a->name }}">{{ strtoupper(substr($a->name,0,1)) }}</div>@endforeach
                                </div>
                            </div>
                        </a>
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
                <div class="al-ptrack"><div class="al-pfill" style="width:{{ $project->progressPercentage() }}%;"></div></div>
                <div style="display:grid; grid-template-columns:repeat(2,1fr); gap:14px; margin-top:18px;">
                    <div><div style="font-size:11px; color:var(--muted); font-family:var(--mono);">TOTAL TASKS</div><div style="font-size:20px; font-weight:600; color:var(--text);">{{ $tasks->count() }}</div></div>
                    <div><div style="font-size:11px; color:var(--muted); font-family:var(--mono);">SECTIONS</div><div style="font-size:20px; font-weight:600; color:var(--text);">{{ $sections->count() }}</div></div>
                    <div><div style="font-size:11px; color:var(--muted); font-family:var(--mono);">IN PROGRESS</div><div style="font-size:20px; font-weight:600; color:#22d3ee;">{{ $tasks->where('status','in_progress')->count() }}</div></div>
                    <div><div style="font-size:11px; color:var(--muted); font-family:var(--mono);">DONE</div><div style="font-size:20px; font-weight:600; color:#4ade80;">{{ $tasks->where('status','done')->count() }}</div></div>
                </div>
                @if($project->due_date)<div style="margin-top:14px; font-size:12px; font-family:var(--mono); {{ $project->due_date->isPast() && $project->status !== 'completed' ? 'color:#f87171;' : 'color:var(--muted);' }}">Due {{ $project->due_date->format('d M Y') }}</div>@endif
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
    function statusStyle(v){
        const m={todo:['#6b7385','rgba(107,115,133,0.14)'],in_progress:['#22d3ee','rgba(34,211,238,0.14)'],in_review:['#a78bfa','rgba(167,139,250,0.14)'],done:['#4ade80','rgba(74,222,128,0.14)']};
        return m[v]||m.todo;
    }
    function applyStatus(sel){ const [c,b]=statusStyle(sel.value); sel.style.color=c; sel.style.background=b; }
    document.querySelectorAll('.al-status').forEach(applyStatus);

    function filterTasks(q){
        q=q.trim().toLowerCase();
        document.querySelectorAll('.al-row').forEach(row=>{
            row.style.display = (!q || row.dataset.title.includes(q)) ? '' : 'none';
        });
    }

    /* ================= TASK DETAIL PANEL (slide-in) ================= */
    /* slug & csrfToken are defined globally by the employee layout. */
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
        fetch(`/${slug}/tasks/${panelTaskId}/panel`, { headers:{'Accept':'text/html'} })
            .then(r=>r.text()).then(html=>{
                body.innerHTML = html;
                body.querySelectorAll('.al-status').forEach(applyStatus);
            });
    }

    function empPanelStatus(value){
        panelDirty = true;
        fetch(`/${slug}/tasks/${panelTaskId}/status`, {
            method:'PATCH',
            headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrfToken,'Accept':'application/json'},
            body: JSON.stringify({status:value})
        }).then(r=>r.json()).then(()=>reloadPanel());
    }
    function empMarkComplete(current){
        empPanelStatus(current==='done' ? 'todo' : 'done');
    }
    function empAddComment(form){
        panelDirty = true;
        fetch(form.action, { method:'POST', headers:{'X-CSRF-TOKEN':csrfToken,'Accept':'application/json'}, body:new FormData(form) })
            .then(r=>r.json()).then(()=>reloadPanel());
        return false;
    }
    function empDeleteComment(id){
        panelDirty = true;
        fetch(`/${slug}/tasks/comments/${id}`, { method:'DELETE', headers:{'X-CSRF-TOKEN':csrfToken,'Accept':'application/json'} })
            .then(()=>reloadPanel());
    }
    function empUpload(input){
        if(!input.files.length) return;
        panelDirty = true;
        const fd=new FormData(); fd.append('file', input.files[0]);
        fetch(input.dataset.action, { method:'POST', headers:{'X-CSRF-TOKEN':csrfToken,'Accept':'application/json'}, body:fd })
            .then(r=>r.json()).then(()=>reloadPanel());
    }
    function empDeleteAttachment(id){
        panelDirty = true;
        fetch(`/${slug}/tasks/attachments/${id}`, { method:'DELETE', headers:{'X-CSRF-TOKEN':csrfToken,'Accept':'application/json'} })
            .then(()=>reloadPanel());
    }
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
        .task-drawer { position:fixed; inset:0; z-index:200; pointer-events:none; }
        .task-drawer .task-drawer-overlay { position:absolute; inset:0; background:rgba(0,0,0,0.5); opacity:0; transition:opacity 0.2s; }
        .task-drawer .task-drawer-panel { position:absolute; top:0; right:0; height:100%; width:560px; max-width:92vw; background:var(--surface); border-left:1px solid var(--border2); display:flex; flex-direction:column; transform:translateX(100%); transition:transform 0.25s ease; box-shadow:-10px 0 40px rgba(0,0,0,0.35); }
        .task-drawer.open { pointer-events:auto; }
        .task-drawer.open .task-drawer-overlay { opacity:1; }
        .task-drawer.open .task-drawer-panel { transform:translateX(0); }
    </style>

</x-employee-layout>
