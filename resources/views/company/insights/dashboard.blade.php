<x-company-layout :title="$dashboard->title">

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
function renderChart(canvasId, style, cd) {
    const COLORS = ['#22d3ee','#4ade80','#a78bfa','#fbbf24','#f87171','#fb923c','#60a5fa','#34d399','#e879f9','#38bdf8'];
    const gridColor = 'rgba(255,255,255,0.06)';
    const tickColor = '#6b7385';
    const labels = cd.map(d => d.label);
    const values = cd.map(d => d.value);
    const topLabels = {
        id:'topLabels',
        afterDatasetsDraw(chart){
            const {ctx} = chart;
            const isH = chart.options.indexAxis === 'y';
            chart.getDatasetMeta(0).data.forEach((bar,i)=>{
                const val = chart.data.datasets[0].data[i];
                if(!val) return;
                ctx.save(); ctx.font='600 11px Inter,sans-serif'; ctx.fillStyle='#cbd5e1'; ctx.textAlign='center';
                if(isH){ ctx.textBaseline='middle'; ctx.fillText(val, bar.x+20, bar.y); }
                else { ctx.textBaseline='bottom'; ctx.fillText(val, bar.x, bar.y-5); }
                ctx.restore();
            });
        }
    };
    const ctx = document.getElementById(canvasId).getContext('2d');
    if(style === 'donut'){
        new Chart(ctx,{
            type:'doughnut',
            data:{labels, datasets:[{data:values, backgroundColor:COLORS.slice(0,cd.length), borderWidth:0, hoverOffset:8}]},
            options:{responsive:true, maintainAspectRatio:false, cutout:'62%',
                plugins:{legend:{position:'bottom',labels:{color:tickColor,font:{size:11},boxWidth:10,padding:12}},
                tooltip:{callbacks:{label:c=>` ${c.label}: ${c.parsed}`}}}}
        });
    } else {
        const isH = style === 'column';
        const type = style === 'line' ? 'line' : 'bar';
        new Chart(ctx,{
            type,
            data:{labels, datasets:[{data:values,
                backgroundColor: style==='line' ? 'rgba(34,211,238,0.12)' : COLORS.slice(0,cd.length),
                borderColor: style==='line' ? '#22d3ee' : 'transparent',
                borderWidth: style==='line' ? 2 : 0,
                borderRadius: style==='line' ? 0 : 5,
                fill: style==='line', tension: style==='line' ? 0.4 : 0,
                pointBackgroundColor: style==='line' ? '#22d3ee' : undefined,
            }]},
            options:{
                indexAxis: isH ? 'y' : 'x',
                responsive:true, maintainAspectRatio:false,
                layout:{padding:{top: isH ? 4 : 24, right: isH ? 44 : 8}},
                plugins:{legend:{display:false}, tooltip:{callbacks:{label:c=>` ${c.parsed[isH?'x':'y']}`}}},
                scales:{
                    x:{grid:{color: isH ? gridColor : 'transparent'}, border:{color:'rgba(255,255,255,0.08)'},
                       ticks:{color:tickColor, font:{size:10}, maxRotation: isH ? 0 : 35,
                        callback(v,i){ const l=this.getLabelForValue(isH?v:i); return l&&l.length>12?l.slice(0,12)+'…':l; }}},
                    y:{grid:{color: isH ? 'transparent' : gridColor}, border:{color:'rgba(255,255,255,0.08)'}, beginAtZero:true,
                       ticks:{color:tickColor, font:{size:10},
                        callback(v){ if(isH){const l=this.getLabelForValue(v); return l&&l.length>14?l.slice(0,14)+'…':l;} return v; }}}
                }
            },
            plugins:[topLabels]
        });
    }
}
</script>

<style>
.db-topbar {
    display: flex; align-items: center; justify-content: space-between;
    padding: 10px 0 18px;
    border-bottom: 1px solid var(--border);
    margin-bottom: 20px;
}
.db-title-row { display: flex; align-items: center; gap: 10px; }
.db-title-icon {
    width: 36px; height: 36px; border-radius: 8px;
    background: linear-gradient(135deg,#4f46e5,#2563eb);
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.db-title-text { font-size: 20px; font-weight: 700; color: var(--text); }
.db-actions { display: flex; align-items: center; gap: 10px; }
.btn-add-widget {
    display: inline-flex; align-items: center; gap: 6px;
    background: var(--surface2); border: 1px solid var(--border);
    color: var(--text); border-radius: 8px; padding: 7px 14px;
    font-size: 13px; font-family: var(--font); cursor: pointer;
    transition: background .15s, border-color .15s;
}
.btn-add-widget:hover { background: var(--border); border-color: var(--border2); }

/* Add widget dropdown menu */
.add-widget-menu {
    display: none; position: absolute; top: calc(100% + 6px); left: 0; z-index: 50;
    background: var(--surface); border: 1px solid var(--border2); border-radius: 10px;
    padding: 6px; width: 240px; box-shadow: 0 8px 24px rgba(0,0,0,.35);
}
.add-widget-menu.open { display: block; }
.add-widget-menu-item {
    display: flex; align-items: center; gap: 10px; width: 100%;
    background: none; border: none; text-align: left; cursor: pointer;
    padding: 8px 10px; border-radius: 8px; color: var(--text); font-family: var(--font);
    transition: background .12s;
}
.add-widget-menu-item:hover { background: var(--surface2); }
.add-widget-menu-item svg { flex-shrink: 0; color: var(--muted); }
.add-widget-menu-item-title { font-size: 13px; font-weight: 600; color: var(--text); }
.add-widget-menu-item-desc { font-size: 11px; color: var(--muted); margin-top: 1px; }

.btn-share {
    display: inline-flex; align-items: center; gap: 6px;
    background: #4573d2; color: #fff; border: none; border-radius: 8px;
    padding: 7px 14px; font-size: 13px; font-weight: 600; cursor: pointer;
    font-family: var(--font); transition: background .15s;
}
.btn-share:hover { background: #3a62bb; }

/* Title dropdown (Edit details / Set color / Copy link / Duplicate / Delete) */
.db-chevron-btn {
    background: none; border: none; color: var(--muted); cursor: pointer;
    padding: 4px; border-radius: 6px; display: flex; transition: background .12s, color .12s;
}
.db-chevron-btn:hover { background: var(--surface2); color: var(--text); }
.db-star-btn {
    background: none; border: none; color: var(--muted); cursor: pointer;
    padding: 4px; border-radius: 6px; display: flex; transition: background .12s, color .12s;
}
.db-star-btn:hover { background: var(--surface2); color: var(--text); }
.db-star-btn.active { color: #fbbf24; }
.db-options-menu {
    display: none; position: absolute; top: calc(100% + 6px); left: 0; z-index: 60;
    background: var(--surface); border: 1px solid var(--border2); border-radius: 10px;
    padding: 6px; width: 240px; box-shadow: 0 8px 24px rgba(0,0,0,.35);
}
.db-options-menu.open { display: block; }
.db-options-item {
    display: flex; align-items: center; gap: 10px; width: 100%;
    background: none; border: none; text-align: left; cursor: pointer;
    padding: 8px 10px; border-radius: 8px; color: var(--text); font-size: 13px; font-family: var(--font);
    transition: background .12s;
}
.db-options-item:hover { background: var(--surface2); }
.db-options-item svg { flex-shrink: 0; color: var(--muted); }
.db-options-divider { border-top: 1px solid var(--border); margin: 6px 2px; }
.db-options-danger { color: var(--danger) !important; }
.db-options-danger svg { color: var(--danger) !important; }
.db-color-flyout {
    display: none; flex-wrap: wrap; gap: 8px;
    padding: 10px 10px 6px;
}
.db-color-flyout.open { display: flex; }
.db-swatch { width: 22px; height: 22px; border-radius: 50%; border: 2px solid transparent; cursor: pointer; padding: 0; box-sizing: border-box; }
.db-swatch.selected { border-color: var(--text); }

/* Widget grid */
.widget-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 16px;
}
.widget-card {
    background: var(--surface); border: 1px solid var(--border);
    border-radius: 14px; padding: 20px 22px; position: relative;
}
.widget-card-title {
    font-size: 14px; font-weight: 600; color: var(--text); margin-bottom: 4px;
}
.widget-card-meta {
    font-size: 11px; color: var(--muted); font-family: var(--mono); margin-bottom: 16px;
}
.widget-delete-btn {
    position: absolute; top: 14px; right: 14px;
    background: none; border: none; color: var(--muted); cursor: pointer;
    padding: 4px; border-radius: 6px; line-height: 1; transition: color .15s, background .15s;
}
.widget-delete-btn:hover { color: var(--danger); background: rgba(248,113,113,.1); }

/* Empty placeholder */
.widget-placeholder {
    background: var(--surface); border: 1px dashed var(--border2);
    border-radius: 14px; padding: 40px 24px;
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    gap: 10px; color: var(--muted); font-size: 13px; text-align: center;
    cursor: pointer; transition: border-color .15s, background .15s;
}
.widget-placeholder:hover { border-color: var(--accent); background: rgba(74,222,128,.03); }

/* Modal */
.modal-overlay {
    display: none; position: fixed; inset: 0;
    background: rgba(0,0,0,.55); z-index: 1000;
    align-items: center; justify-content: center;
}
.modal-overlay.open { display: flex; }
.modal-box {
    background: var(--surface); border: 1px solid var(--border);
    border-radius: 16px; padding: 28px; width: 480px; max-width: 95vw;
}
.modal-title { font-size: 16px; font-weight: 700; color: var(--text); margin-bottom: 20px; }
.form-row { margin-bottom: 14px; }
.form-label { font-size: 11px; color: var(--muted); font-family: var(--mono); text-transform: uppercase; letter-spacing: .06em; margin-bottom: 6px; display: block; }
.form-control {
    width: 100%; background: var(--bg); border: 1px solid var(--border);
    border-radius: 8px; padding: 8px 12px; color: var(--text);
    font-size: 13px; font-family: var(--font); outline: none;
    transition: border-color .15s;
}
.form-control:focus { border-color: var(--accent); }
.form-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
.modal-footer { display: flex; justify-content: flex-end; gap: 10px; margin-top: 22px; }
.btn-cancel {
    background: none; border: 1px solid var(--border2); color: var(--muted);
    border-radius: 8px; padding: 8px 16px; font-size: 13px;
    cursor: pointer; font-family: var(--font);
}
.btn-save {
    background: var(--accent); border: none; color: #0a0f1a;
    border-radius: 8px; padding: 8px 20px; font-size: 13px;
    font-weight: 600; cursor: pointer; font-family: var(--font);
}
.back-btn { display:inline-flex; align-items:center; gap:6px; color:var(--muted); font-size:13px; text-decoration:none; margin-bottom:14px; transition:color .15s; }
.back-btn:hover { color:var(--text); }
</style>

<a href="{{ route('company.insights.index', $slug) }}" class="back-btn">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
    Reporting
</a>

@php
    $DB_COLORS = ['#6b7385', '#f87171', '#fb923c', '#fbbf24', '#a3e635', '#4ade80', '#22d3ee', '#38bdf8', '#2dd4bf', '#60a5fa', '#a78bfa', '#e879f9', '#ec4899', '#f472b6', '#94a3b8'];
@endphp

{{-- Top bar --}}
<div class="db-topbar">
    <div class="db-title-row">
        <div class="db-title-icon" id="dbTitleIcon" style="{{ $dashboard->color ? 'background:'.$dashboard->color.';' : '' }}">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.8"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
        </div>
        <span class="db-title-text">{{ $dashboard->title }}</span>

        <div style="position:relative;">
            <button class="db-chevron-btn" onclick="toggleDashOptionsMenu(event)" title="Dashboard options">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
            </button>
            <div id="dashOptionsMenu" class="db-options-menu">
                <button type="button" class="db-options-item" onclick="closeDashOptionsMenu(); openEditDashDetailsModal();">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    Edit dashboard details
                </button>
                <button type="button" class="db-options-item" onclick="toggleColorFlyout(event)">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="13.5" cy="6.5" r=".5" fill="currentColor"/><circle cx="17.5" cy="10.5" r=".5" fill="currentColor"/><circle cx="8.5" cy="7.5" r=".5" fill="currentColor"/><circle cx="6.5" cy="12.5" r=".5" fill="currentColor"/><path d="M12 2a10 10 0 100 20c1.1 0 2-.9 2-2 0-.5-.2-1-.5-1.3-.3-.4-.5-.8-.5-1.3 0-1.1.9-2 2-2h2.3c1.8 0 3.2-1.4 3.2-3.2C20.5 7.1 16.7 2 12 2z"/></svg>
                    <span style="flex:1;">Set color</span>
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
                </button>
                <div class="db-color-flyout" id="dbColorFlyout">
                    @foreach($DB_COLORS as $c)
                    <button type="button" class="db-swatch {{ $dashboard->color === $c ? 'selected' : '' }}" style="background:{{ $c }};" onclick="pickDashboardColor('{{ $c }}')"></button>
                    @endforeach
                </div>
                <div class="db-options-divider"></div>
                <button type="button" class="db-options-item" onclick="closeDashOptionsMenu(); copyDashboardLink();">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 13a5 5 0 007.5.5l2-2a5 5 0 00-7-7l-1 1"/><path d="M14 11a5 5 0 00-7.5-.5l-2 2a5 5 0 007 7l1-1"/></svg>
                    Copy dashboard link
                </button>
                <button type="button" class="db-options-item" onclick="closeDashOptionsMenu(); duplicateThisDashboard();">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="12" height="12" rx="2"/><path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"/></svg>
                    Duplicate
                </button>
                <div class="db-options-divider"></div>
                <button type="button" class="db-options-item db-options-danger" onclick="closeDashOptionsMenu(); deleteThisDashboard();">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/></svg>
                    Delete dashboard
                </button>
            </div>
        </div>

        <button class="db-star-btn {{ $dashboard->is_favorite ? 'active' : '' }}" id="dbStarBtn" onclick="toggleThisDashboardFavorite()" title="{{ $dashboard->is_favorite ? 'Remove from favorites' : 'Add to favorites' }}">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="{{ $dashboard->is_favorite ? '#fbbf24' : 'none' }}" stroke="{{ $dashboard->is_favorite ? '#fbbf24' : 'currentColor' }}" stroke-width="1.8"><path d="M12 3l2.6 5.6L21 9.3l-4.5 4.2 1.2 6L12 16.8 6.3 19.5l1.2-6L3 9.3l6.4-.7L12 3z"/></svg>
        </button>
    </div>
    <div class="db-actions">
        <div style="position:relative;">
            <button class="btn-add-widget" onclick="toggleAddWidgetMenu(event)">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Add widget
                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="margin-left:2px;"><polyline points="6 9 12 15 18 9"/></svg>
            </button>
            <div id="addWidgetMenu" class="add-widget-menu">
                <button type="button" class="add-widget-menu-item" onclick="closeAddWidgetMenu(); openAddChartModal();">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
                    <div><div class="add-widget-menu-item-title">Template chart</div><div class="add-widget-menu-item-desc">Pick from ready-made reports</div></div>
                </button>
                <button type="button" class="add-widget-menu-item" onclick="closeAddWidgetMenu(); openChartConfig('Custom Chart','bar','assignee');">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><line x1="4" y1="19" x2="4" y2="10"/><line x1="10" y1="19" x2="10" y2="5"/><line x1="16" y1="19" x2="16" y2="13"/></svg>
                    <div><div class="add-widget-menu-item-title">Custom chart</div><div class="add-widget-menu-item-desc">Build your own from scratch</div></div>
                </button>
                <button type="button" class="add-widget-menu-item" onclick="closeAddWidgetMenu(); openTextWidgetModal();">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><line x1="17" y1="10" x2="3" y2="10"/><line x1="21" y1="6" x2="3" y2="6"/><line x1="21" y1="14" x2="3" y2="14"/><line x1="17" y1="18" x2="3" y2="18"/></svg>
                    <div><div class="add-widget-menu-item-title">Text area</div><div class="add-widget-menu-item-desc">Add a free-text note</div></div>
                </button>
            </div>
        </div>
        <button class="btn-share" onclick="openShareModal()">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V7a4 4 0 018 0v4"/></svg>
            Share
        </button>
    </div>
</div>

{{-- Edit dashboard details modal --}}
<div class="modal-overlay" id="editDashDetailsModal">
    <div class="modal-box" style="width:420px;">
        <div class="modal-title">Edit dashboard details</div>
        <div class="form-row">
            <label class="form-label">Title</label>
            <input type="text" id="dbEditTitleInput" class="form-control" maxlength="255" value="{{ $dashboard->title }}">
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-cancel" onclick="closeEditDashDetailsModal()">Cancel</button>
            <button type="button" class="btn-save" id="dbEditTitleSaveBtn" onclick="saveDashDetailsTitle()">Save</button>
        </div>
    </div>
</div>

{{-- Share modal --}}
<div class="modal-overlay" id="shareDashModal">
    <div class="modal-box" style="width:460px;">
        <div class="modal-title">Share dashboard</div>
        <p style="font-size:13px; color:var(--muted); margin:-10px 0 16px;">Anyone at your company with this link can open this dashboard.</p>
        <div class="form-row" style="display:flex; gap:8px; margin-bottom:0;">
            <input type="text" id="dbShareLinkInput" class="form-control" readonly value="{{ route('company.insights.dashboards.show', [$slug, $dashboard->id]) }}">
            <button type="button" class="btn-save" style="flex-shrink:0; white-space:nowrap;" onclick="copyDashboardLink(this)">Copy link</button>
        </div>
        <div class="modal-footer" style="margin-top:20px;">
            <button type="button" class="btn-cancel" onclick="closeShareModal()">Close</button>
        </div>
    </div>
</div>

{{-- Widget grid --}}
<div class="widget-grid" id="widgetGrid">

    {{-- Row 1: Legacy dashboard chart (always visible) + placeholder/first-widget --}}
    {{-- Legacy chart card --}}
    <div class="widget-card">
        <div class="widget-card-title">{{ $dashboard->title }}</div>
        <div class="widget-card-meta">
            {{ ucfirst(str_replace('_',' ',$dashboard->x_axis)) }}
            @if($dashboard->project_filter) · {{ \App\Models\Project::find($dashboard->project_filter)?->name }} @endif
            @if($dashboard->status_filter) · {{ ucfirst(str_replace('_',' ',$dashboard->status_filter)) }} @endif
        </div>

        @if($dashboard->chart_style === 'number')
            @php $legacyTotal = collect($chartData)->sum('value'); @endphp
            <div style="display:flex; flex-direction:column; align-items:center; justify-content:center; padding:30px 0;">
                <div style="font-size:64px; font-weight:700; color:#4ade80; line-height:1;">{{ $legacyTotal }}</div>
            </div>
        @else
            <div style="position:relative; height:260px;">
                <canvas id="legacyChart"></canvas>
            </div>
            <div style="display:flex; align-items:center; justify-content:space-between; margin-top:14px; padding-top:12px; border-top:1px solid var(--border);">
                <span style="font-size:11px; color:var(--muted);">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:middle; margin-right:4px;"><line x1="4" y1="6" x2="20" y2="6"/><line x1="8" y1="12" x2="16" y2="12"/><line x1="11" y1="18" x2="13" y2="18"/></svg>
                    {{ collect([$dashboard->project_filter,$dashboard->status_filter,$dashboard->priority_filter])->filter()->count() }} Filter{{ collect([$dashboard->project_filter,$dashboard->status_filter,$dashboard->priority_filter])->filter()->count() !== 1 ? 's' : '' }}
                    @if($dashboard->project_filter) · {{ \App\Models\Project::find($dashboard->project_filter)?->name }} @endif
                </span>
                <a href="{{ route('company.tasks.index', $slug) }}" style="font-size:12px; color:var(--muted); text-decoration:none; background:var(--surface2); border:1px solid var(--border); border-radius:6px; padding:4px 10px;" onmouseover="this.style.color='var(--text)'" onmouseout="this.style.color='var(--muted)'">See all</a>
            </div>
        @endif

        @if($dashboard->chart_style !== 'number')
        <script>
        document.addEventListener('DOMContentLoaded', function(){
            const cd = @json($chartData);
            if (!cd.length) return;
            renderChart('legacyChart', '{{ $dashboard->chart_style }}', cd);
        });
        </script>
        @endif
    </div>

    {{-- If no widgets yet: show placeholder next to legacy chart --}}
    @if($widgetsData->isEmpty())
    <div class="widget-placeholder" onclick="openAddChartModal()">
        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="opacity:.35;"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
        <div style="font-size:13px; color:var(--text); font-weight:500;">Add text, images, links and more to this dashboard.</div>
    </div>
    @endif

    {{-- Additional widgets --}}
    @foreach($widgetsData as $wd)
    @php
        $w = $wd['widget'];
        $cd = $wd['chartData'];
        $chartId = 'chart_' . $w->id;
        $metaParts = [];
        if ($w->project_filter) $metaParts[] = \App\Models\Project::find($w->project_filter)?->name;
        if ($w->status_filter)  $metaParts[] = ucfirst(str_replace('_',' ',$w->status_filter));
        if ($w->priority_filter) $metaParts[] = ucfirst($w->priority_filter);
        if ($w->date_range)     $metaParts[] = 'Last '.$w->date_range.' days';
        $metaStr = implode(' · ', array_filter($metaParts)) ?: 'All tasks';
    @endphp
    <div class="widget-card" id="wcard_{{ $w->id }}">
        <button class="widget-delete-btn" onclick="deleteWidget({{ $w->id }})" title="Remove widget">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
        <div class="widget-card-title">{{ $w->title }}</div>
        <div class="widget-card-meta">
            @if($w->chart_style === 'text') Note
            @else {{ ucfirst(str_replace('_',' ',$w->x_axis)) }} · {{ $metaStr }}
            @endif
        </div>

        @if($w->chart_style === 'text')
            <div style="white-space:pre-wrap; font-size:13px; line-height:1.7; color:var(--text); min-height:80px; padding-bottom:4px;">{{ $w->content }}</div>
        @elseif($w->chart_style === 'number')
            @php $total = collect($cd)->sum('value'); @endphp
            <div style="display:flex; flex-direction:column; align-items:center; justify-content:center; padding:30px 0;">
                <div style="font-size:64px; font-weight:700; color:#4ade80; line-height:1;">{{ $total }}</div>
                <div style="font-size:12px; color:var(--muted); margin-top:6px;">{{ $w->title }}</div>
            </div>
        @else
            <div style="position:relative; height:260px;">
                <canvas id="{{ $chartId }}"></canvas>
            </div>
            <div style="display:flex; align-items:center; justify-content:space-between; margin-top:14px; padding-top:12px; border-top:1px solid var(--border);">
                <span style="font-size:11px; color:var(--muted);">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:middle; margin-right:4px;"><line x1="4" y1="6" x2="20" y2="6"/><line x1="8" y1="12" x2="16" y2="12"/><line x1="11" y1="18" x2="13" y2="18"/></svg>
                    {{ collect($metaParts)->filter()->count() }} Filter{{ collect($metaParts)->filter()->count() !== 1 ? 's' : '' }} · {{ $metaStr }}
                </span>
                <a href="{{ route('company.tasks.index', $slug) }}" style="font-size:12px; color:var(--muted); text-decoration:none; background:var(--surface2); border:1px solid var(--border); border-radius:6px; padding:4px 10px;" onmouseover="this.style.color='var(--text)'" onmouseout="this.style.color='var(--muted)'">See all</a>
            </div>
            <script>
            document.addEventListener('DOMContentLoaded', function(){
                const cd = @json($cd);
                if (!cd.length) return;
                renderChart('{{ $chartId }}', '{{ $w->chart_style }}', cd);
            });
            </script>
        @endif
    </div>
    @endforeach

    {{-- Trailing placeholder when widgets exist --}}
    @if($widgetsData->isNotEmpty())
    <div class="widget-placeholder" onclick="openAddChartModal()">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="opacity:.35;"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        <div style="font-size:13px;">Add another widget</div>
    </div>
    @endif
</div>

{{-- Add chart gallery + config modal (Template chart / Custom chart) --}}
@include('company.insights.partials.chart-modal', ['submitUrl' => route('company.insights.widgets.store', [$slug, $dashboard->id])])

{{-- Text area widget modal --}}
<div class="modal-overlay" id="textWidgetModal">
    <div class="modal-box">
        <div class="modal-title">Add text</div>
        <div class="form-row">
            <label class="form-label">Title</label>
            <input type="text" id="textWidgetTitle" class="form-control" placeholder="e.g. Notes">
        </div>
        <div class="form-row">
            <label class="form-label">Content</label>
            <textarea id="textWidgetContent" class="form-control" rows="6" placeholder="Write a note…" style="resize:vertical;"></textarea>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-cancel" onclick="closeTextWidgetModal()">Cancel</button>
            <button type="button" class="btn-save" id="saveTextWidgetBtn" onclick="saveTextWidget()">Add widget</button>
        </div>
    </div>
</div>

<script>
const STORE_URL = '{{ route('company.insights.widgets.store', [$slug, $dashboard->id]) }}';
const CSRF = document.querySelector('meta[name=csrf-token]')?.content || '{{ csrf_token() }}';

function widgetDestroyUrl(widgetId) {
    return '{{ url("/{$slug}/admin/insights/dashboards/{$dashboard->id}/widgets") }}/' + widgetId;
}

// Add-widget dropdown menu
function toggleAddWidgetMenu(e) {
    e.stopPropagation();
    document.getElementById('addWidgetMenu').classList.toggle('open');
}
function closeAddWidgetMenu() {
    document.getElementById('addWidgetMenu').classList.remove('open');
}
document.addEventListener('click', function(e) {
    const menu = document.getElementById('addWidgetMenu');
    if (menu.classList.contains('open') && !menu.contains(e.target) && !e.target.closest('.btn-add-widget')) {
        closeAddWidgetMenu();
    }
});

// Text area widget modal
function openTextWidgetModal() {
    document.getElementById('textWidgetModal').classList.add('open');
}
function closeTextWidgetModal() {
    document.getElementById('textWidgetModal').classList.remove('open');
    document.getElementById('textWidgetTitle').value = '';
    document.getElementById('textWidgetContent').value = '';
}
document.getElementById('textWidgetModal').addEventListener('click', function(e) {
    if (e.target === this) closeTextWidgetModal();
});

function saveTextWidget() {
    const content = document.getElementById('textWidgetContent').value.trim();
    if (!content) { document.getElementById('textWidgetContent').focus(); return; }

    const btn = document.getElementById('saveTextWidgetBtn');
    btn.disabled = true; btn.textContent = 'Saving…';

    fetch(STORE_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
        body: JSON.stringify({
            title: document.getElementById('textWidgetTitle').value.trim() || 'Note',
            chart_style: 'text',
            content: content,
        })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) { window.location.reload(); return; }
        btn.disabled = false; btn.textContent = 'Add widget';
    })
    .catch(() => {
        btn.disabled = false; btn.textContent = 'Add widget';
    });
}

async function deleteWidget(widgetId) {
    if (!confirm('Remove this widget?')) return;
    const res = await fetch(widgetDestroyUrl(widgetId), { method:'DELETE', headers:{'X-CSRF-TOKEN':CSRF,'Accept':'application/json'} });
    if (res.ok) {
        document.getElementById('wcard_' + widgetId)?.remove();
        if (!document.querySelector('.widget-card')) window.location.reload();
    }
}

// Dashboard title dropdown: Edit details / Set color / Copy link / Duplicate / Delete
const DASH_ID = {{ $dashboard->id }};
function dashApiUrl(suffix) {
    return '{{ url("/{$slug}/admin/insights/dashboards/{$dashboard->id}") }}' + (suffix || '');
}

function toggleDashOptionsMenu(e) {
    e.stopPropagation();
    document.getElementById('dashOptionsMenu').classList.toggle('open');
    document.getElementById('dbColorFlyout').classList.remove('open');
}
function closeDashOptionsMenu() {
    document.getElementById('dashOptionsMenu').classList.remove('open');
    document.getElementById('dbColorFlyout').classList.remove('open');
}
document.addEventListener('click', function (e) {
    const menu = document.getElementById('dashOptionsMenu');
    if (menu.classList.contains('open') && !menu.contains(e.target) && !e.target.closest('.db-chevron-btn')) {
        closeDashOptionsMenu();
    }
});

function toggleColorFlyout(e) {
    e.stopPropagation();
    document.getElementById('dbColorFlyout').classList.toggle('open');
}

function pickDashboardColor(color) {
    fetch(dashApiUrl(), {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
        body: JSON.stringify({ color: color })
    })
    .then(r => r.json())
    .then(data => { if (data.success) window.location.reload(); });
}

function openEditDashDetailsModal() {
    document.getElementById('editDashDetailsModal').classList.add('open');
}
function closeEditDashDetailsModal() {
    document.getElementById('editDashDetailsModal').classList.remove('open');
}
document.getElementById('editDashDetailsModal').addEventListener('click', function (e) {
    if (e.target === this) closeEditDashDetailsModal();
});
function saveDashDetailsTitle() {
    const title = document.getElementById('dbEditTitleInput').value.trim();
    if (!title) return;
    const btn = document.getElementById('dbEditTitleSaveBtn');
    btn.disabled = true; btn.textContent = 'Saving…';
    fetch(dashApiUrl(), {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
        body: JSON.stringify({ title: title })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) { window.location.reload(); return; }
        btn.disabled = false; btn.textContent = 'Save';
    })
    .catch(() => { btn.disabled = false; btn.textContent = 'Save'; });
}

function duplicateThisDashboard() {
    fetch(dashApiUrl('/duplicate'), {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => { if (data.url) window.location.href = data.url; });
}

function deleteThisDashboard() {
    if (!confirm('Delete this dashboard? This cannot be undone.')) return;
    fetch(dashApiUrl(), {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
    }).then(() => { window.location.href = '{{ route('company.insights.index', $slug) }}'; });
}

function toggleThisDashboardFavorite() {
    fetch(dashApiUrl('/favorite'), {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => { if (data.success) window.location.reload(); });
}

// Share modal
function openShareModal() {
    document.getElementById('shareDashModal').classList.add('open');
}
function closeShareModal() {
    document.getElementById('shareDashModal').classList.remove('open');
}
document.getElementById('shareDashModal').addEventListener('click', function (e) {
    if (e.target === this) closeShareModal();
});
function copyDashboardLink(btn) {
    const link = document.getElementById('dbShareLinkInput')?.value
        || '{{ route('company.insights.dashboards.show', [$slug, $dashboard->id]) }}';
    navigator.clipboard.writeText(link).then(() => {
        if (btn) {
            const original = btn.textContent;
            btn.textContent = 'Copied!';
            setTimeout(() => { btn.textContent = original; }, 1500);
        }
    }).catch(() => {
        const input = document.getElementById('dbShareLinkInput');
        if (input) { input.select(); document.execCommand('copy'); }
    });
}
</script>

</x-company-layout>
