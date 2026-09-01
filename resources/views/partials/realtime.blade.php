<script>
function toastQueued(msg) {
    const el = document.createElement('div');
    el.textContent = msg || 'Queued';
    el.style.cssText = 'position:fixed;bottom:24px;right:24px;z-index:2400;background:var(--surface);border:1px solid var(--border2);padding:12px 16px;border-radius:10px;font-size:13px;color:var(--text);box-shadow:0 8px 24px rgba(0,0,0,.4);max-width:280px;';
    document.body.appendChild(el);
    setTimeout(function () { el.remove(); }, 4500);
}
</script>
@php
    $pusherLive = \App\Models\PusherSetting::current();
@endphp
@if(auth()->check() && $pusherLive->isReady())
<script src="https://js.pusher.com/8.4.0/pusher.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.16.1/dist/echo.iife.js"></script>
<script>
(function () {
    if (!window.Pusher || !window.Echo) return;
    const echo = new Echo({
        broadcaster: 'pusher',
        key: @json($pusherLive->key),
        cluster: @json($pusherLive->cluster),
        forceTLS: true,
        authEndpoint: '/broadcasting/auth',
        auth: {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        }
    });
    echo.private('users.{{ auth()->id() }}')
        .listen('.inbox.received', function (n) {
            if (typeof bumpInboxCount === 'function') bumpInboxCount();
            if (typeof fetchNotifications === 'function') fetchNotifications();
            if ((n.type === 'attachment_ready' || n.type === 'task_attachment') && typeof reloadPanel === 'function') {
                reloadPanel();
            }
            const el = document.createElement('div');
            el.textContent = n.title || 'New notification';
            el.style.cssText = 'position:fixed;bottom:24px;right:24px;z-index:2400;background:var(--surface);border:1px solid var(--border2);padding:12px 16px;border-radius:10px;font-size:13px;color:var(--text);box-shadow:0 8px 24px rgba(0,0,0,.4);max-width:280px;cursor:pointer;';
            if (n.link) el.addEventListener('click', function () { window.location.href = n.link; });
            document.body.appendChild(el);
            setTimeout(function () { el.remove(); }, 6000);
        });
})();
</script>
@endif
