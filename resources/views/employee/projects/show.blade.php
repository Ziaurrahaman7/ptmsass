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
        $cfWidths = str_repeat(' 160px', $customFields->count());
        $colGrid = 'grid-template-columns:minmax(360px,1.5fr) 150px 175px 150px 130px'.$cfWidths.' 44px;';
        $tableMinWidth = 360 + 649 + (160 * $customFields->count());
        $cfTypeIcon = ['text'=>'T','number'=>'#','date'=>'📅','select'=>'▾'];
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
        .al-gridrow > .al-cell:last-child { border-right:none; }
        .col-menu.show { display:block !important; }
        .al-cfval { font-size:13px; color:var(--text); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .tb-btn { display:flex; align-items:center; gap:6px; background:none; border:none; color:var(--muted); font-size:13px; font-family:var(--font); cursor:pointer; padding:6px 10px; border-radius:7px; }
        .tb-btn:hover { color:var(--text); background:var(--surface2); }
        .tb-menu { display:none; position:absolute; top:calc(100% + 6px); right:0; background:var(--surface); border:1px solid var(--border2); border-radius:10px; box-shadow:0 10px 30px rgba(0,0,0,0.4); z-index:60; padding:8px; text-align:left; }
        .tb-menu.show { display:block; }
        .tb-mlabel { font-size:10px; color:var(--muted); font-family:var(--mono); text-transform:uppercase; letter-spacing:0.06em; padding:2px 8px 5px; }
        .tb-opt { display:flex; align-items:center; gap:8px; padding:6px 8px; border-radius:6px; cursor:pointer; font-size:13px; color:var(--text); }
        .tb-opt:hover { background:var(--surface2); }
        .tb-opt input { width:15px; height:15px; cursor:pointer; }
        .qf-pill { display:inline-flex; align-items:center; gap:7px; padding:8px 14px; border-radius:20px; border:1px solid var(--border2); background:transparent; color:var(--text); font-size:13px; font-family:var(--font); cursor:pointer; transition:all 0.15s; }
        .qf-pill:hover { border-color:var(--muted); }
        .qf-pill.active { background:rgba(34,211,238,0.12); border-color:rgba(34,211,238,0.5); color:var(--accent2); }
        .tb-field { display:flex; align-items:center; gap:10px; width:100%; text-align:left; background:none; border:none; color:var(--text); font-size:14px; font-family:var(--font); cursor:pointer; padding:9px 10px; border-radius:7px; }
        .tb-field:hover { background:var(--surface2); }
        .tb-field svg { color:var(--muted); flex-shrink:0; }
        .fg-group { border:1px solid var(--border); border-radius:8px; padding:10px 12px; background:var(--surface2); }
        .fg-head { display:flex; align-items:center; justify-content:space-between; margin-bottom:8px; }
        .fg-title { font-size:12px; font-weight:600; color:var(--text); }
        .fg-opts { display:flex; flex-wrap:wrap; gap:6px 14px; }
        .fg-opt { display:flex; align-items:center; gap:7px; font-size:13px; color:var(--text); cursor:pointer; }
        .fg-opt input { width:15px; height:15px; cursor:pointer; }
        .group-none .al-sechead { display:none; }
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

            /* ---- widget (section) show/hide ---- */
            const WIDGETS = [{key:'metrics',label:'Progress'},{key:'timeline',label:'6-Month Timeline'},{key:'goal',label:'Month Goal'},{key:'weekly',label:'Weekly Execution'}];
            const W_KEY = 'widgets_hidden_emp_{{ $project->id }}';
            function getHiddenW(){ try { return new Set(JSON.parse(localStorage.getItem(W_KEY)||'[]')); } catch(e){ return new Set(); } }
            function saveHiddenW(s){ localStorage.setItem(W_KEY, JSON.stringify([...s])); }
            window.hideWidget = k => { const s=getHiddenW(); s.add(k); saveHiddenW(s); render(); };
            window.toggleWidget = (k,show) => { const s=getHiddenW(); if(show) s.delete(k); else s.add(k); saveHiddenW(s); render(); };
            window.toggleWidgetMenu = e => { e.stopPropagation(); const m=document.getElementById('wMenu'); if(m) m.style.display = (m.style.display==='block'?'none':'block'); };
            function xBtn(k){ return `<button onclick="hideWidget('${k}')" title="Hide section" style="background:none;border:none;color:var(--muted);cursor:pointer;padding:2px 7px;font-size:14px;line-height:1;border-radius:6px;" onmouseover="this.style.color='var(--danger)';this.style.background='var(--surface2)'" onmouseout="this.style.color='var(--muted)';this.style.background='transparent'">✕</button>`; }
            document.addEventListener('click', function(ev){ const m=document.getElementById('wMenu'); if(m && m.style.display==='block' && !m.contains(ev.target) && !ev.target.closest('[onclick*="toggleWidgetMenu"]')) m.style.display='none'; });

            function render(){
                const hw = getHiddenW();
                const total=TASKS.length, done=TASKS.filter(t=>t.status==='done').length;
                const pct = total? Math.round(done/total*100):0;
                let doneWeeks=0; months.forEach(mo=>weekRanges(mo.y,mo.m).forEach(r=>{ if(weekDone(tasksInWeek(mo,r))) doneWeeks++; }));
                const am=months[activeMonth]; const amTasks=tasksInMonth(am);

                let h='';

                // Sections (widget) control
                h+=`<div style="display:flex;justify-content:flex-end;margin-bottom:10px;position:relative;">
                    <button onclick="toggleWidgetMenu(event)" style="display:flex;align-items:center;gap:6px;background:none;border:1px solid var(--border2);color:var(--muted);font-size:12px;font-family:var(--font);cursor:pointer;padding:6px 12px;border-radius:8px;" onmouseover="this.style.color='var(--text)'" onmouseout="this.style.color='var(--muted)'">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
                        Sections${hw.size? ` <span style="color:var(--accent2);">${WIDGETS.length-hw.size}/${WIDGETS.length}</span>`:''}
                    </button>
                    <div id="wMenu" style="display:none;position:absolute;top:calc(100% + 6px);right:0;width:220px;background:var(--surface);border:1px solid var(--border2);border-radius:10px;box-shadow:0 10px 30px rgba(0,0,0,0.4);z-index:60;padding:8px;text-align:left;">
                        <div style="font-size:10px;color:var(--muted);font-family:var(--mono);text-transform:uppercase;letter-spacing:0.06em;padding:2px 8px 6px;">Show sections</div>
                        ${WIDGETS.map(w=>`<label style="display:flex;align-items:center;gap:8px;padding:6px 8px;border-radius:6px;cursor:pointer;font-size:13px;color:var(--text);" onmouseover="this.style.background='var(--surface2)'" onmouseout="this.style.background='transparent'"><input type="checkbox" ${hw.has(w.key)?'':'checked'} onchange="toggleWidget('${w.key}', this.checked)" style="width:15px;height:15px;cursor:pointer;"> ${w.label}</label>`).join('')}
                    </div>
                </div>`;

                if(!hw.has('metrics')){
                    h+=`<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;"><span class="tl-stitle" style="margin-bottom:0;">Progress</span>${xBtn('metrics')}</div>`;
                    h+=`<div class="tl-metrics">
                        <div class="tl-mcard"><div class="tl-mlabel">Overall Progress</div><div class="tl-mvalue" style="color:${am.color}">${pct}%</div><div class="tl-pbar"><div class="tl-pfill" style="width:${pct}%;background:${am.color}"></div></div></div>
                        <div class="tl-mcard"><div class="tl-mlabel">Tasks Done</div><div class="tl-mvalue">${done}<span style="font-size:15px;color:var(--muted)"> / ${total}</span></div><div class="tl-msub">across all months</div></div>
                        <div class="tl-mcard"><div class="tl-mlabel">Weeks Complete</div><div class="tl-mvalue">${doneWeeks}<span style="font-size:15px;color:var(--muted)"> / 24</span></div><div class="tl-msub">fully-done weeks</div></div>
                        <div class="tl-mcard"><div class="tl-mlabel">Selected Month</div><div class="tl-mvalue" style="font-size:16px;line-height:1.3">${am.label}</div><div class="tl-msub">Month ${activeMonth+1} of 6 · ${amTasks.length} tasks</div></div>
                    </div>`;
                }

                if(!hw.has('timeline')){
                    h+=`<div class="tl-section"><div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;"><span class="tl-stitle" style="margin-bottom:0;">6-Month Timeline</span>${xBtn('timeline')}</div><div class="tl-months">`;
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
                }

                if(!hw.has('goal')){
                    const goalVal = GOALS[activeMonth+1] || '';
                    h+=`<div class="tl-section" style="border-color:${am.color}33;"><div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;"><span class="tl-stitle" style="margin-bottom:0;">Month ${activeMonth+1} Goal</span>${xBtn('goal')}</div>
                        <div style="font-size:14px;color:${goalVal?'var(--text)':'var(--muted)'};line-height:1.6;white-space:pre-wrap;">${goalVal? esc(goalVal) : 'No goal set for this month.'}</div>
                    </div>`;
                }

                if(!hw.has('weekly')){
                    h+=`<div class="tl-section"><div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;"><span class="tl-stitle" style="margin-bottom:0;">Weekly Execution — ${am.label}</span>${xBtn('weekly')}</div><div class="tl-tabs">`;
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
                }

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
            <div class="al-toolbar" style="display:flex; align-items:center; justify-content:flex-end; gap:2px; margin-bottom:12px;">
                {{-- Filter --}}
                <div style="position:relative;">
                    <button class="tb-btn" onclick="tbToggle(event,'tbFilter')" id="tbFilterBtn">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                        Filter <span id="tbFilterCount" style="display:none; color:var(--accent2); font-family:var(--mono);"></span>
                    </button>
                    <div id="tbFilter" class="tb-menu" style="width:480px; max-width:92vw; padding:18px 20px;">
                        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:14px;">
                            <span style="font-size:16px; font-weight:600; color:var(--text);">Filters</span>
                            <button onclick="tbClearFilters()" style="background:none; border:none; color:var(--muted); font-size:13px; cursor:pointer; font-family:var(--font);" onmouseover="this.style.color='var(--danger)'" onmouseout="this.style.color='var(--muted)'">Clear</button>
                        </div>
                        <div class="tb-mlabel" style="padding-left:0;">Quick filters</div>
                        <div style="display:flex; flex-wrap:wrap; gap:8px; margin-top:8px;">
                            <button class="qf-pill" data-qf="incomplete" onclick="tbQuick('incomplete')"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/></svg> Incomplete tasks</button>
                            <button class="qf-pill" data-qf="completed" onclick="tbQuick('completed')"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M8 12l3 3 5-6"/></svg> Completed tasks</button>
                            <button class="qf-pill" data-qf="mine" onclick="tbQuick('mine')"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="3.5"/><path d="M5 20a7 7 0 0114 0"/></svg> Just my tasks</button>
                            <button class="qf-pill" data-qf="due_this_week" onclick="tbQuick('due_this_week')"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="17" rx="2"/><path d="M3 9h18M8 2v4M16 2v4"/></svg> Due this week</button>
                            <button class="qf-pill" data-qf="due_next_week" onclick="tbQuick('due_next_week')"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 6l6 6-6 6"/></svg> Due next week</button>
                        </div>
                        <div id="tbFilterGroups" style="margin-top:12px; display:flex; flex-direction:column; gap:10px;"></div>
                        <div style="border-top:1px solid var(--border); margin-top:14px; padding-top:12px; position:relative;">
                            <button onclick="tbToggleAddFields(event)" style="display:flex; align-items:center; gap:7px; background:none; border:none; color:var(--muted); font-size:13px; cursor:pointer; font-family:var(--font);" onmouseover="this.style.color='var(--text)'" onmouseout="this.style.color='var(--muted)'">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                Add filter <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg>
                            </button>
                            <div id="tbAddFields" style="display:none; position:absolute; bottom:calc(100% + 6px); left:0; width:240px; background:var(--surface); border:1px solid var(--border2); border-radius:10px; box-shadow:0 10px 30px rgba(0,0,0,0.45); z-index:70; padding:6px; max-height:320px; overflow-y:auto;">
                                <button class="tb-field" onclick="addFilterField('completion')"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M8 12l3 3 5-6"/></svg> Completion status</button>
                                <button class="tb-field" onclick="addFilterField('due')"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="17" rx="2"/><path d="M3 9h18M8 2v4M16 2v4"/></svg> Due date</button>
                                <button class="tb-field" onclick="addFilterField('createdby')"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="3.5"/><path d="M5 20a7 7 0 0114 0"/></svg> Created by</button>
                                <button class="tb-field" onclick="addFilterField('created')"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg> Created on</button>
                                <button class="tb-field" onclick="addFilterField('modified')"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9M16.5 3.5a2.12 2.12 0 013 3L7 19l-4 1 1-4z"/></svg> Last modified on</button>
                                <button class="tb-field" onclick="addFilterField('assignee')"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="3.5"/><path d="M5 20a7 7 0 0114 0"/></svg> Assignee</button>
                                <button class="tb-field" onclick="addFilterField('status')"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/></svg> Status</button>
                                <button class="tb-field" onclick="addFilterField('priority')"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/></svg> Priority</button>
                            </div>
                        </div>
                    </div>
                </div>
                {{-- Sort --}}
                <div style="position:relative;">
                    <button class="tb-btn" onclick="tbToggle(event,'tbSort')"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 5h10M11 9h7M11 13h4M3 17l3 3 3-3M6 18V4"/></svg> Sort</button>
                    <div id="tbSort" class="tb-menu" style="width:210px;">
                        <div class="tb-mlabel" style="padding-left:0;">Sort by</div>
                        @php $sortOpts = [
                            ['default','Default','<circle cx="12" cy="12" r="9"/>'],
                            ['due','Due date','<rect x="3" y="4" width="18" height="17" rx="2"/><path d="M3 9h18M8 2v4M16 2v4"/>'],
                            ['assignee','Assignee','<circle cx="12" cy="8" r="3.5"/><path d="M5 20a7 7 0 0114 0"/>'],
                            ['createdby','Created by','<circle cx="12" cy="8" r="3.5"/><path d="M5 20a7 7 0 0114 0"/>'],
                            ['created','Created on','<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>'],
                            ['modified','Last modified on','<path d="M12 20h9M16.5 3.5a2.12 2.12 0 013 3L7 19l-4 1 1-4z"/>'],
                            ['title','Alphabetical','<path d="M5 19l4-12 4 12M6.5 15h5M15 8h5M15 8l2.5 6M20 8l-2.5 6"/>'],
                            ['status','Status','<circle cx="12" cy="12" r="9"/><path d="M8 12l3 3 5-6"/>'],
                            ['priority','Priority','<path d="M4 21V4M4 4l14 4-14 4"/>'],
                        ]; @endphp
                        @foreach($sortOpts as $o)
                        <label class="tb-opt"><input type="radio" name="tbsort" value="{{ $o[0] }}" {{ $o[0]==='default'?'checked':'' }} onchange="tbSetSort('{{ $o[0] }}')"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">{!! $o[2] !!}</svg> {{ $o[1] }}</label>
                        @endforeach
                    </div>
                </div>
                {{-- Group --}}
                <div style="position:relative;">
                    <button class="tb-btn" onclick="tbToggle(event,'tbGroup')"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="4" rx="1"/><rect x="3" y="10" width="18" height="4" rx="1"/><rect x="3" y="17" width="18" height="4" rx="1"/></svg> Group</button>
                    <div id="tbGroup" class="tb-menu" style="width:380px; max-width:92vw; padding:18px 20px;">
                        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:14px;">
                            <span style="font-size:16px; font-weight:600; color:var(--text);">Groups</span>
                            <button onclick="setGroupBy('section')" style="background:none; border:none; color:var(--muted); font-size:13px; cursor:pointer; font-family:var(--font);" onmouseover="this.style.color='var(--danger)'" onmouseout="this.style.color='var(--muted)'">Clear</button>
                        </div>
                        <div style="display:flex; align-items:center; gap:8px;">
                            <svg width="12" height="16" viewBox="0 0 24 24" fill="var(--muted)" style="flex-shrink:0;"><circle cx="9" cy="6" r="1.6"/><circle cx="15" cy="6" r="1.6"/><circle cx="9" cy="12" r="1.6"/><circle cx="15" cy="12" r="1.6"/><circle cx="9" cy="18" r="1.6"/><circle cx="15" cy="18" r="1.6"/></svg>
                            <div style="position:relative; flex:1;">
                                <button onclick="toggleGroupFields(event)" style="display:flex; align-items:center; justify-content:space-between; width:100%; gap:8px; background:var(--surface2); border:1px solid var(--border2); border-radius:8px; padding:10px 12px; color:var(--text); font-size:14px; font-family:var(--font); cursor:pointer;">
                                    <span style="display:flex; align-items:center; gap:8px;"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg> <span id="gbLabel">Sections</span></span>
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg>
                                </button>
                                <div id="gbFields" style="display:none; position:absolute; top:calc(100% + 6px); left:0; width:260px; background:var(--surface); border:1px solid var(--border2); border-radius:10px; box-shadow:0 10px 30px rgba(0,0,0,0.45); z-index:80; padding:6px; max-height:340px; overflow-y:auto;">
                                    @php $groupFields = [
                                        ['section','Sections','<line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/>'],
                                        ['due','Due date','<rect x="3" y="4" width="18" height="17" rx="2"/><path d="M3 9h18M8 2v4M16 2v4"/>'],
                                        ['assignee','Assignee','<circle cx="12" cy="8" r="3.5"/><path d="M5 20a7 7 0 0114 0"/>'],
                                        ['createdby','Created by','<circle cx="12" cy="8" r="3.5"/><path d="M5 20a7 7 0 0114 0"/>'],
                                        ['created','Created on','<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>'],
                                        ['modified','Last modified on','<path d="M12 20h9M16.5 3.5a2.12 2.12 0 013 3L7 19l-4 1 1-4z"/>'],
                                        ['status','Status','<circle cx="12" cy="12" r="9"/><path d="M8 12l3 3 5-6"/>'],
                                        ['priority','Priority','<path d="M4 21V4M4 4l14 4-14 4"/>'],
                                        ['none','None','<circle cx="12" cy="12" r="9"/><line x1="5" y1="5" x2="19" y2="19"/>'],
                                    ]; @endphp
                                    @foreach($groupFields as $gf)
                                    <button class="tb-field" onclick="setGroupBy('{{ $gf[0] }}')"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">{!! $gf[2] !!}</svg> {{ $gf[1] }}</button>
                                    @endforeach
                                </div>
                            </div>
                            <div style="position:relative;">
                                <button onclick="toggleOrderMenu(event)" style="display:flex; align-items:center; gap:8px; background:var(--surface2); border:1px solid var(--border2); border-radius:8px; padding:10px 12px; color:var(--text); font-size:14px; font-family:var(--font); cursor:pointer; white-space:nowrap;">
                                    <span id="goLabel">Custom order</span>
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg>
                                </button>
                                <div id="goMenu" style="display:none; position:absolute; top:calc(100% + 6px); right:0; width:180px; background:var(--surface); border:1px solid var(--border2); border-radius:10px; box-shadow:0 10px 30px rgba(0,0,0,0.45); z-index:80; padding:6px;">
                                    <button class="tb-field" onclick="setGroupOrder('custom')"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 13l4 4L19 7"/></svg> Custom order</button>
                                    <button class="tb-field" onclick="setGroupOrder('asc')"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 5h10M11 9h7M11 13h4M4 17l3-3 3 3M7 14V4"/></svg> Ascending</button>
                                    <button class="tb-field" onclick="setGroupOrder('desc')"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 5h4M11 9h7M11 13h10M4 7l3 3 3-3M7 6v10"/></svg> Descending</button>
                                </div>
                            </div>
                        </div>
                        <button onclick="document.getElementById('gbFields').style.display='block'" style="margin-top:12px; display:flex; align-items:center; gap:7px; background:none; border:none; color:var(--muted); font-size:13px; cursor:pointer; font-family:var(--font);" onmouseover="this.style.color='var(--text)'" onmouseout="this.style.color='var(--muted)'">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                            Change grouping
                        </button>
                    </div>
                </div>
                {{-- Options --}}
                <button class="tb-btn" onclick="openOptions()"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 11-2.83 2.83l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 11-2.83-2.83l.06-.06a1.65 1.65 0 00.33-1.82 1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 112.83-2.83l.06.06a1.65 1.65 0 001.82.33H9a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 112.83 2.83l-.06.06a1.65 1.65 0 00-.33 1.82V9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z"/></svg> Options</button>
                <div style="width:1px; height:20px; background:var(--border2); margin:0 6px;"></div>
                {{-- Search --}}
                <div style="position:relative; display:flex; align-items:center;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--muted)" stroke-width="2" style="position:absolute; left:9px; pointer-events:none;"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    <input type="text" oninput="filterTasks(this.value)" placeholder="Search..." class="ptm-input" style="font-size:12px; padding:6px 10px 6px 28px; width:150px;">
                </div>
            </div>

            <div class="ptm-card" style="overflow-x:auto; overflow-y:visible;">
                <div class="al-table-inner" style="min-width:{{ $tableMinWidth }}px;">
                {{-- Column header --}}
                <div class="al-gridrow" style="display:grid; {{ $colGrid }} background:var(--surface2); border-bottom:1px solid var(--border);">
                    <div class="al-cell ptm-section-title c-name">Name</div>
                    <div class="al-cell ptm-section-title c-due">Due date</div>
                    <div class="al-cell ptm-section-title c-assignee">Assignee</div>
                    <div class="al-cell ptm-section-title c-status">Status</div>
                    <div class="al-cell ptm-section-title c-priority">Priority</div>
                    @foreach($customFields as $cf)
                    <div class="al-cell ptm-section-title c-cf-{{ $cf->id }}" style="gap:6px;">
                        <span style="font-size:10px; color:var(--accent2); font-family:var(--mono); opacity:0.7; flex-shrink:0;">{{ $cfTypeIcon[$cf->type] ?? 'T' }}</span>
                        <span style="white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $cf->name }}</span>
                    </div>
                    @endforeach
                    <div class="al-cell c-actions" style="border-right:none; justify-content:center; position:relative; overflow:visible;">
                        <button onclick="event.stopPropagation(); document.getElementById('colMenu').classList.toggle('show')" title="Show columns" style="background:none; border:none; color:var(--muted); cursor:pointer; font-size:16px; line-height:1; padding:2px 6px; border-radius:6px;" onmouseover="this.style.color='var(--accent2)'" onmouseout="this.style.color='var(--muted)'">+</button>
                        <div id="colMenu" class="col-menu" style="display:none; position:absolute; top:calc(100% + 6px); right:0; width:220px; background:var(--surface); border:1px solid var(--border2); border-radius:10px; box-shadow:0 10px 30px rgba(0,0,0,0.4); z-index:60; padding:10px; text-align:left;">
                            <div style="font-size:10px; color:var(--muted); font-family:var(--mono); text-transform:uppercase; letter-spacing:0.06em; margin-bottom:8px;">Show columns</div>
                            @foreach(array_merge([['due','Due date'],['assignee','Assignee'],['status','Status'],['priority','Priority']], $customFields->map(fn($cf)=>['cf-'.$cf->id, $cf->name])->all()) as $bc)
                            <label style="display:flex; align-items:center; gap:8px; padding:5px 6px; border-radius:6px; cursor:pointer; font-size:13px; color:var(--text);" onmouseover="this.style.background='var(--surface2)'" onmouseout="this.style.background='transparent'">
                                <input type="checkbox" class="col-toggle" value="{{ $bc[0] }}" checked onchange="toggleCol('{{ $bc[0] }}', this.checked)" style="width:15px; height:15px; cursor:pointer;">
                                {{ $bc[1] }}
                            </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                @foreach($groups as $group)
                <div x-data="{ open: true }" data-section-block data-sectionname="{{ $group['name'] }}">
                    {{-- Section header --}}
                    <div class="al-sechead" style="display:flex; align-items:center; gap:8px; padding:10px 14px; border-bottom:1px solid var(--border); background:var(--surface);">
                        <svg @click="open=!open" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" :style="open ? '' : 'transform:rotate(-90deg)'" style="color:var(--muted); transition:transform 0.15s; cursor:pointer; flex-shrink:0;"><path d="M19 9l-7 7-7-7"/></svg>
                        <span style="font-size:13px; font-weight:600; color:{{ $group['id'] ? 'var(--text)' : 'var(--muted)' }};">{{ $group['name'] }}</span>
                        <span style="font-size:11px; color:var(--muted); background:var(--surface2); padding:1px 7px; border-radius:10px; font-family:var(--mono);">{{ $group['tasks']->count() }}</span>
                    </div>

                    {{-- Rows --}}
                    <div x-show="open" class="al-tasklist">
                        @forelse($group['tasks'] as $task)
                            @php
                                $isMine = $task->assigned_to === $myId || $task->assignees->contains('id', $myId);
                                $sm = $statusMeta[$task->status] ?? $statusMeta['todo'];
                            @endphp
                            <div class="al-row al-gridrow" id="row-{{ $task->id }}" data-title="{{ strtolower($task->title) }}" data-status="{{ $task->status }}" data-priority="{{ $task->priority }}" data-due="{{ $task->due_date?->format('Y-m-d') }}" data-assignees="{{ $task->assignees->pluck('id')->push($task->assigned_to)->filter()->unique()->implode(',') }}" data-createdby="{{ $task->created_by }}" data-created="{{ $task->created_at?->format('Y-m-d') }}" data-modified="{{ $task->updated_at?->format('Y-m-d') }}" data-section="{{ $group['id'] }}" data-sectionname="{{ $group['name'] }}" style="display:grid; {{ $colGrid }} border-bottom:1px solid var(--border); transition:background 0.1s;">
                                {{-- Name --}}
                                <div class="al-cell c-name" style="gap:6px;">
                                    @if(($task->subtasks_count ?? 0) > 0)
                                    <span class="al-subtoggle" onclick="toggleSubs({{ $task->id }}, this)" title="{{ $task->subtasks_count }} subtask(s)" style="display:flex; align-items:center; gap:2px; cursor:pointer; color:var(--muted); flex-shrink:0; font-size:10px; font-family:var(--mono);">
                                        <svg class="al-chev" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="transform:rotate(-90deg); transition:transform 0.15s;"><path d="M19 9l-7 7-7-7"/></svg>
                                        {{ $task->subtasks_count }}
                                    </span>
                                    @else
                                    <span style="width:15px; flex-shrink:0;"></span>
                                    @endif
                                    <div style="width:15px; height:15px; border-radius:50%; border:1.5px solid {{ $task->status === 'done' ? '#4ade80' : 'var(--border2)' }}; background:{{ $task->status === 'done' ? '#4ade80' : 'transparent' }}; flex-shrink:0; display:flex; align-items:center; justify-content:center;">
                                        @if($task->status === 'done')<svg width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="#0d0f12" stroke-width="3"><path d="M5 13l4 4L19 7"/></svg>@endif
                                    </div>
                                    <span onclick="openPanel({{ $task->id }})" style="font-size:13px; font-weight:500; color:var(--text); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; cursor:pointer; flex:1;" onmouseover="this.style.color='var(--accent2)'" onmouseout="this.style.color='var(--text)'">{{ $task->title }}</span>
                                    <button onclick="openPanel({{ $task->id }})" title="Open details" style="flex-shrink:0; color:var(--muted); background:none; border:none; cursor:pointer; display:flex; align-items:center; gap:3px; padding:3px 5px; border-radius:6px;" onmouseover="this.style.color='var(--accent2)'; this.style.background='var(--surface2)'" onmouseout="this.style.color='var(--muted)'; this.style.background='transparent'">
                                        @if(($task->comments_count ?? 0) > 0)<span style="font-size:11px; font-family:var(--mono);">{{ $task->comments_count }}</span><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 11.5a8.38 8.38 0 01-.9 3.8 8.5 8.5 0 01-7.6 4.7 8.38 8.38 0 01-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 01-.9-3.8 8.5 8.5 0 014.7-7.6 8.38 8.38 0 013.8-.9h.5a8.48 8.48 0 018 8v.5z"/></svg>@endif
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 3h6v6M14 10l7-7M21 14v5a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h5"/></svg>
                                    </button>
                                </div>

                                {{-- Due date --}}
                                <div class="al-cell c-due" style="font-size:12px; font-family:var(--mono); {{ $task->due_date?->isPast() && $task->status !== 'done' ? 'color:#f87171;' : 'color:var(--muted);' }}">
                                    {{ $task->due_date?->format('d M Y') ?? '—' }}
                                </div>

                                {{-- Assignee --}}
                                <div class="al-cell c-assignee">
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
                                <div class="al-cell c-status">
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
                                <div class="al-cell c-priority">
                                    <span class="al-badge" style="{{ $priorityStyles[$task->priority] ?? $priorityStyles['low'] }}">{{ ucfirst($task->priority) }}</span>
                                </div>

                                {{-- Custom fields (read-only) --}}
                                @foreach($customFields as $cf)
                                    @php $cval = ($task->custom_values[$cf->id] ?? '') ?: (($task->custom_values[(string)$cf->id] ?? '')); @endphp
                                    <div class="al-cell c-cf-{{ $cf->id }}">
                                        <span class="al-cfval">{{ $cval !== '' ? $cval : '—' }}</span>
                                    </div>
                                @endforeach

                                <div class="al-cell c-actions" style="border-right:none;"></div>
                            </div>

                            {{-- Subtasks (collapsible) --}}
                            @if($task->subtasks->count() > 0)
                            <div id="subs-{{ $task->id }}" data-open="0" style="display:none;">
                                @foreach($task->subtasks as $sub)
                                    @php
                                        $subMine = $sub->assigned_to === $myId || $sub->assignees->contains('id', $myId);
                                        $ssm = $statusMeta[$sub->status] ?? $statusMeta['todo'];
                                    @endphp
                                    <div class="al-subrow al-gridrow" data-title="{{ strtolower($sub->title) }}" style="display:grid; {{ $colGrid }} border-bottom:1px solid var(--border); background:rgba(255,255,255,0.02);">
                                        <div class="al-cell c-name" style="gap:8px; padding-left:50px;">
                                            <div style="width:14px; height:14px; border-radius:50%; border:1.5px solid {{ $sub->status === 'done' ? '#4ade80' : 'var(--border2)' }}; background:{{ $sub->status === 'done' ? '#4ade80' : 'transparent' }}; flex-shrink:0; display:flex; align-items:center; justify-content:center;">
                                                @if($sub->status === 'done')<svg width="8" height="8" viewBox="0 0 24 24" fill="none" stroke="#0d0f12" stroke-width="3"><path d="M5 13l4 4L19 7"/></svg>@endif
                                            </div>
                                            <span onclick="openPanel({{ $sub->id }})" style="font-size:13px; color:var(--text); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; cursor:pointer; flex:1;" onmouseover="this.style.color='var(--accent2)'" onmouseout="this.style.color='var(--text)'">{{ $sub->title }}</span>
                                        </div>
                                        <div class="al-cell c-due" style="font-size:12px; font-family:var(--mono); {{ $sub->due_date?->isPast() && $sub->status !== 'done' ? 'color:#f87171;' : 'color:var(--muted);' }}">
                                            {{ $sub->due_date?->format('d M Y') ?? '—' }}
                                        </div>
                                        <div class="al-cell c-assignee">
                                            @forelse($sub->assignees->take(3) as $a)
                                                <div class="al-avatar" title="{{ $a->name }}" style="margin-right:2px;">{{ strtoupper(substr($a->name,0,1)) }}</div>
                                            @empty
                                                <span style="font-size:12px; color:var(--muted);">—</span>
                                            @endforelse
                                        </div>
                                        <div class="al-cell c-status">
                                            @if($subMine)
                                                <form method="POST" action="{{ route('employee.tasks.status', [$slug, $sub]) }}" style="width:100%;">
                                                    @csrf @method('PATCH')
                                                    <select name="status" class="al-pill al-status" onchange="applyStatus(this); this.form.submit()">
                                                        @foreach(['todo'=>'To Do','in_progress'=>'In Progress','in_review'=>'In Review','done'=>'Done'] as $val=>$lbl)
                                                            <option value="{{ $val }}" {{ $sub->status===$val?'selected':'' }}>{{ $lbl }}</option>
                                                        @endforeach
                                                    </select>
                                                </form>
                                            @else
                                                <span class="al-badge" style="border-color:var(--border2); color:{{ $ssm['color'] }};">{{ $ssm['label'] }}</span>
                                            @endif
                                        </div>
                                        <div class="al-cell c-priority">
                                            <span class="al-badge" style="{{ $priorityStyles[$sub->priority] ?? $priorityStyles['low'] }}">{{ ucfirst($sub->priority) }}</span>
                                        </div>
                                        @foreach($customFields as $cf)
                                            @php $scval = ($sub->custom_values[$cf->id] ?? '') ?: (($sub->custom_values[(string)$cf->id] ?? '')); @endphp
                                            <div class="al-cell c-cf-{{ $cf->id }}"><span class="al-cfval">{{ $scval !== '' ? $scval : '—' }}</span></div>
                                        @endforeach
                                        <div class="al-cell c-actions" style="border-right:none;"></div>
                                    </div>
                                @endforeach
                            </div>
                            @endif
                        @empty
                            <div style="padding:11px 14px 11px 35px; font-size:12px; color:var(--muted); font-family:var(--mono); border-bottom:1px solid var(--border);">No tasks in this section</div>
                        @endforelse
                    </div>
                </div>
                @endforeach
                </div>
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

    /* ================= LIST TOOLBAR (filter / sort / group / options) ================= */
    const MY_ID = {{ auth()->id() }};
    const MEMBERS = @json($members->map(fn($m)=>['id'=>$m->id,'name'=>$m->name])->values());
    const MEMBER_NAME = {}; MEMBERS.forEach(m=> MEMBER_NAME[String(m.id)] = m.name);
    let TB = { q:'', status:new Set(), priority:new Set(), qf:new Set(), assignee:new Set(), createdby:new Set(), created:new Set(), modified:new Set(), hideDone:false, fields:new Set() };

    function tbToggle(e, id){
        e.stopPropagation();
        const m=document.getElementById(id); const open=m.classList.contains('show');
        document.querySelectorAll('.tb-menu').forEach(x=>x.classList.remove('show'));
        if(!open) m.classList.add('show');
    }
    document.addEventListener('click', function(e){
        if(!e.target.closest('.al-toolbar')) document.querySelectorAll('.tb-menu').forEach(x=>x.classList.remove('show'));
    });

    function fmtDate(d){ return d.getFullYear()+'-'+String(d.getMonth()+1).padStart(2,'0')+'-'+String(d.getDate()).padStart(2,'0'); }
    function weekBounds(offset){ const now=new Date(); now.setHours(0,0,0,0); const day=(now.getDay()+6)%7; const mon=new Date(now); mon.setDate(now.getDate()-day+offset*7); const sun=new Date(mon); sun.setDate(mon.getDate()+6); return [fmtDate(mon), fmtDate(sun)]; }
    function dueInWeek(due, offset){ if(!due) return false; const [s,e]=weekBounds(offset); return due>=s && due<=e; }
    function dateInRange(d, key){ if(!d) return false; const now=new Date(), today=fmtDate(now);
        if(key==='today') return d===today;
        if(key==='this_week') return dueInWeek(d,0);
        if(key==='this_month') return d.slice(0,7)===(now.getFullYear()+'-'+String(now.getMonth()+1).padStart(2,'0'));
        if(key==='older') return d < fmtDate(new Date(now.getFullYear(), now.getMonth(), 1));
        return false; }

    function rowVisible(row){
        if(TB.q && !row.dataset.title.includes(TB.q)) return false;
        if(TB.status.size && !TB.status.has(row.dataset.status)) return false;
        if(TB.priority.size && !TB.priority.has(row.dataset.priority)) return false;
        if(TB.hideDone && row.dataset.status==='done') return false;
        if(TB.qf.has('incomplete') && row.dataset.status==='done') return false;
        if(TB.qf.has('completed') && row.dataset.status!=='done') return false;
        if(TB.qf.has('mine') && !(row.dataset.assignees||'').split(',').includes(String(MY_ID))) return false;
        const dueKeys=['due_this_week','due_next_week','overdue','no_date'].filter(k=>TB.qf.has(k));
        if(dueKeys.length){ const due=row.dataset.due, today=fmtDate(new Date());
            const ok = dueKeys.some(k => (k==='due_this_week'&&dueInWeek(due,0)) || (k==='due_next_week'&&dueInWeek(due,1)) || (k==='overdue'&&due&&due<today&&row.dataset.status!=='done') || (k==='no_date'&&!due));
            if(!ok) return false; }
        if(TB.assignee.size){ const ids=(row.dataset.assignees||'').split(','); if(![...TB.assignee].some(a=>ids.includes(String(a)))) return false; }
        if(TB.createdby.size && !TB.createdby.has(row.dataset.createdby)) return false;
        if(TB.created.size && ![...TB.created].some(k=>dateInRange(row.dataset.created,k))) return false;
        if(TB.modified.size && ![...TB.modified].some(k=>dateInRange(row.dataset.modified,k))) return false;
        return true;
    }
    function applyRowVisibility(){
        document.querySelectorAll('.al-tasklist > .al-row').forEach(row=>{
            const vis=rowVisible(row);
            row.style.display = vis ? 'grid' : 'none';
            const subs=document.getElementById('subs-'+(row.id||'').replace('row-',''));
            if(subs){ if(!vis) subs.style.display='none'; else if(subs.getAttribute('data-open')==='1') subs.style.display='block'; }
        });
    }
    function filterTasks(q){ TB.q=(q||'').trim().toLowerCase(); applyRowVisibility(); }
    function tbFilterCount(){
        const n = TB.status.size+TB.priority.size+TB.qf.size+TB.assignee.size+TB.createdby.size+TB.created.size+TB.modified.size;
        const c=document.getElementById('tbFilterCount'), b=document.getElementById('tbFilterBtn');
        if(n){ c.textContent=n; c.style.display='inline'; b.style.color='var(--text)'; } else { c.style.display='none'; b.style.color=''; }
    }
    function tbQuick(key){
        if(TB.qf.has(key)){ TB.qf.delete(key); } else { if(key==='incomplete') TB.qf.delete('completed'); if(key==='completed') TB.qf.delete('incomplete'); TB.qf.add(key); }
        document.querySelectorAll('#tbFilter .qf-pill').forEach(p=> p.classList.toggle('active', TB.qf.has(p.dataset.qf)));
        renderFilterGroups(); tbFilterCount(); applyRowVisibility();
    }
    const FIELD_DEFS = {
        completion: { label:'Completion status', target:'qf', opts:[['incomplete','Incomplete'],['completed','Completed']] },
        due:        { label:'Due date', target:'qf', opts:[['due_this_week','This week'],['due_next_week','Next week'],['overdue','Overdue'],['no_date','No due date']] },
        assignee:   { label:'Assignee', target:'assignee', opts: MEMBERS.map(m=>[String(m.id), m.name]) },
        createdby:  { label:'Created by', target:'createdby', opts: MEMBERS.map(m=>[String(m.id), m.name]) },
        created:    { label:'Created on', target:'created', opts:[['today','Today'],['this_week','This week'],['this_month','This month'],['older','Older']] },
        modified:   { label:'Last modified on', target:'modified', opts:[['today','Today'],['this_week','This week'],['this_month','This month'],['older','Older']] },
        status:     { label:'Status', target:'status', opts:[['todo','To Do'],['in_progress','In Progress'],['in_review','In Review'],['done','Done']] },
        priority:   { label:'Priority', target:'priority', opts:[['urgent','Urgent'],['high','High'],['medium','Medium'],['low','Low']] },
    };
    function tbSetForTarget(t){ return TB[t]; }
    function tbToggleAddFields(e){ e.stopPropagation(); const m=document.getElementById('tbAddFields'); m.style.display = m.style.display==='block'?'none':'block'; }
    function addFilterField(key){ TB.fields.add(key); document.getElementById('tbAddFields').style.display='none'; renderFilterGroups(); }
    function removeFilterField(key){ TB.fields.delete(key); const def=FIELD_DEFS[key]; const set=tbSetForTarget(def.target); def.opts.forEach(([v])=> set.delete(v)); renderFilterGroups(); tbFilterCount(); applyRowVisibility(); }
    function fgToggle(key, val, on){
        const set=tbSetForTarget(FIELD_DEFS[key].target);
        if(on){ if(key==='completion'&&val==='incomplete') set.delete('completed'); if(key==='completion'&&val==='completed') set.delete('incomplete'); set.add(val); } else set.delete(val);
        document.querySelectorAll('#tbFilter .qf-pill').forEach(p=> p.classList.toggle('active', TB.qf.has(p.dataset.qf)));
        renderFilterGroups(); tbFilterCount(); applyRowVisibility();
    }
    function renderFilterGroups(){
        const wrap=document.getElementById('tbFilterGroups'); if(!wrap) return;
        wrap.innerHTML = [...TB.fields].map(key=>{
            const def=FIELD_DEFS[key]; const set=tbSetForTarget(def.target);
            const opts=def.opts.map(([v,l])=>`<label class="fg-opt"><input type="checkbox" ${set.has(v)?'checked':''} onchange="fgToggle('${key}','${v}',this.checked)"> ${l}</label>`).join('');
            return `<div class="fg-group"><div class="fg-head"><span class="fg-title">${def.label}</span><button onclick="removeFilterField('${key}')" title="Remove" style="background:none;border:none;color:var(--muted);cursor:pointer;font-size:12px;" onmouseover="this.style.color='var(--danger)'" onmouseout="this.style.color='var(--muted)'">✕</button></div><div class="fg-opts">${opts}</div></div>`;
        }).join('');
    }
    function tbClearFilters(){
        TB.status.clear(); TB.priority.clear(); TB.qf.clear(); TB.assignee.clear(); TB.createdby.clear(); TB.created.clear(); TB.modified.clear(); TB.fields.clear();
        document.querySelectorAll('#tbFilter .qf-pill').forEach(p=>p.classList.remove('active'));
        renderFilterGroups(); tbFilterCount(); applyRowVisibility();
    }
    function tbHideCompleted(on){ TB.hideDone=on; applyRowVisibility(); }
    function tbExpandAllSubs(open){
        document.querySelectorAll('[id^="subs-"]').forEach(box=>{ box.style.display=open?'block':'none'; box.setAttribute('data-open', open?'1':'0'); });
        document.querySelectorAll('.al-chev').forEach(ch=> ch.style.transform = open?'rotate(0deg)':'rotate(-90deg)');
        if(open) applyRowVisibility();
        document.querySelectorAll('.tb-menu').forEach(x=>x.classList.remove('show'));
    }
    /* ---- Group by (client-side re-bucket) ---- */
    const GROUP_LABEL = { section:'Sections', due:'Due date', assignee:'Assignee', createdby:'Created by', created:'Created on', modified:'Last modified on', status:'Status', priority:'Priority', none:'None' };
    const STATUS_LABEL = { todo:'To Do', in_progress:'In Progress', in_review:'In Review', done:'Done' };
    function cap(s){ return s ? s.charAt(0).toUpperCase()+s.slice(1) : s; }
    function dateBucket(d){ if(!d) return {k:'zzz_none', l:'No date'}; if(dateInRange(d,'today')) return {k:'0_today', l:'Today'}; if(dateInRange(d,'this_week')) return {k:'1_week', l:'This week'}; if(dateInRange(d,'this_month')) return {k:'2_month', l:'This month'}; return {k:'3_older', l:'Older'}; }
    function dueBucket(due, status){ if(!due) return {k:'zzz_none', l:'No due date'}; const today=fmtDate(new Date()); if(due<today && status!=='done') return {k:'0_over', l:'Overdue'}; if(dueInWeek(due,0)) return {k:'1_week', l:'This week'}; if(dueInWeek(due,1)) return {k:'2_next', l:'Next week'}; return {k:'3_later', l:'Later'}; }
    function groupInfo(row, key){
        if(key==='status') return [row.dataset.status||'todo', STATUS_LABEL[row.dataset.status]||row.dataset.status];
        if(key==='priority') return [row.dataset.priority||'low', cap(row.dataset.priority)];
        if(key==='assignee'){ const id=(row.dataset.assignees||'').split(',')[0]; return id ? [id, MEMBER_NAME[id]||('User '+id)] : ['zzz_none','Unassigned']; }
        if(key==='createdby'){ const id=row.dataset.createdby; return id ? [id, MEMBER_NAME[id]||('User '+id)] : ['zzz_none','Unknown']; }
        if(key==='created'){ const b=dateBucket(row.dataset.created); return [b.k,b.l]; }
        if(key==='modified'){ const b=dateBucket(row.dataset.modified); return [b.k,b.l]; }
        if(key==='due'){ const b=dueBucket(row.dataset.due, row.dataset.status); return [b.k,b.l]; }
        return [row.dataset.section||'', row.dataset.sectionname||'(No section)'];
    }
    function orderGroupKeys(key, keys){
        const fixed = { status:['todo','in_progress','in_review','done'], priority:['urgent','high','medium','low'] };
        if(fixed[key]){ return keys.slice().sort((a,b)=> ((fixed[key].indexOf(a)+1)||99)-((fixed[key].indexOf(b)+1)||99)); }
        return keys.slice().sort();
    }
    let GB_KEY='section', GB_ORDER='custom';
    window.toggleGroupFields = e => { e.stopPropagation(); const m=document.getElementById('gbFields'); m.style.display = m.style.display==='block'?'none':'block'; };
    window.toggleOrderMenu = e => { e.stopPropagation(); const m=document.getElementById('goMenu'); m.style.display = m.style.display==='block'?'none':'block'; };
    window.setGroupOrder = o => { GB_ORDER=o; document.getElementById('goLabel').textContent = o==='asc'?'Ascending':o==='desc'?'Descending':'Custom order'; document.getElementById('goMenu').style.display='none'; setGroupBy(GB_KEY); };
    function reorderSectionBlocks(){
        const inner=document.querySelector('.al-table-inner');
        const blocks=[...inner.querySelectorAll('[data-section-block]')];
        blocks.sort((a,b)=>{ if(GB_ORDER==='asc') return (a.dataset.sectionname||'').localeCompare(b.dataset.sectionname||''); if(GB_ORDER==='desc') return (b.dataset.sectionname||'').localeCompare(a.dataset.sectionname||''); return (+a.dataset.secpos||0)-(+b.dataset.secpos||0); });
        blocks.forEach(b=> inner.appendChild(b));
    }
    function restoreToSections(){
        const gv=document.getElementById('groupedView');
        if(gv){
            gv.querySelectorAll('.al-row').forEach(row=>{
                const list=document.querySelector('.al-tasklist[data-section-id="'+(row.dataset.section||'')+'"]');
                if(list){ list.appendChild(row); const s=document.getElementById('subs-'+row.id.replace('row-','')); if(s) list.appendChild(s); }
            });
            gv.remove();
        }
        document.querySelectorAll('[data-section-block]').forEach(b=> b.style.display='');
        document.querySelectorAll('.al-tasklist').forEach(list=>{
            [...list.querySelectorAll(':scope > .al-row')].sort((a,b)=>(+a.dataset.pos||0)-(+b.dataset.pos||0)).forEach(r=>{ list.appendChild(r); const s=document.getElementById('subs-'+r.id.replace('row-','')); if(s) list.appendChild(s); });
        });
    }
    function buildGrouped(key){
        const rows=[...document.querySelectorAll('.al-tasklist > .al-row')];
        const groups=new Map();
        rows.forEach(row=>{ const [k,l]=groupInfo(row,key); if(!groups.has(k)) groups.set(k,{label:l, rows:[]}); groups.get(k).rows.push(row); });
        document.querySelectorAll('[data-section-block]').forEach(b=> b.style.display='none');
        const gv=document.createElement('div'); gv.id='groupedView';
        let orderedKeys=[...groups.keys()];
        if(GB_ORDER==='asc') orderedKeys.sort((a,b)=> groups.get(a).label.localeCompare(groups.get(b).label));
        else if(GB_ORDER==='desc') orderedKeys.sort((a,b)=> groups.get(b).label.localeCompare(groups.get(a).label));
        else orderedKeys=orderGroupKeys(key, orderedKeys);
        orderedKeys.forEach(k=>{
            const g=groups.get(k);
            const head=document.createElement('div'); head.className='al-sechead'; head.style.cssText='display:flex; align-items:center; gap:8px; padding:10px 14px; border-bottom:1px solid var(--border); background:var(--surface); cursor:pointer;';
            head.innerHTML=`<svg class="gh-chev" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="color:var(--muted); transition:transform 0.15s;"><path d="M19 9l-7 7-7-7"/></svg><span style="font-size:13px; font-weight:600; color:var(--text);">${g.label}</span><span style="font-size:11px; color:var(--muted); background:var(--surface2); padding:1px 7px; border-radius:10px; font-family:var(--mono);">${g.rows.length}</span>`;
            const listDiv=document.createElement('div'); listDiv.className='al-tasklist';
            head.onclick=()=>{ const open=listDiv.style.display!=='none'; listDiv.style.display=open?'none':''; head.querySelector('.gh-chev').style.transform=open?'rotate(-90deg)':''; };
            g.rows.forEach(row=>{ listDiv.appendChild(row); const s=document.getElementById('subs-'+row.id.replace('row-','')); if(s) listDiv.appendChild(s); });
            gv.appendChild(head); gv.appendChild(listDiv);
        });
        document.querySelector('.al-table-inner').appendChild(gv);
        applyRowVisibility();
    }
    function setGroupBy(key){
        GB_KEY=key;
        document.getElementById('gbFields').style.display='none';
        document.getElementById('gbLabel').textContent = GROUP_LABEL[key] || 'Sections';
        const inner=document.querySelector('.al-table-inner');
        restoreToSections();
        inner.classList.remove('group-none');
        if(key==='section'){ if(GB_ORDER!=='custom') reorderSectionBlocks(); return; }
        if(key==='none'){ inner.classList.add('group-none'); return; }
        buildGrouped(key);
    }
    const PRIO_ORDER={urgent:0,high:1,medium:2,low:3}, STAT_ORDER={todo:0,in_progress:1,in_review:2,done:3};
    function tbSetSort(key){
        document.querySelectorAll('.al-tasklist').forEach(list=>{
            const rows=[...list.querySelectorAll(':scope > .al-row')];
            rows.sort((a,b)=>{
                if(key==='title') return a.dataset.title.localeCompare(b.dataset.title);
                if(key==='due') return (a.dataset.due||'9999-99').localeCompare(b.dataset.due||'9999-99');
                if(key==='created') return (a.dataset.created||'9999-99').localeCompare(b.dataset.created||'9999-99');
                if(key==='modified') return (b.dataset.modified||'').localeCompare(a.dataset.modified||'');
                if(key==='priority') return (PRIO_ORDER[a.dataset.priority]??9)-(PRIO_ORDER[b.dataset.priority]??9);
                if(key==='status') return (STAT_ORDER[a.dataset.status]??9)-(STAT_ORDER[b.dataset.status]??9);
                if(key==='assignee') return (MEMBER_NAME[(a.dataset.assignees||'').split(',')[0]]||'zzzz').localeCompare(MEMBER_NAME[(b.dataset.assignees||'').split(',')[0]]||'zzzz');
                if(key==='createdby') return (MEMBER_NAME[a.dataset.createdby]||'zzzz').localeCompare(MEMBER_NAME[b.dataset.createdby]||'zzzz');
                return (+a.dataset.pos||0)-(+b.dataset.pos||0);
            });
            rows.forEach(row=>{ list.appendChild(row); const subs=document.getElementById('subs-'+row.id.replace('row-','')); if(subs) list.appendChild(subs); });
        });
    }
    document.querySelectorAll('.al-tasklist').forEach(list=>{ [...list.querySelectorAll(':scope > .al-row')].forEach((r,i)=> r.dataset.pos=i); });
    document.querySelectorAll('[data-section-block]').forEach((b,i)=> b.dataset.secpos=i);

    /* ---- Options / view-settings drawer ---- */
    let optSubExpanded=false;
    function openOptions(){ document.getElementById('optDrawer').classList.add('open'); updateOptHidden(); }
    function closeOptions(){ document.getElementById('optDrawer').classList.remove('open'); }
    function toggleOptCols(){ const c=document.getElementById('optCols'); c.style.display = c.style.display==='block'?'none':'block'; }
    function updateOptHidden(){ const el=document.getElementById('optHiddenCount'); if(el){ const n=getHidden().size; el.textContent = n? (n+' hidden') : ''; } }
    function toggleOptSubtasks(){ optSubExpanded=!optSubExpanded; tbExpandAllSubs(optSubExpanded); const l=document.getElementById('optSubLabel'); if(l) l.textContent = optSubExpanded?'Expanded':'Collapsed'; }
    document.addEventListener('keydown', e=>{ if(e.key==='Escape'){ const d=document.getElementById('optDrawer'); if(d && d.classList.contains('open')) closeOptions(); } });

    function toggleSubs(id, el){
        const box=document.getElementById('subs-'+id); if(!box) return;
        const chev=el.querySelector('.al-chev');
        if(box.getAttribute('data-open')==='1'){ box.style.display='none'; box.setAttribute('data-open','0'); if(chev) chev.style.transform='rotate(-90deg)'; }
        else { box.style.display='block'; box.setAttribute('data-open','1'); if(chev) chev.style.transform='rotate(0deg)'; }
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
    const COL_KEY = 'cols_hidden_emp_{{ $project->id }}';
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
    applyCols();

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

    {{-- Options / View settings drawer --}}
    <div id="optDrawer" class="opt-drawer">
        <div class="opt-overlay" onclick="closeOptions()"></div>
        <div class="opt-panel">
            <div style="display:flex; align-items:center; justify-content:space-between; padding:16px 20px; border-bottom:1px solid var(--border); flex-shrink:0;">
                <span style="font-size:16px; font-weight:600; color:var(--text);">List</span>
                <button onclick="closeOptions()" style="background:none; border:none; color:var(--muted); cursor:pointer; padding:5px; display:flex;" onmouseover="this.style.color='var(--text)'" onmouseout="this.style.color='var(--muted)'"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 6l-7 6 7 6"/></svg></button>
            </div>
            <div style="flex:1; overflow-y:auto; padding:18px 20px;">
                <div style="display:flex; gap:12px; align-items:flex-end; margin-bottom:18px;">
                    <div>
                        <div style="font-size:11px; color:var(--muted); font-family:var(--mono); margin-bottom:6px;">Icon</div>
                        <div style="width:42px; height:42px; border:1px solid var(--border2); border-radius:8px; display:flex; align-items:center; justify-content:center; font-size:18px; background:var(--surface2);">🗂️</div>
                    </div>
                    <div style="flex:1;">
                        <div style="font-size:11px; color:var(--muted); font-family:var(--mono); margin-bottom:6px;">View name</div>
                        <input type="text" value="List" class="ptm-input" style="width:100%;" readonly>
                    </div>
                </div>
                <div style="border-top:1px solid var(--border); margin:4px 0 8px;"></div>

                <div class="opt-row" onclick="toggleOptCols()">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="16" rx="2"/><line x1="9" y1="4" x2="9" y2="20"/></svg>
                    <span>Show/hide columns</span>
                    <span style="margin-left:auto; font-size:12px; color:var(--muted);" id="optHiddenCount"></span>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--muted)" stroke-width="2"><path d="M9 6l6 6-6 6"/></svg>
                </div>
                <div id="optCols" style="display:none; padding:4px 0 8px 30px;">
                    @foreach(array_merge([['due','Due date'],['assignee','Assignee'],['status','Status'],['priority','Priority']], $customFields->map(fn($cf)=>['cf-'.$cf->id, $cf->name])->all()) as $oc)
                    <label class="opt-check"><input type="checkbox" class="col-toggle" value="{{ $oc[0] }}" checked onchange="toggleCol('{{ $oc[0] }}', this.checked); updateOptHidden()"> {{ $oc[1] }}</label>
                    @endforeach
                </div>

                <div class="opt-row" onclick="closeOptions(); setTimeout(()=>document.getElementById('tbFilter').classList.add('show'),260)">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                    <span>Filters</span><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--muted)" stroke-width="2" style="margin-left:auto;"><path d="M9 6l6 6-6 6"/></svg>
                </div>
                <div class="opt-row" onclick="closeOptions(); setTimeout(()=>document.getElementById('tbSort').classList.add('show'),260)">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 5h10M11 9h7M11 13h4M3 17l3 3 3-3M6 18V4"/></svg>
                    <span>Sorts</span><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--muted)" stroke-width="2" style="margin-left:auto;"><path d="M9 6l6 6-6 6"/></svg>
                </div>
                <div class="opt-row" onclick="closeOptions(); setTimeout(()=>document.getElementById('tbGroup').classList.add('show'),260)">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="4" rx="1"/><rect x="3" y="10" width="18" height="4" rx="1"/><rect x="3" y="17" width="18" height="4" rx="1"/></svg>
                    <span>Groups</span><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--muted)" stroke-width="2" style="margin-left:auto;"><path d="M9 6l6 6-6 6"/></svg>
                </div>
                <div class="opt-row" onclick="toggleOptSubtasks()">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 3v12"/><circle cx="18" cy="6" r="3"/><circle cx="6" cy="18" r="3"/><path d="M18 9a9 9 0 01-9 9"/></svg>
                    <span>Subtasks</span>
                    <span style="margin-left:auto; font-size:12px; color:var(--muted);" id="optSubLabel">Collapsed</span>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--muted)" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg>
                </div>
                <div class="opt-row" style="cursor:default;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M8 12l3 3 5-6"/></svg>
                    <span>Hide completed tasks</span>
                    <label style="margin-left:auto; display:flex; align-items:center;"><input type="checkbox" onchange="tbHideCompleted(this.checked)" style="width:16px; height:16px; cursor:pointer;"></label>
                </div>
            </div>
        </div>
    </div>

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
        .opt-drawer { position:fixed; inset:0; z-index:190; pointer-events:none; }
        .opt-drawer .opt-overlay { position:absolute; inset:0; background:rgba(0,0,0,0.4); opacity:0; transition:opacity 0.2s; }
        .opt-drawer .opt-panel { position:absolute; top:0; right:0; height:100%; width:420px; max-width:92vw; background:var(--surface); border-left:1px solid var(--border2); display:flex; flex-direction:column; transform:translateX(100%); transition:transform 0.25s ease; box-shadow:-10px 0 40px rgba(0,0,0,0.35); }
        .opt-drawer.open { pointer-events:auto; }
        .opt-drawer.open .opt-overlay { opacity:1; }
        .opt-drawer.open .opt-panel { transform:translateX(0); }
        .opt-row { display:flex; align-items:center; gap:12px; padding:12px 10px; border-radius:8px; cursor:pointer; font-size:14px; color:var(--text); }
        .opt-row:hover { background:var(--surface2); }
        .opt-row > svg:first-child { color:var(--muted); flex-shrink:0; }
        .opt-check { display:flex; align-items:center; gap:9px; padding:6px 8px; border-radius:6px; cursor:pointer; font-size:13px; color:var(--text); }
        .opt-check:hover { background:var(--surface2); }
        .opt-check input { width:15px; height:15px; cursor:pointer; }
    </style>

</x-employee-layout>
