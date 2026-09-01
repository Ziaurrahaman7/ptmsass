<?php

namespace App\Services;

use App\Jobs\ProcessTaskAttachment;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\UploadedFile;

class TaskAttachmentIntake
{
    public function queue(Task $task, User $user, UploadedFile $file): void
    {
        $stored = $file->store('attachment-uploads');

        ProcessTaskAttachment::dispatch(
            $task->id,
            $user->id,
            $stored,
            $file->getClientOriginalName(),
            $file->getClientMimeType() ?: 'application/octet-stream',
            (int) $file->getSize(),
        );
    }
}
