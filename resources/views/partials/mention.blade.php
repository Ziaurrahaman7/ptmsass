<style>
    .mention-box {
        display:none; position:fixed; z-index:600; min-width:220px; max-height:220px; overflow-y:auto;
        background:var(--surface); border:1px solid var(--border2); border-radius:8px;
        box-shadow:0 10px 28px rgba(0,0,0,0.45); padding:4px;
    }
    .mention-box button {
        display:flex; align-items:center; gap:8px; width:100%; background:none; border:none;
        color:var(--text); font-size:13px; font-family:var(--font); padding:7px 8px; border-radius:6px;
        cursor:pointer; text-align:left;
    }
    .mention-box button:hover, .mention-box button.active { background:var(--surface2); }
    .mention-field { position:relative; display:block; flex:1; min-width:0; width:100%; }
    .mention-field textarea {
        position:relative; z-index:2; width:100%;
        background:transparent !important;
        color:transparent !important;
        -webkit-text-fill-color:transparent !important;
        caret-color:var(--text);
    }
    .mention-field textarea:placeholder-shown {
        color:var(--muted) !important;
        -webkit-text-fill-color:var(--muted) !important;
    }
    .mention-field textarea::placeholder {
        color:var(--muted);
        -webkit-text-fill-color:var(--muted);
        opacity:1;
    }
    .mention-hl {
        position:absolute; inset:0; z-index:1; pointer-events:none; overflow:hidden;
        white-space:pre-wrap; word-wrap:break-word; word-break:break-word;
        color:var(--text); font-family:var(--font); font-size:13px; line-height:1.45;
        padding:9px 12px; border:1px solid transparent; border-radius:8px; box-sizing:border-box;
        background:var(--surface2);
    }
    .mention-hl .mn { color:#22d3ee; font-weight:700; }
</style>
<script>
window.Mention = {
    bindAll(root) {
        (root || document).querySelectorAll('textarea[name=comment]').forEach(t => {
            const host = t.closest('[data-members]');
            let members = [];
            try { members = host ? JSON.parse(host.getAttribute('data-members') || '[]') : []; } catch (e) {}
            if ((!members || !members.length) && Array.isArray(window.PTM_MEMBERS)) members = window.PTM_MEMBERS;
            this.bind(t, members);
        });
    },
    bind(textarea, members) {
        if (!textarea || textarea.dataset.mentionBound) return;
        textarea.dataset.mentionBound = '1';
        members = Array.isArray(members) ? members : [];
        const form = textarea.form;

        const wrap = document.createElement('div');
        wrap.className = 'mention-field';
        wrap.style.flex = '1';
        wrap.style.minWidth = '0';
        textarea.parentNode.insertBefore(wrap, textarea);
        textarea.style.setProperty('color', 'transparent', 'important');
        textarea.style.setProperty('-webkit-text-fill-color', 'transparent', 'important');
        textarea.style.setProperty('caret-color', 'var(--text)', 'important');
        textarea.style.setProperty('background', 'transparent', 'important');
        textarea.style.flex = 'none';
        textarea.style.width = '100%';
        const hl = document.createElement('div');
        hl.className = 'mention-hl';
        wrap.appendChild(hl);
        wrap.appendChild(textarea);

        const cs = getComputedStyle(textarea);
        ['padding', 'fontSize', 'lineHeight', 'fontFamily', 'fontWeight', 'letterSpacing', 'wordSpacing', 'borderRadius', 'textTransform'].forEach(k => {
            hl.style[k] = cs[k];
        });

        const box = document.createElement('div');
        box.className = 'mention-box';
        document.body.appendChild(box);

        const escapeHtml = (s) => s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
        const names = members.map(u => u.name || '').filter(Boolean).sort((a, b) => b.length - a.length);

        const prefixLen = (typed) => {
            const t = typed.toLowerCase();
            let best = 0;
            names.forEach(name => {
                const n = name.toLowerCase();
                let k = 0;
                while (k < n.length && k < t.length && n[k] === t[k]) k++;
                if (k > best) best = k;
            });
            return best;
        };

        const paint = () => {
            const value = textarea.value;
            if (value) {
                textarea.style.setProperty('color', 'transparent', 'important');
                textarea.style.setProperty('-webkit-text-fill-color', 'transparent', 'important');
            } else {
                textarea.style.setProperty('color', 'var(--muted)', 'important');
                textarea.style.setProperty('-webkit-text-fill-color', 'var(--muted)', 'important');
            }
            let html = '';
            let i = 0;
            while (i < value.length) {
                const start = value[i] === '@' && (i === 0 || !/[A-Za-z0-9_]/.test(value[i - 1]));
                if (start) {
                    const take = prefixLen(value.slice(i + 1));
                    if (take > 0) {
                        html += '<span class="mn">@' + escapeHtml(value.slice(i + 1, i + 1 + take)) + '</span>';
                        i += 1 + take;
                        continue;
                    }
                }
                html += escapeHtml(value[i]);
                i++;
            }
            hl.innerHTML = html + (value.endsWith('\n') ? ' ' : '');
            hl.scrollTop = textarea.scrollTop;
        };

        const syncHidden = (ids) => {
            if (!form) return;
            form.querySelectorAll('input[name="mentioned_ids[]"]').forEach(el => el.remove());
            ids.forEach(id => {
                const i = document.createElement('input');
                i.type = 'hidden';
                i.name = 'mentioned_ids[]';
                i.value = id;
                form.appendChild(i);
            });
        };
        const selected = new Set();

        const place = () => {
            const r = textarea.getBoundingClientRect();
            box.style.left = r.left + 'px';
            box.style.top = (r.bottom + 4) + 'px';
            box.style.width = Math.max(220, r.width) + 'px';
        };

        const closeBox = () => { box.style.display = 'none'; };

        const tokenBeforeCaret = () => {
            const before = textarea.value.slice(0, textarea.selectionStart);
            const at = before.lastIndexOf('@');
            if (at === -1) return null;
            if (at > 0 && /[A-Za-z0-9_]/.test(before[at - 1])) return null;
            const token = before.slice(at + 1);
            if (token.includes('\n')) return null;
            return { at, token };
        };

        const openHits = (hits) => {
            if (!hits.length) { closeBox(); return; }
            box.innerHTML = hits.map((u, i) =>
                `<button type="button" class="${i===0?'active':''}" data-id="${u.id}" data-name="${escapeHtml(u.name || '')}">
                    <span style="width:22px;height:22px;border-radius:50%;background:var(--surface2);display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:700;">${(u.name || '?').slice(0,1).toUpperCase()}</span>
                    <span>${escapeHtml(u.name || '')}<span style="color:var(--muted);margin-left:6px;font-size:11px;">${escapeHtml(u.email || '')}</span></span>
                </button>`
            ).join('');
            place();
            box.style.display = 'block';
            box.querySelectorAll('button').forEach(btn => {
                btn.addEventListener('mousedown', function (e) {
                    e.preventDefault();
                    pick(Number(this.dataset.id), this.dataset.name);
                });
            });
        };

        const pick = (id, name) => {
            const ctx = tokenBeforeCaret();
            const before = textarea.value.slice(0, textarea.selectionStart);
            const end = textarea.value.slice(textarea.selectionStart);
            const head = ctx ? before.slice(0, ctx.at) : before.replace(/@[^\s@]*$/, '');
            textarea.value = head + '@' + name + ' ' + end;
            textarea.selectionStart = textarea.selectionEnd = (head + '@' + name + ' ').length;
            selected.add(id);
            syncHidden([...selected]);
            closeBox();
            paint();
            textarea.focus();
        };

        const suggest = () => {
            const ctx = tokenBeforeCaret();
            if (!ctx) { closeBox(); return; }
            const q = ctx.token.toLowerCase();
            const exact = members.some(u => (u.name || '').toLowerCase() === q);
            if (exact) { closeBox(); return; }
            const hits = members.filter(u => {
                const name = (u.name || '').toLowerCase();
                const email = (u.email || '').toLowerCase();
                return name.startsWith(q) || name.includes(q) || email.includes(q);
            }).slice(0, 8);
            openHits(hits);
        };

        textarea.addEventListener('input', function () {
            this.style.height = 'auto';
            this.style.height = this.scrollHeight + 'px';
            wrap.style.minHeight = this.style.height;
            paint();
            suggest();
        });
        textarea.addEventListener('scroll', () => { hl.scrollTop = textarea.scrollTop; });
        textarea.addEventListener('keyup', paint);
        textarea.addEventListener('click', paint);
        textarea.addEventListener('keydown', (e) => {
            const open = box.style.display === 'block';
            const buttons = open ? [...box.querySelectorAll('button')] : [];
            const idx = buttons.findIndex(b => b.classList.contains('active'));
            if (open && (e.key === 'ArrowDown' || e.key === 'ArrowUp')) {
                e.preventDefault();
                const next = e.key === 'ArrowDown'
                    ? Math.min(buttons.length - 1, idx + 1)
                    : Math.max(0, idx - 1);
                buttons.forEach(b => b.classList.remove('active'));
                buttons[next]?.classList.add('active');
                buttons[next]?.scrollIntoView({ block: 'nearest' });
                return;
            }
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                if (open && buttons[Math.max(idx, 0)]) {
                    pick(Number(buttons[Math.max(idx, 0)].dataset.id), buttons[Math.max(idx, 0)].dataset.name);
                    return;
                }
                if (form) form.requestSubmit();
                return;
            }
            if (e.key === 'Escape') closeBox();
        });
        textarea.addEventListener('blur', () => setTimeout(closeBox, 180));
        paint();
    }
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => Mention.bindAll(document));
} else {
    Mention.bindAll(document);
}
</script>
