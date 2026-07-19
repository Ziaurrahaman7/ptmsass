<x-company-layout :title="'Portfolios'">

<style>
.pf-create-btn {
    display: inline-flex; align-items: center; gap: 6px;
    background: #4573d2; color: #fff;
    border: none; border-radius: 8px;
    padding: 8px 16px; font-size: 13px; font-weight: 600;
    cursor: pointer; font-family: var(--font);
    transition: background .15s;
}
.pf-create-btn:hover { background: #3a62bb; }

.pf-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; }
.pf-card {
    background: var(--surface); border: 1px solid var(--border); border-radius: 14px;
    padding: 18px 18px 16px; text-decoration: none; display: flex; flex-direction: column; gap: 14px;
    transition: border-color .15s, background .15s; cursor: pointer;
}
.pf-card:hover { border-color: var(--border2); }
.pf-icon {
    width: 40px; height: 40px; border-radius: 10px;
    background: linear-gradient(135deg, #f59e0b, #ef4444);
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.pf-title { font-size: 14px; font-weight: 600; color: var(--text); margin-bottom: 3px; }
.pf-meta { font-size: 12px; color: var(--muted); }
.pf-create-card {
    background: transparent; border: 2px dashed var(--border2); border-radius: 14px;
    padding: 18px; min-height: 128px;
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    gap: 8px; cursor: pointer; transition: border-color .15s; color: var(--muted);
}
.pf-create-card:hover { border-color: var(--muted); }
.pf-empty {
    text-align: center; padding: 60px 20px; color: var(--muted); font-size: 13px;
}

/* Create modal */
.modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.55); z-index:1000; align-items:center; justify-content:center; }
.modal-overlay.open { display:flex; }
.modal-box { background: var(--surface); border: 1px solid var(--border); border-radius: 16px; padding: 28px; width: 440px; max-width: 95vw; }
.modal-title { font-size: 16px; font-weight: 700; color: var(--text); margin-bottom: 20px; }
.form-row { margin-bottom: 14px; }
.form-label { font-size: 11px; color: var(--muted); font-family: var(--mono); text-transform: uppercase; letter-spacing: .06em; margin-bottom: 6px; display: block; }
.form-control {
    width: 100%; background: var(--bg); border: 1px solid var(--border); border-radius: 8px;
    padding: 8px 12px; color: var(--text); font-size: 13px; font-family: var(--font); outline: none;
    transition: border-color .15s; box-sizing: border-box;
}
.form-control:focus { border-color: var(--accent); }
.modal-footer { display: flex; justify-content: flex-end; gap: 10px; margin-top: 22px; }
.btn-cancel { background: none; border: 1px solid var(--border2); color: var(--muted); border-radius: 8px; padding: 8px 16px; font-size: 13px; cursor: pointer; font-family: var(--font); }
.btn-save { background: var(--accent); border: none; color: #0a0f1a; border-radius: 8px; padding: 8px 20px; font-size: 13px; font-weight: 600; cursor: pointer; font-family: var(--font); }
</style>

<div style="margin-bottom: 24px;">
    <h1 style="font-size: 22px; font-weight: 700; color: var(--text); margin: 0 0 16px;">Portfolios</h1>
    <button class="pf-create-btn" onclick="document.getElementById('createPortfolioModal').classList.add('open')">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Create portfolio
    </button>
</div>

@if($portfolios->isEmpty())
    <div class="pf-grid">
        <div class="pf-create-card" onclick="document.getElementById('createPortfolioModal').classList.add('open')">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V7z"/></svg>
            <span style="font-size: 13px;">Create your first portfolio</span>
        </div>
    </div>
@else
    <div class="pf-grid">
        @foreach($portfolios as $p)
        <a href="{{ route('company.portfolios.show', [$slug, $p->id]) }}" class="pf-card">
            <div style="display:flex; align-items:center; justify-content:space-between;">
                <div class="pf-icon">
                    <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.8"><path d="M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V7z"/></svg>
                </div>
            </div>
            <div>
                <div class="pf-title">{{ $p->title }}</div>
                <div class="pf-meta">{{ $p->projects_count }} project{{ $p->projects_count !== 1 ? 's' : '' }} · {{ $p->owner->name ?? 'Unknown' }}</div>
            </div>
        </a>
        @endforeach
        <div class="pf-create-card" onclick="document.getElementById('createPortfolioModal').classList.add('open')">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            <span style="font-size: 13px;">Create portfolio</span>
        </div>
    </div>
@endif

{{-- Create Portfolio Modal --}}
<div class="modal-overlay" id="createPortfolioModal">
    <div class="modal-box">
        <div class="modal-title">Create portfolio</div>
        <div class="form-row">
            <label class="form-label">Portfolio name</label>
            <input type="text" id="pfTitleInput" class="form-control" placeholder="e.g. Q3 Marketing Projects" maxlength="255">
        </div>
        <div class="form-row">
            <label class="form-label">Description (optional)</label>
            <textarea id="pfDescInput" class="form-control" rows="3" style="resize:vertical;" placeholder="What is this portfolio for?"></textarea>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-cancel" onclick="closePortfolioModal()">Cancel</button>
            <button type="button" class="btn-save" id="pfSaveBtn" onclick="createPortfolio()">Create</button>
        </div>
    </div>
</div>

<script>
function closePortfolioModal() {
    document.getElementById('createPortfolioModal').classList.remove('open');
    document.getElementById('pfTitleInput').value = '';
    document.getElementById('pfDescInput').value = '';
}
document.getElementById('createPortfolioModal').addEventListener('click', function(e) {
    if (e.target === this) closePortfolioModal();
});

function createPortfolio() {
    const btn = document.getElementById('pfSaveBtn');
    btn.disabled = true; btn.textContent = 'Creating…';

    fetch('{{ route("company.portfolios.store", $slug) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify({
            title: document.getElementById('pfTitleInput').value.trim(),
            description: document.getElementById('pfDescInput').value.trim(),
        })
    })
    .then(r => r.json())
    .then(data => {
        if (data.url) { window.location.href = data.url; return; }
        btn.disabled = false; btn.textContent = 'Create';
    })
    .catch(() => { btn.disabled = false; btn.textContent = 'Create'; });
}
</script>

</x-company-layout>
