<x-company-layout :title="'Reporting'">

<style>
.rep-header-tab {
    display: inline-block;
    font-size: 14px;
    font-weight: 600;
    color: var(--text);
    padding-bottom: 10px;
    border-bottom: 2px solid var(--text);
    margin-right: 24px;
    cursor: pointer;
}
.rep-create-btn {
    display: inline-flex; align-items: center; gap: 6px;
    background: #4573d2; color: #fff;
    border: none; border-radius: 8px;
    padding: 8px 16px; font-size: 13px; font-weight: 600;
    cursor: pointer; font-family: var(--font);
    transition: background .15s;
}
.rep-create-btn:hover { background: #3a62bb; }

.rep-section-title {
    display: flex; align-items: center; gap: 8px;
    font-size: 13px; font-weight: 600; color: var(--text);
    margin-bottom: 2px;
}

/* List view */
.rep-row {
    display: flex; align-items: center; gap: 16px;
    padding: 14px 8px;
    border-bottom: 1px solid var(--border);
    cursor: pointer; transition: background .12s;
    border-radius: 6px; text-decoration: none;
}
.rep-row:hover { background: var(--surface2); }
.rep-row:last-child { border-bottom: none; }

/* Grid view */
.rep-grid-wrap { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; }
.rep-grid-card {
    background: var(--surface2); border: 1px solid var(--border);
    border-radius: 12px; padding: 18px 16px;
    text-decoration: none; display: flex; flex-direction: column; gap: 12px;
    transition: border-color .15s, background .15s; cursor: pointer;
}
.rep-grid-card:hover { border-color: var(--border2); background: #1e2330; }
.rep-grid-create {
    background: transparent; border: 2px dashed var(--border2);
    border-radius: 12px; padding: 18px 16px;
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    gap: 8px; cursor: pointer; min-height: 110px;
    transition: border-color .15s;
}
.rep-grid-create:hover { border-color: var(--muted); }

.rep-icon {
    width: 42px; height: 42px; border-radius: 10px;
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.rep-icon-sm { width: 36px; height: 36px; border-radius: 9px; }
.rep-icon-pink   { background: linear-gradient(135deg, #c026d3, #9333ea); }
.rep-icon-purple { background: linear-gradient(135deg, #7c3aed, #4f46e5); }
.rep-icon-indigo { background: linear-gradient(135deg, #4f46e5, #2563eb); }

.rep-create-row {
    display: flex; align-items: center; gap: 16px;
    padding: 14px 8px; border-bottom: 1px solid var(--border); cursor: pointer;
}
.rep-create-icon {
    width: 42px; height: 42px; border-radius: 10px;
    border: 2px dashed var(--border2);
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0; color: var(--muted);
}
.rep-avatar {
    width: 28px; height: 28px; border-radius: 50%;
    background: #fbbf24; color: #1a1e25;
    font-size: 11px; font-weight: 700;
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}

/* Toggle buttons */
.view-toggle-btn {
    background: none; border: none; cursor: pointer; padding: 5px;
    border-radius: 6px; color: var(--muted); transition: all .15s; display:flex;
}
.view-toggle-btn:hover { background: var(--surface2); color: var(--text); }
.view-toggle-btn.active { background: var(--surface2); color: var(--text); }
</style>

{{-- Page Header --}}
<div style="margin-bottom: 0;">
    <h1 style="font-size: 22px; font-weight: 700; color: var(--text); margin: 0 0 16px;">Reporting</h1>

    {{-- Tab --}}
    <div style="border-bottom: 1px solid var(--border); margin-bottom: 20px;">
        <span class="rep-header-tab">Dashboards</span>
    </div>

    {{-- Create button --}}
    <div style="margin-bottom: 32px;">
        <button class="rep-create-btn" onclick="openAddChartModal()">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Create
        </button>
    </div>
</div>

{{-- Dashboard List --}}
<div style="max-width: 860px; margin: 0 auto;">

    {{-- Recents section --}}
    <div style="margin-bottom: 8px;">
        <div class="rep-section-title" style="margin-bottom: 12px;">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color:var(--muted)"><polyline points="6 9 12 15 18 9"/></svg>
            Recents
            <div style="flex:1;"></div>
            {{-- View toggle --}}
            <button class="view-toggle-btn active" id="btnList" onclick="setView('list')" title="List view">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
            </button>
            <button class="view-toggle-btn" id="btnGrid" onclick="setView('grid')" title="Grid view">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
            </button>
        </div>

        @php
            $sidebarProjects = \App\Models\Project::where('company_id', auth()->user()->company_id)->orderBy('name')->take(3)->get();
            $iconColors = ['rep-icon-purple', 'rep-icon-indigo', 'rep-icon-pink'];
            $dashboards = [
                ['type' => 'my-impact',       'title' => 'My impact',       'desc' => 'See the impact of your work',          'icon' => 'rep-icon-pink'],
                ['type' => 'my-organization', 'title' => 'My organization', 'desc' => 'Metrics across your organization',      'icon' => 'rep-icon-pink'],
            ];
            foreach ($sidebarProjects as $i => $proj) {
                $dashboards[] = ['type' => 'project-'.$proj->id, 'title' => $proj->name, 'desc' => 'Project dashboard', 'icon' => $iconColors[$i % 3]];
            }
        @endphp

        {{-- LIST VIEW --}}
        <div id="viewList">
            <div class="rep-create-row" onclick="openAddChartModal()" style="cursor:pointer;">
                <div class="rep-create-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                </div>
                <span style="font-size: 14px; color: var(--muted);">Create dashboard</span>
            </div>
            {{-- Built-in dashboards --}}
            @foreach($dashboards as $db)
            <a href="{{ route('company.insights.show', [$slug, $db['type']]) }}" class="rep-row">
                <div class="rep-icon {{ $db['icon'] }}">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.8"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                </div>
                <div style="flex: 1; min-width: 0;">
                    <div style="font-size: 14px; font-weight: 600; color: var(--text);">{{ $db['title'] }}</div>
                    <div style="font-size: 12px; color: var(--muted); margin-top: 2px;">{{ $db['desc'] }}</div>
                </div>
                <div class="rep-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}</div>
            </a>
            @endforeach
            {{-- User-created dashboards --}}
            <div id="userDashboardsList">
            @foreach($userDashboards as $ud)
            <a href="{{ route('company.insights.dashboards.show', [$slug, $ud->id]) }}" class="rep-row">
                <div class="rep-icon rep-icon-indigo">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.8"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
                </div>
                <div style="flex: 1; min-width: 0;">
                    <div style="font-size: 14px; font-weight: 600; color: var(--text);">{{ $ud->title }}</div>
                    <div style="font-size: 12px; color: var(--muted); margin-top: 2px;">Custom dashboard · {{ $ud->chart_style }} · {{ $ud->x_axis }}</div>
                </div>
                <div class="rep-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}</div>
            </a>
            @endforeach
            </div>
        </div>

        {{-- GRID VIEW --}}
        <div id="viewGrid" style="display:none;">
            <div class="rep-grid-wrap">
                <div class="rep-grid-create" onclick="openAddChartModal()">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color:var(--muted)"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    <span style="font-size: 13px; color: var(--muted);">Create dashboard</span>
                </div>
                @foreach($dashboards as $db)
                <a href="{{ route('company.insights.show', [$slug, $db['type']]) }}" class="rep-grid-card">
                    <div style="display:flex; align-items:center; justify-content:space-between;">
                        <div class="rep-icon rep-icon-sm {{ $db['icon'] }}">
                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.8"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                        </div>
                        <div class="rep-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}</div>
                    </div>
                    <div>
                        <div style="font-size: 13px; font-weight: 600; color: var(--text); margin-bottom: 4px;">{{ $db['title'] }}</div>
                        <div style="font-size: 12px; color: var(--muted);">{{ $db['desc'] }}</div>
                    </div>
                </a>
                @endforeach
            </div>
        </div>

    </div>
</div>

<script>
function setView(v) {
    document.getElementById('viewList').style.display = v === 'list' ? 'block' : 'none';
    document.getElementById('viewGrid').style.display = v === 'grid' ? 'block' : 'none';
    document.getElementById('btnList').classList.toggle('active', v === 'list');
    document.getElementById('btnGrid').classList.toggle('active', v === 'grid');
    localStorage.setItem('insightView', v);
}
const saved = localStorage.getItem('insightView');
if (saved) setView(saved);

</script>


@include('company.insights.partials.chart-modal', ['submitUrl' => route('company.insights.dashboards.store', $slug)])


</x-company-layout>
