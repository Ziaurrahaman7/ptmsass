<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Concerns\ManagesInbox;
use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    use ManagesInbox;

    public function index(Request $request, string $slug)
    {
        [$notifications, $filter] = $this->inboxQuery($request);

        return view('notifications.inbox', [
            'notifications' => $notifications,
            'filter' => $filter,
            'layout' => 'employee-layout',
            'indexRoute' => 'employee.notifications.index',
            'markAllRoute' => 'employee.notifications.mark-all-read',
            'markOneRoute' => 'employee.notifications.mark-as-read',
            'readBase' => '/'.$slug.'/notifications/',
            'slug' => $slug,
        ]);
    }

    public function unread(string $slug)
    {
        return response()->json($this->unreadPayload());
    }

    public function markAsRead(string $slug, Notification $notification)
    {
        return $this->markOne($notification);
    }

    public function markAllAsRead(string $slug)
    {
        $this->markEvery();

        return back()->with('success', 'Inbox marked as read.');
    }
}
