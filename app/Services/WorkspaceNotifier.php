<?php

namespace App\Services;

use App\Events\InboxReceived;
use App\Mail\WorkspaceNotificationMail;
use App\Models\Notification;
use App\Models\Task;
use App\Models\User;
use App\Support\Mentions;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;

class WorkspaceNotifier
{
    public function follow(Task $task, array $userIds): void
    {
        $ids = collect($userIds)->filter()->map(fn ($id) => (int) $id)->unique()->all();
        if ($ids) {
            $task->followers()->syncWithoutDetaching($ids);
        }
    }

    public function assigned(Task $task, array $userIds, User $actor): void
    {
        $this->follow($task, array_merge($userIds, [$actor->id, $task->created_by]));
        foreach ($this->recipients($userIds, $actor) as $user) {
            $this->push($user, $task, $actor, 'task_assigned', 'Task assigned to you', $actor->name.' assigned you: '.$task->title);
        }
    }

    public function commented(Task $task, string $body, User $actor, Collection $members, array $explicitMentions = []): void
    {
        $this->follow($task, [$actor->id, $task->created_by]);
        $mentioned = Mentions::extractIds($body, $members, $explicitMentions);
        $this->follow($task, $mentioned);

        $preview = mb_strimwidth(trim(preg_replace('/\s+/', ' ', $body)), 0, 140, '…');
        $notified = [];

        foreach ($this->recipients($mentioned, $actor) as $user) {
            $this->push($user, $task, $actor, 'task_mention', $actor->name.' mentioned you', $preview ?: $task->title);
            $notified[] = $user->id;
        }

        $followerIds = $task->followers()->pluck('users.id')->all();
        foreach ($this->recipients($followerIds, $actor) as $user) {
            if (in_array($user->id, $notified, true)) {
                continue;
            }
            $this->push($user, $task, $actor, 'task_comment', $actor->name.' commented', $preview ?: ('on '.$task->title));
        }
    }

    public function statusChanged(Task $task, User $actor, string $from, string $to): void
    {
        $this->follow($task, [$actor->id, $task->created_by]);
        $ids = $task->followers()->pluck('users.id')->all();
        foreach ($this->recipients($ids, $actor) as $user) {
            $this->push(
                $user,
                $task,
                $actor,
                'task_status_changed',
                'Task status updated',
                $actor->name.' moved "'.$task->title.'" from '.$from.' to '.$to
            );
        }
    }

    public function updated(Task $task, User $actor, string $title, string $message): void
    {
        $ids = $task->followers()->pluck('users.id')->all();
        if (! $ids) {
            $ids = $task->assignees()->pluck('users.id')->all();
        }
        foreach ($this->recipients($ids, $actor) as $user) {
            $this->push($user, $task, $actor, 'task_updated', $title, $message);
        }
    }

    public function attached(Task $task, User $actor, string $fileName): void
    {
        $this->follow($task, [$actor->id, $task->created_by]);
        $ids = $task->followers()->pluck('users.id')->all();
        foreach ($this->recipients($ids, $actor) as $user) {
            $this->push($user, $task, $actor, 'task_attachment', $actor->name.' attached a file', $fileName.' on '.$task->title);
        }

        $slug = $actor->company?->slug ?? $task->company?->slug;
        $link = $slug
            ? ($actor->isCompanyAdmin()
                ? '/'.$slug.'/admin/tasks/'.$task->id
                : '/'.$slug.'/tasks/'.$task->id)
            : null;

        $this->personal($actor, 'attachment_ready', 'File ready', $fileName.' is on '.$task->title, $link);
    }

    public function personal(User $user, string $type, string $title, string $message, ?string $link = null): void
    {
        $notification = Notification::create([
            'user_id' => $user->id,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'link' => $link,
        ]);

        $this->deliver($notification, $user);
    }

    protected function recipients(array $userIds, User $actor): Collection
    {
        $ids = collect($userIds)->map(fn ($id) => (int) $id)->unique()->reject(fn ($id) => $id === (int) $actor->id);

        return User::query()
            ->whereIn('id', $ids)
            ->where('is_active', true)
            ->whereIn('role', ['employee', 'company_admin'])
            ->get();
    }

    protected function push(User $user, Task $task, User $actor, string $type, string $title, string $message): void
    {
        $slug = $user->company?->slug ?? $task->company?->slug;
        $link = $slug
            ? ($user->isCompanyAdmin()
                ? '/'.$slug.'/admin/tasks/'.$task->id
                : '/'.$slug.'/tasks/'.$task->id)
            : null;

        $notification = Notification::create([
            'user_id' => $user->id,
            'actor_id' => $actor->id,
            'task_id' => $task->id,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'link' => $link,
        ]);

        $this->deliver($notification, $user);
    }

    protected function deliver(Notification $notification, User $user): void
    {
        try {
            if (PlatformBroadcast::apply()) {
                broadcast(new InboxReceived($notification));
            }
        } catch (\Throwable $e) {
            report($e);
        }

        try {
            PlatformMail::apply();
            Mail::to($user->email)->send(new WorkspaceNotificationMail($notification));
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
