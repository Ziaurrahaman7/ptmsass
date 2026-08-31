@php
    $calScale = request('scale') === 'week' ? 'week' : 'month';
    $weekParam = request('week');
    $monthParam = request('month');

    if ($calScale === 'week') {
        try { $weekStart = $weekParam ? \Carbon\Carbon::parse($weekParam)->startOfWeek(\Carbon\Carbon::MONDAY) : now()->startOfWeek(\Carbon\Carbon::MONDAY); }
        catch (\Exception $e) { $weekStart = now()->startOfWeek(\Carbon\Carbon::MONDAY); }
        $weekEnd = $weekStart->copy()->endOfWeek(\Carbon\Carbon::SUNDAY);
        $calCursor = $weekStart->copy();
        $calPrev = $weekStart->copy()->subWeek()->toDateString();
        $calNext = $weekStart->copy()->addWeek()->toDateString();
        $calTodayKey = now()->startOfWeek(\Carbon\Carbon::MONDAY)->toDateString();
        $calLabel = $weekStart->month === $weekEnd->month
            ? $weekStart->format('M j') . ' – ' . $weekEnd->format('j, Y')
            : $weekStart->format('M j') . ' – ' . $weekEnd->format('M j, Y');
        $calGridStart = $weekStart->copy();
        $calGridEnd = $weekEnd->copy();
    } else {
        try { $calCursor = $monthParam ? \Carbon\Carbon::createFromFormat('Y-m', $monthParam)->startOfMonth() : now()->startOfMonth(); }
        catch (\Exception $e) { $calCursor = now()->startOfMonth(); }
        $calPrev = $calCursor->copy()->subMonth()->format('Y-m');
        $calNext = $calCursor->copy()->addMonth()->format('Y-m');
        $calTodayKey = now()->format('Y-m');
        $calLabel = $calCursor->format('F Y');
        $calGridStart = $calCursor->copy()->startOfMonth()->startOfWeek(\Carbon\Carbon::MONDAY);
        $calGridEnd = $calCursor->copy()->endOfMonth()->endOfWeek(\Carbon\Carbon::SUNDAY);
    }

    $calToday = now()->toDateString();
    $calMonthNum = $calCursor->month;
    $calDays = \Carbon\CarbonPeriod::create($calGridStart, $calGridEnd)->toArray();
    $calByDate = $scheduleTasks->filter(fn ($t) => $t->due_date)->groupBy(fn ($t) => $t->due_date->toDateString());
    $calPalette = ['#4a8f6a','#9c6b4a','#c96b98','#4a7fc0','#8b6fc0','#c08348','#3f9a9a','#b39240','#a05a6f','#5f7d9c'];
    $calColor = fn ($t) => $calPalette[$t->id % count($calPalette)];
    $weekStartOfCursor = $calCursor->copy()->startOfWeek(\Carbon\Carbon::MONDAY)->toDateString();
    $monthOfCursor = $calCursor->format('Y-m');
    $calShowUrl = function (?string $key = null, ?string $scale = null) use ($slug, $project, $calScale, $weekStartOfCursor, $monthOfCursor) {
        $scale = $scale ?? $calScale;
        $params = [
            'slug' => $slug,
            'project' => $project,
            'tab' => 'calendar',
            'scale' => $scale,
        ];
        if ($scale === 'week') {
            $params['week'] = $key ?? $weekStartOfCursor;
        } else {
            $params['month'] = $key ?? $monthOfCursor;
        }
        return route('company.projects.show', $params);
    };
@endphp

<style>
    .pj-cal-nav { width:32px; height:32px; border-radius:8px; border:1px solid var(--border2); background:var(--surface2); color:var(--text); display:inline-flex; align-items:center; justify-content:center; text-decoration:none; font-size:16px; }
    .pj-cal-nav:hover { background:var(--surface); }
    .pj-cal-grid { display:grid; grid-template-columns:repeat(7, minmax(120px, 1fr)); border-top:1px solid var(--border); border-left:1px solid var(--border); }
    .pj-cal-head { padding:7px 10px; font-size:10px; color:var(--muted); font-family:var(--mono); letter-spacing:0.06em; border-right:1px solid var(--border); border-bottom:1px solid var(--border); }
    .pj-cal-day { min-height:148px; padding:6px; border-right:1px solid var(--border); border-bottom:1px solid var(--border); display:flex; flex-direction:column; }
    .pj-cal-grid.is-week .pj-cal-day { min-height:420px; }
    .cal-scale { display:inline-flex; border:1px solid var(--border2); border-radius:8px; overflow:hidden; }
    .cal-scale a { padding:5px 12px; font-size:12px; color:var(--muted); text-decoration:none; font-family:var(--font); }
    .cal-scale a:hover { color:var(--text); background:var(--surface); }
    .cal-scale a.active { background:var(--surface); color:var(--text); font-weight:600; }
    .pj-cal-pill { cursor:pointer; display:flex; align-items:center; gap:5px; margin-bottom:3px; padding:3px 5px; border-radius:5px; overflow:hidden; }
    .pj-cal-list { min-height:90px; }
    .pj-cal-toolbar .tb-btn.active { color:var(--accent2); }
    .pj-cal-grid.hide-weekends { grid-template-columns:repeat(5, minmax(120px, 1fr)); }
    .pj-cal-grid.hide-weekends .pj-cal-weekend { display:none; }
    .pj-cal-search { background:var(--surface2); border:1px solid var(--border2); border-radius:8px; color:var(--text); font-family:var(--font); font-size:12px; padding:6px 10px 6px 28px; width:150px; }
    .pj-cal-search:focus { outline:none; border-color:var(--accent2); }
</style>

<div class="pj-cal-toolbar" style="display:flex; align-items:center; justify-content:space-between; padding:0 0 14px; gap:12px; flex-wrap:wrap;">
    <div style="display:flex; align-items:center; gap:12px; flex-wrap:wrap;">
        <div style="display:flex; align-items:center; gap:4px;">
            <a href="{{ $calShowUrl($calPrev) }}" class="pj-cal-nav" title="{{ $calScale === 'week' ? 'Previous week' : 'Previous month' }}">‹</a>
            <a href="{{ $calShowUrl($calTodayKey) }}" class="pj-cal-nav" style="width:auto; padding:0 12px; font-size:13px;">Today</a>
            <a href="{{ $calShowUrl($calNext) }}" class="pj-cal-nav" title="{{ $calScale === 'week' ? 'Next week' : 'Next month' }}">›</a>
        </div>
        <span style="font-size:15px; font-weight:600; color:var(--text);">{{ $calLabel }}</span>
        <div class="cal-scale">
            <a href="{{ $calShowUrl($monthOfCursor, 'month') }}" class="{{ $calScale === 'month' ? 'active' : '' }}">Month</a>
            <a href="{{ $calShowUrl($weekStartOfCursor, 'week') }}" class="{{ $calScale === 'week' ? 'active' : '' }}">Week</a>
        </div>
    </div>
    <div style="display:flex; align-items:center; gap:2px;">
        <div style="position:relative;">
            <button type="button" class="tb-btn" id="calFilterBtn" onclick="tbToggle(event,'calFilter')">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                Filter <span id="calFilterCount" style="display:none; color:var(--accent2); font-family:var(--mono);"></span>
            </button>
            <div id="calFilter" class="tb-menu" style="right:0; left:auto; width:420px; max-width:92vw; padding:18px 20px;">
                <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:14px;">
                    <span style="font-size:16px; font-weight:600; color:var(--text);">Filters</span>
                    <button type="button" onclick="tbClearFilters()" style="background:none; border:none; color:var(--muted); font-size:13px; cursor:pointer; font-family:var(--font);">Clear</button>
                </div>
                <div class="tb-mlabel" style="padding-left:0;">Quick filters</div>
                <div style="display:flex; flex-wrap:wrap; gap:8px; margin-top:8px;">
                    <button type="button" class="qf-pill" data-qf="incomplete" onclick="tbQuick('incomplete')">Incomplete tasks</button>
                    <button type="button" class="qf-pill" data-qf="completed" onclick="tbQuick('completed')">Completed tasks</button>
                    <button type="button" class="qf-pill" data-qf="mine" onclick="tbQuick('mine')">Just my tasks</button>
                    <button type="button" class="qf-pill" data-qf="due_this_week" onclick="tbQuick('due_this_week')">Due this week</button>
                    <button type="button" class="qf-pill" data-qf="overdue" onclick="tbQuick('overdue')">Overdue</button>
                </div>
                <div style="margin-top:14px;">
                    <div class="tb-mlabel" style="padding-left:0;">Status</div>
                    <div style="display:flex; flex-wrap:wrap; gap:8px; margin-top:8px;">
                        <label class="fg-opt"><input type="checkbox" onchange="tbFilterChange('status','todo',this.checked)"> To Do</label>
                        <label class="fg-opt"><input type="checkbox" onchange="tbFilterChange('status','in_progress',this.checked)"> In Progress</label>
                        <label class="fg-opt"><input type="checkbox" onchange="tbFilterChange('status','in_review',this.checked)"> In Review</label>
                        <label class="fg-opt"><input type="checkbox" onchange="tbFilterChange('status','done',this.checked)"> Done</label>
                    </div>
                </div>
                <div style="margin-top:14px;">
                    <div class="tb-mlabel" style="padding-left:0;">Priority</div>
                    <div style="display:flex; flex-wrap:wrap; gap:8px; margin-top:8px;">
                        @foreach($companyPriorities as $p)
                        <label class="fg-opt"><input type="checkbox" onchange="tbFilterChange('priority','{{ $p->slug }}',this.checked)"> {{ $p->name }}</label>
                        @endforeach
                    </div>
                </div>
                <div style="margin-top:14px;">
                    <div class="tb-mlabel" style="padding-left:0;">Assignee</div>
                    <div style="display:flex; flex-direction:column; gap:6px; margin-top:8px; max-height:160px; overflow-y:auto;">
                        @foreach($members as $m)
                        <label class="fg-opt"><input type="checkbox" onchange="fgToggle('assignee','{{ $m->id }}',this.checked)"> {{ $m->name }}</label>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        <div style="position:relative;">
            <button type="button" class="tb-btn" onclick="tbToggle(event,'calOptions')">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="4" y1="8" x2="20" y2="8"/><line x1="8" y1="12" x2="16" y2="12"/><line x1="10" y1="16" x2="14" y2="16"/></svg>
                Options
            </button>
            <div id="calOptions" class="tb-menu" style="right:0; left:auto; width:220px; padding:10px;">
                <label class="tb-opt"><input type="checkbox" checked onchange="calSetWeekends(this.checked)"> Show weekends</label>
                <label class="tb-opt"><input type="checkbox" onchange="tbHideCompleted(this.checked)"> Hide completed</label>
            </div>
        </div>
        <div style="width:1px; height:20px; background:var(--border2); margin:0 6px;"></div>
        <div style="position:relative; display:flex; align-items:center;">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--muted)" stroke-width="2" style="position:absolute; left:9px; pointer-events:none;"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="search" class="pj-cal-search" placeholder="Search" oninput="filterTasks(this.value)" autocomplete="off">
        </div>
    </div>
</div>

<div style="overflow-x:auto;">
    <div class="pj-cal-grid {{ $calScale === 'week' ? 'is-week' : '' }}" id="pjCalGrid">
        @foreach(['MON','TUE','WED','THU','FRI','SAT','SUN'] as $i => $dow)
            <div class="pj-cal-head {{ $i >= 5 ? 'pj-cal-weekend' : '' }}">{{ $dow }}</div>
        @endforeach
        @foreach($calDays as $date)
            @php
                $dateStr = $date->toDateString();
                $dayTasks = $calByDate[$dateStr] ?? collect();
                $isToday = $dateStr === $calToday;
                $inMonth = $calScale === 'week' || $date->month === $calMonthNum;
            @endphp
            <div class="pj-cal-day {{ $date->isWeekend() ? 'pj-cal-weekend' : '' }}" style="{{ $isToday ? 'background:rgba(74,127,192,0.07);' : '' }} {{ $inMonth ? '' : 'opacity:0.45;' }}">
                <div style="margin-bottom:6px;">
                    @if($isToday)
                        <span style="display:inline-flex; align-items:center; justify-content:center; min-width:20px; height:20px; padding:0 5px; border-radius:10px; background:#3b82f6; color:#fff; font-size:12px; font-weight:700;">{{ $calScale === 'week' ? $date->format('M j') : $date->day }}</span>
                    @else
                        <span style="font-size:12px; font-family:var(--mono); color:var(--muted);">{{ $calScale === 'week' ? $date->format('M j') : $date->day }}</span>
                    @endif
                </div>
                <div class="pj-cal-list" data-date="{{ $dateStr }}" style="flex:1;">
                    @foreach($dayTasks as $t)
                        @php $who = $t->assignees->first() ?? $t->assignee; @endphp
                        <div class="pj-cal-pill" data-id="{{ $t->id }}"
                            data-title="{{ strtolower($t->title) }}"
                            data-status="{{ $t->status }}"
                            data-priority="{{ $t->priority }}"
                            data-due="{{ $t->due_date?->format('Y-m-d') }}"
                            data-assignees="{{ $t->assignees->pluck('id')->push($t->assigned_to)->filter()->unique()->implode(',') }}"
                            data-createdby="{{ $t->created_by }}"
                            data-created="{{ $t->created_at?->format('Y-m-d') }}"
                            data-modified="{{ $t->updated_at?->format('Y-m-d') }}"
                            onclick="if(!window.__pjCalDrag) openPanel({{ $t->id }})" title="{{ $t->title }}" style="background:{{ $calColor($t) }};">
                            @if($who)
                                <span style="width:16px; height:16px; border-radius:50%; background:rgba(0,0,0,0.25); color:#fff; font-size:9px; font-weight:700; display:flex; align-items:center; justify-content:center; flex-shrink:0;">{{ strtoupper(substr($who->name,0,1)) }}</span>
                            @endif
                            @if($t->status === 'done')
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="3" style="flex-shrink:0;"><polyline points="20 6 9 17 4 12"/></svg>
                            @endif
                            <span style="flex:1; min-width:0; font-size:11px; color:#fff; font-weight:500; {{ $calScale === 'week' ? 'white-space:normal; line-height:1.35;' : 'white-space:nowrap; overflow:hidden; text-overflow:ellipsis;' }}">{{ $t->title }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</div>

<script>
function calSetWeekends(on) {
    document.getElementById('pjCalGrid')?.classList.toggle('hide-weekends', !on);
}
(function () {
    if (typeof Sortable === 'undefined') return;
    document.querySelectorAll('.pj-cal-list').forEach(list => {
        new Sortable(list, {
            group: 'project-cal',
            animation: 140,
            onStart() { window.__pjCalDrag = true; },
            onEnd(evt) {
                setTimeout(() => { window.__pjCalDrag = false; }, 40);
                const id = evt.item.dataset.id;
                const date = evt.to.dataset.date;
                if (!id || !date || typeof patchField !== 'function') return;
                patchField(id, 'due_date', date);
            }
        });
    });
})();
</script>
