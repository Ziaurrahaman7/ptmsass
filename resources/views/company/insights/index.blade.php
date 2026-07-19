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

/* Row "..." menu */
.rep-row-menu-wrap { position: relative; flex-shrink: 0; }
.rep-row-menu-btn {
    opacity: 0; background: var(--surface2); border: 1px solid var(--border2); color: var(--muted);
    cursor: pointer; border-radius: 6px; padding: 5px; display: flex; transition: opacity .12s, color .12s, background .12s;
}
.rep-row:hover .rep-row-menu-btn, .rep-row-menu-btn.menu-open { opacity: 1; }
.rep-row-menu-btn:hover { color: var(--text); background: var(--border); }
.rep-row-menu {
    display: none; position: absolute; top: calc(100% + 4px); right: 0; z-index: 200;
    background: var(--surface); border: 1px solid var(--border2); border-radius: 10px;
    padding: 6px; width: 220px; box-shadow: 0 8px 24px rgba(0,0,0,.35); text-align: left;
}
.rep-row-menu.open { display: block; }
.rep-row-menu button {
    display: flex; align-items: center; gap: 10px; width: 100%;
    background: none; border: none; text-align: left; cursor: pointer;
    padding: 8px 10px; border-radius: 8px; color: var(--text); font-size: 13px; font-family: var(--font);
    transition: background .12s;
}
.rep-row-menu button:hover { background: var(--surface2); }
.rep-row-menu button svg { flex-shrink: 0; color: var(--muted); }
.rep-row-menu-divider { border-top: 1px solid var(--border); margin: 6px 2px; }
.rep-row-menu-danger { color: var(--danger) !important; }
.rep-row-menu-danger svg { color: var(--danger) !important; }
.rep-fav-star { flex-shrink: 0; }

/* Modals (shared shape) */
.modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.55); z-index:1000; align-items:center; justify-content:center; }
.modal-overlay.open { display:flex; }
.modal-box { background: var(--surface); border: 1px solid var(--border); border-radius: 16px; padding: 26px; width: 420px; max-width: 95vw; }
.modal-title { font-size: 16px; font-weight: 700; color: var(--text); margin-bottom: 18px; }
.form-row { margin-bottom: 14px; }
.form-label { font-size: 11px; color: var(--muted); font-family: var(--mono); text-transform: uppercase; letter-spacing: .06em; margin-bottom: 8px; display: block; }
.form-control {
    width: 100%; background: var(--bg); border: 1px solid var(--border); border-radius: 8px;
    padding: 8px 12px; color: var(--text); font-size: 13px; font-family: var(--font); outline: none;
    transition: border-color .15s; box-sizing: border-box;
}
.form-control:focus { border-color: var(--accent); }
.modal-footer { display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px; }
.btn-cancel { background: none; border: 1px solid var(--border2); color: var(--muted); border-radius: 8px; padding: 8px 16px; font-size: 13px; cursor: pointer; font-family: var(--font); }
.btn-save { background: var(--accent); border: none; color: #0a0f1a; border-radius: 8px; padding: 8px 20px; font-size: 13px; font-weight: 600; cursor: pointer; font-family: var(--font); }

/* Color & icon picker */
.swatch-grid { display: flex; flex-wrap: wrap; gap: 10px; }
.swatch { width: 28px; height: 28px; border-radius: 50%; border: 2px solid transparent; cursor: pointer; padding: 0; box-sizing: border-box; }
.swatch.selected { border-color: var(--text); }
.icon-grid { display: flex; flex-wrap: wrap; gap: 10px; }
.icon-tile { width: 36px; height: 36px; border-radius: 8px; background: var(--surface2); border: 2px solid transparent; color: var(--muted); display: flex; align-items: center; justify-content: center; cursor: pointer; }
.icon-tile.selected { border-color: var(--accent); color: var(--text); }
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
            $ICONS = [
                'chart'  => '<polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>',
                'grid'   => '<rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/>',
                'target' => '<circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="5"/><circle cx="12" cy="12" r="1"/>',
                'flag'   => '<path d="M4 3v18"/><path d="M4 4h11l-1.5 4L15 12H4"/>',
                'star'   => '<path d="M12 3l2.6 5.6L21 9.3l-4.5 4.2 1.2 6L12 16.8 6.3 19.5l1.2-6L3 9.3l6.4-.7L12 3z"/>',
                'folder' => '<path d="M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V7z"/>',
            ];
            $COLORS = ['#ec4899', '#7c3aed', '#4f46e5', '#2563eb', '#0891b2', '#16a34a', '#d97706', '#dc2626'];

            $sidebarProjects = \App\Models\Project::where('company_id', auth()->user()->company_id)->orderBy('name')->take(3)->get();
            $iconColors = ['rep-icon-purple', 'rep-icon-indigo', 'rep-icon-pink'];
            $dashboards = [
                ['type' => 'my-impact',       'title' => 'My impact',       'desc' => 'See the impact of your work',          'icon' => 'rep-icon-pink'],
                ['type' => 'my-organization', 'title' => 'My organization', 'desc' => 'Metrics across your organization',      'icon' => 'rep-icon-pink'],
            ];
            foreach ($sidebarProjects as $i => $proj) {
                $dashboards[] = ['type' => 'project-'.$proj->id, 'title' => $proj->name, 'desc' => 'Project dashboard', 'icon' => $iconColors[$i % 3]];
            }

            // Merge per-user rename/color/icon/favorite/hidden state, then drop anything hidden
            $dashboards = collect($dashboards)->map(function ($d) use ($builtinPrefs) {
                $p = $builtinPrefs->get($d['type']);
                $d['display_title'] = $p?->title_override ?: $d['title'];
                $d['custom_color']  = $p?->color;
                $d['custom_icon']   = $p?->icon;
                $d['is_favorite']   = (bool) ($p?->is_favorite);
                $d['is_hidden']     = (bool) ($p?->is_hidden);
                return $d;
            })->reject(fn ($d) => $d['is_hidden'])->values()->all();
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
            @php
                $dbIconKey = $db['custom_icon'] && isset($ICONS[$db['custom_icon']]) ? $db['custom_icon'] : null;
                $dbMenuId = 'builtin-' . \Illuminate\Support\Str::slug($db['type']);
            @endphp
            <a href="{{ route('company.insights.show', [$slug, $db['type']]) }}" class="rep-row">
                @if($db['custom_color'] || $dbIconKey)
                <div class="rep-icon" style="background:{{ $db['custom_color'] ?: '#c026d3' }};">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.8">{!! $ICONS[$dbIconKey ?: 'chart'] !!}</svg>
                </div>
                @else
                <div class="rep-icon {{ $db['icon'] }}">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.8"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                </div>
                @endif
                <div style="flex: 1; min-width: 0;">
                    <div style="font-size: 14px; font-weight: 600; color: var(--text); display:flex; align-items:center; gap:6px;">
                        {{ $db['display_title'] }}
                        @if($db['is_favorite'])
                        <svg class="rep-fav-star" width="12" height="12" viewBox="0 0 24 24" fill="#fbbf24" stroke="#fbbf24" stroke-width="1"><path d="M12 3l2.6 5.6L21 9.3l-4.5 4.2 1.2 6L12 16.8 6.3 19.5l1.2-6L3 9.3l6.4-.7L12 3z"/></svg>
                        @endif
                    </div>
                    <div style="font-size: 12px; color: var(--muted); margin-top: 2px;">{{ $db['desc'] }}</div>
                </div>
                <div class="rep-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}</div>
                <div class="rep-row-menu-wrap">
                    <button type="button" class="rep-row-menu-btn" onclick="toggleDashMenu(event, '{{ $dbMenuId }}')" title="More options">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="5" cy="12" r="1.5"/><circle cx="12" cy="12" r="1.5"/><circle cx="19" cy="12" r="1.5"/></svg>
                    </button>
                    <div class="rep-row-menu" id="dashMenu-{{ $dbMenuId }}">
                        <button type="button" onclick="event.preventDefault(); event.stopPropagation(); openEditPrefModal('{{ $db['type'] }}', {{ Js::from($db['display_title']) }})">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                            Edit dashboard details
                        </button>
                        <button type="button" onclick="event.preventDefault(); event.stopPropagation(); openColorIconPrefModal('{{ $db['type'] }}', '{{ $db['custom_color'] ?: '#c026d3' }}', '{{ $dbIconKey ?: 'chart' }}')">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="13.5" cy="6.5" r=".5" fill="currentColor"/><circle cx="17.5" cy="10.5" r=".5" fill="currentColor"/><circle cx="8.5" cy="7.5" r=".5" fill="currentColor"/><circle cx="6.5" cy="12.5" r=".5" fill="currentColor"/><path d="M12 2a10 10 0 100 20c1.1 0 2-.9 2-2 0-.5-.2-1-.5-1.3-.3-.4-.5-.8-.5-1.3 0-1.1.9-2 2-2h2.3c1.8 0 3.2-1.4 3.2-3.2C20.5 7.1 16.7 2 12 2z"/></svg>
                            Set color & icon
                        </button>
                        <button type="button" onclick="event.preventDefault(); event.stopPropagation(); toggleFavoritePref('{{ $db['type'] }}', {{ Js::from($db['display_title']) }})">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3l2.6 5.6L21 9.3l-4.5 4.2 1.2 6L12 16.8 6.3 19.5l1.2-6L3 9.3l6.4-.7L12 3z"/></svg>
                            {{ $db['is_favorite'] ? 'Remove from favorites' : 'Add to favorites' }}
                        </button>
                        <button type="button" onclick="event.preventDefault(); event.stopPropagation(); duplicateBuiltinDash('{{ $db['type'] }}', {{ Js::from($db['display_title']) }})">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="12" height="12" rx="2"/><path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"/></svg>
                            Duplicate
                        </button>
                        <div class="rep-row-menu-divider"></div>
                        <button type="button" class="rep-row-menu-danger" onclick="event.preventDefault(); event.stopPropagation(); hideBuiltinDash('{{ $db['type'] }}')">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/></svg>
                            Remove from Recents
                        </button>
                    </div>
                </div>
            </a>
            @endforeach
            {{-- User-created dashboards --}}
            <div id="userDashboardsList">
            @foreach($userDashboards as $ud)
            @php
                $udColor = $ud->color ?: '#4f46e5';
                $udIconKey = $ud->icon && isset($ICONS[$ud->icon]) ? $ud->icon : 'chart';
            @endphp
            <a href="{{ route('company.insights.dashboards.show', [$slug, $ud->id]) }}" class="rep-row">
                <div class="rep-icon" style="background:{{ $udColor }};">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.8">{!! $ICONS[$udIconKey] !!}</svg>
                </div>
                <div style="flex: 1; min-width: 0;">
                    <div style="font-size: 14px; font-weight: 600; color: var(--text); display:flex; align-items:center; gap:6px;">
                        {{ $ud->title }}
                        @if($ud->is_favorite)
                        <svg class="rep-fav-star" width="12" height="12" viewBox="0 0 24 24" fill="#fbbf24" stroke="#fbbf24" stroke-width="1"><path d="M12 3l2.6 5.6L21 9.3l-4.5 4.2 1.2 6L12 16.8 6.3 19.5l1.2-6L3 9.3l6.4-.7L12 3z"/></svg>
                        @endif
                    </div>
                    <div style="font-size: 12px; color: var(--muted); margin-top: 2px;">Custom dashboard · {{ $ud->chart_style }} · {{ $ud->x_axis }}</div>
                </div>
                <div class="rep-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}</div>
                <div class="rep-row-menu-wrap">
                    <button type="button" class="rep-row-menu-btn" onclick="toggleDashMenu(event, {{ $ud->id }})" title="More options">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="5" cy="12" r="1.5"/><circle cx="12" cy="12" r="1.5"/><circle cx="19" cy="12" r="1.5"/></svg>
                    </button>
                    <div class="rep-row-menu" id="dashMenu-{{ $ud->id }}">
                        <button type="button" onclick="event.preventDefault(); event.stopPropagation(); openEditDashModal({{ $ud->id }}, {{ Js::from($ud->title) }})">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                            Edit dashboard details
                        </button>
                        <button type="button" onclick="event.preventDefault(); event.stopPropagation(); openColorIconModal({{ $ud->id }}, '{{ $udColor }}', '{{ $udIconKey }}')">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="13.5" cy="6.5" r=".5" fill="currentColor"/><circle cx="17.5" cy="10.5" r=".5" fill="currentColor"/><circle cx="8.5" cy="7.5" r=".5" fill="currentColor"/><circle cx="6.5" cy="12.5" r=".5" fill="currentColor"/><path d="M12 2a10 10 0 100 20c1.1 0 2-.9 2-2 0-.5-.2-1-.5-1.3-.3-.4-.5-.8-.5-1.3 0-1.1.9-2 2-2h2.3c1.8 0 3.2-1.4 3.2-3.2C20.5 7.1 16.7 2 12 2z"/></svg>
                            Set color & icon
                        </button>
                        <button type="button" onclick="event.preventDefault(); event.stopPropagation(); toggleDashFavorite({{ $ud->id }})">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3l2.6 5.6L21 9.3l-4.5 4.2 1.2 6L12 16.8 6.3 19.5l1.2-6L3 9.3l6.4-.7L12 3z"/></svg>
                            {{ $ud->is_favorite ? 'Remove from favorites' : 'Add to favorites' }}
                        </button>
                        <button type="button" onclick="event.preventDefault(); event.stopPropagation(); duplicateDash({{ $ud->id }})">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="12" height="12" rx="2"/><path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"/></svg>
                            Duplicate
                        </button>
                        <div class="rep-row-menu-divider"></div>
                        <button type="button" class="rep-row-menu-danger" onclick="event.preventDefault(); event.stopPropagation(); deleteDash({{ $ud->id }})">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/></svg>
                            Delete dashboard
                        </button>
                    </div>
                </div>
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

{{-- Edit dashboard details modal --}}
<div class="modal-overlay" id="editDashModal">
    <div class="modal-box">
        <div class="modal-title">Edit dashboard details</div>
        <div class="form-row">
            <label class="form-label">Title</label>
            <input type="text" id="editDashTitle" class="form-control" maxlength="255">
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-cancel" onclick="closeEditDashModal()">Cancel</button>
            <button type="button" class="btn-save" id="editDashSaveBtn" onclick="saveDashTitle()">Save</button>
        </div>
    </div>
</div>

{{-- Set color & icon modal --}}
<div class="modal-overlay" id="colorIconModal">
    <div class="modal-box">
        <div class="modal-title">Set color & icon</div>
        <div class="form-row">
            <label class="form-label">Color</label>
            <div class="swatch-grid" id="colorSwatchGrid">
                @foreach($COLORS as $c)
                <button type="button" class="swatch" data-color="{{ $c }}" style="background:{{ $c }};" onclick="selectDashColor('{{ $c }}')"></button>
                @endforeach
            </div>
        </div>
        <div class="form-row">
            <label class="form-label">Icon</label>
            <div class="icon-grid" id="iconPickGrid">
                @foreach($ICONS as $key => $svg)
                <button type="button" class="icon-tile" data-icon="{{ $key }}" onclick="selectDashIcon('{{ $key }}')">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">{!! $svg !!}</svg>
                </button>
                @endforeach
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-cancel" onclick="closeColorIconModal()">Cancel</button>
            <button type="button" class="btn-save" id="colorIconSaveBtn" onclick="saveColorIcon()">Save</button>
        </div>
    </div>
</div>

<script>
const DASH_CSRF = document.querySelector('meta[name="csrf-token"]').content;
function dashUrl(id, suffix) {
    return '{{ url("/{$slug}/admin/insights/dashboards") }}/' + id + (suffix || '');
}

// "..." dropdown menu
function toggleDashMenu(event, id) {
    event.preventDefault();
    event.stopPropagation();
    const menu = document.getElementById('dashMenu-' + id);
    const btn = event.currentTarget;
    const wasOpen = menu.classList.contains('open');
    document.querySelectorAll('.rep-row-menu.open').forEach(m => m.classList.remove('open'));
    document.querySelectorAll('.rep-row-menu-btn.menu-open').forEach(b => b.classList.remove('menu-open'));
    if (!wasOpen) {
        menu.classList.add('open');
        btn.classList.add('menu-open');
    }
}
document.addEventListener('click', function () {
    document.querySelectorAll('.rep-row-menu.open').forEach(m => m.classList.remove('open'));
    document.querySelectorAll('.rep-row-menu-btn.menu-open').forEach(b => b.classList.remove('menu-open'));
});

function prefsUrl(suffix) {
    return '{{ url("/{$slug}/admin/insights/dashboard-prefs") }}' + (suffix || '');
}

// Edit dashboard details — shared modal for real dashboards (id) and built-ins (type string)
let editingDashId = null, editingPrefType = null;
function openEditDashModal(id, title) {
    editingDashId = id; editingPrefType = null;
    document.getElementById('editDashTitle').value = title;
    document.getElementById('editDashModal').classList.add('open');
}
function openEditPrefModal(type, title) {
    editingDashId = null; editingPrefType = type;
    document.getElementById('editDashTitle').value = title;
    document.getElementById('editDashModal').classList.add('open');
}
function closeEditDashModal() {
    document.getElementById('editDashModal').classList.remove('open');
}
document.getElementById('editDashModal').addEventListener('click', function (e) {
    if (e.target === this) closeEditDashModal();
});
function saveDashTitle() {
    const title = document.getElementById('editDashTitle').value.trim();
    if (!title) return;
    const btn = document.getElementById('editDashSaveBtn');
    btn.disabled = true; btn.textContent = 'Saving…';

    const url = editingPrefType ? prefsUrl() : dashUrl(editingDashId);
    const body = editingPrefType ? { type: editingPrefType, title: title } : { title: title };

    fetch(url, {
        method: editingPrefType ? 'POST' : 'PATCH',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': DASH_CSRF },
        body: JSON.stringify(body)
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) { window.location.reload(); return; }
        btn.disabled = false; btn.textContent = 'Save';
    })
    .catch(() => { btn.disabled = false; btn.textContent = 'Save'; });
}

// Set color & icon — shared modal for real dashboards (id) and built-ins (type string)
let editingColorId = null, editingColorType = null, pickedColor = null, pickedIcon = null;
function openColorIconModal(id, color, icon) {
    editingColorId = id; editingColorType = null; pickedColor = color; pickedIcon = icon;
    document.querySelectorAll('#colorSwatchGrid .swatch').forEach(s => s.classList.toggle('selected', s.dataset.color === color));
    document.querySelectorAll('#iconPickGrid .icon-tile').forEach(t => t.classList.toggle('selected', t.dataset.icon === icon));
    document.getElementById('colorIconModal').classList.add('open');
}
function openColorIconPrefModal(type, color, icon) {
    editingColorId = null; editingColorType = type; pickedColor = color; pickedIcon = icon;
    document.querySelectorAll('#colorSwatchGrid .swatch').forEach(s => s.classList.toggle('selected', s.dataset.color === color));
    document.querySelectorAll('#iconPickGrid .icon-tile').forEach(t => t.classList.toggle('selected', t.dataset.icon === icon));
    document.getElementById('colorIconModal').classList.add('open');
}
function closeColorIconModal() {
    document.getElementById('colorIconModal').classList.remove('open');
}
document.getElementById('colorIconModal').addEventListener('click', function (e) {
    if (e.target === this) closeColorIconModal();
});
function selectDashColor(c) {
    pickedColor = c;
    document.querySelectorAll('#colorSwatchGrid .swatch').forEach(s => s.classList.toggle('selected', s.dataset.color === c));
}
function selectDashIcon(k) {
    pickedIcon = k;
    document.querySelectorAll('#iconPickGrid .icon-tile').forEach(t => t.classList.toggle('selected', t.dataset.icon === k));
}
function saveColorIcon() {
    const btn = document.getElementById('colorIconSaveBtn');
    btn.disabled = true; btn.textContent = 'Saving…';

    const url = editingColorType ? prefsUrl() : dashUrl(editingColorId);
    const body = editingColorType
        ? { type: editingColorType, color: pickedColor, icon: pickedIcon }
        : { color: pickedColor, icon: pickedIcon };

    fetch(url, {
        method: editingColorType ? 'POST' : 'PATCH',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': DASH_CSRF },
        body: JSON.stringify(body)
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) { window.location.reload(); return; }
        btn.disabled = false; btn.textContent = 'Save';
    })
    .catch(() => { btn.disabled = false; btn.textContent = 'Save'; });
}

// Favorite / duplicate / delete — real dashboards
function toggleDashFavorite(id) {
    fetch(dashUrl(id, '/favorite'), {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': DASH_CSRF, 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => { if (data.success) window.location.reload(); });
}

function duplicateDash(id) {
    fetch(dashUrl(id, '/duplicate'), {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': DASH_CSRF, 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => { if (data.url) window.location.href = data.url; });
}

function deleteDash(id) {
    if (!confirm('Delete this dashboard? This cannot be undone.')) return;
    fetch(dashUrl(id), {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': DASH_CSRF, 'Accept': 'application/json' }
    }).then(() => window.location.reload());
}

// Favorite / duplicate / remove — built-in (My impact, My organization, per-project) dashboards
function toggleFavoritePref(type, title) {
    fetch(prefsUrl('/favorite'), {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': DASH_CSRF },
        body: JSON.stringify({ type: type, title: title })
    })
    .then(r => r.json())
    .then(data => { if (data.success) window.location.reload(); });
}

function duplicateBuiltinDash(type, title) {
    fetch(prefsUrl('/duplicate'), {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': DASH_CSRF },
        body: JSON.stringify({ type: type, title: title })
    })
    .then(r => r.json())
    .then(data => { if (data.url) window.location.href = data.url; });
}

function hideBuiltinDash(type) {
    if (!confirm('Remove this from your Recents list?')) return;
    fetch(prefsUrl('/hide'), {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': DASH_CSRF },
        body: JSON.stringify({ type: type })
    })
    .then(r => r.json())
    .then(data => { if (data.success) window.location.reload(); });
}
</script>

@include('company.insights.partials.chart-modal', ['submitUrl' => route('company.insights.dashboards.store', $slug)])


</x-company-layout>
