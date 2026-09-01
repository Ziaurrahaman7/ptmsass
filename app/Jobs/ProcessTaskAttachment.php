<?php

namespace App\Jobs;

use App\Models\ActivityLog;
use App\Models\Task;
use App\Models\TaskAttachment;
use App\Models\User;
use App\Services\WorkspaceNotifier;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProcessTaskAttachment implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public int $timeout = 110;

    public function __construct(
        public int $taskId,
        public int $userId,
        public string $storedPath,
        public string $originalName,
        public string $mimeType,
        public int $fileSize,
    ) {
        $this->afterCommit();
    }

    public function handle(WorkspaceNotifier $notifier): void
    {
        $task = Task::query()->find($this->taskId);
        $user = User::query()->find($this->userId);

        try {
            if (! $task || ! $user || ! Storage::disk('local')->exists($this->storedPath)) {
                return;
            }

            $base = basename($this->originalName);
            $safe = preg_replace('/[^A-Za-z0-9._-]+/', '_', $base) ?: 'file';
            $dest = 'task-attachments/'.time().'_'.Str::random(8).'_'.$safe;

            Storage::disk('public')->put($dest, Storage::disk('local')->get($this->storedPath));

            TaskAttachment::create([
                'task_id' => $task->id,
                'uploaded_by' => $user->id,
                'file_name' => $base,
                'file_path' => $dest,
                'file_type' => $this->mimeType ?: null,
                'file_size' => $this->fileSize,
            ]);

            ActivityLog::create([
                'company_id' => $task->company_id,
                'user_id' => $user->id,
                'subject_type' => Task::class,
                'subject_id' => $task->id,
                'action' => 'uploaded',
                'description' => $user->name.' uploaded a file: '.$base,
            ]);

            $notifier->attached($task, $user, $base);
        } finally {
            Storage::disk('local')->delete($this->storedPath);
        }
    }
}
