{{-- Reusable slide-in task detail drawer. Include once per page; open with openPanel(taskId). --}}
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
    .task-drawer { position:fixed; inset:0; z-index:250; pointer-events:none; }
    .task-drawer .task-drawer-overlay { position:absolute; inset:0; background:rgba(0,0,0,0.5); opacity:0; transition:opacity 0.2s; }
    .task-drawer .task-drawer-panel { position:absolute; top:0; right:0; height:100%; width:560px; max-width:92vw; background:var(--surface); border-left:1px solid var(--border2); display:flex; flex-direction:column; transform:translateX(100%); transition:transform 0.25s ease; box-shadow:-10px 0 40px rgba(0,0,0,0.35); }
    .task-drawer.open { pointer-events:auto; }
    .task-drawer.open .task-drawer-overlay { opacity:1; }
    .task-drawer.open .task-drawer-panel { transform:translateX(0); }
    #taskDrawer .al-pill { font-size:12px; font-family:var(--font); border:1px solid transparent; border-radius:6px; padding:5px 10px; cursor:pointer; width:100%; -webkit-appearance:none; appearance:none; }
    #taskDrawer .al-pill:focus { outline:none; border-color:var(--accent2); }
    #taskDrawer .al-pill option { background:var(--surface); color:var(--text); }
    #taskDrawer .al-avatar { width:24px; height:24px; border-radius:6px; background:rgba(74,222,128,0.2); color:#4ade80; font-size:10px; font-weight:600; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
    #taskDrawer .panel-drop.show { display:block !important; }
</style>

<script>
    (function () {
        const slug = '{{ $slug ?? auth()->user()->company->slug }}';
        const csrfToken = '{{ csrf_token() }}';
        let panelTaskId = null;
        let panelDirty = false;

        function statusStyle(v){ const m={todo:['#9aa3b2','rgba(154,163,178,0.12)'],in_progress:['#22d3ee','rgba(34,211,238,0.12)'],in_review:['#a78bfa','rgba(167,139,250,0.12)'],done:['#4ade80','rgba(74,222,128,0.12)']}; return m[v]||m.todo; }
        function priStyle(v){ const m={urgent:['#f87171','rgba(248,113,113,0.12)'],high:['#fb923c','rgba(251,146,60,0.12)'],medium:['#fbbf24','rgba(251,191,36,0.12)'],low:['#9aa3b2','transparent']}; return m[v]||m.low; }
        function applyStatus(sel){ const [c,b]=statusStyle(sel.value); sel.style.color=c; sel.style.background=b; }
        function applyPri(sel){ const [c,b]=priStyle(sel.value); sel.style.color=c; sel.style.background=b; }

        function patchField(id, field, value){
            const body={}; body[field]=value;
            return fetch(`/${slug}/admin/tasks/${id}/inline`, {
                method:'PATCH',
                headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrfToken,'Accept':'application/json'},
                body:JSON.stringify(body)
            });
        }
        function post(url, formData){
            return fetch(url, { method:'POST', headers:{'X-CSRF-TOKEN':csrfToken,'Accept':'application/json'}, body:formData });
        }
        function del(url){
            return fetch(url, { method:'DELETE', headers:{'X-CSRF-TOKEN':csrfToken,'Accept':'application/json'} });
        }

        function openPanel(id){
            panelTaskId = id;
            document.getElementById('taskDrawer').classList.add('open');
            reloadPanel();
        }
        function closePanel(){
            document.getElementById('taskDrawer').classList.remove('open');
            if (panelDirty) { location.reload(); }
            panelTaskId = null;
        }
        function reloadPanel(){
            if (!panelTaskId) return;
            const body = document.getElementById('taskPanelBody');
            fetch(`/${slug}/admin/tasks/${panelTaskId}/panel`, { headers:{'Accept':'text/html'} })
                .then(r => r.text()).then(html => {
                    body.innerHTML = html;
                    body.querySelectorAll('.al-status').forEach(applyStatus);
                    body.querySelectorAll('.al-pri').forEach(applyPri);
                });
        }

        function panelPatch(field, value){ panelDirty = true; return patchField(panelTaskId, field, value); }
        function panelMarkComplete(cur){ const next = cur==='done'?'todo':'done'; panelDirty=true; patchField(panelTaskId,'status',next).then(reloadPanel); }
        function syncCompleteBtn(status){
            const btn=document.getElementById('panelComplete'); if(!btn) return;
            const done = status==='done';
            btn.style.background = done ? 'rgba(74,222,128,0.15)' : 'var(--surface2)';
            btn.style.border     = done ? '1px solid rgba(74,222,128,0.4)' : '1px solid var(--border2)';
            btn.style.color      = done ? '#4ade80' : 'var(--text)';
            btn.setAttribute('onclick', `panelMarkComplete('${status}')`);
            if (btn.lastChild) btn.lastChild.textContent = done ? ' Completed' : ' Mark complete';
        }
        function panelAssigneeChange(){
            panelDirty = true;
            const ids = Array.from(document.querySelectorAll('.panel-asg-cb:checked')).map(cb => cb.value);
            patchField(panelTaskId, 'assignees', ids).then(reloadPanel);
        }
        function panelToggleSubtask(id, status){ const next = status==='done'?'todo':'done'; panelDirty=true; patchField(id,'status',next).then(reloadPanel); }
        function panelAddSubtask(form){ panelDirty=true; post(form.action, new FormData(form)).then(r=>r.json()).then(reloadPanel); return false; }
        function panelAddComment(form){ panelDirty=true; post(form.action, new FormData(form)).then(r=>r.json()).then(reloadPanel); return false; }
        function panelDeleteComment(id){ panelDirty=true; del(`/${slug}/admin/tasks/comments/${id}`).then(reloadPanel); }
        function panelUpload(input){ if(!input.files.length) return; panelDirty=true; const fd=new FormData(); fd.append('file', input.files[0]); post(input.dataset.action, fd).then(r=>r.json()).then(reloadPanel); }
        function panelDeleteAttachment(id){ panelDirty=true; del(`/${slug}/admin/tasks/attachments/${id}`).then(reloadPanel); }
        function panelDeleteTask(){
            if(!confirm('Delete this task?')) return;
            del(`/${slug}/admin/tasks/${panelTaskId}`).then(()=>{ panelDirty=false; document.getElementById('taskDrawer').classList.remove('open'); location.reload(); });
        }

        document.addEventListener('keydown', function(e){
            if (e.key === 'Escape') { const d=document.getElementById('taskDrawer'); if (d.classList.contains('open')) closePanel(); }
        });

        // Expose to inline handlers used by the panel partial + callers.
        Object.assign(window, {
            openPanel, closePanel, reloadPanel, applyStatus, applyPri,
            panelPatch, panelMarkComplete, syncCompleteBtn, panelAssigneeChange,
            panelToggleSubtask, panelAddSubtask, panelAddComment, panelDeleteComment,
            panelUpload, panelDeleteAttachment, panelDeleteTask,
        });
    })();
</script>
