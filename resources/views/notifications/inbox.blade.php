<x-dynamic-component :component="$layout" title="Inbox">

@php
    $tabs = ['all' => 'All', 'unread' => 'Unread', 'mentions' => 'Mentions', 'assigned' => 'Assigned'];
    $typeColor = [
        'task_mention' => '#67e8f9',
        'task_assigned' => '#a78bfa',
        'task_comment' => '#22d3ee',
        'task_status_changed' => '#fbbf24',
        'task_updated' => '#94a3b8',
        'csv_import_done' => '#4ade80',
        'csv_export_ready' => '#60a5fa',
        'task_attachment' => '#fb923c',
        'attachment_ready' => '#4ade80',
    ];
@endphp

<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:18px; gap:12px; flex-wrap:wrap;">
    <div>
        <div style="font-size:20px; font-weight:600; letter-spacing:-0.3px; color:var(--text);">Inbox</div>
        <div style="font-size:13px; color:var(--muted); margin-top:2px;">Assignments, comments, and mentions</div>
    </div>
    <form method="POST" action="{{ route($markAllRoute, $slug) }}">
        @csrf
        <button type="submit" class="ptm-btn-ghost" style="font-size:12px; padding:6px 14px;">Mark all as read</button>
    </form>
</div>

<div style="display:flex; gap:6px; margin-bottom:16px; flex-wrap:wrap;">
    @foreach($tabs as $key => $label)
    <a href="{{ route($indexRoute, ['slug' => $slug, 'filter' => $key]) }}"
        style="padding:6px 12px; border-radius:20px; font-size:12px; text-decoration:none; border:1px solid {{ $filter === $key ? 'var(--border2)' : 'var(--border)' }}; color:{{ $filter === $key ? 'var(--text)' : 'var(--muted)' }}; background:{{ $filter === $key ? 'var(--surface2)' : 'transparent' }};">{{ $label }}</a>
    @endforeach
</div>

<div class="ptm-card">
    @forelse($notifications as $notification)
    <a href="{{ $notification->link ?: '#' }}" onclick="inboxMark({{ $notification->id }})"
        style="display:flex; align-items:flex-start; gap:14px; padding:16px 20px; border-bottom:1px solid var(--border); text-decoration:none; {{ $notification->is_read ? '' : 'background:rgba(34,211,238,0.04);' }}">
        <div style="width:8px; height:8px; border-radius:50%; margin-top:7px; flex-shrink:0; background:{{ $notification->is_read ? 'transparent' : ($typeColor[$notification->type] ?? 'var(--accent)') }};"></div>
        <div style="flex:1; min-width:0;">
            <div style="display:flex; justify-content:space-between; gap:12px;">
                <div style="font-size:14px; font-weight:{{ $notification->is_read ? '500' : '600' }}; color:var(--text);">{{ $notification->title }}</div>
                <div style="font-size:11px; color:var(--muted); font-family:var(--mono); white-space:nowrap;">{{ $notification->created_at->diffForHumans() }}</div>
            </div>
            <div style="font-size:13px; color:var(--muted); line-height:1.5; margin-top:4px;">{{ $notification->message }}</div>
        </div>
    </a>
    @empty
    <div style="padding:56px 20px; text-align:center;">
        <div style="font-size:14px; color:var(--muted); margin-bottom:6px;">Inbox is empty</div>
        <div style="font-size:12px; color:var(--muted);">New assignments, comments, and mentions will show up here.</div>
    </div>
    @endforelse
</div>

@if($notifications->hasPages())
<div style="margin-top:20px;">{{ $notifications->links() }}</div>
@endif

<script>
function inboxMark(id) {
    fetch(@json($readBase) + id + '/read', {
        method: 'PATCH',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' }
    });
}
</script>

</x-dynamic-component>
