<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\Models\Role;
use App\Models\User;
use App\Services\MemberInvitationService;
use App\Services\RoleProvisioner;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MemberController extends Controller
{
    public function __construct(private MemberInvitationService $invites) {}

    private function company()
    {
        return auth()->user()->company;
    }

    public function index(string $slug)
    {
        abort_unless(auth()->user()->hasPermission('member.manage'), 403);

        $company = $this->company();
        app(RoleProvisioner::class)->forCompany((int) $company->id);

        $members = $company->users()->with('workspaceRoles')->latest()->get();
        $invitations = $company->invitations()->pending()->with('workspaceRole')->latest()->get();
        $workspaceRoles = Role::query()
            ->where('company_id', $company->id)
            ->where('portal_type', 'employee')
            ->orderBy('name')
            ->get();

        return view('company.members.index', compact('members', 'invitations', 'workspaceRoles'));
    }

    public function store(Request $request, string $slug)
    {
        abort_unless(auth()->user()->hasPermission('member.manage'), 403);

        $company = $this->company();

        $validRoleIds = Role::query()
            ->where('company_id', $company->id)
            ->where('portal_type', 'employee')
            ->pluck('id')
            ->map(fn ($id) => (string) $id)
            ->all();

        $accessRoleOptions = array_merge(['client'], $validRoleIds);
        if (auth()->user()->isCompanyAdmin()) {
            $accessRoleOptions[] = 'company_admin';
        }

        $data = $request->validate([
            'name'         => 'required|string|max:255',
            'email'        => 'required|email|max:255',
            'access_role'  => ['required', 'string', Rule::in($accessRoleOptions)],
        ]);

        if ($data['access_role'] === 'company_admin') {
            abort_unless(auth()->user()->isCompanyAdmin(), 403);
            $data['role'] = 'company_admin';
            $data['workspace_role_id'] = null;
        } elseif ($data['access_role'] === 'client') {
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

    public function resend(string $slug, Invitation $invitation)
    {
        abort_if($invitation->company_id !== $this->company()->id, 403);

        $result = $this->invites->resend($invitation);

        $message = $result['mailed']
            ? 'Invite resent.'
            : 'New invite link created, but email could not be sent. Copy the link below.';

        return back()
            ->with('success', $message)
            ->with('invite_url', $result['url']);
    }

    public function revoke(string $slug, Invitation $invitation)
    {
        abort_if($invitation->company_id !== $this->company()->id, 403);
        abort_if($invitation->accepted_at, 422);

        $invitation->delete();

        return back()->with('success', 'Invite revoked.');
    }

    public function toggle(string $slug, User $user)
    {
        abort_if($user->company_id !== $this->company()->id, 403);
        $user->update(['is_active' => ! $user->is_active]);

        return back()->with('success', 'Member status updated.');
    }
}
