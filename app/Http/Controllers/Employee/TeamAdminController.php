<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;

class TeamAdminController extends Controller
{
    public function index(string $slug)
    {
        $teams = auth()->user()->teams()->orderBy('name')->get();

        return view('employee.teams.index', compact('teams', 'slug'));
    }

    public function show(string $slug, Team $team)
    {
        $this->authorize('view', $team);
        $team->load('members');
        $canManage = auth()->user()->can('update', $team);
        $memberIds = $team->members->pluck('id');
        $people = User::query()
            ->where('company_id', auth()->user()->company_id)
            ->whereIn('role', ['employee', 'company_admin'])
            ->where('is_active', true)
            ->whereNotIn('id', $memberIds)
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        $myPivot = auth()->user()->teams()->where('teams.id', $team->id)->first()?->pivot;
        $myTeamRole = $myPivot?->role ?? 'member';

        return view('employee.teams.show', compact('team', 'people', 'canManage', 'myTeamRole', 'slug'));
    }

    public function update(Request $request, string $slug, Team $team)
    {
        $this->authorize('update', $team);
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
        ]);
        $team->update($data);

        return back()->with('success', 'Team updated.');
    }

    public function addMember(Request $request, string $slug, Team $team)
    {
        $this->authorize('update', $team);
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'role' => 'nullable|in:admin,member',
        ]);
        $user = User::query()->where('company_id', auth()->user()->company_id)->findOrFail($data['user_id']);
        $team->members()->syncWithoutDetaching([$user->id => ['role' => $data['role'] ?? 'member']]);

        return back()->with('success', 'Member added.');
    }

    public function updateMemberRole(Request $request, string $slug, Team $team, User $user)
    {
        $this->authorize('update', $team);
        abort_unless($team->members()->where('users.id', $user->id)->exists(), 404);

        $data = $request->validate([
            'role' => 'required|in:admin,member',
        ]);

        $team->members()->updateExistingPivot($user->id, [
            'role' => $data['role'],
        ]);

        return back()->with('success', 'Team role updated.');
    }

    public function removeMember(string $slug, Team $team, User $user)
    {
        $this->authorize('update', $team);
        abort_if((int) $user->company_id !== (int) auth()->user()->company_id, 403);
        $team->members()->detach($user->id);

        return back()->with('success', 'Member removed.');
    }
}
