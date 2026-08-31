<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Notification;
use Illuminate\Http\Request;

trait ManagesInbox
{
    protected function inboxQuery(Request $request)
    {
        $filter = $request->query('filter', 'all');
        if (! in_array($filter, ['all', 'unread', 'mentions', 'assigned'], true)) {
            $filter = 'all';
        }

        $q = auth()->user()->notifications()->latest();
        if ($filter === 'unread') {
            $q->where('is_read', false);
        } elseif ($filter === 'mentions') {
            $q->where('type', 'task_mention');
        } elseif ($filter === 'assigned') {
            $q->where('type', 'task_assigned');
        }

        return [$q->paginate(20)->withQueryString(), $filter];
    }

    protected function unreadPayload()
    {
        $items = auth()->user()
            ->notifications()
            ->where('is_read', false)
            ->latest()
            ->limit(10)
            ->get();

        return [
            'count' => auth()->user()->notifications()->where('is_read', false)->count(),
            'notifications' => $items,
        ];
    }

    protected function markOne(Notification $notification)
    {
        abort_if($notification->user_id !== auth()->id(), 403);
        $notification->markAsRead();

        return response()->json(['success' => true]);
    }

    protected function markEvery()
    {
        auth()->user()->notifications()->where('is_read', false)->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }
}
