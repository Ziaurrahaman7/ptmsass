<x-employee-layout title="Notifications">

    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:20px;">
        <div>
            <div style="font-size:18px; font-weight:600; letter-spacing:-0.3px; color:var(--text);">Notifications</div>
            <div style="font-size:13px; color:var(--muted); margin-top:2px;">Stay updated with all activities</div>
        </div>
        @if($notifications->where('is_read', false)->count() > 0)
        <form method="POST" action="{{ route('employee.notifications.mark-all-read', auth()->user()->company->slug) }}">
            @csrf
            <button type="submit" class="ptm-btn-ghost" style="font-size:12px; padding:6px 14px; background:transparent; border:1px solid var(--border2); color:var(--muted); border-radius:8px; cursor:pointer; transition:all 0.15s;" onmouseover="this.style.background='var(--surface2)'; this.style.color='var(--text)'" onmouseout="this.style.background='transparent'; this.style.color='var(--muted)'">Mark all as read</button>
        </form>
        @endif
    </div>

    <div class="ptm-card">
        @forelse($notifications as $notification)
        <div style="padding:16px 20px; border-bottom:1px solid var(--border); display:flex; align-items:flex-start; gap:14px; {{ $notification->is_read ? '' : 'background:rgba(74,222,128,0.02);' }}">
            <div style="width:8px; height:8px; border-radius:50%; background:{{ $notification->is_read ? 'transparent' : 'var(--accent)' }}; margin-top:8px; flex-shrink:0;"></div>
            
            <div style="flex:1; min-width:0;">
                <div style="display:flex; align-items:flex-start; justify-content:space-between; gap:12px; margin-bottom:4px;">
                    <div style="font-size:14px; font-weight:500; color:var(--text);">{{ $notification->title }}</div>
                    <div style="font-size:11px; color:var(--muted); font-family:var(--mono); white-space:nowrap;">{{ $notification->created_at->diffForHumans() }}</div>
                </div>
                
                <div style="font-size:13px; color:var(--muted); line-height:1.5; margin-bottom:8px;">{{ $notification->message }}</div>
                
                <div style="display:flex; align-items:center; gap:10px;">
                    @if($notification->link)
                    <a href="{{ $notification->link }}" onclick="markAsRead({{ $notification->id }})" style="font-size:12px; color:var(--accent2); text-decoration:none; font-weight:500;" onmouseover="this.style.color='var(--accent)'" onmouseout="this.style.color='var(--accent2)'">View →</a>
                    @endif
                    
                    @if(!$notification->is_read)
                    <form method="POST" action="{{ route('employee.notifications.mark-as-read', [auth()->user()->company->slug, $notification]) }}" style="display:inline;">
                        @csrf
                        @method('PATCH')
                        <button type="submit" style="background:none; border:none; font-size:12px; color:var(--muted); cursor:pointer; font-family:var(--mono);" onmouseover="this.style.color='var(--text)'" onmouseout="this.style.color='var(--muted)'">Mark as read</button>
                    </form>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div style="padding:60px 20px; text-align:center;">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="var(--muted)" stroke-width="1.5" style="margin:0 auto 16px;"><path d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
            <div style="font-size:14px; color:var(--muted); margin-bottom:6px;">No notifications yet</div>
            <div style="font-size:12px; color:var(--muted);">You'll see updates about your tasks here</div>
        </div>
        @endforelse
    </div>

    @if($notifications->hasPages())
    <div style="margin-top:20px;">
        {{ $notifications->links() }}
    </div>
    @endif

    <script>
    function markAsRead(id) {
        const slug = '{{ auth()->user()->company->slug }}';
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        fetch('/' + slug + '/notifications/' + id + '/read', {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        });
    }
    </script>

</x-employee-layout>
