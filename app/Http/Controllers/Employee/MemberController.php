<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Services\MemberInvitationService;
use App\Services\RoleProvisioner;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MemberController extends Controller
{
    public function __construct(private MemberInvitationService $invites) {}

    public function index(string $slug)
    {
        abort_unless(auth()->user()->hasPermission('member.invite'), 403);

        $company = auth()->user()->company;
        app(RoleProvisioner::class)->forCompany((int) $company->id);

        $members = $company->users()->with('workspaceRoles')->latest()->get();
        $invitations = $company->invitations()->pending()->with('workspaceRole')->latest()->get();
        $workspaceRoles = Role::query()
            ->where('company_id', $company->id)
            ->where('portal_type', 'employee')
            ->orderBy('name')
            ->get();

        return view('employee.members.index', compact('members', 'invitations', 'workspaceRoles', 'slug'));
    }

    public function store(Request $request, string $slug)
    {
        abort_unless(auth()->user()->hasPermission('member.invite'), 403);

        $company = auth()->user()->company;

        $validRoleIds = Role::query()
            ->where('company_id', $company->id)
            ->where('portal_type', 'employee')
            ->pluck('id')
            ->map(fn ($id) => (string) $id)
            ->all();

        $data = $request->validate([
            'name'         => 'required|string|max:255',
            'email'        => 'required|email|max:255',
            'access_role'  => ['required', 'string', Rule::in(array_merge(['client'], $validRoleIds))],
        ]);

        if ($data['access_role'] === 'client') {
            $data['role'] = 'client';
            $data['workspace_role_id'] = null;
        } else {
            $data['role'] = 'employee';
            $data['workspace_role_id'] = (int) $data['access_role'];
        }
        unset($data['access_role']);

        $result = $this->invites->invite($company, auth()->user(), $data);

        $message = $result['mailed']
            ? 'Invite sent to '.$data['email'].'.'
            : 'Invite created, but email could not be sent. Copy the link below.';

        return back()
            ->with('success', $message)
            ->with('invite_url', $result['url']);
    }
}
