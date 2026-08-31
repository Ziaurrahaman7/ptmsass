@php
    $ganttPayload = $scheduleTasks->map(function ($t) {
        $start = $t->start_date ?? $t->due_date ?? $t->created_at;
        $due = $t->due_date ?? $t->start_date ?? $t->created_at;
        if ($due->lt($start)) {
            $due = $start->copy();
        }
        return [
            'id'      => $t->id,
            'title'   => $t->title,
            'start'   => $start->format('Y-m-d'),
            'due'     => $due->format('Y-m-d'),
            'status'  => $t->status,
            'section' => $t->section?->name,
            'sub'     => (bool) $t->parent_task_id,
            'deps'    => $t->blockedByLinks->map(fn ($d) => [
                'id'         => $d->id,
                'depends_on' => $d->depends_on_task_id,
                'title'      => $d->dependsOn?->title,
            ])->values(),
        ];
    })->values();
@endphp

<style>
    .gn-wrap { border:1px solid var(--border); border-radius:12px; overflow:hidden; background:var(--surface); }
    .gn-toolbar { display:flex; align-items:center; justify-content:space-between; gap:12px; padding:12px 14px; border-bottom:1px solid var(--border); }
    .gn-scroll { overflow:auto; max-height:640px; position:relative; }
    .gn-table { min-width:100%; }
    .gn-row { display:grid; grid-template-columns:260px 1fr; border-bottom:1px solid var(--border); min-height:40px; }
    .gn-row:hover { background:rgba(255,255,255,0.02); }
    .gn-left { padding:6px 12px; display:flex; flex-direction:column; justify-content:center; gap:3px; border-right:1px solid var(--border); position:sticky; left:0; background:var(--surface); z-index:2; }
    .gn-title { font-size:13px; color:var(--text); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; cursor:pointer; }
    .gn-title:hover { color:var(--accent2); }
    .gn-meta { font-size:10px; color:var(--muted); display:flex; align-items:center; gap:6px; }
    .gn-track { position:relative; height:40px; }
    .gn-days { display:flex; height:100%; }
    .gn-day { flex:0 0 var(--gn-day-w); border-right:1px solid rgba(255,255,255,0.04); height:100%; }
    .gn-day.weekend { background:rgba(255,255,255,0.015); }
    .gn-day.today { background:rgba(59,130,246,0.08); }
    .gn-head-days { display:flex; border-bottom:1px solid var(--border); }
    .gn-head-day { flex:0 0 var(--gn-day-w); text-align:center; padding:6px 0 4px; border-right:1px solid rgba(255,255,255,0.04); }
    .gn-bar { position:absolute; top:9px; height:22px; border-radius:6px; display:flex; align-items:center; min-width:16px; z-index:1; cursor:grab; }
    .gn-bar:active { cursor:grabbing; }
    .gn-bar.done { opacity:0.55; }
    .gn-handle { position:absolute; top:0; width:7px; height:100%; cursor:ew-resize; }
    .gn-handle.l { left:0; border-radius:6px 0 0 6px; }
    .gn-handle.r { right:0; border-radius:0 6px 6px 0; }
    .gn-svg { position:absolute; inset:0; pointer-events:none; overflow:visible; }
    .gn-dep { display:inline-flex; align-items:center; gap:4px; font-size:10px; color:var(--muted); background:var(--surface2); border-radius:4px; padding:1px 5px; }
    .gn-dep button { background:none; border:none; color:var(--muted); cursor:pointer; padding:0; line-height:1; }
    .gn-dep button:hover { color:var(--danger); }
    .gn-empty { padding:48px 20px; text-align:center; color:var(--muted); font-size:13px; }
</style>

<div class="gn-toolbar">
    <div>
        <div style="font-size:14px; font-weight:600; color:var(--text);">Timeline</div>
        <div style="font-size:12px; color:var(--muted); margin-top:2px;">Drag a bar to move dates. Drag the ends to resize. Add a “blocked by” link for dependencies.</div>
    </div>
    <div style="display:flex; gap:8px; font-size:11px; color:var(--muted);">
        <span style="display:inline-flex; align-items:center; gap:5px;"><span style="width:10px; height:10px; border-radius:3px; background:#22d3ee;"></span> In progress</span>
        <span style="display:inline-flex; align-items:center; gap:5px;"><span style="width:10px; height:10px; border-radius:3px; background:#a78bfa;"></span> In review</span>
        <span style="display:inline-flex; align-items:center; gap:5px;"><span style="width:10px; height:10px; border-radius:3px; background:#4ade80;"></span> Done</span>
    </div>
</div>

@if($scheduleTasks->isEmpty())
    <div class="gn-empty">Add tasks with dates to see the timeline.</div>
@else
<div class="gn-wrap">
    <div class="gn-scroll" id="ganttScroll">
        <div id="ganttRoot" style="--gn-day-w:28px;"></div>
    </div>
</div>
<script>
(function () {
    const TASKS = @json($ganttPayload);
    const DAY_W = 28;
    const ROW_H = 40;
    const colors = { todo:'#6b7385', in_progress:'#22d3ee', in_review:'#a78bfa', done:'#4ade80' };
    const slug = @json($slug);

    if (!TASKS.length) return;

    const parse = s => { const [y,m,d] = s.split('-').map(Number); return new Date(y, m-1, d); };
    const fmt = d => {
        const y = d.getFullYear();
        const m = String(d.getMonth()+1).padStart(2,'0');
        const day = String(d.getDate()).padStart(2,'0');
        return `${y}-${m}-${day}`;
    };
    const addDays = (d, n) => { const x = new Date(d); x.setDate(x.getDate()+n); return x; };
    const diffDays = (a,b) => Math.round((parse(b) - parse(a)) / 86400000);

    let min = TASKS.reduce((m,t) => t.start < m ? t.start : m, TASKS[0].start);
    let max = TASKS.reduce((m,t) => t.due > m ? t.due : m, TASKS[0].due);
    min = fmt(addDays(parse(min), -3));
    max = fmt(addDays(parse(max), 10));
    const range = diffDays(min, max) + 1;
    const today = fmt(new Date());

    const days = [];
    for (let i = 0; i < range; i++) days.push(fmt(addDays(parse(min), i)));

    const root = document.getElementById('ganttRoot');
    const byId = Object.fromEntries(TASKS.map(t => [t.id, t]));

    function render() {
        const headDays = days.map(ds => {
            const d = parse(ds);
            const wk = d.getDay() === 0 || d.getDay() === 6;
            const isToday = ds === today;
            return `<div class="gn-head-day${wk?' weekend':''}${isToday?' today':''}">
                <div style="font-size:9px;color:var(--muted);">${d.toLocaleDateString(undefined,{weekday:'narrow'})}</div>
                <div style="font-size:11px;color:${isToday?'#93c5fd':'var(--text)'};">${d.getDate()}</div>
            </div>`;
        }).join('');

        const rows = TASKS.map((t, idx) => {
            const left = diffDays(min, t.start) * DAY_W;
            const width = Math.max((diffDays(t.start, t.due) + 1) * DAY_W, 16);
            const color = colors[t.status] || '#6b7385';
            const deps = (t.deps || []).map(d =>
                `<span class="gn-dep">${esc(d.title || ('#'+d.depends_on))}
                    <button type="button" onclick="event.stopPropagation(); removeDep(${t.id},${d.id})">×</button>
                </span>`
            ).join('');
            const options = TASKS.filter(o => o.id !== t.id).map(o =>
                `<option value="${o.id}">${esc(o.title)}</option>`
            ).join('');
            const dayCells = days.map(ds => {
                const d = parse(ds);
                const wk = d.getDay() === 0 || d.getDay() === 6;
                return `<div class="gn-day${wk?' weekend':''}${ds===today?' today':''}"></div>`;
            }).join('');

            return `<div class="gn-row" data-row="${t.id}">
                <div class="gn-left">
                    <div class="gn-title" style="${t.sub?'padding-left:12px;':''}" onclick="openPanel(${t.id})">${t.sub?'↳ ':''}${esc(t.title)}</div>
                    <div class="gn-meta">
                        ${t.section ? `<span>${esc(t.section)}</span>` : ''}
                        ${deps}
                        <select onchange="addDep(${t.id}, this)" style="background:var(--surface2);border:1px solid var(--border2);color:var(--muted);border-radius:4px;font-size:10px;padding:1px 4px;max-width:120px;">
                            <option value="">+ blocked by</option>${options}
                        </select>
                    </div>
                </div>
                <div class="gn-track" data-track="${t.id}">
                    <div class="gn-days">${dayCells}</div>
                    <div class="gn-bar ${t.status==='done'?'done':''}" data-bar="${t.id}" style="left:${left}px;width:${width}px;background:${color};">
                        <div class="gn-handle l" data-edge="start"></div>
                        <div class="gn-handle r" data-edge="due"></div>
                    </div>
                </div>
            </div>`;
        }).join('');

        root.innerHTML = `
            <div class="gn-row" style="min-height:auto; position:sticky; top:0; z-index:3; background:var(--surface);">
                <div class="gn-left" style="min-height:44px;"><span style="font-size:11px;color:var(--muted);font-family:var(--mono);">TASK</span></div>
                <div class="gn-head-days">${headDays}</div>
            </div>
            <div style="position:relative;">
                ${rows}
                <svg class="gn-svg" id="ganttSvg"></svg>
            </div>`;

        bindDrag();
        drawDeps();
        const todayEl = root.querySelector('.gn-head-day.today');
        const scroll = document.getElementById('ganttScroll');
        if (todayEl && scroll) scroll.scrollLeft = Math.max(0, todayEl.offsetLeft - 200);
    }

    function esc(s) {
        return String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
    }

    function drawDeps() {
        const svg = document.getElementById('ganttSvg');
        if (!svg) return;
        const box = svg.parentElement.getBoundingClientRect();
        svg.setAttribute('width', svg.parentElement.scrollWidth || box.width);
        svg.setAttribute('height', TASKS.length * ROW_H);
        let paths = '';
        TASKS.forEach((t, ti) => {
            (t.deps || []).forEach(d => {
                const pred = byId[d.depends_on];
                if (!pred) return;
                const pi = TASKS.findIndex(x => x.id === pred.id);
                if (pi < 0) return;
                const leftCol = 260;
                const x1 = leftCol + diffDays(min, pred.due) * DAY_W + DAY_W;
                const y1 = pi * ROW_H + 20;
                const x2 = leftCol + diffDays(min, t.start) * DAY_W;
                const y2 = ti * ROW_H + 20;
                const mid = x1 + Math.max(12, (x2 - x1) / 2);
                paths += `<path d="M${x1},${y1} C${mid},${y1} ${mid},${y2} ${x2},${y2}" fill="none" stroke="#fbbf24" stroke-width="1.6"/>`;
                paths += `<polygon points="${x2},${y2} ${x2-6},${y2-4} ${x2-6},${y2+4}" fill="#fbbf24"/>`;
            });
        });
        svg.innerHTML = paths;
    }

    function bindDrag() {
        root.querySelectorAll('.gn-bar').forEach(bar => {
            bar.addEventListener('pointerdown', ev => {
                if (ev.target.classList.contains('gn-handle')) return;
                startMove(ev, bar, 'move');
            });
        });
        root.querySelectorAll('.gn-handle').forEach(h => {
            h.addEventListener('pointerdown', ev => {
                ev.stopPropagation();
                startMove(ev, h.closest('.gn-bar'), h.dataset.edge);
            });
        });
    }

    function startMove(ev, bar, mode) {
        ev.preventDefault();
        const id = +bar.dataset.bar;
        const t = byId[id];
        const startX = ev.clientX;
        const origStart = t.start;
        const origDue = t.due;
        const onMove = e => {
            const delta = Math.round((e.clientX - startX) / DAY_W);
            if (mode === 'move') {
                t.start = fmt(addDays(parse(origStart), delta));
                t.due = fmt(addDays(parse(origDue), delta));
            } else if (mode === 'start') {
                const next = fmt(addDays(parse(origStart), delta));
                t.start = next <= t.due ? next : t.due;
            } else {
                const next = fmt(addDays(parse(origDue), delta));
                t.due = next >= t.start ? next : t.start;
            }
            const left = diffDays(min, t.start) * DAY_W;
            const width = Math.max((diffDays(t.start, t.due) + 1) * DAY_W, 16);
            bar.style.left = left + 'px';
            bar.style.width = width + 'px';
            drawDeps();
        };
        const onUp = () => {
            document.removeEventListener('pointermove', onMove);
            document.removeEventListener('pointerup', onUp);
            if (typeof patchField === 'function') {
                patchField(id, 'start_date', t.start);
                patchField(id, 'due_date', t.due);
            }
        };
        document.addEventListener('pointermove', onMove);
        document.addEventListener('pointerup', onUp);
    }

    window.addDep = function (taskId, sel) {
        const pred = sel.value;
        sel.value = '';
        if (!pred) return;
        fetch(`/${slug}/admin/tasks/${taskId}/dependencies`, {
            method:'POST',
            headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrfToken,'Accept':'application/json'},
            body: JSON.stringify({ depends_on_task_id: Number(pred), type: 'FS' })
        }).then(r => r.json()).then(d => {
            if (!d.success) { alert(d.message || 'Could not add dependency'); return; }
            const predTask = byId[Number(pred)];
            byId[taskId].deps.push({ id: d.dependency.id, depends_on: Number(pred), title: predTask?.title });
            render();
        }).catch(() => alert('Could not add dependency'));
    };

    window.removeDep = function (taskId, depId) {
        fetch(`/${slug}/admin/tasks/${taskId}/dependencies/${depId}`, {
            method:'DELETE',
            headers:{'X-CSRF-TOKEN':csrfToken,'Accept':'application/json'}
        }).then(r => r.json()).then(d => {
            if (!d.success) return;
            byId[taskId].deps = (byId[taskId].deps || []).filter(x => x.id !== depId);
            render();
        });
    };

    render();
    document.querySelectorAll('.al-tab').forEach(btn => {
        btn.addEventListener('click', () => setTimeout(drawDeps, 80));
    });
})();
</script>
@endif
