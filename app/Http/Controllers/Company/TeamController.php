<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Project;
use App\Models\Task;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    private function companyId(): int
    {
        return auth()->user()->company_id;
    }

    public function overview(string $slug, Team $team)
    {
        abort_if($team->company_id !== $this->companyId(), 403);

        $team->load('members');

        $members = $team->members;

        $memberIds = $members->pluck('id')->toArray();

        $projects = Project::where('company_id', $this->companyId())
            ->withCount('tasks')
            ->with(['tasks.assignee:id,name,is_active', 'tasks.assignees:id,name,is_active'])
            ->orderBy('name')
            ->get();

        // Members shown per project = distinct users assigned to any of its tasks.
        $projectMembers = $projects->mapWithKeys(function ($project) {
            $users = collect();
            foreach ($project->tasks as $t) {
                if ($t->assignee) $users->push($t->assignee);
                foreach ($t->assignees as $a) $users->push($a);
            }
            return [$project->id => $users->unique('id')->values()];
        });

        $tasks = Task::where('company_id', $this->companyId())
            ->whereNull('parent_task_id')
            ->where(function ($q) use ($memberIds) {
                $q->whereIn('assigned_to', $memberIds)
                  ->orWhereHas('assignees', fn($q) => $q->whereIn('user_id', $memberIds));
            })
            ->with(['project', 'assignees', 'section', 'assignee'])
            ->latest()
            ->get();

        $recentActivities = ActivityLog::where('company_id', $this->companyId())
            ->whereIn('user_id', $memberIds)
            ->with('user')
            ->latest()
            ->take(20)
            ->get();

        $messages = $team->messages()->with('user')->orderBy('created_at')->get();

        $docs = $team->docs()->with('user')->get();

        $notes = $team->notes()->get();

        return view('company.team.overview', compact('team', 'members', 'projects', 'projectMembers', 'tasks', 'recentActivities', 'messages', 'docs', 'notes'));
    }

    public function store(Request $request, string $slug)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'members'     => 'nullable|array',
            'members.*'   => 'exists:users,id',
        ]);

        $team = Team::create([
            'company_id'  => $this->companyId(),
            'name'        => $data['name'],
            'description' => $data['description'] ?? null,
        ]);

        if (!empty($data['members'])) {
            $team->members()->attach($data['members']);
        }

        // Go straight to the new team (correct id + slug) so the user never has to guess the URL.
        return redirect()->route('company.team.overview', ['slug' => $slug, 'team' => $team])
            ->with('success', 'Team created.');
    }

    public function update(Request $request, string $slug, Team $team)
    {
        abort_if($team->company_id !== $this->companyId(), 403);

        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'members'     => 'nullable|array',
            'members.*'   => 'exists:users,id',
        ]);

        $team->update([
            'name'        => $data['name'],
            'description' => $data['description'] ?? null,
        ]);

        $team->members()->sync($data['members'] ?? []);

        return back()->with('success', 'Team updated.');
    }

    public function addMembers(Request $request, string $slug, Team $team)
    {
        abort_if($team->company_id !== $this->companyId(), 403);

        $data = $request->validate([
            'members'   => 'required|array',
            'members.*' => 'exists:users,id',
        ]);

        // Only attach users that belong to this company and aren't already members.
        $companyUserIds = \App\Models\User::where('company_id', $this->companyId())
            ->whereIn('id', $data['members'])
            ->pluck('id')
            ->toArray();

        $team->members()->syncWithoutDetaching($companyUserIds);

        return redirect()->route('company.team.overview', ['slug' => $slug, 'team' => $team, 'tab' => 'members'])
            ->with('success', 'Member(s) added to team.');
    }

    public function updateMemberTitle(Request $request, string $slug, Team $team, User $user)
    {
        abort_if($team->company_id !== $this->companyId(), 403);

        $data = $request->validate([
            'job_title' => 'nullable|string|max:255',
        ]);

        $team->members()->updateExistingPivot($user->id, [
            'job_title' => $data['job_title'] ?: null,
        ]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['ok' => true, 'job_title' => $data['job_title'] ?: null]);
        }

        return back()->with('success', 'Job title updated.');
    }

    public function removeMember(string $slug, Team $team, User $user)
    {
        abort_if($team->company_id !== $this->companyId(), 403);

        $team->members()->detach($user->id);

        return redirect()->route('company.team.overview', ['slug' => $slug, 'team' => $team, 'tab' => 'members'])
            ->with('success', 'Member removed from team.');
    }

    public function storeMessage(Request $request, string $slug, Team $team)
    {
        abort_if($team->company_id !== $this->companyId(), 403);

        $data = $request->validate([
            'body' => 'required|string|max:5000',
        ]);

        $team->messages()->create([
            'company_id' => $this->companyId(),
            'user_id'    => auth()->id(),
            'body'       => $data['body'],
        ]);

        return redirect()->route('company.team.overview', ['slug' => $slug, 'team' => $team, 'tab' => 'messages'])
            ->with('success', 'Message sent.');
    }

    public function storeDoc(Request $request, string $slug, Team $team)
    {
        abort_if($team->company_id !== $this->companyId(), 403);

        $data = $request->validate([
            'title'   => 'required|string|max:255',
            'content' => 'nullable|string',
        ]);

        $team->docs()->create([
            'company_id' => $this->companyId(),
            'user_id'    => auth()->id(),
            'title'      => $data['title'],
            'content'    => $data['content'] ?? null,
        ]);

        return redirect()->route('company.team.overview', ['slug' => $slug, 'team' => $team, 'tab' => 'knowledge'])
            ->with('success', 'Doc created.');
    }

    public function destroyDoc(string $slug, Team $team, \App\Models\TeamDoc $doc)
    {
        abort_if($team->company_id !== $this->companyId(), 403);
        abort_if($doc->team_id !== $team->id, 403);

        $doc->delete();

        return redirect()->route('company.team.overview', ['slug' => $slug, 'team' => $team, 'tab' => 'knowledge'])
            ->with('success', 'Doc deleted.');
    }

    public function storeNote(string $slug, Team $team)
    {
        abort_if($team->company_id !== $this->companyId(), 403);

        $note = $team->notes()->create([
            'company_id' => $this->companyId(),
            'user_id'    => auth()->id(),
            'title'      => null,
            'content'    => null,
        ]);

        return redirect()->route('company.team.overview', ['slug' => $slug, 'team' => $team, 'tab' => 'note-' . $note->id]);
    }

    public function updateNote(Request $request, string $slug, Team $team, \App\Models\TeamNote $note)
    {
        abort_if($team->company_id !== $this->companyId(), 403);
        abort_if($note->team_id !== $team->id, 403);

        $data = $request->validate([
            'title'   => 'nullable|string|max:255',
            'content' => 'nullable|string',
        ]);

        $note->update([
            'title'   => $data['title'] ?? null,
            'content' => $data['content'] ?? null,
        ]);

        return response()->json(['ok' => true]);
    }

    public function destroyNote(string $slug, Team $team, \App\Models\TeamNote $note)
    {
        abort_if($team->company_id !== $this->companyId(), 403);
        abort_if($note->team_id !== $team->id, 403);

        $note->delete();

        return redirect()->route('company.team.overview', ['slug' => $slug, 'team' => $team, 'tab' => 'overview'])
            ->with('success', 'Note deleted.');
    }

    public function destroy(string $slug, Team $team)
    {
        abort_if($team->company_id !== $this->companyId(), 403);
        $team->delete();
        return redirect()->route('company.dashboard', $slug)->with('success', 'Team deleted.');
    }
}
