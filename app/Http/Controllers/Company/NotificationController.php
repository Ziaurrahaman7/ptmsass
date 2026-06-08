<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(string $slug)
    {
        $notifications = auth()->user()
            ->notifications()
            ->latest()
            ->paginate(20);

        return view('company.notifications.index', compact('notifications'));
    }

    public function unread(string $slug)
    {
        $notifications = auth()->user()
            ->notifications()
            ->where('is_read', false)
            ->latest()
            ->limit(10)
            ->get();

        return response()->json([
            'count' => $notifications->count(),
            'notifications' => $notifications
        ]);
    }

    public function markAsRead(string $slug, Notification $notification)
    {
        abort_if($notification->user_id !== auth()->id(), 403);
        
        $notification->markAsRead();

        return response()->json(['success' => true]);
    }

    public function markAllAsRead(string $slug)
    {
        auth()->user()
            ->notifications()
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now()
            ]);

        return back()->with('success', 'All notifications marked as read.');
    }
}
