{{--
    Asana-style "Edit field" card for the company's Priority options.
    Include with: @include('company.priorities._editor', ['closeAction' => 'someJsExpression()'])
    $closeAction is a JS expression run when X / Done is clicked (defaults to nothing, i.e. no-op).
--}}
<style>
.pf-card { background:var(--surface); border:1px solid var(--border2); border-radius:14px; width:100%; max-width:640px; overflow:hidden; }
.pf-head { display:flex; align-items:flex-start; justify-content:space-between; padding:20px 24px 16px; border-bottom:1px solid var(--border); }
.pf-title { font-size:19px; font-weight:700; color:var(--text); }
.pf-sub { font-size:12px; color:var(--muted); margin-top:4px; }
.pf-close { background:none; border:none; color:var(--muted); cursor:pointer; font-size:18px; padding:4px; line-height:1; }
.pf-close:hover { color:var(--text); }
.pf-body { padding:20px 24px; }
.pf-row-2 { display:grid; grid-template-columns:1fr 1fr; gap:16px; }
.pf-label { font-size:12px; font-weight:600; color:var(--text); margin-bottom:8px; }
.pf-input { width:100%; background:var(--surface2); border:1px solid var(--border); border-radius:8px; padding:9px 12px; color:var(--muted); font-size:14px; font-family:var(--font); box-sizing:border-box; }
.pf-type-badge { display:inline-flex; align-items:center; gap:6px; background:var(--surface2); border:1px solid var(--border); border-radius:8px; padding:9px 12px; font-size:14px; color:var(--text); }
.pf-options { display:flex; flex-direction:column; gap:2px; margin-top:20px; }
.pf-option-row { display:flex; align-items:center; gap:10px; padding:8px 4px; border-radius:8px; }
.pf-option-row:hover { background:var(--surface2); }
.pf-option-row:hover .pf-delete { opacity:1; }
.pf-drag { color:var(--muted); cursor:grab; flex-shrink:0; display:flex; }
.pf-drag:active { cursor:grabbing; }
.pf-swatch { width:26px; height:26px; border-radius:50%; border:none; cursor:pointer; padding:0; flex-shrink:0; display:flex; align-items:center; justify-content:center; position:relative; }
.pf-swatch::after { content:''; position:absolute; right:-2px; bottom:-2px; width:10px; height:10px; border-radius:50%; background:var(--surface2); border:1px solid var(--border); }
.pf-swatch-pop { display:none; position:absolute; z-index:100; top:calc(100% + 6px); left:0; background:var(--surface); border:1px solid var(--border2); border-radius:10px; padding:10px; box-shadow:0 8px 24px rgba(0,0,0,.35); grid-template-columns:repeat(6,1fr); gap:8px; width:190px; }
.pf-swatch-pop.open { display:grid; }
.pf-swatch-opt { width:22px; height:22px; border-radius:50%; border:2px solid transparent; cursor:pointer; padding:0; }
.pf-swatch-opt.selected { border-color:var(--text); }
.pf-option-name { flex:1; background:none; border:1px solid transparent; border-radius:6px; color:var(--text); font-size:14px; padding:6px 8px; font-family:var(--font); min-width:0; }
.pf-option-name:hover { background:var(--surface2); }
.pf-option-name:focus { outline:none; background:var(--bg); border-color:var(--accent2); }
.pf-default-chip { font-size:10px; color:var(--accent2); border:1px solid rgba(34,211,238,0.3); background:rgba(34,211,238,0.08); padding:2px 9px; border-radius:20px; white-space:nowrap; flex-shrink:0; }
.pf-set-default { font-size:10px; color:var(--muted); background:none; border:1px solid var(--border2); padding:2px 9px; border-radius:20px; cursor:pointer; white-space:nowrap; font-family:var(--font); flex-shrink:0; }
.pf-set-default:hover { color:var(--text); border-color:var(--muted); }
.pf-delete { background:none; border:none; color:var(--muted); cursor:pointer; padding:5px; border-radius:6px; flex-shrink:0; display:flex; opacity:0; transition:opacity .12s; }
.pf-delete:hover { color:var(--danger); background:rgba(248,113,113,.1); }
.pf-add-option { display:flex; align-items:center; gap:8px; background:none; border:none; color:var(--muted); font-size:13px; font-family:var(--font); cursor:pointer; padding:9px 4px; margin-top:4px; }
.pf-add-option:hover { color:var(--text); }
.pf-add-row { display:flex; align-items:center; gap:8px; padding:6px 4px; }
.pf-add-row input { flex:1; background:var(--surface2); border:1px solid var(--border); border-radius:8px; padding:8px 12px; color:var(--text); font-size:13px; font-family:var(--font); }
.pf-add-row button { background:#4573d2; color:#fff; border:none; border-radius:8px; padding:8px 16px; font-size:13px; font-weight:600; cursor:pointer; font-family:var(--font); }
.pf-add-row button:hover { background:#3a62bb; }
.pf-footer { display:flex; justify-content:flex-end; padding:16px 24px; border-top:1px solid var(--border); }
.pf-done-btn { background:#4573d2; color:#fff; border:none; border-radius:8px; padding:9px 22px; font-size:13px; font-weight:600; cursor:pointer; font-family:var(--font); transition:background .15s; }
.pf-done-btn:hover { background:#3a62bb; }
</style>

<div class="pf-card">
    <div class="pf-head">
        <div>
            <div class="pf-title">Edit field</div>
            <div class="pf-sub">Track the priority of each task</div>
        </div>
        <button class="pf-close" onclick="{{ $closeAction ?? '' }}">✕</button>
    </div>
    <div class="pf-body">
        <div class="pf-row-2">
            <div>
                <div class="pf-label">Field title</div>
                <input type="text" class="pf-input" value="Priority" readonly>
            </div>
            <div>
                <div class="pf-label">Field type</div>
                <div class="pf-type-badge">Single-select</div>
            </div>
        </div>

        <div class="pf-label" style="margin-top:20px;">Options</div>
        <div class="pf-options" id="pfOptionsList">
            @foreach($companyPriorities as $p)
            <div class="pf-option-row" data-id="{{ $p->id }}">
                <span class="pf-drag" title="Drag to reorder">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="6" r="1"/><circle cx="9" cy="12" r="1"/><circle cx="9" cy="18" r="1"/><circle cx="15" cy="6" r="1"/><circle cx="15" cy="12" r="1"/><circle cx="15" cy="18" r="1"/></svg>
                </span>
                <div style="position:relative;">
                    <button class="pf-swatch" style="background:{{ $p->color }};" onclick="pfToggleSwatch(event, {{ $p->id }})"></button>
                    <div class="pf-swatch-pop" id="pfSwatchPop-{{ $p->id }}">
                        @foreach(['#6b7385','#94a3b8','#38bdf8','#22d3ee','#2dd4bf','#4ade80','#a3e635','#fbbf24','#fb923c','#f87171','#ec4899','#a78bfa'] as $c)
                        <button type="button" class="pf-swatch-opt {{ $p->color === $c ? 'selected' : '' }}" style="background:{{ $c }};" onclick="pfPickColor({{ $p->id }}, '{{ $c }}')"></button>
                        @endforeach
                    </div>
                </div>
                <input type="text" class="pf-option-name" value="{{ $p->name }}" onblur="pfRename({{ $p->id }}, this.value)" onkeydown="if(event.key==='Enter') this.blur();">
                @if($p->is_default)
                <span class="pf-default-chip">Default</span>
                @else
                <button class="pf-set-default" onclick="pfSetDefault({{ $p->id }})">Set default</button>
                @endif
                <button class="pf-delete" title="Delete option" onclick="pfDelete({{ $p->id }}, '{{ $p->name }}')">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>
            @endforeach
        </div>

        <button class="pf-add-option" onclick="pfShowAddRow()">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Add option
        </button>
        <div class="pf-add-row" id="pfAddRow" style="display:none;">
            <input type="text" id="pfNewOptionName" placeholder="Option name" onkeydown="if(event.key==='Enter') pfSubmitAdd();">
            <button onclick="pfSubmitAdd()">Add</button>
        </div>
    </div>
    <div class="pf-footer">
        <button class="pf-done-btn" onclick="{{ $closeAction ?? '' }}">Done</button>
    </div>
</div>

<script>
(function () {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    const baseUrl = '{{ url("/{$slug}/admin/priorities") }}';

    if (window.pfSortableInit) window.pfSortableInit.destroy?.();
    window.pfSortableInit = new Sortable(document.getElementById('pfOptionsList'), {
        handle: '.pf-drag',
        ghostClass: 'pr-sortable-ghost',
        onEnd: function () {
            const order = Array.from(document.querySelectorAll('#pfOptionsList .pf-option-row')).map(r => r.dataset.id);
            fetch(baseUrl + '/reorder', {
                method: 'PATCH',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                body: JSON.stringify({ order: order })
            });
        }
    });

    document.addEventListener('click', function (e) {
        document.querySelectorAll('.pf-swatch-pop.open').forEach(pop => {
            if (!pop.contains(e.target) && !e.target.closest('.pf-swatch')) pop.classList.remove('open');
        });
    });

    window.pfToggleSwatch = function (e, id) {
        e.stopPropagation();
        const pop = document.getElementById('pfSwatchPop-' + id);
        document.querySelectorAll('.pf-swatch-pop.open').forEach(p => { if (p !== pop) p.classList.remove('open'); });
        pop.classList.toggle('open');
    };

    window.pfPickColor = function (id, color) {
        fetch(`${baseUrl}/${id}`, {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify({ color: color })
        }).then(r => r.json()).then(data => { if (data.success) location.reload(); });
    };

    window.pfRename = function (id, name) {
        name = name.trim();
        if (!name) return;
        fetch(`${baseUrl}/${id}`, {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify({ name: name })
        });
    };

    window.pfSetDefault = function (id) {
        fetch(`${baseUrl}/${id}`, {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify({ is_default: true })
        }).then(r => r.json()).then(data => { if (data.success) location.reload(); });
    };

    window.pfDelete = function (id, name) {
        if (!confirm(`Delete "${name}"? Any tasks using it will move to the default priority.`)) return;
        fetch(`${baseUrl}/${id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(data => { if (data.success) location.reload(); })
        .catch(() => alert('Could not delete — you must keep at least one priority option.'));
    };

    window.pfShowAddRow = function () {
        document.getElementById('pfAddRow').style.display = 'flex';
        document.getElementById('pfNewOptionName').focus();
    };

    window.pfSubmitAdd = function () {
        const input = document.getElementById('pfNewOptionName');
        const name = input.value.trim();
        if (!name) return;
        fetch(baseUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify({ name: name, color: '#94a3b8' })
        })
        .then(r => r.json())
        .then(data => { if (data.success) location.reload(); });
    };
})();
</script>
