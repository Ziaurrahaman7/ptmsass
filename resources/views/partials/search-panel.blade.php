{{-- Asana-style global search. Expects $searchUrl and $isAdmin. --}}
@php
    $searchUrl = $searchUrl ?? '';
    $isAdmin = $isAdmin ?? false;
@endphp

<div id="searchModal" class="sp-overlay" hidden>
    <div class="sp-panel" role="dialog" aria-modal="true" aria-label="Search">
        <div class="sp-search-wrap">
            <div class="sp-input-pill">
                <input id="searchInput" type="text" placeholder="Search" autocomplete="off" aria-label="Search">
                <button type="button" class="sp-filter-btn" id="searchFilterBtn" title="More filters" aria-label="More filters">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="4" y1="7" x2="20" y2="7"/><line x1="7" y1="12" x2="17" y2="12"/><line x1="10" y1="17" x2="14" y2="17"/></svg>
                </button>
            </div>
        </div>

        <div class="sp-chips" id="searchChips">
            <button type="button" class="sp-chip" data-type="tasks">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/></svg>
                Tasks
            </button>
            <button type="button" class="sp-chip" data-type="projects">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/></svg>
                Projects
            </button>
            <button type="button" class="sp-chip" data-type="people">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                People
            </button>
            @if($isAdmin)
            <button type="button" class="sp-chip" data-type="portfolios">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 19a2 2 0 01-2 2H4a2 2 0 01-2-2V5a2 2 0 012-2h5l2 3h9a2 2 0 012 2z"/></svg>
                Portfolios
            </button>
            <button type="button" class="sp-chip" data-type="goals">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3l9 18H3z"/></svg>
                Goals
            </button>
            @endif
            <div class="sp-more-wrap">
                <button type="button" class="sp-chip" id="searchMoreBtn" data-type="more">More</button>
                <div class="sp-more-menu" id="searchMoreMenu" hidden>
                    <button type="button" data-type="comments">Comments</button>
                    @if($isAdmin)
                    <button type="button" data-type="teams">Teams</button>
                    @endif
                </div>
            </div>
        </div>

        <div id="searchResults" class="sp-body"></div>
    </div>
</div>

<style>
    .sp-overlay { position:fixed; inset:0; background:rgba(0,0,0,0.62); z-index:520; display:flex; align-items:flex-start; justify-content:center; padding:56px 16px 24px; }
    .sp-overlay[hidden] { display:none !important; }
    .sp-panel { width:100%; max-width:560px; max-height:calc(100vh - 80px); background:#1c1f24; border:1px solid rgba(255,255,255,0.1); border-radius:16px; box-shadow:0 24px 64px rgba(0,0,0,0.5); display:flex; flex-direction:column; overflow:hidden; }
    .sp-search-wrap { padding:16px 16px 10px; }
    .sp-input-pill { display:flex; align-items:center; border:1px solid rgba(255,255,255,0.38); border-radius:999px; padding:0 6px 0 16px; background:#16181c; }
    .sp-input-pill:focus-within { border-color:rgba(255,255,255,0.7); }
    .sp-input-pill input { flex:1; background:transparent; border:none; outline:none; color:#f4f5f7; font-family:var(--font); font-size:15px; padding:11px 8px; }
    .sp-input-pill input::placeholder { color:#9aa3b2; }
    .sp-filter-btn { background:none; border:none; color:#c5cad3; cursor:pointer; width:36px; height:36px; border-radius:50%; display:flex; align-items:center; justify-content:center; }
    .sp-filter-btn:hover { background:rgba(255,255,255,0.08); color:#fff; }
    .sp-chips { display:flex; flex-wrap:wrap; gap:8px; padding:0 16px 12px; }
    .sp-chip { display:inline-flex; align-items:center; gap:6px; padding:6px 12px; border-radius:999px; border:1px solid rgba(255,255,255,0.28); background:transparent; color:#e8eaf0; font-family:var(--font); font-size:13px; cursor:pointer; }
    .sp-chip:hover { background:rgba(255,255,255,0.06); }
    .sp-chip.active { background:rgba(255,255,255,0.12); border-color:rgba(255,255,255,0.55); }
    .sp-more-wrap { position:relative; }
    .sp-more-menu { position:absolute; top:calc(100% + 6px); left:0; min-width:140px; background:#22262c; border:1px solid rgba(255,255,255,0.12); border-radius:10px; padding:6px; z-index:2; box-shadow:0 8px 24px rgba(0,0,0,0.4); }
    .sp-more-menu button { display:block; width:100%; text-align:left; background:none; border:none; color:#e8eaf0; font-size:13px; padding:8px 10px; border-radius:6px; cursor:pointer; font-family:var(--font); }
    .sp-more-menu button:hover { background:rgba(255,255,255,0.08); }
    .sp-body { flex:1; overflow-y:auto; padding:4px 8px 16px; }
    .sp-label { font-size:12px; color:#9aa3b2; padding:10px 10px 6px; }
    .sp-row { display:flex; align-items:center; gap:12px; padding:9px 10px; border-radius:10px; text-decoration:none; color:inherit; cursor:pointer; }
    .sp-row:hover, .sp-row.sp-active { background:rgba(255,255,255,0.07); }
    .sp-ico { width:22px; height:22px; border-radius:6px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
    .sp-ico svg { display:block; }
    .sp-ico-person { width:26px; height:26px; border-radius:50%; background:#3d3a1f; color:#f5d76e; font-size:11px; font-weight:600; }
    .sp-main { flex:1; min-width:0; }
    .sp-title { font-size:14px; color:#f4f5f7; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
    .sp-sub { font-size:12px; color:#8b93a3; margin-top:1px; display:flex; align-items:center; gap:6px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
    .sp-dot { width:7px; height:7px; border-radius:50%; flex-shrink:0; }
    .sp-archived { display:inline-flex; align-items:center; gap:4px; color:#8b93a3; }
    .sp-avatars { display:flex; align-items:center; flex-shrink:0; }
    .sp-av { width:22px; height:22px; border-radius:50%; background:#3b3f48; color:#d7dbe3; font-size:9px; font-weight:600; display:flex; align-items:center; justify-content:center; border:2px solid #1c1f24; margin-left:-6px; }
    .sp-av:first-child { margin-left:0; }
    .sp-menu-btn { opacity:0; background:none; border:none; color:#9aa3b2; cursor:pointer; padding:4px; border-radius:6px; flex-shrink:0; }
    .sp-row:hover .sp-menu-btn, .sp-row.sp-active .sp-menu-btn { opacity:1; }
    .sp-menu-btn:hover { background:rgba(255,255,255,0.08); color:#fff; }
    .sp-saved { padding:8px 8px 4px; }
    .sp-saved-chips { display:flex; flex-wrap:wrap; gap:8px; }
    .sp-empty { padding:36px 16px; text-align:center; color:#8b93a3; font-size:13px; }
    .sp-seeall { background:none; border:none; color:#9aa3b2; font-size:12px; cursor:pointer; padding:6px 10px; font-family:var(--font); }
    .sp-seeall:hover { color:#fff; }
    .sp-hl { background:rgba(245,215,110,0.22); color:inherit; border-radius:2px; padding:0 1px; }
    .sp-row-menu { position:absolute; right:18px; margin-top:28px; min-width:150px; background:#22262c; border:1px solid rgba(255,255,255,0.12); border-radius:10px; padding:6px; z-index:3; box-shadow:0 8px 24px rgba(0,0,0,0.4); }
    .sp-row-menu button { display:block; width:100%; text-align:left; background:none; border:none; color:#e8eaf0; font-size:13px; padding:8px 10px; border-radius:6px; cursor:pointer; font-family:var(--font); }
    .sp-row-menu button:hover { background:rgba(255,255,255,0.08); }
</style>

<script>
(function () {
    const searchUrl = @json($searchUrl);
    const recentKey = 'ptm-search-recents-{{ auth()->user()->company_id ?? 0 }}-{{ auth()->id() }}';
    const overlay = document.getElementById('searchModal');
    const input = document.getElementById('searchInput');
    const body = document.getElementById('searchResults');
    const moreBtn = document.getElementById('searchMoreBtn');
    const moreMenu = document.getElementById('searchMoreMenu');
    const filterBtn = document.getElementById('searchFilterBtn');

    let timer = null;
    let type = 'all';
    let preset = null;
    let rows = [];
    let active = -1;
    let lastData = null;

    function escapeHtml(s) {
        return String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
    }
    function highlight(text, q) {
        const t = escapeHtml(text);
        if (!q || q.length < 2) return t;
        const re = new RegExp('(' + q.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + ')', 'ig');
        return t.replace(re, '<mark class="sp-hl">$1</mark>');
    }
    function loadLocalRecents() {
        try { return JSON.parse(localStorage.getItem(recentKey) || '[]'); } catch (e) { return []; }
    }
    function saveRecent(item) {
        const list = loadLocalRecents().filter(r => !(r.type === item.type && String(r.id) === String(item.id)));
        list.unshift({ type: item.type, id: item.id, title: item.title, subtitle: item.subtitle, url: item.url, initial: item.initial, color: item.color, archived: item.archived, avatars: item.avatars || [], status: item.status });
        localStorage.setItem(recentKey, JSON.stringify(list.slice(0, 12)));
    }
    function removeRecent(item) {
        const list = loadLocalRecents().filter(r => !(r.type === item.type && String(r.id) === String(item.id)));
        localStorage.setItem(recentKey, JSON.stringify(list));
    }

    function safeColor(c) {
        return /^#[0-9a-fA-F]{3,8}$/.test(c || '') ? c : '#f472b6';
    }
    function iconFor(item) {
        const color = safeColor(item.color);
        if (item.type === 'task') {
            return `<div class="sp-ico" style="color:#c5cad3;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M8 12l3 3 5-6"/></svg>
            </div>`;
        }
        if (item.type === 'project') {
            return `<div class="sp-ico" style="background:${color}; color:#fff;">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/></svg>
            </div>`;
        }
        if (item.type === 'person') {
            return `<div class="sp-ico sp-ico-person">${escapeHtml(item.initial || '?')}</div>`;
        }
        if (item.type === 'portfolio') {
            return `<div class="sp-ico" style="background:#7c3aed; color:#fff;">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 19a2 2 0 01-2 2H4a2 2 0 01-2-2V5a2 2 0 012-2h5l2 3h9a2 2 0 012 2z"/></svg>
            </div>`;
        }
        if (item.type === 'goal') {
            return `<div class="sp-ico" style="color:#fbbf24;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3l9 18H3z"/></svg>
            </div>`;
        }
        if (item.type === 'comment') {
            return `<div class="sp-ico" style="color:#22d3ee;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>
            </div>`;
        }
        if (item.type === 'team') {
            return `<div class="sp-ico" style="color:#4ade80;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>
            </div>`;
        }
        return `<div class="sp-ico"></div>`;
    }

    function avatarsHtml(avatars) {
        if (!avatars || !avatars.length) return '';
        return `<div class="sp-avatars">${avatars.map(a => `<div class="sp-av" title="${escapeHtml(a.name || '')}">${escapeHtml(a.initial || '?')}</div>`).join('')}</div>`;
    }

    function statusDot(item) {
        if (item.type !== 'task' || !item.status) return '';
        const c = { done:'#4ade80', in_progress:'#22d3ee', in_review:'#a78bfa', todo:'#6b7385' }[item.status] || '#6b7385';
        return `<span class="sp-dot" style="background:${c}"></span>`;
    }

    function subtitleHtml(item) {
        if (item.archived) {
            return `<div class="sp-sub"><span class="sp-archived">
                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/><path d="M10 11v6M14 11v6"/></svg>
                Archived</span></div>`;
        }
        const extra = item.subtitle ? escapeHtml(item.subtitle) : '';
        if (!extra && item.type !== 'task') return '';
        return `<div class="sp-sub">${statusDot(item)}${extra}</div>`;
    }

    function rowHtml(item, q) {
        const payload = encodeURIComponent(JSON.stringify({ type: item.type, id: item.id, title: item.title, subtitle: item.subtitle, url: item.url, initial: item.initial, color: item.color, archived: item.archived, avatars: item.avatars || [], status: item.status }));
        return `<div class="sp-row" data-url="${escapeHtml(item.url)}" data-item="${payload}">
            ${iconFor(item)}
            <div class="sp-main">
                <div class="sp-title">${highlight(item.title, q)}</div>
                ${subtitleHtml(item)}
            </div>
            ${avatarsHtml(item.avatars)}
            <button type="button" class="sp-menu-btn" data-menu aria-label="More">${'⋮'}</button>
        </div>`;
    }

    function savedHtml() {
        return `<div class="sp-saved">
            <div class="sp-label">Saved searches</div>
            <div class="sp-saved-chips">
                <button type="button" class="sp-chip" data-preset="created">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/></svg>
                    Tasks I've created
                </button>
                <button type="button" class="sp-chip" data-preset="assigned">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                    Tasks I've assigned to others
                </button>
                <button type="button" class="sp-chip" data-preset="completed">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M8 12l3 3 5-6"/></svg>
                    Recently completed tasks
                </button>
                <button type="button" class="sp-chip" data-preset="deleted">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/></svg>
                    Deleted
                </button>
            </div>
        </div>`;
    }

    function mergeRecents(server) {
        const local = loadLocalRecents();
        const seen = new Set();
        const out = [];
        [...local, ...(server || [])].forEach(item => {
            const key = (item.type || '') + ':' + item.id;
            if (seen.has(key) || !item.url) return;
            seen.add(key);
            out.push(item);
        });
        return out.slice(0, 12);
    }

    function collectRows() {
        rows = Array.from(body.querySelectorAll('.sp-row'));
        active = rows.length ? 0 : -1;
        rows.forEach((r, i) => r.classList.toggle('sp-active', i === active));
    }
    function setActive(i) {
        if (!rows.length) return;
        active = (i + rows.length) % rows.length;
        rows.forEach((r, idx) => r.classList.toggle('sp-active', idx === active));
        rows[active].scrollIntoView({ block: 'nearest' });
    }

    function renderIdle(data) {
        const recents = mergeRecents(data.recent || []);
        let html = `<div class="sp-label">Recents</div>`;
        if (!recents.length) {
            html += `<div class="sp-empty">No recent items yet. Search to get started.</div>`;
        } else {
            html += recents.map(item => rowHtml(item, '')).join('');
        }
        html += savedHtml();
        body.innerHTML = html;
        collectRows();
    }

    function renderResults(data, q) {
        const groups = [
            ['tasks', 'Tasks'],
            ['projects', 'Projects'],
            ['people', 'People'],
            ['portfolios', 'Portfolios'],
            ['goals', 'Goals'],
            ['comments', 'Comments'],
            ['teams', 'Teams'],
        ];
        let html = '';
        const labels = { created: "Tasks I've created", assigned: "Tasks I've assigned to others", completed: 'Recently completed tasks', deleted: 'Deleted' };
        if (data.mode === 'preset' && data.preset) {
            html += `<div class="sp-label">${escapeHtml(labels[data.preset] || 'Results')}</div>`;
        }
        if (data.message) {
            html += `<div class="sp-empty">${escapeHtml(data.message)}</div>`;
        } else if (!data.total) {
            html += `<div class="sp-empty">No results${q ? ' for "' + escapeHtml(q) + '"' : ''}</div>`;
        } else {
            groups.forEach(([key, label]) => {
                const items = (data.results && data.results[key]) || [];
                if (!items.length) return;
                if (data.mode !== 'preset') html += `<div class="sp-label">${label}</div>`;
                html += items.map(item => rowHtml(item, q)).join('');
                if (data.mode === 'results' && type === 'all' && items.length >= 6 && key !== 'comments' && key !== 'teams') {
                    html += `<button type="button" class="sp-seeall" data-seeall="${key}">See all ${label.toLowerCase()}</button>`;
                }
            });
        }
        html += savedHtml();
        body.innerHTML = html;
        collectRows();
        document.querySelectorAll('.sp-chip[data-preset]').forEach(btn => {
            btn.classList.toggle('active', btn.dataset.preset === preset);
        });
    }

    function fetchSearch() {
        const q = input.value.trim();
        const params = new URLSearchParams();
        if (q) params.set('q', q);
        if (type && type !== 'all') params.set('type', type);
        if (preset) params.set('preset', preset);
        body.innerHTML = `<div class="sp-empty">Searching…</div>`;
        fetch(searchUrl + '?' + params.toString(), { headers: { 'Accept': 'application/json' } })
            .then(r => r.json())
            .then(data => {
                lastData = data;
                if (data.mode === 'idle' && !preset) renderIdle(data);
                else renderResults(data, q);
            })
            .catch(() => { body.innerHTML = `<div class="sp-empty">Search failed. Try again.</div>`; });
    }

    function debounceSearch() {
        clearTimeout(timer);
        preset = null;
        document.querySelectorAll('.sp-chip[data-preset]').forEach(b => b.classList.remove('active'));
        if (input.value.trim().length < 2 && type === 'all') {
            timer = setTimeout(fetchSearch, 80);
            return;
        }
        timer = setTimeout(fetchSearch, 280);
    }

    function setType(next) {
        type = next || 'all';
        preset = null;
        document.querySelectorAll('#searchChips .sp-chip[data-type]').forEach(btn => {
            const extra = btn.id === 'searchMoreBtn' && ['comments', 'teams', 'more'].includes(type);
            btn.classList.toggle('active', btn.dataset.type === type || extra);
        });
        moreMenu.hidden = true;
        fetchSearch();
    }

    function setPreset(next) {
        preset = next;
        type = 'all';
        document.querySelectorAll('#searchChips .sp-chip[data-type]').forEach(btn => btn.classList.remove('active'));
        fetchSearch();
    }

    function goTo(url, item) {
        if (item) saveRecent(item);
        closeSearch();
        const hashIdx = url.indexOf('#');
        const path = hashIdx === -1 ? url : url.slice(0, hashIdx);
        const hash = hashIdx === -1 ? '' : url.slice(hashIdx);
        if (path === location.pathname || path === location.pathname + '/') {
            if (hash) {
                location.hash = hash;
                const id = hash.replace('#task-', '');
                if (id && typeof window.openPanel === 'function') window.openPanel(id);
            }
            return;
        }
        location.href = url;
    }

    function parseItem(el) {
        try { return JSON.parse(decodeURIComponent(el.dataset.item || '%7B%7D')); } catch (e) { return {}; }
    }

    function openSearch() {
        overlay.hidden = false;
        overlay.style.display = 'flex';
        input.focus();
        if (!input.value.trim() && type === 'all' && !preset) fetchSearch();
        else fetchSearch();
    }
    function closeSearch() {
        overlay.hidden = true;
        overlay.style.display = 'none';
        moreMenu.hidden = true;
        document.querySelectorAll('.sp-row-menu').forEach(m => m.remove());
    }

    window.openSearch = openSearch;
    window.closeSearch = closeSearch;

    input.addEventListener('input', debounceSearch);

    document.getElementById('searchChips').addEventListener('click', function (e) {
        const chip = e.target.closest('.sp-chip[data-type]');
        if (!chip || chip.id === 'searchMoreBtn') return;
        setType(chip.dataset.type === type ? 'all' : chip.dataset.type);
    });

    moreBtn.addEventListener('click', function (e) {
        e.stopPropagation();
        moreMenu.hidden = !moreMenu.hidden;
    });
    moreMenu.addEventListener('click', function (e) {
        const btn = e.target.closest('[data-type]');
        if (!btn) return;
        setType(btn.dataset.type);
    });
    filterBtn.addEventListener('click', function (e) {
        e.stopPropagation();
        moreMenu.hidden = !moreMenu.hidden;
    });

    body.addEventListener('click', function (e) {
        const see = e.target.closest('[data-seeall]');
        if (see) { setType(see.dataset.seeall); return; }
        const presetBtn = e.target.closest('[data-preset]');
        if (presetBtn) { setPreset(presetBtn.dataset.preset); return; }
        const menuBtn = e.target.closest('[data-menu]');
        if (menuBtn) {
            e.preventDefault();
            e.stopPropagation();
            document.querySelectorAll('.sp-row-menu').forEach(m => m.remove());
            const row = menuBtn.closest('.sp-row');
            const item = parseItem(row);
            const menu = document.createElement('div');
            menu.className = 'sp-row-menu';
            menu.innerHTML = `<button type="button" data-act="copy">Copy link</button>
                <button type="button" data-act="tab">Open in new tab</button>
                <button type="button" data-act="forget">Remove from recents</button>`;
            row.style.position = 'relative';
            row.appendChild(menu);
            menu.addEventListener('click', ev => {
                ev.stopPropagation();
                const act = ev.target.closest('[data-act]')?.dataset.act;
                if (act === 'copy') navigator.clipboard?.writeText(item.url || '');
                if (act === 'tab' && item.url) window.open(item.url, '_blank');
                if (act === 'forget') { removeRecent(item); if (!input.value.trim() && !preset) fetchSearch(); }
                menu.remove();
            });
            return;
        }
        const row = e.target.closest('.sp-row');
        if (row) goTo(row.dataset.url, parseItem(row));
    });

    overlay.addEventListener('click', function (e) {
        if (e.target === overlay) closeSearch();
    });

    document.addEventListener('click', function (e) {
        if (!e.target.closest('.sp-more-wrap') && !e.target.closest('#searchFilterBtn')) moreMenu.hidden = true;
        if (!e.target.closest('[data-menu]') && !e.target.closest('.sp-row-menu')) {
            document.querySelectorAll('.sp-row-menu').forEach(m => m.remove());
        }
    });

    document.addEventListener('keydown', function (e) {
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            overlay.hidden ? openSearch() : closeSearch();
            return;
        }
        if (overlay.hidden) return;
        if (e.key === 'Escape') { e.preventDefault(); closeSearch(); }
        if (e.key === 'ArrowDown') { e.preventDefault(); setActive(active + 1); }
        if (e.key === 'ArrowUp') { e.preventDefault(); setActive(active - 1); }
        if (e.key === 'Enter' && active >= 0 && rows[active]) {
            e.preventDefault();
            goTo(rows[active].dataset.url, parseItem(rows[active]));
        }
    });

    function tryOpenTaskFromHash() {
        const m = location.hash.match(/^#task-(\d+)$/);
        if (!m) return;
        const open = () => typeof window.openPanel === 'function' && window.openPanel(m[1]);
        if (!open()) setTimeout(open, 350);
    }
    window.addEventListener('load', tryOpenTaskFromHash);
    window.addEventListener('hashchange', tryOpenTaskFromHash);
})();
</script>
