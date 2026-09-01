<?php

namespace App\Jobs;

use App\Models\Project;
use App\Models\User;
use App\Services\ProjectCsv;
use App\Services\WorkspaceNotifier;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;

class ImportProjectCsv implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public int $timeout = 110;

    public function __construct(
        public int $projectId,
        public int $userId,
        public string $storedPath,
    ) {
        $this->afterCommit();
    }

    public function handle(ProjectCsv $csv, WorkspaceNotifier $notifier): void
    {
        $project = Project::query()->find($this->projectId);
        $user = User::query()->find($this->userId);
        $absolute = Storage::disk('local')->path($this->storedPath);

        try {
            if (! $project || ! $user || ! is_file($absolute)) {
                return;
            }

            $imported = $csv->import($project, $absolute, $user->id);
            $slug = $user->company?->slug;
            $link = $slug ? '/'.$slug.'/admin/projects/'.$project->id : null;

            $notifier->personal(
                $user,
                'csv_import_done',
                'CSV import finished',
                $imported.' tasks imported into '.$project->name,
                $link
            );
        } finally {
            Storage::disk('local')->delete($this->storedPath);
        }
    }
}
