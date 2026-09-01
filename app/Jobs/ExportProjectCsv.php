<?php

namespace App\Jobs;

use App\Models\Project;
use App\Models\User;
use App\Services\ProjectCsv;
use App\Services\WorkspaceNotifier;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ExportProjectCsv implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 110;

    public function __construct(
        public int $projectId,
        public int $userId,
    ) {
        $this->afterCommit();
    }

    public function handle(ProjectCsv $csv, WorkspaceNotifier $notifier): void
    {
        $project = Project::query()->find($this->projectId);
        $user = User::query()->find($this->userId);
        if (! $project || ! $user) {
            return;
        }

        $token = Str::random(40);
        $relative = 'csv-exports/'.$project->company_id.'/'.$project->id.'/'.$token.'.csv';
        Storage::disk('local')->makeDirectory(dirname($relative));
        $csv->exportToPath($project, Storage::disk('local')->path($relative));

        $slug = $user->company?->slug;
        $link = $slug
            ? '/'.$slug.'/admin/projects/'.$project->id.'/exports/'.$token
            : null;

        $notifier->personal(
            $user,
            'csv_export_ready',
            'CSV export ready',
            'Download tasks from '.$project->name,
            $link
        );
    }
}
